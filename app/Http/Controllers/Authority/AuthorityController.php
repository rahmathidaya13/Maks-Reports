<?php

namespace App\Http\Controllers\Authority;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PageModel;

class AuthorityController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'page.permission:can_view']);
    }

    public function index()
    {
        PageModel::with('permissions')->get();
        return Inertia::render('Authority/Index');
    }
}
