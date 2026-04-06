<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\PortfolioCategory;
use App\Models\PortfolioImage;
use App\Models\RecycleBin;
use App\Support\ActivityLogger;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class PortfolioController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('q')->toString();

        $portfolios = Portfolio::with('category')
            ->when($search, fn ($q) => $q->where('title', 'like', "%{$search}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.portfolio.index', [
            'portfolios' => $portfolios,
            'search' => $search,
            'categories' => PortfolioCategory::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.portfolio.form', [
            'portfolio' => new Portfolio(),
            'categories' => PortfolioCategory::orderBy('name')->get(),
            'action' => route('admin.portfolios.store'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePortfolio($request);

        DB::transaction(function () use ($request, $data): void {
            $coverPath = $request->hasFile('cover_image')
                ? $request->file('cover_image')->store('portfolios/covers', 'public')
                : null;

            $portfolio = Portfolio::create([
                ...$data,
                'slug' => Str::slug($data['title']) . '-' . Str::lower(Str::random(5)),
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
                'cover_image' => $coverPath,
            ]);

            if ($request->hasFile('images')) {
                $firstGalleryPath = null;
                foreach ($request->file('images') as $index => $image) {
                    $storedPath = $image->store('portfolios/gallery', 'public');
                    $firstGalleryPath ??= $storedPath;

                    PortfolioImage::create([
                        'portfolio_id' => $portfolio->id,
                        'image_path' => $storedPath,
                        'sort_order' => $index,
                    ]);
                }

                if (! $portfolio->cover_image && $firstGalleryPath) {
                    $portfolio->update(['cover_image' => $firstGalleryPath]);
                }
            }

            ActivityLogger::log('portfolio', 'create', $portfolio, ['title' => $portfolio->title]);
        });

        return redirect()->route('admin.portfolios.index')->with('success', 'Portfolio berhasil ditambahkan.');
    }

    public function edit(Portfolio $portfolio)
    {
        return view('admin.portfolio.form', [
            'portfolio' => $portfolio->load('images'),
            'categories' => PortfolioCategory::orderBy('name')->get(),
            'action' => route('admin.portfolios.update', $portfolio),
        ]);
    }

    public function update(Request $request, Portfolio $portfolio): RedirectResponse
    {
        $data = $this->validatePortfolio($request);

        DB::transaction(function () use ($request, $data, $portfolio): void {
            if ($request->hasFile('cover_image')) {
                if ($portfolio->cover_image) {
                    Storage::disk('public')->delete($portfolio->cover_image);
                }
                $data['cover_image'] = $request->file('cover_image')->store('portfolios/covers', 'public');
            }

            $portfolio->update([...$data, 'updated_by' => auth()->id()]);

            if ($request->hasFile('images')) {
                $firstGalleryPath = null;
                foreach ($request->file('images') as $index => $image) {
                    $storedPath = $image->store('portfolios/gallery', 'public');
                    $firstGalleryPath ??= $storedPath;

                    PortfolioImage::create([
                        'portfolio_id' => $portfolio->id,
                        'image_path' => $storedPath,
                        'sort_order' => $index,
                    ]);
                }

                if (! $portfolio->cover_image && $firstGalleryPath) {
                    $portfolio->update(['cover_image' => $firstGalleryPath]);
                }
            }

            ActivityLogger::log('portfolio', 'update', $portfolio, ['title' => $portfolio->title]);
        });

        return redirect()->route('admin.portfolios.index')->with('success', 'Portfolio berhasil diperbarui.');
    }

    public function destroy(Portfolio $portfolio): RedirectResponse
    {
        $imagePaths = $portfolio->images()->pluck('image_path')->filter()->values()->all();

        RecycleBin::create([
            'module' => 'portfolio',
            'model_type' => Portfolio::class,
            'model_id' => $portfolio->id,
            'payload' => [
                'title' => $portfolio->title,
                'slug' => $portfolio->slug,
                'cover_image' => $portfolio->cover_image,
                'image_paths' => $imagePaths,
            ],
            'deleted_by' => auth()->id(),
            'deleted_at' => now(),
        ]);

        $portfolio->delete();

        ActivityLogger::log('portfolio', 'delete', $portfolio, ['title' => $portfolio->title]);

        return back()->with('success', 'Portfolio dipindahkan ke recycle bin.');
    }

    public function exportXlsx()
    {
        if (! class_exists(ZipArchive::class)) {
            return back()->withErrors(['portfolio' => 'Export gagal: ekstensi ZipArchive tidak tersedia di server.']);
        }

        $portfolios = Portfolio::with('category')->orderBy('id')->get();
        $filename = 'portfolios-' . now()->format('Ymd-His') . '.xlsx';
        $dir = storage_path('app/exports');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $path = $dir . DIRECTORY_SEPARATOR . $filename;

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->withErrors(['portfolio' => 'Export gagal: tidak dapat membuat file XLSX.']);
        }

        $headers = ['Title', 'Slug', 'Category', 'Summary', 'Description', 'Work Date', 'Client Name', 'Is Published', 'Cover Image Path'];
        $rows = [$headers];
        foreach ($portfolios as $portfolio) {
            $rows[] = [
                (string) $portfolio->title,
                (string) $portfolio->slug,
                (string) ($portfolio->category?->name ?? ''),
                (string) ($portfolio->summary ?? ''),
                (string) ($portfolio->description ?? ''),
                (string) ($portfolio->work_date?->format('Y-m-d') ?? ''),
                (string) ($portfolio->client_name ?? ''),
                $portfolio->is_published ? '1' : '0',
                (string) ($portfolio->cover_image ?? ''),
            ];
        }

        $zip->addFromString('[Content_Types].xml', $this->xlsxContentTypesXml());
        $zip->addFromString('_rels/.rels', $this->xlsxRootRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->xlsxWorkbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->xlsxWorkbookRelsXml());
        $zip->addFromString('xl/styles.xml', $this->xlsxStylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->xlsxSheetXml($rows));
        $zip->close();

        ActivityLogger::log('portfolio', 'export-xlsx', null, [
            'file_name' => $filename,
            'total_portfolios' => $portfolios->count(),
        ]);

        return ResponseFacade::download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function importXlsx(Request $request): RedirectResponse
    {
        if (! class_exists(ZipArchive::class)) {
            return back()->withErrors(['portfolio' => 'Import gagal: ekstensi ZipArchive tidak tersedia di server.']);
        }

        $data = $request->validate([
            'xlsx_file' => ['required', 'file', 'mimes:xlsx', 'max:20480'],
        ]);

        $zip = new ZipArchive();
        if ($zip->open($data['xlsx_file']->getRealPath()) !== true) {
            return back()->withErrors(['portfolio' => 'Import gagal: file XLSX tidak valid.']);
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            $zip->close();
            return back()->withErrors(['portfolio' => 'Import gagal: sheet1 tidak ditemukan.']);
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $sharedStrings = $this->parseSharedStrings($sharedXml);
        }

        $zip->close();

        $rows = $this->parseSheetRows($sheetXml, $sharedStrings);
        if (count($rows) < 2) {
            return back()->withErrors(['portfolio' => 'Import gagal: data XLSX kosong.']);
        }

        $headerMap = $this->buildPortfolioHeaderMap($rows[0]);
        if (! array_key_exists('title', $headerMap)) {
            return back()->withErrors(['portfolio' => "Import gagal: kolom 'title' wajib ada di file XLSX."]);
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $currentUserId = auth()->id();

        DB::transaction(function () use ($rows, $headerMap, &$created, &$updated, &$skipped, $currentUserId): void {
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $title = trim($this->getRowValue($row, $headerMap, 'title'));

                if ($title === '') {
                    $skipped++;
                    continue;
                }

                $slugRaw = trim($this->getRowValue($row, $headerMap, 'slug'));
                $slug = $slugRaw !== '' ? Str::slug($slugRaw) : Str::slug($title);
                if ($slug === '') {
                    $slug = Str::slug($title) . '-' . Str::lower(Str::random(5));
                }

                $categoryRaw = trim($this->getRowValue($row, $headerMap, 'category'));
                $categoryId = null;
                if ($categoryRaw !== '') {
                    if (is_numeric($categoryRaw)) {
                        $categoryId = PortfolioCategory::query()->whereKey((int) $categoryRaw)->value('id');
                    } else {
                        $categoryId = PortfolioCategory::query()
                            ->whereRaw('LOWER(name) = ?', [Str::lower($categoryRaw)])
                            ->value('id');
                    }
                }

                $isPublishedRaw = trim($this->getRowValue($row, $headerMap, 'is_published'));
                $isPublished = in_array(Str::lower($isPublishedRaw), ['1', 'true', 'yes', 'publish', 'published'], true);

                $payload = [
                    'portfolio_category_id' => $categoryId,
                    'title' => $title,
                    'summary' => trim($this->getRowValue($row, $headerMap, 'summary')) ?: null,
                    'description' => trim($this->getRowValue($row, $headerMap, 'description')) ?: null,
                    'client_name' => trim($this->getRowValue($row, $headerMap, 'client_name')) ?: null,
                    'work_date' => $this->normalizeDate($this->getRowValue($row, $headerMap, 'work_date')),
                    'is_published' => $isPublished,
                    'cover_image' => trim($this->getRowValue($row, $headerMap, 'cover_image')) ?: null,
                    'updated_by' => $currentUserId,
                ];

                $portfolio = Portfolio::query()->where('slug', $slug)->first();
                if ($portfolio) {
                    $portfolio->update($payload);
                    $updated++;
                    continue;
                }

                $finalSlug = $slug;
                while (Portfolio::query()->where('slug', $finalSlug)->exists()) {
                    $finalSlug = $slug . '-' . Str::lower(Str::random(4));
                }

                Portfolio::query()->create([
                    ...$payload,
                    'slug' => $finalSlug,
                    'created_by' => $currentUserId,
                ]);
                $created++;
            }
        });

        ActivityLogger::log('portfolio', 'import-xlsx', null, [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
        ]);

        return back()->with('success', "Import portfolio selesai. Baru: {$created}, Update: {$updated}, Dilewati: {$skipped}.");
    }

    protected function validatePortfolio(Request $request): array
    {
        return $request->validate([
            'portfolio_category_id' => ['nullable', 'exists:portfolio_categories,id'],
            'title' => ['required', 'string', 'max:200'],
            'summary' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'client_name' => ['nullable', 'string', 'max:120'],
            'work_date' => ['nullable', 'date'],
            'is_published' => ['nullable', 'boolean'],
            'cover_image' => [$request->isMethod('post') ? 'required_without:images' : 'nullable', 'image', 'max:4096'],
            'images' => [$request->isMethod('post') ? 'required_without:cover_image' : 'nullable', 'array'],
            'images.*' => ['nullable', 'image', 'max:4096'],
        ]);
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
            . '<sheets><sheet name="Portfolios" sheetId="1" r:id="rId1"/></sheets>'
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

    private function parseSharedStrings(string $xml): array
    {
        $dom = new DOMDocument();
        if (! @$dom->loadXML($xml)) {
            return [];
        }

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $items = [];
        foreach ($xpath->query('//x:si') as $si) {
            $texts = [];
            foreach ($xpath->query('.//x:t', $si) as $t) {
                $texts[] = $t->textContent;
            }
            $items[] = implode('', $texts);
        }

        return $items;
    }

    private function parseSheetRows(string $xml, array $sharedStrings): array
    {
        $dom = new DOMDocument();
        if (! @$dom->loadXML($xml)) {
            return [];
        }

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $rows = [];

        foreach ($xpath->query('//x:sheetData/x:row') as $rowNode) {
            $row = [];
            foreach ($xpath->query('./x:c', $rowNode) as $cell) {
                $ref = (string) $cell->attributes->getNamedItem('r')?->nodeValue;
                $col = preg_replace('/\d+/', '', $ref) ?: '';
                $type = (string) $cell->attributes->getNamedItem('t')?->nodeValue;
                $value = '';

                if ($type === 'inlineStr') {
                    $value = (string) $xpath->evaluate('string(./x:is/x:t)', $cell);
                } elseif ($type === 's') {
                    $idx = (int) $xpath->evaluate('string(./x:v)', $cell);
                    $value = $sharedStrings[$idx] ?? '';
                } else {
                    $value = (string) $xpath->evaluate('string(./x:v)', $cell);
                }

                if ($col !== '') {
                    $row[$col] = $value;
                }
            }
            $rows[] = $row;
        }

        return $rows;
    }

    private function buildPortfolioHeaderMap(array $headerRow): array
    {
        $map = [];
        foreach ($headerRow as $col => $header) {
            $normalized = Str::of((string) $header)
                ->lower()
                ->replace([' ', '-', '_'], '')
                ->toString();

            if (in_array($normalized, ['title', 'judul'], true)) $map['title'] = $col;
            if (in_array($normalized, ['slug'], true)) $map['slug'] = $col;
            if (in_array($normalized, ['category', 'kategori'], true)) $map['category'] = $col;
            if (in_array($normalized, ['summary', 'ringkasan'], true)) $map['summary'] = $col;
            if (in_array($normalized, ['description', 'deskripsi'], true)) $map['description'] = $col;
            if (in_array($normalized, ['workdate', 'tanggal', 'tanggalkerja'], true)) $map['work_date'] = $col;
            if (in_array($normalized, ['clientname', 'namaklien', 'klien'], true)) $map['client_name'] = $col;
            if (in_array($normalized, ['ispublished', 'publish', 'status'], true)) $map['is_published'] = $col;
            if (in_array($normalized, ['coverimagepath', 'coverimage', 'cover'], true)) $map['cover_image'] = $col;
        }

        return $map;
    }

    private function getRowValue(array $row, array $headerMap, string $key): string
    {
        if (! isset($headerMap[$key])) {
            return '';
        }

        $col = $headerMap[$key];
        return (string) ($row[$col] ?? '');
    }

    private function normalizeDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
            return $value;
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
