// VietQR.io public image API — no auth required.
// Docs: https://www.vietqr.io/danh-sach-api/link-tao-ma-qr/

export const VIETNAM_BANKS = [
  { code: "VCB", name: "Vietcombank" },
  { code: "TCB", name: "Techcombank" },
  { code: "MB", name: "MB Bank" },
  { code: "ACB", name: "ACB" },
  { code: "BIDV", name: "BIDV" },
  { code: "VTB", name: "Vietinbank" },
  { code: "AGR", name: "Agribank" },
  { code: "TPB", name: "TPBank" },
  { code: "VPB", name: "VPBank" },
  { code: "STB", name: "Sacombank" },
  { code: "HDB", name: "HDBank" },
  { code: "OCB", name: "OCB" },
  { code: "SHB", name: "SHB" },
  { code: "VIB", name: "VIB" },
  { code: "MSB", name: "MSB" },
  { code: "EIB", name: "Eximbank" },
  { code: "CAKE", name: "CAKE by VPBank" },
  { code: "TIMO", name: "Timo" },
] as const;

export type BankCode = (typeof VIETNAM_BANKS)[number]["code"];

export function buildVietQRUrl(opts: {
  bankCode: string;
  accountNo: string;
  accountName?: string | null;
  amount: number;
  addInfo?: string;
  template?: "compact" | "compact2" | "qr_only" | "print";
}): string {
  const template = opts.template ?? "compact2";
  const bank = encodeURIComponent(opts.bankCode);
  const acc = encodeURIComponent(opts.accountNo);
  const params = new URLSearchParams();
  params.set("amount", String(Math.max(0, Math.round(opts.amount))));
  if (opts.addInfo) params.set("addInfo", opts.addInfo);
  if (opts.accountName) params.set("accountName", opts.accountName);
  return `https://img.vietqr.io/image/${bank}-${acc}-${template}.png?${params.toString()}`;
}
