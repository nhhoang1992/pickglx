"use client";

import { useState } from "react";
import Image from "next/image";
import Link from "next/link";
import { useParams } from "next/navigation";
import {
  Star,
  ShoppingCart,
  Minus,
  Plus,
  Truck,
  Shield,
  RotateCcw,
  ChevronLeft,
  Heart,
  Share2,
} from "lucide-react";
import { products, formatPrice, formatSold } from "@/lib/mock-data";
import { useCartStore } from "@/stores/cart-store";
import ProductCard from "@/components/product/ProductCard";

export default function ProductDetailPage() {
  const params = useParams();
  const slug = params.slug as string;
  const product = products.find((p) => p.slug === slug);

  const [selectedImage, setSelectedImage] = useState(0);
  const [selectedVariant, setSelectedVariant] = useState<string | null>(null);
  const [quantity, setQuantity] = useState(1);
  const [activeTab, setActiveTab] = useState<"description" | "reviews">("description");
  const addItem = useCartStore((state) => state.addItem);

  if (!product) {
    return (
      <div className="max-w-7xl mx-auto px-4 py-12 text-center">
        <p className="text-gray-500">Sản phẩm không tồn tại</p>
        <Link href="/" className="text-[#EE4D2D] mt-2 inline-block">
          Quay về trang chủ
        </Link>
      </div>
    );
  }

  const displayPrice = product.flashSalePrice || product.price;
  const relatedProducts = products.filter(
    (p) => p.categoryId === product.categoryId && p.id !== product.id
  );

  const handleAddToCart = () => {
    addItem(product, selectedVariant || undefined, quantity);
  };

  const handleBuyNow = () => {
    addItem(product, selectedVariant || undefined, quantity);
    window.location.href = "/cart";
  };

  return (
    <div className="max-w-7xl mx-auto px-0 md:px-4 py-0 md:py-4">
      {/* Mobile back button */}
      <div className="md:hidden sticky top-0 z-20 bg-white border-b px-3 py-2 flex items-center justify-between">
        <Link href="/" className="p-1">
          <ChevronLeft size={24} />
        </Link>
        <div className="flex gap-3">
          <button className="p-1"><Share2 size={20} /></button>
          <Link href="/cart" className="p-1"><ShoppingCart size={20} /></Link>
        </div>
      </div>

      {/* Product main section */}
      <div className="bg-white md:rounded-md overflow-hidden">
        <div className="md:flex">
          {/* Image gallery */}
          <div className="md:w-[400px] lg:w-[480px] flex-shrink-0">
            {/* Main image */}
            <div className="relative aspect-square">
              <Image
                src={product.images[selectedImage]}
                alt={product.name}
                fill
                className="object-cover"
                priority
              />
              {/* Discount badge */}
              {product.discount > 0 && (
                <div className="absolute top-3 right-3 bg-[#EE4D2D] text-white text-sm font-bold px-2 py-1 rounded">
                  -{product.discount}%
                </div>
              )}
            </div>
            {/* Thumbnails */}
            <div className="flex gap-1 p-2 overflow-x-auto">
              {product.images.map((img, index) => (
                <button
                  key={index}
                  onClick={() => setSelectedImage(index)}
                  className={`w-16 h-16 flex-shrink-0 rounded border-2 overflow-hidden ${
                    selectedImage === index
                      ? "border-[#EE4D2D]"
                      : "border-transparent"
                  }`}
                >
                  <Image
                    src={img}
                    alt={`${product.name} ${index + 1}`}
                    width={64}
                    height={64}
                    className="object-cover w-full h-full"
                  />
                </button>
              ))}
            </div>
          </div>

          {/* Product info */}
          <div className="flex-1 p-3 md:p-5">
            {/* Title */}
            <h1 className="text-base md:text-xl font-medium text-gray-800 leading-snug">
              {product.name}
            </h1>

            {/* Rating & Sold - Shopee style */}
            <div className="flex items-center gap-4 mt-2 pb-3 border-b">
              <div className="flex items-center gap-1">
                <span className="text-[#EE4D2D] font-medium underline">
                  {product.shopeeRating}
                </span>
                <div className="flex">
                  {Array.from({ length: 5 }).map((_, i) => (
                    <Star
                      key={i}
                      size={14}
                      className={
                        i < Math.floor(product.shopeeRating)
                          ? "fill-[#EE4D2D] text-[#EE4D2D]"
                          : "fill-gray-300 text-gray-300"
                      }
                    />
                  ))}
                </div>
              </div>
              <span className="text-gray-300">|</span>
              <span className="text-sm text-gray-600">
                <span className="font-medium underline">
                  {product.reviewCount.toLocaleString()}
                </span>{" "}
                Đánh Giá
              </span>
              <span className="text-gray-300">|</span>
              <span className="text-sm text-gray-600">
                {formatSold(product.shopeeSold)} Đã Bán
              </span>
            </div>

            {/* Price */}
            <div className="bg-[#FAFAFA] p-3 md:p-4 mt-3 rounded">
              <div className="flex items-baseline gap-3">
                {product.originalPrice > displayPrice && (
                  <span className="text-gray-400 line-through text-sm md:text-base">
                    {formatPrice(product.originalPrice)}
                  </span>
                )}
                <span className="text-[#EE4D2D] font-bold text-xl md:text-3xl">
                  {formatPrice(displayPrice)}
                </span>
                {product.discount > 0 && (
                  <span className="bg-[#EE4D2D] text-white text-xs font-bold px-1.5 py-0.5 rounded">
                    -{product.discount}% GIẢM
                  </span>
                )}
              </div>
            </div>

            {/* Shipping info */}
            <div className="mt-4 space-y-2">
              <div className="flex items-center gap-3 text-sm">
                <Truck size={16} className="text-gray-400" />
                <span className="text-gray-600">Miễn phí vận chuyển cho đơn từ 150.000₫</span>
              </div>
              <div className="flex items-center gap-3 text-sm">
                <Shield size={16} className="text-gray-400" />
                <span className="text-gray-600">Bảo hành 1 đổi 1 trong 30 ngày</span>
              </div>
              <div className="flex items-center gap-3 text-sm">
                <RotateCcw size={16} className="text-gray-400" />
                <span className="text-gray-600">Trả hàng miễn phí 15 ngày</span>
              </div>
            </div>

            {/* Variants */}
            {product.variants.length > 0 && (
              <div className="mt-4">
                {product.variants.map((variant) => (
                  <div key={variant.id} className="mb-3">
                    <span className="text-sm text-gray-600 block mb-2">
                      {variant.name}:
                    </span>
                    <div className="flex flex-wrap gap-2">
                      {variant.options.map((option) => (
                        <button
                          key={option.id}
                          onClick={() => setSelectedVariant(option.value)}
                          className={`px-3 py-1.5 border rounded-sm text-sm ${
                            selectedVariant === option.value
                              ? "border-[#EE4D2D] text-[#EE4D2D] bg-orange-50"
                              : "border-gray-300 text-gray-700 hover:border-[#EE4D2D]"
                          }`}
                        >
                          {option.value}
                        </button>
                      ))}
                    </div>
                  </div>
                ))}
              </div>
            )}

            {/* Quantity */}
            <div className="mt-4 flex items-center gap-3">
              <span className="text-sm text-gray-600">Số Lượng:</span>
              <div className="flex items-center border rounded">
                <button
                  onClick={() => setQuantity(Math.max(1, quantity - 1))}
                  className="w-8 h-8 flex items-center justify-center hover:bg-gray-50"
                >
                  <Minus size={14} />
                </button>
                <input
                  type="number"
                  value={quantity}
                  onChange={(e) =>
                    setQuantity(Math.max(1, parseInt(e.target.value) || 1))
                  }
                  className="w-12 h-8 text-center text-sm border-x outline-none"
                />
                <button
                  onClick={() => setQuantity(quantity + 1)}
                  className="w-8 h-8 flex items-center justify-center hover:bg-gray-50"
                >
                  <Plus size={14} />
                </button>
              </div>
              <span className="text-sm text-gray-500">
                {product.stock} sản phẩm có sẵn
              </span>
            </div>

            {/* Action buttons */}
            <div className="mt-5 flex gap-3">
              <button
                onClick={handleAddToCart}
                className="flex-1 md:flex-none px-6 py-2.5 border-2 border-[#EE4D2D] text-[#EE4D2D] rounded-sm hover:bg-orange-50 flex items-center justify-center gap-2 text-sm font-medium"
              >
                <ShoppingCart size={18} />
                Thêm Vào Giỏ
              </button>
              <button
                onClick={handleBuyNow}
                className="flex-1 md:flex-none px-8 py-2.5 bg-[#EE4D2D] text-white rounded-sm hover:bg-[#D73211] text-sm font-medium"
              >
                Mua Ngay
              </button>
              <button className="p-2.5 border rounded-sm hover:bg-gray-50">
                <Heart size={18} className="text-gray-500" />
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Description & Reviews tabs */}
      <div className="bg-white md:rounded-md mt-2 md:mt-4 overflow-hidden">
        {/* Tab header */}
        <div className="flex border-b">
          <button
            onClick={() => setActiveTab("description")}
            className={`flex-1 md:flex-none px-6 py-3 text-sm font-medium ${
              activeTab === "description"
                ? "text-[#EE4D2D] border-b-2 border-[#EE4D2D]"
                : "text-gray-600"
            }`}
          >
            Mô Tả Sản Phẩm
          </button>
          <button
            onClick={() => setActiveTab("reviews")}
            className={`flex-1 md:flex-none px-6 py-3 text-sm font-medium ${
              activeTab === "reviews"
                ? "text-[#EE4D2D] border-b-2 border-[#EE4D2D]"
                : "text-gray-600"
            }`}
          >
            Đánh Giá ({product.reviewCount})
          </button>
        </div>

        {/* Tab content */}
        <div className="p-3 md:p-5">
          {activeTab === "description" ? (
            <div>
              {/* Specifications */}
              {Object.keys(product.specifications).length > 0 && (
                <div className="mb-4">
                  <h3 className="font-medium text-base mb-2">Thông Số Kỹ Thuật</h3>
                  <table className="w-full text-sm">
                    <tbody>
                      {Object.entries(product.specifications).map(
                        ([key, value]) => (
                          <tr key={key} className="border-b">
                            <td className="py-2 text-gray-500 w-1/3">{key}</td>
                            <td className="py-2 text-gray-800">{value}</td>
                          </tr>
                        )
                      )}
                    </tbody>
                  </table>
                </div>
              )}
              <h3 className="font-medium text-base mb-2">Mô Tả</h3>
              <p className="text-sm text-gray-700 leading-relaxed whitespace-pre-line">
                {product.description}
              </p>
            </div>
          ) : (
            <div>
              {/* Rating summary */}
              <div className="flex items-center gap-4 p-3 bg-[#FFFBF8] border border-[#EE4D2D]/20 rounded mb-4">
                <div className="text-center">
                  <div className="text-[#EE4D2D] text-2xl font-bold">
                    {product.shopeeRating} <span className="text-base">/ 5</span>
                  </div>
                  <div className="flex mt-1">
                    {Array.from({ length: 5 }).map((_, i) => (
                      <Star
                        key={i}
                        size={16}
                        className="fill-[#EE4D2D] text-[#EE4D2D]"
                      />
                    ))}
                  </div>
                  <p className="text-xs text-gray-500 mt-1">
                    (Đánh giá từ Shopee)
                  </p>
                </div>
              </div>

              {/* Reviews list */}
              <div className="space-y-4">
                {product.shopeeReviews.map((review) => (
                  <div key={review.id} className="border-b pb-4">
                    <div className="flex items-center gap-2">
                      <div className="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-sm">
                        {review.userName[0]}
                      </div>
                      <div>
                        <p className="text-sm font-medium">{review.userName}</p>
                        <div className="flex">
                          {Array.from({ length: 5 }).map((_, i) => (
                            <Star
                              key={i}
                              size={12}
                              className={
                                i < review.rating
                                  ? "fill-yellow-400 text-yellow-400"
                                  : "fill-gray-300 text-gray-300"
                              }
                            />
                          ))}
                        </div>
                      </div>
                    </div>
                    {review.variant && (
                      <p className="text-xs text-gray-500 mt-1">
                        Phân loại: {review.variant}
                      </p>
                    )}
                    <p className="text-sm text-gray-700 mt-2">{review.comment}</p>
                    <p className="text-xs text-gray-400 mt-1">
                      {new Date(review.createdAt).toLocaleDateString("vi-VN")}
                    </p>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      </div>

      {/* Related products */}
      {relatedProducts.length > 0 && (
        <div className="bg-white md:rounded-md mt-2 md:mt-4 p-3 md:p-4">
          <h2 className="text-sm md:text-base font-bold text-gray-800 mb-3 uppercase">
            Sản Phẩm Tương Tự
          </h2>
          <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
            {relatedProducts.slice(0, 6).map((p) => (
              <ProductCard key={p.id} product={p} />
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
