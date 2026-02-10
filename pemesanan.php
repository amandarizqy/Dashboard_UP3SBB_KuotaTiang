<?php 
include 'koneksi.php'; 

// 1. LOGIKA HAPUS DATA
if (isset($_GET['delete_id'])) {
    $del_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $query_delete = mysqli_query($conn, "DELETE FROM pemesanan WHERE id_pemesanan = '$del_id'");
    
    if($query_delete) {
        header("Location: pemesanan.php");
        exit;
    } else {
        echo "<script>alert('Gagal menghapus data'); window.location='pemesanan.php';</script>";
    }
}

// 2. LOGIKA FILTER & PAGINATION
$filter_vendor = isset($_GET['filter_vendor']) ? $_GET['filter_vendor'] : '';
$filter_tiang  = isset($_GET['filter_tiang']) ? $_GET['filter_tiang'] : '';

// Tentukan jumlah data per halaman
$limit = 10;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pemesanan Tiang - PLN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --pln-blue: #00A3E0;
            --pln-yellow: #FFD100;
            --bg-gray: #f4f7f9;
            --sidebar-width: 70px;
            --sidebar-expanded: 240px;
        }

        body { 
            background-color: var(--bg-gray); 
            margin: 0; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
        }

        /* --- Sidebar Samping --- */
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

        #sidebar.expanded { width: var(--sidebar-expanded); }

        .sidebar-header {
            padding: 20px 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: white;
            margin-bottom: 20px;
        }

        .logo-sidebar {
            width: 40px;
            height: auto;
            transition: 0.3s;
        }

        #sidebar.expanded .logo-sidebar { width: 80px; }

        .nav-item { 
            padding: 20px;
            display: flex;
            align-items: center;
            color: white; 
            text-decoration: none;
            cursor: pointer;
            white-space: nowrap;
            transition: 0.3s;
        }

        .nav-item:hover { background-color: rgba(255, 255, 255, 0.1); border-left: 5px solid var(--pln-yellow); }
        .nav-item i { font-size: 24px; min-width: 30px; margin-right: 20px; text-align: center; }
        .nav-item span { opacity: 0; transition: 0.3s; }
        #sidebar.expanded .nav-item span { opacity: 1; }

        /* --- Main Content --- */
        #main-content { 
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            padding: 40px;
            transition: 0.3s;
        }

        .header-box { 
            display: flex; 
            flex-direction: column;
            gap: 20px;
            background: white; 
            padding: 25px; 
            border-radius: 20px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
            margin-bottom: 30px; 
        }

        .header-top { display: flex; justify-content: space-between; align-items: center; }

        .filter-section {
            display: flex;
            gap: 15px;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .filter-select {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            outline: none;
            font-size: 14px;
            min-width: 150px;
        }

        .btn-filter {
            background: var(--pln-yellow);
            color: #333;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-add { 
            background: var(--pln-blue); 
            color: white; 
            padding: 12px 25px; 
            border-radius: 12px; 
            text-decoration: none; 
            font-weight: bold; 
            transition: 0.3s;
        }

        /* Tabel Renggang */
        table { width: 100%; border-collapse: separate; border-spacing: 0 15px; }
        th { padding: 10px 15px; text-align: left; color: #888; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
        td { padding: 25px 20px; background: white; vertical-align: middle; font-size: 13px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
        tr td:first-child { border-radius: 15px 0 0 15px; }
        tr td:last-child { border-radius: 0 15px 15px 0; text-align: right; }
        
        .badge-kebutuhan { background: #f0f7ff; color: var(--pln-blue); padding: 8px 12px; border-radius: 8px; font-weight: 800; font-size: 1.1rem; }
        
        /* Pagination Styling */
        .pagination-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }

        .page-link {
            padding: 10px 18px;
            background: white;
            color: var(--pln-blue);
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: 0.3s;
        }

        .page-link:hover { background: var(--pln-blue); color: white; }
        .page-link.active { background: var(--pln-blue); color: white; cursor: default; }
        .page-link.disabled { background: #eee; color: #aaa; cursor: not-allowed; pointer-events: none; }
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
            <div class="header-top">
                <div>
                    <h1 style="margin:0; color:var(--pln-blue);">Data Pemesanan</h1>
                    <small>Kelola pemakaian kuota tiang berdasarkan WO pelanggan</small>
                </div>
                <a href="add_pemesanan.php" class="btn-add"><i class="fas fa-plus"></i> Tambah Pesanan Baru</a>
            </div>

            <form action="" method="GET" class="filter-section">
                <select name="filter_vendor" class="filter-select">
                    <option value="">-- Semua Vendor --</option>
                    <?php 
                    $v_query = mysqli_query($conn, "SELECT * FROM vendor");
                    while($v = mysqli_fetch_assoc($v_query)){
                        $selected = ($filter_vendor == $v['id_vendor']) ? 'selected' : '';
                        echo "<option value='".$v['id_vendor']."' $selected>".$v['nama_vendor']."</option>";
                    }
                    ?>
                </select>

                <select name="filter_tiang" class="filter-select">
                    <option value="">-- Semua Ukuran Tiang --</option>
                    <?php 
                    $t_query = mysqli_query($conn, "SELECT * FROM tiang");
                    while($t = mysqli_fetch_assoc($t_query)){
                        $selected = ($filter_tiang == $t['id_tiang']) ? 'selected' : '';
                        echo "<option value='".$t['id_tiang']."' $selected>".$t['jenis_tiang']."</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="btn-filter"><i class="fas fa-filter"></i> Filter</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Pelanggan & Lokasi</th>
                    <th>No SPB / Vendor</th>
                    <th>Ukuran Tiang</th>
                    <th style="text-align: center;">Butuh</th>
                    <th>No & Tgl WO</th>
                    <th>Kecamatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Hitung total data untuk pagination
                $count_sql = "SELECT COUNT(*) as total FROM pemesanan p
                              JOIN kontrak k ON p.id_kontrak = k.id_kontrak
                              JOIN vendor v ON k.id_vendor = v.id_vendor
                              JOIN tiang t ON k.id_tiang = t.id_tiang";
                
                $conditions = [];
                if ($filter_vendor != '') $conditions[] = "v.id_vendor = '$filter_vendor'";
                if ($filter_tiang != '') $conditions[] = "t.id_tiang = '$filter_tiang'";
                if (count($conditions) > 0) $count_sql .= " WHERE " . implode(' AND ', $conditions);

                $count_res = mysqli_query($conn, $count_sql);
                $total_data = mysqli_fetch_assoc($count_res)['total'];
                $total_pages = ceil($total_data / $limit);

                // Query Data dengan LIMIT & OFFSET
                $sql = "SELECT p.*, k.nomor_kontrak, v.nama_vendor, t.jenis_tiang, u.kecamatan
                        FROM pemesanan p
                        JOIN kontrak k ON p.id_kontrak = k.id_kontrak
                        JOIN vendor v ON k.id_vendor = v.id_vendor
                        JOIN tiang t ON k.id_tiang = t.id_tiang
                        JOIN ulp u ON p.id_ulp = u.id_ulp";
                
                if (count($conditions) > 0) $sql .= " WHERE " . implode(' AND ', $conditions);
                $sql .= " ORDER BY p.id_pemesanan DESC LIMIT $limit OFFSET $offset";
                
                $res = mysqli_query($conn, $sql);
                if(mysqli_num_rows($res) > 0) {
                    while($row = mysqli_fetch_assoc($res)) {
                        ?>
                        <tr>
                            <td><strong><?php echo $row['nama_pelanggan']; ?></strong><br><small><?php echo $row['lokasi']; ?></small></td>
                            <td><strong><?php echo $row['nomor_kontrak']; ?></strong><br><small><?php echo $row['nama_vendor']; ?></small></td>
                            <td><?php echo $row['jenis_tiang']; ?></td>
                            <td style="text-align: center;"><span class="badge-kebutuhan"><?php echo $row['kebutuhan']; ?></span></td>
                            <td><strong><?php echo $row['no_wo']; ?></strong><br><small><?php echo date('d/m/Y', strtotime($row['tgl_wo'])); ?></small></td>
                            <td><strong><?php echo $row['kecamatan']; ?></strong></td>
                            <td>
                                <a href="edit_pemesanan.php?id=<?php echo $row['id_pemesanan']; ?>" style="color:var(--pln-blue);"><i class="fas fa-edit"></i></a>
                                <a href="pemesanan.php?delete_id=<?php echo $row['id_pemesanan']; ?>" style="color:#e74c3c; margin-left:15px;" onclick="return confirm('Hapus?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align:center; padding:50px;'>Data tidak ditemukan.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="pagination-container">
            <a href="?page=<?php echo ($page > 1) ? ($page - 1) : 1; ?>&filter_vendor=<?php echo $filter_vendor; ?>&filter_tiang=<?php echo $filter_tiang; ?>" 
               class="page-link <?php if($page <= 1) echo 'disabled'; ?>">
               <i class="fas fa-chevron-left"></i>
            </a>

            <span style="font-size: 14px; font-weight: bold; color: #666;">
                Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?>
            </span>

            <a href="?page=<?php echo ($page < $total_pages) ? ($page + 1) : $total_pages; ?>&filter_vendor=<?php echo $filter_vendor; ?>&filter_tiang=<?php echo $filter_tiang; ?>" 
               class="page-link <?php if($page >= $total_pages) echo 'disabled'; ?>">
               <i class="fas fa-chevron-right"></i>
            </a>
        </div>
    </main>

    <script>
        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('expanded'); }
    </script>
</body>
</html>