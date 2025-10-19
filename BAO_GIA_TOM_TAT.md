# 📋 BÁO GIÁ DỰ ÁN WEBSITE - HC ECO SYSTEM

> **Ngày báo giá:** [Ngày tháng năm hiện tại]  
> **Loại hình:** Website Thương Mại Điện Tử - Năng Lượng Mặt Trời  
> **Công nghệ:** HTML5, CSS3 (Tailwind), JavaScript, PHP, MySQL

---

## 📌 TỔNG QUAN DỰ ÁN

### Thông Tin Cơ Bản
- **Tên dự án:** Website HC Eco System - Giải Pháp Năng Lượng Mặt Trời
- **Tổng số trang:** 35+ trang HTML
- **Tổng số API:** 50+ endpoints PHP
- **Database:** 11 bảng với quan hệ phức tạp
- **Responsive:** ✅ Desktop, Tablet, Mobile
- **Dark Mode:** ✅ Hỗ trợ chế độ tối

### Tính Năng Chính

#### 🛒 **E-Commerce System (Hệ Thống Thương Mại Điện Tử)**
- Catalog sản phẩm (7 loại sản phẩm khác nhau)
- Giỏ hàng thời gian thực
- Hệ thống đặt hàng
- Quản lý đơn hàng
- Lịch sử mua hàng

#### 🔐 **User Authentication (Xác Thực Người Dùng)**
- Đăng ký/Đăng nhập
- Quản lý hồ sơ cá nhân
- Session management
- Bảo mật nâng cao

#### ⚡ **Solar Energy Calculator (Tính Toán Điện Mặt Trời)**
- Form khảo sát nhu cầu (multi-step)
- Tính toán tự động:
  - Số lượng tấm pin
  - Loại biến tần phù hợp
  - Tủ điện
  - Pin lưu trữ
  - Phụ kiện
  - Chi phí tổng thể
- Phân tích bậc thang điện EVN
- Lưu lịch sử khảo sát

#### 🎰 **Lottery System (Vòng Quay May Mắn)**
- Giao diện vòng quay với animation
- Quản lý vé quay
- Hệ thống phần thưởng
- Trang quản lý phần thưởng của tôi

#### 📍 **Location System**
- Danh sách 61 tỉnh/thành phố
- Danh sách quận/huyện/xã
- Tích hợp vào form đặt hàng

#### 🎨 **UI/UX Features**
- Dark Mode
- Background Slideshow động
- Responsive Design (Mobile First)
- Loading animations
- Smooth scrolling
- Floating Cart Button

---

## 💰 CẤU TRÚC GIÁ THEO GIAI ĐOẠN

### **GIAI ĐOẠN 1: PHÂN TÍCH & THIẾT KẾ**
| STT | Hạng Mục | Đơn Giá | Thành Tiền |
|-----|----------|---------|------------|
| 1.1 | Phân tích yêu cầu & Tài liệu đặc tả | | |
| 1.2 | Thiết kế Database Schema (11 bảng) | | |
| 1.3 | Thiết kế API Architecture | | |
| 1.4 | Thiết kế UI/UX (Wireframe, Mockup) | | |
| 1.5 | Thiết kế Brand Identity | | |

### **GIAI ĐOẠN 2: PHÁT TRIỂN FRONTEND**
| Module | Số Lượng | Chi Tiết | Đơn Giá | Thành Tiền |
|--------|----------|----------|---------|------------|
| **Trang Chính** | 6 trang | Trang chủ, Giới thiệu, Dự án, Tin tức, Liên hệ, Nhà phân phối | | |
| **Trang Sản Phẩm** | 8 trang | Bảng giá + 7 trang chi tiết sản phẩm | | |
| **Giỏ Hàng & Đơn Hàng** | 5 module | Giỏ hàng, Đặt hàng, Lịch sử, Chi tiết, FAB | | |
| **Khảo Sát Điện Mặt Trời** | 6 module | Form, Logic tính toán, Hiển thị kết quả, Bill breakdown, Lịch sử, Chi tiết | | |
| **Vòng Quay May Mắn** | 4 module | UI vòng quay, Logic random, Quản lý vé, Phần thưởng | | |
| **Xác Thực** | 4 trang | Login, Register, Profile, Auth logic | | |
| **Chính Sách** | 6 trang | Các trang chính sách và điều khoản | | |
| **Components** | 5 items | Header, Footer, Dark Mode, Slideshow, Responsive | | |

