@forelse($pesananAktif as $pesanan)
    @include('admin.pesanan.partials.row', ['pesanan' => $pesanan])
@empty
    <tr>
        <td colspan="7" class="px-8 py-12 text-center text-sm font-semibold text-gray-500">Belum ada pesanan aktif yang perlu diproses admin.</td>
    </tr>
@endforelse
