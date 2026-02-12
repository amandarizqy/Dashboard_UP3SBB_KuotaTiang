<?php include 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pusat Manajemen Data - PLN</title> 
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
            background: white; /* Putih agar logo terlihat jelas */
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
            pointer-events: auto;
        }

        a.nav-item {
            display: flex;
            color: white;
            text-decoration: none;
        }

        a.nav-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 5px solid var(--pln-yellow);
        }

        .nav-item i { font-size: 24px; min-width: 30px; margin-right: 20px; text-align: center; }
        .nav-item span { opacity: 0; transition: 0.3s; }
        #sidebar.expanded .nav-item span { opacity: 1; }

        .nav-item:hover { background-color: rgba(255, 255, 255, 0.1); border-left: 5px solid var(--pln-yellow); }

        /* --- Main Content --- */
        #main-content { 
            margin-left: var(--sidebar-width);
            flex-grow: 1; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh;
            transition: 0.3s;
            padding: 40px;
        }

        .menu-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 50px;
            padding: 40px;
            width: 100%;
        }

        .menu-card {
            background: white;
            width: 100%;
            padding: 30px 15px;
            border-radius: 15px;
            text-align: center;
            text-decoration: none;
            color: #333;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: 0.3s;
            border-bottom: 6px solid var(--pln-blue);
        }

        .menu-card:hover { 
            transform: translateY(-10px); 
            border-bottom-color: var(--pln-yellow); 
            box-shadow: 0 15px 35px rgba(0, 163, 224, 0.15);
        }

        .menu-card i { font-size: 4.5rem; color: var(--pln-blue); margin-bottom: 25px; }
        .menu-card h3 { font-size: 1.6rem; margin: 0; color: var(--pln-blue); }
        .menu-card p { color: #888; font-size: 0.95rem; margin-top: 15px; line-height: 1.4; }
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
        <div class="menu-container">
            <a href="kelola_kontrak.php" class="menu-card">
                <i class="fas fa-file-signature"></i>
                <h3>Data Kontrak</h3>
                <p>Kelola nomor kontrak dan alokasi kuota tiang vendor.</p>
            </a>

            <a href="manage.php?table=tiang" class="menu-card">
                <i class="fas fa-bolt"></i>
                <h3>Data Tiang</h3>
                <p>Kelola spesifikasi teknis dan jenis tiang meter/daN yang tersedia.</p>
            </a>

            <a href="manage.php?table=vendor" class="menu-card">
                <i class="fas fa-industry"></i>
                <h3>Data Vendor</h3>
                <p>Kelola daftar mitra penyedia barang dan profil vendor resmi PLN.</p>
            </a>
            <a href="kelola_wo.php" class="menu-card">
                <i class="fas fa-tools"></i>
                <h3>Data WO</h3>
                <p>Kelola Work Order (WO) terkait pemasangan dan pemeliharaan tiang.</p>
            </a>
        </div>
    </main>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('expanded');
        }

        // Pastikan nav links bekerja
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('a.nav-item');
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href && href !== '#') {
                        window.location.href = href;
                    }
                });
            });
        });
    </script>
</body>
</html>