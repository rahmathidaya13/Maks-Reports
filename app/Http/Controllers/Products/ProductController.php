<?php

namespace App\Http\Controllers\Products;

use Inertia\Inertia;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
        $this->productRepository->clearCache(auth()->id());
        return Inertia::render('Product/Form/pageForm');
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

        $galleryPaths = [];
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                // Simpan file dan ambil path-nya
                $galleryPaths[] = $file->store('product/gallery', 'public');
            }
        }
        $product = ProductModel::create([
            'created_by'     => auth()->id(),
            'source'         => 'manual',
            'status'            => $request['status'],
            'name'           => $request['name'],
            'link'           => $request['link'],
            'slug'           => Str::slug($request['name']),
            'category'       => $request['category'],
            'price_original' => $request['price_original'],
            'price_discount' => $request['price_discount'],
            'image_path'        => $imageCover,
            'image_url'      => $galleryPaths,
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
        $product = $productModel::with('creator')->findOrFail($id);
        $this->productRepository->clearCache(auth()->id());
        return Inertia::render('Product/ProductDetail', [
            'product' => $product
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductModel $productModel, string $id)
    {
        $product = $productModel::findOrFail($id);
        $this->productRepository->clearCache(auth()->id());
        return Inertia::render('Product/Form/pageForm', [
            'product' => $product
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductModel $productModel, string $id)
    {
        // 1. Ambil data produk lama
        $product = $productModel::findOrFail($id);

        if ($request->hasFile('image')) {
            // hapus thumbnail lama
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }

            // simpan thumbnail baru
            $imageCover = $request->file('image')
                ->store('product/cover', 'public');
        } else {
            $imageCover = $product->image_path;
        }

        $currentGallery = $product->image_url ?? [];
        // 1. Hapus gallery yang dipilih admin
        if ($request->filled('deleted_images')) {
            // gagal menghapus file ketika mengganti produk atau menghapus produk gallery
            foreach ($request->deleted_images as $pathToDelete) {

                if (Storage::disk('public')->exists($pathToDelete)) {
                    Storage::disk('public')->delete($pathToDelete);
                }

                $currentGallery = array_values(
                    array_diff($currentGallery, [$pathToDelete])
                );
            }
        }

        // 2. Tambah gallery baru (append)
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $currentGallery[] = $file
                    ->store('product/gallery', 'public');
            }
        }


        $product->update([
            'created_by'     => auth()->id(),
            'source'         => 'manual',
            'status'         => $request['status'],
            'name'           => $request['name'],
            'link'           => $request['link'],
            'slug'           => Str::slug($request['name']),
            'category'       => $request['category'],
            'price_original' => $request['price_original'],
            'price_discount' => $request['price_discount'],
            'image_path'     => $imageCover,
            'image_url'      => $currentGallery,
            'description'    => $request['description'],
        ]);
        $this->productRepository->clearCache(auth()->id());
        return back()->with('success', 'Produk berhasil diperbarui');
        // return redirect()->route('product')->with('message', 'Produk ' . $product->name . ' Berhasil Diubah');
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
        if ($product->image_link && !Str::startsWith($product->image_link, 'http')) {
            // Hapus prefix 'storage/' agar path sesuai dengan root disk public
            $coverPath = str_replace('storage/', '', $product->image_link);

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

    public function deletedGalleryImage(ProductModel $productModel, string $id)
    {
        $product = $productModel::findOrFail($id);

        $currentGallery = $product->image_url;
        dd($currentGallery);
        // return redirect()->back();
    }
}
