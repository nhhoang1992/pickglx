"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";

export function JoinButton({ clubId, loggedIn }: { clubId: string; loggedIn: boolean }) {
  const router = useRouter();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  if (!loggedIn) {
    return (
      <Link href="/auth/login" className="btn-primary">
        Đăng nhập để gia nhập
      </Link>
    );
  }

  async function onJoin() {
    setError(null);
    setLoading(true);
    const res = await fetch(`/api/clubs/${clubId}/join`, { method: "POST" });
    setLoading(false);
    if (!res.ok) {
      const data = await res.json().catch(() => ({}));
      setError(data.error ?? "Có lỗi xảy ra.");
      return;
    }
    router.refresh();
  }

  return (
    <div className="flex flex-col items-end gap-2">
      <button onClick={onJoin} disabled={loading} className="btn-primary">
        {loading ? "Đang gửi..." : "Gửi yêu cầu gia nhập"}
      </button>
      {error && <span className="text-sm text-red-700">{error}</span>}
    </div>
  );
}
