<?php
include 'koneksi.php';

// Ambil data semua jenis tiang untuk dropdown filter
$query_tiang_list = "SELECT * FROM TIANG";
$result_tiang_list = mysqli_query($conn, $query_tiang_list);

// Cek jika ada filter yang dipilih
$filter_tiang = isset($_GET['id_tiang']) ? $_GET['id_tiang'] : '';

// 1. Query Summary dengan Filter + Status Aktif
$where_clause = " WHERE k.status = 'aktif'"; // Default harus aktif
if ($filter_tiang != '') {
    $where_clause .= " AND t.id_tiang = '$filter_tiang'";
}

$query_summary = "
    SELECT 
        v.nama_vendor, t.jenis_tiang, v.id_vendor, t.id_tiang,
        SUM(k.kuota) as total_kuota,
        SUM(IFNULL(p.total_terpakai, 0)) as total_terpakai,
        (SUM(k.kuota) - SUM(IFNULL(p.total_terpakai, 0))) as sisa_kuota
    FROM KONTRAK k
    JOIN VENDOR v ON k.id_vendor = v.id_vendor
    JOIN TIANG t ON k.id_tiang = t.id_tiang
    LEFT JOIN (
        SELECT id_kontrak, SUM(kebutuhan) as total_terpakai 
        FROM PEMESANAN 
        GROUP BY id_kontrak
    ) p ON k.id_kontrak = p.id_kontrak
    $where_clause
    GROUP BY v.id_vendor, t.id_tiang
";

$query_summary = "
    SELECT 
        v.nama_vendor, t.jenis_tiang, v.id_vendor, t.id_tiang,
        SUM(k.kuota) as total_kuota,
        SUM(IFNULL(p.total_terpakai, 0)) as total_terpakai,
        (SUM(k.kuota) - SUM(IFNULL(p.total_terpakai, 0))) as sisa_kuota
    FROM KONTRAK k
    JOIN VENDOR v ON k.id_vendor = v.id_vendor
    JOIN TIANG t ON k.id_tiang = t.id_tiang
    LEFT JOIN (
        SELECT id_kontrak, SUM(kebutuhan) as total_terpakai 
        FROM PEMESANAN 
        GROUP BY id_kontrak
    ) p ON k.id_kontrak = p.id_kontrak
    $where_clause
    GROUP BY v.id_vendor, t.id_tiang
";

$result_summary = mysqli_query($conn, $query_summary);

// 2. Query Detail untuk Modal (Hanya Kontrak Aktif)
$query_detail = "
    SELECT k.id_vendor, k.id_tiang, k.nomor_kontrak, k.kuota,
           IFNULL(p.total_terpakai, 0) as terpakai,
           (k.kuota - IFNULL(p.total_terpakai, 0)) as sisa
    FROM KONTRAK k
    LEFT JOIN (
        SELECT id_kontrak, SUM(kebutuhan) as total_terpakai FROM PEMESANAN GROUP BY id_kontrak
    ) p ON k.id_kontrak = p.id_kontrak
    WHERE k.status = 'aktif'
";
$result_detail = mysqli_query($conn, $query_detail);

