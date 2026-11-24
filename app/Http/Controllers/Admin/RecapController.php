<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RevenueService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dashboard → "Unduh rekap": PDF rekap pendapatan.
 * ?periode=2026-11 (sebulan, grafik per hari) atau ?periode=2026 (setahun, grafik per bulan).
 * Tanpa periode = bulan berjalan.
 */
class RecapController extends Controller
{
    public function __invoke(Request $request, RevenueService $revenue): Response
    {
        $periode = (string) $request->query('periode', now()->format('Y-m'));
        $today = CarbonImmutable::parse(now()->toDateString());

        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periode)) {
            $from = CarbonImmutable::createFromFormat('!Y-m-d', $periode.'-01');
            $to = $from->endOfMonth()->startOfDay();
            $yearly = false;
            $label = $from->translatedFormat('F Y');
        } elseif (preg_match('/^\d{4}$/', $periode)) {
            $from = CarbonImmutable::createFromFormat('!Y-m-d', $periode.'-01-01');
            $to = $from->endOfYear()->startOfDay();
            $yearly = true;
            $label = 'Tahun '.$periode;
        } else {
            abort(404);
        }

        $running = $today->betweenIncluded($from, $to);   // periode yang masih berjalan

        $data = $revenue->recap($from, $running ? $today : $to, $yearly) + [
            'label' => $label,
            'yearly' => $yearly,
            'range' => $from->translatedFormat('j F Y').' – '.($running ? $today : $to)->translatedFormat('j F Y'),
            'running' => $running,
            'previousLabel' => $yearly ? 'tahun '.($from->year - 1) : $from->subMonthNoOverflow()->translatedFormat('F Y'),
            'printedAt' => now()->translatedFormat('j F Y, H.i'),
        ];

        $pdf = Pdf::loadView('admin.documents.pdf.recap', $data)->setPaper('a4', 'portrait');

        // Nomor halaman "Halaman X dari Y" di kanan bawah setiap halaman
        $dompdf = $pdf->getDomPDF();
        $dompdf->render();
        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
        $canvas->page_text($canvas->get_width() - 98, $canvas->get_height() - 22, 'Halaman {PAGE_NUM} dari {PAGE_COUNT}', $font, 6.5, [0.55, 0.55, 0.55]);

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="rekap-pendapatan-'.$periode.'.pdf"',
        ]);
    }
}
