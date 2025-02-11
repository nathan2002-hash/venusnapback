@extends('layouts.user')



@section('title')
    Welcome
@endsection


@section('content')

<div class="page-content">
        <!-- Swiper -->
        <div class="swiper mySwiper">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <div class="reel-area">
                        <div class="top-content">
                            <div class="user-profile">
                                <h4 class="mb-0 text-white">Reels</h4>
                                <a href="javascript:void(0);" class="back-btn">
                                    <i class="fa fa-close text-white"></i>
                                </a>
                            </div>
                        </div>
                        <div class="reel-section">
                            <div class="user-item">
                                <a href="javascript:void(0);">
                                    <div class="media media-40 rounded-circle">
                                        <img src="assets/images/stories/small/pic6.jpg" alt="/">
                                    </div>
                                </a>
                                <div class="ms-2">
                                    <a href="javascript:void(0);">
                                        <span class="text-white">@barian__23</span>
                                    </a>
                                    <a href="javascript:void(0);" class="follow-btn ms-3">UNFAN</a>
                                </div>
                            </div>
                            <div class="reel-actions-wrapper">
                                <div class="reel-actions">
                                    <a href="javascript:void(0);" class="r-btn">
                                        <div class="like-button font-24"><i class="fa-regular fa-heart ms-auto"></i></div>
                                        <span>87</span>
                                    </a>
                                    <a href="javascript:void(0);" class="r-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512.012 512.012" width="512" height="512" xmlns:v="https://vecta.io/nano"><path d="M255.999 512C114.614 511.999-.001 397.383 0 255.998A256 256 0 0 1 74.98 74.98c99.989-99.971 262.089-99.956 362.059.033 87.177 87.193 99.82 224.139 30.081 325.819 3.229 13.319 21.796 50.976 38.887 81.044 4.829 8.496 1.857 19.298-6.638 24.127-5.328 3.029-11.845 3.085-17.224.148a934.2 934.2 0 0 0-38.23-19.527c-28.226-13.549-40.43-17.189-45.051-18.167-42.193 28.481-91.96 43.649-142.865 43.543zm0-476.611c-121.645 0-220.61 98.965-220.61 220.61s98.965 220.611 220.61 220.611a219.23 219.23 0 0 0 126.409-39.783c9.909-6.943 23.155-3.859 35.991.506 8.31 2.831 18.691 7.099 30.901 12.717-5.691-11.766-10.051-21.759-12.979-29.751-5.41-14.762-7.64-26.513-.94-35.85A219.05 219.05 0 0 0 476.611 256c0-121.646-98.966-220.611-220.612-220.611z"/></svg>
                                    </a>
                                    <a href="javascript:void(0);" class="r-btn" data-bs-toggle="offcanvas" data-bs-target="#offcanvasBottom1" aria-controls="offcanvasBottom">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512.001 512.001" xmlns:v="https://vecta.io/nano"><path d="M507.608 4.395a15 15 0 0 0-16.177-3.321L9.43 193.872a15 15 0 0 0-9.42 13.395 15 15 0 0 0 8.445 14.029l190.068 92.181 92.182 190.068A15 15 0 0 0 304.198 512l.536-.01a15 15 0 0 0 13.394-9.419l192.8-481.998a15 15 0 0 0-3.32-16.178zM52.094 209.118L434.72 56.069 206.691 284.096 52.094 209.118zm250.789 250.789l-74.979-154.599 228.03-228.027-153.051 382.626z"/></svg>
                                    </a>
                                    <a href="javascript:void(0);" class="r-btn pb-0" data-bs-toggle="offcanvas" data-bs-target="#offcanvasBottom2" aria-controls="offcanvasBottom">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </a>
                                </div>
                            </div>

                        </div>
                        {{-- <center>
                            <video autoplay loop src="assets/images/reels/video1.mp4" type="video/mp4" style="height: 77vh"></video>
                        </center> --}}
                       <center>
                        <video autoplay loop muted style="height: 60vh">
                            <source src="assets/images/reels/video1.mp4" type="video/mp4">
                            </video>

                       </center>

                    </div>
                    <div class="menubar-area" style="height: 12vh; background-color: #000;">
                        <div class="toolbar-inner menubar-nav">
                            {{-- <a href="index.html" class="nav-link active">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" xmlns:v="https://vecta.io/nano"><path d="M21.44 11.035a.75.75 0 0 1-.69.465H18.5V19a2.25 2.25 0 0 1-2.25 2.25h-3a.75.75 0 0 1-.75-.75V16a.75.75 0 0 0-.75-.75h-1.5a.75.75 0 0 0-.75.75v4.5a.75.75 0 0 1-.75.75h-3A2.25 2.25 0 0 1 3.5 19v-7.5H1.25a.75.75 0 0 1-.69-.465.75.75 0 0 1 .158-.818l9.75-9.75A.75.75 0 0 1 11 .246a.75.75 0 0 1 .533.222l9.75 9.75a.75.75 0 0 1 .158.818z" fill="#b5b5b5"/></svg>
                            </a> --}}
                            <a href="javascript:void(0);" class="nav-link r-btn">
                                <div class="like-button font-24">
                                    <i class="fa-regular fa-heart ms-auto" style="color: white"></i>
                                    <span style="color: white">787</span>
                                </div>
                            </a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <a href="javascript:void(0);" class="nav-link r-btn">
                                <div class="like-button font-24">
                                    <i class="fa-regular fa-comment ms-auto" style="color: white"></i>
                                    <span style="color: white">787</span>
                                </div>
                            </a>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <a href="javascript:void(0);" class="nav-link r-btn">
                                <div class="like-button font-24">
                                    <i class="fa-regular fa-paper-plane ms-auto" style="color: white"></i>
                                </div>
                            </a>
                            {{-- <a href="profile.html" class="nav-link">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="21" fill="#b5b5b5" xmlns:v="https://vecta.io/nano"><path d="M8 7.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 1 0 0 7.5zm7.5 9v1.5c-.002.199-.079.39-.217.532C13.61 20.455 8.57 20.5 8 20.5s-5.61-.045-7.282-1.718C.579 18.64.501 18.449.5 18.25v-1.5a7.5 7.5 0 1 1 15 0z"/></svg>
                            </a> --}}
                        </div>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="reel-area">
                        <div class="top-content">
                            <div class="user-profile">
                                <h4 class="mb-0 text-white">Reels</h4>
                                <a href="javascript:void(0);" class="back-btn">
                                    <i class="fa fa-close text-white"></i>
                                </a>
                            </div>
                        </div>
                        <div class="reel-section">
                            <div class="user-item">
                                <a href="javascript:void(0);">
                                    <div class="media media-40 rounded-circle">
                                        <img src="assets/images/stories/small/pic3.jpg" alt="/">
                                    </div>
                                </a>
                                <div class="ms-2">
                                    <a href="javascript:void(0);">
                                        <span class="text-white">christian_Hang</span>
                                    </a>
                                    <a href="javascript:void(0);" class="follow-btn ms-3">UNFOLLOW</a>
                                </div>
                            </div>
                            <div class="reel-actions">
                                <a href="javascript:void(0);" class="r-btn">
                                    <div class="like-button font-24"><i class="fa-regular fa-heart ms-auto"></i></div>
                                    <span>87</span>
                                </a>
                                <a href="javascript:void(0);" class="r-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512.012 512.012" width="512" height="512" xmlns:v="https://vecta.io/nano"><path d="M255.999 512C114.614 511.999-.001 397.383 0 255.998A256 256 0 0 1 74.98 74.98c99.989-99.971 262.089-99.956 362.059.033 87.177 87.193 99.82 224.139 30.081 325.819 3.229 13.319 21.796 50.976 38.887 81.044 4.829 8.496 1.857 19.298-6.638 24.127-5.328 3.029-11.845 3.085-17.224.148a934.2 934.2 0 0 0-38.23-19.527c-28.226-13.549-40.43-17.189-45.051-18.167-42.193 28.481-91.96 43.649-142.865 43.543zm0-476.611c-121.645 0-220.61 98.965-220.61 220.61s98.965 220.611 220.61 220.611a219.23 219.23 0 0 0 126.409-39.783c9.909-6.943 23.155-3.859 35.991.506 8.31 2.831 18.691 7.099 30.901 12.717-5.691-11.766-10.051-21.759-12.979-29.751-5.41-14.762-7.64-26.513-.94-35.85A219.05 219.05 0 0 0 476.611 256c0-121.646-98.966-220.611-220.612-220.611z"/></svg>
                                </a>
                                <a href="javascript:void(0);" class="r-btn" data-bs-toggle="offcanvas" data-bs-target="#offcanvasBottom1" aria-controls="offcanvasBottom">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512.001 512.001" xmlns:v="https://vecta.io/nano"><path d="M507.608 4.395a15 15 0 0 0-16.177-3.321L9.43 193.872a15 15 0 0 0-9.42 13.395 15 15 0 0 0 8.445 14.029l190.068 92.181 92.182 190.068A15 15 0 0 0 304.198 512l.536-.01a15 15 0 0 0 13.394-9.419l192.8-481.998a15 15 0 0 0-3.32-16.178zM52.094 209.118L434.72 56.069 206.691 284.096 52.094 209.118zm250.789 250.789l-74.979-154.599 228.03-228.027-153.051 382.626z"/></svg>
                                </a>
                                <a href="javascript:void(0);" class="r-btn pb-0" data-bs-toggle="offcanvas" data-bs-target="#offcanvasBottom2" aria-controls="offcanvasBottom">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </a>
                            </div>
                        </div>

                        <video autoplay loop muted>
                        <source src="assets/images/reels/video2.mp4" type="video/mp4">
                        </video>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="reel-area">
                        <div class="top-content">
                            <div class="user-profile">
                                <h4 class="mb-0 text-white">Reels</h4>
                                <a href="javascript:void(0);" class="back-btn">
                                    <i class="fa fa-close text-white"></i>
                                </a>
                            </div>
                        </div>
                        <div class="reel-section">
                            <div class="user-item">
                                <a href="javascript:void(0);">
                                    <div class="media media-40 rounded-circle">
                                        <img src="assets/images/stories/small/pic4.jpg" alt="/">
                                    </div>
                                </a>
                                <div class="ms-2">
                                    <a href="javascript:void(0);">
                                        <span class="text-white">yatin_325</span>
                                    </a>
                                    <a href="javascript:void(0);" class="follow-btn ms-3">UNFOLLOW</a>
                                </div>
                            </div>
                            <div class="reel-actions">
                                <a href="javascript:void(0);" class="r-btn">
                                    <div class="like-button font-24"><i class="fa-regular fa-heart ms-auto"></i></div>
                                    <span>87</span>
                                </a>
                                <a href="javascript:void(0);" class="r-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512.012 512.012" width="512" height="512" xmlns:v="https://vecta.io/nano"><path d="M255.999 512C114.614 511.999-.001 397.383 0 255.998A256 256 0 0 1 74.98 74.98c99.989-99.971 262.089-99.956 362.059.033 87.177 87.193 99.82 224.139 30.081 325.819 3.229 13.319 21.796 50.976 38.887 81.044 4.829 8.496 1.857 19.298-6.638 24.127-5.328 3.029-11.845 3.085-17.224.148a934.2 934.2 0 0 0-38.23-19.527c-28.226-13.549-40.43-17.189-45.051-18.167-42.193 28.481-91.96 43.649-142.865 43.543zm0-476.611c-121.645 0-220.61 98.965-220.61 220.61s98.965 220.611 220.61 220.611a219.23 219.23 0 0 0 126.409-39.783c9.909-6.943 23.155-3.859 35.991.506 8.31 2.831 18.691 7.099 30.901 12.717-5.691-11.766-10.051-21.759-12.979-29.751-5.41-14.762-7.64-26.513-.94-35.85A219.05 219.05 0 0 0 476.611 256c0-121.646-98.966-220.611-220.612-220.611z"/></svg>
                                </a>
                                <a href="javascript:void(0);" class="r-btn" data-bs-toggle="offcanvas" data-bs-target="#offcanvasBottom1" aria-controls="offcanvasBottom">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512.001 512.001" xmlns:v="https://vecta.io/nano"><path d="M507.608 4.395a15 15 0 0 0-16.177-3.321L9.43 193.872a15 15 0 0 0-9.42 13.395 15 15 0 0 0 8.445 14.029l190.068 92.181 92.182 190.068A15 15 0 0 0 304.198 512l.536-.01a15 15 0 0 0 13.394-9.419l192.8-481.998a15 15 0 0 0-3.32-16.178zM52.094 209.118L434.72 56.069 206.691 284.096 52.094 209.118zm250.789 250.789l-74.979-154.599 228.03-228.027-153.051 382.626z"/></svg>
                                </a>
                                <a href="javascript:void(0);" class="r-btn pb-0" data-bs-toggle="offcanvas" data-bs-target="#offcanvasBottom2" aria-controls="offcanvasBottom">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </a>
                            </div>
                        </div>

                        <video autoplay loop muted>
                            <source src="assets/images/reels/video3.mp4" type="video/mp4">
                        </video>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="reel-area">
                        <div class="top-content">
                            <div class="user-profile">
                                <h4 class="mb-0 text-white">Reels</h4>
                                <a href="javascript:void(0);" class="back-btn">
                                    <i class="fa fa-close text-white"></i>
                                </a>
                            </div>
                        </div>
                        <div class="reel-section">
                            <div class="user-item">
                                <a href="javascript:void(0);">
                                    <div class="media media-40 rounded-circle">
                                        <img src="assets/images/stories/small/pic8.jpg" alt="/">
                                    </div>
                                </a>
                                <div class="ms-2">
                                    <a href="javascript:void(0);">
                                        <span class="text-white">Tjangid_293</span>
                                    </a>
                                    <a href="javascript:void(0);" class="follow-btn ms-3">UNFOLLOW</a>
                                </div>
                            </div>
                            <div class="reel-actions">
                                <a href="javascript:void(0);" class="r-btn">
                                    <div class="like-button font-24"><i class="fa-regular fa-heart ms-auto"></i></div>
                                    <span>87</span>
                                </a>
                                <a href="javascript:void(0);" class="r-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512.012 512.012" width="512" height="512" xmlns:v="https://vecta.io/nano"><path d="M255.999 512C114.614 511.999-.001 397.383 0 255.998A256 256 0 0 1 74.98 74.98c99.989-99.971 262.089-99.956 362.059.033 87.177 87.193 99.82 224.139 30.081 325.819 3.229 13.319 21.796 50.976 38.887 81.044 4.829 8.496 1.857 19.298-6.638 24.127-5.328 3.029-11.845 3.085-17.224.148a934.2 934.2 0 0 0-38.23-19.527c-28.226-13.549-40.43-17.189-45.051-18.167-42.193 28.481-91.96 43.649-142.865 43.543zm0-476.611c-121.645 0-220.61 98.965-220.61 220.61s98.965 220.611 220.61 220.611a219.23 219.23 0 0 0 126.409-39.783c9.909-6.943 23.155-3.859 35.991.506 8.31 2.831 18.691 7.099 30.901 12.717-5.691-11.766-10.051-21.759-12.979-29.751-5.41-14.762-7.64-26.513-.94-35.85A219.05 219.05 0 0 0 476.611 256c0-121.646-98.966-220.611-220.612-220.611z"/></svg>
                                </a>
                                <a href="javascript:void(0);" class="r-btn" data-bs-toggle="offcanvas" data-bs-target="#offcanvasBottom1" aria-controls="offcanvasBottom">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512.001 512.001" xmlns:v="https://vecta.io/nano"><path d="M507.608 4.395a15 15 0 0 0-16.177-3.321L9.43 193.872a15 15 0 0 0-9.42 13.395 15 15 0 0 0 8.445 14.029l190.068 92.181 92.182 190.068A15 15 0 0 0 304.198 512l.536-.01a15 15 0 0 0 13.394-9.419l192.8-481.998a15 15 0 0 0-3.32-16.178zM52.094 209.118L434.72 56.069 206.691 284.096 52.094 209.118zm250.789 250.789l-74.979-154.599 228.03-228.027-153.051 382.626z"/></svg>
                                </a>
                                <a href="javascript:void(0);" class="r-btn pb-0" data-bs-toggle="offcanvas" data-bs-target="#offcanvasBottom2" aria-controls="offcanvasBottom">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </a>
                            </div>
                        </div>

                        <video autoplay loop muted>
                        <source src="assets/images/reels/video4.mp4" type="video/mp4">
                        </video>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="reel-area">
                        <div class="top-content">
                            <div class="user-profile">
                                <h4 class="mb-0 text-white">Reels</h4>
                                <a href="javascript:void(0);" class="back-btn">
                                    <i class="fa fa-close text-white"></i>
                                </a>
                            </div>
                        </div>
                        <div class="reel-section">
                            <div class="user-item">
                                <a href="javascript:void(0);">
                                    <div class="media media-40 rounded-circle">
                                        <img src="assets/images/stories/small/pic6.jpg" alt="/">
                                    </div>
                                </a>
                                <div class="ms-2">
                                    <a href="javascript:void(0);">
                                        <span class="text-white">@barian__23</span>
                                    </a>
                                    <a href="javascript:void(0);" class="follow-btn ms-3">UNFOLLOW</a>
                                </div>
                            </div>
                            <div class="reel-actions">
                                <a href="javascript:void(0);" class="r-btn">
                                    <div class="like-button font-24"><i class="fa-regular fa-heart ms-auto"></i></div>
                                    <span>87</span>
                                </a>
                                <a href="javascript:void(0);" class="r-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512.012 512.012" width="512" height="512" xmlns:v="https://vecta.io/nano"><path d="M255.999 512C114.614 511.999-.001 397.383 0 255.998A256 256 0 0 1 74.98 74.98c99.989-99.971 262.089-99.956 362.059.033 87.177 87.193 99.82 224.139 30.081 325.819 3.229 13.319 21.796 50.976 38.887 81.044 4.829 8.496 1.857 19.298-6.638 24.127-5.328 3.029-11.845 3.085-17.224.148a934.2 934.2 0 0 0-38.23-19.527c-28.226-13.549-40.43-17.189-45.051-18.167-42.193 28.481-91.96 43.649-142.865 43.543zm0-476.611c-121.645 0-220.61 98.965-220.61 220.61s98.965 220.611 220.61 220.611a219.23 219.23 0 0 0 126.409-39.783c9.909-6.943 23.155-3.859 35.991.506 8.31 2.831 18.691 7.099 30.901 12.717-5.691-11.766-10.051-21.759-12.979-29.751-5.41-14.762-7.64-26.513-.94-35.85A219.05 219.05 0 0 0 476.611 256c0-121.646-98.966-220.611-220.612-220.611z"/></svg>
                                </a>
                                <a href="javascript:void(0);" class="r-btn" data-bs-toggle="offcanvas" data-bs-target="#offcanvasBottom1" aria-controls="offcanvasBottom">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512.001 512.001" xmlns:v="https://vecta.io/nano"><path d="M507.608 4.395a15 15 0 0 0-16.177-3.321L9.43 193.872a15 15 0 0 0-9.42 13.395 15 15 0 0 0 8.445 14.029l190.068 92.181 92.182 190.068A15 15 0 0 0 304.198 512l.536-.01a15 15 0 0 0 13.394-9.419l192.8-481.998a15 15 0 0 0-3.32-16.178zM52.094 209.118L434.72 56.069 206.691 284.096 52.094 209.118zm250.789 250.789l-74.979-154.599 228.03-228.027-153.051 382.626z"/></svg>
                                </a>
                                <a href="javascript:void(0);" class="r-btn pb-0" data-bs-toggle="offcanvas" data-bs-target="#offcanvasBottom2" aria-controls="offcanvasBottom">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </a>
                            </div>
                        </div>

                        <video autoplay loop muted>
                        <source src="assets/images/reels/video5.mp4" type="video/mp4">
                        </video>
                    </div>
                </div>
            </div>
        </div>


        <!-- OffCanvas-1 -->
        <div class="offcanvas offcanvas-bottom reel-canvas" tabindex="-1" id="offcanvasBottom1">
            <button type="button" class="btn-close drage-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            <div class="offcanvas-header share-style m-0 pb-0">
                <form class="w-100">
                    <input type="text" class="form-control border-0" placeholder="Write a message ..."/>
                </form>
            </div>
            <div class="offcanvas-body container pb-0">
                <form>
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search..">
                        <span class="input-group-text">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M23.7871 22.7761L17.9548 16.9437C19.5193 15.145 20.4665 12.7982 20.4665 10.2333C20.4665 4.58714 15.8741 0 10.2333 0C4.58714 0 0 4.59246 0 10.2333C0 15.8741 4.59246 20.4665 10.2333 20.4665C12.7982 20.4665 15.145 19.5193 16.9437 17.9548L22.7761 23.7871C22.9144 23.9255 23.1007 24 23.2816 24C23.4625 24 23.6488 23.9308 23.7871 23.7871C24.0639 23.5104 24.0639 23.0528 23.7871 22.7761ZM1.43149 10.2333C1.43149 5.38004 5.38004 1.43681 10.2279 1.43681C15.0812 1.43681 19.0244 5.38537 19.0244 10.2333C19.0244 15.0812 15.0812 19.035 10.2279 19.035C5.38004 19.035 1.43149 15.0865 1.43149 10.2333Z" fill="#FE9063"></path>
                            </svg>
                        </span>
                    </div>
                </form>
                <div class="canvas-height mt-4 dz-scroll">
                    <ul class="share-list">
                        <li>
                            <div class="left-content">
                                <img src="assets/images/stories/small/pic1.jpg" alt="/">
                                <h6 class="name">Andy Lee</h6>
                                <span class="username">mr_andy_lee</span>
                            </div>
                            <a href="javascript:void(0);" class="btn btn-primary btn-sm">Send</a>
                        </li>
                        <li>
                            <div class="left-content">
                                <img src="assets/images/stories/small/pic2.jpg" alt="/">
                                <h6 class="name">Brian Harahap</h6>
                                <span class="username">brian_harahap</span>
                            </div>
                            <a href="javascript:void(0);" class="btn btn-primary btn-sm">Send</a>
                        </li>
                        <li>
                            <div class="left-content">
                                <img src="assets/images/stories/small/pic3.jpg" alt="/">
                                <h6 class="name">Christian Hang</h6>
                                <span class="username">christian_Hang</span>
                            </div>
                            <a href="javascript:void(0);" class="btn btn-primary btn-sm">Send</a>
                        </li>
                        <li>
                            <div class="left-content">
                                <img src="assets/images/stories/small/pic4.jpg" alt="/">
                                <h6 class="name">Chloe Mc. Jenskin</h6>
                                <span class="username">chloe_mc_jenskin</span>
                            </div>
                            <a href="javascript:void(0);" class="btn btn-primary btn-sm">Send</a>
                        </li>
                        <li>
                            <div class="left-content">
                                <img src="assets/images/stories/small/pic5.jpg" alt="/">
                                <h6 class="name">David Bekam</h6>
                                <span class="username">david_bekam</span>
                            </div>
                            <a href="javascript:void(0);" class="btn btn-primary btn-sm">Send</a>
                        </li>
                        <li>
                            <div class="left-content">
                                <img src="assets/images/stories/small/pic6.jpg" alt="/">
                                <h6 class="name">Donas High</h6>
                                <span class="username">donas_high</span>
                            </div>
                            <a href="javascript:void(0);" class="btn btn-primary btn-sm">Send</a>
                        </li>
                        <li>
                            <div class="left-content">
                                <img src="assets/images/stories/small/pic7.jpg" alt="/">
                                <h6 class="name">Lee Comfort</h6>
                                <span class="username">lee_comfort</span>
                            </div>
                            <a href="javascript:void(0);" class="btn btn-primary btn-sm">Send</a>
                        </li>
                        <li>
                            <div class="left-content">
                                <img src="assets/images/stories/small/pic8.jpg" alt="/">
                                <h6 class="name">Michel Evon</h6>
                                <span class="username">michel_evon</span>
                            </div>
                            <a href="javascript:void(0);" class="btn btn-primary btn-sm">Send</a>
                        </li>
                        <li>
                            <div class="left-content">
                                <img src="assets/images/stories/small/pic4.jpg" alt="/">
                                <h6 class="name">Yatin</h6>
                                <span class="username">yatin_325</span>
                            </div>
                            <a href="javascript:void(0);" class="btn btn-primary btn-sm">Send</a>
                        </li>
                        <li>
                            <div class="left-content">
                                <img src="assets/images/stories/small/pic3.jpg" alt="/">
                                <h6 class="name">Tushar</h6>
                                <span class="username">Tjangid_293</span>
                            </div>
                            <a href="javascript:void(0);" class="btn btn-primary btn-sm">Send</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- OffCanvas-1 -->

    <!-- OffCanvas-2 -->
        <div class="offcanvas offcanvas-bottom reel-canvas" tabindex="-1" id="offcanvasBottom2">
            <button type="button" class="btn-close drage-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            <div class="offcanvas-body">
                <ul class="features-list">
                    <li>
                        <a href="javascript:void(0);">
                            <div class="dz-icon">
                                <i class="fa-regular fa-bookmark"></i>
                            </div>
                            <span>Save</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0);">
                            <div class="dz-icon">
                                <i class="fa-solid fa-sliders"></i>
                            </div>
                            <span>Edit</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0);">
                            <div class="dz-icon">
                                <i class="fa-sharp fa-regular fa-eye-slash"></i>
                            </div>
                            <span>Not Intrested</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    <!-- OffCanvas-2 -->

        <!-- OffCanvas-1 -->
        <div class="offcanvas offcanvas-bottom" tabindex="-1" id="offcanvasBottom1">
            <button type="button" class="btn-close drage-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            <div class="offcanvas-header share-style m-0 pb-0">
                <form class="w-100">
                    <input type="text" class="form-control border-0" placeholder="Write a message ..."/>
                </form>
            </div>
            <div class="offcanvas-body container pb-0">
                <form>
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search..">
                        <span class="input-group-text">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M23.7871 22.7761L17.9548 16.9437C19.5193 15.145 20.4665 12.7982 20.4665 10.2333C20.4665 4.58714 15.8741 0 10.2333 0C4.58714 0 0 4.59246 0 10.2333C0 15.8741 4.59246 20.4665 10.2333 20.4665C12.7982 20.4665 15.145 19.5193 16.9437 17.9548L22.7761 23.7871C22.9144 23.9255 23.1007 24 23.2816 24C23.4625 24 23.6488 23.9308 23.7871 23.7871C24.0639 23.5104 24.0639 23.0528 23.7871 22.7761ZM1.43149 10.2333C1.43149 5.38004 5.38004 1.43681 10.2279 1.43681C15.0812 1.43681 19.0244 5.38537 19.0244 10.2333C19.0244 15.0812 15.0812 19.035 10.2279 19.035C5.38004 19.035 1.43149 15.0865 1.43149 10.2333Z" fill="#FE9063"></path>
                            </svg>
                        </span>
                    </div>
                </form>
                <div class="canvas-height mt-4 dz-scroll">
                    <ul class="share-list">
                        <li>
                            <div class="left-content">
                                <a href="user-profile.html"><img src="assets/images/stories/small/pic1.jpg" alt="/"></a>
                                <a href="user-profile.html">
                                    <h6 class="name">Andy Lee</h6>
                                    <span class="username">mr_andy_lee</span>
                                </a>
                            </div>
                            <a href="javascript:void(0);" class="btn btn-primary btn-sm">Send</a>
                        </li>
                        <li>
                            <div class="left-content">
                                <a href="user-profile.html"><img src="assets/images/stories/small/pic2.jpg" alt="/"></a>
                                <a href="user-profile.html">
                                    <h6 class="name">Brian Harahap</h6>
                                    <span class="username">brian_harahap</span>
                                </a>
                            </div>
                            <a href="javascript:void(0);" class="btn btn-primary btn-sm">Send</a>
                        </li>
                        <li>
                            <div class="left-content">
                                <a href="user-profile.html"><img src="assets/images/stories/small/pic3.jpg" alt="/"></a>
                                <a href="user-profile.html">
                                    <h6 class="name">Christian Hang</h6>
                                    <span class="username">christian_Hang</span>
                                </a>
                            </div>
                            <a href="javascript:void(0);" class="btn btn-primary btn-sm">Send</a>
                        </li>
                        <li>
                            <div class="left-content">
                                <a href="user-profile.html"><img src="assets/images/stories/small/pic4.jpg" alt="/"></a>
                                <a href="user-profile.html">
                                    <h6 class="name">Chloe Mc. Jenskin</h6>
                                    <span class="username">chloe_mc_jenskin</span>
                                </a>
                            </div>
                            <a href="javascript:void(0);" class="btn btn-primary btn-sm">Send</a>
                        </li>
                        <li>
                            <div class="left-content">
                                <a href="user-profile.html"><img src="assets/images/stories/small/pic5.jpg" alt="/"></a>
                                <a href="user-profile.html">
                                    <h6 class="name">David Bekam</h6>
                                    <span class="username">david_bekam</span>
                                </a>
                            </div>
                            <a href="javascript:void(0);" class="btn btn-primary btn-sm">Send</a>
                        </li>
                        <li>
                            <div class="left-content">
                                <a href="user-profile.html"><img src="assets/images/stories/small/pic6.jpg" alt="/"></a>
                                <a href="user-profile.html">
                                    <h6 class="name">Donas High</h6>
                                    <span class="username">donas_high</span>
                                </a>
                            </div>
                            <a href="javascript:void(0);" class="btn btn-primary btn-sm">Send</a>
                        </li>
                        <li>
                            <div class="left-content">
                                <a href="user-profile.html"><img src="assets/images/stories/small/pic7.jpg" alt="/"></a>
                                <a href="user-profile.html">
                                    <h6 class="name">Lee Comfort</h6>
                                    <span class="username">lee_comfort</span>
                                </a>
                            </div>
                            <a href="javascript:void(0);" class="btn btn-primary btn-sm">Send</a>
                        </li>
                        <li>
                            <div class="left-content">
                                <a href="user-profile.html"><img src="assets/images/stories/small/pic8.jpg" alt="/"></a>
                                <a href="user-profile.html">
                                    <h6 class="name">Michel Evon</h6>
                                    <span class="username">michel_evon</span>
                                </a>
                            </div>
                            <a href="javascript:void(0);" class="btn btn-primary btn-sm">Send</a>
                        </li>
                        <li>
                            <div class="left-content">
                                <a href="user-profile.html"><img src="assets/images/stories/small/pic4.jpg" alt="/"></a>
                                <a href="user-profile.html">
                                    <h6 class="name">Yatin</h6>
                                    <span class="username">yatin_325</span>
                                </a>
                            </div>
                            <a href="javascript:void(0);" class="btn btn-primary btn-sm">Send</a>
                        </li>
                        <li>
                            <div class="left-content">
                                <a href="user-profile.html"><img src="assets/images/stories/small/pic3.jpg" alt="/"></a>
                                <a href="user-profile.html">
                                    <h6 class="name">Tushar</h6>
                                    <span class="username">Tjangid_293</span>
                                </a>
                            </div>
                            <a href="javascript:void(0);" class="btn btn-primary btn-sm">Send</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- OffCanvas-1 -->

        <!-- Theme Color Settings -->
        <div class="offcanvas offcanvas-bottom" tabindex="-1" id="offcanvasBottom">
            <div class="offcanvas-body small">
                <ul class="theme-color-settings">
                    <li>
                        <input class="filled-in" id="primary_color_8" name="theme_color" type="radio" value="color-primary" />
                        <label for="primary_color_8"></label>
                        <span>Default</span>
                    </li>
                    <li>
                        <input class="filled-in" id="primary_color_2" name="theme_color" type="radio" value="color-green" />
                        <label for="primary_color_2"></label>
                        <span>Green</span>
                    </li>
                    <li>
                        <input class="filled-in" id="primary_color_3" name="theme_color" type="radio" value="color-blue" />
                        <label for="primary_color_3"></label>
                        <span>Blue</span>
                    </li>
                    <li>
                        <input class="filled-in" id="primary_color_4" name="theme_color" type="radio" value="color-pink" />
                        <label for="primary_color_4"></label>
                        <span>Pink</span>
                    </li>
                    <li>
                        <input class="filled-in" id="primary_color_5" name="theme_color" type="radio" value="color-yellow" />
                        <label for="primary_color_5"></label>
                        <span>Yellow</span>
                    </li>
                    <li>
                        <input class="filled-in" id="primary_color_6" name="theme_color" type="radio" value="color-orange" />
                        <label for="primary_color_6"></label>
                        <span>Orange</span>
                    </li>
                    <li>
                        <input class="filled-in" id="primary_color_7" name="theme_color" type="radio" value="color-purple" />
                        <label for="primary_color_7"></label>
                        <span>Purple</span>
                    </li>
                    <li>
                        <input class="filled-in" id="primary_color_1" name="theme_color" type="radio" value="color-red" />
                        <label for="primary_color_1"></label>
                        <span>Red</span>
                    </li>
                    <li>
                        <input class="filled-in" id="primary_color_9" name="theme_color" type="radio" value="color-lightblue" />
                        <label for="primary_color_9"></label>
                        <span>Lightblue</span>
                    </li>
                    <li>
                        <input class="filled-in" id="primary_color_10" name="theme_color" type="radio" value="color-teal" />
                        <label for="primary_color_10"></label>
                        <span>Teal</span>
                    </li>
                    <li>
                        <input class="filled-in" id="primary_color_11" name="theme_color" type="radio" value="color-lime" />
                        <label for="primary_color_11"></label>
                        <span>Lime</span>
                    </li>
                    <li>
                        <input class="filled-in" id="primary_color_12" name="theme_color" type="radio" value="color-deeporange" />
                        <label for="primary_color_12"></label>
                        <span>Deeporange</span>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Theme Color Settings End -->

        <!-- PWA Offcanvas -->
        {{-- <div class="offcanvas offcanvas-bottom pwa-offcanvas">
            <div class="container">
                <div class="offcanvas-body small">
                    <img class="logo" src="assets/images/icon.png" alt="">
                    <h5 class="title">Soziety on Your Home Screen</h5>
                    <p class="pwa-text">Install Soziety social network mobile app template to your home screen for easy access, just like any other app</p>
                    <button type="button" class="btn btn-sm btn-primary pwa-btn">Add to Home Screen</button>
                    <button type="button" class="btn btn-sm pwa-close light btn-secondary ms-2">Maybe later</button>
                </div>
            </div>
        </div> --}}
        {{-- <div class="offcanvas-backdrop pwa-backdrop"></div>
        </div> --}}
    </div>
    <!-- Menubar -->
    {{-- <div class="menubar-area">
        <div class="toolbar-inner menubar-nav">
            <a href="index.html" class="nav-link active">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" xmlns:v="https://vecta.io/nano"><path d="M21.44 11.035a.75.75 0 0 1-.69.465H18.5V19a2.25 2.25 0 0 1-2.25 2.25h-3a.75.75 0 0 1-.75-.75V16a.75.75 0 0 0-.75-.75h-1.5a.75.75 0 0 0-.75.75v4.5a.75.75 0 0 1-.75.75h-3A2.25 2.25 0 0 1 3.5 19v-7.5H1.25a.75.75 0 0 1-.69-.465.75.75 0 0 1 .158-.818l9.75-9.75A.75.75 0 0 1 11 .246a.75.75 0 0 1 .533.222l9.75 9.75a.75.75 0 0 1 .158.818z" fill="#b5b5b5"/></svg>
            </a>
            <a href="timeline.html" class="nav-link">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M11 19C15.4183 19 19 15.4183 19 11C19 6.58172 15.4183 3 11 3C6.58172 3 3 6.58172 3 11C3 15.4183 6.58172 19 11 19Z" stroke="#b5b5b5" stroke-opacity="1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M21 21L16.65 16.65" stroke="#b5b5b5" stroke-opacity="1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
            <a href="create-post.html" class="nav-link add-post">
                <i class="fa-solid fa-plus"></i>
            </a>
            <a href="chat.html" class="nav-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#b5b5b5" viewBox="0 0 511.606 511.606" xmlns:v="https://vecta.io/nano"><path d="M436.594 74.943c-99.917-99.917-261.637-99.932-361.568 0-80.348 80.347-95.531 199.817-48.029 294.96L.662 485.742c-3.423 15.056 10.071 28.556 25.133 25.133l115.839-26.335c168.429 84.092 369.846-37.653 369.846-228.812 0-68.29-26.595-132.494-74.886-180.785zM309.143 319.394h-160c-11.598 0-21-9.402-21-21s9.402-21 21-21h160c11.598 0 21 9.402 21 21s-9.402 21-21 21zm53.334-85.333H149.143c-11.598 0-21-9.402-21-21s9.402-21 21-21h213.334c11.598 0 21 9.402 21 21s-9.403 21-21 21z"/></svg>
            </a>
            <a href="profile.html" class="nav-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="21" fill="#b5b5b5" xmlns:v="https://vecta.io/nano"><path d="M8 7.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 1 0 0 7.5zm7.5 9v1.5c-.002.199-.079.39-.217.532C13.61 20.455 8.57 20.5 8 20.5s-5.61-.045-7.282-1.718C.579 18.64.501 18.449.5 18.25v-1.5a7.5 7.5 0 1 1 15 0z"/></svg>
            </a>
        </div>
    </div> --}}
    <!-- Menubar -->
@endsection


@section('scripts')
@endsection
