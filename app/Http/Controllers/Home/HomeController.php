<?php

namespace App\Http\Controllers\Home;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:developer|admin|super-admin|editor|user');
    }
    public function index()
    {
        return Inertia::render('Home/Index');
    }
}
