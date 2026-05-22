"use client";

import { useState } from "react";
import { SlidersHorizontal, Grid3X3, List } from "lucide-react";
import ProductCard from "@/components/product/ProductCard";
import { products, categories } from "@/lib/mock-data";

const sortOptions = [
  { value: "popular", label: "Phổ Biến" },
  { value: "newest", label: "Mới Nhất" },
  { value: "best-selling", label: "Bán Chạy" },
  { value: "price-asc", label: "Giá Thấp → Cao" },
  { value: "price-desc", label: "Giá Cao → Thấp" },
];

export default function ProductsPage() {
  const [sortBy, setSortBy] = useState("popular");
  const [viewMode, setViewMode] = useState<"grid" | "list">("grid");
  const [showFilter, setShowFilter] = useState(false);
  const [selectedCategory, setSelectedCategory] = useState<string | null>(null);

  const filteredProducts = selectedCategory
    ? products.filter((p) => p.categoryId === selectedCategory)
    : products;

  const sortedProducts = [...filteredProducts].sort((a, b) => {
    switch (sortBy) {
      case "newest":
        return new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime();
      case "best-selling":
        return b.shopeeSold - a.shopeeSold;
      case "price-asc":
        return a.price - b.price;
      case "price-desc":
        return b.price - a.price;
      default:
        return b.sold - a.sold;
    }
  });

  return (
    <div className="max-w-7xl mx-auto px-0 md:px-4 py-0 md:py-4">
      <div className="flex gap-4">
        {/* Sidebar filters - desktop */}
        <aside className="hidden md:block w-52 flex-shrink-0">
          <div className="bg-white rounded-md p-4 sticky top-20">
            <h3 className="font-bold text-sm mb-3">Danh Mục</h3>
            <ul className="space-y-1.5">
              <li>
                <button
                  onClick={() => setSelectedCategory(null)}
                  className={`text-sm w-full text-left px-2 py-1.5 rounded ${
                    !selectedCategory
                      ? "text-[#EE4D2D] font-medium bg-orange-50"
                      : "text-gray-600 hover:text-[#EE4D2D]"
                  }`}
                >
                  Tất cả sản phẩm
                </button>
              </li>
              {categories.map((cat) => (
                <li key={cat.id}>
                  <button
                    onClick={() => setSelectedCategory(cat.id)}
                    className={`text-sm w-full text-left px-2 py-1.5 rounded ${
                      selectedCategory === cat.id
                        ? "text-[#EE4D2D] font-medium bg-orange-50"
                        : "text-gray-600 hover:text-[#EE4D2D]"
                    }`}
                  >
                    {cat.icon} {cat.name}
                  </button>
                </li>
              ))}
            </ul>
          </div>
        </aside>

        {/* Main content */}
        <div className="flex-1">
          {/* Sort & Filter bar */}
          <div className="bg-white p-2.5 md:p-3 rounded-sm md:rounded-md mb-2 md:mb-3 sticky top-[52px] md:top-[88px] z-10">
            <div className="flex items-center justify-between gap-2">
              {/* Sort options */}
              <div className="flex items-center gap-1 md:gap-2 overflow-x-auto scrollbar-hide">
                <span className="hidden md:inline text-sm text-gray-500 mr-1">Sắp xếp:</span>
                {sortOptions.map((option) => (
                  <button
                    key={option.value}
                    onClick={() => setSortBy(option.value)}
                    className={`px-2.5 md:px-3 py-1.5 text-xs md:text-sm rounded-sm whitespace-nowrap ${
                      sortBy === option.value
                        ? "bg-[#EE4D2D] text-white"
                        : "bg-gray-100 text-gray-700 hover:bg-gray-200"
                    }`}
                  >
                    {option.label}
                  </button>
                ))}
              </div>

              {/* View mode & filter toggle */}
              <div className="flex items-center gap-1.5">
                <button
                  onClick={() => setShowFilter(!showFilter)}
                  className="md:hidden p-2 rounded bg-gray-100"
                >
                  <SlidersHorizontal size={16} />
                </button>
                <button
                  onClick={() => setViewMode("grid")}
                  className={`hidden md:block p-1.5 rounded ${
                    viewMode === "grid" ? "bg-[#EE4D2D] text-white" : "bg-gray-100"
                  }`}
                >
                  <Grid3X3 size={16} />
                </button>
                <button
                  onClick={() => setViewMode("list")}
                  className={`hidden md:block p-1.5 rounded ${
                    viewMode === "list" ? "bg-[#EE4D2D] text-white" : "bg-gray-100"
                  }`}
                >
                  <List size={16} />
                </button>
              </div>
            </div>
          </div>

          {/* Mobile filter panel */}
          {showFilter && (
            <div className="md:hidden bg-white p-3 rounded-sm mb-2">
              <h3 className="font-bold text-sm mb-2">Danh Mục</h3>
              <div className="flex flex-wrap gap-2">
                <button
                  onClick={() => {
                    setSelectedCategory(null);
                    setShowFilter(false);
                  }}
                  className={`px-3 py-1.5 text-xs rounded-full ${
                    !selectedCategory
                      ? "bg-[#EE4D2D] text-white"
                      : "bg-gray-100 text-gray-700"
                  }`}
                >
                  Tất cả
                </button>
                {categories.map((cat) => (
                  <button
                    key={cat.id}
                    onClick={() => {
                      setSelectedCategory(cat.id);
                      setShowFilter(false);
                    }}
                    className={`px-3 py-1.5 text-xs rounded-full ${
                      selectedCategory === cat.id
                        ? "bg-[#EE4D2D] text-white"
                        : "bg-gray-100 text-gray-700"
                    }`}
                  >
                    {cat.icon} {cat.name}
                  </button>
                ))}
              </div>
            </div>
          )}

          {/* Products grid */}
          <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-[1px] md:gap-2 bg-gray-100 md:bg-transparent">
            {sortedProducts.map((product) => (
              <ProductCard key={product.id} product={product} />
            ))}
          </div>

          {/* No products */}
          {sortedProducts.length === 0 && (
            <div className="text-center py-12 text-gray-500">
              <p>Không tìm thấy sản phẩm nào</p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
