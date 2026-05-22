import type { Metadata, Viewport } from "next";
import "./globals.css";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import BottomNav from "@/components/layout/BottomNav";

export const metadata: Metadata = {
  title: "Phụ Kiện Hạt Dẻ - Phụ kiện điện thoại chính hãng giá tốt",
  description:
    "Chuyên cung cấp phụ kiện điện thoại: kính cường lực, ốp lưng, sạc nhanh, cáp sạc, tai nghe. Giá tốt nhất - Bảo hành 1 đổi 1 - Giao hàng toàn quốc.",
  keywords: [
    "phụ kiện điện thoại",
    "kính cường lực",
    "ốp lưng",
    "sạc nhanh",
    "cáp sạc",
    "tai nghe",
  ],
};

export const viewport: Viewport = {
  width: "device-width",
  initialScale: 1,
  maximumScale: 1,
  themeColor: "#EE4D2D",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="vi" className="h-full">
      <body className="min-h-full flex flex-col bg-[#f5f5f5]">
        <Header />
        <main className="flex-1 pb-16 md:pb-0">{children}</main>
        <Footer />
        <BottomNav />
      </body>
    </html>
  );
}
