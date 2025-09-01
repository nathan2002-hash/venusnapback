@extends('layouts.admin')

@section('title')
    Notices Management
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Send Notices</h4>
                    </div>
                </div>
            </div>
            <!-- end page title -->

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <p class="card-title-desc">Send in-app notices to users with optional action buttons</p>

                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="mdi mdi-check-all me-2"></i>
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="mdi mdi-block-helper me-2"></i>
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <form action="{{ route('admin.notices.store') }}" method="POST" id="noticeForm" class="needs-validation" novalidate>
                                @csrf

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="recipientType" class="form-label">Send To</label>
                                            <select class="form-select" name="recipient_type" id="recipientType" required>
                                                <option selected disabled value="">Choose Recipient Type...</option>
                                                <option value="specific">Specific Users</option>
                                                <option value="all">All Users</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                Please select a recipient type.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6" id="userSelectionContainer" style="display: none;">
                                        <div class="mb-3">
                                            <label for="user_ids" class="form-label">Select Users</label>
                                            <select class="form-select select2" name="user_ids[]" id="user_ids" multiple>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}">
                                                        {{ $user->name }} ({{ $user->email }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="title" class="form-label">Title</label>
                                            <input type="text" name="title" class="form-control" id="title"
                                                placeholder="Notice Title" required maxlength="255">
                                            <div class="invalid-feedback">
                                                Please provide a title for the notice.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="content" class="form-label">Message Content</label>
                                            <textarea name="content" required class="form-control" id="content" rows="5"
                                                placeholder="Enter the message content here..."></textarea>
                                            <div class="invalid-feedback">
                                                Please provide message content.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="is_important"
                                                    id="is_important" value="1">
                                                <label class="form-check-label" for="is_important">
                                                    Mark as Important
                                                </label>
                                                <small class="form-text text-muted d-block">
                                                    Important notices will be highlighted in the app
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="action_url" class="form-label">Action URL</label>
                                            <select class="form-select" name="action_url" id="action_url">
                                                <option value="notifications">Notifications Page</option>
                                                <option value="profile">Profile Page</option>
                                                <option value="settings">Settings Page</option>
                                                <option value="message_center">Message Center</option>
                                                <option value="custom">Custom URL</option>
                                                 <option value="notifications">Notifications Page</option>
                                                <option value="profile">Profile Page</option>
                                                <option value="settings">Settings Page</option>
                                                <option value="message_center">Message Center</option>
                                                <option value="custom">Custom URL</option>
                                            </select>
                                            <small class="form-text text-muted">
                                                Where should the action button take users?
                                            </small>
                                        </div>
                                    </div>

                                    <div class="col-md-6" id="customUrlContainer" style="display: none;">
                                        <div class="mb-3">
                                            <label for="custom_url" class="form-label">Custom URL</label>
                                            <input type="text" name="custom_url" class="form-control" id="custom_url"
                                                placeholder="https://example.com/path">
                                            <small class="form-text text-muted">
                                                Enter a full URL or app deep link
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row" id="actionTextContainer" style="display: none;">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="action_text" class="form-label">Action Button Text</label>
                                            <input type="text" name="action_text" class="form-control" id="action_text"
                                                placeholder="e.g., View Details, Learn More" maxlength="50">
                                            <small class="form-text text-muted">
                                                Text for the action button (optional)
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="scheduled_at" class="form-label">Schedule Delivery (Optional)</label>
                                            <input type="datetime-local" name="scheduled_at" class="form-control" id="scheduled_at">
                                            <small class="form-text text-muted">
                                                Leave empty to send immediately
                                            </small>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="expires_at" class="form-label">Expiration Date (Optional)</label>
                                            <input type="datetime-local" name="expires_at" class="form-control" id="expires_at">
                                            <small class="form-text text-muted">
                                                When should this notice stop being shown?
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="send_push"
                                                    id="send_push" value="1" checked>
                                                <label class="form-check-label" for="send_push">
                                                    Send Push Notification
                                                </label>
                                                <small class="form-text text-muted d-block">
                                                    Users will receive a push notification in addition to the in-app notice
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <button id="submit-button" class="btn btn-primary" type="submit">
                                        <i class="mdi mdi-send me-1"></i> Send Notice
                                    </button>
                                    <button id="loading-button" class="btn btn-primary" style="display: none;" type="button" disabled>
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                        Sending...
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- end card -->
                </div> <!-- end col -->
            </div>

            <!-- Recent Notices Section -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Recent Notices</h4>

                            @if($recentNotices->isEmpty())
                                <div class="text-center py-4">
                                    <i class="mdi mdi-email-outline display-4 text-muted"></i>
                                    <p class="text-muted mt-2">No notices sent yet</p>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-centered table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Recipients</th>
                                                <th>Status</th>
                                                <th>Sent At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentNotices as $notice)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($notice->is_important)
                                                                <i class="mdi mdi-alert-circle text-warning me-2"></i>
                                                            @endif
                                                            <span class="fw-medium">{{ $notice->title }}</span>
                                                        </div>
                                                    </td>
                                                    <td>{{ $notice->users_count }} users</td>
                                                    <td>
                                                        @if($notice->scheduled_at && $notice->scheduled_at->isFuture())
                                                            <span class="badge bg-info">Scheduled</span>
                                                        @elseif($notice->expires_at && $notice->expires_at->isPast())
                                                            <span class="badge bg-secondary">Expired</span>
                                                        @else
                                                            <span class="badge bg-success">Active</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $notice->created_at->format('M j, Y g:i A') }}</td>
                                                    <td>
                                                        <a href="#" class="btn btn-sm btn-outline-primary">
                                                            <i class="mdi mdi-eye-outline"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div> <!-- container-fluid -->
    </div>
    <!-- End Page-content -->
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js" defer></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 for user selection
        $('#user_ids').select2({
            placeholder: "Select users...",
            allowClear: true
        });

        // Show/hide user selection based on recipient type
        $('#recipientType').change(function() {
            const selectedType = $(this).val();
            $('#userSelectionContainer').toggle(selectedType === 'specific');

            if (selectedType === 'specific') {
                $('#user_ids').prop('required', true);
            } else {
                $('#user_ids').prop('required', false);
            }
        });

        // Show/hide custom URL field
        $('#action_url').change(function() {
            const selectedValue = $(this).val();
            $('#customUrlContainer').toggle(selectedValue === 'custom');
            $('#actionTextContainer').toggle(selectedValue !== '');
        });

        // Form submission handling
        const form = $('#noticeForm');
        const submitButton = $('#submit-button');
        const loadingButton = $('#loading-button');

        form.on('submit', function(e) {
            if (form[0].checkValidity()) {
                // Show loading state
                submitButton.hide();
                loadingButton.show();

                // Validate specific users are selected if recipient type is specific
                const recipientType = $('#recipientType').val();
                if (recipientType === 'specific' && $('#user_ids').val().length === 0) {
                    e.preventDefault();
                    alert('Please select at least one user.');
                    submitButton.show();
                    loadingButton.hide();
                    return false;
                }

                return true;
            } else {
                e.preventDefault();
                form[0].reportValidity();
                return false;
            }
        });

        // Character counter for title
        $('#title').on('input', function() {
            const maxLength = 255;
            const currentLength = $(this).val().length;
            const remaining = maxLength - currentLength;

            if (!$(this).next('.char-count').length) {
                $(this).after('<div class="char-count form-text text-muted"></div>');
            }

            $(this).next('.char-count').text(`${remaining} characters remaining`);
        }).trigger('input');
    });
</script>

<style>
.select2-container--default .select2-selection--multiple {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    min-height: 38px;
}

.select2-container--default.select2-container--focus .select2-selection--multiple {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.char-count {
    font-size: 0.875rem;
    margin-top: 0.25rem;
}
</style>
@endsection
