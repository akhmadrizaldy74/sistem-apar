@forelse($pesananRiwayat as $pesanan)
    @include('admin.pesanan.partials.row', ['pesanan' => $pesanan, 'listType' => 'history'])
@empty
    <tr>
        <td colspan="7" class="px-8 py-12 text-center text-sm font-semibold text-gray-500">Belum ada riwayat pesanan yang selesai atau ditutup.</td>
    </tr>
@endforelse
