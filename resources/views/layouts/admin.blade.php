
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark" data-sidebar="light" data-color="primary" data-topbar="white" data-layout="default" data-size="default" data-width="fluid">
<head>

	<!-- Meta Tags -->
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

<body>

	<!-- Begin Wrapper -->
	<div class="main-wrapper">

		<!-- Topbar Start -->
		         @include('admin.inc.header')
		<!-- Topbar End -->

		<!-- Sidenav Menu Start -->
	         @include('admin.inc.sidebar')
		<!-- Sidenav Menu End -->

		<!-- ========================
			Start Page Content
		========================= -->
        <div class="page-wrapper">
            <!-- Start Content -->
            <div class="content">
                @yield('content')
            </div>
        </div>

		<!-- ========================
			End Page Content
		========================= -->

		<!-- Start Add Ledger  -->
		<div id="add_ledger" class="modal fade">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">Add New Ledger</h4>
						<button type="button" class="btn-close btn-close-modal custom-btn-close" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-x"></i></button>
					</div>
					<form action="https://kanakku.dreamstechnologies.com/html/template/index.html">
						<div class="modal-body pb-1">
							<div class="mb-3">
								<label class="form-label">Amount</label>
								<input type="text" class="form-control">
							</div>
							<div class="mb-3">
								<label class="form-label">Date</label>
								<div class="input-group position-relative">
									<input type="text" class="form-control datetimepicker rounded-end" placeholder="dd/mm/yyyy">
									<span class="input-icon-addon fs-16 text-gray-9">
										<i class="isax isax-calendar-2"></i>
									</span>
								</div>
							</div>
							<div class="mb-3">
								<label class="form-label">Mode</label>
								<div class="d-flex align-items-center">
									<div class="form-check me-3">
										<input class="form-check-input" type="radio" name="Radio" id="Radio-sm-1">
										<label class="form-check-label" for="Radio-sm-1">
											Credit
										</label>
									</div>
									<div class="form-check">
										<input class="form-check-input" type="radio" name="Radio" id="Radio-sm-2" checked="">
										<label class="form-check-label" for="Radio-sm-2">
											Debit
										</label>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer d-flex align-items-center justify-content-between gap-1">
							<button type="button" class="btn btn-outline-white" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-primary">Create</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<!-- End Add Ledger -->

	</div>
	<!-- End Wrapper -->

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

<script src="{{ asset('assets/cdn-cgi/scripts/7d0fa10a/cloudflare-static/rocket-loader.min.js') }}" data-cf-settings="e71244daa4fa1b0497881cc8-|49" defer></script><script defer src="https://static.cloudflareinsights.com/beacon.min.js/vcd15cbe7772f49c399c6a5babf22c1241717689176015" integrity="sha512-ZpsOmlRQV6y907TI0dKBHq9Md29nnaEIPlkf84rnaERnq6zvWvPUqr2ft8M1aS28oN72PdrCzSjY4U6VaAw1EQ==" data-cf-beacon='{"rayId":"9784b0879f424ebb","version":"2025.8.0","serverTiming":{"name":{"cfExtPri":true,"cfEdge":true,"cfOrigin":true,"cfL4":true,"cfSpeedBrain":true,"cfCacheStatus":true}},"token":"3ca157e612a14eccbb30cf6db6691c29","b":1}' crossorigin="anonymous"></script>

</body>

 @yield('scripts')
</html>
