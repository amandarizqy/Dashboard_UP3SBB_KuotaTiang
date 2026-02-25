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
    <link rel="stylesheet" href="styleKontrak.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                        <th>Status</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $sql = "SELECT k.*, v.nama_vendor, t.jenis_tiang 
                                FROM kontrak k
                                JOIN vendor v ON k.id_vendor = v.id_vendor
                                JOIN tiang t ON k.id_tiang = t.id_tiang";

                        if ($q !== '') {
                            $sql .= " WHERE v.nama_vendor LIKE '%$q%' OR k.nomor_kontrak LIKE '%$q%'";
                        }

                        // ORDER BY status ASC agar 'aktif' muncul di atas 'nonaktif'
                        $sql .= " ORDER BY k.status ASC, k.id_kontrak DESC";

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
                                
                                <td>
                                    <span class="status-label" id="label-<?php echo $row['id_kontrak']; ?>" style="color: <?php echo ($row['status'] == 'aktif') ? '#333' : '#999'; ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                    <br>
                                    <label class="switch">
                                        <input type="checkbox" 
                                               class="status-toggle" 
                                               data-id="<?php echo $row['id_kontrak']; ?>" 
                                               <?php echo ($row['status'] == 'aktif') ? 'checked' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                </td>

                                <td class="action-btns">
                                    <a href="edit.php?id=<?php echo $row['id_kontrak']; ?>" class="edit"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="kelola_kontrak.php?delete_id=<?php echo $row['id_kontrak']; ?>" class="delete" onclick="return confirm('Hapus kontrak ini?')"><i class="fas fa-trash"></i> Hapus</a>
                                </td>
                            </tr>
                    <?php
                            }
                        } else {
                            echo "<tr><td colspan='7' style='text-align:center; padding:50px; color:#999;'>Tidak ada data kontrak ditemukan.</td></tr>";
                        }
                    ?>
                </tbody>
            </table>
        </section>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('.status-toggle').on('change', function() {
            var contractId = $(this).data('id');
            var newStatus = $(this).is(':checked') ? 'aktif' : 'nonaktif';
            var label = $('#label-' + contractId);

            $.ajax({
                url: 'update_status.php', // Pastikan file ini sudah dibuat
                type: 'POST',
                data: {
                    id: contractId,
                    status: newStatus
                },
                success: function() {
                    label.text(newStatus);
                    if(newStatus == 'aktif') {
                        label.css('color', '#333');
                    } else {
                        label.css('color', '#999');
                    }
                },
                error: function() {
                    alert('Gagal mengubah status. Coba lagi.');
                }
            });
        });
    });
    </script>
</body>
</html>