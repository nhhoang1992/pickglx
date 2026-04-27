import Link from "next/link";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";
import { Wallet, Plus, Calendar } from "lucide-react";
import { formatVnd } from "@/lib/utils";

export const metadata = { title: "Chia tiền sinh hoạt" };

export const dynamic = "force-dynamic";

export default async function ChiaTienHomePage() {
  const session = await auth();
  const recentSessions = session?.user
    ? await prisma.playSession.findMany({
        where: { createdById: session.user.id },
        orderBy: { createdAt: "desc" },
        take: 10,
        include: { _count: { select: { participants: true } } },
      })
    : [];

  return (
    <div className="mx-auto max-w-5xl px-4 py-12">
      <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
          <h1 className="text-3xl font-bold text-ink md:text-4xl">Chia tiền sinh hoạt</h1>
          <p className="mt-2 text-ink/70">
            Tạo buổi sinh hoạt → thêm thành viên → hệ thống tự chia tiền + sinh QR VietQR.
          </p>
        </div>
        <Link href="/chia-tien/new" className="btn-primary self-start md:self-end">
          <Plus className="h-4 w-4" /> Tạo buổi mới
        </Link>
      </div>

      {!session?.user ? (
        <div className="card mt-8 text-center">
          <Wallet className="mx-auto h-10 w-10 text-brand" />
          <p className="mt-4 text-ink/70">Bạn cần đăng nhập để tạo và quản lý buổi sinh hoạt.</p>
          <div className="mt-4 flex justify-center gap-2">
            <Link href="/auth/login" className="btn-outline">Đăng nhập</Link>
            <Link href="/auth/register" className="btn-primary">Đăng ký</Link>
          </div>
        </div>
      ) : recentSessions.length === 0 ? (
        <div className="card mt-8 text-center">
          <Wallet className="mx-auto h-10 w-10 text-brand" />
          <p className="mt-4 text-ink/70">Chưa có buổi sinh hoạt nào — hãy tạo cái đầu tiên!</p>
          <Link href="/chia-tien/new" className="btn-primary mt-4 inline-flex">
            <Plus className="h-4 w-4" /> Tạo buổi mới
          </Link>
        </div>
      ) : (
        <div className="mt-6 grid gap-3 md:grid-cols-2">
          {recentSessions.map((s) => (
            <Link key={s.id} href={`/chia-tien/${s.id}`} className="card hover:border-brand-300">
              <div className="flex items-start justify-between gap-3">
                <div>
                  <h3 className="font-semibold text-ink">{s.name}</h3>
                  <p className="mt-1 flex items-center gap-1 text-sm text-ink/60">
                    <Calendar className="h-3.5 w-3.5" />
                    {new Date(s.date).toLocaleDateString("vi-VN")}
                    {s.location ? ` · ${s.location}` : ""}
                  </p>
                  <p className="mt-1 text-xs text-ink/60">
                    {s._count.participants} người · {formatVnd(s.totalCost)}
                  </p>
                </div>
              </div>
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}
