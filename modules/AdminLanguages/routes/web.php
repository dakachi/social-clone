<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminLanguages\Http\Controllers\AdminLanguagesController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;

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

Route::middleware(['web', 'auth'])->group(function () {
    Route::group(["prefix" => "admin"], function () {

        Route::group(["prefix" => "languages"], function () {
            Route::resource('/', AdminLanguagesController::class)->only(['index'])->names('admin.languages');
            Route::post('list', [AdminLanguagesController::class, 'list'])->name('admin.languages.list');
            Route::post('translations-list/{id}', [AdminLanguagesController::class, 'translationsList'])->name('admin.languages.translations_list');
            Route::post('update-translation/{id}', [AdminLanguagesController::class, 'updateTranslation'])->name('admin.languages.update_translation');
            Route::post('auto-translation/{id}', [AdminLanguagesController::class, 'autoTranslation'])->name('admin.languages.auto_translation');
            
            Route::get('create', [AdminLanguagesController::class, 'update'])->name('admin.languages.create');
            Route::get('edit/{id}', [AdminLanguagesController::class, 'update'])->name('admin.languages.edit');
            Route::get('export/{id}', [AdminLanguagesController::class, 'export'])->name('admin.languages.export');
            Route::post('import', [AdminLanguagesController::class, 'import'])->name('admin.languages.import');
            Route::post('auto-translate/{id}', [AdminLanguagesController::class, 'autoTranslate'])->name('admin.languages.auto_translate');
            Route::post('update-languages/{id}', [AdminLanguagesController::class, 'updateLanguages'])->name('admin.languages.update_languages');
            Route::get('edit-translations/{id}', [AdminLanguagesController::class, 'editTranslations'])->name('admin.languages.edit_translations');
            Route::post('save', [AdminLanguagesController::class, 'save'])->name('admin.languages.save');
            Route::post('destroy', [AdminLanguagesController::class, 'destroy'])->name('admin.languages.destroy');
            Route::post('status/{status}', [AdminLanguagesController::class, 'status'])->name('admin.languages.status');
        });

    });
});

Route::get('/lang/{locale}', function ($locale) {
    $supportedLocales = \Language::getSupportedLocales();

    if (!in_array($locale, $supportedLocales)) {
        $locale = 'en';
    }

    App::setLocale($locale);
    Cookie::queue('locale', $locale, 60 * 24 * 365 * 10);

    $previous = url()->previous();
    $current = url()->current();

    if (!$previous || $previous === $current || str_contains($previous, '/lang/')) {
        return redirect('/')->with('success', __('Language changed successfully.'));
    }

    return redirect()->to($previous)->with('success', __('Language changed successfully.'));
});