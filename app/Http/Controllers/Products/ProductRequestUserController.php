<?php

namespace App\Http\Controllers\Products;

use Inertia\Inertia;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ProductRequestUserModel;

class ProductRequestUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $product = ProductModel::with(['prices' => function ($q) {
            $q->select('base_price', 'product_id');
        }])
            ->select('product_id', 'name')
            ->get();
        return Inertia::render('Product/RequestProduct/Form', [
            'product' => $product
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,product_id',
            'requested_price' => 'required|numeric|min:0',
            'reason' => 'required|string|min:5',
        ]);

        // Ambil harga saat ini otomatis dari DB agar akurat
        $product = ProductModel::with('prices')->findOrFail($request->product_id);
        ProductRequestUserModel::create([
            'user_id' => auth()->id(),
            'product_id' => $request->product_id,
            'current_price' => $request->current_price, // Simpan harga asli saat request dibuat
            'requested_price' => $request->requested_price,
            'reason' => $request->reason,
            'status' => 'pending'
        ]);
        return redirect()->route('product')->with('message', 'Permintaan harga berhasil dikirim ke Admin.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductRequestUserModel $productRequestUserModel)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductRequestUserModel $productRequestUserModel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductRequestUserModel $productRequests, $id)
    {
        $productRequest = $productRequests::findOrFail($id);
        $productRequest->update([
            'product_id' => $request->product_id,
            'current_price' => $request->current_price, // Simpan harga asli saat request dibuat
            'requested_price' => $request->requested_price,
            'reason' => $request->reason,
        ]);
        return redirect()->route('product')->with('message', 'Berhasil melakukan perubahan permintaan.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductRequestUserModel $productRequestUserModel)
    {
        //
    }
}
