<h1>Data Pelanggan</h1>

<a href="/pelanggan/create">Tambah</a>

@foreach($data as $p)
    <p>{{ $p->nama }} - {{ $p->no_wa }}</p>
@endforeach