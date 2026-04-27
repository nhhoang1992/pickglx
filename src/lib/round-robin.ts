// Standard "circle method" round-robin scheduling.
// Returns an array of rounds; each round is an array of [pairAId, pairBId] matches.
// If number of pairs is odd, one pair sits out per round (BYE).

export function generateRoundRobin(pairIds: string[]): { round: number; pairAId: string; pairBId: string }[] {
  if (pairIds.length < 2) return [];

  // Add a placeholder if odd
  const pairs = [...pairIds];
  const isOdd = pairs.length % 2 === 1;
  if (isOdd) pairs.push("BYE");

  const n = pairs.length;
  const rounds = n - 1;
  const half = n / 2;

  const result: { round: number; pairAId: string; pairBId: string }[] = [];
  // Fix first pair, rotate the rest
  const arr = [...pairs];
  for (let r = 0; r < rounds; r++) {
    for (let i = 0; i < half; i++) {
      const a = arr[i];
      const b = arr[n - 1 - i];
      if (a !== "BYE" && b !== "BYE") {
        result.push({ round: r + 1, pairAId: a, pairBId: b });
      }
    }
    // Rotate: keep arr[0], shift others
    const last = arr[n - 1];
    for (let i = n - 1; i > 1; i--) arr[i] = arr[i - 1];
    arr[1] = last;
  }

  return result;
}

export type Standing = {
  pairId: string;
  played: number;
  won: number;
  lost: number;
  pointsFor: number;
  pointsAgainst: number;
  diff: number;
};

export function computeStandings(
  pairs: { id: string }[],
  matches: { pairAId: string; pairBId: string; scoreA: number | null; scoreB: number | null; status: string }[],
): Standing[] {
  const map = new Map<string, Standing>();
  for (const p of pairs) {
    map.set(p.id, { pairId: p.id, played: 0, won: 0, lost: 0, pointsFor: 0, pointsAgainst: 0, diff: 0 });
  }
  for (const m of matches) {
    if (m.status !== "FINISHED" || m.scoreA === null || m.scoreB === null) continue;
    const a = map.get(m.pairAId);
    const b = map.get(m.pairBId);
    if (!a || !b) continue;
    a.played += 1;
    b.played += 1;
    a.pointsFor += m.scoreA;
    a.pointsAgainst += m.scoreB;
    b.pointsFor += m.scoreB;
    b.pointsAgainst += m.scoreA;
    if (m.scoreA > m.scoreB) {
      a.won += 1;
      b.lost += 1;
    } else if (m.scoreB > m.scoreA) {
      b.won += 1;
      a.lost += 1;
    }
  }
  const list = Array.from(map.values());
  for (const s of list) s.diff = s.pointsFor - s.pointsAgainst;
  return list.sort((x, y) => {
    if (y.won !== x.won) return y.won - x.won;
    if (y.diff !== x.diff) return y.diff - x.diff;
    return y.pointsFor - x.pointsFor;
  });
}
