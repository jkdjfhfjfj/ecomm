<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Category;
use App\Models\Coupon;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class OfferController extends Controller {
    public function index() {
        $pageTitle = "All Offers";
        $offers    = Offer::withCount('products as total_products')->searchable(['name'])->latest()->paginate(getPaginate());
        $missingHomepageSchemaColumns = collect([
            'offers.campaign_type' => Schema::hasColumn('offers', 'campaign_type'),
            'products.external_image_url' => Schema::hasColumn('products', 'external_image_url'),
            'products.external_details' => Schema::hasColumn('products', 'external_details'),
        ])->filter(fn ($exists) => !$exists)->keys()->values();
        $dailyCampaignSchemaReady = !$missingHomepageSchemaColumns->contains('offers.campaign_type');
        $dailyOfferDiagnostics = collect();

        if ($dailyCampaignSchemaReady && $missingHomepageSchemaColumns->isEmpty()) {
            $dailyOfferDiagnostics = Offer::query()
                ->where('campaign_type', 'daily')
                ->withCount([
                    'products as displayable_products_count' => fn ($products) => $products
                        ->published()
                        ->withDisplayableImage(),
                ])
                ->latest('id')
                ->limit(5)
                ->get()
                ->each(function ($offer) {
                    $offer->is_running_now = (bool) $offer->status
                        && $offer->starts_from
                        && $offer->ends_at
                        && $offer->starts_from <= now()
                        && $offer->ends_at >= now();
                    $offer->popup_ready = $offer->is_running_now
                        && $offer->displayable_products_count > 0;
                });
        }

        return view('admin.offers.index', compact(
            'pageTitle',
            'offers',
            'dailyCampaignSchemaReady',
            'missingHomepageSchemaColumns',
            'dailyOfferDiagnostics'
        ));
    }

    public function create() {
        $pageTitle = "Create New Offer";
        $availableCoupons = Coupon::activeAndValid()->orderBy('coupon_code')->get(['coupon_code', 'coupon_name']);
        return view('admin.offers.create', compact('pageTitle', 'availableCoupons'));
    }

    public function save(Request $request, $id) {
        $request->validate([
            "offer_name"     => 'required|string|max:40',
            "campaign_type"  => 'required|in:standard,daily,flash,clearance',
            "discount_type"  => 'required|in:1,2',
            "amount"         => 'required|numeric',
            "starts_from"    => 'required|date|date_format:Y-m-d h:i A',
            "ends_at"        => 'required|date|date_format:Y-m-d h:i A|after:starts_from',
            "products"       => 'nullable|array|min:1',
            "products.*"     => 'required_with:products|exists:products,id',
            'banner'         => ['nullable', 'image', new FileTypeValidate(['jpeg', 'jpg', 'png'])],
            'banner_title'   => 'nullable|string|max:120',
            'banner_message' => 'nullable|string|max:1000',
            'banner_button_text' => 'nullable|string|max:40',
            'coupon_code'    => 'nullable|string|max:40|exists:coupons,coupon_code',
            'show_banner'    => 'nullable|in:1',
            'show_countdown' => 'nullable|in:1',
        ]);


        if ($request->discount_type == Status::DISCOUNT_PERCENT && $request->amount > 100) {
            $notify[] = ['error', 'Offer amount percentage can\'t be greater than 100%'];
            return back()->withNotify($notify);
        }

        $productsToRemove = [];
        $previousProducts = [];
        $newProducts = $request->products ?? [];

        if ($id == 0) {
            $offer    = new Offer();
            $notify[] = ['success', 'Offer created successfully'];
            $productsToAdd = $newProducts;
        } else {
            $offer    = Offer::findOrFail($id);
            $notify[] = ['success', 'Offer updated successfully'];

            $previousProducts = $offer->products->pluck('id')->toArray();

            // Find products to remove from offer
            $productsToRemove = array_diff($previousProducts, $newProducts);

            // Find products to add to offer
            $productsToAdd = array_diff($newProducts, $previousProducts);
        }

        if ($request->hasFile('banner')) {
            $oldImage = $offer->banner;
            $offer->banner = fileUploader($request->banner, getFilePath('offerBanner'), getFileSize('offerBanner'), $oldImage);
        }

        $offer->name           = $request->offer_name;
        $offer->campaign_type  = $request->campaign_type;
        $offer->banner_title  = $request->banner_title;
        $offer->banner_message = $request->banner_message;
        $offer->banner_button_text = $request->banner_button_text;
        $offer->coupon_code   = $request->coupon_code;
        $offer->discount_type  = $request->discount_type;
        $offer->amount         = $request->amount;
        $offer->starts_from    = $request->starts_from;
        $offer->ends_at        = $request->ends_at;
        $offer->show_banner    = $request->show_banner ? 1 : 0;
        $offer->show_countdown = in_array($request->campaign_type, ['daily', 'flash'], true) || $request->show_countdown ? 1 : 0;
        $offer->save();

        if (!$id) {
            $offer->unique_key = 'offer_' . $offer->id;
            $offer->save();
        }

        Product::whereIn('id', $productsToRemove)->update(['offer_id' => 0]);
        Product::whereIn('id', $productsToAdd)->update(['offer_id' => $offer->id]);

        $dailyOffersCategory = Category::where('external_source', 'local_promotions')
            ->where('external_id', 'daily-offers')
            ->first();
        if ($dailyOffersCategory) {
            $affectedProductIds = array_values(array_unique(array_merge($previousProducts, $newProducts)));
            $currentlyDailyIds = Product::whereIn('id', $affectedProductIds)
                ->whereHas('offer', fn ($query) => $query->where('campaign_type', 'daily'))
                ->pluck('id')
                ->all();
            $dailyOffersCategory->products()->syncWithoutDetaching($currentlyDailyIds);
            $dailyOffersCategory->products()->detach(array_diff($previousProducts, $currentlyDailyIds));
        }

        return back()->withNotify($notify);
    }

    public function edit($id) {
        $pageTitle = "Edit Offer";
        $offer     = Offer::with(['products'])->findOrFail($id);
        $availableCoupons = Coupon::activeAndValid()->orderBy('coupon_code')->get(['coupon_code', 'coupon_name']);
        return view('admin.offers.create', compact('pageTitle', 'offer', 'availableCoupons'));
    }

    public function productsForOffer(Request $request) {

        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $products = Product::searchable(['name'])
            ->with([
                'offer',
                'displayImage',
            ])
            ->where(function ($query) {
                $query->where('regular_price', '>', 0)
                    ->orWhereHas('productVariants', function ($q) {
                        return $q->where('regular_price', '>', 0);
                    });
            })
            ->orderBy('id', 'desc')
            ->paginate(30)
            ->withQueryString();


        return response()->json($products);
    }

    public function changeStatus(Request $request) {
        $offer         = Offer::findOrFail($request->id);
        $offer->status = !$offer->status;
        $offer->save();
        return successResponse($offer->status ? 'Offer activated successfully' : 'Offer deactivated successfully');
    }
}
