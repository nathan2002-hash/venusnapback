<!DOCTYPE html>
<html lang="en">
<head>
	<!-- Meta -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, minimal-ui, viewport-fit=cover">
	<meta name="theme-color" content="#2196f3">
	<meta name="author" content="DexignZone" />
    <meta name="keywords" content="" />
    <meta name="robots" content="" />
	<meta name="description" content="Soziety - Social Network Mobile App Template ( Bootstrap 5 + PWA )"/>
	<meta property="og:title" content="Soziety - Social Network Mobile App Template ( Bootstrap 5 + PWA )" />
	<meta property="og:description" content="Soziety - Social Network Mobile App Template ( Bootstrap 5 + PWA )" />
	<meta property="og:image" content="social-image.png"/>
	<meta name="format-detection" content="telephone=no">

	<!-- Favicons Icon -->
	<link rel="shortcut icon" type="image/x-icon" href="assets/images/favicon.png" />

    <!-- Title -->
	<title>@yield('title') - VenuSnap</title>

	<!-- PWA Version -->
	<link rel="manifest" href="manifest.json">

    <!-- Stylesheets -->
	<link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/vendor/swiper/swiper-bundle.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/style.css') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com/">
	<link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@200;300;400;600;700;800;900&amp;family=Poppins:wght@100;200;300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet">

</head>

    <body class="bg-gradient-2">
        <div class="page-wraper header-fixed">

            <!-- Preloader -->
            <div id="preloader">
                <div class="spinner"></div>
            </div>
            <!-- Preloader end-->

        @include('user.includes.header')





        <div class="main-sidebar">

          @include('user.includes.sidebar')

        </div>





        <div class="main-content">

          @yield('content')


        <footer class="main-footer">

          @include('user.includes.footer')

        </footer>


      </div>
    </div>



    <script src="{{ asset('assets/js/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/swiper/swiper-bundle.min.js') }}"></script><!-- Swiper -->
    <script src="{{ asset('assets/vendor/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js') }}"></script><!-- Swiper -->
    <script src="{{ asset('assets/js/dz.carousel.js') }}"></script><!-- Swiper -->
    <script src="{{ asset('assets/js/settings.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <script src="index.js"></script>
    <script>
        $(".stepper").TouchSpin();
    </script>

</body>

@yield('scripts')


<!-- index.html  21 Nov 2019 03:47:04 GMT -->
</html>
