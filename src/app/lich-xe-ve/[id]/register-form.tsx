"use client";

import { useState, useMemo } from "react";
import { useRouter } from "next/navigation";
import { buildVietQRUrl } from "@/lib/vietqr";
import { formatVnd } from "@/lib/utils";

export function RegisterForm({
  scheduleId,
  pricePerSlot,
  slotsLeft,
  bankCode,
  bankAccount,
  bankHolder,
}: {
  scheduleId: string;
  pricePerSlot: number;
  slotsLeft: number;
  bankCode: string | null;
  bankAccount: string | null;
  bankHolder: string | null;
}) {
  const router = useRouter();
  const [slots, setSlots] = useState(1);
  const [phone, setPhone] = useState("");
  const [proofUrl, setProofUrl] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const amount = slots * pricePerSlot;
  const qrUrl = useMemo(() => {
    if (!bankCode || !bankAccount || amount <= 0) return null;
    return buildVietQRUrl({
      bankCode,
      accountNo: bankAccount,
      accountName: bankHolder,
      amount,
      addInfo: `Pickglx ${scheduleId.slice(-6)}`,
    });
  }, [bankCode, bankAccount, bankHolder, amount, scheduleId]);

  async function submit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setLoading(true);
    setError(null);
    const res = await fetch(`/api/schedules/${scheduleId}/register`, {
      method: "POST",
      headers: { "content-type": "application/json" },
      body: JSON.stringify({ slots, guestPhone: phone, proofUrl: proofUrl || undefined }),
    });
    setLoading(false);
    if (!res.ok) {
      const data = await res.json().catch(() => ({}));
      setError(data.error ?? "Có lỗi xảy ra.");
      return;
    }
    router.refresh();
  }

  return (
    <form onSubmit={submit} className="mt-3 space-y-3">
      <div className="grid gap-3 md:grid-cols-2">
        <div>
          <label htmlFor="slots" className="label">Số slot *</label>
          <input
            id="slots"
            type="number"
            min={1}
            max={slotsLeft}
            value={slots}
            onChange={(e) => setSlots(Math.max(1, Math.min(slotsLeft, Number(e.target.value))))}
            className="input"
          />
        </div>
        <div>
          <label htmlFor="phone" className="label">SĐT liên hệ *</label>
          <input
            id="phone"
            required
            value={phone}
            onChange={(e) => setPhone(e.target.value)}
            className="input"
            placeholder="0901234567"
          />
        </div>
      </div>

      <div className="rounded-lg bg-brand-50/40 p-3 text-sm">
        <p>Tổng: <strong className="text-brand">{formatVnd(amount)}</strong></p>
        {qrUrl && (
          <div className="mt-2 flex flex-col items-start gap-2">
            <a href={qrUrl} target="_blank" rel="noreferrer" className="text-xs text-brand underline">
              Xem QR VietQR
            </a>
          </div>
        )}
      </div>

      <div>
        <label htmlFor="proof" className="label">Link bill chuyển khoản (sau khi chuyển)</label>
        <input
          id="proof"
          value={proofUrl}
          onChange={(e) => setProofUrl(e.target.value)}
          className="input"
          placeholder="Dán link ảnh bill (Drive, Imgur, ...)"
        />
      </div>

      {error && <div className="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{error}</div>}

      <button type="submit" disabled={loading} className="btn-primary">
        {loading ? "Đang gửi..." : "Đăng ký slot"}
      </button>
    </form>
  );
}
