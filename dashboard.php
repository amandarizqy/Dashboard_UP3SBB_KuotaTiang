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
    <link rel="stylesheet" href="styledashboard.css">
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

                // 2. Query Total: Hitung sisa kuota per kontrak, lalu sum semuanya
                // Untuk setiap kontrak yang sesuai filter: (kuota - kebutuhan)
                // Kemudian sum hasil pengurangan dari semua kontrak yang sesuai filter
                $sql_total_sinkron = "SELECT 
                    SUM(k.kuota - IFNULL((SELECT SUM(p.kebutuhan) 
                                          FROM pemesanan p 
                                          WHERE p.id_kontrak = k.id_kontrak), 0)
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