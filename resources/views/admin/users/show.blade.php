@extends('layouts.admin')



@section('title')
    User
@endsection


@section('content')

<div class="page-content">
    <div class="container-fluid">



        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="d-flex align-items-center">
                    <img src="{{ asset('assets/images/users/avatar-1.jpg') }}" alt="" class="avatar-sm rounded">
                    <div class="ms-3 flex-grow-1">
                        <h5 class="mb-2 card-title">{{ $user->name }}</h5>
                        <p class="text-muted mb-0">{{ $user->artboard->name }}</p>
                    </div>
                    <div>
                        <a href="javascript:void(0);" class="btn btn-danger"><i class="bx bx-pause align-middle"></i> Suspend Account</a>
                        <a href="javascript:void(0);" class="btn btn-danger"><i class="bx bx-stop-circle align-middle"></i> Suspend Artboard</a>
                    </div>
                </div>
            </div><!--end col-->
        </div><!--end row-->

        <div class="row">
            <div class="col-lg-3">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium">Posts</p>
                                <h4 class="mb-0">{{ $totalposts }}</h4>
                            </div>

                            <div class="flex-shrink-0 align-self-center">
                                <div data-colors='["--bs-success", "--bs-transparent"]' dir="ltr" id="eathereum_sparkline_charts"></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body border-top py-3">
                        <p class="mb-0"> <span class="badge badge-soft-success me-1"><i class="bx bx-trending-up align-bottom me-1"></i> 18.89%</span> Increase last month</p>
                    </div>
                </div>
            </div><!--end col-->
            <div class="col-lg-3">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium">Post Medias</p>
                                <h4 class="mb-0">{{ $postmedias }}</h4>
                            </div>

                            <div class="flex-shrink-0 align-self-center">
                                <div data-colors='["--bs-success", "--bs-transparent"]' dir="ltr" id="new_application_charts"></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body border-top py-3">
                        <p class="mb-0"> <span class="badge badge-soft-success me-1"><i class="bx bx-trending-up align-bottom me-1"></i> 24.07%</span> Increase last month</p>
                    </div>
                </div>
            </div><!--end col-->
            <div class="col-lg-3">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium">Supporters</p>
                                <h4 class="mb-0">{{ $supporters }}</h4>
                            </div>

                            <div class="flex-shrink-0 align-self-center">
                                <div data-colors='["--bs-success", "--bs-transparent"]' dir="ltr" id="total_approved_charts"></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body border-top py-3">
                        <p class="mb-0"> <span class="badge badge-soft-success me-1"><i class="bx bx-trending-up align-bottom me-1"></i> 8.41%</span> Increase last month</p>
                    </div>
                </div>
            </div><!--end col-->
            <div class="col-lg-3">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium">Artworks</p>
                                <h4 class="mb-0">{{ $artworks }}</h4>
                            </div>

                            <div class="flex-shrink-0 align-self-center">
                                <div data-colors='["--bs-danger", "--bs-transparent"]' dir="ltr" id="total_rejected_charts"></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body border-top py-3">
                        <p class="mb-0"> <span class="badge badge-soft-danger me-1"><i class="bx bx-trending-down align-bottom me-1"></i> 20.63%</span> Decrease last month</p>
                    </div>
                </div>
            </div><!--end col-->
        </div><!--end row-->

        <div class="row">
            <div class="col-lg-12">
                <div class="d-flex">
                    <h4 class="card-title mb-4 flex-grow-1">Posts</h4>
                    <div>
                        <a href="job-list.html" class="btn btn-primary btn-sm">View All <i class="bx bx-right-arrow-alt"></i></a>
                    </div>
                </div>
            </div><!--end col-->
            @foreach ($posts as $post)
            <div class="col-lg-2">
                <div class="card">
                    <div class="card-body p-4">
                        <div class="text-center mb-3">
                            <img src="{{ asset('assets/images/companies/airbnb.svg') }}" alt="" class="avatar-sm">
                            <a href="job-details.html" class="text-body">
                                <h5 class="mt-4 mb-2 font-size-15">{{ substr($post->description, 0, 12) }}....</h5>
                            </a>
                            <p class="mb-0 text-muted">{{ $post->type }}</p>
                        </div>

                        <div class="d-flex">
                            <p class="mb-0 flex-grow-1 text-muted">Admires <b>{{ $post->postmedias->sum(fn($media) => $media->admires->count()) }}                            </b></p>
                        </div>

                        <div class="d-flex">
                            <p class="mb-0 text-muted">Comments <b>{{ $post->postmedias->sum(fn($media) => $media->comments->count()) }}</b></p>
                        </div>
                    </div>
                </div><!--end card-->
            </div><!--end col-->
            @endforeach
        </div><!--end row-->

        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Recent Added Jobs</h4>
                        <div data-simplebar style="max-height: 376px;">
                            <div class="vstack gap-4">
                                <div class="d-flex">
                                    <img src="assets/images/companies/wechat.svg" alt="" height="40" class="rounded">
                                    <div class="ms-2 flex-grow-1">
                                        <h6 class="mb-1 font-size-15"><a href="job-details.html" class="text-body">Marketing Director</a></h6>
                                        <p class="text-muted mb-0">Themesbrand, USA - <b>53</b> sec ago</p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                            <li><a class="dropdown-item" href="job-details.html">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Apply Now</a></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="d-flex">
                                    <img src="assets/images/companies/sass.svg" alt="" height="40" class="rounded">
                                    <div class="ms-2 flex-grow-1">
                                        <h6 class="mb-1 font-size-15"><a href="job-details.html" class="text-body">Frontend Developer</a></h6>
                                        <p class="text-muted mb-0">Themesbrand, Hong-Kong - <b>47</b> min ago</p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                                            <li><a class="dropdown-item" href="job-details.html">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Apply Now</a></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="d-flex">
                                    <img src="assets/images/companies/adobe.svg" alt="" height="40" class="rounded">
                                    <div class="ms-2 flex-grow-1">
                                        <h6 class="mb-1 font-size-15"><a href="job-details.html" class="text-body">React Developer</a></h6>
                                        <p class="text-muted mb-0">Creative Agency, Danemark - <b>1</b> hrs ago</p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light" type="button" id="dropdownMenuButton3" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton3">
                                            <li><a class="dropdown-item" href="job-details.html">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Apply Now</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <img src="assets/images/companies/airbnb.svg" alt="" height="40" class="rounded">
                                    <div class="ms-2 flex-grow-1">
                                        <h6 class="mb-1 font-size-15"><a href="job-details.html" class="text-body">NodeJs Developer</a></h6>
                                        <p class="text-muted mb-0">Skote Themes, Louisiana - <b>2</b> hrs ago</p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light" type="button" id="dropdownMenuButton4" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton4">
                                            <li><a class="dropdown-item" href="job-details.html">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Apply Now</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <img src="assets/images/companies/flutter.svg" alt="" height="40" class="rounded">
                                    <div class="ms-2 flex-grow-1">
                                        <h6 class="mb-1 font-size-15"><a href="job-details.html" class="text-body">Digital Marketing</a></h6>
                                        <p class="text-muted mb-0">Web Technology pvt.Ltd, Danemark - <b>8</b> hrs ago</p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light" type="button" id="dropdownMenuButton5" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton5">
                                            <li><a class="dropdown-item" href="job-details.html">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Apply Now</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <img src="assets/images/companies/mailchimp.svg" alt="" height="40" class="rounded">
                                    <div class="ms-2 flex-grow-1">
                                        <h6 class="mb-1 font-size-15"><a href="job-details.html" class="text-body">Marketing Director</a></h6>
                                        <p class="text-muted mb-0">Skote Technology, Dominica - <b>1</b> days ago</p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light" type="button" id="dropdownMenuButton6" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton6">
                                            <li><a class="dropdown-item" href="job-details.html">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Apply Now</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <img src="assets/images/companies/spotify.svg" alt="" height="40" class="rounded">
                                    <div class="ms-2 flex-grow-1">
                                        <h6 class="mb-1 font-size-15"><a href="job-details.html" class="text-body">Business Associate</a></h6>
                                        <p class="text-muted mb-0">Themesbrand, Russia - <b>2</b> days ago</p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light" type="button" id="dropdownMenuButton7" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton7">
                                            <li><a class="dropdown-item" href="job-details.html">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Apply Now</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <img src="assets/images/companies/reddit.svg" alt="" height="40" class="rounded">
                                    <div class="ms-2 flex-grow-1">
                                        <h6 class="mb-1 font-size-15"><a href="job-details.html" class="text-body">Backend Developer</a></h6>
                                        <p class="text-muted mb-0">Adobe Agency, Malaysia - <b>3</b> days ago</p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light" type="button" id="dropdownMenuButton8" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton8">
                                            <li><a class="dropdown-item" href="job-details.html">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Apply Now</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--end card-->
            </div><!--end col-->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Recent Added Jobs</h4>
                        <div data-simplebar style="max-height: 376px;">
                            <div class="vstack gap-4">
                                <div class="d-flex">
                                    <img src="assets/images/companies/wechat.svg" alt="" height="40" class="rounded">
                                    <div class="ms-2 flex-grow-1">
                                        <h6 class="mb-1 font-size-15"><a href="job-details.html" class="text-body">Marketing Director</a></h6>
                                        <p class="text-muted mb-0">Themesbrand, USA - <b>53</b> sec ago</p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                            <li><a class="dropdown-item" href="job-details.html">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Apply Now</a></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="d-flex">
                                    <img src="assets/images/companies/sass.svg" alt="" height="40" class="rounded">
                                    <div class="ms-2 flex-grow-1">
                                        <h6 class="mb-1 font-size-15"><a href="job-details.html" class="text-body">Frontend Developer</a></h6>
                                        <p class="text-muted mb-0">Themesbrand, Hong-Kong - <b>47</b> min ago</p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                                            <li><a class="dropdown-item" href="job-details.html">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Apply Now</a></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="d-flex">
                                    <img src="assets/images/companies/adobe.svg" alt="" height="40" class="rounded">
                                    <div class="ms-2 flex-grow-1">
                                        <h6 class="mb-1 font-size-15"><a href="job-details.html" class="text-body">React Developer</a></h6>
                                        <p class="text-muted mb-0">Creative Agency, Danemark - <b>1</b> hrs ago</p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light" type="button" id="dropdownMenuButton3" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton3">
                                            <li><a class="dropdown-item" href="job-details.html">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Apply Now</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <img src="assets/images/companies/airbnb.svg" alt="" height="40" class="rounded">
                                    <div class="ms-2 flex-grow-1">
                                        <h6 class="mb-1 font-size-15"><a href="job-details.html" class="text-body">NodeJs Developer</a></h6>
                                        <p class="text-muted mb-0">Skote Themes, Louisiana - <b>2</b> hrs ago</p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light" type="button" id="dropdownMenuButton4" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton4">
                                            <li><a class="dropdown-item" href="job-details.html">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Apply Now</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <img src="assets/images/companies/flutter.svg" alt="" height="40" class="rounded">
                                    <div class="ms-2 flex-grow-1">
                                        <h6 class="mb-1 font-size-15"><a href="job-details.html" class="text-body">Digital Marketing</a></h6>
                                        <p class="text-muted mb-0">Web Technology pvt.Ltd, Danemark - <b>8</b> hrs ago</p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light" type="button" id="dropdownMenuButton5" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton5">
                                            <li><a class="dropdown-item" href="job-details.html">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Apply Now</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <img src="assets/images/companies/mailchimp.svg" alt="" height="40" class="rounded">
                                    <div class="ms-2 flex-grow-1">
                                        <h6 class="mb-1 font-size-15"><a href="job-details.html" class="text-body">Marketing Director</a></h6>
                                        <p class="text-muted mb-0">Skote Technology, Dominica - <b>1</b> days ago</p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light" type="button" id="dropdownMenuButton6" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton6">
                                            <li><a class="dropdown-item" href="job-details.html">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Apply Now</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <img src="assets/images/companies/spotify.svg" alt="" height="40" class="rounded">
                                    <div class="ms-2 flex-grow-1">
                                        <h6 class="mb-1 font-size-15"><a href="job-details.html" class="text-body">Business Associate</a></h6>
                                        <p class="text-muted mb-0">Themesbrand, Russia - <b>2</b> days ago</p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light" type="button" id="dropdownMenuButton7" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton7">
                                            <li><a class="dropdown-item" href="job-details.html">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Apply Now</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <img src="assets/images/companies/reddit.svg" alt="" height="40" class="rounded">
                                    <div class="ms-2 flex-grow-1">
                                        <h6 class="mb-1 font-size-15"><a href="job-details.html" class="text-body">Backend Developer</a></h6>
                                        <p class="text-muted mb-0">Adobe Agency, Malaysia - <b>3</b> days ago</p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light" type="button" id="dropdownMenuButton8" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton8">
                                            <li><a class="dropdown-item" href="job-details.html">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Apply Now</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--end card-->
            </div><!--end col-->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Recent Added Jobs</h4>
                        <div data-simplebar style="max-height: 376px;">
                            <div class="vstack gap-4">
                                <div class="d-flex">
                                    <img src="assets/images/companies/wechat.svg" alt="" height="40" class="rounded">
                                    <div class="ms-2 flex-grow-1">
                                        <h6 class="mb-1 font-size-15"><a href="job-details.html" class="text-body">Marketing Director</a></h6>
                                        <p class="text-muted mb-0">Themesbrand, USA - <b>53</b> sec ago</p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                            <li><a class="dropdown-item" href="job-details.html">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Apply Now</a></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="d-flex">
                                    <img src="assets/images/companies/sass.svg" alt="" height="40" class="rounded">
                                    <div class="ms-2 flex-grow-1">
                                        <h6 class="mb-1 font-size-15"><a href="job-details.html" class="text-body">Frontend Developer</a></h6>
                                        <p class="text-muted mb-0">Themesbrand, Hong-Kong - <b>47</b> min ago</p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                                            <li><a class="dropdown-item" href="job-details.html">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Apply Now</a></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="d-flex">
                                    <img src="assets/images/companies/adobe.svg" alt="" height="40" class="rounded">
                                    <div class="ms-2 flex-grow-1">
                                        <h6 class="mb-1 font-size-15"><a href="job-details.html" class="text-body">React Developer</a></h6>
                                        <p class="text-muted mb-0">Creative Agency, Danemark - <b>1</b> hrs ago</p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light" type="button" id="dropdownMenuButton3" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton3">
                                            <li><a class="dropdown-item" href="job-details.html">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Apply Now</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <img src="assets/images/companies/airbnb.svg" alt="" height="40" class="rounded">
                                    <div class="ms-2 flex-grow-1">
                                        <h6 class="mb-1 font-size-15"><a href="job-details.html" class="text-body">NodeJs Developer</a></h6>
                                        <p class="text-muted mb-0">Skote Themes, Louisiana - <b>2</b> hrs ago</p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light" type="button" id="dropdownMenuButton4" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton4">
                                            <li><a class="dropdown-item" href="job-details.html">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Apply Now</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <img src="assets/images/companies/flutter.svg" alt="" height="40" class="rounded">
                                    <div class="ms-2 flex-grow-1">
                                        <h6 class="mb-1 font-size-15"><a href="job-details.html" class="text-body">Digital Marketing</a></h6>
                                        <p class="text-muted mb-0">Web Technology pvt.Ltd, Danemark - <b>8</b> hrs ago</p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light" type="button" id="dropdownMenuButton5" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton5">
                                            <li><a class="dropdown-item" href="job-details.html">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Apply Now</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <img src="assets/images/companies/mailchimp.svg" alt="" height="40" class="rounded">
                                    <div class="ms-2 flex-grow-1">
                                        <h6 class="mb-1 font-size-15"><a href="job-details.html" class="text-body">Marketing Director</a></h6>
                                        <p class="text-muted mb-0">Skote Technology, Dominica - <b>1</b> days ago</p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light" type="button" id="dropdownMenuButton6" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton6">
                                            <li><a class="dropdown-item" href="job-details.html">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Apply Now</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <img src="assets/images/companies/spotify.svg" alt="" height="40" class="rounded">
                                    <div class="ms-2 flex-grow-1">
                                        <h6 class="mb-1 font-size-15"><a href="job-details.html" class="text-body">Business Associate</a></h6>
                                        <p class="text-muted mb-0">Themesbrand, Russia - <b>2</b> days ago</p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light" type="button" id="dropdownMenuButton7" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton7">
                                            <li><a class="dropdown-item" href="job-details.html">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Apply Now</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <img src="assets/images/companies/reddit.svg" alt="" height="40" class="rounded">
                                    <div class="ms-2 flex-grow-1">
                                        <h6 class="mb-1 font-size-15"><a href="job-details.html" class="text-body">Backend Developer</a></h6>
                                        <p class="text-muted mb-0">Adobe Agency, Malaysia - <b>3</b> days ago</p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light" type="button" id="dropdownMenuButton8" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton8">
                                            <li><a class="dropdown-item" href="job-details.html">View Details</a></li>
                                            <li><a class="dropdown-item" href="#">Apply Now</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->

    </div> <!-- container-fluid -->
</div>
@endsection


@section('scripts')

@endsection
