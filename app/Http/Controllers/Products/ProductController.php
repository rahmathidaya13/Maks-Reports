<?php

namespace App\Http\Controllers\Products;

use Inertia\Inertia;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BranchesModel;
use App\Models\ProductPriceModel;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        $branch = BranchesModel::select('branches_id', 'name')->get();
        $this->productRepository->clearCache(auth()->id());
        return Inertia::render('Product/Form/pageForm', [
            'branch' => $branch
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $imageCover = null;
        if ($request->hasFile('image')) {
            // Simpan file baru (nama unik biar nggak tabrakan)
            $imageCover = $request->file('image')->store('product/cover', 'public');
        }


        $product = ProductModel::create([
            'created_by'     => auth()->id(),
            'source'         => 'manual',
            'status'            => $request['status'],
            'name'           => $request['name'],
            'link'           => $request['link'],
            'slug'           => Str::slug($request['name']),
            'category'       => $request['category'],
            'image_path'        => $imageCover,
        ]);

        foreach ($request['branch'] as $branch) {
            $isDiscount = $request->filled('discount_price')
                && $request->discount_price > 0;
            $product->prices()->create([
                'created_by'     => auth()->id(),
                'product_id' => $product->product_id,
                'branch_id' => $branch,
                'base_price' => $request['base_price'],
                'discount_price' => $request['discount_price'],
                'valid_from' => $request['valid_from'],
                'valid_until' => $request['valid_until'],
                'price_type' => $isDiscount ? 'discount' : 'normal',
            ]);
        }
        $this->productRepository->clearCache(auth()->id());
        return redirect()->route('product')->with('success', 'Produk ' . $product->name . ' Berhasil Ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductModel $productModel, string $id)
    {
        $product = $productModel::with('creator')->findOrFail($id);
        $this->productRepository->clearCache(auth()->id());
        return Inertia::render('Product/ProductDetail', [
            'product' => $product
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductPriceModel $productPriceModel, string $id)
    {
        $product = $productPriceModel::with(['creator', 'product', 'branch'])->findOrFail($id);
        $branch = BranchesModel::select('branches_id', 'name')->get();

        $this->productRepository->clearCache(auth()->id());
        return Inertia::render('Product/Form/pageForm', [
            'product' => $product,
            'branch' => $branch

        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductModel $productModel, string $id)
    {
        $product = $productModel::findOrFail($id);

        /**
         * =========================
         * COVER IMAGE
         * =========================
         */
        if ($request->hasFile('image')) {
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }

            $imageCover = $request->file('image')
                ->store('product/cover', 'public');
        } else {
            $imageCover = $product->image_path;
        }

        /**
         * =========================
         * GALLERY IMAGE
         * =========================
         */
        $currentGallery = $product->image_url ?? [];

        /**
         * 1. Hapus gallery yang ditandai
         */
        if (is_array($request->deleted_images)) {
            foreach ($request->deleted_images as $path) {

                // pastikan path bersih
                $cleanPath = str_replace(['storage/', '/storage/'], '', $path);

                if (Storage::disk('public')->exists($cleanPath)) {
                    Storage::disk('public')->delete($cleanPath);
                }

                // hapus dari array DB
                $currentGallery = array_values(
                    array_filter($currentGallery, function ($item) use ($cleanPath) {

                        $itemClean = str_replace(['storage/', '/storage/'], '', $item);

                        return $itemClean !== $cleanPath;
                    })
                );
            }
        } else {
            $currentGallery = $product->image_url;
        }

        /**
         * 2. Tambah gallery baru
         */
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $currentGallery[] = $file
                    ->store('product/gallery', 'public');
            }
        }

        /**
         * =========================
         * UPDATE PRODUCT
         * =========================
         */
        $product->update([
            'created_by'     => auth()->id(),
            'source'         => 'manual',
            'status'         => $request->status,
            'name'           => $request->name,
            'slug'           => Str::slug($request->name),
            'link'           => $request->link,
            'category'       => $request->category,
            'price_original' => $request->price_original,
            'price_discount' => $request->price_discount,
            'image_path'     => $imageCover,
            'image_url'      => $currentGallery,
            'description'    => $request->description,
        ]);

        $this->productRepository->clearCache(auth()->id());

        return back()->with('success', 'Produk berhasil diperbarui');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductModel $productModel, string $id)
    {
        $product = $productModel::findOrFail($id);
        // ======================================================
        // LOGIC 1: HAPUS FOTO UTAMA (COVER)
        // ======================================================
        // Cek apakah ada gambar DAN gambar tersebut adalah file lokal (bukan link http)
        if ($product->image_path) {
            // Hapus prefix 'storage/' agar path sesuai dengan root disk public
            $coverPath = str_replace('storage/', '', $product->image_path);

            // Cek file ada atau tidak untuk menghindari error, lalu hapus
            if (Storage::disk('public')->exists($coverPath)) {
                Storage::disk('public')->delete($coverPath);
            }
        }

        // ======================================================
        // LOGIC 2: HAPUS SEMUA GAMBAR GALERI
        // ======================================================
        // Cek apakah kolom image_url ada isinya (Array)
        if (!empty($product->image_url) && is_array($product->image_url)) {
            foreach ($product->image_url as $galleryImage) {
                // Cek apakah file lokal
                if ($galleryImage && !Str::startsWith($galleryImage, 'http')) {
                    $galleryPath = str_replace('storage/', '', $galleryImage);

                    if (Storage::disk('public')->exists($galleryPath)) {
                        Storage::disk('public')->delete($galleryPath);
                    }
                }
            }
        }

        // ======================================================
        // LOGIC 3: HAPUS DATA DATABASE
        // ======================================================
        $product->delete();
        $this->productRepository->clearCache(auth()->id());
        return redirect()->route('product')->with('message', 'Produk ' . $product->name . '  Berhasil Dihapus');
    }

    public function deletedGalleryImage(Request $request, ProductModel $productModel, string $id)
    {
        $product = $productModel::findOrFail($id);
        $cleanPath = $request['image_path'];
        $cleanPath = str_replace('/storage/', '', $cleanPath);
        $cleanPath = str_replace('storage/', '', $cleanPath);
        // 2. Hapus File Fisik
        if (Storage::disk('public')->exists($cleanPath)) {
            Storage::disk('public')->delete($cleanPath);
        }

        $currentGallery = $product->image_url ?? [];

        $updatedGallery = array_values(array_filter($currentGallery, function ($item) use ($cleanPath) {
            // Kita juga bersihkan item dari DB buat jaga-jaga, biar perbandingannya apple-to-apple
            $dbItemClean = str_replace('storage/', '', $item);

            // Kembalikan TRUE jika item INI bukan yang mau dihapus
            return $dbItemClean !== $cleanPath;
        }));
        $product->update([
            'image_url' => $updatedGallery
        ]);
        return redirect()->back();
    }
}
