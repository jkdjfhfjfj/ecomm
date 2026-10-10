<?php

namespace App\Console\Commands;

use App\Constants\Status;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeedDemoProductReviews extends Command
{
    protected $signature = 'reviews:seed-samples {--ratio=90 : Percentage of imported products to receive sample reviews}';

    protected $description = 'Seed clearly labeled positive sample reviews for the local test storefront.';

    private const REVIEWS = [
        'Sample content: The design looks great, and I am happy with this choice.',
        'Sample content: A polished style and a pleasant shopping experience.',
        'Sample content: I like the look and would browse this collection again.',
        'Sample content: Great presentation and a style that stands out.',
        'Sample content: Very pleased with the overall design.',
        'Sample content: A nice find with an elegant look.',
        'Sample content: A stylish pick that was easy to compare with other options.',
        'Sample content: The overall look is appealing and makes a nice gift idea.',
    ];

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('Sample reviews are disabled in production.');
            return self::FAILURE;
        }

        if (!Schema::hasColumn('product_reviews', 'is_sample') || !Schema::hasColumn('product_reviews', 'reviewer_name')) {
            $this->error('Run the database migrations before seeding sample reviews.');
            return self::FAILURE;
        }

        $ratio = max(0, min(100, (int) $this->option('ratio')));
        $products = Product::query()
            ->where('external_source', 'parsebot_jomashop')
            ->where('is_published', Status::YES)
            ->orderBy('id')
            ->get(['id']);
        $rows = [];
        $created = 0;
        $now = now();
        $alreadySeeded = ProductReview::withTrashed()
            ->where('is_sample', true)
            ->pluck('product_id')
            ->flip();
        $flush = static function () use (&$rows, &$created): void {
            if ($rows === []) {
                return;
            }
            DB::table('product_reviews')->insert($rows);
            $created += count($rows);
            $rows = [];
        };

        DB::transaction(function () use ($products, $ratio, &$rows, &$created, $now, $flush, $alreadySeeded): void {
            foreach ($products as $product) {
                $selection = (int) sprintf('%u', crc32('select:' . $product->id));
                if (($selection % 100) >= $ratio || $alreadySeeded->has($product->id)) {
                    continue;
                }

                $countSeed = (int) sprintf('%u', crc32('count:' . $product->id));
                $reviewsForProduct = 5 + ($countSeed % 16);
                for ($index = 0; $index < $reviewsForProduct; $index++) {
                    $seed = (int) sprintf('%u', crc32($product->id . ':' . $index));
                    $rows[] = [
                        'user_id' => 0,
                        'reviewer_name' => 'Anonymous reviewer',
                        'product_id' => $product->id,
                        'review' => self::REVIEWS[$seed % count(self::REVIEWS)],
                        'rating' => 4 + ($seed % 2),
                        'status' => Status::REVIEW_APPROVED,
                        'is_viewed' => Status::YES,
                        'is_sample' => true,
                        'created_at' => $now->copy()->subDays($seed % 90),
                        'updated_at' => $now,
                    ];
                    if (count($rows) >= 500) {
                        $flush();
                    }
                }
            }
            $flush();
        });

        $this->info("Created {$created} positive sample reviews across {$ratio}% of eligible imported products (5–20 per product).");
        return self::SUCCESS;
    }
}
