@extends('layouts.admin')



@section('title')
    Welcome
@endsection


@section('content')

<div>
    <!-- Start Breadcrumb -->
<div class="d-flex d-block align-items-center justify-content-between flex-wrap gap-3 mb-3">
    <div>
        <h6>Dashboard</h6>
    </div>
    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
        <div id="reportrange" class="reportrange-picker d-flex align-items-center">
            <i class="isax isax-calendar text-gray-5 fs-14 me-1"></i><span class="reportrange-picker-field">16 Apr 25 - 16 Apr 25</span>
        </div>
        <div class="dropdown">
            <a class="btn btn-primary d-flex align-items-center justify-content-center dropdown-toggle" data-bs-toggle="dropdown" href="javascript:void(0);" role="button">
                Create New
            </a>
            <ul class="dropdown-menu dropdown-menu-start">
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
        <div class="dropdown">
            <a href="javascript:void(0);" class="btn btn-outline-white d-inline-flex align-items-center"  data-bs-toggle="dropdown">
                <i class="isax isax-export-1 me-1"></i>Export
            </a>
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item" href="javascript:void(0);">Download as PDF</a>
                </li>
                <li>
                    <a class="dropdown-item" href="javascript:void(0);">Download as Excel</a>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- End Breadcrumb -->

<div class="bg-primary rounded welcome-wrap position-relative mb-3">

    <!-- start row -->
    <div class="row">
        <div class="col-lg-8 col-md-9 col-sm-7">
            <div>
                <h5 class="text-white mb-1">Welcome for work, {{ Auth::user()->name }}</h5>
                <p class="text-white mb-3">You have 15+ invoices saved to draft that has to send to customers</p>
                <div class="d-flex align-items-center flex-wrap gap-3">
                    <p class="d-flex align-items-center fs-13 text-white mb-0" id="current-date"><i class="isax isax-calendar5 me-1"></i>Friday, 24 Mar 2025</p>
                    <p class="d-flex align-items-center fs-13 text-white mb-0" id="current-time"><i class="isax isax-clock5 me-1"></i>11:24 AM</p>
                </div>
            </div>
        </div><!-- end col -->
    </div>
    <!-- end row -->

    <div class="position-absolute end-0 top-50 translate-middle-y p-2 d-none d-sm-block">
        <img src="assets/img/icons/dashboard.svg" alt="img">
    </div>
</div>

