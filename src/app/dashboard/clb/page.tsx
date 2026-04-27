import Link from "next/link";
import { Plus, Users } from "lucide-react";
import { requireUser } from "@/lib/auth-helpers";
import { prisma } from "@/lib/prisma";

export const metadata = { title: "CLB của tôi" };

export const dynamic = "force-dynamic";

export default async function MyClubsPage() {
  const user = await requireUser();
  const memberships = await prisma.clubMember.findMany({
    where: { userId: user.id, status: "ACTIVE" },
    include: {
      club: {
        include: {
          _count: {
            select: {
              members: { where: { status: "ACTIVE" } },
              joinRequests: { where: { status: "PENDING" } },
            },
          },
        },
      },
    },
    orderBy: { joinedAt: "desc" },
  });

  const pendingRequests = await prisma.clubJoinRequest.findMany({
    where: { userId: user.id, status: "PENDING" },
    include: { club: { select: { name: true, slug: true } } },
  });

  return (
    <div className="mx-auto max-w-5xl px-4 py-12">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-ink">CLB của tôi</h1>
          <p className="mt-1 text-sm text-ink/70">Quản lý các CLB bạn tham gia.</p>
        </div>
        <Link href="/clb/new" className="btn-primary">
          <Plus className="h-4 w-4" /> Tạo CLB
        </Link>
      </div>

      {memberships.length === 0 ? (
        <div className="card mt-8 text-center">
          <Users className="mx-auto h-10 w-10 text-brand" />
          <p className="mt-4 text-ink/70">Bạn chưa tham gia CLB nào.</p>
          <div className="mt-4 flex justify-center gap-2">
            <Link href="/clb" className="btn-outline">Tìm CLB</Link>
            <Link href="/clb/new" className="btn-primary">Tạo CLB mới</Link>
          </div>
        </div>
      ) : (
        <div className="mt-6 grid gap-4 md:grid-cols-2">
          {memberships.map((m) => (
            <div key={m.id} className="card">
              <div className="flex items-start justify-between gap-3">
                <div>
                  <h3 className="font-semibold text-ink">{m.club.name}</h3>
                  <p className="mt-1 text-sm text-ink/60">
                    {m.club._count.members} thành viên ·{" "}
                    {m.role === "CAPTAIN"
                      ? "Đội trưởng"
                      : m.role === "VICE"
                        ? "Đội phó"
                        : "Thành viên"}
                  </p>
                </div>
                {m.club._count.joinRequests > 0 && (m.role === "CAPTAIN" || m.role === "VICE") && (
                  <span className="badge bg-accent-100 text-accent-600">
                    {m.club._count.joinRequests} chờ duyệt
                  </span>
                )}
              </div>
              <div className="mt-4 flex gap-2">
                <Link href={`/clb/${m.club.slug}`} className="btn-outline text-xs">Xem</Link>
                {(m.role === "CAPTAIN" || m.role === "VICE") && (
                  <Link href={`/dashboard/clb/${m.club.slug}`} className="btn-primary text-xs">Quản lý</Link>
                )}
              </div>
            </div>
          ))}
        </div>
      )}

      {pendingRequests.length > 0 && (
        <div className="mt-10">
          <h2 className="font-semibold text-ink">Yêu cầu đang chờ duyệt</h2>
          <ul className="mt-3 space-y-2">
            {pendingRequests.map((r) => (
              <li key={r.id} className="card flex items-center justify-between py-3">
                <span className="text-sm text-ink">{r.club.name}</span>
                <span className="badge bg-accent-100 text-accent-600">Chờ duyệt</span>
              </li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );
}
