@extends('layouts.admin')



@section('title')
    Create
@endsection


@section('content')
        <div class="page-content">
            <div class="container-fluid">

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Template Create</h4>
                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <p class="card-title-desc">Template needs low size image and make sure to attribute the author</p>
                                <form action="/restricted/template/store" method="POST" enctype="multipart/form-data" id="submitdata" class="needs-validation" novalidate>
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="tempname" class="form-label">Template Name</label>
                                                <input type="text" name="name" class="form-control" id="tempname"
                                                    placeholder="Template Name" required>
                                                <div class="valid-feedback">
                                                    Looks good!
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="author" class="form-label">Author</label>
                                                <input type="text" name="author" class="form-control" id="author"
                                                    placeholder="Author of Template" required>
                                                <div class="valid-feedback">
                                                    Looks good!
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="validationCustom03" class="form-label">Type</label>
                                                <select class="form-select" name="type" id="validationCustom03" required>
                                                    <option selected disabled value="">Choose Type...</option>
                                                    <option value="free">Free</option>
                                                    <option value="premium">Premium</option>
                                                </select>
                                                <div class="invalid-feedback">
                                                    Please select a valid tpye.
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <div>
                                                    <textarea name="description" required class="form-control" rows="3"></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="validationCustom05" class="form-label">Template File</label>
                                                <input type="file" name="tempfile" class="form-control" id="tempfile"
                                                    placeholder="tempfile" required>
                                                <div class="invalid-feedback">
                                                    Please provide a valid Template File.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <button id="submit-data-button" class="btn btn-primary btn-block col-12" type="submit">Submit Template</button>
                                        <button id="loading-button" class="btn btn-primary btn-block col-12" style="display: none;" type="button" disabled>
                                            <span class="btn-loading" role="status" aria-hidden="true"></span>
                                            &nbsp; &nbsp; &nbsp; Adding Template...
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
@endsection
