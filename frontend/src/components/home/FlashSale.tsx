"use client";

import { useState, useEffect } from "react";
import Link from "next/link";
import { Zap } from "lucide-react";
import { Product } from "@/types";
import ProductCard from "@/components/product/ProductCard";

interface FlashSaleProps {
  products: Product[];
}

export default function FlashSale({ products }: FlashSaleProps) {
  const [timeLeft, setTimeLeft] = useState({ hours: 0, minutes: 0, seconds: 0 });

  useEffect(() => {
    const calculateTimeLeft = () => {
      const now = new Date();
      const endOfDay = new Date(now);
      endOfDay.setHours(23, 59, 59, 999);
      const diff = endOfDay.getTime() - now.getTime();

      return {
        hours: Math.floor(diff / (1000 * 60 * 60)),
        minutes: Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)),
        seconds: Math.floor((diff % (1000 * 60)) / 1000),
      };
    };

    setTimeLeft(calculateTimeLeft());
    const timer = setInterval(() => {
      setTimeLeft(calculateTimeLeft());
    }, 1000);

    return () => clearInterval(timer);
  }, []);

  const flashSaleProducts = products.filter((p) => p.isFlashSale);
  if (flashSaleProducts.length === 0) return null;

  return (
    <section className="bg-white rounded-sm md:rounded-md overflow-hidden">
      {/* Header */}
      <div className="flex items-center justify-between px-3 md:px-4 py-2.5 bg-gradient-to-r from-[#EE4D2D] to-[#FF6633]">
        <div className="flex items-center gap-2">
          <Zap size={18} className="text-yellow-300 fill-yellow-300" />
          <span className="text-white font-bold text-sm md:text-base uppercase tracking-wide">
            Flash Sale
          </span>
        </div>
        <div className="flex items-center gap-1">
          <TimeBox value={timeLeft.hours} />
          <span className="text-white font-bold">:</span>
          <TimeBox value={timeLeft.minutes} />
          <span className="text-white font-bold">:</span>
          <TimeBox value={timeLeft.seconds} />
        </div>
        <Link
          href="/products?sale=true"
          className="text-white text-xs hover:text-orange-200"
        >
          Xem tất cả &gt;
        </Link>
      </div>

      {/* Products scroll */}
      <div className="overflow-x-auto scrollbar-hide">
        <div className="flex gap-2 p-3 md:p-4 min-w-min">
          {flashSaleProducts.map((product) => (
            <div key={product.id} className="w-[140px] md:w-[180px] flex-shrink-0">
              <ProductCard product={product} isFlashSale />
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}

function TimeBox({ value }: { value: number }) {
  return (
    <span className="bg-black/80 text-white text-xs font-mono font-bold px-1.5 py-0.5 rounded min-w-[24px] text-center">
      {value.toString().padStart(2, "0")}
    </span>
  );
}
