import { notFound } from "next/navigation";
import Link from "next/link";
import { prisma } from "@/lib/prisma";
import { auth } from "@/auth";
import { formatVnd } from "@/lib/utils";
import { Calendar, MapPin, Banknote } from "lucide-react";
import { RegisterForm } from "./register-form";
import { VIETNAM_BANKS } from "@/lib/vietqr";

export const dynamic = "force-dynamic";

export async function generateMetadata({ params }: { params: { id: string } }) {
  const sched = await prisma.courtSchedule.findUnique({
    where: { id: params.id },
    include: { court: { select: { name: true } } },
  });
  return { title: sched ? `Lịch ${sched.court.name}` : "Lịch xé vé" };
}

export default async function ScheduleDetailPage({ params }: { params: { id: string } }) {
  const sched = await prisma.courtSchedule.findUnique({
    where: { id: params.id },
    include: {
      court: true,
      registrations: {
        where: { status: { in: ["PENDING", "CONFIRMED"] } },
        orderBy: { createdAt: "asc" },
      },
    },
  });
  if (!sched) notFound();

  const userSession = await auth();
  const userId = userSession?.user?.id;

  const myRegistration = userId
    ? sched.registrations.find((r) => r.userId === userId)
    : null;

  const bankLabel = sched.court.bankName
    ? VIETNAM_BANKS.find((b) => b.code === sched.court.bankName)?.name ?? sched.court.bankName
    : null;

  return (
    <div className="mx-auto max-w-3xl px-4 py-10">
      <Link href="/lich-xe-ve" className="text-sm text-brand hover:underline">← Tất cả lịch</Link>

      <div className="card mt-4">
        <h1 className="text-2xl font-bold text-ink md:text-3xl">{sched.court.name}</h1>
        {sched.court.address && (
          <p className="mt-1 flex items-center gap-1 text-sm text-ink/60">
            <MapPin className="h-3.5 w-3.5" /> {sched.court.address}
          </p>
        )}
        <p className="mt-3 flex items-center gap-2 text-base text-ink">
          <Calendar className="h-5 w-5 text-brand" />
          {new Date(sched.date).toLocaleDateString("vi-VN")} · {sched.startTime} · {sched.durationHours} giờ
        </p>
        <div className="mt-4 grid gap-3 sm:grid-cols-3">
          <div className="rounded-lg bg-brand-50 px-3 py-2">
            <p className="text-xs text-ink/60">Giá / slot</p>
            <p className="text-lg font-semibold text-ink">{formatVnd(sched.pricePerSlot)}</p>
          </div>
          <div className="rounded-lg bg-accent-50 px-3 py-2">
            <p className="text-xs text-ink/60">Slot còn lại</p>
            <p className="text-lg font-semibold text-ink">{sched.slotsLeft}/{sched.totalSlots}</p>
          </div>
          <div className="rounded-lg bg-bg px-3 py-2">
            <p className="text-xs text-ink/60">Đã đăng ký</p>
            <p className="text-lg font-semibold text-ink">{sched.registrations.length} lượt</p>
          </div>
        </div>

        {bankLabel && sched.court.bankAccount && (
          <div className="mt-4 flex items-center gap-2 rounded-lg border border-brand-100 px-3 py-2 text-sm text-ink/80">
            <Banknote className="h-4 w-4 text-brand" />
            <span>Chuyển về: <strong>{sched.court.bankHolder ?? "—"}</strong> · {bankLabel} · {sched.court.bankAccount}</span>
          </div>
        )}

        {sched.notes && <p className="mt-4 whitespace-pre-line text-sm text-ink/70">{sched.notes}</p>}
      </div>

      <div className="mt-6">
        {myRegistration ? (
          <div className="card">
            <p className="font-semibold text-ink">Đăng ký của bạn</p>
            <p className="mt-1 text-sm text-ink/70">
              {myRegistration.slots} slot · {formatVnd(myRegistration.amount)} ·{" "}
              {myRegistration.status === "PENDING" && (
                <span className="badge bg-accent-100 text-accent-600">Chờ chủ sân duyệt</span>
              )}
              {myRegistration.status === "CONFIRMED" && (
                <span className="badge bg-brand-100 text-brand-700">Đã xác nhận</span>
              )}
            </p>
            {myRegistration.proofUrl && (
              <p className="mt-2 text-sm">
                Bill: <a href={myRegistration.proofUrl} target="_blank" rel="noreferrer" className="text-brand underline">{myRegistration.proofUrl}</a>
              </p>
            )}
          </div>
        ) : sched.slotsLeft > 0 && sched.status === "OPEN" ? (
          <div className="card">
            <h2 className="font-semibold text-ink">Đăng ký slot</h2>
            {!userId ? (
              <p className="mt-3 text-sm text-ink/70">
                Bạn cần <Link href="/auth/login" className="text-brand underline">đăng nhập</Link> để đăng ký.
              </p>
            ) : (
              <RegisterForm
                scheduleId={sched.id}
                pricePerSlot={sched.pricePerSlot}
                slotsLeft={sched.slotsLeft}
                bankCode={sched.court.bankName}
                bankAccount={sched.court.bankAccount}
                bankHolder={sched.court.bankHolder}
              />
            )}
          </div>
        ) : (
          <div className="card text-center text-ink/60">
            {sched.status !== "OPEN" ? "Lịch đã đóng đăng ký." : "Đã hết slot."}
          </div>
        )}
      </div>

      {sched.registrations.length > 0 && (
        <div className="mt-6 card">
          <h2 className="font-semibold text-ink">Người đã đăng ký</h2>
          <ul className="mt-3 divide-y divide-brand-100">
            {sched.registrations.map((r) => (
              <li key={r.id} className="flex items-center justify-between py-2 text-sm">
                <span className="text-ink">{r.guestName} · {r.slots} slot</span>
                {r.status === "CONFIRMED" ? (
                  <span className="badge bg-brand-100 text-brand-700">✓</span>
                ) : (
                  <span className="badge bg-accent-100 text-accent-600">Chờ duyệt</span>
                )}
              </li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );
}
