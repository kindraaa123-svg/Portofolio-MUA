<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\RecycleBin;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\ActivityLogger;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Illuminate\Support\Str;
use ZipArchive;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('q')->toString();

        return view('admin.services.index', [
            'services' => Service::with('category')
                ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'addons' => Addon::latest()->paginate(10, ['*'], 'addons_page'),
            'search' => $search,
            'categories' => ServiceCategory::orderBy('name')->get(),
        ]);
    }

    public function storeService(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'service_category_id' => ['nullable', 'exists:service_categories,id'],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:30'],
            'price' => ['required', 'numeric', 'min:0'],
            'home_service_fee' => ['nullable', 'numeric', 'min:0'],
            'is_home_service_available' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $service = Service::create([
            ...$data,
            'slug' => Str::slug($data['name']) . '-' . Str::lower(Str::random(4)),
            'is_home_service_available' => $request->boolean('is_home_service_available'),
            'home_service_fee' => $data['home_service_fee'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        ActivityLogger::log('service', 'create', $service, ['name' => $service->name]);

        return back()->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function updateService(Request $request, Service $service): RedirectResponse
    {
        $data = $request->validate([
            'service_category_id' => ['nullable', 'exists:service_categories,id'],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:30'],
            'price' => ['required', 'numeric', 'min:0'],
            'home_service_fee' => ['nullable', 'numeric', 'min:0'],
            'is_home_service_available' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $service->update([
            ...$data,
            'is_home_service_available' => $request->boolean('is_home_service_available'),
            'home_service_fee' => $data['home_service_fee'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'updated_by' => auth()->id(),
        ]);

        ActivityLogger::log('service', 'update', $service, ['name' => $service->name]);

        return back()->with('success', 'Layanan berhasil diperbarui.');
    }

    public function storeAddon(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $addon = Addon::create([
            ...$data,
            'slug' => Str::slug($data['name']) . '-' . Str::lower(Str::random(4)),
            'is_active' => $request->boolean('is_active', true),
        ]);

        ActivityLogger::log('addon', 'create', $addon, ['name' => $addon->name]);

        return back()->with('success', 'Add-on berhasil ditambahkan.');
    }

    public function updateAddon(Request $request, Addon $addon): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $addon->update([
            ...$data,
            'is_active' => $request->boolean('is_active', true),
        ]);

        ActivityLogger::log('addon', 'update', $addon, ['name' => $addon->name]);

        return back()->with('success', 'Add-on berhasil diperbarui.');
    }

    public function exportServicesXlsx()
    {
        if (! class_exists(ZipArchive::class)) {
            return back()->withErrors(['service' => 'Export gagal: ekstensi ZipArchive tidak tersedia di server.']);
        }

        $services = Service::with('category')->orderBy('id')->get();
        $rows = [[
            'Name',
            'Slug',
            'Category',
            'Description',
            'Duration Minutes',
            'Price',
            'Home Service Fee',
            'Home Service Available',
            'Is Active',
        ]];

        foreach ($services as $service) {
            $rows[] = [
                (string) $service->name,
                (string) $service->slug,
                (string) ($service->category?->name ?? ''),
                (string) ($service->description ?? ''),
                (string) $service->duration_minutes,
                (string) $service->price,
                (string) $service->home_service_fee,
                $service->is_home_service_available ? '1' : '0',
                $service->is_active ? '1' : '0',
            ];
        }

        return $this->downloadXlsx(
            $rows,
            'pricelist-layanan-' . now()->format('Ymd-His') . '.xlsx',
            'Layanan',
            ['module' => 'service', 'total_services' => $services->count()]
        );
    }

    public function exportAddonsXlsx()
    {
        if (! class_exists(ZipArchive::class)) {
            return back()->withErrors(['service' => 'Export gagal: ekstensi ZipArchive tidak tersedia di server.']);
        }

        $addons = Addon::orderBy('id')->get();
        $rows = [[
            'Name',
            'Slug',
            'Description',
            'Price',
            'Is Active',
        ]];

        foreach ($addons as $addon) {
            $rows[] = [
                (string) $addon->name,
                (string) $addon->slug,
                (string) ($addon->description ?? ''),
                (string) $addon->price,
                $addon->is_active ? '1' : '0',
            ];
        }

        return $this->downloadXlsx(
            $rows,
            'pricelist-addon-' . now()->format('Ymd-His') . '.xlsx',
            'Addons',
            ['module' => 'addon', 'total_addons' => $addons->count()]
        );
    }

    public function importServicesXlsx(Request $request): RedirectResponse
    {
        return $this->importXlsxByType($request, 'service');
    }

    public function importAddonsXlsx(Request $request): RedirectResponse
    {
        return $this->importXlsxByType($request, 'addon');
    }

    public function destroyService(Service $service): RedirectResponse
    {
        RecycleBin::create([
            'module' => 'service',
            'model_type' => Service::class,
            'model_id' => $service->id,
            'payload' => [
                'name' => $service->name,
                'slug' => $service->slug,
                'price' => $service->price,
            ],
            'deleted_by' => auth()->id(),
            'deleted_at' => now(),
        ]);

        $service->delete();

        ActivityLogger::log('service', 'delete', $service, ['name' => $service->name]);

        return back()->with('success', 'Layanan dipindahkan ke recycle bin.');
    }

    public function destroyAddon(Addon $addon): RedirectResponse
    {
        RecycleBin::create([
            'module' => 'addon',
            'model_type' => Addon::class,
            'model_id' => $addon->id,
            'payload' => [
                'name' => $addon->name,
                'slug' => $addon->slug,
                'price' => $addon->price,
            ],
            'deleted_by' => auth()->id(),
            'deleted_at' => now(),
        ]);

        $addon->delete();

        ActivityLogger::log('addon', 'delete', $addon, ['name' => $addon->name]);

        return back()->with('success', 'Add-on dipindahkan ke recycle bin.');
    }

    private function downloadXlsx(array $rows, string $filename, string $sheetName, array $properties = [])
    {
        $dir = storage_path('app/exports');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $path = $dir . DIRECTORY_SEPARATOR . $filename;
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->withErrors(['service' => 'Export gagal: tidak dapat membuat file XLSX.']);
        }

        $zip->addFromString('[Content_Types].xml', $this->xlsxContentTypesXml());
        $zip->addFromString('_rels/.rels', $this->xlsxRootRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->xlsxWorkbookXml($sheetName));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->xlsxWorkbookRelsXml());
        $zip->addFromString('xl/styles.xml', $this->xlsxStylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->xlsxSheetXml($rows));
        $zip->close();

        ActivityLogger::log('service', 'export-xlsx', null, ['file_name' => $filename, ...$properties]);

        return ResponseFacade::download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function importXlsxByType(Request $request, string $expectedType): RedirectResponse
    {
        if (! class_exists(ZipArchive::class)) {
            return back()->withErrors(['service' => 'Import gagal: ekstensi ZipArchive tidak tersedia di server.']);
        }

        $data = $request->validate([
            'xlsx_file' => ['required', 'file', 'mimes:xlsx', 'max:20480'],
        ]);

        $zip = new ZipArchive();
        if ($zip->open($data['xlsx_file']->getRealPath()) !== true) {
            return back()->withErrors(['service' => 'Import gagal: file XLSX tidak valid.']);
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            $zip->close();
            return back()->withErrors(['service' => 'Import gagal: sheet1 tidak ditemukan.']);
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $sharedStrings = $this->parseSharedStrings($sharedXml);
        }
        $zip->close();

        $rows = $this->parseSheetRows($sheetXml, $sharedStrings);
        if (count($rows) < 2) {
            return back()->withErrors(['service' => 'Import gagal: data XLSX kosong.']);
        }

        $headerMap = $this->buildHeaderMap($rows[0]);
        if (! isset($headerMap['name'])) {
            return back()->withErrors(['service' => "Import gagal: kolom 'Name' wajib ada."]);
        }
        if ($expectedType === 'service' && ! isset($headerMap['duration_minutes'])) {
            return back()->withErrors(['service' => "Import layanan gagal: kolom 'Duration Minutes' wajib ada."]);
        }
        if ($expectedType === 'addon' && ! isset($headerMap['price'])) {
            return back()->withErrors(['service' => "Import add-on gagal: kolom 'Price' wajib ada."]);
        }

        $currentUserId = auth()->id();
        $created = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($rows, $headerMap, $currentUserId, $expectedType, &$created, &$updated, &$skipped): void {
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $name = trim($this->getRowValue($row, $headerMap, 'name'));
                $slugRaw = trim($this->getRowValue($row, $headerMap, 'slug'));
                $description = trim($this->getRowValue($row, $headerMap, 'description'));

                if ($name === '') {
                    $skipped++;
                    continue;
                }

                if ($expectedType === 'service') {
                    $categoryId = $this->resolveCategoryId(trim($this->getRowValue($row, $headerMap, 'category')));
                    $durationMinutes = (int) max(30, (int) $this->normalizeNumber($this->getRowValue($row, $headerMap, 'duration_minutes'), 90));
                    $price = $this->normalizeNumber($this->getRowValue($row, $headerMap, 'price'), 0);
                    $homeFee = $this->normalizeNumber($this->getRowValue($row, $headerMap, 'home_service_fee'), 0);
                    $homeAvailable = $this->normalizeBoolean($this->getRowValue($row, $headerMap, 'home_service_available'), false);
                    $isActive = $this->normalizeBoolean($this->getRowValue($row, $headerMap, 'is_active'), true);

                    $slug = $slugRaw !== '' ? Str::slug($slugRaw) : Str::slug($name);
                    if ($slug === '') {
                        $slug = Str::slug($name) . '-' . Str::lower(Str::random(4));
                    }

                    $service = Service::withTrashed()->where('slug', $slug)->first();
                    if (! $service) {
                        $service = Service::withTrashed()->whereRaw('LOWER(name) = ?', [Str::lower($name)])->first();
                    }

                    $payload = [
                        'service_category_id' => $categoryId,
                        'name' => $name,
                        'description' => $description !== '' ? $description : null,
                        'duration_minutes' => $durationMinutes,
                        'price' => $price,
                        'home_service_fee' => $homeFee,
                        'is_home_service_available' => $homeAvailable,
                        'is_active' => $isActive,
                        'updated_by' => $currentUserId,
                    ];

                    if ($service) {
                        if (method_exists($service, 'trashed') && $service->trashed()) {
                            $service->restore();
                        }
                        $service->update($payload);
                        $updated++;
                    } else {
                        $finalSlug = $slug;
                        while (Service::withTrashed()->where('slug', $finalSlug)->exists()) {
                            $finalSlug = $slug . '-' . Str::lower(Str::random(4));
                        }

                        Service::create([
                            ...$payload,
                            'slug' => $finalSlug,
                            'created_by' => $currentUserId,
                        ]);
                        $created++;
                    }

                    continue;
                }

                $price = $this->normalizeNumber($this->getRowValue($row, $headerMap, 'price'), 0);
                $isActive = $this->normalizeBoolean($this->getRowValue($row, $headerMap, 'is_active'), true);
                $slug = $slugRaw !== '' ? Str::slug($slugRaw) : Str::slug($name);
                if ($slug === '') {
                    $slug = Str::slug($name) . '-' . Str::lower(Str::random(4));
                }

                $addon = Addon::withTrashed()->where('slug', $slug)->first();
                if (! $addon) {
                    $addon = Addon::withTrashed()->whereRaw('LOWER(name) = ?', [Str::lower($name)])->first();
                }

                $payload = [
                    'name' => $name,
                    'description' => $description !== '' ? $description : null,
                    'price' => $price,
                    'is_active' => $isActive,
                ];

                if ($addon) {
                    if (method_exists($addon, 'trashed') && $addon->trashed()) {
                        $addon->restore();
                    }
                    $addon->update($payload);
                    $updated++;
                } else {
                    $finalSlug = $slug;
                    while (Addon::withTrashed()->where('slug', $finalSlug)->exists()) {
                        $finalSlug = $slug . '-' . Str::lower(Str::random(4));
                    }

                    Addon::create([
                        ...$payload,
                        'slug' => $finalSlug,
                    ]);
                    $created++;
                }
            }
        });

        ActivityLogger::log('service', 'import-xlsx', null, [
            'module' => $expectedType,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
        ]);

        $label = $expectedType === 'service' ? 'layanan' : 'add-on';
        return back()->with('success', "Import {$label} selesai. Baru: {$created}, Update: {$updated}, Dilewati: {$skipped}.");
    }

    private function resolveCategoryId(string $value): ?int
    {
        if ($value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return ServiceCategory::query()->whereKey((int) $value)->value('id');
        }

        return ServiceCategory::query()
            ->whereRaw('LOWER(name) = ?', [Str::lower($value)])
            ->value('id');
    }

    private function normalizeBoolean(string $value, bool $default): bool
    {
        $normalized = Str::lower(trim($value));
        if ($normalized === '') {
            return $default;
        }

        return in_array($normalized, ['1', 'true', 'yes', 'ya', 'aktif', 'active'], true);
    }

    private function normalizeNumber(string $value, float|int $default): float
    {
        $normalized = trim(str_replace(',', '.', $value));
        if ($normalized === '' || ! is_numeric($normalized)) {
            return (float) $default;
        }

        return (float) $normalized;
    }

    private function buildHeaderMap(array $headerRow): array
    {
        $map = [];
        foreach ($headerRow as $col => $header) {
            $normalized = Str::of((string) $header)
                ->lower()
                ->replace([' ', '-', '_'], '')
                ->toString();

            if (in_array($normalized, ['type', 'tipe', 'module'], true)) $map['type'] = $col;
            if (in_array($normalized, ['name', 'nama'], true)) $map['name'] = $col;
            if (in_array($normalized, ['slug'], true)) $map['slug'] = $col;
            if (in_array($normalized, ['category', 'kategori'], true)) $map['category'] = $col;
            if (in_array($normalized, ['description', 'deskripsi'], true)) $map['description'] = $col;
            if (in_array($normalized, ['durationminutes', 'duration', 'durasi'], true)) $map['duration_minutes'] = $col;
            if (in_array($normalized, ['price', 'harga'], true)) $map['price'] = $col;
            if (in_array($normalized, ['homeservicefee', 'biayalayanankerumah'], true)) $map['home_service_fee'] = $col;
            if (in_array($normalized, ['homeserviceavailable', 'layanankerumah'], true)) $map['home_service_available'] = $col;
            if (in_array($normalized, ['isactive', 'active', 'aktif'], true)) $map['is_active'] = $col;
        }

        return $map;
    }

    private function getRowValue(array $row, array $headerMap, string $key): string
    {
        if (! isset($headerMap[$key])) {
            return '';
        }

        return (string) ($row[$headerMap[$key]] ?? '');
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

    private function xlsxWorkbookXml(string $sheetName = 'Pricelist'): string
    {
        $safeSheetName = htmlspecialchars($sheetName, ENT_XML1);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . $safeSheetName . '" sheetId="1" r:id="rId1"/></sheets>'
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
