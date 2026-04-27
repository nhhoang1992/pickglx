import Link from "next/link";
import { requireUser } from "@/lib/auth-helpers";
import { prisma } from "@/lib/prisma";
import { formatVnd } from "@/lib/utils";
import { Plus, Calendar, MapPin } from "lucide-react";

export const metadata = { title: "Quản lý sân & lịch" };
export const dynamic = "force-dynamic";

export default async function ManageCourtsPage() {
  const user = await requireUser();
  const courts = await prisma.court.findMany({
    where: { ownerUserId: user.id },
    include: {
      schedules: {
        orderBy: { date: "desc" },
        take: 5,
        include: { _count: { select: { registrations: { where: { status: "PENDING" } } } } },
      },
      _count: { select: { schedules: true } },
    },
    orderBy: { createdAt: "desc" },
  });

  const myRegs = await prisma.ticketRegistration.findMany({
    where: { userId: user.id },
    include: { schedule: { include: { court: { select: { name: true } } } } },
    orderBy: { createdAt: "desc" },
    take: 10,
  });

  return (
    <div className="mx-auto max-w-5xl px-4 py-10">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-ink">Quản lý sân &amp; lịch xé vé</h1>
        <Link href="/dashboard/lich-xe-ve/court/new" className="btn-primary">
          <Plus className="h-4 w-4" /> Tạo sân
        </Link>
      </div>

      <h2 className="mt-8 font-semibold text-ink">Sân của tôi ({courts.length})</h2>
      {courts.length === 0 ? (
        <p className="mt-3 text-sm text-ink/60">Chưa có sân nào. Bấm &quot;Tạo sân&quot; để bắt đầu.</p>
      ) : (
        <div className="mt-3 space-y-3">
          {courts.map((c) => {
            const pending = c.schedules.reduce((s, sch) => s + sch._count.registrations, 0);
            return (
              <div key={c.id} className="card">
                <div className="flex items-start justify-between gap-3">
                  <div>
                    <h3 className="font-semibold text-ink">{c.name}</h3>
                    {c.address && (
                      <p className="mt-1 flex items-center gap-1 text-xs text-ink/60">
                        <MapPin className="h-3.5 w-3.5" /> {c.address}
                      </p>
                    )}
                    <p className="mt-1 text-xs text-ink/60">{c._count.schedules} lịch · {pending} đăng ký chờ duyệt</p>
                  </div>
                  <Link href={`/dashboard/lich-xe-ve/court/${c.id}/schedule/new`} className="btn-primary text-xs">
                    + Tạo lịch
                  </Link>
                </div>
                {c.schedules.length > 0 && (
                  <ul className="mt-3 divide-y divide-brand-100">
                    {c.schedules.map((s) => (
                      <li key={s.id} className="flex items-center justify-between py-2 text-sm">
                        <Link href={`/dashboard/lich-xe-ve/schedule/${s.id}`} className="flex items-center gap-2 text-ink hover:text-brand">
                          <Calendar className="h-4 w-4" />
                          {new Date(s.date).toLocaleDateString("vi-VN")} · {s.startTime}
                          <span className="text-xs text-ink/60">({s.totalSlots - s.slotsLeft}/{s.totalSlots})</span>
                        </Link>
                        {s._count.registrations > 0 ? (
                          <span className="badge bg-accent-100 text-accent-600">{s._count.registrations} chờ duyệt</span>
                        ) : (
                          <span className="text-xs text-ink/50">{s.status}</span>
                        )}
                      </li>
                    ))}
                  </ul>
                )}
              </div>
            );
          })}
        </div>
      )}

      <h2 className="mt-10 font-semibold text-ink">Đăng ký của tôi</h2>
      {myRegs.length === 0 ? (
        <p className="mt-3 text-sm text-ink/60">Chưa có đăng ký nào.</p>
      ) : (
        <ul className="mt-3 space-y-2">
          {myRegs.map((r) => (
            <li key={r.id} className="card flex items-center justify-between py-3">
              <Link href={`/lich-xe-ve/${r.scheduleId}`} className="text-sm text-ink hover:text-brand">
                {r.schedule.court.name} · {new Date(r.schedule.date).toLocaleDateString("vi-VN")} · {r.slots} slot · {formatVnd(r.amount)}
              </Link>
              <span className={`badge ${
                r.status === "CONFIRMED" ? "bg-brand-100 text-brand-700" :
                r.status === "PENDING" ? "bg-accent-100 text-accent-600" :
                "bg-red-100 text-red-700"}`}>
                {r.status === "CONFIRMED" && "Đã xác nhận"}
                {r.status === "PENDING" && "Chờ duyệt"}
                {r.status === "REJECTED" && "Bị từ chối"}
                {r.status === "CANCELLED" && "Đã huỷ"}
              </span>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
