"use client";

import { useState } from "react";
import Image from "next/image";
import Link from "next/link";
import { MapPin, Truck, CreditCard, CheckCircle2 } from "lucide-react";
import { useCartStore } from "@/stores/cart-store";
import { formatPrice } from "@/lib/mock-data";

export default function CheckoutPage() {
  const { items, getTotalPrice, clearCart } = useCartStore();
  const selectedItems = items.filter((item) => item.selected);
  const [orderPlaced, setOrderPlaced] = useState(false);
  const [paymentMethod, setPaymentMethod] = useState("cod");

  const shippingFee = getTotalPrice() >= 150000 ? 0 : 30000;
  const total = getTotalPrice() + shippingFee;

  const handlePlaceOrder = () => {
    setOrderPlaced(true);
    clearCart();
  };

  if (orderPlaced) {
    return (
      <div className="max-w-lg mx-auto px-4 py-12 text-center">
        <CheckCircle2 size={64} className="mx-auto text-green-500 mb-4" />
        <h1 className="text-xl font-bold text-gray-800 mb-2">
          Đặt hàng thành công!
        </h1>
        <p className="text-gray-600 mb-6">
          Cảm ơn bạn đã mua hàng. Đơn hàng của bạn đang được xử lý.
        </p>
        <Link
          href="/"
          className="inline-block px-6 py-2.5 bg-[#EE4D2D] text-white rounded-sm hover:bg-[#D73211]"
        >
          Tiếp tục mua sắm
        </Link>
      </div>
    );
  }

  if (selectedItems.length === 0) {
    return (
      <div className="max-w-lg mx-auto px-4 py-12 text-center">
        <p className="text-gray-500">Không có sản phẩm để thanh toán</p>
        <Link href="/cart" className="text-[#EE4D2D] mt-2 inline-block">
          Quay lại giỏ hàng
        </Link>
      </div>
    );
  }

  return (
    <div className="max-w-4xl mx-auto px-0 md:px-4 py-0 md:py-4 space-y-2 md:space-y-3">
      {/* Shipping address */}
      <div className="bg-white md:rounded-md p-4">
        <div className="flex items-center gap-2 text-[#EE4D2D] mb-3">
          <MapPin size={18} />
          <h2 className="font-medium text-base">Địa Chỉ Nhận Hàng</h2>
        </div>
        <div className="border-2 border-dashed border-gray-300 rounded p-4 text-center">
          <p className="text-sm text-gray-500">
            Vui lòng nhập địa chỉ nhận hàng
          </p>
          <div className="mt-3 space-y-2">
            <input
              type="text"
              placeholder="Họ và tên"
              className="w-full px-3 py-2 border rounded text-sm focus:border-[#EE4D2D] outline-none"
            />
            <input
              type="tel"
              placeholder="Số điện thoại"
              className="w-full px-3 py-2 border rounded text-sm focus:border-[#EE4D2D] outline-none"
            />
            <input
              type="text"
              placeholder="Địa chỉ chi tiết (số nhà, đường, phường/xã, quận/huyện, tỉnh/thành)"
              className="w-full px-3 py-2 border rounded text-sm focus:border-[#EE4D2D] outline-none"
            />
          </div>
        </div>
      </div>

      {/* Order items */}
      <div className="bg-white md:rounded-md p-4">
        <h2 className="font-medium text-base mb-3">Sản Phẩm</h2>
        <div className="space-y-3">
          {selectedItems.map((item) => {
            const price = item.product.flashSalePrice || item.product.price;
            return (
              <div key={item.id} className="flex gap-3 items-start">
                <Image
                  src={item.product.images[0]}
                  alt={item.product.name}
                  width={64}
                  height={64}
                  className="rounded object-cover"
                />
                <div className="flex-1 min-w-0">
                  <p className="text-sm line-clamp-2">{item.product.name}</p>
                  {item.variant && (
                    <p className="text-xs text-gray-500">
                      Phân loại: {item.variant}
                    </p>
                  )}
                  <p className="text-xs text-gray-500">x{item.quantity}</p>
                </div>
                <span className="text-[#EE4D2D] font-medium text-sm whitespace-nowrap">
                  {formatPrice(price * item.quantity)}
                </span>
              </div>
            );
          })}
        </div>
      </div>

      {/* Shipping method */}
      <div className="bg-white md:rounded-md p-4">
        <div className="flex items-center gap-2 mb-3">
          <Truck size={18} className="text-green-600" />
          <h2 className="font-medium text-base">Phương Thức Vận Chuyển</h2>
        </div>
        <div className="flex justify-between items-center p-3 border rounded bg-green-50/50">
          <div>
            <p className="text-sm font-medium">Giao hàng tiêu chuẩn</p>
            <p className="text-xs text-gray-500">Nhận hàng trong 3-5 ngày</p>
          </div>
          <span className="text-sm">
            {shippingFee === 0 ? (
              <span className="text-green-600 font-medium">Miễn phí</span>
            ) : (
              formatPrice(shippingFee)
            )}
          </span>
        </div>
      </div>

      {/* Payment method */}
      <div className="bg-white md:rounded-md p-4">
        <div className="flex items-center gap-2 mb-3">
          <CreditCard size={18} className="text-blue-600" />
          <h2 className="font-medium text-base">Phương Thức Thanh Toán</h2>
        </div>
        <div className="space-y-2">
          {[
            { id: "cod", label: "Thanh toán khi nhận hàng (COD)", icon: "💵" },
            { id: "banking", label: "Chuyển khoản ngân hàng", icon: "🏦" },
            { id: "momo", label: "Ví MoMo", icon: "📱" },
            { id: "vnpay", label: "VNPay", icon: "💳" },
          ].map((method) => (
            <label
              key={method.id}
              className={`flex items-center gap-3 p-3 border rounded cursor-pointer ${
                paymentMethod === method.id
                  ? "border-[#EE4D2D] bg-orange-50"
                  : "border-gray-200 hover:border-gray-300"
              }`}
            >
              <input
                type="radio"
                name="payment"
                value={method.id}
                checked={paymentMethod === method.id}
                onChange={(e) => setPaymentMethod(e.target.value)}
                className="accent-[#EE4D2D]"
              />
              <span className="text-lg">{method.icon}</span>
              <span className="text-sm">{method.label}</span>
            </label>
          ))}
        </div>
      </div>

      {/* Order summary & Place order */}
      <div className="bg-white md:rounded-md p-4 sticky bottom-16 md:bottom-0 border-t md:border-t-0">
        <div className="space-y-1.5 text-sm mb-3">
          <div className="flex justify-between">
            <span className="text-gray-600">Tổng tiền hàng:</span>
            <span>{formatPrice(getTotalPrice())}</span>
          </div>
          <div className="flex justify-between">
            <span className="text-gray-600">Phí vận chuyển:</span>
            <span>
              {shippingFee === 0 ? (
                <span className="text-green-600">Miễn phí</span>
              ) : (
                formatPrice(shippingFee)
              )}
            </span>
          </div>
          <div className="flex justify-between pt-2 border-t font-bold">
            <span>Tổng thanh toán:</span>
            <span className="text-[#EE4D2D] text-lg">{formatPrice(total)}</span>
          </div>
        </div>
        <button
          onClick={handlePlaceOrder}
          className="w-full py-3 bg-[#EE4D2D] text-white font-medium rounded-sm hover:bg-[#D73211]"
        >
          Đặt Hàng
        </button>
      </div>
    </div>
  );
}
