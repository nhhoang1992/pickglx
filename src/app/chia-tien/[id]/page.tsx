import { notFound } from "next/navigation";
import Link from "next/link";
import { prisma } from "@/lib/prisma";
import { auth } from "@/auth";
import { computeAmounts } from "@/lib/split";
import { formatVnd } from "@/lib/utils";
import { Calendar, MapPin, Banknote, Share2 } from "lucide-react";
import { ParticipantsManager } from "./participants-manager";
import { PaidToggle } from "./paid-toggle";
import { ShareLinkButton } from "./share-link";
import { VIETNAM_BANKS, buildVietQRUrl } from "@/lib/vietqr";

export async function generateMetadata({ params }: { params: { id: string } }) {
  const s = await prisma.playSession.findUnique({ where: { id: params.id }, select: { name: true } });
  return { title: s ? s.name : "Buổi sinh hoạt" };
}

export const dynamic = "force-dynamic";

export default async function SessionDetailPage({ params }: { params: { id: string } }) {
  const session = await prisma.playSession.findUnique({
    where: { id: params.id },
    include: {
      participants: { orderBy: { createdAt: "asc" } },
      createdBy: { select: { name: true, email: true } },
    },
  });
  if (!session) notFound();

  const userSession = await auth();
  const isOwner = userSession?.user?.id === session.createdById || userSession?.user?.isAdmin;

  const amounts = computeAmounts({
    totalCost: session.totalCost,
    method: session.splitMethod,
    participants: session.participants,
  });

  const totalPaid = session.participants
    .filter((p) => p.paid)
    .reduce((sum, p) => sum + (amounts[p.id] ?? 0), 0);

  const bankLabel = session.bankName
    ? VIETNAM_BANKS.find((b) => b.code === session.bankName)?.name ?? session.bankName
    : null;

  return (
    <div className="mx-auto max-w-4xl px-4 py-10">
      <Link href="/chia-tien" className="text-sm text-brand hover:underline">← Tất cả buổi sinh hoạt</Link>

      <div className="card mt-4">
        <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
          <div>
            <h1 className="text-2xl font-bold text-ink md:text-3xl">{session.name}</h1>
            <p className="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-ink/70">
              <span className="flex items-center gap-1">
                <Calendar className="h-3.5 w-3.5" />
                {new Date(session.date).toLocaleDateString("vi-VN")}
              </span>
              {session.location && (
                <span className="flex items-center gap-1">
                  <MapPin className="h-3.5 w-3.5" /> {session.location}
                </span>
              )}
            </p>
          </div>
          <ShareLinkButton />
        </div>

        <div className="mt-4 grid gap-3 sm:grid-cols-3">
          <div className="rounded-lg bg-brand-50 px-3 py-2">
            <p className="text-xs text-ink/60">Tổng chi phí</p>
            <p className="text-lg font-semibold text-ink">{formatVnd(session.totalCost)}</p>
          </div>
          <div className="rounded-lg bg-accent-50 px-3 py-2">
            <p className="text-xs text-ink/60">Đã chuyển</p>
            <p className="text-lg font-semibold text-ink">{formatVnd(totalPaid)}</p>
          </div>
          <div className="rounded-lg bg-bg px-3 py-2">
            <p className="text-xs text-ink/60">Số người</p>
            <p className="text-lg font-semibold text-ink">{session.participants.length}</p>
          </div>
        </div>

        {bankLabel && session.bankAccount && (
          <div className="mt-4 flex items-center gap-2 rounded-lg border border-brand-100 px-3 py-2 text-sm text-ink/80">
            <Banknote className="h-4 w-4 text-brand" />
            <span>
              Chuyển về: <strong>{session.bankHolder ?? "—"}</strong> · {bankLabel} · {session.bankAccount}
            </span>
          </div>
        )}

        {session.notes && (
          <p className="mt-4 whitespace-pre-line text-sm text-ink/70">{session.notes}</p>
        )}
      </div>

      <div className="mt-6">
        <h2 className="font-semibold text-ink">
          Thành viên ({session.participants.length})
        </h2>

        {isOwner && (
          <div className="mt-3">
            <ParticipantsManager
              sessionId={session.id}
              splitMethod={session.splitMethod}
              participants={session.participants}
            />
          </div>
        )}

        <ul className="mt-4 space-y-2">
          {session.participants.length === 0 ? (
            <li className="card text-center text-sm text-ink/60">
              Chưa có thành viên. {isOwner ? "Bấm 'Thêm thành viên' bên trên." : ""}
            </li>
          ) : (
            session.participants.map((p) => {
              const amount = amounts[p.id] ?? 0;
              const qrUrl =
                session.bankName && session.bankAccount && amount > 0
                  ? buildVietQRUrl({
                      bankCode: session.bankName,
                      accountNo: session.bankAccount,
                      accountName: session.bankHolder,
                      amount,
                      addInfo: `${p.guestName} ${session.name}`.slice(0, 60),
                    })
                  : null;
              return (
                <li key={p.id} className="card">
                  <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                    <div>
                      <p className="font-medium text-ink">{p.guestName}</p>
                      <p className="text-xs text-ink/60">
                        {session.splitMethod === "BY_MATCH" && `${p.matchesPlayed} trận`}
                        {session.splitMethod === "BY_HOUR" && `${p.hoursPlayed} giờ`}
                        {session.splitMethod === "CUSTOM" && `Hệ số ${p.weight}`}
                        {session.splitMethod === "EQUAL" && "Chia đều"}
                        {p.guestPhone ? ` · ${p.guestPhone}` : ""}
                      </p>
                      <p className="mt-1 text-lg font-semibold text-brand">{formatVnd(amount)}</p>
                    </div>
                    <div className="flex flex-col items-start gap-2 md:items-end">
                      <PaidToggle
                        sessionId={session.id}
                        participantId={p.id}
                        paid={p.paid}
                        canEdit={isOwner === true || (userSession?.user?.id === p.userId)}
                      />
                      {qrUrl && (
                        <a href={qrUrl} target="_blank" rel="noreferrer" className="btn-outline text-xs">
                          <Share2 className="h-3.5 w-3.5" /> Xem QR chuyển khoản
                        </a>
                      )}
                    </div>
                  </div>
                </li>
              );
            })
          )}
        </ul>
      </div>
    </div>
  );
}
