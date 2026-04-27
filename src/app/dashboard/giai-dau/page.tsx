import Link from "next/link";
import { requireUser } from "@/lib/auth-helpers";
import { prisma } from "@/lib/prisma";
import { Plus, Trophy } from "lucide-react";

export const metadata = { title: "Giải đấu của tôi" };
export const dynamic = "force-dynamic";

export default async function MyTournamentsPage() {
  const user = await requireUser();
  const tournaments = await prisma.tournament.findMany({
    where: { createdById: user.id },
    include: { _count: { select: { participants: true, matches: true } } },
    orderBy: { createdAt: "desc" },
  });
  return (
    <div className="mx-auto max-w-5xl px-4 py-10">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-ink">Giải đấu của tôi</h1>
        <Link href="/dashboard/giai-dau/new" className="btn-primary">
          <Plus className="h-4 w-4" /> Tạo giải
        </Link>
      </div>
      {tournaments.length === 0 ? (
        <div className="card mt-6 text-center">
          <Trophy className="mx-auto h-10 w-10 text-brand" />
          <p className="mt-3 text-ink/70">Chưa tạo giải nào.</p>
        </div>
      ) : (
        <div className="mt-6 grid gap-3 md:grid-cols-2">
          {tournaments.map((t) => (
            <Link key={t.id} href={`/dashboard/giai-dau/${t.slug}`} className="card hover:border-brand-300">
              <h3 className="font-semibold text-ink">{t.name}</h3>
              <p className="mt-1 text-sm text-ink/60">
                {t._count.participants} cặp · {t._count.matches} trận · {t.status}
              </p>
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}
