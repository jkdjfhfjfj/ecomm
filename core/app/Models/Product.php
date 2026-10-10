<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\ProductPrice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\FileExport;

class Product extends Model {
    use SoftDeletes, ProductPrice, FileExport;

    protected $appends = ['price_range'];

    protected $casts = [
        'sale_price'         => 'double',
        'regular_price'      => 'double',
        'sale_starts_from'   => 'datetime',
        'sale_ends_at'       => 'datetime',
        'reviews_avg_rating' => 'double',
        'extra_descriptions' => 'array',
        'specification'      => 'object',
        'meta_keywords'      => 'array',
    ];

    public function digitalFile() {
        return $this->morphOne(DigitalFile::class, 'fileable');
    }

    public function categories() {
        return $this->belongsToMany(Category::class);
    }

    public function offer() {
        return $this->belongsTo(Offer::class);
    }

    public function displayImage() {
        return $this->belongsTo(Media::class,  'main_image_id');
    }

    public function activeOffer() {
        return $this->offer()->running();
    }

    public function coupons() {
        return $this->belongsToMany(Coupon::class, 'coupons_products', 'product_id', 'coupon_id');
    }

    public function orderDetails() {
        return $this->hasMany(OrderDetail::class);
    }

    public function brand() {
        return $this->belongsTo(Brand::class);
    }

    public function attributes() {
        return $this->belongsToMany(Attribute::class);
    }

    public function attributeValues() {
        return $this->belongsToMany(AttributeValue::class)->withPivot('media_id');
    }

    public function carts() {
        return $this->hasMany(Cart::class, 'product_id');
    }

    public function orders() {
        return $this->belongsToMany(Order::class, OrderDetail::class);
    }

    public function reviews() {
        return $this->hasMany(ProductReview::class, 'product_id');
    }

    public function userReview() {
        return $this->hasOne(ProductReview::class, 'product_id')->where('user_id', auth()->id());
    }

    public function productVariants() {
        return $this->hasMany(ProductVariant::class);
    }

    public function galleryImages() {
        return $this->belongsToMany(Media::class);
    }

    public function productType() {
        return $this->belongsTo(ProductType::class);
    }

    /*======= Scopes =======*/
    public function scopeUnpublished($query) {
        return $query->where('is_published', Status::NO);
    }

    public function scopePublished($query) {
        return $query->where('is_published', Status::YES);
    }

    public function scopePhysical($query) {
        return $query->where('is_downloadable', Status::NO);
    }

    public function scopeDigital($query) {
        return $query->where('is_downloadable', Status::YES);
    }

    public function scopeShopPageProducts($query) {
        return $query->where('show_in_products_page', Status::YES);
    }

    public function scopeLowStock($query) {
        return $query->where(function ($query) {
            $query->where('track_inventory', Status::YES)
                ->whereColumn('in_stock', '<=', 'alert_quantity');
        })->orWhere(function ($query) {
            $query->whereHas('productVariants', function ($q) {
                $q->where('track_inventory', Status::YES)
                    ->whereColumn('in_stock', '<=', 'alert_quantity');
            });
        });
    }

    public function scopeOutOfStock($query) {
        return $query->where(function ($query) {
            $query->where('track_inventory', Status::YES)
                ->where(function ($q) {
                    $q->where('in_stock', '=', 0)
                        ->orWhereNull('in_stock');
                });
        })->orWhere(function ($query) {
            $query->whereHas('productVariants', function ($q) {
                $q->where('track_inventory', Status::YES)
                    ->where(function ($subQuery) {
                        $subQuery->where('in_stock', '=', 0)
                            ->orWhereNull('in_stock');
                    });
            });
        });
    }

    public function scopeRatingReviewCount($query) {
        $query->withCount(['reviews' => function ($review) {
            $review->where('product_reviews.status', Status::REVIEW_APPROVED);
        }])
            ->withAvg(['reviews' => function ($review1) {
                $review1->where('product_reviews.status', Status::REVIEW_APPROVED);
            }], 'rating');
    }

    /*======= Helper Methods =======*/

    public function link() {
        return route('product.detail', $this->slug);
    }

    public function mainImage($thumb = true) {
        $thumb = $thumb ? '/thumb_' : '/';
        $image = $this->displayImage;
        if (!$image) {
            if ($this->external_image_url && in_array(parse_url($this->external_image_url, PHP_URL_SCHEME), ['https', 'http'], true)) {
                return $this->external_image_url;
            }
            $externalGallery = $this->externalGalleryUrls();
            if ($externalGallery) {
                return $externalGallery[0];
            }
            return getImage(null);
        }
        return getImage($image->path . $thumb . $image->file_name);
    }

    public function hasDisplayableImage(): bool
    {
        $localImage = $this->displayImage;
        if ($localImage && $localImage->file_name && $localImage->path) {
            return true;
        }

        foreach ($this->externalGalleryUrls() as $url) {
            if (is_string($url) && in_array(parse_url($url, PHP_URL_SCHEME), ['https', 'http'], true)) {
                return true;
            }
        }

        return false;
    }

