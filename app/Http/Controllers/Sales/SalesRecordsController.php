<?php

namespace App\Http\Controllers\Sales;

use Inertia\Inertia;
use App\Models\SalesRecords;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\SalesRecordRepository;

class SalesRecordsController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    protected $salesRepository;
    public function __construct(SalesRecordRepository $salesRepository)
    {
        $this->salesRepository = $salesRepository;
    }
    public function index(Request $request)
    {
        $this->authorize('view', SalesRecords::class);
        $filters = $request->only([
            'limit',
            'page',
            'order_by',
            'page'
        ]);
        $salesRecord = $this->salesRepository->getCached(auth()->id(), $filters);
        return Inertia::render('SalesRecords/Index', [
            'salesRecord' => $salesRecord,
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
