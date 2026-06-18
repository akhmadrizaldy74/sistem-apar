<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-3xl font-black tracking-tight text-slate-900">Edit Jenis Service</h2>
            <p class="mt-1 text-sm font-semibold text-slate-500">Perbarui harga, rincian, dan peralatan jenis service.</p>
        </div>
    </x-slot>

    <form action="{{ route('admin.service-paket.update', $servicePaket) }}" method="POST">
        @php($isEdit = true)
        @include('admin.service-paket._form')
    </form>
</x-app-layout>
