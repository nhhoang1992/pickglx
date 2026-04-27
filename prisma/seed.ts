import { PrismaClient } from "@prisma/client";
import bcrypt from "bcryptjs";

const prisma = new PrismaClient();

async function main() {
  const adminPwHash = await bcrypt.hash("admin123!@#", 10);
  const userPwHash = await bcrypt.hash("test1234", 10);

  const admin = await prisma.user.upsert({
    where: { email: "admin@pickglx.vn" },
    update: { passwordHash: adminPwHash, isAdmin: true, name: "Admin Pickglx" },
    create: {
      email: "admin@pickglx.vn",
      name: "Admin Pickglx",
      passwordHash: adminPwHash,
      isAdmin: true,
    },
  });

  const captain = await prisma.user.upsert({
    where: { email: "captain@pickglx.vn" },
    update: { passwordHash: userPwHash, name: "Đội trưởng Galaxy" },
    create: {
      email: "captain@pickglx.vn",
      name: "Đội trưởng Galaxy",
      passwordHash: userPwHash,
    },
  });

  const member = await prisma.user.upsert({
    where: { email: "member@pickglx.vn" },
    update: { passwordHash: userPwHash, name: "Thành viên test" },
    create: {
      email: "member@pickglx.vn",
      name: "Thành viên test",
      passwordHash: userPwHash,
    },
  });

  // Sample club
  const club = await prisma.club.upsert({
    where: { slug: "galaxy" },
    update: {},
    create: {
      name: "CLB Pickleball Galaxy",
      slug: "galaxy",
      location: "Sân Galaxy, Q. Cầu Giấy, Hà Nội",
      description: "CLB demo cho Pickglx — tham gia thoải mái để test các tính năng.",
      status: "ACTIVE",
      createdById: captain.id,
    },
  });

  await prisma.clubMember.upsert({
    where: { clubId_userId: { clubId: club.id, userId: captain.id } },
    update: { role: "CAPTAIN", status: "ACTIVE" },
    create: { clubId: club.id, userId: captain.id, role: "CAPTAIN", status: "ACTIVE" },
  });

  await prisma.clubMember.upsert({
    where: { clubId_userId: { clubId: club.id, userId: admin.id } },
    update: { role: "MEMBER", status: "ACTIVE" },
    create: { clubId: club.id, userId: admin.id, role: "MEMBER", status: "ACTIVE" },
  });

  console.log("Seeded:", { admin: admin.email, captain: captain.email, member: member.email, club: club.slug });
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
