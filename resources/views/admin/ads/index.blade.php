@extends('layouts.admin')

@section('title')
    Ads
@endsection

@section('content')
<div class="content content-two">
    <!-- Page Header -->
    <div class="d-flex d-block align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <div>
            <h6>Ads Management</h6>
        </div>
        <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
            <div class="dropdown">
                <a href="javascript:void(0);" class="btn btn-outline-white d-inline-flex align-items-center" data-bs-toggle="dropdown">
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
    <!-- End Page Header -->

    <!-- Table Search -->
    <div class="mb-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <div class="table-search d-flex align-items-center mb-0">
                    <div class="search-input">
                        <a href="javascript:void(0);" class="btn-searchset"><i class="isax isax-search-normal fs-12"></i></a>
                    </div>
                </div>
                <a class="btn btn-outline-white fw-normal d-inline-flex align-items-center" href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#customcanvas">
                    <i class="isax isax-filter me-1"></i>Filter
                </a>
            </div>
            <div class="d-flex align-items-center flex-wrap gap-2">
                <div class="dropdown">
                    <a href="javascript:void(0);" class="dropdown-toggle btn btn-outline-white d-inline-flex align-items-center" data-bs-toggle="dropdown">
                        <i class="isax isax-sort me-1"></i>Sort By : <span class="fw-normal ms-1">Latest</span>
                    </a>
                    <ul class="dropdown-menu  dropdown-menu-end">
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item">Latest</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item">Oldest</a>
                        </li>
                    </ul>
                </div>
                <div class="dropdown">
                    <a href="javascript:void(0);" class="dropdown-toggle btn btn-outline-white d-inline-flex align-items-center" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                        <i class="isax isax-grid-3 me-1"></i>Column
                    </a>
                    <ul class="dropdown-menu  dropdown-menu">
                        <li>
                            <label class="dropdown-item d-flex align-items-center form-switch">
                                <i class="fa-solid fa-grip-vertical me-3 text-default"></i>
                                <input class="form-check-input m-0 me-2" type="checkbox" checked>
                                <span>ID</span>
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item d-flex align-items-center form-switch">
                                <i class="fa-solid fa-grip-vertical me-3 text-default"></i>
                                <input class="form-check-input m-0 me-2" type="checkbox" checked>
                                <span>Adboard</span>
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item d-flex align-items-center form-switch">
                                <i class="fa-solid fa-grip-vertical me-3 text-default"></i>
                                <input class="form-check-input m-0 me-2" type="checkbox" checked>
                                <span>Album</span>
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item d-flex align-items-center form-switch">
                                <i class="fa-solid fa-grip-vertical me-3 text-default"></i>
                                <input class="form-check-input m-0 me-2" type="checkbox" checked>
                                <span>Date</span>
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item d-flex align-items-center form-switch">
                                <i class="fa-solid fa-grip-vertical me-3 text-default"></i>
                                <input class="form-check-input m-0 me-2" type="checkbox" checked>
                                <span>Status</span>
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item d-flex align-items-center form-switch">
                                <i class="fa-solid fa-grip-vertical me-3 text-default"></i>
                                <input class="form-check-input m-0 me-2" type="checkbox" checked>
                                <span>Points</span>
                            </label>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- /Table Search -->

    <!-- Table List -->
    <div class="table-responsive">
        <table class="table table-nowrap datatable">
            <thead>
                <tr>
                    <th class="no-sort">
                        <div class="form-check form-check-md">
                            <input class="form-check-input" type="checkbox" id="select-all">
                        </div>
                    </th>
                    <th>ID</th>
                    <th>Adboard</th>
                    <th>Album</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Points</th>
                    <th class="no-sort"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ads as $ad)
                <tr>
                    <td>
                        <div class="form-check form-check-md">
                            <input class="form-check-input" type="checkbox">
                        </div>
                    </td>
                    <td>
                        <a href="javascript:void(0);" class="link-default">{{ $ad->id }}</a>
                    </td>
                    <td>{{ $ad->adboard->name }}</td>
                    <td>{{ $ad->adboard->album->name }}</td>
                    <td>{{ $ad->created_at->format('d M, Y') }}</td>
                    <td>
                        @if ($ad->status == 'active')
                            <span class="badge badge-soft-success badge-sm d-inline-flex align-items-center">
                                Active <i class="isax isax-tick-circle4 ms-1"></i>
                            </span>
                        @elseif ($ad->status == 'paused')
                            <span class="badge badge-soft-warning badge-sm d-inline-flex align-items-center">
                                Paused <i class="isax isax-timer ms-1"></i>
                            </span>
                        @else
                            <span class="badge badge-soft-danger badge-sm d-inline-flex align-items-center">
                                Pending <i class="isax isax-close-circle ms-1"></i>
                            </span>
                        @endif
                    </td>
                    <td class="text-dark">{{ $ad->adboard->points }}</td>
                    <td class="action-item">
                        <a href="javascript:void(0);" data-bs-toggle="dropdown">
                            <i class="isax isax-more"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="javascript:void(0);"
                                   class="dropdown-item d-flex align-items-center view-details"
                                   data-ad-id="{{ $ad->id }}"
                                   data-adboard="{{ $ad->adboard->name }}"
                                   data-album="{{ $ad->adboard->album->name }}"
                                   data-date="{{ $ad->created_at->format('d M, Y') }}"
                                   data-status="{{ $ad->status }}"
                                   data-points="{{ $ad->adboard->points }}"
                                   data-cta-name="{{ $ad->cta_name }}"
                                   data-cta-link="{{ $ad->cta_link }}"
                                   data-media='@json($ad->media->map(function($m) {
                                       return ['filepath' => Storage::disk('s3')->url($m->file_path)];
                                   }))'
                                   data-id="{{ $ad->id }}">
                                    <i class="isax isax-eye me-2"></i>View Details
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item d-flex align-items-center approve-btn" data-ad-id="{{ $ad->id }}">
                                    <i class="isax isax-tick-circle me-2"></i>Approve
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item d-flex align-items-center reject-btn" data-ad-id="{{ $ad->id }}">
                                    <i class="isax isax-close-circle me-2"></i>Reject
                                </a>
                            </li>
                        </ul>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <!-- /Table List -->

