<?php

namespace App\Http\Controllers\Transaction;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\TransactionModel;
use App\Http\Controllers\Controller;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'limit',
            'page',
            'order_by',
            'start_date',
            'end_date',
        ]);
        return Inertia::render('Transaction/Index',[
            'filters' => $filters
        ]);
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
    public function show(TransactionModel $transactionModel)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TransactionModel $transactionModel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TransactionModel $transactionModel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TransactionModel $transactionModel)
    {
        //
    }
}
