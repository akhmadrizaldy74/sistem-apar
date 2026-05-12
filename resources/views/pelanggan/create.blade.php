<h1>Tambah Pelanggan</h1>

<form action="/pelanggan" method="POST">
    @csrf
    <input type="text" name="nama" placeholder="Nama"><br>
    <input type="text" name="no_wa" placeholder="No WA"><br>
    <textarea name="alamat" placeholder="Alamat"></textarea><br>
    <button type="submit">Simpan</button>
</form>