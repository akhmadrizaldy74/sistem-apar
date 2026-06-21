<div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
    <a href="{{ route('admin.testimoni.index') }}" class="p-4 rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-md transition {{ $currentStatus === '' && !request()->filled('replied') ? 'ring-2 ring-blue-500' : '' }}">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Testimoni</p>
        <p class="mt-2 text-3xl font-black text-gray-900">{{ $counts['total'] }}</p>
    </a>
    <a href="{{ route('admin.testimoni.index', ['replied' => 'yes']) }}" class="p-4 rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-md transition {{ request('replied') === 'yes' ? 'ring-2 ring-emerald-500' : '' }}">
        <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Sudah Dibalas</p>
        <p class="mt-2 text-3xl font-black text-gray-900">{{ $counts['replied'] }}</p>
    </a>
    <a href="{{ route('admin.testimoni.index', ['replied' => 'no']) }}" class="p-4 rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-md transition {{ request('replied') === 'no' ? 'ring-2 ring-amber-500' : '' }}">
        <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest">Belum Dibalas</p>
        <p class="mt-2 text-3xl font-black text-gray-900">{{ $counts['unreplied'] }}</p>
    </a>
</div>
