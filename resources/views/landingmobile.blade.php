<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Venusnap - Discover Amazing Art</title>
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
            overflow-x: hidden;
            min-height: 100vh;
        }

        .container {
            position: relative;
            min-height: 100vh;
            width: 100%;
            overflow: hidden;
        }

        .art-container {
            position: relative;
            width: 100%;
            height: 70vh;
            overflow: hidden;
        }

        .art-item {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
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
            position: relative;
            padding: 30px;
            z-index: 10;
            background: #000;
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
            position: relative;
            width: 100%;
            background: #000;
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

        .logo {
            position: absolute;
            top: 25px;
            left: 25px;
            font-size: 24px;
            font-weight: 700;
            z-index: 10;
            letter-spacing: 1px;
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
            .art-container {
                height: 50vh;
            }

            .art-info {
                padding: 20px;
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
                padding: 30px 20px;
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

            .mobile-menu-btn {
                display: flex;
                align-items: center;
                justify-content: center;
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

            .art-container {
                height: 40vh;
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
        <a href="/" class="logo" style="color: white; text-decoration: none; font-weight: bold; font-size: 24px;"></a>

        <div class="art-container">
            <div class="art-item">
                <img src="{{ asset('assets/img/design.jpeg') }}" alt="Artwork">
            </div>
            <div class="overlay"></div>
        </div>

       <div class="art-info">
  <div class="artist">Venusnap</div>
  <h2 class="title">A Home for Visual Creators</h2>
  <p class="description">
    Share your quotes, memes, art, and photography in Albums that inspire, entertain,
    and connect with people who truly appreciate creativity.
  </p>
</div>

<div class="cta-section">
  <h2 class="cta-title">Create. Share. Inspire.</h2>
  <p class="cta-subtitle">
    Join a growing community of creators building Albums that people love.
    Your creativity deserves recognition—start today.
  </p>
    <a href="https://play.google.com/store/apps/details?id=com.venusnap.app"
        target="_blank"
        onclick="trackClick('start_album')">
        <button class="download-btn">Sign Up</button>
    </a>
</div>

    </div>
<script>
document.querySelector('.download-btn').addEventListener('click', function () {
    fetch('/track-button-click', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            button_name: 'Sign Up',
            page_url: window.location.href
        })
    })
    .then(res => res.json())
    .then(data => console.log(data));
});
</script>


    <script>
        // Mobile menu functionality
        const menuBtn = document.getElementById('menuBtn');
        const mobileNav = document.getElementById('mobileNav');
        const closeMenu = document.getElementById('closeMenu');

        menuBtn.addEventListener('click', () => {
            mobileNav.classList.add('active');
            document.body.style.overflow = 'hidden';
        });

        closeMenu.addEventListener('click', () => {
            mobileNav.classList.remove('active');
            document.body.style.overflow = 'auto';
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (mobileNav.classList.contains('active') &&
                !mobileNav.contains(e.target) &&
                e.target !== menuBtn) {
                mobileNav.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        });
    </script>
</body>
</html>
