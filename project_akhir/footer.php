<footer style="margin-top:40px;background:#05060a;border-top:1px solid rgba(255,255,255,0.06);">
    <style>
        .footer-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 20px 20px;
            font-family: 'Inter', system-ui, sans-serif;
            color: #f5f5f5;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr 1fr 1fr;
            gap: 26px;
            font-size: 13px;
        }
        .footer-title {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: .16em;
            color: #e0c677;
            margin-bottom: 10px;
        }
        .footer-about {
            font-size: 13px;
            color: #a4a4b2;
            margin-bottom: 10px;
        }
        .footer-links a {
            display: block;
            color: #c4c4d2;
            margin-bottom: 6px;
            text-decoration: none;
        }
        .footer-links a:hover {
            color: #ffffff;
        }
        .footer-badge {
            display: inline-block;
            margin-top: 8px;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid rgba(212,175,55,0.5);
            font-size: 11px;
            color: #e0c677;
        }
        .footer-social {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }
        .footer-social a {
            width: 28px;
            height: 28px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #f5f5f5;
            text-decoration: none;
        }
        .footer-social a:hover {
            border-color: #d4af37;
        }
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.05);
            margin-top: 20px;
            padding-top: 12px;
            text-align: center;
            font-size: 12px;
            color: #818191;
        }
        @media (max-width: 900px) {
            .footer-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
        @media (max-width: 600px) {
            .footer-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="footer-inner">
        <div class="footer-grid">
            <div>
                <div class="footer-title">Our Hotel</div>
                <p class="footer-about">
                    A modern stay with warm hospitality, designed for travelers who appreciate comfort,
                    style, and thoughtful service.
                </p>
                <div class="footer-badge">4.8 ★ Guest Rating</div>
            </div>

            <div>
                <div class="footer-title">Explore</div>
                <div class="footer-links">
                    <a href="index.php">Home</a>
                    <a href="rooms.php">Rooms &amp; Suites</a>
                    <a href="#amenities">Amenities</a>
                    <a href="#nearby">Nearby Attractions</a>
                </div>
            </div>

            <div>
                <div class="footer-title">Support</div>
                <div class="footer-links">
                    <a href="contact.php">Contact Us</a>
                    <a href="faq.php">FAQs</a>
                    <a href="terms.php">Terms &amp; Conditions</a>
                    <a href="privacy.php">Privacy Policy</a>
                </div>
            </div>

            <div>
                <div class="footer-title">Connect</div>
                <div class="footer-links">
                    <span>Phone: +00 123 456 789</span><br>
                    <span>Email: hello@yourhotel.com</span><br>
                    <span>Address: City, Country</span>
                </div>
                <div class="footer-social">
                    <a href="#"><span>f</span></a>
                    <a href="#"><span>in</span></a>
                    <a href="#"><span>ig</span></a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            © <?php echo date('Y'); ?> Your Hotel Name. All rights reserved.
        </div>
    </div>
</footer>
