import './_group.css';
import { MobileBottomNav, MobileCategoryTree } from './_shared/Storefront';

export function CategoryDrawer() {
  return (
    <div className="vm-preview vm-drawer-preview min-h-screen">
      <div className="drawer-backdrop" />
      <MobileCategoryTree drawer />
      <MobileBottomNav />
    </div>
  );
}
