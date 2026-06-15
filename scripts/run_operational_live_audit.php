<?php

declare(strict_types=1);

use App\Models\JenisApar;
use App\Models\JenisRefill;
use App\Models\Pelanggan;
use App\Models\Pengeluaran;
use App\Models\Peralatan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Refill;
use App\Models\Service;
use App\Models\ServicePaket;
use App\Models\UnitApar;
use App\Models\User;
use App\Http\Controllers\Admin\PesananController as AdminPesananController;
use App\Http\Controllers\Admin\RefillController as AdminRefillController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\TeknisiController;
use App\Support\OperationalAuditDetailLogGenerator;
use App\Services\FinalRevenueService;
use App\Services\ServicePackagePricingService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

final class AuditResponseAdapter
{
    private mixed $response;

    private ?string $renderedContent = null;

    private bool $renderAttempted = false;

    private ?\Throwable $renderException = null;

    public function __construct(mixed $response)
    {
        $this->response = $response;
    }

    public function getStatusCode(): int
    {
        if ($this->response instanceof View) {
            $this->renderViewIfNeeded();

            return $this->renderException ? 500 : 200;
        }

        if ($this->response instanceof SymfonyResponse) {
            return $this->response->getStatusCode();
        }

        return 200;
    }

    public function isRedirect(): bool
    {
        return $this->response instanceof SymfonyResponse
            ? $this->response->isRedirection()
            : false;
    }

    public function getContent(): string
    {
        if ($this->response instanceof View) {
            $this->renderViewIfNeeded();

            return $this->renderedContent ?? '';
        }

        if ($this->response instanceof SymfonyResponse) {
            return (string) $this->response->getContent();
        }

        return is_string($this->response) ? $this->response : '';
    }

    private function renderViewIfNeeded(): void
    {
        if ($this->renderAttempted) {
            return;
        }

        $this->renderAttempted = true;

        try {
            $this->renderedContent = $this->response->render();
        } catch (\Throwable $throwable) {
            $this->renderException = $throwable;
            $this->renderedContent = $throwable->getMessage();
        }
    }
}

final class OperationalLiveAuditRunner extends Tests\TestCase
{
    private const STATUS_OK = 'Berhasil';
    private const STATUS_FAIL = 'Gagal';
    private const STATUS_WARN = 'Perlu diperbaiki';

    private string $runId;

    private string $startedAt;

    private int $initialLogBytes = 0;

    private array $results = [];

    private array $transactions = [];

    private array $proofChecks = [];

    private array $created = [
        'pesanan_online' => [],
        'pesanan_offline' => [],
        'service' => [],
        'refill' => [],
        'unit_apar' => [],
        'pengeluaran' => [],
    ];

    private array $initialState = [];

    private array $finalState = [];

    private ?string $detailLogPath = null;

    private ?string $detailLogError = null;

    private User $admin;

    private User $teknisi;

    private User $customerUser;

    private Pelanggan $customer;

    private Produk $product;

    private JenisRefill $powderRefill;

    private JenisRefill $foamRefill;

    private ServicePaket $servicePaket;

    private UnitApar $registeredUnit;

    private Peralatan $expensePeralatan;

    private string $proofSourcePath;

    private array $servicePeralatanIds = [];

    public function runTest(): void
    {
    }

    public function execute(): array
    {
        $this->setUp();
        $this->withoutMiddleware();
        $this->app['view']->share('errors', new Illuminate\Support\ViewErrorBag());

        try {
            return $this->runAudit();
        } finally {
            $this->tearDown();
        }
    }

    private function runAudit(): array
    {
        $this->runId = now('Asia/Jakarta')->format('Ymd_His');
        $this->startedAt = now('Asia/Jakarta')->toDateTimeString();
        $this->initialLogBytes = $this->laravelLogSize();

        $this->prepareContext();
        $this->initialState = $this->snapshotState();

        $this->record(
            feature: 'Snapshot Awal Live Audit',
            role: 'system',
            steps: [
                'Ambil akun admin, teknisi, dan pelanggan yang sudah ada.',
                'Pilih produk, jenis refill, paket service, peralatan, dan unit APAR live yang akan dipantau.',
                'Catat stok awal, total unit APAR, dan total laporan sebelum transaksi baru dibuat.',
            ],
            expected: 'Audit berjalan di database utama tanpa reset, tanpa akun baru, dan tanpa pembersihan data.',
            actual: 'Audit live memakai DB `' . config('database.connections.mysql.database') . '`, pelanggan `' . $this->customer->nama . '`, produk `' . $this->product->nama . '`, unit `' . ($this->registeredUnit->no_seri ?: ('UNIT-' . $this->registeredUnit->id)) . '`.',
            status: self::STATUS_OK,
        );

        $this->runOnlineProductOrderFlow();
        $this->runOnlineRegisteredRefillFlow();
        $this->runOnlineManualRefillFlow();
        $this->runOnlineRegisteredServiceFlow();
        $this->runOnlineManualServiceFlow();
        $this->runAdminOfflineOrderFlow();
        $this->runAdminOfflineRefillFlow();
        $this->runAdminOfflineManualRefillFlow();
        $this->runAdminOfflineServiceFlow();
        $this->runAdminOfflineManualServiceFlow();
        $this->runExpenseFlow();
        $this->runReportPagesFlow();
        $this->runLaravelLogCheck();

        $this->finalState = $this->snapshotState();

        $report = $this->buildReport();
        $this->writeReports($report);

        return $report;
    }

