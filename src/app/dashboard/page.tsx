import { auth, signOut } from "@/auth";
import { redirect } from "next/navigation";
import Link from "next/link";

export const metadata = { title: "Bảng điều khiển" };

export default async function DashboardPage() {
  const session = await auth();
  if (!session?.user) redirect("/auth/login");

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

      <div className="mt-10 grid gap-4 md:grid-cols-3">
        <Link href="/clb" className="card hover:border-brand-300">
          <h3 className="font-semibold text-ink">CLB của tôi</h3>
          <p className="mt-2 text-sm text-ink/70">Quản lý CLB và thành viên.</p>
        </Link>
        <Link href="/chia-tien" className="card hover:border-brand-300">
          <h3 className="font-semibold text-ink">Buổi sinh hoạt</h3>
          <p className="mt-2 text-sm text-ink/70">Tạo và chia tiền các buổi chơi.</p>
        </Link>
        <Link href="/giai-dau" className="card hover:border-brand-300">
          <h3 className="font-semibold text-ink">Giải đấu</h3>
          <p className="mt-2 text-sm text-ink/70">Tổ chức giải nội bộ hoặc mở rộng.</p>
        </Link>
      </div>

      <p className="mt-10 text-sm text-ink/60">
        Các tính năng sẽ được triển khai theo từng PR — phiên bản hiện tại là khung sườn.
      </p>
    </div>
  );
}
