<?php

namespace Webkul\Newsletters\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Repositories\StopListRepository;

class HooksController extends Controller
{
    /**
     * Create a new controller instance.
     */
//    public function __construct(
//        protected StopListRepository $stopListRepository
//    ) {}

     /**
     * Check if phone number is blocked.
     */
    public function get_hook(Request $request)
    {

        Log::info("GreenAPI hook received:", [
            'body' =>  $request->all(),
        ]);
//
//        $isBlocked = $this->stopListRepository->isBlocked($request->phone_number);
//
//        return response()->json([
//            'is_blocked' => $isBlocked,
//            'message' => $isBlocked
//                ? trans('newsletters::app.admin.stop-list.phone-blocked')
//                : trans('newsletters::app.admin.stop-list.phone-not-blocked'),
//        ]);
    }
}
