<?php
include 'koneksi.php';

$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

// Ambil Data Master Kontrak
$q_master = mysqli_query($conn, "SELECT k.kuota, v.nama_vendor, t.jenis_tiang 
                                 FROM kontrak k 
                                 JOIN vendor v ON k.id_vendor = v.id_vendor 
                                 JOIN tiang t ON k.id_tiang = t.id_tiang 
                                 WHERE k.id_kontrak = '$id'");
$master = mysqli_fetch_assoc($q_master);

// Ambil Histori Pemesanan
$q_histori = mysqli_query($conn, "SELECT * FROM pemesanan WHERE id_kontrak = '$id' ORDER BY id_pemesanan ASC");

echo "<table class='table-detail'>
        <thead>
            <tr>
                <th>VENDOR</th>
                <th>JENIS TIANG</th>
                <th>AWAL</th>
                <th>BUTUH</th>
                <th>SISA</th>
                <th>KET</th>
                <th>PELANGGAN</th>
                <th>LOKASI</th>
            </tr>
        </thead>
        <tbody>";

$saldo = $master['kuota'];
$first = true;

if (mysqli_num_rows($q_histori) > 0) {
    while ($row = mysqli_fetch_assoc($q_histori)) {
        $saldo -= $row['kebutuhan'];
        echo "<tr>
                <td>" . ($first ? $master['nama_vendor'] : "") . "</td>
                <td>" . ($first ? $master['jenis_tiang'] : "") . "</td>
                <td align='center'>" . ($first ? $master['kuota'] : "") . "</td>
                <td align='center' class='text-red'>-" . $row['kebutuhan'] . "</td>
                <td align='center' class='".($saldo < 0 ? 'text-red' : 'text-green')."'>" . $saldo . "</td>
                <td>" . $row['ket_kuota'] . "</td>
                <td>" . $row['nama_pelanggan'] . "</td>
                <td>" . $row['lokasi'] . "</td>
              </tr>";
        $first = false;
    }
} else {
    echo "<tr><td colspan='8' align='center' style='padding:20px; color:#999;'>Belum ada data pemakaian kuota.</td></tr>";
}
echo "</tbody></table>";
?>