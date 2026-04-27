"use client";

import { Share2, Check } from "lucide-react";
import { useState } from "react";

export function ShareLinkButton() {
  const [copied, setCopied] = useState(false);
  async function copy() {
    if (typeof window === "undefined") return;
    await navigator.clipboard.writeText(window.location.href);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  }
  return (
    <button onClick={copy} className="btn-outline">
      {copied ? <Check className="h-4 w-4" /> : <Share2 className="h-4 w-4" />}
      {copied ? "Đã copy link" : "Chia sẻ"}
    </button>
  );
}
