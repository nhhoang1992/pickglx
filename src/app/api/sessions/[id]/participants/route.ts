import { NextResponse } from "next/server";
import { z } from "zod";
import { prisma } from "@/lib/prisma";
import { requireUser } from "@/lib/auth-helpers";

const schema = z.object({
  guestName: z.string().min(1).max(120),
  guestPhone: z.string().max(30).optional(),
  matchesPlayed: z.number().int().min(0).optional(),
  hoursPlayed: z.number().min(0).optional(),
  weight: z.number().min(0).optional(),
});

export async function POST(req: Request, { params }: { params: { id: string } }) {
  const user = await requireUser();
  const session = await prisma.playSession.findUnique({ where: { id: params.id } });
  if (!session) return NextResponse.json({ error: "Không tìm thấy buổi" }, { status: 404 });
  if (session.createdById !== user.id && !user.isAdmin) {
    return NextResponse.json({ error: "Bạn không có quyền chỉnh sửa" }, { status: 403 });
  }
  const body = await req.json().catch(() => null);
  const parsed = schema.safeParse(body);
  if (!parsed.success) {
    return NextResponse.json({ error: "Dữ liệu không hợp lệ" }, { status: 400 });
  }
  const data = parsed.data;
  const created = await prisma.sessionParticipant.create({
    data: {
      sessionId: params.id,
      guestName: data.guestName,
      guestPhone: data.guestPhone || null,
      matchesPlayed: data.matchesPlayed ?? 0,
      hoursPlayed: data.hoursPlayed ?? 0,
      weight: data.weight ?? 1,
    },
  });
  return NextResponse.json({ id: created.id });
}
