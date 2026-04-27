import { auth } from "@/auth";
import { NextResponse } from "next/server";

const protectedPaths = ["/dashboard"];

export default auth((req) => {
  const { pathname } = req.nextUrl;
  const isProtected = protectedPaths.some((p) => pathname === p || pathname.startsWith(p + "/"));
  if (isProtected && !req.auth) {
    const url = new URL("/auth/login", req.url);
    url.searchParams.set("from", pathname);
    return NextResponse.redirect(url);
  }
  return NextResponse.next();
});

export const config = {
  matcher: ["/dashboard/:path*"],
};
