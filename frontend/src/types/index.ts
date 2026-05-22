export interface Product {
  id: string;
  name: string;
  slug: string;
  sku: string;
  price: number;
  originalPrice: number;
  discount: number;
  images: string[];
  category: Category;
  categoryId: string;
  description: string;
  specifications: Record<string, string>;
  variants: ProductVariant[];
  stock: number;
  sold: number;
  rating: number;
  reviewCount: number;
  shopeeRating: number;
  shopeeSold: number;
  shopeeReviews: ShopeeReview[];
  compatibleModels: string[];
  isFlashSale: boolean;
  flashSalePrice?: number;
  flashSaleEnd?: string;
  createdAt: string;
  updatedAt: string;
}

export interface ProductVariant {
  id: string;
  name: string;
  options: VariantOption[];
}

export interface VariantOption {
  id: string;
  value: string;
  image?: string;
  price?: number;
  stock: number;
}

export interface Category {
  id: string;
  name: string;
  slug: string;
  icon: string;
  image?: string;
  parentId?: string;
  children?: Category[];
  productCount: number;
}

export interface ShopeeReview {
  id: string;
  userName: string;
  avatar?: string;
  rating: number;
  comment: string;
  images?: string[];
  variant?: string;
  createdAt: string;
  likes: number;
}

export interface CartItem {
  id: string;
  product: Product;
  variant?: string;
  quantity: number;
  selected: boolean;
}

export interface Order {
  id: string;
  orderNumber: string;
  items: OrderItem[];
  customer: CustomerInfo;
  shippingAddress: Address;
  paymentMethod: string;
  shippingMethod: string;
  subtotal: number;
  shippingFee: number;
  discount: number;
  total: number;
  status: OrderStatus;
  note?: string;
  createdAt: string;
  updatedAt: string;
}

export interface OrderItem {
  productId: string;
  productName: string;
  productImage: string;
  variant?: string;
  quantity: number;
  price: number;
}

export interface CustomerInfo {
  name: string;
  phone: string;
  email?: string;
}

export interface Address {
  fullName: string;
  phone: string;
  province: string;
  district: string;
  ward: string;
  detail: string;
  isDefault?: boolean;
}

export type OrderStatus =
  | "pending"
  | "confirmed"
  | "shipping"
  | "delivered"
  | "cancelled"
  | "returned";

export interface Banner {
  id: string;
  image: string;
  mobileImage?: string;
  link?: string;
  title?: string;
  order: number;
}

export interface FlashSale {
  id: string;
  startTime: string;
  endTime: string;
  products: Product[];
}

export interface Voucher {
  id: string;
  code: string;
  type: "percent" | "fixed";
  value: number;
  minOrder: number;
  maxDiscount?: number;
  usageLimit: number;
  usedCount: number;
  startDate: string;
  endDate: string;
}
