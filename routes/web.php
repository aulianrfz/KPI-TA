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
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\SupporterController;
use App\Http\Controllers\PembimbingController;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\KuisionerController;
use App\Http\Controllers\SertifikatController;
use App\Http\Controllers\KelolaPendaftarController;



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

    Route::get('/register/admin', [AuthController::class, 'showAdminRegisterForm'])->name('register.admin.form');
    Route::post('/register/admin', [AuthController::class, 'registerAdmin'])->name('register.admin');

    // forgot
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'handleForgotPassword'])->name('password.email');

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset.form');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');


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
        Route::get('/my-event/detail/{id}', [MyEventController::class, 'showDetail'])->name('my-event.detail');
        Route::get('/kuisioner/isi/{peserta}', [MyEventController::class, 'isi'])->name('kuisioner.isi');
        Route::post('/kuisioner/simpan/{peserta}', [MyEventController::class, 'simpan'])->name('kuisioner.simpan');

        // pembayaran
        Route::get('/pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
        Route::get('/pembayaran/{tipe}/{id}', [PembayaranController::class, 'bayar'])->name('pembayaran.bayar');
        Route::post('/pembayaran/{tipe}/{id}/upload', [PembayaranController::class, 'uploadBuktiPembayaran'])->name('pembayaran.upload');

        // supporter
        Route::get('/supporter/daftar/{eventId}', [SupporterController::class, 'create'])->name('supporter.form');
        Route::post('/supporter/daftar', [SupporterController::class, 'store'])->name('supporter.store');

        //pembimbing
        Route::get('/pembimbing/daftar/{event}', [PembimbingController::class, 'create'])->name('pembimbing.create');
        Route::post('/pembimbing/daftar', [PembimbingController::class, 'store'])->name('pembimbing.store');

        //pengajuan
        Route::get('/pengajuan', [PengajuanController::class, 'index'])->name('pengajuan.index');
        Route::get('/pengajuan/create', [PengajuanController::class, 'create'])->name('pengajuan.create');
        Route::post('/pengajuan', [PengajuanController::class, 'store'])->name('pengajuan.store');
        Route::get('/pengajuan/retur', function () {
            return view('user.pengajuan.retur');
        })->name('pengajuan.retur');


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

        //kuisioner
        Route::get('/kuisioner/event', [KuisionerController::class, 'selectEvent'])->name('kuisioner.select-event');
        Route::get('kuisioner/admin/event/{id}', [KuisionerController::class, 'byEvent'])->name('kuisioner.by-event');
        Route::get('kuisioner/admin/event/{id}/create', [KuisionerController::class, 'create'])->name('admin.kuisioner.create');
        Route::post('kuisioner/admin/store', [KuisionerController::class, 'store'])->name('admin.kuisioner.store');
        Route::get('kuisioner/admin/{id}/edit', [KuisionerController::class, 'edit'])->name('admin.kuisioner.edit');
        Route::put('kuisioner/admin/{id}', [KuisionerController::class, 'update'])->name('admin.kuisioner.update');
        Route::delete('kuisioner/admin/{id}', [KuisionerController::class, 'destroy'])->name('admin.kuisioner.destroy');

        //pendaftaran
        Route::get('/admin/keloladaftar/byevent', [KelolaPendaftarController::class, 'pilihEvent'])->name('pendaftaran.pilih-event');
        Route::get('/pendaftaran/event/{event}', [KelolaPendaftarController::class, 'pilihTipePendaftar'])->name('pendaftaran.pilih-tipe');

        Route::get('/event/{event}/peserta', [KelolaPendaftarController::class, 'formPeserta'])->name('admin.pendaftaran.peserta');
        Route::get('/admin/pendaftaran/pendamping/{event}', [KelolaPendaftarController::class, 'formPendamping'])->name('admin.pendaftaran.pendamping');
        Route::get('/event/{event}/supporter', [KelolaPendaftarController::class, 'formSupporter'])->name('admin.pendaftaran.supporter');
        //editpendaftar
        Route::get('/admin/pendaftaran/peserta/{id}/edit', [KelolaPendaftarController::class, 'editPeserta'])->name('pendaftaran.peserta.edit');
        Route::put('/admin/pendaftaran/peserta/{id}', [KelolaPendaftarController::class, 'updatePeserta'])->name('pendaftaran.peserta.update');
        Route::delete('/admin/pendaftaran/peserta/{id}', [KelolaPendaftarController::class, 'destroyPeserta'])->name('pendaftaran.peserta.destroy');
        
        //DELETE PESERTA
        Route::delete('/peserta/{id}', [KelolaPendaftarController::class, 'hapusPeserta'])->name('pendaftaran.peserta.destroy');

        //eit pembimbing
        Route::get('/admin/pendaftaran/pembimbing/{id}/edit', [KelolaPendaftarController::class, 'editPembimbing'])->name('pendaftaran.pembimbing.edit');
        Route::put('/admin/pendaftaran/pembimbing/{id}', [KelolaPendaftarController::class, 'updatePembimbing'])->name('pendaftaran.pembimbing.update');

        //edit supporter
        Route::get('/admin/pendaftaran/supporter/{id}/edit', [KelolaPendaftarController::class, 'editSupporter'])->name('pendaftaran.supporter.edit');
        Route::put('/admin/pendaftaran/supporter/{id}', [KelolaPendaftarController::class, 'updateSupporter'])->name('pendaftaran.supporter.update');

        // delete pembimbing
        Route::delete('/admin/pendaftaran/pembimbing/{id}', [KelolaPendaftarController::class, 'destroyPembimbing'])->name('pendaftaran.pembimbing.destroy');

        // delete supporter
        Route::delete('/admin/pendaftaran/supporter/{id}', [KelolaPendaftarController::class, 'destroySupporter'])->name('pendaftaran.supporter.destroy');

        //sertif
        // Route::get('/sertif', [SertifikatController::class, 'index'])->name('admin.sertifikat.index');
        // Route::get('sertif/event/{id}', [SertifikatController::class, 'byEvent'])->name('admin.sertifikat.by-event');
        // Route::post('sertif/upload-template', [SertifikatController::class, 'uploadTemplate'])->name('admin.sertifikat.upload');
        // Route::post('/admin/sertifikat/update-posisi', [SertifikatController::class, 'updatePosisi'])->name('admin.sertifikat.update-posisi');
        // Route::get('/admin/sertifikat/event/{id}/atur-posisi', [SertifikatController::class, 'aturPosisi'])->name('admin.sertifikat.atur-posisi');
        // Route::post('sertif/generate', [SertifikatController::class, 'generate'])->name('admin.sertifikat.generate');

        Route::get('/sertif', [SertifikatController::class, 'pilihEvent'])->name('sertifikat.pilihEvent');
        Route::get('/sertifikat/{event}/upload', [SertifikatController::class, 'uploadForm'])->name('sertifikat.uploadForm');
        Route::post('/sertifikat/{event}/upload', [SertifikatController::class, 'uploadTemplate'])->name('sertifikat.upload');
        Route::get('/sertifikat/{event}/atur', [SertifikatController::class, 'aturPosisi'])->name('sertifikat.atur');
        Route::post('/sertifikat/{event}/simpan', [SertifikatController::class, 'simpanPosisi'])->name('sertifikat.simpan');
        Route::get('/sertifikat/generate', [SertifikatController::class, 'index'])->name('sertifikat.index');
        Route::get('/sertifikat/generate/{event}', [SertifikatController::class, 'pesertaByEvent'])->name('sertifikat.pesertaByEvent');
        Route::post('/sertifikat/generate/{peserta}', [SertifikatController::class, 'generateSingle'])->name('sertifikat.generateSingle');


        //DASHBOARD ADMIN
        // Pilih event terlebih dahulu
        Route::get('/admin/dashboard', [DashboardAdminController::class, 'listEvents'])->name('dashboard.index');

        // Lihat peserta dashboard berdasarkan event
        Route::get('/admin/dashboard/event/{eventId}', [DashboardAdminController::class, 'byEvent'])->name('dashboard.by-event');

        // Route::get('/dashboardadmin', [DashboardAdminController::class, 'index'])->name('admin.dashboard');
        Route::post('/admin/mark-present', [DashboardAdminController::class, 'markAsPresent'])->name('admin.markPresent');
        Route::get('/admin/peserta/{id}/identitas', [DashboardAdminController::class, 'showIdentitas'])->name('admin.peserta.identitas');
        Route::get('/verifikasi/qr/{id}', [DashboardAdminController::class, 'verifikasiQR'])->name('verifikasi.qr');
        Route::get('/admin/export', [DashboardAdminController::class, 'exportExcel'])->name('admin.export');

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

        //pengajuan
        Route::get('/pengajuan/admin', [PengajuanController::class, 'adminIndex'])->name('admin.pengajuan.index');
        Route::put('/admin/pengajuan/{id}/update-status', [PengajuanController::class, 'updateStatus'])->name('admin.pengajuan.update');
        Route::get('/admin/pengajuan/{id}', [PengajuanController::class, 'show'])->name('admin.pengajuan.show');


        // Route::get('/kehadiran', [KehadiranController::class, 'index'])->name('kehadiran.index');
        Route::get('/kehadiran/{id}/qr', [KehadiranController::class, 'showQR'])->name('admin.qr.show');
        Route::get('/kehadiran/{id}/edit', [KehadiranController::class, 'edit'])->name('kehadiran.edit');
        Route::put('/kehadiran/{id}', [KehadiranController::class, 'update'])->name('kehadiran.update');
        Route::get('/kehadiran-export', [KehadiranController::class, 'exportExcel'])->name('kehadiran.export');

        //transaksi
        Route::get('/admin/transaksi/index', [PembayaranController::class, 'listEvents'])->name('transaksi.index');
        Route::get('/admin/transaksi/event/{eventId}', [PembayaranController::class, 'byEvent'])->name('transaksi.by-event');
        Route::get('/admin/transaksi', [PembayaranController::class, 'show'])->name('transaksi.index');
        Route::post('/admin/transaksi/bulk-action', [PembayaranController::class, 'bulkAction'])->name('admin.transaksi.bulkAction');
        Route::get('/verifikasi/qr/{id}', [PembayaranController::class, 'showQr'])->name('verifikasi.qr');

        //jadwal
        // Route::get('/jadwal', [PenjadwalanController::class, 'index'])->name('jadwal.index');
        Route::get('/jadwal/create', [PenjadwalanController::class, 'create'])->name('jadwal.create');
        Route::post('/jadwal/create/step2', [PenjadwalanController::class, 'createStep2'])->name('jadwal.create.step2');
        Route::get('/jadwal/create/step2', [PenjadwalanController::class, 'showStep2'])->name('jadwal.create.step2');
        Route::post('/jadwal/store', [PenjadwalanController::class, 'store'])->name('jadwal.store');
        // Route::post('/jadwal/create-step3', [PenjadwalanController::class, 'createStep3'])->name('jadwal.create.step3');
        Route::get('/jadwal/{id}/change', [PenjadwalanController::class, 'change'])->name('jadwal.change');
        Route::get('/jadwal/{id}/detail', [PenjadwalanController::class, 'detail'])->name('jadwal.detail');
        Route::match(['get', 'post'], '/jadwal/create-step3', [PenjadwalanController::class, 'createStep3'])->name('jadwal.create.step3');

        Route::get('/jadwal/status', [PenjadwalanController::class, 'getStatus'])->name('jadwal.status');
        Route::get('/jadwal/check-status', [PenjadwalanController::class, 'checkStatus'])->name('jadwal.checkStatus');
        Route::get('/jadwal/refresh', [PenjadwalanController::class, 'refresh'])->name('jadwal.refresh');

        Route::get('/jadwal/{nama_jadwal}/{tahun}/{version}/switch', [PenjadwalanController::class, 'switchJadwal'])->name('jadwal.switch');
        Route::post('/jadwal/switch/proses', [PenjadwalanController::class, 'prosesSwitch'])->name('jadwal.switch.proses');
        // Route::resource('jadwal', PenjadwalanController::class);
        Route::get('/jadwal/{id}/edit', [PenjadwalanController::class, 'edit'])->name('jadwal.edit');
        Route::put('/jadwal/{id}', [PenjadwalanController::class, 'update'])->name('jadwal.update');
        Route::get('/jadwal/{nama_jadwal}/{tahun}/{version}/create', [PenjadwalanController::class, 'createWithDetail'])->name('jadwal.create.withDetail');
        Route::post('/jadwal/add', [PenjadwalanController::class, 'add'])->name('jadwal.add');
        Route::delete('/jadwal/{id}', [PenjadwalanController::class, 'destroy'])->name('jadwal.destroy');

        //baru
        Route::get('/jadwal/event', [PenjadwalanController::class, 'event'])->name('jadwal.event');
        Route::get('/jadwal/{event?}', [PenjadwalanController::class, 'index'])->name('jadwal.index');
        Route::post('/jadwal/{event}/set-session', function ($event) {
            session(['jadwal_event_id' => $event]);
            return response()->json(['status' => 'ok']);
        })->name('jadwal.setEventSession');

        Route::get('/generate-variabel-x', [PenjadwalanController::class, 'generateVariabelX']);
        Route::delete('/jadwal/{id}/delete', [PenjadwalanController::class, 'destroyJadwal'])->name('jadwal.destroyJadwal');
    });
});

