@php
    $sideBarLinks = json_decode($sidenav);
@endphp

<div class="sidebar bg--dark">
    <button class="res-sidebar-close-btn"><i class="las la-times"></i></button>
    <div class="sidebar__inner">
        <div class="sidebar__logo">
            <a href="{{route('admin.dashboard')}}" class="sidebar__main-logo"><img src="{{siteLogo()}}" alt="image"></a>
        </div>
        <div class="admin-sidebar-search">
            <label class="visually-hidden" for="adminSidebarSearch">@lang('Search navigation')</label>
            <i class="las la-search" aria-hidden="true"></i>
            <input id="adminSidebarSearch" type="search" placeholder="@lang('Find a page')" autocomplete="off">
            <kbd>/</kbd>
        </div>
        <div class="sidebar__menu-wrapper">
            <ul class="sidebar__menu">
                @foreach($sideBarLinks as $key => $data)
                    @if (isset($data->header))
                        <li class="sidebar__menu-header">{{ __($data->header) }}</li>
                    @endif
                    @if(isset($data->submenu))
                        <li class="sidebar-menu-item sidebar-dropdown">
                            <a href="javascript:void(0)" @isset($data->menu_active) class="{{ menuActive($data->menu_active, 3) }}" @endisset>
                                <i class="menu-icon {{ isset($data->icon) ? $data->icon : '' }}"></i>
                                <span class="menu-title">{{ isset($data->title) ? __($data->title) : '' }}</span>
                                @isset($data->counters)
                                    @foreach($data->counters ?? [] as $counter)
                                        @if($$counter > 0)
                                            <span class="menu-badge menu-badge-level-one bg--warning ms-auto">
                                                <i class="fas fa-exclamation"></i>
                                            </span>
                                            @break
                                        @endif
                                    @endforeach
                                @endisset
                            </a>
                            <div class="sidebar-submenu {{ isset($data->menu_active) ? menuActive($data->menu_active, 2) : '' }} ">
                                <ul>
                                    @foreach($data->submenu as $menu)
                                    @php
                                        $submenuParams = null;
                                        if (isset($menu->params)) {
                                            foreach ($menu->params as $submenuParamVal) {
                                                $submenuParams[] = array_values((array)$submenuParamVal)[0];
                                            }
                                        }
                                    @endphp
                                        <li class="sidebar-menu-item {{ isset($menu->menu_active) ?  menuActive($menu->menu_active, param:$submenuParams) : '' }} ">
                                            <a href="{{ isset($menu->route_name) ? route($menu->route_name,$submenuParams) : '' }}" class="nav-link">
                                                <i class="menu-icon las la-dot-circle"></i>
                                                <span class="menu-title">{{ __($menu->title) }}</span>
                                                @php $counter = isset($menu->counter) ? $menu->counter : ''; @endphp
                                                @if(isset($$counter) && $$counter)
                                                    <span class="menu-badge bg--info ms-auto">{{ $$counter }}</span>
                                                @endif
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </li>
                    @else
                        @php
                            $mainParams = null;
                            if (isset($data->params)) {
                                foreach ($data->params as $paramVal) {
                                    $mainParams[] = array_values((array)$paramVal)[0];
                                }
                            }
                        @endphp
                        <li class="sidebar-menu-item {{ isset($data->menu_active) ? menuActive($data->menu_active, param:$mainParams) : '' }}">
                            <a href="{{ route($data->route_name,$mainParams) }}" class="nav-link">
                                <i class="menu-icon {{ $data->icon }}"></i>
                                <span class="menu-title">{{ isset($data->title) ? __($data->title) : '' }}</span>
                                @php $counter = isset($data->counter) ? $data->counter : ''; @endphp
                                @if(isset($$counter) && $$counter)
                                    <span class="menu-badge bg--info ms-auto">{{ $$counter }}</span>
                                @endif
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
        <div class="version-info text-center text-uppercase">
            <span class="text--primary">{{__(systemDetails()['name'])}}</span>
            <span class="text--success">@lang('V'){{systemDetails()['version']}} </span>
        </div>
    </div>
</div>
<!-- sidebar end -->

