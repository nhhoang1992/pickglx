import { auth, signOut } from "@/auth";
import { redirect } from "next/navigation";
import Link from "next/link";
import { prisma } from "@/lib/prisma";
import { Users, Wallet, Calendar, Trophy } from "lucide-react";

export const metadata = { title: "Bảng điều khiển" };

export const dynamic = "force-dynamic";

export default async function DashboardPage() {
  const session = await auth();
  if (!session?.user) redirect("/auth/login");

  const myClubsCount = await prisma.clubMember.count({
    where: { userId: session.user.id, status: "ACTIVE" },
  });

  const pendingApprovalsCount = await prisma.clubJoinRequest.count({
    where: {
      status: "PENDING",
      club: {
        members: {
          some: {
            userId: session.user.id,
            status: "ACTIVE",
            role: { in: ["CAPTAIN", "VICE"] },
          },
        },
      },
    },
  });

  return (
    <div className="mx-auto max-w-6xl px-4 py-12">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-ink">Xin chào, {session.user.name}</h1>
          <p className="mt-1 text-sm text-ink/70">{session.user.email}</p>
        </div>
        <form
          action={async () => {
            "use server";
            await signOut({ redirectTo: "/" });
          }}
        >
          <button type="submit" className="btn-outline">Đăng xuất</button>
        </form>
      </div>

      {pendingApprovalsCount > 0 && (
        <div className="card mt-6 flex items-center justify-between bg-accent-50 border-accent-200">
          <div>
            <p className="font-semibold text-ink">Có {pendingApprovalsCount} yêu cầu gia nhập đang chờ bạn duyệt.</p>
            <p className="text-sm text-ink/70">Bạn là đội trưởng/đội phó của 1 hoặc nhiều CLB.</p>
          </div>
          <Link href="/dashboard/clb" className="btn-accent">Xem & Duyệt</Link>
        </div>
      )}

      <div className="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Link href="/dashboard/clb" className="card hover:border-brand-300">
          <Users className="h-7 w-7 text-brand" />
          <h3 className="mt-3 font-semibold text-ink">CLB của tôi</h3>
          <p className="mt-1 text-sm text-ink/70">{myClubsCount} CLB</p>
        </Link>
        <Link href="/chia-tien" className="card hover:border-brand-300">
          <Wallet className="h-7 w-7 text-brand" />
          <h3 className="mt-3 font-semibold text-ink">Chia tiền</h3>
          <p className="mt-1 text-sm text-ink/70">Tạo buổi & chia QR VietQR</p>
        </Link>
        <Link href="/dashboard/lich-xe-ve" className="card hover:border-brand-300">
          <Calendar className="h-7 w-7 text-brand" />
          <h3 className="mt-3 font-semibold text-ink">Lịch xé vé</h3>
          <p className="mt-1 text-sm text-ink/70">Quản lý sân & duyệt đăng ký</p>
        </Link>
        <Link href="/dashboard/giai-dau" className="card hover:border-brand-300">
          <Trophy className="h-7 w-7 text-brand" />
          <h3 className="mt-3 font-semibold text-ink">Giải đấu</h3>
          <p className="mt-1 text-sm text-ink/70">Round-robin tự sinh lịch + BXH</p>
        </Link>
      </div>
    </div>
  );
}
