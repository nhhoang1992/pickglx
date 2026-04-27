import type { Metadata } from "next";
import { Inter } from "next/font/google";
import "./globals.css";
import { Header } from "@/components/header";
import { Footer } from "@/components/footer";

const inter = Inter({ subsets: ["latin", "vietnamese"], variable: "--font-sans" });

export const metadata: Metadata = {
  title: {
    default: "Pickglx — Cộng đồng Pickleball Việt Nam",
    template: "%s | Pickglx",
  },
  description:
    "Pickglx — nền tảng cho CLB Pickleball: tổ chức giải đấu, chia tiền sinh hoạt, lịch xé vé sân.",
  openGraph: {
    title: "Pickglx",
    description:
      "Tổ chức giải đấu, chia tiền sinh hoạt, lịch xé vé sân Pickleball.",
    type: "website",
    locale: "vi_VN",
  },
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="vi" className={inter.variable}>
      <body className="min-h-screen flex flex-col font-sans antialiased">
        <Header />
        <main className="flex-1">{children}</main>
        <Footer />
      </body>
    </html>
  );
}
