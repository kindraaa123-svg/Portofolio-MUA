<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\ActivityLogger;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Illuminate\Support\Str;
use ZipArchive;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')->latest()->paginate(12);

        return view('admin.users.index', [
            'users' => $users,
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        $users = User::query()
            ->with('role')
            ->when($request->filled('role_id'), function ($query) use ($request) {
                $query->where('role_id', (int) $request->input('role_id'));
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return response()->json([
            'rows' => view('admin.users.partials.table-rows', ['users' => $users])->render(),
            'pagination' => $users->links()->render(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:32'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $defaultPassword = '12345';

        $user = User::create([
            ...$data,
            'password' => Hash::make($defaultPassword),
            'is_active' => true,
        ]);

        ActivityLogger::log('user', 'create', $user, [
            'email' => $user->email,
            'default_password' => $defaultPassword,
        ]);

        return back()->with('success', 'User baru berhasil ditambahkan. Password default: 12345');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $defaultPassword = '12345';
        $user->update(['password' => Hash::make($defaultPassword)]);

        ActivityLogger::log('user', 'reset-password', $user, [
            'email' => $user->email,
            'default_password' => $defaultPassword,
        ]);

        return back()->with('success', 'Password user berhasil direset ke 12345.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ((int) auth()->id() === (int) $user->id) {
            return back()->withErrors(['user' => 'Akun yang sedang login tidak bisa dihapus.']);
        }

        $user->delete();

        ActivityLogger::log('user', 'delete', $user, ['email' => $user->email]);

        return back()->with('success', 'User berhasil dihapus.');
    }

    public function exportXlsx()
    {
        if (! class_exists(ZipArchive::class)) {
            return back()->withErrors(['user' => 'Export gagal: ekstensi ZipArchive tidak tersedia di server.']);
        }

        $users = User::with('role')->orderBy('id')->get();
        $filename = 'users-' . now()->format('Ymd-His') . '.xlsx';
        $dir = storage_path('app/exports');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $path = $dir . DIRECTORY_SEPARATOR . $filename;

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->withErrors(['user' => 'Export gagal: tidak dapat membuat file XLSX.']);
        }

        $headers = ['Name', 'Email', 'Phone', 'Role', 'Is Active'];
        $rows = [$headers];
        foreach ($users as $user) {
            $rows[] = [
                (string) $user->name,
                (string) $user->email,
                (string) ($user->phone ?? ''),
                (string) ($user->role?->name ?? ''),
                $user->is_active ? '1' : '0',
            ];
        }

        $zip->addFromString('[Content_Types].xml', $this->xlsxContentTypesXml());
        $zip->addFromString('_rels/.rels', $this->xlsxRootRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->xlsxWorkbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->xlsxWorkbookRelsXml());
        $zip->addFromString('xl/styles.xml', $this->xlsxStylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->xlsxSheetXml($rows));
        $zip->close();

        ActivityLogger::log('user', 'export-xlsx', null, [
            'file_name' => $filename,
            'total_users' => $users->count(),
        ]);

        return ResponseFacade::download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function importXlsx(Request $request): RedirectResponse
    {
        if (! class_exists(ZipArchive::class)) {
            return back()->withErrors(['user' => 'Import gagal: ekstensi ZipArchive tidak tersedia di server.']);
        }

        $data = $request->validate([
            'xlsx_file' => ['required', 'file', 'mimes:xlsx', 'max:20480'],
        ]);

        $zip = new ZipArchive();
        if ($zip->open($data['xlsx_file']->getRealPath()) !== true) {
            return back()->withErrors(['user' => 'Import gagal: file XLSX tidak valid.']);
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            $zip->close();
            return back()->withErrors(['user' => 'Import gagal: sheet1 tidak ditemukan.']);
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $sharedStrings = $this->parseSharedStrings($sharedXml);
        }

        $zip->close();

        $rows = $this->parseSheetRows($sheetXml, $sharedStrings);
        if (count($rows) < 2) {
            return back()->withErrors(['user' => 'Import gagal: data XLSX kosong.']);
        }

        $headerMap = $this->buildHeaderMap($rows[0]);
        $required = ['name', 'email'];
        foreach ($required as $field) {
            if (! array_key_exists($field, $headerMap)) {
                return back()->withErrors(['user' => "Import gagal: kolom '{$field}' wajib ada di file XLSX."]);
            }
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $defaultPassword = '12345';

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $name = trim($this->getRowValue($row, $headerMap, 'name'));
            $email = trim($this->getRowValue($row, $headerMap, 'email'));

            if ($name === '' || $email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                continue;
            }

            $phone = trim($this->getRowValue($row, $headerMap, 'phone'));
            $roleRaw = trim($this->getRowValue($row, $headerMap, 'role'));
            $isActiveRaw = trim($this->getRowValue($row, $headerMap, 'is_active'));
            $isActive = in_array(Str::lower($isActiveRaw), ['1', 'true', 'yes', 'aktif'], true);

            $role = null;
            if ($roleRaw !== '') {
                if (is_numeric($roleRaw)) {
                    $role = Role::find((int) $roleRaw);
                } else {
                    $role = Role::whereRaw('LOWER(name) = ?', [Str::lower($roleRaw)])->first();
                }
            }

            if (! $role) {
                $role = Role::orderBy('id')->first();
            }

            if (! $role) {
                $skipped++;
                continue;
            }

            $payload = [
                'name' => $name,
                'phone' => $phone,
                'role_id' => $role->id,
                'is_active' => $isActive,
            ];

            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update($payload);
                $updated++;
            } else {
                User::create([
                    ...$payload,
                    'email' => $email,
                    'password' => Hash::make($defaultPassword),
                ]);
                $created++;
            }
        }

        ActivityLogger::log('user', 'import-xlsx', null, [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
        ]);

        return back()->with('success', "Import user selesai. Baru: {$created}, Update: {$updated}, Dilewati: {$skipped}.");
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
            . '<sheets><sheet name="Users" sheetId="1" r:id="rId1"/></sheets>'
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

    private function buildHeaderMap(array $headerRow): array
    {
        $map = [];
        foreach ($headerRow as $col => $header) {
            $normalized = Str::of((string) $header)
                ->lower()
                ->replace([' ', '-', '_'], '')
                ->toString();

            if (in_array($normalized, ['name', 'nama'], true)) $map['name'] = $col;
            if (in_array($normalized, ['email'], true)) $map['email'] = $col;
            if (in_array($normalized, ['phone', 'telepon', 'nohp', 'nomorhp'], true)) $map['phone'] = $col;
            if (in_array($normalized, ['role', 'level'], true)) $map['role'] = $col;
            if (in_array($normalized, ['isactive', 'active', 'status'], true)) $map['is_active'] = $col;
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
}
