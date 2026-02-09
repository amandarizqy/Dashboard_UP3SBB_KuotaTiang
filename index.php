<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Kuota Tiang PLN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --pln-blue: #00A3E0;
            --pln-yellow: #FFD100;
        }

        body, html {
            height: 100%;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
            /* Background gradasi agar tidak kaku */
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        /* Container Kartu yang menyesuaikan Window */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            width: 90%; /* Fleksibel mengikuti lebar layar */
            max-width: 600px; /* Batas maksimal agar tidak terlalu lebar di PC */
            padding: 40px;
            border-radius: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Box Logo: Kunci agar TIDAK CROP */
        .logo-wrapper {
            width: 100%;
            max-width: 300px; /* Ukuran logo di dalam kartu */
            margin: 0 auto 30px auto;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 150px; /* Tinggi minimum untuk accommodasi gambar */
        }

        .logo-wrapper img {
            max-width: 100%;
            max-height: 150px;
            height: auto; /* WAJIB: Menjaga proporsi asli */
            display: block;
            object-fit: contain; /* Memastikan seluruh gambar terlihat */
        }

        .welcome-text h1 {
            font-size: clamp(20px, 4vw, 32px);
            margin: 0 0 10px 0;
            color: #222;
        }

        .welcome-text p {
            color: #666;
            margin-bottom: 40px;
            line-height: 1.6;
            font-size: clamp(14px, 2vw, 18px);
        }

        .btn-start {
            background-color: var(--pln-blue);
            color: white;
            text-decoration: none;
            padding: 18px 40px;
            border-radius: 15px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            transition: 0.3s;
            box-shadow: 0 8px 15px rgba(0, 163, 224, 0.2);
        }

        .btn-start:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 20px rgba(0, 163, 224, 0.3);
            background-color: #008cc1;
        }

        /* Respon bila layar sangat kecil */
        @media (max-width: 480px) {
            .glass-card {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

    <div class="glass-card">
        <div class="logo-wrapper">
            <img src="logo_PLN.png" alt="Logo PLN">
        </div>

        <div class="welcome-text">
            <h1>Selamat Datang</h1>
            <p>Sistem Monitoring Kuota Tiang Listrik<br>
            <small>Manajemen Kontrak & Distribusi Vendor</small></p>
            
            <a href="dashboard.php" class="btn-start">
                Masuk ke Dashboard <i class="fas fa-chevron-right"></i>
            </a>
        </div>
    </div>

</body>
</html>