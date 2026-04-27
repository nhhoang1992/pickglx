import { requireUser } from "@/lib/auth-helpers";
import { CourtForm } from "./form";
import Link from "next/link";

export const metadata = { title: "Tạo sân mới" };

export default async function NewCourtPage() {
  await requireUser();
  return (
    <div className="mx-auto max-w-2xl px-4 py-10">
      <Link href="/dashboard/lich-xe-ve" className="text-sm text-brand hover:underline">← Quản lý sân</Link>
      <div className="card mt-4">
        <h1 className="text-2xl font-bold text-ink">Tạo sân mới</h1>
        <CourtForm />
      </div>
    </div>
  );
}
