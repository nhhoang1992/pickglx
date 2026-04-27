import Link from "next/link";
import { prisma } from "@/lib/prisma";
import { auth } from "@/auth";
import { formatVnd } from "@/lib/utils";
import { Calendar, MapPin, Plus } from "lucide-react";

export const metadata = { title: "Lịch xé vé" };

export const dynamic = "force-dynamic";

export default async function CourtSchedulesPage() {
  const session = await auth();
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  const schedules = await prisma.courtSchedule.findMany({
    where: {
      status: "OPEN",
      date: { gte: today },
    },
    include: {
      court: { select: { name: true, address: true } },
      _count: { select: { registrations: { where: { status: { in: ["PENDING", "CONFIRMED"] } } } } },
    },
    orderBy: { date: "asc" },
    take: 100,
  });

  return (
    <div className="mx-auto max-w-6xl px-4 py-12">
      <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
          <h1 className="text-3xl font-bold text-ink md:text-4xl">Lịch xé vé sân</h1>
          <p className="mt-2 text-ink/70">
            Đăng ký slot sân pickleball và upload bill chuyển khoản. Chủ sân xác nhận sau khi nhận tiền.
          </p>
        </div>
        {session?.user && (
          <Link href="/dashboard/lich-xe-ve" className="btn-primary self-start md:self-end">
            <Plus className="h-4 w-4" /> Quản lý / tạo sân
          </Link>
        )}
      </div>

      <div className="mt-8 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {schedules.length === 0 ? (
          <div className="card md:col-span-2 lg:col-span-3 text-center text-ink/70">
            Chưa có lịch nào — hãy tạo sân và lịch trên Dashboard!
          </div>
        ) : (
          schedules.map((s) => (
            <Link key={s.id} href={`/lich-xe-ve/${s.id}`} className="card hover:border-brand-300 transition-colors">
              <h3 className="font-semibold text-ink">{s.court.name}</h3>
              {s.court.address && (
                <p className="mt-1 flex items-center gap-1 text-xs text-ink/60">
                  <MapPin className="h-3.5 w-3.5" /> {s.court.address}
                </p>
              )}
              <p className="mt-3 flex items-center gap-1 text-sm text-ink">
                <Calendar className="h-4 w-4 text-brand" />
                {new Date(s.date).toLocaleDateString("vi-VN")} · {s.startTime} ({s.durationHours}h)
              </p>
              <div className="mt-3 flex items-center justify-between">
                <span className="text-sm font-semibold text-brand">{formatVnd(s.pricePerSlot)} / slot</span>
                <span className={`badge ${s.slotsLeft > 0 ? "bg-brand-100 text-brand-700" : "bg-red-100 text-red-700"}`}>
                  Còn {s.slotsLeft}/{s.totalSlots}
                </span>
              </div>
            </Link>
          ))
        )}
      </div>
    </div>
  );
}
