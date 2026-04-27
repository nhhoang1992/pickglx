import { RegisterForm } from "./register-form";
import Link from "next/link";

export const metadata = { title: "Đăng ký" };

export default function RegisterPage() {
  return (
    <div className="mx-auto max-w-md px-4 py-16">
      <div className="card">
        <h1 className="text-2xl font-bold text-ink">Tạo tài khoản Pickglx</h1>
        <p className="mt-1 text-sm text-ink/70">
          Đăng ký để gia nhập CLB hoặc tạo CLB mới của riêng bạn.
        </p>
        <div className="mt-6">
          <RegisterForm />
        </div>
        <p className="mt-6 text-center text-sm text-ink/70">
          Đã có tài khoản?{" "}
          <Link href="/auth/login" className="font-medium text-brand hover:underline">
            Đăng nhập
          </Link>
        </p>
      </div>
    </div>
  );
}
