@extends('layouts.admin')



@section('title')
    Verification Centre
@endsection


@section('content')
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18"> Verification Centre</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->
        {{-- Success Message --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Error Message --}}
        @if($errors->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ $errors->first('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Validation Errors (e.g. missing form fields) --}}
        @if ($errors->any() && !$errors->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title-desc">Verification Centre for the albums</p>
                        <form action="/restricted/verification/album" method="POST" enctype="multipart/form-data" id="submitdata" class="needs-validation" novalidate>
                            @csrf
                            <div class="row">
                               <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="validationCustom05" class="form-label">Album</label>
                                        <select class="form-select" name="album_id" id="validationCustom05" required>
                                            <option selected disabled value="">Choose Album...</option>
                                            @foreach ($albums as $album)
                                               <option value="{{ $album->id }}">{{ $album->name }} ({{ $album->id }})</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select a valid album.
                                        </div>

                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="validationCustom03" class="form-label">Type</label>
                                        <select class="form-select" name="type" id="validationCustom03" required>
                                            <option selected disabled value="">Choose Type...</option>
                                            <option value="1">Album</option>
                                            <option value="0">Monetization</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select a valid type.
                                        </div>

                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="validationCustom03" class="form-label">Type</label>
                                        <select class="form-select" name="type" id="validationCustom03" required>
                                            <option selected disabled value="">Choose Type...</option>
                                            <option value="1">Verify</option>
                                            <option value="0">Take Away</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select a valid type.
                                        </div>

                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="validationCustom04" class="form-label">Reason</label>
                                        <select class="form-select" name="reason" id="validationCustom04" required>
                                            <option selected disabled value="">Choose Reason...</option>

                                              <optgroup label="Verify Album Reasons">
                                                    <option value="artist_identity_verified">Artist Identity Verified</option>
                                                    <option value="business_legit_verified">Legit Business/Brand Verified</option>
                                                    <option value="active_contributions">High Activity & Community Engagement</option>
                                                    <option value="content_quality">Outstanding Content Quality</option>
                                                    <option value="support_request_verified">Verified Through Official Support Request</option>
                                                    <option value="promotion_partnership">Verified for Promotional Partnership</option>
                                                </optgroup>

                                                <optgroup label="Remove Badge Reasons">
                                                    <option value="false_identity">False Identity or Misrepresentation</option>
                                                    <option value="policy_violation">Violation of Platform Policies</option>
                                                    <option value="inactivity_removal">Long-Term Inactivity</option>
                                                    <option value="user_request_removal">User Requested Badge Removal</option>
                                                    <option value="fraud_or_abuse">Detected Fraud or Platform Abuse</option>
                                                    <option value="content_quality_drop">Decline in Content Standards</option>
                                                </optgroup>
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select a valid reason.
                                        </div>
                                    </div>
                                </div>


                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <div>
                                            <textarea name="description" required class="form-control" rows="3"></textarea>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div>
                                <button id="submit-data-button" class="btn btn-primary btn-block col-12" type="submit">Submit Point Manage</button>
                                <button id="loading-button" class="btn btn-primary btn-block col-12" style="display: none;" type="button" disabled>
                                    <span class="btn-loading" role="status" aria-hidden="true"></span>
                                    &nbsp; &nbsp; &nbsp; Adding Badge...
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
<script>
    // Get references to the buttons and the form
    var signInButton = document.getElementById('submit-data-button');
    var loadingButton = document.getElementById('loading-button');
    var form = document.getElementById('submitdata'); // Replace 'your-form-id' with the actual ID of your form

    // Add event listener to the sign-in button
    signInButton.addEventListener('click', function(event) {
        // Check if the form is valid
        if (form.checkValidity()) {
            // Hide the sign-in button
            signInButton.style.display = 'none';
            // Show the loading button
            loadingButton.style.display = 'block';

            // You can submit the form here
            form.submit();
        } else {
            // Prevent the default form submission
            event.preventDefault();
            // Trigger the browser's native validation message
            form.reportValidity();
        }
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userSelect = document.getElementById('validationCustom05');
    const currentPointsInput = document.getElementById('current_points');

    userSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            currentPointsInput.value = selectedOption.getAttribute('data-points') || 0;
        } else {
            currentPointsInput.value = '';
        }
    });
});
</script>
@endsection
