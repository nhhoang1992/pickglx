import { notFound, redirect } from "next/navigation";
import Link from "next/link";
import { requireUser } from "@/lib/auth-helpers";
import { prisma } from "@/lib/prisma";
import { StartButton, FinishButton } from "./controls";
import { ScoreEntry } from "./score-entry";

export const metadata = { title: "Quản lý giải" };
export const dynamic = "force-dynamic";

export default async function ManageTournamentPage({ params }: { params: { slug: string } }) {
  const user = await requireUser();
  const t = await prisma.tournament.findUnique({
    where: { slug: params.slug },
    include: {
      participants: { orderBy: { createdAt: "asc" } },
      matches: { orderBy: [{ round: "asc" }, { createdAt: "asc" }] },
    },
  });
  if (!t) notFound();
  if (t.createdById !== user.id && !user.isAdmin) redirect(`/giai-dau/${t.slug}`);

  const pairsById = new Map(t.participants.map((p) => [p.id, p]));

  return (
    <div className="mx-auto max-w-5xl px-4 py-10">
      <Link href={`/giai-dau/${t.slug}`} className="text-sm text-brand hover:underline">← Trang công khai của giải</Link>
      <div className="card mt-4">
        <div className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
          <div>
            <h1 className="text-2xl font-bold text-ink">{t.name}</h1>
            <p className="mt-1 text-sm text-ink/70">Trạng thái: {t.status} · {t.participants.length} cặp · {t.matches.length} trận</p>
          </div>
          <div className="flex gap-2">
            {t.status === "OPEN" && <StartButton tournamentId={t.id} canStart={t.participants.length >= 2} />}
            {t.status === "ONGOING" && <FinishButton tournamentId={t.id} />}
          </div>
        </div>
      </div>

      <h2 className="mt-8 font-semibold text-ink">Cặp đăng ký ({t.participants.length})</h2>
      {t.participants.length === 0 ? (
        <p className="mt-3 text-sm text-ink/60">Chưa có cặp nào đăng ký.</p>
      ) : (
        <ul className="mt-3 grid gap-2 sm:grid-cols-2">
          {t.participants.map((p, i) => (
            <li key={p.id} className="rounded-lg border border-brand-100 bg-white px-3 py-2 text-sm text-ink">
              <span className="text-xs text-ink/50">#{i + 1}</span> {p.player1Name} / {p.player2Name}
              <span className="ml-2 text-xs text-ink/50">{p.phone}</span>
            </li>
          ))}
        </ul>
      )}

      {t.matches.length > 0 && (
        <div className="mt-8">
          <h2 className="font-semibold text-ink">Lịch & nhập kết quả</h2>
          {Array.from(new Set(t.matches.map((m) => m.round)))
            .sort((a, b) => a - b)
            .map((round) => (
              <div key={round} className="mt-3">
                <p className="text-xs uppercase text-ink/50">Vòng {round}</p>
                <ul className="mt-1 space-y-2">
                  {t.matches.filter((m) => m.round === round).map((m) => {
                    const a = pairsById.get(m.pairAId);
                    const b = pairsById.get(m.pairBId);
                    return (
                      <li key={m.id} className="card flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div className="text-sm text-ink">
                          {a?.player1Name} / {a?.player2Name}
                          <span className="mx-2 text-ink/40">vs</span>
                          {b?.player1Name} / {b?.player2Name}
                        </div>
                        <ScoreEntry
                          tournamentId={t.id}
                          matchId={m.id}
                          scoreA={m.scoreA}
                          scoreB={m.scoreB}
                          finished={m.status === "FINISHED"}
                        />
                      </li>
                    );
                  })}
                </ul>
              </div>
            ))}
        </div>
      )}
    </div>
  );
}
