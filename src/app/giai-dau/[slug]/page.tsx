import { notFound } from "next/navigation";
import Link from "next/link";
import { prisma } from "@/lib/prisma";
import { auth } from "@/auth";
import { computeStandings } from "@/lib/round-robin";
import { Trophy, Calendar, MapPin, Users } from "lucide-react";
import { formatVnd } from "@/lib/utils";

export const dynamic = "force-dynamic";

export async function generateMetadata({ params }: { params: { slug: string } }) {
  const t = await prisma.tournament.findUnique({ where: { slug: params.slug }, select: { name: true } });
  return { title: t ? t.name : "Giải đấu" };
}

export default async function TournamentDetailPage({ params }: { params: { slug: string } }) {
  const t = await prisma.tournament.findUnique({
    where: { slug: params.slug },
    include: {
      club: { select: { name: true, slug: true } },
      participants: { orderBy: { createdAt: "asc" } },
      matches: {
        orderBy: [{ round: "asc" }, { createdAt: "asc" }],
      },
    },
  });
  if (!t) notFound();

  const session = await auth();
  const isOwner = session?.user?.id === t.createdById || session?.user?.isAdmin;
  const standings = computeStandings(t.participants.map((p) => ({ id: p.id })), t.matches);
  const pairsById = new Map(t.participants.map((p) => [p.id, p]));

  const myParticipant = session?.user?.id
    ? t.participants.find(
        (p) => p.player1UserId === session.user.id || p.player2UserId === session.user.id,
      )
    : null;

  return (
    <div className="mx-auto max-w-5xl px-4 py-10">
      <Link href="/giai-dau" className="text-sm text-brand hover:underline">← Tất cả giải</Link>

      <div className="card mt-4">
        <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
          <div className="flex items-start gap-3">
            <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand">
              <Trophy className="h-6 w-6" />
            </div>
            <div>
              <h1 className="text-2xl font-bold text-ink md:text-3xl">{t.name}</h1>
              {t.club && (
                <p className="mt-1 text-sm text-ink/70">
                  Tổ chức bởi <Link href={`/clb/${t.club.slug}`} className="text-brand">{t.club.name}</Link>
                </p>
              )}
              <div className="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-ink/70">
                {t.startDate && (
                  <span className="flex items-center gap-1">
                    <Calendar className="h-3.5 w-3.5" /> {new Date(t.startDate).toLocaleDateString("vi-VN")}
                  </span>
                )}
                {t.location && (
                  <span className="flex items-center gap-1">
                    <MapPin className="h-3.5 w-3.5" /> {t.location}
                  </span>
                )}
                {t.fee > 0 && <span>Phí: {formatVnd(t.fee)}</span>}
                <span className="flex items-center gap-1">
                  <Users className="h-3.5 w-3.5" /> {t.participants.length} cặp
                </span>
              </div>
            </div>
          </div>
          <div className="flex flex-col gap-2 md:items-end">
            <span className={`badge self-start md:self-end ${
              t.status === "OPEN" ? "bg-accent-100 text-accent-600" :
              t.status === "ONGOING" ? "bg-brand-100 text-brand-700" :
              t.status === "FINISHED" ? "bg-bg text-ink/60" :
              "bg-bg text-ink/40"}`}>
              {t.status === "DRAFT" && "Nháp"}
              {t.status === "OPEN" && "Mở đăng ký"}
              {t.status === "ONGOING" && "Đang diễn ra"}
              {t.status === "FINISHED" && "Đã kết thúc"}
            </span>
            {isOwner && (
              <Link href={`/dashboard/giai-dau/${t.slug}`} className="btn-primary text-xs">Quản lý giải</Link>
            )}
            {t.status === "OPEN" && !myParticipant && (
              <Link href={`/giai-dau/${t.slug}/register`} className="btn-accent text-xs">
                Đăng ký cặp đôi
              </Link>
            )}
            {myParticipant && (
              <span className="badge bg-brand-100 text-brand-700">
                Bạn đã đăng ký: {myParticipant.player1Name} / {myParticipant.player2Name}
              </span>
            )}
          </div>
        </div>
      </div>

      {standings.length > 0 && t.matches.length > 0 && (
        <div className="card mt-6">
          <h2 className="font-semibold text-ink">Bảng xếp hạng</h2>
          <div className="mt-3 overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="text-left text-xs text-ink/60">
                  <th className="py-2">#</th>
                  <th>Cặp</th>
                  <th className="text-center">Trận</th>
                  <th className="text-center">Thắng</th>
                  <th className="text-center">Thua</th>
                  <th className="text-center">+/-</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-brand-100">
                {standings.map((s, i) => {
                  const p = pairsById.get(s.pairId);
                  return (
                    <tr key={s.pairId}>
                      <td className="py-2 font-semibold text-ink">{i + 1}</td>
                      <td className="text-ink">{p?.player1Name} / {p?.player2Name}</td>
                      <td className="text-center text-ink/80">{s.played}</td>
                      <td className="text-center font-semibold text-brand">{s.won}</td>
                      <td className="text-center text-ink/60">{s.lost}</td>
                      <td className="text-center text-ink/80">{s.diff > 0 ? "+" : ""}{s.diff}</td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {t.matches.length > 0 && (
        <div className="card mt-6">
          <h2 className="font-semibold text-ink">Lịch & kết quả</h2>
          <div className="mt-3 space-y-3">
            {Array.from(new Set(t.matches.map((m) => m.round)))
              .sort((a, b) => a - b)
              .map((round) => (
                <div key={round}>
                  <p className="text-xs uppercase text-ink/50">Vòng {round}</p>
                  <ul className="mt-1 space-y-1">
                    {t.matches.filter((m) => m.round === round).map((m) => {
                      const a = pairsById.get(m.pairAId);
                      const b = pairsById.get(m.pairBId);
                      return (
                        <li key={m.id} className="flex items-center justify-between rounded-lg border border-brand-100 px-3 py-2 text-sm">
                          <span className="text-ink">
                            <span className={m.scoreA !== null && m.scoreB !== null && m.scoreA > m.scoreB ? "font-semibold text-brand" : ""}>
                              {a?.player1Name} / {a?.player2Name}
                            </span>
                            {" vs "}
                            <span className={m.scoreA !== null && m.scoreB !== null && m.scoreB > m.scoreA ? "font-semibold text-brand" : ""}>
                              {b?.player1Name} / {b?.player2Name}
                            </span>
                          </span>
                          {m.status === "FINISHED" && m.scoreA !== null && m.scoreB !== null ? (
                            <span className="font-semibold text-ink">{m.scoreA} – {m.scoreB}</span>
                          ) : (
                            <span className="text-xs text-ink/50">Chưa đấu</span>
                          )}
                        </li>
                      );
                    })}
                  </ul>
                </div>
              ))}
          </div>
        </div>
      )}

      {t.participants.length > 0 && (
        <div className="card mt-6">
          <h2 className="font-semibold text-ink">Danh sách cặp đôi ({t.participants.length})</h2>
          <ul className="mt-3 grid gap-2 sm:grid-cols-2">
            {t.participants.map((p, i) => (
              <li key={p.id} className="rounded-lg border border-brand-100 px-3 py-2 text-sm text-ink">
                <span className="text-xs text-ink/50">#{i + 1}</span> {p.player1Name} / {p.player2Name}
              </li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );
}
