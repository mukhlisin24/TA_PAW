<?php
require 'koneksi.php';
include "header.php";
?>

<link rel="stylesheet" href="./assets/style.css">

<style>
    /* ======= GLOBAL ======= */
    body {
        font-family: "Poppins", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        margin: 0;
        background: #f5f5f7;
        color: #222;
    }

    .container {
        width: 90%;
        max-width: 1200px;
        margin: 0 auto;
    }

    /* ======= HERO ======= */
  .hero {
    height: 80vh;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border-radius: 0 0 30px 30px;
}

/* Animated Background Image */
.hero::before {
    content: "";
    position: absolute;
    inset: 0;
    background: url('upload/photo-2.jpeg') center/cover no-repeat;
    filter: brightness(0.55) blur(2px);
    transform: scale(1.15);
    animation: heroZoom 13s ease-in-out infinite alternate;
}

@keyframes heroZoom {
    from { transform: scale(1.1); }
    to   { transform: scale(1.2); }
}

/* Glass Effect Box */
.hero-content {
    position: relative;
    z-index: 2;
    padding: 45px 55px;
    max-width: 720px;
    text-align: center;
    border-radius: 22px;

    background: rgba(255,255,255,0.12);
    backdrop-filter: blur(14px);
    border: 1px solid rgba(255,255,255,0.25);

    animation: fadeUp 1.1s ease-out forwards;
    opacity: 0;
    margin-top: 30px;
}

@keyframes fadeUp {
    to { opacity: 1; margin-top: 0; }
}

.hero-title {
    font-family: "Playfair Display", serif;
    font-size: 52px !important;
    font-weight: 700;
    color: black !important;
    line-height: 1.2;
    margin-bottom: 18px;
}

.hero-title span {
    color: #d4af37;
}

.hero-subtitle {
    color: #eeeeee;
    font-size: 22px  !important;
    max-width: 520px;
    margin: 0 auto 26px;
    line-height: 1.6;
}

/* BUTTONS */
.hero-buttons {
    display: flex;
    justify-content: center;
    gap: 18px;
    margin-top: 10px;
}

.hero-btn {
    padding: 14px 32px;
    font-size: 15px;
    border-radius: 40px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    text-transform: uppercase;
    transition: .25s;
}

.btn-gold {
    background: #d4af37;
    color: #1f1608;
}

.btn-gold:hover {
    filter: brightness(1.15);
    transform: translateY(-2px);
}

.btn-outline {
    border: 2px solid #fff;
    color: #fff;
    background: transparent;
}

