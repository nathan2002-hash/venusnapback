
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Welcome - Venusnap</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="{{ asset('assets1/img/logo1.png') }}" rel="icon">
  <link href="{{ asset('assets1/img/logo1.png') }}" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{ asset('assets1/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets1/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('assets1/vendor/aos/aos.css') }}" rel="stylesheet">
  <link href="{{ asset('assets1/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets1/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="{{ asset('assets1/css/main.css') }}" rel="stylesheet">

  <!-- =======================================================
  * Template Name: Invent
  * Template URL: https://bootstrapmade.com/invent-bootstrap-business-template/
  * Updated: May 12 2025 with Bootstrap v5.3.6
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container position-relative d-flex align-items-center justify-content-between">

      <a href="/" class="logo d-flex align-items-center me-auto me-xl-0">
        <img src="{{ asset('assets1/img/logo1.png') }}" alt="">
        <h1 class="sitename">Venusnap</h1><span>.</span>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="#hero" class="active">Home</a></li>
          <li><a href="#about">About</a></li>
          <li><a href="#services">Services</a></li>
          <li><a href="#portfolio">Portfolio</a></li>
          <li><a href="#download">Download</a></li>
          <li><a href="#contact">Contact</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <a class="btn-getstarted" href="#download">Download</a>

    </div>
  </header>

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row align-items-center mb-5">
          <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="badge-wrapper mb-3">
              <div class="d-inline-flex align-items-center rounded-pill border border-accent-light">
                <div class="icon-circle me-2">
                  <i class="bi bi-bell"></i>
                </div>
                <span class="badge-text me-3">Empowering Creators</span>
              </div>
            </div>

            <h1 class="hero-title mb-4">Inspire. Create. Earn. All in One Platform.</h1>

            <p class="hero-description mb-4">
                Venusnap is where visual creators share powerful content, build authentic communities, and monetize through engagement. Whether you're an artist, photographer, or storyteller — your creativity belongs here.
            </p>
            <div class="cta-wrapper">
              <a href="#download" class="btn btn-primary">Download</a>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="hero-image">
              <img src="{{ asset('assets1/img/illustration/illustration-16.webp') }}" alt="Business Growth" class="img-fluid" loading="lazy">
            </div>
          </div>
        </div>

        <div class="row feature-boxes">
          <div class="col-lg-4 mb-4 mb-lg-0" data-aos="fade-up" data-aos-delay="200">
            <div class="feature-box">
              <div class="feature-icon me-sm-4 mb-3 mb-sm-0">
                <i class="bi bi-lightning-charge"></i>
              </div>
              <div class="feature-content">
                <h3 class="feature-title">Instant Publishing</h3>
                <p class="feature-text">Upload your snaps, tell your story, and share with the world in seconds — no hassle, no noise.</p>
            </div>
            </div>
          </div>

          <div class="col-lg-4 mb-4 mb-lg-0" data-aos="fade-up" data-aos-delay="300">
            <div class="feature-box">
              <div class="feature-icon me-sm-4 mb-3 mb-sm-0">
                <i class="bi bi-shield-lock"></i>
              </div>
               <div class="feature-content">
                    <h3 class="feature-title">Your Content, Protected</h3>
                    <p class="feature-text">Every post is secured and backed by modern infrastructure to keep your work safe and respected.</p>
                </div>
            </div>
          </div>

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="400">
            <div class="feature-box">
              <div class="feature-icon me-sm-4 mb-3 mb-sm-0">
                <i class="bi bi-heart"></i>
              </div>
               <div class="feature-content">
                    <h3 class="feature-title">Earn from Supporters</h3>
                    <p class="feature-text">Turn admiration into earnings with Venusnap Points. Fans can support you directly, one snap at a time.</p>
                </div>
            </div>
          </div>
        </div>

      </div>

    </section><!-- /Hero Section -->

    <!-- About Section -->
    <section id="about" class="about section">

      <div class="container">

        <div class="row gy-4">

          <div class="col-lg-6 content" data-aos="fade-up" data-aos-delay="100">
            <p class="who-we-are">Who We Are</p>
            <h3>Empowering Visual Creators to Inspire, Connect, and Earn</h3>
            <p class="fst-italic">
            Venusnap is a creator-first platform designed to help individuals share meaningful visual stories, grow their following, and monetize through engagement.
            </p>
            <ul>
            <li><i class="bi bi-check-circle"></i> <span>Share snaps, art, and designs in beautifully crafted albums.</span></li>
            <li><i class="bi bi-check-circle"></i> <span>Get supported through admires, subscriptions, and Venusnap Points.</span></li>
            <li><i class="bi bi-check-circle"></i> <span>Join a vibrant community of artists, photographers, and creatives who believe in the power of visual expression.</span></li>
            </ul>
            <a href="#" class="read-more"><span>Read More</span><i class="bi bi-arrow-right"></i></a>
          </div>

          <div class="col-lg-6 about-images" data-aos="fade-up" data-aos-delay="200">
            <div class="row gy-4">
              <div class="col-lg-6">
                <img src="{{ asset('assets1/img/about/about-portrait-1.webp') }}" class="img-fluid" alt="">
              </div>
              <div class="col-lg-6">
                <div class="row gy-4">
                  <div class="col-lg-12">
                    <img src="{{ asset('assets1/img/about/about-8.webp') }}" class="img-fluid" alt="">
                  </div>
                  <div class="col-lg-12">
                    <img src="{{ asset('assets1/img/about/about-12.webp') }}" class="img-fluid" alt="">
                  </div>
                </div>
              </div>
            </div>

          </div>

        </div>

      </div>
    </section><!-- /About Section -->

    <!-- How We Work Section -->
    <section id="how-we-work" class="how-we-work section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>How We Work</h2>
        <p>We make it simple for creators to share, grow, and preserve memories — all in one place.</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="steps-5">
          <div class="process-container">

            <div class="process-item" data-aos="fade-up" data-aos-delay="200">
              <div class="content">
                <span class="step-number">01</span>
                <div class="card-body">
                  <div class="step-icon">
                    <i class="bi bi-person-plus"></i>
                  </div>
                  <div class="step-content">
                    <h3>Create Your Account</h3>
                    <p>Sign up for free and set up your Venusnap profile as a creator, business, or personal album.</p>
                  </div>
                </div>
              </div>
            </div><!-- End Process Item -->

            <div class="process-item" data-aos="fade-up" data-aos-delay="300">
              <div class="content">
                <span class="step-number">02</span>
                <div class="card-body">
                  <div class="step-icon">
                    <i class="bi bi-images"></i>
                  </div>
                   <div class="step-content">
                        <h3>Post to Albums</h3>
                        <p>Upload your snaps, designs, or visuals and organize them into Albums that reflect your style and story.</p>
                    </div>
                </div>
              </div>
            </div><!-- End Process Item -->

            <div class="process-item" data-aos="fade-up" data-aos-delay="400">
              <div class="content">
                <span class="step-number">03</span>
                <div class="card-body">
                  <div class="step-icon">
                    <i class="bi bi-people"></i>
                  </div>
                  <div class="step-content">
                        <h3>Grow Your Audience</h3>
                        <p>Gain followers, engage with admirers, and build your creative community on Venusnap.</p>
                    </div>
                </div>
              </div>
            </div><!-- End Process Item -->

            <div class="process-item" data-aos="fade-up" data-aos-delay="500">
              <div class="content">
                <span class="step-number">04</span>
                <div class="card-body">
                  <div class="step-icon">
                    <i class="bi bi-rocket-takeoff"></i>
                  </div>
                 <div class="step-content">
                    <h3>Monetize Your Content</h3>
                    <p>Receive multiple admirers, supporters, and points that convert into real earnings. Monetize your visuals effortlessly.</p>
                </div>
                </div>
              </div>
            </div><!-- End Process Item -->

          </div>
        </div>

      </div>

    </section><!-- /How We Work Section -->

    <!-- Services Section -->
    <section id="services" class="services section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>What Venusnap Offers</h2>
        <p>Empowering creators, businesses, and everyday users through visual storytelling and meaningful support.</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row justify-content-center g-5">

          <div class="col-md-6" data-aos="fade-right" data-aos-delay="100">
            <div class="service-item">
              <div class="service-icon">
                <i class="bi bi-images"></i>
              </div>
              <div class="service-content">
                <h3>Visual Albums</h3>
                <p>Create stunning albums—whether personal, business, or creative—and share your story with your audience through captivating visuals.</p>
                <a href="#" class="service-link">
                <span>Explore Albums</span>
                <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            </div>
          </div><!-- End Service Item -->

          <div class="col-md-6" data-aos="fade-left" data-aos-delay="100">
            <div class="service-item">
              <div class="service-icon">
                <i class="bi bi-star"></i>
              </div>
              <div class="service-content">
                <h3>Monetization Through visual</h3>
                <p>Users can support your content with paid visuals, turning admiration into earnings. Every like has real value.</p>
                <a href="#" class="service-link">
                <span>Learn More</span>
                <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            </div>
          </div><!-- End Service Item -->

          <div class="col-md-6" data-aos="fade-right" data-aos-delay="200">
            <div class="service-item">
              <div class="service-icon">
                <i class="bi bi-people"></i>
              </div>
               <div class="service-content">
                <h3>Support Creators</h3>
                <p>Support creators or brands you admire. Support albums and stay updated on their latest content while showing your support.</p>
                <a href="#" class="service-link">
                <span>Support a Creator</span>
                <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            </div>
          </div><!-- End Service Item -->

          <div class="col-md-6" data-aos="fade-left" data-aos-delay="200">
            <div class="service-item">
              <div class="service-icon">
                <i class="bi bi-megaphone"></i>
              </div>
               <div class="service-content">
                    <h3>Adboard for Businesses</h3>
                    <p>Advertise your products through snap-based ads that stay active until your points run out. Pay only per view or click.</p>
                    <a href="#" class="service-link">
                    <span>Advertise on Venusnap</span>
                    <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
          </div><!-- End Service Item -->

          <div class="col-md-6" data-aos="fade-right" data-aos-delay="300">
            <div class="service-item">
              <div class="service-icon">
                <i class="bi bi-tools"></i>
              </div>
              <div class="service-content">
                <h3>Creator Tools</h3>
                <p>Design eye-catching visuals directly in the app using customizable templates, themed backgrounds, and typography tools—no Photoshop needed.</p>
                <a href="#" class="service-link">
                <span>Get Creative</span>
                <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            </div>
          </div><!-- End Service Item -->

          <div class="col-md-6" data-aos="fade-left" data-aos-delay="300">
            <div class="service-item">
              <div class="service-icon">
                <i class="bi bi-globe2"></i>
              </div>
              <div class="service-content">
                <h3>Global Reach</h3>
                <p>Venusnap is a worldwide platform, enabling anyone—from Lusaka to Los Angeles—to connect, express, and earn through photos.</p>
                <a href="#" class="service-link">
                <span>Join the Community</span>
                <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            </div>
          </div><!-- End Service Item -->

        </div>

      </div>

    </section><!-- /Services Section -->

    <!-- Services Alt Section -->
    <section id="services-alt" class="services-alt section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row">
          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
            <div class="content-block">
              <h6 class="subtitle">Advertise Smarter</h6>
              <h2 class="title">Empowering Businesses to Reach the Right Audience</h2>
              <p class="description">
                Venusnap is more than just a social platform it's your next advertising powerhouse. Businesses can now create engaging Snaps, target the right audiences, and track performance in real time. Whether you're launching a product, promoting an event, or building your brand, Venusnap gives you the tools to connect directly with people who care.
            </p>
              <div class="button-wrapper">
                <a class="btn" href="services.html"><span>Explore Ad Tools</span></a>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="services-list">
              <div class="service-item d-flex align-items-center" data-aos="fade-up" data-aos-delay="200">
                <div class="service-icon">
                  <i class="bi bi-megaphone-fill"></i>
                </div>
                <div class="service-content">
                <h4><a href="service-details.html">Ad Snaps</a></h4>
                <p>Create image ads automatically using AI from a simple description no designer needed.</p>
                </div>
              </div><!-- End Service Item -->

              <div class="service-item d-flex align-items-center" data-aos="fade-up" data-aos-delay="300">
                <div class="service-icon">
                  <i class="bi bi-bullseye"></i>
                </div>
                <div class="service-content">
                <h4><a href="service-details.html">Targeted Reach</a></h4>
                <p>Your ads are shown to the right audience based on interests, categories, and behavior.</p>
                </div>
              </div><!-- End Service Item -->

              <div class="service-item d-flex align-items-center" data-aos="fade-up" data-aos-delay="400">
                <div class="service-icon">
                  <i class="bi bi-bar-chart"></i>
                </div>
                <div class="service-content">
                <h4><a href="service-details.html">Ad Performance Tracking</a></h4>
                <p>Track views, clicks, and engagement in real-time. Optimize campaigns with ease.</p>
                </div>
              </div><!-- End Service Item -->

              <div class="service-item d-flex align-items-center" data-aos="fade-up" data-aos-delay="500">
                <div class="service-icon">
                  <i class="bi bi-lightning-charge"></i>
                </div>
                <div class="service-content">
                <h4><a href="service-details.html">Fast Activation</a></h4>
                <p>Launch your ad in minutes. No setup hassle. Just Snap and Go.</p>
                </div>
              </div><!-- End Service Item -->
            </div>
          </div>
        </div>

      </div>

    </section><!-- /Services Alt Section -->

    <!-- Call To Action 2 Section -->
    <section id="call-to-action-2" class="call-to-action-2 section light-background">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row g-5 align-items-center">
          <div class="col-lg-6" data-aos="fade-right" data-aos-delay="200">
            <div class="cta-image-wrapper">
              <img src="{{ asset('assets1/img/cta/cta-4.webp') }}" alt="Call to Action" class="img-fluid rounded-4">
              <div class="cta-pattern"></div>
            </div>
          </div>

          <div class="col-lg-6" data-aos="fade-left" data-aos-delay="300">
            <div class="cta-content">
              <h2>Create Powerful Ads from Just a Description</h2>
              <p class="lead">
                    No graphic designer? No problem. Venusnap helps businesses generate eye-catching ad Snaps instantly just describe your offer, and you're ready to go.
                </p>

              <div class="cta-features">
                <div class="feature-item" data-aos="zoom-in" data-aos-delay="400">
                <i class="bi bi-check-circle-fill"></i>
                <span>Instant image generation for ads</span>
                </div>
                <div class="feature-item" data-aos="zoom-in" data-aos-delay="450">
                <i class="bi bi-check-circle-fill"></i>
                <span>Smart targeting to reach the right audience</span>
                </div>
                <div class="feature-item" data-aos="zoom-in" data-aos-delay="500">
                <i class="bi bi-check-circle-fill"></i>
                <span>No design skills needed just type and go</span>
                </div>
            </div>

            <div class="cta-action mt-5">
                <a href="/ads" class="btn btn-primary btn-lg me-3">Start Creating Ads</a>
                <a href="/learn-more" class="btn btn-outline-primary btn-lg">How It Works</a>
            </div>
            </div>
          </div>
        </div>

      </div>

    </section><!-- /Call To Action 2 Section -->

    <!-- Portfolio Section -->
    <section id="portfolio" class="portfolio section">

      <div class="container section-title" data-aos="fade-up">
        <h2>Venusnap Gallery</h2>
        <p>A showcase of image art, AI-generated visuals, and advertising creatives from the Venusnap community.</p>
      </div>


      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="isotope-layout" data-default-filter="*" data-layout="masonry" data-sort="original-order">

          <div class="portfolio-filters-container" data-aos="fade-up" data-aos-delay="200">
          <ul class="portfolio-filters isotope-filters">
  <li data-filter="*" class="filter-active">All</li>
  <li data-filter=".filter-creator">Creators</li>
  <li data-filter=".filter-ai">AI Generated</li>
  <li data-filter=".filter-ads">Ad Creatives</li>
</ul>

          </div>

          <div class="row g-4 isotope-container" data-aos="fade-up" data-aos-delay="300">

           <div class="col-lg-6 col-md-6 portfolio-item isotope-item filter-creator">
  <div class="portfolio-card">
    <div class="portfolio-image">
      <img src="{{ asset('assets1/img/portfolio/portfolio-1.webp') }}" class="img-fluid" alt="Creator Artwork" loading="lazy">
      <div class="portfolio-overlay">
        <div class="portfolio-actions">
          <a href="{{ asset('assets1/img/portfolio/portfolio-1.webp') }}" class="glightbox preview-link" data-gallery="portfolio-gallery-creator"><i class="bi bi-eye"></i></a>
          <a href="https://venusnap.com/posts/123" class="details-link"><i class="bi bi-arrow-right"></i></a>
        </div>
      </div>
    </div>
    <div class="portfolio-content">
      <span class="category">Creator</span>
      <h3>Nature Aesthetic by @zoe_art</h3>
      <p>A serene collection exploring light and color in nature.</p>
    </div>
  </div>
</div>


           <div class="col-lg-6 col-md-6 portfolio-item isotope-item filter-ai">
  <div class="portfolio-card">
    <div class="portfolio-image">
      <img src="{{ asset('assets1/img/portfolio/1.jpeg') }}" class="img-fluid" alt="AI Art" loading="lazy">
      <div class="portfolio-overlay">
        <div class="portfolio-actions">
          <a href="{{ asset('assets1/img/portfolio/1.jpeg') }}" class="glightbox preview-link" data-gallery="portfolio-gallery-ai"><i class="bi bi-eye"></i></a>
          <a href="https://venusnap.com/ai-gallery" class="details-link"><i class="bi bi-arrow-right"></i></a>
        </div>
      </div>
    </div>
    <div class="portfolio-content">
      <span class="category">AI Generated</span>
      <h3>Cyber Dreams</h3>
      <p>Created with Venusnap's AI image generator — futuristic portrait collection.</p>
    </div>
  </div>
</div>


           <div class="col-lg-6 col-md-6 portfolio-item isotope-item filter-ads">
  <div class="portfolio-card">
    <div class="portfolio-image">
      <img src="{{ asset('assets1/img/portfolio/2.jpeg') }}" class="img-fluid" alt="Ad Creative" loading="lazy">
      <div class="portfolio-overlay">
        <div class="portfolio-actions">
          <a href="{{ asset('assets1/img/portfolio/2.jpeg') }}" class="glightbox preview-link" data-gallery="portfolio-gallery-ads"><i class="bi bi-eye"></i></a>
          <a href="https://venusnap.com/ads/coffee-brand" class="details-link"><i class="bi bi-arrow-right"></i></a>
        </div>
      </div>
    </div>
    <div class="portfolio-content">
      <span class="category">Ad Creative</span>
      <h3>Java Beans Campaign</h3>
      <p>Advertising image made using Venusnap's AD generator tool.</p>
    </div>
  </div>
</div>


            <div class="col-lg-6 col-md-6 portfolio-item isotope-item filter-brand">
              <div class="portfolio-card">
                <div class="portfolio-image">
                  <img src="{{ asset('assets1/img/portfolio/portfolio-7.webp') }}" class="img-fluid" alt="" loading="lazy">
                  <div class="portfolio-overlay">
                    <div class="portfolio-actions">
                      <a href="{{ asset('assets1/img/portfolio/portfolio-7.webp') }}" class="glightbox preview-link" data-gallery="portfolio-gallery-brand"><i class="bi bi-eye"></i></a>
                      <a href="portfolio-details.html" class="details-link"><i class="bi bi-arrow-right"></i></a>
                    </div>
                  </div>
                </div>
                <div class="portfolio-content">
                  <span class="category">Branding</span>
                  <h3>Luxury Brand Package</h3>
                  <p>Aenean lacinia bibendum nulla sed consectetur elit.</p>
                </div>
              </div>
            </div><!-- End Portfolio Item -->

          </div><!-- End Portfolio Container -->

        </div>

      </div>

    </section><!-- /Portfolio Section -->

   <!-- App Download Section -->
<section id="download" class="pricing section light-background">

  <!-- Section Title -->
  <div class="container section-title" data-aos="fade-up">
    <h2>Download Venusnap</h2>
    <p>Experience Venusnap on your preferred platform. Start creating, sharing, and growing your influence today.</p>
  </div><!-- End Section Title -->

  <div class="container" data-aos="fade-up" data-aos-delay="100">

    <div class="row g-4 justify-content-center">

      <!-- Android App -->
      <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
        <div class="pricing-card">
          <h3>Android App</h3>
          <div class="price">
            <i class="bi bi-android2" style="font-size: 48px; color:#7C4DFF"></i>
          </div>
          <p class="description">Download the official Venusnap app for Android and explore all the features with ease.</p>

          <h4>Permissions Required:</h4>
          <ul class="features-list">
            <li><i class="bi bi-check-circle-fill"></i> Internet Access</li>
            <li><i class="bi bi-check-circle-fill"></i> Access to Photos</li>
            <li><i class="bi bi-check-circle-fill"></i> Storage (for saving media)</li>
            <li><i class="bi bi-check-circle-fill"></i> Push Notifications</li>
          </ul>

          <a href="https://play.google.com/store/apps/details?id=com.venusnap.app" class="btn btn-primary" target="_blank">
            Download Now <i class="bi bi-arrow-down-circle"></i>
          </a>
        </div>
      </div>

      <!-- iOS App -->
      <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
        <div class="pricing-card">
          <h3>iOS App</h3>
          <div class="price">
            <i class="bi bi-apple" style="font-size: 48px; color:#7C4DFF"></i>
          </div>
          <p class="description">Coming soon to the App Store. Stay tuned for iOS support.</p>

          <h4>Expected Permissions:</h4>
          <ul class="features-list">
            <li><i class="bi bi-check-circle"></i> Internet Access</li>
            <li><i class="bi bi-check-circle"></i> Access to Photos</li>
            <li><i class="bi bi-check-circle"></i> Storage (for saving media)</li>
            <li><i class="bi bi-check-circle"></i> Push Notifications</li>
          </ul>

          <a href="#" class="btn btn-outline-secondary disabled">
            Coming Soon
          </a>
        </div>
      </div>

      <!-- Desktop App -->
      <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
        <div class="pricing-card">
          <h3>Desktop App</h3>
          <div class="price">
            <i class="bi bi-laptop" style="font-size: 48px; color:#7C4DFF"></i>
          </div>
          <p class="description">A powerful desktop version is on its way for Windows, macOS, and Linux users.</p>

          <h4>Expected Permissions:</h4>
          <ul class="features-list">
            <li><i class="bi bi-check-circle"></i> Internet Connection</li>
            <li><i class="bi bi-check-circle"></i> Media Storage</li>
            <li><i class="bi bi-check-circle"></i> Push Notifications</li>
          </ul>

          <a href="#" class="btn btn-outline-secondary disabled">
            Coming Soon
          </a>
        </div>
      </div>

    </div>

  </div>

</section><!-- /App Download Section -->


   <!-- Faq Section -->
<section id="faq" class="faq section">

  <div class="container" data-aos="fade-up" data-aos-delay="100">

    <div class="row gy-5">
      <!-- Contact Info Card -->
      <div class="col-lg-6" data-aos="zoom-out" data-aos-delay="200">
        <div class="faq-contact-card">
          <div class="card-icon">
            <i class="bi bi-question-circle"></i>
          </div>
          <div class="card-content">
            <h3>Still Have Questions?</h3>
            <p>We’re here to help! If you have any issues installing the app or using our platform, feel free to reach out to us directly.</p>
            <div class="contact-options">
              <a href="mailto:support@yourapp.com" class="contact-option">
                <i class="bi bi-envelope"></i>
                <span>Email Support</span>
              </a>
              <a href="#" class="contact-option">
                <i class="bi bi-chat-dots"></i>
                <span>Live Chat</span>
              </a>
              <a href="tel:+123456789" class="contact-option">
                <i class="bi bi-telephone"></i>
                <span>Call Us</span>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- FAQ Accordion -->
      <div class="col-lg-6" data-aos="fade-up" data-aos-delay="300">
        <div class="faq-accordion">

          <!-- Question 1 -->
          <div class="faq-item faq-active">
            <div class="faq-header">
              <h3>Which platforms is the app available on?</h3>
              <i class="bi bi-chevron-down faq-toggle"></i>
            </div>
            <div class="faq-content">
              <p>
                The app is currently available for Android users. iOS and Desktop versions are under development and will be released soon.
              </p>
            </div>
          </div>

          <!-- Question 2 -->
          <div class="faq-item">
            <div class="faq-header">
              <h3>What permissions does the Android app require?</h3>
              <i class="bi bi-chevron-down faq-toggle"></i>
            </div>
            <div class="faq-content">
              <p>
                Our Android app may request the following permissions to enhance your experience: Internet Access, Notifications, Storage Access (for media), and Network State. We do not collect personal data without your consent.
              </p>
            </div>
          </div>

          <!-- Question 3 -->
          <div class="faq-item">
            <div class="faq-header">
              <h3>Will I need to create an account to use the app?</h3>
              <i class="bi bi-chevron-down faq-toggle"></i>
            </div>
            <div class="faq-content">
              <p>
                Yes, to get the best experience and access to all features, we recommend creating a free account. It helps us personalize your experience and keep your data secure.
              </p>
            </div>
          </div>

          <!-- Question 4 -->
          <div class="faq-item">
            <div class="faq-header">
              <h3>Is there any cost to download or use the app?</h3>
              <i class="bi bi-chevron-down faq-toggle"></i>
            </div>
            <div class="faq-content">
              <p>
                The app is free to download and use. In the future, we may introduce optional premium features, but core functionality will always remain free.
              </p>
            </div>
          </div>

        </div>
      </div>
    </div>

  </div>

</section>


    <!-- Team Section -->
    {{-- <section id="team" class="team section light-background">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Team</h2>
        <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row g-5">

          <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="100">
            <div class="team-card">
              <div class="team-image">
                <img src="{{ asset('assets1/img/person/person-m-1.webp') }}" class="img-fluid" alt="">
                <div class="team-overlay">
                  <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla quam velit, vulputate eu pharetra nec, mattis ac neque.</p>
                  <div class="team-social">
                    <a href=""><i class="bi bi-twitter-x"></i></a>
                    <a href=""><i class="bi bi-facebook"></i></a>
                    <a href=""><i class="bi bi-instagram"></i></a>
                    <a href=""><i class="bi bi-linkedin"></i></a>
                  </div>
                </div>
              </div>
              <div class="team-content">
                <h4>Daniel Mitchell</h4>
                <span class="position">Creative Director</span>
              </div>
            </div>
          </div><!-- End Team Member -->

          <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="200">
            <div class="team-card">
              <div class="team-image">
                <img src="{{ asset('assets1/img/person/person-f-6.webp') }}" class="img-fluid" alt="">
                <div class="team-overlay">
                  <p>Aliquam tincidunt mauris eu risus. Vestibulum auctor dapibus neque. Nunc dignissim risus id metus.</p>
                  <div class="team-social">
                    <a href=""><i class="bi bi-twitter-x"></i></a>
                    <a href=""><i class="bi bi-facebook"></i></a>
                    <a href=""><i class="bi bi-instagram"></i></a>
                    <a href=""><i class="bi bi-linkedin"></i></a>
                  </div>
                </div>
              </div>
              <div class="team-content">
                <h4>Nathan Mwamba</h4>
                <span class="position">Founder and CEO</span>
              </div>
            </div>
          </div><!-- End Team Member -->

          <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="300">
            <div class="team-card">
              <div class="team-image">
                <img src="{{ asset('assets1/img/person/person-m-6.webp') }}" class="img-fluid" alt="">
                <div class="team-overlay">
                  <p>Cras ornare tristique elit. Integer in dui quis est placerat ornare. Phasellus a lacus a risus.</p>
                  <div class="team-social">
                    <a href=""><i class="bi bi-twitter-x"></i></a>
                    <a href=""><i class="bi bi-facebook"></i></a>
                    <a href=""><i class="bi bi-instagram"></i></a>
                    <a href=""><i class="bi bi-linkedin"></i></a>
                  </div>
                </div>
              </div>
              <div class="team-content">
                <h4>Marcus Johnson</h4>
                <span class="position">UI/UX Designer</span>
              </div>
            </div>
          </div><!-- End Team Member -->

          <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="400">
            <div class="team-card">
              <div class="team-image">
                <img src="{{ asset('assets1/img/person/person-f-3.webp') }}" class="img-fluid" alt="">
                <div class="team-overlay">
                  <p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.</p>
                  <div class="team-social">
                    <a href=""><i class="bi bi-twitter-x"></i></a>
                    <a href=""><i class="bi bi-facebook"></i></a>
                    <a href=""><i class="bi bi-instagram"></i></a>
                    <a href=""><i class="bi bi-linkedin"></i></a>
                  </div>
                </div>
              </div>
              <div class="team-content">
                <h4>Jessica Parker</h4>
                <span class="position">Marketing Strategist</span>
              </div>
            </div>
          </div><!-- End Team Member -->

        </div>

      </div>

    </section> --}}
    <!-- /Team Section -->

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Testimonials</h2>
        <p>Real stories from real users and why they trust and love Venusnap.</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="testimonials-slider swiper init-swiper">
          <script type="application/json" class="swiper-config">
            {
              "loop": true,
              "speed": 800,
              "autoplay": {
                "delay": 5000
              },
              "slidesPerView": 1,
              "spaceBetween": 30,
              "pagination": {
                "el": ".swiper-pagination",
                "type": "bullets",
                "clickable": true
              },
              "breakpoints": {
                "768": {
                  "slidesPerView": 2
                },
                "1200": {
                  "slidesPerView": 3
                }
              }
            }
          </script>
          <div class="swiper-wrapper">
            <div class="swiper-slide">
              <div class="testimonial-card">
                <div class="testimonial-content">
                  <p>
                    <i class="bi bi-quote quote-icon"></i>
                   Venusnap began with a bold idea: to empower creators, entrepreneurs, and users to express themselves visually. I’ve witnessed how creativity fuels connection and growth. That’s why our platform centers on visual storytelling through powerful, personal, and business ready albums.
                  </p>
                </div>
                <div class="testimonial-profile">
                  <div class="rating">
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                  </div>
                  <div class="profile-info">
                    <img src="{{ asset('assets1/img/person/person-m-9.webp') }}" alt="Profile Image">
                    <div>
                      <h3>Nathan Mwamba</h3>
                      <h4>CEO &amp; Founder</h4>
                    </div>
                  </div>
                </div>
              </div>
            </div><!-- End testimonial item -->
          </div>
          <div class="swiper-pagination"></div>
        </div>

      </div>

    </section><!-- /Testimonials Section -->

    <!-- Contact Section -->
    <section id="contact" class="contact section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Contact</h2>
        <p>Contact us if you have questions</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4 mb-5">
          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
            <div class="info-card">
              <div class="icon-box">
                <i class="bi bi-geo-alt"></i>
              </div>
              <h3>Our Address</h3>
              <p>Remote</p>
            </div>
          </div>

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
            <div class="info-card">
              <div class="icon-box">
                <i class="bi bi-telephone"></i>
              </div>
              <h3>Contact Number</h3>
              <p>Mobile: +260 970 333 596<br>
                Email: support@venusnap.com</p>
            </div>
          </div>

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
            <div class="info-card">
              <div class="icon-box">
                <i class="bi bi-clock"></i>
              </div>
              <h3>Opening Hours</h3>
              <p>24/7<br>
                </p>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="form-wrapper" data-aos="fade-up" data-aos-delay="400">
              <form action="#" method="post" role="form" class="php-email-form">
                <div class="row">
                  <div class="col-md-6 form-group">
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-person"></i></span>
                      <input type="text" name="name" class="form-control" placeholder="Your name*" required="">
                    </div>
                  </div>
                  <div class="col-md-6 form-group">
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                      <input type="email" class="form-control" name="email" placeholder="Email address*" required="">
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col-md-6 form-group">
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-phone"></i></span>
                      <input type="text" class="form-control" name="phone" placeholder="Phone number*" required="">
                    </div>
                  </div>
                  <div class="col-md-6 form-group">
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-list"></i></span>
                      <select name="subject" class="form-control" required="">
                        <option value="">Select service*</option>
                        <option value="Service 1">Consulting</option>
                        <option value="Service 2">Development</option>
                        <option value="Service 3">Marketing</option>
                        <option value="Service 4">Support</option>
                      </select>
                    </div>
                  </div>
                  <div class="form-group mt-3">
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-chat-dots"></i></span>
                      <textarea class="form-control" name="message" rows="6" placeholder="Write a message*" required=""></textarea>
                    </div>
                  </div>
                  <div class="my-3">
                    <div class="loading">Loading</div>
                    <div class="error-message"></div>
                    <div class="sent-message">Your message has been sent. Thank you!</div>
                  </div>
                  <div class="text-center">
                    <button type="submit">Submit Message</button>
                  </div>

                </div>
              </form>
            </div>
          </div>

        </div>

      </div>
    </section><!-- /Contact Section -->

  </main>

  <footer id="footer" class="footer light-background">

    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="index.html" class="logo d-flex align-items-center">
            <span class="sitename">Venusnap</span>
          </a>
          <div class="footer-contact pt-3">
            <p>444 Alaska Avenue</p>
            <p>California, Suite #CEC468</p>
            <p class="mt-3"><strong>Phone:</strong> <span>+1 681 435 4816</span></p>
            <p><strong>Email:</strong> <span>support@venusnap.com</span></p>
          </div>
          {{-- <div class="social-links d-flex mt-4">
            <a href=""><i class="bi bi-twitter-x"></i></a>
            <a href=""><i class="bi bi-facebook"></i></a>
            <a href=""><i class="bi bi-instagram"></i></a>
            <a href=""><i class="bi bi-linkedin"></i></a>
          </div> --}}
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Useful Links</h4>
          <ul>
            <li><a href="#home">Home</a></li>
            <li><a href="#about">About us</a></li>
            <li><a href="#services">Services</a></li>
            <li><a href="/terms/of/service">Terms of service</a></li>
            <li><a href="/privacy/policy">Privacy policy</a></li>
          </ul>
        </div>

       <div class="col-lg-2 col-md-3 footer-links">
        <h4>Our Services</h4>
        <ul>
          <li><a href="#">Post Visual Content</a></li>
          <li><a href="#">Album Support</a></li>
          <li><a href="#">Admire</a></li>
          <li><a href="#">Venusnap Points</a></li>
          <li><a href="#">Business Promotions</a></li>
        </ul>
      </div>

        <div class="col-lg-2 col-md-3 footer-links">
        <h4>Features of App</h4>
        <ul>
          <li><a href="#">Vertical Image Feed</a></li>
          <li><a href="#">Swipeable Snaps</a></li>
          <li><a href="#">Customizable Albums</a></li>
          <li><a href="#">Built-in Design Tools</a></li>
          <li><a href="#">Verified Albums Priority</a></li>
        </ul>
      </div>

        <div class="col-lg-2 col-md-3 footer-links">
        <h4>Permissions of App</h4>
        <ul>
          <li><a href="#">Access to Photos</a></li>
          <li><a href="#">Camera Usage</a></li>
          <li><a href="#">Internet Access</a></li>
          <li><a href="#">Push Notifications</a></li>
          <li><a href="#">Storage Access</a></li>
        </ul>
      </div>

      </div>
    </div>

    <div class="container copyright text-center mt-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">Venusnap</strong> <span>All Rights Reserved</span></p>
      <div class="credits">
        Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>
      </div>
    </div>

  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="{{ asset('assets1/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets1/vendor/php-email-form/validate.js') }}"></script>
  <script src="{{ asset('assets1/vendor/aos/aos.js') }}"></script>
  <script src="{{ asset('assets1/vendor/glightbox/js/glightbox.min.js') }}"></script>
  <script src="{{ asset('assets1/vendor/imagesloaded/imagesloaded.pkgd.min.js') }}"></script>
  <script src="{{ asset('assets1/vendor/isotope-layout/isotope.pkgd.min.js') }}"></script>
  <script src="{{ asset('assets1/vendor/swiper/swiper-bundle.min.js') }}"></script>

  <!-- Main JS File -->
  <script src="{{ asset('assets1/js/main.js') }}"></script>

</body>

</html>
