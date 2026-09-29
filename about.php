<?php
include "header.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    <link rel="stylesheet" href="assets/about.css">

    <style>
        .fade-in {
            opacity: 0;
            animation: fadeIn 1.6s ease-out forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .fade-up {
            opacity: 0;
            transform: translateY(25px);
            animation: fadeUp 1.4s ease forwards;
        }

        @keyframes fadeUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .parallax-bg {
            background-attachment: fixed;
        }

        .glass-box {
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .feature-box:hover,
        .value-box:hover {
            transform: translateY(-5px);
            transition: 0.3s ease;
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
            border-left-color: #d4af37;
        }
    </style>
</head>

<body>
    <section class="about-page">
        <div class="about-hero parallax-bg fade-in">
            <h1>NEXORA <span>Hotel Web</span></h1>
            <p>Your comfort is our top priority — experience hospitality with a touch of luxury.</p>
        </div>

        <div class="about-container fade-up glass-box">

            <div class="about-image">
                <img src="./upload/photo-1.jpeg" alt="Hotel Image">
            </div>

            <div class="about-content">
                <h2>Welcome</h2>
                <p>
                    NEXORA adalah platform pemesanan hotel modern yang berfokus pada kenyamanan, kemudahan,
                    dan pengalaman menginap terbaik bagi setiap tamu. Kami hadir untuk memberikan solusi cepat
                    dalam menemukan kamar hotel ideal di berbagai kota di Indonesia.
                </p>

                <p>
                    Dengan jaringan kamar dari berbagai mitra hotel berkualitas, kami memastikan setiap tamu
                    merasakan suasana menginap yang aman, nyaman, dan berkesan. Mulai dari liburan keluarga,
                    perjalanan bisnis, hingga perjalanan romantis. Kami menjadi bagian dari setiap pengalaman Anda.
                </p>

                <div class="about-features">
                    <div class="feature-box">
                        <h3>✔ Easy Booking</h3>
                        <p>Pemesanan cepat, aman, dan tanpa ribet melalui platform modern.</p>
                    </div>
                    <div class="feature-box">
                        <h3>✔ Trusted Quality</h3>
                        <p>Setiap kamar hotel telah dipilih berdasarkan kualitas dan pelayanan terbaik.</p>
                    </div>
                    <div class="feature-box">
                        <h3>✔ 24/7 Support</h3>
                        <p>Dukungan pelanggan siap membantu kapan saja Anda membutuhkan.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="about-mission fade-up glass-box">
            <h2>Our Mission</h2>
            <p>
                Menciptakan pengalaman menginap yang mudah diakses, nyaman, dan berkualitas tinggi untuk setiap tamu.
                Dengan teknologi modern, kami ingin membuat perjalanan Anda lebih sederhana dan menyenangkan.
            </p>
        </div>

        <div class="about-values fade-up glass-box">
            <h2>Our Values</h2>
            <div class="values-grid">
                <div class="value-box">
                    <h3>Hospitality</h3>
                    <p>Pelayanan ramah dan tulus untuk setiap tamu.</p>
                </div>
                <div class="value-box">
                    <h3>Innovation</h3>
                    <p>Teknologi modern untuk mempermudah semua proses.</p>
                </div>
                <div class="value-box">
                    <h3>Comfort</h3>
                    <p>Kenyamanan adalah prioritas utama kami.</p>
                </div>
            </div>
        </div>
    </section>

</body>

</html>