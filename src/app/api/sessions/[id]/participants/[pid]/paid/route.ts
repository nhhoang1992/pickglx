import { NextResponse } from "next/server";
import { z } from "zod";
import { prisma } from "@/lib/prisma";
import { auth } from "@/auth";

const schema = z.object({ paid: z.boolean() });

export async function PATCH(
  req: Request,
  { params }: { params: { id: string; pid: string } },
) {
  const userSession = await auth();
  const userId = userSession?.user?.id ?? null;
  const isAdmin = userSession?.user?.isAdmin ?? false;

  const playSession = await prisma.playSession.findUnique({ where: { id: params.id } });
  if (!playSession) return NextResponse.json({ error: "Không tìm thấy buổi" }, { status: 404 });

  const participant = await prisma.sessionParticipant.findUnique({ where: { id: params.pid } });
  if (!participant || participant.sessionId !== params.id) {
    return NextResponse.json({ error: "Không tìm thấy thành viên" }, { status: 404 });
  }

  const isOwner = userId === playSession.createdById || isAdmin;
  const isSelf = userId !== null && userId === participant.userId;
  if (!isOwner && !isSelf) {
    return NextResponse.json({ error: "Bạn không có quyền cập nhật" }, { status: 403 });
  }

  const body = await req.json().catch(() => null);
  const parsed = schema.safeParse(body);
  if (!parsed.success) {
    return NextResponse.json({ error: "Dữ liệu không hợp lệ" }, { status: 400 });
  }
  await prisma.sessionParticipant.update({
    where: { id: params.pid },
    data: {
      paid: parsed.data.paid,
      paidAt: parsed.data.paid ? new Date() : null,
    },
  });
  return NextResponse.json({ ok: true });
}
