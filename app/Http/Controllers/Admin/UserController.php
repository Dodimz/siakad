<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fakultas;
use App\Models\Kampus;
use App\Models\User;
use App\Services\UserService;
use App\Support\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Check if user is superadmin, redirect if not
     */
    private function authorizeSuperAdmin(): void
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(response()->view('errors.403', ['message' => 'Hanya superadmin yang dapat mengelola user.'], 403));
        }
    }

    public function index(Request $request)
    {
        $this->authorizeSuperAdmin();

        $query = User::with(['fakultas', 'mahasiswa.prodi', 'dosen.prodi', 'kampus']);

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        // Faculty scoping for admin_fakultas
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $fakultasId = $request->get('fakultas_scope');
            $query->where(function ($q) use ($fakultasId) {
                $q->where('fakultas_id', $fakultasId)
                    ->orWhereHas('mahasiswa', fn ($m) => $m->whereHas('prodi', fn ($p) => $p->where('fakultas_id', $fakultasId)))
                    ->orWhereHas('dosen', fn ($d) => $d->whereHas('prodi', fn ($p) => $p->where('fakultas_id', $fakultasId)));
            });
        }

        $users = $query->orderBy('name')->paginate(config('siakad.pagination', 15))->withQueryString();
        $fakultasList = Fakultas::orderBy('nama')->get();

        $kampusList = Kampus::active()->orderBy('nama')->get();

        $currentKampusId = app(TenantManager::class)->id();

        return view('admin.users.index', compact('users', 'fakultasList', 'kampusList', 'currentKampusId'));
    }

    public function store(Request $request)
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', Password::min(8)],
            'role' => 'required|in:superadmin,admin_fakultas,admin_kampus,dosen,mahasiswa',
            'fakultas_id' => 'nullable|exists:fakultas,id',
            'kampus_id' => 'required_if:role,admin_kampus|nullable|exists:kampus,id',
            // Polymorphic validation
            'nim' => 'required_if:role,mahasiswa|nullable|unique:mahasiswa,nim',
            'nidn' => 'required_if:role,dosen|nullable|unique:dosen,nidn',
            'prodi_id' => 'required_if:role,mahasiswa,dosen|nullable|exists:prodi,id',
            'angkatan' => 'required_if:role,mahasiswa|nullable|numeric',
        ]);

        if ($validated['role'] !== 'admin_kampus') {
            unset($validated['kampus_id']);
        }

        try {
            $user = $this->userService->createUser($validated);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'User created', 'data' => $user], 201);
            }

            return back()->with('success', 'User berhasil ditambahkan.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 400);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => ['nullable', 'string', Password::min(8)],
            'role' => 'required|in:superadmin,admin_fakultas,admin_kampus,dosen,mahasiswa',
            'fakultas_id' => 'nullable|exists:fakultas,id',
            'kampus_id' => 'required_if:role,admin_kampus|nullable|exists:kampus,id',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'fakultas_id' => $validated['fakultas_id'] ?? null,
        ];

        if ($validated['role'] === 'admin_kampus') {
            $updateData['kampus_id'] = $validated['kampus_id'];
        }

        $user->update($updateData);

        if (! empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        return back()->with('success', 'User berhasil diupdate.');
    }

    public function destroy(User $user)
    {
        $this->authorizeSuperAdmin();

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus akun sendiri.']);
        }

        // Prevent deletion of last superadmin
        if ($user->role === 'superadmin') {
            $superadminCount = User::where('role', 'superadmin')->count();
            if ($superadminCount <= 1) {
                return back()->withErrors(['error' => 'Tidak dapat menghapus superadmin terakhir.']);
            }
        }

        // Delete related records
        if ($user->mahasiswa) {
            $user->mahasiswa->delete();
        }
        if ($user->dosen) {
            $user->dosen->delete();
        }

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }
}
