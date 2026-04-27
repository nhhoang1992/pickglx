import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { requireUser } from "@/lib/auth-helpers";
import { generateRoundRobin } from "@/lib/round-robin";

export async function POST(_req: Request, { params }: { params: { id: string } }) {
  const user = await requireUser();
  const t = await prisma.tournament.findUnique({
    where: { id: params.id },
    include: { participants: true, matches: true },
  });
  if (!t) return NextResponse.json({ error: "Không tìm thấy giải" }, { status: 404 });
  if (t.createdById !== user.id && !user.isAdmin) {
    return NextResponse.json({ error: "Bạn không phải BTC" }, { status: 403 });
  }
  if (t.status !== "OPEN") {
    return NextResponse.json({ error: "Giải không ở trạng thái mở đăng ký" }, { status: 409 });
  }
  if (t.participants.length < 2) {
    return NextResponse.json({ error: "Cần ít nhất 2 cặp" }, { status: 400 });
  }
  if (t.matches.length > 0) {
    return NextResponse.json({ error: "Đã có lịch thi đấu" }, { status: 409 });
  }

  // Shuffle for fairness
  const ids = [...t.participants].map((p) => p.id);
  for (let i = ids.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [ids[i], ids[j]] = [ids[j], ids[i]];
  }
  const schedule = generateRoundRobin(ids);

  await prisma.$transaction([
    prisma.match.createMany({
      data: schedule.map((m) => ({
        tournamentId: t.id,
        round: m.round,
        pairAId: m.pairAId,
        pairBId: m.pairBId,
      })),
    }),
    prisma.tournament.update({
      where: { id: t.id },
      data: { status: "ONGOING" },
    }),
  ]);

  return NextResponse.json({ ok: true, matches: schedule.length });
}
