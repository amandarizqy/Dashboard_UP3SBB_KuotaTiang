<?php 
include 'koneksi.php'; 

// Pengaturan Sidebar
$expanded = isset($_GET['expand']) ? 'expanded' : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Database Keseluruhan - Monitoring Kuota PLN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --pln-blue: #00A3E0;
            --pln-yellow: #FFD100;
            --bg-gray: #f4f7f9;
            --sidebar-width: 70px;
        }

        body { 
            background-color: var(--bg-gray); 
            margin: 0; 
            font-family: 'Segoe UI', sans-serif;
            display: flex;
        }

        /* Sidebar Styling */
        #sidebar {
            width: var(--sidebar-width);
            background-color: var(--pln-blue);
            color: white;
            height: 100vh;
            position: fixed;
            transition: 0.3s;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            border-right: 5px solid var(--pln-yellow);
            z-index: 1000;
        }
        #sidebar.expanded { width: 240px; }
        .nav-item { 
            padding: 20px; display: flex; align-items: center; color: white; 
            text-decoration: none; transition: 0.3s; white-space: nowrap;
        }
        .nav-item i { font-size: 24px; min-width: 30px; margin-right: 20px; text-align: center; }
        .nav-item span { opacity: 0; transition: 0.3s; }
        #sidebar.expanded .nav-item span { opacity: 1; }
        .nav-item:hover { background: rgba(255,255,255,0.1); border-left: 5px solid var(--pln-yellow); }

        /* Main Content */
        #main-content { 
            margin-left: var(--sidebar-width); 
            flex-grow: 1; 
            padding: 30px; 
            transition: 0.3s; 
            overflow-x: auto;
        }

        .header-box {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Table Styling */
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th {
            background-color: #f8f9fa;
            color: #555;
            padding: 12px 10px;
            border: 1px solid #dee2e6;
            text-transform: uppercase;
            white-space: nowrap;
        }

        td {
            padding: 10px;
            border: 1px solid #dee2e6;
            vertical-align: middle;
            color: #333;
        }

        .row-spb { background-color: #f0f7ff; font-weight: bold; }
        .minus { color: #e74c3c; font-weight: bold; }
        .plus { color: #27ae60; font-weight: bold; }
    </style>
</head>
<body>

    <nav id="sidebar" id="sidebar">
        <div style="padding: 20px; text-align: center; background: white; margin-bottom: 20px;">
            <img src="logo_PLN.png" style="width: 40px;">
        </div>
        <div class="nav-item" onclick="toggleSidebar()"><i class="fas fa-bars"></i><span>Menu</span></div>
        <a href="dashboard.php" class="nav-item"><i class="fas fa-home"></i><span>Beranda Dashboard</span></a>
        <a href="menu.php" class="nav-item"><i class="fas fa-database"></i><span>Manajemen Data</span></a>
        <a href="pemesanan.php" class="nav-item"><i class="fas fa-shopping-cart"></i><span>Pemesanan</span></a>
        <a href="database.php" class="nav-item"><i class="fas fa-table"></i><span>Database Keseluruhan</span></a>
    </nav>

    <main id="main-content">
        <div class="header-box">
            <div>
                <h1 style="margin:0; color:var(--pln-blue);">Database Keseluruhan</h1>
                <p style="margin:5px 0 0; color:#888;">Rekapitulasi sinkronisasi Kuota Kontrak dan Realisasi Pemesanan WO</p>
            </div>
            <button onclick="window.print()" class="nav-item" style="background: var(--pln-blue); border-radius: 10px; border:none; cursor:pointer;">
                <i class="fas fa-print"></i> <span>Cetak Laporan</span>
            </button>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama PT (Vendor)</th>
                        <th>Ukuran Tiang</th>
                        <th>Kuota Awal</th>
                        <th>Kebutuhan</th>
                        <th>Sisa Kuota</th>
                        <th>Ket Kuota</th>
                        <th>Nama Pelanggan</th>
                        <th>Lokasi</th>
                        <th>No SPB</th>
                        <th>Tgl Terbit</th>
                        <th>Tgl Akhir</th>
                        <th>No WO</th>
                        <th>Tgl WO</th>
                        <th>Kecamatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Ambil semua kontrak
                    $q_kontrak = mysqli_query($conn, "SELECT k.*, v.nama_vendor, t.jenis_tiang 
                                                      FROM kontrak k 
                                                      JOIN vendor v ON k.id_vendor = v.id_vendor 
                                                      JOIN tiang t ON k.id_tiang = t.id_tiang 
                                                      ORDER BY k.id_kontrak DESC");
                    
                    $no = 1;
                    while($k = mysqli_fetch_assoc($q_kontrak)) {
                        $id_k = $k['id_kontrak'];
                        $kuota_berjalan = $k['kuota']; // Start saldo dari kuota kontrak
                        
                        // Ambil semua pemesanan untuk kontrak ini secara kronologis
                        $q_pesan = mysqli_query($conn, "SELECT p.*, u.kecamatan 
                                                        FROM pemesanan p 
                                                        JOIN ulp u ON p.id_ulp = u.id_ulp 
                                                        WHERE p.id_kontrak = '$id_k' 
                                                        ORDER BY p.id_pemesanan ASC");
                        
                        if(mysqli_num_rows($q_pesan) > 0) {
                            $first = true;
                            while($p = mysqli_fetch_assoc($q_pesan)) {
                                $sisa_sebelumnya = $kuota_berjalan;
                                $kuota_berjalan -= $p['kebutuhan']; // Logika pengurangan dinamis
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo $k['nama_vendor']; ?></td>
                                    <td><?php echo $k['jenis_tiang']; ?></td>
                                    <td style="text-align:center; font-weight:bold;"><?php echo ($first) ? $k['kuota'] : ''; ?></td>
                                    <td style="text-align:center; color:var(--pln-blue); font-weight:bold;"><?php echo $p['kebutuhan']; ?></td>
                                    <td class="<?php echo ($kuota_berjalan < 0) ? 'minus' : 'plus'; ?>" style="text-align:center;">
                                        <?php echo $kuota_berjalan; ?>
                                    </td>
                                    <td><?php echo $p['ket_kuota']; ?></td>
                                    <td><?php echo $p['nama_pelanggan']; ?></td>
                                    <td><?php echo $p['lokasi']; ?></td>
                                    <td><?php echo $k['nomor_kontrak']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($k['tanggal_terbit'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($k['akhir_tenggat'])); ?></td>
                                    <td><?php echo $p['no_wo']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($p['tgl_wo'])); ?></td>
                                    <td><?php echo $p['kecamatan']; ?></td>
                                </tr>
                                <?php
                                $first = false;
                            }
                        } else {
                            // Jika kontrak belum ada pemesanan sama sekali
                            ?>
                            <tr class="row-spb">
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $k['nama_vendor']; ?></td>
                                <td><?php echo $k['jenis_tiang']; ?></td>
                                <td style="text-align:center;"><?php echo $k['kuota']; ?></td>
                                <td style="text-align:center;">0</td>
                                <td style="text-align:center;" class="plus"><?php echo $k['kuota']; ?></td>
                                <td colspan="9" style="text-align:center; color:#aaa; font-style:italic;">Belum ada realisasi pemesanan untuk SPB ini</td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('expanded');
        }
    </script>
</body>
</html>