<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\WebsiteSetting;
use App\Support\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as ResponseFacade;
use ZipArchive;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $report = $this->buildReportData($request, true);

        ActivityLogger::log('report', 'view', null, $report['filters']);

        return view('admin.reports.index', [
            ...$report,
            'statusOptions' => ['pending', 'confirmed', 'on_process', 'completed', 'cancelled'],
            'paymentStatusOptions' => ['pending', 'verified', 'rejected'],
        ]);
    }

    public function exportExcel(Request $request)
    {
        if (! class_exists(ZipArchive::class)) {
            return back()->withErrors(['report' => 'Export gagal: ekstensi ZipArchive tidak tersedia di server.']);
        }

        $report = $this->buildReportData($request, false);
        $filename = 'laporan-' . now()->format('Ymd-His') . '.xlsx';
        $dir = storage_path('app/exports');

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $path = $dir . DIRECTORY_SEPARATOR . $filename;
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->withErrors(['report' => 'Export gagal: tidak dapat membuat file XLSX.']);
        }

        $rows = [];
        $rows[] = ['Ringkasan Laporan'];
        $rows[] = ['Website', $report['siteName']];
        $rows[] = ['Logo Website', $report['siteLogoUrl'] ?: '-'];
        $rows[] = ['Rentang Tanggal', $report['from'] . ' s/d ' . $report['to']];
        $rows[] = ['Keyword', $report['keyword'] !== '' ? $report['keyword'] : '-'];
        $rows[] = ['Status Booking', $report['status'] !== '' ? $report['status'] : '-'];
        $rows[] = ['Status Pembayaran', $report['paymentStatus'] !== '' ? $report['paymentStatus'] : '-'];
        $rows[] = ['Total Booking', (string) $report['summary']['total_bookings']];
        $rows[] = ['Booking Pending', (string) $report['summary']['total_pending']];
        $rows[] = ['Booking Confirmed', (string) $report['summary']['total_confirmed']];
        $rows[] = ['Total Transaksi', (string) $report['summary']['total_transactions']];
        $rows[] = ['DP Verified', (string) $report['summary']['total_dp_verified']];
        $rows[] = ['DP Pending', (string) $report['summary']['total_dp_pending']];
        $rows[] = [];
        $rows[] = ['Rekap Booking'];
        $rows[] = ['Kode', 'Customer', 'Tanggal', 'Status', 'Total'];
        foreach ($report['bookings'] as $booking) {
            $rows[] = [
                (string) $booking->booking_code,
                (string) ($booking->customer?->name ?? ''),
                (string) ($booking->booking_date?->format('Y-m-d') ?? ''),
                (string) $booking->status,
                (string) $booking->grand_total,
            ];
        }

        $rows[] = [];
        $rows[] = ['Rekap Pembayaran'];
        $rows[] = ['Booking', 'Pembayar', 'Tipe', 'Status', 'Nominal'];
        foreach ($report['payments'] as $payment) {
            $rows[] = [
                (string) ($payment->booking?->booking_code ?? ''),
                (string) $payment->payer_name,
                (string) $payment->payment_type,
                (string) $payment->status,
                (string) $payment->amount,
            ];
        }

        $zip->addFromString('[Content_Types].xml', $this->xlsxContentTypesXml());
        $zip->addFromString('_rels/.rels', $this->xlsxRootRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->xlsxWorkbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->xlsxWorkbookRelsXml());
        $zip->addFromString('xl/styles.xml', $this->xlsxStylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->xlsxSheetXml($rows));
        $zip->close();

        ActivityLogger::log('report', 'export-excel', null, $report['filters']);

        return ResponseFacade::download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function exportPdf(Request $request)
    {
        $report = $this->buildReportData($request, false);

        ActivityLogger::log('report', 'export-pdf', null, $report['filters']);

        return response()->view('admin.reports.print', [
            ...$report,
            'mode' => 'pdf',
            'autoPrint' => true,
        ]);
    }

    public function print(Request $request)
    {
        $report = $this->buildReportData($request, false);

        ActivityLogger::log('report', 'print', null, $report['filters']);

        return response()->view('admin.reports.print', [
            ...$report,
            'mode' => 'print',
            'autoPrint' => true,
        ]);
    }

    private function buildReportData(Request $request, bool $paginate): array
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());
        $keyword = $request->string('q')->toString();
        $status = $request->string('status')->toString();
        $paymentStatus = $request->string('payment_status')->toString();

        $fromDate = Carbon::parse($from)->startOfDay();
        $toDate = Carbon::parse($to)->endOfDay();

        $bookingBaseQuery = Booking::query()->with('customer')->whereBetween('created_at', [$fromDate, $toDate]);
        $paymentBaseQuery = BookingPayment::query()
            ->with('booking.customer')
            ->whereBetween('created_at', [$fromDate, $toDate]);

        $this->applyBookingFilters($bookingBaseQuery, $keyword, $status);
        $this->applyPaymentFilters($paymentBaseQuery, $keyword, $status, $paymentStatus);

        $filters = [
            'from' => $from,
            'to' => $to,
            'q' => $keyword,
            'status' => $status,
            'payment_status' => $paymentStatus,
        ];

        $bookings = $paginate
            ? (clone $bookingBaseQuery)->latest()->paginate(20)->withQueryString()
            : (clone $bookingBaseQuery)->latest()->get();

        $payments = $paginate
            ? (clone $paymentBaseQuery)->latest()->paginate(20, ['*'], 'payments_page')->withQueryString()
            : (clone $paymentBaseQuery)->latest()->get();

        $websiteSetting = WebsiteSetting::query()->latest('id')->first();
        $siteName = $websiteSetting?->site_name ?: config('app.name', 'Website');
        $siteLogoUrl = $websiteSetting?->logo ? asset('storage/' . ltrim($websiteSetting->logo, '/')) : null;

        return [
            'siteName' => $siteName,
            'siteLogoUrl' => $siteLogoUrl,
            'from' => $from,
            'to' => $to,
            'keyword' => $keyword,
            'status' => $status,
            'paymentStatus' => $paymentStatus,
            'filters' => $filters,
            'summary' => [
                'total_bookings' => (clone $bookingBaseQuery)->count(),
                'total_pending' => (clone $bookingBaseQuery)->where('status', 'pending')->count(),
                'total_confirmed' => (clone $bookingBaseQuery)->where('status', 'confirmed')->count(),
                'total_transactions' => (float) (clone $bookingBaseQuery)->sum('grand_total'),
                'total_dp_verified' => (float) (clone $paymentBaseQuery)->where('payment_type', 'dp')->where('status', 'verified')->sum('amount'),
                'total_dp_pending' => (float) (clone $paymentBaseQuery)->where('payment_type', 'dp')->where('status', 'pending')->sum('amount'),
            ],
            'bookings' => $bookings,
            'payments' => $payments,
        ];
    }

    private function applyBookingFilters($query, string $keyword, string $status): void
    {
        $query->when($status !== '', fn ($inner) => $inner->where('status', $status))
            ->when($keyword !== '', function ($inner) use ($keyword) {
                $inner->where(function ($search) use ($keyword) {
                    $search->where('booking_code', 'like', "%{$keyword}%")
                        ->orWhere('notes', 'like', "%{$keyword}%")
                        ->orWhereHas('customer', fn ($customerQuery) => $customerQuery
                            ->where('name', 'like', "%{$keyword}%")
                            ->orWhere('phone', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%"));
                });
            });
    }

    private function applyPaymentFilters($query, string $keyword, string $status, string $paymentStatus): void
    {
        $query->when($paymentStatus !== '', fn ($inner) => $inner->where('status', $paymentStatus))
            ->when($status !== '', fn ($inner) => $inner->whereHas('booking', fn ($bookingQuery) => $bookingQuery->where('status', $status)))
            ->when($keyword !== '', function ($inner) use ($keyword) {
                $inner->where(function ($search) use ($keyword) {
                    $search->where('payer_name', 'like', "%{$keyword}%")
                        ->orWhere('bank_name', 'like', "%{$keyword}%")
                        ->orWhereHas('booking', fn ($bookingQuery) => $bookingQuery
                            ->where('booking_code', 'like', "%{$keyword}%")
                            ->orWhereHas('customer', fn ($customerQuery) => $customerQuery
                                ->where('name', 'like', "%{$keyword}%")
                                ->orWhere('phone', 'like', "%{$keyword}%")
                                ->orWhere('email', 'like', "%{$keyword}%")));
                });
            });
    }

    private function xlsxContentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private function xlsxRootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function xlsxWorkbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Laporan" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private function xlsxWorkbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private function xlsxStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="1"><fill><patternFill patternType="none"/></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs>'
            . '</styleSheet>';
    }

    private function xlsxSheetXml(array $rows): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $xml .= '<sheetData>';

        foreach ($rows as $rowIndex => $row) {
            $excelRow = $rowIndex + 1;
            $xml .= '<row r="' . $excelRow . '">';
            foreach ($row as $colIndex => $value) {
                $cellRef = $this->columnLetter($colIndex + 1) . $excelRow;
                $safe = htmlspecialchars((string) $value, ENT_XML1);
                $xml .= '<c r="' . $cellRef . '" t="inlineStr"><is><t>' . $safe . '</t></is></c>';
            }
            $xml .= '</row>';
        }

        $xml .= '</sheetData></worksheet>';

        return $xml;
    }

    private function columnLetter(int $index): string
    {
        $letters = '';
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letters = chr(65 + $mod) . $letters;
            $index = intdiv($index - 1, 26);
        }

        return $letters;
    }
}
