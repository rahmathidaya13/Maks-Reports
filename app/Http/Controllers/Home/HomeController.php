<?php

namespace App\Http\Controllers\Home;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        // dd(auth()->user()->profile->role->can_add);
        return Inertia::render('Home/Index');
    }
}
