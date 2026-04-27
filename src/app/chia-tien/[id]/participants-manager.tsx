"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import type { SessionParticipant, SplitMethod } from "@prisma/client";
import { Plus, Trash2 } from "lucide-react";

export function ParticipantsManager({
  sessionId,
  splitMethod,
  participants,
}: {
  sessionId: string;
  splitMethod: SplitMethod;
  participants: SessionParticipant[];
}) {
  const router = useRouter();
  const [adding, setAdding] = useState(false);
  const [name, setName] = useState("");
  const [phone, setPhone] = useState("");
  const [weight, setWeight] = useState<string>("1");
  const [matches, setMatches] = useState<string>("0");
  const [hours, setHours] = useState<string>("0");
  const [error, setError] = useState<string | null>(null);

  async function add() {
    if (!name.trim()) return;
    setAdding(true);
    setError(null);
    const body = {
      guestName: name.trim(),
      guestPhone: phone.trim() || undefined,
      weight: Number(weight),
      matchesPlayed: Number(matches),
      hoursPlayed: Number(hours),
    };
    const res = await fetch(`/api/sessions/${sessionId}/participants`, {
      method: "POST",
      headers: { "content-type": "application/json" },
      body: JSON.stringify(body),
    });
    setAdding(false);
    if (!res.ok) {
      const data = await res.json().catch(() => ({}));
      setError(data.error ?? "Có lỗi xảy ra.");
      return;
    }
    setName("");
    setPhone("");
    setWeight("1");
    setMatches("0");
    setHours("0");
    router.refresh();
  }

  async function remove(id: string) {
    if (!confirm("Xoá thành viên này?")) return;
    await fetch(`/api/sessions/${sessionId}/participants/${id}`, { method: "DELETE" });
    router.refresh();
  }

  async function update(id: string, patch: Partial<SessionParticipant>) {
    await fetch(`/api/sessions/${sessionId}/participants/${id}`, {
      method: "PATCH",
      headers: { "content-type": "application/json" },
      body: JSON.stringify(patch),
    });
    router.refresh();
  }

  return (
    <div className="card bg-bg">
      <h3 className="text-sm font-semibold text-ink">Quản lý thành viên</h3>

      <div className="mt-3 grid gap-2 md:grid-cols-[1fr_140px_120px_auto]">
        <input
          value={name}
          onChange={(e) => setName(e.target.value)}
          placeholder="Tên thành viên *"
          className="input"
        />
        <input
          value={phone}
          onChange={(e) => setPhone(e.target.value)}
          placeholder="SĐT (tuỳ chọn)"
          className="input"
        />
        {splitMethod === "BY_MATCH" && (
          <input
            type="number"
            min={0}
            value={matches}
            onChange={(e) => setMatches(e.target.value)}
            placeholder="Số trận"
            className="input"
          />
        )}
        {splitMethod === "BY_HOUR" && (
          <input
            type="number"
            min={0}
            step={0.5}
            value={hours}
            onChange={(e) => setHours(e.target.value)}
            placeholder="Số giờ"
            className="input"
          />
        )}
        {splitMethod === "CUSTOM" && (
          <input
            type="number"
            min={0}
            step={0.1}
            value={weight}
            onChange={(e) => setWeight(e.target.value)}
            placeholder="Hệ số"
            className="input"
          />
        )}
        {splitMethod === "EQUAL" && <div />}
        <button onClick={add} disabled={adding || !name.trim()} className="btn-primary">
          <Plus className="h-4 w-4" /> Thêm
        </button>
      </div>

      {error && <p className="mt-2 text-sm text-red-700">{error}</p>}

      {participants.length > 0 && splitMethod !== "EQUAL" && (
        <div className="mt-4">
          <p className="text-xs text-ink/60">Chỉnh nhanh:</p>
          <ul className="mt-2 divide-y divide-brand-100">
            {participants.map((p) => (
              <li key={p.id} className="flex items-center gap-2 py-2 text-sm">
                <span className="flex-1 truncate text-ink">{p.guestName}</span>
                {splitMethod === "BY_MATCH" && (
                  <input
                    type="number"
                    min={0}
                    defaultValue={p.matchesPlayed}
                    onBlur={(e) => update(p.id, { matchesPlayed: Number(e.target.value) })}
                    className="input w-24"
                  />
                )}
                {splitMethod === "BY_HOUR" && (
                  <input
                    type="number"
                    min={0}
                    step={0.5}
                    defaultValue={p.hoursPlayed}
                    onBlur={(e) => update(p.id, { hoursPlayed: Number(e.target.value) })}
                    className="input w-24"
                  />
                )}
                {splitMethod === "CUSTOM" && (
                  <input
                    type="number"
                    min={0}
                    step={0.1}
                    defaultValue={p.weight}
                    onBlur={(e) => update(p.id, { weight: Number(e.target.value) })}
                    className="input w-24"
                  />
                )}
                <button onClick={() => remove(p.id)} className="btn-ghost p-1.5">
                  <Trash2 className="h-4 w-4 text-red-500" />
                </button>
              </li>
            ))}
          </ul>
        </div>
      )}

      {participants.length > 0 && splitMethod === "EQUAL" && (
        <ul className="mt-3 divide-y divide-brand-100">
          {participants.map((p) => (
            <li key={p.id} className="flex items-center justify-between py-2 text-sm">
              <span className="text-ink">{p.guestName}</span>
              <button onClick={() => remove(p.id)} className="btn-ghost p-1.5">
                <Trash2 className="h-4 w-4 text-red-500" />
              </button>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
