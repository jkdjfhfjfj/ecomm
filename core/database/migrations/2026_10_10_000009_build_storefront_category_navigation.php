<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SOURCE = 'local_navigation';

    public function up(): void
    {
        $now = now();
        $all = $this->group('All Categories', 'all-categories', 'all-categories', null, 0, $now);
        $accessories = $this->group('Accessories', 'nav-accessories', 'accessories', $all, 0, $now);
        $fashion = $this->group('Fashion', 'nav-fashion', 'fashion', $all, 1, $now);
        $beauty = $this->group('Beauty & Fragrance', 'nav-beauty-fragrance', 'beauty-fragrance', $all, 2, $now);

        $this->placeSourceCategory('871', $accessories, 0, $now); // Watches
        $this->placeSourceCategory('31', $accessories, 1, $now); // Jewelry
        $this->placeSourceCategory('46', $accessories, 2, $now); // Handbags
        $this->placeSourceCategory('50', $accessories, 3, $now); // Sunglasses
        $this->placeSourceCategory('3789', $accessories, 4, $now); // Eyeglasses

        $this->placeSourceCategory('63', $fashion, 0, $now); // Clothing
        $this->placeSourceCategory('5677', $fashion, 1, $now); // Shoes
        $this->placeSourceCategory('4621', $fashion, 2, $now); // Pre-owned

        $this->placeSourceCategory('3432', $beauty, 0, $now); // Beauty
        $this->placeSourceCategory('5869', $beauty, 1, $now); // Fragrances
    }

    public function down(): void
    {
        DB::table('categories')
            ->where('external_source', 'parsebot_jomashop')
            ->whereIn('external_id', ['871', '31', '46', '50', '3789', '63', '5677', '4621', '3432', '5869'])
            ->update(['parent_id' => null, 'updated_at' => now()]);

        DB::table('categories')
            ->where('external_source', self::SOURCE)
            ->whereIn('external_id', ['all-categories', 'nav-accessories', 'nav-fashion', 'nav-beauty-fragrance'])
            ->delete();
    }

    private function group(string $name, string $externalId, string $slug, ?int $parentId, int $position, $now): int
    {
        $existing = DB::table('categories')
            ->where('external_source', self::SOURCE)
            ->where('external_id', $externalId)
            ->first();

        $data = [
            'name' => $name,
            'slug' => $slug,
            'parent_id' => $parentId,
            'position' => $position,
            'external_source' => self::SOURCE,
            'external_id' => $externalId,
            'is_featured' => 0,
            'updated_at' => $now,
        ];

        if ($existing) {
            DB::table('categories')->where('id', $existing->id)->update($data);
            return (int) $existing->id;
        }

        return (int) DB::table('categories')->insertGetId($data + ['created_at' => $now]);
    }

    private function placeSourceCategory(string $externalId, int $parentId, int $position, $now): void
    {
        DB::table('categories')
            ->where('external_source', 'parsebot_jomashop')
            ->where('external_id', $externalId)
            ->whereNull('deleted_at')
            ->update(['parent_id' => $parentId, 'position' => $position, 'updated_at' => $now]);
    }
};
