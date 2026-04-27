"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import type { TicketStatus } from "@prisma/client";

export function RegistrationActions({
  scheduleId,
  registrationId,
  status,
}: {
  scheduleId: string;
  registrationId: string;
  status: TicketStatus;
  slots: number;
}) {
  const router = useRouter();
  const [loading, setLoading] = useState<TicketStatus | null>(null);

  async function update(action: "confirm" | "reject") {
    setLoading(action === "confirm" ? "CONFIRMED" : "REJECTED");
    await fetch(`/api/schedules/${scheduleId}/registrations/${registrationId}`, {
      method: "PATCH",
      headers: { "content-type": "application/json" },
      body: JSON.stringify({ action }),
    });
    setLoading(null);
    router.refresh();
  }

  if (status === "CONFIRMED") {
    return <span className="badge bg-brand-100 text-brand-700">Đã xác nhận</span>;
  }
  if (status === "REJECTED") {
    return <span className="badge bg-red-100 text-red-700">Đã từ chối</span>;
  }
  if (status === "CANCELLED") {
    return <span className="badge bg-bg text-ink/60">Đã huỷ</span>;
  }

  return (
    <div className="flex gap-2">
      <button onClick={() => update("confirm")} disabled={loading !== null} className="btn-primary text-xs">
        {loading === "CONFIRMED" ? "Đang xác nhận..." : "Xác nhận"}
      </button>
      <button onClick={() => update("reject")} disabled={loading !== null} className="btn-outline text-xs">
        {loading === "REJECTED" ? "Đang từ chối..." : "Từ chối"}
      </button>
    </div>
  );
}
