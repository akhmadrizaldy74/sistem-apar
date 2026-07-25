<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-3xl font-black tracking-tight text-slate-900">Tambah Jasa / Service</h2>
            <p class="mt-1 text-sm font-semibold text-slate-500">Buat layanan jasa atau jenis service baru beserta harga dan peralatan pendukungnya (opsional).</p>
        </div>
    </x-slot>

    <form action="{{ route('admin.service-paket.store') }}" method="POST">
        @php($isEdit = false)
        @include('admin.service-paket._form')
    </form>
</x-app-layout>
