@once
        <style>
            @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap');

            :root {
                --store-paper: #f8f6f1;
                --store-warm: #f0ece4;
                --store-ink: #252521;
                --store-muted: #746f67;
                --store-line: #e4dfd5;
                --store-accent: #9b493d;
                --store-accent-dark: #79392f;
                --store-display: 'Playfair Display', Georgia, serif;
                --store-body: 'DM Sans', sans-serif;
                --store-shadow: 0 16px 44px rgba(44, 37, 29, .08);
            }

            body { background:var(--store-paper); color:var(--store-ink); font-family:var(--store-body); -webkit-font-smoothing:antialiased; }
            body:before { position:fixed; z-index:1000; inset:0; pointer-events:none; content:""; opacity:.08; background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 180 180' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.84' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.12'/%3E%3C/svg%3E"); }
            button,input,select { font-family:var(--store-body); }
            .container { max-width:1320px; }
            .header-area { position:relative; z-index:30; background:var(--store-paper)!important; }
            .header-top { background:var(--store-ink)!important; color:#eee8de; }
            .header-top a,.header-top .header-top-wrap { color:#eee8de; }
            .header-middle { border-bottom:1px solid var(--store-line); background:var(--store-paper); }
            .header-middle .container { padding-top:10px; padding-bottom:10px; }
            .header-wrapper { min-height:58px; gap:clamp(12px,2.2vw,30px); }
            .header-middle .logo img { max-height:48px; width:auto; object-fit:contain; }
            .header-search-wrapper { flex:1 1 420px; max-width:620px; }
            .header-search-form { position:relative; display:flex; align-items:center; border:1px solid #ddd6ca; border-radius:3px; background:#fffefa; }
            .header-search-form .header-form-group { flex:1; }
            .header-search-form input.form--control { height:44px; padding:0 16px; border:0; background:transparent; color:var(--store-ink); }
            .header-search-form .icon { width:46px; height:44px; border:0; background:var(--store-ink); color:#fff; }
            .option-list-wrapper { gap:18px; }
            .option-list-wrapper a,.option-list-wrapper .ecommerce { color:var(--store-ink); }
            .header-bottom { background:var(--store-ink)!important; }
            .header-bottom-wrapper { min-height:46px; }
            .header-bottom .menu>li>a,.header-bottom .menu li a { color:#f7f3eb; }
            .header-bottom .menu li a.active { background:rgba(255,255,255,.12); }
            .view-category .menu-category-btn { min-height:46px; color:#fff; }
            .header-bottom .action-btn-inner { gap:8px; }
            .header-bottom .cart-icon-design .button { border:0; background:transparent; color:#fff; }
            .mobile-menu-icons { background:#20211f!important; border-top:1px solid rgba(255,255,255,.16)!important; color:#fff!important; box-shadow:0 -8px 24px rgba(20,22,20,.16); }
            .mobile-menu-icons .ecommerce,.mobile-menu-icons .ecommerce__text,.mobile-menu-icons .ecommerce__icon,.mobile-menu-icons .ecommerce__icon i,.mobile-menu-icons a,.mobile-menu-icons button { color:#fff!important; opacity:1; }
            .mobile-menu-icons img { filter:brightness(0) invert(1); }
            .mobile-menu-icons>li+li { border-left-color:rgba(255,255,255,.2)!important; }
            .mobile-menu.sidebar-nav { width:min(430px,92vw); max-width:none; background:#fbfaf7; box-shadow:18px 0 55px rgba(22,27,31,.2); }
            .mobile-menu .mobile-menu-body { padding:18px 16px 28px; }
            .mobile-category-tree { border:1px solid #e7e1d7!important; border-radius:4px!important; background:#fffefa!important; box-shadow:0 10px 28px rgba(37,37,33,.06); }
            .mobile-category-tree__heading { position:sticky; top:0; z-index:2; background:#fffefa; }
            .mobile-category-tree__heading>a { border-radius:3px!important; background:#252521!important; color:#fff!important; }
            .mobile-category-tree__row { border-color:#eee9e0!important; }
            .mobile-category-tree__row>a { color:#252521!important; text-decoration:none; }
            .mobile-category-tree__row>a:hover,.mobile-category-tree__row>a:focus-visible { color:var(--store-accent)!important; }
            .mobile-category-tree__toggle { border-radius:3px!important; background:#f3eee5!important; }
            .mobile-category-tree__children { border-color:#d9cfc1!important; }
            .hero-section { margin-top:18px; }
            .hero-slider { overflow:hidden; }
            .banner-container { max-width:1360px; }
            .site-section,.section-header { color:var(--store-ink); }
            .section-header .title,.section-header h3 { font-family:var(--store-display); font-size:clamp(1.8rem,3vw,2.7rem); font-weight:500; letter-spacing:-.035em; }

            .catalog-intro { display:flex; align-items:end; justify-content:space-between; gap:1.5rem; padding:22px 0 27px; margin:0 0 22px; border-bottom:1px solid var(--store-line); }
            .catalog-kicker,.home-shelf-eyebrow { color:var(--store-accent); font-size:.69rem; font-weight:700; letter-spacing:.17em; text-transform:uppercase; }
            .catalog-intro h1 { margin:7px 0 5px; color:var(--store-ink); font-family:var(--store-display); font-size:clamp(2.4rem,5vw,4rem); font-weight:500; letter-spacing:-.045em; line-height:1.05; }
            .catalog-intro p { max-width:570px; margin:0; color:var(--store-muted); font-size:.97rem; }
            .catalog-intro-meta { display:flex; flex-direction:column; align-items:flex-end; color:var(--store-muted); font-size:.78rem; letter-spacing:.04em; text-transform:uppercase; }
            .catalog-total { color:var(--store-accent); font-family:var(--store-display); font-size:2.3rem; line-height:1; }
            .catalog-category-strip { display:flex; gap:9px; overflow:auto; padding:0 0 26px; scrollbar-width:thin; }
            .catalog-category-pill { display:flex; flex:0 0 auto; align-items:center; justify-content:space-between; gap:20px; min-width:120px; padding:10px 13px; border:1px solid var(--store-line); background:#fdfbf7; color:var(--store-ink); font-size:.83rem; text-decoration:none; }
            .catalog-category-pill:hover { border-color:var(--store-accent); color:var(--store-accent); }
            .product-shop { align-items:flex-start; gap:28px; }
            .category-sidebar { flex:0 0 240px; padding:18px 18px 10px; border:1px solid var(--store-line); background:#fdfbf8; }
            .category-sidebar .widget { padding:0 0 19px; margin:0 0 18px; border-bottom:1px solid var(--store-line); }
            .category-sidebar .widget:last-child { border-bottom:0; }
            .category-sidebar .widget .title { margin:0 0 13px; color:var(--store-ink); font-size:.77rem; font-weight:700; letter-spacing:.12em; text-transform:uppercase; }
            .category-sidebar .filter-category li { padding:5px 0; }
            .category-sidebar .filter-category a { color:#5e5952; font-size:.9rem; }
            .category-sidebar .filter-category a:hover { color:var(--store-accent); }
            .category-sidebar .form-check-input { accent-color:var(--store-accent); }
            .category-sidebar .form-check-input:checked { background-color:var(--store-accent); border-color:var(--store-accent); }
            .category-sidebar .form-control,.category-sidebar input[type=number] { min-height:38px; border:1px solid var(--store-line); background:#fff; }
            .products-container { min-width:0; }
            .filter-category-header { min-height:54px; padding:9px 13px; margin-bottom:18px; border:1px solid var(--store-line); background:#fdfbf8; }
            .filter-category-header .small { color:var(--store-muted)!important; font-size:.82rem; }
            .filter-category-header .form--control,.filter-category-header select { min-height:36px; padding:5px 26px 5px 9px; border:1px solid var(--store-line); background:#fff; color:var(--store-ink); }
            .filter-category-header p { color:var(--store-muted); font-size:.8rem; }
            .view-style a { display:grid; width:34px; height:34px; place-items:center; border:1px solid var(--store-line); color:var(--store-muted); }
            .view-style a.active { border-color:var(--store-ink); background:var(--store-ink); color:#fff; }
            #grid-view { --bs-gutter-y:25px; }
            .product-card { height:100%; border:1px solid #e8e3da; border-radius:0; background:#fffefa; box-shadow:none; transition:transform .25s ease,box-shadow .25s ease,border-color .25s ease; }
            .catalog-product-tile { position:relative; height:100%; }
            .catalog-brand-tag { position:absolute; z-index:2; top:11px; left:11px; max-width:calc(100% - 62px); overflow:hidden; padding:6px 8px; background:rgba(253,251,247,.94); color:#4a443d; font-size:.63rem; font-weight:700; letter-spacing:.1em; text-overflow:ellipsis; text-transform:uppercase; white-space:nowrap; }
            .product-card:hover { transform:translateY(-4px); border-color:#d6ccbd; box-shadow:var(--store-shadow); }
            .product-card .product-thumb { position:relative; overflow:hidden; aspect-ratio:.88; background:#efede8; }
            .product-card .product-thumb>a { display:block; width:100%; height:100%; }
            .product-card .product-thumb img { display:block; width:100%; height:100%; object-fit:cover; mix-blend-mode:multiply; transition:transform .45s ease; }
            .product-card:hover .product-thumb img { transform:scale(1.035); }
            .product-card-buttons { top:12px; right:12px; }
            .product-card-buttons button { width:35px; height:35px; border:1px solid rgba(40,37,32,.12); border-radius:50%; background:#fffefa; color:var(--store-ink); }
            .product-card-buttons button:hover,.product-card-buttons button.active { background:var(--store-accent); color:white; }
            .product-content { padding:14px 15px 16px; }
            .product-card .product-content .title { min-height:39px; margin:0 0 8px; font-size:.91rem; font-weight:500; line-height:1.4; }
            .product-card .product-content .title a { color:var(--store-ink); text-decoration:none; }
            .product-card .product-content .title a:hover { color:var(--store-accent); }
            .product-card .single_content__info { gap:8px; }
            .product-card .price { color:var(--store-accent-dark); font-size:1rem; font-weight:700; }
            .product-card .ratings-area { color:#9b6a2d; font-size:.8rem; }
            .product-card .rating-count { color:#8b857d; }
            .product-card .add-to-cart-btn { width:100%; min-height:39px; margin-top:13px; border:1px solid var(--store-ink); border-radius:0; background:var(--store-ink); color:white; font-size:.78rem; font-weight:600; letter-spacing:.04em; text-transform:uppercase; }
            .product-card .add-to-cart-btn:hover { border-color:var(--store-accent); background:var(--store-accent); }
            .pagination-wrapper { margin-top:34px!important; }
            .pagination-wrapper .page-link { border-color:var(--store-line); color:var(--store-ink); }
            .pagination-wrapper .active .page-link { border-color:var(--store-ink); background:var(--store-ink); color:#fff; }
            .products-container .empty-message { min-height:300px; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:38px 20px; border:1px solid var(--store-line); background:#fffefa; }
            .products-container .empty-message img { width:min(180px,42vw); max-height:130px; object-fit:contain; opacity:.82; }
            .products-container .empty-message h5 { margin:15px 0 4px!important; color:var(--store-ink)!important; font-family:var(--store-display); font-size:1.55rem; font-weight:500; }
            .products-container .empty-message .message { max-width:430px; margin:0; color:var(--store-muted); line-height:1.65; }
            .products-container .empty-message-link { display:inline-flex; align-items:center; gap:7px; padding:10px 13px; margin-top:16px; border:1px solid var(--store-ink); color:var(--store-ink); font-size:.8rem; font-weight:650; text-decoration:none; }
            .products-container .empty-message-link:hover { background:var(--store-ink); color:#fff; }
            .category-directory .child-category { padding:10px 0 0 14px; margin:7px 0 0; border-left:1px solid var(--store-line); list-style:none; }
            .category-directory .child-category li { padding:4px 0; }
            .category-directory .child-category .text-nowrap { display:flex; align-items:center; gap:8px; white-space:normal!important; }
            .category-directory .child-category .prefix { color:#b2a594; font-size:.8rem; }
            .category-directory .child-category .category-name { color:#5e5952; font-size:.9rem; text-decoration:none; }
            .category-directory .child-category .icon-img { width:20px; height:20px; object-fit:contain; }
            .daily-offer-reopen { right:22px!important; bottom:22px!important; border-radius:3px!important; background:var(--store-accent)!important; }

            @media(max-width:1199px) { .category-sidebar { flex-basis:220px; } .product-shop { gap:20px; } }
            @media(max-width:991px) {
                .category-sidebar { position:fixed; z-index:1200; top:0; bottom:0; left:0; width:min(360px,90vw); max-width:none; overflow:auto; padding:60px 22px 24px; background:var(--store-paper); box-shadow:var(--store-shadow); transform:translateX(-105%); transition:transform .25s ease; }
                .category-sidebar.active { transform:translateX(0); }
                .category-sidebar .close-sidebar { position:absolute; top:16px; right:18px; border:0; background:none; font-size:1.2rem; }
                .filter-category-header { gap:12px; }
                .filter-select-right { justify-content:flex-end; flex-wrap:wrap; }
            }
            @media(max-width:767px) {
                .header-middle .container { padding:5px 12px!important; }
                .header-wrapper { min-height:52px!important; gap:8px!important; }
                .header-middle .logo { display:flex; flex:0 1 auto; align-items:center; min-width:0; height:44px; }
                .header-middle .logo img { max-width:min(44vw,160px)!important; max-height:38px!important; }
                .header-middle .header-search-btn { flex:0 0 38px; width:38px; height:38px; }
                .header-bottom-wrapper { min-height:46px!important; }
                .mobile-menu-icons { position:fixed!important; z-index:1050!important; right:0; bottom:0; left:0; display:flex!important; justify-content:space-around; padding:8px 0 calc(8px + env(safe-area-inset-bottom)); background:#20211f!important; color:#fff!important; }
                .mobile-menu-icons .ecommerce,.mobile-menu-icons .ecommerce__text,.mobile-menu-icons .ecommerce__icon i { color:#fff!important; }
                .mobile-menu-icons .ecommerce__text { font-size:10px; font-weight:600; }
                .mobile-menu-icons>li+li { border-left:1px solid rgba(255,255,255,.18); }
                body { padding-bottom:calc(78px + env(safe-area-inset-bottom)); }
                .hero-section { margin-top:10px; }
                .catalog-intro { align-items:flex-start; padding:13px 0 19px; margin-bottom:16px; }
                .catalog-intro h1 { font-size:2.55rem; }
                .catalog-intro p { max-width:330px; font-size:.88rem; }
                .catalog-intro-meta { padding-top:7px; font-size:.62rem; text-align:right; }
                .catalog-total { font-size:1.8rem; }
                .catalog-category-strip { padding-bottom:18px; }
                .product-shop { display:block; }
                .filter-category-header { align-items:flex-start!important; padding:10px; }
                .filter-select-right { gap:10px!important; justify-content:space-between; }
                .filter-select-right>div:first-child { flex:1; flex-wrap:wrap; }
                .filter-select-item p { font-size:.72rem; }
                .filter-category-header select { max-width:130px; font-size:.75rem; }
                #grid-view { --bs-gutter-x:.6rem; --bs-gutter-y:.75rem; }
                .products-container #grid-view>[class*="col-"] { flex:0 0 50%; max-width:50%; }
                .product-card .product-thumb,.products-container .product-card .product-thumb { aspect-ratio:.8; }
                .product-content,.products-container .product-content { padding:9px 9px 11px; }
                .product-card .product-content .title { min-height:38px; font-size:.8rem; }
                .product-card .price { font-size:.91rem; }
                .product-card .add-to-cart-btn { min-height:36px; margin-top:10px; font-size:.69rem; }
            }
            @media(prefers-reduced-motion:reduce) { *,*:before,*:after { scroll-behavior:auto!important; transition-duration:.01ms!important; animation-duration:.01ms!important; } }
        </style>
@endonce
