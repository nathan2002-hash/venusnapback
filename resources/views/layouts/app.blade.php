<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title') | Welcome</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Kanakku is a Sales, Invoices & Accounts Admin template for Accountant or Companies/Offices with various features for all your needs. Try Demo and Buy Now.">
	<meta name="keywords" content="admin, estimates, bootstrap, business, corporate, creative, management, minimal, modern, accounts, invoice, html5, responsive, CRM, Projects">
	<meta name="author" content="Dreams Technologies">

	<!-- Favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png">

	<!-- Apple Touch Icon -->
	<link rel="apple-touch-icon" sizes="180x180" href="assets/img/apple-touch-icon.png">

    <!-- Theme Script js -->
    <script src="{{ asset('assets/js/theme-script.js') }}" type="e71244daa4fa1b0497881cc8-text/javascript"></script>

	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">

	<!-- Tabler Icon CSS -->
	<link rel="stylesheet" href="{{ asset('assets/plugins/tabler-icons/tabler-icons.min.css') }}">

	<!-- Daterangepikcer CSS -->
	<link rel="stylesheet" href="{{ asset('assets/plugins/daterangepicker/daterangepicker.css') }}">

	<!-- Datetimepicker CSS -->
	<link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datetimepicker.min.css') }}">

    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/all.min.css') }}">

	<!-- Tabler Icon CSS -->
	<link rel="stylesheet" href="{{ asset('assets/plugins/tabler-icons/tabler-icons.min.css') }}">

    <!-- Simplebar CSS -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/simplebar/simplebar.min.css') }}">

	<!-- Iconsax CSS -->
	<link rel="stylesheet" href="{{ asset('assets/css/iconsax.css') }}">

	<!-- Main CSS -->
	<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

</head>
<!-- body start -->
<body>

    <!-- Begin page -->
    <div class="page-wrapper">

    @include('admin.inc.header')





    <div class="main-sidebar">

        @include('admin.inc.sidebar')

    </div>




    <div class="content">

        @yield('content')

    </div>





    <footer class="main-footer">

        @include('admin.inc.footer')

    </footer>


      </div>
    </div>
<!-- Vendor -->
<!-- jQuery -->
	<script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}" type="e71244daa4fa1b0497881cc8-text/javascript"></script>

	<!-- Bootstrap Core JS -->
	<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}" type="e71244daa4fa1b0497881cc8-text/javascript"></script>

	<!-- Daterangepikcer JS -->
	<script src="{{ asset('assets/js/moment.min.js') }}" type="e71244daa4fa1b0497881cc8-text/javascript"></script>
	<script src="{{ asset('assets/plugins/daterangepicker/daterangepicker.js') }}" type="e71244daa4fa1b0497881cc8-text/javascript"></script>

	<!-- Simplebar JS -->
	<script src="{{ asset('assets/plugins/simplebar/simplebar.min.js') }}" type="e71244daa4fa1b0497881cc8-text/javascript"></script>

	<!-- Datetimepicker JS -->
	<script src="{{ asset('assets/js/bootstrap-datetimepicker.min.js') }}" type="e71244daa4fa1b0497881cc8-text/javascript"></script>

	<!-- Chart JS -->
	<script src="{{ asset('assets/plugins/apexchart/apexcharts.min.js') }}" type="e71244daa4fa1b0497881cc8-text/javascript"></script>
	<script src="{{ asset('assets/plugins/apexchart/chart-data.js') }}" type="e71244daa4fa1b0497881cc8-text/javascript"></script>

	<!-- Datatable JS -->
	<script src="{{ asset('assets/js/jquery.dataTables.min.js') }}" type="e71244daa4fa1b0497881cc8-text/javascript"></script>
    <script src="{{ asset('assets/js/dataTables.bootstrap5.min.js') }}" type="e71244daa4fa1b0497881cc8-text/javascript"></script>

	<!-- Custom JS -->
	<script src="{{ asset('assets/js/script.js') }}" type="e71244daa4fa1b0497881cc8-text/javascript"></script>

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

