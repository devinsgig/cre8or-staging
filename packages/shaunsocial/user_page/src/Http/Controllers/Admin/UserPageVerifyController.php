<?php

namespace Packages\ShaunSocial\UserPage\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Enum\UserVerifyStatus;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\User;

class UserPageVerifyController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:user_page.manage_verifes');
    }

    public function index(Request $request)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Page Verification Requests'),
            ],
        ];
        $title = __('Page Verification Requests');
        
        $builder = User::where('verify_status', UserVerifyStatus::SENT)->where('is_page', true)->orderBy('verify_status_at','desc');

        $name = $request->query('name');
        if ($name) {
            $builder->where(function ($query) use ($name){
                $query->where('name', 'LIKE', '%'.$name.'%')->orWhere('user_name', 'LIKE', '%'.$name.'%');
            });
        }

        $pages = $builder->paginate(setting('feature.item_per_page'));

        return view('shaun_user_page::admin.verify.index', compact('breadcrumbs', 'title', 'pages', 'name'));
    }

    public function store_verify(Request $request)
    {
        $page = User::findOrFail($request->id);
        if ($page->verify_status == UserVerifyStatus::OK || ! $page->isPage()) {
            abort(403);
        }

        $page->doVerify();

        return redirect()->back()->with([
            'admin_message_success' =>  __('This page has been successfully verified.'),
        ]);
    }

    public function store_reject(Request $request)
    {
        $page = User::findOrFail($request->id);
        if ($page->verify_status != UserVerifyStatus::SENT || ! $page->isPage()) {
            abort(403);
        }

        $page->doRejectVerify($request->user());

        return redirect()->back()->with([
            'admin_message_success' =>  __('This request has been successfully rejected.'),
        ]);
    }

    public function store_unverify(Request $request)
    {
        $page = User::findOrFail($request->id);
        if ($page->verify_status != UserVerifyStatus::OK || ! $page->isPage()) {
            abort(403);
        }

        $page->doUnVerify();

        return redirect()->back()->with([
            'admin_message_success' =>  __('This page has been successfully unverified.'),
        ]);
    }
}
