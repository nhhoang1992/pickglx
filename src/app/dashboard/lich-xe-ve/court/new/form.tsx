"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { VIETNAM_BANKS } from "@/lib/vietqr";

export function CourtForm() {
  const router = useRouter();
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  async function submit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setError(null);
    setLoading(true);
    const fd = new FormData(e.currentTarget);
    const payload = Object.fromEntries(fd.entries());
    const res = await fetch("/api/courts", {
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
    router.push(`/dashboard/lich-xe-ve/court/${data.id}/schedule/new`);
    router.refresh();
  }

  return (
    <form onSubmit={submit} className="mt-4 space-y-3">
      <div>
        <label htmlFor="name" className="label">Tên sân *</label>
        <input id="name" name="name" required className="input" placeholder="VD: Sân Pickleball Galaxy" />
      </div>
      <div>
        <label htmlFor="address" className="label">Địa chỉ</label>
        <input id="address" name="address" className="input" placeholder="VD: 123 Cầu Giấy, Hà Nội" />
      </div>
      <div>
        <label htmlFor="contact" className="label">Liên hệ (SĐT)</label>
        <input id="contact" name="contact" className="input" />
      </div>
      <div className="rounded-xl border border-brand-100 bg-brand-50/40 p-3">
        <p className="text-sm font-medium text-ink">Tài khoản nhận tiền (VietQR)</p>
        <div className="mt-3 grid gap-3 md:grid-cols-2">
          <div>
            <label htmlFor="bankName" className="label">Ngân hàng</label>
            <select id="bankName" name="bankName" className="input" defaultValue="">
              <option value="">— Chọn —</option>
              {VIETNAM_BANKS.map((b) => (
                <option key={b.code} value={b.code}>{b.name}</option>
              ))}
            </select>
          </div>
          <div>
            <label htmlFor="bankHolder" className="label">Chủ TK</label>
            <input id="bankHolder" name="bankHolder" className="input" />
          </div>
        </div>
        <div className="mt-3">
          <label htmlFor="bankAccount" className="label">Số TK</label>
          <input id="bankAccount" name="bankAccount" className="input" />
        </div>
      </div>
      {error && <div className="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{error}</div>}
      <button type="submit" disabled={loading} className="btn-primary">
        {loading ? "Đang tạo..." : "Tạo sân"}
      </button>
    </form>
  );
}
