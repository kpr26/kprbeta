<?php

namespace App\Services;

use Carbon\Carbon;
use ZipArchive;

class ExcelReaderService
{
    /**
     * Parse file Excel (.xlsx, .xls, .csv) menjadi array baris [ [col0, col1, ...], ... ]
     */
    public function parse(string $filePath, ?string $originalName = null): array
    {
        $ext = strtolower(pathinfo($originalName ?? $filePath, PATHINFO_EXTENSION));

        // Cek apakah file merupakan file ZIP (format standar .xlsx)
        $fh = @fopen($filePath, 'rb');
        $magic = $fh ? fread($fh, 4) : '';
        if ($fh) fclose($fh);

        $isZip = ($magic === "PK\x03\x04");

        if ($isZip || $ext === 'xlsx') {
            return $this->parseXlsx($filePath);
        }

        if (in_array($ext, ['csv', 'txt'], true)) {
            return $this->parseCsv($filePath);
        }

        // Cek apakah .xls berbasis HTML/XML
        $content = @file_get_contents($filePath, false, null, 0, 4096);
        if ($content && (stripos($content, '<table') !== false || stripos($content, '<html') !== false || stripos($content, '<?xml') !== false)) {
            return $this->parseHtmlOrXml($filePath);
        }

        // Fallback: coba CSV parser
        return $this->parseCsv($filePath);
    }

    /**
     * Parse native OpenXML (.xlsx) menggunakan ZipArchive bawaan PHP
     */
    public function parseXlsx(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \RuntimeException("Gagal membuka arsip file Excel (.xlsx).");
        }

        // 1. Ekstrak Shared Strings
        $sharedStrings = [];
        $sstContent = $zip->getFromName('xl/sharedStrings.xml');
        if ($sstContent !== false) {
            $xml = @simplexml_load_string($sstContent);
            if ($xml) {
                foreach ($xml->si as $si) {
                    $text = '';
                    if (isset($si->t)) {
                        $text .= (string)$si->t;
                    } elseif (isset($si->r)) {
                        foreach ($si->r as $r) {
                            $text .= (string)$r->t;
                        }
                    }
                    $sharedStrings[] = $text;
                }
            }
        }

