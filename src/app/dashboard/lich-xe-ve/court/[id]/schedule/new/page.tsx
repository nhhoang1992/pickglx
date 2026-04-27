import { notFound, redirect } from "next/navigation";
import Link from "next/link";
import { requireUser } from "@/lib/auth-helpers";
import { prisma } from "@/lib/prisma";
import { ScheduleForm } from "./form";

export const metadata = { title: "Tạo lịch xé vé" };

export default async function NewSchedulePage({ params }: { params: { id: string } }) {
  const user = await requireUser();
  const court = await prisma.court.findUnique({ where: { id: params.id } });
  if (!court) notFound();
  if (court.ownerUserId !== user.id && !user.isAdmin) redirect("/dashboard/lich-xe-ve");
  return (
    <div className="mx-auto max-w-2xl px-4 py-10">
      <Link href="/dashboard/lich-xe-ve" className="text-sm text-brand hover:underline">← Quản lý sân</Link>
      <div className="card mt-4">
        <h1 className="text-2xl font-bold text-ink">Tạo lịch — {court.name}</h1>
        <ScheduleForm courtId={court.id} />
      </div>
    </div>
  );
}
