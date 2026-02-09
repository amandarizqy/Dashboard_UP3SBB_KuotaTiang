<?php 
include 'koneksi.php'; 

// 1. Ambil Parameter dari URL
$table = isset($_GET['table']) ? $_GET['table'] : 'vendor';
$id    = isset($_GET['id']) ? $_GET['id'] : '';
$id_column = "id_" . $table; // Menyesuaikan nama primary key (id_vendor atau id_tiang)

if (!$id) {
    header("Location: manage.php?table=$table");
    exit;
}

// 2. Tarik Data Lama dari Database
$query = mysqli_query($conn, "SELECT * FROM $table WHERE $id_column = '$id'");
$data  = mysqli_fetch_assoc($query);

if (!$data) {
    echo "Data tidak ditemukan!";
    exit;
}

// 3. Proses Update Data
if (isset($_POST['update'])) {
    if ($table == 'vendor') {
        $nama_vendor = mysqli_real_escape_string($conn, $_POST['nama_vendor']);
        $sql = "UPDATE vendor SET nama_vendor = '$nama_vendor' WHERE id_vendor = '$id'";
    } elseif ($table == 'tiang') {
        $jenis_tiang = mysqli_real_escape_string($conn, $_POST['jenis_tiang']);
        $sql = "UPDATE tiang SET jenis_tiang = '$jenis_tiang' WHERE id_tiang = '$id'";
    }

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Data berhasil diperbarui!'); window.location='manage.php?table=$table';</script>";
    } else {
        echo "<script>alert('Gagal: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Data <?php echo ucfirst($table); ?> - PLN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --pln-blue: #00A3E0; --pln-yellow: #FFD100; --bg-gray: #f4f7f9; }
        body { background-color: var(--bg-gray); font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; justify-content: center; padding: 80px 20px; }
        
        .edit-card {
            background: white;
            width: 100%;
            max-width: 500px;
            padding: 40px;
            border-radius: 30px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.06);
            border-top: 10px solid var(--pln-blue);
            text-align: center;
        }

        .edit-header i { font-size: 3rem; color: var(--pln-blue); margin-bottom: 20px; }
        .edit-header h2 { margin: 0; color: #333; font-size: 24px; }
        .edit-header p { color: #888; font-size: 14px; margin-top: 5px; margin-bottom: 35px; }

        .form-group { text-align: left; margin-bottom: 30px; }
        label { display: block; margin-bottom: 12px; font-weight: 700; color: #666; font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; }
        
        .form-control {
            width: 100%;
            padding: 16px;
            border: 2px solid #eee;
            border-radius: 15px;
            font-size: 16px;
            outline: none;
            box-sizing: border-box;
            transition: 0.3s;
        }
        .form-control:focus { border-color: var(--pln-blue); background-color: #fcfdfe; }

        .btn-update {
            background: var(--pln-blue);
            color: white;
            border: none;
            width: 100%;
            padding: 18px;
            border-radius: 15px;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 8px 20px rgba(0, 163, 224, 0.2);
        }
        .btn-update:hover { background: #008cc1; transform: translateY(-3px); }
        
        .btn-back {
            display: inline-block;
            margin-top: 25px;
            color: #bbb;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: 0.2s;
        }
        .btn-back:hover { color: #e74c3c; }
    </style>
</head>
<body>

<div class="edit-card">
    <div class="edit-header">
        <i class="fas fa-edit"></i>
        <h2>Edit Master <?php echo ucfirst($table); ?></h2>
        <p>ID Data: <span style="color: var(--pln-blue); font-weight: bold;"><?php echo $id; ?></span></p>
    </div>

    <form action="" method="POST">
        <div class="form-group">
            <?php if ($table == 'vendor'): ?>
                <label>Nama Perusahaan Vendor</label>
                <input type="text" name="nama_vendor" class="form-control" value="<?php echo htmlspecialchars($data['nama_vendor']); ?>" required autofocus>
            <?php elseif ($table == 'tiang'): ?>
                <label>Spesifikasi Jenis Tiang</label>
                <input type="text" name="jenis_tiang" class="form-control" value="<?php echo htmlspecialchars($data['jenis_tiang']); ?>" required autofocus>
            <?php endif; ?>
        </div>

        <button type="submit" name="update" class="btn-update">
            <i class="fas fa-save"></i> SIMPAN PERUBAHAN
        </button>
        <a href="manage.php?table=<?php echo $table; ?>" class="btn-back">Batalkan Perubahan</a>
    </form>
</div>

</body>
</html>