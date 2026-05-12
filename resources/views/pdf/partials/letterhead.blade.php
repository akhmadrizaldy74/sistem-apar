@php
    $logoPath = public_path('images/kop-surat/logo.png');
    $logoSrc = null;
    if (is_file($logoPath)) {
        $logoSrc = 'data:image/png;base64,'.base64_encode((string) file_get_contents($logoPath));
    }
@endphp
<header class="pdf-letterhead">
    <table class="pdf-letterhead-inner" cellpadding="0" cellspacing="0">
        <tr>
            <td class="pdf-letterhead-logo-cell">
                @if ($logoSrc)
                    <img src="{{ $logoSrc }}" alt="" class="pdf-letterhead-logo" />
                @else
                    <div class="pdf-letterhead-logo-fallback">PD</div>
                @endif
            </td>
            <td class="pdf-letterhead-text-cell">
                <div class="pdf-company-name">PD. ANUGRAH UTAMA</div>
                <div class="pdf-company-tagline">Menjual, Mengisi &amp; Service Segala Macam Alat Pemadam Api</div>
                <div class="pdf-company-address">Perum Alam Lestari Cibinong — Bogor</div>
                <div class="pdf-company-contact">Telp: 0821 2471 6109 | Email: wiyana80@gmail.com</div>
            </td>
        </tr>
    </table>
    <div class="pdf-letterhead-rule"></div>
</header>
