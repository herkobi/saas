<?php

use App\Http\Controllers\User\DashboardController;

use App\Http\Controllers\User\Account\Payment\PaymentController;
use App\Http\Controllers\User\Account\Plan\PlanController;
use App\Http\Controllers\User\Account\Profile\ProfileController;
use Illuminate\Support\Facades\Route;
//use App\Models\Link;

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


Route::redirect('/', '/login');
Route::redirect('/admin', '/admin/login');

//Route::redirect('/bsinternet', 'https://api.whatsapp.com/send?phone=905355529128');

// Route::get('/{name}', function (string $name) {
//     return redirect('https://api.whatsapp.com/send?phone=905355529128');
// })->whereAlpha('name', '[A-Za-z]+');

// Route::get('/{link}', function (Link $link) {
//     return redirect($link);
// })->whereAlpha('name', '[A-Za-z]+');

//Route::get('{all}', '\Common\Core\Controllers\HomeController@show')->where('all', '.*');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/plan', [PlanController::class, 'index'])->name('plan');
    Route::get('/payment', [PaymentController::class, 'index'])->name('payment');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
