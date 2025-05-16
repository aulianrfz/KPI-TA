<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportExcelController;
use App\Http\Controllers\PenjadwalanController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\SubKategoriController;
use App\Http\Controllers\PendaftaranController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardUserController;
use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\RoleMiddleware;


// Route::get('/', function () {
//     return view('welcome');
// });


// Guest only
Route::get('/', [DashboardUserController::class, 'index']);
Route::get('/events/{id}', [DashboardUserController::class, 'show'])->name('event.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

 //profile
 Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show')->middleware('auth');
 Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
 Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');


// Pengaturan akses route
Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function () {
        return view(auth()->user()->role === 'admin' ? 'admin.dashboard' : 'landing');
    })->name('dashboard');

    Route::middleware([RoleMiddleware::class . ':user'])->group(function () {
        Route::get('/event/{eventId}', [DashboardUserController::class, 'showEvent'])->name('event.list');
        Route::get('/event/list/{kategori_id}', [DashboardUserController::class, 'showCategory'])->name('event.showCategory');
        Route::get('/event/detail/{id}', [DashboardUserController::class, 'showDetail'])->name('event.detail');
        Route::resource('subkategori', SubKategoriController::class);

        // Pendaftaran
        Route::get('/pendaftaran/{id_subkategori}', [PendaftaranController::class, 'showForm'])->name('pendaftaran.form');
        Route::post('/pendaftaran/store', [PendaftaranController::class, 'store'])->name('pendaftaran.store');

        // // pembayaran
        // Route::get('/my-event', [DashboardUserController::class, 'index'])->name('events.index');

        // dashboard myevent
        Route::get('/my-event', [EventController::class, 'index'])->name('events.index');
        // pembayaran
        Route::get('/pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
        Route::get('/pembayaran/bayar/{id}', [PembayaranController::class, 'bayar'])->name('pembayaran.bayar');
        Route::post('pembayaran/{id}/upload', [PembayaranController::class, 'uploadBuktiPembayaran'])->name('pembayaran.upload');

    });

    // ADMIN ROUTES
    Route::middleware([RoleMiddleware::class . ':admin'])->group(function () {
        Route::resource( 'kategori', KategoriController::class);
        Route::resource('subkategori', SubKategoriController::class);
        Route::resource('juri', JuriController::class);
        Route::get('/dashboardadmin', [DashboardAdminController::class, 'index'])->name('admin.dashboard');
        Route::post('/dashboard-admin/mark-present', [DashboardAdminController::class, 'markAsPresent'])->name('admin.markPresent');

    
        Route::get('/admin/transaksi', [PembayaranController::class, 'show'])->name('transaksi.index');
        Route::post('/admin/transaksi/bulk-action', [PembayaranController::class, 'bulkAction'])->name('admin.transaksi.bulkAction');
        Route::get('/verifikasi/qr/{id}', [PembayaranController::class, 'showQr'])->name('verifikasi.qr');

    });
});



Route::get('/import-excel', [ImportExcelController::class, 'import_excel']);
Route::post('/import-excel', [ImportExcelController::class, 'import_excel_post'])->name('import_excel_post');

Route::post('/generate-schedule', [PenjadwalanController::class, 'generateSchedule']);

// nyoba halaman sukses
// Route::get('/sukses', [PendaftaranController::class, 'sukses']);
