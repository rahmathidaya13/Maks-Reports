<?php

namespace App\Http\Controllers\Customer;

use App\Models\CustomerModel;
use App\Traits\CustomerValidation;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\CustomerRepository;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use CustomerValidation;
    protected $customerRepository;
    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }
    public function index(Request $request)
    {
        $this->authorize('view', CustomerModel::class);
        $filters = $request->only([
            'keyword',
            'limit',
            'order_by',
            'page',
        ]);
        $customers = $this->customerRepository->getCached(auth()->id(), $filters);
        return Inertia::render('Customer/Index', [
            'customers' => $customers,
            'filters' => $filters
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', CustomerModel::class);
        $this->customerRepository->clearCache(auth()->id());
        return Inertia::render('Customer/Form/pageForm');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', CustomerModel::class);
        $this->validationText($request->all());
        $customers = new CustomerModel();
        $customers->created_by = auth()->id();
        $customers->national_id_number = $request->input('national_id');
        $customers->customer_name = $request->input('customer_name');
        $customers->number_phone_customer = $request->input('number_phone');
        $customers->city = Str::upper($request->input('city'));
        $customers->province = Str::upper($request->input('province'));
        $customers->address = $request->input('address');
        $customers->save();
        $this->customerRepository->clearCache(auth()->id());
        return redirect()->route('customers')->with('message', 'Data pelanggan Baru ' . $customers->customer_name . ' berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->authorize('edit', CustomerModel::class);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->authorize('edit', CustomerModel::class);
        $customers = CustomerModel::findOrFail($id);
        $this->customerRepository->clearCache(auth()->id());
        return Inertia::render('Customer/Form/pageForm', [
            'customers' => $customers
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->authorize('edit', CustomerModel::class);
        $this->validationText($request->all(), $id);
        $customers = CustomerModel::findOrFail($id);
        $customers->created_by = auth()->id();
        $customers->national_id_number = $request->input('national_id');
        $customers->customer_name = $request->input('customer_name');
        $customers->number_phone_customer = $request->input('number_phone');
        $customers->city = Str::upper($request->input('city'));
        $customers->province = Str::upper($request->input('province'));
        $customers->address = $request->input('address');
        $customers->update();
        $this->customerRepository->clearCache(auth()->id());
        return redirect()->route('customers')->with('message', 'Data pelanggan ' . $customers->customer_name . ' berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete', CustomerModel::class);
        $customers = CustomerModel::findOrFail($id);
        $customers->delete();
        $this->customerRepository->clearCache(auth()->id());
        return redirect()->route('customers')->with('message', 'Data pelanggan ' . $customers->customer_name . ' berhasil dihapus.');
    }

    public function destroyAll(Request $request)
    {
        $this->authorize('delete', CustomerModel::class);
        $ids = $request->input('all_id', []);
        if (!count($ids)) return back()->with('message', 'Tidak ada data yang dipilih.');

        CustomerModel::whereIn('customer_id', $ids)->delete();
        $this->customerRepository->clearCache(auth()->id());
        return redirect()->route('customers')->with('message', count($ids) . ' Data berhasil Terhapus.');
    }
}
