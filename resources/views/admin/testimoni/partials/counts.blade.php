<div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
    <a href="{{ route('admin.testimoni.index') }}" class="p-4 rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-md transition {{ $currentStatus === '' ? 'ring-2 ring-red-500' : '' }}">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total</p>
        <p class="mt-2 text-3xl font-black text-gray-900">{{ $counts['total'] }}</p>
    </a>
    <a href="{{ route('admin.testimoni.index', ['status' => 'pending']) }}" class="p-4 rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-md transition {{ $currentStatus === 'pending' ? 'ring-2 ring-amber-500' : '' }}">
        <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest">Menunggu</p>
        <p class="mt-2 text-3xl font-black text-gray-900">{{ $counts['pending'] }}</p>
    </a>
    <a href="{{ route('admin.testimoni.index', ['status' => 'approved']) }}" class="p-4 rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-md transition {{ $currentStatus === 'approved' ? 'ring-2 ring-emerald-500' : '' }}">
        <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Disetujui</p>
        <p class="mt-2 text-3xl font-black text-gray-900">{{ $counts['approved'] }}</p>
    </a>
    <a href="{{ route('admin.testimoni.index', ['status' => 'rejected']) }}" class="p-4 rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-md transition {{ $currentStatus === 'rejected' ? 'ring-2 ring-red-500' : '' }}">
        <p class="text-[10px] font-black text-red-500 uppercase tracking-widest">Ditolak</p>
        <p class="mt-2 text-3xl font-black text-gray-900">{{ $counts['rejected'] }}</p>
    </a>
</div>
