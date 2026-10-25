<?php

namespace App\Console\Commands;

use App\Legacy\CsvReader;
use App\Legacy\ImportReport;
use App\Legacy\LegacyImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use InvalidArgumentException;

#[Signature('legacy:import {--breeds= : breeds CSV} {--ems= : EMS colours CSV} {--cats= : cats CSV}')]
#[Description('Import CSV files exported by the old Find A Cat backup page')]
class ImportLegacy extends Command
{
    public function handle(LegacyImporter $importer): int
    {
        if (! $this->option('breeds') && ! $this->option('ems') && ! $this->option('cats')) {
            $this->error('Pass at least one of --breeds, --ems, --cats.');

            return self::INVALID;
        }

        try {
            // Order matters: EMS colours point at breeds, cats point at nothing but themselves.
            foreach (['breeds' => 'importBreeds', 'ems' => 'importEms', 'cats' => 'importCats'] as $option => $method) {
                if ($path = $this->option($option)) {
                    $read = new ImportReport;
                    $report = $importer->{$method}(CsvReader::read($path, $read));
                    $this->show($option, $read, $report);
                }
            }
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function show(string $what, ImportReport ...$reports): void
    {
        foreach ($reports as $report) {
            foreach ($report->counts as $key => $n) {
                $this->info("{$what}: {$n} {$key}");
            }
            foreach ($report->warnings as $warning) {
                $this->warn($warning);
            }
        }
    }
}
