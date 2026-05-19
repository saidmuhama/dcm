<?php
/**
 * SimpleXlsxReader — zero-dependency XLSX reader using PHP's built-in ZipArchive + SimpleXML.
 * Handles shared strings, rich-text runs, and sparse rows (empty cells create null slots).
 */
class SimpleXlsxReader {

    private ?ZipArchive $zip     = null;
    private array $sharedStrings = [];   // index → string value
    private array $sheetMap      = [];   // sheet name → xl-relative path (e.g. worksheets/sheet2.xml)

    /* ── Open ─────────────────────────────────────────────── */
    public function open(string $path): bool {
        $this->zip = new ZipArchive();
        if ($this->zip->open($path) !== true) {
            $this->zip = null;
            return false;
        }
        $this->loadSharedStrings();
        $this->loadSheetMap();
        return true;
    }

    public function close(): void {
        if ($this->zip) { $this->zip->close(); $this->zip = null; }
    }

    public function getSheetNames(): array {
        return array_keys($this->sheetMap);
    }

    /**
     * Return all rows of a named sheet as arrays of scalar values.
     * Empty cells become null.  Header row is row 0.
     */
    public function getRows(string $sheetName, bool $skipHeader = false): ?array {
        if (!isset($this->sheetMap[$sheetName])) return null;

        $target = $this->sheetMap[$sheetName];
        // target from rels is relative to xl/, e.g. "worksheets/sheet2.xml"
        $xml = $this->zip->getFromName("xl/$target");
        if ($xml === false) {
            $xml = $this->zip->getFromName($target);   // fallback if already absolute
        }
        if ($xml === false) return null;

        $rows = $this->parseSheet($xml);
        if ($skipHeader && !empty($rows)) array_shift($rows);
        return $rows;
    }

    /**
     * Auto-detect the sheet whose first row matches $expectedHeaders.
     * Returns rows (header stripped).  Returns null if no sheet matches.
     */
    public function findSheetByHeaders(array $expectedHeaders): ?array {
        foreach ($this->sheetMap as $name => $target) {
            $rows = $this->getRows($name, false);
            if (!$rows) continue;
            $header = array_map(fn($v) => trim((string)$v), $rows[0]);
            $match  = true;
            foreach ($expectedHeaders as $h) {
                if (!in_array(trim($h), $header, true)) { $match = false; break; }
            }
            if ($match) {
                array_shift($rows);
                return $rows;
            }
        }
        return null;
    }

    /* ── Internal: shared strings ─────────────────────────── */
    private function loadSharedStrings(): void {
        $xml = $this->zip->getFromName('xl/sharedStrings.xml');
        if (!$xml) return;
        $dom = @simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (!$dom) return;
        foreach ($dom->si as $si) {
            if (isset($si->t)) {
                $this->sharedStrings[] = (string)$si->t;
            } else {
                $parts = [];
                foreach ($si->r ?? [] as $r) {
                    $parts[] = (string)$r->t;
                }
                $this->sharedStrings[] = implode('', $parts);
            }
        }
    }

    /* ── Internal: sheet name → file path map ─────────────── */
    private function loadSheetMap(): void {
        $wbXml = $this->zip->getFromName('xl/workbook.xml');
        if (!$wbXml) return;
        $wb = @simplexml_load_string($wbXml);
        if (!$wb) return;

        // Build rId → target from rels
        $relsXml     = $this->zip->getFromName('xl/_rels/workbook.xml.rels');
        $rIdToTarget = [];
        if ($relsXml) {
            $rels = @simplexml_load_string($relsXml);
            if ($rels) {
                foreach ($rels->Relationship as $rel) {
                    $rIdToTarget[(string)$rel['Id']] = (string)$rel['Target'];
                }
            }
        }

        foreach ($wb->sheets->sheet as $sheet) {
            $name = (string)$sheet['name'];
            // r:id lives in the relationships namespace
            $rAttrs = $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
            $rId    = $rAttrs ? (string)$rAttrs['id'] : '';
            if (!$rId) {
                // Try the compact prefix form
                foreach ($sheet->attributes() as $k => $v) {
                    if (str_contains($k, 'id') || str_contains((string)$k, 'Id')) { $rId = (string)$v; break; }
                }
            }
            if ($rId && isset($rIdToTarget[$rId])) {
                $this->sheetMap[$name] = $rIdToTarget[$rId];
            }
        }

        // Fallback: if sheetMap is still empty, enumerate sheet XMLs directly
        if (empty($this->sheetMap)) {
            for ($i = 1; $i <= 20; $i++) {
                $path = "xl/worksheets/sheet{$i}.xml";
                if ($this->zip->getFromName($path) !== false) {
                    $this->sheetMap["Sheet{$i}"] = "worksheets/sheet{$i}.xml";
                } else {
                    break;
                }
            }
        }
    }

    /* ── Internal: parse sheet XML → array of rows ────────── */
    private function parseSheet(string $xml): array {
        $dom = @simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (!$dom || !isset($dom->sheetData)) return [];

        $rows = [];
        foreach ($dom->sheetData->row as $xmlRow) {
            $cells      = [];
            $maxColIdx  = 0;
            foreach ($xmlRow->c as $cell) {
                $ref    = (string)$cell['r'];
                $colIdx = $this->colLetterToIndex($ref);
                $cells[$colIdx] = $this->cellValue($cell);
                if ($colIdx > $maxColIdx) $maxColIdx = $colIdx;
            }
            $rowArr = [];
            for ($i = 1; $i <= $maxColIdx; $i++) {
                $rowArr[] = $cells[$i] ?? null;
            }
            $rows[] = $rowArr;
        }
        return $rows;
    }

    /* ── Internal: resolve cell value ─────────────────────── */
    private function cellValue(SimpleXMLElement $cell): mixed {
        $type  = (string)($cell['t'] ?? '');
        $value = isset($cell->v) ? (string)$cell->v : null;
        if ($value === null || $value === '') return null;

        return match ($type) {
            's'         => $this->sharedStrings[(int)$value] ?? null,  // shared string
            'inlineStr' => isset($cell->is->t) ? (string)$cell->is->t : null,
            'b'         => $value === '1' ? 'TRUE' : 'FALSE',
            default     => $value,  // numeric, formula-result-number, etc.
        };
    }

    /* ── Internal: "AB12" → 1-based column index ──────────── */
    private function colLetterToIndex(string $cellRef): int {
        preg_match('/^([A-Z]+)/', strtoupper($cellRef), $m);
        $letters = $m[1] ?? 'A';
        $idx     = 0;
        foreach (str_split($letters) as $ch) {
            $idx = $idx * 26 + (ord($ch) - 64);
        }
        return $idx;
    }
}
