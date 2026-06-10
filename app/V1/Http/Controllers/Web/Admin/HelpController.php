<?php

namespace App\V1\Http\Controllers\Web\Admin;

use Illuminate\Contracts\View\View;

class HelpController extends AdminController
{
    public function index(): View
    {
        return view('v1.admin.help.index');
    }
}
