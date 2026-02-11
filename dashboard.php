<?php 
include 'koneksi.php'; 

$filter_vendor = isset($_GET['filter_vendor']) ? $_GET['filter_vendor'] : '';
$filter_tiang  = isset($_GET['filter_tiang']) ? $_GET['filter_tiang'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Ringkas - PLN Monitoring</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styledashboard.css">
    <style>
        /* Reset & Base Styling */
        body { background-color: #f4f7f9; margin: 0; font-family: 'Segoe UI', sans-serif; display: flex; }
        
        /* Modal Pop-out */
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(3px); }
        .modal-content { background: white; margin: 5% auto; padding: 25px; border-radius: 12px; width: 75%; max-height: 85vh; overflow-y: auto; box-shadow: 0 5px 30px rgba(0,0,0,0.3); }
        .close-modal { float: right; font-size: 24px; cursor: pointer; color: #888; }
        
        /* Tabel Simpel PLN */
        .table-simple { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
        .table-simple th { background: #f8f9fa; color: #555; font-size: 12px; text-transform: uppercase; padding: 12px 15px; border-bottom: 2px solid #dee2e6; text-align: left; }
        .table-simple td { padding: 10px 15px; border-bottom: 1px solid #eee; font-size: 13px; color: #333; }
        .table-simple tr:hover { background-color: #fcfcfc; }
        
        /* Link & Badge */
        .spb-link { color: #00A3E0; text-decoration: none; font-weight: bold; cursor: pointer; }
        .spb-link:hover { text-decoration: underline; }
        .badge-tiang { background: #eef2f7; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; color: #555; }
        
        /* Utility */
        .text-minus { color: #e74c3c !important; font-weight: bold; }
        .text-blue { color: #00A3E0 !important; font-weight: bold; }
        .total-quota-box { padding: 10px 25px !important; font-size: 2rem !important; min-width: 100px; text-align: center; }
        /* CSS untuk Tabel Pop-out agar Rapat dan Simpel */
.table-detail { 
    width: 100%; 
    border-collapse: collapse; /* Menghilangkan jarak antar sel agar tidak renggang */
    margin-top: 10px; 
    font-size: 11px; /* Ukuran font lebih kecil agar padat */
}

.table-detail th { 
    background: #f2f2f2; 
    color: #333; 
    padding: 6px 8px; /* Padding kecil agar rapat */
    border: 1px solid #ccc; /* Garis tipis */
    text-align: center;
    white-space: nowrap;
}

.table-detail td { 
    padding: 5px 8px; /* Padding baris rapat */
    border: 1px solid #ccc;
    vertical-align: middle;
    color: #000;
}

/* Warna khusus indikator */
.text-red { color: #e74c3c; font-weight: bold; }
.text-green { color: #27ae60; font-weight: bold; }

/* Menghilangkan bayangan dan margin berlebih di modal */
.modal-content { 
    padding: 15px; 
    width: 85%; /* Lebih lebar agar tabel tidak tertekuk */
}
    </style>
</head>
<body>

    <nav id="sidebar">
        <div class="sidebar-header"><img src="logo_PLN.png" alt="Logo PLN" class="logo-sidebar"></div>
        <div class="nav-item" onclick="toggleSidebar()"><i class="fas fa-bars"></i><span>Menu</span></div>
        <a href="dashboard.php" class="nav-item"><i class="fas fa-home"></i><span>Dashboard</span></a>
        <hr style="width: 80%; border: 0.5px solid rgba(255,255,255,0.1); margin: 10px auto;">
        <a href="menu.php" class="nav-item"><i class="fas fa-database"></i><span>Master Data</span></a>
        <a href="pemesanan.php" class="nav-item"><i class="fas fa-shopping-cart"></i><span>Pemesanan</span></a>
        <a href="histori.php" class="nav-item"><i class="fas fa-history"></i><span>History</span></a>
    </nav>

    <main id="main-content">
        <header class="header-section">
            <div class="title-container">
                <h1 style="color:#00A3E0; margin:0;">Monitoring Kuota</h1>
                <form action="" method="GET" class="filter-container">
                    <select name="filter_vendor" class="select-filter" onchange="this.form.submit()">
                        <option value="">-- Semua Vendor --</option>
                        <?php 
                        $v_list = mysqli_query($conn, "SELECT * FROM vendor");
                        while($v = mysqli_fetch_assoc($v_list)) {
                            $sel = ($filter_vendor == $v['id_vendor']) ? 'selected' : '';
                            echo "<option value='".$v['id_vendor']."' $sel>".$v['nama_vendor']."</option>";
                        }
                        ?>
                    </select>
                    <select name="filter_tiang" class="select-filter" onchange="this.form.submit()">
                        <option value="">-- Semua Ukuran --</option>
                        <?php 
                        $t_list = mysqli_query($conn, "SELECT * FROM tiang");
                        while($t = mysqli_fetch_assoc($t_list)) {
                            $sel = ($filter_tiang == $t['id_tiang']) ? 'selected' : '';
                            echo "<option value='".$t['id_tiang']."' $sel>".$t['jenis_tiang']."</option>";
                        }
                        ?>
                    </select>
                </form>
            </div>
            <div class="total-quota-box">
                <?php
                $where_clauses = ["k.status = 'aktif'"];
                if(!empty($filter_vendor)) $where_clauses[] = "k.id_vendor = '$filter_vendor'";
                if(!empty($filter_tiang)) $where_clauses[] = "k.id_tiang = '$filter_tiang'";
                $where_sql = " WHERE " . implode(" AND ", $where_clauses);

                $sql_total = "SELECT SUM(k.kuota - IFNULL((SELECT SUM(p.kebutuhan) FROM pemesanan p WHERE p.id_kontrak = k.id_kontrak), 0)) as hasil FROM kontrak k $where_sql";
                $res_total = mysqli_fetch_assoc(mysqli_query($conn, $sql_total));
                $total_global = $res_total['hasil'] ?? 0;
                echo "<span class='".($total_global < 0 ? 'text-minus' : '')."'>".$total_global."</span>";
                ?>
            </div>
        </header>

        <section class="table-container" style="background:white; padding:15px; border-radius:10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <table class="table-simple">
                <thead>
                    <tr>
                        <th width="30%">No. SPB (Kontrak)</th>
                        <th width="30%">Nama Vendor</th>
                        <th width="20%">Jenis Tiang</th>
                        <th width="20%" style="text-align: center;">Sisa Kuota</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT k.id_kontrak, v.nama_vendor, k.nomor_kontrak, t.jenis_tiang,
                            (k.kuota - IFNULL((SELECT SUM(kebutuhan) FROM pemesanan WHERE id_kontrak = k.id_kontrak), 0)) as sisa_kuota
                            FROM kontrak k
                            JOIN vendor v ON k.id_vendor = v.id_vendor
                            JOIN tiang t ON k.id_tiang = t.id_tiang
                            $where_sql ORDER BY k.id_kontrak DESC";
                    
                    $result = mysqli_query($conn, $sql);
                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            $is_minus = ($row['sisa_kuota'] < 0);
                            echo "<tr>
                                <td><span class='spb-link' onclick='showHistori(".$row['id_kontrak'].", \"".$row['nomor_kontrak']."\")'>".$row['nomor_kontrak']."</span></td>
                                <td>".$row['nama_vendor']."</td>
                                <td><span class='badge-tiang'>".$row['jenis_tiang']."</span></td>
                                <td style='text-align: center;' class='".($is_minus ? 'text-minus' : 'text-blue')."'>".$row['sisa_kuota']."</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' style='text-align:center; padding:30px; color:#999;'>Tidak ada data kontrak aktif.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </main>

    <div id="historiModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h3 id="modalTitle" style="color:#00A3E0; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-top:0;">Histori Pemesanan</h3>
            <div id="modalBody">Memuat data histori...</div>
        </div>
    </div>

    <script>
        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('expanded'); }

        function showHistori(id, nomor) {
            document.getElementById('historiModal').style.display = "block";
            document.getElementById('modalTitle').innerText = "Rincian Pemakaian: " + nomor;
            fetch('get_histori.php?id=' + id)
                .then(response => response.text())
                .then(data => { document.getElementById('modalBody').innerHTML = data; });
        }

        function closeModal() { document.getElementById('historiModal').style.display = "none"; }
        window.onclick = function(event) { if (event.target == document.getElementById('historiModal')) closeModal(); }
    </script>
</body>
</html>