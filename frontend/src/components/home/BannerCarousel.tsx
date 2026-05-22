"use client";

import { useState, useEffect } from "react";
import Image from "next/image";
import Link from "next/link";
import { ChevronLeft, ChevronRight } from "lucide-react";
import { Banner } from "@/types";

interface BannerCarouselProps {
  banners: Banner[];
}

export default function BannerCarousel({ banners }: BannerCarouselProps) {
  const [currentIndex, setCurrentIndex] = useState(0);

  useEffect(() => {
    const timer = setInterval(() => {
      setCurrentIndex((prev) => (prev + 1) % banners.length);
    }, 4000);
    return () => clearInterval(timer);
  }, [banners.length]);

  const goTo = (index: number) => {
    setCurrentIndex(index);
  };

  const goPrev = () => {
    setCurrentIndex((prev) => (prev - 1 + banners.length) % banners.length);
  };

  const goNext = () => {
    setCurrentIndex((prev) => (prev + 1) % banners.length);
  };

  return (
    <div className="relative w-full overflow-hidden rounded-sm md:rounded-md">
      <div
        className="flex transition-transform duration-500 ease-in-out"
        style={{ transform: `translateX(-${currentIndex * 100}%)` }}
      >
        {banners.map((banner) => (
          <div key={banner.id} className="w-full flex-shrink-0">
            <Link href={banner.link || "#"}>
              <div className="relative w-full aspect-[2/1] md:aspect-[3/1]">
                <Image
                  src={banner.image}
                  alt={banner.title || "Banner"}
                  fill
                  className="object-cover hidden md:block"
                  priority
                />
                <Image
                  src={banner.mobileImage || banner.image}
                  alt={banner.title || "Banner"}
                  fill
                  className="object-cover md:hidden"
                  priority
                />
              </div>
            </Link>
          </div>
        ))}
      </div>

      {/* Navigation arrows - hidden on mobile */}
      <button
        onClick={goPrev}
        className="hidden md:flex absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-black/30 hover:bg-black/50 rounded-full items-center justify-center text-white"
      >
        <ChevronLeft size={20} />
      </button>
      <button
        onClick={goNext}
        className="hidden md:flex absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-black/30 hover:bg-black/50 rounded-full items-center justify-center text-white"
      >
        <ChevronRight size={20} />
      </button>

      {/* Dots */}
      <div className="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-1.5">
        {banners.map((_, index) => (
          <button
            key={index}
            onClick={() => goTo(index)}
            className={`w-2 h-2 rounded-full transition-all ${
              index === currentIndex ? "bg-white w-4" : "bg-white/50"
            }`}
          />
        ))}
      </div>
    </div>
  );
}
