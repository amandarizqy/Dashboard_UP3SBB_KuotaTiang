<?php 
include 'koneksi.php'; 

// Mengambil parameter tabel dari URL
$table = isset($_GET['table']) ? $_GET['table'] : 'vendor';

// Proses Simpan Data
if (isset($_POST['save'])) {
    if ($table == 'vendor') {
        $nama_vendor = mysqli_real_escape_string($conn, $_POST['nama_vendor']);
        $query = "INSERT INTO vendor (nama_vendor) VALUES ('$nama_vendor')";
    } elseif ($table == 'tiang') {
        $jenis_tiang = mysqli_real_escape_string($conn, $_POST['jenis_tiang']);
        $query = "INSERT INTO tiang (jenis_tiang) VALUES ('$jenis_tiang')";
    }

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data berhasil ditambahkan!'); window.location='manage.php?table=$table';</script>";
    } else {
        echo "<script>alert('Gagal: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Data <?php echo ucfirst($table); ?> - PLN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --pln-blue: #00A3E0; --pln-yellow: #FFD100; --bg-gray: #f4f7f9; }
        body { background-color: var(--bg-gray); font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; justify-content: center; padding: 80px 20px; }
        
        .form-card {
            background: white;
            width: 100%;
            max-width: 500px;
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            border-top: 10px solid var(--pln-blue);
            text-align: center;
        }

        .header-form i { font-size: 3.5rem; color: var(--pln-blue); margin-bottom: 20px; }
        .header-form h2 { margin: 0; color: #333; font-size: 22px; }
        .header-form p { color: #888; font-size: 14px; margin-top: 5px; margin-bottom: 30px; }

        .form-group { text-align: left; margin-bottom: 25px; }
        label { display: block; margin-bottom: 10px; font-weight: 700; color: #555; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; }
        
        .form-control {
            width: 100%;
            padding: 15px;
            border: 2px solid #eee;
            border-radius: 12px;
            font-size: 16px;
            outline: none;
            box-sizing: border-box;
            transition: 0.3s;
        }
        .form-control:focus { border-color: var(--pln-blue); box-shadow: 0 0 10px rgba(0,163,224,0.1); }

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
            transition: 0.3s;
        }
        .btn-submit:hover { background: #008cc1; transform: translateY(-2px); }
        
        .btn-cancel {
            display: inline-block;
            margin-top: 20px;
            color: #aaa;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }
        .btn-cancel:hover { color: #e74c3c; }
    </style>
</head>
<body>

<div class="form-card">
    <div class="header-form">
        <i class="fas <?php echo ($table == 'vendor') ? 'fa-industry' : 'fa-bolt'; ?>"></i>
        <h2>Tambah Data <?php echo ucfirst($table); ?></h2>
        <p>Silakan isi data master <?php echo $table; ?> di bawah ini.</p>
    </div>

    <form action="" method="POST">
        <div class="form-group">
            <?php if ($table == 'vendor'): ?>
                <label>Nama Perusahaan Vendor</label>
                <input type="text" name="nama_vendor" class="form-control" placeholder="Contoh: PT. Tonggak Ampuh" required autofocus>
            <?php else: ?>
                <label>Spesifikasi Jenis Tiang</label>
                <input type="text" name="jenis_tiang" class="form-control" placeholder="Contoh: 9 Meter 200 daN" required autofocus>
            <?php endif; ?>
        </div>

        <button type="submit" name="save" class="btn-submit">
            <i class="fas fa-check-circle"></i> SIMPAN DATA
        </button>
        <a href="manage.php?table=<?php echo $table; ?>" class="btn-cancel">Batalkan</a>
    </form>
</div>

</body>
</html>