.btn-outline:hover {
    background: rgba(255,255,255,0.18);
    transform: translateY(-2px);
}
    /* ======= SEARCH SECTION ======= */
    .search-section {
        margin-top: -50px;
        margin-bottom: 40px;
        position: relative;
        z-index: 2;
    }

    .search-box {
        background: #fff;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
        border-radius: 16px;
        padding: 16px 18px;
        display: grid;
        grid-template-columns: 2fr 1.5fr 1fr 1.5fr 1fr;
        gap: 10px;
        align-items: center;
    }

    .search-box label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #888;
        margin-bottom: 3px;
        display: block;
    }

    .search-box input,
    .search-box select {
        width: 100%;
        padding: 6px 8px;
        border-radius: 8px;
        border: 1px solid #ddd;
        font-size: 13px;
    }

    .search-box button {
        width: 100%;
        border-radius: 10px;
        border: none;
        padding: 10px 0;
        background: #c59d5f;
        color: #fff;
        font-weight: 600;
        cursor: pointer;
        font-size: 14px;
    }

    /* ======= ABOUT SECTION ======= */
    .about {
        display: grid;
        grid-template-columns: 1.3fr 1fr;
        gap: 30px;
        align-items: center;
        padding: 40px 0 30px;
    }

    .about-left img {
        width: 100%;
        border-radius: 18px;
        object-fit: cover;
    }

    .about-right h2 {
        font-size: 45px;
        margin-bottom: 10px;
        color: #c59d5f;
    }

    .about-right p {
        font-size: 16px;
        color: #666;
        margin-bottom: 8px;
    }

    .about-badges {
        display: flex;
        gap: 10px;
        margin-top: 10px;
        flex-wrap: wrap;
    }

    .badge {
        font-size: 14px;
        text-transform: uppercase;
        border-radius: 999px;
        padding: 6px 10px;
        border: 1px solid #e0d7c7;
        color: #8a6d34;
    }

    /* ======= ROOMS SECTION ======= */
    .section-title {
        font-size: 30px !important;
        margin-bottom: 8px;
        text-align: center;
    }

    .section-subtitle {
        font-size: 13px;
        color: #777;
        text-align: center;
        margin-bottom: 25px;
    }

    .rooms {
        padding-bottom: 40px;
    }

    .room-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .room-card {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        display: flex;
        flex-direction: column;
    }

    .room-card img {
        width: 100%;
        height: 170px;
        object-fit: cover;
    }

    .room-info {
        padding: 12px 14px 14px;
    }

    .room-info h3 {
        font-size: 16px;
        margin-bottom: 4px;
    }

    .room-meta {
        font-size: 12px;
        color: #777;
        margin-bottom: 6px;
    }

    .room-price {
        font-size: 14px;
        font-weight: 600;
        color: #c59d5f;
        margin-bottom: 10px;
    }

    .room-actions {
        display: flex;
        gap: 8px;
    }

    .room-actions .btn,
    .room-actions .btn2 {
        flex: 1;
        text-align: center;
        font-size: 13px;
        padding: 7px 0;
        border-radius: 999px;
        text-decoration: none;
        cursor: pointer;
    }

    .room-actions .btn {
        background: #c59d5f;
        color: #fff;
    }

    .room-actions .btn2 {
        background: #f3eee4;
        color: #8a6d34;
    }

    /* ======= REVIEWS SLIDER ======= */
    .reviews-section {
        background: #fff;
        padding: 35px 0 45px;
    }

    .reviews-wrapper {
        overflow: hidden;
        position: relative;
    }

    .reviews-track {
        display: flex;
        gap: 16px;
        transition: transform .5s ease;
    }

    .review-card {
        min-width: 260px;
        max-width: 260px;
        background: #f9f7f2;
        border-radius: 14px;
        padding: 12px 14px;
        box-shadow: 0 5px 14px rgba(0, 0, 0, 0.05);
    }

    .review-top {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
    }

    .review-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }

    .review-name {
        font-size: 14px;
        font-weight: 600;
    }

    .review-location {
        font-size: 11px;
        color: #888;
    }

    .review-stars {
        font-size: 12px;
        color: #f3b740;
        margin-bottom: 6px;
    }

    .review-text {
        font-size: 12px;
        color: #555;
    }

    /* simple slider dots */
    .review-dots {
        display: flex;
        justify-content: center;
        gap: 6px;
        margin-top: 12px;
    }

    .review-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #ddd;
    }

    .review-dot.active {
        background: #c59d5f;
    }

    /* ======= FOOTER ======= */
    .footer {
        background: #111;
        color: #eee;
        padding: 30px 0 15px;
        margin-top: 30px;
    }

    .footer-grid {
        display: grid;
        grid-template-columns: 2fr 1.3fr 1.3fr;
        gap: 20px;
    }

    .footer h4 {
        font-size: 15px;
        margin-bottom: 8px;
    }

    .footer p,
    .footer li,
    .footer a {
        font-size: 13px;
        color: #ccc;
    }

    .footer ul {
        list-style: none;
        padding: 0;
    }

    .footer a {
        text-decoration: none;
    }

    .footer a:hover {
        color: #c59d5f;
    }

    .footer-bottom {
        text-align: center;
        margin-top: 18px;
        font-size: 12px;
        color: #888;
        border-top: 1px solid #333;
        padding-top: 10px;
    }

    /* ======= RESPONSIVE ======= */

/* TABLET */
@media (max-width: 1024px) {

    /* HERO */
    .hero {
        height: 65vh;
    }

    .hero-title {
        font-size: 30px !important;
    }

    .hero-subtitle {
        font-size: 18px !important;
    }

    /* ABOUT */
    .about {
        grid-template-columns: 1fr;
        text-align: center;
    }

    .about-right h2 {
        font-size: 30px;
       text-align:left;
    }
    

     .about-right p {
         text-align:left;
    }

    /* ROOMS GRID - 2 per row */
    .room-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}


