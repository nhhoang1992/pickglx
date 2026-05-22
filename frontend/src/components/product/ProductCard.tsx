import Image from "next/image";
import Link from "next/link";
import { Star } from "lucide-react";
import { Product } from "@/types";
import { formatPrice, formatSold } from "@/lib/mock-data";

interface ProductCardProps {
  product: Product;
  isFlashSale?: boolean;
}

export default function ProductCard({ product, isFlashSale }: ProductCardProps) {
  const displayPrice = isFlashSale && product.flashSalePrice
    ? product.flashSalePrice
    : product.price;
  const displayDiscount = isFlashSale && product.flashSalePrice
    ? Math.round((1 - product.flashSalePrice / product.originalPrice) * 100)
    : product.discount;

  return (
    <Link
      href={`/product/${product.slug}`}
      className="block bg-white rounded-sm overflow-hidden border border-gray-100 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 h-full"
    >
      {/* Image */}
      <div className="relative aspect-square">
        <Image
          src={product.images[0]}
          alt={product.name}
          fill
          className="object-cover"
          sizes="(max-width: 768px) 50vw, 200px"
        />
        {/* Discount badge */}
        {displayDiscount > 0 && (
          <div className="absolute top-0 right-0 bg-[#EE4D2D] text-white text-[10px] md:text-xs font-bold px-1.5 py-0.5 rounded-bl">
            -{displayDiscount}%
          </div>
        )}
        {/* Flash sale progress bar */}
        {isFlashSale && (
          <div className="absolute bottom-0 left-0 right-0 bg-[#EE4D2D]/90 px-2 py-1">
            <div className="relative h-3.5 bg-[#FF8B70] rounded-full overflow-hidden">
              <div
                className="absolute inset-y-0 left-0 bg-white/30 rounded-full"
                style={{
                  width: `${Math.min((product.sold / (product.stock + product.sold)) * 100, 90)}%`,
                }}
              />
              <span className="absolute inset-0 flex items-center justify-center text-[9px] text-white font-bold">
                Đã bán {formatSold(product.shopeeSold)}
              </span>
            </div>
          </div>
        )}
      </div>

      {/* Info */}
      <div className="p-2 md:p-2.5">
        {/* Product name */}
        <h3 className="text-xs md:text-sm text-gray-800 line-clamp-2 leading-tight min-h-[32px] md:min-h-[36px]">
          {product.name}
        </h3>

        {/* Price */}
        <div className="mt-1.5 flex items-baseline gap-1.5">
          <span className="text-[#EE4D2D] font-bold text-sm md:text-base">
            {formatPrice(displayPrice)}
          </span>
          {product.originalPrice > displayPrice && (
            <span className="text-gray-400 text-[10px] md:text-xs line-through">
              {formatPrice(product.originalPrice)}
            </span>
          )}
        </div>

        {/* Rating & Sold - Shopee style */}
        {!isFlashSale && (
          <div className="mt-1.5 flex items-center justify-between text-[10px] md:text-xs text-gray-500">
            <div className="flex items-center gap-0.5">
              <Star size={10} className="fill-yellow-400 text-yellow-400" />
              <span>{product.shopeeRating}</span>
            </div>
            <span>Đã bán {formatSold(product.shopeeSold)}</span>
          </div>
        )}
      </div>
    </Link>
  );
}
