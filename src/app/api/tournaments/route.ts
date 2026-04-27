import { NextResponse } from "next/server";
import { z } from "zod";
import { prisma } from "@/lib/prisma";
import { requireUser } from "@/lib/auth-helpers";
import { slugify } from "@/lib/utils";

const schema = z.object({
  name: z.string().min(2).max(150),
  clubId: z.string().nullable().optional(),
  startDate: z.string().optional().or(z.literal("")),
  location: z.string().max(200).optional().or(z.literal("")),
  fee: z.number().int().min(0).optional(),
  maxPairs: z.number().int().min(2).max(64).optional(),
});

export async function POST(req: Request) {
  const user = await requireUser();
  const body = await req.json().catch(() => null);
  const parsed = schema.safeParse(body);
  if (!parsed.success) {
    return NextResponse.json({ error: "Dữ liệu không hợp lệ" }, { status: 400 });
  }
  const d = parsed.data;
  const baseSlug = slugify(d.name) || "giai-dau";
  let slug = baseSlug;
  let counter = 1;
  while (await prisma.tournament.findUnique({ where: { slug } })) {
    counter += 1;
    slug = `${baseSlug}-${counter}`;
  }
  const created = await prisma.tournament.create({
    data: {
      name: d.name,
      slug,
      clubId: d.clubId || null,
      startDate: d.startDate ? new Date(d.startDate) : null,
      location: d.location || null,
      fee: d.fee ?? 0,
      maxPairs: d.maxPairs ?? null,
      status: "OPEN",
      createdById: user.id,
      format: "ROUND_ROBIN",
    },
  });
  return NextResponse.json({ id: created.id, slug: created.slug });
}