/* MOBILE */
@media (max-width: 600px) {

    /* HERO */
    .hero {
        height: 60vh;
        border-radius: 0 0 20px 20px;
    }

    .hero-content {
        padding: 25px;
        max-width: 90%;
    }

    .hero-title {
        font-size: 40px !important;
    }

    .hero-subtitle {
        font-size: 15px !important;
    }

    /* SEARCH BOX → SINGLE COLUMN */
    .search-box {
        grid-template-columns: 1fr !important;
        row-gap: 12px;
        padding: 20px;
    }

    /* ABOUT */
    .about {
        grid-template-columns: 1fr;
    }

    /* ROOMS GRID — **ONLY 1 ROOM ON MOBILE** */
    .room-grid {
        grid-template-columns: 1fr !important;
    }

    .room-card img {
        height: 180px;
    }

    /* REVIEWS */
    .review-card {
        min-width: 85% !important;
        max-width: 85% !important;
    }

    /* FOOTER GRID */
    .footer-grid {
        grid-template-columns: 1fr;
        text-align: center;
    }

    .about-left img {
        height: 280px;
        object-fit: cover;
    }
      .section-title {
        font-size: 30px !important;
        margin-bottom: 8px;
        text-align: center;
    }

     
}

</style>

<?php
// fetch 6 latest rooms from DB
$rooms = mysqli_query($conn, "SELECT * FROM rooms ORDER BY id DESC LIMIT 6");
?>

<!-- HERO -->
<section class="hero">
    <div class="hero-content">

        <h1 class="hero-title">
            Your Perfect <span>Stay</span> Awaits
        </h1>

        <p class="hero-subtitle">
            Premium rooms, modern comfort, and warm hospitality — crafted to make every stay memorable.
        </p>

        <div class="hero-buttons">
            <a href="rooms.php"><button class="hero-btn btn-gold">Browse Rooms</button></a>
            <a href="#rooms"><button class="hero-btn btn-outline">View Offers</button></a>
        </div>

    </div>
</section>

<!-- SEARCH BOX -->
<section class="search-section" id="search">
    <div class="container search-box">
        <form style="display:contents;" method="get" action="rooms.php">
            <div>
                <label>City / Destination</label>
                <input type="text" name="city" placeholder="e.g. Jakarta, Bali">
            </div>
            <div>
                <label>Room Type</label>
                <select name="type">
                    <option value="">Any Type</option>
                    <option value="Standard">Standard</option>
                    <option value="Deluxe">Deluxe</option>
                    <option value="Suite">Suite</option>
                    <option value="Family">Family</option>
                    <option value="Premium">Premium</option>
                </select>
            </div>
            <div>
                <label>Guests</label>
                <input type="number" name="guests" min="1" placeholder="2">
            </div>
            <div>
                <label>Max Budget / Night</label>
                <input type="number" name="max_price" min="0" placeholder="e.g. 1500000">
            </div>
            <div>
                <label>&nbsp;</label>
                <button type="submit">Search</button>
            </div>
        </form>
    </div>
</section>

<!-- ABOUT -->
<section id="about" class="about container">
    <div class="about-left">
        <img src="./upload/photo-1.jpeg" alt="Hotel Lobby">
    </div>
    <div class="about-right">
        <h2>Welcome to Hotel Web</h2>
        <p>We combine modern design, comfort, and personalized service to create the perfect stay for business and
            leisure guests.</p>
        <p>Located in prime destinations across Indonesia, our rooms are crafted for relaxation, productivity, and
            memorable experiences.</p>
        <div class="about-badges">
            <span class="badge">24/7 Reception</span>
            <span class="badge">Free High-Speed WiFi</span>
            <span class="badge">Breakfast Available</span>
            <span class="badge">Airport Pickup</span>
        </div>
    </div>
</section>

<!-- FEATURED ROOMS -->
<section id="rooms" class="container rooms">
    <h2 class="section-title">Featured Rooms</h2>
    <p class="section-subtitle">Explore some of our most popular stays.</p>

    <div class="room-grid">
        <?php if (mysqli_num_rows($rooms) > 0): ?>
            <?php while ($r = mysqli_fetch_assoc($rooms)): ?>
                <div class="room-card">
                    <?php
                    $img = !empty($r['image']) ? 'uploads/' . $r['image'] : 'https://images.unsplash.com/photo-1554995207-c18c203602cb?auto=format&fit=crop&w=800&q=80';
                    ?>
                    <img src="<?php echo $img; ?>" alt="Room Image">
                    <div class="room-info">
                        <h3><?php echo htmlspecialchars($r['title']); ?></h3>
                        <div class="room-meta">
                            <?php echo htmlspecialchars($r['city']); ?> ·
                            <?php echo htmlspecialchars($r['type']); ?> ·
                            Up to <?php echo (int) $r['capacity']; ?> guests
                        </div>
                        <div class="room-price">
                            Rp <?php echo number_format($r['price_per_night']); ?> / night
                        </div>
                        <div class="room-actions">
                            <a href="room_detail.php?id=<?php echo $r['id']; ?>" class="btn">View Details</a>
                            <a href="rooms.php?room_id=<?php echo $r['id']; ?>" class="btn2">Book Now</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center;">No rooms found.</p>
        <?php endif; ?>
    </div>
