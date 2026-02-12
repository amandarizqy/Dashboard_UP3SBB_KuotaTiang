<?php 
include 'koneksi.php'; 

// 1. Proses Simpan WO Baru (Tanpa Tanggal)
if (isset($_POST['save_wo'])) {
    $no_wo = mysqli_real_escape_string($conn, $_POST['no_wo']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi_wo']);

    // Cek apakah no_wo sudah ada
    $cek = mysqli_query($conn, "SELECT no_wo FROM wo WHERE no_wo = '$no_wo'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Nomor WO sudah terdaftar di Master!');</script>";
    } else {
        // Query disesuaikan: Menghapus kolom tgl_wo
        $query = "INSERT INTO wo (no_wo, deskripsi_wo) VALUES ('$no_wo', '$deskripsi')";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Master WO Berhasil Ditambahkan!'); window.location='kelola_wo.php';</script>";
        }
    }
}

// 2. Proses Hapus WO
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    mysqli_query($conn, "DELETE FROM wo WHERE no_wo = '$id'");
    header("Location: kelola_wo.php");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Master WO - PLN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --pln-blue: #00A3E0; --pln-yellow: #FFD100; --bg-gray: #f4f7f9; }
        body { background-color: var(--bg-gray); font-family: 'Segoe UI', sans-serif; margin: 0; padding: 40px; }
        
        .container { max-width: 1000px; margin: auto; display: grid; grid-template-columns: 1fr 2fr; gap: 30px; }
        
        .card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); height: fit-content; }
        .card h3 { margin-top: 0; color: var(--pln-blue); border-bottom: 2px solid var(--pln-yellow); padding-bottom: 10px; }
        
        .form-group { margin-bottom: 15px; }
        label { display: block; font-size: 11px; font-weight: bold; margin-bottom: 5px; color: #666; text-transform: uppercase; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 14px; }
        
        .btn-primary { background: var(--pln-blue); color: white; border: none; width: 100%; padding: 12px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-primary:hover { background: #008cc1; }

        table { width: 100%; border-collapse: collapse; background: white; border-radius: 15px; overflow: hidden; }
        th { background: #f8f9fa; padding: 15px; text-align: left; font-size: 12px; color: #777; border-bottom: 2px solid #eee; }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; }
        .btn-delete { color: #e74c3c; text-decoration: none; font-size: 16px; transition: 0.2s; }
        .btn-delete:hover { color: #c0392b; }
        
        .header-nav { margin-bottom: 20px; }
        .btn-back { text-decoration: none; color: var(--pln-blue); font-weight: bold; font-size: 14px; }
        .badge-info { background: #e1f5fe; color: #01579b; padding: 4px 8px; border-radius: 5px; font-size: 11px; font-weight: bold; }
    </style>
</head>
<body>

<div class="header-nav">
    <a href="pemesanan.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali ke Riwayat Pemesanan</a>
</div>

<div class="container">
    <div class="card">
        <h3>Registrasi WO</h3>
        <p style="font-size: 12px; color: #888;">Daftarkan nomor WO master di sini agar bisa dipilih saat input pesanan.</p>
        <form action="" method="POST">
            <div class="form-group">
                <label>Nomor Work Order (WO)</label>
                <input type="text" name="no_wo" class="form-control" placeholder="Contoh: 54321/DAN.01..." required>
            </div>
            <div class="form-group">
                <label>Deskripsi / Peruntukan</label>
                <textarea name="deskripsi_wo" class="form-control" rows="4" placeholder="Contoh: Proyek Perumahan A atau Nama Vendor..."></textarea>
            </div>
            <button type="submit" name="save_wo" class="btn-primary">
                <i class="fas fa-save"></i> SIMPAN MASTER WO
            </button>
        </form>
    </div>

    <div class="card shadow">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="margin:0;">Master Data WO</h3>
            <span class="badge-info">Total WO Terdaftar</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th width="40%">Nomor WO</th>
                    <th width="50%">Keterangan</th>
                    <th width="10%" style="text-align:center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = mysqli_query($conn, "SELECT * FROM wo ORDER BY no_wo ASC");
                if (mysqli_num_rows($query) > 0) {
                    while ($row = mysqli_fetch_assoc($query)) {
                        ?>
                        <tr>
                            <td><strong><?php echo $row['no_wo']; ?></strong></td>
                            <td><span style="color:#666; font-size:13px;"><?php echo $row['deskripsi_wo'] ?: '-'; ?></span></td>
                            <td align="center">
                                <a href="?delete=<?php echo urlencode($row['no_wo']); ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Hapus master WO ini? Seluruh riwayat pemesanan dengan WO ini mungkin akan terdampak.')">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='3' align='center' style='padding:30px; color:#999;'>Belum ada Master WO yang didaftarkan.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>