"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { Home, Grid3X3, ShoppingCart, User, Bell } from "lucide-react";
import { useCartStore } from "@/stores/cart-store";

const navItems = [
  { href: "/", icon: Home, label: "Trang chủ" },
  { href: "/products", icon: Grid3X3, label: "Danh mục" },
  { href: "/cart", icon: ShoppingCart, label: "Giỏ hàng", badge: true },
  { href: "/notifications", icon: Bell, label: "Thông báo" },
  { href: "/account", icon: User, label: "Tôi" },
];

export default function BottomNav() {
  const pathname = usePathname();
  const totalItems = useCartStore((state) => state.getTotalItems());

  return (
    <nav className="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t z-50 safe-area-bottom">
      <div className="flex items-center justify-around py-1.5">
        {navItems.map((item) => {
          const isActive = pathname === item.href;
          const Icon = item.icon;

          return (
            <Link
              key={item.href}
              href={item.href}
              className={`flex flex-col items-center gap-0.5 px-3 py-1 relative ${
                isActive ? "text-[#EE4D2D]" : "text-gray-500"
              }`}
            >
              <Icon size={20} />
              <span className="text-[10px]">{item.label}</span>
              {item.badge && totalItems > 0 && (
                <span className="absolute -top-0.5 right-1 bg-[#EE4D2D] text-white text-[9px] font-bold rounded-full min-w-[16px] h-4 flex items-center justify-center px-1">
                  {totalItems > 99 ? "99+" : totalItems}
                </span>
              )}
            </Link>
          );
        })}
      </div>
    </nav>
  );
}
