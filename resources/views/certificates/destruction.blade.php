<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat Pemusnahan - {{ $certificate->certificate_number }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 40px auto; color: #1e293b; }
        h1 { text-align: center; font-size: 22px; margin-bottom: 8px; }
        .subtitle { text-align: center; color: #64748b; margin-bottom: 32px; }
        table { width: 100%; border-collapse: collapse; margin: 24px 0; }
        td { padding: 10px 0; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        td:first-child { width: 200px; font-weight: bold; color: #475569; }
        .footer { margin-top: 48px; display: flex; justify-content: space-between; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()" style="margin-bottom:20px;padding:10px 16px;">Cetak Sertifikat</button>

    <h1>SERTIFIKAT PEMUSNAHAN REKAM MEDIS</h1>
    <p class="subtitle">Klinik Kolbu — {{ config('app.name') }}</p>

    <table>
        <tr><td>Nomor Sertifikat</td><td>{{ $certificate->certificate_number }}</td></tr>
        <tr><td>Tanggal Pemusnahan</td><td>{{ $certificate->destroyed_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td>No. Rekam Medis</td><td>{{ $certificate->berkas->no_rm }}</td></tr>
        <tr><td>Nama Pasien</td><td>{{ $certificate->berkas->nama_pasien }}</td></tr>
        <tr><td>Disetujui Oleh</td><td>{{ $certificate->approver->nama_lengkap }}</td></tr>
        <tr><td>Catatan</td><td>{{ $certificate->catatan ?? '-' }}</td></tr>
    </table>

    <div class="footer">
        <div>
            <p>Petugas Arsip</p>
            <br><br>
            <p>_________________________</p>
        </div>
        <div>
            <p>Administrator</p>
            <br><br>
            <p>{{ $certificate->approver->nama_lengkap }}</p>
        </div>
    </div>
</body>
</html>
