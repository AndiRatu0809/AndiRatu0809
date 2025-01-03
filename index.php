<?php
session_start();
include '../config/db.php'; // Koneksi ke database

// Ambil data kegiatan dari database
$stmt = $pdo->prepare("SELECT * FROM activities ORDER BY created_at DESC");
$stmt->execute();
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web HIPERMAWA</title>
    <style>
        /* Global Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
            scroll-behavior: smooth;
        }

        body {
            background-color: #0d0d0d;
            color: #fff;
            overflow-x: hidden;
            position: relative;
        }

        /* Header */
        .header {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 20px 0;
            text-align: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
        }

        .logo img {
            height: 50px;
        }

        .header ul {
            list-style: none;
            display: flex;
            justify-content: flex-end;
            flex-grow: 1;
        }

        .header ul li {
            margin: 0 20px;
        }

        .header ul li a {
            text-decoration: none;
            color: #fff;
            font-size: 16px;
            padding: 10px 15px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .header ul li a:hover {
            background-color: #00aaff;
            border-radius: 10px;
            color: #000;
        }

        /* Main Content */
        .main-content {
            padding: 80px 20px;
            margin-top: 20px;
        }

        .content {
            text-align: center;
            margin-bottom: 30px;
            opacity: 0;
            transform: translateY(50px);
            transition: opacity 1s ease, transform 1s ease;
            position: relative;
        }

        .content.show {
            opacity: 1;
            transform: translateY(0);
        }

        .content h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .content h3 {
            color: #00aaff;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }

        .content p {
            margin-bottom: 20px;
            font-size: 1rem;
            line-height: 1.6;
        }

        .btn {
            display: inline-block;
            background-color: #00aaff;
            color: #fff;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            transition: transform 0.3s ease-in-out;
        }

        .btn:hover {
            background-color: #0088cc;
            transform: scale(1.05);
        }

        /* Gambar dalam kegiatan */
        .activity-media img {
            width: 100%;
            height: auto;
            max-height: 300px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
        }

        .activity-media img:hover {
            transform: scale(1.05);
        }

        .org-structure {
            padding: 20px 10px;
            background: linear-gradient(135deg, #1a1a1a 0%, #0d0d0d 100%);
            font-family: 'Arial', sans-serif;
            max-width: 900px;
            margin: 0 auto;
        }

        .org-title {
            text-align: center;
            color: #00aaff;
            margin-bottom: 30px;
            font-size: 1.8em;
            text-transform: uppercase;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .content {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .org-level {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
            position: relative;
        }

        .org-box {
            text-align: center;
            transition: all 0.3s ease;
            background: rgba(0, 170, 255, 0.05);
            padding: 10px;
            border-radius: 10px;
            width: 140px;
        }

        .org-box:hover {
            transform: translateY(-5px);
            background: rgba(0, 170, 255, 0.1);
        }

        .photo-frame {
            width: 120px;
            height: 120px;
            border-radius: 15px;
            overflow: hidden;
            border: 3px solid #00aaff;
            margin: 0 auto 10px;
            position: relative;
            box-shadow: 0 3px 10px rgba(0, 170, 255, 0.2);
            transition: all 0.3s ease;
        }

        .executive .photo-frame {
            width: 140px;
            height: 140px;
            border-width: 4px;
        }

        .photo-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .org-box:hover .photo-frame {
            box-shadow: 0 5px 15px rgba(0, 170, 255, 0.4);
            border-color: #33bbff;
        }

        .org-box:hover img {
            transform: scale(1.1);
        }

        .position-title {
            color: #00aaff;
            font-size: 0.9em;
            margin-bottom: 3px;
            font-weight: bold;
        }

        .executive .position-title {
            font-size: 1em;
            color: #33bbff;
        }

        .person-name {
            color: #cccccc;
            font-size: 0.85em;
        }

        .department-level {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 15px;
            padding: 10px;
            margin-top: 20px;
            position: relative;
        }

        .department-level::before {
            content: '';
            position: absolute;
            top: -20px;
            left: 50%;
            width: 2px;
            height: 20px;
            background: #00aaff;
            transform: translateX(-50%);
        }

        .department-box {
            background: rgba(0, 170, 255, 0.05);
            border-radius: 10px;
            padding: 10px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .department-box:hover {
            transform: translateY(-3px);
            background: rgba(0, 170, 255, 0.1);
        }

        @media (max-width: 600px) {
            .org-level {
                flex-direction: column;
                align-items: center;
            }
            
            .photo-frame, .executive .photo-frame {
                width: 100px;
                height: 100px;
            }
            
            .org-box {
                width: 120px;
            }
            
            .department-level {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Bubble Effect */
        .bubble {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            animation: bubble-animation linear infinite;
        }

        @keyframes bubble-animation {
            from { transform: translateY(100vh) scale(0); }
            to { transform: translateY(-100vh) scale(1); }
        }

        /* Background Image */
        .background-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('background.jpg');
            background-size: cover;
            background-position: center;
            filter: blur(5px);
            z-index: -1;
        }

        /* Card Container for Gallery */
        .gallery {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 30px;
        }

        .gallery img {
            width: 100%;
            height: auto;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }

        .gallery img:hover {
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            .gallery {
                grid-template-columns: 1fr 1fr;
            }

            .content h1 {
                font-size: 2rem;
            }

            .content h3 {
                font-size: 1.2rem;
            }

            .profile-picture img {
                width: 250px;
            }
        }
    </style>
</head>
<body>
    <!-- Background Image -->
    <div class="background-image"></div>

    <!-- Header with Logo and Menu -->
    <div class="header">
        <div class="logo">
        <img src="../modules/logo.png" alt="Logo" class="login-logo">
        </div>
        <ul>
            <li><a href="#kegiatan">Kegiatan</a></li>
            <li><a href="#struktur">Struktural Organisasi</a></li>
            <li><a href="#visi_misi">Visi Misi</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">

        <!-- Kegiatan Section -->
        <div id="kegiatan" class="content">
            <h1>Daftar Kegiatan</h1>
            <p>Berikut adalah beberapa kegiatan yang telah dilaksanakan oleh HIPERMAWA.</p>

            <!-- Daftar Kegiatan -->
            <div class="activities-grid">
                <?php foreach ($activities as $activity): ?>
                    <div class="activity-card">
                        <h3><?php echo htmlspecialchars($activity['activity_name']); ?></h3>
                        <p><?php echo htmlspecialchars($activity['activity_description']); ?></p>

                        <!-- Jika ada media, tampilkan -->
                        <?php if (!empty($activity['media_url'])): ?>
                            <?php
                            $file_extension = strtolower(pathinfo($activity['media_url'], PATHINFO_EXTENSION));
                            if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                <!-- Menampilkan gambar -->
                                <div class="activity-media">
                                    <img src="<?php echo htmlspecialchars($activity['media_url']); ?>" alt="Media Kegiatan">
                                </div>
                            <?php elseif (in_array($file_extension, ['mp4', 'webm', 'ogg'])): ?>
                                <!-- Menampilkan video -->
                                <video controls>
                                    <source src="<?php echo htmlspecialchars($activity['media_url']); ?>" type="video/<?php echo $file_extension; ?>">
                                </video>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Struktur Organisasi Section -->
        <div class="org-structure">
        <h1 class="org-title">Struktur Organisasi HIPERMAWA</h1>
        
        <div id="struktur" class="content">
            <div class="org-level executive">
                <div class="org-box">
                    <div class="photo-frame">
                        <img src="../modules/Ketua.jpg" alt="Ketua">
                    </div>
                    <div class="position-title">Ketua Umum</div>
                    <div class="person-name">Arifuddin</div>
                </div>
            </div>

            <div class="org-level executive">
                <div class="org-box">
                    <div class="photo-frame">
                        <img src="../modules/wakil.JPG" alt="Wakil Ketua">
                    </div>
                    <div class="position-title">Wakil Ketua</div>
                    <div class="person-name">Rezaldi Maebul Fatwa</div>
                </div>
                
                <div class="org-box">
                    <div class="photo-frame">
                        <img src="../modules/Sekretaris.jpg" alt="Sekretaris">
                    </div>
                    <div class="position-title">Sekretaris</div>
                    <div class="person-name">Sri Wahyuni</div>
                </div>
                
                <div class="org-box">
                    <div class="photo-frame">
                        <img src="../modules/Bendahara.jpg" alt="Bendahara">
                    </div>
                    <div class="position-title">Bendahara</div>
                    <div class="person-name">Raoda</div>
                </div>
            </div>

            <div class="department-level">
                <div class="department-box">
                    <div class="photo-frame">
                        <img src="/api/placeholder/180/180" alt="PAPO">
                    </div>
                    <div class="position-title">Bidang PAPO</div>
                    <div class="person-name">Muh. Yasim</div>
                </div>

                <div class="department-box">
                    <div class="photo-frame">
                        <img src="../modules/iptek.jpg" alt="IPTEK">
                    </div>
                    <div class="position-title">Bidang IPTEK</div>
                    <div class="person-name">Ainur Rahima</div>
                </div>

                <div class="department-box">
                    <div class="photo-frame">
                        <img src="/api/placeholder/180/180" alt="P3M">
                    </div>
                    <div class="position-title">Bidang P3M</div>
                    <div class="person-name">Dani Pratama</div>
                </div>

                <div class="department-box">
                    <div class="photo-frame">
                        <img src="/api/placeholder/180/180" alt="HUMAS">
                    </div>
                    <div class="position-title">Bidang HUMAS</div>
                    <div class="person-name">Agus</div>
                </div>
            </div>
        </div>
    </div>

        <!-- Visi Misi Section -->
        <div id="visi_misi" class="content">
            <h1>Visi Misi</h1>
            <h3>Visi</h3>
            <p>Menjadi organisasi yang berperan aktif dalam pengembangan mahasiswa.</p>
            <h3>Misi</h3>
            <p>1. Membentuk karakter mahasiswa yang mandiri dan berintegritas.</p>
            <p>2. Meningkatkan kualitas akademik dan non-akademik mahasiswa.</p>
        </div>

        <!-- Contact Section -->
        <div id="contact" class="content">
            <h1>Contact</h1>
            <p>Hubungi kontak di bawah ini jika Anda tertarik bergabung dengan HIPERMAWA:</p>
            <div class="contacts">
                <span>Messenger: user-2626</span>
                <span>WhatsApp: 070 1234 567</span>
                <span>Email: sl.codehub@gmail.com</span>
            </div>
            <a href="#" class="btn">More About Us</a>
        </div>

        <!-- Gallery Section
        <div class="gallery">
            <img src="image1.jpg" alt="Image 1">
            <img src="image2.jpg" alt="Image 2">
            <img src="image3.jpg" alt="Image 3">
        </div>

         Profile Picture -->
        <!-- <div class="profile-picture">
            <img src="Wajo.jpg" alt="Foto Wakil">
        </div> --> 

        <!-- Bubble Effects -->
        <div class="bubbles">
            <?php for ($i = 0; $i < 10; $i++) : ?>
                <div class="bubble" style="width: <?= rand(20,50) ?>px; height: <?= rand(20,50) ?>px; left: <?= rand(0,100) ?>%; animation-duration: <?= rand(5,15) ?>s;"></div>
            <?php endfor; ?>
        </div>

    </div>

    <!-- JavaScript for bubble effect and content fade-in -->
    <script>
        // Bubble effects
        const bubbles = document.querySelectorAll('.bubble');
        bubbles.forEach(bubble => {
            bubble.style.animationDelay = `${Math.random() * -15}s`;
        });

        // Content fade-in effect when scrolling
        const contentSections = document.querySelectorAll('.content');

        function fadeInOnScroll() {
            const windowHeight = window.innerHeight;
            contentSections.forEach(section => {
                const sectionTop = section.getBoundingClientRect().top;
                if (sectionTop < windowHeight - 150) {
                    section.classList.add('show');
                }
            });
        }

        window.addEventListener('scroll', fadeInOnScroll);
        fadeInOnScroll(); // Trigger on page load to handle sections already in view
    </script>
</body>
</html>
