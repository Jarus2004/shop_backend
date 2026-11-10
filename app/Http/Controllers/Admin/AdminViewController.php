<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class AdminViewController extends Controller
{
    /**
     * Show admin login page.
     */
    public function login(): View
    {
        return view('admin.login');
    }

    /**
     * Show products management page.
     */
    public function products(): View
    {
        return view('admin.products.index');
    }

    /**
     * Show categories management page.
     */
    public function categories(): View
    {
        return view('admin.categories.index');
    }

    /**
     * Show orders management page.
     */
    public function orders(): View
    {
        return view('admin.orders.index');
    }
}
