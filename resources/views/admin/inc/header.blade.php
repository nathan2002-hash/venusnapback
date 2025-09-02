<div class="header">
			<div class="main-header">

				<!-- Logo -->
				<div class="header-left">
					<a href="index.html" class="logo">
						<img src="assets/img/logo.svg" alt="Logo">
					</a>
					<a href="index.html" class="dark-logo">
						<img src="assets/img/logo-white.svg" alt="Logo">
					</a>
				</div>

				<!-- Sidebar Menu Toggle Button -->
				<a id="mobile_btn" class="mobile_btn" href="#sidebar">
					<span class="bar-icon">
						<span></span>
						<span></span>
						<span></span>
					</span>
				</a>

				<div class="header-user">
					<div class="nav user-menu nav-list">
						<div class="me-auto d-flex align-items-center" id="header-search">

                            <!-- Add -->
                            <div class="dropdown me-3">
                                <a class="btn btn-primary bg-gradient btn-xs btn-icon rounded-circle d-flex align-items-center justify-content-center" data-bs-toggle="dropdown" href="javascript:void(0);" role="button">
                                    <i class="isax isax-add text-white"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-start p-2">
                                    <li>
                                        <a href="add-invoice.html" class="dropdown-item d-flex align-items-center">
                                            <i class="isax isax-document-text-1 me-2"></i>Invoice
                                        </a>
                                    </li>
                                    <li>
                                        <a href="expenses.html" class="dropdown-item d-flex align-items-center">
                                            <i class="isax isax-money-send me-2"></i>Expense
                                        </a>
                                    </li>
                                    <li>
                                        <a href="add-credit-notes.html" class="dropdown-item d-flex align-items-center">
                                            <i class="isax isax-money-add me-2"></i>Credit Notes
                                        </a>
                                    </li>
                                    <li>
                                        <a href="add-debit-notes.html" class="dropdown-item d-flex align-items-center">
                                            <i class="isax isax-money-recive me-2"></i>Debit Notes
                                        </a>
                                    </li>
                                    <li>
                                        <a href="add-purchases-orders.html" class="dropdown-item d-flex align-items-center">
                                            <i class="isax isax-document me-2"></i>Purchase Order
                                        </a>
                                    </li>
                                    <li>
                                        <a href="add-quotation.html" class="dropdown-item d-flex align-items-center">
                                            <i class="isax isax-document-download me-2"></i>Quotation
                                        </a>
                                    </li>
                                    <li>
                                        <a href="add-delivery-challan.html" class="dropdown-item d-flex align-items-center">
                                            <i class="isax isax-document-forward me-2"></i>Delivery Challan
                                        </a>
                                    </li>
                                </ul>
                            </div>

							<!-- Breadcrumb -->
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb breadcrumb-divide mb-0">
									<li class="breadcrumb-item d-flex align-items-center"><a href="index.html"><i class="isax isax-home-2 me-1"></i>Home</a></li>
									<li class="breadcrumb-item active" aria-current="page">Dashboard</li>
								</ol>
							</nav>

						</div>

						<div class="d-flex align-items-center">

							<!-- Search -->
							<div class="input-icon-end position-relative me-2">
								<input type="text" class="form-control" placeholder="Search">
								<span class="input-icon-addon">
									<i class="isax isax-search-normal"></i>
								</span>
							</div>
							<!-- /Search -->

							<!-- Notification -->
							<div class="notification_item me-2">
								<a href="#" class="btn btn-menubar position-relative" id="notification_popup" data-bs-toggle="dropdown" data-bs-auto-close="outside">
									<i class="isax isax-notification-bing5"></i>
									<span class="position-absolute badge bg-success border border-white"></span>
								</a>
								<div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg" style="min-height: 300px;">

									<div class="p-2 border-bottom">
										<div class="row align-items-center">
											<div class="col">
												<h6 class="m-0 fs-16 fw-semibold"> Notifications</h6>
											</div>
											<div class="col-auto">
												<div class="dropdown">
													<a href="#" class="dropdown-toggle drop-arrow-none link-dark" data-bs-toggle="dropdown" data-bs-offset="0,15" aria-expanded="false">
														<i class="isax isax-setting-2 fs-16 text-body align-middle"></i>
													</a>
													<div class="dropdown-menu dropdown-menu-end">
														<!-- item-->
														<a href="javascript:void(0);" class="dropdown-item"><i class="ti ti-bell-check me-1"></i>Mark as Read</a>
														<!-- item-->
														<a href="javascript:void(0);" class="dropdown-item"><i class="ti ti-trash me-1"></i>Delete All</a>
													</div>
												</div>
											</div>
										</div>
									</div>

									<!-- View All-->
									<div class="p-2 rounded-bottom border-top text-center">
										<a href="notifications.html" class="text-center fw-medium fs-14 mb-0">
											View All
										</a>
									</div>

								</div>
							</div>

							<!-- Light/Dark Mode Button -->
							<div class="me-2 theme-item">
                                <a href="javascript:void(0);" id="dark-mode-toggle" class="theme-toggle btn btn-menubar">
                                    <i class="isax isax-moon"></i>
                                </a>
                                <a href="javascript:void(0);" id="light-mode-toggle" class="theme-toggle btn btn-menubar">
                                    <i class="isax isax-sun-1"></i>
                                </a>
                            </div>

							<!-- User Dropdown -->
							<div class="dropdown profile-dropdown">
								<a href="javascript:void(0);" class="dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown"  data-bs-auto-close="outside">
									<span class="avatar online">
										<img src="{{ asset('assets/img/profiles/avatar-01.jpg') }}" alt="Img" class="img-fluid rounded-circle">
									</span>
								</a>
								<div class="dropdown-menu p-2">
									<div class="d-flex align-items-center bg-light rounded-1 p-2 mb-2">
										<span class="avatar avatar-lg me-2">
											<img src="{{ asset('assets/img/profiles/avatar-01.jpg') }}" alt="img" class="rounded-circle" >
										</span>
										<div>
											<h6 class="fs-14 fw-medium mb-1">{{ Auth::user()->name }}</h6>
											<p class="fs-13">Administrator</p>
										</div>
									</div>

									<!-- Item-->
									<a class="dropdown-item d-flex align-items-center" href="account-settings.html">
										<i class="isax isax-profile-circle me-2"></i>Profile Settings
									</a>

									<!-- Item-->
									<a class="dropdown-item d-flex align-items-center" href="inventory-report.html">
										<i class="isax isax-document-text me-2"></i>Reports
									</a>

									<!-- Item-->
									<div class="form-check form-switch form-check-reverse d-flex align-items-center justify-content-between dropdown-item mb-0">
										<label class="form-check-label" for="notify"><i class="isax isax-notification me-2"></i>Notifications</label>
										<input class="form-check-input" type="checkbox" role="switch" id="notify">
									</div>

									<hr class="dropdown-divider my-2">

									<!-- Item-->
									<a class="dropdown-item logout d-flex align-items-center" href="login.html">
										<i class="isax isax-logout me-2"></i>Sign Out
									</a>
								</div>
							</div>

						</div>
					</div>
				</div>

				<!-- Mobile Menu -->
				<div class="dropdown mobile-user-menu profile-dropdown">
					<a href="javascript:void(0);" class="dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown"  data-bs-auto-close="outside">
						<span class="avatar avatar-md online">
							<img src="{{ asset('assets/img/profiles/avatar-01.jpg') }}" alt="Img" class="img-fluid rounded-circle">
						</span>
					</a>
					<div class="dropdown-menu p-2 mt-0">
						<a class="dropdown-item d-flex align-items-center" href="profile.html">
							<i class="isax isax-profile-circle me-2"></i>Profile Settings
						</a>
						<a class="dropdown-item d-flex align-items-center" href="reports.html">
							<i class="isax isax-document-text me-2"></i>Reports
						</a>
						<a class="dropdown-item d-flex align-items-center" href="account-settings.html">
							<i class="isax isax-setting me-2"></i>Settings
						</a>
						<a class="dropdown-item logout d-flex align-items-center" href="login.html">
							<i class="isax isax-logout me-2"></i>Signout
						</a>
					</div>
				</div>
				<!-- /Mobile Menu -->

			</div>
		</div>