<!-- start row -->
<div class="row">
    <div class="col-md-4 d-flex">
        <div class="card flex-fill">
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="d-flex align-items-center mb-1"><i class="isax isax-category5 text-default me-2"></i>App Growth</h6>
                </div>
                <div class="row g-4">
                    <div class="col-xl-6">
                        <div class="d-flex align-items-center">
                            <span class="avatar avatar-44 avatar-rounded bg-primary-subtle text-primary flex-shrink-0 me-2">
                                <i class="isax isax-document-text-1 fs-20"></i>
                            </span>
                            <div>
                                <p class="mb-1 text-truncate">Albums</p>
                                <h6 class="fs-16 fw-semibold mb-0 text-truncate">{{ $album }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="d-flex align-items-center me-2">
                            <span class="avatar avatar-44 avatar-rounded bg-success-subtle text-success-emphasis flex-shrink-0 me-2">
                                <i class="isax isax-profile-2user fs-20"></i>
                            </span>
                            <div>
                                <p class="mb-1 text-truncate">Users</p>
                                <h6 class="fs-16 fw-semibold mb-0 text-truncate">{{ $usersc }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="d-flex align-items-center">
                            <span class="avatar avatar-44 avatar-rounded bg-warning-subtle text-warning-emphasis flex-shrink-0 me-2">
                                <i class="isax isax-dcube fs-20"></i>
                            </span>
                            <div>
                                <p class="mb-1 text-truncate">Posts</p>
                                <h6 class="fs-16 fw-semibold mb-0 text-truncate">{{ $postsc }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="d-flex align-items-center me-2">
                            <span class="avatar avatar-44 avatar-rounded bg-info-subtle text-info-emphasis flex-shrink-0 me-2">
                                <i class="isax isax-document-text fs-20"></i>
                            </span>
                            <div>
                                <p class="mb-1 text-truncate">Medias</p>
                                <h6 class="fs-16 fw-semibold mb-0 text-truncate">{{ $postmedias }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- end card body -->
        </div> <!-- end card -->
    </div> <!-- end col -->
    <div class="col-md-4 d-flex">
        <div class="card flex-fill">
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="d-flex align-items-center mb-1"><i class="isax isax-chart-215 text-default me-2"></i>Work Eng</h6>
                </div>
                <div class="row g-4">
                    <div class="col-xl-6">
                        <div class="d-flex align-items-center">
                            <span class="avatar avatar-44 avatar-rounded bg-primary-subtle text-primary flex-shrink-0 me-2">
                                <i class="isax isax-document-forward fs-20"></i>
                            </span>
                            <div>
                                <p class="mb-1 text-truncate">Artworks</p>
                                <h6 class="fs-16 fw-semibold mb-0">$40,569</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="d-flex align-items-center me-2">
                            <span class="avatar avatar-44 avatar-rounded bg-success-subtle text-success-emphasis flex-shrink-0 me-2">
                                <i class="isax isax-programming-arrow fs-20"></i>
                            </span>
                            <div>
                                <p class="mb-1 text-truncate">UTemp</p>
                                <h6 class="fs-16 fw-semibold mb-0 text-truncate">{{ $usertemplates }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="d-flex align-items-center">
                            <span class="avatar avatar-44 avatar-rounded bg-warning-subtle text-warning-emphasis flex-shrink-0 me-2">
                                <i class="isax isax-dollar-circle fs-20"></i>
                            </span>
                            <div>
                                <p class="mb-1 mb-0">VTemp</p>
                                <h6 class="fs-16 fw-semibold text-truncate">{{ $venusnaptemplates }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="d-flex align-items-center me-2">
                            <span class="avatar avatar-44 avatar-rounded bg-info-subtle text-info-emphasis flex-shrink-0 me-2">
                                <i class="isax isax-flag fs-20"></i>
                            </span>
                            <div>
                                <p class="mb-1 text-truncate">TTemp</p>
                                <h6 class="fs-16 fw-semibold mb-0 text-truncate">{{ $venusnaptemplates + $usertemplates }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- end card body -->
        </div> <!-- end card -->
    </div> <!-- end col -->
    <div class="col-md-4 d-flex">
        <div class="card flex-fill">
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="d-flex align-items-center mb-1"><i class="isax isax-chart-success5 text-default me-2"></i>Sales Analytics</h6>
                </div>
                <div class="row g-4">
                    <div class="col-xl-6">
                        <div class="d-flex align-items-center">
                            <span class="avatar avatar-44 avatar-rounded bg-primary-subtle text-primary flex-shrink-0 me-2">
                                <i class="isax isax-document fs-20"></i>
                            </span>
                            <div>
                                <p class="mb-1 text-truncate">Adboards</p>
                                <h6 class="fs-16 fw-semibold mb-0">{{ $adboards }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="d-flex align-items-center me-2">
                            <span class="avatar avatar-44 avatar-rounded bg-success-subtle text-success-emphasis flex-shrink-0 me-2">
                                <i class="isax isax-document-forward fs-20"></i>
                            </span>
                            <div>
                                <p class="mb-1 text-truncate">Active Ads</p>
                                <h6 class="fs-16 fw-semibold mb-0 text-truncate">{{ $runningads }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="d-flex align-items-center">
                            <span class="avatar avatar-44 avatar-rounded bg-warning-subtle text-warning-emphasis flex-shrink-0 me-2">
                                <i class="isax isax-document-previous fs-20"></i>
                            </span>
                            <div>
                                <p class="mb-1 text-truncate">Paused Ads</p>
                                <h6 class="fs-16 fw-semibold mb-0 text-truncate">{{ $pausedads }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="d-flex align-items-center me-2">
                            <span class="avatar avatar-44 avatar-rounded bg-info-subtle text-info-emphasis flex-shrink-0 me-2">
                                <i class="isax isax-dislike fs-20"></i>
                            </span>
                            <div>
                                <p class="mb-1 text-truncate">Total Ads</p>
                                <h6 class="fs-16 fw-semibold text-truncate mb-0">{{ $ads }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- end card body -->
        </div> <!-- end card -->
    </div> <!-- end col -->
</div>
<!-- end row -->

<!-- start row -->
    <div class="row">
    <div class="col-md-4 d-flex flex-column">
        <div class="card overflow-hidden z-1 flex-fill">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between border-bottom mb-2 pb-2">
                    <div>
                        <p class="mb-1">User Points</p>
                        <div class="d-flex align-items-center">
                            <h6 class="fs-16 fw-semibold me-2">{{ number_format($totalpoints) }}</h6>
                            <span class="badge badge-sm badge-soft-success">+45<i class="isax isax-arrow-up-15 ms-1"></i></span>
                        </div>
                    </div>
                    <span class="avatar avatar-lg bg-light text-dark avatar-rounded">
                        <i class="isax isax-document-text fs-16"></i>
                    </span>
                </div>
            </div> <!-- end card body -->
        </div> <!-- end card -->
    </div> <!-- end col -->
    <div class="col-md-4 d-flex flex-column">
        <div class="card overflow-hidden z-1 flex-fill">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between border-bottom mb-2 pb-2">
                    <div>
                        <p class="mb-1">User Points Amount</p>
                        <div class="d-flex align-items-center">
                            <h6 class="fs-16 fw-semibold me-2">${{ $pointamount }}</h6>
                            <span class="badge badge-sm badge-soft-success">+45<i class="isax isax-arrow-up-15 ms-1"></i></span>
                        </div>
                    </div>
                    <span class="avatar avatar-lg bg-light text-dark avatar-rounded">
                        <i class="isax isax-document-text fs-16"></i>
                    </span>
                </div>
            </div> <!-- end card body -->
        </div> <!-- end card -->
    </div> <!-- end col -->
    <div class="col-md-4 d-flex flex-column">
        <div class="card overflow-hidden z-1 flex-fill">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between border-bottom mb-2 pb-2">
                    <div>
                        <p class="mb-1">Templates</p>
                        <div class="d-flex align-items-center">
                            <h6 class="fs-16 fw-semibold me-2">{{ $templates }}</h6>
                            <span class="badge badge-sm badge-soft-success">+45<i class="isax isax-arrow-up-15 ms-1"></i></span>
                        </div>
                    </div>
                    <span class="avatar avatar-lg bg-light text-dark avatar-rounded">
                        <i class="isax isax-document-text fs-16"></i>
                    </span>
                </div>
            </div> <!-- end card body -->
        </div> <!-- end card -->
    </div>
    </div>
<!-- end row -->

<!-- start row -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
                    <h6 class="mb-1">Invoices</h6>
                    <a href="invoices.html" class="btn btn-primary mb-1">View all Invoices</a>
                </div>
                <div class="table-responsive no-filter no-pagination">
                    <table class="table table-nowrap border mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Album</th>
                                <th>User</th>
                                <th>Status</th>
                                <th>Media</th>
                                <th>Payment Mode</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($posts as $post)
                             @php
                                    $thumbnailUrl = null;
                                    if ($post->album->type === 'personal' || $post->album->type === 'creator') {
                                        $thumbnailUrl = $post->album->thumbnail_compressed
                                            ? generateSecureMediaUrl($post->album->thumbnail_compressed)
                                            : ($post->album->thumbnail_original
                                                ? generateSecureMediaUrl($post->album->thumbnail_original)
                                                : null);
                                    } elseif ($post->album->type === 'business') {
                                        $thumbnailUrl = $post->album->business_logo_compressed
                                            ? generateSecureMediaUrl($post->album->business_logo_compressed)
                                            : ($post->album->business_logo_original
                                                ? generateSecureMediaUrl($post->album->business_logo_original)
                                                : null);
                                    }
                                @endphp
                            <tr>
                                <td>
                                    <a href="invoice-details.html" class="link-default">{{ $post->id }}</a>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="customer-details.html" class="avatar avatar-sm rounded-circle me-2 flex-shrink-0">
                                            <img src="{{ $thumbnailUrl ?? 'https://www.gravatar.com/avatar' }}"
                                                alt="img"
                                                class="rounded-circle"
                                                style="width: 40px; height: 40px; object-fit: cover;">
                                        </a>
                                        <div>
                                            <h6 class="fs-14 fw-medium mb-0"><a href="customer-details.html">{{ $post->album->name }}</a></h6>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $post->user->name }}</td>
                                <td class="text-dark">
                                     @if ($post->status == 'active')
                                        <a href="#" class="btn btn-sm btn-soft-success border-0  d-inline-flex align-items-center me-1 fs-12 fw-regular" data-bs-toggle="modal" data-bs-target="#add_stockin">
                                            <i class="isax isax-document-sketch5 me-1"></i> Active
                                        </a>
                                    @else
                                        <a href="#" class="btn btn-sm btn-soft-danger border-0 d-inline-flex align-items-center fs-12 fw-regular" data-bs-toggle="modal" data-bs-target="#add_stockout">
                                            <i class="isax isax-document-sketch5 me-1"></i> Deleted
                                        </a>
                                    @endif
                                </td>
                                <td>{{ $post->postmedias->count() }}</td>
                                <td class="text-dark">Cash</td>
                                <td>{{ $post->created_at->format('D M Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div> <!-- end card body -->
        </div> <!-- end card -->
    </div> <!-- end col -->
</div>
<!-- end row -->

<!-- start row -->
<div class="row">
    <div class="col-lg-12 col-xl-4 d-flex">
        <div class="card flex-fill">
            <div class="card-body pb-1">
                <div class="mb-3">
                    <h6 class="mb-1">Recent Transactions</h6>
                </div>
                <h6 class="fs-14 fw-semibold mb-3">Today</h6>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center">
                        <a href="javascript:void(0);" class="avatar avatar-md flex-shrink-0 me-2">
                            <img src="assets/img/icons/transaction-01.svg" class="rounded-circle" alt="img">
                        </a>
                        <div>
                            <h6 class="fs-14 fw-semibold mb-1"><a href="javascript:void(0);">Andrew James</a></h6>
                            <p class="fs-13"><a href="invoice-details.html" class="link-default">#INV45478</a></p>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge badge-lg badge-soft-success">+ $989.15</span>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center">
                        <a href="javascript:void(0);" class="avatar avatar-md flex-shrink-0 me-2">
                            <img src="assets/img/icons/transaction-02.svg" class="rounded-circle" alt="img">
                        </a>
                        <div>
                            <h6 class="fs-14 fw-semibold mb-1"><a href="javascript:void(0);">John Carter</a></h6>
                            <p class="fs-13"><a href="invoice-details.html" class="link-default">#INV45477</a></p>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge badge-lg badge-soft-danger">- $300.12</span>
                    </div>
                </div>
                <hr>
                <h6 class="fs-14 fw-semibold mb-3">Yesterday</h6>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center">
                        <a href="javascript:void(0);" class="avatar avatar-md flex-shrink-0 me-2">
                            <img src="assets/img/icons/transaction-02.svg" class="rounded-circle" alt="img">
                        </a>
                        <div>
                            <h6 class="fs-14 fw-semibold mb-1"><a href="javascript:void(0);">Sophia White</a></h6>
                            <p class="fs-13"><a href="invoice-details.html" class="link-default">#INV45476</a></p>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge badge-lg badge-soft-success"> + $669</span>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center">
                        <a href="javascript:void(0);" class="avatar avatar-md flex-shrink-0 me-2">
                            <img src="assets/img/icons/transaction-02.svg" class="rounded-circle" alt="img">
                        </a>
                        <div>
                            <h6 class="fs-14 fw-semibold mb-1"><a href="javascript:void(0);">Daniel Martinez</a></h6>
                            <p class="fs-13"><a href="invoice-details.html" class="link-default">#INV45475</a></p>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge badge-lg badge-soft-success"> + $474.22</span>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center">
                        <a href="javascript:void(0);" class="avatar avatar-md flex-shrink-0 me-2">
                            <img src="assets/img/icons/transaction-01.svg" class="rounded-circle" alt="img">
                        </a>
                        <div>
                            <h6 class="fs-14 fw-semibold mb-1"><a href="javascript:void(0);">Amelia Robinson</a></h6>
                            <p class="fs-13"><a href="invoice-details.html" class="link-default">#INV45474</a></p>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge badge-lg badge-soft-success"> + $339.79</span>
                    </div>
                </div>
            </div> <!-- end card body -->
        </div> <!-- end card -->
    </div> <!-- end col -->

    <div class="col-md-6 col-xl-4 d-flex">
        <div class="card flex-fill">
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="mb-1">Quotations</h6>
                </div>
                <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-3">
                    <div class="d-flex align-items-center">
                        <a href="customer-details.html" class="avatar avatar-lg flex-shrink-0 me-2">
                            <img src="assets/img/users/user-02.jpg" class="rounded-circle" alt="img">
                        </a>
                        <div>
                            <h6 class="fs-14 fw-semibold mb-1"><a href="customer-details.html">Emily Clark</a></h6>
                            <p class="fs-13">QU0014</p>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge badge-sm badge-soft-success d-inline-flex align-items-center mb-1">Accepted<i class="isax isax-tick-circle ms-1"></i></span>
                        <p class="fs-13">25 Mar 2025</p>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-3">
                    <div class="d-flex align-items-center">
                        <a href="customer-details.html" class="avatar avatar-lg flex-shrink-0 me-2">
                            <img src="assets/img/users/user-07.jpg" class="rounded-circle" alt="img">
                        </a>
                        <div>
                            <h6 class="fs-14 fw-semibold mb-1"><a href="customer-details.html">David Anderson</a></h6>
                            <p class="fs-13">QU0147</p>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge badge-sm badge-soft-info d-inline-flex align-items-center mb-1">Sent<i class="isax isax-arrow-right-24 ms-1"></i></span>
                        <p class="fs-13">12 Feb 2025</p>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-3">
                    <div class="d-flex align-items-center">
                        <a href="customer-details.html" class="avatar avatar-lg flex-shrink-0 me-2">
                            <img src="assets/img/users/user-16.jpg" class="rounded-circle" alt="img">
                        </a>
                        <div>
                            <h6 class="fs-14 fw-semibold mb-1"><a href="customer-details.html">Sophia White</a></h6>
                            <p class="fs-13">QU1947</p>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge badge-sm badge-soft-light d-inline-flex align-items-center text-dark mb-1">Expired<i class="isax isax-timer-pause ms-1"></i></span>
                        <p class="fs-13">08 Mar 2025</p>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-3">
                    <div class="d-flex align-items-center">
                        <a href="customer-details.html" class="avatar avatar-lg flex-shrink-0 me-2">
                            <img src="assets/img/users/user-08.jpg" class="rounded-circle" alt="img">
                        </a>
                        <div>
                            <h6 class="fs-14 fw-semibold mb-1"><a href="customer-details.html">Michael Johnson</a></h6>
                            <p class="fs-13">QU2842</p>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge badge-sm badge-soft-danger d-inline-flex align-items-center mb-1">Declined<i class="isax isax-close-circle ms-1"></i></span>
                        <p class="fs-13">31 Jan 2025</p>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <a href="customer-details.html" class="avatar avatar-lg flex-shrink-0 me-2">
                            <img src="assets/img/users/user-22.jpg" class="rounded-circle" alt="img">
                        </a>
                        <div>
                            <h6 class="fs-14 fw-semibold mb-1"><a href="customer-details.html">Emily Clark</a></h6>
                            <p class="fs-13">QU7868</p>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge badge-sm badge-soft-success d-inline-flex align-items-center mb-1">Accepted<i class="isax isax-tick-circle ms-1"></i></span>
                        <p class="fs-13">18 Jan 2025</p>
                    </div>
                </div>
            </div> <!-- end card body -->
        </div> <!-- end card -->
    </div> <!-- end col -->
    <div class="col-md-6 col-xl-4 d-flex flex-column">
        <div class="card d-flex">
            <div class="card-body flex-fill">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1">Total Income on Invoice</p>
                        <h6 class="fs-16 fw-semibold">$98,545</h6>
                    </div>
                    <div>
                        <h6 class="fs-14 fw-semibold mb-1">30.2 <i class="isax isax-arrow-circle-up4 text-success"></i></h6>
                        <p class="fs-13">Vs Last Week</p>
                    </div>
                </div>
            </div> <!-- end card body -->
            <div id="invoice_income"></div>
        </div> <!-- end card -->
        <div class="card d-flex">
            <div class="card-body flex-fill">
                <h6 class="mb-3">Top Sales Statistics</h6>
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-3">
                    <p class="d-flex align-items-center fs-13 text-dark mb-0"><i class="fa-solid fa-circle fs-8 me-1 text-pink"></i>Dell XPS 13</p>
                    <p class="d-flex align-items-center fs-13 text-dark mb-0"><i class="fa-solid fa-circle fs-8 me-1 text-secondary"></i>Nike T-shirt</p>
                    <p class="d-flex align-items-center fs-13 text-dark mb-0"><i class="fa-solid fa-circle fs-8 me-1 text-success"></i>Apple iPhone 15</p>
                </div>
                <div id="total_sales"></div>
            </div> <!-- end card body -->
        </div> <!-- end card -->
    </div> <!-- end col -->
</div>
<!-- end row -->
<!-- End Content -->
</div>
@endsection


@section('scripts')
    <script>
        // Update time and date
        function updateDateTime() {
            const now = new Date();

            // Format time
            const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit' };
            const formattedTime = now.toLocaleTimeString('en-US', timeOptions);

            // Format date
            const dateOptions = { weekday: 'long', day: '2-digit', month: 'short', year: 'numeric' };
            const formattedDate = now.toLocaleDateString('en-US', dateOptions);

            // Update DOM
            document.getElementById('current-time').textContent = formattedTime;
            document.getElementById('current-date').textContent = formattedDate;

            // Update message based on time of day
            updateWelcomeMessage(now);
        }

        // Update welcome message based on time of day
        function updateWelcomeMessage(now) {
            const hour = now.getHours();
            const messageElement = document.getElementById('dynamic-message');
            let message = '';

            if (hour < 5) {
                message = 'Late night? You have 15+ invoices saved to draft that need to be sent to customers';
            } else if (hour < 12) {
                message = 'Good morning! You have 15+ invoices saved to draft that need to be sent to customers';
            } else if (hour < 17) {
                message = 'Good afternoon! You have 15+ invoices saved to draft that need to be sent to customers';
            } else {
                message = 'Good evening! You have 15+ invoices saved to draft that need to be sent to customers';
            }

            messageElement.textContent = message;
        }

        // Initialize and update time every second
        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Simulate user name - in a real application this would come from your backend
        document.getElementById('username').textContent = 'Alex Johnson';
    </script>
@endsection
