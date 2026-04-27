"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { VIETNAM_BANKS } from "@/lib/vietqr";

export function CreateSessionForm({ clubs }: { clubs: { id: string; name: string }[] }) {
  const router = useRouter();
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  async function onSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setError(null);
    setLoading(true);
    const fd = new FormData(e.currentTarget);
    const payload: Record<string, unknown> = Object.fromEntries(fd.entries());
    payload.totalCost = Number(payload.totalCost);
    if (payload.clubId === "") payload.clubId = null;
    const res = await fetch("/api/sessions", {
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
    router.push(`/chia-tien/${data.id}`);
    router.refresh();
  }

  return (
    <form onSubmit={onSubmit} className="space-y-4">
      <div>
        <label htmlFor="name" className="label">Tên buổi *</label>
        <input id="name" name="name" required className="input" placeholder="VD: Sinh hoạt thứ 7 — Sân Galaxy" />
      </div>
      <div className="grid gap-4 md:grid-cols-2">
        <div>
          <label htmlFor="date" className="label">Ngày *</label>
          <input id="date" name="date" type="date" required defaultValue={new Date().toISOString().slice(0, 10)} className="input" />
        </div>
        <div>
          <label htmlFor="location" className="label">Địa điểm</label>
          <input id="location" name="location" className="input" placeholder="VD: Sân Galaxy" />
        </div>
      </div>

      {clubs.length > 0 && (
        <div>
          <label htmlFor="clubId" className="label">Gắn với CLB (tuỳ chọn)</label>
          <select id="clubId" name="clubId" className="input" defaultValue="">
            <option value="">— Không gắn CLB —</option>
            {clubs.map((c) => (
              <option key={c.id} value={c.id}>{c.name}</option>
            ))}
          </select>
        </div>
      )}

      <div className="grid gap-4 md:grid-cols-2">
        <div>
          <label htmlFor="totalCost" className="label">Tổng chi phí (VND) *</label>
          <input id="totalCost" name="totalCost" type="number" min={0} step={1000} required className="input" placeholder="VD: 500000" />
        </div>
        <div>
          <label htmlFor="splitMethod" className="label">Cách chia *</label>
          <select id="splitMethod" name="splitMethod" required className="input" defaultValue="EQUAL">
            <option value="EQUAL">Chia đều theo đầu người</option>
            <option value="BY_MATCH">Theo số trận chơi</option>
            <option value="BY_HOUR">Theo số giờ chơi</option>
            <option value="CUSTOM">Tuỳ chỉnh hệ số</option>
          </select>
        </div>
      </div>

      <div className="rounded-xl border border-brand-100 bg-brand-50/40 p-4">
        <p className="text-sm font-medium text-ink">Tài khoản nhận tiền (sinh QR VietQR)</p>
        <div className="mt-3 grid gap-3 md:grid-cols-2">
          <div>
            <label htmlFor="bankName" className="label">Ngân hàng</label>
            <select id="bankName" name="bankName" className="input" defaultValue="">
              <option value="">— Chọn ngân hàng —</option>
              {VIETNAM_BANKS.map((b) => (
                <option key={b.code} value={b.code}>{b.name}</option>
              ))}
            </select>
          </div>
          <div>
            <label htmlFor="bankHolder" className="label">Tên chủ tài khoản</label>
            <input id="bankHolder" name="bankHolder" className="input" placeholder="NGUYEN VAN A" />
          </div>
        </div>
        <div className="mt-3">
          <label htmlFor="bankAccount" className="label">Số tài khoản</label>
          <input id="bankAccount" name="bankAccount" className="input" placeholder="VD: 1234567890" />
        </div>
      </div>

      <div>
        <label htmlFor="notes" className="label">Ghi chú</label>
        <textarea id="notes" name="notes" rows={3} className="input" placeholder="VD: 4 giờ sân + 6 ống cầu + nước" />
      </div>

      {error && <div className="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{error}</div>}

      <button type="submit" disabled={loading} className="btn-primary">
        {loading ? "Đang tạo..." : "Tạo buổi"}
      </button>
    </form>
  );
}