$details = [];
while ($row_det = mysqli_fetch_assoc($result_detail)) {
    $details[$row_det['id_vendor'] . '-' . $row_det['id_tiang']][] = $row_det;
}
$details_json = json_encode($details);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Kuota PLN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --pln-blue: #00a2d1;
            --pln-yellow: #ffcc00;
            --dark: #333;
            --light: #f4f7f6;
            --white: #ffffff;
            --danger: #e74c3c;
            --warning: #f39c12;
            --success: #2ecc71;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(rgba(244, 247, 246, 0.8), rgba(244, 247, 246, 0.8)), 
                        url('bg_pln.png') no-repeat center center fixed;
            background-size: cover;
            color: var(--dark);
            line-height: 1.6;
        }

        /* Navbar */
        .navbar {
            background: var(--pln-blue);
            color: var(--white);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .btn-back {
            color: var(--white);
            text-decoration: none;
            font-size: 1.2rem;
            margin-right: 1.5rem;
            transition: 0.3s;
        }
        .btn-back:hover { color: var(--pln-yellow); }
        .nav-title { font-size: 1.3rem; font-weight: bold; letter-spacing: 1px; }

        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }

        /* Filter Section */
        .filter-card {
            background: rgba(255,255,255,0.9);
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border-left: 5px solid var(--pln-yellow);
        }
        .filter-card label { font-weight: bold; color: var(--pln-blue); }
        .filter-card select {
            padding: 0.6rem 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
            min-width: 250px;
            font-size: 1rem;
        }

        /* Grid System */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }

        /* Card Style */
        .card {
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: 0.3s;
            cursor: pointer;
            position: relative;
            border-bottom: 4px solid var(--pln-yellow);
        }
        .card:hover { transform: translateY(-10px); box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        
        .card-header {
            padding: 1.5rem;
            background: rgba(0, 162, 209, 0.05);
            border-bottom: 1px solid #eee;
        }
        .vendor-name { font-size: 0.8rem; font-weight: bold; color: var(--pln-blue); text-uppercase: uppercase; }
        .tiang-type { font-size: 1.2rem; font-weight: bold; margin-top: 0.3rem; }

        .card-body { padding: 1.5rem; }
        
        .status-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .sisa-badge {
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
            color: var(--white);
        }

        /* Progress Bar */
        .progress-container {
            background: #eee;
            height: 10px;
            border-radius: 10px;
            margin-bottom: 0.5rem;
            overflow: hidden;
        }
        .progress-bar { height: 100%; background: var(--pln-blue); border-radius: 10px; }

        .card-footer-info {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: #666;
            background: #fafafa;
            padding: 1rem 1.5rem;
        }

       /* Modal / Popup */
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0; 
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(4px);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            padding: 20px; /* Jarak aman agar modal tidak nempel ke pinggir layar hp */
        }

        .modal-content {
            background: var(--white);
            width: 100%;
            max-width: 800px;
            max-height: 90vh; /* Batasi tinggi maksimal 90% dari tinggi layar */
            border-radius: 15px;
            display: flex;
            flex-direction: column; /* Biarkan isi modal tersusun vertikal */
            animation: slideDown 0.3s ease-out;
            overflow: hidden; /* Sembunyikan overflow luar */
        }

        .modal-body {
            padding: 0;
            overflow-y: auto; /* Aktifkan scroll hanya di bagian isi tabel */
            flex-grow: 1; /* Biarkan bagian ini mengambil sisa ruang yang ada */
        }

        /* Memastikan header tabel tetap di atas saat di-scroll */
        table thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #333;
        }
                
        @keyframes slideDown { from { transform: translateY(-50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        .modal-header {
            background: var(--pln-blue);
            color: var(--white);
            padding: 1.2rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .close-modal { cursor: pointer; font-size: 1.5rem; }

        table { width: 100%; border-collapse: collapse; }
        th { background: #333; color: var(--white); text-align: left; padding: 1rem; }
        td { padding: 1rem; border-bottom: 1px solid #eee; }
        tr:hover { background: #f9f9f9; }

        .text-center { text-align: center; }
        .bg-danger { background: var(--danger); }
        .bg-warning { background: var(--warning); }
        .bg-success { background: var(--success); }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="dashboard.php" class="btn-back"><i class="fa-solid fa-chevron-left"></i></a>
    <div class="nav-title"><i class="fa-solid fa-bolt"></i> PLN MONITORING KUOTA TIANG</div>
</nav>

<div class="container">
    
    <div class="filter-card">
        <label for="filterTiang"><i class="fa-solid fa-filter"></i> JENIS TIANG:</label>
        <form id="filterForm" method="GET">
            <select name="id_tiang" id="filterTiang" onchange="document.getElementById('filterForm').submit()">
                <option value="">-- Tampilkan Semua --</option>
                <?php while($t = mysqli_fetch_assoc($result_tiang_list)): ?>
                    <option value="<?= $t['id_tiang'] ?>" <?= ($filter_tiang == $t['id_tiang']) ? 'selected' : '' ?>>
                        <?= $t['jenis_tiang'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>
    </div>

    <div class="grid">
        <?php if (mysqli_num_rows($result_summary) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result_summary)): 
                $total_k = $row['total_kuota'] > 0 ? $row['total_kuota'] : 1;
                $persen = ($row['total_terpakai'] / $total_k) * 100;
                
                $badge_class = ($row['sisa_kuota'] <= 5) ? 'bg-danger' : (($row['sisa_kuota'] <= 15) ? 'bg-warning' : 'bg-success');
            ?>
                <div class="card" onclick="showDetail('<?= $row['nama_vendor'] ?>', '<?= $row['jenis_tiang'] ?>', '<?= $row['id_vendor'] ?>-<?= $row['id_tiang'] ?>')">
                    <div class="card-header">
                        <div class="vendor-name"><?= $row['nama_vendor'] ?></div>
                        <div class="tiang-type"><?= $row['jenis_tiang'] ?></div>
                    </div>
                    <div class="card-body">
                        <div class="status-row">
                            <span>Sisa Kuota</span>
                            <span class="sisa-badge <?= $badge_class ?>"><?= $row['sisa_kuota'] ?> Batang</span>
                        </div>
                        <div class="progress-container">
                            <div class="progress-bar" style="width: <?= $persen ?>%"></div>
                        </div>
                        <div style="font-size: 0.8rem; color: #888; text-align: right;">
                            Terpakai: <?= round($persen) ?>%
                        </div>
                    </div>
                    <div class="card-footer-info">
                        <span><i class="fa-solid fa-boxes-stacked"></i> Stok: <?= $row['total_kuota'] ?></span>
                        <span><i class="fa-solid fa-truck-ramp-box"></i> Pakai: <?= $row['total_terpakai'] ?></span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 3rem; background: var(--white); border-radius: 10px;">
                <i class="fa-solid fa-folder-open" style="font-size: 3rem; color: #ccc;"></i>
                <p style="margin-top: 1rem; color: #888;">Data kuota tidak ditemukan untuk filter ini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="myModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <div id="modalTitle" style="font-weight: bold;"></div>
            <div class="close-modal" onclick="closeModal()">&times;</div>
        </div>
        <div class="modal-body">
            <table>
                <thead>
                    <tr>
                        <th>Nomor Kontrak</th>
                        <th class="text-center">Kuota</th>
                        <th class="text-center">Pakai</th>
                        <th class="text-center">Sisa</th>
                    </tr>
                </thead>
                <tbody id="modalBody"></tbody>
            </table>
        </div>
        <div style="padding: 1rem; border-top: 1px solid #eee; text-align: right; background: #f9f9f9;">
            <button onclick="closeModal()" style="padding: 0.5rem 1.5rem; border-radius: 5px; border: none; background: #666; color: white; cursor: pointer;">Tutup</button>
        </div>
    </div>
</div>

<script>
    const detailData = <?= $details_json ?>;

    function showDetail(vendor, tiang, key) {
        document.getElementById('modalTitle').innerHTML = `<i class="fa-solid fa-file-contract"></i> ${vendor} - ${tiang}`;
        const tbody = document.getElementById('modalBody');
        tbody.innerHTML = '';

        if (detailData[key]) {
            detailData[key].forEach(item => {
                const sisaColor = item.sisa <= 5 ? 'color: var(--danger); font-weight: bold;' : 'color: var(--success); font-weight: bold;';
                tbody.innerHTML += `
                    <tr>
                        <td>${item.nomor_kontrak}</td>
                        <td class="text-center">${item.kuota}</td>
                        <td class="text-center" style="color: var(--danger)">${item.terpakai}</td>
                        <td class="text-center" style="${sisaColor}">${item.sisa}</td>
                    </tr>`;
            });
        }

        document.getElementById('myModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('myModal').style.display = 'none';
    }

    // Menutup modal jika user klik di luar kotak modal
    window.onclick = function(event) {
        if (event.target == document.getElementById('myModal')) {
            closeModal();
        }
    }
</script>

</body>
</html>