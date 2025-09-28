<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venusnap - Discover Amazing Art</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary: #7c3aed;
            --primary-dark: #6d28d9;
            --primary-light: #8b5cf6;
            --accent: #d8b4fe;
            --text-light: #f8fafc;
            --text-dark: #1e293b;
            --bg-dark: #0f172a;
            --bg-card: rgba(255, 255, 255, 0.1);
        }

        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            color: var(--text-light);
            overflow-x: hidden;
            min-height: 100vh;
        }

        .container {
            position: relative;
            min-height: 100vh;
            width: 100%;
            overflow: hidden;
        }

        /* Header Styles */
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            padding: 1.5rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo i {
            color: var(--primary);
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            color: var(--text-light);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            position: relative;
        }

        .nav-links a:hover {
            color: var(--accent);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .cta-button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .cta-button:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(124, 58, 237, 0.3);
        }

        /* Hero Section */
        .hero {
            padding: 10rem 5% 5rem;
            text-align: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            background: linear-gradient(to right, #fff, var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.2;
        }

        .hero p {
            font-size: 1.25rem;
            margin-bottom: 2.5rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            color: #cbd5e1;
            line-height: 1.6;
        }

        .hero-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.hero-buttons a {
    white-space: nowrap;
}

        .secondary-button {
            background: transparent;
            color: white;
            border: 2px solid var(--primary);
            padding: 0.75rem 1.5rem;
            border-radius: 2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .secondary-button:hover {
            background: rgba(124, 58, 237, 0.1);
            transform: translateY(-2px);
        }

        /* Carousel Section */
        .carousel-section {
            padding: 5rem 5%;
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            font-weight: 700;
        }

        .carousel-container {
            position: relative;
            width: 100%;
            overflow: hidden;
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .carousel {
            display: flex;
            transition: transform 0.5s ease;
        }

        .carousel-item {
            min-width: 100%;
            position: relative;
            height: 500px;
            overflow: hidden;
        }

        .carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .carousel-content {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 2rem;
            background: linear-gradient(to top, rgba(15, 23, 42, 0.9) 0%, transparent 100%);
            color: white;
        }

        .carousel-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .carousel-description {
            font-size: 1rem;
            margin-bottom: 1rem;
            max-width: 600px;
        }

        .carousel-tags {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .carousel-tag {
            background: var(--bg-card);
            padding: 0.4rem 0.8rem;
            border-radius: 1rem;
            font-size: 0.8rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .carousel-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.2);
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            transition: all 0.3s;
            z-index: 10;
        }

        .carousel-nav:hover {
            background: var(--primary);
        }

        .carousel-prev {
            left: 20px;
        }

        .carousel-next {
            right: 20px;
        }

        .carousel-indicators {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }

        .indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            cursor: pointer;
            transition: all 0.3s;
        }

        .indicator.active {
            background: var(--primary);
            transform: scale(1.2);
        }

        /* Features Section */
        .features {
            padding: 5rem 5%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: var(--bg-card);
            padding: 2rem;
            border-radius: 1rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .feature-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .feature-description {
            color: #cbd5e1;
            line-height: 1.6;
        }

        /* CTA Section */
        .cta-section {
            padding: 6rem 5%;
            text-align: center;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            margin: 5rem 0 0;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="%236d28d9"><polygon points="0,0 1000,50 1000,100 0,100"></polygon></svg>');
            background-size: cover;
            opacity: 0.1;
        }

        .cta-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
        }

        .cta-subtitle {
            font-size: 1.2rem;
            margin-bottom: 2.5rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .store-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .store-button {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.1);
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .store-button:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .store-icon {
            font-size: 1.5rem;
        }

        .store-text {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .store-label {
            font-size: 0.8rem;
        }

        .store-name {
            font-size: 1.1rem;
            font-weight: 600;
        }

        /* Footer */
        footer {
            padding: 3rem 5%;
            text-align: center;
            background: var(--bg-dark);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: var(--accent);
        }

        .copyright {
            color: #64748b;
            font-size: 0.9rem;
        }

        /* Mobile Styles */
        @media (max-width: 768px) {
            header {
                padding: 1rem 5%;
            }

            .nav-links {
                display: none;
            }

            .mobile-menu-btn {
                display: block;
                background: none;
                border: none;
                color: white;
                font-size: 1.5rem;
                cursor: pointer;
            }

            .hero {
                padding: 8rem 5% 3rem;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .carousel-item {
                height: 400px;
            }

            .carousel-content {
                padding: 1.5rem;
            }

            .carousel-title {
                font-size: 1.5rem;
            }

            .carousel-nav {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .cta-title {
                font-size: 2rem;
            }

            .cta-subtitle {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 480px) {
            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .carousel-item {
                height: 350px;
            }

            .carousel-content {
                padding: 1rem;
            }

            .carousel-title {
                font-size: 1.3rem;
            }

            .carousel-description {
                font-size: 0.9rem;
            }

            .feature-card {
                padding: 1.5rem;
            }

            .cta-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header>
            <a href="/" class="logo">
                Venusnap
            </a>

            <div class="nav-links">
                <a href="#features">Features</a>
                <a href="#gallery">Gallery</a>
                <a href="#community">Community</a>
                <a href="{{ route('login') }}">Sign In</a>
            </div>
            <a href="https://play.google.com/store/apps/details?id=com.venusnap.app"
                target="_blank"
                onclick="trackClick('start_album')">
                <button class="download-btn">Download App</button>
            </a>

            {{-- <a href="{{ route('register') }}" class="cta-button">Download App</a> --}}
        </header>

        <!-- Hero Section -->
        <section class="hero">
            <h1>A Home for Visual Creators</h1>
            <p>Get Eaarly Access and Monetization, Share your quotes, memes, art, and photography in Albums that inspire, entertain, and connect with people who truly appreciate creativity.</p>
           <div class="hero-buttons">
                <a href="{{ route('register') }}" class="cta-button">Start Creating</a><a href="#gallery" class="secondary-button">Explore Gallery</a>
            </div>
        </section>

        <!-- Carousel Section -->
        <section class="carousel-section" id="gallery">
            <h2 class="section-title">Featured Creations</h2>
            <div class="carousel-container">
                <div class="carousel">
                    <div class="carousel-item">
                        <img src="https://images.unsplash.com/photo-1579546929662-711aa81148cf?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Abstract Art">
                        <div class="carousel-content">
                            <h3 class="carousel-title">Colorful Abstract</h3>
                            <p class="carousel-description">Vibrant colors blending in perfect harmony, creating an emotional visual experience.</p>
                            <div class="carousel-tags">
                                <span class="carousel-tag">Abstract</span>
                                <span class="carousel-tag">Colorful</span>
                                <span class="carousel-tag">Modern</span>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="https://images.unsplash.com/photo-1541961017774-22349e4a1262?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2058&q=80" alt="Portrait Photography">
                        <div class="carousel-content">
                            <h3 class="carousel-title">Emotional Portrait</h3>
                            <p class="carousel-description">Capturing the raw emotion and story behind every face through the lens.</p>
                            <div class="carousel-tags">
                                <span class="carousel-tag">Portrait</span>
                                <span class="carousel-tag">Photography</span>
                                <span class="carousel-tag">Emotion</span>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Landscape Art">
                        <div class="carousel-content">
                            <h3 class="carousel-title">Mountain Serenity</h3>
                            <p class="carousel-description">Peaceful landscapes that transport you to another world of calm and beauty.</p>
                            <div class="carousel-tags">
                                <span class="carousel-tag">Landscape</span>
                                <span class="carousel-tag">Nature</span>
                                <span class="carousel-tag">Serene</span>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="carousel-nav carousel-prev">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="carousel-nav carousel-next">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <div class="carousel-indicators">
                    <div class="indicator active"></div>
                    <div class="indicator"></div>
                    <div class="indicator"></div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features" id="features">
            <h2 class="section-title">Why Creators Love Venusnap</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <h3 class="feature-title">Organize in Albums</h3>
                    <p class="feature-description">Create beautiful albums to showcase your work thematically and tell your creative story.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="feature-title">Engage with Community</h3>
                    <p class="feature-description">Connect with fellow creators, get feedback, and find inspiration in a supportive environment.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="feature-title">Track Your Growth</h3>
                    <p class="feature-description">Monitor your audience engagement and understand what resonates with your followers.</p>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section" id="community">
            <h2 class="cta-title">Create. Share. Inspire.</h2>
            <p class="cta-subtitle">Join a growing community of creators building Albums that people love. Your creativity deserves recognition—start today.</p>
            <div class="cta-buttons">
                <a href="{{ route('register') }}" class="cta-button">Start Your Album</a>
                <a href="#gallery" class="secondary-button">See Examples</a>
            </div>
            <div class="store-buttons">
                <a href="https://play.google.com/store/apps/details?id=com.venusnap.app" class="store-button">
                    <i class="fab fa-google-play store-icon"></i>
                    <div class="store-text">
                        <span class="store-label">GET IT ON</span>
                        <span class="store-name">Google Play</span>
                    </div>
                </a>
                <a href="#" class="store-button">
                    <i class="fab fa-apple store-icon"></i>
                    <div class="store-text">
                        <span class="store-label">Coming Soon</span>
                        <span class="store-name">App Store</span>
                    </div>
                </a>
            </div>
        </section>

        <!-- Footer -->
        <footer>
            <div class="footer-links">
                <a href="#">About</a>
                <a href="#">Blog</a>
                <a href="#">Careers</a>
                <a href="#">Help</a>
                <a href="#">Privacy</a>
                <a href="#">Terms</a>
            </div>
            <p class="copyright">© 2023 Venusnap. All rights reserved.</p>
        </footer>
    </div>

    <script>
        // Carousel functionality
        document.addEventListener('DOMContentLoaded', function() {
            const carousel = document.querySelector('.carousel');
            const items = document.querySelectorAll('.carousel-item');
            const prevBtn = document.querySelector('.carousel-prev');
            const nextBtn = document.querySelector('.carousel-next');
            const indicators = document.querySelectorAll('.indicator');

            let currentIndex = 0;
            const totalItems = items.length;

            // Update carousel position
            function updateCarousel() {
                carousel.style.transform = `translateX(-${currentIndex * 100}%)`;

                // Update indicators
                indicators.forEach((indicator, index) => {
                    if (index === currentIndex) {
                        indicator.classList.add('active');
                    } else {
                        indicator.classList.remove('active');
                    }
                });
            }

            // Next slide
            function nextSlide() {
                currentIndex = (currentIndex + 1) % totalItems;
                updateCarousel();
            }

            // Previous slide
            function prevSlide() {
                currentIndex = (currentIndex - 1 + totalItems) % totalItems;
                updateCarousel();
            }

            // Go to specific slide
            function goToSlide(index) {
                currentIndex = index;
                updateCarousel();
            }

            // Event listeners
            nextBtn.addEventListener('click', nextSlide);
            prevBtn.addEventListener('click', prevSlide);

            indicators.forEach((indicator, index) => {
                indicator.addEventListener('click', () => goToSlide(index));
            });

            // Auto advance carousel
            setInterval(nextSlide, 5000);

            // Mobile menu functionality
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const navLinks = document.querySelector('.nav-links');

            mobileMenuBtn.addEventListener('click', () => {
                navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
            });

            // Adjust menu on resize
            window.addEventListener('resize', () => {
                if (window.innerWidth > 768) {
                    navLinks.style.display = 'flex';
                } else {
                    navLinks.style.display = 'none';
                }
            });

            // Track button clicks
            document.querySelectorAll('.cta-button, .store-button').forEach(button => {
                button.addEventListener('click', function() {
                    const buttonText = this.textContent || this.querySelector('.store-name').textContent;

                    fetch('/track-button-click', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            button_name: buttonText,
                            page_url: window.location.href
                        })
                    })
                    .then(res => res.json())
                    .then(data => console.log('Button click tracked:', data));
                });
            });
        });
    </script>
    <script>
document.querySelector('.download-btn').addEventListener('click', function () {
    fetch('/track-button-click', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            button_name: 'Download App',
            page_url: window.location.href
        })
    })
    .then(res => res.json())
    .then(data => console.log(data));
});
</script>
</body>
</html>