<style>
    .sidebar.bg--dark { background:linear-gradient(180deg,#111827 0%,#0b1220 100%); box-shadow:8px 0 28px rgba(15,23,42,.14); }
    .sidebar .sidebar__inner { padding:18px 12px 14px; }
    .sidebar .sidebar__logo { padding:8px 12px 18px; border-bottom:1px solid rgba(255,255,255,.09); }
    .admin-sidebar-search { position:relative; display:flex; align-items:center; gap:9px; margin:16px 5px 14px; padding:0 11px; min-height:42px; border:1px solid rgba(255,255,255,.12); border-radius:11px; background:rgba(255,255,255,.055); color:#94a3b8; }
    .admin-sidebar-search input { width:100%; min-width:0; border:0; outline:0; background:transparent; color:#f8fafc; font-size:13px; }
    .admin-sidebar-search input::placeholder { color:#94a3b8; }
    .admin-sidebar-search kbd { border:1px solid rgba(255,255,255,.16); border-radius:5px; padding:1px 5px; color:#94a3b8; font-size:11px; }
    .sidebar .sidebar__menu-wrapper { max-height:calc(100vh - 190px); overflow-y:auto; padding:0 4px 16px; scrollbar-width:thin; scrollbar-color:rgba(148,163,184,.45) transparent; }
    .sidebar .sidebar__menu { display:flex; flex-direction:column; gap:3px; }
    .sidebar .sidebar__menu-header { margin:16px 10px 6px; color:#7e8da5; font-size:10px; font-weight:700; letter-spacing:.12em; text-transform:uppercase; }
    .sidebar .sidebar-menu-item > a, .sidebar .sidebar-dropdown > a { display:flex; align-items:center; gap:11px; min-height:42px; padding:9px 11px; border-radius:10px; color:#cbd5e1; transition:background .18s ease,color .18s ease,transform .18s ease; }
    .sidebar .sidebar-menu-item > a:hover, .sidebar .sidebar-dropdown > a:hover { background:rgba(148,163,184,.13); color:#fff; transform:translateX(2px); }
    .sidebar .sidebar-menu-item.active > a, .sidebar .sidebar-dropdown > a.active { background:linear-gradient(100deg,rgba(59,130,246,.26),rgba(14,165,233,.12)); color:#fff; box-shadow:inset 3px 0 #60a5fa; }
    .sidebar .menu-icon { width:19px; margin:0; color:#93a4bd; font-size:17px; text-align:center; }
    .sidebar .sidebar-menu-item.active .menu-icon, .sidebar .sidebar-dropdown > a.active .menu-icon { color:#7dd3fc; }
    .sidebar .menu-title { flex:1; font-size:13px; font-weight:550; }
    .sidebar .sidebar-submenu { margin:3px 0 7px 19px; padding-left:9px; border-left:1px solid rgba(148,163,184,.24); }
    .sidebar .sidebar-submenu li a { display:flex; gap:9px; padding:8px 10px; border-radius:8px; color:#aebbd0; font-size:12px; }
    .sidebar .sidebar-submenu li a:hover, .sidebar .sidebar-submenu li.active a { background:rgba(148,163,184,.11); color:#fff; }
    .sidebar .sidebar-submenu .menu-icon { color:#64748b; font-size:13px; }
    .sidebar .version-info { margin:10px 5px 0; padding-top:12px; border-top:1px solid rgba(255,255,255,.09); color:#94a3b8; font-size:10px; }
    @media(max-width:767px) {
        .sidebar .sidebar__inner { padding-top:calc(14px + env(safe-area-inset-top)); }
        .sidebar .sidebar__menu-wrapper { max-height:calc(100dvh - 180px); }
    }
</style>

@push('script')
    <script>
        if($('li').hasClass('active')){
            $('.sidebar__menu-wrapper').animate({
                scrollTop: eval($(".active").offset().top - 320)
            },500);
        }

        (function () {
            const input = document.getElementById('adminSidebarSearch');
            const menu = document.querySelector('.sidebar__menu');
            if (!input || !menu) return;
            const filterMenu = () => {
                const query = input.value.trim().toLocaleLowerCase();
                Array.from(menu.children).forEach((item) => {
                    if (!item.classList.contains('sidebar-menu-item')) return;
                    if (!item.classList.contains('sidebar-dropdown')) {
                        item.hidden = !!query && !item.textContent.toLocaleLowerCase().includes(query);
                        return;
                    }
                    const groupTitle = item.querySelector(':scope > a .menu-title')?.textContent.toLocaleLowerCase() || '';
                    const groupMatches = !query || groupTitle.includes(query);
                    const submenu = item.querySelector('.sidebar-submenu');
                    const links = Array.from(item.querySelectorAll('.sidebar-submenu > ul > li'));
                    const matchingChild = links.some(link => link.textContent.toLocaleLowerCase().includes(query));
                    item.hidden = !!query && !groupMatches && !matchingChild;
                    links.forEach(link => {
                        link.hidden = !!query && !groupMatches && !link.textContent.toLocaleLowerCase().includes(query);
                    });
                    if (query && matchingChild && !groupMatches) {
                        submenu?.classList.add('sidebar-submenu__open');
                        item.dataset.searchOpened = '1';
                    } else if (!query && item.dataset.searchOpened) {
                        submenu?.classList.remove('sidebar-submenu__open');
                        delete item.dataset.searchOpened;
                    }
                });
            };
            input.addEventListener('input', filterMenu);
            document.addEventListener('keydown', event => {
                if (event.key === '/' && !/INPUT|TEXTAREA|SELECT/.test(document.activeElement?.tagName || '')) {
                    event.preventDefault();
                    input.focus();
                }
                if (event.key === 'Escape' && document.activeElement === input) {
                    input.value = '';
                    filterMenu();
                    input.blur();
                }
            });
        })();
    </script>
@endpush
