import { NextResponse } from "next/server";
import { z } from "zod";
import { prisma } from "@/lib/prisma";
import { requireUser } from "@/lib/auth-helpers";

const schema = z.object({
  name: z.string().min(1).max(200),
  date: z.string(),
  location: z.string().max(200).optional().or(z.literal("")),
  clubId: z.string().nullable().optional(),
  totalCost: z.number().int().min(0),
  splitMethod: z.enum(["EQUAL", "BY_MATCH", "BY_HOUR", "CUSTOM"]),
  bankName: z.string().max(50).optional().or(z.literal("")),
  bankAccount: z.string().max(50).optional().or(z.literal("")),
  bankHolder: z.string().max(120).optional().or(z.literal("")),
  notes: z.string().max(2000).optional().or(z.literal("")),
});

export async function POST(req: Request) {
  const user = await requireUser();
  const body = await req.json().catch(() => null);
  const parsed = schema.safeParse(body);
  if (!parsed.success) {
    return NextResponse.json({ error: "Dữ liệu không hợp lệ" }, { status: 400 });
  }
  const data = parsed.data;
  const created = await prisma.playSession.create({
    data: {
      name: data.name,
      date: new Date(data.date),
      location: data.location || null,
      clubId: data.clubId || null,
      totalCost: data.totalCost,
      splitMethod: data.splitMethod,
      bankName: data.bankName || null,
      bankAccount: data.bankAccount || null,
      bankHolder: data.bankHolder || null,
      notes: data.notes || null,
      createdById: user.id,
    },
  });
  return NextResponse.json({ id: created.id });
}
