import React from 'react';

export const categories = ['Fashion', 'Home & Living', 'Electronics', 'Beauty', 'Accessories', 'Sports'];

export function Header() {
  return (
    <>
    <header className="header-area bg-white storefront-header">
      <div className="header-middle">
        <div className="container">
          <div className="d-flex justify-content-between header-wrapper middle-menu-lsw">
            <div className="logo">
              <a href="#"><img src="/__mockup/images/visermart/logo_dark.png" alt="Visermart" /></a>
            </div>
            <div className="header-search-wrapper">
              <form className="header-search-form me-auto">
                <div className="header-form-group"><input className="form--control" placeholder="I am shopping for..." /></div>
                <button className="icon" type="button" aria-label="Search">⌕</button>
              </form>
            </div>
            <button className="header-search-btn" aria-label="Search">⌕</button>
            <ul className="list list--row option-list-wrapper justify-content-center justify-content-md-end option-list d-flex align-items-center">
              <li><a href="#"><span className="ecommerce__icon">♡</span><span className="ecommerce__text">Wishlist</span></a></li>
              <li><a href="#"><span className="ecommerce__icon">⇄</span><span className="ecommerce__text">Compare</span></a></li>
              <li><a href="#"><span className="ecommerce__icon">♙</span><span className="ecommerce__text">My Account</span></a></li>
            </ul>
          </div>
        </div>
      </div>
      <div className="header-bottom">
        <div className="container">
          <div className="header-bottom-wrapper primary-menu-clw">
            <div className="view-category-wrapper">
              <div className="view-category"><div className="menu-category-btn">☰ <span>Categories</span></div></div>
            </div>
            <ul className="menu">
              <li><a href="#">Home</a></li>
              <li><a href="#">Shop</a></li>
              <li><a href="#">Brands</a></li>
              <li><a href="#">Contact</a></li>
            </ul>
            <div className="action-btn-inner d-flex">
              <div className="cart-icon-design"><button className="button cart-button">♧ <span><span className="amount d-block">$0.00</span><span className="items d-block">(0) items</span></span></button></div>
            </div>
          </div>
        </div>
      </div>
    </header>
    <MobileBottomNav />
    </>
  );
}

export function ProductCard({ compact = false }: { compact?: boolean }) {
  return (
    <div className="product-card">
      <div className="product-thumb">
        <ul className="product-card-buttons">
          <li><button aria-label="Add to wishlist">♡</button></li>
          <li><button aria-label="Compare">⇄</button></li>
        </ul>
        <a href="#"><img src="/__mockup/images/visermart/product.jpg" alt="Product" /></a>
      </div>
      <div className="product-content">
        <div className="product-before-content">
          <h6 className="title"><a href="#">Product</a></h6>
          <div className="single_content__info">
            <div className="price">$24.00</div>
            <div className="ratings-area"><span className="ratings">☆☆☆☆☆</span> <span className="rating-count">(0)</span></div>
          </div>
        </div>
        {!compact && <button type="button" className="add-to-cart-btn">♧ Add to Cart</button>}
      </div>
    </div>
  );
}

export function MobileCategoryTree({ drawer = false }: { drawer?: boolean }) {
  return (
    <div className={drawer ? 'site-sidebar mobile-menu sidebar-nav d-lg-none active' : 'mobile-category-tree'}>
      {drawer && <button type="button" className="sidebar-close-btn" aria-label="Close menu">×</button>}
      {drawer && <div className="mobile-menu-header"><div className="d-block d-lg-none">English</div></div>}
      <div className="mobile-menu-body">
        <nav className="mobile-category-tree" aria-label="Shop by category">
          <div className="mobile-category-tree__heading">
            <div><span>Browse</span><strong>Shop by category</strong></div>
            <a href="#"><span aria-hidden="true">♧</span> Shop by brand</a>
          </div>
          <ul className="mobile-category-tree__roots">
            <CategoryItem name="Fashion" id="fashion">
              <CategoryItem name="Clothing" id="clothing">
                <CategoryItem name="Women" id="women" />
                <CategoryItem name="Men" id="men" />
              </CategoryItem>
              <CategoryItem name="Shoes" id="shoes" />
              <CategoryItem name="Accessories" id="accessories" />
            </CategoryItem>
            <CategoryItem name="Home & Living" id="home-living">
              <CategoryItem name="Home Decor" id="decor" />
              <CategoryItem name="Kitchen" id="kitchen" />
            </CategoryItem>
            <CategoryItem name="Electronics" id="electronics">
              <CategoryItem name="Audio" id="audio" />
            </CategoryItem>
            <CategoryItem name="Beauty" id="beauty" />
            <CategoryItem name="Sports" id="sports" />
          </ul>
        </nav>
      </div>
    </div>
  );
}

function CategoryItem({ name, id, children }: { name: string; id: string; children?: React.ReactNode }) {
  const [open, setOpen] = React.useState(true);
  return (
    <li className="mobile-category-tree__item">
      <div className="mobile-category-tree__row">
        <a href="#">{name}</a>
        {children && <button type="button" className="mobile-category-tree__toggle" aria-expanded={open} aria-controls={`mobile-category-children-${id}`} aria-label={`${open ? 'Collapse' : 'Expand'} ${name}`} onClick={() => setOpen(!open)}><i aria-hidden="true">{open ? '−' : '+'}</i></button>}
      </div>
      {children && <ul className="mobile-category-tree__children" id={`mobile-category-children-${id}`} hidden={!open}>{children}</ul>}
    </li>
  );
}

export function MobileBottomNav() {
  return (
    <ul className="list list--row mobile-menu-icons justify-content-center justify-content-md-end option-list d-lg-none d-flex">
      <li><a className="ecommerce" href="#"><span className="ecommerce__icon">☷</span><span className="ecommerce__text">Category</span></a></li>
      <li><a className="ecommerce" href="#"><span className="ecommerce__icon">♡</span><span className="ecommerce__text">Wishlist</span></a></li>
      <li><a className="ecommerce" href="#"><span className="ecommerce__icon">⇄</span><span className="ecommerce__text">Compare</span></a></li>
      <li><a className="ecommerce" href="#"><span className="ecommerce__icon">♙</span><span className="ecommerce__text">My Account</span></a></li>
    </ul>
  );
}
