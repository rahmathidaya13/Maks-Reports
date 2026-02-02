<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ProductRequestUserModel;
use App\Repositories\AdminRequestProduct;

class AdminController extends Controller
{
    protected $adminRequestProductRepo;
    public function __construct(AdminRequestProduct $adminRequestProduct)
    {
        $this->adminRequestProductRepo = $adminRequestProduct;
        $this->middleware(['role:developer|role:admin']);
    }
    public function index(Request $request)
    {
        $filters = $request->only([
            'keyword',
            'limit',
            'page',
            'order_by',
        ]);
        $requestProduct = $this->adminRequestProductRepo->getCached(auth()->id(), $filters);
        return Inertia::render('Admin/ProductRequest/IndexRequest', [
            'products' => $requestProduct,
            'filters' => $filters
        ]);
    }
    public function update(Request $request, ProductRequestUserModel $productRequests, $id)
    {
        $this->adminRequestProductRepo->clearCache(auth()->id());
        $productRequest = $productRequests::find($id);
        $productRequest->status = $request->input('status');
        $productRequest->admin_note = $request->input('admin_note');
        $productRequest->update();
        return back()->with('message', 'Data berhasil diperbarui.');
    }

    public function reset()
    {
        $this->adminRequestProductRepo->clearCache(auth()->id());
        return back()->with('message', 'Data permintaan berhasil diperbarui.');
    }
}
