@extends('layouts.admin')



@section('title')
    Albums
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
                    <h4 class="mb-sm-0 font-size-18">Albums</h4>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table id="datatable" class="table table-bordered dt-responsive  nowrap w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>Album ID</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Owner</th>
                                    <th>Supporters</th>
                                    <th>Status</th>
                                    <th class="text-center">View Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($albums as $album)
                                <tr>
                                    <td><a href="javascript: void(0);" class="text-body fw-bold">{{ $album->id }}</a> </td>
                                    <td>{{ $album->name }}</td>
                                    <td>{{ $album->type }}</td>
                                    <td>{{ $album->user->email }}</td>
                                    <td>{{ $album->supporters->count() }}</td>
                                    <td>
                                        @if ($album->status == 'active')
                                        <span class="badge-soft-success font-size-11">Active</span>
                                        @elseif ($album->status == 'deleted')
                                        <span class="badge-soft-danger font-size-11">Deleted</span>
                                        @elseif ($album->status == 'suspended')
                                        <span class="badge-soft-warning font-size-11">Suspended</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <!-- Button trigger modal -->
                                        <a href="#" class="btn btn-primary btn-sm btn-rounded waves-effect waves-light">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
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
