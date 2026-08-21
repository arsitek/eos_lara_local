<!doctype html>
<html lang="en" dir="ltr">

<head>
  <!-- META DATA -->
  <meta charset="UTF-8">
  <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="Sistem informasi rencana kegiatan dan anggaran terpadu">
  <meta name="keywords"
    content="sirekat, sirekat usk,Sistem informasi rencana kegiatan,dan anggaran terpadu,Sistem informasi rencana,kegiatan,dan anggaran,terpadu">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <!-- title -->
  <title>@yield('title')</title>
  <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/unsyiah.ico') }}" />
  <link id="style" href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/dark-style.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/transparent-style.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/skin-modes.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/icons/icons.css') }}" rel="stylesheet" />
  <link id="theme" rel="stylesheet" type="text/css }}" media="all" href="{{ asset('assets/css/color1.css') }}" />
  <link href="{{ asset('assets/switcher/css/switcher.css') }}" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet" />
  <link href=" https://cdn.jsdelivr.net/npm/chosenjs@1.4.3/chosen.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('assets/css/sweetalert2.min.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/css/selectize.default.min.css"
    integrity="sha512-pTaEn+6gF1IeWv3W1+7X7eM60TFu/agjgoHmYhAfLEU8Phuf6JKiiE8YmsNC0aCgQv4192s4Vai8YZ6VNM6vyQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  @stack('yss')
  @include('layout.style')
</head>

