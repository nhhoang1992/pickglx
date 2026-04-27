import { Logo } from "./logo";

export function Footer() {
  return (
    <footer className="border-t border-brand-100 bg-white">
      <div className="mx-auto flex max-w-6xl flex-col items-start justify-between gap-4 px-4 py-8 text-sm text-ink/70 md:flex-row md:items-center">
        <div className="flex items-center gap-3">
          <Logo />
          <span>© {new Date().getFullYear()} — Cộng đồng Pickleball Việt Nam</span>
        </div>
        <div className="flex gap-4">
          <a href="https://github.com/nhhoang1992/pickglx" target="_blank" rel="noreferrer" className="hover:text-brand">
            GitHub
          </a>
          <a href="mailto:nhhoang.dgn@gmail.com" className="hover:text-brand">Liên hệ</a>
        </div>
      </div>
    </footer>
  );
}
