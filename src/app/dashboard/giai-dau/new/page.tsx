import Link from "next/link";
import { requireUser } from "@/lib/auth-helpers";
import { prisma } from "@/lib/prisma";
import { CreateTournamentForm } from "./form";

export const metadata = { title: "Tạo giải đấu" };

export default async function NewTournamentPage() {
  const user = await requireUser();
  const memberships = await prisma.clubMember.findMany({
    where: { userId: user.id, status: "ACTIVE", role: { in: ["CAPTAIN", "VICE"] } },
    include: { club: { select: { id: true, name: true } } },
  });
  return (
    <div className="mx-auto max-w-2xl px-4 py-10">
      <Link href="/giai-dau" className="text-sm text-brand hover:underline">← Tất cả giải</Link>
      <div className="card mt-4">
        <h1 className="text-2xl font-bold text-ink">Tạo giải đấu</h1>
        <p className="mt-1 text-sm text-ink/70">Round-robin (vòng tròn). Hệ thống tự sinh lịch sau khi bạn lock đăng ký.</p>
        <CreateTournamentForm clubs={memberships.map((m) => m.club)} />
      </div>
    </div>
  );
}
