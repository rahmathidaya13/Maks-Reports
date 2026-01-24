<?php

namespace App\Http\Controllers\Authorization;

use Closure;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use App\Repositories\AuthorizationPermissions;

class PermissionController extends Controller
{
    protected $permissions;
    public function __construct(AuthorizationPermissions $permissionRepos)
    {
        $this->permissions = $permissionRepos;
    }
    public function index(Request $request)
    {
        $filters = $request->only([
            'keyword',
            'limit',
            'page',
            'order_by',
        ]);
        $permissions = $this->permissions->getCached(auth()->id(), $filters);
        return Inertia::render('Authorization/Permissions/Index', [
            'permissions' => $permissions,
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        $this->permissions->clearCache(auth()->id());
        return Inertia::render('Authorization/Permissions/Form');
    }
    public function store(Request $request)
    {

        $validated = $request->validate(
            [
                'name'   => 'required|array|min:1',
                'name.*' => 'required|array|min:1|distinct|unique:permissions,name',
            ],
            [
                // Untuk array utama
                'name.required' => 'Minimal harus mengisi daftar izin.',
                'name.array'    => 'Format izin tidak valid.',
                'name.min'      => 'Minimal harus ada 1 izin yang diinputkan.',

                // Untuk setiap item dalam array
                'name.*.required' => 'Izin wajib diisi.',
                'name.*.string'   => 'Format izin harus berupa teks.',
                'name.*.min'      => 'Nama izin minimal harus 1 karakter.',
                'name.*.distinct' => 'Nama izin tidak boleh duplikat dengan izin lain.',
                'name.*.unique'   => 'Nama izin ini sudah ada di database.',
            ]
        );


        $permissionNames = [];

        foreach ($validated['name'] as $name) {
            $nameOrigin = Str::snake($name);
            $nameChanges = Str::replace('_', '.', $nameOrigin);
            Permission::create([
                'name' => $nameChanges,
            ]);
            $permissionNames[] = $nameChanges;
        }
        $this->permissions->clearCache(auth()->id());
        $message = count($permissionNames) . " izin akses berhasil ditambahkan: " . implode(', ', $permissionNames);
        return redirect()->route('permissions')->with('message', $message);
    }

    public function show(Permission $permission, string $id)
    {
        $permissions = $permission::findOrFail($id);
        $this->permissions->clearCache(auth()->id());
        return response()->json([
            'status' => true,
            'permissions' => $permissions,
        ]);
    }

    public function edit(string $id)
    {
        $permissions = Permission::findOrFail($id);
        $this->permissions->clearCache(auth()->id());
        return Inertia::render('Authorization/Permissions/Form', compact('permissions'));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate(
            [
                'name' => 'required|array|min:1|unique:permissions,name,' . $id . ',.id',
                'name.*' => 'required|array|min:1|distinct|unique:permissions,name,' . $id . ',.id',
            ],
            [
                 'name.required' => 'Minimal harus mengisi daftar izin.',
                'name.array'    => 'Format izin tidak valid.',
                'name.min'      => 'Minimal harus ada 1 izin yang diinputkan.',

                // Untuk setiap item dalam array
                'name.*.required' => 'Izin wajib diisi.',
                'name.*.array'   => 'Salah satu izin akses harus dipilih.',
                'name.*.min'      => 'Nama izin minimal harus 1 karakter.',
                'name.*.distinct' => 'Nama izin tidak boleh duplikat dengan izin lain.',
                'name.*.unique'   => 'Nama izin ini sudah ada di database.',
            ]
        );
        $nameOrigin = Str::snake($validated['name']);
        $nameChanges = Str::replace('_', '.', $nameOrigin);

        $permission = Permission::findOrFail($id);
        $permission->name = $nameChanges;
        $permission->guard_name = $validated['guard_name'];
        $permission->update();

        $this->permissions->clearCache(auth()->id());
        return redirect()->route('permissions')->with('message', 'Permission ' . $validated['name'] . ' Berhasil diperbarui');
    }
    public function destroy(Permission $permission, string $id)
    {
        $permission::findOrFail($id)->delete();
        $this->permissions->clearCache(auth()->id());
        return redirect()->route('permissions')->with('message', 'Permission ' . $permission->name . ' Berhasil dihapus.');
    }

    public function destroyAll(Request $request)
    {
        $all_id = $request->input('all_id', []);
        if (!count($all_id)) return back()->with('message', 'Tidak ada data yang dipilih.');
        Permission::whereIn('id', $all_id)->delete();
        $this->permissions->clearCache(auth()->id());
        return redirect()->route('permissions')->with('message', count($all_id) . ' Data berhasil Terhapus.');
    }

    public function editMultiple(Request $request)
    {
        $all_id = $request->input('all_id', []);
        if (!count($all_id)) return back()->with('message', 'Tidak ada data yang dipilih.');
        $permissions = Permission::whereIn('id', $all_id)->get();
        $this->permissions->clearCache(auth()->id());
        return Inertia::render('Authorization/Permissions/Form', compact('permissions'));
    }

    public function storeMultiple(Request $request)
    {
        // 1. Normalisasi data input dulu sebelum divalidasi
        // Agar validasi unique mengecek string hasil akhir (contoh: "User Create" -> "user.create")
        $rawData = $request->input('permissions', []);
        $normalizedData = [];

        foreach ($rawData as $index => $item) {
            $normalizedData[$index] = $item;
            if (isset($item['name'])) {
                // Samakan format dengan yang akan disimpan ke DB
                $normalizedData[$index]['name'] = Str::replace('_', '.', Str::snake($item['name']));
            }
        }

        // Replace input request dengan data yang sudah dinormalisasi
        $request->merge(['permissions' => $normalizedData]);

        // 2. Validasi
        $validated = $request->validate([
            'permissions' => 'required|array|min:1',
            'permissions.*.id' => 'nullable|exists:permissions,id',
            'permissions.*.name' => [
                'required',
                'string',
                'min:1',
                'distinct', // Mencegah duplikat antar input (misal baris 1 & 2 sama)

                // Custom Rule untuk Unique yang Support Edit (Ignore ID)
                function (string $attribute, mixed $value, Closure $fail) use ($request) {
                    // Ambil index dari attribute (contoh: permissions.0.name -> index = 0)
                    $index = explode('.', $attribute)[1];
                    $currentItem = $request->input("permissions.{$index}");
                    $id = $currentItem['id'] ?? null;

                    // Query cek database
                    $query = Permission::where('name', $value);

                    // Jika ada ID (sedang Edit), abaikan ID tersebut
                    if ($id) {
                        $query->where('id', '!=', $id);
                    }

                    if ($query->exists()) {
                        $fail("Nama izin akses '{$value}' sudah digunakan.");
                    }
                },
            ],
        ], [
            'permissions.required' => 'Minimal harus ada satu izin akses.',
            'permissions.array'    => 'Format data izin akses tidak valid.',
            'permissions.min'      => 'Minimal harus mengisi satu izin akses.',
            'permissions.*.id.exists' => 'Data izin akses tidak ditemukan di database.',
            'permissions.*.name.required' => 'Nama izin akses wajib diisi.',
            'permissions.*.name.string'   => 'Nama izin akses harus berupa teks.',
            'permissions.*.name.min'      => 'Nama izin akses minimal 1 karakter.',
            'permissions.*.name.distinct' => 'Terdapat nama izin akses yang sama dalam formulir ini.',
        ]);

        // 3. Simpan Data (Gunakan Transaction agar aman)
        $permissionNames = [];

        DB::transaction(function () use ($validated, &$permissionNames) {
            foreach ($validated['permissions'] as $item) {
                // updateOrCreate akan menangani Create (jika id null) dan Update (jika id ada)
                $permission = Permission::updateOrCreate(
                    ['id' => $item['id'] ?? null],
                    ['name' => $item['name']] // Data sudah dinormalisasi di atas
                );
                $permissionNames[] = $permission->name;
            }
        });

        // 4. Clear Cache & Redirect
        // Pastikan $this->permissions merujuk ke service class yang benar, atau gunakan cache helper
        if (method_exists($this, 'permissions') && method_exists($this->permissions, 'clearCache')) {
            $this->permissions->clearCache(auth()->id());
        } else {
            // Fallback jika tidak menggunakan repository pattern khusus
            cache()->forget('spatie.permission.cache');
        }

        $message = count($permissionNames) . " izin akses berhasil diproses: " . implode(', ', $permissionNames);

        return redirect()->route('permissions')->with('message', $message);
    }

    public function reset()
    {
        $this->permissions->clearCache(auth()->id());
        return redirect()->route('permissions')->with('message', 'Data izin akses berhasil diperbarui.');
    }
}
