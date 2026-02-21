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
use App\Models\ProductRequestUserModel;
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

        $this->authorize('view', ProductModel::class);

        $request->validate([
            'keyword' => 'nullable|string|max:100',
            'limit' => 'nullable|integer|in:10,20,50,100',
            'page' => 'nullable|integer|min:1',
            'order_by' => 'nullable|in:asc,desc',
            'category' => 'nullable|string|max:100',
            'condition' => 'nullable|in:new,used,refurbished,damaged,discontinued|string',
        ]);

        $filters = $request->only([
            'keyword',
            'limit',
            'page',
            'order_by',
            'category',
            'condition',
        ]);
        $products = $this->productRepository->getCached(auth()->id(), $filters);

        $category = ProductModel::select('category')->distinct()->get();
        $branch = BranchesModel::select('branches_id', 'name')->get();


        return Inertia::render('Product/Index', [
            'product' => $products,
            'filters' => $filters,
            'category' => $category,
            'branch' => $branch,

        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', ProductModel::class);
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
        // dd($request->all());
        $this->authorize('create', ProductModel::class);
        $this->validationText($request->all());
        $imageCover = null;
        if ($request->hasFile('image')) {
            // Simpan file baru (nama unik biar nggak tabrakan)
            $imageCover = $request->file('image')->store('product/cover', 'public');
        }

        $product = ProductModel::create([
            'created_by'     => auth()->id(),
            'source'         => 'manual',
            'name'           => ucwords($request['name']),
            'item_condition' => $request['item_condition'],
            'link'           => $request['link'],
            'slug'           => Str::slug($request['name']),
            'category'       => Str::slug($request['category']),
            'image_path'        => $imageCover,
        ]);

        foreach ($request['branch_prices'] as $branch_prices) {
            $isDiscount = $branch_prices['discount_price'] !== null
                && $branch_prices['discount_price'] > 0;

            $product->prices()->create([
                'created_by'     => auth()->id(),
                'product_id' => $product->product_id,
                'branch_id' => $branch_prices['branch_id'],
                'status'            => $branch_prices['status'],
                'base_price' => $branch_prices['base_price'],
                'discount_price' => $branch_prices['discount_price'] ?? 0,
                'valid_from' => $branch_prices['valid_from'] ?? Carbon::now()->toDateString(),
                'valid_until' => $branch_prices['valid_until'],
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
        $this->authorize('edit', ProductModel::class);
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
        $this->authorize('edit', ProductModel::class);

        $product = $productModel::with(['creator', 'prices.branch'])
            ->findOrFail($id);

        $branches = BranchesModel::select('branches_id', 'name', 'status_official')
            ->get();

        $this->productRepository->clearCache(auth()->id());
        return Inertia::render('Product/Form/pageForm', [
            'product' => $product,
            'branch' => $branches,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductModel $productModel, string $id)
    {
        $this->authorize('edit', ProductModel::class);
        $this->validationText($request->all(), $id);

        $product = $productModel::with(['creator', 'prices.branch'])
            ->findOrFail($id);

        $imageCover = null;
        if ($request->hasFile('image')) {
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }
            $imageCover = $request->file('image')
                ->store('product/cover', 'public');
        } else {
            $imageCover = $product->image_path;
        }

        $product->update([
            'created_by'     => auth()->id(),
            'source'         => 'manual',
            'name'           => ucwords($request['name']),
            'item_condition' => $request['item_condition'],
            'link'           => $request['link'],
            'slug'           => Str::slug($request['name']),
            'category'       => Str::slug($request['category']),
            'image_path'     => $imageCover,
        ]);

        // Kumpulkan semua branch_id yang dikirim dari Vue
        // Kita butuh array ID: ['uuid-jakarta', 'uuid-bandung']
        $requestBranchIds = collect($request['branch_prices'])->pluck('branch_id')->toArray();
        // B. HAPUS harga di database yang TIDAK ADA di list request
        // "Tolong hapus harga milik produk ini, KECUALI cabang yang ada di list $requestBranchIds"
        $product->prices()
            ->whereNotIn('branch_id', $requestBranchIds)
            ->delete();

        foreach ($request['branch_prices'] as $branch_prices) {

            $isDiscount = isset($branch_prices['discount_price'])
                && $branch_prices['discount_price'] !== null
                && $branch_prices['discount_price'] > 0;

            $product->prices()->updateOrCreate(
                [
                    'branch_id' => $branch_prices['branch_id'],
                    'product_id' => $product->product_id
                ],
                [
                    'created_by'     => auth()->id(),
                    'status'         => $branch_prices['status'],
                    'base_price'     => $branch_prices['base_price'],
                    'discount_price' => $branch_prices['discount_price'] ?? 0,
                    'valid_from'     => $branch_prices['valid_from'] ?? Carbon::now()->toDateString(),
                    'valid_until'    => $branch_prices['valid_until'],
                    'price_type'     => $isDiscount ? 'discount' : 'normal',
                ]
            );
        }
        $this->productRepository->clearCache(auth()->id());
        return redirect()->route('product')->with('message', 'Produk ' . ucwords($product->name) . ' berhasil diperbarui.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductModel $productModel, string $id)
    {
        $this->authorize('delete', ProductModel::class);
        $product = $productModel::findOrFail($id);

        if ($product->transactions()->exists()) {
            return redirect()->back()->with('warning', 'Gagal menghapus. Produk "' . $product->name . '" masih terkait dengan data transaksi.');
        }

        if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->prices()->delete();
        $product->delete();
        $message = 'Produk ' . $product->name . ' telah dihapus.';
        $this->productRepository->clearCache(auth()->id());
        return redirect()->back()->with('message', $message);
    }

    public function destroy_all(Request $request)
    {
        // 1. Otorisasi
        $this->authorize('delete', ProductModel::class);

        // 2. Validasi Input
        $ids = $request->input('ids', []);

        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('error', 'Tidak ada produk yang dipilih.');
        }

        // FILTERING: Cari Produk yang SEDANG DIPAKAI TRANSAKSI
        // ambil ID produk yang punya relasi ke transaksi
        $usedIds = ProductModel::whereIn('product_id', $ids)
            ->has('transactions') // Cek relasi (sesuaikan nama function di model, misal: transactions / orderItems)
            ->pluck('product_id')
            ->toArray();

        // Tentukan Produk yang AMAN dihapus
        // Aman = Semua Pilihan - Yang Terpakai
        $idsToDelete = array_diff($ids, $usedIds);

        // Hitung statistik
        $countDeleted = count($idsToDelete);
        $countSkipped = count($usedIds);

        // EKSEKUSI HAPUS (Hanya untuk yang Aman)
        if ($countDeleted > 0) {
            // Ambil data produk yang aman (kita butuh modelnya untuk hapus gambar)
            $products = ProductModel::whereIn('product_id', $idsToDelete)->get();

            foreach ($products as $prod) {
                // A. Hapus Gambar
                if ($prod->image_path && Storage::disk('public')->exists($prod->image_path)) {
                    Storage::disk('public')->delete($prod->image_path);
                }

                // B. Hapus Harga (Child)
                $prod->prices()->delete();

                // C. Hapus Produk (Parent)
                $prod->delete();
            }

            // Bersihkan cache setelah loop selesai
            $this->productRepository->clearCache(auth()->id());
        }

        // Skenario A: Semua gagal karena terpakai
        if ($countDeleted === 0 && $countSkipped > 0) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus. Produk yang dipilih sedang digunakan dalam transaksi user.');
        }

        // Skenario B: Partial Success (Ada yang terhapus, ada yang dilewati)
        if ($countSkipped > 0) {
            return redirect()->route('product')
                ->with('warning', "$countDeleted produk berhasil dihapus. $countSkipped produk DILEWATI karena sedang digunakan dalam transaksi.");
        }

        return redirect()->route('product')->with('message', $countDeleted . ' Produk terpilih berhasil dihapus.');
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
        return redirect()->route('product')->with('message', 'Data tabel Produk berhasil diperbarui.');
    }
}
