import type { Config } from "tailwindcss";

const config: Config = {
  content: ["./src/**/*.{js,ts,jsx,tsx,mdx}"],
  theme: {
    extend: {
      colors: {
        // Pickglx — Court Vibes palette
        brand: {
          DEFAULT: "#0E7C66",
          50: "#E8F5F1",
          100: "#C5E5DC",
          200: "#9FD4C5",
          300: "#74C0AB",
          400: "#43A88B",
          500: "#0E7C66",
          600: "#0B6754",
          700: "#085245",
          800: "#063E34",
          900: "#042A24",
        },
        accent: {
          DEFAULT: "#F4C430",
          50: "#FEF6DD",
          100: "#FBEAB1",
          200: "#F8DD80",
          300: "#F6CF50",
          400: "#F4C430",
          500: "#D9A815",
          600: "#A8810F",
        },
        ink: "#0B1F2A",
        bg: "#F7FAF9",
      },
      fontFamily: {
        sans: ["var(--font-sans)", "system-ui", "sans-serif"],
      },
      boxShadow: {
        soft: "0 4px 14px rgba(14, 124, 102, 0.08)",
      },
    },
  },
  plugins: [],
};
export default config;
