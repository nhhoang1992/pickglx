import { NextResponse } from "next/server";
import { z } from "zod";
import { prisma } from "@/lib/prisma";
import { requireUser } from "@/lib/auth-helpers";

const schema = z.object({
  name: z.string().min(1).max(120),
  address: z.string().max(200).optional().or(z.literal("")),
  contact: z.string().max(60).optional().or(z.literal("")),
  bankName: z.string().max(50).optional().or(z.literal("")),
  bankAccount: z.string().max(50).optional().or(z.literal("")),
  bankHolder: z.string().max(120).optional().or(z.literal("")),
});

export async function POST(req: Request) {
  const user = await requireUser();
  const body = await req.json().catch(() => null);
  const parsed = schema.safeParse(body);
  if (!parsed.success) {
    return NextResponse.json({ error: "Dữ liệu không hợp lệ" }, { status: 400 });
  }
  const d = parsed.data;
  const created = await prisma.court.create({
    data: {
      name: d.name,
      address: d.address || null,
      contact: d.contact || null,
      bankName: d.bankName || null,
      bankAccount: d.bankAccount || null,
      bankHolder: d.bankHolder || null,
      ownerUserId: user.id,
    },
  });
  return NextResponse.json({ id: created.id });
}
