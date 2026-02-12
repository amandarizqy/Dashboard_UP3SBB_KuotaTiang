<?php
include 'koneksi.php';

// Get filters dari GET parameter - Handle multiple selections
$filter_vendors = isset($_GET['filter_vendor']) ? $_GET['filter_vendor'] : [];
$filter_tiangs = isset($_GET['filter_tiang']) ? $_GET['filter_tiang'] : [];
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';

// Pastikan array
if (!is_array($filter_vendors)) {
    $filter_vendors = !empty($filter_vendors) ? [$filter_vendors] : [];
}
if (!is_array($filter_tiangs)) {
    $filter_tiangs = !empty($filter_tiangs) ? [$filter_tiangs] : [];
}

// Build WHERE clause
$where_clauses = [];
if (!empty($filter_vendors)) {
    $vendor_list = "'" . implode("','", array_map(function($v) use ($conn) { return mysqli_real_escape_string($conn, $v); }, $filter_vendors)) . "'";
    $where_clauses[] = "k.id_vendor IN ($vendor_list)";
}
if (!empty($filter_tiangs)) {
    $tiang_list = "'" . implode("','", array_map(function($t) use ($conn) { return mysqli_real_escape_string($conn, $t); }, $filter_tiangs)) . "'";
    $where_clauses[] = "k.id_tiang IN ($tiang_list)";
}
if (!empty($filter_status)) {
    $fs = mysqli_real_escape_string($conn, $filter_status);
    if ($fs === 'non-aktif') $fs = 'nonaktif';
    $where_clauses[] = "k.status = '$fs'";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = " WHERE " . implode(" AND ", $where_clauses);
}

// Set header untuk Excel
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=REKAP_MONITORING_" . date('Y-m-d') . ".xls");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Pragma: public");

// Buat output HTML Table untuk Excel
$output = '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse;">' . "\n";

// Header baris
$headers = ['No', 'Nama PT', 'Ukuran Tiang', 'Kuota Awal', 'Kebutuhan', 'Sisa Kuota', 'Ket Kuota', 'Nama Pelanggan', 'Lokasi', 'No SPB', 'Tgl Terbit SPB', 'Tgl Akhir SPB', 'No WO (Full)', 'Tgl WO', 'Kecamatan'];

$output .= '<tr style="background-color: #00A3E0; font-weight: bold; text-align: center; color: #FFFFFF;">' . "\n";
foreach ($headers as $header) {
    $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px;">' . htmlspecialchars($header) . '</td>' . "\n";
}
$output .= '</tr>' . "\n";

