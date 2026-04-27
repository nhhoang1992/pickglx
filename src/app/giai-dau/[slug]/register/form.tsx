"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";

export function RegisterPairForm({
  tournamentId,
  slug,
  defaultName,
  defaultPhone,
}: {
  tournamentId: string;
  slug: string;
  defaultName: string;
  defaultPhone: string;
}) {
  const router = useRouter();
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  async function submit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setError(null);
    setLoading(true);
    const fd = new FormData(e.currentTarget);
    const payload = Object.fromEntries(fd.entries());
    const res = await fetch(`/api/tournaments/${tournamentId}/register`, {
      method: "POST",
      headers: { "content-type": "application/json" },
      body: JSON.stringify(payload),
    });
    const data = await res.json().catch(() => ({}));
    setLoading(false);
    if (!res.ok) {
      setError(data.error ?? "Có lỗi xảy ra.");
      return;
    }
    router.push(`/giai-dau/${slug}`);
    router.refresh();
  }

  return (
    <form onSubmit={submit} className="mt-4 space-y-3">
      <div className="grid gap-3 md:grid-cols-2">
        <div>
          <label htmlFor="player1Name" className="label">Tên người chơi 1 *</label>
          <input id="player1Name" name="player1Name" required defaultValue={defaultName} className="input" />
        </div>
        <div>
          <label htmlFor="player2Name" className="label">Tên người chơi 2 *</label>
          <input id="player2Name" name="player2Name" required className="input" />
        </div>
      </div>
      <div>
        <label htmlFor="phone" className="label">SĐT liên hệ *</label>
        <input id="phone" name="phone" required defaultValue={defaultPhone} className="input" />
      </div>
      {error && <div className="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{error}</div>}
      <button type="submit" disabled={loading} className="btn-primary">
        {loading ? "Đang đăng ký..." : "Xác nhận đăng ký"}
      </button>
    </form>
  );
}
