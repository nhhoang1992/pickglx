import { notFound, redirect } from "next/navigation";
import Link from "next/link";
import { requireUser } from "@/lib/auth-helpers";
import { prisma } from "@/lib/prisma";
import { formatVnd } from "@/lib/utils";
import { RegistrationActions } from "./actions";

export const metadata = { title: "Quản lý lịch" };
export const dynamic = "force-dynamic";

export default async function ManageSchedulePage({ params }: { params: { id: string } }) {
  const user = await requireUser();
  const sched = await prisma.courtSchedule.findUnique({
    where: { id: params.id },
    include: {
      court: true,
      registrations: {
        include: { user: { select: { name: true, email: true } } },
        orderBy: { createdAt: "asc" },
      },
    },
  });
  if (!sched) notFound();
  if (sched.court.ownerUserId !== user.id && !user.isAdmin) {
    redirect("/dashboard/lich-xe-ve");
  }

  return (
    <div className="mx-auto max-w-4xl px-4 py-10">
      <Link href="/dashboard/lich-xe-ve" className="text-sm text-brand hover:underline">← Quản lý sân</Link>
      <div className="card mt-4">
        <h1 className="text-2xl font-bold text-ink">{sched.court.name}</h1>
        <p className="mt-1 text-sm text-ink/70">
          {new Date(sched.date).toLocaleDateString("vi-VN")} · {sched.startTime} · {sched.durationHours}h ·{" "}
          {formatVnd(sched.pricePerSlot)}/slot · còn {sched.slotsLeft}/{sched.totalSlots}
        </p>
      </div>

      <h2 className="mt-6 font-semibold text-ink">Đăng ký ({sched.registrations.length})</h2>
      {sched.registrations.length === 0 ? (
        <p className="mt-3 text-sm text-ink/60">Chưa có ai đăng ký.</p>
      ) : (
        <ul className="mt-3 space-y-2">
          {sched.registrations.map((r) => (
            <li key={r.id} className="card flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <p className="font-medium text-ink">{r.guestName}</p>
                <p className="text-xs text-ink/60">{r.guestPhone} · {r.slots} slot · {formatVnd(r.amount)}</p>
                {r.proofUrl && (
                  <a href={r.proofUrl} target="_blank" rel="noreferrer" className="text-xs text-brand underline">
                    Xem bill chuyển khoản
                  </a>
                )}
              </div>
              <RegistrationActions
                scheduleId={sched.id}
                registrationId={r.id}
                status={r.status}
                slots={r.slots}
              />
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