</div>
<!-- End Content -->

<!-- Modal -->
<div class="modal fade" id="adDetailsModal" tabindex="-1" aria-labelledby="adDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adDetailsModalLabel">Ad Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Ad Information</h6>
                        <p><strong>Ad ID:</strong> <span id="modalAdId"></span></p>
                        <p><strong>Adboard:</strong> <span id="modalAdboard"></span></p>
                        <p><strong>Album:</strong> <span id="modalAlbum"></span></p>
                        <p><strong>Date:</strong> <span id="modalDate"></span></p>
                        <p><strong>Status:</strong> <span id="modalStatus"></span></p>
                        <p><strong>Points:</strong> <span id="modalPoints"></span></p>
                        <p><strong>CTA:</strong> <span id="modalCta"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Ad Content</h6>
                        <div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner" id="modalAdContent">
                                <!-- Carousel items will be inserted here by JavaScript -->
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger reject-btn" data-ad-id="">Reject</button>
                <button type="button" class="btn btn-success approve-btn" data-ad-id="">Approve</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // View Details button click handler
        $('.view-details').click(function() {
            const adId = $(this).data('ad-id');
            const adboard = $(this).data('adboard');
            const album = $(this).data('album');
            const date = $(this).data('date');
            const status = $(this).data('status');
            const points = $(this).data('points');
            const ctaName = $(this).data('cta-name');
            const ctaLink = $(this).data('cta-link');
            const media = $(this).data('media');

            // Set modal content
            $('#modalAdId').text(adId);
            $('#modalAdboard').text(adboard);
            $('#modalAlbum').text(album);
            $('#modalDate').text(date);
            $('#modalPoints').text(points);
            $('#modalCta').html(`<a href="${ctaLink}" target="_blank">${ctaName}</a>`);

            // Set status with appropriate badge
            let statusBadge = '';
            if (status == 'active') {
                statusBadge = '<span class="badge badge-soft-success badge-sm d-inline-flex align-items-center">Active <i class="isax isax-tick-circle4 ms-1"></i></span>';
            } else if (status == 'paused') {
                statusBadge = '<span class="badge badge-soft-warning badge-sm d-inline-flex align-items-center">Paused <i class="isax isax-timer ms-1"></i></span>';
            } else {
                statusBadge = '<span class="badge badge-soft-danger badge-sm d-inline-flex align-items-center">Pending <i class="isax isax-close-circle ms-1"></i></span>';
            }
            $('#modalStatus').html(statusBadge);

            // Build carousel items
            let carouselItems = '';
            media.forEach((item, index) => {
                const activeClass = index === 0 ? 'active' : '';
                carouselItems += `
                    <div class="carousel-item ${activeClass}">
                        <img src="${item.filepath}" class="d-block w-100" alt="Ad image ${index + 1}">
                    </div>
                `;
            });

            // Insert carousel items and show if there are images
            const $carouselInner = $('#modalAdContent');
            $carouselInner.html(carouselItems);

            // Set ad ID on action buttons
            $('.modal-footer .approve-btn, .modal-footer .reject-btn').attr('data-ad-id', adId);

            // Show modal
            $('#adDetailsModal').modal('show');
        });

        // Approve button click handler
        $('.approve-btn').click(function() {
            const adId = $(this).data('ad-id');
            updateAdStatus(adId, 'active');
        });

        // Reject button click handler
        $('.reject-btn').click(function() {
            const adId = $(this).data('ad-id');
            updateAdStatus(adId, 'rejected');
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        });

        // Function to update ad status
        function updateAdStatus(adId, status) {
            if (confirm(`Are you sure you want to ${status} this ad?`)) {
                $.ajax({
                    url: "{{ route('ads.updateStatus') }}",
                    method: 'POST',
                    data: {
                        ad_id: adId,
                        status: status
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#adDetailsModal').modal('hide');
                            location.reload(); // Reload page to reflect changes
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.responseJSON.message);
                    }
                });
            }
        }
    });
</script>
@endsection
