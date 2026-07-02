<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use app\Http\Controllers\DashboardController;

class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard sesuai role user yang sedang login.
     *
     * Satu route /dashboard untuk semua role, tapi view yang dirender
     * berbeda-beda tergantung kolom `role` pada user. Pendekatan ini
     * dipilih supaya link "Dashboard" di mana pun selalu konsisten
     * mengarah ke /dashboard, tanpa perlu tahu role user di awal.
     */
    public function index()
    {
        $role = Auth::user()->role;

        return match ($role) {
            'admin'    => view('dashboard.admin'),
            'petani'   => view('dashboard.petani'),
            'penyuluh' => view('dashboard.penyuluh'),
            'user'     => view('dashboard.user'),
            default    => abort(403, 'Role tidak dikenali.'),
        };
    }
}