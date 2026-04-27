import { notFound, redirect } from "next/navigation";
import Link from "next/link";
import { requireUser, isCaptainOrAdmin } from "@/lib/auth-helpers";
import { prisma } from "@/lib/prisma";
import { JoinRequestActions } from "./request-actions";
import { Users } from "lucide-react";

export async function generateMetadata({ params }: { params: { slug: string } }) {
  const club = await prisma.club.findUnique({ where: { slug: params.slug }, select: { name: true } });
  return { title: club ? `Quản lý ${club.name}` : "Quản lý CLB" };
}

export const dynamic = "force-dynamic";

export default async function ManageClubPage({ params }: { params: { slug: string } }) {
  const user = await requireUser();
  const club = await prisma.club.findUnique({ where: { slug: params.slug } });
  if (!club) notFound();

  const allowed = await isCaptainOrAdmin(club.id, user.id);
  if (!allowed) redirect(`/clb/${club.slug}`);

  const [pendingRequests, members] = await Promise.all([
    prisma.clubJoinRequest.findMany({
      where: { clubId: club.id, status: "PENDING" },
      include: { user: { select: { id: true, name: true, email: true, phone: true } } },
      orderBy: { createdAt: "asc" },
    }),
    prisma.clubMember.findMany({
      where: { clubId: club.id, status: "ACTIVE" },
      include: { user: { select: { id: true, name: true, email: true } } },
      orderBy: [{ role: "asc" }, { joinedAt: "asc" }],
    }),
  ]);

  return (
    <div className="mx-auto max-w-5xl px-4 py-12">
      <Link href="/dashboard/clb" className="text-sm text-brand hover:underline">← Về CLB của tôi</Link>
      <div className="card mt-4">
        <div className="flex items-start gap-3">
          <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand">
            <Users className="h-6 w-6" />
          </div>
          <div>
            <h1 className="text-2xl font-bold text-ink">{club.name}</h1>
            <p className="mt-1 text-sm text-ink/60">Bảng điều khiển CLB</p>
          </div>
        </div>
      </div>

      <div className="mt-6">
        <h2 className="font-semibold text-ink">
          Yêu cầu chờ duyệt {pendingRequests.length > 0 && `(${pendingRequests.length})`}
        </h2>
        {pendingRequests.length === 0 ? (
          <p className="mt-3 text-sm text-ink/60">Không có yêu cầu nào đang chờ.</p>
        ) : (
          <ul className="mt-3 space-y-2">
            {pendingRequests.map((r) => (
              <li key={r.id} className="card flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <p className="font-medium text-ink">{r.user.name ?? r.user.email}</p>
                  <p className="text-xs text-ink/60">{r.user.email}{r.user.phone ? ` · ${r.user.phone}` : ""}</p>
                </div>
                <JoinRequestActions clubId={club.id} requestId={r.id} />
              </li>
            ))}
          </ul>
        )}
      </div>

      <div className="mt-10">
        <h2 className="font-semibold text-ink">Thành viên ({members.length})</h2>
        <ul className="mt-3 divide-y divide-brand-100 rounded-xl border border-brand-100 bg-white">
          {members.map((m) => (
            <li key={m.id} className="flex items-center justify-between px-4 py-3 text-sm">
              <div>
                <p className="font-medium text-ink">{m.user.name ?? m.user.email}</p>
                <p className="text-xs text-ink/60">{m.user.email}</p>
              </div>
              {m.role === "CAPTAIN" && <span className="badge bg-accent-100 text-accent-600">Đội trưởng</span>}
              {m.role === "VICE" && <span className="badge bg-brand-100 text-brand-700">Đội phó</span>}
              {m.role === "MEMBER" && <span className="text-xs text-ink/60">Thành viên</span>}
            </li>
          ))}
        </ul>
      </div>
    </div>
  );
}
