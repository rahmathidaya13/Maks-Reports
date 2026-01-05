<?php

namespace App\Http\Controllers\Products;

use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use App\Models\BranchesModel;
use App\Models\ProductPriceModel;
use App\Traits\ProductValidation;
use App\Http\Controllers\Controller;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use ProductValidation;
    protected $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }
    public function index(Request $request)
    {

        $this->authorize('view', ProductPriceModel::class);

        $filters = $request->only([
            'keyword',
            'limit',
            'page',
            'order_by',
            'category',

            'status',
            'branch',
            'discount_only',
        ]);
        $products = $this->productRepository->getCached(auth()->id(), $filters);

        $category = ProductModel::select('category')->distinct()->get();
        $branch = BranchesModel::select('branches_id', 'name')->get();

        return Inertia::render('Product/Index', [
            'product' => $products,
            'filters' => $filters,
            'category' => $category,
            'branch' => $branch
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', ProductPriceModel::class);
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
        $this->authorize('create', ProductPriceModel::class);
        $this->validationText($request->all());
        $imageCover = null;
        if ($request->hasFile('image')) {
            // Simpan file baru (nama unik biar nggak tabrakan)
            $imageCover = $request->file('image')->store('product/cover', 'public');
        }


        $product = ProductModel::create([
            'created_by'     => auth()->id(),
            'source'         => 'manual',
            'name'           => $request['name'],
            'link'           => $request['link'],
            'slug'           => Str::slug($request['name']),
            'category'       => Str::slug($request['category']),
            'image_path'        => $imageCover,
        ]);

        foreach ($request['branch'] as $branch) {
            $isDiscount = $request->filled('discount_price')
                && $request->discount_price > 0;
            $product->prices()->create([
                'created_by'     => auth()->id(),
                'product_id' => $product->product_id,
                'branch_id' => $branch,
                'status'            => $request['status'],
                'base_price' => $request['base_price'],
                'discount_price' => $request['discount_price'],
                'valid_from' => $request['valid_from'],
                'valid_until' => $request['valid_until'],
                'price_type' => $isDiscount ? 'discount' : 'normal',
            ]);
        }
        $this->productRepository->clearCache(auth()->id());
        return redirect()->route('product')->with('message', 'Produk ' . $product->name . ' Berhasil Ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductModel $productModel, string $id)
    {
        $this->authorize('edit', ProductPriceModel::class);
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
        $this->authorize('edit', ProductPriceModel::class);

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
    public function update(Request $request, ProductPriceModel $productPriceModel, string $id)
    {
        $this->authorize('edit', ProductPriceModel::class);
        $this->validationText($request->all(), $productPriceModel::findOrFail($id)->product_id);
        $product = $productPriceModel::findOrFail($id);

        $imageCover = null;
        if ($request->hasFile('image')) {
            if ($product->product->image_path && Storage::disk('public')->exists($product->product->image_path)) {
                Storage::disk('public')->delete($product->product->image_path);
            }
            $imageCover = $request->file('image')
                ->store('product/cover', 'public');
        } else {
            $imageCover = $product->product->image_path;
        }

        $product->product()->update([
            'created_by'     => auth()->id(),
            'source'         => 'manual',
            'name'           => $request['name'],
            'link'           => $request['link'],
            'slug'           => Str::slug($request['name']),
            'category'       => Str::slug($request['category']),
            'image_path'     => $imageCover,
        ]);

        $branches = $request['branch'];
        $isDiscount = $request->filled('discount_price')
            && $request->discount_price > 0;
        $priceData = [
            'created_by'     => auth()->id(),
            'status'         => $request['status'],
            'base_price'     => $request['base_price'],
            'discount_price' => $request['discount_price'] ?? 0,
            'valid_from'     => $request['valid_from'],
            'valid_until'    => $request['valid_until'],
            'price_type'     => $isDiscount ? 'discount' : 'normal',
        ];
        if (count($branches) > 0) {
            // Ambil cabang pertama, dan hapus dia dari array $branches
            $firstBranchId = array_shift($branches);

            // Update baris ini ($id) menjadi milik cabang pertama
            $product->update(array_merge($priceData, [
                'branch_id' => $firstBranchId
            ]));
        }
        foreach ($branches as $branch) {
            $productPriceModel::updateOrCreate(
                [
                    'product_id' => $product->product_id,
                    'branch_id'  => $branch
                ],
                $priceData // Data harga sama dengan yang pertama
            );
        }

        $this->productRepository->clearCache(auth()->id());

        return redirect()->route('product')->with('message', 'Produk ' . $product->product->name . ' Berhasil Diperbarui');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductPriceModel $productPriceModel, string $id)
    {
        $this->authorize('delete', ProductPriceModel::class);
        $productPrice = $productPriceModel::with('product')->findOrFail($id);
        $productMaster = $productPrice->product;
        $totalProduct = $productPriceModel::where('product_id', $productPrice->product_id)->count();

        if ($totalProduct > 1) {
            $productPrice->delete();
            $message = 'Salah satu varian harga/cabang berhasil dihapus. Produk utama masih tersimpan.';
        } else {
            // HAPUS FOTO UTAMA (COVER)
            if ($productMaster->image_path) {
                // Cek file ada atau tidak untuk menghindari error, lalu hapus
                if (Storage::disk('public')->exists($productMaster->image_path)) {
                    Storage::disk('public')->delete($productMaster->image_path);
                }
            }
            $productMaster->delete();
            $message = 'Produk ' . $productMaster->name . ' telah dihapus permanen beserta seluruh datanya.';
        }
        $this->productRepository->clearCache(auth()->id());
        return redirect()->route('product')->with('message', $message);
    }

    public function destroy_all(Request $request)
    {
        $this->authorize('delete', ProductPriceModel::class);
        $ids = $request->input('ids');
        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('error', 'Tidak ada produk yang dipilih.');
        }

        foreach ($ids as $id) {
            $productPrice = ProductPriceModel::with('product')->find($id);

            // Jika data tidak ditemukan (mungkin sudah dihapus), skip ke loop berikutnya
            if (!$productPrice) continue;

            $productMaster = $productPrice->product;

            // Pastikan Parent Product masih ada (Mencegah error orphan data)
            if (!$productMaster) {
                // Jika induknya hilang, hapus saja data harga ini langsung
                $productPrice->delete();
                continue;
            }

            // Hitung jumlah varian yang tersisa untuk produk induk ini
            $totalProduct = ProductPriceModel::where('product_id', $productPrice->product_id)->count();

            // LOGIKA UTAMA
            if ($totalProduct > 1) {
                // KONDISI A: Masih ada varian lain (kakak/adiknya).
                // Hapus varian ini saja.
                $productPrice->delete();
            } else {
                // KONDISI B: Ini varian terakhir (Single Fighter).
                // Hapus Induknya sekalian + Gambarnya.

                if ($productMaster->image_path && Storage::disk('public')->exists($productMaster->image_path)) {
                    Storage::disk('public')->delete($productMaster->image_path);
                }

                // Hapus Parent (Otomatis anak terakhir ini ikut terhapus karena Cascade atau logic DB)
                $productMaster->delete();
            }
        }
        $this->productRepository->clearCache(auth()->id());
        return redirect()->route('product')->with('message', count($ids) . ' Produk terpilih berhasil dihapus.');
    }

    public function deletedGalleryImage(Request $request, ProductModel $productModel, string $id)
    {
        $this->authorize('delete', ProductModel::class);
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

    public function reset()
    {
        $this->productRepository->clearCache(auth()->id());
        return redirect()->route('product');
    }
}
