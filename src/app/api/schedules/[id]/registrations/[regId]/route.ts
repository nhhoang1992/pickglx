import { NextResponse } from "next/server";
import { z } from "zod";
import { prisma } from "@/lib/prisma";
import { requireUser } from "@/lib/auth-helpers";

const schema = z.object({ action: z.enum(["confirm", "reject"]) });

export async function PATCH(
  req: Request,
  { params }: { params: { id: string; regId: string } },
) {
  const user = await requireUser();
  const sched = await prisma.courtSchedule.findUnique({
    where: { id: params.id },
    include: { court: true },
  });
  if (!sched) return NextResponse.json({ error: "Không tìm thấy lịch" }, { status: 404 });
  if (sched.court.ownerUserId !== user.id && !user.isAdmin) {
    return NextResponse.json({ error: "Bạn không phải chủ sân" }, { status: 403 });
  }
  const body = await req.json().catch(() => null);
  const parsed = schema.safeParse(body);
  if (!parsed.success) {
    return NextResponse.json({ error: "Dữ liệu không hợp lệ" }, { status: 400 });
  }
  const reg = await prisma.ticketRegistration.findUnique({ where: { id: params.regId } });
  if (!reg || reg.scheduleId !== params.id) {
    return NextResponse.json({ error: "Không tìm thấy đăng ký" }, { status: 404 });
  }
  if (reg.status !== "PENDING") {
    return NextResponse.json({ error: "Đăng ký đã được xử lý" }, { status: 409 });
  }

  if (parsed.data.action === "confirm") {
    await prisma.ticketRegistration.update({
      where: { id: reg.id },
      data: { status: "CONFIRMED", confirmedById: user.id, confirmedAt: new Date() },
    });
  } else {
    await prisma.$transaction([
      prisma.ticketRegistration.update({
        where: { id: reg.id },
        data: { status: "REJECTED", confirmedById: user.id, confirmedAt: new Date() },
      }),
      prisma.courtSchedule.update({
        where: { id: sched.id },
        data: { slotsLeft: { increment: reg.slots } },
      }),
    ]);
  }
  return NextResponse.json({ ok: true });
}
