"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";

export function StartButton({ tournamentId, canStart }: { tournamentId: string; canStart: boolean }) {
  const router = useRouter();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  async function start() {
    if (!confirm("Khoá đăng ký và sinh lịch round-robin? Sau bước này không thêm cặp được nữa.")) return;
    setLoading(true);
    setError(null);
    const res = await fetch(`/api/tournaments/${tournamentId}/start`, { method: "POST" });
    const data = await res.json().catch(() => ({}));
    setLoading(false);
    if (!res.ok) {
      setError(data.error ?? "Có lỗi.");
      return;
    }
    router.refresh();
  }

  return (
    <div>
      <button onClick={start} disabled={loading || !canStart} className="btn-primary">
        {loading ? "Đang khoá..." : "Khoá & sinh lịch"}
      </button>
      {error && <p className="mt-1 text-xs text-red-700">{error}</p>}
      {!canStart && <p className="mt-1 text-xs text-ink/60">Cần ít nhất 2 cặp.</p>}
    </div>
  );
}

export function FinishButton({ tournamentId }: { tournamentId: string }) {
  const router = useRouter();
  const [loading, setLoading] = useState(false);

  async function finish() {
    if (!confirm("Kết thúc giải?")) return;
    setLoading(true);
    await fetch(`/api/tournaments/${tournamentId}/finish`, { method: "POST" });
    setLoading(false);
    router.refresh();
  }

  return (
    <button onClick={finish} disabled={loading} className="btn-outline">
      {loading ? "Đang kết thúc..." : "Kết thúc giải"}
    </button>
  );
}
