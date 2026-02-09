<?php 
include 'koneksi.php'; 

// 1. Ambil ID dari URL
$id = isset($_GET['id']) ? $_GET['id'] : '';

if (!$id) {
    header("Location: dashboard.php");
    exit;
}

// 2. Ambil data kontrak lama untuk ditampilkan di form
$query = mysqli_query($conn, "SELECT k.*, v.nama_vendor, t.jenis_tiang 
                             FROM kontrak k 
                             JOIN vendor v ON k.id_vendor = v.id_vendor 
                             JOIN tiang t ON k.id_tiang = t.id_tiang 
                             WHERE k.id_kontrak = '$id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "Data tidak ditemukan!";
    exit;
}

// 3. Proses Update Data saat tombol simpan ditekan
if (isset($_POST['update'])) {
    $nomor_kontrak = $_POST['nomor_kontrak'];
    $tgl_terbit    = $_POST['tanggal_terbit'];
    $tgl_tenggat   = $_POST['akhir_tenggat'];
    $kuota         = $_POST['kuota'];

    $update = mysqli_query($conn, "UPDATE kontrak SET 
                                    nomor_kontrak = '$nomor_kontrak', 
                                    tanggal_terbit = '$tgl_terbit', 
                                    akhir_tenggat = '$tgl_tenggat', 
                                    kuota = '$kuota' 
                                   WHERE id_kontrak = '$id'");

    if ($update) {
        echo "<script>alert('Data Berhasil Diperbarui!'); window.location='dashboard.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kontrak - PLN Monitoring</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --pln-blue: #00A3E0;
            --pln-yellow: #FFD100;
            --bg-gray: #f4f7f9;
        }

        body { background-color: var(--bg-gray); margin: 0; font-family: 'Segoe UI', sans-serif; }

        #main-content { padding: 40px; display: flex; justify-content: center; }

        /* Form Card */
        .edit-card {
            background: white;
            width: 100%;
            max-width: 700px;
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-top: 8px solid var(--pln-blue);
        }

        .edit-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 20px;
        }

        .edit-header i { font-size: 2.5rem; color: var(--pln-blue); }
        .edit-header h2 { margin: 0; color: #333; font-size: 1.8rem; }

        /* Form Styling */
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 700; color: #555; text-transform: uppercase; font-size: 0.8rem; }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #eee;
            border-radius: 10px;
            font-size: 1rem;
            outline: none;
            transition: 0.3s;
            box-sizing: border-box;
        }

        .form-control:focus { border-color: var(--pln-blue); }
        .form-control[readonly] { background-color: #f9f9f9; color: #888; cursor: not-allowed; }

        .btn-row { display: flex; gap: 15px; margin-top: 30px; }

        .btn-save {
            background-color: var(--pln-blue);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            flex: 2;
            transition: 0.3s;
        }

        .btn-save:hover { background-color: #008cc1; transform: translateY(-2px); }

        .btn-back {
            background-color: #eee;
            color: #666;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 12px;
            font-weight: 700;
            text-align: center;
            flex: 1;
            transition: 0.3s;
        }

        .btn-back:hover { background-color: #ddd; }

        .info-tag {
            display: inline-block;
            background: #f0f7ff;
            color: var(--pln-blue);
            padding: 5px 12px;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 5px;
        }
    </style>
</head>
<body>

<div id="main-content">
    <div class="edit-card">
        <div class="edit-header">
            <i class="fas fa-file-signature"></i>
            <div>
                <h2>Edit Data Kontrak</h2>
                <span class="info-tag"><?php echo $data['nama_vendor']; ?></span>
            </div>
        </div>

        <form action="" method="POST">
            <div class="form-group">
                <label>Nama Vendor (Master Data)</label>
                <input type="text" class="form-control" value="<?php echo $data['nama_vendor']; ?>" readonly>
            </div>

            <div class="form-group">
                <label>Jenis Tiang (Master Data)</label>
                <input type="text" class="form-control" value="<?php echo $data['jenis_tiang']; ?>" readonly>
            </div>

            <div class="form-group">
                <label>Nomor Kontrak / SPB</label>
                <input type="text" name="nomor_kontrak" class="form-control" value="<?php echo $data['nomor_kontrak']; ?>" required>
            </div>

            <div style="display: flex; gap: 20px;">
                <div class="form-group" style="flex: 1;">
                    <label>Tanggal Terbit</label>
                    <input type="date" name="tanggal_terbit" class="form-control" value="<?php echo $data['tanggal_terbit']; ?>" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Akhir Tenggat (Batas Berlaku)</label>
                    <input type="date" name="akhir_tenggat" class="form-control" value="<?php echo $data['akhir_tenggat']; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Kuota Tiang</label>
                <input type="number" name="kuota" class="form-control" value="<?php echo $data['kuota']; ?>" required>
            </div>

            <div class="btn-row">
                <a href="dashboard.php" class="btn-back">Batal</a>
                <button type="submit" name="update" class="btn-save">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>