<?php 
include 'koneksi.php'; 

// Pengaturan Sidebar
$expanded = isset($_GET['expand']) ? 'expanded' : '';

// Filter settings
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

// Pagination settings
$rows_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

// Hitung total data rows dengan filter
$total_rows = 0;
$q_kontrak_count = mysqli_query($conn, "SELECT k.id_kontrak FROM kontrak k $where_sql ORDER BY k.id_kontrak DESC");
while($k = mysqli_fetch_assoc($q_kontrak_count)) {
    $id_k = $k['id_kontrak'];
    $q_pesan_count = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM pemesanan WHERE id_kontrak = '$id_k'");
    $p_cnt = mysqli_fetch_assoc($q_pesan_count);
    $total_rows += max(1, $p_cnt['cnt']); // minimal 1 baris per kontrak
}

$total_pages = ceil($total_rows / $rows_per_page);
if ($current_page > $total_pages) $current_page = $total_pages;

$offset = ($current_page - 1) * $rows_per_page;

// Build query string untuk pagination
$params = [];
if (!empty($filter_vendor)) $params['filter_vendor'] = $filter_vendor;
if (!empty($filter_tiang)) $params['filter_tiang'] = $filter_tiang;

$pagination_query_string = !empty($params) ? '&' . http_build_query($params) : '';
$export_query_string = !empty($params) ? '?' . http_build_query($params) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Database Keseluruhan - Monitoring Kuota PLN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styledashboard.css">
    <style>
        :root {
            --pln-blue: #00A3E0;
            --pln-yellow: #FFD100;
            --bg-gray: #f4f7f9;
            --sidebar-width: 70px;
        }

        body { 
            background-color: var(--bg-gray); 
            margin: 0; 
            font-family: 'Segoe UI', sans-serif;
            display: flex;
        }

        /* Sidebar Styling */
        #sidebar {
            width: var(--sidebar-width);
            background-color: var(--pln-blue);
            color: white;
            height: 100vh;
            position: fixed;
            transition: 0.3s;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            border-right: 5px solid var(--pln-yellow);
            z-index: 1000;
        }
        #sidebar.expanded { width: 240px; }
        .nav-item { 
            padding: 20px; display: flex; align-items: center; color: white; 
            text-decoration: none; transition: 0.3s; white-space: nowrap;
        }
        .nav-item i { font-size: 24px; min-width: 30px; margin-right: 20px; text-align: center; }
        .nav-item span { opacity: 0; transition: 0.3s; }
        #sidebar.expanded .nav-item span { opacity: 1; }
        .nav-item:hover { background: rgba(255,255,255,0.1); border-left: 5px solid var(--pln-yellow); }

        /* Main Content */
        #main-content { 
            margin-left: var(--sidebar-width); 
            flex-grow: 1; 
            padding: 30px; 
            transition: 0.3s; 
            overflow-x: auto;
        }

        .header-box {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Table Styling */
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th {
            background-color: #f8f9fa;
            color: #555;
            padding: 12px 10px;
            border: 1px solid #dee2e6;
            text-transform: uppercase;
            white-space: nowrap;
        }

        td {
            padding: 10px;
            border: 1px solid #dee2e6;
            vertical-align: middle;
            color: #333;
        }

        .row-spb { background-color: #f0f7ff; font-weight: bold; }
        .minus { color: #e74c3c; font-weight: bold; }
        .plus { color: #27ae60; font-weight: bold; }

        /* Pagination Styling */
        .pagination-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .pagination-btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #f0f0f0;
            color: #333;
            text-decoration: none;
            border-radius: 8px;
            border: 1px solid #ddd;
            cursor: pointer;
            transition: 0.3s;
        }

        .pagination-btn:hover {
            background-color: var(--pln-blue);
            color: white;
            border-color: var(--pln-blue);
        }

        .pagination-btn.active {
            background-color: var(--pln-blue);
            color: white;
            border-color: var(--pln-blue);
            font-weight: bold;
        }

        .pagination-btn:disabled,
        .pagination-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-btn:disabled:hover,
        .pagination-btn.disabled:hover {
            background-color: #f0f0f0;
            color: #333;
            border-color: #ddd;
        }

        .pagination-info {
            color: #666;
            font-weight: 500;
        }

        /* Filter Styling */
        .filter-box {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-box label {
            font-weight: 600;
            color: #555;
            margin-right: 5px;
        }

        .filter-box select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
            min-width: 180px;
            font-size: 14px;
        }

        .filter-box button {
            background-color: var(--pln-yellow);
            color: #222;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 800;
            cursor: pointer;
            text-transform: uppercase;
            transition: 0.3s;
        }

        .filter-box button:hover {
            background-color: #FFD100;
            opacity: 0.9;
        }

        .filter-box a {
            background-color: #e9ecef;
            color: #555;
            border: 1px solid #ddd;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .filter-box a:hover {
            background-color: #dee2e6;
        }
    </style>
</head>
<body>

    <nav id="sidebar">
        <div class="sidebar-header">
            <img src="logo_PLN.png" alt="Logo PLN" class="logo-sidebar">
        </div>
        <div class="nav-item" onclick="toggleSidebar()"><i class="fas fa-bars"></i><span>Tutup/Buka Menu</span></div>
        <a href="dashboard.php" class="nav-item"><i class="fas fa-home"></i><span>Beranda Dashboard</span></a>
        <hr style="width: 80%; border: 0.5px solid rgba(255,255,255,0.2); margin: 15px auto;">
        <a href="menu.php" class="nav-item"><i class="fas fa-database"></i><span>Manajemen Data</span></a>
        <a href="pemesanan.php" class="nav-item"><i class="fas fa-shopping-cart"></i><span>Pemesanan</span></a>
        <a href="histori.php" class="nav-item"><i class="fas fa-history"></i><span>History</span></a>
    </nav>

    <main id="main-content">
        <div class="header-box">
            <div>
                <h1 style="margin:0; color:var(--pln-blue);">Database Keseluruhan</h1>
                <p style="margin:5px 0 0; color:#888;">Rekapitulasi sinkronisasi Kuota Kontrak dan Realisasi Pemesanan WO</p>
            </div>
            <a href="export_excel.php<?php echo $export_query_string; ?>" class="nav-item" style="background: var(--pln-blue); border-radius: 10px; border:none; cursor:pointer; text-decoration: none;">
                <i class="fas fa-download"></i> <h3>Export</h3> <span>Export Excel</span> 
            </a>
        </div>

        <!-- Filter Box -->
        <div class="filter-box">
            <form action="" method="GET" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap; width: 100%;">
                <div>
                    <label for="filter_vendor">Filter Vendor:</label>
                    <select name="filter_vendor" id="filter_vendor">
                        <option value="">Semua Vendor</option>
                        <?php 
                        $v_list = mysqli_query($conn, "SELECT * FROM vendor ORDER BY nama_vendor ASC");
                        while($v = mysqli_fetch_assoc($v_list)) {
                            $sel = ($filter_vendor == $v['id_vendor']) ? 'selected' : '';
                            echo "<option value='".$v['id_vendor']."' $sel>".$v['nama_vendor']."</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label for="filter_tiang">Filter Tiang:</label>
                    <select name="filter_tiang" id="filter_tiang">
                        <option value="">Semua Ukuran</option>
                        <?php 
                        $t_list = mysqli_query($conn, "SELECT * FROM tiang ORDER BY jenis_tiang ASC");
                        while($t = mysqli_fetch_assoc($t_list)) {
                            $sel = ($filter_tiang == $t['id_tiang']) ? 'selected' : '';
                            echo "<option value='".$t['id_tiang']."' $sel>".$t['jenis_tiang']."</option>";
                        }
                        ?>
                    </select>
                </div>

                <button type="submit">Filter</button>
                <a href="histori.php">Reset</a>
            </form>
        </div>

            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama PT (Vendor)</th>
                        <th>Ukuran Tiang</th>
                        <th>Kuota Awal</th>
                        <th>Kebutuhan</th>
                        <th>Sisa Kuota</th>
                        <th>Ket Kuota</th>
                        <th>Nama Pelanggan</th>
                        <th>Lokasi</th>
                        <th>No SPB</th>
                        <th>Tgl Terbit</th>
                        <th>Tgl Akhir</th>
                        <th>No WO</th>
                        <th>Tgl WO</th>
                        <th>Kecamatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Ambil semua kontrak dengan pemesanannya (dengan filter)
                    $q_kontrak = mysqli_query($conn, "SELECT k.*, v.nama_vendor, t.jenis_tiang 
                                                      FROM kontrak k 
                                                      JOIN vendor v ON k.id_vendor = v.id_vendor 
                                                      JOIN tiang t ON k.id_tiang = t.id_tiang 
                                                      $where_sql
                                                      ORDER BY k.id_kontrak DESC");
                    
                    // Kumpulkan semua row data
                    $all_rows = [];
                    $no = 1;
                    
                    while($k = mysqli_fetch_assoc($q_kontrak)) {
                        $id_k = $k['id_kontrak'];
                        $kuota_berjalan = $k['kuota'];
                        
                        // Ambil semua pemesanan untuk kontrak ini secara kronologis
                        $q_pesan = mysqli_query($conn, "SELECT p.*, u.kecamatan 
                                                        FROM pemesanan p 
                                                        JOIN ulp u ON p.id_ulp = u.id_ulp 
                                                        WHERE p.id_kontrak = '$id_k' 
                                                        ORDER BY p.id_pemesanan ASC");
                        
                        if(mysqli_num_rows($q_pesan) > 0) {
                            $first = true;
                            while($p = mysqli_fetch_assoc($q_pesan)) {
                                $kuota_berjalan -= $p['kebutuhan'];
                                
                                $row_data = [
                                    'no' => $no++,
                                    'nama_vendor' => $k['nama_vendor'],
                                    'jenis_tiang' => $k['jenis_tiang'],
                                    'kuota_awal' => ($first) ? $k['kuota'] : '',
                                    'kebutuhan' => $p['kebutuhan'],
                                    'sisa_kuota' => $kuota_berjalan,
                                    'ket_kuota' => $p['ket_kuota'],
                                    'nama_pelanggan' => $p['nama_pelanggan'],
                                    'lokasi' => $p['lokasi'],
                                    'nomor_kontrak' => $k['nomor_kontrak'],
                                    'tanggal_terbit' => date('d/m/Y', strtotime($k['tanggal_terbit'])),
                                    'akhir_tenggat' => date('d/m/Y', strtotime($k['akhir_tenggat'])),
                                    'no_wo' => $p['no_wo'],
                                    'tgl_wo' => date('d/m/Y', strtotime($p['tgl_wo'])),
                                    'kecamatan' => $p['kecamatan'],
                                    'type' => 'pesan'
                                ];
                                $all_rows[] = $row_data;
                                $first = false;
                            }
                        } else {
                            // Jika kontrak belum ada pemesanan sama sekali
                            $row_data = [
                                'no' => $no++,
                                'nama_vendor' => $k['nama_vendor'],
                                'jenis_tiang' => $k['jenis_tiang'],
                                'kuota_awal' => $k['kuota'],
                                'kebutuhan' => 0,
                                'sisa_kuota' => $k['kuota'],
                                'ket_kuota' => '',
                                'nama_pelanggan' => 'Belum ada realisasi',
                                'lokasi' => '',
                                'nomor_kontrak' => $k['nomor_kontrak'],
                                'tanggal_terbit' => date('d/m/Y', strtotime($k['tanggal_terbit'])),
                                'akhir_tenggat' => date('d/m/Y', strtotime($k['akhir_tenggat'])),
                                'no_wo' => '',
                                'tgl_wo' => '',
                                'kecamatan' => '',
                                'type' => 'empty'
                            ];
                            $all_rows[] = $row_data;
                        }
                    }
                    
                    // Potong array untuk halaman saat ini
                    $page_rows = array_slice($all_rows, $offset, $rows_per_page);
                    
                    // Tampilkan rows untuk halaman saat ini
                    foreach($page_rows as $row) {
                        if($row['type'] == 'empty') {
                            echo '<tr class="row-spb">';
                        } else {
                            echo '<tr>';
                        }
                        echo '<td>' . $row['no'] . '</td>';
                        echo '<td>' . $row['nama_vendor'] . '</td>';
                        echo '<td>' . $row['jenis_tiang'] . '</td>';
                        echo '<td style="text-align:center; font-weight:bold;">' . $row['kuota_awal'] . '</td>';
                        echo '<td style="text-align:center; color:var(--pln-blue); font-weight:bold;">' . $row['kebutuhan'] . '</td>';
                        $minus_class = ($row['sisa_kuota'] < 0) ? 'minus' : 'plus';
                        echo '<td class="' . $minus_class . '" style="text-align:center;">' . $row['sisa_kuota'] . '</td>';
                        echo '<td>' . $row['ket_kuota'] . '</td>';
                        echo '<td>' . $row['nama_pelanggan'] . '</td>';
                        echo '<td>' . $row['lokasi'] . '</td>';
                        echo '<td>' . $row['nomor_kontrak'] . '</td>';
                        echo '<td>' . $row['tanggal_terbit'] . '</td>';
                        echo '<td>' . $row['akhir_tenggat'] . '</td>';
                        echo '<td>' . $row['no_wo'] . '</td>';
                        echo '<td>' . $row['tgl_wo'] . '</td>';
                        echo '<td>' . $row['kecamatan'] . '</td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div class="pagination-container">
            <span class="pagination-info">Halaman <?php echo $current_page; ?> dari <?php echo $total_pages; ?> | Total Data: <?php echo $total_rows; ?> baris</span>
            
            <div style="display: flex; gap: 5px;">
                <?php if($current_page > 1): ?>
                    <a href="?page=1<?php echo $pagination_query_string; ?>" class="pagination-btn">|< Awal</a>
                    <a href="?page=<?php echo $current_page - 1; ?><?php echo $pagination_query_string; ?>" class="pagination-btn">< Sebelumnya</a>
                <?php else: ?>
                    <span class="pagination-btn disabled">|< Awal</span>
                    <span class="pagination-btn disabled">< Sebelumnya</span>
                <?php endif; ?>

                <!-- Nomor Halaman -->
                <?php 
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);
                
                if($start_page > 1) {
                    echo '<span class="pagination-btn" style="cursor: default; background: transparent; border: none;">...</span>';
                }
                
                for($i = $start_page; $i <= $end_page; $i++): 
                    if($i == $current_page):
                        echo '<span class="pagination-btn active">' . $i . '</span>';
                    else:
                        echo '<a href="?page=' . $i . $pagination_query_string . '" class="pagination-btn">' . $i . '</a>';
                    endif;
                endfor;
                
                if($end_page < $total_pages) {
                    echo '<span class="pagination-btn" style="cursor: default; background: transparent; border: none;">...</span>';
                }
                ?>

                <?php if($current_page < $total_pages): ?>
                    <a href="?page=<?php echo $current_page + 1; ?><?php echo $pagination_query_string; ?>" class="pagination-btn">Berikutnya ></a>
                    <a href="?page=<?php echo $total_pages; ?><?php echo $pagination_query_string; ?>" class="pagination-btn">Akhir >|</a>
                <?php else: ?>
                    <span class="pagination-btn disabled">Berikutnya ></span>
                    <span class="pagination-btn disabled">Akhir >|</span>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('expanded');
        }
    </script>
</body>
</html>