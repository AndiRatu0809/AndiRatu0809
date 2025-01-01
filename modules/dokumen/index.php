<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Drive UI</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        /* Sidebar */
        .sidebar {
            width: 240px;
            height: 100vh;
            background-color: #fff;
            border-right: 1px solid #e0e0e0;
            position: fixed;
            padding-top: 20px;
        }

        .sidebar ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .sidebar ul li {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #5f6368;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .sidebar ul li i {
            margin-right: 10px;
            font-size: 18px;
            color: #5f6368;
        }

        .sidebar ul li:hover {
            background-color: #e8f0fe;
            color: #1967d2;
        }

        .sidebar ul .active {
            background-color: #e8f0fe;
            color: #1967d2;
            font-weight: bold;
        }

        /* Main Content */
        .main-content {
            margin-left: 240px;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .header h2 {
            color: #202124;
        }

        .upload-section {
            margin-top: 20px;
        }

        .upload-section input[type="file"] {
            display: none;
        }

        .upload-section label {
            padding: 10px 20px;
            background-color: #1a73e8;
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <ul id="menu-list">
            <li class="back-dashboard"><i>⬅️</i> Kembali ke Dashboard</li>
            <li class="active" data-content="beranda"><i>🏠</i> Beranda</li>
            <li data-content="dokumentasi-saya"><i>📂</i> Dokumentasi Saya</li>
            <li data-content="komputer"><i>💻</i> Komputer</li>
            <li data-content="dibagikan"><i>👥</i> Dibagikan kepada saya</li>
            <li data-content="terbaru"><i>🕒</i> Terbaru</li>
            <li data-content="berbintang"><i>⭐</i> Berbintang</li>
            <li data-content="spam"><i>🚫</i> Spam</li>
            <li data-content="sampah"><i>🗑️</i> Sampah</li>
            <li data-content="penyimpanan"><i>💾</i> Penyimpanan</li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2 id="main-title">Selamat datang di Drive Hipermawa</h2>
            <input type="text" placeholder="Telusuri di Drive">
        </div>

        <!-- Konten Dinamis Berdasarkan Pilihan Sidebar -->
        <div class="content-section active" id="beranda">
            <h3>Ini adalah konten Beranda</h3>
            <p>Selamat datang di Beranda.</p>
        </div>
        
        <div class="content-section" id="drive-saya">
            <h3>Ini adalah konten Drive Saya</h3>
            <p>Di sini Anda bisa melihat file yang Anda simpan.</p>
        </div>

        <div class="content-section" id="komputer">
            <h3>Ini adalah konten Komputer</h3>
            <p>Sinkronkan file dari komputer Anda ke drive.</p>
        </div>

        <div class="content-section" id="dibagikan">
            <h3>Ini adalah konten Dibagikan kepada saya</h3>
            <p>Lihat file yang dibagikan kepada Anda.</p>
        </div>

        <div class="content-section" id="terbaru">
            <h3>Ini adalah konten Terbaru</h3>
            <p>File terbaru yang telah Anda akses.</p>
        </div>

        <div class="content-section" id="berbintang">
            <h3>Ini adalah konten Berbintang</h3>
            <p>File favorit Anda.</p>
        </div>

        <div class="content-section" id="spam">
            <h3>Ini adalah konten Spam</h3>
            <p>File yang dianggap sebagai spam.</p>
        </div>

        <div class="content-section" id="sampah">
            <h3>Ini adalah konten Sampah</h3>
            <p>File yang telah Anda hapus.</p>
        </div>

        <div class="content-section" id="penyimpanan">
            <h3>Ini adalah konten Penyimpanan</h3>
            <p>Lihat penggunaan penyimpanan Anda.</p>
        </div>

        <!-- Bagian untuk Unggah File -->
        <div class="upload-section">
            <h3>Unggah File</h3>
            <label for="file-upload">Pilih Foto atau Video untuk Diunggah</label>
            <input type="file" id="file-upload" accept="image/*,video/*">
            <p id="file-name"></p>
        </div>
    </div>

    <script>
        const menuItems = document.querySelectorAll("#menu-list li[data-content]");
        const backDashboard = document.querySelector(".back-dashboard");
        const mainTitle = document.getElementById("main-title");
        const contentSections = document.querySelectorAll(".content-section");
        const fileUpload = document.getElementById("file-upload");
        const fileName = document.getElementById("file-name");

        menuItems.forEach((item) => {
            item.addEventListener("click", () => {
                menuItems.forEach((menu) => menu.classList.remove("active"));
                item.classList.add("active");

                mainTitle.textContent = item.textContent;

                const contentId = item.getAttribute("data-content");
                contentSections.forEach((section) => {
                    if (section.id === contentId) {
                        section.classList.add("active");
                    } else {
                        section.classList.remove("active");
                    }
                });
            });
        });

        backDashboard.addEventListener("click", () => {
            window.location.href = '../../modules/dashboard/index.php';
        });

        fileUpload.addEventListener("change", () => {
            if (fileUpload.files.length > 0) {
                fileName.textContent = "File terpilih: " + fileUpload.files[0].name;
            }
        });
    </script>
</body>
</html>
