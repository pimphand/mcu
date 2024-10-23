<!-- BEGIN: Main Menu-->
<div class="main-menu menu-fixed menu-light menu-accordion menu-shadow" data-scroll-to-active="true">
    <div class="navbar-header">
        <ul class="nav navbar-nav flex-row">
            <li class="nav-item me-auto"><a class="navbar-brand" href="{{ route('welcome.index') }}">
            <span
                        class="brand-logo">
                        <img src="{{ asset('logo.png') }}" alt="" width="30">
                        </span>
                    <h2 class="brand-text">{{ config('app.name') }}</h2>
                </a></li>
            <li class="nav-item nav-toggle"><a class="nav-link modern-nav-toggle pe-0" data-bs-toggle="collapse"><i
                        class="d-block d-xl-none text-primary toggle-icon font-medium-4" data-feather="x"></i><i
                        class="d-none d-xl-block collapse-toggle-icon font-medium-4  text-primary" data-feather="disc"
                        data-ticon="disc"></i></a></li>
        </ul>
    </div>
    <div class="shadow-bottom"></div>
    <div class="main-menu-content">
        <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
            @foreach ($dataMenus as $item)
                @if ($item['is_view'])
                    <li class=" nav-item">
                        <a class="d-flex align-items-center" href="{{ $item['url'] }}">
                            <i data-feather="{{ $item['icon'] }}"></i>
                            <span class="menu-title text-truncate" data-i18n="User">{{ $item['name'] }}</span>
                        </a>
                        @if (isset($item['sub_menu']))
                            @foreach ($item['sub_menu'] as $item)
                                @if ($item['is_view'])
                                    <ul class="menu-content">
                                        <li
                                            class="{{ sprintf('/%s', request()->segment(1)) == $item['url'] ? 'active' : '' }}">
                                            <a class="d-flex align-items-center" href="{{ $item['url'] }}"><i
                                                    data-feather="{{ $item['icon'] }}"></i><span
                                                    class="menu-item text-truncate"
                                                    data-i18n="List">{{ $item['name'] }}</span></a>
                                        </li>
                                    </ul>
                                @endif
                            @endforeach
                        @endif
                    </li>
                @endif
            @endforeach
        </ul>
    </div>
</div>
<!-- END: Main Menu-->
