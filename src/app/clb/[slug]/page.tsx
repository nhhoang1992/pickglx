import { notFound } from "next/navigation";
import Link from "next/link";
import { prisma } from "@/lib/prisma";
import { auth } from "@/auth";
import { Users, MapPin, Crown } from "lucide-react";
import { JoinButton } from "./join-button";

export async function generateMetadata({ params }: { params: { slug: string } }) {
  const club = await prisma.club.findUnique({ where: { slug: params.slug }, select: { name: true } });
  return { title: club ? club.name : "CLB" };
}

export default async function ClubDetailPage({ params }: { params: { slug: string } }) {
  const club = await prisma.club.findUnique({
    where: { slug: params.slug },
    include: {
      members: {
        where: { status: "ACTIVE" },
        include: { user: { select: { id: true, name: true, avatarUrl: true } } },
        orderBy: [{ role: "asc" }, { joinedAt: "asc" }],
      },
    },
  });
  if (!club || club.status !== "ACTIVE") notFound();

  const session = await auth();
  const userId = session?.user?.id;

  const myMembership = userId
    ? await prisma.clubMember.findUnique({
        where: { clubId_userId: { clubId: club.id, userId } },
      })
    : null;
  const myJoinRequest = userId
    ? await prisma.clubJoinRequest.findUnique({
        where: { clubId_userId: { clubId: club.id, userId } },
      })
    : null;

  const captains = club.members.filter((m) => m.role === "CAPTAIN");

  return (
    <div className="mx-auto max-w-4xl px-4 py-12">
      <Link href="/clb" className="text-sm text-brand hover:underline">← Về danh sách CLB</Link>

      <div className="card mt-4">
        <div className="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
          <div className="flex items-start gap-4">
            <div className="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand">
              <Users className="h-8 w-8" />
            </div>
            <div>
              <h1 className="text-2xl font-bold text-ink md:text-3xl">{club.name}</h1>
              {club.location && (
                <p className="mt-1 flex items-center gap-1 text-sm text-ink/70">
                  <MapPin className="h-4 w-4" /> {club.location}
                </p>
              )}
              <p className="mt-1 text-sm text-ink/60">{club.members.length} thành viên</p>
            </div>
          </div>
          <div className="flex flex-wrap gap-2">
            {myMembership ? (
              <>
                <span className="badge bg-brand-100 text-brand-700">
                  Bạn là {myMembership.role === "CAPTAIN" ? "đội trưởng" : myMembership.role === "VICE" ? "đội phó" : "thành viên"}
                </span>
                {(myMembership.role === "CAPTAIN" || myMembership.role === "VICE") && (
                  <Link href={`/dashboard/clb/${club.slug}`} className="btn-primary">
                    Quản lý CLB
                  </Link>
                )}
              </>
            ) : myJoinRequest ? (
              <span className="badge bg-accent-100 text-accent-600">
                {myJoinRequest.status === "PENDING" && "Đang chờ duyệt"}
                {myJoinRequest.status === "REJECTED" && "Yêu cầu đã bị từ chối"}
              </span>
            ) : (
              <JoinButton clubId={club.id} loggedIn={Boolean(userId)} />
            )}
          </div>
        </div>

        {club.description && (
          <p className="mt-6 whitespace-pre-line text-ink/80">{club.description}</p>
        )}
      </div>

      {captains.length > 0 && (
        <div className="card mt-4">
          <h2 className="flex items-center gap-2 font-semibold text-ink">
            <Crown className="h-5 w-5 text-accent-500" /> Đội trưởng
          </h2>
          <ul className="mt-3 flex flex-wrap gap-3">
            {captains.map((m) => (
              <li key={m.id} className="flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1.5 text-sm text-ink">
                <span className="flex h-6 w-6 items-center justify-center rounded-full bg-brand text-xs font-medium text-white">
                  {(m.user.name ?? "?").slice(0, 1).toUpperCase()}
                </span>
                {m.user.name ?? "Đội trưởng"}
              </li>
            ))}
          </ul>
        </div>
      )}

      <div className="card mt-4">
        <h2 className="font-semibold text-ink">Thành viên ({club.members.length})</h2>
        <ul className="mt-3 grid gap-2 sm:grid-cols-2">
          {club.members.map((m) => (
            <li key={m.id} className="flex items-center gap-2 text-sm">
              <span className="flex h-7 w-7 items-center justify-center rounded-full bg-brand-100 text-xs font-medium text-brand-700">
                {(m.user.name ?? "?").slice(0, 1).toUpperCase()}
              </span>
              <span className="text-ink">{m.user.name ?? "Thành viên"}</span>
              {m.role === "CAPTAIN" && <span className="badge bg-accent-100 text-accent-600">Đội trưởng</span>}
              {m.role === "VICE" && <span className="badge bg-brand-100 text-brand-700">Đội phó</span>}
            </li>
          ))}
        </ul>
      </div>
    </div>
  );
}
