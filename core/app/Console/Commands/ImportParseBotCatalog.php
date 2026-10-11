<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Attribute as ProductAttribute;
use App\Models\AttributeValue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class ImportParseBotCatalog extends Command
{
    protected $signature = 'parsebot:import-catalog {--credit-limit=250 : Maximum Parse.bot request attempts for this run (daily limit: 250)} {--all-pages : Fetch category pages round-robin up to the credit limit} {--details-only : Enrich products already in the local database without fetching category listings} {--sync-stored-details : Populate product columns from details already saved locally without API calls} {--categories-only : Save category records and hosted category image URLs without scraper requests} {--category-ids= : Comma-separated source category IDs to import} {--listings-only : Skip product detail requests and save listing data only}';
    protected $description = 'Import Jomashop category listings, optionally paginating fairly across categories within a Parse.bot credit cap.';

    private const SOURCE = 'parsebot_jomashop';
    private const CATEGORY_IMAGES = [
        10709 => 'https://www.jomashop.com/media/catalog/category/sale.png',
        17043 => 'https://cdn2.jomashop.com/media/wysiwyg/mens.jpg',
        17045 => 'https://cdn2.jomashop.com/media/wysiwyg/ladies.jpg',
        871 => 'https://www.jomashop.com/media/catalog/category/mens_1.png',
        31 => 'https://www.jomashop.com/media/catalog/category/jewelry.png',
        46 => 'https://www.jomashop.com/media/catalog/category/handbags.png',
        50 => 'https://www.jomashop.com/media/catalog/category/sunglasses.png',
        3789 => 'https://cdn2.jomashop.com/media/wysiwyg/pr_eyeglasses.jpg',
        5869 => 'https://cdn2.jomashop.com/media/wysiwyg/1461c65f-1ec1-4f55-b0ef-f759578c3172_1.jpeg',
        3432 => 'https://www.jomashop.com/media/catalog/category/beauty.png',
        63 => 'https://www.jomashop.com/media/catalog/category/outerwear.png',
        5677 => 'https://www.jomashop.com/media/catalog/category/shoes.png',
        4621 => 'https://www.jomashop.com/media/catalog/category/pre-owned.png',
        17587 => 'https://cdn2.jomashop.com/media/wysiwyg/all_gifts.jpg',
    ];
    private const CATEGORIES = [
        10709 => 'Sale',
        17043 => 'Men',
        17045 => 'Women',
        871 => 'Watches',
        31 => 'Jewelry',
        46 => 'Handbags',
        50 => 'Sunglasses',
        3789 => 'Eyeglasses',
        5869 => 'Fragrances',
        3432 => 'Beauty',
        63 => 'Clothing',
        5677 => 'Shoes',
        4621 => 'Pre-owned',
        17587 => 'Gifts',
    ];
    private const DAILY_REQUEST_CAP = 250;
    private array $sourceAttributeCache = [];
    private array $sourceAttributeValueCache = [];

    public function handle(): int
    {
        if ($this->option('sync-stored-details')) {
            return $this->syncStoredDetails();
        }

        $baseUrl = rtrim((string) config('services.parsebot.scraper_url'), '/');
        $limit = (int) $this->option('credit-limit');

        if (!config('services.parsebot.api_key')) {
            $this->error('PARSE_BOT_API_KEY is not configured in Replit Secrets.');
            return self::FAILURE;
        }
        if (!filter_var($baseUrl, FILTER_VALIDATE_URL) || !str_starts_with($baseUrl, 'https://')) {
            $this->error('PARSE_BOT_SCRAPER_URL must be a valid HTTPS URL.');
            return self::FAILURE;
        }
        if ($limit < 1 || $limit > self::DAILY_REQUEST_CAP) {
            $this->error('The credit limit must be between 1 and 250.');
            return self::FAILURE;
        }

        if ($this->option('categories-only') && $this->option('details-only')) {
            $this->error('Choose either --categories-only or --details-only, not both.');
            return self::FAILURE;
        }
        if ($this->option('categories-only')) {
            foreach (self::CATEGORIES as $sourceId => $categoryName) {
                $this->saveCategory($sourceId, $categoryName);
            }
            $this->info('Saved all 15 categories with hosted image URLs where the source provides them.');
            return self::SUCCESS;
        }

        $remainingToday = self::DAILY_REQUEST_CAP - $this->dailyRequestsUsedToday();
        if ($remainingToday < 1) {
            $this->error('The Parse.bot daily request limit of 250 has been reached. Try again after the daily reset.');
            return self::FAILURE;
        }
        $limit = min($limit, $remainingToday);
        $this->info("Parse.bot request budget today: {$remainingToday} remaining (maximum 250).");
        if ($this->option('details-only')) {
            return $this->enrichExistingProducts($baseUrl, $limit);
        }

        $categoriesToImport = self::CATEGORIES;
        $categoryIdsOption = trim((string) $this->option('category-ids'));
        if ($categoryIdsOption !== '') {
            $requestedIds = array_values(array_unique(array_filter(array_map('intval', explode(',', $categoryIdsOption)))));
            $categoriesToImport = array_intersect_key(self::CATEGORIES, array_fill_keys($requestedIds, true));
            if (count($categoriesToImport) !== count($requestedIds)) {
                $this->error('One or more requested category IDs are not configured.');
                return self::FAILURE;
            }
        }

        $calls = 0;
        $fetchedCategories = 0;
        $pageCounts = [];
        $products = [];
        $sourceCategories = [];
        $listingProgress = $this->option('all-pages') ? $this->loadListingProgress() : [];
        $categoryFailures = [];

        foreach ($categoriesToImport as $sourceId => $categoryName) {
            if ($calls >= $limit) {
                $this->warn('Credit limit reached before all category pages could be fetched.');
                break;
            }

            $category = $this->saveCategory($sourceId, $categoryName);
            $sourceCategories[$sourceId] = $category->id;

            $sourceCategories[$sourceId] = $category->id;
            if (
                $this->option('all-pages')
                && isset($listingProgress[$sourceId]['total_pages'])
                && (int) ($listingProgress[$sourceId]['next_page'] ?? 1) > 1
            ) {
                $pageCounts[$sourceId] = max(1, (int) $listingProgress[$sourceId]['total_pages']);
                continue;
            }

            $calls++;
            try {
                $payload = $this->callApi($baseUrl, 'get_category_products', [
                    'category_id' => $sourceId,
                    'page' => 1,
                    'page_size' => 60,
                    'sort_by' => 'most_viewed',
                ]);
            } catch (\Throwable $e) {
                if (preg_match('/HTTP (401|403|429)\\b/', $e->getMessage())) {
                    throw $e;
                }
                $this->warn("Skipped product listing for {$categoryName}: {$e->getMessage()}");
                $categoryFailures[$sourceId] = true;
                continue;
            }
            $fetchedCategories++;
            $data = $payload['data'] ?? $payload;
            $pageCounts[$sourceId] = max(1, (int) ($data['total_pages'] ?? $data['totalPages'] ?? 1));
            $categoryName = (string) ($data['name'] ?? $categoryName);
            $category = $this->saveCategory($sourceId, $categoryName);
            $sourceCategories[$sourceId] = $category->id;

            foreach (($data['items'] ?? []) as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $identity = (string) ($item['id'] ?? $item['sku'] ?? $item['url_key'] ?? '');
                if ($identity === '') {
                    continue;
                }
                $products[$identity]['listing'] = $item;
                $products[$identity]['category_ids'][$sourceId] = $category->id;
                $product = $this->saveProduct($identity, $item, []);
                $this->attachCategories($product, [$sourceId => $category->id]);
            }

            if ($this->option('all-pages')) {
                $listingProgress[$sourceId] = [
                    'total_pages' => $pageCounts[$sourceId],
                    'next_page' => 2,
                ];
                $this->saveListingProgress($listingProgress);
            }
        }

        $fetchedPages = $fetchedCategories;
        if ($this->option('all-pages')) {
            while ($calls < $limit) {
                $madeProgress = false;
                foreach ($pageCounts as $sourceId => $totalPages) {
                    if ($calls >= $limit) {
                        break;
                    }
                    if (isset($categoryFailures[$sourceId])) {
                        continue;
                    }
                    $page = (int) ($listingProgress[$sourceId]['next_page'] ?? 2);
                    if ($page > $totalPages) {
                        continue;
                    }

                    $calls++;
                    try {
                        $payload = $this->callApi($baseUrl, 'get_category_products', [
                            'category_id' => $sourceId,
                            'page' => $page,
                            'page_size' => 60,
                            'sort_by' => 'most_viewed',
                        ]);
                    } catch (\Throwable $e) {
                        if (preg_match('/HTTP (401|403|429)\b/', $e->getMessage())) {
                            throw $e;
                        }
                        $this->warn("Skipped page {$page} for {$categoriesToImport[$sourceId]}: {$e->getMessage()}");
                        $categoryFailures[$sourceId] = true;
                        continue;
                    }

                    $fetchedPages++;
                    $madeProgress = true;
                    $data = $payload['data'] ?? $payload;
                    foreach (($data['items'] ?? []) as $item) {
                        if (!is_array($item)) {
                            continue;
                        }
                        $identity = (string) ($item['id'] ?? $item['sku'] ?? $item['url_key'] ?? '');
                        if ($identity === '') {
                            continue;
                        }
                        $products[$identity]['listing'] = $item;
                        $products[$identity]['category_ids'][$sourceId] = $sourceCategories[$sourceId];
                        $product = $this->saveProduct($identity, $item, []);
                        $this->attachCategories($product, [$sourceId => $sourceCategories[$sourceId]]);
                    }

                    $listingProgress[$sourceId]['next_page'] = $page + 1;
                    $this->saveListingProgress($listingProgress);
                }
                if (!$madeProgress) {
                    break;
                }
            }
        }

        $this->info("Fetched {$fetchedPages} listing pages across {$fetchedCategories} categories.");
        $this->info('Unique products in those pages: ' . count($products) . '.');

        if ($this->option('listings-only')) {
            $this->info("Imported/updated " . count($products) . " listing products; API requests attempted: {$calls}/{$limit}.");
            return self::SUCCESS;
        }

        foreach ($products as $identity => $productData) {
            $listing = $productData['listing'];
            $product = $this->saveProduct($identity, $listing, []);
            $this->attachCategories($product, $productData['category_ids']);

            if ($calls >= $limit) {
                continue;
            }
            if ($this->hasStoredDetails($product)) {
                continue;
            }

            $urlKey = (string) ($listing['url_key'] ?? '');
            if ($urlKey === '') {
                continue;
            }

            try {
                $calls++;
                $detailsPayload = $this->callApi($baseUrl, 'get_product_details', ['url_key' => $urlKey]);
                $details = $detailsPayload['data'] ?? $detailsPayload;
                if (is_array($details)) {
                    $product = $this->saveProduct($identity, $listing, $details);
                    $this->attachCategories($product, $productData['category_ids']);
                }
            } catch (\Throwable $e) {
                if (preg_match('/HTTP (401|403|429)\\b/', $e->getMessage())) {
                    throw $e;
                }
                $this->warn("Could not enrich product {$identity}: {$e->getMessage()}");
            }
        }

        $this->info("Imported/updated " . count($products) . " products; API requests attempted: {$calls}/{$limit}.");
        return self::SUCCESS;
    }

    private function enrichExistingProducts(string $baseUrl, int $limit): int
    {
        $products = Product::query()
            ->where('external_source', self::SOURCE)
            ->orderBy('id')
            ->get()
            ->filter(fn (Product $product) => !$this->hasStoredDetails($product))
            ->values();
        $calls = 0;
        $enriched = 0;

        foreach ($products as $product) {
            if ($calls >= $limit) {
                break;
            }

            $suffix = '-' . (string) $product->external_id;
            if (!Str::endsWith((string) $product->slug, $suffix)) {
                $this->warn("Skipped product {$product->external_id}: could not recover its source URL key.");
                continue;
            }
            $urlKey = substr((string) $product->slug, 0, -strlen($suffix));
            if ($urlKey === '') {
                $this->warn("Skipped product {$product->external_id}: source URL key is empty.");
                continue;
            }

            $calls++;
            try {
                $payload = $this->callApi($baseUrl, 'get_product_details', ['url_key' => $urlKey]);
                $details = $payload['data'] ?? $payload;
                if (!is_array($details)) {
                    $this->warn("Product details were invalid for {$product->external_id}.");
                    continue;
                }

                $listing = [
                    'id' => (string) $product->external_id,
                    'url_key' => $urlKey,
                    'name' => $product->name,
                    'sku' => $product->sku,
                ];
                $this->saveProduct((string) $product->external_id, $listing, $details);
                $enriched++;
            } catch (\Throwable $e) {
                if (preg_match('/HTTP (401|403|429)\\b/', $e->getMessage())) {
                    throw $e;
                }
                $this->warn("Could not enrich product {$product->external_id}: {$e->getMessage()}");
            }
        }

        $this->info("Enriched {$enriched} existing products; API requests attempted: {$calls}/{$limit}.");
        return self::SUCCESS;
    }

    private function callApi(string $baseUrl, string $endpoint, array $query): array
    {
        $this->reserveDailyRequestSlot();
        try {
            $response = Http::acceptJson()
                ->withHeaders([
                    'X-API-Key' => config('services.parsebot.api_key'),
                    'API-Snapshot-Version' => (string) config('services.parsebot.snapshot_version', 14),
                ])
                ->connectTimeout(10)
                ->timeout(45)
                ->get($baseUrl . '/' . $endpoint, $query);
        } catch (\Throwable $e) {
            // Do not chain the HTTP exception: framework traces can include request headers.
            throw new RuntimeException('Parse.bot request timed out or could not connect.');
        }

        if (!$response->successful()) {
            throw new RuntimeException("Parse.bot returned HTTP {$response->status()}.");
        }

        $payload = $response->json();
        if (!is_array($payload) || (($payload['status'] ?? 'success') !== 'success')) {
            throw new RuntimeException('Parse.bot returned an unsuccessful or invalid response.');
        }

        return $payload;
    }

    private function dailyRequestUsagePath(): string
    {
        return dirname(base_path()) . '/.mysql-data/parsebot-daily-request-usage.json';
    }

    private function dailyRequestsUsedToday(): int
    {
        $path = $this->dailyRequestUsagePath();
        if (!is_file($path)) {
            return 0;
        }
        $contents = file_get_contents($path);
        $state = is_string($contents) ? json_decode($contents, true) : null;
        if (!is_array($state) || !isset($state['date'], $state['used'])) {
            throw new RuntimeException('Parse.bot daily request usage state is invalid; refusing to risk exceeding the daily limit.');
        }

        return $state['date'] === now()->toDateString() ? max(0, (int) $state['used']) : 0;
    }

    private function reserveDailyRequestSlot(): void
    {
        $path = $this->dailyRequestUsagePath();
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new RuntimeException('Could not create the Parse.bot request usage directory.');
        }
        $handle = fopen($path, 'c+');
        if ($handle === false) {
            throw new RuntimeException('Could not open Parse.bot daily request usage state.');
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                throw new RuntimeException('Could not lock Parse.bot daily request usage state.');
            }
            rewind($handle);
            $contents = stream_get_contents($handle);
            $state = $contents ? json_decode($contents, true) : ['date' => now()->toDateString(), 'used' => 0];
            if (!is_array($state) || !isset($state['date'], $state['used'])) {
                throw new RuntimeException('Parse.bot daily request usage state is invalid; refusing to risk exceeding the daily limit.');
            }

            $today = now()->toDateString();
            $used = $state['date'] === $today ? max(0, (int) $state['used']) : 0;
            if ($used >= self::DAILY_REQUEST_CAP) {
            throw new RuntimeException('Parse.bot daily request limit of 250 has been reached.');
            }

            $nextState = json_encode(['date' => $today, 'used' => $used + 1], JSON_UNESCAPED_SLASHES);
            if (!is_string($nextState)) {
                throw new RuntimeException('Could not encode Parse.bot daily request usage state.');
            }
            rewind($handle);
            ftruncate($handle, 0);
            if (fwrite($handle, $nextState) === false || !fflush($handle)) {
                throw new RuntimeException('Could not save Parse.bot daily request usage state.');
            }
            @chmod($path, 0600);
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function saveCategory(int $sourceId, string $name): Category
    {
        $category = Category::withTrashed()->firstOrNew([
            'external_source' => self::SOURCE,
            'external_id' => (string) $sourceId,
        ]);
        $category->name = Str::limit($name, 40, '');
        $category->slug = Str::limit(Str::slug($name . '-' . $sourceId), 40, '');
        $category->parent_id = null;
        $category->position = $sourceId;
        $category->is_featured = 0;
        $category->feature_in_banner = 0;
        $imageUrl = self::CATEGORY_IMAGES[$sourceId] ?? null;
        if ($imageUrl && filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            $category->external_image_url = $imageUrl;
        }
        $category->deleted_at = null;
        $category->save();

        return $category;
    }

    private function saveProduct(string $identity, array $listing, array $details): Product
    {
        $externalId = (string) ($listing['id'] ?? $identity);
        $product = Product::withTrashed()
            ->where('external_source', self::SOURCE)
            ->where('external_id', $externalId)
            ->first();
        if (!$product) {
            $product = new Product();
        }

        $storedSnapshot = json_decode((string) $product->external_details, true);
        $storedSnapshot = is_array($storedSnapshot) ? $storedSnapshot : [];
        $storedListing = is_array($storedSnapshot['listing'] ?? null) ? $storedSnapshot['listing'] : [];
        $storedDetails = $this->extractStoredDetails($storedSnapshot);
        $listing = array_replace_recursive($storedListing, $listing);
        $details = $details ?: $storedDetails;
        $data = array_replace_recursive($listing, $details);
        $name = (string) ($data['name'] ?? $listing['name'] ?? 'Imported product ' . $externalId);
        $urlKey = (string) ($data['url_key'] ?? $listing['url_key'] ?? '');
        $listingImage = data_get($data, 'image.url_nocache')
            ?? data_get($data, 'image.url')
            ?? (is_string($data['image'] ?? null) ? $data['image'] : null)
            ?? $data['image_url']
            ?? data_get($data, 'media_gallery.0.sizes.0.url')
            ?? data_get($data, 'media_gallery.0.url')
            ?? null;
        $rawImage = data_get($details, 'image.url_nocache')
            ?? data_get($details, 'image.url')
            ?? data_get($details, 'media_gallery.0.sizes.0.url')
            ?? $listingImage;
        $imageUrl = is_string($rawImage) && in_array(parse_url($rawImage, PHP_URL_SCHEME), ['https', 'http'], true)
            ? $rawImage
            : null;
        $description = data_get($details, 'description.html')
            ?? $details['description']
            ?? data_get($data, 'description.html')
            ?? $data['description']
            ?? null;
        if (is_array($description)) {
            $description = collect($description)
                ->filter(fn ($part) => is_string($part) && trim($part) !== '' && trim($part) !== 'ComplexTextValue')
                ->map(fn ($part) => trim($part))
                ->implode("\n\n");
        }
        $description = is_string($description) && trim($description) !== '' && trim($description) !== 'ComplexTextValue'
            ? trim($description)
            : null;

        $regularPrice = $data['price']
            ?? $data['regular_price']
            ?? $data['msrp']
            ?? data_get($data, 'price_range.minimum_price.msrp_price.value')
            ?? $product->regular_price;
        $listedSalePrice = $data['sale_price']
            ?? data_get($data, 'price_range.minimum_price.plp_price.now_price')
            ?? data_get($data, 'price_range.minimum_price.final_price.value');
        $salePrice = is_numeric($listedSalePrice) && is_numeric($regularPrice) && (float) $listedSalePrice < (float) $regularPrice
            ? $listedSalePrice
            : $product->sale_price;
        $brandId = $this->resolveBrandId($data) ?? $product->brand_id;
        $specification = data_get($details, 'moredetails.more_details_text')
            ?? data_get($data, 'moredetails.more_details_text')
            ?? $product->specification;
        $specificationFacts = is_object($specification) ? (array) $specification : (is_array($specification) ? $specification : []);
        $specificationFacts = array_filter($specificationFacts, fn ($value, $key) =>
            !str_starts_with((string) $key, '__')
            && !in_array((string) $key, ['out_of_stock_template', 'on_hand_priority'], true)
            && (is_string($value) || is_numeric($value))
            && trim((string) $value) !== '', ARRAY_FILTER_USE_BOTH);
        $oldSpecificationDescription = $this->formatSpecificationDescription($specificationFacts);
        $existingExtraDescriptions = $product->extra_descriptions;
        $oldExtraDescriptionText = $this->formatExtraDescriptionText($existingExtraDescriptions);
        $descriptionWasSpecificationFallback = $oldSpecificationDescription !== ''
            && $this->matchesImportedText($product->description, $oldSpecificationDescription);
        $descriptionWasSpecificationFallback = $descriptionWasSpecificationFallback
            || $this->matchesImportedText($product->description, $oldExtraDescriptionText);

        $storedDescription = (string) ($product->description ?? '');
        if (trim($storedDescription) === 'ComplexTextValue') {
            $storedDescription = '';
            $product->description = null;
        }
        if ($description === null && !$descriptionWasSpecificationFallback) {
            $description = $storedDescription !== '' ? $storedDescription : null;
        }

        $sourceExtraDescriptions = data_get($details, 'extra_descriptions')
            ?? data_get($data, 'extra_descriptions')
            ?? null;
        if (!is_array($sourceExtraDescriptions)) {
            if (is_array($existingExtraDescriptions) && $this->matchesSpecificationExtraDescriptions($existingExtraDescriptions, $specificationFacts)) {
                $sourceExtraDescriptions = null;
            } else {
                $sourceExtraDescriptions = $existingExtraDescriptions;
            }
        }

        $summary = $product->summary;
        $metaDescription = $product->meta_description;
        if ($descriptionWasSpecificationFallback) {
            $summary = null;
            $metaDescription = null;
        }
        if ($description !== null && ($descriptionWasSpecificationFallback || empty($summary))) {
            $summary = Str::limit(trim(strip_tags($description)), 6000, '');
        }
        if ($description !== null && ($descriptionWasSpecificationFallback || empty($metaDescription))) {
            $metaDescription = Str::limit(trim(strip_tags($description)), 255, '');
        }
        $manufacturer = data_get($data, 'moredetails.more_details_text.manufacturer');
        $sourceKeywords = array_values(array_filter([
            $name,
            is_string($manufacturer) ? $manufacturer : null,
            data_get($data, 'moredetails.more_details_text.department'),
            data_get($data, 'moredetails.more_details_text.subtype'),
        ], fn ($value) => is_string($value) && trim($value) !== ''));
        $stockStatus = $data['stock_status'] ?? null;

        $product->forceFill([
            'name' => Str::limit($name, 255, ''),
            'slug' => Str::limit(Str::slug(($urlKey ?: $name) . '-' . $externalId), 255, ''),
            'sku' => isset($data['sku']) ? Str::limit((string) $data['sku'], 40, '') : null,
            'regular_price' => is_numeric($regularPrice) ? $regularPrice : null,
            'sale_price' => is_numeric($salePrice) ? $salePrice : null,
            'description' => $description !== null ? $this->escapeDescriptionOnce($description) : null,
            'summary' => $summary,
            'brand_id' => $brandId,
            'specification' => $specification,
            'extra_descriptions' => $sourceExtraDescriptions,
            'video_link' => $this->sourceVideoUrls($listing, $details)[0] ?? $product->video_link,
            'meta_title' => $product->meta_title ?: Str::limit($name, 255, ''),
            'meta_description' => $metaDescription,
            'meta_keywords' => $product->meta_keywords ?: $sourceKeywords,
            'product_type' => 1,
            'product_type_id' => 0,
            'is_published' => 1,
            'show_in_products_page' => 1,
            'track_inventory' => 0,
            'show_stock' => 0,
            'in_stock' => $stockStatus === null ? $product->in_stock : (strtoupper((string) $stockStatus) === 'IN_STOCK' ? 1 : 0),
            'external_source' => self::SOURCE,
            'external_id' => $externalId,
            'external_image_url' => $imageUrl ?? $product->external_image_url,
            'external_details' => json_encode([
                'listing' => $listing,
                'details' => $details ?: null,
            ], JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE),
            'deleted_at' => null,
        ])->save();

        $representativeImageUrl = $imageUrl ?? $product->external_image_url;
        if ($brandId && is_string($representativeImageUrl) && str_starts_with($representativeImageUrl, 'https://')) {
            Brand::query()
                ->whereKey($brandId)
                ->whereNull('external_image_url')
                ->update(['external_image_url' => $representativeImageUrl]);
        }

        $sourceAttributeFacts = $specificationFacts;
        foreach (['is_preowned', 'is_labgrown', 'is_tester', 'has_virtual_try_on', 'has_prescription'] as $flag) {
            $sourceValue = $data[$flag] ?? null;
            if (!is_scalar($sourceValue)) {
                continue;
            }

            $normalizedValue = strtolower(trim((string) $sourceValue));
            if (in_array($normalizedValue, ['1', 'true', 'yes', 'y'], true)) {
                $sourceAttributeFacts[$flag] = 'Yes';
            } elseif (in_array($normalizedValue, ['0', 'false', 'no', 'n'], true)) {
                $sourceAttributeFacts[$flag] = 'No';
            }
        }
        $this->syncSourceAttributes($product, $sourceAttributeFacts);

        if (!empty($details['variants']) && is_array($details['variants'])) {
            $this->syncSourceVariants($product, $details['variants']);
        }

        return $product;
    }

    private function formatSpecificationDescription(array $facts): string
    {
        if (!$facts) {
            return '';
        }

        return collect($facts)
            ->map(fn ($value, $key) => Str::headline((string) $key) . ': ' . trim((string) $value))
            ->implode('. ');
    }

    private function matchesImportedText(?string $current, string $expected): bool
    {
        if (!$current || $expected === '') {
            return false;
        }

        return $this->normalizeImportedText($current) === $this->normalizeImportedText($expected);
    }

    private function normalizeImportedText(string $text): string
    {
        $normalized = trim($text);
        for ($attempt = 0; $attempt < 32; $attempt++) {
            $decoded = trim(html_entity_decode($normalized, ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, 'UTF-8'));
            if ($decoded === $normalized) {
                break;
            }
            $normalized = $decoded;
        }
        return $normalized;
    }

    private function escapeDescriptionOnce(string $description): string
    {
        $description = html_entity_decode($description, ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, 'UTF-8');
        return htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function formatExtraDescriptionText(mixed $extraDescriptions): string
    {
        if (!is_array($extraDescriptions)) {
            return '';
        }
        $parts = [];
        foreach ($extraDescriptions as $entry) {
            if (
                is_array($entry)
                && is_string($entry['key'] ?? null)
                && is_string($entry['value'] ?? null)
                && trim($entry['key']) !== ''
                && trim($entry['value']) !== ''
            ) {
                $parts[] = trim($entry['key']) . ': ' . trim($entry['value']);
            }
        }

        return implode('. ', $parts);
    }

    private function matchesSpecificationExtraDescriptions(array $existing, array $facts): bool
    {
        if (!$existing || !$facts) {
            return false;
        }

        $expected = [];
        foreach ($facts as $key => $value) {
            $expected[strtolower(Str::headline((string) $key))] = trim((string) $value);
        }

        foreach ($existing as $entry) {
            if (
                !is_array($entry)
                || !is_string($entry['key'] ?? null)
                || !is_string($entry['value'] ?? null)
                || !array_key_exists(strtolower(trim($entry['key'])), $expected)
                || trim($entry['value']) !== $expected[strtolower(trim($entry['key']))]
            ) {
                return false;
            }
        }

        return true;
    }

    private function listingProgressPath(): string
    {
        return dirname(base_path()) . '/.mysql-data/parsebot-listing-progress.json';
    }

    private function loadListingProgress(): array
    {
        $contents = @file_get_contents($this->listingProgressPath());
        if (!is_string($contents) || $contents === '') {
            return [];
        }
        $progress = json_decode($contents, true);
        return is_array($progress) ? $progress : [];
    }

    private function saveListingProgress(array $progress): void
    {
        $path = $this->listingProgressPath();
        $temporaryPath = $path . '.tmp';
        $encoded = json_encode($progress, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if (!is_string($encoded) || file_put_contents($temporaryPath, $encoded, LOCK_EX) === false) {
            throw new RuntimeException('Could not persist Parse.bot listing progress.');
        }
        @chmod($temporaryPath, 0600);
        if (!rename($temporaryPath, $path)) {
            @unlink($temporaryPath);
            throw new RuntimeException('Could not finalize Parse.bot listing progress.');
        }
    }

    private function sourceVideoUrls(array $listing, array $details): array
    {
        $urls = [];
        $walk = function ($node, bool $inVideoField = false, string $key = '') use (&$walk, &$urls) {
            $inVideoField = $inVideoField || str_contains(strtolower($key), 'video');
            if (is_string($node)) {
                $isVideoValue = str_contains(strtolower($key), 'video')
                    || ($inVideoField && in_array(strtolower($key), ['url', 'src', 'link', 'content_url', 'embed_url'], true));
                if ($isVideoValue && in_array(parse_url($node, PHP_URL_SCHEME), ['https', 'http'], true)) {
                    $urls[] = $node;
                }
                return;
            }
            if (!is_array($node)) {
                return;
            }
            foreach ($node as $childKey => $value) {
                $walk($value, $inVideoField, (string) $childKey);
            }
        };
        $walk($listing);
        $walk($details);
        return array_values(array_unique($urls));
    }

    private function syncSourceVariants(Product $product, array $sourceVariants): void
    {
        foreach ($sourceVariants as $sourceVariant) {
            $variantData = $sourceVariant['product'] ?? null;
            if (!is_array($variantData)) {
                continue;
            }

            $valueIds = [];
            foreach (($sourceVariant['attributes'] ?? []) as $option) {
                if (!is_array($option)) {
                    continue;
                }
                $code = strtolower((string) ($option['code'] ?? ''));
                $label = trim((string) ($option['label'] ?? $option['value'] ?? ''));
                if ($code === '' || $label === '') {
                    continue;
                }

                $attributeName = str_contains($code, 'size')
                    ? 'Size'
                    : (str_contains($code, 'color') || str_contains($code, 'colour')
                        ? 'Color'
                        : Str::limit(Str::headline($code), 40, ''));
                $attribute = ProductAttribute::query()
                    ->whereRaw('LOWER(name) = ?', [strtolower($attributeName)])
                    ->first();
                if (!$attribute) {
                    $attribute = new ProductAttribute();
                    $attribute->name = $attributeName;
                    $attribute->type = 1;
                    $attribute->status = 1;
                    $attribute->save();
                }

                $storedLabel = Str::limit($label, 40, '');
                $value = AttributeValue::query()
                    ->where('attribute_id', $attribute->id)
                    ->where('name', $storedLabel)
                    ->first() ?? new AttributeValue();
                $value->attribute_id = $attribute->id;
                $value->name = $storedLabel;
                $value->value = $label;
                $value->save();

                $product->attributes()->syncWithoutDetaching([$attribute->id]);
                $product->attributeValues()->syncWithoutDetaching([$value->id => ['media_id' => 0]]);
                $valueIds[] = (int) $value->id;
            }

            if (!$valueIds) {
                continue;
            }

            $sku = isset($variantData['sku']) ? Str::limit((string) $variantData['sku'], 40, '') : null;
            $variantQuery = ProductVariant::query()->where('product_id', $product->id);
            $variantQuery = $sku !== null && $sku !== ''
                ? $variantQuery->where('sku', $sku)
                : $variantQuery->where('name', (string) ($variantData['name'] ?? ''));
            $variant = $variantQuery->first() ?? new ProductVariant();

            $regularPrice = $variantData['msrp']
                ?? data_get($variantData, 'price_range.minimum_price.msrp_price.value')
                ?? null;
            $salePrice = data_get($variantData, 'price_range.minimum_price.plp_price.now_price')
                ?? data_get($variantData, 'price_range.minimum_price.final_price.value');
            $stockStatus = $variantData['stock_status'] ?? null;

            $variant->fill([
                'product_id' => $product->id,
                'name' => Str::limit((string) ($variantData['name'] ?? $product->name), 255, ''),
                'sku' => $sku,
                'manage_stock' => 0,
                'track_inventory' => 0,
                'show_stock' => 0,
                'in_stock' => $stockStatus === null
                    ? $product->in_stock
                    : (strtoupper((string) $stockStatus) === 'IN_STOCK' ? 1 : 0),
                'attribute_values' => array_values(array_unique($valueIds)),
                'regular_price' => is_numeric($regularPrice) ? $regularPrice : $product->regular_price,
                'sale_price' => is_numeric($salePrice) && is_numeric($regularPrice) && (float) $salePrice < (float) $regularPrice
                    ? $salePrice
                    : null,
                'main_image_id' => 0,
                'is_published' => 1,
            ])->save();
        }
    }

    private function syncSourceAttributes(Product $product, array $facts): void
    {
        foreach ($facts as $sourceKey => $sourceValue) {
            if (
                str_starts_with((string) $sourceKey, '__')
                || !is_scalar($sourceValue)
                || trim((string) $sourceValue) === ''
            ) {
                continue;
            }

            $attributeName = Str::limit(Str::headline((string) $sourceKey), 40, '');
            if ($attributeName === '') {
                continue;
            }

            $attributeCacheKey = strtolower($attributeName);
            $attribute = $this->sourceAttributeCache[$attributeCacheKey] ?? null;
            if (!$attribute) {
                $attribute = ProductAttribute::query()
                    ->whereRaw('LOWER(name) = ?', [$attributeCacheKey])
                    ->first();
                if (!$attribute) {
                    $attribute = new ProductAttribute();
                    $attribute->name = $attributeName;
                    $attribute->type = 1;
                    $attribute->status = 1;
                    $attribute->save();
                }
                $this->sourceAttributeCache[$attributeCacheKey] = $attribute;
            }

            $label = trim((string) $sourceValue);
            $storedLabel = Str::limit($label, 40, '');
            $valueCacheKey = $attribute->id . ':' . $storedLabel;
            $value = $this->sourceAttributeValueCache[$valueCacheKey] ?? null;
            if (!$value) {
                $value = AttributeValue::query()
                    ->where('attribute_id', $attribute->id)
                    ->where('name', $storedLabel)
                    ->first() ?? new AttributeValue();
                $value->attribute_id = $attribute->id;
                $value->name = $storedLabel;
                $value->value = Str::limit($label, 255, '');
                $value->save();
                $this->sourceAttributeValueCache[$valueCacheKey] = $value;
            }

            $product->attributes()->syncWithoutDetaching([(int) $attribute->id]);
            $product->attributeValues()->syncWithoutDetaching([(int) $value->id => ['media_id' => 0]]);
        }
    }

    private function resolveBrandId(array $data): ?int
    {
        $brandName = data_get($data, 'moredetails.more_details_text.manufacturer')
            ?? data_get($data, 'manufacturer')
            ?? data_get($data, 'brand.name')
            ?? (is_string($data['brand'] ?? null) ? $data['brand'] : null);

        if (!is_string($brandName) || trim($brandName) === '') {
            return null;
        }

        $brandName = trim($brandName);
        $slug = Str::limit(Str::slug($brandName), 40, '');
        if ($slug === '') {
            return null;
        }

        $brand = Brand::withTrashed()->firstOrNew(['slug' => $slug]);
        $brand->name = Str::limit($brandName, 40, '');
        $brand->slug = $slug;
        $brand->deleted_at = null;
        $brand->save();

        return (int) $brand->id;
    }

    private function syncStoredDetails(): int
    {
        $products = Product::query()
            ->where('external_source', self::SOURCE)
            ->whereNotNull('external_details')
            ->whereRaw("JSON_VALID(external_details)")
            ->whereRaw("JSON_TYPE(JSON_EXTRACT(external_details, '$.details')) = 'OBJECT'")
            ->get();
        $updated = 0;

        foreach ($products as $product) {
            $snapshot = json_decode((string) $product->external_details, true);
            if (!is_array($snapshot)) {
                continue;
            }
            $details = $this->extractStoredDetails($snapshot);

            $suffix = '-' . (string) $product->external_id;
            $urlKey = Str::endsWith((string) $product->slug, $suffix)
                ? substr((string) $product->slug, 0, -strlen($suffix))
                : '';
            $listing = is_array($snapshot['listing'] ?? null) ? $snapshot['listing'] : [
                'id' => (string) $product->external_id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->regular_price,
                'url_key' => $urlKey,
                'stock_status' => $details['stock_status'] ?? null,
            ];

            $this->saveProduct((string) $product->external_id, $listing, $details);
            $updated++;
        }

        $this->info("Synced stored details for {$updated} products without making API requests.");
        return self::SUCCESS;
    }

    private function syncCatalogSeoMetadata(): void
    {
        Product::query()
            ->where('external_source', self::SOURCE)
            ->with('brand')
            ->chunkById(250, function ($products) {
                foreach ($products as $product) {
                    $product->meta_title = $product->meta_title ?: Str::limit($product->name, 255, '');
                    $plainDescription = trim(strip_tags((string) $product->description));
                    $product->meta_description = $product->meta_description
                        ?: Str::limit($plainDescription !== '' ? $plainDescription : 'Browse ' . $product->name . ' in the ViserMart catalog.', 255, '');
                    $product->meta_keywords = $product->meta_keywords ?: array_values(array_filter([
                        $product->name,
                        $product->brand?->name,
                    ]));
                    $product->save();
                }
            });

        $brandIds = Product::query()
            ->where('external_source', self::SOURCE)
            ->whereNotNull('brand_id')
            ->distinct()
            ->pluck('brand_id');

        foreach (Brand::whereIn('id', $brandIds)->get() as $brand) {
            $brand->meta_title = $brand->meta_title ?: Str::limit('Shop ' . $brand->name . ' | ViserMart', 255, '');
            $brand->meta_description = $brand->meta_description ?: Str::limit('Browse products by ' . $brand->name . ' in the ViserMart catalog.', 255, '');
            $brand->meta_keywords = $brand->meta_keywords ?: array_unique([$brand->name, $brand->name . ' products']);
            $brand->save();
        }

        foreach (Category::where('external_source', self::SOURCE)->get() as $category) {
            $category->meta_title = $category->meta_title ?: Str::limit('Shop ' . $category->name . ' | ViserMart', 255, '');
            $category->meta_description = $category->meta_description ?: Str::limit('Browse ' . $category->name . ' products in the ViserMart catalog.', 255, '');
            $category->meta_keywords = $category->meta_keywords ?: array_unique([$category->name, $category->name . ' products']);
            $category->save();
        }
    }

    private function hasStoredDetails(Product $product): bool
    {
        $snapshot = json_decode((string) $product->external_details, true);
        return is_array($snapshot) && $this->extractStoredDetails($snapshot) !== [];
    }

    private function extractStoredDetails(array $snapshot): array
    {
        if (array_key_exists('details', $snapshot)) {
            return is_array($snapshot['details']) ? $snapshot['details'] : [];
        }

        // Earlier imports saved the details object directly instead of a snapshot wrapper.
        return $snapshot;
    }

    private function attachCategories(Product $product, array $categoryIds): void
    {
        if ($categoryIds) {
            $product->categories()->syncWithoutDetaching(array_values($categoryIds));
        }
    }
}
