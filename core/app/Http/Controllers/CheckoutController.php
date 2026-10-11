<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Lib\CartManager;
use App\Models\Guest;
use App\Models\Order;
use App\Models\ShippingAddress;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Crypt;

class CheckoutController extends Controller {
    private $cartManager;

    public function __construct(CartManager $cartManager) {
        parent::__construct();
        $this->cartManager = $cartManager;
    }

    public function addressSuggestions(Request $request) {
        if (!(bool) gs('geoapify_address_enabled')) {
            return response()->json(['message' => 'Address lookup is disabled in storefront settings.'], 503);
        }
        $validated = $request->validate(['text' => 'required|string|min:3|max:180']);
        $apiKey = $this->geoapifyApiKey();
        if (!$apiKey) {
            return response()->json(['message' => 'Address suggestions are not configured.'], 503);
        }

        $params = ['text' => $validated['text'], 'limit' => 6, 'apiKey' => $apiKey];
        $countryCodes = strtoupper(preg_replace('/[^a-z,]/i', '', (string) gs('geoapify_country_codes')));
        if ($countryCodes !== '') {
            $params['filter'] = 'countrycode:' . strtolower($countryCodes);
        }

        try {
            $response = Http::timeout(8)->get('https://api.geoapify.com/v1/geocode/autocomplete', $params)->throw();
        } catch (\Throwable $exception) {
            return response()->json(['message' => 'Address suggestions are temporarily unavailable.'], 502);
        }

        $suggestions = collect($response->json('features', []))->map(function ($feature) {
            $place = $feature['properties'] ?? [];
            return [
                'label' => $place['formatted'] ?? '',
                'address' => trim(implode(' ', array_filter([$place['housenumber'] ?? null, $place['street'] ?? null]))) ?: ($place['address_line1'] ?? $place['formatted'] ?? ''),
                'city' => $place['city'] ?? $place['town'] ?? $place['village'] ?? '',
                'state' => $place['state'] ?? '',
                'zip' => $place['postcode'] ?? '',
                'country' => $place['country'] ?? '',
            ];
        })->filter(fn ($place) => $place['label'] !== '')->values();

        return response()->json(['suggestions' => $suggestions]);
    }