Route::middleware([RoleMiddleware::class . ':superadmin'])->group(function () {
    Route::get('/admin/manage', [SuperAdminController::class, 'manage'])->name('superadmin.admin.manage');
    Route::get('/admin/list', [SuperAdminController::class, 'listAll'])->name('superadmin.admin.list');
    Route::get('/admin/create', [SuperAdminController::class, 'create'])->name('superadmin.admin.create');
    Route::post('/admin/store', [SuperAdminController::class, 'store'])->name('superadmin.admin.store');
    Route::get('/admin/{id}/edit', [SuperAdminController::class, 'edit'])->name('superadmin.admin.edit');
    Route::put('/admin/{id}', [SuperAdminController::class, 'update'])->name('superadmin.admin.update');
    Route::delete('/admin/{id}', [SuperAdminController::class, 'destroy'])->name('superadmin.admin.destroy');
    Route::get('/admin/approval', [SuperAdminController::class, 'listAdmin'])->name('superadmin.admin.approval');
    Route::post('/admin/approval/{id}/approve', [SuperAdminController::class, 'approveAdmin'])->name('superadmin.admin.approve');
    Route::post('/admin/approval/{id}/reject', [SuperAdminController::class, 'rejectAdmin'])->name('superadmin.admin.reject');
    Route::post('/admin/approval/bulk-action', [SuperAdminController::class, 'bulkAction'])->name('superadmin.admin.bulkAction');
});

Route::get('/import-excel', [ImportExcelController::class, 'import_excel']);
Route::post('/import-excel', [ImportExcelController::class, 'import_excel_post'])->name('import_excel_post');

Route::post('/generate-schedule', [PenjadwalanController::class, 'generateSchedule']);


Route::get('/test-email', function () {
    try {
        Mail::raw('Ini adalah email percobaan dari Laravel.', function ($message) {
            $message->to('aulianurulf25@gmail.com')
                ->subject('Tes Email dari Laravel');
        });

        return 'Email berhasil dikirim.';
    } catch (\Exception $e) {
        return 'Gagal kirim email: ' . $e->getMessage();
    }
});

// nyoba halaman sukses
// Route::get('/sukses', [PendaftaranController::class, 'sukses']);
