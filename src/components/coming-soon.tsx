import Link from "next/link";

export function ComingSoon({
  title,
  description,
}: {
  title: string;
  description: string;
}) {
  return (
    <div className="mx-auto max-w-3xl px-4 py-20 text-center">
      <span className="badge bg-accent-100 text-accent-600">Sắp ra mắt</span>
      <h1 className="mt-4 text-3xl font-bold text-ink md:text-4xl">{title}</h1>
      <p className="mt-4 text-ink/70">{description}</p>
      <Link href="/" className="btn-outline mt-8 inline-flex">
        Về trang chủ
      </Link>
    </div>
  );
}
