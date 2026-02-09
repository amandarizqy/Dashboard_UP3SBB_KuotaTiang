<?php 
include 'koneksi.php'; 

// Logika Hapus Khusus Kontrak
if (isset($_GET['delete_id'])) {
    $del_id = $_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM kontrak WHERE id_kontrak = '$del_id'");
    header("Location: kelola_kontrak.php");
    exit;
}

// Inisialisasi variabel pencarian
$q = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Data Kontrak - PLN</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --pln-blue: #00A3E0; --pln-yellow: #FFD100; }
        body { background-color: #f8f9fa; margin: 0; padding: 20px; font-family: 'Segoe UI', sans-serif; }
        
        .main-wrapper { display: flex; gap: 24px; align-items: flex-start; max-width: 1400px; margin: 0 auto; }

        /* Panel Kiri */
        .left-panel { 
            width: 320px; background: white; padding: 30px; border-radius: 20px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); display: flex; flex-direction: column; 
            gap: 20px; position: sticky; top: 20px;
        }

        .right-panel { flex: 1; min-width: 0; }
        .logo-img { height: 60px; object-fit: contain; margin-bottom: 10px; }
        .back-btn { text-decoration: none; color: var(--pln-blue); font-weight: 600; font-size: 14px; }
        .header-title h2 { margin: 0; color: #333; font-size: 22px; }
        .search-form { display: flex; flex-direction: column; gap: 10px; }
        .search-input { padding: 12px; border: 1px solid #ddd; border-radius: 10px; outline: none; }
        .search-btn { padding: 12px; border-radius: 10px; border: none; background: var(--pln-blue); color: white; font-weight: bold; cursor: pointer; }
        .btn-add { background: var(--pln-yellow); color: #333; padding: 15px; border-radius: 12px; text-decoration: none; font-weight: 800; text-align: center; }

        /* Tabel Khusus Kontrak */
        table { width: 100%; border-collapse: separate; border-spacing: 0 15px; }
        th { padding: 10px 20px; text-align: left; color: #aaa; font-size: 11px; text-transform: uppercase; }
        td { padding: 20px; background: white; vertical-align: middle; border-top: 1px solid #f0f0f0; border-bottom: 1px solid #f0f0f0; }
        tr td:first-child { border-radius: 15px 0 0 15px; border-left: 1px solid #f0f0f0; }
        tr td:last-child { border-radius: 0 15px 15px 0; border-right: 1px solid #f0f0f0; text-align: right; }
        
        .vendor-text { font-weight: bold; color: #333; display: block; }
        .contract-text { font-size: 12px; color: var(--pln-blue); font-family: monospace; }
        .action-btns a { margin-left: 15px; text-decoration: none; font-weight: bold; font-size: 13px; }
        .delete { color: #e74c3c; }
        .edit { color: var(--pln-blue); }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <aside class="left-panel">
            <a href="menu.php" class="back-btn"><i class="fas fa-arrow-left"></i> Kembali ke Menu</a>
            <div style="text-align: center;"><img src="logo_PLN.png" alt="PLN" class="logo-img"></div>
            <div class="header-title">
                <h2>Kelola Kontrak</h2>
                <small style="color:#888;">Manajemen SPB & Kuota Vendor</small>
            </div>
            <form method="GET" class="search-form">
                <input type="text" name="q" class="search-input" placeholder="Cari vendor/nomor..." value="<?php echo htmlspecialchars($q); ?>">
                <button type="submit" class="search-btn"><i class="fas fa-search"></i> CARI</button>
            </form>
            <a href="add_kontrak.php" class="btn-add"><i class="fas fa-plus-circle"></i> TAMBAH KONTRAK BARU</a>
        </aside>

        <section class="right-panel">
            <table>
                <thead>
                    <tr>
                        <th>Vendor & Nomor SPB</th>
                        <th>Jenis Tiang</th>
                        <th>Tanggal Terbit</th>
                        <th>Batas Berlaku</th>
                        <th>Kuota</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Query JOIN untuk mendapatkan NAMA Vendor dan Jenis Tiang
                    $sql = "SELECT k.*, v.nama_vendor, t.jenis_tiang 
                            FROM kontrak k
                            JOIN vendor v ON k.id_vendor = v.id_vendor
                            JOIN tiang t ON k.id_tiang = t.id_tiang";

                    if ($q !== '') {
                        $sql .= " WHERE v.nama_vendor LIKE '%$q%' OR k.nomor_kontrak LIKE '%$q%'";
                    }
                    $sql .= " ORDER BY k.id_kontrak DESC";

                    $result = mysqli_query($conn, $sql);
                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            ?>
                            <tr>
                                <td>
                                    <span class="vendor-text"><?php echo $row['nama_vendor']; ?></span>
                                    <span class="contract-text"><?php echo $row['nomor_kontrak']; ?></span>
                                </td>
                                <td><?php echo $row['jenis_tiang']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal_terbit'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['akhir_tenggat'])); ?></td>
                                <td style="font-weight:bold; color:var(--pln-blue);"><?php echo $row['kuota']; ?></td>
                                <td class="action-btns">
                                    <a href="edit.php?id=<?php echo $row['id_kontrak']; ?>" class="edit"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="kelola_kontrak.php?delete_id=<?php echo $row['id_kontrak']; ?>" class="delete" onclick="return confirm('Hapus kontrak ini?')"><i class="fas fa-trash"></i> Hapus</a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center; padding:50px; color:#999;'>Tidak ada data kontrak ditemukan.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>