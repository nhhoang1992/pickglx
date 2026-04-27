import Link from "next/link";
import { Logo } from "./logo";
import { auth } from "@/auth";

const navLinks = [
  { href: "/giai-dau", label: "Giải đấu" },
  { href: "/chia-tien", label: "Chia tiền" },
  { href: "/lich-xe-ve", label: "Lịch xé vé" },
  { href: "/clb", label: "CLB" },
];

export async function Header() {
  const session = await auth();
  return (
    <header className="sticky top-0 z-40 border-b border-brand-100 bg-white/80 backdrop-blur">
      <div className="mx-auto flex h-16 max-w-6xl items-center justify-between px-4">
        <Link href="/" aria-label="Pickglx">
          <Logo />
        </Link>
        <nav className="hidden gap-1 md:flex">
          {navLinks.map((l) => (
            <Link
              key={l.href}
              href={l.href}
              className="rounded-md px-3 py-2 text-sm font-medium text-ink hover:bg-brand-50"
            >
              {l.label}
            </Link>
          ))}
        </nav>
        <div className="flex items-center gap-2">
          {session?.user ? (
            <Link href="/dashboard" className="btn-primary">
              {session.user.name ?? "Tài khoản"}
            </Link>
          ) : (
            <>
              <Link href="/auth/login" className="btn-ghost">
                Đăng nhập
              </Link>
              <Link href="/auth/register" className="btn-primary">
                Đăng ký
              </Link>
            </>
          )}
        </div>
      </div>
    </header>
  );
}
