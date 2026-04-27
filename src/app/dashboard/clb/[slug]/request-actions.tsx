"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";

export function JoinRequestActions({ clubId, requestId }: { clubId: string; requestId: string }) {
  const router = useRouter();
  const [loading, setLoading] = useState<"approve" | "reject" | null>(null);

  async function handle(action: "approve" | "reject") {
    setLoading(action);
    const res = await fetch(`/api/clubs/${clubId}/requests/${requestId}`, {
      method: "PATCH",
      headers: { "content-type": "application/json" },
      body: JSON.stringify({ action }),
    });
    setLoading(null);
    if (res.ok) router.refresh();
  }

  return (
    <div className="flex gap-2">
      <button
        onClick={() => handle("approve")}
        disabled={loading !== null}
        className="btn-primary text-xs"
      >
        {loading === "approve" ? "Đang duyệt..." : "Duyệt"}
      </button>
      <button
        onClick={() => handle("reject")}
        disabled={loading !== null}
        className="btn-outline text-xs"
      >
        {loading === "reject" ? "Đang từ chối..." : "Từ chối"}
      </button>
    </div>
  );
}
