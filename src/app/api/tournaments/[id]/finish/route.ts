import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { requireUser } from "@/lib/auth-helpers";

export async function POST(_req: Request, { params }: { params: { id: string } }) {
  const user = await requireUser();
  const t = await prisma.tournament.findUnique({ where: { id: params.id } });
  if (!t) return NextResponse.json({ error: "Không tìm thấy giải" }, { status: 404 });
  if (t.createdById !== user.id && !user.isAdmin) {
    return NextResponse.json({ error: "Bạn không phải BTC" }, { status: 403 });
  }
  if (t.status !== "ONGOING") {
    return NextResponse.json({ error: "Giải không đang diễn ra" }, { status: 409 });
  }
  await prisma.tournament.update({
    where: { id: t.id },
    data: { status: "FINISHED", endDate: new Date() },
  });
  return NextResponse.json({ ok: true });
}
