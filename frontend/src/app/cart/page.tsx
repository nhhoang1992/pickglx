"use client";

import Image from "next/image";
import Link from "next/link";
import { Minus, Plus, Trash2, ShoppingCart } from "lucide-react";
import { useCartStore } from "@/stores/cart-store";
import { formatPrice } from "@/lib/mock-data";

export default function CartPage() {
  const { items, removeItem, updateQuantity, toggleSelect, selectAll, getTotalPrice } =
    useCartStore();

  const allSelected = items.length > 0 && items.every((item) => item.selected);
  const selectedCount = items.filter((item) => item.selected).length;

  if (items.length === 0) {
    return (
      <div className="max-w-7xl mx-auto px-4 py-12 text-center">
        <ShoppingCart size={64} className="mx-auto text-gray-300 mb-4" />
        <p className="text-gray-500 text-lg mb-2">Giỏ hàng trống</p>
        <p className="text-gray-400 text-sm mb-4">
          Hãy thêm sản phẩm vào giỏ hàng nhé!
        </p>
        <Link
          href="/products"
          className="inline-block px-6 py-2.5 bg-[#EE4D2D] text-white rounded-sm hover:bg-[#D73211] text-sm"
        >
          Tiếp tục mua sắm
        </Link>
      </div>
    );
  }

  return (
    <div className="max-w-7xl mx-auto px-0 md:px-4 py-0 md:py-4">
      {/* Header - desktop */}
      <div className="hidden md:grid grid-cols-12 gap-4 bg-white rounded-md p-4 mb-3 text-sm text-gray-600">
        <div className="col-span-6 flex items-center gap-3">
          <input
            type="checkbox"
            checked={allSelected}
            onChange={(e) => selectAll(e.target.checked)}
            className="w-4 h-4 accent-[#EE4D2D]"
          />
          <span>Sản Phẩm</span>
        </div>
        <div className="col-span-2 text-center">Đơn Giá</div>
        <div className="col-span-2 text-center">Số Lượng</div>
        <div className="col-span-1 text-center">Thành Tiền</div>
        <div className="col-span-1 text-center">Thao Tác</div>
      </div>

      {/* Cart items */}
      <div className="space-y-2">
        {items.map((item) => {
          const price = item.product.flashSalePrice || item.product.price;
          return (
            <div
              key={item.id}
              className="bg-white md:rounded-md p-3 md:p-4"
            >
              <div className="flex items-start gap-3">
                {/* Checkbox */}
                <input
                  type="checkbox"
                  checked={item.selected}
                  onChange={() => toggleSelect(item.id)}
                  className="w-4 h-4 mt-4 accent-[#EE4D2D] flex-shrink-0"
                />

                {/* Product image */}
                <div className="w-20 h-20 md:w-24 md:h-24 flex-shrink-0 rounded overflow-hidden">
                  <Image
                    src={item.product.images[0]}
                    alt={item.product.name}
                    width={96}
                    height={96}
                    className="object-cover w-full h-full"
                  />
                </div>

                {/* Product info */}
                <div className="flex-1 min-w-0">
                  <Link
                    href={`/product/${item.product.slug}`}
                    className="text-sm text-gray-800 line-clamp-2 hover:text-[#EE4D2D]"
                  >
                    {item.product.name}
                  </Link>
                  {item.variant && (
                    <p className="text-xs text-gray-500 mt-0.5">
                      Phân loại: {item.variant}
                    </p>
                  )}

                  {/* Mobile price & quantity */}
                  <div className="md:hidden mt-2 flex items-center justify-between">
                    <span className="text-[#EE4D2D] font-medium text-sm">
                      {formatPrice(price)}
                    </span>
                    <div className="flex items-center border rounded">
                      <button
                        onClick={() =>
                          updateQuantity(item.id, item.quantity - 1)
                        }
                        className="w-7 h-7 flex items-center justify-center"
                      >
                        <Minus size={12} />
                      </button>
                      <span className="w-8 text-center text-sm">
                        {item.quantity}
                      </span>
                      <button
                        onClick={() =>
                          updateQuantity(item.id, item.quantity + 1)
                        }
                        className="w-7 h-7 flex items-center justify-center"
                      >
                        <Plus size={12} />
                      </button>
                    </div>
                  </div>
                </div>

                {/* Desktop: price, quantity, total, delete */}
                <div className="hidden md:flex items-center gap-8">
                  <span className="text-[#EE4D2D] font-medium w-24 text-center">
                    {formatPrice(price)}
                  </span>
                  <div className="flex items-center border rounded">
                    <button
                      onClick={() =>
                        updateQuantity(item.id, item.quantity - 1)
                      }
                      className="w-7 h-7 flex items-center justify-center hover:bg-gray-50"
                    >
                      <Minus size={12} />
                    </button>
                    <span className="w-10 text-center text-sm">
                      {item.quantity}
                    </span>
                    <button
                      onClick={() =>
                        updateQuantity(item.id, item.quantity + 1)
                      }
                      className="w-7 h-7 flex items-center justify-center hover:bg-gray-50"
                    >
                      <Plus size={12} />
                    </button>
                  </div>
                  <span className="text-[#EE4D2D] font-bold w-28 text-center">
                    {formatPrice(price * item.quantity)}
                  </span>
                  <button
                    onClick={() => removeItem(item.id)}
                    className="text-gray-400 hover:text-red-500 p-1"
                  >
                    <Trash2 size={16} />
                  </button>
                </div>

                {/* Mobile delete */}
                <button
                  onClick={() => removeItem(item.id)}
                  className="md:hidden text-gray-400 p-1"
                >
                  <Trash2 size={16} />
                </button>
              </div>
            </div>
          );
        })}
      </div>

      {/* Checkout bar - sticky bottom */}
      <div className="sticky bottom-16 md:bottom-0 bg-white border-t mt-3 p-3 md:p-4 md:rounded-md flex items-center justify-between">
        <div className="flex items-center gap-3">
          <input
            type="checkbox"
            checked={allSelected}
            onChange={(e) => selectAll(e.target.checked)}
            className="w-4 h-4 accent-[#EE4D2D]"
          />
          <span className="text-sm">Chọn Tất Cả ({items.length})</span>
        </div>
        <div className="flex items-center gap-3 md:gap-5">
          <div className="text-right">
            <span className="text-sm text-gray-600 hidden md:inline">
              Tổng thanh toán ({selectedCount} Sản phẩm):{" "}
            </span>
            <span className="text-[#EE4D2D] font-bold text-lg md:text-xl">
              {formatPrice(getTotalPrice())}
            </span>
          </div>
          <Link
            href="/checkout"
            className={`px-5 md:px-8 py-2.5 rounded-sm text-sm font-medium ${
              selectedCount > 0
                ? "bg-[#EE4D2D] text-white hover:bg-[#D73211]"
                : "bg-gray-300 text-gray-500 cursor-not-allowed"
            }`}
          >
            Mua Hàng
          </Link>
        </div>
      </div>
    </div>
  );
}
