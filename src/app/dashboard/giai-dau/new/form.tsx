"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";

export function CreateTournamentForm({ clubs }: { clubs: { id: string; name: string }[] }) {
  const router = useRouter();
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  async function submit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setError(null);
    setLoading(true);
    const fd = new FormData(e.currentTarget);
    const payload: Record<string, unknown> = Object.fromEntries(fd.entries());
    payload.fee = Number(payload.fee || 0);
    payload.maxPairs = payload.maxPairs ? Number(payload.maxPairs) : undefined;
    if (payload.clubId === "") payload.clubId = null;
    const res = await fetch("/api/tournaments", {
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
    router.push(`/dashboard/giai-dau/${data.slug}`);
    router.refresh();
  }

  return (
    <form onSubmit={submit} className="mt-4 space-y-3">
      <div>
        <label htmlFor="name" className="label">Tên giải *</label>
        <input id="name" name="name" required className="input" placeholder="VD: Galaxy Open Tháng 12" />
      </div>
      {clubs.length > 0 && (
        <div>
          <label htmlFor="clubId" className="label">Gắn với CLB</label>
          <select id="clubId" name="clubId" className="input" defaultValue="">
            <option value="">— Không gắn CLB —</option>
            {clubs.map((c) => (
              <option key={c.id} value={c.id}>{c.name}</option>
            ))}
          </select>
        </div>
      )}
      <div className="grid gap-3 md:grid-cols-2">
        <div>
          <label htmlFor="startDate" className="label">Ngày bắt đầu</label>
          <input id="startDate" name="startDate" type="date" className="input" />
        </div>
        <div>
          <label htmlFor="location" className="label">Địa điểm</label>
          <input id="location" name="location" className="input" />
        </div>
      </div>
      <div className="grid gap-3 md:grid-cols-2">
        <div>
          <label htmlFor="fee" className="label">Phí đăng ký / cặp (VND)</label>
          <input id="fee" name="fee" type="number" min={0} step={10000} defaultValue={0} className="input" />
        </div>
        <div>
          <label htmlFor="maxPairs" className="label">Số cặp tối đa</label>
          <input id="maxPairs" name="maxPairs" type="number" min={2} max={64} className="input" placeholder="Để trống = không giới hạn" />
        </div>
      </div>
      {error && <div className="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{error}</div>}
      <button type="submit" disabled={loading} className="btn-primary">
        {loading ? "Đang tạo..." : "Tạo & mở đăng ký"}
      </button>
    </form>
  );
}
