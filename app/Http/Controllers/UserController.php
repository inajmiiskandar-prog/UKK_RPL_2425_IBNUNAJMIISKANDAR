<?php

namespace App\Http\Controllers;

use App\Helpers\KodeGenerator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Filter search
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('nama', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter role
        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        // Filter status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter divisi - ambil dari query parameter
        if ($request->has('divisi') && $request->divisi) {
            $query->where('divisi', $request->divisi);
        }

        // Ambil daftar divisi unik untuk dropdown filter
        $divisiList = User::whereNotNull('divisi')
                         ->where('divisi', '!=', '')
                         ->distinct()
                         ->orderBy('divisi')
                         ->pluck('divisi')
                         ->toBase(); // Convert to Collection for consistency

        $users = $query->orderBy('nama')->paginate(10);
        $users->appends($request->all());

        return view('users.index', compact('users', 'divisiList'));
    }

    public function create()
    {
        $atasanOptions = User::whereIn('role', ['ADMIN', 'LEADER'])
            ->orderBy('nama')
            ->get();

        return view('users.create', compact('atasanOptions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'jkl' => 'required|in:LAKI_LAKI,PEREMPUAN',
            'role' => 'required|in:ADMIN,LEADER,SALES,TEKNISI,LOGISTIK',
            'no_hp' => 'nullable|string|max:20',
            'telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:users,email',
            'status' => 'required|in:0,1',
            'nik' => 'nullable|string|max:20',
            'divisi' => 'nullable|string|max:100',
            'jabatan' => 'nullable|string|max:100',
            'alamat' => 'nullable|string|max:500',
            'tanggal_masuk' => 'nullable|date',
            'atasan_id' => 'nullable|exists:users,id_user',
        ], [
            'nama.required' => 'Nama wajib diisi!',
            'username.required' => 'Username wajib diisi!',
            'username.unique' => 'Username sudah digunakan!',
            'password.required' => 'Password wajib diisi!',
            'password.min' => 'Password minimal 6 karakter!',
            'password.confirmed' => 'Konfirmasi password tidak cocok!',
            'jkl.required' => 'Jenis Kelamin wajib dipilih!',
            'role.required' => 'Role wajib dipilih!',
            'atasan_id.exists' => 'Atasan yang dipilih tidak valid!',
        ]);

        try {
            DB::beginTransaction();

            $kodeUser = KodeGenerator::generateWithGapFilling('users', 'kode_user', 'USR', 3);
            $kodeKaryawan = KodeGenerator::generateWithGapFilling('users', 'kode_karyawan', 'EMP', 3);

            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('user-photos', 'public');
            }

            $telepon = $request->telepon ?: $request->no_hp;
            $email = $request->email ?: $request->username . '@passnet.local';

            User::create([
                'kode_user' => $kodeUser,
                'kode_karyawan' => $kodeKaryawan,
                'nama' => $request->nama,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'jkl' => $request->jkl,
                'role' => $request->role,
                'foto' => $fotoPath,
                'no_hp' => $telepon,
                'no_telp' => $telepon,
                'email' => $email,
                'status' => $request->status,
                'nik' => $request->nik,
                'divisi' => $request->divisi,
                'jabatan' => $request->jabatan,
                'alamat' => $request->alamat,
                'tanggal_masuk' => $request->tanggal_masuk,
                'join_date' => $request->tanggal_masuk,
                'atasan_id' => $request->atasan_id ?: null,
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
        $atasanOptions = User::whereIn('role', ['ADMIN', 'LEADER'])
            ->where('id_user', '!=', $user->id_user)
            ->orderBy('nama')
            ->get();

        return view('users.edit', compact('user', 'atasanOptions'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username,' . $user->id_user . ',id_user',
            'password' => 'nullable|string|min:6|confirmed',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'jkl' => 'required|in:LAKI_LAKI,PEREMPUAN',
            'role' => 'required|in:ADMIN,LEADER,SALES,TEKNISI,LOGISTIK',
            'no_hp' => 'nullable|string|max:20',
            'telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:users,email,' . $user->id_user . ',id_user',
            'status' => 'required|in:0,1',
            'nik' => 'nullable|string|max:20',
            'divisi' => 'nullable|string|max:100',
            'jabatan' => 'nullable|string|max:100',
            'alamat' => 'nullable|string|max:500',
            'tanggal_masuk' => 'nullable|date',
            'atasan_id' => ['nullable', 'exists:users,id_user', function ($attribute, $value, $fail) use ($user) {
                if ($value != null && (int) $value === (int) $user->id_user) {
                    $fail('Atasan tidak bisa sama dengan user itu sendiri!');
                }
            }],
        ]);

        try {
            DB::beginTransaction();

            $telepon = $request->telepon ?: $request->no_hp;

            $data = [
                'nama' => $request->nama,
                'username' => $request->username,
                'jkl' => $request->jkl,
                'role' => $request->role,
                'no_hp' => $telepon,
                'no_telp' => $telepon,
                'email' => $request->email,
                'status' => $request->status,
                'nik' => $request->nik,
                'divisi' => $request->divisi,
                'jabatan' => $request->jabatan,
                'alamat' => $request->alamat,
                'tanggal_masuk' => $request->tanggal_masuk,
                'join_date' => $request->tanggal_masuk,
                'atasan_id' => $request->atasan_id ?: null,
            ];

            if ($request->password) {
                $data['password'] = Hash::make($request->password);
            }

            if ($request->hasFile('foto')) {
                // delete old foto if exists
                if ($user->foto && \Storage::disk('public')->exists($user->foto)) {
                    \Storage::disk('public')->delete($user->foto);
                }
                $data['foto'] = $request->file('foto')->store('user-photos', 'public');
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