    public function externalGalleryUrls(): array
    {
        $snapshot = json_decode((string) $this->external_details, true);
        if (!is_array($snapshot)) {
            return $this->external_image_url ? [$this->external_image_url] : [];
        }

        $listing = is_array($snapshot['listing'] ?? null) ? $snapshot['listing'] : $snapshot;
        $details = is_array($snapshot['details'] ?? null) ? $snapshot['details'] : [];
        $urls = [];

        foreach ([$this->external_image_url, data_get($details, 'image.url_nocache'), data_get($details, 'image.url'), data_get($listing, 'image.url_nocache'), data_get($listing, 'image.url'), $details['image_url'] ?? null, $listing['image_url'] ?? null] as $url) {
            if (is_string($url) && in_array(parse_url($url, PHP_URL_SCHEME), ['https', 'http'], true)) {
                $urls[] = $url;
            }
        }

        foreach ([$details['media_gallery'] ?? [], $listing['media_gallery'] ?? []] as $gallery) {
            foreach (is_array($gallery) ? $gallery : [] as $image) {
                if (!is_array($image)) {
                    continue;
                }
                foreach (is_array($image['sizes'] ?? null) ? $image['sizes'] : [] as $size) {
                    $url = is_array($size) ? ($size['url'] ?? null) : null;
                    if (is_string($url) && in_array(parse_url($url, PHP_URL_SCHEME), ['https', 'http'], true)) {
                        $urls[] = $url;
                    }
                }
                foreach (['url_nocache', 'url', 'image_url'] as $key) {
                    $url = $image[$key] ?? null;
                    if (is_string($url) && in_array(parse_url($url, PHP_URL_SCHEME), ['https', 'http'], true)) {
                        $urls[] = $url;
                    }
                }
            }
        }

        $collectVariantGalleries = function ($node) use (&$collectVariantGalleries, &$urls) {
            if (!is_array($node)) {
                return;
            }
            if (is_array($node['media_gallery'] ?? null)) {
                foreach ($node['media_gallery'] as $image) {
                    if (!is_array($image)) {
                        continue;
                    }
                    foreach (is_array($image['sizes'] ?? null) ? $image['sizes'] : [] as $size) {
                        $url = is_array($size) ? ($size['url'] ?? null) : null;
                        if (is_string($url) && in_array(parse_url($url, PHP_URL_SCHEME), ['https', 'http'], true)) {
                            $urls[] = $url;
                        }
                    }
                    foreach (['url_nocache', 'url', 'image_url'] as $key) {
                        $url = $image[$key] ?? null;
                        if (is_string($url) && in_array(parse_url($url, PHP_URL_SCHEME), ['https', 'http'], true)) {
                            $urls[] = $url;
                        }
                    }
                }
            }
            foreach ($node as $value) {
                $collectVariantGalleries($value);
            }
        };
        $collectVariantGalleries($details);

        return array_values(array_unique($urls));
    }

    public function externalVideoUrls(): array
    {
        $snapshot = json_decode((string) $this->external_details, true);
        if (!is_array($snapshot)) {
            return [];
        }

        $urls = [];
        $walk = function ($node, string $key = '') use (&$walk, &$urls) {
            if (is_string($node) && str_contains(strtolower($key), 'video')) {
                if (in_array(parse_url($node, PHP_URL_SCHEME), ['https', 'http'], true)) {
                    $urls[] = $node;
                }
                return;
            }
            if (!is_array($node)) {
                return;
            }
            foreach ($node as $childKey => $value) {
                $walk($value, (string) $childKey);
            }
        };
        $walk($snapshot);

        return array_values(array_unique($urls));
    }

    function typeInText() {
        return $this->is_downloadable ? 'digital' : 'physical';
    }

    public function inStock($variant = null) {
        return $variant ? $variant->inStock($this) : $this->in_stock;
    }

    function detailedStock($limit = null) {
        return productManager()->getDetailedStock($this, $limit);
    }

    function totalInStock() {
        return productManager()->getStockData($this);
    }

    public function trackInventory($variant = null) {
        return $variant ? $variant->trackInventory($this) : $this->track_inventory;
    }

    public function showCategories($limit = null) {
        if (!blank($this->categories)) {
            $productCategories = $this->categories->take($limit)->pluck('name')->toArray();
            return implode(', ', $productCategories);
        }
        return trans('N/A');
    }

    public function editUrl() {
        return route("admin.products.edit", $this->id);
    }

    public static function topSales($limit = 6, $paginate = false) {
        $products = self::leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
            ->leftJoin('orders', 'order_details.order_id', '=', 'orders.id')
            ->selectRaw('products.*, COALESCE(sum(order_details.quantity),0) total')
            ->where('orders.status', '=', Status::ORDER_DELIVERED)
            ->groupBy('products.id')
            ->withCount(['reviews' => function ($review) {
                $review->where('status', Status::REVIEW_APPROVED);
            }])
            ->withAvg(['reviews' => function ($review1) {
                $review1->where('status', Status::REVIEW_APPROVED);
            }], 'rating')
            ->orderBy('total', 'desc');

        if ($paginate) {
            return $products->searchable(['name'])->filter(['brand_id'])->paginate(getPaginate());
        }

        return $products->limit($limit)->get();
    }


    public function getSeoImageAttribute() {
        return $this->mainImage(false);
    }

    public function getSeoImageSizeAttribute() {
        return getFileSize('product');
    }
}
