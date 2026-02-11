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

// Membangun Query Condition untuk Filter
$conditions = [];
if ($filter_vendor != '') $conditions[] = "k.id_vendor = '$filter_vendor'";
if ($filter_tiang != '') $conditions[] = "k.id_tiang = '$filter_tiang'";
$where = count($conditions) > 0 ? " WHERE " . implode(' AND ', $conditions) : "";

// Konfigurasi Pagination
$limit = 10;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// HITUNG TOTAL DATA (Penting agar pagination muncul)
$count_query = "SELECT COUNT(*) as total FROM pemesanan p 
                JOIN kontrak k ON p.id_kontrak = k.id_kontrak 
                $where";
$count_res = mysqli_query($conn, $count_query);
$total_data = mysqli_fetch_assoc($count_res)['total'];
$total_pages = ceil($total_data / $limit);
if($total_pages < 1) $total_pages = 1; // Minimal 1 halaman
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pemesanan Tiang - PLN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="stylepemesanan.css">
</head>
<body>

    <nav id="sidebar">
        <div class="sidebar-header"><img src="logo_PLN.png" alt="Logo PLN" class="logo-sidebar"></div>
        <div class="nav-item" onclick="toggleSidebar()"><i class="fas fa-bars"></i><span>Menu</span></div>
        <a href="dashboard.php" class="nav-item"><i class="fas fa-home"></i><span>Dashboard</span></a>
        <hr style="width: 80%; border: 0.5px solid rgba(255,255,255,0.2); margin: 15px auto;">
        <a href="menu.php" class="nav-item"><i class="fas fa-database"></i><span>Master Data</span></a>
        <a href="pemesanan.php" class="nav-item"><i class="fas fa-shopping-cart"></i><span>Pemesanan</span></a>
        <a href="histori.php" class="nav-item"><i class="fas fa-history"></i><span>History</span></a>
    </nav>

    <main id="main-content">
        <div class="header-box">
            <div class="header-top">
                <div>
                    <h2 style="margin:0; color:var(--pln-blue);">Data Pemesanan</h2>
                    <small style="color:#888;">Kelola realisasi pemakaian tiang dari WO pelanggan</small>
                </div>
                <a href="add_pemesanan.php" class="btn-add"><i class="fas fa-plus"></i> Tambah Pesanan</a>
            </div>

            <form action="" method="GET" class="filter-section">
                <select name="filter_vendor" class="filter-select" onchange="this.form.submit()">
                    <option value="">-- Semua Vendor --</option>
                    <?php 
                    $v_query = mysqli_query($conn, "SELECT * FROM vendor");
                    while($v = mysqli_fetch_assoc($v_query)){
                        $selected = ($filter_vendor == $v['id_vendor']) ? 'selected' : '';
                        echo "<option value='".$v['id_vendor']."' $selected>".$v['nama_vendor']."</option>";
                    }
                    ?>
                </select>

                <select name="filter_tiang" class="filter-select" onchange="this.form.submit()">
                    <option value="">-- Semua Ukuran Tiang --</option>
                    <?php 
                    $t_query = mysqli_query($conn, "SELECT * FROM tiang");
                    while($t = mysqli_fetch_assoc($t_query)){
                        $selected = ($filter_tiang == $t['id_tiang']) ? 'selected' : '';
                        echo "<option value='".$t['id_tiang']."' $selected>".$t['jenis_tiang']."</option>";
                    }
                    ?>
                </select>
            </form>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Pelanggan & Lokasi</th>
                        <th>No SPB / Vendor</th>
                        <th>Ukuran Tiang</th>
                        <th style="text-align: center;">Butuh</th>
                        <th>No & Tgl WO</th>
                        <th>Kecamatan</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Ambil data dengan JOIN ke tabel wo (untuk mengambil tgl_wo)
                    $sql = "SELECT p.*, k.nomor_kontrak, v.nama_vendor, t.jenis_tiang, u.kecamatan, w.tgl_wo
                            FROM pemesanan p
                            JOIN kontrak k ON p.id_kontrak = k.id_kontrak
                            JOIN vendor v ON k.id_vendor = v.id_vendor
                            JOIN tiang t ON k.id_tiang = t.id_tiang
                            JOIN ulp u ON p.id_ulp = u.id_ulp
                            JOIN wo w ON p.no_wo = w.no_wo
                            $where 
                            ORDER BY p.id_pemesanan DESC 
                            LIMIT $limit OFFSET $offset";

                    $res = mysqli_query($conn, $sql);
                    if(mysqli_num_rows($res) > 0) {
                        while($row = mysqli_fetch_assoc($res)) {
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo $row['nama_pelanggan']; ?></strong><br>
                                    <small style="color:#888;"><?php echo $row['lokasi']; ?></small>
                                </td>
                                <td>
                                    <strong><?php echo $row['nomor_kontrak']; ?></strong><br>
                                    <small style="color:#888;"><?php echo $row['nama_vendor']; ?></small>
                                </td>
                                <td>
                                    <span style="background:#eee; padding:3px 8px; border-radius:4px; font-size:11px;">
                                        <?php echo $row['jenis_tiang']; ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge-kebutuhan"><?php echo $row['kebutuhan']; ?></span>
                                </td>
                                <td>
                                    <strong><?php echo $row['no_wo']; ?></strong><br>
                                    <small style="color:#888;">
                                        <?php echo date('d/m/Y', strtotime($row['tgl_wo'])); ?>
                                    </small>
                                </td>
                                <td><?php echo $row['kecamatan']; ?></td>
                                <td style="text-align: center;" class="action-links">
                                    <a href="edit_pemesanan.php?id=<?php echo $row['id_pemesanan']; ?>" style="color:var(--pln-blue);">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="pemesanan.php?delete_id=<?php echo $row['id_pemesanan']; ?>" 
                                       style="color:#e74c3c; margin-left:12px;" 
                                       onclick="return confirm('Hapus pemesanan ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align:center; padding:40px; color:#999;'>Belum ada data pemesanan.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="pagination-container">
            <a href="?page=<?php echo ($page > 1) ? ($page - 1) : 1; ?>&filter_vendor=<?php echo $filter_vendor; ?>&filter_tiang=<?php echo $filter_tiang; ?>" 
               class="page-link <?php if($page <= 1) echo 'disabled'; ?>"><i class="fas fa-chevron-left"></i></a>
            
            <span style="font-size: 13px; color: #666;">Halaman <b><?php echo $page; ?></b> dari <?php echo $total_pages; ?></span>

            <a href="?page=<?php echo ($page < $total_pages) ? ($page + 1) : $total_pages; ?>&filter_vendor=<?php echo $filter_vendor; ?>&filter_tiang=<?php echo $filter_tiang; ?>" 
               class="page-link <?php if($page >= $total_pages) echo 'disabled'; ?>"><i class="fas fa-chevron-right"></i></a>
        </div>
    </main>

    <script>
        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('expanded'); }
    </script>
</body>
</html>