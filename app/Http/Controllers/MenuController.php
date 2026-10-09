<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Halaman pertama: hanya menu utama (tanpa induk).
     * Anak menu ditampilkan di halaman detail (route menu.detail).
     */
    public function index()
    {
        $search = trim((string) request()->get('search', ''));

        $match = function ($query) use ($search) {
            $query->where(function ($qb) use ($search) {
                $qb->where('name', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%")
                    ->orWhere('icon', 'like', "%{$search}%");
            });
        };

        $menus = Menu::query()
            ->whereNull('parent_id')
            ->when($search !== '', function ($query) use ($search, $match) {
                // cari juga di anak; yang tampil induknya supaya hasil tetap di halaman root
                $parentIds = Menu::whereNotNull('parent_id')->when(true, $match)->pluck('parent_id');

                $query->where(function ($qb) use ($match, $parentIds) {
                    $match($qb);
                    if ($parentIds->isNotEmpty()) {
                        $qb->orWhereIn('id', $parentIds);
                    }
                });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('pages.menu.index', compact('menus', 'search'));
    }

    /**
     * Detail menu: info menu utama + daftar anak (sub menu) nya.
     */
    public function detail(string $id)
    {
        $menu = Menu::findOrFail($id);
        $children = Menu::where('parent_id', $menu->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('pages.menu.detail', compact('menu', 'children'));
    }

    /**
     * Form tambah menu.
     */
    public function create()
    {
        $menus = $this->parentOptions();
        $parentId = (int) request()->get('parent_id', 0);

        // prefill induk hanya boleh menu utama
        if ($parentId && !in_array($parentId, $menus->pluck('id')->all())) {
            $parentId = 0;
        }

        return view('pages.menu.create', compact('menus', 'parentId'));
    }

    /**
     * Simpan menu baru.
     */
    public function store(Request $request)
    {
        $data = $this->validated($request);
        $menu = Menu::create($data);

        if ($menu->parent_id) {
            return redirect()->route('menu.detail', $menu->parent_id)->with('success', 'Menu berhasil ditambahkan');
        }

        return redirect()->route('menu.index')->with('success', 'Menu berhasil ditambahkan');
    }

    /**
     * Form edit menu.
     */
    public function edit(string $id)
    {
        $menu = Menu::findOrFail($id);
        $menus = $this->parentOptions($menu->id);

        return view('pages.menu.edit', compact('menu', 'menus'));
    }

    /**
     * Update menu.
     */
    public function update(Request $request, string $id)
    {
        $menu = Menu::findOrFail($id);
        $data = $this->validated($request, $menu->id);

        // cegah menu menjadi induknya sendiri
        if ((int) ($data['parent_id'] ?? 0) === (int) $menu->id) {
            return back()->withInput()->withErrors(['parent_id' => 'Menu tidak bisa menjadi induknya sendiri.']);
        }

        $menu->update($data);

        if ($menu->parent_id) {
            return redirect()->route('menu.detail', $menu->parent_id)->with('success', 'Menu berhasil diupdate');
        }

        return redirect()->route('menu.index')->with('success', 'Menu berhasil diupdate');
    }

    /**
     * Toggle status aktif/non aktif menu (dipanggil AJAX dari tabel index).
     */
    public function toggle(Request $request, string $id)
    {
        $menu = Menu::findOrFail($id);

        // idempoten: ikuti nilai dari request (aman terhadap klik ganda / retry)
        $menu->is_active = $request->has('is_active') ? ($request->boolean('is_active') ? 1 : 0) : ($menu->is_active ? 0 : 1);
        $menu->save();

        return response()->json([
            'success' => true,
            'is_active' => (int) $menu->is_active,
            'message' => sprintf('Menu "%s" %s', $menu->name, $menu->is_active ? 'diaktifkan' : 'dinonaktifkan'),
            '_token' => csrf_token(),
        ]);
    }

    /**
     * Hapus menu (diblokir bila masih punya sub menu).
     */
    public function destroy(string $id)
    {
        $menu = Menu::findOrFail($id);
        $parentId = $menu->parent_id;

        if (Menu::where('parent_id', $menu->id)->exists()) {
            return redirect()->route('menu.index')
                ->with('error', sprintf('Menu "%s" masih memiliki sub menu. Pindahkan atau hapus sub menu dulu.', $menu->name));
        }

        \DB::table('permissions')->where('menu_id', $menu->id)->delete();
        $menu->delete();

        if ($parentId) {
            return redirect()->route('menu.detail', $parentId)->with('success', 'Menu berhasil dihapus');
        }

        return redirect()->route('menu.index')->with('success', 'Menu berhasil dihapus');
    }

    /**
     * Validasi form tambah / edit menu.
     */
    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'parent_id' => [
                'nullable',
                'integer',
                // Exists tidak punya ->ignore(); tolak sub menu & induknya sendiri lewat closure
                function ($attribute, $value, $fail) use ($ignoreId) {
                    if ((int) $value === (int) $ignoreId) {
                        $fail('Menu tidak bisa menjadi induknya sendiri.');
                        return;
                    }
                    if (!Menu::where('id', $value)->whereNull('parent_id')->exists()) {
                        $fail('Induk menu harus menu utama (tanpa induk).');
                    }
                },
            ],
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:255',
            'sort_order' => 'required|integer|min:0|max:9999',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'Nama menu wajib diisi.',
            'sort_order.required' => 'Urutan wajib diisi.',
            'sort_order.integer' => 'Urutan harus berupa angka.',
        ], [
            'parent_id' => 'induk menu',
            'name' => 'nama menu',
            'icon' => 'icon',
            'url' => 'url',
            'sort_order' => 'urutan',
        ]);

        $data['parent_id'] = $data['parent_id'] ?? null ?: null;
        $data['is_active'] = $request->boolean('is_active') ? 1 : 0;

        return $data;
    }

    /**
     * Opsi induk menu: hanya menu utama (parent_id kosong) supaya struktur tetap 2 level.
     */
    private function parentOptions(?int $ignoreId = null)
    {
        return Menu::whereNull('parent_id')
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }
}
