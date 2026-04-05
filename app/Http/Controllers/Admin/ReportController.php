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
use Illuminate\Support\Str;
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
        $logoAsset = $this->resolveLogoAsset($report['siteLogoPath'] ?? null);

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $path = $dir . DIRECTORY_SEPARATOR . $filename;
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->withErrors(['report' => 'Export gagal: tidak dapat membuat file XLSX.']);
        }

        $rows = [];
        if ($logoAsset) {
            // Sisakan ruang untuk area logo agar tidak menutupi isi laporan.
            for ($i = 0; $i < 6; $i++) {
                $rows[] = [];
            }
        }

        $rows[] = ['Laporan Keuangan Profesional'];
        $rows[] = ['Website', $report['siteName']];
        $rows[] = ['Periode', $report['from'] . ' s/d ' . $report['to']];
        $rows[] = ['Dibuat Pada', $report['generatedAt']];
        $rows[] = [];
        $rows[] = ['Laporan Laba Rugi (Cash Basis)'];
        $rows[] = ['Total Income (Verified)', (string) $report['summary']['total_income_verified']];
        $rows[] = ['Total Outcome (Verified)', (string) $report['summary']['total_outcome_verified']];
        $rows[] = ['Laba Bersih', (string) $report['summary']['net_income']];
        $rows[] = [];
        $rows[] = ['Rincian Income/Outcome Harian'];
        $rows[] = ['Tanggal', 'Income', 'Outcome', 'Net'];
        foreach ($report['dailyFinancialRows'] as $row) {
            $rows[] = [
                (string) $row['date'],
                (string) $row['income'],
                (string) $row['outcome'],
                (string) $row['net'],
            ];
        }

        $zip->addFromString('[Content_Types].xml', $this->xlsxContentTypesXml($logoAsset));
        $zip->addFromString('_rels/.rels', $this->xlsxRootRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->xlsxWorkbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->xlsxWorkbookRelsXml());
        $zip->addFromString('xl/styles.xml', $this->xlsxStylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->xlsxSheetXml($rows, $logoAsset !== null));
        if ($logoAsset) {
            $zip->addFromString('xl/worksheets/_rels/sheet1.xml.rels', $this->xlsxSheetRelsXml());
            $zip->addFromString('xl/drawings/drawing1.xml', $this->xlsxDrawingXml());
            $zip->addFromString('xl/drawings/_rels/drawing1.xml.rels', $this->xlsxDrawingRelsXml($logoAsset['filename']));
            $zip->addFromString('xl/media/' . $logoAsset['filename'], $logoAsset['content']);
        }
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
        $filename = 'laporan-keuangan-' . now()->format('Ymd-His') . '.pdf';

        if (! class_exists('TCPDF')) {
            $tcpdfPath = base_path('vendor/tecnickcom/tcpdf/tcpdf.php');
            if (is_file($tcpdfPath)) {
                require_once $tcpdfPath;
            }
        }

        if (! class_exists('TCPDF')) {
            return back()->withErrors(['report' => 'Export PDF gagal: library TCPDF tidak ditemukan.']);
        }

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator(config('app.name', 'Laravel'));
        $pdf->SetAuthor($report['siteName']);
        $pdf->SetTitle('Laporan Keuangan');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 12);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();
        $pdf->SetY(12);

        $logoPath = $report['siteLogoPath'] ?? null;
        if ($logoPath && is_file($logoPath) && is_readable($logoPath)) {
            try {
                $pdf->Image($logoPath, 10, 10, 18, 18, '', '', '', true, 300, '', false, false, 0, false, false, false);
                $pdf->SetY(30);
            } catch (\Throwable $e) {
                // Ignore logo rendering failure and continue generating PDF.
                $pdf->SetY(12);
            }
        }

        $html = view('admin.reports.pdf', [
            ...$report,
        ])->render();

        $pdf->writeHTML($html, true, false, true, false, '');
        $content = $pdf->Output($filename, 'S');

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
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
        $defaultFrom = Carbon::create(now()->year, 4, 1)->toDateString();
        $from = $request->input('from', $defaultFrom);
        $to = $request->input('to', now()->toDateString());
        $order = strtolower((string) $request->input('order', 'desc')) === 'asc' ? 'asc' : 'desc';
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
            'order' => $order,
            'q' => $keyword,
            'status' => $status,
            'payment_status' => $paymentStatus,
        ];

        $transactionDates = (clone $paymentBaseQuery)
            ->selectRaw('DATE(created_at) as report_date')
            ->groupBy('report_date')
            ->orderBy('report_date', $order)
            ->pluck('report_date')
            ->filter()
            ->values();

        $bookings = $paginate
            ? (clone $bookingBaseQuery)->latest()->paginate(20)->withQueryString()
            : (clone $bookingBaseQuery)->latest()->get();

        $payments = $paginate
            ? (clone $paymentBaseQuery)->latest()->paginate(20, ['*'], 'payments_page')->withQueryString()
            : (clone $paymentBaseQuery)->latest()->get();

        $verifiedPayments = (clone $paymentBaseQuery)->where('status', 'verified')->get();
        $incomePayments = $verifiedPayments->filter(function ($payment) {
            $type = strtolower((string) $payment->payment_type);
            return $type !== 'refund' && (float) $payment->amount > 0;
        });
        $outcomePayments = $verifiedPayments->filter(function ($payment) {
            $type = strtolower((string) $payment->payment_type);
            return $type === 'refund' || (float) $payment->amount < 0;
        });

        $dailyFinancialRows = $verifiedPayments
            ->groupBy(function ($payment) {
                return optional($payment->paid_at ?? $payment->created_at)->format('Y-m-d');
            })
            ->map(function ($group, $date) {
                $income = $group->filter(function ($payment) {
                    $type = strtolower((string) $payment->payment_type);
                    return $type !== 'refund' && (float) $payment->amount > 0;
                })->sum('amount');

                $outcome = $group->filter(function ($payment) {
                    $type = strtolower((string) $payment->payment_type);
                    return $type === 'refund' || (float) $payment->amount < 0;
                })->sum(function ($payment) {
                    return abs((float) $payment->amount);
                });

                return [
                    'date' => $date,
                    'income' => (float) $income,
                    'outcome' => (float) $outcome,
                    'net' => (float) $income - (float) $outcome,
                ];
            })
            ->sortByDesc('date')
            ->values();

        $websiteSetting = WebsiteSetting::query()->latest('id')->first();
        $siteName = $websiteSetting?->site_name ?: config('app.name', 'Website');
        $siteLogoUrl = $websiteSetting?->logo ? asset('storage/' . ltrim($websiteSetting->logo, '/')) : null;
        $siteLogoPath = $websiteSetting?->logo ? storage_path('app/public/' . ltrim($websiteSetting->logo, '/')) : null;

        return [
            'siteName' => $siteName,
            'siteLogoUrl' => $siteLogoUrl,
            'siteLogoPath' => $siteLogoPath,
            'from' => $from,
            'to' => $to,
            'order' => $order,
            'generatedAt' => now()->format('Y-m-d H:i:s'),
            'keyword' => $keyword,
            'status' => $status,
            'paymentStatus' => $paymentStatus,
            'filters' => $filters,
            'transactionDates' => $transactionDates,
            'dailyFinancialRows' => $dailyFinancialRows,
            'summary' => [
                'total_income_verified' => (float) $incomePayments->sum('amount'),
                'total_outcome_verified' => (float) $outcomePayments->sum(function ($payment) {
                    return abs((float) $payment->amount);
                }),
                'net_income' => (float) $incomePayments->sum('amount') - (float) $outcomePayments->sum(function ($payment) {
                    return abs((float) $payment->amount);
                }),
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

    private function xlsxContentTypesXml(?array $logoAsset = null): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Default Extension="png" ContentType="image/png"/>'
            . '<Default Extension="jpg" ContentType="image/jpeg"/>'
            . '<Default Extension="jpeg" ContentType="image/jpeg"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';

        if ($logoAsset) {
            $xml .= '<Override PartName="/xl/drawings/drawing1.xml" ContentType="application/vnd.openxmlformats-officedocument.drawing+xml"/>';
        }

        $xml .= '</Types>';

        return $xml;
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

    private function xlsxSheetXml(array $rows, bool $hasLogo = false): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
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

        $xml .= '</sheetData>';

        if ($hasLogo) {
            $xml .= '<drawing r:id="rId1"/>';
        }

        $xml .= '</worksheet>';

        return $xml;
    }

    private function xlsxSheetRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/drawing" Target="../drawings/drawing1.xml"/>'
            . '</Relationships>';
    }

    private function xlsxDrawingXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<xdr:wsDr xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<xdr:twoCellAnchor editAs="oneCell">'
            . '<xdr:from><xdr:col>0</xdr:col><xdr:colOff>0</xdr:colOff><xdr:row>0</xdr:row><xdr:rowOff>0</xdr:rowOff></xdr:from>'
            . '<xdr:to><xdr:col>2</xdr:col><xdr:colOff>0</xdr:colOff><xdr:row>4</xdr:row><xdr:rowOff>0</xdr:rowOff></xdr:to>'
            . '<xdr:pic>'
            . '<xdr:nvPicPr><xdr:cNvPr id="1" name="Website Logo"/><xdr:cNvPicPr/></xdr:nvPicPr>'
            . '<xdr:blipFill><a:blip r:embed="rId1"/><a:stretch><a:fillRect/></a:stretch></xdr:blipFill>'
            . '<xdr:spPr><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></xdr:spPr>'
            . '</xdr:pic>'
            . '<xdr:clientData/>'
            . '</xdr:twoCellAnchor>'
            . '</xdr:wsDr>';
    }

    private function xlsxDrawingRelsXml(string $logoFilename): string
    {
        $safeFilename = htmlspecialchars($logoFilename, ENT_XML1);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/' . $safeFilename . '"/>'
            . '</Relationships>';
    }

    private function resolveLogoAsset(?string $logoPath): ?array
    {
        if (! $logoPath || ! is_file($logoPath) || ! is_readable($logoPath)) {
            return null;
        }

        $ext = Str::lower(pathinfo($logoPath, PATHINFO_EXTENSION));
        if (! in_array($ext, ['png', 'jpg', 'jpeg'], true)) {
            return null;
        }

        $content = @file_get_contents($logoPath);
        if ($content === false) {
            return null;
        }

        return [
            'filename' => 'site-logo.' . $ext,
            'content' => $content,
        ];
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
