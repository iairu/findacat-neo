<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Legacy\CsvReader;
use App\Legacy\ImportReport;
use App\Legacy\LegacyExporter;
use App\Legacy\LegacyImporter;
use App\Models\Breed;
use App\Models\Cat;
use App\Models\EmsColor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminBackupController extends Controller
{
    public function export(string $table, LegacyExporter $exporter): StreamedResponse
    {
        abort_unless(in_array($table, LegacyExporter::TABLES, true), 404);

        return response()->streamDownload(
            $exporter->stream($table),
            "findacat-{$table}-".now()->format('Y-m-d').'.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    /**
     * @return array{data: array{counts: array<string, int>, warnings: list<string>}}
     */
    public function import(Request $request, string $table, LegacyImporter $importer): array
    {
        abort_unless(in_array($table, LegacyExporter::TABLES, true), 404);
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:51200']);

        $rows = CsvReader::read($request->file('file')->getRealPath(), $report = new ImportReport);
        $result = match ($table) {
            'cats' => $importer->importCats($rows),
            'breeds' => $importer->importBreeds($rows),
            'ems' => $importer->importEms($rows),
        };

        return ['data' => [
            'counts' => $result->counts,
            'warnings' => array_slice([...$report->warnings, ...$result->warnings], 0, 200),
        ]];
    }

    /** Wipes one table. The caller must send `confirm: "DELETE"` so a stray request cannot do it. */
    public function clear(Request $request, string $table): JsonResponse
    {
        abort_unless(in_array($table, LegacyExporter::TABLES, true), 404);
        $request->validate(['confirm' => ['required', Rule::in(['DELETE'])]]);

        match ($table) {
            'cats' => Cat::query()->delete(),
            'breeds' => Breed::query()->delete(),
            'ems' => EmsColor::query()->delete(),
        };

        return response()->json(null, 204);
    }
}
