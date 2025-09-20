<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venusnap - Discover Amazing Art</title>
    <link href="{{ asset('assets1/img/logo1.png') }}" rel="icon">
    <link href="{{ asset('assets1/img/logo1.png') }}" rel="apple-touch-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #000;
            color: #fff;
            overflow: hidden;
            height: 100vh;
        }

        .container {
            position: relative;
            height: 100vh;
            width: 100%;
            overflow: hidden;
        }

        .art-container {
            position: absolute;
            height: 100%;
            width: 100%;
        }

        .art-item {
            position: absolute;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: transform 1.2s ease, opacity 1.2s ease;
            transform: translateX(100%);
        }

        .art-item.active {
            opacity: 1;
            transform: translateX(0);
            z-index: 2;
        }

        .art-item.exiting {
            transform: translateX(-100%);
            opacity: 0.5;
            z-index: 1;
        }

        .art-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.4) 30%, rgba(0,0,0,0) 100%);
            z-index: 5;
        }

        .art-info {
            position: absolute;
            bottom: 130px;
            left: 0;
            width: 100%;
            padding: 0 30px;
            z-index: 10;
        }

        .artist {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
            color: #f8f8f8;
            letter-spacing: 0.5px;
        }

        .title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.9);
            color: #fff;
            line-height: 1.2;
        }

        .description {
            font-size: 16px;
            font-weight: 400;
            line-height: 1.5;
            margin-bottom: 15px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
            max-width: 600px;
            color: #f0f0f0;
        }

        .tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 15px;
        }

        .tag {
            background: rgba(255, 255, 255, 0.15);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 14px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .cta-section {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.95));
            padding: 25px 20px;
            text-align: center;
            z-index: 20;
        }

        .cta-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
        }

        .cta-subtitle {
            font-size: 16px;
            margin-bottom: 20px;
            opacity: 0.9;
            font-weight: 400;
            line-height: 1.4;
        }

        .download-btn {
            background: #fff;
            color: #000;
            border: none;
            padding: 16px 40px;
            border-radius: 30px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.3s, background 0.3s;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }

        .download-btn:hover {
            transform: translateY(-2px);
            background: #f8f8f8;
        }

        .progress-bar {
            position: absolute;
            top: 0;
            left: 0;
            height: 4px;
            background: rgba(255, 255, 255, 0.2);
            width: 100%;
            z-index: 30;
        }

        .progress {
            height: 100%;
            background: #fff;
            width: 0%;
            transition: width 6s linear;
        }

        .logo {
            position: absolute;
            top: 25px;
            left: 25px;
            font-size: 24px;
            font-weight: 700;
            z-index: 10;
            letter-spacing: 1px;
        }

        .slide-indicators {
            position: absolute;
            bottom: 180px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 10;
        }

        .indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            transition: all 0.3s ease;
        }

        .indicator.active {
            background: #fff;
            transform: scale(1.3);
        }

        /* Mobile menu button */
        .mobile-menu-btn {
            display: none;
            position: absolute;
            top: 25px;
            right: 25px;
            z-index: 100;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            color: white;
            font-size: 20px;
            cursor: pointer;
            backdrop-filter: blur(10px);
        }

        /* Mobile-specific styles */
        @media (max-width: 768px) {
            body {
                overflow: auto;
            }

            .container {
                height: auto;
                min-height: 100vh;
                overflow: auto;
            }

            .art-container {
                position: relative;
                height: 60vh;
            }

            .art-info {
                position: relative;
                bottom: auto;
                padding: 20px;
                margin-top: -80px;
            }

            .artist {
                font-size: 14px;
                margin-bottom: 6px;
            }

            .title {
                font-size: 20px;
                margin-bottom: 10px;
                line-height: 1.3;
            }

            .description {
                font-size: 14px;
                margin-bottom: 12px;
                line-height: 1.5;
                max-width: 100%;
            }

            .tags {
                gap: 6px;
                margin-top: 12px;
            }

            .tag {
                padding: 5px 12px;
                font-size: 12px;
            }

            .cta-section {
                position: relative;
                padding: 30px 20px;
                background: #000;
            }

            .cta-title {
                font-size: 20px;
                margin-bottom: 10px;
            }

            .cta-subtitle {
                font-size: 14px;
                margin-bottom: 18px;
                line-height: 1.4;
                padding: 0 10px;
            }

            .download-btn {
                padding: 14px 35px;
                font-size: 16px;
                width: 100%;
                max-width: 300px;
            }

            .logo {
                top: 20px;
                left: 20px;
                font-size: 20px;
            }

            .slide-indicators {
                bottom: 200px;
            }

            .mobile-menu-btn {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            /* Adjust art item for mobile */
            .art-item {
                position: relative;
                height: 60vh;
            }

            /* Mobile navigation */
            .mobile-nav {
                position: fixed;
                top: 0;
                right: -100%;
                width: 80%;
                height: 100%;
                background: rgba(0, 0, 0, 0.95);
                z-index: 1000;
                padding: 60px 30px;
                transition: right 0.3s ease;
                backdrop-filter: blur(10px);
            }

            .mobile-nav.active {
                right: 0;
            }

            .mobile-nav a {
                display: block;
                color: white;
                text-decoration: none;
                font-size: 18px;
                margin-bottom: 20px;
                padding: 10px 0;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            .close-menu {
                position: absolute;
                top: 20px;
                right: 20px;
                background: none;
                border: none;
                color: white;
                font-size: 24px;
                cursor: pointer;
            }
        }

        @media (max-width: 480px) {
            .art-info {
                margin-top: -60px;
                padding: 15px;
            }

            .artist {
                font-size: 13px;
            }

            .title {
                font-size: 18px;
            }

            .description {
                font-size: 13px;
            }

            .tag {
                font-size: 11px;
                padding: 4px 10px;
            }

            .cta-title {
                font-size: 18px;
            }

            .cta-subtitle {
                font-size: 13px;
            }

            .slide-indicators {
                bottom: 210px;
            }

            .art-container {
                height: 50vh;
            }

            .art-item {
                height: 50vh;
            }

            .download-btn {
                padding: 12px 25px;
                font-size: 15px;
            }
        }

        /* Safe area insets for notch phones */
        @supports(padding: max(0px)) {
            .logo, .mobile-menu-btn {
                top: max(25px, env(safe-area-inset-top));
                left: max(25px, env(safe-area-inset-left));
            }

            .mobile-menu-btn {
                right: max(25px, env(safe-area-inset-right));
            }

            .cta-section {
                padding-bottom: max(25px, env(safe-area-inset-bottom));
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="progress-bar">
            <div class="progress" id="progress"></div>
        </div>

        <a href="/" class="logo" style="color: white; text-decoration: none; font-weight: bold; font-size: 24px;">Venusnap</a>

        {{-- <button class="mobile-menu-btn" id="menuBtn">
            <i class="fas fa-bars"></i>
        </button> --}}

        {{-- <div class="mobile-nav" id="mobileNav">
            <button class="close-menu" id="closeMenu">
                <i class="fas fa-times"></i>
            </button>
            <a href="/">Home</a>
            <a href="/gallery">Gallery</a>
            <a href="/about">About</a>
            <a href="/contact">Contact</a>
            <a href="https://play.google.com/store/apps/details?id=com.venusnap.app" target="_blank">Download App</a>
        </div> --}}

        <div class="art-container" id="artContainer">
            <!-- Art items will be added dynamically -->
        </div>

        <div class="overlay"></div>

        <div class="slide-indicators" id="indicators">
            <!-- Indicators will be added dynamically -->
        </div>

        <div class="art-info" id="artInfo">
            <!-- Art info will be added dynamically -->
        </div>

        <div class="cta-section">
            <h2 class="cta-title">Create. Share. Inspire.</h2>
            <p class="cta-subtitle">
                On Venusnap, albums bring your ideas to life create, share, and connect with fresh content every day.
            </p>
            <a href="https://play.google.com/store/apps/details?id=com.venusnap.app" target="_blank">
                <button class="download-btn">Get the App</button>
            </a>
        </div>
    </div>

    <script>
        // Sample art data
        const artData = [
            {
                image: "https://images.unsplash.com/photo-1579546929662-711aa81148cf",
                artist: "SnapCrack",
                title: "Addictive funny content",
                description: "Addictive, fast, and funny. This album brings the internet’s funniest moments to life one meme at a time",
                tags: ["Abstract", "Colorful", "Modern"]
            },
            {
                image: "https://images.unsplash.com/photo-1579546929662-711aa81148cf",
                artist: "ToonPlay",
                title: "Animated humor",
                description: "ToonPlay is the home of animated humor where iconic characters, funny expressions.",
                tags: ["Colorful", "Modern", "Expressionism"]
            },
            {
                image: "https://images.unsplash.com/photo-1541961017774-22349e4a1262",
                artist: "Better Every Day",
                title: "Source of inspiration",
                description: "This album is your daily source of inspiration and practical wisdom to help you grow, heal, and thrive.",
                tags: ["Urban", "Abstract", "Contemporary"]
            }
        ];

        const artContainer = document.getElementById('artContainer');
        const indicatorsContainer = document.getElementById('indicators');
        const artInfoContainer = document.getElementById('artInfo');
        const progressBar = document.getElementById('progress');
        const menuBtn = document.getElementById('menuBtn');
        const mobileNav = document.getElementById('mobileNav');
        const closeMenu = document.getElementById('closeMenu');
        let currentIndex = 0;
        let slideInterval;
        let isMobile = window.innerWidth <= 768;

        // Create art items and indicators
        artData.forEach((art, index) => {
            // Create art item
            const artItem = document.createElement('div');
            artItem.className = 'art-item';
            if (index === 0) artItem.classList.add('active');
            artItem.innerHTML = `
                <img src="${art.image}" alt="${art.title}">
            `;
            artContainer.appendChild(artItem);

            // Create indicator
            const indicator = document.createElement('div');
            indicator.className = 'indicator';
            if (index === 0) indicator.classList.add('active');
            indicatorsContainer.appendChild(indicator);
        });

        // Create initial art info
        function createArtInfo(index) {
            artInfoContainer.innerHTML = `
                <div class="artist">${artData[index].artist}</div>
                <h2 class="title">${artData[index].title}</h2>
                <p class="description">${artData[index].description}</p>
                <div class="tags">
                    ${artData[index].tags.map(tag => `<span class="tag">${tag}</span>`).join('')}
                </div>
            `;
        }

        const artItems = document.querySelectorAll('.art-item');
        const indicators = document.querySelectorAll('.indicator');

        // Show art item with smooth transition
        function showArt(index) {
            // Mark current item as exiting
            artItems[currentIndex].classList.remove('active');
            artItems[currentIndex].classList.add('exiting');

            // Update indicators
            indicators[currentIndex].classList.remove('active');

            // Set new current index
            currentIndex = index;

            // Show new item
            artItems[currentIndex].classList.remove('exiting');
            artItems[currentIndex].classList.add('active');

            // Update indicators
            indicators[currentIndex].classList.add('active');

            // Update art info
            createArtInfo(currentIndex);

            // Reset and animate progress bar
            progressBar.style.width = '0%';
            setTimeout(() => {
                progressBar.style.width = '100%';
            }, 50);
        }

        // Auto-advance art
        function nextArt() {
            const nextIndex = (currentIndex + 1) % artItems.length;
            showArt(nextIndex);
        }

        // Initialize
        function initSlider() {
            createArtInfo(0);
            showArt(0);
            slideInterval = setInterval(nextArt, 6000);
        }

        // Start the slider
        initSlider();

        // Progress bar completes
        progressBar.addEventListener('transitionend', () => {
            if (progressBar.style.width === '100%') {
                // This is handled by the interval
            }
        });

        // Pause on hover (desktop only)
        if (!isMobile) {
            artContainer.addEventListener('mouseenter', () => {
                clearInterval(slideInterval);
                progressBar.style.transition = 'width 0s linear';
            });

            artContainer.addEventListener('mouseleave', () => {
                // Calculate remaining time based on progress bar width
                const remainingWidth = 100 - parseFloat(progressBar.style.width || 0);
                const remainingTime = (remainingWidth / 100) * 6000;

                // Continue progress bar
                progressBar.style.transition = `width ${remainingTime}ms linear`;
                progressBar.style.width = '100%';

                // Set timeout for next slide
                setTimeout(() => {
                    nextArt();
                    // Restart interval
                    slideInterval = setInterval(nextArt, 6000);
                }, remainingTime);
            });
        }

        // Handle swipe for mobile
        let touchStartX = 0;
        let touchEndX = 0;

        artContainer.addEventListener('touchstart', e => {
            touchStartX = e.changedTouches[0].screenX;
            clearInterval(slideInterval);
        });

        artContainer.addEventListener('touchend', e => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
            slideInterval = setInterval(nextArt, 6000);
        });

        function handleSwipe() {
            const minSwipeDistance = 50;
            const distance = touchStartX - touchEndX;

            if (Math.abs(distance) < minSwipeDistance) return;

            if (distance > 0) {
                // Swipe left - next art
                nextArt();
            } else {
                // Swipe right - previous art
                const prevIndex = (currentIndex - 1 + artItems.length) % artItems.length;
                showArt(prevIndex);
            }
        }

        // Mobile menu functionality
        menuBtn.addEventListener('click', () => {
            mobileNav.classList.add('active');
            document.body.style.overflow = 'hidden';
        });

        closeMenu.addEventListener('click', () => {
            mobileNav.classList.remove('active');
            document.body.style.overflow = 'auto';
        });

        // Adjust layout for mobile
        function adjustLayout() {
            isMobile = window.innerWidth <= 768;

            if (isMobile) {
                // Additional mobile adjustments if needed
                document.body.style.overflow = 'auto';
            } else {
                document.body.style.overflow = 'hidden';
                mobileNav.classList.remove('active');
            }
        }

        // Initial adjustment
        adjustLayout();

        // Adjust on resize
        window.addEventListener('resize', adjustLayout);
    </script>
</body>
</html>
