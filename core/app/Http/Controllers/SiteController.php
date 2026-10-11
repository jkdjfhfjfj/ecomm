<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Models\AdminNotification;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Frontend;
use App\Models\Language;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Page;
use App\Models\Product;
use App\Models\Subscriber;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;

class SiteController extends Controller {
    public function index() {
        $pageTitle   = 'Home';
        $sections    = Page::where('tempname', activeTemplate())->where('slug', '/')->first();
        $seoContents = $sections->seo_content;
        $seoImage    = @$seoContents->image ? getImage(getFilePath('seo') . '/' . @$seoContents->image, getFileSize('seo')) : null;
        $dailyOffer = Offer::running()
            ->where('campaign_type', 'daily')
            ->whereHas('products', fn ($products) => $products->published()->withDisplayableImage())
            ->with(['products' => fn ($products) => $products->published()->withDisplayableImage()->with(['displayImage', 'brand', 'productVariants'])])
            ->latest('id')
            ->first();
        $homepageOfferBanners = Offer::running()
            ->where('show_banner', 1)
            ->where('campaign_type', '!=', 'daily')
            ->whereHas('products', fn ($products) => $products->published()->withDisplayableImage())
            ->orderByRaw("campaign_type = 'flash' DESC")
            ->latest('starts_from')
            ->limit(4)
            ->get();
        $homeProducts = Product::published()
            ->shopPageProducts()
            ->withDisplayableImage()
            ->whereHas('categories')
            ->with(['displayImage', 'brand', 'productVariants'])
            ->latest('id')
            ->limit(36)
            ->get()
            ->filter(fn (Product $product) => $product->hasDisplayableImage())
            ->take(12)
            ->values();
        $homeCategoryRows = Category::query()
            ->where('external_source', 'parsebot_jomashop')
            ->whereNotIn('external_id', ['5695'])
            ->orderBy('position')
            ->limit(8)
            ->get()
            ->map(function (Category $category) {
                $products = Product::published()
                    ->shopPageProducts()
                    ->withDisplayableImage()
                    ->whereHas('categories', fn ($query) => $query->where('categories.id', $category->id))
                    ->with(['displayImage', 'brand', 'productVariants'])
                    ->latest('id')
                    ->limit(24)
                    ->get()
                    ->filter(fn (Product $product) => $product->hasDisplayableImage())
                    ->take(12)
                    ->values();

                return ['category' => $category, 'products' => $products];
            })
            ->filter(fn (array $row) => $row['products']->isNotEmpty())
            ->values();
        return view('Template::home', compact('pageTitle', 'sections', 'seoContents', 'seoImage', 'homeProducts', 'homeCategoryRows', 'dailyOffer', 'homepageOfferBanners'));
    }

    public function about() {
        $pageTitle = 'About Us';
        $sections = Page::where('tempname', activeTemplate())->where('slug', 'about-us')->first();
        $seoContents = $sections->seo_content;
        $seoImage = @$seoContents->image ? getImage(getFilePath('seo') . '/' . @$seoContents->image, getFileSize('seo')) : null;
        return view('Template::about', compact('pageTitle', 'sections', 'seoContents', 'seoImage'));
    }

    public function faq() {
        $pageTitle = 'FAQ';
        $sections = Page::where('tempname', activeTemplate())->where('slug', 'faq')->first();
        $seoContents = $sections->seo_content;
        $seoImage = @$seoContents->image ? getImage(getFilePath('seo') . '/' . @$seoContents->image, getFileSize('seo')) : null;
        return view('Template::faq', compact('pageTitle', 'sections', 'seoContents', 'seoImage'));
    }

    public function brands() {
        $pageTitle = 'Brands';
        $brands = Brand::orderBy('name')->get();
        $sections = Page::where('tempname', activeTemplate())->where('slug', 'brands')->first();
        $seoContents = $sections->seo_content;
        $seoImage = @$seoContents->image ? getImage(getFilePath('seo') . '/' . @$seoContents->image, getFileSize('seo')) : null;
        return view('Template::brands', compact('pageTitle', 'brands', 'sections', 'seoContents', 'seoImage'));
    }

    public function categories() {
        $pageTitle = 'Categories';
        $categories = Category::isParent()->with('allSubcategories')->orderBy('name')->get();

        $sections = Page::where('tempname', activeTemplate())->where('slug', 'categories')->first();
        $seoContents = $sections->seo_content;
        $seoImage = @$seoContents->image ? getImage(getFilePath('seo') . '/' . @$seoContents->image, getFileSize('seo')) : null;
        return view('Template::categories', compact('pageTitle', 'categories', 'sections', 'seoContents', 'seoImage'));
    }

