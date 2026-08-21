<!-- app-Header -->
<div class="app-header header sticky">
  <div class="container-fluid main-container">
    <div class="d-flex">
      <a aria-label="Hide Sidebar" class="app-sidebar__toggle" data-bs-toggle="sidebar" href="javascript:void(0)"></a>
      <!-- sidebar-toggle-->
      <a class="logo-horizontal " href="/">
        <img src="{{ asset('assets/img/logo.png') }}" class="header-brand-img desktop-logo"
          style="width:100px;height:30px" alt="logo USJ">
        <img src="{{ asset('assets/img/logo.png') }}" class="header-brand-img light-logo1" style="width:90px"
          alt="logo USK">
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
        <button class="navbar-toggler navresponsive-toggler d-lg-none ms-auto" type="button" data-bs-toggle="collapse"
          data-bs-target="#navbarSupportedContent-4" aria-controls="navbarSupportedContent-4" aria-expanded="false"
          aria-label="Toggle navigation">
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
