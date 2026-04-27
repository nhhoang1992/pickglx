"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";

export function CreateClubForm() {
  const router = useRouter();
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  async function onSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setError(null);
    setLoading(true);
    const fd = new FormData(e.currentTarget);
    const payload = Object.fromEntries(fd.entries());
    const res = await fetch("/api/clubs", {
      method: "POST",
      headers: { "content-type": "application/json" },
      body: JSON.stringify(payload),
    });
    setLoading(false);
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
      setError(data.error ?? "Có lỗi xảy ra. Vui lòng thử lại.");
      return;
    }
    router.push(`/dashboard/clb/${data.slug}`);
    router.refresh();
  }

  return (
    <form onSubmit={onSubmit} className="space-y-4">
      <div>
        <label htmlFor="name" className="label">Tên CLB *</label>
        <input id="name" name="name" required className="input" placeholder="VD: CLB Pickleball Galaxy" />
      </div>
      <div>
        <label htmlFor="location" className="label">Địa điểm sinh hoạt</label>
        <input id="location" name="location" className="input" placeholder="VD: Sân Galaxy, Q. Cầu Giấy, Hà Nội" />
      </div>
      <div>
        <label htmlFor="description" className="label">Mô tả</label>
        <textarea id="description" name="description" rows={4} className="input" placeholder="Giới thiệu ngắn về CLB..." />
      </div>
      {error && <div className="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{error}</div>}
      <button type="submit" disabled={loading} className="btn-primary">
        {loading ? "Đang tạo..." : "Tạo CLB"}
      </button>
    </form>
  );
}
