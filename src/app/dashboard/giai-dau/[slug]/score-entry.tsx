"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";

export function ScoreEntry({
  tournamentId,
  matchId,
  scoreA,
  scoreB,
  finished,
}: {
  tournamentId: string;
  matchId: string;
  scoreA: number | null;
  scoreB: number | null;
  finished: boolean;
}) {
  const router = useRouter();
  const [a, setA] = useState<string>(scoreA?.toString() ?? "");
  const [b, setB] = useState<string>(scoreB?.toString() ?? "");
  const [loading, setLoading] = useState(false);

  async function save() {
    setLoading(true);
    await fetch(`/api/tournaments/${tournamentId}/matches/${matchId}`, {
      method: "PATCH",
      headers: { "content-type": "application/json" },
      body: JSON.stringify({ scoreA: Number(a), scoreB: Number(b) }),
    });
    setLoading(false);
    router.refresh();
  }

  return (
    <div className="flex items-center gap-2">
      <input
        type="number"
        min={0}
        max={99}
        value={a}
        onChange={(e) => setA(e.target.value)}
        className="input w-16 text-center"
        placeholder="A"
      />
      <span className="text-ink/40">–</span>
      <input
        type="number"
        min={0}
        max={99}
        value={b}
        onChange={(e) => setB(e.target.value)}
        className="input w-16 text-center"
        placeholder="B"
      />
      <button
        onClick={save}
        disabled={loading || a === "" || b === ""}
        className={finished ? "btn-outline text-xs" : "btn-primary text-xs"}
      >
        {loading ? "..." : finished ? "Sửa" : "Lưu"}
      </button>
    </div>
  );
}
