import Link from "next/link";

export default function Footer() {
  return (
    <footer className="bg-gray-100 border-t mt-8">
      <div className="max-w-7xl mx-auto px-4 py-8">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          {/* About */}
          <div>
            <h3 className="font-bold text-gray-800 mb-3">PHỤ KIỆN HẠT DẺ</h3>
            <p className="text-sm text-gray-600 leading-relaxed">
              Chuyên cung cấp phụ kiện điện thoại chính hãng, giá tốt nhất thị
              trường. Bảo hành 1 đổi 1. Giao hàng toàn quốc.
            </p>
          </div>

          {/* Customer Service */}
          <div>
            <h3 className="font-bold text-gray-800 mb-3">CHĂM SÓC KHÁCH HÀNG</h3>
            <ul className="space-y-2 text-sm text-gray-600">
              <li><Link href="/help" className="hover:text-[#EE4D2D]">Trung tâm trợ giúp</Link></li>
              <li><Link href="/shipping" className="hover:text-[#EE4D2D]">Hướng dẫn mua hàng</Link></li>
              <li><Link href="/return" className="hover:text-[#EE4D2D]">Trả hàng & Hoàn tiền</Link></li>
              <li><Link href="/contact" className="hover:text-[#EE4D2D]">Liên hệ</Link></li>
            </ul>
          </div>

          {/* Policy */}
          <div>
            <h3 className="font-bold text-gray-800 mb-3">CHÍNH SÁCH</h3>
            <ul className="space-y-2 text-sm text-gray-600">
              <li><Link href="/privacy" className="hover:text-[#EE4D2D]">Chính sách bảo mật</Link></li>
              <li><Link href="/warranty" className="hover:text-[#EE4D2D]">Chính sách bảo hành</Link></li>
              <li><Link href="/payment-policy" className="hover:text-[#EE4D2D]">Chính sách thanh toán</Link></li>
              <li><Link href="/shipping-policy" className="hover:text-[#EE4D2D]">Chính sách vận chuyển</Link></li>
            </ul>
          </div>

          {/* Contact */}
          <div>
            <h3 className="font-bold text-gray-800 mb-3">LIÊN HỆ</h3>
            <ul className="space-y-2 text-sm text-gray-600">
              <li>📞 Hotline: 0123.456.789</li>
              <li>📧 Email: contact@phukienhatde.vn</li>
              <li>🕐 Giờ làm việc: 8:00 - 22:00</li>
              <li className="flex gap-3 pt-2">
                <a href="#" className="text-blue-600 hover:text-blue-800">Facebook</a>
                <a href="#" className="text-pink-600 hover:text-pink-800">Instagram</a>
                <a href="#" className="text-red-600 hover:text-red-800">YouTube</a>
              </li>
            </ul>
          </div>
        </div>

        {/* Payment & Shipping */}
        <div className="mt-6 pt-6 border-t flex flex-col md:flex-row justify-between items-center gap-4">
          <div className="flex items-center gap-2 text-sm text-gray-500">
            <span>Thanh toán:</span>
            <span className="px-2 py-1 bg-white rounded border text-xs">COD</span>
            <span className="px-2 py-1 bg-white rounded border text-xs">MoMo</span>
            <span className="px-2 py-1 bg-white rounded border text-xs">VNPay</span>
            <span className="px-2 py-1 bg-white rounded border text-xs">Chuyển khoản</span>
          </div>
          <div className="flex items-center gap-2 text-sm text-gray-500">
            <span>Vận chuyển:</span>
            <span className="px-2 py-1 bg-white rounded border text-xs">GHN</span>
            <span className="px-2 py-1 bg-white rounded border text-xs">GHTK</span>
            <span className="px-2 py-1 bg-white rounded border text-xs">J&T</span>
          </div>
        </div>

        {/* Copyright */}
        <div className="mt-6 pt-4 border-t text-center text-xs text-gray-400">
          © 2024 Phụ Kiện Hạt Dẻ. All rights reserved.
        </div>
      </div>
    </footer>
  );
}
