import type { SessionParticipant, SplitMethod } from "@prisma/client";

export type SplitInput = {
  totalCost: number;
  method: SplitMethod;
  participants: Pick<
    SessionParticipant,
    "id" | "matchesPlayed" | "hoursPlayed" | "weight"
  >[];
};

export function computeAmounts(input: SplitInput): Record<string, number> {
  const { totalCost, method, participants } = input;
  if (participants.length === 0) return {};

  const weights: Record<string, number> = {};
  for (const p of participants) {
    let w: number;
    switch (method) {
      case "EQUAL":
        w = 1;
        break;
      case "BY_MATCH":
        w = Math.max(0, p.matchesPlayed);
        break;
      case "BY_HOUR":
        w = Math.max(0, p.hoursPlayed);
        break;
      case "CUSTOM":
        w = Math.max(0, p.weight);
        break;
      default:
        w = 1;
    }
    weights[p.id] = w;
  }

  const totalWeight = Object.values(weights).reduce((a, b) => a + b, 0);
  if (totalWeight === 0) {
    // Fall back to equal split
    const equalAmount = Math.round(totalCost / participants.length);
    const result: Record<string, number> = {};
    for (const p of participants) result[p.id] = equalAmount;
    return adjustRounding(result, totalCost);
  }

  const result: Record<string, number> = {};
  for (const p of participants) {
    result[p.id] = Math.round((weights[p.id] / totalWeight) * totalCost);
  }
  return adjustRounding(result, totalCost);
}

function adjustRounding(amounts: Record<string, number>, target: number): Record<string, number> {
  const keys = Object.keys(amounts);
  if (keys.length === 0) return amounts;
  const sum = keys.reduce((s, k) => s + amounts[k], 0);
  const diff = target - sum;
  if (diff !== 0) {
    amounts[keys[0]] += diff;
  }
  return amounts;
}
