"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";

export function ScheduleForm({ courtId }: { courtId: string }) {
  const router = useRouter();
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  async function submit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setError(null);
    setLoading(true);
    const fd = new FormData(e.currentTarget);
    const payload: Record<string, unknown> = Object.fromEntries(fd.entries());
    payload.totalSlots = Number(payload.totalSlots);
    payload.pricePerSlot = Number(payload.pricePerSlot);
    payload.durationHours = Number(payload.durationHours);
    payload.courtId = courtId;
    const res = await fetch(`/api/courts/${courtId}/schedules`, {
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
    router.push(`/dashboard/lich-xe-ve/schedule/${data.id}`);
    router.refresh();
  }

  return (
    <form onSubmit={submit} className="mt-4 space-y-3">
      <div className="grid gap-3 md:grid-cols-2">
        <div>
          <label htmlFor="date" className="label">Ngày *</label>
          <input id="date" name="date" type="date" required defaultValue={new Date().toISOString().slice(0, 10)} className="input" />
        </div>
        <div>
          <label htmlFor="startTime" className="label">Giờ bắt đầu *</label>
          <input id="startTime" name="startTime" type="time" required defaultValue="18:00" className="input" />
        </div>
      </div>
      <div className="grid gap-3 md:grid-cols-3">
        <div>
          <label htmlFor="durationHours" className="label">Số giờ *</label>
          <input id="durationHours" name="durationHours" type="number" min={0.5} step={0.5} required defaultValue={2} className="input" />
        </div>
        <div>
          <label htmlFor="totalSlots" className="label">Tổng slot *</label>
          <input id="totalSlots" name="totalSlots" type="number" min={1} required defaultValue={8} className="input" />
        </div>
        <div>
          <label htmlFor="pricePerSlot" className="label">Giá / slot (VND) *</label>
          <input id="pricePerSlot" name="pricePerSlot" type="number" min={0} step={1000} required defaultValue={50000} className="input" />
        </div>
      </div>
      <div>
        <label htmlFor="notes" className="label">Ghi chú</label>
        <textarea id="notes" name="notes" rows={3} className="input" placeholder="VD: Mang theo vợt, có nước miễn phí" />
      </div>
      {error && <div className="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{error}</div>}
      <button type="submit" disabled={loading} className="btn-primary">
        {loading ? "Đang tạo..." : "Mở đăng ký"}
      </button>
    </form>
  );
}
