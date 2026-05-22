/** API client for the backend FastAPI service. */

const API_URL =
  process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api/v1";

export interface OrderItemInput {
  product_id: number;
  product_name: string;
  product_sku: string;
  variant?: string;
  price: number;
  quantity: number;
}

export interface OrderCreateInput {
  customer_name: string;
  customer_phone: string;
  customer_email?: string;
  shipping_address: string;
  shipping_city?: string;
  shipping_district?: string;
  shipping_ward?: string;
  payment_method: "cod" | "momo" | "zalopay";
  note?: string;
  items: OrderItemInput[];
}

export interface OrderResponse {
  id: number;
  order_number: string;
  status: string;
  payment_method: string;
  payment_status: string;
  subtotal: number;
  shipping_fee: number;
  total: number;
  created_at: string;
}

export interface PaymentSessionResponse {
  provider: string;
  order_number: string;
  amount: number;
  payment_url?: string | null;
  qr_code_url?: string | null;
  deeplink?: string | null;
  payment_status: string;
  message?: string;
}

async function request<T>(path: string, init?: RequestInit): Promise<T> {
  const res = await fetch(`${API_URL}${path}`, {
    headers: { "Content-Type": "application/json", ...(init?.headers || {}) },
    ...init,
  });
  if (!res.ok) {
    const text = await res.text();
    throw new Error(`API ${res.status} ${path}: ${text}`);
  }
  return res.json();
}

export async function listProducts(params?: {
  category?: string;
  q?: string;
  limit?: number;
  offset?: number;
}) {
  const qs = new URLSearchParams(
    Object.entries(params || {}).reduce((acc, [k, v]) => {
      if (v !== undefined && v !== null && v !== "") acc[k] = String(v);
      return acc;
    }, {} as Record<string, string>),
  );
  return request<{ items: unknown[]; total: number }>(
    `/products${qs.toString() ? `?${qs}` : ""}`,
  );
}

export async function getProductBySlug(slug: string) {
  return request<unknown>(`/products/${slug}`);
}

export async function createOrder(data: OrderCreateInput): Promise<OrderResponse> {
  return request<OrderResponse>("/orders", {
    method: "POST",
    body: JSON.stringify(data),
  });
}

export async function createPaymentSession(
  provider: "momo" | "zalopay" | "cod",
  orderNumber: string,
): Promise<PaymentSessionResponse> {
  return request<PaymentSessionResponse>(
    `/payments/${provider}/create/${orderNumber}`,
    { method: "POST" },
  );
}
