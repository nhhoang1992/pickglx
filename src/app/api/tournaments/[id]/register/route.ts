import { NextResponse } from "next/server";
import { z } from "zod";
import { prisma } from "@/lib/prisma";
import { requireUser } from "@/lib/auth-helpers";

const schema = z.object({
  player1Name: z.string().min(1).max(120),
  player2Name: z.string().min(1).max(120),
  phone: z.string().min(5).max(30),
});

export async function POST(req: Request, { params }: { params: { id: string } }) {
  const user = await requireUser();
  const body = await req.json().catch(() => null);
  const parsed = schema.safeParse(body);
  if (!parsed.success) {
    return NextResponse.json({ error: "Dữ liệu không hợp lệ" }, { status: 400 });
  }
  const t = await prisma.tournament.findUnique({
    where: { id: params.id },
    include: { _count: { select: { participants: true } } },
  });
  if (!t || t.status !== "OPEN") {
    return NextResponse.json({ error: "Giải không nhận đăng ký" }, { status: 404 });
  }
  if (t.maxPairs && t._count.participants >= t.maxPairs) {
    return NextResponse.json({ error: "Đã đủ số cặp đôi" }, { status: 409 });
  }
  // Avoid duplicate registration by same user
  const existing = await prisma.tournamentParticipant.findFirst({
    where: { tournamentId: t.id, OR: [{ player1UserId: user.id }, { player2UserId: user.id }] },
  });
  if (existing) {
    return NextResponse.json({ error: "Bạn đã đăng ký giải này" }, { status: 409 });
  }
  await prisma.tournamentParticipant.create({
    data: {
      tournamentId: t.id,
      player1Name: parsed.data.player1Name,
      player2Name: parsed.data.player2Name,
      phone: parsed.data.phone,
      player1UserId: user.id,
    },
  });
  return NextResponse.json({ ok: true });
}
