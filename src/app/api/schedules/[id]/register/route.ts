import { NextResponse } from "next/server";
import { z } from "zod";
import { prisma } from "@/lib/prisma";
import { requireUser } from "@/lib/auth-helpers";

const schema = z.object({
  slots: z.number().int().min(1).max(20),
  guestPhone: z.string().min(5).max(30),
  proofUrl: z.string().url().optional(),
});

export async function POST(req: Request, { params }: { params: { id: string } }) {
  const user = await requireUser();
  const body = await req.json().catch(() => null);
  const parsed = schema.safeParse(body);
  if (!parsed.success) {
    return NextResponse.json({ error: "Dữ liệu không hợp lệ" }, { status: 400 });
  }
  const { slots, guestPhone, proofUrl } = parsed.data;

  const sched = await prisma.courtSchedule.findUnique({ where: { id: params.id } });
  if (!sched || sched.status !== "OPEN") {
    return NextResponse.json({ error: "Lịch không nhận đăng ký" }, { status: 404 });
  }
  if (slots > sched.slotsLeft) {
    return NextResponse.json({ error: `Chỉ còn ${sched.slotsLeft} slot.` }, { status: 409 });
  }

  // 1 user 1 registration per schedule
  const existing = await prisma.ticketRegistration.findFirst({
    where: { scheduleId: sched.id, userId: user.id, status: { in: ["PENDING", "CONFIRMED"] } },
  });
  if (existing) {
    return NextResponse.json({ error: "Bạn đã đăng ký lịch này rồi." }, { status: 409 });
  }

  const amount = slots * sched.pricePerSlot;

  await prisma.$transaction([
    prisma.ticketRegistration.create({
      data: {
        scheduleId: sched.id,
        userId: user.id,
        guestName: user.name ?? user.email,
        guestPhone,
        slots,
        amount,
        proofUrl: proofUrl ?? null,
        status: "PENDING",
      },
    }),
    prisma.courtSchedule.update({
      where: { id: sched.id },
      data: { slotsLeft: { decrement: slots } },
    }),
  ]);

  return NextResponse.json({ ok: true });
}
