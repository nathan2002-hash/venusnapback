<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8" />
    <title>@yield('title') | Welcome</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Venusnap" name="description" />
    <meta content="Quixnes" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">

    <!-- Bootstrap Css -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />

</head>
<!-- body start -->
<body data-sidebar="dark" data-layout-mode="light">

    <!-- Begin page -->
    <div id="wrapper">
    <div class="vertical-overlay"></div>

    @include('admin.inc.header')





    <div class="main-sidebar">

        @include('admin.inc.sidebar')

    </div>





    <div class="main-content">

        @yield('content')

    </div>





    <footer class="main-footer">

        @include('admin.inc.footer')

    </footer>


      </div>
    </div>
<!-- Vendor -->
 <!-- JAVASCRIPT -->
 <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
 <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
 <script src="{{ asset('assets/libs/metismenu/metisMenu.min.js') }}"></script>
 <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
 <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>

 <!-- apexcharts -->
 <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

 <!-- dashboard init -->
 <script src="{{ asset('assets/js/pages/dashboard.init.js') }}"></script>

 <!-- App js -->
 <script src="{{ asset('assets/js/app.js') }}"></script>

<script>
    // Get references to the buttons and the form
    var signInButton = document.getElementById('submit-data-button');
    var loadingButton = document.getElementById('loading-button');
    var form = document.getElementById('submitdata'); // Replace 'your-form-id' with the actual ID of your form

    // Add event listener to the sign-in button
    signInButton.addEventListener('click', function(event) {
        // Check if the form is valid
        if (form.checkValidity()) {
            // Hide the sign-in button
            signInButton.style.display = 'none';
            // Show the loading button
            loadingButton.style.display = 'block';

            // You can submit the form here
            form.submit();
        } else {
            // Prevent the default form submission
            event.preventDefault();
            // Trigger the browser's native validation message
            form.reportValidity();
        }
    });
</script>
</body>
    @yield('scripts')
    </html>

