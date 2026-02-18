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

        $product = $productModel::with(['creator', 'prices.branch'])->find($id);


        // $branchOfficial = BranchesModel::where('status_official', $product->branch->status_official)->first();

        // $currentOfficial = $branchOfficial && $branchOfficial->status_official === 'official';

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
        $product = $productModel::with(['prices.branch', 'creator'])->findOrFail($id);

        // $totalProduct = $productModel::where('product_id', $product->product_id)->count();
        if ($product->image_path) {
            // Cek file ada atau tidak untuk menghindari error, lalu hapus
            if (Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }
        }

        $product->delete();
        $message = 'Produk ' . $product->name . ' telah dihapus beserta seluruh datanya.';
        // dd($totalProduct);
        // if ($totalProduct > 1) {
        //     $product->delete();
        //     $message = 'Salah satu produk ' . $productMaster->name . ' dari cabang ' . $product->branch->name . ' berhasil dihapus. Produk utama masih tersimpan.';
        // } else {
        //     // HAPUS FOTO UTAMA (COVER)
        //     if ($productMaster->image_path) {
        //         // Cek file ada atau tidak untuk menghindari error, lalu hapus
        //         if (Storage::disk('public')->exists($productMaster->image_path)) {
        //             Storage::disk('public')->delete($productMaster->image_path);
        //         }
        //     }
        //     $productMaster->delete();
        //     $message = 'Produk ' . $productMaster->name . ' telah dihapus permanen beserta seluruh datanya.';
        // }
        $this->productRepository->clearCache(auth()->id());
        return redirect()->route('product')->with('message', $message);
    }

    public function destroy_all(Request $request)
    {
        $this->authorize('delete', ProductModel::class);
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
        return redirect()->route('product')->with('message', 'Data tabel Produk berhasil diperbarui.');
    }
}
