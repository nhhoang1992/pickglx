import { requireUser } from "@/lib/auth-helpers";
import { CreateClubForm } from "./form";
import Link from "next/link";

export const metadata = { title: "Tạo CLB mới" };

export default async function NewClubPage() {
  await requireUser();
  return (
    <div className="mx-auto max-w-2xl px-4 py-12">
      <Link href="/clb" className="text-sm text-brand hover:underline">← Về danh sách CLB</Link>
      <div className="card mt-4">
        <h1 className="text-2xl font-bold text-ink">Tạo CLB mới</h1>
        <p className="mt-1 text-sm text-ink/70">
          Bạn sẽ là <strong>đội trưởng đầu tiên</strong> của CLB. Có thể bổ sung thông tin, logo, đội phó sau.
        </p>
        <div className="mt-6">
          <CreateClubForm />
        </div>
      </div>
    </div>
  );
}
