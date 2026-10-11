<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompareController extends Controller {

    public function __construct() {
        parent::__construct();

        if (!gs('product_compare')) {
            abort(404);
        }
    }

    public function addToCompare(Request $request) {

        $product   = Product::published()->find($request->product_id);
        if (!$product) {
            return response()->json(['error' => 'Product not found']);
        }

        $compare = session('compare', []);

        if (isset($compare[$product->id])) {
            unset($compare[$product->id]);
            session()->put('compare', $compare);
            return response()->json(['success' => 'Removed from the comparison list', 'type' => 'REMOVED']);
        }

        $productIds      = array_keys(session('compare') ?? []);
        $compareProducts = Product::published()->whereIn('id', $productIds)->get();

        if ($compareProducts->isNotEmpty()) {
            $firstProduct = $compareProducts->first(fn($candidate) => (bool) $candidate->product_type_id);
            if (
                $product->product_type_id
                && $firstProduct?->product_type_id
                && $product->product_type_id != $firstProduct?->product_type_id
            ) {
                return response()->json(['error' => 'The product type doesn\'t match', 'type' => 'ADDED']);
            }
        }

        $compare[$product->id] = ['id' => $product->id];
        session()->put('compare', $compare);

        return response()->json(['success' => 'Added to the comparison list', 'type' => 'ADDED']);
    }

    public function compare() {
        $pageTitle       = 'Product Comparison';
        $productIds      = array_keys(session('compare') ?? []);
        $compareProducts = Product::published()->with('categories', 'activeOffer', 'brand')->ratingReviewCount()->whereIn('id', $productIds)->get();
        $specificationTemplate = ProductType::find(@$compareProducts->first()?->product_type_id);
        $comparisonRows = [];

        foreach ($compareProducts as $product) {
            $facts = $product->specification;
            if (is_string($facts)) {
                $facts = json_decode($facts, true);
            } elseif (is_object($facts)) {
                $facts = (array) $facts;
            }
            if (!is_array($facts)) {
                $facts = [];
            }

            foreach ($facts as $key => $value) {
                if (is_array($value) && isset($value['key'])) {
                    $label = trim((string) $value['key']);
                    $value = $value['value'] ?? null;
                } else {
                    $label = is_string($key) ? Str::headline($key) : '';
                }
                if ($label === '' || !is_scalar($value) || trim((string) $value) === '') {
                    continue;
                }
                $rowKey = Str::lower($label);
                $comparisonRows[$rowKey]['label'] = $label;
                $comparisonRows[$rowKey]['values'][$product->id] = trim((string) $value);
            }
        }

        foreach ($compareProducts as $product) {
            $comparisonRows['brand']['label'] = __('Brand');
            $comparisonRows['brand']['values'][$product->id] = $product->brand?->name ?: '—';
            $comparisonRows['categories']['label'] = __('Categories');
            $comparisonRows['categories']['values'][$product->id] = $product->categories->pluck('name')->implode(', ') ?: '—';
            $comparisonRows['availability']['label'] = __('Availability');
            $comparisonRows['availability']['values'][$product->id] = $product->in_stock ? __('In stock') : __('Out of stock');
        }
        $comparisonRows = collect($comparisonRows)->values();

        return view('Template::compare', compact('pageTitle', 'compareProducts', 'specificationTemplate', 'comparisonRows'));
    }

    public function compareProductsCount() {
        if (!session('compare')) {
            return response(['total' => 0]);
        }

        $productIds   = array_keys(session('compare', []));
        $productCount = Product::published()->whereIn('id', $productIds)->count();
        return response(['total' => $productCount]);
    }

    public function removeFromCompare($id = null) {

        $compare = session('compare');

        if (!$id) {
            session()->forget('compare');
            $notify[] = ['success', 'Product comparison cleared successfully'];
            return back()->withNotify($notify);
        }

        if (isset($compare[$id])) {
            unset($compare[$id]);
            session()->put('compare', $compare);

            $notify[] = ['success', 'Product removed successfully'];
            return back()->withNotify($notify);
        }

        $notify[] = ['error', 'Something went wrong'];
        return back()->withNotify($notify);
    }
}
