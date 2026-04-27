"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";

export function RegisterForm() {
  const router = useRouter();
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  async function onSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setError(null);
    setLoading(true);
    const fd = new FormData(e.currentTarget);
    const payload = Object.fromEntries(fd.entries());
    const res = await fetch("/api/auth/register", {
      method: "POST",
      headers: { "content-type": "application/json" },
      body: JSON.stringify(payload),
    });
    setLoading(false);
    if (!res.ok) {
      const data = await res.json().catch(() => ({}));
      setError(data.error ?? "Có lỗi xảy ra. Vui lòng thử lại.");
      return;
    }
    router.push("/auth/login?registered=1");
  }

  return (
    <form onSubmit={onSubmit} className="space-y-4">
      <div>
        <label htmlFor="name" className="label">Họ và tên</label>
        <input id="name" name="name" required className="input" placeholder="Nguyễn Văn A" />
      </div>
      <div>
        <label htmlFor="email" className="label">Email</label>
        <input id="email" name="email" type="email" required className="input" placeholder="ban@email.com" />
      </div>
      <div>
        <label htmlFor="phone" className="label">Số điện thoại (tuỳ chọn)</label>
        <input id="phone" name="phone" className="input" placeholder="09xx xxx xxx" />
      </div>
      <div>
        <label htmlFor="password" className="label">Mật khẩu</label>
        <input id="password" name="password" type="password" required minLength={6} className="input" placeholder="Tối thiểu 6 ký tự" />
      </div>
      {error && (
        <div className="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{error}</div>
      )}
      <button type="submit" disabled={loading} className="btn-primary w-full">
        {loading ? "Đang tạo tài khoản..." : "Đăng ký"}
      </button>
    </form>
  );
}
