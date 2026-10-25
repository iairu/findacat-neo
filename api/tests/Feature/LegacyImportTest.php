<?php

namespace Tests\Feature;

use App\Legacy\CsvReader;
use App\Legacy\ImportReport;
use App\Legacy\LegacyImporter;
use App\Models\Breed;
use App\Models\Cat;
use App\Models\EmsColor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyImportTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(string $name): array
    {
        return CsvReader::read(__DIR__.'/../fixtures/legacy/'.$name, new ImportReport);
    }

    private function importCats(): ImportReport
    {
        return (new LegacyImporter)->importCats($this->fixture('cats.csv'));
    }

    public function test_breeds_and_ems_colours_are_imported(): void
    {
        $importer = new LegacyImporter;
        $importer->importBreeds($this->fixture('breeds.csv'));
        $report = $importer->importEms($this->fixture('ems.csv'));

        $this->assertSame('Persian', Breed::find('2')->name);
        $this->assertNull(Breed::find('3')->name);
        $this->assertSame('black', EmsColor::find('2')->english_name);
        $this->assertSame('2', EmsColor::find('2')->breed_id);
        $this->assertNull(EmsColor::find('3')->breed_id);
        $this->assertStringContainsString('unknown breed 99', $report->warnings[0]);
    }

    public function test_placeholder_cat_is_dropped_and_links_become_null(): void
    {
        $report = $this->importCats();

        $this->assertNull(Cat::find('u1'));
        $this->assertSame(1, $report->counts['placeholders_dropped']);
        $this->assertNull(Cat::find('a')->sire_id);
        $this->assertNull(Cat::find('a')->dam_id);
    }

    public function test_pedigree_links_and_fields_are_mapped(): void
    {
        $this->importCats();

        $kitten = Cat::find('e');
        $this->assertSame('c', $kitten->sire_id);
        $this->assertSame('d', $kitten->dam_id);
        $this->assertSame(Cat::FEMALE, $kitten->sex);
        $this->assertSame('2004-06-01', $kitten->born_on->toDateString());

        $grandSire = Cat::find('a');
        $this->assertSame('Ch', $grandSire->title_prefix);
        $this->assertSame('del Fiore', $grandSire->title_suffix);
        $this->assertNull(Cat::find('b')->title_suffix, "'-' means empty");
        $this->assertSame('Doe, John', Cat::find('b')->breeder, 'quoted commas survive');
        $this->assertSame(['doo' => '2003-03-01'], Cat::find('c')->metadata);
        $this->assertSame('2015-01-02', Cat::find('c')->died_on->toDateString());
    }

    public function test_unknown_sentinel_and_invalid_dates_become_null(): void
    {
        $report = $this->importCats();

        $this->assertNull(Cat::find('c')->born_on);
        $this->assertContains("Sire date of birth '2003-02-30' is not a valid date; left empty.", $report->warnings);
    }

    public function test_reference_to_missing_parent_is_dropped_with_a_warning(): void
    {
        $report = $this->importCats();

        $this->assertNull(Cat::find('f')->sire_id);
        $this->assertSame('d', Cat::find('f')->dam_id);
        $this->assertContains('Stray Kitten (f) refers to unknown sire_id missing; link left empty.', $report->warnings);
    }

    public function test_ancestry_cycles_are_broken(): void
    {
        $report = $this->importCats();

        $x = Cat::find('x');
        $y = Cat::find('y');
        $this->assertTrue($x->sire_id === null || $y->sire_id === null, 'one link of the loop must go');
        $this->assertCount(1, array_filter($report->warnings, fn ($w) => str_contains($w, 'its own ancestor')));
    }

    public function test_importing_twice_updates_instead_of_duplicating(): void
    {
        $this->importCats();
        $this->importCats();

        $this->assertSame(8, Cat::count());
        $this->assertSame('c', Cat::find('e')->sire_id);
    }

    public function test_reader_repairs_the_known_export_defect(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'cats');
        // Header lists genetic_tests_file, the old exporter wrote no value for it.
        file_put_contents($path, "id,full_name,genetic_tests_file,photo\n1,Tom,pic.jpg\n");
        $report = new ImportReport;

        $rows = CsvReader::read($path, $report);
        unlink($path);

        $this->assertNull($rows[0]['genetic_tests_file']);
        $this->assertSame('pic.jpg', $rows[0]['photo']);
        $this->assertSame([], $report->warnings);
    }
}
