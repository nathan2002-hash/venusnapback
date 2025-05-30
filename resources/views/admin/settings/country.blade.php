@extends('layouts.admin')



@section('title')
    Countries
@endsection


@section('content')
<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />

        <!-- Responsive datatable examples -->
        <link href="{{ asset('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Countries</h4>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="col-4">
                        <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#country">New Country</button>
                    </div>
                    <div class="card-body">
                        <table id="datatable" class="table table-bordered dt-responsive  nowrap w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>C Code</th>
                                    <th>Capital</th>
                                    <th>Currency</th>
                                    <th>Currency Code</th>
                                    <th>P Code</th>
                                    <th>Sample Phone</th>
                                    <th>Phone Len</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($countries as $country)
                                <tr>
                                    <td>{{ $country->name }}</td>
                                    <td>{{ $country->code }}</td>
                                    <td>{{ $country->capital }}</td>
                                    <td>{{ $country->currency }}</td>
                                    <td>{{ $country->currency_code }}</td>
                                    <td>{{ $country->phone_code }}</td>
                                    <td>{{ $country->sample_phone }}</td>
                                    <td>{{ $country->phone_number_length }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="country" tabindex="-1" aria-labelledby="country" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="country">New Unique Country</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/restricted/settings/country/store" id="submitdata" method="POST">
                @csrf
                <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="col-form-label">Name:</label>
                            <input type="text" name="name" class="form-control" id="name">
                        </div>
                         <div class="mb-3">
                            <label for="continent" class="col-form-label">Continent:</label><br>
                            <select name="continent_id" class="form-control" id="continent">
                                <option selected disabled>Select Continent</option>
                                @foreach ($continents as $continent)
                                     <option value="{{ $continent->id }}">{{ $continent->name }}</option>
                                @endforeach
                            </select>
                        </div>
                         <div class="mb-3">
                            <label for="code" class="col-form-label">Country Code:</label>
                            <input type="text" name="code" class="form-control" id="code">
                        </div>
                         <div class="mb-3">
                            <label for="phone-code" class="col-form-label">Phone Code:</label>
                            <input type="text" name="phone_code" class="form-control" id="phone-name">
                        </div>
                         <div class="mb-3">
                            <label for="phone-sample" class="col-form-label">Sample Phone:</label>
                            <input type="text" name="sample_phone" class="form-control" id="phone-sample">
                        </div>
                         <div class="mb-3">
                            <label for="phone-length" class="col-form-label">Phone Length:</label>
                            <input type="text" name="phone_number_length" class="form-control" id="phone-length">
                        </div>
                         <div class="mb-3">
                            <label for="currency" class="col-form-label">Currency:</label>
                            <input type="text" name="currency" class="form-control" id="currency">
                        </div>
                         <div class="mb-3">
                            <label for="currency-code" class="col-form-label">Currency Code:</label>
                            <input type="text" name="currency_code" class="form-control" id="currency-code">
                        </div>
                         <div class="mb-3">
                            <label for="capital" class="col-form-label">Capital City:</label>
                            <input type="text" name="capital" class="form-control" id="capital">
                        </div>
                        <div class="mb-3">
                            <label for="message-text" class="col-form-label">Description:</label>
                            <textarea class="form-control" name="description" id="message-text"></textarea>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button id="submit-data-button" type="submit" class="btn btn-primary"><i class="mdi mdi-plus"></i> Submit Country</button>
                    <button id="loading-button" class="btn btn-primary" type="button" style="display: none;" disabled>
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Adding Country...
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

    </div> <!-- container-fluid -->
</div>
<!-- End Page-content -->
@endsection


@section('scripts')
   <!-- Required datatable js -->
   <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
   <script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
   <!-- Buttons examples -->
   <script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
   <script src="{{ asset('assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js') }}"></script>
   <script src="{{ asset('assets/libs/jszip/jszip.min.js') }}"></script>
   <script src="{{ asset('assets/libs/pdfmake/build/pdfmake.min.js') }}"></script>
   <script src="{{ asset('assets/libs/pdfmake/build/vfs_fonts.js') }}"></script>
   <script src="{{ asset('assets/libs/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
   <script src="{{ asset('assets/libs/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
   <script src="{{ asset('assets/libs/datatables.net-buttons/js/buttons.colVis.min.js') }}"></script>

   <!-- Responsive examples -->
   <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
   <script src="{{ asset('assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }}"></script>

   <!-- Datatable init js -->
   <script src="{{ asset('assets/js/pages/datatables.init.js') }}"></script>
@endsection
