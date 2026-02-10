<?php 
include 'koneksi.php'; 

// Proses Simpan Data Pemesanan
if (isset($_POST['save'])) {
    $id_kontrak     = $_POST['id_kontrak'];
    $nama_pelanggan = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
    $lokasi         = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $kebutuhan      = $_POST['kebutuhan'];
    $ket_kuota      = mysqli_real_escape_string($conn, $_POST['ket_kuota']);
    $no_wo          = mysqli_real_escape_string($conn, $_POST['no_wo']);
    $tgl_wo         = $_POST['tgl_wo'];
    $id_ulp         = $_POST['id_ulp'];

    $query = "INSERT INTO pemesanan (id_kontrak, nama_pelanggan, lokasi, kebutuhan, ket_kuota, no_wo, tgl_wo, id_ulp) 
              VALUES ('$id_kontrak', '$nama_pelanggan', '$lokasi', '$kebutuhan', '$ket_kuota', '$no_wo', '$tgl_wo', '$id_ulp')";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data Pemesanan Berhasil Disimpan!'); window.location='pemesanan.php';</script>";
    } else {
        echo "<script>alert('Gagal: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Pemesanan Tiang - PLN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --pln-blue: #00A3E0; --pln-yellow: #FFD100; --bg-gray: #f4f7f9; }
        body { background-color: var(--bg-gray); font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; justify-content: center; padding: 40px 20px; }
        
        .form-card {
            background: white;
            width: 100%;
            max-width: 700px;
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-top: 10px solid var(--pln-blue);
        }

        .header-form { text-align: center; margin-bottom: 30px; }
        .header-form h2 { margin: 0; color: var(--pln-blue); }

        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 8px; font-weight: 700; color: #555; font-size: 12px; text-transform: uppercase; }
        
        .form-control {
            width: 100%; padding: 12px; border: 2px solid #eee; border-radius: 10px; 
            font-size: 14px; outline: none; box-sizing: border-box; transition: 0.3s;
        }
        .form-control:focus { border-color: var(--pln-blue); }

        .btn-save {
            background: var(--pln-blue); color: white; border: none; width: 100%; 
            padding: 16px; border-radius: 12px; font-size: 16px; font-weight: 800; 
            cursor: pointer; margin-top: 20px;
        }
        .btn-save:hover { background: #008cc1; }

        .btn-cancel {
            display: block; text-align: center; margin-top: 15px; color: #888; 
            text-decoration: none; font-weight: 600; font-size: 14px;
        }
    </style>
</head>
<body>

<div class="form-card">
    <div class="header-form">
        <img src="logo_PLN.png" alt="Logo PLN" style="height: 50px; margin-bottom: 10px;">
        <h2>Form Pemesanan Baru</h2>
    </div>

    <form action="" method="POST">
        <div class="form-group">
            <label>Pilih Nomor SPB / Vendor</label>
            <select name="id_kontrak" class="form-control" required>
                <option value="">-- Pilih SPB --</option>
                <?php
                $q_kontrak = mysqli_query($conn, "SELECT k.id_kontrak, k.nomor_kontrak, v.nama_vendor, t.jenis_tiang 
                                                  FROM kontrak k 
                                                  JOIN vendor v ON k.id_vendor = v.id_vendor
                                                  JOIN tiang t ON k.id_tiang = t.id_tiang");
                while($row = mysqli_fetch_assoc($q_kontrak)) {
                    echo "<option value='".$row['id_kontrak']."'>".$row['nomor_kontrak']." (".$row['nama_vendor'].") - ".$row['jenis_tiang']."</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label>Nama Pelanggan</label>
            <input type="text" name="nama_pelanggan" class="form-control" placeholder="Masukkan nama pelanggan" required>
        </div>

        <div class="form-group">
            <label>Lokasi Pemasangan</label>
            <textarea name="lokasi" class="form-control" rows="2" placeholder="Alamat lengkap lokasi" required></textarea>
        </div>

        <div style="display: flex; gap: 20px;">
            <div class="form-group" style="flex: 1;">
                <label>Nomor WO</label>
                <input type="text" name="no_wo" class="form-control" placeholder="Contoh: 0000/DAN..." required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Tanggal WO</label>
                <input type="date" name="tgl_wo" class="form-control" required>
            </div>
        </div>

        <div style="display: flex; gap: 20px;">
            <div class="form-group" style="flex: 1;">
                <label>Kebutuhan (Batang)</label>
                <input type="number" name="kebutuhan" class="form-control" placeholder="Jml" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Kecamatan (ULP)</label>
                <select name="id_ulp" class="form-control" required>
                    <option value="">-- Pilih --</option>
                    <?php
                    $q_ulp = mysqli_query($conn, "SELECT * FROM ulp");
                    while($u = mysqli_fetch_assoc($q_ulp)) {
                        echo "<option value='".$u['id_ulp']."'>".$u['kecamatan']."</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Keterangan Kuota</label>
            <input type="text" name="ket_kuota" class="form-control" placeholder="Contoh: Ambil dari gudang proyek">
        </div>

        <button type="submit" name="save" class="btn-save">
            <i class="fas fa-check-circle"></i> SIMPAN PEMESANAN
        </button>
        <a href="pemesanan.php" class="btn-cancel">Batal</a>
    </form>
</div>

</body>
</html>