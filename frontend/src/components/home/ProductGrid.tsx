import { Product } from "@/types";
import ProductCard from "@/components/product/ProductCard";

interface ProductGridProps {
  title: string;
  products: Product[];
}

export default function ProductGrid({ title, products }: ProductGridProps) {
  return (
    <section className="bg-white rounded-sm md:rounded-md">
      {/* Section header - Shopee style */}
      <div className="border-b-2 border-[#EE4D2D] px-3 md:px-4 py-2.5">
        <h2 className="text-sm md:text-base font-bold text-[#EE4D2D] uppercase text-center">
          {title}
        </h2>
      </div>

      {/* Product grid */}
      <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-[1px] md:gap-2 p-0 md:p-3 bg-gray-100 md:bg-white">
        {products.map((product) => (
          <ProductCard key={product.id} product={product} />
        ))}
      </div>

      {/* See more button */}
      <div className="p-3 text-center">
        <button className="px-8 py-2 border border-[#EE4D2D] text-[#EE4D2D] text-sm hover:bg-orange-50 rounded-sm">
          Xem Thêm
        </button>
      </div>
    </section>
  );
}
