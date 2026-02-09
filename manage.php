<?php 
include 'koneksi.php'; 
$table = isset($_GET['table']) ? $_GET['table'] : 'vendor';

// 1. LOGIKA NAVIGASI TOMBOL TAMBAH (Diletakkan di atas agar variabel terbaca)
if ($table == 'kontrak') {
    $add_link = "add_kontrak.php";
} else {
    $add_link = "add.php?table=" . $table; 
}

// 2. LOGIKA HAPUS
if (isset($_GET['delete_id'])) {
    $id_name = "id_" . $table;
    $del_id = $_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM $table WHERE $id_name = '$del_id'");
    header("Location: manage.php?table=$table");
    exit;
}

// 3. AMBIL KOLOM DATABASE
$columns = array();
$res = mysqli_query($conn, "SHOW COLUMNS FROM $table");
while($col = mysqli_fetch_assoc($res)) {
    $columns[] = $col['Field'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Data <?php echo ucfirst($table); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --pln-blue: #00A3E0; --pln-yellow: #FFD100; }
        body { background-color: #f8f9fa; margin: 0; padding: 20px; font-family: 'Segoe UI', sans-serif; }
        
        .main-wrapper { 
            display: flex; 
            gap: 24px; 
            align-items: flex-start; 
            max-width: 1400px; 
            margin: 0 auto; 
        }

        /* Panel Kiri Tetap (Sticky) */
        .left-panel { 
            width: 320px; 
            background: white; 
            padding: 30px; 
            border-radius: 20px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            display: flex; 
            flex-direction: column; 
            gap: 20px; 
            position: sticky;
            top: 20px;
        }

        .right-panel { flex: 1; min-width: 0; }

        .logo-img { height: 60px; object-fit: contain; margin-bottom: 10px; }
        .back-btn { text-decoration: none; color: var(--pln-blue); font-weight: 600; font-size: 14px; }

        .header-title h2 { margin: 0; color: #333; font-size: 22px; }
        .header-title small { color: #888; display: block; margin-top: 5px; }

        .search-form { display: flex; flex-direction: column; gap: 10px; }
        .search-input { padding: 12px; border: 1px solid #ddd; border-radius: 10px; outline: none; }
        .search-btn { padding: 12px; border-radius: 10px; border: none; background: var(--pln-blue); color: white; font-weight: bold; cursor: pointer; }

        .btn-add { 
            background: var(--pln-yellow); 
            color: #333; 
            padding: 15px; 
            border-radius: 12px; 
            text-decoration: none; 
            font-weight: 800; 
            text-align: center;
            display: block;
            box-shadow: 0 4px 10px rgba(255, 209, 0, 0.2);
        }

        /* Tabel Renggang */
        table { width: 100%; border-collapse: separate; border-spacing: 0 15px; }
        th { padding: 10px 20px; text-align: left; color: #aaa; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
        td { padding: 25px 20px; background: white; vertical-align: middle; border-top: 1px solid #f0f0f0; border-bottom: 1px solid #f0f0f0; }
        tr td:first-child { border-radius: 15px 0 0 15px; border-left: 1px solid #f0f0f0; }
        tr td:last-child { border-radius: 0 15px 15px 0; border-right: 1px solid #f0f0f0; text-align: right; }
        
        .action-btns a { margin-left: 15px; text-decoration: none; font-weight: bold; font-size: 13px; }
        .delete { color: #e74c3c; }
        .edit { color: var(--pln-blue); }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <aside class="left-panel">
            <a href="menu.php" class="back-btn"><i class="fas fa-arrow-left"></i> Kembali ke Menu</a>
            
            <div style="text-align: center;">
                <img src="logo_PLN.png" alt="PLN" class="logo-img">
            </div>

            <div class="header-title">
                <h2>Kelola <?php echo ucfirst($table); ?></h2>
                <small>Manajemen Database Distribusi</small>
            </div>

            <form method="GET" class="search-form">
                <input type="hidden" name="table" value="<?php echo htmlspecialchars($table); ?>">
                <input type="text" name="q" class="search-input" placeholder="Cari data..." value="<?php echo isset($_GET['q'])?htmlspecialchars($_GET['q']):''; ?>">
                <button type="submit" class="search-btn"><i class="fas fa-search"></i> CARI</button>
            </form>

            <a href="<?php echo $add_link; ?>" class="btn-add">
                <i class="fas fa-plus-circle"></i> TAMBAH <?php echo strtoupper($table); ?>
            </a>
        </aside>

        <section class="right-panel">
            <table>
                <thead>
                    <tr>
                        <?php foreach ($columns as $c) { echo "<th>" . str_replace('_', ' ', $c) . "</th>"; } ?>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM $table";
                    if (isset($_GET['q']) && trim($_GET['q']) !== '') {
                        $q = mysqli_real_escape_string($conn, $_GET['q']);
                        $where = array();
                        foreach ($columns as $c) { $where[] = "$c LIKE '%$q%'"; }
                        $sql .= ' WHERE ' . implode(' OR ', $where);
                    }
                    $sql .= " ORDER BY {$columns[0]} DESC";

                    $data_res = mysqli_query($conn, $sql);
                    if(mysqli_num_rows($data_res) > 0) {
                        while($row = mysqli_fetch_assoc($data_res)) {
                            $id_val = $row[$columns[0]];
                            echo "<tr>";
                            foreach ($columns as $colname) {
                                echo "<td>" . htmlspecialchars($row[$colname]) . "</td>";
                            }
                            echo "<td class='action-btns'>
                                    <a href='edit_generic.php?table=$table&id=$id_val' class='edit'><i class='fas fa-edit'></i> Edit</a>
                                    <a href='manage.php?table=$table&delete_id=$id_val' class='delete' onclick='return confirm(\"Hapus data ini?\")'><i class='fas fa-trash'></i> Hapus</a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='".(count($columns)+1)."' style='text-align:center; padding:50px; color:#999;'>Data tidak ditemukan.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>