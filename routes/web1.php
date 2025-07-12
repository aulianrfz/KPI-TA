<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportExcelController;
use App\Http\Controllers\PenjadwalanController;
use App\Http\Controllers\MyEventController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ProvinsiController;
use App\Http\Controllers\InstitusiController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\MataLombaController;
use App\Http\Controllers\PendaftaranController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardUserController;
use App\Http\Controllers\LaporanPenjualanController;
use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\KehadiranController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\JuriController;
use App\Http\Middleware\RoleMiddleware;

// Route::get('/', function () {
//     return view('welcome');
// });


// Guest only
Route::get('/', [DashboardUserController::class, 'index'])->name('landing');
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
        return view(auth()->user()->role === 'admin' ? 'admin.dashboard.home' : 'landing');
    })->name('dashboard');

    Route::middleware([RoleMiddleware::class . ':user'])->group(function () {
        Route::get('/event/{eventId}', [DashboardUserController::class, 'showEvent'])->name('event.list');
        Route::get('/event/list/{kategori_id}', [DashboardUserController::class, 'showCategory'])->name('event.showCategory');
        Route::get('/event/detail/{id}', [DashboardUserController::class, 'showDetail'])->name('event.detail');

        // Pendaftaran
        Route::get('/pendaftaran/{id_mataLomba}', [PendaftaranController::class, 'showForm'])->name('pendaftaran.form');
        Route::post('/pendaftaran/store', [PendaftaranController::class, 'store'])->name('pendaftaran.store');

        // dashboard myevent
        Route::get('/my-event', [MyEventController::class, 'index'])->name('events.list');
        Route::get('/my-event/{eventId}/lomba', [MyEventController::class, 'detailEvent'])->name('events.lomba.detail');

        // pembayaran
        Route::get('/pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
        Route::get('/pembayaran/bayar/{id}', [PembayaranController::class, 'bayar'])->name('pembayaran.bayar');
        Route::post('pembayaran/{id}/upload', [PembayaranController::class, 'uploadBuktiPembayaran'])->name('pembayaran.upload');

    });

    // ADMIN ROUTES
    Route::middleware([RoleMiddleware::class . ':admin'])->group(function () {
        Route::resource('listevent', EventController::class);
        Route::resource('kategori', KategoriController::class);
        Route::resource('mataLomba', MataLombaController::class);
        Route::resource('juri', JuriController::class);
        Route::resource('venue', VenueController::class);
        Route::resource('provinsi', ProvinsiController::class);
        Route::resource('institusi', InstitusiController::class);
        Route::resource('jurusan', JurusanController::class);


        Route::get('/listcrud', [DashboardAdminController::class, 'listCrud'])->name('admin.list.crud');
        Route::get('/dashboardadmin', [DashboardAdminController::class, 'index'])->name('admin.dashboard');
        Route::post('/admin/mark-present', [DashboardAdminController::class, 'markAsPresent'])->name('admin.markPresent');
        Route::get('/admin/peserta/{id}/identitas', [DashboardAdminController::class, 'showIdentitas'])->name('admin.peserta.identitas');
        Route::get('/verifikasi/qr/{id}', [DashboardAdminController::class, 'verifikasiQR'])->name('verifikasi.qr');
        Route::get('/admin/export', [DashboardAdminController::class, 'exportExcel'])->name('admin.export');

        //pendaftaran
        Route::get('/admin/keloladaftar/byevent', [KelolaPendaftarController::class, 'pilihEvent'])->name('pendaftaran.pilih-event');
        Route::get('/pendaftaran/event/{event}', [KelolaPendaftarController::class, 'pilihTipePendaftar'])->name('pendaftaran.pilih-tipe');

        Route::get('/event/{event}/peserta', [KelolaPendaftarController::class, 'formPeserta'])->name('admin.pendaftaran.peserta');
        Route::get('/event/{event}/pendamping', [KelolaPendaftarController::class, 'formPendamping'])->name('admin.pendaftaran.pendamping');
        Route::get('/event/{event}/supporter', [KelolaPendaftarController::class, 'formSupporter'])->name('admin.pendaftaran.supporter');

// Sudah benar
Route::get('/admin/pendaftaran/peserta/{id}/edit', [KelolaPendaftarController::class, 'editPeserta'])->name('pendaftaran.peserta.edit');
Route::put('/admin/pendaftaran/peserta/{id}', [KelolaPendaftarController::class, 'updatePeserta'])->name('pendaftaran.peserta.update');

        Route::delete('/admin/pendaftaran/peserta/{id}', [KelolaPendaftarController::class, 'destroyPeserta'])->name('pendaftaran.peserta.destroy');

        
        //laporan penjualan
        // Route::get('/laporanpenjualan', [LaporanPenjualanController::class, 'index'])->name('admin.laporan.penjualan');
        // Route::get('/laporan-penjualan', [LaporanPenjualanController::class, 'index'])->name('laporan.penjualan');
        // Route::get('/laporan-penjualan/{institusi?}', [LaporanPenjualanController::class, 'detail'])->name('laporan.penjualan.detail');

        Route::get('/laporan-penjualan/pilih', [LaporanPenjualanController::class, 'pilihEvent'])->name('laporan.penjualan.pilih');
        Route::get('/laporan-penjualan/event/{event}', [LaporanPenjualanController::class, 'index'])->name('laporan.penjualan');
        Route::get('/laporan-penjualan/event/{event}/detail/{institusi}', [LaporanPenjualanController::class, 'detail'])->name('laporan.penjualan.detail');

        //kehairan
        Route::get('/kehadiran/event', [KehadiranController::class, 'event'])->name('kehadiran.event');
        Route::get('/kehadiran/kategori/{kategori_id}', [KehadiranController::class, 'kategori'])->name('kehadiran.kategori');
        Route::get('/kehadiran/mataLomba/{id}', [KehadiranController::class, 'mataLomba'])->name('kehadiran.mataLomba');
        Route::get('/admin/kehadiran/mata-lomba/{mataLombaId}', [KehadiranController::class, 'index'])->name('kehadiran.mata-lomba');


        Route::get('/kehadiran', [KehadiranController::class, 'index'])->name('kehadiran.index');
        Route::get('/kehadiran/{id}/qr', [KehadiranController::class, 'showQR'])->name('admin.qr.show');
        Route::get('/kehadiran/{id}/edit', [KehadiranController::class, 'edit'])->name('kehadiran.edit');
        Route::put('/kehadiran/{id}', [KehadiranController::class, 'update'])->name('kehadiran.update');
        Route::get('/kehadiran-export', [KehadiranController::class, 'exportExcel'])->name('kehadiran.export');

        //transaksi
        Route::get('/admin/transaksi', [PembayaranController::class, 'show'])->name('transaksi.index');
        Route::post('/admin/transaksi/bulk-action', [PembayaranController::class, 'bulkAction'])->name('admin.transaksi.bulkAction');
        Route::get('/verifikasi/qr/{id}', [PembayaranController::class, 'showQr'])->name('verifikasi.qr');

        //jadwal
        Route::get('/jadwal', [PenjadwalanController::class, 'index'])->name('jadwal.index');
        Route::get('/jadwal/create', [PenjadwalanController::class, 'create'])->name('jadwal.create');
        Route::post('/jadwal/create/step2', [PenjadwalanController::class, 'createStep2'])->name('jadwal.create.step2');
        Route::get('/jadwal/create/step2', [PenjadwalanController::class, 'showStep2'])->name('jadwal.create.step2');
        Route::post('/jadwal/store', [PenjadwalanController::class, 'store'])->name('jadwal.store');
        // Route::post('/jadwal/create-step3', [PenjadwalanController::class, 'createStep3'])->name('jadwal.create.step3');
        Route::match(['get', 'post'], '/jadwal/create-step3', [PenjadwalanController::class, 'createStep3'])->name('jadwal.create.step3');

        Route::get('/jadwal/{id}/change', [PenjadwalanController::class, 'change'])->name('jadwal.change');
        Route::get('/jadwal/{id}/detail', [PenjadwalanController::class, 'detail'])->name('jadwal.detail');
        Route::get('/jadwal/status', [PenjadwalanController::class, 'getStatus'])->name('jadwal.status');
        Route::get('/jadwal/refresh', [PenjadwalanController::class, 'refresh'])->name('jadwal.refresh');


        Route::get('/jadwal/{nama_jadwal}/{tahun}/{version}/switch', [PenjadwalanController::class, 'switchJadwal'])->name('jadwal.switch');
        Route::post('/jadwal/switch/proses', [PenjadwalanController::class, 'prosesSwitch'])->name('jadwal.switch.proses');
        Route::get('/jadwal/{id}/edit', [PenjadwalanController::class, 'edit'])->name('jadwal.edit');
        Route::put('/jadwal/{id}', [PenjadwalanController::class, 'update'])->name('jadwal.update');
        Route::get('/jadwal/{nama_jadwal}/{tahun}/{version}/create', [PenjadwalanController::class, 'createWithDetail'])->name('jadwal.create.withDetail');
        Route::post('/jadwal/add', [PenjadwalanController::class, 'add'])->name('jadwal.add');
        Route::delete('/jadwal/{id}', [PenjadwalanController::class, 'destroy'])->name('jadwal.destroy');

        Route::get('/generate-variabel-x', [PenjadwalanController::class, 'generateVariabelX']);
        Route::delete('/jadwal/{id}/delete', [PenjadwalanController::class, 'destroyJadwal'])->name('jadwal.destroyJadwal');
    });
});



Route::get('/import-excel', [ImportExcelController::class, 'import_excel']);
Route::post('/import-excel', [ImportExcelController::class, 'import_excel_post'])->name('import_excel_post');

Route::post('/generate-schedule', [PenjadwalanController::class, 'generateSchedule']);

// nyoba halaman sukses
// Route::get('/sukses', [PendaftaranController::class, 'sukses']);
