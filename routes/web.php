<?php

use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\JenisAparController;
use App\Http\Controllers\Admin\JenisRefillController;
use App\Http\Controllers\Admin\ManajemenAkunController;
use App\Http\Controllers\Admin\PelangganController;
use App\Http\Controllers\Admin\PesananController;
use App\Http\Controllers\Admin\ProdukController;
use App\Http\Controllers\Admin\RefillController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\StokController;
use App\Http\Controllers\Admin\PeralatanController;
use App\Http\Controllers\Admin\PengeluaranController;
use App\Http\Controllers\Admin\ServicePaketController;
use App\Http\Controllers\Admin\UnitAparController;
use App\Http\Controllers\Admin\ComplainController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminRealtimeController;
use App\Http\Controllers\Admin\TestimoniController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeknisiController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingPageController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/riwayat', [LandingPageController::class, 'riwayatApar'])->name('riwayat');
    Route::get('/riwayat-apar', [LandingPageController::class, 'riwayatApar'])->name('riwayat-apar');
    Route::get('/riwayat-apar/status', [LandingPageController::class, 'riwayatAparStatus'])->name('riwayat-apar.status');
    Route::post('/riwayat-apar/ajukan-refill', [LandingPageController::class, 'ajukanRefill'])->name('riwayat-apar.ajukan-refill');
    
    // Invoice routes
    Route::get('/invoice/{pesanan}', [InvoiceController::class, 'show'])->name('invoice.show');
    Route::get('/invoice/{pesanan}/pdf', [InvoiceController::class, 'pdf'])->name('invoice.pdf');
});

Route::get('/produk', [LandingPageController::class, 'produkIndex'])->name('produk.index');
Route::get('/produk/{produk}', [LandingPageController::class, 'produkShow'])->name('produk.show');

Route::get('/order', [PublicController::class, 'orderCreate'])->name('order.create');
Route::post('/order', [PublicController::class, 'orderStore'])->name('order.store');
Route::post('/order/shipping/quote', [PublicController::class, 'orderShippingQuote'])->name('order.shipping.quote');
Route::get('/order/address/suggest', [PublicController::class, 'orderAddressSuggest'])->name('order.address.suggest');
Route::get('/order/{pesanan}/payment', [PublicController::class, 'orderPayment'])->name('order.payment');
Route::post('/order/{pesanan}/payment', [PublicController::class, 'orderPaymentStore'])
    ->withoutMiddleware([
        \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
    ])  
    ->name('order.payment.store');
Route::get('/complain', fn () => redirect()->route('riwayat-apar'));
Route::post('/complain', [PublicController::class, 'complainStore'])->name('complain.store');
Route::get('/testimoni', fn () => redirect()->route('riwayat-apar'));
Route::post('/testimoni', [PublicController::class, 'testimoniStore'])->name('testimoni.store');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
});

