<?php

use Illuminate\Support\Facades\Route;
use Modules\Guest\Http\Controllers\GuestController;

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

Route::get('', [ GuestController::class, 'index' ])->name('home');
Route::get('pricing', [ GuestController::class, 'pricing' ])->name('pricing');
Route::get('faqs', [ GuestController::class, 'faqs' ])->name('faqs');
Route::get('contact', [ GuestController::class, 'contact' ])->name('contact');
Route::get('about', [ GuestController::class, 'about' ])->name('about');
Route::get('blogs', [ GuestController::class, 'blogs' ])->name('blogs');
Route::get('blogs/{cate_slug}', [ GuestController::class, 'blogs' ])->name('blog.category');
Route::get('blogs/tag/{tag_slug}', [ GuestController::class, 'blogs' ])->name('blog.tag');
Route::get('blog-detail/{blog_slug}', [ GuestController::class, 'blogDetail' ])->name('blog.detail');
Route::get('page-not-found', [ GuestController::class, 'pageNotFound' ])->name('page_not_found');
Route::get('privacy-policy', [ GuestController::class, 'privacyPolicy' ])->name('privacy_policy');
Route::get('terms-of-service', [ GuestController::class, 'termsOfService' ])->name('terms_of_service');
