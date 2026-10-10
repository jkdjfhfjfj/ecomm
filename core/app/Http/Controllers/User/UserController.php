<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Models\Order;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller {
    public function home() {
        $pageTitle = 'Dashboard';
        $user = auth()->user();
        $orders    = Order::isValidOrder()->with('orderDetail', 'appliedCoupon')->where('user_id', $user->id);

        $order['total']      = numberFormatShort((clone $orders)->count());
        $order['pending']    = numberFormatShort(Order::isValidOrder()->pending()->where('user_id', $user->id)->count());
        $order['processing'] = numberFormatShort(Order::isValidOrder()->processing()->where('user_id', $user->id)->count());
        $order['dispatched'] = numberFormatShort(Order::isValidOrder()->dispatched()->where('user_id', $user->id)->count());
        $order['delivered']  = numberFormatShort(Order::isValidOrder()->delivered()->where('user_id', $user->id)->count());
        $order['canceled']   = numberFormatShort(Order::isValidOrder()->canceled()->where('user_id', $user->id)->count());
        $latestOrders  = $orders->orderBy('id', 'desc')->limit(6)->get();
        return view('Template::user.dashboard', compact('pageTitle', 'latestOrders', 'order'));
    }

    public function depositHistory(Request $request) {
        $pageTitle = 'Payment History';
        $deposits = auth()->user()->deposits()->searchable(['trx'])->with(['gateway'])->orderBy('id', 'desc')->paginate(getPaginate());
        return view('Template::user.deposit_history', compact('pageTitle', 'deposits'));
    }

    public function userData() {
        $user = auth()->user();

        if ($user->profile_complete != Status::YES) {
            $user->profile_complete = Status::YES;
            $user->save();
        }
        return to_route('user.home');
    }

    public function userDataSubmit(Request $request) {
        $user = auth()->user();
        if ($user->profile_complete != Status::YES) {
            $user->profile_complete = Status::YES;
            $user->save();
        }
        return to_route('user.home');
    }


    public function addDeviceToken(Request $request) {

        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()->all()];
        }

        $deviceToken = DeviceToken::where('token', $request->token)->first();

        if ($deviceToken) {
            return ['success' => true, 'message' => 'Already exists'];
        }

        $deviceToken          = new DeviceToken();
        $deviceToken->user_id = auth()->user()->id;
        $deviceToken->token   = $request->token;
        $deviceToken->is_app  = Status::NO;
        $deviceToken->save();

        return ['success' => true, 'message' => 'Token saved successfully'];
    }

    public function notifications() {
        $pageTitle = 'All Notifications';
        $notifications = UserNotification::where('user_id', auth()->id())->latest()->paginate(getPaginate());
        return view('Template::user.notifications', compact('pageTitle', 'notifications'));
    }

    public function readNotification($id) {
        try {
            $id = decrypt($id);
            $notification = UserNotification::where('user_id', auth()->id())->find($id);
            $notification->is_read = Status::YES;
            $notification->save();

            $url = $notification->click_url ?? url()->previous();
            return redirect($url);
        } catch (\Exception $e) {
            abort(404);
        }
    }
}
