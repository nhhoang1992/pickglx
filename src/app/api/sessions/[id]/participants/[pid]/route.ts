import { NextResponse } from "next/server";
import { z } from "zod";
import { prisma } from "@/lib/prisma";
import { requireUser } from "@/lib/auth-helpers";

const patchSchema = z.object({
  guestName: z.string().min(1).max(120).optional(),
  guestPhone: z.string().max(30).optional().or(z.null()),
  matchesPlayed: z.number().int().min(0).optional(),
  hoursPlayed: z.number().min(0).optional(),
  weight: z.number().min(0).optional(),
});

async function assertOwner(sessionId: string, userId: string, isAdmin: boolean) {
  const session = await prisma.playSession.findUnique({ where: { id: sessionId } });
  if (!session) return { ok: false as const, status: 404, error: "Không tìm thấy buổi" };
  if (session.createdById !== userId && !isAdmin) {
    return { ok: false as const, status: 403, error: "Bạn không có quyền chỉnh sửa" };
  }
  return { ok: true as const, session };
}

export async function PATCH(
  req: Request,
  { params }: { params: { id: string; pid: string } },
) {
  const user = await requireUser();
  const guard = await assertOwner(params.id, user.id, user.isAdmin);
  if (!guard.ok) return NextResponse.json({ error: guard.error }, { status: guard.status });

  const body = await req.json().catch(() => null);
  const parsed = patchSchema.safeParse(body);
  if (!parsed.success) {
    return NextResponse.json({ error: "Dữ liệu không hợp lệ" }, { status: 400 });
  }
  const data = parsed.data;
  await prisma.sessionParticipant.update({
    where: { id: params.pid },
    data: {
      ...(data.guestName !== undefined ? { guestName: data.guestName } : {}),
      ...(data.guestPhone !== undefined ? { guestPhone: data.guestPhone || null } : {}),
      ...(data.matchesPlayed !== undefined ? { matchesPlayed: data.matchesPlayed } : {}),
      ...(data.hoursPlayed !== undefined ? { hoursPlayed: data.hoursPlayed } : {}),
      ...(data.weight !== undefined ? { weight: data.weight } : {}),
    },
  });
  return NextResponse.json({ ok: true });
}

export async function DELETE(
  _req: Request,
  { params }: { params: { id: string; pid: string } },
) {
  const user = await requireUser();
  const guard = await assertOwner(params.id, user.id, user.isAdmin);
  if (!guard.ok) return NextResponse.json({ error: guard.error }, { status: guard.status });
  await prisma.sessionParticipant.delete({ where: { id: params.pid } });
  return NextResponse.json({ ok: true });
}