Route::middleware(['auth', 'teknisi'])->prefix('teknisi')->name('teknisi.')->group(function () {
    Route::get('/dashboard', [TeknisiController::class, 'dashboard'])->name('dashboard');
    Route::get('/pekerjaan-aktif', [TeknisiController::class, 'pekerjaanAktif'])->name('pekerjaan-aktif');
    Route::get('/riwayat-pekerjaan', [TeknisiController::class, 'riwayatPekerjaan'])->name('riwayat-pekerjaan');
    Route::get('/tugas-produk', fn () => redirect()->route('teknisi.pekerjaan-aktif', ['filter' => 'produk']))->name('tugas-produk');
    Route::get('/tugas-service-refill', [TeknisiController::class, 'tugasServiceRefill'])->name('tugas-service-refill');
    Route::get('/riwayat-tugas', [TeknisiController::class, 'riwayatTugas'])->name('riwayat-tugas');
    Route::post('/tugas/{pesanan}/mulai', [TeknisiController::class, 'tugasMulai'])->name('tugas.mulai');
    Route::post('/tugas/{pesanan}/selesai', [TeknisiController::class, 'tugasSelesai'])->name('tugas.selesai');
    Route::post('/tugas/{pesanan}/ajukan-tambahan', fn () => back()->with('error', 'Teknisi hanya mengerjakan dan melaporkan pekerjaan Service / Refill dari admin.'))->name('tugas.ajukan-tambahan');
    
    Route::get('/refill-stock', fn () => redirect()->route('teknisi.pekerjaan-aktif', ['filter' => 'service-refill']))->name('refill-stock.index');
    Route::post('/refill-stock/{tugasRefill}/mulai', fn () => redirect()->route('teknisi.pekerjaan-aktif', ['filter' => 'service-refill']))->name('refill-stock.mulai');
    Route::post('/refill-stock/{tugasRefill}/selesai', fn () => redirect()->route('teknisi.pekerjaan-aktif', ['filter' => 'service-refill']))->name('refill-stock.selesai');

    // Service Log — Teknisi Report
    Route::get('/service-log', fn () => redirect()->route('teknisi.pekerjaan-aktif', ['filter' => 'service-refill']))->name('service-log');
    Route::post('/service-log/{service}/laporan', fn () => redirect()->route('teknisi.pekerjaan-aktif', ['filter' => 'service-refill']))->name('service-log.laporan');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('realtime')->name('realtime.')->group(function () {
        Route::get('/dashboard', [AdminRealtimeController::class, 'dashboard'])->name('dashboard');
        Route::get('/pesanan', [AdminRealtimeController::class, 'pesanan'])->name('pesanan');
        Route::get('/pelanggan', [AdminRealtimeController::class, 'pelanggan'])->name('pelanggan');
        Route::get('/complain', [AdminRealtimeController::class, 'complain'])->name('complain');
        Route::get('/testimoni', [AdminRealtimeController::class, 'testimoni'])->name('testimoni');
    });

    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/pdf', [LaporanController::class, 'indexPdf'])->name('laporan.index.pdf');
    Route::get('/laporan/apar', [LaporanController::class, 'apar'])->name('laporan.apar');
    Route::get('/laporan/penjualan', [LaporanController::class, 'penjualan'])->name('laporan.penjualan');
    Route::get('/laporan/pesanan', [LaporanController::class, 'pesanan'])->name('laporan.pesanan');
    Route::get('/laporan/service', [LaporanController::class, 'service'])->name('laporan.service');
    Route::get('/laporan/keuangan', [LaporanController::class, 'keuangan'])->name('laporan.keuangan');
    Route::get('/laporan/apar/pdf', [LaporanController::class, 'aparPdf'])->name('laporan.apar.pdf');
    Route::get('/laporan/penjualan/pdf', [LaporanController::class, 'penjualanPdf'])->name('laporan.penjualan.pdf');
    Route::get('/laporan/pesanan/pdf', [LaporanController::class, 'pesananPdf'])->name('laporan.pesanan.pdf');
    Route::get('/laporan/service/pdf', [LaporanController::class, 'servicePdf'])->name('laporan.service.pdf');
    Route::get('/laporan/keuangan/pdf', [LaporanController::class, 'keuanganPdf'])->name('laporan.keuangan.pdf');
    Route::get('/stok', [StokController::class, 'index'])->name('stok.index');
    Route::post('/stok/batch', [StokController::class, 'storeBatch'])->name('stok.batch.store');
    Route::post('/stok/batch/{stokBatch}/refill', [StokController::class, 'refillBatch'])->name('stok.batch.refill');
    // CRUD Peralatan di halaman Stok
    Route::post('/stok/peralatan', [StokController::class, 'storePeralatan'])->name('stok.peralatan.store');
    Route::put('/stok/peralatan/{peralatan}', [StokController::class, 'updatePeralatan'])->name('stok.peralatan.update');
    Route::delete('/stok/peralatan/{peralatan}', [StokController::class, 'destroyPeralatan'])->name('stok.peralatan.destroy');
    Route::resource('pelanggan', PelangganController::class)->except(['create', 'store', 'destroy']);
    Route::get('/akun', [ManajemenAkunController::class, 'index'])->name('akun.index');
    Route::post('/akun', [ManajemenAkunController::class, 'store'])->name('akun.store');
    Route::put('/akun/{user}', [ManajemenAkunController::class, 'update'])->name('akun.update');
    Route::delete('/akun/{user}', [ManajemenAkunController::class, 'destroy'])->name('akun.destroy');
    Route::get('/pesanan/{pesanan}/invoice/pdf', [PesananController::class, 'invoicePdf'])->name('pesanan.invoice.pdf');
    Route::get('/pesanan/notifikasi/pembayaran', [PesananController::class, 'paymentNotifications'])->name('pesanan.payment-notifications');
    Route::post('/pesanan/{pesanan}/kirim-link-pembayaran', [PesananController::class, 'kirimLinkPembayaran'])->name('pesanan.kirim-link-pembayaran');
    Route::post('/pesanan/{pesanan}/input-bukti-pembayaran-manual', [PesananController::class, 'inputBuktiPembayaranManual'])->name('pesanan.input-bukti-pembayaran-manual');
    Route::post('/pesanan/{pesanan}/konfirmasi-pembayaran-manual', [PesananController::class, 'konfirmasiPembayaranManual'])->name('pesanan.konfirmasi-pembayaran-manual');
    Route::post('/pesanan/{pesanan}/pengajuan-harga/acc', [PesananController::class, 'approvePurchasePriceRequest'])->name('pesanan.pengajuan-harga.acc');
    Route::post('/pesanan/{pesanan}/pengajuan-harga/tolak', [PesananController::class, 'rejectPurchasePriceRequest'])->name('pesanan.pengajuan-harga.tolak');
    Route::post('/pesanan/{pesanan}/assign-teknisi', [PesananController::class, 'assignTeknisi'])->name('pesanan.assign-teknisi');
    Route::post('/pesanan/{pesanan}/konfirmasi-pelanggan', [PesananController::class, 'konfirmasiKePelanggan'])->name('pesanan.konfirmasi-pelanggan');
    Route::post('/pesanan/{pesanan}/selesai-final', [PesananController::class, 'selesaiFinal'])->name('pesanan.selesai-final');
    Route::resource('pesanan', PesananController::class);
    Route::resource('jenis-apar', JenisAparController::class)->except(['show']);
    Route::resource('jenis-refill', JenisRefillController::class);
    Route::resource('service-paket', ServicePaketController::class)->except(['show']);
    Route::resource('produk', ProdukController::class);
    Route::resource('unit-apar', UnitAparController::class);
    Route::post('/service/request/{pesanan}/status', [ServiceController::class, 'updateRequestStatus'])->name('service.request.status');
    Route::post('/service/{service}/konfirmasi-selesai', [ServiceController::class, 'konfirmasiSelesai'])->name('service.konfirmasi-selesai');
    Route::post('/service/{service}/tolak', [ServiceController::class, 'tolakService'])->name('service.tolak');
    Route::resource('service', ServiceController::class);
    Route::resource('refill', RefillController::class);
    Route::post('/refill/{pesanan}/assign-teknisi', [RefillController::class, 'assignTeknisi'])->name('refill.assign-teknisi');
    Route::post('/refill/{pesanan}/update-status', [RefillController::class, 'updateStatus'])->name('refill.update-status');
    Route::resource('peralatan', PeralatanController::class);
    Route::resource('pengeluaran', PengeluaranController::class);
    // Complain
    Route::get('/complain', [ComplainController::class, 'index'])->name('complain.index');
    Route::put('/complain/{complain}', [ComplainController::class, 'update'])->name('complain.update');
    Route::delete('/complain/{complain}', [ComplainController::class, 'destroy'])->name('complain.destroy');
    // Testimoni
    Route::get('/testimoni', [TestimoniController::class, 'index'])->name('testimoni.index');
    Route::post('/testimoni', [TestimoniController::class, 'store'])->name('testimoni.store');
    Route::put('/testimoni/{testimoni}', [TestimoniController::class, 'update'])->name('testimoni.update');
    Route::post('/testimoni/{testimoni}/approve', [TestimoniController::class, 'approve'])->name('testimoni.approve');
    Route::post('/testimoni/{testimoni}/reject', [TestimoniController::class, 'reject'])->name('testimoni.reject');
    Route::post('/testimoni/{testimoni}/pending', [TestimoniController::class, 'pending'])->name('testimoni.pending');
    Route::delete('/testimoni/{testimoni}', [TestimoniController::class, 'destroy'])->name('testimoni.destroy');

});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ==============================
// KERANJANG BELANJA & CHECKOUT
// ==============================
Route::middleware('auth')->group(function () {
    Route::get('/keranjang', [KeranjangController::class, 'index'])->name('keranjang.index');
    Route::post('/keranjang', [KeranjangController::class, 'store'])->name('keranjang.store');
    Route::patch('/keranjang/{item}', [KeranjangController::class, 'update'])->name('keranjang.update');
    Route::delete('/keranjang/{item}', [KeranjangController::class, 'destroy'])->name('keranjang.destroy');
    Route::get('/keranjang/count', [KeranjangController::class, 'count'])->name('keranjang.count');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
});

require __DIR__.'/auth.php';