    public function addressFromCoordinates(Request $request) {
        if (!(bool) gs('geoapify_address_enabled')) {
            return response()->json(['message' => 'Address lookup is disabled in storefront settings.'], 503);
        }
        $coordinates = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180',
        ]);
        $apiKey = $this->geoapifyApiKey();
        if (!$apiKey) {
            return response()->json(['message' => 'Address lookup is not configured.'], 503);
        }

        $params = [
            'lat' => $coordinates['lat'],
            'lon' => $coordinates['lon'],
            'limit' => 1,
            'apiKey' => $apiKey,
        ];
        $countryCodes = strtolower(preg_replace('/[^a-z,]/i', '', (string) gs('geoapify_country_codes')));
        if ($countryCodes !== '') {
            $params['filter'] = 'countrycode:' . $countryCodes;
        }

        try {
            $response = Http::timeout(8)->get('https://api.geoapify.com/v1/geocode/reverse', $params)->throw();
        } catch (\Throwable $exception) {
            return response()->json(['message' => 'Could not look up this location right now.'], 502);
        }

        $place = data_get($response->json(), 'features.0.properties');
        if (!is_array($place)) {
            return response()->json(['message' => 'No address was found for this location.'], 404);
        }

        return response()->json([
            'place' => [
                'label' => $place['formatted'] ?? '',
                'address' => trim(implode(' ', array_filter([$place['housenumber'] ?? null, $place['street'] ?? null]))) ?: ($place['address_line1'] ?? $place['formatted'] ?? ''),
                'city' => $place['city'] ?? $place['town'] ?? $place['village'] ?? '',
                'state' => $place['state'] ?? '',
                'zip' => $place['postcode'] ?? '',
                'country' => $place['country'] ?? '',
            ],
        ]);
    }

    private function geoapifyApiKey(): ?string
    {
        $environmentKey = config('services.geoapify.key');
        if (is_string($environmentKey) && trim($environmentKey) !== '') {
            return trim($environmentKey);
        }

        $encryptedDatabaseKey = gs('geoapify_api_key');
        if (!is_string($encryptedDatabaseKey) || trim($encryptedDatabaseKey) === '') {
            return null;
        }

        try {
            $databaseKey = Crypt::decryptString($encryptedDatabaseKey);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $exception) {
            return null;
        }

        return trim($databaseKey) !== '' ? trim($databaseKey) : null;
    }

    public function storeGuestUser(Request $request) {
        $countryData  = (array)json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryCodes = implode(',', array_keys($countryData));
        $mobileCodes  = implode(',', array_column($countryData, 'dial_code'));
        $countries    = implode(',', array_column($countryData, 'country'));

        $request->validate([
            'email'        => 'required|email',
            'mobile'       => 'required|regex:/^([0-9]*)$/',
            'country_code' => 'required|in:' . $countryCodes,
            'country'      => 'required|in:' . $countries,
            'mobile_code'  => 'required|in:' . $mobileCodes,
        ]);

        $guest = Guest::where('email', $request->email)->where('mobile', $request->mobile)->where('dial_code', $request->mobile_code)->firstOrNew();
        $guest->email        = $request->email;
        $guest->dial_code    = $request->mobile_code;
        $guest->country_name = $request->country;
        $guest->country_code = $request->country_code;
        $guest->mobile       = $request->mobile;
        $guest->session_id   = getSessionId();
        $guest->save();

        session()->put('guest_user_data', $guest);
        return redirect()->route('checkout.shipping.info');
    }

    public function storeGuestShippingInfo(Request $request) {

        $request->validate([
            'firstname' => 'required|string',
            'lastname'  => 'required|string',
            'mobile'    => 'required|string',
            'email'     => 'required|email',
            'city'      => 'required|string',
            'state'     => 'required|string',
            'zip'       => 'required|string',
            'country'   => 'required|string',
            'address'   => 'required|string',
        ]);

        $shippingData = [
            'firstname'    => $request->firstname,
            'lastname'     => $request->lastname,
            'mobile'       => $request->mobile,
            'email'        => $request->email,
            'city'         => $request->city,
            'state'        => $request->state,
            'zip'          => $request->zip,
            'country_code' => $request->country_code,
            'dial_code'    => $request->mobile_code,
            'country'      => $request->country,
            'address'      => $request->address,
        ];

        Session::put('shipping_info', $shippingData);
        return redirect()->route('checkout.delivery.methods');
    }

    //============= checkout step start here ===================//
    public function shippingInfo() {
        $pageTitle = 'Shipping Information';

        $shippingAddresses = ShippingAddress::where('user_id', auth()->id())->get();
        $countries         = getCountries();


        if (auth()->user()) {
            $view = 'Template::checkout_steps.shipping_info';
        } else {
            if (!gs('guest_checkout')) {
                abort(404);
            }
            $session = session()->get('guest_user_data');
            if (!$session) {
                $notify[] = ['error', 'Session Expired'];
                return to_route('cart.page')->withNotify($notify);
            }

            $view = 'Template::checkout_steps.shipping_info_guest';
        }

        $addressAutocompleteEnabled = (bool) gs('geoapify_address_enabled');
        return view($view, compact('pageTitle', 'shippingAddresses', 'countries', 'addressAutocompleteEnabled'));
    }

    public function addShippingInfo(Request $request) {
        $ids = ShippingAddress::where('user_id', auth()->id())->pluck('id')->toArray();

        $request->validate([
            'shipping_address_id' => 'required|in:' . implode(',', $ids)
        ], [
            'shipping_address_id.required' => 'Shipping address is required',
            'shipping_address_id.in' => 'Invalid address selected'
        ]);

        $checkoutData = session('shipping_info');
        $checkoutData['shipping_address_id'] = $request->shipping_address_id;

        session()->put('shipping_info', $checkoutData);
        return to_route('checkout.delivery.methods');
    }

    public function deliveryMethods() {
        $pageTitle = 'Delivery Methods';
        $shippingMethods = ShippingMethod::active()->get();
        return view('Template::checkout_steps.shipping_methods', compact('pageTitle', 'shippingMethods'));
    }

    public function addDeliveryMethod(Request $request) {
        $ids = ShippingMethod::active()->pluck('id')->toArray();

        $request->validate([
            'shipping_method_id' => 'required|in:' . implode(',', $ids)
        ], [
            'shipping_method_id.required' => 'Delivery type field is required',
            'shipping_method_id.in'       => 'Invalid delivery type selected'
        ]);

        $checkoutData = session('shipping_info');
        $checkoutData['shipping_method_id'] = $request->shipping_method_id;

        session()->put('shipping_info', $checkoutData);
        return to_route('checkout.payment.methods');
    }

    public function confirmation($orderNumber) {
        $order  = Order::where('order_number', $orderNumber)->with('deposit', 'orderDetail.product',  'orderDetail.productVariant', 'appliedCoupon')->first();

        $pageTitle = 'Order Number -' . $order->order_number;

        return view('Template::checkout_steps.confirmation', compact('pageTitle', 'order'));
    }

    private function appliedCoupon($cartData, $subtotal) {
        $coupon = session('coupon');

        if (!$coupon) {
            return null;
        }

        // Match the coupon code with database and check is exists
        $coupon  = $this->cartManager->getCouponByCode($coupon['code']);

        if (!$coupon) {
            return ['error' => "Applied coupon is invalid or expired"];
        }

        $checkCoupon = $this->cartManager->isValidCoupon($coupon, $subtotal, $cartData);

        if (isset($checkCoupon['error'])) {
            return $checkCoupon;
        }

        $coupon->discount_amount = $coupon->discountAmount($subtotal);
        return $coupon;
    }
}
