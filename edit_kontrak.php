<?php 
include 'koneksi.php'; 

// 1. Ambil ID dari URL
$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

if (!$id) {
    header("Location: kelola_kontrak.php");
    exit;
}

// 2. Tarik Data Kontrak Lama
$query = mysqli_query($conn, "SELECT * FROM kontrak WHERE id_kontrak = '$id'");
$data  = mysqli_fetch_assoc($query);

if (!$data) {
    echo "Data tidak ditemukan!";
    exit;
}

// 3. Proses Update Data
if (isset($_POST['update'])) {
    $nomor_kontrak = mysqli_real_escape_string($conn, $_POST['nomor_kontrak']);
    $tgl_terbit    = $_POST['tanggal_terbit'];
    $tgl_tenggat   = $_POST['akhir_tenggat'];
    $id_tiang      = $_POST['id_tiang'];
    $id_vendor     = $_POST['id_vendor'];
    $kuota         = $_POST['kuota'];

    $sql = "UPDATE kontrak SET 
            nomor_kontrak = '$nomor_kontrak', 
            tanggal_terbit = '$tgl_terbit', 
            akhir_tenggat = '$tgl_tenggat', 
            id_tiang = '$id_tiang', 
            id_vendor = '$id_vendor', 
            kuota = '$kuota' 
            WHERE id_kontrak = '$id'";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Perubahan Kontrak Berhasil Disimpan!'); window.location='kelola_kontrak.php';</script>";
    } else {
        echo "<script>alert('Gagal: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Kontrak PLN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --pln-blue: #00A3E0; --pln-yellow: #FFD100; --bg-gray: #f4f7f9; }
        body { background-color: var(--bg-gray); font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; justify-content: center; padding: 50px 20px; }
        
        .edit-container {
            background: white;
            width: 100%;
            max-width: 600px;
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-top: 10px solid var(--pln-blue);
        }

        .header { text-align: center; margin-bottom: 30px; }
        .header h2 { color: var(--pln-blue); margin: 0; font-size: 24px; }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 700; color: #555; font-size: 12px; text-transform: uppercase; }
        
        .form-control {
            width: 100%; padding: 14px; border: 2px solid #eee; border-radius: 12px; 
            font-size: 15px; outline: none; box-sizing: border-box; transition: 0.3s;
        }
        .form-control:focus { border-color: var(--pln-blue); }

        .btn-update {
            background: var(--pln-blue); color: white; border: none; width: 100%; 
            padding: 16px; border-radius: 12px; font-size: 16px; font-weight: 800; 
            cursor: pointer; margin-top: 10px; transition: 0.3s;
        }
        .btn-update:hover { background: #008cc1; }

        .btn-cancel {
            display: block; text-align: center; margin-top: 15px; color: #888; 
            text-decoration: none; font-weight: 600; font-size: 14px;
        }
        .btn-cancel:hover { color: #e74c3c; }
    </style>
</head>
<body>

<div class="edit-container">
    <div class="header">
        <img src="logo_PLN.png" alt="Logo PLN" style="height: 50px; margin-bottom: 15px;">
        <h2>Edit Data Kontrak</h2>
        <p style="color: #888; font-size: 13px;">ID Kontrak: <?php echo $id; ?></p>
    </div>

    <form action="" method="POST">
        <div class="form-group">
            <label>Vendor Pelaksana</label>
            <select name="id_vendor" class="form-control" required>
                <?php
                $v_query = mysqli_query($conn, "SELECT * FROM vendor ORDER BY nama_vendor ASC");
                while($v = mysqli_fetch_assoc($v_query)) {
                    $selected = ($v['id_vendor'] == $data['id_vendor']) ? 'selected' : '';
                    echo "<option value='".$v['id_vendor']."' $selected>".$v['nama_vendor']."</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label>Jenis Tiang</label>
            <select name="id_tiang" class="form-control" required>
                <?php
                $t_query = mysqli_query($conn, "SELECT * FROM tiang");
                while($t = mysqli_fetch_assoc($t_query)) {
                    $selected = ($t['id_tiang'] == $data['id_tiang']) ? 'selected' : '';
                    echo "<option value='".$t['id_tiang']."' $selected>".$t['jenis_tiang']."</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label>Nomor Kontrak / SPB</label>
            <input type="text" name="nomor_kontrak" class="form-control" value="<?php echo htmlspecialchars($data['nomor_kontrak']); ?>" required>
        </div>

        <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
                <label>Tanggal Terbit</label>
                <input type="date" name="tanggal_terbit" class="form-control" value="<?php echo $data['tanggal_terbit']; ?>" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Batas Berlaku</label>
                <input type="date" name="akhir_tenggat" class="form-control" value="<?php echo $data['akhir_tenggat']; ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label>Kuota (Batang)</label>
            <input type="number" name="kuota" class="form-control" value="<?php echo $data['kuota']; ?>" required>
        </div>

        <button type="submit" name="update" class="btn-update">
            <i class="fas fa-save"></i> SIMPAN PERUBAHAN
        </button>
        <a href="kelola_kontrak.php" class="btn-cancel">Batal dan Kembali</a>
    </form>
</div>

</body>
</html>