"use client";

import { useState } from "react";
import Link from "next/link";
import { Search, ShoppingCart, User, Bell, Menu, X } from "lucide-react";
import { useCartStore } from "@/stores/cart-store";

const hotKeywords = ["iPhone 16", "Ốp lưng", "Sạc nhanh", "Tai nghe", "Kính cường lực"];

export default function Header() {
  const [searchQuery, setSearchQuery] = useState("");
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const totalItems = useCartStore((state) => state.getTotalItems());

  return (
    <header className="sticky top-0 z-50 bg-gradient-to-b from-[#EE4D2D] to-[#D73211] shadow-md">
      {/* Top bar - hidden on mobile */}
      <div className="hidden md:block bg-[#D73211]/80 text-white text-xs">
        <div className="max-w-7xl mx-auto px-4 py-1 flex justify-between items-center">
          <div className="flex gap-4">
            <span>Miễn phí vận chuyển cho đơn từ 150k</span>
            <span>|</span>
            <Link href="/download" className="hover:text-orange-200">Tải ứng dụng</Link>
          </div>
          <div className="flex gap-4 items-center">
            <Link href="/notifications" className="hover:text-orange-200 flex items-center gap-1">
              <Bell size={12} /> Thông báo
            </Link>
            <Link href="/account" className="hover:text-orange-200">Đăng nhập</Link>
            <span>|</span>
            <Link href="/account/register" className="hover:text-orange-200">Đăng ký</Link>
          </div>
        </div>
      </div>

      {/* Main header */}
      <div className="max-w-7xl mx-auto px-3 md:px-4 py-2 md:py-3">
        <div className="flex items-center gap-2 md:gap-4">
          {/* Mobile menu button */}
          <button
            className="md:hidden text-white p-1"
            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
          >
            {mobileMenuOpen ? <X size={24} /> : <Menu size={24} />}
          </button>

          {/* Logo */}
          <Link href="/" className="flex-shrink-0">
            <h1 className="text-white font-bold text-lg md:text-2xl whitespace-nowrap">
              Phụ Kiện<span className="hidden sm:inline"> Hạt Dẻ</span>
            </h1>
          </Link>

          {/* Search bar */}
          <div className="flex-1 max-w-2xl">
            <div className="relative">
              <input
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="Tìm sản phẩm phụ kiện điện thoại..."
                className="w-full px-3 md:px-4 py-2 md:py-2.5 rounded-sm text-sm bg-white text-gray-800 placeholder-gray-400 focus:outline-none"
              />
              <button className="absolute right-0 top-0 h-full px-3 md:px-5 bg-[#FB6132] hover:bg-[#EE4D2D] rounded-r-sm flex items-center">
                <Search size={18} className="text-white" />
              </button>
            </div>
            {/* Hot keywords - hidden on mobile */}
            <div className="hidden md:flex gap-3 mt-1.5">
              {hotKeywords.map((keyword) => (
                <Link
                  key={keyword}
                  href={`/products?q=${keyword}`}
                  className="text-white text-xs hover:text-orange-200 whitespace-nowrap"
                >
                  {keyword}
                </Link>
              ))}
            </div>
          </div>

          {/* Cart */}
          <Link href="/cart" className="relative p-2 text-white hover:text-orange-200">
            <ShoppingCart size={24} />
            {totalItems > 0 && (
              <span className="absolute -top-1 -right-1 bg-white text-[#EE4D2D] text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center border-2 border-[#EE4D2D]">
                {totalItems > 99 ? "99+" : totalItems}
              </span>
            )}
          </Link>

          {/* User - hidden on mobile */}
          <Link href="/account" className="hidden md:block p-2 text-white hover:text-orange-200">
            <User size={24} />
          </Link>
        </div>
      </div>

      {/* Mobile menu */}
      {mobileMenuOpen && (
        <div className="md:hidden bg-white border-t shadow-lg absolute w-full left-0">
          <nav className="px-4 py-3 space-y-3">
            <Link href="/account" className="flex items-center gap-2 text-gray-700 py-2">
              <User size={20} /> Tài khoản
            </Link>
            <Link href="/notifications" className="flex items-center gap-2 text-gray-700 py-2">
              <Bell size={20} /> Thông báo
            </Link>
            <hr />
            <div className="flex flex-wrap gap-2 py-2">
              {hotKeywords.map((keyword) => (
                <Link
                  key={keyword}
                  href={`/products?q=${keyword}`}
                  className="px-3 py-1 bg-gray-100 rounded-full text-sm text-gray-600"
                  onClick={() => setMobileMenuOpen(false)}
                >
                  {keyword}
                </Link>
              ))}
            </div>
          </nav>
        </div>
      )}
    </header>
  );
}
