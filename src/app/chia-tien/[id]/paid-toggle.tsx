"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { Check, Circle } from "lucide-react";

export function PaidToggle({
  sessionId,
  participantId,
  paid,
  canEdit,
}: {
  sessionId: string;
  participantId: string;
  paid: boolean;
  canEdit: boolean;
}) {
  const router = useRouter();
  const [loading, setLoading] = useState(false);

  if (!canEdit) {
    return (
      <span className={`badge ${paid ? "bg-brand-100 text-brand-700" : "bg-bg text-ink/60"}`}>
        {paid ? "✓ Đã chuyển" : "Chưa chuyển"}
      </span>
    );
  }

  async function toggle() {
    setLoading(true);
    await fetch(`/api/sessions/${sessionId}/participants/${participantId}/paid`, {
      method: "PATCH",
      headers: { "content-type": "application/json" },
      body: JSON.stringify({ paid: !paid }),
    });
    setLoading(false);
    router.refresh();
  }

  return (
    <button
      onClick={toggle}
      disabled={loading}
      className={paid ? "btn-primary text-xs" : "btn-outline text-xs"}
    >
      {paid ? <Check className="h-3.5 w-3.5" /> : <Circle className="h-3.5 w-3.5" />}
      {paid ? "Đã chuyển" : "Đánh dấu đã chuyển"}
    </button>
  );
}
