<?php

namespace App\Http\Controllers;

use App\Exceptions\TransisiStatusTidakValidException;
use App\Services\Marketplace\MidtransGateway;
use App\Services\Marketplace\PembayaranService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * POST /webhook/midtrans
 *
 * Endpoint "Payment Notification URL" di dashboard Midtrans. Tidak memakai
 * login maupun CSRF; keasliannya dijamin lewat signature_key.
 */
class MidtransWebhookController extends Controller
{
    public function __invoke(Request $request, MidtransGateway $midtrans, PembayaranService $layanan)
    {
        $payload = $request->all();

        if (! $midtrans->signatureValid($payload)) {
            Log::warning('Midtrans: notifikasi dengan signature tidak valid ditolak.', [
                'order_id' => $payload['order_id'] ?? null,
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'Signature tidak valid.'], 403);
        }

        try {
            $layanan->prosesNotifikasiMidtrans($payload);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        } catch (TransisiStatusTidakValidException $e) {
            // Mis. dana masuk untuk pesanan yang sudah dibatalkan. Dijawab 200
            // supaya Midtrans berhenti mengirim ulang; kasus ini perlu
            // ditangani manual (refund) oleh Admin.
            Log::error('Midtrans: notifikasi tidak bisa diterapkan, perlu dicek manual.', [
                'order_id' => $payload['order_id'] ?? null,
                'transaction_status' => $payload['transaction_status'] ?? null,
                'alasan' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Diterima, perlu ditinjau manual.']);
        }

        return response()->json(['message' => 'OK']);
    }
}
