import React from 'react';
import './_group.css';
import { Header, ProductCard } from './_shared/Storefront';

const categories = ['Fashion', 'Home & Living', 'Electronics', 'Beauty', 'Accessories', 'Sports'];
const brands = ['Brand A', 'Brand B', 'Brand C'];

export function Listing() {
  const [filterOpen, setFilterOpen] = React.useState(false);
  return (
    <div className="vm-preview min-h-screen">
      <Header />
      <main className="py-60 storefront-listing">
        <div className="container">
          <ul className="mb-4 breadcrumb">
            <li className="breadcrumb-item"><a href="#">Home</a></li>
            <li className="breadcrumb-item"><span>Shop all</span></li>
          </ul>
          <header className="catalog-intro">
            <div><span className="catalog-kicker">The collection</span><h1>Shop all</h1><p>Explore pieces across the collection. Refine by brand, price, and the details that matter to you.</p></div>
            <div className="catalog-intro-meta"><span className="catalog-total">24</span><span>pieces to explore</span></div>
          </header>
          <nav className="catalog-category-strip" aria-label="Browse categories">
            {categories.map((category) => <a href="#" className="catalog-category-pill" key={category}><span>{category}</span><span aria-hidden="true">↗</span></a>)}
          </nav>
          <div className="product-shop">
            <aside className={`category-sidebar ${filterOpen ? 'active' : ''}`}>
              <button type="button" className="close-sidebar text--secondary d-xl-none" onClick={() => setFilterOpen(false)} aria-label="Close filters">×</button>
              <form className="product-filter-form">
                <div className="widget">
                  <h5 className="title">Categories</h5>
                  <div className="widget-body"><ul className="filter-category filter-overflow">{categories.map((category) => <li key={category}><a href="#">› {category}</a></li>)}</ul></div>
                </div>
                <div className="widget">
                  <h5 className="title">Filter by Brand</h5>
                  <div className="widget-body filter-overflow">{brands.map((brand, index) => <div className="widget-check-group brand-filter" key={brand}><input type="checkbox" className="form-check-input brand-item" value={brand.toLowerCase().replace(' ', '-')} id={`brand-${index + 1}`} /><label htmlFor={`brand-${index + 1}`}>{brand}</label></div>)}</div>
                </div>
                <div className="widget">
                  <h5 className="title">Filter by Price</h5>
                  <div className="widget-body">
                    <div className="price-range d-flex flex-wrap mb-4">
                      <div className="widget-check-group"><input type="checkbox" className="form-check-input" id="priceCheck" /><label className="form-check-label" htmlFor="priceCheck">Price Filter</label></div>
                      <div className="input-group"><input type="number" step="any" className="form-control" min="0" name="min_price" value="12.00" readOnly disabled /><input type="number" step="any" className="form-control" min="0" name="max_price" value="240.00" readOnly disabled /></div>
                    </div>
                    <div id="slider-range" />
                  </div>
                </div>
                <div className="widget">
                  <h5 className="title">Size</h5>
                  <div className="widget-body product-size-filter">
                    {['S', 'M', 'L', 'XL'].map((size) => <div className="widget-check-group" key={size}><input className="form-check-input attributes" type="checkbox" value={size} id={`attr-${size}`} /><label className="form-check-label" htmlFor={`attr-${size}`}>{size}</label></div>)}
                  </div>
                </div>
                <div className="widget">
                  <h5 className="title">Ratings</h5>
                  <div className="widget-body product-filter__card"><ul className="list">
                    {[5, 4, 3, 2, 1].map((rating) => <li key={rating}><div className="widget-check-group"><input className="form-check-input rating" name="rating" value={rating} type="radio" id={`${rating}-star`} /><label className="form-check-label" htmlFor={`${rating}-star`}><span className="list list--row rating-list">{'★'.repeat(rating)}{'☆'.repeat(5 - rating)} {rating < 5 && <span className="rating-up-text">and Up</span>}</span></label></div></li>)}
                  </ul></div>
                </div>
              </form>
            </aside>
            <div className="products-container w-100">
              <div className="filter-category-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div className="d-flex gap-2 align-items-center flex-grow-1 flex-md-grow-0 justify-content-between">
                  <div className="filter-select-item d-xl-none pl-0"><button className="filter-in flex-shrink-0" onClick={() => setFilterOpen(true)}><span aria-hidden="true">☷</span> Filter</button></div>
                  <span className="small text-muted flex-shrink-0">Total <span className="fw-semibold totalProducts">24</span> products found</span>
                </div>
                <div className="d-flex align-items-center gap-3 filter-select-right flex-grow-1">
                  <div className="d-flex gap-2 justify-content-between">
                    <div className="filter-select-item d-flex gap-1 align-items-center"><p className="mb-0">Sort By :</p><div className="select-item"><select className="form--control form--select" defaultValue=""><option value="">Default</option><option value="price_htl">Price Hight to Low</option><option value="price_lth">Price Low to High</option><option value="latest">Latest</option><option value="oldest">Oldest</option></select></div></div>
                    <div className="filter-select-item d-flex gap-1 align-items-center"><p className="mb-0">Show :</p><div className="select-item"><select className="form--control form--select" defaultValue="24">{[4, 8, 16, 24, 32, 64, 80, 100].map((number) => <option value={number} key={number}>{number}</option>)}</select></div></div>
                  </div>
                  <div className="filter-select-item"><ul className="view-style d-flex gap-2 align-items-center"><li><a href="#" className="active view-grid-style" aria-label="Grid view">▦</a></li><li><a href="#" className="view-list-style" aria-label="List view">☰</a></li></ul></div>
                </div>
              </div>
              <div className="position-relative">
                <div id="overlay" /><div className="overlay-2" id="overlay2" />
                <div className="page-main-content">
                  <div className="row g-3" id="grid-view">
                    {[0, 1, 2, 3, 4, 5, 6, 7].map((item) => <div className="col-6 grid-control col-md-4 col-lg-3" key={item}><div className="catalog-product-tile"><ProductCard /></div></div>)}
                  </div>
                  <div className="mt-4 d-flex justify-content-end pagination-wrapper"><ul className="pagination-wrapper d-flex gap-2"><li><a className="page-link" href="#">1</a></li><li><a className="page-link" href="#">2</a></li><li><a className="page-link" href="#">→</a></li></ul></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  );
}
