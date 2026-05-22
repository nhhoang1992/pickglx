"use client";

import { create } from "zustand";
import { CartItem, Product } from "@/types";

interface CartStore {
  items: CartItem[];
  addItem: (product: Product, variant?: string, quantity?: number) => void;
  removeItem: (id: string) => void;
  updateQuantity: (id: string, quantity: number) => void;
  toggleSelect: (id: string) => void;
  selectAll: (selected: boolean) => void;
  clearCart: () => void;
  getSelectedItems: () => CartItem[];
  getTotalPrice: () => number;
  getTotalItems: () => number;
}

export const useCartStore = create<CartStore>((set, get) => ({
  items: [],

  addItem: (product, variant, quantity = 1) => {
    set((state) => {
      const existingItem = state.items.find(
        (item) => item.product.id === product.id && item.variant === variant
      );

      if (existingItem) {
        return {
          items: state.items.map((item) =>
            item.id === existingItem.id
              ? { ...item, quantity: item.quantity + quantity }
              : item
          ),
        };
      }

      const newItem: CartItem = {
        id: `${product.id}-${variant || "default"}-${Date.now()}`,
        product,
        variant,
        quantity,
        selected: true,
      };

      return { items: [...state.items, newItem] };
    });
  },

  removeItem: (id) => {
    set((state) => ({
      items: state.items.filter((item) => item.id !== id),
    }));
  },

  updateQuantity: (id, quantity) => {
    if (quantity < 1) return;
    set((state) => ({
      items: state.items.map((item) =>
        item.id === id ? { ...item, quantity } : item
      ),
    }));
  },

  toggleSelect: (id) => {
    set((state) => ({
      items: state.items.map((item) =>
        item.id === id ? { ...item, selected: !item.selected } : item
      ),
    }));
  },

  selectAll: (selected) => {
    set((state) => ({
      items: state.items.map((item) => ({ ...item, selected })),
    }));
  },

  clearCart: () => set({ items: [] }),

  getSelectedItems: () => {
    return get().items.filter((item) => item.selected);
  },

  getTotalPrice: () => {
    return get()
      .items.filter((item) => item.selected)
      .reduce((total, item) => {
        const price = item.product.flashSalePrice || item.product.price;
        return total + price * item.quantity;
      }, 0);
  },

  getTotalItems: () => {
    return get().items.reduce((total, item) => total + item.quantity, 0);
  },
}));
