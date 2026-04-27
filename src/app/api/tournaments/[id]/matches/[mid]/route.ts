import { NextResponse } from "next/server";
import { z } from "zod";
import { prisma } from "@/lib/prisma";
import { requireUser } from "@/lib/auth-helpers";

const schema = z.object({
  scoreA: z.number().int().min(0).max(99),
  scoreB: z.number().int().min(0).max(99),
});

export async function PATCH(
  req: Request,
  { params }: { params: { id: string; mid: string } },
) {
  const user = await requireUser();
  const t = await prisma.tournament.findUnique({ where: { id: params.id } });
  if (!t) return NextResponse.json({ error: "Không tìm thấy giải" }, { status: 404 });
  if (t.createdById !== user.id && !user.isAdmin) {
    return NextResponse.json({ error: "Bạn không phải BTC" }, { status: 403 });
  }
  const body = await req.json().catch(() => null);
  const parsed = schema.safeParse(body);
  if (!parsed.success) {
    return NextResponse.json({ error: "Dữ liệu không hợp lệ" }, { status: 400 });
  }
  const match = await prisma.match.findUnique({ where: { id: params.mid } });
  if (!match || match.tournamentId !== t.id) {
    return NextResponse.json({ error: "Không tìm thấy trận" }, { status: 404 });
  }
  await prisma.match.update({
    where: { id: match.id },
    data: { scoreA: parsed.data.scoreA, scoreB: parsed.data.scoreB, status: "FINISHED" },
  });
  return NextResponse.json({ ok: true });
}
