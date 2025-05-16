@extends('layouts.admin')

@section('title')
    Post Comment Replies
@endsection

@section('content')
<link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />

<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Post Comment Replies</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table id="datatable" class="table table-bordered dt-responsive nowrap w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Comment ID</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($replies as $commentreply)
                                <tr>
                                    <td>{{ $commentreply->id }}</td>
                                    <td><a href="/restricted/user/{{ $commentreply->user_id }}">{{ $commentreply->user->name }}</a></td>
                                    <td>{{ $commentreply->comment_id }}</td>
                                    <td>{{ $commentreply->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm btn-rounded waves-effect waves-light view-comment"
                                            data-bs-toggle="modal" data-bs-target="#commentModal"
                                            data-comment-id="{{ $commentreply->id }}"
                                            data-user-name="{{ $commentreply->user->name }}"
                                            data-comment-text="{{ $commentreply->comment }}">
                                            View Reply
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comment Modal -->
        <div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="commentModalLabel">Comment from <span id="commentUserName"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="/restricted/comment/hold" method="POST">
                        @csrf
                        <input type="hidden" name="comment_id" id="modalCommentId">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="commentText" class="col-form-label">Comment:</label>
                                <textarea class="form-control" readonly name="comment" id="commentText" rows="5"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="flagReason" class="col-form-label">Reason for Flagging (optional):</label>
                                <input type="text" name="reason" class="form-control" id="flagReason">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger">Flag as suspicious</button>
                        </div>
                    </form>
                </div>
            </div>
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

   <!-- Custom script for comment modal -->
   <script>
       document.addEventListener('DOMContentLoaded', function() {
           // Handle view comment button clicks
           document.querySelectorAll('.view-comment').forEach(button => {
               button.addEventListener('click', function() {
                   const commentId = this.getAttribute('data-comment-id');
                   const userName = this.getAttribute('data-user-name');
                   const commentText = this.getAttribute('data-comment-text');

                   document.getElementById('commentUserName').textContent = userName;
                   document.getElementById('commentText').value = commentText;
                   document.getElementById('modalCommentId').value = commentId;
               });
           });
       });
   </script>
@endsection
