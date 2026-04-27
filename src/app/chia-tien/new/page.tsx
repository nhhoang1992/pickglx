import { requireUser } from "@/lib/auth-helpers";
import { CreateSessionForm } from "./form";
import Link from "next/link";
import { prisma } from "@/lib/prisma";

export const metadata = { title: "Tạo buổi sinh hoạt" };

export default async function NewSessionPage() {
  const user = await requireUser();
  const memberships = await prisma.clubMember.findMany({
    where: { userId: user.id, status: "ACTIVE" },
    include: { club: { select: { id: true, name: true } } },
  });
  return (
    <div className="mx-auto max-w-2xl px-4 py-12">
      <Link href="/chia-tien" className="text-sm text-brand hover:underline">← Về danh sách buổi sinh hoạt</Link>
      <div className="card mt-4">
        <h1 className="text-2xl font-bold text-ink">Tạo buổi sinh hoạt mới</h1>
        <p className="mt-1 text-sm text-ink/70">
          Sau khi tạo, bạn sẽ thêm thành viên + hệ thống tự chia tiền và sinh QR.
        </p>
        <div className="mt-6">
          <CreateSessionForm clubs={memberships.map((m) => m.club)} />
        </div>
      </div>
    </div>
  );
}
