<?php

namespace App\Http\Controllers\Sales;

use Inertia\Inertia;
use App\Models\SalesRecords;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SalesRecordsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('SalesRecords/Index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(SalesRecords $salesRecords)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SalesRecords $salesRecords)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SalesRecords $salesRecords)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SalesRecords $salesRecords)
    {
        //
    }
}