### **GIAI ĐOẠN 3: PHÁT TRIỂN BACKEND**
| Module | Số API | Chi Tiết | Đơn Giá | Thành Tiền |
|--------|--------|----------|---------|------------|
| **Database Setup** | 1 gói | 11 bảng + Data import + Connection Class | | |
| **Authentication APIs** | 6 APIs | Register, Login, Logout, Session, Get User, Update Profile | | |
| **Shopping Cart APIs** | 4 APIs | Get Cart, Add to Cart, Update, Remove | | |
| **Order APIs** | 4 APIs | Create Order, Create from Items, History, Detail | | |
| **Survey APIs** | 3 APIs | Save Survey, Get History, Get Detail | | |
| **Lottery APIs** | 4 APIs | Get Tickets, Use Ticket, Save Reward, Get Rewards | | |
| **Location APIs** | 4 APIs | Get Provinces, Get Districts (2 versions each) | | |
| **Helper Files** | 3 modules | Config, Auth Helpers, DB Class | | |

### **GIAI ĐOẠN 4: TESTING & QA**
| STT | Hạng Mục | Đơn Giá | Thành Tiền |
|-----|----------|---------|------------|
| 4.1 | Testing Frontend (Cross-browser, Responsive) | | |
| 4.2 | Testing Backend APIs (Unit Testing) | | |
| 4.3 | Integration Testing | | |
| 4.4 | Security Testing (SQL Injection, XSS, CSRF) | | |
| 4.5 | Performance Testing & Optimization | | |
| 4.6 | Bug Fixing & Refinement | | |

### **GIAI ĐOẠN 5: TRIỂN KHAI**
| STT | Hạng Mục | Đơn Giá | Thành Tiền |
|-----|----------|---------|------------|
| 5.1 | Cấu hình Server & Hosting | | |
| 5.2 | Cấu hình Database Production | | |
| 5.3 | Cấu hình Nginx/Apache & SSL | | |
| 5.4 | Deploy Code & Assets | | |
| 5.5 | Testing Production | | |

### **GIAI ĐOẠN 6: TÀI LIỆU & ĐÀO TẠO**
| STT | Hạng Mục | Đơn Giá | Thành Tiền |
|-----|----------|---------|------------|
| 6.1 | Tài liệu kỹ thuật (Technical Documentation) | | |
| 6.2 | Tài liệu hướng dẫn sử dụng (User Manual) | | |
| 6.3 | Tài liệu API Documentation | | |
| 6.4 | Database Schema & ERD | | |
| 6.5 | Đào tạo sử dụng & quản lý (1 buổi) | | |

### **GIAI ĐOẠN 7: HỖ TRỢ & BẢO TRÌ**
| STT | Hạng Mục | Đơn Giá | Thành Tiền |
|-----|----------|---------|------------|
| 7.1 | Hỗ trợ sau bàn giao (1 tháng - Miễn phí) | 0 | 0 |
| 7.2 | Bảo trì website (tùy chọn - theo tháng) | | |
| 7.3 | Cập nhật nội dung (tùy chọn - theo giờ) | | |

---

## 📊 TỔNG KẾT

```
┌─────────────────────────────────────────────┐
│  TỔNG CỘNG (CHƯA VAT):           ___ VNĐ   │
│  VAT (10%):                      ___ VNĐ   │
├─────────────────────────────────────────────┤
│  TỔNG THANH TOÁN:                ___ VNĐ   │
└─────────────────────────────────────────────┘
```

---

## 📝 ĐIỀU KHOẢN & ĐIỀU KIỆN

### ⏱️ Thời Gian Thực Hiện
- **Tổng thời gian:** 45-60 ngày làm việc
- **Phụ thuộc vào:** Độ phức tạp và yêu cầu cụ thể từ khách hàng