// Query data kontrak
$q_kontrak = mysqli_query($conn, "SELECT k.*, v.nama_vendor, t.jenis_tiang 
                                  FROM kontrak k 
                                  JOIN vendor v ON k.id_vendor = v.id_vendor 
                                  JOIN tiang t ON k.id_tiang = t.id_tiang 
                                  $where_sql
                                  ORDER BY k.id_kontrak DESC");

$no = 1;

while($k = mysqli_fetch_assoc($q_kontrak)) {
    $id_k = $k['id_kontrak'];
    $kuota_berjalan = $k['kuota'];
    
    // URUTKAN BERDASARKAN TANGGAL WO TERDAHULU (ASC)
    $q_pesan = mysqli_query($conn, "SELECT p.*, u.kecamatan 
                                    FROM pemesanan p 
                                    JOIN ulp u ON p.id_ulp = u.id_ulp 
                                    WHERE p.id_kontrak = '$id_k' 
                                    ORDER BY p.tgl_wo ASC, p.id_pemesanan ASC");
    
    if(mysqli_num_rows($q_pesan) > 0) {
        $first = true;
        while($p = mysqli_fetch_assoc($q_pesan)) {
            $kuota_berjalan -= $p['kebutuhan'];
            
            // Gabungkan Sub WO dan Master WO
            $full_wo = $p['sub_wo'] . "/" . $p['no_wo'];
            
            $output .= '<tr>' . "\n";
            $output .= '<td style="border: 1px solid #DEE2E6; text-align: center;">' . $no++ . '</td>' . "\n";
            $output .= '<td style="border: 1px solid #DEE2E6;">' . htmlspecialchars($k['nama_vendor']) . '</td>' . "\n";
            $output .= '<td style="border: 1px solid #DEE2E6;">' . htmlspecialchars($k['jenis_tiang']) . '</td>' . "\n";
            $output .= '<td style="border: 1px solid #DEE2E6; text-align: center; font-weight: bold;">' . (($first) ? $k['kuota'] : '-') . '</td>' . "\n";
            $output .= '<td style="border: 1px solid #DEE2E6; text-align: center;">' . $p['kebutuhan'] . '</td>' . "\n";
            
            // Sisa Kuota dengan warna (Merah jika minus)
            $color = ($kuota_berjalan < 0) ? 'color: #ff0000;' : '';
            $output .= '<td style="border: 1px solid #DEE2E6; text-align: center; font-weight: bold; '.$color.'">' . $kuota_berjalan . '</td>' . "\n";
            
            $output .= '<td style="border: 1px solid #DEE2E6;">' . htmlspecialchars($p['ket_kuota']) . '</td>' . "\n";
            $output .= '<td style="border: 1px solid #DEE2E6;">' . htmlspecialchars($p['nama_pelanggan']) . '</td>' . "\n";
            $output .= '<td style="border: 1px solid #DEE2E6;">' . htmlspecialchars($p['lokasi']) . '</td>' . "\n";
            $output .= '<td style="border: 1px solid #DEE2E6;">' . htmlspecialchars($k['nomor_kontrak']) . '</td>' . "\n";
            $output .= '<td style="border: 1px solid #DEE2E6; text-align: center;">' . date('d/m/Y', strtotime($k['tanggal_terbit'])) . '</td>' . "\n";
            $output .= '<td style="border: 1px solid #DEE2E6; text-align: center;">' . date('d/m/Y', strtotime($k['akhir_tenggat'])) . '</td>' . "\n";
            
            // Tampilan WO hasil Concat
            $output .= '<td style="border: 1px solid #DEE2E6;">' . htmlspecialchars($full_wo) . '</td>' . "\n";
            
            $output .= '<td style="border: 1px solid #DEE2E6; text-align: center;">' . date('d/m/Y', strtotime($p['tgl_wo'])) . '</td>' . "\n";
            $output .= '<td style="border: 1px solid #DEE2E6;">' . htmlspecialchars($p['kecamatan']) . '</td>' . "\n";
            $output .= '</tr>' . "\n";
            
            $first = false;
        }
    } else {
        // Jika tidak ada pemesanan (Hanya baris Kuota)
        $output .= '<tr style="background-color: #FCFCFC;">' . "\n";
        $output .= '<td style="border: 1px solid #DEE2E6; text-align: center;">' . $no++ . '</td>' . "\n";
        $output .= '<td style="border: 1px solid #DEE2E6;">' . htmlspecialchars($k['nama_vendor']) . '</td>' . "\n";
        $output .= '<td style="border: 1px solid #DEE2E6;">' . htmlspecialchars($k['jenis_tiang']) . '</td>' . "\n";
        $output .= '<td style="border: 1px solid #DEE2E6; text-align: center; font-weight: bold;">' . $k['kuota'] . '</td>' . "\n";
        $output .= '<td style="border: 1px solid #DEE2E6; text-align: center;">0</td>' . "\n";
        $output .= '<td style="border: 1px solid #DEE2E6; text-align: center; font-weight: bold;">' . $k['kuota'] . '</td>' . "\n";
        $output .= '<td colspan="9" style="border: 1px solid #DEE2E6; text-align: center; color: #999;">Belum ada realisasi pemesanan</td>' . "\n";
        $output .= '</tr>' . "\n";
    }
}

$output .= '</table>';

echo $output;
?>