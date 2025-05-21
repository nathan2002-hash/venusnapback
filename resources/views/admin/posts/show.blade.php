@extends('layouts.admin')



@section('title')
    Posts
@endsection


@section('content')
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Post</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->


        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                            <ol class="carousel-indicators">
                                <li data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active"></li>
                                <li data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1"></li>
                                <li data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2"></li>
                            </ol>
                            <div class="carousel-inner" role="listbox">
                               @foreach ($post->postmedias as $postmedia)
                                <div class="carousel-item active">
                                    <img class="d-block img-fluid" src="{{ Storage::disk('s3')->url($postmedia->file_path_compress) }}" alt="First slide">
                                </div>
                               @endforeach
                            </div>
                            <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="sr-only">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="sr-only">Next</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div> <!-- end col -->
            <!-- sample modal content -->
<div class="modal fade" id="hold" tabindex="-1" aria-labelledby="hold" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="hold">Post State</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/restricted/post/state/{{ $post->id }}" method="POST">
                @csrf
                <div class="modal-body">
                        <div class="mb-3">
                            <label for="recipient-name" class="col-form-label">Title</label>
                            <input type="text" name="title" class="form-control" id="recipient-name" required>
                        </div>
                         @if ($post->status == 'hold')
                            <div class="mb-3">
                            <label for="recipient-name" class="col-form-label"></label>
                            <input type="hidden" name="state" class="form-control" value="active" required>
                        </div>
                        @else
                            <div class="mb-3">
                            <label for="recipient-name" class="col-form-label"></label>
                            <input type="hidden" name="state" class="form-control" value="hold" required>
                        </div>
                        @endif
                        <div class="mb-3">
                            <label for="message-text" class="col-form-label">Reason for hold</label>
                            <textarea class="form-control" name="reason" id="message-text" required></textarea>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    @if ($post->status == 'deletion')
                        <button type="submit" class="btn btn-success">Restore</button>
                    @else
                         <button type="submit" class="btn btn-warning">Hold</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

 <!-- sample modal content -->
<div class="modal fade" id="delete" tabindex="-1" aria-labelledby="delete" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="delete">Post State</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/restricted/post/state/{{ $post->id }}" method="POST">
                @csrf
                <div class="modal-body">
                        <div class="mb-3">
                            <label for="recipient-name" class="col-form-label">Title</label>
                            <input type="text" name="title" class="form-control" id="recipient-name" required>
                        </div>
                        @if ($post->status == 'deletion')
                            <div class="mb-3">
                            <label for="recipient-name" class="col-form-label"></label>
                            <input type="hidden" name="state" class="form-control" value="active" required>
                        </div>
                        @else
                            <div class="mb-3">
                            <label for="recipient-name" class="col-form-label"></label>
                            <input type="hidden" name="state" class="form-control" value="deletion" required>
                        </div>
                        @endif
                        <div class="mb-3">
                            <label for="message-text" class="col-form-label">Reason for Deletion</label>
                            <textarea class="form-control" name="reason" id="message-text" required></textarea>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    @if ($post->status == 'deletion')
                        <button type="submit" class="btn btn-success">Restore</button>
                    @else
                         <button type="submit" class="btn btn-warning">Delete</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>


            <div class="col-md-6">
    <div class="card h-100 d-flex flex-column">
        <div class="card-body flex-grow-1">
            <h4 class="card-title">Post Caption</h4>
            <p class="card-title-desc">{{ $post->description }}</p>
            <ul>
                <li><b>Total Admires</b>: {{ $post->postmedias->sum(fn($media) => $media->admires->count()) }}</li>
                <li><b>Total Comments</b>: {{ $post->postmedias->sum(fn($media) => $media->comments->count()) }}</li>
                <li><b>Total Shares</b>: {{ $post->postmedias->sum(fn($media) => $media->shares->count()) }}</li>
                <li><b>Total Downloads</b>: {{ $post->postmedias->sum(fn($media) => $media->mediaDownloads->count()) }}</li>
            </ul>

            <div class="d-flex flex-wrap gap-5">
                @if ($post->status == 'hold')
                <button type="button" class="btn btn-success waves-effect waves-light w-sm" data-bs-toggle="modal" data-bs-target="#hold">
                    <i class="mdi mdi-pause d-block font-size-16"></i> UnHold
                </button>
                @else
                    <button type="button" class="btn btn-warning waves-effect waves-light w-sm" data-bs-toggle="modal" data-bs-target="#hold">
                    <i class="mdi mdi-pause d-block font-size-16"></i> Hold
                </button>
                @endif
               @if ($post->status == 'deletion')
                <button type="button" class="btn btn-success waves-effect waves-light w-sm" data-bs-toggle="modal" data-bs-target="#delete">
                    <i class="mdi mdi-trash-can d-block font-size-16"></i> Reversal
                </button>
               @else
                <button type="button" class="btn btn-danger waves-effect waves-light w-sm" data-bs-toggle="modal" data-bs-target="#delete">
                    <i class="mdi mdi-trash-can d-block font-size-16"></i> Delete
                </button>
               @endif
            </div>
        </div>
    </div>
</div>


</div> <!-- end row -->


</div> <!-- container-fluid -->
</div>
@endsection


@section('scripts')
@endsection