    private function prepareContext(): void
    {
        $this->admin = User::query()->where('role', 'admin')->firstOrFail();
        $this->teknisi = User::query()->where('role', 'teknisi')->firstOrFail();
        $this->customer = Pelanggan::query()
            ->with('user')
            ->where('nama', 'Akhmad Rizaldy')
            ->firstOrFail();
        $this->customerUser = $this->customer->user()->firstOrFail();

        $this->product = Produk::query()
            ->where('stok', '>=', 3)
            ->orderByDesc('stok')
            ->orderBy('id')
            ->firstOrFail();

        $this->powderRefill = JenisRefill::query()
            ->where('nama', 'like', '%Powder%')
            ->firstOrFail();

        $this->foamRefill = JenisRefill::query()
            ->where('nama', 'like', '%Foam%')
            ->firstOrFail();

        $this->servicePaket = ServicePaket::query()
            ->with('peralatans')
            ->whereHas('peralatans', fn (Builder $query) => $query->where('stok', '>', 0))
            ->orderBy('harga')
            ->firstOrFail();

        $this->servicePeralatanIds = $this->servicePaket->peralatans
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $this->expensePeralatan = $this->servicePaket->peralatans
            ->sortByDesc('stok')
            ->first() ?? Peralatan::query()->where('stok', '>', 0)->orderByDesc('stok')->firstOrFail();

        $unitQuery = UnitApar::query()
            ->with(['produk.jenisApar'])
            ->where('pelanggan_id', $this->customer->id);

        $this->registeredUnit = (clone $unitQuery)
            ->where(function (Builder $query) {
                $query->where('ukuran', 'like', '%1 kg%')
                    ->orWhereHas('produk', fn (Builder $productQuery) => $productQuery->where('kapasitas', 'like', '%1 kg%'));
            })
            ->latest('id')
            ->first() ?? $unitQuery->latest('id')->firstOrFail();

        $proofCandidates = collect();

        $proofDirectory = storage_path('app/public/bukti-pembayaran');
        if (File::isDirectory($proofDirectory)) {
            $proofCandidates = $proofCandidates->merge(File::files($proofDirectory));
        }

        if ($proofCandidates->isEmpty()) {
            $proofCandidates = $proofCandidates->merge(
                collect(File::allFiles(storage_path('app/public')))
                    ->filter(fn ($file) => in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png'], true))
            );
        }

        $proofFile = $proofCandidates->first();

        if (!$proofFile) {
            throw new RuntimeException('Tidak ditemukan file gambar existing untuk bukti pembayaran di storage/app/public.');
        }

        $this->proofSourcePath = $proofFile->getPathname();
    }

    private function runOnlineProductOrderFlow(): void
    {
        $beforePesananId = (int) (Pesanan::query()->max('id') ?? 0);
        $beforeUnitId = (int) (UnitApar::query()->max('id') ?? 0);

        $order = $this->callControllerWithRequestAs(
            $this->customerUser,
            PublicController::class,
            'orderStore',
            '/order',
            [
            'nama' => $this->customer->nama,
            'no_wa' => $this->customer->no_wa,
            'alamat_maps' => $this->customer->alamat_maps,
            'alamat_detail' => $this->customer->alamat_detail,
            'alamat_provinsi' => $this->customer->alamat_provinsi,
            'alamat_kota' => $this->customer->alamat_kota,
            'alamat_kecamatan' => $this->customer->alamat_kecamatan,
            'alamat_kode_pos' => $this->customer->alamat_kode_pos,
            'alamat_lat' => $this->customer->alamat_lat,
            'alamat_lng' => $this->customer->alamat_lng,
            'tipe_layanan' => 'beli',
            'metode_pengiriman' => 'pickup',
            'bank_tujuan' => 'bca',
            'submit_source' => 'normal',
            'items' => [
                [
                    'produk_id' => $this->product->id,
                    'jumlah' => 1,
                ],
            ],
            ],
        );

        $pesanan = Pesanan::query()
            ->where('id', '>', $beforePesananId)
            ->where('pelanggan_id', $this->customer->id)
            ->where('tipe', 'produk')
            ->latest('id')
            ->first();

        if (!$pesanan) {
            $this->record(
                feature: 'Pesanan Produk Pelanggan',
                role: 'pelanggan/admin/teknisi',
                steps: [
                    'Pelanggan checkout produk existing.',
                    'Sistem harus membentuk pesanan produk baru.',
                ],
                expected: 'Pesanan produk online terbentuk dan bisa diteruskan ke pembayaran.',
                actual: 'Submit checkout status ' . $order->getStatusCode() . ', tetapi pesanan produk baru tidak ditemukan.',
                status: self::STATUS_FAIL,
            );
            return;
        }

        $paymentPage = $this->callControllerAs(
            $this->customerUser,
            PublicController::class,
            'orderPayment',
            ['pesanan' => $pesanan],
            '/order/' . $pesanan->id . '/payment'
        );
        $payment = $this->callControllerWithRequestAs(
            $this->customerUser,
            PublicController::class,
            'orderPaymentStore',
            '/order/' . $pesanan->id . '/payment',
            [
                'metode_pembayaran' => 'transfer',
                'bank' => 'bca',
            ],
            ['pesanan' => $pesanan],
            ['bukti_pembayaran' => $this->paymentProofFile('produk-online')]
        );

        $pesanan->refresh();
        $proofExists = $this->proofExists($pesanan);
        $adminPage = $this->actingAs($this->admin)->get('/admin/pesanan');
        $proofDisplayed = $proofExists && $this->pageContainsProof($adminPage->getContent(), $pesanan);
        $this->registerProofCheck('produk_online', $pesanan, '/admin/pesanan', $proofDisplayed);

        $flow = $this->completeTransactionFlow(
            $pesanan,
            '/admin/pesanan/' . $pesanan->id . '/assign-teknisi',
            'Audit operasional live - produk online'
        );

        $invoiceHtml = $this->callControllerAs(
            $this->customerUser,
            InvoiceController::class,
            'show',
            ['pesanan' => $pesanan],
            '/invoice/' . $pesanan->id
        );
        $invoicePdf = $this->callControllerAs(
            $this->customerUser,
            InvoiceController::class,
            'pdf',
            ['pesanan' => $pesanan],
            '/invoice/' . $pesanan->id . '/pdf'
        );
        $units = UnitApar::query()
            ->where('id', '>', $beforeUnitId)
            ->where('pesanan_id', $pesanan->id)
            ->get();

        foreach ($units as $unit) {
            $this->created['unit_apar'][] = [
                'source' => 'produk_online',
                'id' => (int) $unit->id,
                'pesanan_id' => (int) $pesanan->id,
                'no_seri' => $unit->no_seri,
            ];
        }

        $this->created['pesanan_online'][] = [
            'flow' => 'produk_pelanggan',
            'pesanan_id' => (int) $pesanan->id,
        ];

        $this->transactions[] = [
            'flow' => 'customer_product_order',
            'pesanan_id' => (int) $pesanan->id,
            'tipe' => 'produk',
            'status' => (string) $pesanan->status,
            'proof_path' => $pesanan->bukti_pembayaran,
            'invoice_html_status' => $invoiceHtml->getStatusCode(),
            'invoice_pdf_status' => $invoicePdf->getStatusCode(),
            'created_unit_ids' => $units->pluck('id')->map(fn ($id) => (int) $id)->all(),
        ];

        $status = $order->isRedirect()
            && $paymentPage->getStatusCode() === 200
            && $payment->isRedirect()
            && $proofExists
            && $proofDisplayed
            && $invoiceHtml->getStatusCode() === 200
            && $invoicePdf->getStatusCode() === 200
            && $pesanan->status === Pesanan::STATUS_SELESAI_FINAL
            && $units->count() > 0
            ? self::STATUS_OK
            : self::STATUS_FAIL;

        $this->record(
            feature: 'Pesanan Produk Pelanggan',
            role: 'pelanggan/admin/teknisi',
            steps: [
                'Checkout 1 produk APAR dari akun pelanggan existing.',
                'Buka halaman pembayaran lalu upload bukti transfer dari file existing storage.',
                'Cek tampilan bukti bayar di halaman admin pesanan.',
                'Assign teknisi, mulai, selesaikan, lalu finalisasi admin.',
                'Buka invoice HTML dan PDF transaksi.',
            ],
            expected: 'Pesanan produk online selesai final, bukti bayar tersimpan dan tampil di admin, invoice terbuka, serta unit APAR baru tercatat.',
            actual: 'Order ' . $order->getStatusCode()
                . ', payment page ' . $paymentPage->getStatusCode()
                . ', upload ' . $payment->getStatusCode()
                . ', proof ' . ($proofExists ? 'tersimpan' : 'tidak tersimpan')
                . ', display ' . ($proofDisplayed ? 'muncul' : 'tidak muncul')
                . ', assign/mulai/selesai/final ' . $this->responseCode($flow['assign']) . '/' . $flow['mulai']->getStatusCode() . '/' . $flow['selesai']->getStatusCode() . '/' . $flow['final']->getStatusCode()
                . ', invoice ' . $invoiceHtml->getStatusCode() . '/' . $invoicePdf->getStatusCode()
                . ', status akhir `' . $pesanan->status . '`, unit baru ' . $units->count() . '.',
            status: $status,
        );
    }

    private function runOnlineRegisteredRefillFlow(): void
    {
        $marker = $this->marker('refill-online-terdaftar');
        $beforePesananId = (int) (Pesanan::query()->max('id') ?? 0);

        $order = $this->callControllerWithRequestAs(
            $this->customerUser,
            PublicController::class,
            'orderStore',
            '/order',
            [
            'nama' => $this->customer->nama,
            'no_wa' => $this->customer->no_wa,
            'alamat_maps' => $this->customer->alamat_maps,
            'alamat_detail' => $this->customer->alamat_detail,
            'alamat_provinsi' => $this->customer->alamat_provinsi,
            'alamat_kota' => $this->customer->alamat_kota,
            'alamat_kecamatan' => $this->customer->alamat_kecamatan,
            'alamat_kode_pos' => $this->customer->alamat_kode_pos,
            'alamat_lat' => $this->customer->alamat_lat,
            'alamat_lng' => $this->customer->alamat_lng,
            'tipe_layanan' => 'service',
            'service_jenis_layanan' => 'refill',
            'service_unit_status' => 'terdaftar',
            'service_purchase_group' => optional($this->registeredUnit->tgl_beli)->toDateString(),
            'service_unit_apar_ids' => [$this->registeredUnit->id],
            'service_keluhan' => $marker,
            'service_metode_penanganan' => 'dijemput',
            ],
        );

        $pesanan = Pesanan::query()
            ->where('id', '>', $beforePesananId)
            ->where('pelanggan_id', $this->customer->id)
            ->where('tipe', 'service')
            ->where('service_jenis_layanan', 'refill')
            ->latest('id')
            ->first();

        if (!$pesanan) {
            $this->record(
                feature: 'Refill Pelanggan APAR Terdaftar',
                role: 'pelanggan/admin/teknisi',
                steps: [
                    'Pelanggan ajukan refill untuk unit APAR yang sudah terdaftar.',
                ],
                expected: 'Pesanan refill online terdaftar terbentuk.',
                actual: 'Submit refill status ' . $order->getStatusCode() . ', tetapi pesanan baru tidak ditemukan.',
                status: self::STATUS_FAIL,
            );
            return;
        }

        $paymentPage = $this->callControllerAs(
            $this->customerUser,
            PublicController::class,
            'orderPayment',
            ['pesanan' => $pesanan],
            '/order/' . $pesanan->id . '/payment'
        );
        $payment = $this->callControllerWithRequestAs(
            $this->customerUser,
            PublicController::class,
            'orderPaymentStore',
            '/order/' . $pesanan->id . '/payment',
            [
                'metode_pembayaran' => 'transfer',
                'bank' => 'bca',
            ],
            ['pesanan' => $pesanan],
            ['bukti_pembayaran' => $this->paymentProofFile('refill-terdaftar')]
        );

        $pesanan->refresh();
        $proofExists = $this->proofExists($pesanan);
        $adminPage = $this->actingAs($this->admin)->get('/admin/refill');
        $proofDisplayed = $proofExists && $this->pageContainsProof($adminPage->getContent(), $pesanan);
        $this->registerProofCheck('refill_online_registered', $pesanan, '/admin/refill', $proofDisplayed);

        $flow = $this->completeTransactionFlow(
            $pesanan,
            '/admin/refill/' . $pesanan->id . '/assign-teknisi',
            'Audit operasional live - refill online terdaftar'
        );

        $refill = Refill::query()
            ->whereHas('service', fn (Builder $query) => $query->where('pesanan_id', $pesanan->id))
            ->latest('id')
            ->first();

        if ($refill) {
            $this->created['refill'][] = [
                'source' => 'online_registered',
                'pesanan_id' => (int) $pesanan->id,
                'refill_id' => (int) $refill->id,
            ];
        }

        $this->created['pesanan_online'][] = [
            'flow' => 'refill_terdaftar',
            'pesanan_id' => (int) $pesanan->id,
        ];

        $this->transactions[] = [
            'flow' => 'customer_refill_registered',
            'pesanan_id' => (int) $pesanan->id,
            'status' => (string) $pesanan->status,
            'refill_id' => $refill?->id,
            'proof_path' => $pesanan->bukti_pembayaran,
            'unit_apar_id' => (int) $this->registeredUnit->id,
        ];

        $status = $order->isRedirect()
            && $paymentPage->getStatusCode() === 200
            && $payment->isRedirect()
            && $proofExists
            && $proofDisplayed
            && $pesanan->status === Pesanan::STATUS_SELESAI_FINAL
            && $refill
            ? self::STATUS_OK
            : self::STATUS_FAIL;

        $this->record(
            feature: 'Refill Pelanggan APAR Terdaftar',
            role: 'pelanggan/admin/teknisi',
            steps: [
                'Pelanggan pilih unit APAR existing yang sudah terdaftar.',
                'Upload bukti transfer dari file existing storage.',
                'Cek bukti bayar tampil di halaman admin refill.',
                'Assign teknisi, kerjakan, lalu finalisasi admin.',
            ],
            expected: 'Refill online untuk unit terdaftar selesai final dan log refill terbentuk.',
            actual: 'Order ' . $order->getStatusCode()
                . ', payment page ' . $paymentPage->getStatusCode()
                . ', upload ' . $payment->getStatusCode()
                . ', proof ' . ($proofExists ? 'tersimpan' : 'tidak tersimpan')
                . ', display ' . ($proofDisplayed ? 'muncul' : 'tidak muncul')
                . ', assign/mulai/selesai/final ' . $this->responseCode($flow['assign']) . '/' . $flow['mulai']->getStatusCode() . '/' . $flow['selesai']->getStatusCode() . '/' . $flow['final']->getStatusCode()
                . ', status akhir `' . $pesanan->status . '`, refill log ' . ($refill?->id ?? '-') . '.',
            status: $status,
        );
    }

    private function runOnlineManualRefillFlow(): void
    {
        $marker = $this->marker('refill-online-belum-terdaftar');
        $beforePesananId = (int) (Pesanan::query()->max('id') ?? 0);
        $beforeUnitId = (int) (UnitApar::query()->max('id') ?? 0);

        $order = $this->callControllerWithRequestAs(
            $this->customerUser,
            PublicController::class,
            'orderStore',
            '/order',
            [
            'nama' => $this->customer->nama,
            'no_wa' => $this->customer->no_wa,
            'alamat_maps' => $this->customer->alamat_maps,
            'alamat_detail' => $this->customer->alamat_detail,
            'alamat_provinsi' => $this->customer->alamat_provinsi,
            'alamat_kota' => $this->customer->alamat_kota,
            'alamat_kecamatan' => $this->customer->alamat_kecamatan,
            'alamat_kode_pos' => $this->customer->alamat_kode_pos,
            'alamat_lat' => $this->customer->alamat_lat,
            'alamat_lng' => $this->customer->alamat_lng,
            'tipe_layanan' => 'service',
            'service_jenis_layanan' => 'refill',
            'service_unit_status' => 'belum_terdaftar',
            'service_jenis_refill_id' => $this->foamRefill->id,
            'service_ukuran_apar' => '6 kg',
            'service_jumlah_unit' => 2,
            'service_keluhan' => $marker,
            'service_metode_penanganan' => 'dijemput',
            ],
        );

        $pesanan = Pesanan::query()
            ->where('id', '>', $beforePesananId)
            ->where('pelanggan_id', $this->customer->id)
            ->where('tipe', 'service')
            ->where('service_jenis_layanan', 'refill')
            ->where('service_jenis_refill_id', $this->foamRefill->id)
            ->latest('id')
            ->first();

        if (!$pesanan) {
            $this->record(
                feature: 'Refill Pelanggan APAR Belum Terdaftar',
                role: 'pelanggan/admin/teknisi',
                steps: [
                    'Pelanggan ajukan refill untuk APAR yang belum terdaftar.',
                ],
                expected: 'Pesanan refill manual online terbentuk.',
                actual: 'Submit refill manual status ' . $order->getStatusCode() . ', tetapi pesanan baru tidak ditemukan.',
                status: self::STATUS_FAIL,
            );
            return;
        }

        $paymentPage = $this->callControllerAs(
            $this->customerUser,
            PublicController::class,
            'orderPayment',
            ['pesanan' => $pesanan],
            '/order/' . $pesanan->id . '/payment'
        );
        $payment = $this->callControllerWithRequestAs(
            $this->customerUser,
            PublicController::class,
            'orderPaymentStore',
            '/order/' . $pesanan->id . '/payment',
            [
                'metode_pembayaran' => 'transfer',
                'bank' => 'mandiri',
            ],
            ['pesanan' => $pesanan],
            ['bukti_pembayaran' => $this->paymentProofFile('refill-manual')]
        );

        $pesanan->refresh();
        $proofExists = $this->proofExists($pesanan);
        $adminPage = $this->actingAs($this->admin)->get('/admin/refill');
        $proofDisplayed = $proofExists && $this->pageContainsProof($adminPage->getContent(), $pesanan);
        $this->registerProofCheck('refill_online_manual', $pesanan, '/admin/refill', $proofDisplayed);

        $flow = $this->completeTransactionFlow(
            $pesanan,
            '/admin/refill/' . $pesanan->id . '/assign-teknisi',
            'Audit operasional live - refill online belum terdaftar'
        );

        $refill = Refill::query()
            ->whereHas('service', fn (Builder $query) => $query->where('pesanan_id', $pesanan->id))
            ->latest('id')
            ->first();
        $units = UnitApar::query()
            ->where('id', '>', $beforeUnitId)
            ->where('pesanan_id', $pesanan->id)
            ->orderBy('id')
            ->get();

        if ($refill) {
            $this->created['refill'][] = [
                'source' => 'online_manual',
                'pesanan_id' => (int) $pesanan->id,
                'refill_id' => (int) $refill->id,
            ];
        }

        foreach ($units as $unit) {
            $this->created['unit_apar'][] = [
                'source' => 'refill_online_manual',
                'id' => (int) $unit->id,
                'pesanan_id' => (int) $pesanan->id,
                'no_seri' => (string) $unit->no_seri,
            ];
        }

        $this->created['pesanan_online'][] = [
            'flow' => 'refill_belum_terdaftar',
            'pesanan_id' => (int) $pesanan->id,
        ];

        $this->transactions[] = [
            'flow' => 'customer_refill_unregistered',
            'pesanan_id' => (int) $pesanan->id,
            'status' => (string) $pesanan->status,
            'refill_id' => $refill?->id,
            'proof_path' => $pesanan->bukti_pembayaran,
            'foam_unit_label' => $this->foamRefill->satuan_label,
            'created_unit_ids' => $units->pluck('id')->map(fn ($id) => (int) $id)->all(),
        ];

        $status = $order->isRedirect()
            && $paymentPage->getStatusCode() === 200
            && $payment->isRedirect()
            && $proofExists
            && $proofDisplayed
            && $pesanan->status === Pesanan::STATUS_SELESAI_FINAL
            && $refill
            && $units->count() === 2
            && strcasecmp($this->foamRefill->satuan_label, 'Kg') === 0
            ? self::STATUS_OK
            : self::STATUS_WARN;

        $this->record(
            feature: 'Refill Pelanggan APAR Belum Terdaftar',
            role: 'pelanggan/admin/teknisi',
            steps: [
                'Pelanggan ajukan refill manual Foam 6 kg untuk 2 unit APAR yang belum terdaftar.',
                'Upload bukti transfer dari file existing storage.',
                'Cek bukti bayar tampil di halaman admin refill.',
                'Assign teknisi, kerjakan, lalu finalisasi admin.',
                'Validasi satuan Foam terbaca Kg, bukan liter, dan terbentuk 2 unit APAR baru.',
            ],
            expected: 'Refill manual online selesai final, jenis refill Foam memakai satuan Kg, dan terbentuk tepat 2 unit APAR baru.',
            actual: 'Order ' . $order->getStatusCode()
                . ', payment page ' . $paymentPage->getStatusCode()
                . ', upload ' . $payment->getStatusCode()
                . ', proof ' . ($proofExists ? 'tersimpan' : 'tidak tersimpan')
                . ', display ' . ($proofDisplayed ? 'muncul' : 'tidak muncul')
                . ', assign/mulai/selesai/final ' . $this->responseCode($flow['assign']) . '/' . $flow['mulai']->getStatusCode() . '/' . $flow['selesai']->getStatusCode() . '/' . $flow['final']->getStatusCode()
                . ', status akhir `' . $pesanan->status . '`, refill log ' . ($refill?->id ?? '-') . ', satuan Foam `' . $this->foamRefill->satuan_label . '`, unit baru ' . $units->count() . '.',
            status: $status,
        );
    }

    private function runOnlineRegisteredServiceFlow(): void
    {
        $marker = $this->marker('service-online-terdaftar');
        $beforePesananId = (int) (Pesanan::query()->max('id') ?? 0);

        $order = $this->callControllerWithRequestAs(
            $this->customerUser,
            PublicController::class,
            'orderStore',
            '/order',
            [
            'nama' => $this->customer->nama,
            'no_wa' => $this->customer->no_wa,
            'alamat_maps' => $this->customer->alamat_maps,
            'alamat_detail' => $this->customer->alamat_detail,
            'alamat_provinsi' => $this->customer->alamat_provinsi,
            'alamat_kota' => $this->customer->alamat_kota,
            'alamat_kecamatan' => $this->customer->alamat_kecamatan,
            'alamat_kode_pos' => $this->customer->alamat_kode_pos,
            'alamat_lat' => $this->customer->alamat_lat,
            'alamat_lng' => $this->customer->alamat_lng,
            'tipe_layanan' => 'service',
            'service_jenis_layanan' => 'service',
            'service_unit_status' => 'terdaftar',
            'service_purchase_group' => optional($this->registeredUnit->tgl_beli)->toDateString(),
            'service_unit_apar_ids' => [$this->registeredUnit->id],
            'service_paket_id' => $this->servicePaket->id,
            'service_keluhan' => $marker,
            'service_metode_penanganan' => 'dijemput',
            ],
        );

        $pesanan = Pesanan::query()
            ->where('id', '>', $beforePesananId)
            ->where('pelanggan_id', $this->customer->id)
            ->where('tipe', 'service')
            ->where('service_jenis_layanan', 'service')
            ->latest('id')
            ->first();

        if (!$pesanan) {
            $this->record(
                feature: 'Service Pelanggan APAR Terdaftar',
                role: 'pelanggan/admin/teknisi',
                steps: [
                    'Pelanggan ajukan service untuk unit APAR terdaftar.',
                ],
                expected: 'Pesanan service online terdaftar terbentuk.',
                actual: 'Submit service status ' . $order->getStatusCode() . ', tetapi pesanan baru tidak ditemukan.',
                status: self::STATUS_FAIL,
            );
            return;
        }

        $paymentPage = $this->callControllerAs(
            $this->customerUser,
            PublicController::class,
            'orderPayment',
            ['pesanan' => $pesanan],
            '/order/' . $pesanan->id . '/payment'
        );
        $payment = $this->callControllerWithRequestAs(
            $this->customerUser,
            PublicController::class,
            'orderPaymentStore',
            '/order/' . $pesanan->id . '/payment',
            [
                'metode_pembayaran' => 'transfer',
                'bank' => 'bri',
            ],
            ['pesanan' => $pesanan],
            ['bukti_pembayaran' => $this->paymentProofFile('service-terdaftar')]
        );

        $pesanan->refresh();
        $proofExists = $this->proofExists($pesanan);
        $adminPage = $this->actingAs($this->admin)->get('/admin/service');
        $proofDisplayed = $proofExists && $this->pageContainsProof($adminPage->getContent(), $pesanan);
        $this->registerProofCheck('service_online_registered', $pesanan, '/admin/service', $proofDisplayed);

        $flow = $this->completeTransactionFlow(
            $pesanan,
            '/admin/pesanan/' . $pesanan->id . '/assign-teknisi',
            'Audit operasional live - service online terdaftar'
        );

        $service = Service::query()->where('pesanan_id', $pesanan->id)->latest('id')->first();

        if ($service) {
            $this->created['service'][] = [
                'source' => 'online_registered',
                'pesanan_id' => (int) $pesanan->id,
                'service_id' => (int) $service->id,
            ];
        }

        $this->created['pesanan_online'][] = [
            'flow' => 'service_terdaftar',
            'pesanan_id' => (int) $pesanan->id,
        ];

        $this->transactions[] = [
            'flow' => 'customer_service_registered',
            'pesanan_id' => (int) $pesanan->id,
            'status' => (string) $pesanan->status,
            'service_id' => $service?->id,
            'proof_path' => $pesanan->bukti_pembayaran,
            'service_paket_id' => (int) $this->servicePaket->id,
        ];

        $status = $order->isRedirect()
            && $paymentPage->getStatusCode() === 200
            && $payment->isRedirect()
            && $proofExists
            && $proofDisplayed
            && $pesanan->status === Pesanan::STATUS_SELESAI_FINAL
            && $service
            ? self::STATUS_OK
            : self::STATUS_FAIL;

        $this->record(
            feature: 'Service Pelanggan APAR Terdaftar',
            role: 'pelanggan/admin/teknisi',
            steps: [
                'Pelanggan ajukan service untuk unit APAR existing yang terdaftar.',
                'Upload bukti transfer dari file existing storage.',
                'Cek bukti bayar tampil di halaman admin service.',
                'Assign teknisi, kerjakan, lalu finalisasi admin.',
            ],
            expected: 'Service online untuk unit terdaftar selesai final dan log service terbentuk.',
            actual: 'Order ' . $order->getStatusCode()
                . ', payment page ' . $paymentPage->getStatusCode()
                . ', upload ' . $payment->getStatusCode()
                . ', proof ' . ($proofExists ? 'tersimpan' : 'tidak tersimpan')
                . ', display ' . ($proofDisplayed ? 'muncul' : 'tidak muncul')
                . ', assign/mulai/selesai/final ' . $this->responseCode($flow['assign']) . '/' . $flow['mulai']->getStatusCode() . '/' . $flow['selesai']->getStatusCode() . '/' . $flow['final']->getStatusCode()
                . ', status akhir `' . $pesanan->status . '`, service log ' . ($service?->id ?? '-') . '.',
            status: $status,
        );
    }

    private function runOnlineManualServiceFlow(): void
    {
        $marker = $this->marker('service-online-belum-terdaftar');
        $beforePesananId = (int) (Pesanan::query()->max('id') ?? 0);

        $pricingService = app(ServicePackagePricingService::class);
        $jenisApar = JenisApar::query()->where('nama', 'like', '%Powder%')->first() ?? JenisApar::query()->firstOrFail();
        $manualMedia = $pricingService->displayMediaLabel((string) $jenisApar->nama);
        $manualUkuran = collect($pricingService->availableMediaOptions())
            ->firstWhere('label', $manualMedia)['sizes'][0] ?? '1 kg';

        $order = $this->callControllerWithRequestAs(
            $this->customerUser,
            PublicController::class,
            'orderStore',
            '/order',
            [
            'nama' => $this->customer->nama,
            'no_wa' => $this->customer->no_wa,
            'alamat_maps' => $this->customer->alamat_maps,
            'alamat_detail' => $this->customer->alamat_detail,
            'alamat_provinsi' => $this->customer->alamat_provinsi,
            'alamat_kota' => $this->customer->alamat_kota,
            'alamat_kecamatan' => $this->customer->alamat_kecamatan,
            'alamat_kode_pos' => $this->customer->alamat_kode_pos,
            'alamat_lat' => $this->customer->alamat_lat,
            'alamat_lng' => $this->customer->alamat_lng,
            'tipe_layanan' => 'service',
            'service_jenis_layanan' => 'service',
            'service_unit_status' => 'belum_terdaftar',
            'service_jenis_apar' => $manualMedia,
            'service_ukuran_apar' => $manualUkuran,
            'service_jumlah_unit' => 1,
            'service_paket_id' => $this->servicePaket->id,
            'service_keluhan' => $marker,
            'service_metode_penanganan' => 'dijemput',
            ],
        );

        $pesanan = Pesanan::query()
            ->where('id', '>', $beforePesananId)
            ->where('pelanggan_id', $this->customer->id)
            ->where('tipe', 'service')
            ->where('service_jenis_layanan', 'service')
            ->latest('id')
            ->first();

        if (!$pesanan) {
            $this->record(
                feature: 'Service Pelanggan APAR Belum Terdaftar',
                role: 'pelanggan/admin/teknisi',
                steps: [
                    'Pelanggan ajukan service untuk APAR yang belum terdaftar.',
                ],
                expected: 'Pesanan service manual online terbentuk.',
                actual: 'Submit service manual status ' . $order->getStatusCode() . ', tetapi pesanan baru tidak ditemukan.',
                status: self::STATUS_FAIL,
            );
            return;
        }

        $paymentPage = $this->callControllerAs(
            $this->customerUser,
            PublicController::class,
            'orderPayment',
            ['pesanan' => $pesanan],
            '/order/' . $pesanan->id . '/payment'
        );
        $payment = $this->callControllerWithRequestAs(
            $this->customerUser,
            PublicController::class,
            'orderPaymentStore',
            '/order/' . $pesanan->id . '/payment',
            [
                'metode_pembayaran' => 'transfer',
                'bank' => 'bca',
            ],
            ['pesanan' => $pesanan],
            ['bukti_pembayaran' => $this->paymentProofFile('service-manual')]
        );

        $pesanan->refresh();
        $proofExists = $this->proofExists($pesanan);
        $adminPage = $this->actingAs($this->admin)->get('/admin/service');
        $proofDisplayed = $proofExists && $this->pageContainsProof($adminPage->getContent(), $pesanan);
        $this->registerProofCheck('service_online_manual', $pesanan, '/admin/service', $proofDisplayed);

        $flow = $this->completeTransactionFlow(
            $pesanan,
            '/admin/pesanan/' . $pesanan->id . '/assign-teknisi',
            'Audit operasional live - service online belum terdaftar'
        );

        $service = Service::query()->where('pesanan_id', $pesanan->id)->latest('id')->first();

        if ($service) {
            $this->created['service'][] = [
                'source' => 'online_manual',
                'pesanan_id' => (int) $pesanan->id,
                'service_id' => (int) $service->id,
            ];
        }

        $this->created['pesanan_online'][] = [
            'flow' => 'service_belum_terdaftar',
            'pesanan_id' => (int) $pesanan->id,
        ];

        $this->transactions[] = [
            'flow' => 'customer_service_unregistered',
            'pesanan_id' => (int) $pesanan->id,
            'status' => (string) $pesanan->status,
            'service_id' => $service?->id,
            'proof_path' => $pesanan->bukti_pembayaran,
            'service_paket_id' => (int) $this->servicePaket->id,
            'service_media' => $manualMedia,
            'service_size' => $manualUkuran,
        ];

        $status = $order->isRedirect()
            && $paymentPage->getStatusCode() === 200
            && $payment->isRedirect()
            && $proofExists
            && $proofDisplayed
            && $pesanan->status === Pesanan::STATUS_SELESAI_FINAL
            && $service
            ? self::STATUS_OK
            : self::STATUS_FAIL;

        $this->record(
            feature: 'Service Pelanggan APAR Belum Terdaftar',
            role: 'pelanggan/admin/teknisi',
            steps: [
                'Pelanggan ajukan service manual untuk APAR yang belum terdaftar.',
                'Upload bukti transfer dari file existing storage.',
                'Cek bukti bayar tampil di halaman admin service.',
                'Assign teknisi, kerjakan, lalu finalisasi admin.',
            ],
            expected: 'Service manual online selesai final dan log service terbentuk.',
            actual: 'Order ' . $order->getStatusCode()
                . ', payment page ' . $paymentPage->getStatusCode()
                . ', upload ' . $payment->getStatusCode()
                . ', proof ' . ($proofExists ? 'tersimpan' : 'tidak tersimpan')
                . ', display ' . ($proofDisplayed ? 'muncul' : 'tidak muncul')
                . ', assign/mulai/selesai/final ' . $this->responseCode($flow['assign']) . '/' . $flow['mulai']->getStatusCode() . '/' . $flow['selesai']->getStatusCode() . '/' . $flow['final']->getStatusCode()
                . ', status akhir `' . $pesanan->status . '`, service log ' . ($service?->id ?? '-') . '.',
            status: $status,
        );
    }

    private function runAdminOfflineOrderFlow(): void
    {
        $marker = $this->marker('pesanan-offline-admin');
        $beforePesananId = (int) (Pesanan::query()->max('id') ?? 0);
        $beforeUnitId = (int) (UnitApar::query()->max('id') ?? 0);

        $order = $this->actingAs($this->admin)->post('/admin/pesanan', [
            'tipe' => 'produk',
            'pelanggan_id' => $this->customer->id,
            'tanggal' => Carbon::today('Asia/Jakarta')->toDateString(),
            'catatan_admin' => $marker,
            'items' => [
                [
                    'produk_id' => $this->product->id,
                    'kapasitas' => $this->product->kapasitas,
                    'merek' => $this->product->merek,
                    'jumlah' => 1,
                ],
            ],
        ]);

        $pesanan = Pesanan::query()
            ->where('id', '>', $beforePesananId)
            ->where('pelanggan_id', $this->customer->id)
            ->where('tipe', 'produk')
            ->where('sumber_pesanan', 'datang_langsung')
            ->where('catatan_admin', $marker)
            ->latest('id')
            ->first();

        if (!$pesanan) {
            $this->record(
                feature: 'Pesanan Offline Admin',
                role: 'admin/teknisi',
                steps: [
                    'Admin input pesanan produk offline untuk pelanggan existing.',
                ],
                expected: 'Pesanan offline admin terbentuk.',
                actual: 'Store offline status ' . $order->getStatusCode() . ', tetapi pesanan baru tidak ditemukan.',
                status: self::STATUS_FAIL,
            );
            return;
        }

        $flow = $this->completeTransactionFlow(
            $pesanan,
            '/admin/pesanan/' . $pesanan->id . '/assign-teknisi',
            'Audit operasional live - pesanan offline admin'
        );

        $invoiceHtml = $this->callControllerAs(
            $this->admin,
            InvoiceController::class,
            'show',
            ['pesanan' => $pesanan],
            '/invoice/' . $pesanan->id
        );
        $invoicePdf = $this->callControllerAs(
            $this->admin,
            InvoiceController::class,
            'pdf',
            ['pesanan' => $pesanan],
            '/invoice/' . $pesanan->id . '/pdf'
        );
        $units = UnitApar::query()
            ->where('id', '>', $beforeUnitId)
            ->where('pesanan_id', $pesanan->id)
            ->get();

        foreach ($units as $unit) {
            $this->created['unit_apar'][] = [
                'source' => 'produk_offline',
                'id' => (int) $unit->id,
                'pesanan_id' => (int) $pesanan->id,
                'no_seri' => $unit->no_seri,
            ];
        }

        $this->created['pesanan_offline'][] = [
            'flow' => 'produk_admin',
            'pesanan_id' => (int) $pesanan->id,
        ];

        $this->transactions[] = [
            'flow' => 'admin_offline_product_order',
            'pesanan_id' => (int) $pesanan->id,
            'status' => (string) $pesanan->status,
            'invoice_html_status' => $invoiceHtml->getStatusCode(),
            'invoice_pdf_status' => $invoicePdf->getStatusCode(),
            'created_unit_ids' => $units->pluck('id')->map(fn ($id) => (int) $id)->all(),
        ];

        $status = $order->isRedirect()
            && $pesanan->status === Pesanan::STATUS_SELESAI_FINAL
            && $invoiceHtml->getStatusCode() === 200
            && $invoicePdf->getStatusCode() === 200
            && $units->count() > 0
            ? self::STATUS_OK
            : self::STATUS_FAIL;

        $this->record(
            feature: 'Pesanan Offline Admin',
            role: 'admin/teknisi',
            steps: [
                'Admin input 1 pesanan produk offline untuk pelanggan existing.',
                'Assign teknisi, mulai, selesaikan, lalu finalisasi admin.',
                'Buka invoice HTML dan PDF pesanan offline.',
            ],
            expected: 'Pesanan offline selesai final, invoice terbuka, dan unit APAR baru tercatat.',
            actual: 'Store ' . $order->getStatusCode()
                . ', assign/mulai/selesai/final ' . $this->responseCode($flow['assign']) . '/' . $flow['mulai']->getStatusCode() . '/' . $flow['selesai']->getStatusCode() . '/' . $flow['final']->getStatusCode()
                . ', invoice ' . $invoiceHtml->getStatusCode() . '/' . $invoicePdf->getStatusCode()
                . ', status akhir `' . $pesanan->status . '`, unit baru ' . $units->count() . '.',
            status: $status,
        );
    }

    private function runAdminOfflineRefillFlow(): void
    {
        $marker = $this->marker('refill-offline-admin');
        $beforePesananId = (int) (Pesanan::query()->max('id') ?? 0);

        $store = $this->actingAs($this->admin)->post('/admin/refill', [
            'pelanggan_id' => $this->customer->id,
            'unit_apar_id' => $this->registeredUnit->id,
            'jenis_refill_id' => $this->powderRefill->id,
            'ukuran_apar' => $this->registeredUnit->ukuran ?: ($this->registeredUnit->produk?->kapasitas ?: '1 kg'),
            'jumlah_unit' => 1,
            'tgl_refill' => Carbon::today('Asia/Jakarta')->toDateString(),
            'catatan_admin' => $marker,
        ]);

        $pesanan = Pesanan::query()
            ->where('id', '>', $beforePesananId)
            ->where('pelanggan_id', $this->customer->id)
            ->where('tipe', 'service')
            ->where('sumber_pesanan', 'datang_langsung')
            ->where('service_jenis_layanan', 'refill')
            ->where('catatan_admin', $marker)
            ->latest('id')
            ->first();

        if (!$pesanan) {
            $this->record(
                feature: 'Refill Offline Admin APAR Terdaftar',
                role: 'admin/teknisi',
                steps: [
                    'Admin input refill offline untuk unit existing pelanggan.',
                ],
                expected: 'Refill offline admin membentuk pesanan baru.',
                actual: 'Store refill offline status ' . $store->getStatusCode() . ', tetapi pesanan baru tidak ditemukan.',
                status: self::STATUS_FAIL,
            );
            return;
        }

        $flow = $this->completeTransactionFlow(
            $pesanan,
            null,
            'Audit operasional live - refill offline admin'
        );

        $refill = Refill::query()
            ->whereHas('service', fn (Builder $query) => $query->where('pesanan_id', $pesanan->id))
            ->latest('id')
            ->first();

        if ($refill) {
            $this->created['refill'][] = [
                'source' => 'offline_admin_registered',
                'pesanan_id' => (int) $pesanan->id,
                'refill_id' => (int) $refill->id,
            ];
        }

        $this->created['pesanan_offline'][] = [
            'flow' => 'refill_admin_registered',
            'pesanan_id' => (int) $pesanan->id,
        ];

        $this->transactions[] = [
            'flow' => 'admin_offline_refill_registered',
            'pesanan_id' => (int) $pesanan->id,
            'status' => (string) $pesanan->status,
            'refill_id' => $refill?->id,
            'unit_apar_id' => (int) $this->registeredUnit->id,
        ];

        $status = $store->isRedirect()
            && $pesanan->status === Pesanan::STATUS_SELESAI_FINAL
            && $refill
            ? self::STATUS_OK
            : self::STATUS_FAIL;

        $this->record(
            feature: 'Refill Offline Admin APAR Terdaftar',
            role: 'admin/teknisi',
            steps: [
                'Admin input refill offline memakai unit APAR existing pelanggan.',
                'Teknisi mulai, selesaikan, lalu admin finalisasi.',
            ],
            expected: 'Refill offline selesai final dan log refill terbentuk.',
            actual: 'Store ' . $store->getStatusCode()
                . ', mulai/selesai/final ' . $flow['mulai']->getStatusCode() . '/' . $flow['selesai']->getStatusCode() . '/' . $flow['final']->getStatusCode()
                . ', status akhir `' . $pesanan->status . '`, refill log ' . ($refill?->id ?? '-') . '.',
            status: $status,
        );
    }

    private function runAdminOfflineManualRefillFlow(): void
    {
        $marker = $this->marker('refill-offline-admin-belum-terdaftar');
        $beforePesananId = (int) (Pesanan::query()->max('id') ?? 0);
        $beforeUnitId = (int) (UnitApar::query()->max('id') ?? 0);

        $store = $this->actingAs($this->admin)->post('/admin/refill', [
            'pelanggan_id' => $this->customer->id,
            'jenis_refill_id' => $this->foamRefill->id,
            'ukuran_apar' => '6 kg',
            'jumlah_unit' => 2,
            'tgl_refill' => Carbon::today('Asia/Jakarta')->toDateString(),
            'catatan_admin' => $marker,
        ]);

        $pesanan = Pesanan::query()
            ->where('id', '>', $beforePesananId)
            ->where('pelanggan_id', $this->customer->id)
            ->where('tipe', 'service')
            ->where('sumber_pesanan', 'datang_langsung')
            ->where('service_jenis_layanan', 'refill')
            ->where('catatan_admin', $marker)
            ->latest('id')
            ->first();

        if (!$pesanan) {
            $this->record(
                feature: 'Refill Offline Admin APAR Tidak Terdaftar',
                role: 'admin/teknisi',
                steps: [
                    'Admin input refill offline untuk APAR tidak terdaftar dengan 2 unit.',
                ],
                expected: 'Refill offline admin membentuk pesanan baru untuk APAR tidak terdaftar.',
                actual: 'Store refill offline manual status ' . $store->getStatusCode() . ', tetapi pesanan baru tidak ditemukan.',
                status: self::STATUS_FAIL,
            );
            return;
        }

        $flow = $this->completeTransactionFlow(
            $pesanan,
            null,
            'Audit operasional live - refill offline admin belum terdaftar'
        );

        $refill = Refill::query()
            ->whereHas('service', fn (Builder $query) => $query->where('pesanan_id', $pesanan->id))
            ->latest('id')
            ->first();
        $units = UnitApar::query()
            ->where('id', '>', $beforeUnitId)
            ->where('pesanan_id', $pesanan->id)
            ->orderBy('id')
            ->get();

        if ($refill) {
            $this->created['refill'][] = [
                'source' => 'offline_admin_unregistered',
                'pesanan_id' => (int) $pesanan->id,
                'refill_id' => (int) $refill->id,
            ];
        }

        foreach ($units as $unit) {
            $this->created['unit_apar'][] = [
                'source' => 'refill_offline_manual',
                'id' => (int) $unit->id,
                'pesanan_id' => (int) $pesanan->id,
                'no_seri' => (string) $unit->no_seri,
            ];
        }

        $this->created['pesanan_offline'][] = [
            'flow' => 'refill_admin_unregistered',
            'pesanan_id' => (int) $pesanan->id,
        ];

        $this->transactions[] = [
            'flow' => 'admin_offline_refill_unregistered',
            'pesanan_id' => (int) $pesanan->id,
            'status' => (string) $pesanan->status,
            'refill_id' => $refill?->id,
            'created_unit_ids' => $units->pluck('id')->map(fn ($id) => (int) $id)->all(),
        ];

        $status = $store->isRedirect()
            && $pesanan->status === Pesanan::STATUS_SELESAI_FINAL
            && $refill
            && $units->count() === 2
            ? self::STATUS_OK
            : self::STATUS_FAIL;

        $this->record(
            feature: 'Refill Offline Admin APAR Tidak Terdaftar',
            role: 'admin/teknisi',
            steps: [
                'Admin input refill offline Foam 6 kg untuk 2 unit APAR tidak terdaftar.',
                'Teknisi mulai, selesaikan, lalu admin finalisasi.',
                'Validasi terbentuk tepat 2 unit APAR baru setelah final.',
            ],
            expected: 'Refill offline tidak terdaftar selesai final, log refill terbentuk, dan muncul tepat 2 unit APAR baru.',
            actual: 'Store ' . $store->getStatusCode()
                . ', mulai/selesai/final ' . $flow['mulai']->getStatusCode() . '/' . $flow['selesai']->getStatusCode() . '/' . $flow['final']->getStatusCode()
                . ', status akhir `' . $pesanan->status . '`, refill log ' . ($refill?->id ?? '-') . ', unit baru ' . $units->count() . '.',
            status: $status,
        );
    }

    private function runAdminOfflineServiceFlow(): void
    {
        $marker = $this->marker('service-offline-admin');
        $beforePesananId = (int) (Pesanan::query()->max('id') ?? 0);

        $store = $this->actingAs($this->admin)->post('/admin/service', [
            'pelanggan_id' => $this->customer->id,
            'unit_apar_id' => $this->registeredUnit->id,
            'service_paket_id' => $this->servicePaket->id,
            'jenis_apar' => $this->registeredUnit->bahan ?: ($this->registeredUnit->produk?->jenisApar?->nama ?: 'Dry Chemical Powder'),
            'ukuran_apar' => $this->registeredUnit->ukuran ?: ($this->registeredUnit->produk?->kapasitas ?: '1 kg'),
            'jumlah_unit' => 1,
            'tgl_service' => Carbon::today('Asia/Jakarta')->toDateString(),
            'catatan_admin' => $marker,
        ]);

        $pesanan = Pesanan::query()
            ->where('id', '>', $beforePesananId)
            ->where('pelanggan_id', $this->customer->id)
            ->where('tipe', 'service')
            ->where('sumber_pesanan', 'datang_langsung')
            ->where('service_jenis_layanan', 'service')
            ->where('catatan_admin', $marker)
            ->latest('id')
            ->first();

        if (!$pesanan) {
            $this->record(
                feature: 'Service Offline Admin APAR Terdaftar',
                role: 'admin/teknisi',
                steps: [
                    'Admin input service offline untuk unit existing pelanggan.',
                ],
                expected: 'Service offline admin membentuk pesanan baru.',
                actual: 'Store service offline status ' . $store->getStatusCode() . ', tetapi pesanan baru tidak ditemukan.',
                status: self::STATUS_FAIL,
            );
            return;
        }

        $flow = $this->completeTransactionFlow(
            $pesanan,
            null,
            'Audit operasional live - service offline admin'
        );

        $service = Service::query()->where('pesanan_id', $pesanan->id)->latest('id')->first();

        if ($service) {
            $this->created['service'][] = [
                'source' => 'offline_admin_registered',
                'pesanan_id' => (int) $pesanan->id,
                'service_id' => (int) $service->id,
            ];
        }

        $this->created['pesanan_offline'][] = [
            'flow' => 'service_admin_registered',
            'pesanan_id' => (int) $pesanan->id,
        ];

        $this->transactions[] = [
            'flow' => 'admin_offline_service_registered',
            'pesanan_id' => (int) $pesanan->id,
            'status' => (string) $pesanan->status,
            'service_id' => $service?->id,
            'service_paket_id' => (int) $this->servicePaket->id,
            'unit_apar_id' => (int) $this->registeredUnit->id,
        ];

        $status = $store->isRedirect()
            && $pesanan->status === Pesanan::STATUS_SELESAI_FINAL
            && $service
            ? self::STATUS_OK
            : self::STATUS_FAIL;

        $this->record(
            feature: 'Service Offline Admin APAR Terdaftar',
            role: 'admin/teknisi',
            steps: [
                'Admin input service offline memakai unit APAR existing pelanggan.',
                'Teknisi mulai, selesaikan, lalu admin finalisasi.',
            ],
            expected: 'Service offline selesai final dan log service terbentuk.',
            actual: 'Store ' . $store->getStatusCode()
                . ', mulai/selesai/final ' . $flow['mulai']->getStatusCode() . '/' . $flow['selesai']->getStatusCode() . '/' . $flow['final']->getStatusCode()
                . ', status akhir `' . $pesanan->status . '`, service log ' . ($service?->id ?? '-') . '.',
            status: $status,
        );
    }

    private function runAdminOfflineManualServiceFlow(): void
    {
        $marker = $this->marker('service-offline-admin-belum-terdaftar');
        $beforePesananId = (int) (Pesanan::query()->max('id') ?? 0);
        $beforeUnitId = (int) (UnitApar::query()->max('id') ?? 0);

        $pricingService = app(ServicePackagePricingService::class);
        $jenisApar = JenisApar::query()->where('nama', 'like', '%Powder%')->first() ?? JenisApar::query()->firstOrFail();
        $manualMedia = $pricingService->displayMediaLabel((string) $jenisApar->nama);
        $manualUkuran = collect($pricingService->availableMediaOptions())
            ->firstWhere('label', $manualMedia)['sizes'][0] ?? '1 kg';

        $store = $this->actingAs($this->admin)->post('/admin/service', [
            'pelanggan_id' => $this->customer->id,
            'service_paket_id' => $this->servicePaket->id,
            'jenis_apar' => $manualMedia,
            'ukuran_apar' => $manualUkuran,
            'jumlah_unit' => 1,
            'tgl_service' => Carbon::today('Asia/Jakarta')->toDateString(),
            'catatan_admin' => $marker,
        ]);

        $pesanan = Pesanan::query()
            ->where('id', '>', $beforePesananId)
            ->where('pelanggan_id', $this->customer->id)
            ->where('tipe', 'service')
            ->where('sumber_pesanan', 'datang_langsung')
            ->where('service_jenis_layanan', 'service')
            ->where('catatan_admin', $marker)
            ->latest('id')
            ->first();

        if (!$pesanan) {
            $this->record(
                feature: 'Service Offline Admin APAR Tidak Terdaftar',
                role: 'admin/teknisi',
                steps: [
                    'Admin input service offline untuk APAR tidak terdaftar.',
                ],
                expected: 'Service offline admin membentuk pesanan baru untuk APAR tidak terdaftar.',
                actual: 'Store service offline manual status ' . $store->getStatusCode() . ', tetapi pesanan baru tidak ditemukan.',
                status: self::STATUS_FAIL,
            );
            return;
        }

        $flow = $this->completeTransactionFlow(
            $pesanan,
            null,
            'Audit operasional live - service offline admin belum terdaftar'
        );

        $service = Service::query()->where('pesanan_id', $pesanan->id)->latest('id')->first();
        $units = UnitApar::query()
            ->where('id', '>', $beforeUnitId)
            ->where('pesanan_id', $pesanan->id)
            ->orderBy('id')
            ->get();

        if ($service) {
            $this->created['service'][] = [
                'source' => 'offline_admin_unregistered',
                'pesanan_id' => (int) $pesanan->id,
                'service_id' => (int) $service->id,
            ];
        }

        foreach ($units as $unit) {
            $this->created['unit_apar'][] = [
                'source' => 'service_offline_manual',
                'id' => (int) $unit->id,
                'pesanan_id' => (int) $pesanan->id,
                'no_seri' => (string) $unit->no_seri,
            ];
        }

        $this->created['pesanan_offline'][] = [
            'flow' => 'service_admin_unregistered',
            'pesanan_id' => (int) $pesanan->id,
        ];

        $this->transactions[] = [
            'flow' => 'admin_offline_service_unregistered',
            'pesanan_id' => (int) $pesanan->id,
            'status' => (string) $pesanan->status,
            'service_id' => $service?->id,
            'service_paket_id' => (int) $this->servicePaket->id,
            'created_unit_ids' => $units->pluck('id')->map(fn ($id) => (int) $id)->all(),
        ];

        $status = $store->isRedirect()
            && $pesanan->status === Pesanan::STATUS_SELESAI_FINAL
            && $service
            && $units->count() >= 1
            ? self::STATUS_OK
            : self::STATUS_FAIL;

        $this->record(
            feature: 'Service Offline Admin APAR Tidak Terdaftar',
            role: 'admin/teknisi',
            steps: [
                'Admin input service offline untuk APAR tidak terdaftar.',
                'Teknisi mulai, selesaikan, lalu admin finalisasi.',
                'Validasi unit APAR baru terbentuk setelah final bila alur sistem mendukung.',
            ],
            expected: 'Service offline tidak terdaftar selesai final, log service terbentuk, dan unit APAR baru tercatat.',
            actual: 'Store ' . $store->getStatusCode()
                . ', mulai/selesai/final ' . $flow['mulai']->getStatusCode() . '/' . $flow['selesai']->getStatusCode() . '/' . $flow['final']->getStatusCode()
                . ', status akhir `' . $pesanan->status . '`, service log ' . ($service?->id ?? '-') . ', unit baru ' . $units->count() . '.',
            status: $status,
        );
    }

    private function runExpenseFlow(): void
    {
        $today = Carbon::today('Asia/Jakarta')->toDateString();
        $productExpense = $this->storeExpense([
            'jenis_pengeluaran' => Pengeluaran::JENIS_PEMBELIAN_APAR,
            'produk_id' => $this->product->id,
            'qty' => 3,
            'harga_beli' => max(1000, (int) round((float) $this->product->harga * 0.6)),
            'keterangan' => $this->marker('pengeluaran-apar'),
            'tanggal' => $today,
        ]);

        $refillExpense = $this->storeExpense([
            'jenis_pengeluaran' => Pengeluaran::JENIS_PEMBELIAN_REFILL,
            'jenis_refill_id' => $this->foamRefill->id,
            'qty' => 10,
            'keterangan' => $this->marker('pengeluaran-refill'),
            'tanggal' => $today,
        ]);

        $peralatanExpense = $this->storeExpense([
            'jenis_pengeluaran' => Pengeluaran::JENIS_PEMBELIAN_PERALATAN,
            'peralatan_id' => $this->expensePeralatan->id,
            'qty' => 5,
            'keterangan' => $this->marker('pengeluaran-peralatan'),
            'tanggal' => $today,
        ]);

        $stokPage = $this->actingAs($this->admin)->get('/admin/stok');

        $allCreated = array_filter([$productExpense['record'], $refillExpense['record'], $peralatanExpense['record']]);
        foreach ($allCreated as $expense) {
            $this->created['pengeluaran'][] = [
                'id' => (int) $expense->id,
                'jenis_pengeluaran' => $expense->jenis_pengeluaran,
                'qty' => (float) $expense->qty,
                'total' => (float) $expense->effective_amount,
            ];
        }

        $this->transactions[] = [
            'flow' => 'stock_additions',
            'pengeluaran_ids' => collect($allCreated)->pluck('id')->map(fn ($id) => (int) $id)->all(),
            'stok_page_status' => $stokPage->getStatusCode(),
        ];

        $status = $productExpense['response']->isRedirect()
            && $refillExpense['response']->isRedirect()
            && $peralatanExpense['response']->isRedirect()
            && $stokPage->getStatusCode() === 200
            ? self::STATUS_OK
            : self::STATUS_FAIL;

        $this->record(
            feature: 'Pengeluaran dan Penambahan Stok',
            role: 'admin',
            steps: [
                'Tambah stok produk via pengeluaran pembelian APAR.',
                'Tambah stok jenis refill Foam via pengeluaran pembelian refill.',
                'Tambah stok peralatan via pengeluaran pembelian peralatan.',
                'Buka halaman stok admin setelah seluruh penambahan.',
            ],
            expected: 'Tiga jenis pengeluaran stock-affecting berhasil menambah stok dan halaman stok tetap terbuka normal.',
            actual: 'Store APAR/refill/peralatan '
                . $productExpense['response']->getStatusCode() . '/'
                . $refillExpense['response']->getStatusCode() . '/'
                . $peralatanExpense['response']->getStatusCode()
                . ', record ID '
                . ($productExpense['record']?->id ?? '-') . '/'
                . ($refillExpense['record']?->id ?? '-') . '/'
                . ($peralatanExpense['record']?->id ?? '-')
                . ', halaman stok ' . $stokPage->getStatusCode() . '.',
            status: $status,
        );
    }

    private function runUnitAparFlow(): void
    {
        $marker = $this->marker('unit-apar-manual');
        $beforeUnitId = (int) (UnitApar::query()->max('id') ?? 0);

        $store = $this->actingAs($this->admin)->post('/admin/unit-apar', [
            'pelanggan_id' => $this->customer->id,
            'produk_id' => $this->product->id,
            'tgl_beli' => Carbon::today('Asia/Jakarta')->toDateString(),
            'tgl_produksi' => Carbon::today('Asia/Jakarta')->toDateString(),
            'lokasi_unit' => 'Lokasi Audit Live ' . $this->runId,
            'kondisi_awal' => 'layak',
            'catatan_unit' => $marker,
        ]);

        $unit = UnitApar::query()
            ->where('id', '>', $beforeUnitId)
            ->where('pelanggan_id', $this->customer->id)
            ->where('catatan_unit', $marker)
            ->latest('id')
            ->first();

        if (!$unit) {
            $this->record(
                feature: 'Registrasi Unit APAR Manual',
                role: 'admin',
                steps: [
                    'Admin registrasi unit APAR manual untuk pelanggan existing.',
                ],
                expected: 'Unit APAR manual baru terbentuk.',
                actual: 'Store unit status ' . $store->getStatusCode() . ', tetapi unit baru tidak ditemukan.',
                status: self::STATUS_FAIL,
            );
            return;
        }

        $update = $this->actingAs($this->admin)->put('/admin/unit-apar/' . $unit->id, [
            'pelanggan_id' => $this->customer->id,
            'produk_id' => $this->product->id,
            'no_seri' => $unit->no_seri,
            'tgl_beli' => Carbon::today('Asia/Jakarta')->subDay()->toDateString(),
            'tgl_produksi' => Carbon::today('Asia/Jakarta')->subDay()->toDateString(),
            'lokasi_unit' => 'Lokasi Audit Live Update ' . $this->runId,
            'kondisi_awal' => 'perlu_servis',
            'catatan_unit' => $marker . ' update',
        ]);
        $detail = $this->actingAs($this->admin)->get('/admin/unit-apar/' . $unit->id);

        $unit->refresh();
        $this->created['unit_apar'][] = [
            'source' => 'manual_admin',
            'id' => (int) $unit->id,
            'pesanan_id' => $unit->pesanan_id ? (int) $unit->pesanan_id : null,
            'no_seri' => $unit->no_seri,
        ];

        $this->transactions[] = [
            'flow' => 'unit_apar_creation',
            'unit_id' => (int) $unit->id,
            'detail_status' => $detail->getStatusCode(),
        ];

        $status = $store->isRedirect()
            && $update->isRedirect()
            && $detail->getStatusCode() === 200
            ? self::STATUS_OK
            : self::STATUS_FAIL;

        $this->record(
            feature: 'Registrasi Unit APAR Manual',
            role: 'admin',
            steps: [
                'Admin buat unit APAR manual untuk pelanggan existing.',
                'Admin edit unit yang baru dibuat.',
                'Buka halaman detail unit APAR.',
            ],
            expected: 'Unit APAR manual tersimpan, bisa diubah, dan tampil di halaman detail.',
            actual: 'Store ' . $store->getStatusCode()
                . ', update ' . $update->getStatusCode()
                . ', detail ' . $detail->getStatusCode()
                . ', unit ID ' . $unit->id . '.',
            status: $status,
        );
    }

    private function runReportPagesFlow(): void
    {
        $pages = [
            '/admin/laporan',
            '/admin/laporan/pesanan',
            '/admin/laporan/service',
            '/admin/laporan/keuangan',
            '/admin/laporan/apar',
            '/admin/laporan/pdf',
            '/admin/laporan/pesanan/pdf',
            '/admin/laporan/service/pdf',
            '/admin/laporan/keuangan/pdf',
            '/admin/laporan/apar/pdf',
        ];

        $statuses = [];
        foreach ($pages as $page) {
            $statuses[$page] = $this->actingAs($this->admin)->get($page)->getStatusCode();
        }

        $this->transactions[] = [
            'flow' => 'report_pages',
            'statuses' => $statuses,
        ];

        $allOk = collect($statuses)->every(fn ($status) => $status === 200);

        $this->record(
            feature: 'Halaman Laporan dan PDF',
            role: 'admin',
            steps: [
                'Buka laporan utama, pesanan, service, keuangan, dan unit APAR.',
                'Buka seluruh versi PDF laporan setelah transaksi audit dibuat.',
            ],
            expected: 'Seluruh halaman laporan dan PDF terbuka normal dengan status HTTP 200.',
            actual: json_encode($statuses, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            status: $allOk ? self::STATUS_OK : self::STATUS_FAIL,
        );
    }

    private function runLaravelLogCheck(): void
    {
        $safe = !$this->hasNewLaravelErrors();

        $this->record(
            feature: 'Cek Laravel Log',
            role: 'system',
            steps: [
                'Bandingkan ukuran dan isi baru `storage/logs/laravel.log` sebelum dan sesudah audit.',
            ],
            expected: 'Tidak ada error baru level ERROR/exception yang muncul selama audit operasional live.',
            actual: $safe ? 'Tidak ada error baru yang terdeteksi di laravel.log.' : 'Ditemukan error baru di laravel.log setelah audit.',
            status: $safe ? self::STATUS_OK : self::STATUS_WARN,
        );
    }

    private function completeTransactionFlow(Pesanan $pesanan, ?string $assignPath, string $note): array
    {
        $assign = null;
        if ($assignPath) {
            $assignController = str_contains($assignPath, '/admin/refill/')
                ? AdminRefillController::class
                : AdminPesananController::class;

            $assign = $this->callControllerWithRequestAs(
                $this->admin,
                $assignController,
                'assignTeknisi',
                $assignPath,
                [],
                ['pesanan' => $pesanan]
            );
        }

        $mulai = $this->callControllerAs(
            $this->teknisi,
            TeknisiController::class,
            'tugasMulai',
            ['pesanan' => $pesanan],
            '/teknisi/tugas/' . $pesanan->id . '/mulai',
            'POST'
        );
        $selesai = $this->callControllerWithRequestAs(
            $this->teknisi,
            TeknisiController::class,
            'tugasSelesai',
            '/teknisi/tugas/' . $pesanan->id . '/selesai',
            [
                'catatan' => $note,
            ],
            ['pesanan' => $pesanan]
        );
        $final = $this->callControllerAs(
            $this->admin,
            AdminPesananController::class,
            'selesaiFinal',
            ['pesanan' => $pesanan],
            '/admin/pesanan/' . $pesanan->id . '/selesai-final',
            'POST'
        );
        $pesanan->refresh();

        return compact('assign', 'mulai', 'selesai', 'final');
    }

    private function callControllerAs(
        User $user,
        string $controllerClass,
        string $method,
        array $routeParameters = [],
        string $uri = '/',
        string $httpMethod = 'GET'
    ): AuditResponseAdapter {
        try {
            $this->prepareControllerRequest($user, $uri, $httpMethod);

            return new AuditResponseAdapter(
                $this->app->call([app($controllerClass), $method], $routeParameters)
            );
        } catch (ValidationException $exception) {
            return new AuditResponseAdapter(response()->json([
                'message' => 'Validation failed.',
                'errors' => $exception->errors(),
            ], 422));
        } catch (HttpExceptionInterface $exception) {
            return new AuditResponseAdapter(response($exception->getMessage(), $exception->getStatusCode()));
        } catch (\Throwable $throwable) {
            return new AuditResponseAdapter(response($throwable->getMessage(), 500));
        }
    }

    private function callControllerWithRequestAs(
        User $user,
        string $controllerClass,
        string $method,
        string $uri,
        array $payload = [],
        array $routeParameters = [],
        array $files = [],
        string $httpMethod = 'POST'
    ): AuditResponseAdapter {
        try {
            $request = $this->prepareControllerRequest($user, $uri, $httpMethod, $payload, $files);

            return new AuditResponseAdapter(
                $this->app->call(
                    [app($controllerClass), $method],
                    array_merge($routeParameters, ['request' => $request])
                )
            );
        } catch (ValidationException $exception) {
            return new AuditResponseAdapter(response()->json([
                'message' => 'Validation failed.',
                'errors' => $exception->errors(),
            ], 422));
        } catch (HttpExceptionInterface $exception) {
            return new AuditResponseAdapter(response($exception->getMessage(), $exception->getStatusCode()));
        } catch (\Throwable $throwable) {
            return new AuditResponseAdapter(response($throwable->getMessage(), 500));
        }
    }

    private function prepareControllerRequest(
        User $user,
        string $uri,
        string $httpMethod,
        array $payload = [],
        array $files = []
    ): Request {
        Auth::shouldUse(config('auth.defaults.guard', 'web'));
        Auth::logout();
        Auth::login($user);

        $session = $this->app['session']->driver();
        $session->start();

        $request = Request::create($uri, strtoupper($httpMethod), $payload, [], $files, [
            'HTTP_REFERER' => url('/'),
            'HTTP_HOST' => '127.0.0.1:8000',
            'REQUEST_URI' => $uri,
        ]);
        $request->setLaravelSession($session);
        $request->setUserResolver(fn () => $user);

        $this->app->instance('request', $request);

        return $request;
    }

    private function storeExpense(array $payload): array
    {
        $beforeId = (int) (Pengeluaran::query()->max('id') ?? 0);

        $response = $this->actingAs($this->admin)->post('/admin/pengeluaran', $payload);

        $record = Pengeluaran::query()
            ->where('id', '>', $beforeId)
            ->where('keterangan', $payload['keterangan'])
            ->latest('id')
            ->first();

        return compact('response', 'record');
    }

    private function paymentProofFile(string $label): UploadedFile
    {
        $extension = pathinfo($this->proofSourcePath, PATHINFO_EXTENSION) ?: 'jpg';
        $mime = File::mimeType($this->proofSourcePath) ?: 'image/jpeg';

        return new UploadedFile(
            $this->proofSourcePath,
            $label . '-' . strtolower($this->runId) . '.' . strtolower($extension),
            $mime,
            null,
            true
        );
    }

    private function proofExists(Pesanan $pesanan): bool
    {
        return filled($pesanan->bukti_pembayaran)
            && Storage::disk('public')->exists((string) $pesanan->bukti_pembayaran);
    }

    private function pageContainsProof(?string $content, Pesanan $pesanan): bool
    {
        $proofPath = (string) $pesanan->bukti_pembayaran;
        $needle = '/storage/' . ltrim($proofPath, '/');
        $fileName = basename($proofPath);

        return is_string($content) && (
            ($needle !== '/storage/' && str_contains($content, $needle))
            || ($fileName !== '' && str_contains($content, $fileName))
        );
    }

    private function registerProofCheck(string $context, Pesanan $pesanan, string $page, bool $displayed): void
    {
        $this->proofChecks[] = [
            'context' => $context,
            'pesanan_id' => (int) $pesanan->id,
            'proof_path' => (string) $pesanan->bukti_pembayaran,
            'storage_exists' => $this->proofExists($pesanan),
            'display_page' => $page,
            'displayed' => $displayed,
        ];
    }

    private function snapshotState(): array
    {
        return [
            'stocks' => $this->snapshotStocks(),
            'units' => $this->snapshotUnits(),
            'reports' => $this->snapshotReports(),
        ];
    }

    private function snapshotStocks(): array
    {
        $product = $this->product->fresh();
        $powder = $this->powderRefill->fresh();
        $foam = $this->foamRefill->fresh();
        $peralatanIds = $this->monitoredPeralatanIds();

        $peralatans = Peralatan::query()
            ->whereIn('id', $peralatanIds)
            ->orderBy('id')
            ->get()
            ->map(fn (Peralatan $item) => [
                'id' => (int) $item->id,
                'nama' => (string) $item->nama,
                'stok' => (int) $item->stok,
                'stok_minimum' => (int) $item->stok_minimum,
            ])
            ->all();

        return [
            'produk' => [
                'id' => (int) $product->id,
                'nama' => (string) $product->nama,
                'stok' => (int) $product->stok,
            ],
            'refills' => [
                [
                    'id' => (int) $powder->id,
                    'nama' => (string) $powder->nama,
                    'stok' => (float) $powder->stok,
                    'satuan' => (string) $powder->satuan_label,
                ],
                [
                    'id' => (int) $foam->id,
                    'nama' => (string) $foam->nama,
                    'stok' => (float) $foam->stok,
                    'satuan' => (string) $foam->satuan_label,
                ],
            ],
            'peralatan' => $peralatans,
        ];
    }

    private function snapshotUnits(): array
    {
        $customerUnits = UnitApar::query()
            ->where('pelanggan_id', $this->customer->id)
            ->orderBy('id')
            ->get(['id', 'no_seri', 'pesanan_id']);

        return [
            'global_total' => (int) UnitApar::query()->count(),
            'customer_total' => (int) $customerUnits->count(),
            'customer_units' => $customerUnits->map(fn (UnitApar $unit) => [
                'id' => (int) $unit->id,
                'no_seri' => (string) ($unit->no_seri ?: ''),
                'pesanan_id' => $unit->pesanan_id ? (int) $unit->pesanan_id : null,
            ])->all(),
        ];
    }

    private function snapshotReports(): array
    {
        $revenue = app(FinalRevenueService::class);
        $productOrdersQuery = $revenue->productOrdersQuery();
        $serviceQuery = $revenue->serviceTransactionsQuery();
        $refillQuery = $revenue->refillTransactionsQuery();
        $breakdown = $revenue->breakdown();

        $productOrders = (clone $productOrdersQuery)->with('details')->get();
        $services = (clone $serviceQuery)->get();
        $refills = (clone $refillQuery)->get();
        $pengeluarans = Pengeluaran::query()->get();
        $units = UnitApar::query()->get(['id', 'tgl_expired']);

        $now = now()->startOfDay();
        $expiringLimit = $now->copy()->addDays(30);
        $totalPengeluaran = $pengeluarans->sum(fn (Pengeluaran $item) => $item->effective_amount);

        return [
            'summary' => [
                'total_pesanan_produk' => (int) $productOrders->count(),
                'total_nilai_pesanan' => (float) $breakdown['product'],
                'total_service' => (int) $services->count(),
                'total_biaya_service' => (float) $breakdown['service'],
                'total_refill' => (int) $refills->count(),
                'total_biaya_refill' => (float) $breakdown['refill'],
                'total_unit' => (int) $units->count(),
                'total_pengeluaran' => (float) $totalPengeluaran,
                'total_pemasukan' => (float) $breakdown['total'],
                'laba_bersih' => (float) ($breakdown['total'] - $totalPengeluaran),
            ],
            'pesanan' => [
                'total_transaksi' => (int) $productOrders->count(),
                'total_item' => (int) $productOrders->sum(fn (Pesanan $pesanan) => $pesanan->details->sum('jumlah')),
                'total_nilai' => (float) $productOrders->sum('total'),
            ],
            'service' => [
                'total_transaksi' => (int) $services->count(),
                'total_biaya' => (float) $services->sum('biaya'),
            ],
            'refill' => [
                'total_transaksi' => (int) $refills->count(),
                'total_biaya' => (float) $refills->sum('biaya'),
            ],
            'keuangan' => [
                'total_pemasukan' => (float) $breakdown['total'],
                'total_pengeluaran' => (float) $totalPengeluaran,
                'laba_bersih' => (float) ($breakdown['total'] - $totalPengeluaran),
                'total_transaksi' => (int) ($productOrders->count() + $services->count() + $refills->count()),
            ],
            'apar' => [
                'total' => (int) $units->count(),
                'aktif' => (int) $units->filter(fn ($unit) => $unit->tgl_expired && $unit->tgl_expired->isFuture() && $unit->tgl_expired->gt($expiringLimit))->count(),
                'hampir' => (int) $units->filter(fn ($unit) => $unit->tgl_expired && $unit->tgl_expired->betweenIncluded($now, $expiringLimit))->count(),
                'expired' => (int) $units->filter(fn ($unit) => $unit->tgl_expired && $unit->tgl_expired->lt($now))->count(),
            ],
        ];
    }

    private function buildReport(): array
    {
        $summary = [
            'berhasil' => collect($this->results)->where('status', self::STATUS_OK)->count(),
            'gagal' => collect($this->results)->where('status', self::STATUS_FAIL)->count(),
            'perlu_diperbaiki' => collect($this->results)->where('status', self::STATUS_WARN)->count(),
        ];

        $report = [
            'meta' => [
                'generated_at' => now('Asia/Jakarta')->toDateTimeString(),
                'started_at' => $this->startedAt,
                'database' => config('database.connections.mysql.database'),
                'timezone' => config('app.timezone'),
                'run_id' => $this->runId,
                'note' => 'Audit live ini tidak mereset database dan tidak menghapus data transaksi uji yang dibuat.',
                'accounts' => [
                    'admin' => $this->accountMeta($this->admin),
                    'teknisi' => $this->accountMeta($this->teknisi),
                    'pelanggan' => [
                        'user' => $this->accountMeta($this->customerUser),
                        'pelanggan_id' => (int) $this->customer->id,
                        'nama' => (string) $this->customer->nama,
                        'no_wa' => (string) $this->customer->no_wa,
                    ],
                ],
                'monitored_entities' => [
                    'produk' => [
                        'id' => (int) $this->product->id,
                        'nama' => (string) $this->product->nama,
                    ],
                    'registered_unit' => [
                        'id' => (int) $this->registeredUnit->id,
                        'no_seri' => (string) ($this->registeredUnit->no_seri ?: ('UNIT-' . $this->registeredUnit->id)),
                    ],
                    'service_paket' => [
                        'id' => (int) $this->servicePaket->id,
                        'nama' => (string) $this->servicePaket->nama,
                    ],
                    'proof_source' => $this->proofSourcePath,
                ],
            ],
            'summary' => $summary,
            'initial_state' => $this->initialState,
            'transactions' => $this->transactions,
            'proof_checks' => $this->proofChecks,
            'created_data' => $this->created,
            'results' => $this->results,
            'final_state' => $this->finalState,
            'deltas' => [
                'stocks' => $this->buildStockDeltas(),
                'units' => $this->buildUnitDeltas(),
                'reports' => $this->buildReportDeltas(),
                'pengeluaran' => $this->buildExpenseChanges(),
            ],
            'conclusion' => $this->buildConclusion($summary),
        ];

        return $report;
    }

    private function buildStockDeltas(): array
    {
        $initialProduct = $this->initialState['stocks']['produk'];
        $finalProduct = $this->finalState['stocks']['produk'];

        $initialRefills = $this->indexById($this->initialState['stocks']['refills']);
        $finalRefills = $this->indexById($this->finalState['stocks']['refills']);
        $initialPeralatan = $this->indexById($this->initialState['stocks']['peralatan']);
        $finalPeralatan = $this->indexById($this->finalState['stocks']['peralatan']);

        return [
            'produk' => [
                'id' => $initialProduct['id'],
                'nama' => $initialProduct['nama'],
                'before' => $initialProduct['stok'],
                'after' => $finalProduct['stok'],
                'delta' => $finalProduct['stok'] - $initialProduct['stok'],
            ],
            'refills' => collect($finalRefills)
                ->map(function (array $item, int $id) use ($initialRefills) {
                    $before = $initialRefills[$id]['stok'] ?? 0;

                    return [
                        'id' => $id,
                        'nama' => $item['nama'],
                        'satuan' => $item['satuan'],
                        'before' => $before,
                        'after' => $item['stok'],
                        'delta' => round((float) $item['stok'] - (float) $before, 2),
                    ];
                })
                ->values()
                ->all(),
            'peralatan' => collect($finalPeralatan)
                ->map(function (array $item, int $id) use ($initialPeralatan) {
                    $before = $initialPeralatan[$id]['stok'] ?? 0;

                    return [
                        'id' => $id,
                        'nama' => $item['nama'],
                        'before' => $before,
                        'after' => $item['stok'],
                        'delta' => (int) $item['stok'] - (int) $before,
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    private function buildUnitDeltas(): array
    {
        $beforeUnits = $this->initialState['units'];
        $afterUnits = $this->finalState['units'];
        $beforeById = $this->indexById($beforeUnits['customer_units']);
        $afterById = $this->indexById($afterUnits['customer_units']);

        $newUnits = collect($afterById)
            ->reject(fn ($value, $id) => array_key_exists($id, $beforeById))
            ->values()
            ->all();

        return [
            'global_total' => [
                'before' => $beforeUnits['global_total'],
                'after' => $afterUnits['global_total'],
                'delta' => $afterUnits['global_total'] - $beforeUnits['global_total'],
            ],
            'customer_total' => [
                'before' => $beforeUnits['customer_total'],
                'after' => $afterUnits['customer_total'],
                'delta' => $afterUnits['customer_total'] - $beforeUnits['customer_total'],
            ],
            'new_customer_units' => $newUnits,
        ];
    }

    private function buildReportDeltas(): array
    {
        return [
            'summary' => $this->numericDeltaMap(
                $this->initialState['reports']['summary'],
                $this->finalState['reports']['summary']
            ),
            'pesanan' => $this->numericDeltaMap(
                $this->initialState['reports']['pesanan'],
                $this->finalState['reports']['pesanan']
            ),
            'service' => $this->numericDeltaMap(
                $this->initialState['reports']['service'],
                $this->finalState['reports']['service']
            ),
            'refill' => $this->numericDeltaMap(
                $this->initialState['reports']['refill'],
                $this->finalState['reports']['refill']
            ),
            'keuangan' => $this->numericDeltaMap(
                $this->initialState['reports']['keuangan'],
                $this->finalState['reports']['keuangan']
            ),
            'apar' => $this->numericDeltaMap(
                $this->initialState['reports']['apar'],
                $this->finalState['reports']['apar']
            ),
        ];
    }

    private function buildExpenseChanges(): array
    {
        $expenses = collect($this->created['pengeluaran'])->filter()->values();

        return [
            'created_count' => (int) $expenses->count(),
            'created_ids' => $expenses->pluck('id')->map(fn ($id) => (int) $id)->all(),
            'by_type' => $expenses
                ->groupBy('jenis_pengeluaran')
                ->map(fn ($group) => [
                    'count' => (int) $group->count(),
                    'total' => (float) $group->sum('total'),
                ])
                ->all(),
        ];
    }

    private function buildConclusion(array $summary): array
    {
        $issues = collect($this->results)
            ->filter(fn ($item) => in_array($item['status'], [self::STATUS_FAIL, self::STATUS_WARN], true))
            ->map(fn ($item) => $item['feature'] . ': ' . $item['actual'])
            ->values()
            ->all();

        $recommendations = [];

        if ($summary['gagal'] === 0 && $summary['perlu_diperbaiki'] === 0) {
            $recommendations[] = 'Flow operasional utama sudah bisa dipakai ulang pada akun existing tanpa menyentuh reset database atau auth flow.';
            $recommendations[] = 'Gunakan file laporan audit ini sebagai baseline sebelum testing regresi berikutnya.';
        } else {
            $recommendations[] = 'Fokuskan perbaikan pada item yang berstatus Gagal atau Perlu diperbaiki sebelum transaksi live berikutnya.';
            $recommendations[] = 'Bandingkan delta stok, unit APAR, dan laporan dengan log transaksi pada report ini untuk melacak titik deviasi.';
        }

        return [
            'status' => $summary['gagal'] > 0 ? 'Perlu Perbaikan' : ($summary['perlu_diperbaiki'] > 0 ? 'Lulus dengan Catatan' : 'Lulus'),
            'issues' => $issues,
            'recommendations' => $recommendations,
        ];
    }

    private function writeReports(array $report): void
    {
        $directory = storage_path('app/qa_reports');
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0777, true);
        }

        $jsonPath = $directory . DIRECTORY_SEPARATOR . 'operational_live_audit_' . $this->runId . '.json';
        $mdPath = $directory . DIRECTORY_SEPARATOR . 'operational_live_audit_' . $this->runId . '.md';

        File::put($jsonPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        File::put($mdPath, $this->markdownReport($report));

        try {
            $this->detailLogPath = app(OperationalAuditDetailLogGenerator::class)
                ->generateFromReport($report, $jsonPath);
        } catch (\Throwable $throwable) {
            $this->detailLogError = $throwable->getMessage();
        }
    }

    private function markdownReport(array $report): string
    {
        $lines = [];
        $lines[] = '# Laporan Audit Operasional Live Sistem APAR';
        $lines[] = '';
        $lines[] = '- Waktu generate: ' . $report['meta']['generated_at'];
        $lines[] = '- Database: `' . $report['meta']['database'] . '`';
        $lines[] = '- Run ID: `' . $report['meta']['run_id'] . '`';
        $lines[] = '- Catatan: ' . $report['meta']['note'];
        $lines[] = '';
        $lines[] = '## Ringkasan Testing';
        $lines[] = '';
        $lines[] = '- Berhasil: ' . $report['summary']['berhasil'];
        $lines[] = '- Gagal: ' . $report['summary']['gagal'];
        $lines[] = '- Perlu diperbaiki: ' . $report['summary']['perlu_diperbaiki'];
        $lines[] = '';
        $lines[] = '## Stock Awal';
        $lines[] = '';
        $lines[] = '- Produk dipantau: `' . json_encode($report['initial_state']['stocks']['produk'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '`';
        $lines[] = '- Refill dipantau: `' . json_encode($report['initial_state']['stocks']['refills'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '`';
        $lines[] = '- Peralatan dipantau: `' . json_encode($report['initial_state']['stocks']['peralatan'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '`';
        $lines[] = '';
        $lines[] = '## Unit APAR & Total Laporan Awal';
        $lines[] = '';
        $lines[] = '- Unit APAR: `' . json_encode($report['initial_state']['units'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '`';
        $lines[] = '- Totals laporan: `' . json_encode($report['initial_state']['reports'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '`';
        $lines[] = '';
        $lines[] = '## Transaksi yang Dilakukan';
        $lines[] = '';
        foreach ($report['transactions'] as $transaction) {
            $lines[] = '- `' . $transaction['flow'] . '`: `' . json_encode($transaction, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '`';
        }
        $lines[] = '';
        $lines[] = '## Bukti Pembayaran';
        $lines[] = '';
        foreach ($report['proof_checks'] as $proof) {
            $lines[] = '- `' . $proof['context'] . '`: `' . json_encode($proof, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '`';
        }
        $lines[] = '';
        $lines[] = '## Delta Stok';
        $lines[] = '';
        $lines[] = '- `' . json_encode($report['deltas']['stocks'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '`';
        $lines[] = '';
        $lines[] = '## Perubahan Unit APAR';
        $lines[] = '';
        $lines[] = '- `' . json_encode($report['deltas']['units'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '`';
        $lines[] = '';
        $lines[] = '## Perubahan Pengeluaran';
        $lines[] = '';
        $lines[] = '- `' . json_encode($report['deltas']['pengeluaran'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '`';
        $lines[] = '';
        $lines[] = '## Perubahan Laporan';
        $lines[] = '';
        $lines[] = '- `' . json_encode($report['deltas']['reports'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '`';
        $lines[] = '';
        $lines[] = '## Detail Hasil Uji';
        $lines[] = '';
        foreach ($report['results'] as $item) {
            $lines[] = '### ' . $item['feature'];
            $lines[] = '';
            $lines[] = '- Role: `' . $item['role'] . '`';
            foreach ($item['steps'] as $step) {
                $lines[] = '- Step: ' . $step;
            }
            $lines[] = '- Expected: ' . $item['expected'];
            $lines[] = '- Actual: ' . $item['actual'];
            $lines[] = '- Status: **' . $item['status'] . '**';
            $lines[] = '';
        }
        $lines[] = '## Kesimpulan';
        $lines[] = '';
        $lines[] = '- Status akhir: `' . $report['conclusion']['status'] . '`';
        foreach ($report['conclusion']['issues'] as $issue) {
            $lines[] = '- Bug/Catatan: ' . $issue;
        }
        foreach ($report['conclusion']['recommendations'] as $recommendation) {
            $lines[] = '- Rekomendasi: ' . $recommendation;
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    private function record(
        string $feature,
        string $role,
        array $steps,
        string $expected,
        string $actual,
        string $status
    ): void {
        $this->results[] = [
            'feature' => $feature,
            'role' => $role,
            'steps' => $steps,
            'expected' => $expected,
            'actual' => $actual,
            'status' => $status,
        ];
    }

    private function accountMeta(User $user): array
    {
        return [
            'user_id' => (int) $user->id,
            'name' => (string) $user->name,
            'no_telpon' => (string) $user->no_telpon,
            'role' => (string) $user->role,
        ];
    }

    private function marker(string $suffix): string
    {
        return 'AUDIT LIVE ' . $this->runId . ' - ' . strtoupper(str_replace('_', ' ', $suffix));
    }

    private function monitoredPeralatanIds(): array
    {
        return array_values(array_unique(array_merge(
            $this->servicePeralatanIds,
            [(int) $this->expensePeralatan->id]
        )));
    }

    private function indexById(array $rows): array
    {
        return collect($rows)->mapWithKeys(function (array $row) {
            return [(int) $row['id'] => $row];
        })->all();
    }

    private function numericDeltaMap(array $before, array $after): array
    {
        $result = [];

        foreach ($after as $key => $afterValue) {
            $beforeValue = $before[$key] ?? 0;

            $result[$key] = [
                'before' => $beforeValue,
                'after' => $afterValue,
                'delta' => is_numeric($afterValue) && is_numeric($beforeValue)
                    ? round((float) $afterValue - (float) $beforeValue, 2)
                    : null,
            ];
        }

        return $result;
    }

    private function hasNewLaravelErrors(): bool
    {
        $path = storage_path('logs/laravel.log');
        if (!File::exists($path)) {
            return false;
        }

        $content = File::get($path);
        $newContent = substr($content, $this->initialLogBytes);

        return is_string($newContent)
            && $newContent !== ''
            && (
                str_contains($newContent, 'local.ERROR')
                || str_contains($newContent, 'ERROR')
                || str_contains($newContent, 'exception')
            );
    }

    private function laravelLogSize(): int
    {
        $path = storage_path('logs/laravel.log');

        return File::exists($path) ? (int) File::size($path) : 0;
    }

    private function responseCode($response): string
    {
        return $response && method_exists($response, 'getStatusCode')
            ? (string) $response->getStatusCode()
            : '-';
    }

    public function getDetailLogPath(): ?string
    {
        return $this->detailLogPath;
    }

    public function getDetailLogError(): ?string
    {
        return $this->detailLogError;
    }
}

$runner = new OperationalLiveAuditRunner('runTest');
$report = $runner->execute();
$jsonPath = storage_path('app/qa_reports/operational_live_audit_' . $report['meta']['run_id'] . '.json');
$mdPath = storage_path('app/qa_reports/operational_live_audit_' . $report['meta']['run_id'] . '.md');

echo 'Operational live audit selesai.' . PHP_EOL;
echo 'JSON: ' . $jsonPath . PHP_EOL;
echo 'Markdown: ' . $mdPath . PHP_EOL;
if ($runner->getDetailLogPath()) {
    echo 'Detail Markdown: ' . $runner->getDetailLogPath() . PHP_EOL;
}
if ($runner->getDetailLogError()) {
    echo 'Detail Markdown Error: ' . $runner->getDetailLogError() . PHP_EOL;
}
echo 'Summary: ' . json_encode($report['summary'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
exit(0);