</section>

<!-- REVIEWS SLIDER (FAKE DATA) -->
<section class="reviews-section">
    <div class="container">
        <h2 class="section-title">What Guests Say</h2>
        <p class="section-subtitle">Real experiences from guests who stayed with us.</p>

        <div class="reviews-wrapper">
            <div class="reviews-track" id="reviewsTrack">
                <div class="review-card">
                    <div class="review-top">
                        <img class="review-avatar" src="https://randomuser.me/api/portraits/women/65.jpg" alt="">
                        <div>
                            <div class="review-name">Sarah Johnson</div>
                            <div class="review-location">Jakarta · Business Trip</div>
                        </div>
                    </div>
                    <div class="review-stars">★★★★★</div>
                    <div class="review-text">“Room was clean, stylish, and the staff were extremely helpful. Perfect for
                        my business meetings.”</div>
                </div>

                <div class="review-card">
                    <div class="review-top">
                        <img class="review-avatar" src="https://randomuser.me/api/portraits/men/31.jpg" alt="">
                        <div>
                            <div class="review-name">Michael Tan</div>
                            <div class="review-location">Bali · Holiday</div>
                        </div>
                    </div>
                    <div class="review-stars">★★★★★</div>
                    <div class="review-text">“Amazing view, delicious breakfast, and a very comfortable bed. We will
                        definitely come back!”</div>
                </div>

                <div class="review-card">
                    <div class="review-top">
                        <img class="review-avatar" src="https://randomuser.me/api/portraits/women/22.jpg" alt="">
                        <div>
                            <div class="review-name">Ayu Putri</div>
                            <div class="review-location">Bandung · Family Trip</div>
                        </div>
                    </div>
                    <div class="review-stars">★★★★☆</div>
                    <div class="review-text">“Family room was spacious, kids loved it. The hotel location is close to
                        many attractions.”</div>
                </div>

                <div class="review-card">
                    <div class="review-top">
                        <img class="review-avatar" src="https://randomuser.me/api/portraits/men/46.jpg" alt="">
                        <div>
                            <div class="review-name">David Lee</div>
                            <div class="review-location">Lombok · Honeymoon</div>
                        </div>
                    </div>
                    <div class="review-stars">★★★★★</div>
                    <div class="review-text">“Romantic ambiance, great service, and beautiful surroundings. Couldn’t ask
                        for more.”</div>
                </div>
            </div>
        </div>

        <div class="review-dots">
            <span class="review-dot active"></span>
            <span class="review-dot"></span>
            <span class="review-dot"></span>
            <span class="review-dot"></span>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="footer">
    <div class="container footer-grid">
        <div>
            <h4>Hotel Web</h4>
            <p>Your trusted partner for modern and comfortable stays in Indonesia’s favorite destinations.</p>
        </div>
        <div>
            <h4>Contact</h4>
            <p>Jl. Contoh No. 123, Jakarta</p>
            <p>Phone: +62 812-3456-7890</p>
            <p>Email: info@hotelweb.com</p>
        </div>
        <div>
            <h4>Follow Us</h4>
            <ul>
                <li><a href="#">Instagram</a></li>
                <li><a href="#">Facebook</a></li>
                <li><a href="#">Tripadvisor</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        © <?php echo date('Y'); ?> Hotel Web — All Rights Reserved.
    </div>
</footer>

<script>
    // simple auto slider for reviews
    let currentSlide = 0;
    const track = document.getElementById('reviewsTrack');
    const cards = document.querySelectorAll('.review-card');
    const dots = document.querySelectorAll('.review-dot');

    function updateSlider() {
        const cardWidth = cards[0].offsetWidth + 16; // width + gap
        track.style.transform = 'translateX(' + (-cardWidth * currentSlide) + 'px)';
        dots.forEach((d, i) => d.classList.toggle('active', i === currentSlide));
    }
    setInterval(() => {
        if (!cards.length) return;
        currentSlide = (currentSlide + 1) % cards.length;
        updateSlider();
    }, 3500);
</script>