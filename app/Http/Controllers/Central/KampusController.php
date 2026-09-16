<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Kampus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KampusController extends Controller
{
    /**
     * Daftar seluruh kampus dengan ringkasan jumlah data.
     */
    public function index(): View
    {
        $kampus = Kampus::query()
            ->withCount(['fakultas', 'prodi', 'mahasiswa', 'dosen', 'users', 'calonMahasiswa'])
            ->orderBy('nama')
            ->get();

        return view('central.kampus.index', compact('kampus'));
    }

    /**
     * Form tambah kampus.
     */
    public function create(): View
    {
        return view('central.kampus.create');
    }

    /**
     * Simpan kampus baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $validated['slug'] = ($validated['slug'] ?? '') ?: Str::slug($validated['nama']);

        Kampus::create($validated);

        return redirect()->route('central.kampus.index')
            ->with('success', 'Kampus berhasil ditambahkan.');
    }

    /**
     * Form edit kampus.
     */
    public function edit(Kampus $kampus): View
    {
        return view('central.kampus.edit', compact('kampus'));
    }

    /**
     * Perbarui kampus.
     */
    public function update(Request $request, Kampus $kampus): RedirectResponse
    {
        $validated = $request->validate($this->rules($kampus));

        $validated['slug'] = ($validated['slug'] ?? '') ?: Str::slug($validated['nama']);

        $kampus->update($validated);

        return redirect()->route('central.kampus.index')
            ->with('success', 'Kampus berhasil diperbarui.');
    }

    /**
     * Hapus kampus beserta data terkait (cascade).
     */
    public function destroy(Kampus $kampus): RedirectResponse
    {
        $kampus->delete();

        return redirect()->route('central.kampus.index')
            ->with('success', 'Kampus berhasil dihapus.');
    }

    /**
     * Aturan validasi kampus.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function rules(?Kampus $kampus = null): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'alpha_dash', 'max:255', Rule::unique('kampus', 'slug')->ignore($kampus?->id)],
            'kode' => ['required', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', Rule::in(array_keys(Kampus::getStatusList()))],
        ];
    }
}
