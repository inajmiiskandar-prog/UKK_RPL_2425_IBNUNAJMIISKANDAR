<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('search') && $request->search) {
            $query->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('username', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('nama')->paginate(10);
        $users->appends($request->all());

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
            'jkl' => 'required|in:LAKI_LAKI,PEREMPUAN',
            'role' => 'required|in:ADMIN,LEADER,SALES,TEKNISI,LOGISTIK',
            'no_hp' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:users,email',
            'status' => 'required|in:0,1',
        ], [
            'nama.required' => 'Nama wajib diisi!',
            'username.required' => 'Username wajib diisi!',
            'username.unique' => 'Username sudah digunakan!',
            'password.required' => 'Password wajib diisi!',
            'password.min' => 'Password minimal 6 karakter!',
            'password.confirmed' => 'Konfirmasi password tidak cocok!',
            'jkl.required' => 'Jenis Kelamin wajib dipilih!',
            'role.required' => 'Role wajib dipilih!',
        ]);

        try {
            DB::beginTransaction();

            // Generate kode_user
            $lastUser = User::orderBy('id_user', 'desc')->first();
            $lastNumber = $lastUser ? (int) substr($lastUser->kode_user, 3) : 0;
            $kodeUser = 'USR' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

            User::create([
                'kode_user' => $kodeUser,
                'nama' => $request->nama,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'jkl' => $request->jkl,
                'role' => $request->role,
                'no_hp' => $request->no_hp,
                'email' => $request->email,
                'status' => $request->status,
            ]);

            \App\Models\ActivityLog::log('USER_CREATED', "Menambah User: {$request->nama}", auth()->id());
            DB::commit();

            return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan User: ' . $e->getMessage());
        }
    }

    public function show(User $user)
    {
        $user->load('fabsSales', 'baas');
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username,' . $user->id_user . ',id_user',
            'password' => 'nullable|string|min:6|confirmed',
            'jkl' => 'required|in:LAKI_LAKI,PEREMPUAN',
            'role' => 'required|in:ADMIN,LEADER,SALES,TEKNISI,LOGISTIK',
            'no_hp' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:users,email,' . $user->id_user . ',id_user',
            'status' => 'required|in:0,1',
        ]);

        try {
            DB::beginTransaction();

            $data = [
                'nama' => $request->nama,
                'username' => $request->username,
                'jkl' => $request->jkl,
                'role' => $request->role,
                'no_hp' => $request->no_hp,
                'email' => $request->email,
                'status' => $request->status,
            ];

            if ($request->password) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            \App\Models\ActivityLog::log('USER_UPDATED', "Mengubah User: {$user->nama}", auth()->id());
            DB::commit();

            return redirect()->route('users.index')->with('success', 'User berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui User: ' . $e->getMessage());
        }
    }

    public function destroy(User $user)
    {
        try {
            DB::beginTransaction();

            if ($user->id_user === auth()->id()) {
                return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun sendiri!');
            }

            if ($user->fabs()->count() > 0 || $user->baas()->count() > 0) {
                // Soft delete by setting status to false
                $user->update(['status' => false]);
                \App\Models\ActivityLog::log('USER_DEACTIVATED', "Menonaktifkan User: {$user->nama}", auth()->id());
                DB::commit();
                return redirect()->route('users.index')->with('success', 'User berhasil dinonaktifkan!');
            }

            $namaUser = $user->nama;
            $user->delete();

            \App\Models\ActivityLog::log('USER_DELETED', "Menghapus User: {$namaUser}", auth()->id());
            DB::commit();

            return redirect()->route('users.index')->with('success', 'User berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus User: ' . $e->getMessage());
        }
    }
}
