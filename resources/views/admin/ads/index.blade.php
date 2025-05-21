@extends('layouts.admin')



@section('title')
    Ads
@endsection


@section('content')
<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />

        <!-- Responsive datatable examples -->
        <link href="{{ asset('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Ads</h4>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <style>
    .carousel-inner {
        max-height: 400px;
    }
    .carousel-item img {
        object-fit: contain;
        max-height: 400px;
        width: 100%;
    }
    .carousel-control-prev, .carousel-control-next {
        background-color: rgba(0,0,0,0.2);
    }
</style>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table id="datatable" class="table table-bordered dt-responsive  nowrap w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>Ad ID</th>
                                    <th>Adboard</th>
                                    <th>Album</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Points</th>
                                    <th>View Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ads as $ad)
                                <tr>
                                    <td>{{ $ad->id }}</td>
                                    <td>{{ $ad->adboard->name }}</td>
                                    <td>
                                        {{ $ad->adboard->album->name }}
                                    </td>
                                    <td>
                                        {{ $ad->created_at->format('d M, Y') }}
                                    </td>
                                    <td>
                                        @if ($ad->status == 'active')
                                            <span class="badge-soft-success font-size-13">Active</span>
                                        @elseif ($ad->status == 'paused')
                                            <span class="badge-soft-warning font-size-13">Paused</span>
                                        @else
                                            <span class="badge-soft-danger font-size-13">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $ad->adboard->points }}
                                    </td>
                                    <td>
                                        <!-- Button trigger modal -->
                                       <td>
                                            <a href="#" class="btn btn-primary btn-sm btn-rounded waves-effect waves-light view-details"
                                            data-ad-id="{{ $ad->id }}"
                                            data-adboard="{{ $ad->adboard->name }}"
                                            data-album="{{ $ad->adboard->album->name }}"
                                            data-date="{{ $ad->created_at->format('d M, Y') }}"
                                            data-status="{{ $ad->status }}"
                                            data-points="{{ $ad->adboard->points }}"
                                            data-cta-name="{{ $ad->cta_name }}"
                                            data-cta-link="{{ $ad->cta_link }}"
                                            data-media="{{ json_encode($ad->media->map(function($m) {
                                                return ['filepath' => Storage::disk('s3')->url($m->file_path)];
                                            })) }}">
                                                View Details
                                            </a>
                                        </td>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
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

    </div> <!-- container-fluid -->
</div>
<!-- End Page-content -->
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
                const media = JSON.parse($(this).data('media'));

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
                    statusBadge = '<span class="badge-soft-success font-size-13">Active</span>';
                } else if (status == 'paused') {
                    statusBadge = '<span class="badge-soft-warning font-size-13">Paused</span>';
                } else {
                    statusBadge = '<span class="badge-soft-danger font-size-13">Pending</span>';
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
                $('.approve-btn, .reject-btn').attr('data-ad-id', adId);

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

            // Function to update ad status
            function updateAdStatus(adId, status) {
                if (confirm(`Are you sure you want to ${status} this ad?`)) {
                    $.ajax({
                        url: "{{ route('admin.ads.update-status') }}",
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
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
   <!-- Required datatable js -->
   <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
   <script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
   <!-- Buttons examples -->
   <script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
   <script src="{{ asset('assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js') }}"></script>
   <script src="{{ asset('assets/libs/jszip/jszip.min.js') }}"></script>
   <script src="{{ asset('assets/libs/pdfmake/build/pdfmake.min.js') }}"></script>
   <script src="{{ asset('assets/libs/pdfmake/build/vfs_fonts.js') }}"></script>
   <script src="{{ asset('assets/libs/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
   <script src="{{ asset('assets/libs/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
   <script src="{{ asset('assets/libs/datatables.net-buttons/js/buttons.colVis.min.js') }}"></script>

   <!-- Responsive examples -->
   <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
   <script src="{{ asset('assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }}"></script>

   <!-- Datatable init js -->
   <script src="{{ asset('assets/js/pages/datatables.init.js') }}"></script>
@endsection
