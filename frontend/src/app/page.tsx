import BannerCarousel from "@/components/home/BannerCarousel";
import CategoryGrid from "@/components/home/CategoryGrid";
import FlashSale from "@/components/home/FlashSale";
import ProductGrid from "@/components/home/ProductGrid";
import { banners, categories, products } from "@/lib/mock-data";

export default function HomePage() {
  const bestSellers = [...products].sort((a, b) => b.shopeeSold - a.shopeeSold);

  return (
    <div className="max-w-7xl mx-auto px-0 md:px-4 py-0 md:py-4 space-y-2 md:space-y-4">
      {/* Banner */}
      <BannerCarousel banners={banners} />

      {/* Categories */}
      <CategoryGrid categories={categories} />

      {/* Flash Sale */}
      <FlashSale products={products} />

      {/* Best Sellers */}
      <ProductGrid title="Sản Phẩm Bán Chạy" products={bestSellers.slice(0, 8)} />

      {/* Daily Recommendations */}
      <ProductGrid title="Gợi Ý Hôm Nay" products={products} />
    </div>
  );
}
