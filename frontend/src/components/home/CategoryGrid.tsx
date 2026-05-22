import Link from "next/link";
import { Category } from "@/types";

interface CategoryGridProps {
  categories: Category[];
}

export default function CategoryGrid({ categories }: CategoryGridProps) {
  return (
    <section className="bg-white rounded-sm md:rounded-md p-3 md:p-4">
      <h2 className="text-sm md:text-base font-bold text-gray-800 mb-3 uppercase">
        Danh Mục
      </h2>
      <div className="grid grid-cols-4 md:grid-cols-8 gap-2 md:gap-3">
        {categories.map((category) => (
          <Link
            key={category.id}
            href={`/products?category=${category.slug}`}
            className="flex flex-col items-center gap-1.5 p-2 rounded-md hover:bg-orange-50 transition-colors"
          >
            <span className="text-2xl md:text-3xl">{category.icon}</span>
            <span className="text-[11px] md:text-xs text-center text-gray-700 leading-tight line-clamp-2">
              {category.name}
            </span>
          </Link>
        ))}
      </div>
    </section>
  );
}