    public function offers() {
        $pageTitle = 'All Offers';
        $offers = Offer::running()
            ->whereHas('products', fn ($products) => $products->published()->withDisplayableImage())
            ->get();

        $sections = Page::where('tempname', activeTemplate())->where('slug', 'offers')->first();
        $seoContents = $sections->seo_content;
        $seoImage = @$seoContents->image ? getImage(getFilePath('seo') . '/' . @$seoContents->image, getFileSize('seo')) : null;

        return view('Template::offers', compact('pageTitle', 'offers', 'sections', 'seoContents', 'seoImage'));
    }

    public function offerProducts($id) {
        try {
            $id = decrypt($id);
            $offer = Offer::running()->with(['products' => fn ($products) => $products->published()->withDisplayableImage()])
                ->whereHas('products', fn ($products) => $products->published()->withDisplayableImage())
                ->findOrFail($id);
            $pageTitle =   $offer->name . ' - Products';
            return view('Template::offer_products', compact('pageTitle', 'offer'));
        } catch (\Throwable $th) {
            abort(404);
        }
    }

    public function trackOrder() {
        $pageTitle = 'Order Tracking';
        return view('Template::order_track', compact('pageTitle'));
    }

    public function contact() {
        $pageTitle = "Contact Us";
        $user = auth()->user();
        $sections = Page::where('tempname', activeTemplate())->where('slug', 'contact')->first();
        $seoContents = $sections->seo_content;
        $seoImage = @$seoContents->image ? getImage(getFilePath('seo') . '/' . @$seoContents->image, getFileSize('seo')) : null;
        return view('Template::contact', compact('pageTitle', 'user', 'sections', 'seoContents', 'seoImage'));
    }


