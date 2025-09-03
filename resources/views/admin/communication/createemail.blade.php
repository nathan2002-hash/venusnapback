@extends('layouts.admin')

@section('title')
    Email Communication
@endsection

@section('content')
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border: none;
        }
        .page-title-box {
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        .form-control, .form-select {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #ced4da;
        }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
            border-color: #86b7fe;
        }
        .btn-primary {
            background: linear-gradient(135deg, #4e54c8, #8f94fb);
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 84, 200, 0.4);
        }
        .btn-loading {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            vertical-align: -0.125em;
            border: 0.2em solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spinner-border 0.75s linear infinite;
        }
        @keyframes spinner-border {
            to { transform: rotate(360deg); }
        }
        .character-count {
            font-size: 0.875rem;
            color: #6c757d;
        }
        .email-preview {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            border-left: 4px solid #4e54c8;
        }
    </style>
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Send Email</h4>
                        <div class="page-title-right">
                            <a href="/restricted/communication/sms" class="btn btn-outline-primary">
                                <i class="fas fa-comment-sms me-1"></i> Switch to SMS
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end page title -->

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <p class="card-title-desc">Send emails to your customers with attachments</p>
                            <form action="/restricted/communication/email/store" method="POST" enctype="multipart/form-data" id="emailForm" class="needs-validation" novalidate>
                                @csrf
                                <input type="hidden" name="type" value="email">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="recipientType" class="form-label">Recipient Type</label>
                                            <select class="form-select" name="recipient_type" id="recipientType" required>
                                                <option selected disabled value="">Choose Recipient Type...</option>
                                                <option value="user">Specific User</option>
                                                <option value="album">Album Users</option>
                                                <option value="all">All Users</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                Please select a recipient type.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="emailService" class="form-label">Email Service</label>
                                            <select class="form-select" name="email_service" id="emailService" required>
                                                <option selected disabled value="">Select Service...</option>
                                                <option value="smtp">SMTP</option>
                                                <option value="mailgun">Mailgun</option>
                                                <option value="ses">Amazon SES</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                Please select an email service.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6" id="userSelectionContainer" style="display: none;">
                                        <div class="mb-3">
                                            <label for="userSelect" class="form-label">Select User</label>
                                            <select class="form-select" name="user_id" id="userSelect">
                                                <option selected disabled value="">Select User...</option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6" id="albumSelectionContainer" style="display: none;">
                                        <div class="mb-3">
                                            <label for="albumSelect" class="form-label">Select Album</label>
                                            <select class="form-select" name="album_id" id="albumSelect">
                                                <option selected disabled value="">Select Album...</option>
                                                @foreach($albums as $album)
                                                    <option value="{{ $album->id }}">{{ $album->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="subject" class="form-label">Subject</label>
                                            <input type="text" name="subject" class="form-control" id="subject"
                                                placeholder="Email Subject" required>
                                            <div class="invalid-feedback">
                                                Please provide an email subject.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="messageBody" class="form-label">Message Body</label>
                                            <textarea name="body" required class="form-control" id="messageBody" rows="8" placeholder="Write your email content here..."></textarea>
                                            <div class="character-count mt-2">
                                                Character count: <span id="charCount">0</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="attachment" class="form-label">Attachment</label>
                                            <input type="file" name="attachment" class="form-control" id="attachment">
                                            <div class="form-text">Max file size: 5MB. Allowed types: pdf, doc, docx, jpg, png</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="email-preview">
                                    <h6><i class="fas fa-eye me-2"></i>Email Preview</h6>
                                    <div class="preview-subject mb-2"><strong>Subject:</strong> <span id="previewSubject">No subject yet</span></div>
                                    <div class="preview-body"><strong>Content:</strong> <span id="previewBody">No content yet</span></div>
                                </div>

                                <div class="mt-4">
                                    <button id="submit-button" class="btn btn-primary btn-block col-12" type="submit">
                                        <i class="fas fa-paper-plane me-2"></i> Send Email
                                    </button>
                                    <button id="loading-button" class="btn btn-primary btn-block col-12" style="display: none;" type="button" disabled>
                                        <span class="btn-loading" role="status" aria-hidden="true"></span>
                                        &nbsp;Sending Email...
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Show/hide recipient selection based on recipient type
            $('#recipientType').change(function() {
                const selectedType = $(this).val();

                $('#userSelectionContainer').hide();
                $('#albumSelectionContainer').hide();

                if (selectedType === 'user') {
                    $('#userSelectionContainer').show();
                } else if (selectedType === 'album') {
                    $('#albumSelectionContainer').show();
                }
            });

            // Character counter
            $('#messageBody').on('input', function() {
                const text = $(this).val();
                const charCount = text.length;
                $('#charCount').text(charCount);

                // Update preview
                updateEmailPreview();
            });

            // Update subject preview
            $('#subject').on('input', function() {
                updateEmailPreview();
            });

            function updateEmailPreview() {
                const subject = $('#subject').val();
                const body = $('#messageBody').val();

                $('#previewSubject').text(subject || 'No subject yet');
                $('#previewBody').text(body || 'No content yet');
            }

            // Initialize preview
            updateEmailPreview();

            // Trigger recipient type change to show/hide appropriate fields
            $('#recipientType').trigger('change');

            // Form submission handling
            var submitButton = $('#submit-button');
            var loadingButton = $('#loading-button');
            var form = $('#emailForm');

            submitButton.on('click', function(event) {
                // Check if the form is valid
                if (form[0].checkValidity()) {
                    // Hide the submit button
                    submitButton.hide();
                    // Show the loading button
                    loadingButton.show();

                    // You can submit the form here
                    form.submit();
                } else {
                    // Prevent the default form submission
                    event.preventDefault();
                    // Trigger the browser's native validation message
                    form[0].reportValidity();
                }
            });
        });
    </script>
@endsection
