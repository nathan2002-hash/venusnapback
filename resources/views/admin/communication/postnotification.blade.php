@extends('layouts.admin')

@section('title')
    Send Post Notifications
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Send Post Notifications</h4>
                    </div>
                </div>
            </div>
            <!-- end page title -->

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <p class="card-title-desc">Send notifications about specific posts to selected users</p>

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

                            <form action="{{ route('admin.notices.send-post-notification') }}" method="POST" id="postNoticeForm" class="needs-validation" novalidate>
                                @csrf

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="post_id" class="form-label">Select Post</label>
                                            <select class="form-select select2" name="post_id" id="post_id" required>
                                                <option selected disabled value="">Choose a Post...</option>
                                                @foreach($posts as $post)
                                                    <option value="{{ $post->id }}" data-user="{{ $post->user->name }}">
                                                        Post #{{ $post->id }} by {{ $post->user->name }} ({{ $post->created_at->format('M j, Y') }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback">
                                                Please select a post.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="recipientType" class="form-label">Send To</label>
                                            <select class="form-select" name="recipient_type" id="recipientType" required>
                                                <option selected disabled value="">Choose Recipient Type...</option>
                                                <option value="specific">Specific Users</option>
                                                <option value="all">All Users</option>
                                                <option value="album_supporters">Album Supporters</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                Please select a recipient type.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
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
                                            <label for="title" class="form-label">Notification Title</label>
                                            <input type="text" name="title" class="form-control" id="title"
                                                placeholder="Notification Title" required maxlength="255">
                                            <small class="form-text text-muted">
                                                This will be the title of the push notification
                                            </small>
                                            <div class="invalid-feedback">
                                                Please provide a title for the notification.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="message" class="form-label">Message Content</label>
                                            <textarea name="message" required class="form-control" id="message" rows="3"
                                                placeholder="Enter the notification message here..."></textarea>
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
                                                    Important notifications will be highlighted
                                                </small>
                                            </div>
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
                                                    Users will receive a push notification
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <button id="submit-button" class="btn btn-primary" type="submit">
                                        <i class="mdi mdi-send me-1"></i> Send Notification
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

        // Initialize Select2 for post selection
        $('#post_id').select2({
            placeholder: "Select a post...",
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

        // Auto-generate title and message based on selected post
        $('#post_id').change(function() {
            const selectedOption = $(this).find('option:selected');
            const username = selectedOption.data('user');

            // Set default title and message
            if (!$('#title').val()) {
                $('#title').val(`New content from ${username}`);
            }

            if (!$('#message').val()) {
                $('#message').val(`${username} has shared new content you might be interested in. Check it out now!`);
            }
        });

        // Form submission handling
        const form = $('#postNoticeForm');
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
</style>
@endsection
