<?php

namespace App\Http\Controllers;

use App\Breed;
use App\Cat;
use App\Ems;
use BackupManager\Manager;
use Illuminate\Http\Request;
use League\Flysystem\FileExistsException;
use App\Http\Requests\BackupUploadRequest;
use BackupManager\Filesystems\Destination;
use Illuminate\Support\Facades\Response;
use League\Flysystem\FileNotFoundException;
use Illuminate\Support\Facades\Auth;

/**
 * Database Backups Controller.
 *
 * @author Nafies Luthfi <nafiesL@gmail.com>
 */
class BackupsController extends Controller
{
    /**
     * List of backup files.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        if (Auth::user() && Auth::user()->is_admin) {
            if (!file_exists(storage_path('app/backup/db'))) {
                $backups = [];
            } else {
                $backups = \File::allFiles(storage_path('app/backup/db'));

                // Sort files by modified time DESC
                usort($backups, function ($a, $b) {
                    return -1 * strcmp($a->getMTime(), $b->getMTime());
                });
            }

            return view('backups.index', compact('backups'));
        } else {
            return redirect('/');
        }
    }


    public function help(Request $request)
    {
        if (Auth::user() && Auth::user()->is_admin) {
            return view('backups.help');
        } else {
            return redirect('/');
        }
    }


    /**
     * Display the specified Backup.
     *
     */
    public function show(Request $request)
    {
        if (Auth::user() && Auth::user()->is_admin) {
            return view('backups.issue', [
            ]);
        } else {
            return redirect('/');
        }
    }

    /**
     * Create new backup file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        if (Auth::user() && Auth::user()->is_admin) {
            $this->validate($request, [
                'file_name' => 'nullable|max:30|regex:/^[\w._-]+$/',
            ]);

            try {
                $manager = app()->make(Manager::class);
                $fileName = $request->get('file_name') ?: date('Y-m-d_Hi');

                $manager->makeBackup()->run('mysql', [
                    new Destination('local', 'backup/db/'.$fileName),
                ], 'gzip');

                return redirect()->route('backups.index');
            } catch (FileExistsException $e) {
                return redirect()->route('backups.index');
            }
        } else {
            return redirect('/');
        }
    }

    /**
     * Delete a backup file from storage.
     *
     * @param  string  $fileName
     * @return \Illuminate\Routing\Redirector
     */
    public function destroy($fileName)
    {
        if (Auth::user() && Auth::user()->is_admin) {
            if (file_exists(storage_path('app/backup/db/').$fileName)) {
                unlink(storage_path('app/backup/db/').$fileName);
            }

            return redirect()->route('backups.index');
        } else {
            return redirect('/');
        }
    }

    /**
     * Download a backup file.
     *
     * @param  string  $fileName
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($fileName)
    {
        if (Auth::user() && Auth::user()->is_admin) {
            return response()->download(storage_path('app/backup/db/').$fileName);
        } else {
            return redirect('/');
        }
    }

    /**
     * Restore database from a backup file.
     *
     * @param  string  $fileName
     * @return \Illuminate\Routing\Redirector
     */
    public function restore($fileName)
    {
        if (Auth::user() && Auth::user()->is_admin) {
            try {
                $manager = app()->make(Manager::class);
                $manager->makeRestore()->run('local', 'backup/db/'.$fileName, 'mysql', 'gzip');
            } catch (FileNotFoundException $e) {
            }

            return redirect()->route('backups.index');
        } else {
            return redirect('/');
        }
    }

    /**
     * Upload a backup file to the storage.
     *
     * @param  \App\Http\Requests\BackupUploadRequest  $request
     * @return \Illuminate\Routing\Redirector
     */
    public function upload(BackupUploadRequest $request)
    {
        if (Auth::user() && Auth::user()->is_admin) {
            $file = $request->file('backup_file');

            if (file_exists(storage_path('app/backup/db/').$file->getClientOriginalName()) == false) {
                $file->storeAs('backup/db', $file->getClientOriginalName());
            }

            return redirect()->route('backups.index');
        } else {
            return redirect('/');
        }
    }

    public function clear_cats(Request $request)
    {
        if (Auth::user() && Auth::user()->is_admin) {
            try {
                Cat::truncate();
            } catch (FileNotFoundException $e) {
                return redirect()->route('backups.issue');
            }

            return redirect()->route('backups.index');
        } else {
            return redirect('/');
        }
    }

    public function import_cats(Request $request)
    {
        if (Auth::user() && Auth::user()->is_admin) {
            try {
                $file = $request->file('file');
                if ($file == null) throw new FileNotFoundException("");
                if ($file->getPathname() == "") throw new FileNotFoundException("");
                $fileContents = file($file->getPathname());
        
                foreach ($fileContents as $key=>$line) {
                    if ($key == 0) {
                        continue;
                    }
                    $data = str_getcsv($line);
        
                    Cat::create([
                        'id' => $data[0],
                        'full_name' => $data[1],
                        'gender_id' => $data[2],
                        'sire_id' => $data[3],
                        'dam_id' => $data[4],
                        'dob' => $data[5],
                        'titles_before_name' => $data[6],
                        'titles_after_name' => $data[7],
                        'ems_color' => $data[8],
                        'breed' => $data[9],
                        'chip_number' => $data[10],
                        'genetic_tests' => $data[11],
                        'breeding_station' => $data[12],
                        'country_code' => $data[13],
                        'alternative_name' => $data[14],
                        'print_name_r1' => $data[15],
                        'print_name_r2' => $data[16],
                        'dod' => $data[17],
                        'original_reg_num' => $data[18],
                        'last_reg_num' => $data[19],
                        'reg_num_2' => $data[20],
                        'reg_num_3' => $data[21],
                        'notes' => $data[22],
                        'breeder' => $data[23],
                        'current_owner' => $data[24],
                        'country_of_origin' => $data[25],
                        'country' => $data[26],
                        'ownership_notes' => $data[27],
                        'personal_info' => $data[28],
                        'genetic_tests_file' => $data[29],
                        'photo' => $data[30],
                        'vet_confirmation' => $data[31]
                        // Add more fields as needed
                    ]);
                }
            } catch (FileNotFoundException $e) {
                return redirect()->route('backups.issue');
            }

            return redirect()->route('backups.index');
        } else {
            return redirect('/');
        }
    }

