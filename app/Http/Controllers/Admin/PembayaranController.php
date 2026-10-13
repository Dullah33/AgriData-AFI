<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\TransisiStatusTidakValidException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketplace\AlasanRequest;
use App\Models\Pembayaran;
use App\Services\Marketplace\PembayaranService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Verifikasi pembayaran transfer manual oleh Admin. Pembayaran Midtrans
 * berubah status otomatis lewat webhook, di sini hanya bisa dipantau.
 */
class PembayaranController extends Controller
{
    public function __construct(private PembayaranService $layanan)
    {
    }

    // GET /admin/pembayaran?status=menunggu_verifikasi
    public function index(Request $request)
    {
        $status = $request->query('status', Pembayaran::MENUNGGU_VERIFIKASI);
        $status = array_key_exists($status, Pembayaran::LABEL_STATUS) || $status === 'semua' ? $status : Pembayaran::MENUNGGU_VERIFIKASI;

        $jumlahPerStatus = Pembayaran::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        $pembayarans = Pembayaran::with(['pembeli', 'transaksis'])
            ->when($status !== 'semua', fn ($q) => $q->status($status))
            // Yang paling lama menunggu verifikasi tampil paling atas
            ->orderBy($status === Pembayaran::MENUNGGU_VERIFIKASI ? 'bukti_diunggah_at' : 'created_at', $status === Pembayaran::MENUNGGU_VERIFIKASI ? 'asc' : 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.pembayaran.index', compact('pembayarans', 'status', 'jumlahPerStatus'));
    }

    // GET /admin/pembayaran/{kode}
    public function show(Pembayaran $pembayaran)
    {
        $pembayaran->load(['pembeli', 'verifikator', 'transaksis.produk.petani']);

        return view('admin.pembayaran.show', compact('pembayaran'));
    }

    // GET /admin/pembayaran/{kode}/bukti
    public function bukti(Pembayaran $pembayaran)
    {
        abort_unless($pembayaran->bukti_transfer && Storage::disk(PembayaranService::DISK_BUKTI)->exists($pembayaran->bukti_transfer), 404);

        return Storage::disk(PembayaranService::DISK_BUKTI)->response($pembayaran->bukti_transfer);
    }

    // POST /admin/pembayaran/{kode}/verifikasi
    public function verifikasi(Pembayaran $pembayaran)
    {
        try {
            $this->layanan->konfirmasiLunas($pembayaran, Auth::user());
        } catch (TransisiStatusTidakValidException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.pembayaran.index')
            ->with('success', "Pembayaran {$pembayaran->kode} dinyatakan lunas. Pesanan diteruskan ke petani.");
    }

    // POST /admin/pembayaran/{kode}/tolak
    public function tolak(AlasanRequest $request, Pembayaran $pembayaran)
    {
        try {
            $this->layanan->tolakBukti($pembayaran, Auth::user(), $request->validated('alasan'));
        } catch (TransisiStatusTidakValidException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.pembayaran.index')
            ->with('success', "Bukti transfer {$pembayaran->kode} ditolak. Pembeli diminta mengunggah ulang.");
    }
}
