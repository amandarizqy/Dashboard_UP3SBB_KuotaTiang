<?php 
include 'koneksi.php'; 

// Pengaturan Sidebar
$expanded = isset($_GET['expand']) ? 'expanded' : '';

// Filter settings - Handle multiple selections
// Status filter: '' = semua, 'aktif' or 'nonaktif'
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';

$filter_vendors = isset($_GET['filter_vendor']) ? $_GET['filter_vendor'] : [];
$filter_tiangs = isset($_GET['filter_tiang']) ? $_GET['filter_tiang'] : [];

// Pastikan array
if (!is_array($filter_vendors)) {
    $filter_vendors = !empty($filter_vendors) ? [$filter_vendors] : [];
}
if (!is_array($filter_tiangs)) {
    $filter_tiangs = !empty($filter_tiangs) ? [$filter_tiangs] : [];
}

// Build WHERE clause dengan IN untuk multiple values
$where_clauses = [];
if (!empty($filter_vendors)) {
    $vendor_list = implode("','", array_map('mysqli_real_escape_string', array_fill(0, count($filter_vendors), $conn), $filter_vendors));
    $where_clauses[] = "k.id_vendor IN ('$vendor_list')";
}
if (!empty($filter_tiangs)) {
    $tiang_list = implode("','", array_map('mysqli_real_escape_string', array_fill(0, count($filter_tiangs), $conn), $filter_tiangs));
    $where_clauses[] = "k.id_tiang IN ('$tiang_list')";
}
// Apply status filter if provided
if (!empty($filter_status)) {
    $fs = mysqli_real_escape_string($conn, $filter_status);
    $where_clauses[] = "k.status = '$fs'";
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

// Build query string untuk pagination dengan multiple selections
$params = [];
if (!empty($filter_vendors)) {
    foreach ($filter_vendors as $v) {
        $params['filter_vendor[]'] = $v;
    }
}
if (!empty($filter_tiangs)) {
    foreach ($filter_tiangs as $t) {
        $params['filter_tiang[]'] = $t;
    }
}
// include status in query string params when provided
if (!empty($filter_status)) {
    $params['filter_status'] = $filter_status;
}

$pagination_query_string = !empty($params) ? '&' . http_build_query($params) : '';
$export_query_string = !empty($params) ? '?' . http_build_query($params) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Database Keseluruhan - Monitoring Kuota PLN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="histori.css">
    <link rel="stylesheet" href="styledashboard.css">
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
            <div class="header-actions">
                <a href="export_excel.php<?php echo $export_query_string; ?>" class="btn-action-export" style="background: var(--pln-blue); border-radius: 8px; border:none; cursor:pointer; text-decoration: none; color: white; padding: 8px 14px; font-weight: 700; font-size: 13px; display: inline-block;">
                    <i class="fas fa-download"></i> Export
                </a>
                <button id="openFilterModal" class="btn-action-filter" style="background: var(--pln-yellow); border-radius: 8px; border:none; cursor:pointer; color: #222; font-weight: 700; padding: 8px 14px; font-size: 13px;">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
        </div>

        <!-- Filter Modal -->
        <div id="filterModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Pengaturan Filter Data</h2>
                    <button type="button" id="closeModal" class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="filterForm" action="" method="GET" style="display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap; width: 100%; flex-direction: column;">
                        <div class="filter-section">
                            <div class="filter-section-title">Status Kontrak:</div>
                            <div class="filter-group">
                                <select name="filter_status" class="form-control" style="min-width:160px; padding:8px; border-radius:8px;">
                                    <option value="" <?php echo ($filter_status == '') ? 'selected' : ''; ?>>Semua</option>
                                    <option value="aktif" <?php echo ($filter_status == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="nonaktif" <?php echo ($filter_status == 'nonaktif') ? 'selected' : ''; ?>>Non-Aktif</option>
                                </select>
                            </div>
                        </div>

                        <div class="filter-section">
                            <div class="filter-section-title">Pilih Vendor (PT):</div>
                            <div class="filter-group">
                                <?php 
                                $v_list = mysqli_query($conn, "SELECT * FROM vendor ORDER BY nama_vendor ASC");
                                while($v = mysqli_fetch_assoc($v_list)) {
                                    $checked = in_array($v['id_vendor'], $filter_vendors) ? 'checked' : '';
                                    echo "<label><input type='checkbox' name='filter_vendor[]' value='".$v['id_vendor']."' $checked> ".$v['nama_vendor']."</label>";
                                }
                                ?>
                            </div>
                        </div>

                        <div class="filter-section">
                            <div class="filter-section-title">Pilih Ukuran Tiang:</div>
                            <div class="filter-group">
                                <?php 
                                $t_list = mysqli_query($conn, "SELECT * FROM tiang ORDER BY jenis_tiang ASC");
                                while($t = mysqli_fetch_assoc($t_list)) {
                                    $checked = in_array($t['id_tiang'], $filter_tiangs) ? 'checked' : '';
                                    echo "<label><input type='checkbox' name='filter_tiang[]' value='".$t['id_tiang']."' $checked> ".$t['jenis_tiang']."</label>";
                                }
                                ?>
                            </div>
                        </div>

                        <div style="display: flex; gap: 15px; margin-top: 20px;">
                            <button type="submit" style="background-color: var(--pln-blue); color: white; border: none; padding: 12px 25px; border-radius: 8px; font-weight: 600; cursor: pointer;">Terapkan Filter</button>
                            <a href="histori.php" style="background-color: #e9ecef; color: #555; border: 1px solid #ddd; padding: 12px 25px; border-radius: 8px; text-decoration: none; font-weight: 600; cursor: pointer; align-self: center;">Reset Filter</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="table-container" style="overflow-x: auto; background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-top: 20px;">
                    <table style="width: 100%; border-collapse: collapse; min-width: 1500px;">
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
                                <th>No WO (Full)</th>
                                <th>Tgl WO</th>
                                <th>Kecamatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // 1. Ambil semua kontrak (Filter tetap berlaku)
                            $q_kontrak = mysqli_query($conn, "SELECT k.*, v.nama_vendor, t.jenis_tiang 
                                                            FROM kontrak k 
                                                            JOIN vendor v ON k.id_vendor = v.id_vendor 
                                                            JOIN tiang t ON k.id_tiang = t.id_tiang 
                                                            $where_sql
                                                            ORDER BY k.id_kontrak DESC");
                            
                            $all_rows = [];
                            $no = 1;
                            
                            while($k = mysqli_fetch_assoc($q_kontrak)) {
                                $id_k = $k['id_kontrak'];
                                $kuota_berjalan = $k['kuota'];
                                
                                // 2. Ambil pemesanan URUT BERDASARKAN TANGGAL WO TERDAHULU (Kronologis)
                                $q_pesan = mysqli_query($conn, "SELECT p.*, u.kecamatan 
                                                                FROM pemesanan p 
                                                                JOIN ulp u ON p.id_ulp = u.id_ulp 
                                                                WHERE p.id_kontrak = '$id_k' 
                                                                ORDER BY p.tgl_wo ASC");
                                
                                if(mysqli_num_rows($q_pesan) > 0) {
                                    $first = true;
                                    while($p = mysqli_fetch_assoc($q_pesan)) {
                                        $kuota_berjalan -= $p['kebutuhan'];
                                        
                                        // Gabungkan Sub WO dan No WO Master
                                        $full_wo = $p['sub_wo'] . " / " . $p['no_wo'];
                                        
                                        $all_rows[] = [
                                            'no' => $no++,
                                            'nama_vendor' => $k['nama_vendor'],
                                            'jenis_tiang' => $k['jenis_tiang'],
                                            'kuota_awal' => ($first) ? $k['kuota'] : '-', // Hanya tampil di baris pertama kontrak
                                            'kebutuhan' => $p['kebutuhan'],
                                            'sisa_kuota' => $kuota_berjalan,
                                            'ket_kuota' => $p['ket_kuota'],
                                            'nama_pelanggan' => $p['nama_pelanggan'],
                                            'lokasi' => $p['lokasi'],
                                            'nomor_kontrak' => $k['nomor_kontrak'],
                                            'tanggal_terbit' => date('d/m/Y', strtotime($k['tanggal_terbit'])),
                                            'akhir_tenggat' => date('d/m/Y', strtotime($k['akhir_tenggat'])),
                                            'no_wo' => $full_wo, // Hasil Concat
                                            'tgl_wo' => date('d/m/Y', strtotime($p['tgl_wo'])),
                                            'kecamatan' => $p['kecamatan'],
                                            'type' => 'pesan'
                                        ];
                                        $first = false;
                                    }
                                } else {
                                    // Jika kontrak belum ada realisasi
                                    $all_rows[] = [
                                        'no' => $no++,
                                        'nama_vendor' => $k['nama_vendor'],
                                        'jenis_tiang' => $k['jenis_tiang'],
                                        'kuota_awal' => $k['kuota'],
                                        'kebutuhan' => 0,
                                        'sisa_kuota' => $k['kuota'],
                                        'ket_kuota' => '-',
                                        'nama_pelanggan' => '<i style="color:#bbb">Belum ada realisasi</i>',
                                        'lokasi' => '-',
                                        'nomor_kontrak' => $k['nomor_kontrak'],
                                        'tanggal_terbit' => date('d/m/Y', strtotime($k['tanggal_terbit'])),
                                        'akhir_tenggat' => date('d/m/Y', strtotime($k['akhir_tenggat'])),
                                        'no_wo' => '-',
                                        'tgl_wo' => '-',
                                        'kecamatan' => '-',
                                        'type' => 'empty'
                                    ];
                                }
                            }
                            
                            // Potong array untuk pagination
                            $page_rows = array_slice($all_rows, $offset, $rows_per_page);
                            
                            foreach($page_rows as $row) {
                                $tr_class = ($row['type'] == 'empty') ? 'style="background-color: #fcfcfc;"' : '';
                                echo "<tr $tr_class>";
                                echo "<td>{$row['no']}</td>";
                                echo "<td>{$row['nama_vendor']}</td>";
                                echo "<td>{$row['jenis_tiang']}</td>";
                                echo "<td style='text-align:center; font-weight:bold;'>{$row['kuota_awal']}</td>";
                                echo "<td style='text-align:center; color:var(--pln-blue); font-weight:bold;'>{$row['kebutuhan']}</td>";
                                
                                $sisa_style = ($row['sisa_kuota'] < 0) ? 'color: #e74c3c; font-weight:bold;' : 'color: #27ae60; font-weight:bold;';
                                echo "<td style='text-align:center; $sisa_style'>{$row['sisa_kuota']}</td>";
                                
                                echo "<td>{$row['ket_kuota']}</td>";
                                echo "<td>{$row['nama_pelanggan']}</td>";
                                echo "<td>{$row['lokasi']}</td>";
                                echo "<td>{$row['nomor_kontrak']}</td>";
                                echo "<td>{$row['tanggal_terbit']}</td>";
                                echo "<td>{$row['akhir_tenggat']}</td>";
                                
                                // Menampilkan Full WO (Concat)
                                echo "<td><span class='badge-wo' style='background:#e3f2fd; color:#0d47a1; padding:4px 8px; border-radius:6px; font-size:11px; font-weight:bold;'>{$row['no_wo']}</span></td>";
                                
                                echo "<td>{$row['tgl_wo']}</td>";
                                echo "<td>{$row['kecamatan']}</td>";
                                echo "</tr>";
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
        
        // Modal functionality
        const filterModal = document.getElementById('filterModal');
        const openModalBtn = document.getElementById('openFilterModal');
        const closeModalBtn = document.getElementById('closeModal');
        
        // Open modal
        openModalBtn.addEventListener('click', function() {
            filterModal.classList.add('show');
        });
        
        // Close modal
        closeModalBtn.addEventListener('click', function() {
            filterModal.classList.remove('show');
        });
        
        // Close modal when clicking outside of modal-content
        filterModal.addEventListener('click', function(event) {
            if (event.target === filterModal) {
                filterModal.classList.remove('show');
            }
        });
        
        // Close modal when form is submitted
        document.getElementById('filterForm').addEventListener('submit', function() {
            setTimeout(() => {
                filterModal.classList.remove('show');
            }, 100);
        });
    </script>
</body>
</html>