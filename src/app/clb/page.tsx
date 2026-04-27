import Link from "next/link";
import { prisma } from "@/lib/prisma";
import { Plus, Users, MapPin } from "lucide-react";
import { auth } from "@/auth";

export const metadata = { title: "Câu lạc bộ" };

export const dynamic = "force-dynamic";

export default async function ClubsPage({ searchParams }: { searchParams: { q?: string } }) {
  const session = await auth();
  const q = searchParams.q?.trim() ?? "";
  const clubs = await prisma.club.findMany({
    where: {
      status: "ACTIVE",
      ...(q
        ? {
            OR: [
              { name: { contains: q, mode: "insensitive" } },
              { location: { contains: q, mode: "insensitive" } },
            ],
          }
        : {}),
    },
    include: {
      _count: { select: { members: { where: { status: "ACTIVE" } } } },
    },
    orderBy: { createdAt: "desc" },
    take: 60,
  });

  return (
    <div className="mx-auto max-w-6xl px-4 py-12">
      <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
          <h1 className="text-3xl font-bold text-ink md:text-4xl">Câu lạc bộ Pickleball</h1>
          <p className="mt-2 text-ink/70">
            Tìm CLB gần bạn để gia nhập, hoặc tạo CLB mới của riêng bạn.
          </p>
        </div>
        {session?.user && (
          <Link href="/clb/new" className="btn-primary self-start md:self-end">
            <Plus className="h-4 w-4" /> Tạo CLB mới
          </Link>
        )}
      </div>

      <form className="mt-8" action="/clb">
        <input
          name="q"
          defaultValue={q}
          placeholder="Tìm theo tên hoặc địa điểm..."
          className="input max-w-md"
        />
      </form>

      <div className="mt-8 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {clubs.length === 0 ? (
          <div className="card md:col-span-2 lg:col-span-3 text-center text-ink/70">
            {q ? `Không tìm thấy CLB nào với từ khoá "${q}".` : "Chưa có CLB nào — hãy là người đầu tiên!"}
          </div>
        ) : (
          clubs.map((c) => (
            <Link key={c.id} href={`/clb/${c.slug}`} className="card hover:border-brand-300 transition-colors">
              <div className="flex items-start gap-3">
                <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand">
                  <Users className="h-6 w-6" />
                </div>
                <div className="min-w-0">
                  <h3 className="truncate font-semibold text-ink">{c.name}</h3>
                  {c.location && (
                    <p className="mt-1 flex items-center gap-1 text-sm text-ink/60">
                      <MapPin className="h-3.5 w-3.5" /> {c.location}
                    </p>
                  )}
                  <p className="mt-1 text-xs text-ink/60">{c._count.members} thành viên</p>
                </div>
              </div>
              {c.description && (
                <p className="mt-3 line-clamp-2 text-sm text-ink/70">{c.description}</p>
              )}
            </Link>
          ))
        )}
      </div>
    </div>
  );
}
