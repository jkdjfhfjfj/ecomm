import React from 'react';
import './_group.css';
import { Header } from './_shared/Storefront';

const addressFields = [
  ['Title', 'label', 'Home'],
  ['First Name', 'firstname', 'Alex'],
  ['Last Name', 'lastname', 'Morgan'],
  ['Mobile', 'mobile', '+1 555 014 2080'],
  ['Email', 'email', 'alex@example.com'],
  ['City', 'city', 'Brooklyn'],
  ['State', 'state', 'New York'],
  ['Zip', 'zip', '11201'],
];

export function ShippingAddress() {
  const [addressModalOpen, setAddressModalOpen] = React.useState(false);
  return (
    <div className="vm-preview min-h-screen">
      <Header />
      <main className="container py-60">
        <div className="row justify-content-center">
          <div className="col-xl-10">
            <div className="pb-40">
              <div className="cart-step">
                <div className="row g-2 g-md-3">
                  {[
                    ['🛒', 'My cart'],
                    ['⌖', 'Shipping Info'],
                    ['▱', 'Delivery Info'],
                    ['▣', 'Payment'],
                    ['✓', 'Confirmation'],
                  ].map(([icon, label], index) => (
                    <div className="col" key={label}>
                      <div className={`cart-step-box ${index === 1 ? 'active' : ''}`}><div className="cart-step-icon mb-2"><span className="step-symbol">{icon}</span></div><p className="cart-step-title mb-0">{label}</p></div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
            <h5 className="mb-3 d-block d-sm-none">Shipping Info</h5>
            <div className="address-wrapper">
              <label className="address-single" htmlFor="address-1">
                <div className="flex-fill">
                  <div className="address-item-left">
                    <div className="form--check d-inline-block"><input className="form-check-input mt-0" type="radio" name="shipping_address_id" value="1" form="shipping-form" id="address-1" defaultChecked /></div>
                    <h6>Home</h6>
                  </div>
                  <div className="address-item-right">
                    {[
                      ['Address', '42 Example Street'],
                      ['Zip Code', '11201'],
                      ['City', 'Brooklyn'],
                      ['State', 'New York'],
                      ['Country', 'United States'],
                      ['Phone', '+1 555 014 2080'],
                    ].map(([label, value]) => <div className="address-item-inner" key={label}><span className="address-item-label">{label}</span><span className="address-item-value"><span className="item-devide">:</span> {value}</span></div>)}
                  </div>
                </div>
                <div className="position-relative text-end"><button type="button" data-resource="address-1" className="btn btn-outline--light editAddress" onClick={() => setAddressModalOpen(true)}>✎ <span className="d-none d-sm-inline">Change</span></button></div>
              </label>
              <label className="address-single" htmlFor="address-2">
                <div className="flex-fill">
                  <div className="address-item-left">
                    <div className="form--check d-inline-block"><input className="form-check-input mt-0" type="radio" name="shipping_address_id" value="2" form="shipping-form" id="address-2" /></div>
                    <h6>Office</h6>
                  </div>
                  <div className="address-item-right">
                    {[
                      ['Address', '18 Market Street'],
                      ['Zip Code', '10013'],
                      ['City', 'New York'],
                      ['State', 'New York'],
                      ['Country', 'United States'],
                      ['Phone', '+1 555 018 4012'],
                    ].map(([label, value]) => <div className="address-item-inner" key={label}><span className="address-item-label">{label}</span><span className="address-item-value"><span className="item-devide">:</span> {value}</span></div>)}
                  </div>
                </div>
                <div className="position-relative text-end"><button type="button" data-resource="address-2" className="btn btn-outline--light editAddress" onClick={() => setAddressModalOpen(true)}>✎ <span className="d-none d-sm-inline">Change</span></button></div>
              </label>
              <button className="newAddress w-100 address-single-add-new" type="button" onClick={() => setAddressModalOpen(true)}><span aria-hidden="true">⊕</span><span className="add-new-title d-block">Add New Address</span></button>
            </div>
            <div className="d-flex align-items-center justify-content-between flex-wrap mt-4">
              <a href="#" className="text--base">‹ Back to Cart</a>
              <form action="#" method="POST" id="shipping-form"><button type="submit" className="btn btn--base h-45">Continue to Next <span aria-hidden="true">›</span></button></form>
            </div>
          </div>
        </div>
      </main>
      {addressModalOpen && (
        <div className="address-modal-layer" role="presentation" onMouseDown={(event) => { if (event.target === event.currentTarget) setAddressModalOpen(false); }}>
          <div className="modal custom--modal show" id="addressModal" role="dialog" aria-modal="true" aria-labelledby="addressModalTitle">
            <div className="modal-dialog modal-lg modal-dialog-centered">
              <div className="modal-content"><div className="modal-body">
                <h5 className="modal-title" id="addressModalTitle">Add New Shipping Address</h5>
                <button type="button" className="close modal-close-btn" onClick={() => setAddressModalOpen(false)} aria-label="Close">×</button>
                <form method="POST" action="#">
                  <div className="row">
                    {addressFields.map(([label, name, value], index) => (
                      <div className={index === 0 ? 'col-md-12' : 'col-md-6'} key={name}><div className="form-group"><label htmlFor={`address-${name}`}>{label}</label><input id={`address-${name}`} type="text" className="form-control form--control" name={name} defaultValue={value} required /></div></div>
                    ))}
                    <div className="col-md-6"><div className="form-group"><label htmlFor="address-country">Country</label><select id="address-country" name="country" className="form-control form--control form-select"><option>United States</option><option>Canada</option></select></div></div>
                    <div className="col-md-12"><div className="form-group mb-0"><label htmlFor="address-line">Address</label><input id="address-line" type="text" className="form-control form--control" name="address" defaultValue="42 Example Street" autoComplete="shipping street-address" aria-autocomplete="list" required /></div></div>
                  </div>
                  <div className="col-12 mt-3"><button type="submit" className="btn btn--base w-100 h-45">Submit</button></div>
                </form>
              </div></div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
