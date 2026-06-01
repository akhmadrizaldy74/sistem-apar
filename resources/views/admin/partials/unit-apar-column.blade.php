@php
    $pesanan = $pesanan ?? null;
    $unitApar = $unitApar ?? null;
    
    $isOffline = $pesanan 
        ? in_array((string) $pesanan->sumber_pesanan, ['datang_langsung', 'offline', 'input_admin'], true)
        : true;
        
    $isAutoCreatedUnit = $unitApar && str_contains($unitApar->catatan_unit ?? '', 'Unit dibuat otomatis dari');
    
    $unitsToDisplay = [];
    
    if ($unitApar && !$isAutoCreatedUnit) {
        $unitsToDisplay[] = [
            'nama' => $unitApar->produk?->nama ?? 'APAR',
            'no_seri' => $unitApar->no_seri ?? 'Tanpa Seri',
        ];
    } elseif ($pesanan && !$isOffline && str_contains($pesanan->keterangan ?? '', 'Status Unit: APAR Terdaftar')) {
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $pesanan->service_keluhan ?? ''));
        foreach ($lines as $line) {
            $line = trim($line);
            // Match both Refill format (Masa berlaku) and Service format (Rp or x unit)
            if (preg_match('/^\d+\.\s*(.+?)(?:\s+-\s+Masa berlaku:|\s+-\s+Rp|\s+x\s+\d+\s+unit|$)/i', $line, $matches)) {
                $label = trim($matches[1]);
                $parts = explode(' - ', $label);
                $noSeri = trim($parts[0] ?? 'Tanpa Seri');
                $namaProduk = trim($parts[1] ?? 'APAR');
                // The label format in PublicController is: no_seri - produk_nama - jenis - ukuran
                // So index 0 is no_seri, index 1 is produk_nama
                $unitsToDisplay[] = [
                    'nama' => $namaProduk,
                    'no_seri' => $noSeri,
                ];
            }
        }
    }
@endphp

<div class="flex items-center gap-2 flex-wrap mb-2">
    <span class="inline-flex px-3 py-1 rounded-full {{ $isOffline ? 'bg-slate-900 text-white' : 'bg-white border border-slate-200 text-slate-600' }} text-[10px] font-black uppercase tracking-widest">{{ $isOffline ? 'Offline' : 'Online' }}</span>
</div>

@if(count($unitsToDisplay) > 0)
    <p class="mt-2 text-sm font-black text-gray-900">Unit APAR Terdaftar</p>
    @foreach($unitsToDisplay as $u)
        <div class="mt-1">
            <p class="text-xs font-semibold text-gray-500">{{ $u['nama'] }}</p>
            <p class="text-[10px] font-semibold text-gray-400">{{ $u['no_seri'] }}</p>
        </div>
    @endforeach
@else
    <p class="mt-2 text-sm font-semibold text-gray-400">-</p>
@endif
