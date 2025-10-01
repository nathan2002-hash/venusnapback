<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venusnap - Discover Amazing Art</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Previous CSS remains the same until the carousel section */

        /* Updated Carousel Section for Portrait Layout */
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
        }

        .carousel {
            display: flex;
            transition: transform 0.5s ease;
            gap: 1.5rem;
            padding: 0 1rem;
        }

        .carousel-item {
            flex: 0 0 auto;
            width: 300px; /* Fixed width for portrait items */
            position: relative;
            height: 450px; /* Taller height for portrait */
            overflow: hidden;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .carousel-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
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
            padding: 1.5rem;
            background: linear-gradient(to top, rgba(15, 23, 42, 0.9) 0%, transparent 100%);
            color: white;
        }

        .carousel-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .carousel-description {
            font-size: 0.9rem;
            margin-bottom: 1rem;
            max-width: 100%;
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
            left: 10px;
        }

        .carousel-next {
            right: 10px;
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

        /* Mobile Styles for Portrait Carousel */
        @media (max-width: 768px) {
            .carousel-container {
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none; /* Firefox */
                -ms-overflow-style: none; /* IE and Edge */
            }

            .carousel-container::-webkit-scrollbar {
                display: none; /* Chrome, Safari and Opera */
            }

            .carousel {
                padding: 0 1rem;
                scroll-snap-type: x mandatory;
            }

            .carousel-item {
                scroll-snap-align: center;
                width: 280px;
                height: 420px;
            }

            .carousel-nav {
                display: none; /* Hide navigation arrows on mobile */
            }

            .carousel-indicators {
                display: none; /* Hide indicators on mobile */
            }
        }

        /* Rest of the CSS remains the same */
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
                <button class="cta-button">Download App</button>
            </a>

        </header>

        <!-- Hero Section -->
        <section class="hero">
            <h1>A Home for Visual Creators</h1>
            <p>Get Early Access and Monetization, Share your quotes, memes, art, and photography in Albums that inspire, entertain, and connect with people who truly appreciate creativity.</p>
           <div class="hero-buttons">
                <a href="{{ route('register') }}" class="cta-button">Start Creating</a><a href="#gallery" class="secondary-button">Explore Gallery</a>
            </div>
        </section>

        <!-- Updated Carousel Section with Portrait Layout -->
        <section class="carousel-section" id="gallery">
            <h2 class="section-title">Featured Creations</h2>
            <div class="carousel-container">
                <div class="carousel">
                   <!-- Sample posts - replace with your dynamic content -->
                   <div class="carousel-item">
                        <img src="https://images.unsplash.com/photo-1500462918059-b1a0cb512f1d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80" alt="Portrait Art">
                        <div class="carousel-content">
                            <div class="carousel-tags">
                                <span class="carousel-tag">Abstract Portraits</span>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=688&q=80" alt="Portrait Photography">
                        <div class="carousel-content">
                            <div class="carousel-tags">
                                <span class="carousel-tag">Portrait Photography</span>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=764&q=80" alt="Artistic Portrait">
                        <div class="carousel-content">
                            <div class="carousel-tags">
                                <span class="carousel-tag">Artistic Vision</span>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80" alt="Creative Portrait">
                        <div class="carousel-content">
                            <div class="carousel-tags">
                                <span class="carousel-tag">Creative Expression</span>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="https://images.unsplash.com/photo-1488426862026-3ee34a7d66df?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80" alt="Professional Portrait">
                        <div class="carousel-content">
                            <div class="carousel-tags">
                                <span class="carousel-tag">Professional Work</span>
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
            <p class="cta-subtitle">Join a growing community of creators building Albums that people love. Your creativity deserves recognition start today.</p>
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
                <a href="/">Venusnap</a>
                <a href="/#about">About</a>
                <a href="/#contact">Help</a>
                <a href="/privacy/policy">Privacy</a>
                <a href="/terms/of/service">Terms</a>
            </div>
            <p class="copyright">© 2025 Venusnap. All rights reserved.</p>
        </footer>
    </div>

    <script>
        // Updated Carousel functionality for portrait layout
        document.addEventListener('DOMContentLoaded', function() {
            const carousel = document.querySelector('.carousel');
            const items = document.querySelectorAll('.carousel-item');
            const prevBtn = document.querySelector('.carousel-prev');
            const nextBtn = document.querySelector('.carousel-next');
            const indicators = document.querySelectorAll('.indicator');
            const container = document.querySelector('.carousel-container');

            let currentIndex = 0;
            const totalItems = items.length;
            const itemWidth = items[0].offsetWidth + 24; // width + gap
            const isMobile = window.innerWidth <= 768;

            // Update carousel position
            function updateCarousel() {
                if (!isMobile) {
                    carousel.style.transform = `translateX(-${currentIndex * itemWidth}px)`;
                }

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
                if (currentIndex < totalItems - 1) {
                    currentIndex++;
                    updateCarousel();
                }
            }

            // Previous slide
            function prevSlide() {
                if (currentIndex > 0) {
                    currentIndex--;
                    updateCarousel();
                }
            }

            // Go to specific slide
            function goToSlide(index) {
                currentIndex = index;
                updateCarousel();
            }

            // Event listeners for desktop navigation
            if (!isMobile) {
                nextBtn.addEventListener('click', nextSlide);
                prevBtn.addEventListener('click', prevSlide);

                indicators.forEach((indicator, index) => {
                    indicator.addEventListener('click', () => goToSlide(index));
                });

                // Auto advance carousel on desktop
                setInterval(nextSlide, 4000);
            }

            // Mobile swipe functionality
            if (isMobile) {
                let startX = 0;
                let currentX = 0;
                let isDragging = false;

                container.addEventListener('touchstart', (e) => {
                    startX = e.touches[0].clientX;
                    isDragging = true;
                });

                container.addEventListener('touchmove', (e) => {
                    if (!isDragging) return;
                    currentX = e.touches[0].clientX;
                });

                container.addEventListener('touchend', () => {
                    if (!isDragging) return;

                    const diffX = startX - currentX;
                    const threshold = 50;

                    if (Math.abs(diffX) > threshold) {
                        if (diffX > 0) {
                            // Swiped left
                            nextSlide();
                        } else {
                            // Swiped right
                            prevSlide();
                        }
                    }

                    isDragging = false;
                });
            }

            // Mobile menu functionality
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const navLinks = document.querySelector('.nav-links');

            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', () => {
                    navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
                });
            }

            // Adjust menu on resize
            window.addEventListener('resize', () => {
                if (window.innerWidth > 768) {
                    if (navLinks) navLinks.style.display = 'flex';
                } else {
                    if (navLinks) navLinks.style.display = 'none';
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
</body>
</html>
