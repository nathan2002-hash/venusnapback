@extends('layouts.admin')



@section('title')
    Point Manage
@endsection


@section('content')
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18"> Point Manage</h4>
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
                        <p class="card-title-desc">Points Management for the user</p>
                        <form action="/restricted/point/manage/user" method="POST" enctype="multipart/form-data" id="submitdata" class="needs-validation" novalidate>
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="validationCustom05" class="form-label">Customer</label>
                                        <select class="form-select" name="user_id" id="validationCustom05" required>
                                            <option selected disabled value="">Choose Customer...</option>
                                            @foreach ($users as $user)
                                               <option value="{{ $user->id }}" data-points="{{ $user->points }}">{{ $user->email }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select a valid user.
                                        </div>

                                    </div>
                                </div>
                                 <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="current_points" class="form-label">Current User Points</label>
                                        <input type="number" class="form-control" id="current_points"
                                            placeholder="Points" readonly>
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="points" class="form-label">Points</label>
                                        <input type="number" name="points" class="form-control" id="points"
                                            placeholder="Points" required>
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="validationCustom03" class="form-label">Type</label>
                                        <select class="form-select" name="type" id="validationCustom03" required>
                                            <option selected disabled value="">Choose Type...</option>
                                            <option value="add">Add</option>
                                            <option value="remove">Remove</option>
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

                                            <optgroup label="Add Reasons">
                                                <option value="reward_campaign">Reward for Campaign Participation</option>
                                                <option value="bonus_activity">Bonus for User Activity</option>
                                                <option value="manual_adjustment_add">Manual Adjustment - Added</option>
                                                <option value="refund_compensation">Refund / Compensation</option>
                                                <option value="support_promotion">Support / Promotional Reward</option>
                                            </optgroup>

                                            <optgroup label="Remove Reasons">
                                                <option value="violation_penalty">Policy Violation Penalty</option>
                                                <option value="fraudulent_activity">Fraudulent Activity Detected</option>
                                                <option value="manual_adjustment_remove">Manual Adjustment - Removed</option>
                                                <option value="chargeback_penalty">Chargeback / Dispute Penalty</option>
                                                <option value="duplicate_correction">Duplicate Correction</option>
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
                                    &nbsp; &nbsp; &nbsp; Managing point...
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
