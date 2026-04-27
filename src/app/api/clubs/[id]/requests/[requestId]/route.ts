import { NextResponse } from "next/server";
import { z } from "zod";
import { prisma } from "@/lib/prisma";
import { requireUser, isCaptainOrAdmin } from "@/lib/auth-helpers";

const schema = z.object({
  action: z.enum(["approve", "reject"]),
});

export async function PATCH(
  req: Request,
  { params }: { params: { id: string; requestId: string } },
) {
  const user = await requireUser();
  const allowed = await isCaptainOrAdmin(params.id, user.id);
  if (!allowed) {
    return NextResponse.json({ error: "Bạn không có quyền duyệt yêu cầu" }, { status: 403 });
  }

  const body = await req.json().catch(() => null);
  const parsed = schema.safeParse(body);
  if (!parsed.success) {
    return NextResponse.json({ error: "Dữ liệu không hợp lệ" }, { status: 400 });
  }

  const request = await prisma.clubJoinRequest.findUnique({
    where: { id: params.requestId },
  });
  if (!request || request.clubId !== params.id) {
    return NextResponse.json({ error: "Yêu cầu không tồn tại" }, { status: 404 });
  }
  if (request.status !== "PENDING") {
    return NextResponse.json({ error: "Yêu cầu đã được xử lý" }, { status: 409 });
  }

  if (parsed.data.action === "approve") {
    await prisma.$transaction([
      prisma.clubJoinRequest.update({
        where: { id: request.id },
        data: { status: "APPROVED", reviewedById: user.id, reviewedAt: new Date() },
      }),
      prisma.clubMember.upsert({
        where: { clubId_userId: { clubId: request.clubId, userId: request.userId } },
        create: {
          clubId: request.clubId,
          userId: request.userId,
          role: "MEMBER",
          status: "ACTIVE",
        },
        update: { status: "ACTIVE" },
      }),
    ]);
  } else {
    await prisma.clubJoinRequest.update({
      where: { id: request.id },
      data: { status: "REJECTED", reviewedById: user.id, reviewedAt: new Date() },
    });
  }

  return NextResponse.json({ ok: true });
}