    public function contactSubmit(Request $request) {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'subject' => 'required|string|max:255',
            'message' => 'required',
        ]);

        $request->session()->regenerateToken();

        if (!verifyCaptcha()) {
            $notify[] = ['error', 'Invalid captcha provided'];
            return back()->withNotify($notify);
        }

        $random = getNumber();

        $ticket = new SupportTicket();
        $ticket->user_id = auth()->id() ?? 0;
        $ticket->name = $request->name;
        $ticket->email = $request->email;
        $ticket->priority = Status::PRIORITY_MEDIUM;


        $ticket->ticket = $random;
        $ticket->subject = $request->subject;
        $ticket->last_reply = Carbon::now();
        $ticket->status = Status::TICKET_OPEN;
        $ticket->save();

        $adminNotification = new AdminNotification();
        $adminNotification->user_id = auth()->id() ?? 0;
        $adminNotification->title = 'A new contact message has been submitted';
        $adminNotification->click_url = urlPath('admin.ticket.view', $ticket->id);
        $adminNotification->save();

        $message = new SupportMessage();
        $message->support_ticket_id = $ticket->id;
        $message->message = $request->message;
        $message->save();

        $notify[] = ['success', 'Ticket created successfully!'];

        return to_route('ticket.view', [$ticket->ticket])->withNotify($notify);
    }

    public function policyPages($slug) {
        $policy = Frontend::where('tempname', activeTemplateName())->where('slug', $slug)->where('data_keys', 'policy_pages.element')->firstOrFail();
        $pageTitle = $policy->data_values->title;
        $seoContents = $policy->seo_content;
        $seoImage = @$seoContents->image ? frontendImage('policy_pages', $seoContents->image, getFileSize('seo'), true) : null;
        return view('Template::policy', compact('policy', 'pageTitle', 'seoContents', 'seoImage'));
    }

    public function changeLanguage($lang = null) {
        $language = Language::where('code', $lang)->first();
        if (!$language) $lang = 'en';
        session()->put('lang', $lang);
        return back();
    }


    public function cookieAccept() {
        Cookie::queue('gdpr_cookie', gs('site_name'), 43200);
    }

    public function cookiePolicy() {
        $cookieContent = Frontend::where('data_keys', 'cookie.data')->first();
        abort_if($cookieContent->data_values->status != Status::ENABLE, 404);
        $pageTitle = 'Cookie Policy';
        $cookie = Frontend::where('data_keys', 'cookie.data')->first();
        return view('Template::cookie', compact('pageTitle', 'cookie'));
    }

    public function placeholderImage($size = null) {
        $imgWidth = explode('x', $size)[0];
        $imgHeight = explode('x', $size)[1];
        $text = $imgWidth . '×' . $imgHeight;
        $fontFile = realpath('assets/font/solaimanLipi_bold.ttf');
        $fontSize = round(($imgWidth - 50) / 8);
        if ($fontSize <= 9) {
            $fontSize = 9;
        }
        if ($imgHeight < 100 && $fontSize > 30) {
            $fontSize = 30;
        }

        $image     = imagecreatetruecolor($imgWidth, $imgHeight);
        $colorFill = imagecolorallocate($image, 100, 100, 100);
        $bgFill    = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $bgFill);
        $textBox = imagettfbbox($fontSize, 0, $fontFile, $text);
        $textWidth  = abs($textBox[4] - $textBox[0]);
        $textHeight = abs($textBox[5] - $textBox[1]);
        $textX      = ($imgWidth - $textWidth) / 2;
        $textY      = ($imgHeight + $textHeight) / 2;
        header('Content-Type: image/jpeg');
        imagettftext($image, $fontSize, 0, $textX, $textY, $colorFill, $fontFile, $text);
        imagejpeg($image);
        imagedestroy($image);
    }

    public function maintenance() {
        $pageTitle = 'Maintenance Mode';
        if (gs('maintenance_mode') == Status::DISABLE) {
            return to_route('home');
        }
        $maintenance = Frontend::where('data_keys', 'maintenance.data')->first();
        return view('Template::maintenance', compact('pageTitle', 'maintenance'));
    }

    public function addSubscriber(Request $request) {

        if (!(gs('subscriber_module'))) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->all()
            ]);
        }

        $subscribe = Subscriber::where('email', $request->email)->first();
        if (!$subscribe) {
            $subscribe = new Subscriber();
            $subscribe->email = $request->email;
            $subscribe->save();
            return response()->json([
                'status' => true,
                'message' => trans('Subscribed successfully')
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => trans('Already subscribed')
            ]);
        }
    }

    public function getOrderTrackData($orderNumber) {

        $orderData = Order::isValidOrder()->where('order_number', $orderNumber)->first();

        if (!$orderData) {
            $notify = 'No order found with this order number';
            return response()->json(['error' => $notify]);
        }

        return response()->json([
            'success' => true,
            'payment_status' => $orderData->payment_status,
            'status' => $orderData->status,
            'is_cod' => $orderData->is_cod,
        ]);
    }

    public function robots()
    {
        $content = "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /user/\nDisallow: /checkout/\nDisallow: /cart\nDisallow: /login\nDisallow: /register\nSitemap: " . url('/sitemap.xml') . "\n";
        return response($content)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function llms()
    {
        $content = "# " . gs()->siteName('ViseMart') . "\n\n"
            . "> Browse products, categories, brands, and current offers from this online store.\n\n"
            . "## Public pages\n"
            . "- Home: " . url('/') . "\n"
            . "- Categories: " . url('/categories') . "\n"
            . "- Brands: " . url('/brands') . "\n"
            . "- Products: " . url('/products') . "\n"
            . "- Offers: " . url('/offers') . "\n\n"
            . "Use each product page as the source of truth for price, availability, descriptions, and specifications. These details can change; do not infer missing attributes.\n";
        return response($content)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function sitemap()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $append = function (string $location, $updatedAt = null) use (&$xml) {
            $xml .= '<url><loc>' . htmlspecialchars($location, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</loc>';
            if ($updatedAt) {
                $xml .= '<lastmod>' . $updatedAt->toAtomString() . '</lastmod>';
            }
            $xml .= '</url>';
        };

        foreach ([url('/'), url('/categories'), url('/brands'), url('/products'), url('/offers')] as $location) {
            $append($location);
        }
        Category::query()->select(['id', 'slug', 'updated_at'])->chunkById(500, function ($categories) use ($append) {
            foreach ($categories as $category) {
                $append($category->shopLink(), $category->updated_at);
            }
        });
        Brand::query()->select(['id', 'slug', 'updated_at'])->chunkById(500, function ($brands) use ($append) {
            foreach ($brands as $brand) {
                $append($brand->shopLink(), $brand->updated_at);
            }
        });
        Product::published()
            ->whereHas('categories')
            ->where(function ($query) {
                $query->where(fn ($q) => $q->whereNotNull('external_image_url')->where('external_image_url', '!=', ''))
                    ->orWhereHas('displayImage');
            })
            ->with('displayImage')
            ->select(['id', 'slug', 'external_image_url', 'updated_at'])
            ->chunkById(500, function ($products) use ($append) {
                foreach ($products as $product) {
                    if ($product->hasDisplayableImage()) {
                        $append($product->link(), $product->updated_at);
                    }
                }
            });
        $xml .= '</urlset>';
        return response($xml)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
