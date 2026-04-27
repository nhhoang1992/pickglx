import Link from "next/link";
import { prisma } from "@/lib/prisma";
import { auth } from "@/auth";
import { Trophy, Calendar, Plus } from "lucide-react";

export const metadata = { title: "Giải đấu" };
export const dynamic = "force-dynamic";

export default async function TournamentsPage() {
  const session = await auth();
  const tournaments = await prisma.tournament.findMany({
    where: { status: { in: ["OPEN", "ONGOING", "FINISHED"] } },
    include: {
      club: { select: { name: true, slug: true } },
      _count: { select: { participants: true } },
    },
    orderBy: { createdAt: "desc" },
    take: 60,
  });

  return (
    <div className="mx-auto max-w-6xl px-4 py-12">
      <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
          <h1 className="text-3xl font-bold text-ink md:text-4xl">Giải đấu Pickleball</h1>
          <p className="mt-2 text-ink/70">
            Tham gia hoặc tổ chức giải đấu round-robin với tính năng tự động sinh lịch + BXH realtime.
          </p>
        </div>
        {session?.user && (
          <Link href="/dashboard/giai-dau/new" className="btn-primary self-start md:self-end">
            <Plus className="h-4 w-4" /> Tạo giải mới
          </Link>
        )}
      </div>

      <div className="mt-8 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {tournaments.length === 0 ? (
          <div className="card md:col-span-2 lg:col-span-3 text-center text-ink/70">
            Chưa có giải nào — hãy tạo giải đầu tiên!
          </div>
        ) : (
          tournaments.map((t) => (
            <Link key={t.id} href={`/giai-dau/${t.slug}`} className="card hover:border-brand-300">
              <div className="flex items-start gap-3">
                <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand">
                  <Trophy className="h-6 w-6" />
                </div>
                <div className="min-w-0">
                  <h3 className="truncate font-semibold text-ink">{t.name}</h3>
                  {t.club && <p className="mt-1 text-xs text-ink/60">{t.club.name}</p>}
                  {t.startDate && (
                    <p className="mt-1 flex items-center gap-1 text-xs text-ink/60">
                      <Calendar className="h-3.5 w-3.5" />
                      {new Date(t.startDate).toLocaleDateString("vi-VN")}
                    </p>
                  )}
                  <div className="mt-2 flex items-center gap-2">
                    <span className={`badge ${
                      t.status === "OPEN" ? "bg-accent-100 text-accent-600" :
                      t.status === "ONGOING" ? "bg-brand-100 text-brand-700" :
                      "bg-bg text-ink/60"}`}>
                      {t.status === "OPEN" && "Mở đăng ký"}
                      {t.status === "ONGOING" && "Đang diễn ra"}
                      {t.status === "FINISHED" && "Đã kết thúc"}
                    </span>
                    <span className="text-xs text-ink/60">{t._count.participants} cặp</span>
                  </div>
                </div>
              </div>
            </Link>
          ))
        )}
      </div>
    </div>
  );
}
