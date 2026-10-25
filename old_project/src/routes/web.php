<?php

use App\Http\Controllers\RegisterCatController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BackupsController;
use App\Http\Controllers\BirthdayController;
use App\Http\Controllers\CatMarriagesController;
use App\Http\Controllers\CatsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CouplesController;
use App\Http\Controllers\FamilyActionsController;
use App\Http\Controllers\FilesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocalizationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['localization'])->group(function () {
Route::get('/', [CatsController::class, 'search']);

Route::controller(HomeController::class)->group(function () {
    Route::get('home', 'index')->name('home');
    Route::get('profile', 'index')->name('profile');
});

Route::controller(CatsController::class)->group(function () {
    Route::get('profile-search', 'search')->name('cats.search');
    Route::get('cats/{cat}', 'show')->name('cats.show');
    Route::get('cats/{cat}/chart', 'chart')->name('cats.chart');
    Route::get('cats/{cat}/tree', 'tree')->name('cats.tree');
    Route::get('test/{breed}/{breed2}/{cat}/{cat2}', 'test')->name('cats.test');
    Route::get('cats/{cat}/tree/{generations}', 'tree')->name('cats.tree');
    Route::get('cats/{cat}/death', 'death')->name('cats.death');
});

Route::get('birthdays', [BirthdayController::class, 'index'])->name('birthdays.index');
Route::get('birthdays/{month}/{day}', [BirthdayController::class, 'date'])->name('birthdays.date');

/**
 * Admin only routes
 */

Route::controller(RegisterCatController::class)->group(function () {
    Route::get('register-cat', 'index')->name('register-cat');
    Route::post('register-cat', 'create')->name('register-cat');
});
Route::controller(FamilyActionsController::class)->group(function () {
    Route::post('family-actions/{cat}/set-sire', 'setSire')->name('family-actions.set-sire');
    Route::post('family-actions/{cat}/set-dam', 'setDam')->name('family-actions.set-dam');
    Route::post('family-actions/{cat}/set-parent', 'setParent')->name('family-actions.set-parent');
});
Route::controller(CatsController::class)->group(function () {
    Route::get('cats/{cat}/edit', 'edit')->name('cats.edit');
    Route::post('cats/{cat}', 'update')->name('cats.update');
    Route::delete('cats/{cat}', 'destroy')->name('cats.destroy');
});
/**
 * Backup Restore Database Routes
 */
Route::controller(BackupsController::class)->group(function () {
    Route::post('backups/issue', 'issue')->name('backups.issue');
    Route::get('backups/help', 'help')->name('backups.help');
    Route::post('backups/export_cats', 'export_cats')->name('backups.export_cats');
    Route::post('backups/import_cats', 'import_cats')->name('backups.import_cats');
    Route::post('backups/clear_cats', 'clear_cats')->name('backups.clear_cats');
    Route::post('backups/export_breeds', 'export_breeds')->name('backups.export_breeds');
    Route::post('backups/import_breeds', 'import_breeds')->name('backups.import_breeds');
    Route::post('backups/clear_breeds', 'clear_breeds')->name('backups.clear_breeds');
    Route::post('backups/export_ems', 'export_ems')->name('backups.export_ems');
    Route::post('backups/import_ems', 'import_ems')->name('backups.import_ems');
    Route::post('backups/clear_ems', 'clear_ems')->name('backups.clear_ems');
    Route::post('backups/upload', 'upload')->name('backups.upload');
    Route::post('backups/{fileName}/restore', 'restore')->name('backups.restore');
    Route::get('backups/{fileName}/dl', 'download')->name('backups.download');
});
Route::resource('backups', BackupsController::class);

});
Route::post('/language/change', [LocalizationController::class, 'changeLanguage'])->name('language.change');

require __DIR__.'/auth.php';