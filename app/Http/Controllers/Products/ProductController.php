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
        $this->productRepository->clearCache(auth()->id());
        return Inertia::render('Product/Form/pageForm');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $finalImageLink = $request->input('image_link') ?? null;
        if ($request->hasFile('image_path')) {
            // Simpan file baru (nama unik biar nggak tabrakan)
            $path = $request->file('image_path')->store('product/cover', 'public');
            $finalImageLink = 'storage/' . $path;
        }

        $galleryPaths = [];
        if ($request->hasFile('image_url')) {
            foreach ($request->file('image_url') as $file) {
                // Simpan file dan ambil path-nya
                $path = $file->store('product/gallery', 'public');
                $galleryPaths[] = 'storage/' . $path;
            }
        }

        $product = ProductModel::create([
            'created_by'     => auth()->id(),
            'source'         => 'manual',
            'name'           => $request['name'],
            'category'       => $request['category'],
            'price_original' => $request['price_original'],
            'price_discount' => $request['price_discount'],
            'link'           => $request['link'], // Bisa null
            'image_link'     => $finalImageLink, // String (Path File atau URL)
            'image_url'      => $galleryPaths, // Laravel otomatis cast ke JSON jika di model dicasting
            'description'    => $request['description'],
        ]);

        $this->productRepository->clearCache(auth()->id());
        return redirect()->route('product')->with('success', 'Produk ' . $product->name . ' Berhasil Ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductModel $productModel, string $id)
    {
        $product = $productModel::findOrFail($id);
        $this->productRepository->clearCache(auth()->id());
        return Inertia::render('Product/ProductDetail', [
            'product' => $product
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
    public function destroy(ProductModel $productModel, string $id)
    {
        $product = $productModel::findOrFail($id);
        $product->delete();
        $this->productRepository->clearCache(auth()->id());
        return redirect()->route('product')->with('message', 'Produk ' . $product->name . '  Berhasil Dihapus');
    }
}
