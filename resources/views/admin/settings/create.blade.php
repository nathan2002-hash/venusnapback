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
                            <h4 class="mb-sm-0 font-size-18">Country Create</h4>
                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <p class="card-title-desc">Country Must be unique</p>
                                <form action="/restricted/settings/country/store" id="submitdata" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                            <div class="row">
                                               <div class="col-md-4">
                                                 <div class="mb-3">
                                                <label for="name" class="col-form-label">Name:</label>
                                                <input type="text" name="name" class="form-control" id="name" required>
                                                </div>
                                               </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                    <label for="continent" class="col-form-label">Continent:</label><br>
                                                    <select name="continent_id" class="form-control" id="continent" required>
                                                        <option selected disabled>Select Continent</option>
                                                        @foreach ($continents as $continent)
                                                            <option value="{{ $continent->id }}">{{ $continent->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                    <label for="code" class="col-form-label">Country Code:</label>
                                                    <input type="text" name="code" class="form-control" id="code" required>
                                                </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                               <div class="col-md-4">
                                                 <div class="mb-3">
                                                <label for="phone-code" class="col-form-label">Phone Code:</label>
                                                <input type="text" name="phone_code" class="form-control" id="phone-name" required>
                                            </div>
                                               </div>
                                           <div class="col-md-4">
                                             <div class="mb-3">
                                                <label for="phone-sample" class="col-form-label">Sample Phone:</label>
                                                <input type="text" name="sample_phone" class="form-control" id="phone-sample" required>
                                            </div>
                                           </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                <label for="phone-length" class="col-form-label">Phone Length:</label>
                                                <input type="text" name="phone_number_length" class="form-control" id="phone-length" required>
                                            </div>
                                            </div>
                                            </div>
                                            <div class="row">
                                               <div class="col-md-4">
                                                 <div class="mb-3">
                                                <label for="currency" class="col-form-label">Currency:</label>
                                                <input type="text" name="currency" class="form-control" id="currency" required>
                                            </div>
                                               </div>
                                           <div class="col-md-4">
                                             <div class="mb-3">
                                                <label for="currency-code" class="col-form-label">Currency Code:</label>
                                                <input type="text" name="currency_code" class="form-control" id="currency-code" required>
                                            </div>
                                           </div>
                                           <div class="col-md-4">
                                             <div class="mb-3">
                                                <label for="capital" class="col-form-label">Capital City:</label>
                                                <input type="text" name="capital" class="form-control" id="capital" required>
                                            </div>
                                           </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                <label for="message-text" class="col-form-label">Description:</label>
                                                <textarea class="form-control" name="description" id="message-text" required></textarea>
                                            </div>
                                                </div>
                                            </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button id="submit-data-button" type="submit" class="btn btn-primary col-md-12"><i class="mdi mdi-plus"></i> Submit Country</button>
                                        <button id="loading-button" class="btn btn-primary col-md-12" type="button" style="display: none;" disabled>
                                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            Adding Country...
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
@endsection
