<?php
include 'koneksi.php';

// Get filters dari GET parameter
$filter_vendor = isset($_GET['filter_vendor']) ? $_GET['filter_vendor'] : '';
$filter_tiang = isset($_GET['filter_tiang']) ? $_GET['filter_tiang'] : '';

// Build WHERE clause
$where_clauses = [];
if (!empty($filter_vendor)) {
    $where_clauses[] = "k.id_vendor = '$filter_vendor'";
}
if (!empty($filter_tiang)) {
    $where_clauses[] = "k.id_tiang = '$filter_tiang'";
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
$headers = ['No', 'Nama PT', 'Ukuran Tiang', 'Kuota', 'Kebutuhan', 'Sisa Kuota', 'Ket Kuota', 'Nama Pelanggan', 'Lokasi', 'No SPB', 'Tgl Terbit SPB', 'Tgl Akhir SPB', 'No WO', 'Tgl WO', 'Kecamatan'];

$output .= '<tr style="background-color: #F8F9FA; font-weight: bold; text-align: center; color: #555555;">' . "\n";
foreach ($headers as $header) {
    $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px;">' . htmlspecialchars($header) . '</td>' . "\n";
}
$output .= '</tr>' . "\n";

// Query data dengan filter
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
    
    // Ambil semua pemesanan untuk kontrak ini
    $q_pesan = mysqli_query($conn, "SELECT p.*, u.kecamatan 
                                    FROM pemesanan p 
                                    JOIN ulp u ON p.id_ulp = u.id_ulp 
                                    WHERE p.id_kontrak = '$id_k' 
                                    ORDER BY p.id_pemesanan ASC");
    
    if(mysqli_num_rows($q_pesan) > 0) {
        $first = true;
        while($p = mysqli_fetch_assoc($q_pesan)) {
            $kuota_berjalan -= $p['kebutuhan'];
            
            $output .= '<tr>' . "\n";
            $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px;">' . $no++ . '</td>' . "\n";
            $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px;">' . htmlspecialchars($k['nama_vendor']) . '</td>' . "\n";
            $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px;">' . htmlspecialchars($k['jenis_tiang']) . '</td>' . "\n";
            $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px; text-align: center;">' . (($first) ? $k['kuota'] : '') . '</td>' . "\n";
            $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px; text-align: center;">' . $p['kebutuhan'] . '</td>' . "\n";
            $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px; text-align: center;">' . $kuota_berjalan . '</td>' . "\n";
            $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px;">' . htmlspecialchars($p['ket_kuota']) . '</td>' . "\n";
            $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px;">' . htmlspecialchars($p['nama_pelanggan']) . '</td>' . "\n";
            $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px;">' . htmlspecialchars($p['lokasi']) . '</td>' . "\n";
            $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px;">' . htmlspecialchars($k['nomor_kontrak']) . '</td>' . "\n";
            $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px;">' . date('d/m/Y', strtotime($k['tanggal_terbit'])) . '</td>' . "\n";
            $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px;">' . date('d/m/Y', strtotime($k['akhir_tenggat'])) . '</td>' . "\n";
            $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px;">' . htmlspecialchars($p['no_wo']) . '</td>' . "\n";
            $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px;">' . date('d/m/Y', strtotime($p['tgl_wo'])) . '</td>' . "\n";
            $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px;">' . htmlspecialchars($p['kecamatan']) . '</td>' . "\n";
            $output .= '</tr>' . "\n";
            
            $first = false;
        }
    } else {
        // Jika tidak ada pemesanan
        $output .= '<tr>' . "\n";
        $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px;">' . $no++ . '</td>' . "\n";
        $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px;">' . htmlspecialchars($k['nama_vendor']) . '</td>' . "\n";
        $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px;">' . htmlspecialchars($k['jenis_tiang']) . '</td>' . "\n";
        $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px; text-align: center;">' . $k['kuota'] . '</td>' . "\n";
        $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px; text-align: center;">0</td>' . "\n";
        $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px; text-align: center;">' . $k['kuota'] . '</td>' . "\n";
        $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px;"></td>' . "\n";
        $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px;">Belum ada realisasi</td>' . "\n";
        $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px;"></td>' . "\n";
        $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px;">' . htmlspecialchars($k['nomor_kontrak']) . '</td>' . "\n";
        $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px;">' . date('d/m/Y', strtotime($k['tanggal_terbit'])) . '</td>' . "\n";
        $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px;">' . date('d/m/Y', strtotime($k['akhir_tenggat'])) . '</td>' . "\n";
        $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px;"></td>' . "\n";
        $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px;"></td>' . "\n";
        $output .= '<td style="border: 1px solid #DEE2E6; padding: 8px;"></td>' . "\n";
        $output .= '</tr>' . "\n";
    }
}

$output .= '</table>';

echo $output;
?>
