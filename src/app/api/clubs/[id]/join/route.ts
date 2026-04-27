import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { requireUser } from "@/lib/auth-helpers";

export async function POST(_req: Request, { params }: { params: { id: string } }) {
  const user = await requireUser();
  const club = await prisma.club.findUnique({ where: { id: params.id } });
  if (!club || club.status !== "ACTIVE") {
    return NextResponse.json({ error: "CLB không tồn tại" }, { status: 404 });
  }

  const existingMember = await prisma.clubMember.findUnique({
    where: { clubId_userId: { clubId: club.id, userId: user.id } },
  });
  if (existingMember) {
    return NextResponse.json({ error: "Bạn đã là thành viên của CLB này" }, { status: 409 });
  }

  const existingRequest = await prisma.clubJoinRequest.findUnique({
    where: { clubId_userId: { clubId: club.id, userId: user.id } },
  });
  if (existingRequest && existingRequest.status === "PENDING") {
    return NextResponse.json({ error: "Yêu cầu của bạn đang chờ duyệt" }, { status: 409 });
  }

  if (existingRequest) {
    await prisma.clubJoinRequest.update({
      where: { id: existingRequest.id },
      data: { status: "PENDING", reviewedAt: null, reviewedById: null },
    });
  } else {
    await prisma.clubJoinRequest.create({
      data: { clubId: club.id, userId: user.id, status: "PENDING" },
    });
  }

  return NextResponse.json({ ok: true });
}
