import Link from "next/link";
import { Trophy, Users, Calendar, Wallet } from "lucide-react";

const features = [
  {
    icon: Trophy,
    title: "Giải đấu",
    desc: "Tổ chức giải vòng tròn, knock-out, Mexicano. Bảng đấu & BXH cập nhật realtime.",
    href: "/giai-dau",
  },
  {
    icon: Wallet,
    title: "Chia tiền sinh hoạt",
    desc: "Chia tiền theo đầu người, số trận, hoặc số giờ chơi. Tự sinh QR VietQR + tick đã chuyển.",
    href: "/chia-tien",
  },
  {
    icon: Calendar,
    title: "Lịch xé vé",
    desc: "Sân đăng khung giờ trống — người chơi đăng ký slot, upload bill, sân duyệt nhanh chóng.",
    href: "/lich-xe-ve",
  },
  {
    icon: Users,
    title: "Quản lý CLB",
    desc: "Đội trưởng duyệt thành viên, quản lý buổi sinh hoạt, quỹ CLB.",
    href: "/clb",
  },
];

export default function Home() {
  return (
    <>
      <section className="relative overflow-hidden">
        <div className="absolute inset-0 -z-10 bg-gradient-to-br from-brand-50 via-bg to-accent-50" />
        <div className="mx-auto max-w-6xl px-4 py-20 md:py-28">
          <div className="max-w-3xl">
            <span className="badge bg-brand-100 text-brand-700">Beta · v0.1</span>
            <h1 className="mt-4 text-4xl font-bold tracking-tight text-ink md:text-6xl">
              Mọi thứ CLB <span className="text-brand">Pickleball</span> cần — trong một app.
            </h1>
            <p className="mt-6 text-lg text-ink/70 md:text-xl">
              Pickglx giúp CLB tổ chức giải đấu, chia tiền sinh hoạt, và xé vé sân
              chỉ với vài cú chạm. Miễn phí cho đến khi cộng đồng đủ lớn.
            </p>
            <div className="mt-8 flex flex-wrap gap-3">
              <Link href="/auth/register" className="btn-primary">
                Bắt đầu — tạo CLB
              </Link>
              <Link href="/giai-dau" className="btn-outline">
                Xem giải đấu công khai
              </Link>
            </div>
          </div>
        </div>
      </section>

      <section className="mx-auto max-w-6xl px-4 py-16">
        <h2 className="text-2xl font-bold text-ink md:text-3xl">Tính năng chính</h2>
        <div className="mt-8 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          {features.map((f) => (
            <Link key={f.href} href={f.href} className="card hover:border-brand-300 transition-colors">
              <f.icon className="h-8 w-8 text-brand" />
              <h3 className="mt-4 text-lg font-semibold text-ink">{f.title}</h3>
              <p className="mt-2 text-sm text-ink/70">{f.desc}</p>
            </Link>
          ))}
        </div>
      </section>

      <section className="mx-auto max-w-6xl px-4 pb-20">
        <div className="card flex flex-col items-start justify-between gap-4 bg-brand text-white md:flex-row md:items-center">
          <div>
            <h3 className="text-xl font-semibold">Bạn là đội trưởng CLB?</h3>
            <p className="mt-1 text-white/80">
              Đăng ký CLB của bạn lên Pickglx — quản lý thành viên, lịch sinh hoạt, và giải đấu chỉ trong vài phút.
            </p>
          </div>
          <Link href="/auth/register" className="btn-accent">
            Tạo CLB miễn phí
          </Link>
        </div>
      </section>
    </>
  );
}
