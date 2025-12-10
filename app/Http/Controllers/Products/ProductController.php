<?php

namespace App\Http\Controllers\Products;

use Inertia\Inertia;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\ProductRepository;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }
    public function index(Request $request)
    {
        $filters = $request->only([
            'keyword',
            'limit',
            'page',
            'order_by',
            'category',
        ]);
        $products = $this->productRepository->getCached(auth()->id(), $filters);
        $category = ProductModel::select('category')->distinct()->get();
        return Inertia::render('Product/Index', [
            'product' => $products,
            'filters' => $filters,
            'category' => $category,
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
    public function show(ProductModel $productModel, string $id)
    {
        $galleryImages = $productModel::findOrFail($id);
        $this->productRepository->clearCache(auth()->id());
        return response()->json([
            'status' => true,
            'galleryImages' => $galleryImages->image_url,
            'description' => $galleryImages->description,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductModel $productModel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductModel $productModel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductModel $productModel)
    {
        //
    }
}
