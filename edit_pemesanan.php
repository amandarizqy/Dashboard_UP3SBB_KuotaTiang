<?php 
include 'koneksi.php'; 

// 1. Ambil ID Pemesanan dari URL
$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

if (!$id) {
    header("Location: pemesanan.php");
    exit;
}

// 2. Ambil Data Lama Pemesanan
$query_lama = mysqli_query($conn, "SELECT * FROM pemesanan WHERE id_pemesanan = '$id'");
$data = mysqli_fetch_assoc($query_lama);

if (!$data) {
    echo "Data tidak ditemukan!";
    exit;
}

// 3. Proses Update Data saat tombol ditekan
if (isset($_POST['update'])) {
    $id_kontrak     = $_POST['id_kontrak'];
    $nama_pelanggan = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
    $lokasi         = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $kebutuhan      = $_POST['kebutuhan'];
    $ket_kuota      = mysqli_real_escape_string($conn, $_POST['ket_kuota']);
    $sub_wo         = mysqli_real_escape_string($conn, $_POST['sub_wo']); // Tambahkan sub_wo
    $no_wo          = mysqli_real_escape_string($conn, $_POST['no_wo']);  // Master WO
    $tgl_wo         = $_POST['tgl_wo'];
    $id_ulp         = $_POST['id_ulp'];

    $sql_update = "UPDATE pemesanan SET 
                    id_kontrak = '$id_kontrak', 
                    nama_pelanggan = '$nama_pelanggan', 
                    lokasi = '$lokasi', 
                    kebutuhan = '$kebutuhan', 
                    ket_kuota = '$ket_kuota', 
                    sub_wo = '$sub_wo', 
                    no_wo = '$no_wo', 
                    tgl_wo = '$tgl_wo', 
                    id_ulp = '$id_ulp' 
                   WHERE id_pemesanan = '$id'";

    if (mysqli_query($conn, $sql_update)) {
        echo "<script>alert('Data Pemesanan Berhasil Diperbarui!'); window.location='pemesanan.php';</script>";
    } else {
        echo "<script>alert('Gagal: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Pemesanan - PLN Monitoring</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        :root { --pln-blue: #00A3E0; --pln-yellow: #FFD100; --bg-gray: #f4f7f9; }
        body { background-color: var(--bg-gray); font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; justify-content: center; padding: 40px 20px; }
        
        .edit-card {
            background: white; width: 100%; max-width: 700px; padding: 40px;
            border-radius: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-top: 10px solid var(--pln-blue);
        }

        .header { text-align: center; margin-bottom: 30px; }
        .header h2 { color: var(--pln-blue); margin: 0; }

        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 8px; font-weight: 700; color: #555; font-size: 12px; text-transform: uppercase; }
        
        .form-control {
            width: 100%; padding: 12px; border: 2px solid #eee; border-radius: 10px; 
            font-size: 14px; outline: none; box-sizing: border-box; transition: 0.3s;
            height: 48px;
        }

        .select2-container--default .select2-selection--single {
            height: 48px !important; border: 2px solid #eee !important; border-radius: 10px !important; padding-top: 10px;
        }

        .btn-update {
            background: var(--pln-blue); color: white; border: none; width: 100%; 
            padding: 16px; border-radius: 12px; font-size: 16px; font-weight: 800; 
            cursor: pointer; margin-top: 20px; transition: 0.3s;
        }
        .btn-update:hover { background: #008cc1; }

        .btn-cancel {
            display: block; text-align: center; margin-top: 15px; color: #888; 
            text-decoration: none; font-weight: 600; font-size: 14px;
        }
    </style>
</head>
<body>

<div class="edit-card">
    <div class="header">
        <h2>Edit Data Pemesanan</h2>
        <p style="color:#888; font-size: 13px;">ID Transaksi: <?php echo $id; ?></p>
    </div>

    <form action="" method="POST">
        <div class="form-group">
            <label>Nomor SPB / Vendor</label>
            <select name="id_kontrak" class="form-control select2-js" required>
                <?php
                $q_kontrak = mysqli_query($conn, "SELECT k.id_kontrak, k.nomor_kontrak, v.nama_vendor FROM kontrak k JOIN vendor v ON k.id_vendor = v.id_vendor");
                while($rk = mysqli_fetch_assoc($q_kontrak)) {
                    $selected = ($rk['id_kontrak'] == $data['id_kontrak']) ? 'selected' : '';
                    echo "<option value='".$rk['id_kontrak']."' $selected>".$rk['nomor_kontrak']." (".$rk['nama_vendor'].")</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label>Nama Pelanggan</label>
            <input type="text" name="nama_pelanggan" class="form-control" value="<?php echo htmlspecialchars($data['nama_pelanggan']); ?>" required>
        </div>

        <div class="form-group">
            <label>Lokasi Pemasangan</label>
            <textarea name="lokasi" class="form-control" rows="2" style="height: auto;" required><?php echo htmlspecialchars($data['lokasi']); ?></textarea>
        </div>

        <div class="form-group">
            <label>Nomor WO (Custom / Master)</label>
            <div style="display: flex; gap: 15px; align-items: stretch;">
                <input type="text" name="sub_wo" class="form-control" style="flex: 1;" 
                       value="<?php echo htmlspecialchars($data['sub_wo']); ?>" placeholder="Kiri" required>
                
                <div style="display: flex; align-items: center; font-weight: bold; color: var(--pln-blue); font-size: 20px;">/</div>
                
                <div style="flex: 1;">
                    <select name="no_wo" class="form-control select2-js" required>
                        <?php
                        $q_wo = mysqli_query($conn, "SELECT no_wo FROM wo ORDER BY no_wo ASC");
                        while($w = mysqli_fetch_assoc($q_wo)) {
                            $sel_wo = ($w['no_wo'] == $data['no_wo']) ? 'selected' : '';
                            echo "<option value='".$w['no_wo']."' $sel_wo>".$w['no_wo']."</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Tanggal WO</label>
            <input type="date" name="tgl_wo" class="form-control" value="<?php echo $data['tgl_wo']; ?>" required>
        </div>

        <div style="display: flex; gap: 20px;">
            <div class="form-group" style="flex: 1;">
                <label>Kebutuhan (Batang)</label>
                <input type="number" name="kebutuhan" class="form-control" value="<?php echo $data['kebutuhan']; ?>" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Kecamatan (ULP)</label>
                <select name="id_ulp" class="form-control select2-js" required>
                    <?php
                    $q_ulp = mysqli_query($conn, "SELECT * FROM ulp");
                    while($ru = mysqli_fetch_assoc($q_ulp)) {
                        $selected_ulp = ($ru['id_ulp'] == $data['id_ulp']) ? 'selected' : '';
                        echo "<option value='".$ru['id_ulp']."' $selected_ulp>".$ru['kecamatan']."</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Keterangan Kuota</label>
            <input type="text" name="ket_kuota" class="form-control" value="<?php echo htmlspecialchars($data['ket_kuota']); ?>">
        </div>

        <button type="submit" name="update" class="btn-update">
            <i class="fas fa-save"></i> SIMPAN PERUBAHAN
        </button>
        <a href="pemesanan.php" class="btn-cancel">Batal dan Kembali</a>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2-js').select2({ width: '100%' });
    });
</script>

</body>
</html>