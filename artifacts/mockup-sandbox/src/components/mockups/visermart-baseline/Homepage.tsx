import './_group.css';
import { Header, ProductCard } from './_shared/Storefront';

const homeCategories = ['Fashion', 'Home & Living', 'Electronics', 'Beauty'];
const additionalCategories = ['Fashion', 'Home & Living', 'Electronics', 'Beauty', 'Accessories', 'Sports'];

export function Homepage() {
  return (
    <div className="vm-preview min-h-screen">
      <Header />
      <main>
        <div className="container banner-container">
          <div className="hero-section mb-60">
            <div className="hero-slider">
              <div className="slider-wrapper overflow-hidden rounded--5">
                <div className="banner-slider owl-theme owl-carousel">
                  <div className="slide-item"><a href="#" className="d-block"><img src="/__mockup/images/visermart/home-banner.png" alt="Fashion sale" /></a></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <section className="home-catalog py-5">
          <div className="container">
            <div className="home-category-intro">
              <div><span className="home-shelf-eyebrow">Find your next favourite</span><h2>A world of good things</h2></div>
              <p>Browse by collection, then take your time with the details.</p>
            </div>
            <div className="home-category-feature-grid">
              {homeCategories.map((category) => (
                <a className="home-category-feature" href="#" key={category}>
                  <span className="home-category-feature-art" aria-hidden="true" />
                  <span className="home-category-feature-label"><span>{category}</span><span aria-hidden="true">↗</span></span>
                </a>
              ))}
            </div>
            <nav className="home-category-links" aria-label="Shop by category">
              {additionalCategories.map((category) => <a href="#" key={category}>{category}</a>)}
            </nav>
            {homeCategories.map((category) => (
              <section className="home-product-shelf" key={category} aria-labelledby={`home-products-${category.replaceAll(' ', '-')}-title`}>
                <div className="home-shelf-heading">
                  <div><span className="home-shelf-eyebrow">Explore the collection</span><h2 id={`home-products-${category.replaceAll(' ', '-')}-title`}>{category}</h2></div>
                  <div className="home-shelf-actions">
                    <a className="home-view-all" href="#">Shop all <span aria-hidden="true">→</span></a>
                    <button type="button" className="home-carousel-control" aria-label="Scroll products left">‹</button>
                    <button type="button" className="home-carousel-control" aria-label="Scroll products right">›</button>
                  </div>
                </div>
                <div className="home-product-track" tabIndex={0} aria-label={`${category} products`}>
                  {[0, 1, 2, 3].map((item) => <article className="home-product-slide" key={item}><div className="catalog-product-tile"><ProductCard /></div></article>)}
                </div>
              </section>
            ))}
          </div>
        </section>
        <section className="site-section my-60">
          <div className="container">
            <div className="section-header left-style"><h3 className="title">Latest Products</h3></div>
            <div className="row gy-4">
              {[0, 1, 2, 3].map((item) => <div className="col-6 col-md-4 col-xl-3" key={item}><div className="catalog-product-tile"><ProductCard /></div></div>)}
            </div>
          </div>
        </section>
      </main>
    </div>
  );
}
