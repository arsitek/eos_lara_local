<!--APP-SIDEBAR-->
<div class="sticky">
    <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
    <div class="app-sidebar">
        <div class="side-header">
            <a class="header-brand1" href="/">
                <img src="{{ asset('assets/img/usk_baru.png') }}" class="img-fluid desktop-logo" alt="logo" width="100">
            </a>
            <!-- LOGO -->
        </div>
        <div class="main-sidemenu">
            <div class="slide-left disabled" id="slide-left"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z" /></svg>
            </div>
            <ul class="side-menu">
                <li class="sub-category">
                    <h3>Main</h3>
                </li>
                <?php $level=1; ?>
                <?php $menu = get_sidebar_menu() ?>
                @foreach ($menu as $index => $menu)
                @if($level > $menu->level_menu )
                    @for ($i = $menu->level_menu; $i < $level; $i++)
                            </ul>
                        </li>
                    @endfor
                    <?php $level=$menu->level_menu ?>
                @endif
                @if ($menu->is_nested == 0)
                    @if($level < 3)
                        <li class="{{ get_class_slide($level) }}">
                            <a class="{{ get_class_menu($level) }}__item" data-bs-toggle="{{ get_class_slide($level) }}" href="{{ route($menu->route) }}">
                                @if ($level == 1)
                                    <i class="{{ get_class_menu($level) }}__icon {{ $menu->icon }}"></i>
                                @endif
                                <span class="{{ get_class_menu($level) }}__label">{{ $menu->nama }}</span>
                            </a>
                        </li>
                    @elseif($level < 4)
                        <li class="{{ get_class_slide(2) }}{{ $level-1  }}">
                            <a class="{{ get_class_menu(2) }}__item{{ $level-1  }}"
                                data-bs-toggle="{{ get_class_slide(2) }}{{ $level-1  }}"
                                href="{{ route($menu->route) }}">
                                <span class="{{ get_class_menu(2) }}__label{{ $level-1  }}">{{ $menu->nama }}</span>
                            </a>
                        </li>
                    @else
                        <li class="{{ get_class_slide(2) }}2">
                            <a class="ms-{{ ($level - 3) * 5 }} {{ get_class_menu(2) }}__item2"
                                data-bs-toggle="{{ get_class_slide(2) }}2"
                                href="{{ route($menu->route) }}">
                                <span class="{{ get_class_menu(2) }}__label2">{{ $menu->nama }}</span>
                            </a>
                        </li>
                    @endif
                @endif

                @if ($menu->is_nested == 1)
                    @if ($level < 3)
                        <li class="{{ get_class_slide($level) }}">
                            <a class="{{ get_class_menu($level) }}__item" data-bs-toggle="{{ get_class_slide($level) }}" href="{{ route($menu->route) }}">
                                @if ($level == 1)
                                    <i class="{{ get_class_menu($level) }}__icon {{ $menu->icon }}"></i>
                                @endif
                                <span class="{{ get_class_menu($level) }}__label">{{ $menu->nama }}</span>
                                <i class="angle fe fe-chevron-right"></i>
                                </a>
                            <ul class="{{ get_class_slide($level) }}-menu">
                    @elseif($level < 4)
                        <li class="{{ get_class_slide(2) }}{{ $level-1  }}">
                            <a class="{{ get_class_menu(2) }}__item{{ $level-1  }}"
                                data-bs-toggle="{{ get_class_slide(2) }}" href="{{ route($menu->route) }}">
                                <span
                                    class="{{ get_class_menu(2) }}__label{{ $level-1  }}">{{ $menu->nama }}</span>
                                <i class="angle fe fe-chevron-right"></i>
                                </a>
                            <ul class="{{ get_class_slide(2) }}-menu{{ $level-1  }}">
                    @else
                        <li class="{{ get_class_slide(2) }}2">
                            <a class="ms-{{ ($level - 3) * 5 }} {{ get_class_menu(2) }}__item2"
                                data-bs-toggle="{{ get_class_slide(2) }}" href="{{ route($menu->route) }}">
                                <span
                                    class="{{ get_class_menu(2) }}__label2">{{ $menu->nama }}</span>
                                <i class="angle fe fe-chevron-right"></i>
                                </a>
                            <ul class="{{ get_class_slide(2) }}-menu2">
                    @endif
                    <?php $level+=1 ?>
                @endif


                @if($index == $menu->count() - 1)
                    @if($level != 1)
                        </ul>
                    </li>
                    @endif
                @endif
                @endforeach
            </ul>
            @if( in_array( session("id_user"), [  "2024019712182001", "	2024019511151001"] ) )  <!-- Harcode menu untuk upt percetakan -->
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="http://10.44.0.111">
                        <i class="side-menu__icon fe fe-layers"></i>
                        <span class="side-menu__label">Mutasi</span>
                        <i class="angle fe fe-chevron-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li class="sub-slide">
                            <a class="sub-side-menu__item" data-bs-toggle="sub-slide" href="https://sirekat.usk.ac.id/mutasi/percetakan">
                                <span class="sub-side-menu__label">Mutasi Anggaran Percetakan</span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
            <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24"
                    height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z" /></svg>
            </div>
        </div>
    </div>
    <!--/APP-SIDEBAR-->
</div>
