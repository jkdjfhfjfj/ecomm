 @php
     $headers = App\Models\Frontend::where('data_keys', 'headers.order.content')->first()->data_values;
     $headerThree = App\Models\Frontend::where('data_keys', 'header_three.content')->first()?->data_values;
     $siteMenu = $headerThree->group->links;
 @endphp

 @include('Template::partials.storefront_styles')

 <div class="storefront-appbar" role="banner">
     <div class="storefront-appbar__inner container">
         <button type="button" class="primary-menu-button storefront-appbar__menu" aria-label="@lang('Open navigation menu')">
             <span></span><span></span><span></span>
         </button>
         <div class="storefront-appbar__logo"><x-site-logo type="dark" /></div>
         <form action="{{ route('product.all') }}" method="GET" class="storefront-appbar__search" id="storefrontSearchForm">
             <input type="search" name="search" value="{{ request()->search }}" placeholder="@lang('Search products and brands')" aria-label="@lang('Search products and brands')">
             <button type="submit" aria-label="@lang('Search')"><i class="las la-search" aria-hidden="true"></i></button>
         </form>
         <div class="storefront-appbar__actions">
             <button type="button" class="storefront-appbar__icon storefront-appbar__mobile-search" aria-label="@lang('Open search')" aria-controls="storefrontSearchForm" aria-expanded="false"><i class="las la-search"></i></button>
             @auth
                 <div class="storefront-appbar__notification"><x-user-notification-component /></div>
             @endauth
             <div class="storefront-appbar__account">@include('Template::partials.user_auth_options')</div>
             <button type="button" class="storefront-appbar__cart cart-button" aria-label="@lang('Open cart')">
                 <i class="las la-shopping-bag" aria-hidden="true"></i>
                 <span class="storefront-appbar__cart-copy">
                     <span class="storefront-appbar__cart-total">{{ gs('cur_sym') }}<span class="cartSubtotal">0</span></span>
                     <span class="storefront-appbar__cart-items">(<span class="cartItemCount">0</span>) @lang('items')</span>
                 </span>
             </button>
         </div>
     </div>
 </div>

 <div class="header-area bg-white storefront-header">
     @foreach ($headers as $header)
         @if ($header === 'header_three')
             @include('Template::partials.headers.'.$header)
         @endif
     @endforeach
 </div>

 @include('Template::partials.mobile_menu')

 @push('script')
     <script>
         (() => {
             const bar = document.querySelector('.storefront-appbar');
             const button = document.querySelector('.storefront-appbar__mobile-search');
             const input = document.querySelector('#storefrontSearchForm input[name="search"]');
             if (!bar || !button || !input) return;
             button.addEventListener('click', () => {
                 const open = bar.classList.toggle('storefront-appbar--search-open');
                 button.setAttribute('aria-expanded', String(open));
                 if (open) input.focus();
             });
             document.addEventListener('keydown', (event) => {
                 if (event.key === 'Escape') {
                     bar.classList.remove('storefront-appbar--search-open');
                     button.setAttribute('aria-expanded', 'false');
                 }
             });
         })();
     </script>
 @endpush
