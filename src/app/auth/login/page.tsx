import { LoginForm } from "./login-form";
import Link from "next/link";
import { Suspense } from "react";

export const metadata = { title: "Đăng nhập" };

export default function LoginPage() {
  return (
    <div className="mx-auto max-w-md px-4 py-16">
      <div className="card">
        <h1 className="text-2xl font-bold text-ink">Đăng nhập</h1>
        <p className="mt-1 text-sm text-ink/70">Chào mừng bạn quay lại Pickglx.</p>
        <div className="mt-6">
          <Suspense>
            <LoginForm />
          </Suspense>
        </div>
        <p className="mt-6 text-center text-sm text-ink/70">
          Chưa có tài khoản?{" "}
          <Link href="/auth/register" className="font-medium text-brand hover:underline">
            Đăng ký
          </Link>
        </p>
      </div>
    </div>
  );
}
