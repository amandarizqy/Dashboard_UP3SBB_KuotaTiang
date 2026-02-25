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
    <link rel="stylesheet" href="stylekuota.css">
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