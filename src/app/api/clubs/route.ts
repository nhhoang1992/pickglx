import { NextResponse } from "next/server";
import { z } from "zod";
import { prisma } from "@/lib/prisma";
import { requireUser } from "@/lib/auth-helpers";
import { slugify } from "@/lib/utils";

const createSchema = z.object({
  name: z.string().min(2).max(120),
  location: z.string().max(200).optional().or(z.literal("")),
  description: z.string().max(2000).optional().or(z.literal("")),
});

export async function POST(req: Request) {
  const user = await requireUser();
  const body = await req.json().catch(() => null);
  const parsed = createSchema.safeParse(body);
  if (!parsed.success) {
    return NextResponse.json({ error: "Dữ liệu không hợp lệ" }, { status: 400 });
  }
  const { name, location, description } = parsed.data;

  // Generate unique slug
  const baseSlug = slugify(name) || "clb";
  let slug = baseSlug;
  let counter = 1;
  while (await prisma.club.findUnique({ where: { slug } })) {
    counter += 1;
    slug = `${baseSlug}-${counter}`;
  }

  const club = await prisma.club.create({
    data: {
      name,
      slug,
      location: location || null,
      description: description || null,
      status: "ACTIVE",
      createdById: user.id,
      members: {
        create: {
          userId: user.id,
          role: "CAPTAIN",
          status: "ACTIVE",
        },
      },
    },
  });

  return NextResponse.json({ id: club.id, slug: club.slug });
}
