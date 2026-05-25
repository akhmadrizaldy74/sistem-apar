<x-app-layout>
    @php
        $formatRupiah = static fn ($amount) => 'Rp ' . number_format((float) $amount, 0, ',', '.');
        $kpiCards = [
            [
                'label' => 'Total Produk',
                'value' => number_format($kpis['totalProduk']),
                'icon' => 'box',
                'accent' => 'rose',
                'hint' => 'Produk aktif dalam katalog.',
            ],
            [
                'label' => 'Total Pelanggan',
                'value' => number_format($kpis['totalPelanggan']),
                'icon' => 'users',
                'accent' => 'navy',
                'hint' => 'Pelanggan yang tersimpan di sistem.',
            ],
            [
                'label' => 'Total Unit APAR',
                'value' => number_format($kpis['totalUnitApar']),
                'icon' => 'shield',
                'accent' => 'slate',
                'hint' => 'Unit yang tercatat dan termonitor.',
            ],
            [
                'label' => 'Pendapatan Bulan Ini',
                'value' => $formatRupiah($kpis['pendapatanBulanIni']),
                'icon' => 'wallet',
                'accent' => 'emerald',
                'hint' => 'Akumulasi penjualan, service, dan refill.',
            ],
            [
                'label' => 'Unit Akan Expired',
                'value' => number_format($kpis['unitAkanExpired']),
                'icon' => 'clock',
                'accent' => 'amber',
                'hint' => 'Perlu follow up dalam 30 hari.',
            ],
            [
                'label' => 'Unit Expired',
                'value' => number_format($kpis['unitExpired']),
                'icon' => 'alert',
                'accent' => 'red',
                'hint' => 'Prioritas penanganan admin dan teknisi.',
            ],
        ];
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-red-500">Admin Dashboard</p>
                <h2 class="mt-2 text-2xl font-bold text-slate-900">Ringkasan utama sistem APAR</h2>
                <p class="mt-1 text-sm text-slate-500">Dashboard difokuskan pada data penting agar admin lebih cepat memantau transaksi, pendapatan, dan kondisi unit.</p>
            </div>
            <div class="rounded-2xl border border-red-100 bg-white px-4 py-3 text-sm text-slate-600 shadow-sm">
                <div class="font-semibold text-slate-800">Mode monitoring</div>
                <div>Ringkasan memakai data dari tabel lama tanpa menambah struktur database baru.</div>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            @foreach ($kpiCards as $card)
                <article class="dashboard-card dashboard-card--{{ $card['accent'] }}">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                            <p class="mt-3 text-2xl font-bold tracking-tight text-slate-900">{{ $card['value'] }}</p>
                        </div>
                        <span class="dashboard-icon">
                            @if ($card['icon'] === 'box')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7.5L12 3 4 7.5m16 0v9L12 21m8-13.5L12 12M4 7.5v9L12 21m0-9L4 7.5m8 4.5v9" />
                                </svg>
                            @elseif ($card['icon'] === 'users')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m18 0v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75M12 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            @elseif ($card['icon'] === 'shield')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3l7 4v5c0 5-3.5 7.5-7 9-3.5-1.5-7-4-7-9V7l7-4z" />
                                </svg>
                            @elseif ($card['icon'] === 'wallet')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16a1 1 0 011 1v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a1 1 0 011-1zm0 0V6a2 2 0 012-2h11" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 13h2" />
                                </svg>
                            @elseif ($card['icon'] === 'clock')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l2.5 2.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                                </svg>
                            @endif
                        </span>
                    </div>
                    <p class="mt-4 text-xs leading-5 text-slate-500">{{ $card['hint'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="grid grid-cols-1 gap-4 xl:grid-cols-12">
            <article class="dashboard-panel xl:col-span-4">
                <div class="dashboard-panel__head">
                    <div>
                        <h3 class="dashboard-panel__title">Komposisi Pendapatan</h3>
                        <p class="dashboard-panel__caption">Sumber pendapatan utama bulan berjalan.</p>
                    </div>
                    <span class="dashboard-badge dashboard-badge--rose">{{ $formatRupiah(array_sum($charts['revenueComposition']['series'])) }}</span>
                </div>
                <div id="revenue-composition-chart" class="dashboard-chart h-72"></div>
            </article>

            <article class="dashboard-panel xl:col-span-4">
                <div class="dashboard-panel__head">
                    <div>
                        <h3 class="dashboard-panel__title">Status Transaksi</h3>
                        <p class="dashboard-panel__caption">Distribusi status pesanan yang aktif di sistem.</p>
                    </div>
                    <span class="dashboard-badge dashboard-badge--navy">{{ number_format(array_sum($charts['transactionStatus']['series'])) }} transaksi</span>
                </div>
                <div id="transaction-status-chart" class="dashboard-chart h-72"></div>
            </article>

            <article class="dashboard-panel xl:col-span-4">
                <div class="dashboard-panel__head">
                    <div>
                        <h3 class="dashboard-panel__title">Status Unit APAR</h3>
                        <p class="dashboard-panel__caption">Pembagian unit aktif, akan expired, dan expired.</p>
                    </div>
                    <span class="dashboard-badge dashboard-badge--amber">{{ number_format(array_sum($charts['unitStatus']['series'])) }} unit</span>
                </div>
                <div id="unit-status-chart" class="dashboard-chart h-72"></div>
            </article>

            <article class="dashboard-panel xl:col-span-12">
                <div class="dashboard-panel__head">
                    <div>
                        <h3 class="dashboard-panel__title">Tren Pendapatan 6 Bulan</h3>
                        <p class="dashboard-panel__caption">Pergerakan pemasukan dari penjualan, service, dan refill dalam enam bulan terakhir.</p>
                    </div>
                    <span class="dashboard-badge dashboard-badge--emerald">6 bulan terakhir</span>
                </div>
                <div id="revenue-trend-chart" class="dashboard-chart h-80"></div>
            </article>
        </section>

        <section class="grid grid-cols-1 gap-4 xl:grid-cols-12">
            <article class="dashboard-panel xl:col-span-3">
                <div class="dashboard-panel__head">
                    <div>
                        <h3 class="dashboard-panel__title">Pesanan Terbaru</h3>
                        <p class="dashboard-panel__caption">Pesanan yang terakhir masuk ke admin.</p>
                    </div>
                    <a href="{{ route('admin.pesanan.index') }}" class="dashboard-link">Lihat semua</a>
                </div>
                <div class="space-y-3">
                    @forelse ($latest['orders'] as $order)
                        <div class="dashboard-list-item">
                            <div>
                                <p class="dashboard-list-title">{{ $order->no_pesanan ?? 'Pesanan #' . $order->id }}</p>
                                <p class="dashboard-list-meta">{{ $order->pelanggan?->nama ?? 'Pelanggan belum tersedia' }}</p>
                            </div>
                            <div class="text-right">
                                <span class="dashboard-status">{{ ucfirst($order->status ?? 'baru') }}</span>
                                <p class="dashboard-list-meta">{{ optional($order->created_at)->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="dashboard-empty">Belum ada pesanan terbaru.</div>
                    @endforelse
                </div>
            </article>

            <article class="dashboard-panel xl:col-span-3">
                <div class="dashboard-panel__head">
                    <div>
                        <h3 class="dashboard-panel__title">Service Terbaru</h3>
                        <p class="dashboard-panel__caption">Aktivitas service yang paling baru dicatat.</p>
                    </div>
                    <a href="{{ route('admin.service.index') }}" class="dashboard-link">Lihat semua</a>
                </div>
                <div class="space-y-3">
                    @forelse ($latest['services'] as $service)
                        <div class="dashboard-list-item">
                            <div>
                                <p class="dashboard-list-title">Service #{{ $service->id }}</p>
                                <p class="dashboard-list-meta">{{ $service->display_customer_name }}</p>
                            </div>
                            <div class="text-right">
                                <span class="dashboard-status">{{ ucfirst($service->status_konfirmasi ?? 'diproses') }}</span>
                                <p class="dashboard-list-meta">{{ $service->tgl_service ? \Carbon\Carbon::parse($service->tgl_service)->translatedFormat('d M Y') : '-' }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="dashboard-empty">Belum ada data service terbaru.</div>
                    @endforelse
                </div>
            </article>

            <article class="dashboard-panel xl:col-span-3">
                <div class="dashboard-panel__head">
                    <div>
                        <h3 class="dashboard-panel__title">Refill Terbaru</h3>
                        <p class="dashboard-panel__caption">Refill yang baru diproses di sistem.</p>
                    </div>
                    <a href="{{ route('admin.refill.index') }}" class="dashboard-link">Lihat semua</a>
                </div>
                <div class="space-y-3">
                    @forelse ($latest['refills'] as $refill)
                        <div class="dashboard-list-item">
                            <div>
                                <p class="dashboard-list-title">Refill #{{ $refill->id }}</p>
                                <p class="dashboard-list-meta">{{ $refill->unitApar?->pelanggan?->nama ?? $refill->service?->display_customer_name ?? 'Pelanggan belum tersedia' }}</p>
                            </div>
                            <div class="text-right">
                                <span class="dashboard-status">{{ $refill->jenisRefill?->nama ?? 'Refill' }}</span>
                                <p class="dashboard-list-meta">{{ $refill->tgl_refill ? \Carbon\Carbon::parse($refill->tgl_refill)->translatedFormat('d M Y') : '-' }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="dashboard-empty">Belum ada data refill terbaru.</div>
                    @endforelse
                </div>
            </article>

            <article class="dashboard-panel xl:col-span-3">
                <div class="dashboard-panel__head">
                    <div>
                        <h3 class="dashboard-panel__title">Pembayaran Terbaru</h3>
                        <p class="dashboard-panel__caption">Bukti transfer dan pembayaran yang baru tercatat.</p>
                    </div>
                    <a href="{{ route('admin.pesanan.index') }}" class="dashboard-link">Lihat pesanan</a>
                </div>
                <div class="space-y-3" id="payment-notification-list">
                    @forelse ($latest['payments'] as $payment)
                        <div class="dashboard-list-item">
                            <div>
                                <p class="dashboard-list-title">{{ $payment->no_pesanan ?? 'Pesanan #' . $payment->id }}</p>
                                <p class="dashboard-list-meta">{{ $payment->pelanggan?->nama ?? 'Pelanggan belum tersedia' }}</p>
                            </div>
                            <div class="text-right">
                                <span class="dashboard-status">{{ $formatRupiah($payment->total_harga ?? $payment->total ?? 0) }}</span>
                                <p class="dashboard-list-meta">{{ optional($payment->pembayaran_terkonfirmasi_at ?? $payment->updated_at)->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="dashboard-empty">Belum ada pembayaran terbaru.</div>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="grid grid-cols-1 gap-4 xl:grid-cols-12">
            <article class="dashboard-panel xl:col-span-4">
                <div class="dashboard-panel__head">
                    <div>
                        <h3 class="dashboard-panel__title">Bukti Transfer Masuk</h3>
                        <p class="dashboard-panel__caption">Transfer produk yang perlu dicek oleh admin.</p>
                    </div>
                    <span class="dashboard-badge dashboard-badge--rose" id="payment-today-count">{{ $notifications['transferProofCount'] }} transfer</span>
                </div>
                <div class="space-y-3">
                    @forelse ($notifications['transferProofs'] as $transfer)
                        <div class="dashboard-list-item">
                            <div>
                                <p class="dashboard-list-title">{{ $transfer->no_pesanan ?? 'Pesanan #' . $transfer->id }}</p>
                                <p class="dashboard-list-meta">{{ $transfer->pelanggan?->nama ?? 'Pelanggan belum tersedia' }}</p>
                            </div>
                            <div class="text-right">
                                <span class="dashboard-status">{{ ucfirst($transfer->status ?? 'menunggu') }}</span>
                                <p class="dashboard-list-meta">{{ optional($transfer->updated_at)->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="dashboard-empty">Belum ada bukti transfer yang perlu dicek.</div>
                    @endforelse
                </div>
            </article>

            <article class="dashboard-panel xl:col-span-4">
                <div class="dashboard-panel__head">
                    <div>
                        <h3 class="dashboard-panel__title">Unit Akan Expired</h3>
                        <p class="dashboard-panel__caption">Daftar unit yang mendekati batas expired.</p>
                    </div>
                    <span class="dashboard-badge dashboard-badge--amber">{{ number_format($kpis['unitAkanExpired']) }} unit</span>
                </div>
                <div class="space-y-3">
                    @forelse ($notifications['expiringUnits'] as $unit)
                        <div class="dashboard-list-item">
                            <div>
                                <p class="dashboard-list-title">{{ $unit->no_seri ?? 'Unit #' . $unit->id }}</p>
                                <p class="dashboard-list-meta">{{ $unit->pelanggan?->nama ?? 'Pelanggan belum tersedia' }}</p>
                            </div>
                            <div class="text-right">
                                <span class="dashboard-status">{{ $unit->produk?->nama ?? 'Produk APAR' }}</span>
                                <p class="dashboard-list-meta">{{ $unit->tgl_expired ? \Carbon\Carbon::parse($unit->tgl_expired)->translatedFormat('d M Y') : '-' }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="dashboard-empty">Belum ada unit yang mendekati expired.</div>
                    @endforelse
                </div>
            </article>

            <article class="dashboard-panel xl:col-span-4">
                <div class="dashboard-panel__head">
                    <div>
                        <h3 class="dashboard-panel__title">Pesanan Perlu Diproses</h3>
                        <p class="dashboard-panel__caption">Antrean pesanan yang masih menunggu tindak lanjut.</p>
                    </div>
                    <span class="dashboard-badge dashboard-badge--navy">{{ number_format($notifications['urgentOrdersCount']) }} antrean</span>
                </div>
                <div class="space-y-3">
                    @forelse ($notifications['urgentOrders'] as $order)
                        <div class="dashboard-list-item">
                            <div>
                                <p class="dashboard-list-title">{{ $order->no_pesanan ?? 'Pesanan #' . $order->id }}</p>
                                <p class="dashboard-list-meta">{{ $order->pelanggan?->nama ?? 'Pelanggan belum tersedia' }}</p>
                            </div>
                            <div class="text-right">
                                <span class="dashboard-status">{{ ucfirst($order->status ?? 'menunggu') }}</span>
                                <p class="dashboard-list-meta">{{ optional($order->created_at)->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="dashboard-empty">Tidak ada pesanan yang menumpuk saat ini.</div>
                    @endforelse
                </div>
            </article>

            <article class="dashboard-panel xl:col-span-12">
                <div class="dashboard-panel__head">
                    <div>
                        <h3 class="dashboard-panel__title">Unit Sudah Expired</h3>
                        <p class="dashboard-panel__caption">Unit yang butuh tindakan segera dari tim admin atau teknisi.</p>
                    </div>
                    <span class="dashboard-badge dashboard-badge--red">{{ number_format($kpis['unitExpired']) }} prioritas</span>
                </div>
                <div class="grid grid-cols-1 gap-3 lg:grid-cols-2 xl:grid-cols-3">
                    @forelse ($notifications['expiredUnits'] as $unit)
                        <div class="dashboard-alert">
                            <div>
                                <p class="dashboard-list-title">{{ $unit->no_seri ?? 'Unit #' . $unit->id }}</p>
                                <p class="dashboard-list-meta">{{ $unit->pelanggan?->nama ?? 'Pelanggan belum tersedia' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-red-700">{{ $unit->produk?->nama ?? 'Produk APAR' }}</p>
                                <p class="dashboard-list-meta">{{ $unit->tgl_expired ? \Carbon\Carbon::parse($unit->tgl_expired)->translatedFormat('d M Y') : '-' }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="dashboard-empty xl:col-span-3">Tidak ada unit expired. Kondisi monitoring sedang aman.</div>
                    @endforelse
                </div>
            </article>
        </section>
    </div>

    @push('styles')
        <style>
            .dashboard-card {
                position: relative;
                overflow: hidden;
                border-radius: 1.5rem;
                border: 1px solid rgba(226, 232, 240, 0.9);
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.98));
                padding: 1.25rem;
                box-shadow: 0 18px 35px -24px rgba(15, 23, 42, 0.45);
            }

            .dashboard-card::after {
                content: '';
                position: absolute;
                inset: auto -36px -44px auto;
                height: 110px;
                width: 110px;
                border-radius: 9999px;
                opacity: 0.12;
            }

            .dashboard-card--rose::after {
                background: #ef4444;
            }

            .dashboard-card--navy::after {
                background: #1d4ed8;
            }

            .dashboard-card--slate::after {
                background: #0f172a;
            }

            .dashboard-card--emerald::after {
                background: #059669;
            }

            .dashboard-card--amber::after {
                background: #f59e0b;
            }

            .dashboard-card--red::after {
                background: #b91c1c;
            }

            .dashboard-icon {
                display: inline-flex;
                height: 2.75rem;
                width: 2.75rem;
                align-items: center;
                justify-content: center;
                border-radius: 0.95rem;
                background: rgba(15, 23, 42, 0.05);
                color: #0f172a;
            }

            .dashboard-panel {
                border-radius: 1.6rem;
                border: 1px solid rgba(226, 232, 240, 0.9);
                background: rgba(255, 255, 255, 0.96);
                padding: 1.25rem;
                box-shadow: 0 18px 35px -26px rgba(15, 23, 42, 0.45);
            }

            .dashboard-panel__head {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 1rem;
                margin-bottom: 1rem;
            }

            .dashboard-panel__title {
                font-size: 1.05rem;
                font-weight: 700;
                color: #0f172a;
            }

            .dashboard-panel__caption {
                margin-top: 0.25rem;
                font-size: 0.875rem;
                color: #64748b;
            }

            .dashboard-badge {
                display: inline-flex;
                align-items: center;
                border-radius: 9999px;
                padding: 0.45rem 0.85rem;
                font-size: 0.75rem;
                font-weight: 700;
                white-space: nowrap;
            }

            .dashboard-badge--rose {
                background: rgba(254, 226, 226, 0.9);
                color: #b91c1c;
            }

            .dashboard-badge--navy {
                background: rgba(219, 234, 254, 0.9);
                color: #1d4ed8;
            }

            .dashboard-badge--amber {
                background: rgba(254, 243, 199, 0.95);
                color: #b45309;
            }

            .dashboard-badge--emerald {
                background: rgba(209, 250, 229, 0.95);
                color: #047857;
            }

            .dashboard-badge--red {
                background: rgba(254, 226, 226, 0.95);
                color: #b91c1c;
            }

            .dashboard-link {
                font-size: 0.875rem;
                font-weight: 600;
                color: #dc2626;
                transition: color 0.2s ease;
            }

            .dashboard-link:hover {
                color: #991b1b;
            }

            .dashboard-list-item,
            .dashboard-alert {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 1rem;
                border-radius: 1rem;
                border: 1px solid rgba(226, 232, 240, 0.8);
                background: #f8fafc;
                padding: 0.9rem 1rem;
            }

            .dashboard-alert {
                background: linear-gradient(180deg, rgba(254, 242, 242, 0.95), rgba(255, 255, 255, 0.95));
                border-color: rgba(248, 113, 113, 0.35);
            }

            .dashboard-list-title {
                font-size: 0.95rem;
                font-weight: 700;
                color: #0f172a;
            }

            .dashboard-list-meta {
                margin-top: 0.2rem;
                font-size: 0.8rem;
                color: #64748b;
            }

            .dashboard-status {
                display: inline-flex;
                align-items: center;
                border-radius: 9999px;
                background: rgba(15, 23, 42, 0.06);
                padding: 0.3rem 0.65rem;
                font-size: 0.75rem;
                font-weight: 700;
                color: #334155;
            }

            .dashboard-empty {
                border-radius: 1rem;
                border: 1px dashed rgba(148, 163, 184, 0.6);
                background: rgba(248, 250, 252, 0.9);
                padding: 1rem;
                text-align: center;
                font-size: 0.875rem;
                color: #64748b;
            }

            .dashboard-chart {
                min-height: 18rem;
            }

            @media (max-width: 768px) {
                .dashboard-panel,
                .dashboard-card {
                    border-radius: 1.25rem;
                }

                .dashboard-panel__head,
                .dashboard-list-item,
                .dashboard-alert {
                    flex-direction: column;
                }
            }
        </style>
    @endpush

    <script type="application/json" id="dashboard-revenue-composition-data">@json($charts['revenueComposition'])</script>
    <script type="application/json" id="dashboard-transaction-status-data">@json($charts['transactionStatus'])</script>
    <script type="application/json" id="dashboard-unit-status-data">@json($charts['unitStatus'])</script>
    <script type="application/json" id="dashboard-revenue-trend-data">@json($charts['revenueTrend'])</script>
    <div id="dashboard-runtime-config" class="hidden" data-payment-notifications-url="{{ route('admin.pesanan.payment-notifications') }}"></div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const parseJsonScript = (id, fallback = {}) => {
                    const element = document.getElementById(id);

                    if (!element) {
                        return fallback;
                    }

                    try {
                        return JSON.parse(element.textContent || '');
                    } catch (error) {
                        console.error(`Gagal membaca data dashboard: ${id}`, error);
                        return fallback;
                    }
                };

                const runtimeConfig = document.getElementById('dashboard-runtime-config');
                const paymentNotificationsUrl = runtimeConfig?.dataset?.paymentNotificationsUrl || '';
                const revenueComposition = parseJsonScript('dashboard-revenue-composition-data');
                const transactionStatus = parseJsonScript('dashboard-transaction-status-data');
                const unitStatus = parseJsonScript('dashboard-unit-status-data');
                const revenueTrend = parseJsonScript('dashboard-revenue-trend-data');

                const palette = {
                    red: '#dc2626',
                    rose: '#ef4444',
                    navy: '#0f172a',
                    blue: '#2563eb',
                    amber: '#f59e0b',
                    emerald: '#059669',
                    soft: '#e2e8f0'
                };

                const rupiah = (value) => new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    maximumFractionDigits: 0
                }).format(value || 0);

                const numberId = (value) => new Intl.NumberFormat('id-ID').format(value || 0);

                const makeDonutOptions = (config) => {
                    const hasData = (config.series || []).some(value => Number(value) > 0);

                    return {
                        chart: {
                            type: 'donut',
                            height: 300,
                            toolbar: { show: false },
                            animations: {
                                enabled: true,
                                easing: 'easeinout',
                                speed: 850
                            }
                        },
                        series: hasData ? config.series : [1],
                        labels: hasData ? config.labels : ['Belum Ada Data'],
                        colors: hasData ? config.colors : [palette.soft],
                        stroke: { width: 0 },
                        dataLabels: { enabled: false },
                        legend: {
                            position: 'bottom',
                            fontSize: '13px',
                            labels: { colors: '#475569' }
                        },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '70%',
                                    labels: {
                                        show: true,
                                        name: {
                                            show: true,
                                            color: '#64748b'
                                        },
                                        value: {
                                            show: true,
                                            color: '#0f172a',
                                            fontSize: '22px',
                                            fontWeight: 700,
                                            formatter: (value) => config.valueFormatter ? config.valueFormatter(value) : value
                                        },
                                        total: {
                                            show: true,
                                            label: config.totalLabel,
                                            color: '#64748b',
                                            formatter: () => config.totalFormatter(config.series || [])
                                        }
                                    }
                                }
                            }
                        },
                        tooltip: {
                            y: {
                                formatter: config.tooltipFormatter
                            }
                        }
                    };
                };

                new ApexCharts(document.querySelector('#revenue-composition-chart'), makeDonutOptions({
                    labels: revenueComposition.labels,
                    series: revenueComposition.series,
                    colors: [palette.red, palette.navy, palette.amber],
                    totalLabel: 'Total',
                    totalFormatter: (series) => rupiah(series.reduce((sum, value) => sum + Number(value || 0), 0)),
                    valueFormatter: (value) => rupiah(value),
                    tooltipFormatter: (value) => rupiah(value)
                })).render();

                new ApexCharts(document.querySelector('#transaction-status-chart'), makeDonutOptions({
                    labels: transactionStatus.labels,
                    series: transactionStatus.series,
                    colors: [palette.amber, palette.blue, palette.emerald, palette.red],
                    totalLabel: 'Transaksi',
                    totalFormatter: (series) => numberId(series.reduce((sum, value) => sum + Number(value || 0), 0)),
                    valueFormatter: (value) => numberId(value),
                    tooltipFormatter: (value) => `${numberId(value)} transaksi`
                })).render();

                new ApexCharts(document.querySelector('#unit-status-chart'), makeDonutOptions({
                    labels: unitStatus.labels,
                    series: unitStatus.series,
                    colors: [palette.emerald, palette.amber, palette.red],
                    totalLabel: 'Unit',
                    totalFormatter: (series) => numberId(series.reduce((sum, value) => sum + Number(value || 0), 0)),
                    valueFormatter: (value) => numberId(value),
                    tooltipFormatter: (value) => `${numberId(value)} unit`
                })).render();

                new ApexCharts(document.querySelector('#revenue-trend-chart'), {
                    chart: {
                        type: 'line',
                        height: 340,
                        toolbar: { show: false },
                        zoom: { enabled: false },
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 900
                        }
                    },
                    series: revenueTrend.series,
                    colors: [palette.red, palette.navy, '#2563eb', palette.amber],
                    stroke: {
                        width: [4, 2.5, 2.5, 2.5],
                        curve: 'smooth'
                    },
                    markers: {
                        size: 4,
                        strokeWidth: 0,
                        hover: { size: 6 }
                    },
                    grid: {
                        borderColor: '#e2e8f0',
                        strokeDashArray: 4
                    },
                    xaxis: {
                        categories: revenueTrend.labels,
                        labels: {
                            style: { colors: '#64748b' }
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: (value) => rupiah(value),
                            style: { colors: '#64748b' }
                        }
                    },
                    fill: {
                        type: ['gradient', 'solid', 'solid', 'solid'],
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.25,
                            opacityTo: 0.02,
                            stops: [0, 90, 100]
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'left',
                        labels: { colors: '#475569' }
                    },
                    tooltip: {
                        shared: true,
                        intersect: false,
                        y: {
                            formatter: (value) => rupiah(value)
                        }
                    }
                }).render();

                const paymentList = document.getElementById('payment-notification-list');
                const paymentTodayCount = document.getElementById('payment-today-count');
                let lastPaymentSignature = null;

                const renderPayments = (orders) => {
                    if (!paymentList) {
                        return;
                    }

                    if (!orders.length) {
                        paymentList.innerHTML = '<div class="dashboard-empty">Belum ada pembayaran terbaru.</div>';
                        return;
                    }

                    paymentList.innerHTML = orders.map(order => `
                        <div class="dashboard-list-item">
                            <div>
                                <p class="dashboard-list-title">${order.kode || ('Pesanan #' + order.id)}</p>
                                <p class="dashboard-list-meta">${order.pelanggan || 'Pelanggan belum tersedia'}</p>
                            </div>
                            <div class="text-right">
                                <span class="dashboard-status">${rupiah(order.total || 0)}</span>
                                <p class="dashboard-list-meta">${order.updated_at ? new Date(order.updated_at).toLocaleString('id-ID') : '-'}</p>
                            </div>
                        </div>
                    `).join('');
                };

                const pollPayments = async () => {
                    if (!paymentNotificationsUrl) {
                        return;
                    }

                    try {
                        const response = await fetch(paymentNotificationsUrl, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });

                        if (!response.ok) {
                            return;
                        }

                        const data = await response.json();
                        const orders = Array.isArray(data.orders) ? data.orders : [];
                        const signature = JSON.stringify(orders.map(item => [item.id, item.updated_at]));

                        if (signature !== lastPaymentSignature) {
                            renderPayments(orders);
                            lastPaymentSignature = signature;
                        }

                        if (paymentTodayCount && typeof data.paid_today !== 'undefined') {
                            paymentTodayCount.textContent = `${numberId(data.paid_today)} masuk hari ini`;
                        }
                    } catch (error) {
                        console.error('Gagal memuat notifikasi pembayaran.', error);
                    }
                };

                pollPayments();
                setInterval(pollPayments, 30000);
            });
        </script>
    @endpush
</x-app-layout>
