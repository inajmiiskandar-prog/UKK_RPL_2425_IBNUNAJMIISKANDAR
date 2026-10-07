<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Pop;
use App\Models\Olt;
use App\Models\Odp;
use App\Models\Ont;
use App\Models\PortPon;
use App\Models\Fab;
use App\Models\Baa;
use App\Models\Paket;
use App\Models\Material;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with statistics.
     */
    public function index()
    {
        // Count statistics
        $stats = [
            'total_area' => Area::count(),
            'total_pop' => Pop::count(),
            'total_olt' => Olt::count(),
            'total_odp' => Odp::count(),
            'total_ont' => Ont::count(),
            'total_portpon' => PortPon::count(),
            'total_pelanggan' => Fab::count(),
            'total_pelanggan_aktif' => Fab::where('status', 'AKTIF')->count(),
            'total_pelanggan_open' => Fab::where('status', 'OPEN')->count(),
            'total_baa' => Baa::count(),
            'total_paket' => Paket::count(),
            'total_material' => Material::count(),
            'total_user' => User::where('status', true)->count(),
        ];

        // ONT Status
        $ont_status = [
            'tersedia' => Ont::where('status', 'TERSEDIA')->count(),
            'terpasang' => Ont::where('status', 'TERPASANG')->count(),
            'rusak' => Ont::where('status', 'RUSAK')->count(),
        ];

        // Port PON Status
        $port_status = [
            'tersedia' => PortPon::where('status', 'TERSEDIA')->count(),
            'terpasang' => PortPon::where('status', 'TERPASANG')->count(),
            'rusak' => PortPon::where('status', 'RUSAK')->count(),
        ];

        // Recent Activity (last 10)
        $recent_activity = \App\Models\ActivityLog::with('user')
            ->orderBy('createdAt', 'desc')
            ->limit(10)
            ->get();

        // Recent FAB / Customers (last 5)
        $recent_customers = Fab::with(['area', 'paket', 'sales'])
            ->orderBy('createdAt', 'desc')
            ->limit(5)
            ->get();

        // Recent BAA / Installation (last 5)
        $recent_installations = Baa::with(['fab', 'teknisi'])
            ->orderBy('createdAt', 'desc')
            ->limit(5)
            ->get();

        // Monthly FAB stats (last 6 months) - SQLite compatible using Carbon ranges
        $monthly_fab = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();
            $count = Fab::whereBetween('createdAt', [$start, $end])->count();
            $monthly_fab[] = (object) [
                'month' => (int) $date->format('m'),
                'year' => (int) $date->format('Y'),
                'total' => $count,
            ];
        }

        // Monthly BAA stats (last 6 months) - SQLite compatible using Carbon ranges
        $monthly_baa = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();
            $count = Baa::whereBetween('createdAt', [$start, $end])->count();
            $monthly_baa[] = (object) [
                'month' => (int) $date->format('m'),
                'year' => (int) $date->format('Y'),
                'total' => $count,
            ];
        }

        // Low stock materials
        $low_stock_materials = Material::whereColumn('stok', '<=', 'minimal_stok')
            ->where('kondisi', 'BAIK')
            ->orderBy('stok')
            ->limit(5)
            ->get();

        // SLA pelanggan FAB yang masih OPEN/pending
        $pending_fabs = Fab::with(['area', 'paket'])
            ->where('status', 'OPEN')
            ->orderBy('createdAt')
            ->limit(8)
            ->get();
        $pending_fab_count = Fab::where('status', 'OPEN')->count();

        // Map data - POP, OLT, ODP, FAB with coordinates
        $mapData = [
            'pops' => Pop::whereNotNull('latitude')->where('latitude', '!=', '')->whereNotNull('longitude')->where('longitude', '!=', '')->get(['kode_pop', 'nama_pop', 'latitude', 'longitude', 'alamat']),
            'olts' => Olt::whereNotNull('latitude')->where('latitude', '!=', '')->whereNotNull('longitude')->where('longitude', '!=', '')->get(['kode_olt', 'nama_olt', 'latitude', 'longitude', 'lokasi']),
            'odps' => Odp::whereNotNull('latitude')->where('latitude', '!=', '')->whereNotNull('longitude')->where('longitude', '!=', '')->get(['kode_odp', 'nama_odp', 'latitude', 'longitude', 'alamat']),
            'fabs' => Fab::whereNotNull('latitude')->where('latitude', '!=', '')->whereNotNull('longitude')->where('longitude', '!=', '')->get(['kode_fab', 'nama_pelanggan', 'latitude', 'longitude', 'alamat', 'status']),
        ];

        return view('dashboard', compact(
            'stats',
            'ont_status',
            'port_status',
            'recent_activity',
            'recent_customers',
            'recent_installations',
            'monthly_fab',
            'monthly_baa',
            'low_stock_materials',
            'pending_fabs',
            'pending_fab_count',
            'mapData'
        ));
    }
}
