@extends('layouts.admin')

@section('title')
    Sessions
@endsection

@section('content')
<div class="content content-two">
    <!-- Page Header -->
    <div class="d-flex d-block align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <div>
            <h6>Sessions Management</h6>
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
                                <span>Title</span>
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item d-flex align-items-center form-switch">
                                <i class="fa-solid fa-grip-vertical me-3 text-default"></i>
                                <input class="form-check-input m-0 me-2" type="checkbox" checked>
                                <span>User Agent</span>
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item d-flex align-items-center form-switch">
                                <i class="fa-solid fa-grip-vertical me-3 text-default"></i>
                                <input class="form-check-input m-0 me-2" type="checkbox" checked>
                                <span>Device</span>
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
                                <span>Date</span>
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item d-flex align-items-center form-switch">
                                <i class="fa-solid fa-grip-vertical me-3 text-default"></i>
                                <input class="form-check-input m-0 me-2" type="checkbox" checked>
                                <span>IP Address</span>
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item d-flex align-items-center form-switch">
                                <i class="fa-solid fa-grip-vertical me-3 text-default"></i>
                                <input class="form-check-input m-0 me-2" type="checkbox" checked>
                                <span>User</span>
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
                    <th>ID</th>
                    <th>Title</th>
                    <th>Version</th>
                    <th>Device</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>IP Address</th>
                    <th>User</th>
                    <th class="no-sort"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($activities as $activity)
                <tr>
                    <td>
                        <a href="javascript:void(0);" class="link-default">{{ $activity->id }}</a>
                    </td>
                    <td>{{ $activity->title }}</td>
                    <td>
                        <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $activity->user_agent }}">
                            {{ \Illuminate\Support\Str::after($activity->user_agent, 'Venusnap/') }}
                        </span>
                    </td>
                    <td>
                        <span class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $activity->device_info }}">
                            {{ $activity->device_info }}
                        </span>
                    </td>
                    <td>
                        @if ($activity->status == '1')
                            <span class="badge badge-soft-success badge-sm d-inline-flex align-items-center">
                                Success <i class="isax isax-tick-circle4 ms-1"></i>
                            </span>
                        @else
                            <span class="badge badge-soft-danger badge-sm d-inline-flex align-items-center">
                                Failed <i class="isax isax-close-circle ms-1"></i>
                            </span>
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($activity->created_at)->format('d M, Y') }}</td>
                    <td>{{ $activity->ipaddress }}</td>
                    <td>{{ $activity->user?->email ?? '—' }}</td>
                    <td class="action-item">
                        <a href="javascript:void(0);" data-bs-toggle="dropdown">
                            <i class="isax isax-more"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item d-flex align-items-center session-details"
                                   data-id="{{ $activity->id }}"
                                   data-title="{{ $activity->title }}"
                                   data-user-agent="{{ $activity->user_agent }}"
                                   data-device="{{ $activity->device_info }}"
                                   data-status="{{ $activity->status }}"
                                   data-date="{{ \Carbon\Carbon::parse($activity->created_at)->format('d M, Y') }}"
                                   data-ip="{{ $activity->ipaddress }}"
                                   data-user="{{ $activity->user?->email ?? '—' }}">
                                    <i class="isax isax-eye me-2"></i>View Details
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item d-flex align-items-center">
                                    <i class="isax isax-edit me-2"></i>Edit
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item d-flex align-items-center">
                                    <i class="isax isax-trash me-2"></i>Delete
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

<!-- Session Details Modal -->
<div class="modal fade" id="sessionDetailsModal" tabindex="-1" aria-labelledby="sessionDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sessionDetailsModalLabel">Session Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Session Information</h6>
                        <p><strong>ID:</strong> <span id="modalSessionId"></span></p>
                        <p><strong>Title:</strong> <span id="modalSessionTitle"></span></p>
                        <p><strong>Status:</strong> <span id="modalSessionStatus"></span></p>
                        <p><strong>Date:</strong> <span id="modalSessionDate"></span></p>
                        <p><strong>IP Address:</strong> <span id="modalSessionIp"></span></p>
                        <p><strong>User:</strong> <span id="modalSessionUser"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Technical Details</h6>
                        <p><strong>User Agent:</strong></p>
                        <div class="bg-light p-3 rounded mb-3">
                            <code id="modalSessionUserAgent" class="d-block text-break"></code>
                        </div>
                        <p><strong>Device Info:</strong></p>
                        <div class="bg-light p-3 rounded">
                            <code id="modalSessionDevice" class="d-block text-break"></code>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Session Details button click handler
        $('.session-details').click(function() {
            const id = $(this).data('id');
            const title = $(this).data('title');
            const userAgent = $(this).data('user-agent');
            const device = $(this).data('device');
            const status = $(this).data('status');
            const date = $(this).data('date');
            const ip = $(this).data('ip');
            const user = $(this).data('user');

            // Set modal content
            $('#modalSessionId').text(id);
            $('#modalSessionTitle').text(title);
            $('#modalSessionUserAgent').text(userAgent);
            $('#modalSessionDevice').text(device);
            $('#modalSessionDate').text(date);
            $('#modalSessionIp').text(ip);
            $('#modalSessionUser').text(user);

            // Set status with appropriate badge
            let statusBadge = '';
            if (status == '1') {
                statusBadge = '<span class="badge badge-soft-success badge-sm d-inline-flex align-items-center">Success <i class="isax isax-tick-circle4 ms-1"></i></span>';
            } else {
                statusBadge = '<span class="badge badge-soft-danger badge-sm d-inline-flex align-items-center">Failed <i class="isax isax-close-circle ms-1"></i></span>';
            }
            $('#modalSessionStatus').html(statusBadge);

            // Show modal
            $('#sessionDetailsModal').modal('show');
        });
    });
</script>
@endsection
