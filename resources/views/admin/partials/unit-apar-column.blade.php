@php
    use App\Models\Pesanan;
    use App\Support\ServiceUnitDisplay;

    $pesanan = $pesanan ?? null;
    $unitApar = $unitApar ?? null;

    $display = $pesanan instanceof Pesanan
        ? $pesanan->serviceUnitDisplay()
        : ($unitApar ? ServiceUnitDisplay::forUnitApar($unitApar) : ServiceUnitDisplay::empty());

    $sumberPesanan = (string) ($pesanan?->sumber_pesanan ?? 'offline');
    $isOffline = in_array($sumberPesanan, ['datang_langsung', 'offline', 'input_admin'], true);
@endphp

<div class="flex items-center gap-2 flex-wrap mb-2">
    <span class="inline-flex px-3 py-1 rounded-full {{ $isOffline ? 'bg-slate-900 text-white' : 'bg-white border border-slate-200 text-slate-600' }} text-[10px] font-black uppercase tracking-widest">
        {{ $isOffline ? 'Offline' : 'Online' }}
    </span>
</div>

@if(!empty($display['entries']))
    <p class="mt-2 text-sm font-black text-gray-900">{{ $display['heading'] }}</p>
    @foreach($display['entries'] as $entry)
        <div class="mt-1">
            <p class="text-xs font-semibold text-gray-600">{{ $entry['label'] }}</p>
            @if(!empty($entry['code']) && ($display['is_registered'] ?? false))
                <p class="text-[10px] font-semibold text-gray-400">{{ $entry['code'] }}</p>
            @endif
        </div>
    @endforeach
    @if(!empty($display['quantity_label']) && (int) ($display['quantity'] ?? 0) > 1)
        <p class="mt-2 text-[11px] font-black uppercase tracking-wide text-gray-500">{{ $display['quantity_label'] }}</p>
    @endif
@elseif(!empty($display['detail_label']))
    <p class="mt-2 text-sm font-black text-gray-900">{{ $display['heading'] }}</p>
    <p class="mt-1 text-xs font-semibold text-gray-600">{{ $display['detail_label'] }}</p>
    @if(!empty($display['quantity_label']))
        <p class="mt-2 text-[11px] font-black uppercase tracking-wide text-gray-500">{{ $display['quantity_label'] }}</p>
    @endif
@else
    <p class="mt-2 text-sm font-semibold text-gray-400">Data unit APAR belum tersedia.</p>
@endif
