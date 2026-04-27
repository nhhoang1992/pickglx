import { notFound, redirect } from "next/navigation";
import Link from "next/link";
import { prisma } from "@/lib/prisma";
import { requireUser } from "@/lib/auth-helpers";
import { RegisterPairForm } from "./form";

export const metadata = { title: "Đăng ký cặp đôi" };

export default async function RegisterPairPage({ params }: { params: { slug: string } }) {
  const user = await requireUser();
  const t = await prisma.tournament.findUnique({ where: { slug: params.slug } });
  if (!t) notFound();
  if (t.status !== "OPEN") redirect(`/giai-dau/${params.slug}`);
  return (
    <div className="mx-auto max-w-2xl px-4 py-10">
      <Link href={`/giai-dau/${params.slug}`} className="text-sm text-brand hover:underline">← Về trang giải</Link>
      <div className="card mt-4">
        <h1 className="text-2xl font-bold text-ink">Đăng ký cặp đôi — {t.name}</h1>
        <RegisterPairForm tournamentId={t.id} slug={t.slug} defaultName={user.name ?? ""} defaultPhone={user.phone ?? ""} />
      </div>
    </div>
  );
}
