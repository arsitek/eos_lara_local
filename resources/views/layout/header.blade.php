@php
    $allowedRoles = ["Pimpinan Unit", "Pimpinan Univ", "Verifikator Aset", "Verifikator Keuangan", "Verifikator RKAT", "Pimpinan USK"];
    $countVerifOperasional = $countVerifOperasional ?? 0;
    $idunit = session('unitkerja');
@endphp
<!-- app-Header -->
 <div class="app-header header sticky">
     <div class="container-fluid main-container">
         <div class="d-flex">
             <a aria-label="Hide Sidebar" class="app-sidebar__toggle" data-bs-toggle="sidebar"
                 href="javascript:void(0)"></a>
             <!-- sidebar-toggle-->
             <a class="logo-horizontal " href="/">
                <img src="{{ asset('assets/img/logo.png') }}" class="header-brand-img desktop-logo"
                    style="width:100px;height:30px" alt="logo USJ">
                 <img src="{{ asset('assets/img/logo.png') }}" class="header-brand-img light-logo1"
                    style="width:90px" alt="logo USK">
             </a>
             <!-- LOGO -->
             <div class="main-header-center ms-3 d-none d-lg-block">
                 {{-- <input class="form-control" placeholder="Search for results..." type="search">
                 <button class="btn px-0 pt-2"><i class="fe fe-search" aria-hidden="true"></i></button> --}}
             </div>
             <div class="d-flex order-lg-2 ms-auto header-right-icons">
                 <div class="dropdown d-none">
                     {{-- <a href="javascript:void(0)" class="nav-link icon" data-bs-toggle="dropdown">
                         <i class="fe fe-search"></i>
                     </a> --}}
                     <div class="dropdown-menu header-search dropdown-menu-start">
                         <div class="input-group w-100 p-2">
                             <input type="text" class="form-control" placeholder="Search....">
                             <div class="input-group-text btn btn-primary">
                                 <i class="fe fe-search" aria-hidden="true"></i>
                             </div>
                         </div>
                     </div>
                 </div>
                 <!-- SEARCH -->
                 <button class="navbar-toggler navresponsive-toggler d-lg-none ms-auto" type="button"
                     data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent-4"
                     aria-controls="navbarSupportedContent-4" aria-expanded="false" aria-label="Toggle navigation">
                     <span class="navbar-toggler-icon fe fe-more-vertical"></span>
                 </button>
                 <div class="navbar navbar-collapse responsive-navbar p-0">
                     <div class="collapse navbar-collapse" id="navbarSupportedContent-4">
                         <div class="d-flex order-lg-2">
                             <div class="dropdown d-lg-none d-flex">
                                 {{-- <a href="javascript:void(0)" class="nav-link icon" data-bs-toggle="dropdown">
                                     <i class="fe fe-search"></i>
                                 </a> --}}
                                 <div class="dropdown-menu header-search dropdown-menu-start">
                                     <div class="input-group w-100 p-2">
                                         {{-- <input type="text" class="form-control" placeholder="Search....">
                                         <div class="input-group-text btn btn-primary">
                                             <i class="fa fa-search" aria-hidden="true"></i> --}}
                                         {{-- </div> --}}
                                     </div>
                                 </div>
                             </div>
                             <!-- SEARCH -->
                             <div class="dropdown  d-flex">
                                 <a class="nav-link icon theme-layout nav-link-bg layout-setting">
                                     <span class="dark-layout"><i class="fe fe-moon"></i></span>
                                     <span class="light-layout"><i class="fe fe-sun"></i></span>
                                 </a>
                             </div>
                             <!-- Theme-Layout -->
                             <div class="dropdown d-flex">
                                 <a class="nav-link icon full-screen-link nav-link-bg">
                                     <i class="fe fe-minimize fullscreen-button"></i>
                                 </a>
                             </div>
                             <!-- FULL-SCREEN -->
                            @php
                                $verificationRoles = [
                                    "superadmin", "admin", "Pimpinan Unit", "Pengawasan Internal", "Auditor",
                                    "Pimpinan USK", "Verifikator Aset", "Verifikator Keuangan", "Verifikator RKAT"
                                ];
                            @endphp
                            @if(in_array(session('role'), $verificationRoles))
                            <div class="dropdown d-flex notifications">
                                <a class="nav-link icon" data-bs-toggle="dropdown" id="notificationBell">
                                    <i class="fe fe-bell"></i>
                                    <span class="pulse" id="notificationPulse" style="display: none;"></span>
                                    <span class="badge bg-danger rounded-pill notification-badge" id="notificationCount" style="display: none; position: absolute; top: 8px; right: 8px; font-size: 10px; padding: 2px 5px;"></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow" style="max-height: 500px; overflow-y: auto; min-width: 380px;">
                                    <div class="drop-heading border-bottom">
                                        <div class="d-flex flex-column">
                                            <h6 class="mb-1 fs-16 fw-semibold text-dark">Notifikasi</h6>
                                            {{-- <span class="badge bg-info-transparent text-info fs-11 d-inline-flex align-items-center mb-1">
                                                <i class="fe fe-info me-1"></i>
                                                Data otomatis diperbarui setiap 5 menit
                                            </span> --}}
                                        </div>
                                    </div>
                                    <div class="notifications-menu" id="notificationList">
                                        <div class="text-center p-3">
                                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <p class="mb-0 mt-2">Memuat notifikasi...</p>
                                        </div>
                                    </div>
                                    <div class="dropdown-divider m-0"></div>
                                </div>
                            </div>
                            @endif
                            <!-- NOTIFICATIONS -->
                            {{-- <div class="dropdown  d-flex message">
                                 <a class="nav-link icon text-center" data-bs-toggle="dropdown">
                                     <i class="fe fe-message-square"></i><span class="pulse-danger"></span>
                                 </a>
                                 <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                     <div class="drop-heading border-bottom">
                                         <div class="d-flex">
                                             <h6 class="mt-1 mb-0 fs-16 fw-semibold text-dark">You have 5 Messages</h6>
                                             <div class="ms-auto">
                                                 <a href="javascript:void(0)" class="text-muted p-0 fs-12">make all
                                                     unread</a>
                                             </div>
                                         </div>
                                     </div>
                                     <div class="message-menu">
                                         <a class="dropdown-item d-flex" href="chat.html">
                                             <span class="avatar avatar-md brround me-3 align-self-center cover-image"
                                                 data-bs-image-src="{{ asset('assets/images/users/1.html') }}"></span>
                             <div class="wd-90p">
                                 <div class="d-flex">
                                     <h5 class="mb-1">Peter Theil</h5>
                                     <small class="text-muted ms-auto text-end">
                                         6:45 am
                                     </small>
                                 </div>
                                 <span>Commented on file Guest list....</span>
                             </div>
                             </a>
                             <a class="dropdown-item d-flex" href="chat.html">
                                 <span class="avatar avatar-md brround me-3 align-self-center cover-image"
                                     data-bs-image-src="{{ asset('assets/images/users/15.html')}}"></span>
                                 <div class="wd-90p">
                                     <div class="d-flex">
                                         <h5 class="mb-1">Abagael Luth</h5>
                                         <small class="text-muted ms-auto text-end">
                                             10:35 am
                                         </small>
                                     </div>
                                     <span>New Meetup Started......</span>
                                 </div>
                             </a>
                             <a class="dropdown-item d-flex" href="chat.html">
                                 <span class="avatar avatar-md brround me-3 align-self-center cover-image"
                                     data-bs-image-src="{{ asset('assets/images/users/12.jpg')}}"></span>
                                 <div class="wd-90p">
                                     <div class="d-flex">
                                         <h5 class="mb-1">Brizid Dawson</h5>
                                         <small class="text-muted ms-auto text-end">
                                             2:17 pm
                                         </small>
                                     </div>
                                     <span>Brizid is in the Warehouse...</span>
                                 </div>
                             </a>
                             <a class="dropdown-item d-flex" href="chat.html">
                                 <span class="avatar avatar-md brround me-3 align-self-center cover-image"
                                     data-bs-image-src="{{ asset('assets/images/users/4.jpg')}}"></span>
                                 <div class="wd-90p">
                                     <div class="d-flex">
                                         <h5 class="mb-1">Shannon Shaw</h5>
                                         <small class="text-muted ms-auto text-end">
                                             7:55 pm
                                         </small>
                                     </div>
                                     <span>New Product Realease......</span>
                                 </div>
                             </a>

                         </div>
                         <div class="dropdown-divider m-0"></div>
                         <a href="javascript:void(0)" class="dropdown-item text-center p-3 text-muted">See
                             all Messages</a>
                     </div>
                 </div> --}}
                 <!-- MESSAGE-BOX -->

                 <!-- SIDE-MENU -->
                 <div class="dropdown d-flex profile-1">
                     <a href="javascript:void(0)" data-bs-toggle="dropdown" class="nav-link leading-none d-flex">
                         <img src="{{ asset('assets/images/users/user.png')}}" alt="profile-user"
                             class="avatar  profile-user brround cover-image">
                     </a>
                     <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                         <div class="drop-heading">
                             <div class="text-center">
                                <h5 class="text-dark mb-0 fs-14 fw-semibold">{{ session('name') }}</h5>
                                 <div class="">
                                     <small class="text-primary">{{ session('tahun') }} |
                                         {{session('role')}}</small>
                                 </div>
                                 <small class="text-muted">{{ session('unitkerja_nama') }}</small>
                             </div>
                         </div>
                         <div class="dropdown-divider m-0"></div>
                         <a class="dropdown-item" href="{{ route('choose_role') }}">
                             <i class="dropdown-icon fe fe-user"></i> Pilih Akses
                         </a>
                         <a class="dropdown-item" href="{{route('password.reset')}}">
                             <i class="dropdown-icon fa fa-key"></i> Ubah Pasword
                         </a>
                         <a class="dropdown-item" href="{{ route('logout') }}">
                             <i class="dropdown-icon fe fe-alert-circle"></i> Keluar
                         </a>
                     </div>
                 </div>
             </div>
         </div>
     </div>
     <div class="demo-icon nav-link icon">
         <i class="fe fe-settings fa-spin  text_primary"></i>
     </div>
 </div>
 </div>
 </div>
 </div>
 <!-- /app-Header -->
