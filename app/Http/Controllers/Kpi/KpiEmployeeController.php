<?php

/**
 * Controller untuk Data Karyawan
 * Halaman ini BUKAN tabel baru - mengambil data dari tabel users yang sudah ada
 * Hanya menambahkan filter dan tampilan khusus untuk data KPI employee
 *
 * Menu: Master Data -> Data Karyawan
 */

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class KpiEmployeeController extends Controller
{
    /**
     * Menampilkan daftar semua karyawan
     * Ditampilkan dalam format yang fokus ke data KPI: nik, divisi, jabatan, atasan, role
     */
    public function index(Request $request)
    {
        $query = User::query()->with('atasan');

        // Filter pencarian
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('divisi', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter divisi
        if ($divisi = $request->input('divisi')) {
            $query->where('divisi', $divisi);
        }

        // Filter status
        if ($status = $request->input('status')) {
            $query->where('status', $status === 'aktif');
        }

        // Urutkan
        $sortBy = $request->input('sort_by', 'nama');
        $sortDir = $request->input('sort_dir', 'asc');
        $allowedSorts = ['nama', 'nik', 'divisi', 'jabatan', 'role', 'status'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('nama');
        }

        $employees = $query->paginate(15);
        $employees->appends($request->all());

        // Ambil daftar divisi unik untuk filter dropdown
        $divisis = User::whereNotNull('divisi')
                       ->where('divisi', '!=', '')
                       ->distinct()
                       ->orderBy('divisi')
                       ->pluck('divisi');

        return view('kpi.employee.index', compact('employees', 'divisis'));
    }

    /**
     * Form tambah karyawan baru
     */
    public function create()
    {
        // Semua user untuk dropdown atasan
        $allUsers = User::where('status', true)
                       ->orderBy('nama')
                       ->get();

        return view('kpi.employee.create', compact('allUsers'));
    }

    /**
     * Simpan karyawan baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'nik' => 'nullable|string|max:20',
            'divisi' => 'nullable|string|max:100',
            'jabatan' => 'nullable|string|max:100',
            'alamat' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:20',
            'tanggal_masuk' => 'nullable|date',
            'role' => ['required', Rule::in(['ADMIN', 'LEADER', 'SALES', 'TEKNISI', 'LOGISTIK'])],
            'password' => 'required|min:6',
        ], [
            'nama.required' => 'Nama wajib diisi!',
            'role.required' => 'Role wajib dipilih!',
            'password.required' => 'Password wajib diisi!',
            'password.min' => 'Password minimal 6 karakter!',
        ]);

        try {
            DB::beginTransaction();

            // Generate kode user otomatis - ROBUST parser menggunakan regex
            $lastUser = User::orderBy('id_user', 'desc')->first();
            $lastNumber = $lastUser
                ? \App\Helpers\KodeGenerator::extractNumber($lastUser->kode_user, 'USR') ?? 0
                : 0;
            $kodeUser = 'USR' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

            // Generate kode_karyawan otomatis dengan logika gap filling, tanpa input manual
            $kodeKaryawan = \App\Helpers\KodeGenerator::generateWithGapFilling('users', 'kode_karyawan', 'EMP', 3);
            while (User::where('kode_karyawan', $kodeKaryawan)->exists()) {
                $kodeKaryawan = \App\Helpers\KodeGenerator::generateWithGapFilling('users', 'kode_karyawan', 'EMP', 3);
            }

            // Generate unique email
            $baseEmail = Str::slug($request->nama, '.') . '@passnet.local';
            $email = $baseEmail;
            $counter = 1;
            while (User::where('email', $email)->exists()) {
                $email = Str::slug($request->nama, '.') . '.' . $counter . '@passnet.local';
                $counter++;
            }

            $employee = User::create([
                'kode_user' => $kodeUser,
                'kode_karyawan' => $kodeKaryawan,
                'nama' => $request->nama,
                'email' => $email,
                'password' => $request->password,
                'nik' => $request->nik,
                'role' => $request->role,
                'divisi' => $request->divisi,
                'jabatan' => $request->jabatan,
                'alamat' => $request->alamat,
                'no_hp' => $request->telepon,
                'tanggal_masuk' => $request->tanggal_masuk,
                'no_telp' => $request->telepon,
                'join_date' => $request->tanggal_masuk,
                'employee_status' => 'Active',
                'status' => true,
            ]);

            \App\Models\ActivityLog::log(
                'KPI_EMPLOYEE_CREATED',
                "Menambah karyawan baru: {$request->nama} ({$request->role})",
                auth()->user()->id_user
            );

            DB::commit();

            return redirect()->route('kpi.employee.index')
                           ->with('success', "Karyawan berhasil ditambahkan!");

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating employee: ' . $e->getMessage());
            return redirect()->back()->withInput()
                           ->with('error', 'Gagal menambahkan karyawan: ' . $e->getMessage());
        }
    }

    /**
     * Detail satu karyawan
     */
    public function show(User $employee)
    {
        $employee->load(['atasan', 'bawahan', 'kpiAssessments']);

        // Ambil histori KPI (diurutkan berdasarkan created_at)
        $kpiHistory = $employee->kpiAssessments()
                              ->orderByDesc('created_at')
                              ->limit(12)
                              ->get();

        // Statistik KPI
        $stats = [
            'total_assessments' => $kpiHistory->count(),
            'completed' => $kpiHistory->where('status', 'selesai')->count(),
            'avg_score' => $kpiHistory->whereNotNull('skor_akhir')->avg('skor_akhir'),
            'best_score' => $kpiHistory->whereNotNull('skor_akhir')->max('skor_akhir'),
            'total_bawahan' => $employee->bawahan()->count(),
        ];

        return view('kpi.employee.show', compact('employee', 'kpiHistory', 'stats'));
    }

    /**
     * Form edit data karyawan (khusus ADMIN)
     */
    public function edit(User $employee)
    {
        // Semua user untuk dropdown atasan (kecuali diri sendiri)
        $allUsers = User::where('id_user', '!=', $employee->id_user)
                       ->where('status', true)
                       ->orderBy('nama')
                       ->get();

        return view('kpi.employee.edit', compact('employee', 'allUsers'));
    }

    /**
     * Update data karyawan
     */
    public function update(Request $request, User $employee)
    {
        // 5 role PassOne: ADMIN, LEADER, SALES, TEKNISI, LOGISTIK
        $allowedRoles = ['ADMIN', 'LEADER', 'SALES', 'TEKNISI', 'LOGISTIK'];
        $roleOptions = $allowedRoles;

        $request->validate([
            'nik' => 'nullable|string|max:20|unique:users,nik,' . $employee->id_user . ',id_user',
            'role' => ['required', Rule::in($roleOptions)],
            'divisi' => 'nullable|string|max:100',
            'jabatan' => 'nullable|string|max:100',
            'alamat' => 'nullable|string|max:255',
            'no_telp' => 'nullable|string|max:20',
            'join_date' => 'nullable|date',
            'atasan_id' => 'nullable|exists:users,id_user',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'nik.unique' => 'NIK sudah digunakan oleh karyawan lain!',
            'atasan_id.exists' => 'Atasan yang dipilih tidak valid!',
            'role.required' => 'Role wajib dipilih!',
        ]);

        // Prevent circular reference (atasan tidak bisa diri sendiri)
        if ($request->atasan_id == $employee->id_user) {
            return redirect()->back()->withInput()
                           ->with('error', 'Atasan tidak bisa diri sendiri!');
        }

        try {
            DB::beginTransaction();

            // Siapkan data untuk diupdate
            $data = [
                'nik' => $request->nik,
                'role' => $request->role,
                'divisi' => $request->divisi,
                'jabatan' => $request->jabatan,
                'alamat' => $request->alamat,
                'no_telp' => $request->no_telp,
                'join_date' => $request->join_date,
                'atasan_id' => $request->atasan_id ?: null,
            ];

            // Handle foto upload - hanya jika ada file baru yang diupload
            if ($request->hasFile('foto')) {
                // Hapus foto lama jika ada
                if ($employee->foto && \Storage::disk('public')->exists($employee->foto)) {
                    \Storage::disk('public')->delete($employee->foto);
                }
                // Simpan foto baru
                $data['foto'] = $request->file('foto')->store('user-photos', 'public');
            }

            $employee->update($data);

            \App\Models\ActivityLog::log(
                'KPI_EMPLOYEE_UPDATED',
                "Mengubah data karyawan: {$employee->nama}",
                auth()->user()->id_user
            );

            DB::commit();

            return redirect()->route('kpi.employee.index')
                           ->with('success', 'Data karyawan berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                           ->with('error', 'Gagal memperbarui: ' . $e->getMessage());
        }
    }

    /**
     * Reset password karyawan
     */
    public function resetPassword(Request $request, User $employee)
    {
        try {
            DB::beginTransaction();

            // Generate password baru (8 karakter)
            $newPassword = Str::random(8);

            $employee->update([
                'password' => $newPassword, // Cast 'hashed' di User model akan auto-hash
            ]);

            \App\Models\ActivityLog::log(
                'KPI_EMPLOYEE_PASSWORD_RESET',
                "Reset password karyawan: {$employee->nama}",
                auth()->user()->id_user
            );

            DB::commit();

            return redirect()->route('kpi.employee.show', $employee->id_user)
                           ->with('success', 'Password berhasil di-reset!')
                           ->with('new_password', $newPassword);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->with('error', 'Gagal reset password: ' . $e->getMessage());
        }
    }

    /**
     * Update status karyawan (aktif/nonaktif)
     */
    public function toggleStatus(Request $request, User $employee)
    {
        try {
            DB::beginTransaction();

            $newStatus = !$employee->status;
            $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

            $employee->update(['status' => $newStatus]);

            \App\Models\ActivityLog::log(
                'KPI_EMPLOYEE_STATUS_CHANGED',
                "Merubah status karyawan {$employee->nama} menjadi " . ($newStatus ? 'Aktif' : 'Nonaktif'),
                auth()->user()->id_user
            );

            DB::commit();

            return redirect()->back()
                           ->with('success', "Karyawan berhasil {$statusText}!");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }

    /**
     * Export data karyawan ke format Excel (HTML table)
     *
     * Akses: ADMIN dan HR saja
     * Parameter:
     * - ids: daftar id_user yang dipilih (prioritas jika ada)
     * - search: filter berdasarkan nama, kode_user, kode_karyawan, nik, divisi, jabatan
     */
    public function export(Request $request)
    {
        // Cek hak akses: ADMIN dan HR saja
        $user = auth()->user();
        if (!in_array($user->role, ['ADMIN', 'HR'])) {
            abort(403, 'Anda tidak memiliki akses untuk export data karyawan!');
        }

        $query = User::query()->with('atasan');

        // Prioritas: jika ada ids, gunakan ids tersebut
        if ($request->filled('ids')) {
            $ids = is_array($request->ids) ? $request->ids : explode(',', $request->ids);
            $query->whereIn('id_user', $ids);
        }
        // Jika tidak ada ids, gunakan filter search
        elseif ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode_user', 'like', "%{$search}%")
                  ->orWhere('kode_karyawan', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('divisi', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $employees = $query->orderBy('nama')->get();

        // Jika tidak ada data, kembalikan pesan
        if ($employees->isEmpty()) {
            return back()->with('error', 'Tidak ada data karyawan yang sesuai dengan filter!');
        }

        // Generate HTML untuk Excel
        $html = $this->generateExportHtml($employees);

        $filename = "Data_Karyawan_" . date('Ymd_His') . ".xls";

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Generate HTML untuk export Excel
     */
    private function generateExportHtml($employees)
    {
        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $html .= '<head><meta charset="utf-8"></head><body>';
        $html .= '<table border="1">';

        // Title
        $html .= '<tr><td colspan="12" style="font-weight:bold;font-size:14pt;">DATA KARYAWAN</td></tr>';
        $html .= '<tr><td colspan="12">Dicetak pada: ' . date('d/m/Y H:i:s') . '</td></tr>';
        $html .= '<tr></tr>';

        // Header
        $headers = ['No', 'Kode Karyawan', 'Nama', 'Username', 'Email', 'No HP', 'NIK', 'Divisi', 'Jabatan', 'Atasan', 'Role', 'Status'];
        $html .= '<tr>';
        foreach ($headers as $h) {
            $html .= "<th style=\"background:#9333ea;color:white;\">{$h}</th>";
        }
        $html .= '</tr>';

        // Data
        $no = 1;
        foreach ($employees as $emp) {
            $bg = $no % 2 === 0 ? '#f3e8ff' : '#ffffff';

            $kodeKaryawan = $emp->kode_karyawan ?: ($emp->kode_user ?: '-');
            $noHp = $emp->no_hp ?: ($emp->no_telp ?: '-');
            $atasanNama = $emp->atasan ? $emp->atasan->nama : '-';

            $html .= "<tr style=\"background:{$bg}\">";
            $html .= "<td>{$no}</td>";
            $html .= "<td>{$kodeKaryawan}</td>";
            $html .= "<td>{$emp->nama}</td>";
            $html .= "<td>" . ($emp->username ?: '-') . "</td>";
            $html .= "<td>{$emp->email}</td>";
            $html .= "<td>{$noHp}</td>";
            $html .= "<td>" . ($emp->nik ?: '-') . "</td>";
            $html .= "<td>" . ($emp->divisi ?: '-') . "</td>";
            $html .= "<td>" . ($emp->jabatan ?: '-') . "</td>";
            $html .= "<td>{$atasanNama}</td>";
            $html .= "<td>{$emp->role}</td>";
            $html .= "<td>" . ($emp->status ? 'Active' : 'Nonaktif') . "</td>";
            $html .= '</tr>';
            $no++;
        }

        // Footer
        $html .= '<tr></tr>';
        $html .= "<tr><td colspan=\"12\">Total: {$employees->count()} karyawan</td></tr>";

        $html .= '</table></body></html>';

        return $html;
    }
}