<body class="app sidebar-mini ltr">
  <!-- Switcher -->
  <div class="switcher-wrapper m-2">
    <div class="demo_changer">
      <div class="form_holder sidebar-right1">
        <div class="row">
          <div class="predefined_styles">
            <div class="swichermainleft">
              <h4>Navigation Style</h4>
              <div class="skin-body px-3">
                <div class="switch_section">
                  <div class="switch-toggle d-flex">
                    <span class="me-auto">Vertical Menu</span>
                    <p class="onoffswitch2"><input type="radio" name="onoffswitch15" id="myonoffswitch34"
                        class="onoffswitch2-checkbox" checked>
                      <label for="myonoffswitch34" class="onoffswitch2-label"></label>
                    </p>
                  </div>
                  <div class="switch-toggle d-flex mt-2">
                    <span class="me-auto">Horizontal Click Menu</span>
                    <p class="onoffswitch2"><input type="radio" name="onoffswitch15" id="myonoffswitch35"
                        class="onoffswitch2-checkbox">
                      <label for="myonoffswitch35" class="onoffswitch2-label"></label>
                    </p>
                  </div>
                  <div class="switch-toggle d-flex mt-2">
                    <span class="me-auto">Horizontal Hover Menu</span>
                    <p class="onoffswitch2"><input type="radio" name="onoffswitch15" id="myonoffswitch111"
                        class="onoffswitch2-checkbox">
                      <label for="myonoffswitch111" class="onoffswitch2-label"></label>
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div class="swichermainleft">
              <h4>LTR and RTL VERSIONS</h4>
              <div class="skin-body px-3">
                <div class="switch_section">
                  <div class="switch-toggle d-flex">
                    <span class="me-auto">LTR Version</span>
                    <p class="onoffswitch2"><input type="radio" name="onoffswitch7" id="myonoffswitch23"
                        class="onoffswitch2-checkbox" checked>
                      <label for="myonoffswitch23" class="onoffswitch2-label"></label>
                    </p>
                  </div>
                  <div class="switch-toggle d-flex mt-2">
                    <span class="me-auto">RTL Version</span>
                    <p class="onoffswitch2"><input type="radio" name="onoffswitch7" id="myonoffswitch24"
                        class="onoffswitch2-checkbox">
                      <label for="myonoffswitch24" class="onoffswitch2-label"></label>
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div class="swichermainleft">
              <h4>Light Theme Style</h4>
              <div class="skin-body px-3">
                <div class="switch_section">
                  <div class="switch-toggle d-flex">
                    <span class="me-auto">Light Theme</span>
                    <p class="onoffswitch2"><input type="radio" name="onoffswitch1" id="myonoffswitch1"
                        class="onoffswitch2-checkbox" checked>
                      <label for="myonoffswitch1" class="onoffswitch2-label"></label>
                    </p>
                  </div>
                  <div class="switch-toggle d-flex">
                    <span class="me-auto">Light Primary</span>
                    <div class="">
                      <input class="w-30p h-30 input-color-picker color-primary-light" value="#6c5ffc" id="colorID"
                        type="color" data-id="bg-color" data-id1="bg-hover" data-id2="bg-border"
                        data-id7="transparentcolor" name="lightPrimary">
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="swichermainleft">
              <h4>Dark Theme Style</h4>
              <div class="skin-body px-3">
                <div class="switch_section">
                  <div class="switch-toggle d-flex mt-2">
                    <span class="me-auto">Dark Theme</span>
                    <p class="onoffswitch2"><input type="radio" name="onoffswitch1" id="myonoffswitch2"
                        class="onoffswitch2-checkbox">
                      <label for="myonoffswitch2" class="onoffswitch2-label"></label>
                    </p>
                  </div>
                  <div class="switch-toggle d-flex mt-2">
                    <span class="me-auto">Dark Primary</span>
                    <div class="">
                      <input class="w-30p h-30 input-dark-color-picker color-primary-dark" value="#6c5ffc"
                        id="darkPrimaryColorID" type="color" data-id="bg-color" data-id1="bg-hover"
                        data-id2="bg-border" data-id3="primary" data-id8="transparentcolor" name="darkPrimary">
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="swichermainleft">
              <h4>Transparent Theme Style</h4>
              <div class="skin-body px-3">
                <div class="switch_section">
                  <div class="switch-toggle d-flex mt-2">
                    <span class="me-auto">Transparent Theme</span>
                    <p class="onoffswitch2"><input type="radio" name="onoffswitch1" id="myonoffswitchTransparent"
                        class="onoffswitch2-checkbox">
                      <label for="myonoffswitchTransparent" class="onoffswitch2-label"></label>
                    </p>
                  </div>
                  <div class="switch-toggle d-flex mt-2">
                    <span class="me-auto">Transparent Primary</span>
                    <div class="">
                      <input class="w-30p h-30 input-transparent-color-picker color-primary-transparent"
                        value="#6c5ffc" id="transparentPrimaryColorID" type="color" data-id="bg-color"
                        data-id1="bg-hover" data-id2="bg-border" data-id3="primary" data-id4="primary"
                        data-id9="transparentcolor" name="tranparentPrimary">
                    </div>
                  </div>
                  <div class="switch-toggle d-flex mt-2">
                    <span class="me-auto">Transparent Background</span>
                    <div class="">
                      <input class="w-30p h-30 input-transparent-color-picker color-bg-transparent" value="#6c5ffc"
                        id="transparentBgColorID" type="color" data-id5="body" data-id6="theme"
                        data-id9="transparentcolor" name="transparentBackground">
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="swichermainleft">
              <h4>Transparent Bg-Image Style</h4>
              <div class="skin-body switch_section px-3">
                <div class="switch-toggle d-flex">
                  <span class="me-auto">Bg-Image Primary</span>
                  <div class="">
                    <input class="w-30p h-30 input-transparent-color-picker color-primary-transparent" value="#7e44d5"
                      id="transparentBgImgPrimaryColorID" type="color" data-id="bg-color" data-id1="bg-hover"
                      data-id2="bg-border" data-id3="primary" data-id4="primary" data-id9="transparentcolor"
                      name="tranparentPrimary">
                  </div>
                </div>
                <div class="switch-toggle d-flex mt-2">
                  <a class="bg-img1 bg-img" href="javascript:void(0);"><img
                      src="{{ asset('assets/images/media/bg-img1.html') }}" alt="Bg-Image" id="bgimage1"></a>
                  <a class="bg-img2 bg-img" href="javascript:void(0);"><img
                      src="{{ asset('assets/images/media/bg-img2.html') }}" alt="Bg-Image" id="bgimage2"></a>
                  <a class="bg-img3 bg-img" href="javascript:void(0);"><img
                      src="{{ asset('assets/images/media/bg-img3.html') }}" alt="Bg-Image" id="bgimage3"></a>
                  <a class="bg-img4 bg-img" href="javascript:void(0);"><img
                      src="{{ asset('assets/images/media/bg-img4.html') }}" alt="Bg-Image" id="bgimage4"></a>
                </div>
              </div>
            </div>
            <div class="swichermainleft">
              <h4>Leftmenu Styles</h4>
              <div class="skin-body px-3">
                <div class="switch_section">
                  <div class="switch-toggle lightMenu d-flex">
                    <span class="me-auto">Light Menu</span>
                    <p class="onoffswitch2"><input type="radio" name="onoffswitch2" id="myonoffswitch3"
                        class="onoffswitch2-checkbox" checked>
                      <label for="myonoffswitch3" class="onoffswitch2-label"></label>
                    </p>
                  </div>
                  <div class="switch-toggle colorMenu d-flex mt-2">
                    <span class="me-auto">Color Menu</span>
                    <p class="onoffswitch2"><input type="radio" name="onoffswitch2" id="myonoffswitch4"
                        class="onoffswitch2-checkbox">
                      <label for="myonoffswitch4" class="onoffswitch2-label"></label>
                    </p>
                  </div>
                  <div class="switch-toggle darkMenu d-flex mt-2">
                    <span class="me-auto">Dark Menu</span>
                    <p class="onoffswitch2"><input type="radio" name="onoffswitch2" id="myonoffswitch5"
                        class="onoffswitch2-checkbox">
                      <label for="myonoffswitch5" class="onoffswitch2-label"></label>
                    </p>
                  </div>
                  <div class="switch-toggle gradientMenu d-flex mt-2">
                    <span class="me-auto">Gradient Menu</span>
                    <p class="onoffswitch2"><input type="radio" name="onoffswitch2" id="myonoffswitch19"
                        class="onoffswitch2-checkbox">
                      <label for="myonoffswitch19" class="onoffswitch2-label"></label>
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div class="swichermainleft">
              <h4>Header Styles</h4>
              <div class="skin-body px-3">
                <div class="switch_section">
                  <div class="switch-toggle lightHeader d-flex">
                    <span class="me-auto">Light Header</span>
                    <p class="onoffswitch2"><input type="radio" name="onoffswitch3" id="myonoffswitch6"
                        class="onoffswitch2-checkbox" checked>
                      <label for="myonoffswitch6" class="onoffswitch2-label"></label>
                    </p>
                  </div>
                  <div class="switch-toggle  colorHeader d-flex mt-2">
                    <span class="me-auto">Color Header</span>
                    <p class="onoffswitch2"><input type="radio" name="onoffswitch3" id="myonoffswitch7"
                        class="onoffswitch2-checkbox">
                      <label for="myonoffswitch7" class="onoffswitch2-label"></label>
                    </p>
                  </div>
                  <div class="switch-toggle darkHeader d-flex mt-2">
                    <span class="me-auto">Dark Header</span>
                    <p class="onoffswitch2"><input type="radio" name="onoffswitch3" id="myonoffswitch8"
                        class="onoffswitch2-checkbox">
                      <label for="myonoffswitch8" class="onoffswitch2-label"></label>
                    </p>
                  </div>

                  <div class="switch-toggle darkHeader d-flex mt-2">
                    <span class="me-auto">Gradient Header</span>
                    <p class="onoffswitch2"><input type="radio" name="onoffswitch3" id="myonoffswitch20"
                        class="onoffswitch2-checkbox">
                      <label for="myonoffswitch20" class="onoffswitch2-label"></label>
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div class="swichermainleft">
              <h4>Layout Width Styles</h4>
              <div class="skin-body px-3">
                <div class="switch_section">
                  <div class="switch-toggle d-flex">
                    <span class="me-auto">Full Width</span>
                    <p class="onoffswitch2"><input type="radio" name="onoffswitch4" id="myonoffswitch9"
                        class="onoffswitch2-checkbox" checked>
                      <label for="myonoffswitch9" class="onoffswitch2-label"></label>
                    </p>
                  </div>
                  <div class="switch-toggle d-flex mt-2">
                    <span class="me-auto">Boxed</span>
                    <p class="onoffswitch2"><input type="radio" name="onoffswitch4" id="myonoffswitch10"
                        class="onoffswitch2-checkbox">
                      <label for="myonoffswitch10" class="onoffswitch2-label"></label>
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div class="swichermainleft">
              <h4>Layout Positions</h4>
              <div class="skin-body px-3">
                <div class="switch_section">
                  <div class="switch-toggle d-flex">
                    <span class="me-auto">Fixed</span>
                    <p class="onoffswitch2"><input type="radio" name="onoffswitch5" id="myonoffswitch11"
                        class="onoffswitch2-checkbox" checked>
                      <label for="myonoffswitch11" class="onoffswitch2-label"></label>
                    </p>
                  </div>
                  <div class="switch-toggle d-flex mt-2">
                    <span class="me-auto">Scrollable</span>
                    <p class="onoffswitch2"><input type="radio" name="onoffswitch5" id="myonoffswitch12"
                        class="onoffswitch2-checkbox">
                      <label for="myonoffswitch12" class="onoffswitch2-label"></label>
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div class="swichermainleft leftmenu-styles">
              <h4>Sidemenu layout Styles</h4>
              <div class="skin-body px-3">
                <div class="switch_section">
                  <div class="switch-toggle d-flex">
                    <span class="me-auto">Default Menu</span>
                    <p class="onoffswitch2"><input type="radio" name="onoffswitch6" id="myonoffswitch13"
                        class="onoffswitch2-checkbox default-menu" checked>
                      <label for="myonoffswitch13" class="onoffswitch2-label"></label>
                    </p>
                  </div>
                  <div class="switch-toggle d-flex mt-2">
                    <span class="me-auto">Icon with Text</span>
                    <p class="onoffswitch2"><input type="radio" name="onoffswitch6" id="myonoffswitch14"
                        class="onoffswitch2-checkbox">
                      <label for="myonoffswitch14" class="onoffswitch2-label"></label>
                    </p>
                  </div>
                  <div class="switch-toggle d-flex mt-2">
                    <span class="me-auto">Icon Overlay</span>
                    <p class="onoffswitch2"><input type="radio" name="onoffswitch6" id="myonoffswitch15"
                        class="onoffswitch2-checkbox">
                      <label for="myonoffswitch15" class="onoffswitch2-label"></label>
                    </p>
                  </div>
                  <div class="switch-toggle d-flex mt-2">
                    <span class="me-auto">Closed Sidemenu</span>
                    <p class="onoffswitch2"><input type="radio" name="onoffswitch6" id="myonoffswitch16"
                        class="onoffswitch2-checkbox">
                      <label for="myonoffswitch16" class="onoffswitch2-label"></label>
                    </p>
                  </div>
                  <div class="switch-toggle d-flex mt-2">
                    <span class="me-auto">Hover Submenu</span>
                    <p class="onoffswitch2"><input type="radio" name="onoffswitch6" id="myonoffswitch17"
                        class="onoffswitch2-checkbox">
                      <label for="myonoffswitch17" class="onoffswitch2-label"></label>
                    </p>
                  </div>
                  <div class="switch-toggle d-flex mt-2">
                    <span class="me-auto">Hover Submenu Style 1</span>
                    <p class="onoffswitch2"><input type="radio" name="onoffswitch6" id="myonoffswitch18"
                        class="onoffswitch2-checkbox">
                      <label for="myonoffswitch18" class="onoffswitch2-label"></label>
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div class="swichermainleft">
              <h4>Reset All Styles</h4>
              <div class="skin-body px-3">
                <div class="switch_section my-4">
                  <button class="btn btn-danger btn-block resetCustomStyles" type="button">Reset All
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- End Switcher -->
  <div class="page">
    <div class="page-main">
      @include('layout.header')
      @include('layout.sidebar')
      <!--app-content open-->
      <div class="main-content app-content mt-0">
        <div class="side-app">
          <!-- container -->
          <div class="main-container container-fluid">
            @if (session()->get('Pesan'))
              <div class="alert alert-danger alert-dismissible mt-2">
                {{ session()->get('Pesan') }}
              </div>
            @endif
            @yield('content')
          </div>
        </div>
      </div>
    </div>
    @include('layout.rightSide')
    @include('layout.footer')
  </div>
  </div>
  <script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/bootstrap/js/popper.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/input-mask/jquery.mask.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/sidemenu/sidemenu.js') }}"></script>
  <script src="{{ asset('assets/plugins/sidebar/sidebar.js') }}"></script>
  <script src="{{ asset('assets/plugins/p-scroll/perfect-scrollbar.js') }}"></script>
  <script src="{{ asset('assets/plugins/p-scroll/pscroll.js') }}"></script>
  <script src="{{ asset('assets/plugins/jquery-sparkline/jquery.sparkline.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/circle-progress/circle-progress.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/select2/select2.full.min.js') }}"></script>
  <script src="{{ asset('assets/js/select2.js') }}"></script>
  <script src="{{ asset('assets/plugins/select2/select2.full.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.js') }}"></script>
  <script src="{{ asset('assets/plugins/datatable/dataTables.responsive.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/flot/jquery.flot.js') }}"></script>
  <script src="{{ asset('assets/plugins/flot/jquery.flot.fillbetween.js') }}"></script>
  <script src="{{ asset('assets/js/themeColors.js') }}"></script>
  <script src="{{ asset('assets/js/sticky.js') }}"></script>
  <script src="{{ asset('assets/switcher/js/switcher.js') }}"></script>
  <script src="{{ asset('assets/plugins/select2/select2.full.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/edit-table/bst-edittable.js') }}"></script>
  <script src="{{ asset('assets/plugins/edit-table/edit-table.js') }}"></script>

  <!-- DATA TABLE JS-->
  <script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.js') }}"></script>
  <script src="{{ asset('assets/plugins/datatable/js/dataTables.buttons.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/datatable/js/buttons.bootstrap5.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/datatable/js/jszip.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/datatable/pdfmake/pdfmake.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/datatable/pdfmake/vfs_fonts.js') }}"></script>
  <script src="{{ asset('assets/plugins/datatable/js/buttons.html5.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/datatable/js/buttons.print.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/datatable/js/buttons.colVis.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/datatable/dataTables.responsive.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/datatable/responsive.bootstrap5.min.js') }}"></script>
  <script src="{{ asset('assets/js/table-data.js') }}"></script>
  <script src="{{ asset('assets/js/sweetalert2.min.js') }}"></script>
  <script src="{{ asset('assets/js/tata-master/dist/tata.js') }}"></script>
  <script src="{{ asset('assets/js/rowGroup.js') }}"></script>
  <script src="{{ asset('assets/js/jquery.rowspanizer.min.js') }}"></script>
  <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.dataTables.js"></script>
  <!-- Script -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
  <script src="https://cdn.rawgit.com/rainabba/jquery-table2excel/1.1.0/dist/jquery.table2excel.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.3.2/html2canvas.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"
    integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/js/selectize.min.js"
    integrity="sha512-IOebNkvA/HZjMM7MxL0NYeLYEalloZ8ckak+NDtOViP7oiYzG5vn6WVXyrJDiJPhl4yRdmNAG49iuLmhkUdVsQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/floatthead/2.2.5/jquery.floatThead.min.js"></script>
  <script src="https://unpkg.com/xlsx@0.15.1/dist/xlsx.full.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.6/jspdf.plugin.autotable.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.2.1/purify.min.js"
    integrity="sha512-PBUtfPzExa/FxBEi6tr884CPkb9Wh0kjchdWPECubdH16+G0JjkGQHgWCO7zgINZlXtmVnpII7KnZctAPUAZWg=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://code.jquery.com/ui/1.14.0/jquery-ui.min.js"
    integrity="sha256-Fb0zP4jE3JHqu+IBB9YktLcSjI1Zc6J2b6gTjB0LpoM=" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js"></script>
  @include('layout.script')
  <script>
    setTimeout(function() {
      // console.clear()
      var cssRule =
        "color: rgb(255, 213, 111);" +
        "font-size: 60px;" +
        "font-weight: bold;" +
        "text-shadow: 2px 1px 5px rgb(235, 199, 230);" +
        "filter: dropshadow(color=rgb(235, 199, 230), offx=1, offy=1);";
      var cssRule2 =
        "color: rgb(145, 216, 228);" +
        "font-size: 20px;" +
        "padding-right: 5px;" +
        "text-shadow: 2px 1px 5px rgb(235, 199, 230);"
      var cssRule3 =
        "color: #fff;" +
        "font-size: 12px;" +
        "padding-right: 5px;" +
        "text-shadow: 0 0 5px #fff, 0 0 10px #fff, 0 0 15px #0073e6, 0 0 20px #0073e6, 0 0 25px #0073e6;" +
        "font-family: 'Arial', sans-serif;" +
        "letter-spacing: 2px;"
      setTimeout(console.log.bind(console, '%cSIREKAT\n2026', cssRule), 0);
      setTimeout(console.log.bind(console, '%c@SIREKAT TEAM', cssRule2), 0);
      setTimeout(console.log.bind(console,
        '%cPeringatan: Konsol browser ini adalah alat khusus untuk pengembang (developer). Harap berhati-hati jika seseorang meminta Anda untuk menyalin dan menempelkan kode di sini dengan alasan untuk mengaktifkan fitur tertentu. Tindakan tersebut merupakan upaya penipuan yang dapat memberikan akses kepada pihak tidak bertanggung jawab ke akun Anda.',
        cssRule3), 0);
    }, 3000) //4 detik

    let rupiah = (number) => {
      const formattedValue = new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
      }).format(number)
      // Replace dots with commas
      return formattedValue.replace(/\./g, ',');
    }
    let rupiahToNumber = (rupiahString) => {
      if (!rupiahString) return null;
      // Allow digits, dot, and minus. Remove everything else
      const numericString = rupiahString.replace(/[^\d.-]/g, '');
      // Remove commas (thousands separator)
      const numericValue = parseFloat(numericString.replace(/,/g, ''));
      return isNaN(numericValue) ? null : numericValue;
    };

    const getCurrentDate = () => {
      const today = new Date();
      const dd = String(today.getDate()).padStart(2, '0');
      const mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
      const yyyy = today.getFullYear();
      return mm + '/' + dd + '/' + yyyy;
    }
    const initSelect2 = (el) => {
      $(`.${el}`).select2()
    }
    const initChosen = (el) => {
      $(`.${el}`).chosen()
    }
    const clearInput = (el) => {
      if (el.hasClass('select2-hidden-accessible')) {
        el.val(null).trigger('change')
      } else {
        el.val('')
      }
    }

    function debounce(callback, delay) {
      let timer
      return function(...args) { // Ambil semua argumen nya
        clearTimeout(timer)
        timer = setTimeout(() => {
          callback(...args)
        }, delay)
      }
    }
    const getKodefikasiAset = async () => {
      $('.kodefikasi_aset').select2({
        placeholder: 'Silahkan pilih',
        ajax: {
          url: " {{ route('rabper.sapras') }}",
          dataType: 'json',
          delay: 250,
          processResults: function(data) {
            return {
              results: $.map(data, function(item) {
                return {
                  text: `${item.kode} | ${item.nama}`,
                  id: item.kode
                }
              })
            }
          },
          cache: true
        }
      })
    }
    const capitalizeFirstLetter = (string) => {
      return string.charAt(0).toUpperCase() + string.slice(1)
    }
    const getClient = () => {
      window.clientInfo = {
        platform: navigator.platform,
        screenSize: `${window.screen.width}x${window.screen.height}`,
        lang: navigator.language || navigator.userLanguage
      }
    }
  </script>
  @stack('scripts')
</body>

</html>
