<?php
include 'koneksi.php';

// 1. Validasi Input
if (!isset($_POST['selected_id']) || empty($_POST['selected_id'])) {
    echo "<script>alert('Pilih data terlebih dahulu!'); window.location='pemesanan.php';</script>";
    exit;
}

// 2. Tangkap Data Form
$selected_ids = $_POST['selected_id'];
$new_sub_wo   = mysqli_real_escape_string($conn, $_POST['new_sub_wo']);
$new_no_wo    = mysqli_real_escape_string($conn, $_POST['new_no_wo']);
$full_wo_baru = $new_sub_wo . " / " . $new_no_wo;
$ids_string   = implode(',', array_map('intval', $selected_ids));

// 3. Logika Update: Sinkronisasi WO baru ke Database
$update_sql = "UPDATE pemesanan SET 
               sub_wo = '$new_sub_wo', 
               no_wo = '$new_no_wo' 
               WHERE id_pemesanan IN ($ids_string)";

if (!mysqli_query($conn, $update_sql)) {
    die("Gagal memperbarui database: " . mysqli_error($conn));
}

// 4. Query Data Final untuk Excel
$sql = "SELECT p.*, k.nomor_kontrak, v.nama_vendor, t.jenis_tiang, u.kecamatan
        FROM pemesanan p
        JOIN kontrak k ON p.id_kontrak = k.id_kontrak
        JOIN vendor v ON k.id_vendor = v.id_vendor
        JOIN tiang t ON k.id_tiang = t.id_tiang
        JOIN ulp u ON p.id_ulp = u.id_ulp
        WHERE p.id_pemesanan IN ($ids_string)
        ORDER BY p.tgl_wo ASC";

$res = mysqli_query($conn, $sql);
if (!$res) die("Kesalahan Query SQL: " . mysqli_error($conn));

// 5. Header Download Excel
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Export_WO_" . date('Ymd') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Proses data ke dalam array agar bisa digunakan berulang (Tabel 1, Tabel 2, dan Footer)
$data_array = [];
$total_volume = 0;
$nama_barang = "";

while ($row = mysqli_fetch_assoc($res)) {
    $data_array[] = $row;
    $total_volume += $row['kebutuhan'];
    $nama_barang = $row['jenis_tiang'];
}
?>

<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-type" content="text/html;charset=utf-8" />
    <style>
        .text-top { vertical-align: top; }
        @page { mso-page-orientation: landscape; }
    </style>
</head>
<body>

<table>
    <tr>
        <td colspan="8">Lampiran Surat No. <?php echo $full_wo_baru; ?> Tanggal <?php echo date('d F Y'); ?></td>
    </tr>
    <tr><td colspan="8"></td></tr>
</table>

<table border="1">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th>No.</th>
            <th colspan="2">Nama Barang</th>
            <th>Sat.</th>
            <th>Volume</th>
            <th>Harga Satuan</th>
            <th>Jumlah</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td align="center">1</td>
            <td colspan="2"><?php echo $nama_barang; ?></td>
            <td align="center">Btg</td>
            <td align="center"><?php echo $total_volume; ?></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="3" align="right"><b>JUMLAH</b></td>
            <td></td><td></td><td></td><td></td>
        </tr>
        <tr>
            <td colspan="3" align="right"><b>PPN 11%</b></td>
            <td></td><td></td><td></td><td></td>
        </tr>
        <tr style="background-color: #ffff00;">
            <td colspan="3" align="right"><b>TOTAL = JUMLAH + PPN</b></td>
            <td></td><td></td><td></td><td></td>
        </tr>
    </tbody>
</table>

<br>

<table>
    <tr>
        <td colspan="8" style="font-weight: bold;">Lokasi Pemasangan <?php echo $nama_barang; ?></td>
    </tr>
</table>
<table border="1">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th>No.</th>
            <th>Nama</th>
            <th>Alamat</th>
            <th>Sat</th>
            <th>Vol</th>
            <th>SLA</th>
            <th>ULP</th>
            <th>PENGAWAS</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1; foreach ($data_array as $item): ?>
            <tr>
                <td align="center"><?php echo $no++; ?></td>
                <td><?php echo $item['nama_pelanggan']; ?></td>
                <td><?php echo $item['lokasi']; ?></td>
                <td align="center">Btg</td>
                <td align="center"><?php echo $item['kebutuhan']; ?></td>
                <td align="center">3 Hari</td>
                <td><?php echo $item['kecamatan']; ?></td>
                <td></td>
            </tr>
        <?php endforeach; ?>
        <tr style="font-weight: bold;">
            <td colspan="4" align="right">Jumlah</td>
            <td align="center"><?php echo $total_volume; ?></td>
            <td colspan="3"></td>
        </tr>
    </tbody>
</table>

<table>
    <tr><td colspan="8"></td></tr>
    <tr><td colspan="8"></td></tr>
    <tr><td colspan="8"></td></tr>
    
    <tr>
        <td></td> <td colspan="3" class="text-top" style="font-weight: bold;">Catatan:</td> <td colspan="3"></td> <td align="center" class="text-top">Sidoarjo, <?php echo date('d F Y'); ?></td> </tr>
    <tr>
        <td></td>
        <td colspan="6" class="text-top">- SLA mulai diperhitungkan pada saat tanggal email work order diterima</td>
        <td align="center" class="text-top">Asman Perencanaan</td>
    </tr>
    <tr>
        <td></td>
        <td colspan="6" class="text-top">- Sesuai Kontrak Rinci No. <?php echo $data_array[0]['nomor_kontrak'] . " " . $data_array[0]['nama_vendor']; ?></td>
        <td></td>
    </tr>
    
    <tr><td colspan="8"></td></tr>
    <tr><td colspan="8"></td></tr>
    
    <tr>
        <td colspan="7"></td>
        <td align="center"><b>( ......................................... )</b></td>
    </tr>
</table>

</body>
</html>