        // 2. Temukan lembar kerja utama (sheet 1)
        $sheetXmlContent = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXmlContent === false) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (str_starts_with($name, 'xl/worksheets/sheet') && str_ends_with($name, '.xml')) {
                    $sheetXmlContent = $zip->getFromIndex($i);
                    break;
                }
            }
        }

        if ($sheetXmlContent === false) {
            $zip->close();
            throw new \RuntimeException("Lembar kerja Excel (worksheet) tidak ditemukan di dalam file.");
        }

        $xml = @simplexml_load_string($sheetXmlContent);
        $rows = [];

        if ($xml && isset($xml->sheetData->row)) {
            foreach ($xml->sheetData->row as $row) {
                $cells = [];
                foreach ($row->c as $c) {
                    $ref = (string)$c['r'];
                    $colIndex = $this->colLetterToIndex($ref);

                    $type = (string)$c['t'];
                    $val = '';
                    if ($type === 's') {
                        $idx = (int)$c->v;
                        $val = $sharedStrings[$idx] ?? '';
                    } elseif ($type === 'inlineStr') {
                        $val = (string)$c->is->t;
                    } else {
                        $val = (string)$c->v;
                    }

                    $cells[$colIndex] = trim((string)$val);
                }

                if (!empty($cells)) {
                    $maxKey = max(array_keys($cells));
                    $rowArr = [];
                    for ($k = 0; $k <= $maxKey; $k++) {
                        $rowArr[$k] = $cells[$k] ?? '';
                    }
                    $rows[] = $rowArr;
                }
            }
        }

        $zip->close();
        return $rows;
    }

    /**
     * Parse file CSV (Otomatis mendeteksi delimiter: koma, titik koma, atau tab)
     */
    public function parseCsv(string $filePath): array
    {
        $rows = [];
        $fh = @fopen($filePath, 'r');
        if (! $fh) {
            return $rows;
        }

        // Deteksi delimiter dari baris pertama
        $firstLine = fgets($fh);
        rewind($fh);

        $delimiter = ',';
        if ($firstLine !== false) {
            $countComma = substr_count($firstLine, ',');
            $countSemi = substr_count($firstLine, ';');
            $countTab = substr_count($firstLine, "\t");

            if ($countSemi > $countComma && $countSemi >= $countTab) {
                $delimiter = ';';
            } elseif ($countTab > $countComma && $countTab > $countSemi) {
                $delimiter = "\t";
            }
        }

        while (($data = fgetcsv($fh, 0, $delimiter)) !== false) {
            // Bersihkan BOM jika ada di sel pertama
            if (!empty($data) && isset($data[0])) {
                $data[0] = preg_replace('/^\xEF\xBB\xBF/', '', $data[0]);
            }
            $cleanRow = array_map(fn($v) => trim((string)$v), $data);
            if (!empty(array_filter($cleanRow, fn($v) => $v !== ''))) {
                $rows[] = $cleanRow;
            }
        }
        fclose($fh);

        return $rows;
    }

    /**
     * Parse file HTML/XML yang disimpan dengan ekstensi .xls
     */
    public function parseHtmlOrXml(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $rows = [];

        // Coba ekstrak tabel HTML
        if (preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $content, $trMatches)) {
            foreach ($trMatches[1] as $tr) {
                if (preg_match_all('/<(?:td|th)[^>]*>(.*?)<\/(?:td|th)>/is', $tr, $tdMatches)) {
                    $row = [];
                    foreach ($tdMatches[1] as $cell) {
                        $cellClean = html_entity_decode(strip_tags($cell), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        $row[] = trim($cellClean);
                    }
                    if (!empty(array_filter($row, fn($v) => $v !== ''))) {
                        $rows[] = $row;
                    }
                }
            }
        }

        return $rows;
    }

    /**
     * Konversi koordinat cell (misal 'A1' -> 0, 'B1' -> 1, 'AA1' -> 26)
     */
    protected function colLetterToIndex(string $cellRef): int
    {
        if (preg_match('/^([A-Z]+)\d+$/i', $cellRef, $m)) {
            $col = strtoupper($m[1]);
            $len = strlen($col);
            $num = 0;
            for ($i = 0; $i < $len; $i++) {
                $num = $num * 26 + (ord($col[$i]) - ord('A') + 1);
            }
            return $num - 1;
        }
        return 0;
    }

    /**
     * Parser Tanggal Cerdas (Mendukung Excel serial date, m/d/Y, d/m/Y, Y-m-d)
     */
    public static function parseDate(mixed $val, string $fallbackFormat = 'm/d/Y'): ?string
    {
        if ($val === null || $val === '') {
            return null;
        }

        // 1. Cek Excel Serial Date (e.g. 46054 untuk 2026-02-01)
        if (is_numeric($val) && (float)$val > 20000 && (float)$val < 80000) {
            $unix = ((float)$val - 25569) * 86400;
            return gmdate('Y-m-d', (int)round($unix));
        }

        $str = trim((string)$val);
        if ($str === '') {
            return null;
        }

        // 2. Format YYYY-MM-DD atau YYYY/MM/DD
        if (preg_match('/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})$/', $str, $m)) {
            return sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
        }

        // 3. Format D/M/Y atau M/D/Y (misal 2/1/2026 atau 2/13/2026)
        if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $str, $m)) {
            $p1 = (int)$m[1];
            $p2 = (int)$m[2];
            $year = (int)$m[3];

            if ($p1 > 12 && $p2 <= 12) {
                // p1 adalah Hari, p2 adalah Bulan (e.g. 23/02/2026)
                return sprintf('%04d-%02d-%02d', $year, $p2, $p1);
            }
            if ($p2 > 12 && $p1 <= 12) {
                // p1 adalah Bulan, p2 adalah Hari (e.g. 02/23/2026)
                return sprintf('%04d-%02d-%02d', $year, $p1, $p2);
            }

            // Keduanya <= 12: gunakan fallbackFormat (default M/D/Y sesuai gambar contoh 2/1/2026)
            if ($fallbackFormat === 'd/m/Y') {
                return sprintf('%04d-%02d-%02d', $year, $p2, $p1);
            } else {
                return sprintf('%04d-%02d-%02d', $year, $p1, $p2);
            }
        }

        // 4. Fallback dengan Carbon
        try {
            return Carbon::parse($str)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Parser Angka Stand Meter (Toleran terhadap koma, titik ribuan, atau spasi)
     */
    public static function parseDecimal(mixed $val): ?float
    {
        if ($val === null || $val === '') {
            return null;
        }
        if (is_numeric($val)) {
            return (float)$val;
        }

        $str = trim((string)$val);
        // Hapus karakter non angka, titik, koma
        $str = preg_replace('/[^\d.,+-]/', '', $str);
        if ($str === '' || $str === '-' || $str === '+') {
            return null;
        }

        $lastDot = strrpos($str, '.');
        $lastComma = strrpos($str, ',');
        if ($lastDot !== false && $lastComma !== false) {
            if ($lastComma > $lastDot) {
                // Titik adalah ribuan, koma adalah desimal (misal 10.271,856)
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            } else {
                // Koma adalah ribuan, titik adalah desimal (misal 10,271.856)
                $str = str_replace(',', '', $str);
            }
        } elseif ($lastComma !== false) {
            // Koma sebagai desimal (misal 10271,86)
            $str = str_replace(',', '.', $str);
        }

        return is_numeric($str) ? (float)$str : null;
    }

    /**
     * Generate file template Excel (.xlsx) siap pakai untuk Water Meter Public Area
     */
    public function generateWaterTemplateXlsx(?int $year = null, ?int $month = null): string
    {
        $year = $year ?? (int)date('Y');
        $month = $month ?? (int)date('n');

        $tempFile = tempnam(sys_get_temp_dir(), 'kpr_xlsx_');
        $zip = new ZipArchive();
        $zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
            '<Default Extension="xml" ContentType="application/xml"/>' .
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
            '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
            '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStringItem+xml"/>' .
            '</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        // _rels/.rels
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
            '</Relationships>';
        $zip->addFromString('_rels/.rels', $rels);

        // xl/_rels/workbook.xml.rels
        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>' .
            '</Relationships>';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);

        // xl/workbook.xml
        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
            '<sheets><sheet name="Water Meter PA" sheetId="1" r:id="rId1" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"/></sheets>' .
            '</workbook>';
        $zip->addFromString('xl/workbook.xml', $workbook);

        // Header string (Persis sesuai gambar lampiran pengguna)
        $sharedStrings = [
            'Tanggal',
            'Water meter Cooling Tower 12A',
            'Water Meter Toilet Sky Lobby 11 & Musholla 12',
            'Water meter Pantry office',
        ];

        // Buat baris tanggal sesuai jumlah hari dalam bulan terpilih
        $daysInMonth = (int)date('t', strtotime(sprintf('%04d-%02d-01', $year, $month)));
        $dateStrings = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateStr = sprintf('%d/%d/%d', $month, $d, $year); // Format M/d/YYYY persis lampiran
            $sharedStrings[] = $dateStr;
            $dateStrings[$d] = count($sharedStrings) - 1;
        }

        // Shared strings XML
        $sstXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($sharedStrings) . '" uniqueCount="' . count($sharedStrings) . '">';
        foreach ($sharedStrings as $str) {
            $sstXml .= '<si><t>' . htmlspecialchars($str, ENT_XML1) . '</t></si>';
        }
        $sstXml .= '</sst>';
        $zip->addFromString('xl/sharedStrings.xml', $sstXml);

        // Sheet1 XML
        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
        // Header
        $sheetXml .= '<row r="1"><c r="A1" t="s"><v>0</v></c><c r="B1" t="s"><v>1</v></c><c r="C1" t="s"><v>2</v></c><c r="D1" t="s"><v>3</v></c></row>';

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $rowNum = $d + 1;
            $sIdx = $dateStrings[$d];
            $sheetXml .= "<row r=\"{$rowNum}\"><c r=\"A{$rowNum}\" t=\"s\"><v>{$sIdx}</v></c><c r=\"B{$rowNum}\"/><c r=\"C{$rowNum}\"/><c r=\"D{$rowNum}\"/></row>";
        }
        $sheetXml .= '</sheetData></worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

        $zip->close();
        $content = file_get_contents($tempFile);
        @unlink($tempFile);

        return $content;
    }
}
