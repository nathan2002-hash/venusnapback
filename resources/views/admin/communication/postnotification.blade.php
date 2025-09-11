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

                                <!-- Hidden field for selected post -->
                                <input type="hidden" name="post_id" id="selected_post_id" required>

                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="mb-0">Select a Post</h5>
                                            <div class="d-flex gap-2">
                                                <div class="search-box">
                                                    <input type="text" id="post-search" class="form-control" placeholder="Search posts...">
                                                    <i class="mdi mdi-magnify search-icon"></i>
                                                </div>
                                                <button type="button" class="btn btn-outline-secondary" id="clear-search">
                                                    <i class="mdi mdi-close"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Post Selection Cards -->
                                        <div class="position-relative">
                                            <div class="post-cards-container" id="post-cards-container">
                                                <div class="post-cards-scroller" id="post-cards-scroller">
                                                    @foreach($posts as $post)
                                                        @php
                                                            $media = $post->postmedias->first();
                                                            $imageUrl = $media ? generateSecureMediaUrl($media->file_path_compress) : asset('assets/images/default-post.jpg');
                                                            $album = $post->album;
                                                        @endphp
                                                        <div class="post-card" data-post-id="{{ $post->id }}" data-user="{{ $post->user->name }}">
                                                            <div class="post-card-image">
                                                                <img src="{{ $imageUrl }}" alt="Post image" class="img-fluid">
                                                                <div class="post-card-overlay">
                                                                    <div class="form-check">
                                                                        <input type="radio" name="post_id" class="form-check-input post-selection-radio" value="{{ $post->id }}" id="post_{{ $post->id }}">
                                                                        <label class="form-check-label" for="post_{{ $post->id }}"></label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="post-card-body">
                                                                <h6 class="post-card-title">Post #{{ $post->id }}</h6>
                                                                <p class="post-card-text mb-1">
                                                                    <small>By: {{ $post->user->name }}</small>
                                                                </p>
                                                                @if($album)
                                                                <p class="post-card-text">
                                                                    <small class="text-muted">
                                                                        <i class="mdi mdi-album me-1"></i>{{ $album->name }}
                                                                    </small>
                                                                </p>
                                                                @endif
                                                                <p class="post-card-date">
                                                                    <small class="text-muted">{{ $post->created_at->format('M j, Y g:i A') }}</small>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <button type="button" class="post-cards-nav post-cards-nav-prev" id="scroll-prev">
                                                <i class="mdi mdi-chevron-left"></i>
                                            </button>
                                            <button type="button" class="post-cards-nav post-cards-nav-next" id="scroll-next">
                                                <i class="mdi mdi-chevron-right"></i>
                                            </button>
                                        </div>
                                        <div class="invalid-feedback" id="post-selection-error">
                                            Please select a post.
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
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

<style>
.post-cards-container {
    position: relative;
    margin: 1rem 0;
    padding: 0 40px;
}

.post-cards-scroller {
    display: flex;
    overflow-x: auto;
    gap: 15px;
    padding: 10px 0;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.post-cards-scroller::-webkit-scrollbar {
    display: none;
}

.post-card {
    flex: 0 0 auto;
    width: 250px;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    background: white;
    cursor: pointer;
}

.post-card:hover {
    border-color: #86b7fe;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.post-card.selected {
    border-color: #0d6efd;
    box-shadow: 0 4px 20px rgba(13, 110, 253, 0.2);
}

.post-card-image {
    position: relative;
    height: 150px;
    overflow: hidden;
}

.post-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.post-card:hover .post-card-image img {
    transform: scale(1.05);
}

.post-card-overlay {
    position: absolute;
    top: 10px;
    right: 10px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.post-card:hover .post-card-overlay,
.post-card.selected .post-card-overlay {
    opacity: 1;
}

.post-card-body {
    padding: 15px;
}

.post-card-title {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
    color: #212529;
}

.post-card-text {
    font-size: 12px;
    margin-bottom: 5px;
    color: #6c757d;
}

.post-card-date {
    font-size: 11px;
    color: #adb5bd;
}

.post-cards-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 50%;
    background: white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    transition: all 0.3s ease;
}

.post-cards-nav:hover {
    background: #0d6efd;
    color: white;
}

.post-cards-nav-prev {
    left: 0;
}

.post-cards-nav-next {
    right: 0;
}

.post-cards-nav:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.search-box {
    position: relative;
    width: 250px;
}

.search-box .search-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}

.loading-indicator {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    color: #6c757d;
    font-style: italic;
}

.form-check-input.post-selection-radio {
    width: 18px;
    height: 18px;
    margin: 0;
}

.form-check-input.post-selection-radio:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

#post-selection-error {
    display: none;
    margin-top: 8px;
}

/* Responsive design */
@media (max-width: 768px) {
    .post-cards-container {
        padding: 0 30px;
    }

    .post-card {
        width: 220px;
    }

    .search-box {
        width: 200px;
    }
}

@media (max-width: 576px) {
    .post-cards-container {
        padding: 0 20px;
    }

    .post-card {
        width: 200px;
    }

    .post-card-image {
        height: 120px;
    }

    .search-box {
        width: 180px;
    }

    .post-cards-nav {
        width: 32px;
        height: 32px;
    }
}

/* Custom scrollbar for webkit browsers */
.post-cards-scroller::-webkit-scrollbar {
    height: 6px;
}

.post-cards-scroller::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.post-cards-scroller::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.post-cards-scroller::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Animation for card selection */
@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.4);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(13, 110, 253, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(13, 110, 253, 0);
    }
}

.post-card.selected {
    animation: pulse 1s ease-in-out;
}

/* Focus states for accessibility */
.post-card:focus-within {
    outline: 2px solid #0d6efd;
    outline-offset: 2px;
}

.post-cards-nav:focus {
    outline: 2px solid #0d6efd;
    outline-offset: 2px;
}

/* Loading animation */
@keyframes shimmer {
    0% {
        background-position: -200px 0;
    }
    100% {
        background-position: calc(200px + 100%) 0;
    }
}

.loading-shimmer {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200px 100%;
    animation: shimmer 1.5s infinite;
}

/* Empty state styling */
.post-cards-empty {
    text-align: center;
    padding: 40px;
    color: #6c757d;
}

.post-cards-empty i {
    font-size: 48px;
    margin-bottom: 16px;
    color: #dee2e6;
}
</style>
@endsection
