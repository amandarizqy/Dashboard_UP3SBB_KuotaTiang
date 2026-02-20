<?php 
include 'koneksi.php'; 

// 1. LOGIKA HAPUS DATA
if (isset($_GET['delete_id'])) {
    $del_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM pemesanan WHERE id_pemesanan = '$del_id'");
    header("Location: pemesanan.php");
    exit;
}

// 2. LOGIKA FILTER, SEARCH & PAGINATION
$filter_vendor = isset($_GET['filter_vendor']) ? $_GET['filter_vendor'] : '';
$filter_tiang  = isset($_GET['filter_tiang']) ? $_GET['filter_tiang'] : '';
$search_kontrak = isset($_GET['search_kontrak']) ? mysqli_real_escape_string($conn, $_GET['search_kontrak']) : '';

$conditions = [];
if ($filter_vendor != '') $conditions[] = "k.id_vendor = '$filter_vendor'";
if ($filter_tiang != '') $conditions[] = "k.id_tiang = '$filter_tiang'";
if ($search_kontrak != '') $conditions[] = "k.nomor_kontrak LIKE '%$search_kontrak%'";

$where = count($conditions) > 0 ? " WHERE " . implode(' AND ', $conditions) : "";

// --- PAGINATION SEDERHANA ---
$limit = 10;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$count_query = "SELECT COUNT(*) as total FROM pemesanan p JOIN kontrak k ON p.id_kontrak = k.id_kontrak $where";
$count_res = mysqli_query($conn, $count_query);
$total_data = mysqli_fetch_assoc($count_res)['total'];
$total_pages = ceil($total_data / $limit);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pemesanan & Export WO - PLN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="stylepemesanan.css">
    <style>
        .search-box { padding: 10px; border-radius: 8px; border: 1px solid #ddd; width: 220px; font-size: 14px; }
        .btn-export { background: #27ae60; color: white; padding: 10px 18px; border-radius: 8px; border: none; cursor: pointer; display: none; font-weight: bold; }
        .btn-add-new { background: var(--pln-blue); color: white; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-weight: bold; display: inline-flex; align-items: center; gap: 8px; }
        
        .modal-wo { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); }
        .modal-content { background: white; margin: 10% auto; padding: 25px; border-radius: 15px; width: 450px; box-shadow: 0 5px 20px rgba(0,0,0,0.2); }
        
        .selected-row { background-color: #e3f2fd !important; }
        
        /* Pagination Sederhana */
        .pagination-simple { display: flex; justify-content: space-between; align-items: center; margin-top: 25px; padding: 15px; background: white; border-radius: 12px; }
        .btn-nav { padding: 8px 20px; background: #eee; color: #333; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; border: 1px solid #ddd; }
        .btn-nav:hover { background: #ddd; }
        .btn-nav.disabled { opacity: 0.5; pointer-events: none; }
    </style>
</head>
<body>
    <nav id="sidebar">
        <div class="sidebar-header"><img src="logo_PLN.png" alt="Logo PLN" class="logo-sidebar"></div>
        <div class="nav-item" onclick="toggleSidebar()"><i class="fas fa-bars"></i><span>Menu</span></div>
        <a href="dashboard.php" class="nav-item"><i class="fas fa-home"></i><span>Dashboard</span></a>
        <a href="menu.php" class="nav-item"><i class="fas fa-database"></i><span>Master Data</span></a>
        <a href="pemesanan.php" class="nav-item"><i class="fas fa-shopping-cart"></i><span>Pemesanan</span></a>
        <a href="histori.php" class="nav-item"><i class="fas fa-history"></i><span>History</span></a>
    </nav>
    
    <main id="main-content">
        <div class="header-box">
            <div class="header-top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h2 style="margin:0; color:var(--pln-blue);">Pemesanan & Export</h2>
                    <small style="color: #666;">Centang baris dengan kontrak yang sama untuk export WO baru</small>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <button type="button" id="btnExport" class="btn-export" onclick="openModal()">
                        <i class="fas fa-file-excel"></i> Export (<span id="countCheck">0</span>)
                    </button>
                    <a href="add_pemesanan.php" class="btn-add-new">
                        <i class="fas fa-plus-circle"></i> Tambah Pesanan
                    </a>
                </div>
            </div>

            <form action="" method="GET" class="filter-section" style="display: flex; gap: 10px;">
                <input type="text" name="search_kontrak" class="search-box" placeholder="Cari No. Kontrak..." value="<?php echo htmlspecialchars($search_kontrak); ?>">
                <select name="filter_vendor" class="search-box" style="width: 150px;" onchange="this.form.submit()">
                    <option value="">-- Vendor --</option>
                    <?php 
                    $v_query = mysqli_query($conn, "SELECT * FROM vendor");
                    while($v = mysqli_fetch_assoc($v_query)){
                        $sel = ($filter_vendor == $v['id_vendor']) ? 'selected' : '';
                        echo "<option value='".$v['id_vendor']."' $sel>".$v['nama_vendor']."</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="btn-add-new" style="background: #555; height: 40px; padding: 0 15px;"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <form id="formExport" action="export_pemesanan.php" method="POST">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th width="30"></th>
                            <th>Nama Pelanggan</th>
                            <th>No SPB</th>
                            <th>Ukuran</th>
                            <th>Butuh</th>
                            <th>WO Saat Ini</th>
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT p.*, k.nomor_kontrak, v.nama_vendor, t.jenis_tiang
                                FROM pemesanan p
                                JOIN kontrak k ON p.id_kontrak = k.id_kontrak
                                JOIN vendor v ON k.id_vendor = v.id_vendor
                                JOIN tiang t ON k.id_tiang = t.id_tiang
                                $where ORDER BY p.id_pemesanan DESC LIMIT $limit OFFSET $offset";
                        $res = mysqli_query($conn, $sql);
                        while($row = mysqli_fetch_assoc($res)) : ?>
                            <tr class="row-item" data-kontrak="<?php echo $row['id_kontrak']; ?>">
                                <td align="center">
                                    <input type="checkbox" name="selected_id[]" value="<?php echo $row['id_pemesanan']; ?>" 
                                           class="cb-child" onchange="validateSelection(this)">
                                </td>
                                <td><strong><?php echo $row['nama_pelanggan']; ?></strong></td>
                                <td><?php echo $row['nomor_kontrak']; ?></td>
                                <td><?php echo $row['jenis_tiang']; ?></td>
                                <td align="center"><?php echo $row['kebutuhan']; ?></td>
                                <td><?php echo $row['sub_wo']."/".$row['no_wo']; ?></td>
                                <td align="center">
                                    <a href="edit_pemesanan.php?id=<?php echo $row['id_pemesanan']; ?>" style="color:var(--pln-blue); margin-right: 10px;"><i class="fas fa-edit"></i></a>
                                    <a href="?delete_id=<?php echo $row['id_pemesanan']; ?>" style="color:red;" onclick="return confirm('Hapus data ini?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination-simple">
                <a href="?page=<?php echo ($page > 1) ? ($page - 1) : 1; ?>&search_kontrak=<?php echo $search_kontrak; ?>&filter_vendor=<?php echo $filter_vendor; ?>" 
                   class="btn-nav <?php if($page <= 1) echo 'disabled'; ?>"><i class="fas fa-arrow-left"></i> Sebelumnya</a>
                
                <span style="font-weight: bold; color: #555;">Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?></span>

                <a href="?page=<?php echo ($page < $total_pages) ? ($page + 1) : $total_pages; ?>&search_kontrak=<?php echo $search_kontrak; ?>&filter_vendor=<?php echo $filter_vendor; ?>" 
                   class="btn-nav <?php if($page >= $total_pages) echo 'disabled'; ?>">Selanjutnya <i class="fas fa-arrow-right"></i></a>
            </div>

            <div id="modalWO" class="modal-wo">
                <div class="modal-content">
                    <h3 style="margin-top:0;">Input Data WO Baru</h3>
                    <p style="font-size: 13px; color: #666;">Data ini akan digunakan untuk laporan Excel yang Anda download.</p>
                    <hr>
                    <div style="margin: 15px 0;">
                        <label style="display:block; font-weight:bold; margin-bottom:5px;">Sub WO:</label>
                        <input type="text" name="new_sub_wo" class="search-box" style="width: 100%;" placeholder="Ketik Sub WO..." required>
                    </div>
                    <div style="margin: 15px 0;">
                        <label style="display:block; font-weight:bold; margin-bottom:5px;">Master WO:</label>
                        <select name="new_no_wo" class="search-box" style="width: 100%;" required>
                            <?php 
                            $wo_m = mysqli_query($conn, "SELECT * FROM wo");
                            while($wm = mysqli_fetch_assoc($wo_m)) echo "<option value='".$wm['no_wo']."'>".$wm['no_wo']."</option>";
                            ?>
                        </select>
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                        <button type="button" onclick="closeModal()" style="padding: 10px 20px; border: none; border-radius: 8px; cursor:pointer;">Batal</button>
                        <button type="submit" class="btn-add-new" onclick="setTimeout(()=> location.reload(), 1000)">Download Excel</button>
                    </div>
                </div>
            </div>
        </form>
    </main>

    <script>
        let currentKontrakId = null;
        function validateSelection(cb) {
            const checkboxes = document.querySelectorAll('.cb-child:checked');
            const row = cb.closest('tr');
            const kontrakId = row.getAttribute('data-kontrak');
            if (checkboxes.length === 1) currentKontrakId = kontrakId;
            if (cb.checked) {
                if (currentKontrakId && kontrakId !== currentKontrakId) {
                    alert("Peringatan: Anda hanya bisa memilih baris dengan Nomor Kontrak (SPB) yang sama!");
                    cb.checked = false;
                } else { row.classList.add('selected-row'); }
            } else {
                row.classList.remove('selected-row');
                if (checkboxes.length === 0) currentKontrakId = null;
            }
            const btn = document.getElementById('btnExport');
            const countSpan = document.getElementById('countCheck');
            countSpan.innerText = checkboxes.length;
            btn.style.display = checkboxes.length > 0 ? 'block' : 'none';
        }
        function openModal() { document.getElementById('modalWO').style.display = 'block'; }
        function closeModal() { document.getElementById('modalWO').style.display = 'none'; }
        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('expanded'); }
    </script>
</body>
</html>