### 💳 Phương Thức Thanh Toán
1. **30%** khi ký hợp đồng
2. **40%** khi hoàn thành 70% dự án
3. **30%** khi nghiệm thu và bàn giao

### 🛡️ Bảo Hành & Hỗ Trợ
- **Bảo hành:** 12 tháng kể từ ngày nghiệm thu
- **Bao gồm:** Sửa lỗi phát sinh, tối ưu hóa
- **Không bao gồm:** Tính năng mới, thay đổi thiết kế lớn
- **Hỗ trợ miễn phí:** 1 tháng đầu tiên (qua email/phone/remote)

### 📜 Quyền Sở Hữu
- Khách hàng sở hữu **100% source code** sau khi thanh toán đủ
- Được phép chỉnh sửa, phát triển thêm
- Không giới hạn thời gian sử dụng

### 🚫 Không Bao Gồm
- Chi phí hosting và domain
- Chi phí SSL certificate (nếu sử dụng SSL trả phí)
- Chi phí bản quyền phần mềm/plugin bên thứ ba (nếu có)
- Chi phí sản xuất nội dung (text, ảnh, video)

### 📸 Nội Dung
- Khách hàng cung cấp: Text, hình ảnh, video
- Team hỗ trợ: Format, tối ưu hóa, compress

### 🔄 Thay Đổi & Bổ Sung
- Thay đổi trong phạm vi ban đầu: Miễn phí
- Thay đổi ngoài phạm vi: Báo giá bổ sung
- Quy trình: Change Request → Đánh giá → Báo giá → Thực hiện

---

## 🛠️ CÔNG NGHỆ SỬ DỤNG

### Frontend
- **HTML5** - Cấu trúc trang web
- **CSS3** - Styling (Tailwind CSS Framework)
- **JavaScript** - Tương tác người dùng (Vanilla JS)
- **Responsive** - Mobile First Design

### Backend
- **PHP 7.4+** - Server-side scripting
- **MySQL 5.7+** - Database
- **RESTful API** - Giao tiếp Frontend-Backend
- **Session-based Authentication** - Bảo mật

### Tools & Libraries
- **Tailwind CSS** - Utility-first CSS Framework
- **Google Fonts** - Typography (Inter Font)
- **Font Awesome** - Icons (nếu cần)

### Security
- **Password Hashing** - bcrypt
- **SQL Injection Prevention** - Prepared Statements
- **XSS Protection** - Input sanitization
- **CSRF Protection** - Token validation
- **SSL/TLS** - HTTPS encryption

---

## 🌐 TƯƠNG THÍCH

### Trình Duyệt
✅ Chrome (phiên bản mới nhất)  
✅ Firefox (phiên bản mới nhất)  
✅ Safari (phiên bản mới nhất)  
✅ Edge (phiên bản mới nhất)  
⚠️ IE11 (không hỗ trợ)

### Thiết Bị
✅ Desktop (1920px - 2560px)  
✅ Laptop (1366px - 1920px)  
✅ Tablet (768px - 1024px)  
✅ Mobile (320px - 767px)

---

## 📞 LIÊN HỆ

Để biết thêm chi tiết hoặc có thắc mắc, vui lòng liên hệ:

- **📧 Email:** hcecosystem@gmail.com
- **📱 Hotline:** 0988 919 868
- **🌐 Website:** hcecosystem.vn
- **📍 Địa chỉ:** 790 Ngô Quyền, Phường An Hải, TP. Đà Nẵng

---

## ✍️ CHỮ KÝ XÁC NHẬN

### Bên Cung Cấp Dịch Vụ
**Họ tên:** ___________________________  
**Chức vụ:** ___________________________  
**Ngày ký:** ___________________________  
**Chữ ký:** ___________________________

### Bên Khách Hàng
**Họ tên:** ___________________________  
**Chức vụ:** ___________________________  
**Ngày ký:** ___________________________  
**Chữ ký:** ___________________________

---

<div align="center">

**📌 Báo giá có hiệu lực trong 30 ngày kể từ ngày phát hành**

*Cảm ơn quý khách đã tin tưởng và lựa chọn dịch vụ của chúng tôi!*

</div>

