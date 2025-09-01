@extends('layouts.admin')

@section('title')
    Communication
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Send Communication</h4>
                    </div>
                </div>
            </div>
            <!-- end page title -->

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <p class="card-title-desc">Send messages or emails to your customers</p>
                            <form action="/restricted/communication/store" method="POST" enctype="multipart/form-data" id="communicationForm" class="needs-validation" novalidate>
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="communicationType" class="form-label">Communication Type</label>
                                            <select class="form-select" name="type" id="communicationType" required>
                                                <option selected disabled value="">Choose Type...</option>
                                                <option value="sms">SMS</option>
                                                <option value="email">Email</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                Please select a communication type.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="recipientType" class="form-label">Recipient Type</label>
                                            <select class="form-select" name="recipient_type" id="recipientType" required>
                                                <option selected disabled value="">Choose Recipient Type...</option>
                                                <option value="user">Specific User</option>
                                                <option value="album">Album Users</option>
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

                                <!-- SMS Provider Selection (shown only when SMS is selected) -->
                                <div class="row" id="smsProviderContainer" style="display: none;">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="smsProvider" class="form-label">SMS Provider</label>
                                            <select class="form-select" name="sms_provider" id="smsProvider">
                                                <option selected disabled value="">Select Provider...</option>
                                                <option value="vonage">Vonage</option>
                                                <option value="beem">Beem</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                Please select an SMS provider.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="subject" class="form-label">Subject</label>
                                            <input type="text" name="subject" class="form-control" id="subject"
                                                placeholder="Message Subject" required>
                                            <div class="valid-feedback">
                                                Looks good!
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="messageBody" class="form-label">Message Body</label>
                                            <textarea name="body" required class="form-control" id="messageBody" rows="5"></textarea>
                                            <div id="smsCounter" style="display: none; margin-top: 5px;">
                                                <small>Character count: <span id="charCount">0</span></small>
                                                <small class="float-end">Messages: <span id="messageCount">0</span></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row" id="emailAttachmentContainer" style="display: none;">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="attachment" class="form-label">Attachment (Email only)</label>
                                            <input type="file" name="attachment" class="form-control" id="attachment">
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <button id="submit-button" class="btn btn-primary btn-block col-12" type="submit">Send Communication</button>
                                    <button id="loading-button" class="btn btn-primary btn-block col-12" style="display: none;" type="button" disabled>
                                        <span class="btn-loading" role="status" aria-hidden="true"></span>
                                        &nbsp; &nbsp; &nbsp; Sending...
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

        // Show/hide SMS counter, provider selection, and email attachment based on communication type
        $('#communicationType').change(function() {
            const selectedType = $(this).val();

            if (selectedType === 'sms') {
                $('#smsCounter').show();
                $('#smsProviderContainer').show();
                $('#emailAttachmentContainer').hide();
                // Trigger character count on initial load
                countSmsCharacters();
            } else {
                $('#smsCounter').hide();
                $('#smsProviderContainer').hide();
                $('#emailAttachmentContainer').show();
            }
        });

        // SMS character counter
        $('#messageBody').on('input', function() {
            if ($('#communicationType').val() === 'sms') {
                countSmsCharacters();
            }
        });

        function countSmsCharacters() {
            const text = $('#messageBody').val();
            const charCount = text.length;
            $('#charCount').text(charCount);

            // Calculate number of SMS messages (160 chars per SMS)
            let messageCount = 1;
            if (charCount > 160) {
                messageCount = Math.ceil(charCount / 153); // After 160 chars, messages are split into 153 char segments
            }
            $('#messageCount').text(messageCount);

            // Add warning if approaching limit (optional)
            if (charCount > 140) {
                $('#smsCounter small').css('color', 'red');
            } else {
                $('#smsCounter small').css('color', 'inherit');
            }
        }

        // Form submission handling
        var submitButton = $('#submit-button');
        var loadingButton = $('#loading-button');
        var form = $('#communicationForm');

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
