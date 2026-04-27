import { NextResponse } from "next/server";
import { z } from "zod";
import { prisma } from "@/lib/prisma";
import { requireUser } from "@/lib/auth-helpers";

const schema = z.object({
  date: z.string(),
  startTime: z.string().min(1),
  durationHours: z.number().min(0.5).max(24),
  totalSlots: z.number().int().min(1).max(100),
  pricePerSlot: z.number().int().min(0),
  notes: z.string().max(2000).optional().or(z.literal("")),
});

export async function POST(req: Request, { params }: { params: { id: string } }) {
  const user = await requireUser();
  const court = await prisma.court.findUnique({ where: { id: params.id } });
  if (!court) return NextResponse.json({ error: "Không tìm thấy sân" }, { status: 404 });
  if (court.ownerUserId !== user.id && !user.isAdmin) {
    return NextResponse.json({ error: "Bạn không phải chủ sân" }, { status: 403 });
  }
  const body = await req.json().catch(() => null);
  const parsed = schema.safeParse(body);
  if (!parsed.success) {
    return NextResponse.json({ error: "Dữ liệu không hợp lệ" }, { status: 400 });
  }
  const d = parsed.data;
  const created = await prisma.courtSchedule.create({
    data: {
      courtId: params.id,
      date: new Date(d.date),
      startTime: d.startTime,
      durationHours: d.durationHours,
      totalSlots: d.totalSlots,
      slotsLeft: d.totalSlots,
      pricePerSlot: d.pricePerSlot,
      notes: d.notes || null,
      status: "OPEN",
    },
  });
  return NextResponse.json({ id: created.id });
}