    public function export_cats()
    {
        if (Auth::user() && Auth::user()->is_admin) {
            $cats = Cat::all();
            $csvFileName = 'cats.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $csvFileName . '"',
            ];

            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'full_name', 'gender_id', 'sire_id', 'dam_id', 'dob', 'titles_before_name', 'titles_after_name', 'ems_color', 'breed', 'chip_number', 'genetic_tests', 'breeding_station', 'country_code', 'alternative_name', 'print_name_r1', 'print_name_r2', 'dod', 'original_reg_num', 'last_reg_num', 'reg_num_2', 'reg_num_3', 'notes', 'breeder', 'current_owner', 'country_of_origin', 'country', 'ownership_notes', 'personal_info', 'genetic_tests_file', 'photo', 'vet_confirmation']); // Add more headers as needed

            foreach ($cats as $cat) {
                fputcsv($handle, [$cat->id, $cat->full_name, $cat->gender_id, $cat->sire_id, $cat->dam_id, $cat->dob, $cat->titles_before_name, $cat->titles_after_name, $cat->ems_color, $cat->breed, $cat->chip_number, $cat->genetic_tests, $cat->breeding_station, $cat->country_code, $cat->alternative_name, $cat->print_name_r1, $cat->print_name_r2, $cat->dod, $cat->original_reg_num, $cat->last_reg_num, $cat->reg_num_2, $cat->reg_num_3, $cat->notes, $cat->breeder, $cat->current_owner, $cat->country_of_origin, $cat->country, $cat->ownership_notes, $cat->personal_info, $cat->photo, $cat->vet_confirmation]); // Add more fields as needed
            }

            fclose($handle);

            return Response::make('', 200, $headers);
        } else {
            return redirect('/');
        }
    }

    public function clear_breeds(Request $request)
    {
        if (Auth::user() && Auth::user()->is_admin) {
            try {
                Breed::truncate();
            } catch (FileNotFoundException $e) {
                return redirect()->route('backups.issue');
            }

            return redirect()->route('backups.index');
        } else {
            return redirect('/');
        }
    }

    public function import_breeds(Request $request)
    {
        if (Auth::user() && Auth::user()->is_admin) {
            try {
                $file = $request->file('file');
                if ($file == null) throw new FileNotFoundException("");
                if ($file->getPathname() == "") throw new FileNotFoundException("");
                $fileContents = file($file->getPathname());
        
                foreach ($fileContents as $key=>$line) {
                    if ($key == 0) {
                        continue;
                    }
                    $data = str_getcsv($line);
        
                    Breed::create([
                        'id' => $data[0],
                        'breed' => $data[1],
                        'name' => $data[2]
                        // Add more fields as needed
                    ]);
                }
            } catch (FileNotFoundException $e) {
                return redirect()->route('backups.issue');
            }

            return redirect()->route('backups.index');
        } else {
            return redirect('/');
        }
    }

    public function export_breeds()
    {
        if (Auth::user() && Auth::user()->is_admin) {
            $breeds = Breed::all();
            $csvFileName = 'breeds.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $csvFileName . '"',
            ];

            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'breed', 'name']); // Add more headers as needed

            foreach ($breeds as $breed) {
                fputcsv($handle, [$breed->id, $breed->breed, $breed->name]); // Add more fields as needed
            }

            fclose($handle);

            return Response::make('', 200, $headers);
        } else {
            return redirect('/');
        }
    }

    public function clear_ems(Request $request)
    {
        if (Auth::user() && Auth::user()->is_admin) {
            try {
                Ems::truncate();
            } catch (FileNotFoundException $e) {
                return redirect()->route('backups.issue');
            }

            return redirect()->route('backups.index');
        } else {
            return redirect('/');
        }
    }

    public function import_ems(Request $request)
    {
        if (Auth::user() && Auth::user()->is_admin) {
            try {
                $file = $request->file('file');
                if ($file == null) throw new FileNotFoundException("");
                if ($file->getPathname() == "") throw new FileNotFoundException("");
                $fileContents = file($file->getPathname());
        
                foreach ($fileContents as $key=>$line) {
                    if ($key == 0) {
                        continue;
                    }
                    $data = str_getcsv($line);
        
                    Ems::create([
                        'id' => $data[0],
                        'breed_id' => $data[1],
                        'ems' => $data[2],
                        'english' => $data[3]
                        // Add more fields as needed
                    ]);
                }
            } catch (FileNotFoundException $e) {
                return redirect()->route('backups.issue');
            }

            return redirect()->route('backups.index');
        } else {
            return redirect('/');
        }
    }

    public function export_ems()
    {
        if (Auth::user() && Auth::user()->is_admin) {
            $ems = Ems::all();
            $csvFileName = 'ems.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $csvFileName . '"',
            ];

            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'breed_id', 'ems', 'english']); // Add more headers as needed

            foreach ($ems as $e) {
                fputcsv($handle, [$e->id, $e->breed_id, $e->ems, $e->english]); // Add more fields as needed
            }

            fclose($handle);

            return Response::make('', 200, $headers);
        } else {
            return redirect('/');
        }
    }

}
