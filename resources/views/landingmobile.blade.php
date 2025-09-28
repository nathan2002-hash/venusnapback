<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Venusnap - A Home for Visual Creators</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #000;
      color: #fff;
    }

    .container {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .art-container {
      position: relative;
      width: 100%;
      height: 70vh;
      overflow: hidden;
    }

    .art-container img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .overlay {
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: linear-gradient(to top, rgba(0,0,0,0.8), rgba(0,0,0,0));
    }

    .art-info {
      position: absolute;
      bottom: 40px;
      left: 20px;
      z-index: 10;
      max-width: 600px;
    }

    .title {
      font-size: 32px;
      font-weight: 700;
      margin-bottom: 15px;
    }

    .subline {
      font-size: 16px;
      margin-bottom: 20px;
      opacity: 0.9;
    }

    .benefits {
      margin: 15px 0;
      font-size: 16px;
      line-height: 1.6;
    }

    .benefits li {
      margin-bottom: 8px;
    }

    .cta-btn {
      background: #ff2df2; /* brand accent */
      color: #fff;
      border: none;
      padding: 14px 35px;
      border-radius: 30px;
      font-size: 18px;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 4px 20px rgba(0,0,0,0.5);
      text-decoration: none;
      display: inline-block;
      transition: background 0.3s, transform 0.3s;
    }

    .cta-btn:hover {
      background: #e226d8;
      transform: translateY(-2px);
    }

    .cta-section {
      padding: 50px 20px;
      text-align: center;
      background: #111;
    }

    .cta-title {
      font-size: 26px;
      font-weight: 700;
      margin-bottom: 12px;
    }

    .cta-subtitle {
      font-size: 16px;
      margin-bottom: 25px;
      opacity: 0.9;
    }

    .social-proof {
      margin-top: 20px;
      font-size: 15px;
      opacity: 0.85;
      font-style: italic;
    }

    /* Floating CTA button for mobile */
    .floating-btn {
      position: fixed;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      background: #ff2df2;
      color: #fff;
      padding: 14px 25px;
      border-radius: 25px;
      font-size: 16px;
      font-weight: bold;
      box-shadow: 0 4px 15px rgba(0,0,0,0.4);
      text-decoration: none;
      z-index: 1000;
      display: none;
    }

    @media (max-width: 768px) {
      .floating-btn { display: block; }
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Hero Section -->
    <div class="art-container">
      <img src="{{ asset('assets/img/design.jpeg') }}" alt="Artwork">
      <div class="overlay"></div>
      <div class="art-info">
        <h1 class="title">A Home for Visual Creators</h1>
        <p class="subline">Share your quotes, memes, art, and photography with a community that values creativity.</p>

        <ul class="benefits">
          <li>✔ Build Albums that inspire thousands</li>
          <li>✔ Share memes, quotes & art instantly</li>
          <li>✔ Connect with real supporters who care</li>
        </ul>

        <a href="https://play.google.com/store/apps/details?id=com.venusnap.app" target="_blank" class="cta-btn">Install Venusnap Free</a>
      </div>
    </div>

    <!-- CTA Section -->
    <div class="cta-section">
      <h2 class="cta-title">Create. Share. Inspire.</h2>
      <p class="cta-subtitle">Join a growing community of creators building Albums that people love. Your creativity deserves recognition—start today.</p>
      <a href="https://play.google.com/store/apps/details?id=com.venusnap.app" target="_blank" class="cta-btn">Start Your Album Free</a>
      <p class="social-proof">✨ Already trusted by creators sharing their work on Venusnap.</p>
    </div>
  </div>

  <!-- Floating Mobile CTA -->
  <a href="https://play.google.com/store/apps/details?id=com.venusnap.app" target="_blank" class="floating-btn">Install Free</a>

<script>
document.querySelector('.download-btn').addEventListener('click', function () {
    fetch('/track-button-click', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            button_name: 'Get Started',
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
