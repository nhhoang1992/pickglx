import { cn } from "@/lib/utils";

export function Logo({ className, withText = true }: { className?: string; withText?: boolean }) {
  return (
    <span className={cn("inline-flex items-center gap-2", className)}>
      <svg
        width="32"
        height="32"
        viewBox="0 0 32 32"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden
      >
        <rect width="32" height="32" rx="8" fill="#0E7C66" />
        <circle cx="11" cy="21" r="5" fill="#F4C430" />
        <path
          d="M18 6 L26 6 L26 14 Q26 20 21 21 L17 21 L17 13 Q17 6 18 6 Z"
          fill="#F7FAF9"
        />
        <circle cx="21.5" cy="11.5" r="0.9" fill="#0E7C66" />
        <circle cx="23.5" cy="13.5" r="0.9" fill="#0E7C66" />
        <circle cx="19.5" cy="13.5" r="0.9" fill="#0E7C66" />
        <circle cx="21.5" cy="15.5" r="0.9" fill="#0E7C66" />
      </svg>
      {withText && (
        <span className="text-lg font-bold tracking-tight text-ink">
          Pick<span className="text-brand">glx</span>
        </span>
      )}
    </span>
  );
}
