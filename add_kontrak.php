<?php 
include 'koneksi.php';

// Proses Simpan Data
if (isset($_POST['save'])) {
    $nomor_kontrak = mysqli_real_escape_string($conn, $_POST['nomor_kontrak']);
    $tgl_terbit    = $_POST['tanggal_terbit'];
    $tgl_tenggat   = $_POST['akhir_tenggat'];
    $id_tiang      = $_POST['id_tiang'];
    $id_vendor     = $_POST['id_vendor'];
    $kuota         = $_POST['kuota'];

    $query = "INSERT INTO kontrak (nomor_kontrak, tanggal_terbit, akhir_tenggat, id_tiang, id_vendor, kuota) 
              VALUES ('$nomor_kontrak', '$tgl_terbit', '$tgl_tenggat', '$id_tiang', '$id_vendor', '$kuota')";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data Kontrak Berhasil Ditambahkan!'); window.location='manage.php?table=kontrak';</script>";
    } else {
        echo "<script>alert('Gagal: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Kontrak Baru - PLN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --pln-blue: #00A3E0; --pln-yellow: #FFD100; --bg-gray: #f4f7f9; }
        body { background-color: var(--bg-gray); font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; justify-content: center; padding: 50px 20px; }
        
        .form-card {
            background: white;
            width: 100%;
            max-width: 650px;
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            border-top: 10px solid var(--pln-blue);
        }

        .header-form { text-align: center; margin-bottom: 35px; }
        .header-form img { height: 60px; margin-bottom: 15px; }
        .header-form h2 { margin: 0; color: var(--pln-blue); font-size: 24px; }

        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 700; color: #444; font-size: 13px; text-transform: uppercase; }
        
        .form-control {
            width: 100%;
            padding: 14px;
            border: 2px solid #eee;
            border-radius: 12px;
            font-size: 15px;
            outline: none;
            box-sizing: border-box;
            transition: 0.3s;
        }
        .form-control:focus { border-color: var(--pln-blue); background: #fcfdfe; }

        .btn-submit {
            background: var(--pln-blue);
            color: white;
            border: none;
            width: 100%;
            padding: 16px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            margin-top: 10px;
            transition: 0.3s;
        }
        .btn-submit:hover { background: #008cc1; transform: translateY(-2px); }
        
        .btn-cancel {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #888;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="form-card">
    <div class="header-form">
        <img src="logo_PLN.png" alt="Logo PLN">
        <h2>Input Kontrak Baru</h2>
        <p style="color:#888; font-size: 14px;">Masukkan rincian Surat Pesanan Barang (SPB) Vendor</p>
    </div>

    <form action="" method="POST">
        <div class="form-group">
            <label>Pilih Vendor</label>
            <select name="id_vendor" class="form-control" required>
                <option value="">-- Pilih Vendor --</option>
                <?php
                $v_query = mysqli_query($conn, "SELECT * FROM vendor ORDER BY nama_vendor ASC");
                while($v = mysqli_fetch_assoc($v_query)) {
                    echo "<option value='".$v['id_vendor']."'>".$v['nama_vendor']."</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label>Spesifikasi Tiang</label>
            <select name="id_tiang" class="form-control" required>
                <option value="">-- Pilih Jenis Tiang --</option>
                <?php
                $t_query = mysqli_query($conn, "SELECT * FROM tiang");
                while($t = mysqli_fetch_assoc($t_query)) {
                    echo "<option value='".$t['id_tiang']."'>".$t['jenis_tiang']."</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label>Nomor Kontrak / SPB</label>
            <input type="text" name="nomor_kontrak" class="form-control" placeholder="Contoh: 1241 .PJ/DAN.01.01/..." required>
        </div>

        <div style="display: flex; gap: 20px;">
            <div class="form-group" style="flex: 1;">
                <label>Tanggal Terbit</label>
                <input id="tanggal_terbit" type="date" name="tanggal_terbit" class="form-control" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Batas Berlaku</label>
                <input id="akhir_tenggat" type="date" name="akhir_tenggat" class="form-control" required>
            </div>
        </div>

        <div class="form-group">
            <label>Jumlah Kuota (Batang)</label>
            <input type="number" name="kuota" class="form-control" placeholder="Masukkan jumlah kuota" required>
        </div>

        <button type="submit" name="save" class="btn-submit">
            <i class="fas fa-save"></i> SIMPAN KONTRAK
        </button>
        <a href="manage.php?table=kontrak" class="btn-cancel">Batal dan Kembali</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tInput = document.getElementById('tanggal_terbit');
    const endInput = document.getElementById('akhir_tenggat');

    function toYMD(d) {
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        return yyyy + '-' + mm + '-' + dd;
    }

    function addDays(dateObj, days) {
        const d = new Date(dateObj.getTime());
        d.setDate(d.getDate() + days);
        return d;
    }

    function updateEnd() {
        if (!tInput.value) return;
        const dt = new Date(tInput.value + 'T00:00:00');
        if (isNaN(dt)) return;
        const end = addDays(dt, 60);
        endInput.value = toYMD(end);
    }

    tInput.addEventListener('change', updateEnd);
    tInput.addEventListener('input', updateEnd);

    // Jika form diisi ulang (mis. back), set otomatis
    if (tInput.value && !endInput.value) {
        updateEnd();
    }
});
</script>

</body>
</html>