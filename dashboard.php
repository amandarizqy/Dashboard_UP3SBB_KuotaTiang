<?php 
include 'koneksi.php'; 

// Mengambil nilai filter dari URL
$filter_vendor = isset($_GET['filter_vendor']) ? $_GET['filter_vendor'] : '';
$filter_tiang  = isset($_GET['filter_tiang']) ? $_GET['filter_tiang'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard PLN - Monitoring Kuota Tiang</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --pln-blue: #00A3E0;
            --pln-yellow: #FFD100;
            --bg-gray: #f4f7f9;
            --sidebar-width: 70px;
            --sidebar-expanded: 240px;
        }

        body { 
            background-color: var(--bg-gray); 
            margin: 0; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
        }

        /* --- Sidebar Samping --- */
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

        #sidebar.expanded { width: var(--sidebar-expanded); }

        .sidebar-header {
            padding: 20px 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: white;
            margin-bottom: 20px;
        }

        .logo-sidebar {
            width: 40px;
            height: auto;
            transition: 0.3s;
        }

        #sidebar.expanded .logo-sidebar { width: 80px; }

        .nav-item { 
            padding: 20px;
            display: flex;
            align-items: center;
            color: white; 
            text-decoration: none;
            cursor: pointer;
            white-space: nowrap;
            transition: 0.3s;
        }

        .nav-item i { font-size: 24px; min-width: 30px; margin-right: 20px; text-align: center; }
        .nav-item span { opacity: 0; transition: 0.3s; }
        #sidebar.expanded .nav-item span { opacity: 1; }

        .nav-item:hover { background-color: rgba(255, 255, 255, 0.1); border-left: 5px solid var(--pln-yellow); }

        /* --- Main Content --- */
        #main-content { 
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            padding: 30px;
            transition: 0.3s;
        }

        /* --- Header Card --- */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 25px 40px;
            border-radius: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-bottom: 40px;
        }

        .title-group h1 {
            font-size: 3rem;
            margin: 0;
            color: var(--pln-blue);
            font-weight: 800;
        }

        .filter-container {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 10px;
        }

        .select-filter {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 12px;
            background: white;
            min-width: 150px;
        }

        .btn-update {
            background-color: var(--pln-yellow);
            color: #222;
            border: none;
            padding: 10px 25px;
            border-radius: 12px;
            font-weight: 800;
            cursor: pointer;
            text-transform: uppercase;
        }

        .total-quota-box {
            background: linear-gradient(135deg, var(--pln-blue) 0%, #008cc1 100%);
            color: white;
            padding: 30px 60px;
            font-size: 5rem;
            font-weight: 900;
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(0, 163, 224, 0.3);
            border-bottom: 8px solid var(--pln-yellow);
            text-align: center;
        }

        /* --- Tabel Gaya Kartu --- */
        .table-container { width: 100%; }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 25px;
        }

        th {
            text-align: left;
            padding: 0 30px;
            color: #aaa;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1.5px;
        }

        td {
            padding: 40px 30px;
            background: white;
            vertical-align: middle;
            transition: 0.3s;
        }

        tr td:first-child { border-radius: 25px 0 0 25px; }
        tr td:last-child { border-radius: 0 25px 25px 0; }

        .vendor-name { font-size: 1.4rem; font-weight: 800; color: #333; margin-bottom: 8px; }
        .spb-num { color: var(--pln-blue); font-weight: 600; font-family: 'Courier New', monospace; }
        
        .badge-tiang {
            background: #f8f9fa;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 800;
            border-left: 5px solid var(--pln-yellow);
            color: #444;
            box-shadow: inset 0 0 5px rgba(0,0,0,0.05);
        }

        .col-kuota {
            font-size: 2rem;
            font-weight: 900;
            text-align: center;
        }

        .text-minus { color: #e74c3c !important; }
        .text-blue { color: var(--pln-blue); }
    </style>
</head>
<body>

    <nav id="sidebar">
        <div class="sidebar-header">
            <img src="logo_PLN.png" alt="Logo PLN" class="logo-sidebar">
        </div>
        <div class="nav-item" onclick="toggleSidebar()"><i class="fas fa-bars"></i><span>Tutup/Buka Menu</span></div>
        <a href="dashboard.php" class="nav-item"><i class="fas fa-home"></i><span>Beranda Dashboard</span></a>
        <hr style="width: 80%; border: 0.5px solid rgba(255,255,255,0.2); margin: 15px auto;">
        <a href="menu.php" class="nav-item"><i class="fas fa-database"></i><span>Manajemen Data</span></a>
        <a href="pemesanan.php" class="nav-item"><i class="fas fa-shopping-cart"></i><span>Pemesanan</span></a>
        <a href="histori.php" class="nav-item"><i class="fas fa-history"></i><span>History</span></a>
    </nav>

    <main id="main-content">
        <header class="header-section">
            <div class="title-container">
                <div class="title-group">
                    <h1>Tiang Listrik</h1>
                    <form action="" method="GET" class="filter-container">
                        <select name="filter_vendor" class="select-filter">
                            <option value="">Semua Vendor</option>
                            <?php 
                            $v_list = mysqli_query($conn, "SELECT * FROM vendor");
                            while($v = mysqli_fetch_assoc($v_list)) {
                                $sel = ($filter_vendor == $v['id_vendor']) ? 'selected' : '';
                                echo "<option value='".$v['id_vendor']."' $sel>".$v['nama_vendor']."</option>";
                            }
                            ?>
                        </select>
                        <select name="filter_tiang" class="select-filter">
                            <option value="">Semua Ukuran</option>
                            <?php 
                            $t_list = mysqli_query($conn, "SELECT * FROM tiang");
                            while($t = mysqli_fetch_assoc($t_list)) {
                                $sel = ($filter_tiang == $t['id_tiang']) ? 'selected' : '';
                                echo "<option value='".$t['id_tiang']."' $sel>".$t['jenis_tiang']."</option>";
                            }
                            ?>
                        </select>
                        <button type="submit" class="btn-update">Filter</button>
                    </form>
                </div>
            </div>

            <div class="total-quota-box">
                <?php
                // 1. Membangun kondisi WHERE yang sinkron dengan filter tabel
                $where_clauses = [];
                if (!empty($filter_vendor)) {
                    $where_clauses[] = "k.id_vendor = '$filter_vendor'";
                }
                if (!empty($filter_tiang)) {
                    $where_clauses[] = "k.id_tiang = '$filter_tiang'";
                }

                $where_sql = "";
                if (count($where_clauses) > 0) {
                    $where_sql = " WHERE " . implode(" AND ", $where_clauses);
                }

                // 2. Query Total: Menghitung Sisa Kuota (Kuota Awal - Terpakai) 
                // hanya untuk data yang tampil di tabel (berdasarkan filter)
                $sql_total_sinkron = "SELECT 
                    (SUM(k.kuota) - 
                        IFNULL((SELECT SUM(p.kebutuhan) 
                                FROM pemesanan p 
                                JOIN kontrak k2 ON p.id_kontrak = k2.id_kontrak 
                                $where_sql), 0)
                    ) as hasil_sinkron
                    FROM kontrak k
                    $where_sql";

                $query_total = mysqli_query($conn, $sql_total_sinkron);
                $data_total = mysqli_fetch_assoc($query_total);
                
                // Pastikan jika hasil filter kosong, tampilkan 0
                $hasil_akhir = ($data_total['hasil_sinkron'] !== null) ? $data_total['hasil_sinkron'] : 0;

                // 3. Tampilkan angka dengan warna merah jika minus
                $warna_class = ($hasil_akhir < 0) ? "text-minus" : "";
                echo "<span class='$warna_class'>$hasil_akhir</span>";
                ?>
            </div>
    
        </header>

        <section class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Vendor & Kontrak</th>
                        <th>Spesifikasi Tiang</th>
                        <th>Jatuh Tempo</th>
                        <th style="text-align: center;">Sisa Kuota</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT k.id_kontrak, v.nama_vendor, k.nomor_kontrak, t.jenis_tiang, k.akhir_tenggat, k.kuota,
                            (k.kuota - IFNULL((SELECT SUM(kebutuhan) FROM pemesanan WHERE id_kontrak = k.id_kontrak), 0)) as sisa_kuota
                            FROM kontrak k
                            JOIN vendor v ON k.id_vendor = v.id_vendor
                            JOIN tiang t ON k.id_tiang = t.id_tiang";
                    
                    if(count($where_clauses) > 0) $sql .= " WHERE " . implode(" AND ", $where_clauses);
                    $sql .= " ORDER BY k.akhir_tenggat ASC";
                    
                    $result = mysqli_query($conn, $sql);

                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            $is_minus = ($row['sisa_kuota'] < 0);
                            ?>
                            <tr>
                                <td>
                                    <div class="vendor-name"><?php echo $row['nama_vendor']; ?></div>
                                    <div class="spb-num"><?php echo $row['nomor_kontrak']; ?></div>
                                </td>
                                <td><span class="badge-tiang"><?php echo $row['jenis_tiang']; ?></span></td>
                                <td><i class="far fa-calendar-check" style="color: #888;"></i> <?php echo date('d/m/Y', strtotime($row['akhir_tenggat'])); ?></td>
                                <td class="col-kuota <?php echo $is_minus ? 'text-minus' : 'text-blue'; ?>">
                                    <?php echo $row['sisa_kuota']; ?>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='4' style='text-align:center; background:none; padding:150px; color:#ccc; border:none;'>
                                <i class='fas fa-search' style='font-size:4rem; margin-bottom:20px;'></i><br>
                                Tidak ada data yang sesuai filter.
                              </td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </main>

    <script>
        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('expanded'); }
    </script>
</body>
</html>