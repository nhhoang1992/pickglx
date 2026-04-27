import type { NextAuthConfig } from "next-auth";

// Edge-compatible config (no Prisma adapter, no Node-only deps).
// Used by middleware. The full `auth.ts` extends this with credentials + adapter.
export const authConfig: NextAuthConfig = {
  session: { strategy: "jwt" },
  pages: { signIn: "/auth/login" },
  providers: [],
  callbacks: {
    async session({ session, token }) {
      if (session.user) {
        session.user.id = (token.id as string) ?? (token.sub ?? "");
        session.user.isAdmin = Boolean(token.isAdmin);
      }
      return session;
    },
  },
};
