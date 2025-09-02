@extends('layouts.admin')



@section('title')
    Countries
@endsection


@section('content')


<!-- Start Content -->
            <div class="content content-two">

                <!-- Page Header -->
                <div class="d-flex d-block align-items-center justify-content-between flex-wrap gap-3 mb-3">
                    <div>
                        <h6>Countries</h6>
                    </div>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                        <div class="dropdown">
                            <a href="javascript:void(0);" class="btn btn-outline-white d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                <i class="isax isax-export-1 me-1"></i>Export
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="#">Download as PDF</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">Download as Excel</a>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <a href="#" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#country">
                                <i class="isax isax-add-circle5 me-1"></i>New Country

                            </a>
                        </div>
                    </div>
                </div>
                <!-- End Page Header -->

                <!-- Table Search Start -->
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <div class="table-search d-flex align-items-center mb-0">
                                <div class="search-input">
                                    <a href="javascript:void(0);" class="btn-searchset"><i class="isax isax-search-normal fs-12"></i></a>
                                </div>
                            </div>
                            <a class="btn btn-outline-white fw-normal d-inline-flex align-items-center" href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#customcanvas">
                                <i class="isax isax-filter me-1"></i>Filter
                            </a>
                        </div>
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <div class="dropdown">
                                <a href="javascript:void(0);" class="dropdown-toggle btn btn-outline-white d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                    <i class="isax isax-sort me-1"></i>Sort By : <span class="fw-normal ms-1">Latest</span>
                                </a>
                                <ul class="dropdown-menu  dropdown-menu-end">
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item">Latest</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item">Oldest</a>
                                    </li>
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- Table Search End -->

                <!-- Table List Start -->
                <div class="table-responsive">
                    <table class="table table-nowrap datatable">
                        <thead class="thead-light">
                            <tr>
                                <th class="no-sort">
                                    <div class="form-check form-check-md">
                                        <input class="form-check-input" type="checkbox" id="select-all">
                                    </div>
                                </th>
                                <th class="no-sort">Name</th>
                                <th class="no-sort">Capital</th>
                                <th class="no-sort">Currency</th>
                                <th class="no-sort">Phone Code</th>
                                <th class="no-sort">Sample Phone</th>
                                <th class="no-sort"></th>
                            </tr>
                        </thead>
                        <tbody>
                           @foreach ($countries as $country)
                                <tr>
                                <td>
                                    <div class="form-check form-check-md">
                                        <input class="form-check-input" type="checkbox">
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-xs me-2 flex-shrink-0">
                                            <img src="{{ asset('assets/img/flags/us.png') }}" alt="img">
                                        </a>
                                        <div>
                                            <h6 class="fs-14 fw-medium mb-0"><a href="javascript:void(0);">{{ $country->name }} ({{ $country->code }})</a></h6>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $country->capital }}</td>
                                <td>{{ $country->currency }} ({{ $country->currency_code }})</td>
                                <td>{{ $country->phone_code }}</td>
                                <td>{{ $country->sample_phone }}</td>
                                <td class="action-item">
                                    <a href="javascript:void(0);" data-bs-toggle="dropdown">
                                        <i class="isax isax-more"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="countries.html" class="dropdown-item d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#edit_modal"><i class="isax isax-edit me-2"></i>Edit</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#delete_modal"><i class="isax isax-trash me-2"></i>Delete</a>
                                        </li>
                                    </ul>
                                </td>
                            </tr>
                           @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- Table List End -->

            </div>
			<!-- End Content -->
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
                            <input type="text" name="name" class="form-control" id="name" required>
                        </div>
                         <div class="mb-3">
                            <label for="continent" class="col-form-label">Continent:</label><br>
                            <select name="continent_id" class="form-control" id="continent" required>
                                <option selected disabled>Select Continent</option>
                                @foreach ($continents as $continent)
                                     <option value="{{ $continent->id }}">{{ $continent->name }}</option>
                                @endforeach
                            </select>
                        </div>
                         <div class="mb-3">
                            <label for="code" class="col-form-label">Country Code:</label>
                            <input type="text" name="code" class="form-control" id="code" required>
                        </div>
                         <div class="mb-3">
                            <label for="phone-code" class="col-form-label">Phone Code:</label>
                            <input type="text" name="phone_code" class="form-control" id="phone-name" required>
                        </div>
                         <div class="mb-3">
                            <label for="phone-sample" class="col-form-label">Sample Phone:</label>
                            <input type="text" name="sample_phone" class="form-control" id="phone-sample" required>
                        </div>
                         <div class="mb-3">
                            <label for="phone-length" class="col-form-label">Phone Length:</label>
                            <input type="text" name="phone_number_length" class="form-control" id="phone-length" required>
                        </div>
                         <div class="mb-3">
                            <label for="currency" class="col-form-label">Currency:</label>
                            <input type="text" name="currency" class="form-control" id="currency" required>
                        </div>
                         <div class="mb-3">
                            <label for="currency-code" class="col-form-label">Currency Code:</label>
                            <input type="text" name="currency_code" class="form-control" id="currency-code" required>
                        </div>
                         <div class="mb-3">
                            <label for="capital" class="col-form-label">Capital City:</label>
                            <input type="text" name="capital" class="form-control" id="capital" required>
                        </div>
                        <div class="mb-3">
                            <label for="message-text" class="col-form-label">Description:</label>
                            <textarea class="form-control" name="description" id="message-text" required></textarea>
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
