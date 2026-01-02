-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost
-- Thời gian đã tạo: Th1 02, 2026 lúc 01:55 PM
-- Phiên bản máy phục vụ: 10.11.6-MariaDB-log
-- Phiên bản PHP: 8.3.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `nangluongmattroi`
--

DELIMITER $$
--
-- Thủ tục
--
CREATE DEFINER=`nangluongmattroi`@`localhost` PROCEDURE `CleanupExpiredPayments` ()   BEGIN
    -- Update expired transactions
    UPDATE payment_transactions 
    SET status = 'expired', updated_at = NOW()
    WHERE status = 'pending' 
    AND expires_at < NOW();
    
    -- Update expired orders
    UPDATE orders 
    SET payment_status = 'expired', updated_at = NOW()
    WHERE payment_status = 'pending' 
    AND payment_expires_at < NOW();
    
    -- Log cleanup action
    INSERT INTO payment_logs (transaction_id, order_id, action, status_from, status_to, data)
    SELECT 
        pt.transaction_id,
        pt.order_id,
        'cleanup_expired',
        'pending',
        'expired',
        JSON_OBJECT('cleanup_time', NOW())
    FROM payment_transactions pt
    WHERE pt.status = 'expired' 
    AND pt.updated_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` varchar(100) DEFAULT NULL,
  `old_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_value`)),
  `new_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_value`)),
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `severity` enum('INFO','WARNING','CRITICAL') DEFAULT 'INFO',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `username`, `action`, `entity_type`, `entity_id`, `old_value`, `new_value`, `description`, `ip_address`, `user_agent`, `severity`, `created_at`) VALUES
(1, 2, 'Anonymous', 'LOGIN', 'user', NULL, NULL, '{\"email\":\"admin\"}', 'Đăng nhập thành công: admin', '171.225.184.238', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'INFO', '2026-01-02 01:18:53');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Đang đổ dữ liệu cho bảng `cart_items`
--

INSERT INTO `cart_items` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(15, 3, 1, 1, '2025-11-18 06:21:50', '2025-11-18 06:21:50'),
(40, 2, 38, 3, '2025-11-27 16:38:14', '2026-01-02 02:29:06'),
(41, 2, 1, 2, '2025-11-27 16:38:14', '2026-01-02 02:29:04');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contact_channels`
--

CREATE TABLE `contact_channels` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL COMMENT 'Tên kênh (VD: Hotline, Zalo)',
  `description` varchar(255) DEFAULT NULL COMMENT 'Mô tả (VD: Hỗ trợ 24/7)',
  `content` text NOT NULL COMMENT 'Nội dung (Số điện thoại, email, link, username...)',
  `category` enum('phone','zalo','email','facebook','tiktok','youtube','website') NOT NULL COMMENT 'Danh mục kênh liên hệ',
  `color` varchar(50) DEFAULT '#16a34a' COMMENT 'Màu nền card dạng hex (VD: #16a34a)',
  `display_order` int(11) DEFAULT 0 COMMENT 'Thứ tự hiển thị',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Trạng thái hiển thị',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Quản lý các kênh liên hệ hiển thị trên trang liên hệ';

--
-- Đang đổ dữ liệu cho bảng `contact_channels`
--

INSERT INTO `contact_channels` (`id`, `name`, `description`, `content`, `category`, `color`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Hotline', 'Hỗ trợ 24/7', '0969397434', 'phone', '#16a34a', 1, 1, '2025-10-30 17:55:49', '2025-10-30 17:55:49'),
(2, 'Hotline phụ', 'Hỗ trợ 24/7', '0988919868', 'phone', '#16a34a', 2, 1, '2025-10-30 17:55:49', '2025-10-30 17:55:49'),
(3, 'Zalo', 'Chat trực tuyến (Chính)', '0969397434', 'zalo', '#2563eb', 3, 1, '2025-10-30 17:55:49', '2025-10-30 17:55:49'),
(4, 'Zalo phụ', 'Chat trực tuyến', '0988919868', 'zalo', '#2563eb', 4, 1, '2025-10-30 17:55:49', '2025-10-30 17:55:49'),
(5, 'Email', 'Phản hồi trong 24h', 'hcecosystem@gmail.com', 'email', '#9333ea', 5, 1, '2025-10-30 17:55:49', '2025-10-30 17:55:49'),
(6, 'Facebook', 'Theo dõi tin tức', 'https://www.facebook.com/hceco.io.vn', 'facebook', '#1d4ed8', 6, 1, '2025-10-30 17:55:49', '2025-10-30 17:55:49'),
(7, 'TikTok', 'Video về năng lượng', '@hc.channal', 'tiktok', '#ec4899', 7, 1, '2025-10-30 17:55:49', '2025-10-30 17:55:49'),
(8, 'Website', 'Mã số thuế: 0123456789', 'https://hcecosystem.vn', 'website', '#4b5563', 8, 1, '2025-10-30 17:55:49', '2025-10-30 17:55:49');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dich_vu`
--

CREATE TABLE `dich_vu` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL COMMENT 'Tên dịch vụ',
  `logo_url` varchar(500) DEFAULT NULL COMMENT 'URL logo/ảnh dịch vụ',
  `description` text DEFAULT NULL COMMENT 'Mô tả dịch vụ',
  `highlight_color` varchar(50) DEFAULT '#3FA34D' COMMENT 'Màu nổi bật (hex color)',
  `link_name` varchar(100) DEFAULT NULL COMMENT 'Tên link hiển thị (ví dụ: "Xem bảng giá")',
  `link_type` enum('page','custom') DEFAULT 'page' COMMENT 'Loại link: page hoặc custom',
  `link_value` varchar(500) DEFAULT NULL COMMENT 'Giá trị link (tên trang hoặc URL)',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Trạng thái hiển thị',
  `display_order` int(11) DEFAULT 0 COMMENT 'Thứ tự hiển thị',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Đang đổ dữ liệu cho bảng `dich_vu`
--

INSERT INTO `dich_vu` (`id`, `name`, `logo_url`, `description`, `highlight_color`, `link_name`, `link_type`, `link_value`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'Bảo Duy Solar', '../assets/img/ecosystem/baoduy-solar-logo.jpg', 'Chuyên cung cấp giải pháp năng lượng mặt trời toàn diện cho hộ gia đình và doanh nghiệp. Lắp đặt pin năng lượng mặt trời chất lượng cao, tiết kiệm điện năng tối đa với chính sách bảo hành lâu dài.', '#FBBF24', 'Xem bảng giá', 'page', 'pricing.html', 1, 1, '2025-10-30 17:55:49', '2025-10-30 17:55:49'),
(2, 'HC Travel', '../assets/img/ecosystem/hc-travel-logo.jpg', 'Dịch vụ du lịch đặc biệt dành cho khách hàng lắp đặt năng lượng mặt trời. Tận hưởng những chuyến du lịch đáng nhớ với ưu đãi đặc quyền và trải nghiệm độc đáo.', '#60A5FA', 'Liên hệ ngay', 'page', 'lien-he.html', 1, 2, '2025-10-30 17:55:49', '2025-10-30 17:55:49'),
(3, 'HC Coffee & Restaurant', '../assets/img/ecosystem/hc-cafe-logo.jpg', 'Nhà hàng và quán cà phê phục vụ những món ăn ngon, đồ uống chất lượng cao. Môi trường thân thiện, lý tưởng cho họp mặt, làm việc và thư giãn với bạn bè, gia đình.', '#F59E0B', 'Xem thực đơn', 'page', 'pricing.html', 1, 3, '2025-10-30 17:55:49', '2025-10-30 17:55:49'),
(4, 'C Home Build', '../assets/img/ecosystem/c-home-logo.jpg', 'Dịch vụ xây dựng và thiết kế nhà ở hiện đại, bền vững với tiêu chuẩn cao. Tích hợp công nghệ xanh, tiết kiệm năng lượng trong từng công trình.', '#10B981', 'Xem website', 'custom', 'https://c-homebuild.com/', 1, 4, '2025-10-30 17:55:49', '2025-10-30 17:55:49'),
(5, 'Thiết Kế Website - Mobile App - Hệ Thống IOT Thông Minh', '../assets/img/logo/logo_1763632497_691ee5710631c.png', 'Chuyên Thiết Kế WebSite - Mobile App (android - ios) - Triển Khai Các Mô Hình Hệ Thống Nhúng IOT Thông Minh', '#13fb83', 'Liên Hệ', 'custom', 'https://phuture.io.vn', 1, 5, '2025-11-20 09:47:45', '2025-11-20 09:54:57');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `electricity_prices`
--

CREATE TABLE `electricity_prices` (
  `id` int(11) NOT NULL,
  `tier` int(11) NOT NULL COMMENT 'Bậc thang (1-6)',
  `tier_name` varchar(100) NOT NULL COMMENT 'Tên bậc (VD: Bậc 1: 0-50 kWh)',
  `kwh_from` int(11) NOT NULL COMMENT 'Từ kWh',
  `kwh_to` int(11) DEFAULT NULL COMMENT 'Đến kWh (NULL nếu không giới hạn)',
  `price_no_vat` decimal(10,2) NOT NULL COMMENT 'Giá chưa VAT (VNĐ/kWh)',
  `price_with_vat` decimal(10,2) NOT NULL COMMENT 'Giá đã bao gồm VAT 8% (VNĐ/kWh)',
  `effective_date` date NOT NULL COMMENT 'Ngày áp dụng',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Đang áp dụng',
  `notes` text DEFAULT NULL COMMENT 'Ghi chú',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Bảng giá điện sinh hoạt EVN theo bậc thang';

--
-- Đang đổ dữ liệu cho bảng `electricity_prices`
--

INSERT INTO `electricity_prices` (`id`, `tier`, `tier_name`, `kwh_from`, `kwh_to`, `price_no_vat`, `price_with_vat`, `effective_date`, `is_active`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'Bậc 1: 0-50 kWh', 0, 50, 1984.00, 2143.00, '2025-05-10', 1, 'Bậc tiêu thụ thấp nhất', '2025-10-30 17:55:49', '2025-10-30 17:55:49'),
(2, 2, 'Bậc 2: 51-100 kWh', 51, 100, 2050.00, 2214.00, '2025-05-10', 1, 'Bậc tiêu thụ trung bình thấp', '2025-10-30 17:55:49', '2025-10-30 17:55:49'),
(3, 3, 'Bậc 3: 101-200 kWh', 101, 200, 2380.00, 2570.00, '2025-05-10', 1, 'Bậc tiêu thụ trung bình', '2025-10-30 17:55:49', '2025-10-30 17:55:49'),
(4, 4, 'Bậc 4: 201-300 kWh', 201, 300, 2930.00, 3164.00, '2025-05-10', 1, 'Bậc tiêu thụ cao', '2025-10-30 17:55:49', '2025-10-30 17:55:49'),
(5, 5, 'Bậc 5: 301-400 kWh', 301, 400, 3270.00, 3532.00, '2025-05-10', 1, 'Bậc tiêu thụ rất cao', '2025-10-30 17:55:49', '2025-10-30 17:55:49'),
(6, 6, 'Bậc 6: Từ 401 kWh', 401, NULL, 3460.00, 3737.00, '2025-05-10', 1, 'Bậc tiêu thụ cao nhất (không giới hạn)', '2025-10-30 17:55:49', '2025-10-30 17:55:49');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `home_posts`
--

CREATE TABLE `home_posts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL COMMENT 'Tiêu đề bài đăng',
  `description` text NOT NULL COMMENT 'Nội dung mô tả',
  `highlight_text` varchar(100) DEFAULT NULL COMMENT 'Văn bản highlight (VD: GIẢI PHÁP GIA ĐÌNH)',
  `highlight_color` varchar(50) DEFAULT '#3FA34D' COMMENT 'Màu highlight dạng hex (VD: #3FA34D)',
  `image_url` varchar(500) NOT NULL COMMENT 'Đường dẫn ảnh bài đăng chính (backward compatibility)',
  `image_position` enum('left','right') DEFAULT 'right' COMMENT 'Vị trí ảnh: left (trái) hoặc right (phải)',
  `button_text` varchar(100) DEFAULT NULL COMMENT 'Văn bản nút CTA',
  `button_url` varchar(500) DEFAULT NULL COMMENT 'Link của nút CTA',
  `button_color` varchar(50) DEFAULT '#3FA34D' COMMENT 'Màu nút dạng hex (VD: #3FA34D)',
  `features` text DEFAULT NULL COMMENT 'JSON array của các tính năng: [{"text":"Feature 1"},{"text":"Feature 2"}]',
  `media_gallery` text DEFAULT NULL COMMENT 'JSON array chứa nhiều ảnh/video: [{"type":"image","url":"...","order":1},{"type":"video","url":"...","order":2}]',
  `display_order` int(11) DEFAULT 0 COMMENT 'Thứ tự hiển thị',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Trạng thái hiển thị',
  `section_id` varchar(50) DEFAULT 'solutions' COMMENT 'ID của section trong HTML',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Quản lý bài đăng hiển thị trên trang chủ';

--
-- Đang đổ dữ liệu cho bảng `home_posts`
--

INSERT INTO `home_posts` (`id`, `title`, `description`, `highlight_text`, `highlight_color`, `image_url`, `image_position`, `button_text`, `button_url`, `button_color`, `features`, `media_gallery`, `display_order`, `is_active`, `section_id`, `created_at`, `updated_at`) VALUES
(1, 'Xây Dựng Chìa Khóa Trao Tay', 'Giải pháp thi công trọn gói từ thiết kế, vật tư đến hoàn thiện nội thất. Bàn giao nhanh, đúng tiến độ, đảm bảo chất lượng – khách hàng chỉ việc nhận nhà.', 'Giải Pháp Tối Ưu', '#bcfd44', '/assets/img/home/home_1763552808_691dae28c2b16.png', 'right', 'Tư vấn & Báo giá', 'https://c-homebuild.com/', '#b6df20', '[{\"text\":\"Thiết kế – thi công trọn gói từ A → Z\"},{\"text\":\"Vật liệu đạt chuẩn, minh bạch chi phí\"},{\"text\":\"Đội ngũ kỹ sư – kiến trúc sư giàu kinh nghiệm\"},{\"text\":\"Bàn giao đúng tiến độ, bảo hành sau thi công\"}]', '[]', 2, 1, 'solutions', '2025-10-28 19:53:28', '2025-11-25 03:20:24'),
(2, 'TRẢI NGHIỆM MIỀN ĐÁ HÙNG VĨ', 'Không gian núi đá hùng vĩ, những cung đường uốn lượn và bản làng đặc trưng của vùng cao. Trải nghiệm văn hóa, ẩm thực và thiên nhiên nguyên sơ của Hà Giang.', 'Hà Giang Adventure Tour', '#21c4b9', '/assets/img/home/home_1763548934_691d9f065a91d.png', 'left', 'Đặt tour ngay', 'html/pricing.html', '#35e34c', '[{\"text\":\"Check-in Đèo Mã Pí Lèng – Một trong tứ đại đỉnh đèo\"},{\"text\":\"Khám phá hẻm Tu Sản, sông Nho Quế bằng thuyền\"},{\"text\":\"Thăm phố cổ Đồng Văn & các bản làng người H’Mông\"},{\"text\":\"Tour 2N1Đ \\/ 3N2Đ linh hoạt, giá ưu đãi\"}]', '[]', 3, 1, 'solutions', '2025-10-28 20:10:14', '2025-11-25 03:20:31'),
(3, 'Hệ Thống Năng Lượng Mặt Trời', 'Giải pháp solar toàn diện cho hộ gia đình và doanh nghiệp với thiết kế tối ưu, thiết bị Tier 1 và giám sát 24/7.', 'Năng Lượng Sạch', '#34d399', '/assets/img/home/home_1763639813_691f0205bdee9.jpg', 'left', 'Đặt lịch khảo sát', 'html/khao-sat-dien-mat-troi.html', '#16a34a', '[{\"text\":\"Thiết kế riêng cho từng mái nhà\"},{\"text\":\"Inverter & pin Tier 1 chính hãng\"},{\"text\":\"Giám sát và bảo hành dài hạn\"}]', '[]', 1, 1, 'solutions', '2025-11-20 12:00:00', '2025-11-20 11:56:53'),
(4, 'HC Coffee & Restaurant', 'Không gian F&B kết nối cộng đồng với thực đơn organic, cà phê đặc sản và dịch vụ tổ chức sự kiện.', 'Trải Nghiệm Vị Giác', '#f97316', '/assets/img/home/home_1763640060_691f02fcad7c7.png', 'right', 'Đặt bàn & sự kiện', 'html/lien-he.html', '#ea580c', '[{\"text\":\"Không gian cảm hứng cho làm việc & gặp gỡ\"},{\"text\":\"Thực đơn hữu cơ, seasonal menu\"},{\"text\":\"Nhận đặt tiệc & sự kiện doanh nghiệp\"}]', '[]', 4, 1, 'solutions', '2025-11-20 12:00:00', '2025-11-20 12:01:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `intro_posts`
--

CREATE TABLE `intro_posts` (
  `id` int(11) NOT NULL,
  `title` varchar(500) NOT NULL COMMENT 'Tiêu đề bài viết',
  `description` text DEFAULT NULL COMMENT 'Mô tả/ nội dung bài viết',
  `image_url` varchar(500) DEFAULT NULL COMMENT 'URL ảnh đại diện chính (backward compatibility)',
  `video_url` varchar(500) DEFAULT NULL COMMENT 'URL video chính (backward compatibility)',
  `media_gallery` text DEFAULT NULL COMMENT 'JSON array chứa nhiều ảnh/video: [{"type":"image","url":"...","order":1},{"type":"video","url":"...","order":2}]',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Trạng thái hiển thị',
  `display_order` int(11) DEFAULT 0 COMMENT 'Thứ tự hiển thị',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lottery_rewards`
--

CREATE TABLE `lottery_rewards` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reward_template_id` int(11) DEFAULT NULL COMMENT 'Mẫu phần thưởng từ admin',
  `reward_name` varchar(255) NOT NULL,
  `reward_type` enum('voucher','cash','gift') NOT NULL,
  `reward_value` decimal(15,2) DEFAULT NULL COMMENT 'Giá trị voucher/tiền mặt',
  `reward_description` text DEFAULT NULL COMMENT 'Mô tả chi tiết',
  `voucher_code` varchar(50) DEFAULT NULL COMMENT 'Mã voucher nếu là loại voucher',
  `reward_image` varchar(255) DEFAULT NULL,
  `status` enum('pending','used','expired') DEFAULT 'pending',
  `ticket_id` int(11) DEFAULT NULL COMMENT 'ID của vé số đã sử dụng',
  `won_at` timestamp NULL DEFAULT current_timestamp(),
  `used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lưu phần thưởng từ vòng quay may mắn';

--
-- Đang đổ dữ liệu cho bảng `lottery_rewards`
--

INSERT INTO `lottery_rewards` (`id`, `user_id`, `reward_template_id`, `reward_name`, `reward_type`, `reward_value`, `reward_description`, `voucher_code`, `reward_image`, `status`, `ticket_id`, `won_at`, `used_at`, `expires_at`, `notes`, `created_at`, `updated_at`) VALUES
(1, 5, 7, 'Voucher giam 1 trieu', 'voucher', 1000000.00, 'cho 0988919868', 'VC69076D7B2DCB0', NULL, 'used', 171, '2025-11-02 14:40:59', '2025-11-02 14:45:34', '2025-12-02 14:40:59', NULL, '2025-11-02 14:40:59', '2025-11-02 14:45:34'),
(2, 5, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'pending', 170, '2025-11-02 14:41:19', NULL, '2025-12-02 14:41:19', NULL, '2025-11-02 14:41:19', '2025-11-02 14:41:19'),
(3, 5, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69076DA8A9542', NULL, 'used', 169, '2025-11-02 14:41:44', '2025-11-02 14:45:34', '2025-12-02 14:41:44', NULL, '2025-11-02 14:41:44', '2025-11-02 14:45:34'),
(4, 5, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'pending', 172, '2025-11-02 14:41:56', NULL, '2025-12-02 14:41:56', NULL, '2025-11-02 14:41:56', '2025-11-02 14:41:56'),
(5, 5, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'pending', 168, '2025-11-02 14:57:24', NULL, '2025-12-02 14:57:24', NULL, '2025-11-02 14:57:24', '2025-11-02 14:57:24'),
(6, 2, 7, 'Voucher giam 1 trieu', 'voucher', 1000000.00, 'cho 0988919868', 'VC69079F8962F7D', NULL, 'expired', NULL, '2025-11-02 18:14:33', NULL, '2025-12-02 18:14:33', NULL, '2025-11-02 18:14:33', '2026-01-02 02:29:50'),
(7, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F89E8FFD', NULL, 'expired', NULL, '2025-11-02 18:14:34', '2025-11-02 18:16:41', '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:45'),
(8, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A45B53', NULL, 'expired', NULL, '2025-11-02 18:14:34', '2025-11-02 18:16:42', '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:45'),
(9, 2, 3, 'Tiền mặt 200.000đ', 'cash', 200000.00, 'Nhận ngay 200.000đ tiền mặt', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:45'),
(10, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A45ED8', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:45'),
(11, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A46667', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:45'),
(12, 2, 5, 'Chai nước giặt Omo', 'gift', NULL, 'Chai nước giặt Omo 3.8kg', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:45'),
(13, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:45'),
(14, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A46EE8', NULL, 'expired', NULL, '2025-11-02 18:14:34', '2025-11-02 18:16:42', '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:45'),
(15, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A4709E', NULL, 'expired', NULL, '2025-11-02 18:14:34', '2025-11-02 18:16:42', '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:45'),
(16, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:45'),
(17, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A475AB', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:45'),
(18, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC69079F8A47982', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(19, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A47B2B', NULL, 'expired', NULL, '2025-11-02 18:14:34', '2025-11-02 18:16:42', '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(20, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A47CD6', NULL, 'expired', NULL, '2025-11-02 18:14:34', '2025-11-02 18:16:42', '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(21, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(22, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(23, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A480E0', NULL, 'expired', NULL, '2025-11-02 18:14:34', '2025-11-02 18:16:42', '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(24, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(25, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(26, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(27, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A48859', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(28, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC69079F8A48A2B', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(29, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(30, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A48D39', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(31, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A490D7', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(32, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A4927E', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(33, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(34, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(35, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(36, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(37, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A4A0CB', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(38, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(39, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A4A3CA', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(40, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(41, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC69079F8A4A6A0', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(42, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(43, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A4A98A', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(44, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC69079F8A4AB1D', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(45, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:46'),
(46, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:47'),
(47, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A4AF2B', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:47'),
(48, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC69079F8A4B0FA', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:47'),
(49, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A4B28B', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:47'),
(50, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:47'),
(51, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:47'),
(52, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:47'),
(53, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A4B7D7', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:47'),
(54, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC69079F8A4B976', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:47'),
(55, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A4BB05', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:47'),
(56, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:47'),
(57, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:47'),
(58, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A4BF25', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:47'),
(59, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:47'),
(60, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:47'),
(61, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:47'),
(62, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A4C415', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:47'),
(63, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC69079F8A4C5A7', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:47'),
(64, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A4C761', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:47'),
(65, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A4CB80', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:47'),
(66, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:47'),
(67, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:47'),
(68, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:47'),
(69, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC69079F8A4D0AF', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:47'),
(70, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC69079F8A4D244', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:47'),
(71, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC69079F8A4D3E9', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:47'),
(72, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC69079F8A4D78C', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:48'),
(73, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A4D930', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:48'),
(74, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A4DBCC', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:48'),
(75, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC69079F8A4DD7E', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:48'),
(76, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:48'),
(77, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:48'),
(78, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A4E16A', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:48'),
(79, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:48'),
(80, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:48'),
(81, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:48'),
(82, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:48'),
(83, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A4E7A9', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:48'),
(84, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:48'),
(85, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:48'),
(86, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A4EBD5', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:48'),
(87, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:48'),
(88, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A4EEB3', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:48'),
(89, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC69079F8A4F061', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:48'),
(90, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:48'),
(91, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A4F30A', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:48'),
(92, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A4F4B4', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:48'),
(93, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:48'),
(94, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:48'),
(95, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:49'),
(96, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A4F9F6', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:49'),
(97, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A4FBA0', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:49'),
(98, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:49'),
(99, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A4FE87', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:49'),
(100, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:49'),
(101, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:49'),
(102, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC69079F8A502A4', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:49'),
(103, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A50468', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:49'),
(104, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A50628', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:49'),
(105, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A507B8', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:49'),
(106, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:49'),
(107, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC69079F8A50AA5', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:49'),
(108, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC69079F8A50C67', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:49'),
(109, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:49'),
(110, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:49'),
(111, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:49'),
(112, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:49'),
(113, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A51255', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:49'),
(114, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:49'),
(115, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A514FD', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:49'),
(116, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:49'),
(117, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A517D1', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:49'),
(118, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:49'),
(119, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A51AC6', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:49'),
(120, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC69079F8A51C81', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:49'),
(121, 2, 1, 'Voucher giảm 500.000đ', 'voucher', 500000.00, 'Voucher giảm giá 500.000đ cho đơn hàng tiếp theo', 'VC69079F8A51E10', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:50'),
(122, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC69079F8A51FA5', NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:50'),
(123, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:14:34', NULL, '2025-12-02 18:14:34', NULL, '2025-11-02 18:14:34', '2026-01-02 02:29:50'),
(124, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:37', NULL, '2025-12-02 18:17:37', NULL, '2025-11-02 18:17:37', '2026-01-02 02:29:45'),
(125, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:37', NULL, '2025-12-02 18:17:37', NULL, '2025-11-02 18:17:37', '2026-01-02 02:29:45'),
(126, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:41'),
(127, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A04200205', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:41'),
(128, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:41'),
(129, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:41'),
(130, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:41'),
(131, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A04200800', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:41'),
(132, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:41'),
(133, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A04201482', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:41'),
(134, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A042018C5', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:41'),
(135, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:41'),
(136, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A04201B82', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:41'),
(137, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:41'),
(138, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A04201E63', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:41'),
(139, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A042135DB', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:41'),
(140, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A042139FB', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:41'),
(141, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:41'),
(142, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A04213CFC', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:41'),
(143, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:41'),
(144, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A04214790', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:41'),
(145, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A04214946', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:41'),
(146, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:41'),
(147, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:41'),
(148, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A04214D8F', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(149, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A04214F2A', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(150, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(151, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(152, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(153, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(154, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(155, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A0421569D', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(156, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A04215835', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(157, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A042159CD', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(158, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(159, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A04215CB0', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(160, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(161, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A04215F7C', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(162, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A04216126', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(163, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A042162E1', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(164, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A0421648B', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(165, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(166, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(167, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(168, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A0421698D', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(169, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A04216B4F', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(170, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A04216D12', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(171, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A04216EBA', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(172, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A0421707D', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(173, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A04217212', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(174, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(175, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A042174D7', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(176, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A0421766C', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:42'),
(177, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:43'),
(178, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A0421791E', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:43'),
(179, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A04217AE2', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:43'),
(180, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:43'),
(181, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A04217DBF', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:43'),
(182, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:43'),
(183, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A042180B1', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:43'),
(184, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:43'),
(185, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:43'),
(186, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A0421849C', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:43'),
(187, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A04218632', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:43'),
(188, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:43'),
(189, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A0421890F', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:43'),
(190, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A04218AD3', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:43'),
(191, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A04218C90', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:43'),
(192, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:43'),
(193, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A04218F76', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:43'),
(194, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:43'),
(195, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:43'),
(196, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:43'),
(197, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:43'),
(198, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A0421A9CE', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:44'),
(199, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:44'),
(200, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:44'),
(201, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:44'),
(202, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A0421AEF6', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:44'),
(203, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A0421B0A9', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:44'),
(204, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A0421B241', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:44'),
(205, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A04227E32', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:44'),
(206, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A04228016', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:44'),
(207, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A04228436', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:44'),
(208, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:44'),
(209, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A04228719', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:44'),
(210, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A042288FC', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:44'),
(211, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:44'),
(212, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:44'),
(213, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A04228CF9', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:44'),
(214, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:44'),
(215, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A04228FC5', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:44'),
(216, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:44'),
(217, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A042292B1', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:44'),
(218, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:44'),
(219, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:44'),
(220, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A042296C3', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:44'),
(221, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A0422986E', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:44'),
(222, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:44'),
(223, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:44'),
(224, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A04229C6D', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:45'),
(225, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A04229E04', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:45');
INSERT INTO `lottery_rewards` (`id`, `user_id`, `reward_template_id`, `reward_name`, `reward_type`, `reward_value`, `reward_description`, `voucher_code`, `reward_image`, `status`, `ticket_id`, `won_at`, `used_at`, `expires_at`, `notes`, `created_at`, `updated_at`) VALUES
(226, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:45'),
(227, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:45'),
(228, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:45'),
(229, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:45'),
(230, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:45'),
(231, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A0422A94D', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:45'),
(232, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A0422AAEE', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:45'),
(233, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A0422AC9E', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:45'),
(234, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:45'),
(235, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:45'),
(236, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A0422B08B', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:45'),
(237, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A0422B223', NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:45'),
(238, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:17:38', NULL, '2025-12-02 18:17:38', NULL, '2025-11-02 18:17:38', '2026-01-02 02:29:45'),
(239, 2, 2, 'Voucher giảm 1.000.000đ', 'voucher', 1000000.00, 'Voucher giảm giá 1.000.000đ cho đơn hàng tiếp theo', 'VC6907A1B436483', NULL, 'expired', NULL, '2025-11-02 18:23:48', NULL, '2025-12-02 18:23:48', NULL, '2025-11-02 18:23:48', '2026-01-02 02:29:41'),
(240, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDB5652', NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(241, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A1BDB58C0', NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(242, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(243, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(244, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(245, 2, 3, 'Tiền mặt 200.000đ', 'cash', 200000.00, 'Nhận ngay 200.000đ tiền mặt', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(246, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDB60BE', NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(247, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A1BDB62B9', NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:41'),
(248, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A1BDB64A8', NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:41'),
(249, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:41'),
(250, 2, 4, 'Tiền mặt 500.000đ', 'cash', 500000.00, 'Nhận ngay 500.000đ tiền mặt', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:41'),
(251, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:41'),
(252, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDB6A1F', NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:41'),
(253, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:41'),
(254, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:41'),
(255, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDB6EAD', NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:41'),
(256, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDB7086', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-25 07:22:34', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:37'),
(257, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:37'),
(258, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A1BDB73CE', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-25 07:23:54', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:37'),
(259, 2, 5, 'Chai nước giặt Omo', 'gift', NULL, 'Chai nước giặt Omo 3.8kg', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:37'),
(260, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A1BDB76D7', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-25 06:38:46', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:37'),
(261, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:37'),
(262, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDB79EB', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-25 06:38:46', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:37'),
(263, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:37'),
(264, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:37'),
(265, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:37'),
(266, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:37'),
(267, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A1BDC8A03', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-25 07:23:54', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:37'),
(268, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:37'),
(269, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:37'),
(270, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDC8EA8', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-25 07:28:07', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:37'),
(271, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDC9072', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-25 07:23:54', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(272, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(273, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A1BDC9388', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-25 07:23:54', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(274, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(275, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDC9EA2', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-25 07:28:07', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(276, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(277, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDCA1DD', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-25 07:28:07', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(278, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDCA383', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-25 07:28:07', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(279, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDCA562', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-25 07:36:13', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(280, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(281, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A1BDCA88D', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-25 07:36:13', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(282, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(283, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(284, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(285, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDCADB3', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-25 07:36:13', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(286, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(287, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(288, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(289, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(290, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDCD754', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-25 07:36:13', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(291, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDCD90F', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-25 07:36:13', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(292, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(293, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDCDC1B', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-25 08:00:04', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(294, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDCDDBC', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-25 08:00:04', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(295, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDCDF75', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 15:43:15', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(296, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDCE11C', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 15:43:15', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(297, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A1BDCE338', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 16:29:57', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(298, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDCE567', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 16:29:57', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(299, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:38'),
(300, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A1BDCE887', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 16:29:57', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(301, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDCEE5C', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 16:38:04', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(302, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDCF023', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 16:38:04', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(303, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(304, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(305, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A1BDCF45C', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 16:38:28', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(306, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDCF62D', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 16:38:28', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(307, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDCF99D', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 16:38:50', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(308, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(309, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDCFC86', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 16:38:50', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(310, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(311, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(312, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A1BDD007A', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 17:14:45', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(313, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(314, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(315, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A1BDD04B8', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 17:14:45', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(316, 2, NULL, 'Tiền mặt 100.000đ', 'cash', 100000.00, 'Tiền mặt 100.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(317, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(318, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDD08AE', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 17:14:45', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(319, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(320, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(321, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A1BDD0CBB', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 17:14:45', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(322, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDD0E7A', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 17:14:45', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(323, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A1BDD100F', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 17:14:45', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(324, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A1BDD11D2', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 17:59:34', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(325, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A1BDD137B', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 17:59:34', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(326, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDD150F', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 18:00:44', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(327, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A1BDD16B4', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 18:00:44', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(328, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(329, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(330, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:39'),
(331, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(332, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(333, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDD1E20', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 18:17:47', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(334, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDD1FC1', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 18:17:47', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(335, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(336, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDD22AD', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 18:28:18', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(337, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDD244C', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 18:28:18', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(338, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDD25DF', NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(339, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(340, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(341, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(342, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A1BDD32DA', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 18:28:18', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(343, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(344, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A1BDD3598', NULL, 'expired', NULL, '2025-11-02 18:23:57', '2025-11-27 18:28:18', '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(345, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A1BDD3737', NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(346, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(347, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDD3A00', NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(348, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A1BDD3B96', NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(349, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(350, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Chúc may mắn lần sau!', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(351, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A1BDD3F9D', NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(352, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A1BDD4169', NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(353, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:23:57', NULL, '2025-12-02 18:23:57', NULL, '2025-11-02 18:23:57', '2026-01-02 02:29:40'),
(354, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:25:35', NULL, '2025-12-02 18:25:35', NULL, '2025-11-02 18:25:35', '2026-01-02 02:29:37'),
(355, 2, 1, 'Voucher giảm 500.000đ', 'voucher', 500000.00, 'Voucher giảm giá 500.000đ cho đơn hàng tiếp theo', 'VC6907A22954F3B', NULL, 'expired', NULL, '2025-11-02 18:25:45', '2025-11-25 07:22:34', '2025-12-02 18:25:45', NULL, '2025-11-02 18:25:45', '2026-01-02 02:29:37'),
(356, 2, NULL, 'Quà tặng phụ kiện', 'gift', NULL, 'Quà tặng phụ kiện', NULL, NULL, 'expired', NULL, '2025-11-02 18:25:52', NULL, '2025-12-02 18:25:52', NULL, '2025-11-02 18:25:52', '2026-01-02 02:29:36'),
(357, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A2371FF97', NULL, 'expired', NULL, '2025-11-02 18:25:59', '2025-11-25 07:07:29', '2025-12-02 18:25:59', NULL, '2025-11-02 18:25:59', '2026-01-02 02:29:36'),
(358, 2, NULL, 'Voucher giảm 200.000đ', 'voucher', 200000.00, 'Voucher giảm 200.000đ', 'VC6907A287F1BED', NULL, 'expired', NULL, '2025-11-02 18:27:19', '2025-11-25 07:07:29', '2025-12-02 18:27:19', NULL, '2025-11-02 18:27:19', '2026-01-02 02:29:36'),
(359, 2, 6, 'Bộ dụng cụ gia đình', 'gift', NULL, 'Bộ dụng cụ gia đình 10 món', NULL, NULL, 'expired', NULL, '2025-11-02 18:27:27', NULL, '2025-12-02 18:27:27', NULL, '2025-11-02 18:27:27', '2026-01-02 02:29:36'),
(360, 2, NULL, 'Voucher giảm 100.000đ', 'voucher', 100000.00, 'Voucher giảm 100.000đ', 'VC6907A298C8A7C', NULL, 'expired', NULL, '2025-11-02 18:27:36', '2025-11-25 06:38:46', '2025-12-02 18:27:36', NULL, '2025-11-02 18:27:36', '2026-01-02 02:29:36'),
(361, 2, NULL, 'Tiền mặt 50.000đ', 'cash', 50000.00, 'Tiền mặt 50.000đ', NULL, NULL, 'expired', NULL, '2025-11-02 18:27:47', NULL, '2025-12-02 18:27:47', NULL, '2025-11-02 18:27:47', '2026-01-02 02:29:36'),
(362, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'expired', NULL, '2025-11-02 18:38:34', NULL, '2025-12-02 18:38:34', NULL, '2025-11-02 18:38:34', '2026-01-02 02:29:35'),
(363, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'expired', NULL, '2025-11-02 18:38:45', NULL, '2025-12-02 18:38:45', NULL, '2025-11-02 18:38:45', '2026-01-02 02:29:35'),
(364, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'expired', NULL, '2025-11-02 18:38:51', NULL, '2025-12-02 18:38:51', NULL, '2025-11-02 18:38:51', '2026-01-02 02:29:35'),
(365, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'expired', NULL, '2025-11-02 18:38:59', NULL, '2025-12-02 18:38:59', NULL, '2025-11-02 18:38:59', '2026-01-02 02:29:35'),
(366, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'expired', NULL, '2025-11-02 18:40:13', NULL, '2025-12-02 18:40:13', NULL, '2025-11-02 18:40:13', '2026-01-02 02:29:35'),
(367, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'expired', NULL, '2025-11-02 18:40:13', NULL, '2025-12-02 18:40:13', NULL, '2025-11-02 18:40:13', '2026-01-02 02:29:35'),
(368, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'expired', NULL, '2025-11-02 18:40:46', NULL, '2025-12-02 18:40:46', NULL, '2025-11-02 18:40:46', '2026-01-02 02:29:34'),
(369, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'expired', NULL, '2025-11-02 18:40:46', NULL, '2025-12-02 18:40:46', NULL, '2025-11-02 18:40:46', '2026-01-02 02:29:34'),
(370, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'expired', NULL, '2025-11-02 18:40:46', NULL, '2025-12-02 18:40:46', NULL, '2025-11-02 18:40:46', '2026-01-02 02:29:34'),
(371, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'expired', NULL, '2025-11-02 18:40:46', NULL, '2025-12-02 18:40:46', NULL, '2025-11-02 18:40:46', '2026-01-02 02:29:35'),
(372, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'expired', NULL, '2025-11-02 18:40:46', NULL, '2025-12-02 18:40:46', NULL, '2025-11-02 18:40:46', '2026-01-02 02:29:35'),
(373, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'expired', NULL, '2025-11-02 18:41:23', NULL, '2025-12-02 18:41:23', NULL, '2025-11-02 18:41:23', '2026-01-02 02:29:34'),
(374, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'expired', NULL, '2025-11-02 18:42:02', NULL, '2025-12-02 18:42:02', NULL, '2025-11-02 18:42:02', '2026-01-02 02:29:33'),
(375, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'expired', NULL, '2025-11-02 18:42:09', NULL, '2025-12-02 18:42:09', NULL, '2025-11-02 18:42:09', '2026-01-02 02:29:33'),
(376, 2, 5, 'Chai nước giặt Omo', 'gift', NULL, 'Chai nước giặt Omo 3.8kg', NULL, NULL, 'expired', NULL, '2025-11-02 18:42:16', NULL, '2025-12-02 18:42:16', NULL, '2025-11-02 18:42:16', '2026-01-02 02:29:33'),
(377, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'expired', NULL, '2025-11-02 18:45:40', NULL, '2025-12-02 18:45:40', NULL, '2025-11-02 18:45:40', '2026-01-02 02:29:33'),
(378, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'expired', NULL, '2025-11-02 18:45:47', NULL, '2025-12-02 18:45:47', NULL, '2025-11-02 18:45:47', '2026-01-02 02:29:33'),
(379, 2, 1, 'Voucher giảm 500.000đ', 'voucher', 500000.00, 'Voucher giảm giá 500.000đ cho đơn hàng tiếp theo', 'VC6907A6E390304', NULL, 'expired', NULL, '2025-11-02 18:45:55', '2025-11-25 06:38:46', '2025-12-02 18:45:55', NULL, '2025-11-02 18:45:55', '2026-01-02 02:29:32'),
(380, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'expired', NULL, '2025-11-02 18:46:02', NULL, '2025-12-02 18:46:02', NULL, '2025-11-02 18:46:02', '2026-01-02 02:29:32'),
(381, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'expired', NULL, '2025-11-02 18:46:09', NULL, '2025-12-02 18:46:09', NULL, '2025-11-02 18:46:09', '2026-01-02 02:29:32'),
(382, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(383, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(384, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(385, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(386, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(387, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(388, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(389, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(390, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(391, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(392, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(393, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(394, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(395, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(396, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(397, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(398, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(399, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(400, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(401, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(402, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(403, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(404, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(405, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(406, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(407, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(408, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(409, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(410, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(411, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(412, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(413, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(414, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(415, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(416, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(417, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(418, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(419, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(420, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(421, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(422, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(423, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(424, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(425, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(426, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(427, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(428, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(429, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(430, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(431, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(432, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(433, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(434, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(435, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(436, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(437, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(438, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(439, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(440, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(441, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(442, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(443, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(444, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(445, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(446, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(447, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(448, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(449, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55');
INSERT INTO `lottery_rewards` (`id`, `user_id`, `reward_template_id`, `reward_name`, `reward_type`, `reward_value`, `reward_description`, `voucher_code`, `reward_image`, `status`, `ticket_id`, `won_at`, `used_at`, `expires_at`, `notes`, `created_at`, `updated_at`) VALUES
(450, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(451, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(452, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(453, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(454, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(455, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(456, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(457, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(458, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(459, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(460, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(461, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(462, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(463, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(464, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(465, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(466, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(467, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(468, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(469, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(470, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(471, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(472, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(473, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(474, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(475, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(476, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(477, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(478, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(479, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(480, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(481, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(482, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(483, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(484, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(485, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(486, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(487, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(488, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(489, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(490, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(491, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(492, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(493, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(494, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(495, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(496, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(497, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(498, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(499, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(500, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(501, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(502, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(503, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(504, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(505, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(506, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(507, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(508, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(509, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(510, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(511, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(512, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(513, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(514, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(515, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(516, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(517, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(518, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(519, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(520, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(521, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(522, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(523, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(524, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(525, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(526, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(527, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(528, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(529, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(530, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(531, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(532, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(533, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(534, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(535, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(536, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(537, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(538, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(539, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(540, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(541, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(542, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(543, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(544, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(545, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(546, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(547, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(548, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(549, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(550, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(551, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(552, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(553, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(554, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(555, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(556, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(557, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(558, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(559, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(560, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(561, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(562, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(563, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(564, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(565, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(566, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(567, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(568, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(569, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(570, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(571, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(572, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(573, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(574, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(575, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(576, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(577, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(578, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(579, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(580, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(581, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(582, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(583, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(584, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(585, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(586, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(587, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(588, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(589, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(590, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(591, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(592, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(593, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(594, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(595, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(596, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(597, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(598, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(599, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(600, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(601, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(602, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(603, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(604, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(605, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-03 02:49:55', NULL, '2025-12-03 02:49:55', NULL, '2025-11-03 02:49:55', '2025-11-03 02:49:55'),
(606, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'expired', NULL, '2025-11-25 06:41:08', NULL, '2025-12-25 06:41:08', NULL, '2025-11-25 06:41:08', '2026-01-02 02:29:32'),
(607, 2, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'expired', NULL, '2025-11-25 06:41:15', NULL, '2025-12-25 06:41:15', NULL, '2025-11-25 06:41:15', '2026-01-02 02:29:32'),
(608, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:38', NULL, '2025-12-27 15:21:38', NULL, '2025-11-27 15:21:38', '2025-11-27 15:21:38'),
(609, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:45', NULL, '2025-12-27 15:21:45', NULL, '2025-11-27 15:21:45', '2025-11-27 15:21:45'),
(610, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(611, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(612, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(613, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(614, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(615, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(616, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(617, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(618, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(619, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(620, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(621, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(622, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(623, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(624, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(625, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(626, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(627, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(628, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(629, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(630, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(631, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(632, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(633, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(634, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(635, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(636, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(637, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(638, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(639, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(640, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(641, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(642, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(643, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(644, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(645, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(646, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(647, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(648, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(649, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(650, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(651, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(652, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(653, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(654, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(655, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(656, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(657, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(658, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(659, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(660, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(661, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(662, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(663, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(664, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(665, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(666, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(667, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(668, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(669, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(670, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(671, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(672, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(673, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(674, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(675, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(676, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(677, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(678, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(679, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(680, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(681, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54');
INSERT INTO `lottery_rewards` (`id`, `user_id`, `reward_template_id`, `reward_name`, `reward_type`, `reward_value`, `reward_description`, `voucher_code`, `reward_image`, `status`, `ticket_id`, `won_at`, `used_at`, `expires_at`, `notes`, `created_at`, `updated_at`) VALUES
(682, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(683, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(684, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(685, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(686, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(687, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(688, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(689, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(690, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(691, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(692, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(693, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(694, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(695, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(696, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(697, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(698, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(699, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(700, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(701, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(702, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(703, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(704, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(705, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(706, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(707, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(708, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(709, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(710, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(711, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(712, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(713, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(714, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(715, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(716, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(717, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(718, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(719, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(720, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(721, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(722, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(723, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(724, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(725, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(726, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(727, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(728, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(729, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(730, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(731, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(732, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(733, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(734, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(735, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(736, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(737, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(738, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(739, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(740, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(741, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(742, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(743, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(744, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(745, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(746, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(747, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(748, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(749, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(750, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(751, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(752, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(753, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(754, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(755, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(756, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(757, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(758, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(759, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(760, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(761, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(762, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(763, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(764, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(765, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(766, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(767, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(768, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(769, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(770, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(771, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(772, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(773, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(774, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(775, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(776, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(777, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(778, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(779, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(780, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(781, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(782, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(783, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(784, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(785, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(786, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(787, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(788, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(789, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(790, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(791, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(792, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(793, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(794, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(795, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(796, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(797, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(798, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(799, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(800, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(801, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(802, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(803, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(804, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(805, 5, NULL, 'Chúc may mắn lần sau!', 'gift', NULL, 'Hãy thử lại lần sau nhé!', NULL, NULL, 'pending', NULL, '2025-11-27 15:21:54', NULL, '2025-12-27 15:21:54', NULL, '2025-11-27 15:21:54', '2025-11-27 15:21:54'),
(806, 5, 1, 'Voucher giảm 500.000đ', 'voucher', 500000.00, 'Voucher giảm giá 500.000đ cho đơn hàng tiếp theo', 'VC69286D7D4F560', NULL, 'used', NULL, '2025-11-27 15:25:49', '2025-11-27 15:32:02', '2025-12-27 15:25:49', NULL, '2025-11-27 15:25:49', '2025-11-27 15:32:02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lottery_tickets`
--

CREATE TABLE `lottery_tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `ticket_type` enum('purchase','bonus','promotion') DEFAULT 'purchase',
  `status` enum('active','used','expired') DEFAULT 'active',
  `pre_assigned_reward_id` int(11) DEFAULT NULL COMMENT 'Phần thưởng được admin set trước',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Lưu vé quay may mắn của người dùng';

--
-- Đang đổ dữ liệu cho bảng `lottery_tickets`
--

INSERT INTO `lottery_tickets` (`id`, `user_id`, `order_id`, `ticket_type`, `status`, `pre_assigned_reward_id`, `created_at`, `expires_at`) VALUES
(168, 5, 8, 'purchase', 'used', NULL, '2025-11-02 14:34:54', NULL),
(169, 5, 8, 'purchase', 'used', NULL, '2025-11-02 14:34:54', NULL),
(170, 5, 8, 'purchase', 'used', NULL, '2025-11-02 14:34:54', NULL),
(171, 5, 8, 'purchase', 'used', 7, '2025-11-02 14:34:54', NULL),
(172, 5, 8, 'purchase', 'used', NULL, '2025-11-02 14:34:54', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `city` varchar(255) NOT NULL COMMENT 'Tỉnh/Thành phố',
  `district` varchar(255) NOT NULL COMMENT 'Quận/Huyện',
  `ward` varchar(255) DEFAULT NULL COMMENT 'Phường/Xã',
  `address` varchar(500) NOT NULL COMMENT 'Địa chỉ chi tiết',
  `notes` text DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL COMMENT 'Tổng tiền trước giảm giá',
  `voucher_code` varchar(500) DEFAULT NULL COMMENT 'Danh sách mã voucher (có thể nhiều mã, cách nhau bởi dấu phẩy)',
  `discount_amount` decimal(15,2) DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL COMMENT 'Tổng tiền sau giảm giá',
  `order_status` enum('pending','approved','processing','shipping','shipped','delivered','cancelled') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL COMMENT 'Admin ID duyệt đơn',
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `full_name`, `phone`, `email`, `city`, `district`, `ward`, `address`, `notes`, `subtotal`, `voucher_code`, `discount_amount`, `total_amount`, `order_status`, `approved_by`, `approved_at`, `created_at`) VALUES
(1, 2, 'ĐỖ QUANG PHÚC', '0375779219', 'doquangphuc21@gmail.com', 'Tỉnh Yên Bái', 'Thị xã Nghĩa Lộ', 'Xã Phù Nham', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 1950000.00, NULL, 0.00, 1950000.00, 'approved', 2, '2025-10-28 00:28:18', '2025-10-28 00:02:26'),
(2, 2, 'ĐỖ QUANG PHÚC', '0375779219', 'doquangphuc21@gmail.com', 'Tỉnh Hoà Bình', 'Huyện Yên Thủy', 'Xã Đoàn Kết', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 1950000.00, 'VC6900104027C86', 1000000.00, 950000.00, 'approved', 2, '2025-10-28 00:39:00', '2025-10-28 00:37:48'),
(3, 4, 'hai', '0987955829', 'onemusicdanang@gmail.com', 'Thành phố Đà Nẵng', 'Quận Sơn Trà', 'Phường Thọ Quang', '93 Võ Duy Ninh, Phường Thọ Quang, Sơn Trà, Đà Nẵng', '', 40250000.00, NULL, 0.00, 40250000.00, 'approved', 2, '2025-10-28 16:38:42', '2025-10-28 14:46:53'),
(4, 2, 'ĐỖ QUANG PHÚC', '0375779219', 'doquangphuc21@gmail.com', 'Tỉnh Cao Bằng', 'Huyện Bảo Lạc', 'Xã Hưng Đạo', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 1500000.00, NULL, 0.00, 1500000.00, 'approved', 2, '2025-10-28 16:34:03', '2025-10-28 16:33:41'),
(5, 3, 'ĐỖ QUANG PHÚC', '0375779219', 'doquangphuc21@gmail.com', 'Tỉnh Cao Bằng', 'Huyện Bảo Lạc', 'Xã Hồng An', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 1950000.00, NULL, 0.00, 1950000.00, 'approved', 2, '2025-10-28 16:35:08', '2025-10-28 16:34:48'),
(6, 3, 'ĐỖ QUANG PHÚC', '0375779219', 'doquangphuc21@gmail.com', 'Tỉnh Cao Bằng', 'Huyện Bảo Lạc', 'Xã Phan Thanh', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 20125000.00, NULL, 0.00, 20125000.00, 'approved', 2, '2025-10-28 16:37:36', '2025-10-28 16:36:31'),
(7, 3, 'ĐỖ QUANG PHÚC', '0375779219', 'doquangphuc21@gmail.com', 'Tỉnh Cao Bằng', 'Huyện Hạ Lang', 'Xã An Lạc', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 40250000.00, NULL, 0.00, 40250000.00, 'approved', 2, '2025-10-28 16:37:39', '2025-10-28 16:36:53'),
(8, 5, 'gia huy', '033838383838933993', '', 'Tỉnh Vĩnh Phúc', 'Thành phố Vĩnh Yên', 'Phường Tích Sơn', '323', 'Gói khảo sát điện mặt trời - 8.34kWp - 15 tấm pin - Inverter: Hybrid GEN-LB-EU 6K - Pin: Cell A-Cornex LiFePO4 16 Cell', 121430000.00, NULL, 0.00, 121430000.00, 'approved', 2, '2025-11-02 14:34:54', '2025-11-02 13:59:26'),
(9, 5, 'gia huy', '0992929292', '', 'Tỉnh Vĩnh Phúc', 'Thành phố Vĩnh Yên', 'Phường Tích Sơn', '9999', 'hu hi', 1850000.00, NULL, 1100000.00, 750000.00, 'approved', 2, '2025-11-02 14:46:03', '2025-11-02 14:45:34'),
(10, 2, 'Admin User', '0988919868', 'doquangphuc21@gmail.com', 'Tỉnh Cao Bằng', 'Huyện Bảo Lạc', 'Xã Hưng Đạo', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', 'Gói khảo sát điện mặt trời - 5.23kWp - 9 tấm pin - Inverter: ECO Hybrid 5kW (Bản mới 2025) - Pin: Cell A-Cornex LiFePO4 16 Cell', 74700000.00, NULL, 0.00, 74700000.00, 'approved', 2, '2025-11-02 18:08:48', '2025-11-02 18:07:58'),
(11, 2, 'Admin User', '0988919868', 'doquangphuc21@gmail.com', 'Tỉnh Cao Bằng', 'Huyện Quảng Hòa', 'Xã Bế Văn Đàn', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', 'Gói khảo sát điện mặt trời - 5.23kWp - 9 tấm pin - Inverter: ECO Hybrid 5kW (Bản mới 2025) - Pin: Cell A-Cornex LiFePO4 16 Cell', 74700000.00, NULL, 700000.00, 74000000.00, 'approved', 2, '2025-11-02 18:17:08', '2025-11-02 18:16:41'),
(12, 2, 'Admin User', '0988919868', 'doquangphuc21@gmail.com', 'Tỉnh Cao Bằng', 'Huyện Hà Quảng', 'Xã Thanh Long', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', 'Gói khảo sát điện mặt trời - 5.23kWp - 9 tấm pin - Inverter: ECO Hybrid 5kW (Bản mới 2025) - Pin: Cell A-Cornex LiFePO4 16 Cell', 74700000.00, NULL, 0.00, 74700000.00, 'approved', 2, '2025-11-02 18:23:07', '2025-11-02 18:23:00'),
(13, 2, 'Admin User', '0988919868', 'doquangphuc21@gmail.com', 'Tỉnh Cao Bằng', 'Huyện Nguyên Bình', 'Xã Quang Thành', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 123250000.00, NULL, 0.00, 123250000.00, 'approved', 2, '2025-11-02 18:39:58', '2025-11-02 18:39:41'),
(14, 2, 'Admin User', '0988919868', 'doquangphuc21@gmail.com', 'Tỉnh Cao Bằng', 'Huyện Quảng Hòa', 'Xã Cách Linh', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 73625000.00, NULL, 0.00, 73625000.00, 'approved', 2, '2025-11-02 18:45:21', '2025-11-02 18:45:06'),
(17, 2, 'Admin User', '0988919868', 'doquangphuc21@gmail.com', 'Tỉnh Cao Bằng', 'Huyện Quảng Hòa', 'Xã Hạnh Phúc', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 13850000.00, NULL, 0.00, 13850000.00, 'pending', NULL, NULL, '2025-11-25 06:28:47'),
(18, 2, 'Admin User', '0988919868', 'doquangphuc21@gmail.com', 'Tỉnh Cao Bằng', 'Huyện Bảo Lạc', 'Xã Cô Ba', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 31250000.00, NULL, 0.00, 31250000.00, 'approved', 2, '2025-11-25 06:40:58', '2025-11-25 06:29:03'),
(20, 2, 'Admin User', '0988919868', 'doquangphuc21@gmail.com', 'Tỉnh Cao Bằng', 'Huyện Bảo Lâm', 'Xã Thạch Lâm', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 39350000.00, NULL, 900000.00, 38450000.00, 'approved', 2, '2025-11-25 06:40:38', '2025-11-25 06:38:46'),
(21, 2, 'Admin User', '0988919868', 'phucdq.21ce@vku.udn.vn', 'Tỉnh Lạng Sơn', 'Huyện Hữu Lũng', 'Xã Yên Bình', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 13850000.00, NULL, 400000.00, 13450000.00, 'pending', NULL, NULL, '2025-11-25 07:07:29'),
(22, 2, 'Admin User', '0988919868', 'phucdq.21ce@vku.udn.vn', 'Tỉnh Lạng Sơn', 'Huyện Bắc Sơn', 'Xã Vũ Lăng', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 31250000.00, NULL, 600000.00, 30650000.00, 'pending', NULL, NULL, '2025-11-25 07:22:34'),
(23, 2, 'Admin User', '0988919868', 'phucdq.21ce@vku.udn.vn', 'Tỉnh Lạng Sơn', 'Huyện Tràng Định', 'Xã Cao Minh', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 59600000.00, NULL, 700000.00, 58900000.00, 'pending', NULL, NULL, '2025-11-25 07:23:54'),
(24, 2, 'Admin User', '0988919868', 'phucdq.21ce@vku.udn.vn', 'Tỉnh Lạng Sơn', 'Huyện Cao Lộc', 'Xã Phú Xá', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 17650000.00, NULL, 400000.00, 17250000.00, 'pending', NULL, NULL, '2025-11-25 07:28:07'),
(25, 2, 'Admin User', '0988919868', 'phucdq.21ce@vku.udn.vn', 'Tỉnh Lạng Sơn', 'Huyện Lộc Bình', 'Xã Hữu Lân', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 120950000.00, NULL, 600000.00, 120350000.00, 'pending', NULL, NULL, '2025-11-25 07:36:13'),
(26, 2, 'Admin User', '0988919868', 'phucdq.21ce@vku.udn.vn', 'Tỉnh Lạng Sơn', 'Huyện Tràng Định', 'Xã Trung Thành', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 13850000.00, 'VC6907A1BDCDC1B, VC6907A1BDCDDBC', 200000.00, 13650000.00, 'pending', NULL, NULL, '2025-11-25 08:00:04'),
(27, 5, 'giahuy', '0987955829', 'onemusicdanang@gmail.com', 'Tỉnh Vĩnh Phúc', 'Thành phố Vĩnh Yên', 'Phường Liên Bảo', '93 Võ Duy Ninh, Phường Thọ Quang, Sơn Trà, Đà Nẵng', 'Gói khảo sát điện mặt trời - 7.59kWp - 13 tấm pin - Inverter: ECO Hybrid 6kW - Pin: PIN GS POWER - Wifi - Bluetooth', 88420000.00, NULL, 0.00, 88420000.00, 'approved', 2, '2025-11-27 15:20:52', '2025-11-27 15:18:26'),
(28, 5, 'giahuy', '0987955829', 'onemusicdanang@gmail.com', 'Tỉnh Bắc Ninh', 'Thành phố Bắc Ninh', 'Phường Khắc Niệm', '93 Võ Duy Ninh, Phường Thọ Quang, Sơn Trà, Đà Nẵng', '', 16900000.00, NULL, 0.00, 16900000.00, 'pending', NULL, NULL, '2025-11-27 15:24:39'),
(29, 5, 'giahuy', '0987955829', 'onemusicdanang@gmail.com', 'Tỉnh Hải Dương', 'Thành phố Hải Dương', 'Xã Tiền Tiến', '93 Võ Duy Ninh, Phường Thọ Quang, Sơn Trà, Đà Nẵng', '', 29500000.00, NULL, 0.00, 29500000.00, 'pending', NULL, NULL, '2025-11-27 15:28:21'),
(30, 5, 'giahuy', '0987955829', 'onemusicdanang@gmail.com', 'Tỉnh Hải Dương', 'Thành phố Hải Dương', 'Xã Tiền Tiến', '93 Võ Duy Ninh, Phường Thọ Quang, Sơn Trà, Đà Nẵng', '', 13850000.00, 'VC69286D7D4F560', 500000.00, 13350000.00, 'pending', NULL, NULL, '2025-11-27 15:32:02'),
(31, 2, 'Admin User', '0988919868', 'phucdq.21ce@vku.udn.vn', 'Tỉnh Lạng Sơn', 'Huyện Bình Gia', 'Xã Minh Khai', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 67600000.00, 'VC6907A1BDCDF75, VC6907A1BDCE11C', 200000.00, 67400000.00, 'pending', NULL, NULL, '2025-11-27 15:43:15'),
(32, 2, 'Admin User', '0988919868', 'phucdq.21ce@vku.udn.vn', 'Tỉnh Lạng Sơn', 'Huyện Tràng Định', 'Xã Tân Minh', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 59000000.00, 'VC6907A1BDCE338, VC6907A1BDCE567, VC6907A1BDCE887', 500000.00, 58500000.00, 'pending', NULL, NULL, '2025-11-27 16:29:57'),
(33, 2, 'Admin User', '0988919868', 'phucdq.21ce@vku.udn.vn', 'Tỉnh Lạng Sơn', 'Huyện Bắc Sơn', 'Xã Chiến Thắng', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 29500000.00, 'VC6907A1BDCEE5C, VC6907A1BDCF023', 200000.00, 29300000.00, 'pending', NULL, NULL, '2025-11-27 16:38:04'),
(34, 2, 'Admin User', '0988919868', 'phucdq.21ce@vku.udn.vn', 'Tỉnh Lạng Sơn', 'Huyện Bắc Sơn', 'Xã Tân Hương', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 1850000.00, 'VC6907A1BDCF45C, VC6907A1BDCF62D', 300000.00, 1550000.00, 'pending', NULL, NULL, '2025-11-27 16:38:28'),
(35, 2, 'Admin User', '0988919868', 'phucdq.21ce@vku.udn.vn', 'Tỉnh Lạng Sơn', 'Huyện Hữu Lũng', 'Xã Hòa Lạc', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 29500000.00, 'VC6907A1BDCF99D, VC6907A1BDCFC86', 200000.00, 29300000.00, 'pending', NULL, NULL, '2025-11-27 16:38:50'),
(37, 2, 'Admin User', '0988919868', 'phucdq.21ce@vku.udn.vn', 'Tỉnh Lạng Sơn', 'Huyện Cao Lộc', 'Xã Phú Xá', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', 'Gói khảo sát điện mặt trời - 61.11kWp - 104 tấm pin - Inverter: ECO Hybrid 12kW - Pin: PIN LƯU TRỮ  32,6kw', 560160000.00, 'VC6907A1BDD007A, VC6907A1BDD04B8, VC6907A1BDD08AE, VC6907A1BDD0CBB, VC6907A1BDD0E7A, VC6907A1BDD100F', 1000000.00, 559160000.00, 'pending', NULL, NULL, '2025-11-27 17:14:45'),
(38, 2, 'Admin User', '0988919868', 'phucdq.21ce@vku.udn.vn', 'Tỉnh Lạng Sơn', 'Thành phố Lạng Sơn', 'Phường Tam Thanh', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', 'Gói khảo sát điện mặt trời - 61.11kWp - 104 tấm pin - Inverter: Hybrid GEN-LB-EU 10K - Pin: Cell A-Cornex LiFePO4 16 Cell', 572110000.00, 'VC6907A1BDD11D2, VC6907A1BDD137B', 400000.00, 571710000.00, 'pending', NULL, NULL, '2025-11-27 17:59:34'),
(39, 2, 'Admin User', '0988919868', 'phucdq.21ce@vku.udn.vn', 'Tỉnh Lạng Sơn', 'Thành phố Lạng Sơn', 'Phường Tam Thanh', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', 'Gói khảo sát điện mặt trời - 61.11kWp - 104 tấm pin - Inverter: Hybrid GEN-LB-EU 10K - Pin: Cell A-Cornex LiFePO4 16 Cell', 572110000.00, 'VC6907A1BDD150F, VC6907A1BDD16B4', 300000.00, 571810000.00, 'pending', NULL, NULL, '2025-11-27 18:00:44'),
(40, 2, 'Admin User', '0988919868', 'phucdq.21ce@vku.udn.vn', 'Tỉnh Lạng Sơn', 'Huyện Tràng Định', 'Xã Kim Đồng', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', 'Gói khảo sát điện mặt trời - 61.11kWp - 104 tấm pin - Inverter: ECO Hybrid 12kW - Pin: Cell A-Cornex LiFePO4 16 Cell', 511660000.00, 'VC6907A1BDD1E20, VC6907A1BDD1FC1', 200000.00, 511460000.00, 'pending', NULL, NULL, '2025-11-27 18:17:46'),
(41, 2, 'Admin User', '0988919868', 'phucdq.21ce@vku.udn.vn', 'Tỉnh Lạng Sơn', 'Huyện Bình Gia', 'Xã Minh Khai', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', 'Gói khảo sát điện mặt trời - 61.11kWp - 104 tấm pin - Inverter: Hybrid GEN-LB-EU 10K - Pin: PIN LƯU TRỮ  32,6kw', 569410000.00, 'VC6907A1BDD22AD, VC6907A1BDD244C, VC6907A1BDD32DA, VC6907A1BDD3598', 600000.00, 568810000.00, 'pending', NULL, NULL, '2025-11-27 18:28:18');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL COMMENT 'NULL for virtual items from survey',
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `image_url` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `price`, `image_url`) VALUES
(1, 1, 2, 'Tấm Pin Jinko Solar 630W Tiger Neo', 1, 1950000.00, '../assets/img/products/tam-pin-jinko-solar-630w-tiger-neo.png'),
(2, 2, 2, 'Tấm Pin Jinko Solar 630W Tiger Neo', 1, 1950000.00, '../assets/img/products/tam-pin-jinko-solar-630w-tiger-neo.png'),
(3, 3, 4, 'ECO Hybrid 6kW', 2, 20125000.00, '../assets/img/products/luxpower-6kw-gen.png'),
(4, 4, 35, 'CT Ngoài LuxPower SNA 6kW', 1, 1500000.00, '../assets/img/products/ct-ngoai-luxpower-sna-6kw.png'),
(5, 5, 2, 'Tấm Pin Jinko Solar 630W Tiger Neo', 1, 1950000.00, '../assets/img/products/tam-pin-jinko-solar-630w-tiger-neo.png'),
(6, 6, 4, 'ECO Hybrid 6kW', 1, 20125000.00, '../assets/img/products/luxpower-6kw-gen.png'),
(7, 7, 4, 'ECO Hybrid 6kW', 2, 20125000.00, '../assets/img/products/luxpower-6kw-gen.png'),
(8, 8, 1, 'Tấm Pin Jinko Solar 590W Tiger Neo (590W | 22.3%)', 15, 1850000.00, '../assets/img/products/product_1761917665_6904bae1c9306.jpg'),
(9, 8, 6, 'Hybrid GEN-LB-EU 6K', 1, 21350000.00, '../assets/img/products/hybrid-gen-lb-eu-6k.png'),
(10, 8, 12, 'Tủ điện Hybrid 1 pha 6kW', 1, 1850000.00, '../assets/img/products/electrical-cabinet.jpg'),
(11, 8, 11, 'Cell A-Cornex LiFePO4 16 Cell', 2, 25500000.00, '../assets/img/products/cell-a-cornex-lifepo4-16cell.png'),
(12, 8, NULL, 'Dongles Lan LuxPower', 1, 1380000.00, '../assets/img/products/dongles-lan-luxpower.png'),
(13, 8, NULL, 'Wifi LuxPower', 1, 1150000.00, '../assets/img/products/wifi-luxpower.png'),
(14, 8, NULL, 'Bách Z Mạ Kẽm', 90, 80000.00, '../assets/img/products/bachz.png'),
(15, 8, NULL, 'Jack MC4 1500VDC', 15, 50000.00, '../assets/img/products/jackcam.png'),
(16, 8, NULL, 'Dây Điện (AC/DC)', 100, 30000.00, '../assets/img/products/daydien.png'),
(17, 8, NULL, 'Chi phí công thợ lắp đặt trọn gói', 1, 6000000.00, NULL),
(18, 8, NULL, 'Chi phí vận chuyển thiết bị', 1, 0.00, NULL),
(19, 9, 1, 'Tấm Pin Jinko Solar 590W Tiger Neo', 1, 1850000.00, '..//assets/img/products/product_1761917665_6904bae1c9306.jpg'),
(20, 10, 1, 'Tấm Pin Jinko Solar 590W Tiger Neo (590W | 22.3%)', 9, 1850000.00, '../assets/img/products/product_1761917665_6904bae1c9306.jpg'),
(21, 10, NULL, 'ECO Hybrid 5kW (Bản mới 2025)', 1, 14500000.00, '../assets/img/products/luxpower-6kw-gen.png'),
(22, 10, 12, 'Tủ điện Hybrid 1 pha 6kW', 1, 1850000.00, '../assets/img/products/electrical-cabinet.jpg'),
(23, 10, 11, 'Cell A-Cornex LiFePO4 16 Cell', 1, 25500000.00, '../assets/img/products/cell-a-cornex-lifepo4-16cell.png'),
(24, 10, NULL, 'Dây Điện (AC/DC)', 100, 30000.00, '../assets/img/products/daydien.png'),
(25, 10, NULL, 'Gói Phụ Kiện 6-8kw', 1, 7200000.00, '../assets/img/products/product_1762093315_690769030bd65.jpg'),
(26, 10, NULL, 'Công Thợ Lắp Đặt Trọn Gói', 1, 6000000.00, NULL),
(27, 10, NULL, 'Vận Chuyển Đến Công Trình', 1, 0.00, NULL),
(28, 11, 1, 'Tấm Pin Jinko Solar 590W Tiger Neo (590W | 22.3%)', 9, 1850000.00, '../assets/img/products/product_1761917665_6904bae1c9306.jpg'),
(29, 11, 3, 'ECO Hybrid 5kW (Bản mới 2025)', 1, 14500000.00, '../assets/img/products/luxpower-6kw-gen.png'),
(30, 11, 12, 'Tủ điện Hybrid 1 pha 6kW', 1, 1850000.00, '../assets/img/products/electrical-cabinet.jpg'),
(31, 11, 11, 'Cell A-Cornex LiFePO4 16 Cell', 1, 25500000.00, '../assets/img/products/cell-a-cornex-lifepo4-16cell.png'),
(32, 11, NULL, 'Dây Điện (AC/DC)', 100, 30000.00, '../assets/img/products/daydien.png'),
(33, 11, NULL, 'Gói Phụ Kiện 6-8kw', 1, 7200000.00, '../assets/img/products/product_1762093315_690769030bd65.jpg'),
(34, 11, NULL, 'Chi phí công thợ lắp đặt trọn gói', 1, 6000000.00, NULL),
(35, 11, NULL, 'Chi phí vận chuyển thiết bị', 1, 0.00, NULL),
(36, 12, 1, 'Tấm Pin Jinko Solar 590W Tiger Neo (590W | 22.3%)', 9, 1850000.00, '../assets/img/products/product_1761917665_6904bae1c9306.jpg'),
(37, 12, NULL, 'ECO Hybrid 5kW (Bản mới 2025)', 1, 14500000.00, '../assets/img/products/luxpower-6kw-gen.png'),
(38, 12, 12, 'Tủ điện Hybrid 1 pha 6kW', 1, 1850000.00, '../assets/img/products/electrical-cabinet.jpg'),
(39, 12, 11, 'Cell A-Cornex LiFePO4 16 Cell', 1, 25500000.00, '../assets/img/products/cell-a-cornex-lifepo4-16cell.png'),
(40, 12, NULL, 'Dây Điện (AC/DC)', 100, 30000.00, '../assets/img/products/daydien.png'),
(41, 12, NULL, 'Gói Phụ Kiện 6-8kw', 1, 7200000.00, '../assets/img/products/product_1762093315_690769030bd65.jpg'),
(42, 12, NULL, 'Công Thợ Lắp Đặt Trọn Gói', 1, 6000000.00, NULL),
(43, 12, NULL, 'Vận Chuyển Đến Công Trình', 1, 0.00, NULL),
(44, 13, 27, 'Pin lưu trữ A-Cornex', 1, 25500000.00, '../assets/img/products/pin-luu-tru-acornex.jpg'),
(45, 13, 31, 'Hybrid TRIP 25k', 1, 97750000.00, '../assets/img/products/hybrid-trip-25k.png'),
(46, 14, 1, 'Tấm Pin Jinko Solar 590W Tiger Neo', 1, 1850000.00, '..//assets/img/products/product_1761917665_6904bae1c9306.jpg'),
(47, 14, 2, 'Tấm Pin Jinko Solar 630W Tiger Neo', 1, 1950000.00, '../assets/img/products/tam-pin-jinko-solar-630w-tiger-neo.png'),
(48, 14, 3, 'ECO Hybrid 5kW (Bản mới 2025)', 1, 14500000.00, '..//assets/img/products/luxpower-6kw-gen.png'),
(49, 14, 4, 'ECO Hybrid 6kW', 1, 14500000.00, '..//assets/img/products/luxpower-6kw-gen.png'),
(50, 14, 5, 'ECO Hybrid 12kW', 1, 40825000.00, '../assets/img/products/eco-hybrid-12kw-sna12k.png'),
(51, 17, 3, 'ECO Hybrid 5kW (Bản mới 2025)', 1, 13850000.00, '..//assets/img/products/luxpower-6kw-gen.png'),
(52, 18, 5, 'ECO Hybrid 12kW', 1, 31250000.00, '..//assets/img/products/product_1762136850_69081312978ab.png'),
(53, 20, 8, 'Hybrid GEN-LB-EU 10K', 1, 39350000.00, '../assets/img/products/hybrid-gen-lb-eu-10k.png'),
(54, 21, 3, 'ECO Hybrid 5kW (Bản mới 2025)', 1, 13850000.00, '..//assets/img/products/luxpower-6kw-gen.png'),
(55, 22, 5, 'ECO Hybrid 12kW', 1, 31250000.00, '..//assets/img/products/product_1762136850_69081312978ab.png'),
(56, 23, 3, 'ECO Hybrid 5kW (Bản mới 2025)', 1, 13850000.00, '..//assets/img/products/luxpower-6kw-gen.png'),
(57, 23, 4, 'ECO Hybrid 6kW', 1, 14500000.00, '..//assets/img/products/luxpower-6kw-gen.png'),
(58, 23, 5, 'ECO Hybrid 12kW', 1, 31250000.00, '..//assets/img/products/product_1762136850_69081312978ab.png'),
(59, 24, 1, 'Tấm Pin Jinko Solar 590W Tiger Neo', 1, 1850000.00, '..//assets/img/products/product_1761917665_6904bae1c9306.jpg'),
(60, 24, 2, 'Tấm Pin Jinko Solar 630W Tiger Neo', 1, 1950000.00, '..//assets/img/products/product_1762136900_6908134464d5d.jpg'),
(61, 24, 3, 'ECO Hybrid 5kW (Bản mới 2025)', 1, 13850000.00, '..//assets/img/products/luxpower-6kw-gen.png'),
(62, 25, 7, 'Hybrid GEN-LB-EU 8K', 1, 37250000.00, '../assets/img/products/hybrid-gen-lb-eu-8k.png'),
(63, 25, 8, 'Hybrid GEN-LB-EU 10K', 1, 39350000.00, '../assets/img/products/hybrid-gen-lb-eu-10k.png'),
(64, 25, 9, 'Hybrid LXP-12K 12kW', 1, 44350000.00, '../assets/img/products/hybrid-lxp-12k.png'),
(65, 26, 3, 'ECO Hybrid 5kW (Bản mới 2025)', 1, 13850000.00, '..//assets/img/products/luxpower-6kw-gen.png'),
(66, 27, 1, 'Tấm Pin Jinko Solar 590W Tiger Neo (590W | 22.3%)', 13, 1850000.00, '../assets/img/products/product_1761917665_6904bae1c9306.jpg'),
(67, 27, NULL, 'ECO Hybrid 6kW', 1, 14500000.00, '../assets/img/products/luxpower-6kw-gen.png'),
(68, 27, 12, 'Tủ điện Hybrid 1 pha 6kW', 1, 1850000.00, '../assets/img/products/electrical-cabinet.jpg'),
(69, 27, 39, 'PIN GS POWER - Wifi - Bluetooth', 1, 29500000.00, '../assets/img/products/product_1764252850_69285cb253c16.png'),
(70, 27, NULL, 'Kẹp biên, Kẹp giữa tấm Pin', 78, 15000.00, '../assets/img/products/kepbien-tamgiua.png'),
(71, 27, NULL, 'Dây Điện (AC/DC)', 100, 30000.00, '../assets/img/products/daydien.png'),
(72, 27, NULL, 'Wifi LuxPower', 1, 1150000.00, '../assets/img/products/wifi-luxpower.png'),
(73, 27, NULL, 'Gói Phụ Kiện 6-8kw', 1, 7200000.00, '../assets/img/products/product_1762093315_690769030bd65.jpg'),
(74, 27, NULL, 'Công Thợ Lắp Đặt Trọn Gói', 1, 6000000.00, NULL),
(75, 27, NULL, 'Vận Chuyển Đến Công Trình', 1, 0.00, NULL),
(76, 28, 38, 'Biến Tần Huvi-Eco 6300W', 1, 16900000.00, '..//assets/img/products/product_1764243474_69283812c8824.png'),
(77, 29, 39, 'PIN GS POWER - Wifi - Bluetooth', 1, 29500000.00, '..//assets/img/products/product_1764252850_69285cb253c16.png'),
(78, 30, 3, 'ECO Hybrid 5kW (Bản mới 2025)', 1, 13850000.00, '..//assets/img/products/luxpower-6kw-gen.png'),
(79, 31, 38, 'Biến Tần Huvi-Eco 6300W', 4, 16900000.00, '..//assets/img/products/product_1764243474_69283812c8824.png'),
(80, 32, 39, 'PIN GS POWER - Wifi - Bluetooth', 2, 29500000.00, '..//assets/img/products/product_1764252850_69285cb253c16.png'),
(81, 33, 39, 'PIN GS POWER - Wifi - Bluetooth', 1, 29500000.00, '..//assets/img/products/product_1764252850_69285cb253c16.png'),
(82, 34, 1, 'Tấm Pin Jinko Solar 590W Tiger Neo', 1, 1850000.00, '..//assets/img/products/product_1761917665_6904bae1c9306.jpg'),
(83, 35, 39, 'PIN GS POWER - Wifi - Bluetooth', 1, 29500000.00, '..//assets/img/products/product_1764252850_69285cb253c16.png'),
(93, 37, 1, 'Tấm Pin Jinko Solar 590W Tiger Neo (590W | 22.3%)', 104, 1850000.00, '../assets/img/products/product_1761917665_6904bae1c9306.jpg'),
(94, 37, 5, 'ECO Hybrid 12kW', 4, 35500000.00, '../assets/img/products/product_1762136850_69081312978ab.png'),
(95, 37, 15, 'Tủ điện Hybrid 1 pha 12-14kW', 1, 3850000.00, '../assets/img/products/product_1761916997_6904b84553e3a.jpg'),
(96, 37, 41, 'PIN LƯU TRỮ  32,6kw', 4, 44000000.00, '../assets/img/products/product_1764253790_6928605e548d2.png'),
(97, 37, NULL, 'Kẹp biên, Kẹp giữa tấm Pin', 624, 15000.00, '../assets/img/products/kepbien-tamgiua.png'),
(98, 37, NULL, 'Dây Điện (AC/DC)', 100, 30000.00, '../assets/img/products/daydien.png'),
(99, 37, NULL, 'Wifi LuxPower', 1, 1150000.00, '../assets/img/products/wifi-luxpower.png'),
(100, 37, NULL, 'Công Thợ Lắp Đặt Trọn Gói', 1, 32400000.00, NULL),
(101, 37, NULL, 'Vận Chuyển Đến Công Trình', 1, 0.00, NULL),
(102, 38, 1, 'Tấm Pin Jinko Solar 590W Tiger Neo (590W | 22.3%)', 104, 1850000.00, '../assets/img/products/product_1761917665_6904bae1c9306.jpg'),
(103, 38, 8, 'Hybrid GEN-LB-EU 10K', 5, 39350000.00, '../assets/img/products/hybrid-gen-lb-eu-10k.png'),
(104, 38, 14, 'Tủ điện Hybrid 1 pha 10kW', 1, 2350000.00, '../assets/img/products/electrical-cabinet.jpg'),
(105, 38, 11, 'Cell A-Cornex LiFePO4 16 Cell', 5, 25500000.00, '../assets/img/products/cell-a-cornex-lifepo4-16cell.png'),
(106, 38, NULL, 'Kẹp biên, Kẹp giữa tấm Pin', 624, 15000.00, '../assets/img/products/kepbien-tamgiua.png'),
(107, 38, NULL, 'Dây Điện (AC/DC)', 100, 30000.00, '../assets/img/products/daydien.png'),
(108, 38, NULL, 'Wifi LuxPower', 1, 1150000.00, '../assets/img/products/wifi-luxpower.png'),
(109, 38, NULL, 'Gói Phụ Kiện 6-8kw', 1, 7200000.00, '../assets/img/products/product_1762093315_690769030bd65.jpg'),
(110, 38, NULL, 'Công Thợ Lắp Đặt Trọn Gói', 1, 32400000.00, NULL),
(111, 38, NULL, 'Vận Chuyển Đến Công Trình', 1, 0.00, NULL),
(112, 39, 1, 'Tấm Pin Jinko Solar 590W Tiger Neo (590W | 22.3%)', 104, 1850000.00, '../assets/img/products/product_1761917665_6904bae1c9306.jpg'),
(113, 39, 8, 'Hybrid GEN-LB-EU 10K', 5, 39350000.00, '../assets/img/products/hybrid-gen-lb-eu-10k.png'),
(114, 39, 14, 'Tủ điện Hybrid 1 pha 10kW', 1, 2350000.00, '../assets/img/products/electrical-cabinet.jpg'),
(115, 39, 11, 'Cell A-Cornex LiFePO4 16 Cell', 5, 25500000.00, '../assets/img/products/cell-a-cornex-lifepo4-16cell.png'),
(116, 39, NULL, 'Kẹp biên, Kẹp giữa tấm Pin', 624, 15000.00, '../assets/img/products/kepbien-tamgiua.png'),
(117, 39, NULL, 'Dây Điện (AC/DC)', 100, 30000.00, '../assets/img/products/daydien.png'),
(118, 39, NULL, 'Wifi LuxPower', 1, 1150000.00, '../assets/img/products/wifi-luxpower.png'),
(119, 39, NULL, 'Gói Phụ Kiện 6-8kw', 1, 7200000.00, '../assets/img/products/product_1762093315_690769030bd65.jpg'),
(120, 39, NULL, 'Chi phí công thợ lắp đặt trọn gói', 1, 32400000.00, NULL),
(121, 39, NULL, 'Chi phí vận chuyển thiết bị', 1, 0.00, NULL),
(122, 40, 1, 'Tấm Pin Jinko Solar 590W Tiger Neo (590W | 22.3%)', 104, 1850000.00, '../assets/img/products/product_1761917665_6904bae1c9306.jpg'),
(123, 40, 5, 'ECO Hybrid 12kW', 4, 35500000.00, '../assets/img/products/product_1762136850_69081312978ab.png'),
(124, 40, 15, 'Tủ điện Hybrid 1 pha 12-14kW', 1, 3850000.00, '../assets/img/products/product_1761916997_6904b84553e3a.jpg'),
(125, 40, 11, 'Cell A-Cornex LiFePO4 16 Cell', 5, 25500000.00, '../assets/img/products/cell-a-cornex-lifepo4-16cell.png'),
(126, 40, NULL, 'Kẹp biên, Kẹp giữa tấm Pin', 624, 15000.00, '../assets/img/products/kepbien-tamgiua.png'),
(127, 40, NULL, 'Dây Điện (AC/DC)', 100, 30000.00, '../assets/img/products/daydien.png'),
(128, 40, NULL, 'Wifi LuxPower', 1, 1150000.00, '../assets/img/products/wifi-luxpower.png'),
(129, 40, NULL, 'Công Thợ Lắp Đặt Trọn Gói', 1, 32400000.00, NULL),
(130, 40, NULL, 'Vận Chuyển Đến Công Trình', 1, 0.00, NULL),
(131, 41, 1, 'Tấm Pin Jinko Solar 590W Tiger Neo (590W | 22.3%)', 104, 1850000.00, '../assets/img/products/product_1761917665_6904bae1c9306.jpg'),
(132, 41, 8, 'Hybrid GEN-LB-EU 10K', 5, 39350000.00, '../assets/img/products/hybrid-gen-lb-eu-10k.png'),
(133, 41, 14, 'Tủ điện Hybrid 1 pha 10kW', 1, 2350000.00, '../assets/img/products/electrical-cabinet.jpg'),
(134, 41, 41, 'PIN LƯU TRỮ  32,6kw', 3, 44000000.00, '../assets/img/products/product_1764253790_6928605e548d2.png'),
(135, 41, NULL, 'Kẹp biên, Kẹp giữa tấm Pin', 624, 15000.00, '../assets/img/products/kepbien-tamgiua.png'),
(136, 41, NULL, 'Dây Điện (AC/DC)', 100, 30000.00, '../assets/img/products/daydien.png'),
(137, 41, NULL, 'Wifi LuxPower', 1, 1150000.00, '../assets/img/products/wifi-luxpower.png'),
(138, 41, NULL, 'Chi phí công thợ lắp đặt trọn gói', 1, 32400000.00, NULL),
(139, 41, NULL, 'Chi phí vận chuyển thiết bị', 1, 0.00, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_vouchers`
--

CREATE TABLE `order_vouchers` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `voucher_id` int(11) NOT NULL,
  `voucher_code` varchar(50) NOT NULL COMMENT 'Mã voucher',
  `discount_amount` decimal(15,2) NOT NULL COMMENT 'Số tiền giảm',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Lưu trữ nhiều vouchers cho 1 đơn hàng';

--
-- Đang đổ dữ liệu cho bảng `order_vouchers`
--

INSERT INTO `order_vouchers` (`id`, `order_id`, `voucher_id`, `voucher_code`, `discount_amount`, `created_at`) VALUES
(1, 9, 3, 'VC69076DA8A9542', 100000.00, '2025-11-02 14:45:34'),
(2, 9, 1, 'VC69076D7B2DCB0', 1000000.00, '2025-11-02 14:45:34'),
(3, 11, 7, 'VC69079F89E8FFD', 100000.00, '2025-11-02 18:16:41'),
(4, 11, 8, 'VC69079F8A45B53', 100000.00, '2025-11-02 18:16:41'),
(5, 11, 14, 'VC69079F8A46EE8', 100000.00, '2025-11-02 18:16:42'),
(6, 11, 15, 'VC69079F8A4709E', 100000.00, '2025-11-02 18:16:42'),
(7, 11, 20, 'VC69079F8A47CD6', 100000.00, '2025-11-02 18:16:42'),
(8, 11, 19, 'VC69079F8A47B2B', 100000.00, '2025-11-02 18:16:42'),
(9, 11, 23, 'VC69079F8A480E0', 100000.00, '2025-11-02 18:16:42');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `packages`
--

CREATE TABLE `packages` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(15,2) NOT NULL,
  `savings_per_month` varchar(100) DEFAULT NULL COMMENT 'Deprecated: Use highlights field instead',
  `payback_period` varchar(100) DEFAULT NULL COMMENT 'Deprecated: Use highlights field instead',
  `highlights` text DEFAULT NULL COMMENT 'JSON array of highlights: [{"title":"...", "content":"..."}]',
  `badge_text` varchar(100) DEFAULT NULL,
  `badge_color` varchar(50) DEFAULT 'green',
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Đang đổ dữ liệu cho bảng `packages`
--

INSERT INTO `packages` (`id`, `category_id`, `name`, `description`, `price`, `savings_per_month`, `payback_period`, `highlights`, `badge_text`, `badge_color`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Gói Solar 5,5kW - Hộ Gia Đình', 'Hệ thống điện mặt trời 3kW phù hợp cho gia đình 2-3 người, giúp giảm 70-80% hóa đơn điện hàng tháng.', 39250000.00, '~2.5 triệu/tháng', '4-5 năm', '[{\"title\":\"Ti\\u1ebft ki\\u1ec7m\\/th\\u00e1ng\",\"content\":\"~2,2 tri\\u1ec7u\\/th\\u00e1ng\"},{\"title\":\"Ho\\u00e0n v\\u1ed1n\",\"content\":\"4-5 n\\u0103m\"}]', 'PHỔ BIẾN', '#fc551d', 1, 1, '2025-10-27 11:31:09', '2025-11-30 13:53:22'),
(2, 1, 'Gói Solar 10.8kW - Gia Đình Vừa', 'Hệ thống điện mặt trời 5kW phù hợp cho gia đình 4-5 người, công suất cao, tiết kiệm tối đa.', 88000000.00, '~4 triệu/tháng', '4-5 năm', '[{\"title\":\"Ti\\u1ebft ki\\u1ec7m\\/th\\u00e1ng\",\"content\":\"~4,05 tri\\u1ec7u\\/th\\u00e1ng\"},{\"title\":\"Ho\\u00e0n v\\u1ed1n\",\"content\":\"2 n\\u0103m\"}]', 'BÁN CHẠY', '#ff425f', 2, 1, '2025-10-27 11:31:09', '2025-11-25 03:59:31'),
(3, 1, 'Gói Solar 17,6kW - 1 Pha', 'Hệ thống điện mặt trời 10kW phù hợp cho cửa hàng, văn phòng nhỏ, doanh nghiệp tiết kiệm chi phí.', 165000000.00, '~8 triệu/tháng', '4-5 năm', '[{\"title\":\"Ti\\u1ebft ki\\u1ec7m\\/th\\u00e1ng\",\"content\":\"~8,5 tri\\u1ec7u\\/th\\u00e1ng\"},{\"title\":\"Ho\\u00e0n v\\u1ed1n\",\"content\":\"1,7 n\\u0103m\"}]', 'KHUYẾN MÃI', '#10b981', 3, 1, '2025-10-27 11:31:09', '2025-11-03 02:24:47'),
(4, 1, 'Gói Solar 20kW - Nhà Xưởng', 'Hệ thống điện mặt trời 20kW phù hợp cho nhà xưởng, doanh nghiệp vừa, tiết kiệm năng lượng lớn.', 785000000.00, '~15 triệu/tháng', '4-5 năm', '[{\"title\":\"Ti\\u1ebft ki\\u1ec7m\\/th\\u00e1ng\",\"content\":\"~15 tri\\u1ec7u\\/th\\u00e1ng\"},{\"title\":\"Ho\\u00e0n v\\u1ed1n\",\"content\":\"4-5 n\\u0103m\"}]', 'TIẾT KIỆM', '#42f0cd', 4, 1, '2025-10-27 11:31:09', '2025-11-24 18:15:35'),
(5, 2, 'Hệ Thống Điện Nhà Thông Minh', 'Tích hợp hệ thống điện mặt trời với hệ thống điều khiển thông minh, tự động hóa toàn bộ.', 555000000.00, '~10 triệu/tháng', '4-5 năm', '[{\"title\":\"Tiết kiệm/tháng\",\"content\":\"~10 triệu/tháng\"},{\"title\":\"Hoàn vốn\",\"content\":\"4-5 năm\"}]', 'MỚI', '#8b5cf6', 7, 1, '2025-10-27 11:31:09', '2025-10-27 23:48:42');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `package_categories`
--

CREATE TABLE `package_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `logo_url` varchar(500) DEFAULT NULL COMMENT 'URL logo của danh mục',
  `badge_text` varchar(50) DEFAULT NULL COMMENT 'Văn bản badge (VD: PHỔ BIẾN, HOT, ƯU ĐÃI)',
  `badge_color` varchar(50) DEFAULT 'blue' COMMENT 'Màu badge (blue, green, red, yellow, purple, orange)',
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Đang đổ dữ liệu cho bảng `package_categories`
--

INSERT INTO `package_categories` (`id`, `name`, `logo_url`, `badge_text`, `badge_color`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Bảo Duy Solar', '/assets/img/package-categories/package-category_1761695061_690155556371e.jpg', 'Siêu Hot', '#fb8b23', 1, 1, '2025-10-27 11:31:08', '2025-10-28 23:44:21'),
(2, 'C - Home Building', '/assets/img/package-categories/package-category_1761695069_6901555d9710f.jpg', 'New', '#568de6', 2, 1, '2025-10-27 11:31:08', '2025-10-28 23:44:29'),
(3, 'Coffee', '/assets/img/package-categories/package-category_1761695078_69015566d7908.jpg', 'Bán Chạy', '#5ff7ec', 3, 1, '2025-10-27 12:38:52', '2025-10-28 23:44:38'),
(4, 'NewPen', '/assets/img/package-categories/package-category_1762173663_6908a2dfb709b.jpg', 'Nước Giặt', '#3b82f6', 4, 1, '2025-11-03 12:41:03', '2025-11-03 12:41:43'),
(5, 'HC Travel', '/assets/img/package-categories/package-category_1762173762_6908a342387ad.jpg', 'Travel', '#3b82f6', 5, 1, '2025-11-03 12:42:42', '2025-11-03 12:42:42');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `package_items`
--

CREATE TABLE `package_items` (
  `id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL COMMENT 'ID sản phẩm (nếu chọn từ danh sách sản phẩm)',
  `item_name` varchar(255) NOT NULL COMMENT 'Tên item (dùng khi product_id NULL)',
  `item_description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT 1 COMMENT 'Số lượng sản phẩm',
  `price_type` enum('market_price','category_price') DEFAULT 'market_price' COMMENT 'Loại giá sử dụng',
  `display_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Đang đổ dữ liệu cho bảng `package_items`
--

INSERT INTO `package_items` (`id`, `package_id`, `product_id`, `item_name`, `item_description`, `quantity`, `price_type`, `display_order`) VALUES
(24, 5, NULL, 'Tấm Pin Jinko 630W', '15 tấm pin Jinko Solar 630W Tiger Neo', 1, 'market_price', 1),
(25, 5, NULL, 'Inverter LuxPower 10kW', 'Bộ inverter hybrid 10kW GEN-LB-EU', 1, 'market_price', 2),
(26, 5, NULL, 'Pin Lưu Trữ BYD', '1 pin lưu trữ BYD 8.8kW (173Ah)', 1, 'market_price', 3),
(27, 5, NULL, 'Tủ Điện Thông Minh', 'Tủ điện hybrid 1 pha 12kW với điều khiển thông minh', 1, 'market_price', 4),
(28, 5, NULL, 'App Điều Khiển', 'Ứng dụng điện thoại điều khiển hệ thống', 1, 'market_price', 5),
(29, 5, NULL, 'WiFi Module', 'Modul kết nối WiFi', 1, 'market_price', 6),
(30, 5, NULL, 'Phụ Kiện Cao Cấp', 'Dây cáp DC cao cấp, kẹp pin, Bách Z và phụ kiện đầy đủ', 1, 'market_price', 7),
(48, 3, NULL, 'Tấm Pin Jinko 630W', '28 tấm pin Jinko Solar 630W Tiger Neo', 1, 'market_price', 1),
(49, 3, NULL, 'Inverter LuxPower 12kW', 'Bộ inverter hybrid 12kW ECO', 1, 'market_price', 2),
(50, 3, NULL, 'Pin A-Cornex 16 Cell', '1 pin lưu trữ A-Cornex 16 Cell', 1, 'market_price', 3),
(51, 3, NULL, 'Tủ Điện 1P-15kW', 'Tủ điện hybrid 1 pha 15kW đầy đủ thiết bị', 1, 'market_price', 4),
(52, 3, NULL, 'Dongles LAN', 'Modul kết nối internet LAN', 1, 'market_price', 5),
(53, 3, NULL, 'Phụ Kiện', 'Dây cáp DC, kẹp pin, Bách Z và phụ kiện đầy đủ', 1, 'market_price', 6),
(69, 4, NULL, 'Tấm Pin Jinko 630W', '40 tấm pin Jinko Solar 630W Tiger Neo', 1, 'market_price', 1),
(70, 4, NULL, 'Inverter LuxPower 25kW', 'Bộ inverter hybrid 25kW TRIP', 1, 'market_price', 2),
(71, 4, NULL, 'Pin A-Cornex 16 Cell', '2 pin lưu trữ A-Cornex 16 Cell', 1, 'market_price', 3),
(72, 4, NULL, 'Tủ Điện 3P-15kW', 'Tủ điện hybrid 3 pha 15kW đầy đủ thiết bị', 1, 'market_price', 4),
(73, 4, NULL, 'Dongles LAN', 'Modul kết nối internet LAN', 1, 'market_price', 5),
(74, 4, NULL, 'Phụ Kiện', 'Dây cáp DC, kẹp pin, Bách Z và phụ kiện đầy đủ', 1, 'market_price', 6),
(85, 2, NULL, 'Tấm Pin Jinko 590W', '18 tấm pin Jinko Solar 590W Tiger Neo', 1, 'market_price', 1),
(86, 2, NULL, 'Inverter LuxPower 6kW', 'Bộ inverter hybrid 8kW GEN-LB-EU', 1, 'market_price', 2),
(87, 2, NULL, 'Pin Lưu Trữ BYD', '2 pin lưu trữ BYD 8.8kW (173Ah)', 1, 'market_price', 3),
(88, 2, NULL, 'Tủ Điện 1P-12kW', 'Tủ điện hybrid 1 pha 12kW đầy đủ thiết bị', 1, 'market_price', 4),
(89, 2, NULL, 'Dongles LAN', 'Modul kết nối internet LAN', 1, 'market_price', 5),
(90, 2, NULL, 'Phụ Kiện', 'Dây cáp DC, kẹp pin, Bách Z và phụ kiện đầy đủ', 1, 'market_price', 6),
(96, 1, 1, 'Tấm Pin Jinko 590W', '10 tấm pin Jinko Solar 590W Tiger Neo', 1, 'category_price', 1),
(97, 1, 3, 'Inverter LuxPower 5kW', 'Bộ inverter hybrid ECO 5kW, hỗ trợ backup', 1, 'category_price', 2),
(98, 1, 26, 'Pin Lưu Trữ BYD', '1 pin lưu trữ BYD 8.8kW (173Ah)', 1, 'category_price', 3),
(99, 1, 12, 'Tủ Điện 1P-6kW', 'Tủ điện hybrid 1 pha 6kW đầy đủ thiết bị', 1, 'category_price', 4),
(100, 1, 37, 'Phụ Kiện', 'Dây cáp DC, kẹp pin, Bách Z và phụ kiện đầy đủ', 1, 'category_price', 5);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phuong`
--

CREATE TABLE `phuong` (
  `id` int(11) NOT NULL,
  `ten_phuong` varchar(255) NOT NULL,
  `id_tinh` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Đang đổ dữ liệu cho bảng `phuong`
--

INSERT INTO `phuong` (`id`, `ten_phuong`, `id_tinh`) VALUES
(1, 'Phường Hoàn Kiếm', 1),
(2, 'Phường Cửa Nam', 1),
(3, 'Phường Ba Đình', 1),
(4, 'Phường Ngọc Hà', 1),
(5, 'Phường Giảng Võ', 1),
(6, 'Phường Hai Bà Trưng', 1),
(7, 'Phường Vĩnh Tuy', 1),
(8, 'Phường Bạch Mai', 1),
(9, 'Phường Đống Đa', 1),
(10, 'Phường Kim Liên', 1),
(11, 'Phường Láng Thượng', 1),
(12, 'Phường Ô Chợ Dừa', 1),
(13, 'Phường Thịnh Quang', 1),
(14, 'Phường Trung Liệt', 1),
(15, 'Phường Cát Linh', 1),
(16, 'Phường Văn Miếu', 1),
(17, 'Phường Quốc Tử Giám', 1),
(18, 'Phường Láng Hạ', 1),
(19, 'Phường Khâm Thiên', 1),
(20, 'Phường Thổ Quan', 1),
(21, 'Phường Nam Đồng', 1),
(22, 'Phường Trung Phụng', 1),
(23, 'Phường Quang Trung', 1),
(24, 'Phường Tràng Tiền', 1),
(25, 'Phường Cửa Đông', 1),
(26, 'Phường Lý Thái Tổ', 1),
(27, 'Phường Hàng Bạc', 1),
(28, 'Phường Hàng Buồm', 1),
(29, 'Phường Hàng Đào', 1),
(30, 'Phường Hàng Giấy', 1),
(31, 'Phường Hàng Mã', 1),
(32, 'Phường Hàng Ngang', 1),
(33, 'Phường Hàng Rồng', 1),
(34, 'Phường Hàng Trống', 1),
(35, 'Phường Chương Dương Độ', 1),
(36, 'Phường Đồng Xuân', 1),
(37, 'Phường Hàng Bồ', 1),
(38, 'Phường Hàng Bông', 1),
(39, 'Phường Hàng Gai', 1),
(40, 'Phường Lý Thường Kiệt', 1),
(41, 'Phường Phan Chu Trinh', 1),
(42, 'Phường Phúc Tân', 1),
(43, 'Phường Trần Hưng Đạo', 1),
(44, 'Phường Tràng Thi', 1),
(45, 'Phường An Hải Bắc', 45),
(46, 'Phường An Hải Đông', 45),
(47, 'Phường An Hải Tây', 45),
(48, 'Phường An Hải Nam', 45),
(49, 'Phường An Hải Trung', 45),
(50, 'Phường Mân Thái', 45),
(51, 'Phường Nại Hiên Đông', 45),
(52, 'Phường Phước Mỹ', 45),
(53, 'Phường Thọ Quang', 45),
(54, 'Phường An Khê', 45),
(55, 'Phường Hải Châu I', 45),
(56, 'Phường Hải Châu II', 45),
(57, 'Phường Phước Ninh', 45),
(58, 'Phường Hòa Thuận Tây', 45),
(59, 'Phường Hòa Thuận Đông', 45),
(60, 'Phường Nam Dương', 45),
(61, 'Phường Bình Hiên', 45),
(62, 'Phường Bình Thuận', 45),
(63, 'Phường Hòa Cường Bắc', 45),
(64, 'Phường Hòa Cường Nam', 45),
(65, 'Phường Thạch Thang', 45),
(66, 'Phường Hải Châu', 45),
(67, 'Phường Thanh Bình', 45),
(68, 'Phường Thuận Phước', 45),
(69, 'Phường Hòa Minh', 45),
(70, 'Phường Hòa Quý', 45),
(71, 'Phường Hòa Thọ Đông', 45),
(72, 'Phường Hòa Thọ Tây', 45),
(73, 'Phường Hòa Phát', 45),
(74, 'Phường Hòa An', 45),
(75, 'Phường Hòa Phước', 45),
(76, 'Phường Hòa Thọ', 45),
(77, 'Phường Hòa Xuân', 45),
(78, 'Phường Hòa Khánh Bắc', 45),
(79, 'Phường Hòa Khánh Nam', 45),
(80, 'Phường Hòa Khánh', 45),
(81, 'Phường Bến Nghé', 32),
(82, 'Phường Bến Thành', 32),
(83, 'Phường Cầu Kho', 32),
(84, 'Phường Cầu Ông Lãnh', 32),
(85, 'Phường Cô Giang', 32),
(86, 'Phường Đa Kao', 32),
(87, 'Phường Nguyễn Cư Trinh', 32),
(88, 'Phường Nguyễn Thái Bình', 32),
(89, 'Phường Phạm Ngũ Lão', 32),
(90, 'Phường Tân Định', 32);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL COMMENT 'Tiêu đề/Tên sản phẩm',
  `market_price` decimal(15,2) NOT NULL COMMENT 'Giá thị trường',
  `category_price` decimal(15,2) DEFAULT NULL COMMENT 'Giá theo danh mục',
  `technical_description` text DEFAULT NULL COMMENT 'Mô tả kỹ thuật (gộp mô tả ngắn và thông số kỹ thuật)',
  `image_url` varchar(500) DEFAULT NULL COMMENT 'Đường dẫn ảnh sản phẩm',
  `panel_power_watt` int(11) DEFAULT NULL COMMENT 'Công suất tấm pin (W/tấm)',
  `inverter_power_watt` int(11) DEFAULT NULL COMMENT 'Công suất inverter (W)',
  `battery_capacity_kwh` decimal(10,2) DEFAULT NULL COMMENT 'Dung lượng 1 bộ pin lưu trữ (kWh)',
  `cabinet_power_kw` decimal(10,2) DEFAULT NULL COMMENT 'Công suất tủ điện (kW)',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Trạng thái hiển thị',
  `display_order` int(11) DEFAULT 0 COMMENT 'Thứ tự hiển thị sản phẩm',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `category_id`, `title`, `market_price`, `category_price`, `technical_description`, `image_url`, `panel_power_watt`, `inverter_power_watt`, `battery_capacity_kwh`, `cabinet_power_kw`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'Tấm Pin Jinko Solar 590W Tiger Neo', 2200000.00, 1850000.00, 'Thương hiệu: Jinko Solar\nModel: Tiger Neo 590W\n\nThông số kỹ thuật:\n- Công suất: 590W\n- Công nghệ: N-Type Tiger Neo\n- Hiệu suất: 22.3%\n- Kích thước: 2278×1134×30mm\n- Diện tích: 2,583m²/tấm pin\n- Bảo hành: 15 năm sản phẩm, 30 năm công suất', '/assets/img/products/product_1761917665_6904bae1c9306.jpg', NULL, NULL, NULL, NULL, 1, 2, '2025-10-30 17:55:48', '2025-11-27 12:19:43'),
(2, 1, 'Tấm Pin Jinko Solar 630W Tiger Neo', 2350000.00, 1950000.00, 'Thương hiệu: Jinko Solar\nModel: Tiger Neo 630W\n\nThông số kỹ thuật:\n- Công suất: 630W\n- Công nghệ: N-Type Tiger Neo\n- Hiệu suất: 22.5%\n- Diện tích: 2,702m²/tấm pin\n- Bảo hành: 15 năm sản phẩm, 30 năm công suất', '/assets/img/products/product_1762136900_6908134464d5d.jpg', NULL, NULL, NULL, NULL, 1, 5, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(3, 1, 'ECO Hybrid 5kW (Bản mới 2025)', 16500000.00, 13850000.00, 'Thương hiệu: LuxPower\nModel: SNA5000WPV\n\nThông số kỹ thuật:\n- On-grid/Back-up: 5kW\n- Điện áp: 1 pha\n- Hỗ trợ pin lithium và ắc quy\n- Bảo hành: 5 năm', '/assets/img/products/luxpower-6kw-gen.png', NULL, NULL, NULL, NULL, 1, 6, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(4, 1, 'ECO Hybrid 6kW', 17500000.00, 14500000.00, 'Thương hiệu: LuxPower\nModel: SNA6000WPV\n\nThông số kỹ thuật:\n- On-grid/Back-up: 6kW\n- Điện áp: 1 pha\n- Hỗ trợ pin lithium và ắc quy\n- Bảo hành: 5 năm', '/assets/img/products/luxpower-6kw-gen.png', NULL, NULL, NULL, NULL, 1, 7, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(5, 1, 'ECO Hybrid 12kW', 35500000.00, 31250000.00, 'Thương hiệu: LuxPower\nModel: SNA 12K\n\nThông số kỹ thuật:\n- On-grid/Back-up: 12kW\n- Điện áp: 1 pha\n- Hỗ trợ pin lithium và ắc quy\n- Bảo hành: 5 năm', '/assets/img/products/product_1762136850_69081312978ab.png', NULL, NULL, NULL, NULL, 1, 8, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(6, 1, 'Hybrid GEN-LB-EU 6K', 21350000.00, 21350000.00, 'Thương hiệu: LuxPower\nModel: GEN-LB-EU 6K\n\nThông số kỹ thuật:\n- On-grid/Backup: 6kW\n- Sạc/xả: 6000W 125A/140A\n- Điện áp: 1 pha\n- Bảo hành: 12 tháng', 'assets/img/products/hybrid-gen-lb-eu-6k.png', NULL, NULL, NULL, NULL, 1, 9, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(7, 1, 'Hybrid GEN-LB-EU 8K', 37250000.00, 37250000.00, 'Thương hiệu: LuxPower\nModel: GEN-LB-EU 8K\n\nThông số kỹ thuật:\n- On-grid/Backup: 8kW\n- Sạc/xả: 8000W 167A/167A\n- Điện áp: 1 pha\n- Bảo hành: 12 tháng', 'assets/img/products/hybrid-gen-lb-eu-8k.png', NULL, NULL, NULL, NULL, 1, 10, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(8, 1, 'Hybrid GEN-LB-EU 10K', 39350000.00, 39350000.00, 'Thương hiệu: LuxPower\nModel: GEN-LB-EU 10K\n\nThông số kỹ thuật:\n- On-grid/Backup: 10kW\n- Sạc/xả: 10000W 210A/210A\n- Điện áp: 1 pha\n- Bảo hành: 12 tháng', 'assets/img/products/hybrid-gen-lb-eu-10k.png', NULL, NULL, NULL, NULL, 1, 11, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(9, 1, 'Hybrid LXP-12K 12kW', 44350000.00, 44350000.00, 'Thương hiệu: LuxPower\nModel: LXP-12K\n\nThông số kỹ thuật:\n- Hòa lưới On-grid: 12kW\n- Chạy độc lập Back-up: 12kW\n- Điện áp: 1 pha\n- Bảo hành: 12 tháng', 'assets/img/products/hybrid-lxp-12k.png', NULL, NULL, NULL, NULL, 1, 12, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(10, 1, 'Cell BYD 173ah LiFePO4', 14500000.00, 14500000.00, 'Thương hiệu: BYD\nModel: LiFePO4 173Ah\n\nThông số kỹ thuật:\n- Điện áp: 51.2V\n- Dung lượng: 173ah (8.8kW)\n- Công nghệ: LiFePO4\n- Bảo hành: 10 năm', 'assets/img/products/cell-byd-173ah-lifepo4.png', NULL, NULL, NULL, NULL, 1, 13, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(11, 1, 'Cell A-Cornex LiFePO4 16 Cell', 25500000.00, 25500000.00, 'Thương hiệu: A-Cornex\nModel: LiFePO4 16C\n\nThông số kỹ thuật:\n- Cấu hình: 16 Cell\n- Điện áp hệ thống: 52V\n- Công nghệ: LiFePO4\n- Bảo hành: 10 năm', 'assets/img/products/cell-a-cornex-lifepo4-16cell.png', NULL, NULL, NULL, NULL, 1, 14, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(12, 1, 'Tủ điện Hybrid 1 pha 6kW', 1850000.00, 1850000.00, 'Thương hiệu: HC Eco\nModel: 1P-6KW\n\nThông số kỹ thuật:\n- Công suất hệ thống: 6 kW\n- Điện áp: 1 pha\n- Bao gồm: MCB, RCCB, SPD, các thiết bị bảo vệ', 'assets/img/products/electrical-cabinet.jpg', NULL, NULL, NULL, NULL, 1, 15, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(13, 1, 'Tủ điện Hybrid 1 pha 8kW', 2100000.00, 2100000.00, 'Thương hiệu: HC Eco\nModel: 1P-8KW\n\nThông số kỹ thuật:\n- Công suất hệ thống: 8 kW\n- Điện áp: 1 pha\n- Bao gồm: MCB, RCCB, SPD, các thiết bị bảo vệ', 'assets/img/products/electrical-cabinet.jpg', NULL, NULL, NULL, NULL, 1, 16, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(14, 1, 'Tủ điện Hybrid 1 pha 10kW', 2350000.00, 2350000.00, 'Thương hiệu: HC Eco\nModel: 1P-10KW\n\nThông số kỹ thuật:\n- Công suất hệ thống: 10 kW\n- Điện áp: 1 pha\n- Bao gồm: MCB, RCCB, SPD, các thiết bị bảo vệ', 'assets/img/products/electrical-cabinet.jpg', NULL, NULL, NULL, NULL, 1, 17, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(15, 1, 'Tủ điện Hybrid 1 pha 12-14kW', 5850000.00, 3850000.00, 'Thương hiệu: HC Eco\nModel: 1P-12KW\n\nThông số kỹ thuật:\n- Công suất hệ thống: 12 kW\n- Điện áp: 1 pha\n- Bao gồm: MCB, RCCB, SPD, các thiết bị bảo vệ', '/assets/img/products/product_1761916997_6904b84553e3a.jpg', NULL, NULL, NULL, NULL, 1, 18, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(16, 1, 'Tủ điện Hybrid 3 pha 12kW', 7850000.00, 4550000.00, 'Thương hiệu: HC Eco\nModel: 3P-12KW\n\nThông số kỹ thuật:\n- Công suất hệ thống: 12 kW\n- Điện áp: 3 pha\n- Bao gồm: MCB, RCCB, SPD, các thiết bị bảo vệ', '/assets/img/products/product_1761917056_6904b880da0e1.jpg', NULL, NULL, NULL, NULL, 1, 19, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(17, 1, 'Tủ điện Hybrid 3 pha 15kW', 8000000.00, 4850000.00, 'Thương hiệu: HC Eco\nModel: 3P-15KW\n\nThông số kỹ thuật:\n- Công suất hệ thống: 15 kW\n- Điện áp: 3 pha\n- Bao gồm: MCB, RCCB, SPD, các thiết bị bảo vệ', '/assets/img/products/product_1761917140_6904b8d409b96.jpg', NULL, NULL, NULL, NULL, 1, 20, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(18, 1, 'Dongles Lan LuxPower', 1200000.00, 1380000.00, 'Thương hiệu: LuxPower\nModel: LAN Dongle\n\nThông số kỹ thuật:\n- Kết nối internet qua dây mạng LAN\n- Tương thích: Tất cả inverter LuxPower', 'assets/img/products/dongles-lan-luxpower.png', NULL, NULL, NULL, NULL, 1, 21, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(19, 1, 'Wifi LuxPower', 1000000.00, 1150000.00, 'Thương hiệu: LuxPower\nModel: WiFi Module\n\nThông số kỹ thuật:\n- Kết nối internet qua WiFi 2.4GHz\n- Tương thích: Tất cả inverter LuxPower', 'assets/img/products/wifi-luxpower.png', NULL, NULL, NULL, NULL, 1, 22, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(20, 1, 'Bách Z Mạ Kẽm', 80000.00, 80000.00, 'Thương hiệu: HC Eco\nModel: Bách Z\n\nThông số kỹ thuật:\n- Chức năng: Mạ kẽm nhũng nóng áp mái tôn\n- Ứng dụng: Cố định khung giá đỡ trên mái tôn\n- Vật liệu: Thép mạ kẽm nhúng nóng\n- Sử dụng: 6 cái/tấm pin', 'assets/img/products/bachz.png', NULL, NULL, NULL, NULL, 1, 23, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(21, 1, 'Kẹp biên, Kẹp giữa tấm Pin', 15000.00, 15000.00, 'Thương hiệu: HC Eco\nModel: Kẹp Pin\n\nThông số kỹ thuật:\n- Chức năng: Cố định tấm pin vào khung giá đỡ\n- Vật liệu: Hợp kim nhôm\n- Sử dụng: 6 bộ/tấm pin', 'assets/img/products/kepbien-tamgiua.png', NULL, NULL, NULL, NULL, 1, 24, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(22, 1, 'Jack MC4 1500VDC', 50000.00, 50000.00, 'Thương hiệu: HC Eco\nModel: MC4 Connector\n\nThông số kỹ thuật:\n- Chức năng: Kết nối dây điện giữa các tấm pin\n- Tiêu chuẩn: IP67\n- Vật liệu: Nhựa chống UV\n- Sử dụng: Số tấm + 3 bộ dự phòng', 'assets/img/products/jackcam.png', NULL, NULL, NULL, NULL, 1, 25, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(23, 1, 'Dây Điện (AC/DC)', 30000.00, 30000.00, 'Thương hiệu: HC Eco\nModel: PV Cable 4mm² / 6mm²\n\nThông số kỹ thuật:\n- Tiết diện: 4mm² hoặc 6mm²\n- Tiêu chuẩn: TUV, UL\n- Chống tia cực tím, chịu nhiệt độ cao\n- Đơn giá: 30,000 VNĐ/mét\n- Dự trù: 100m cho toàn bộ hệ thống', 'assets/img/products/daydien.png', NULL, NULL, NULL, NULL, 1, 26, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(24, 1, 'ECO Hybrid 14kW', 39000000.00, 35000000.00, 'Thương hiệu: LuxPower\nModel: SNA-EU-14K\n\nThông số kỹ thuật:\n- On-grid/Back-up: 14kW\n- Điện áp: 1 pha\n- Hỗ trợ pin lithium và ắc quy\n- Bảo hành: 5 năm', '/assets/img/products/product_1762136959_6908137f68854.png', NULL, NULL, NULL, NULL, 1, 27, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(25, 1, 'Hybrid GEN-LB-EU 12K', 62000000.00, 71300000.00, 'Thương hiệu: LuxPower\nModel: GEN-LB-EU 12K\n\nThông số kỹ thuật:\n- On-grid/Backup: 12kW\n- Sạc/xả: 12000W 250A/250A\n- Điện áp: 1 pha\n- Bảo hành: 5 năm', 'assets/img/products/hybrid-gen-lb-eu-12k.png', NULL, NULL, NULL, NULL, 1, 28, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(26, 1, 'Pin lưu trữ BYD', 14500000.00, 14500000.00, 'Thương hiệu: BYD\nModel: LiFePO4 173Ah\n\nThông số kỹ thuật:\n- Điện áp: 51.2V\n- Dung lượng: 173ah (8.8kW)\n- Công nghệ: LiFePO4\n- Bảo hành: 10 năm', 'assets/img/products/pin-luu-tru-byd.jpg', NULL, NULL, NULL, NULL, 1, 29, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(27, 1, 'Pin lưu trữ A-Cornex', 25500000.00, 25500000.00, 'Thương hiệu: A-Cornex\nModel: LiFePO4 16C\n\nThông số kỹ thuật:\n- Cấu hình: 16 Cell\n- Điện áp hệ thống: 52V\n- Dung lượng: 16.3kWh (315Ah @ 52V)\n- Công nghệ: LiFePO4\n- Bảo hành: 10 năm', 'assets/img/products/pin-luu-tru-acornex.jpg', NULL, NULL, NULL, NULL, 1, 30, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(28, 1, 'Hybrid TRIP 10k', 52000000.00, 59800000.00, 'Thương hiệu: LuxPower\nModel: TRIP-10K\n\nThông số kỹ thuật:\n- On-grid/Backup: 10kW\n- Điện áp: 1 pha\n- Bảo hành: 5 năm', 'assets/img/products/hybrid-trip-10k.png', NULL, NULL, NULL, NULL, 1, 31, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(29, 1, 'Hybrid TRIP 15k', 65000000.00, 74750000.00, 'Thương hiệu: LuxPower\nModel: TRIP-15K\n\nThông số kỹ thuật:\n- On-grid/Backup: 15kW\n- Điện áp: 3 pha\n- Bảo hành: 5 năm', 'assets/img/products/hybrid-trip-15k.png', NULL, NULL, NULL, NULL, 1, 32, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(30, 1, 'Hybrid TRIP 20k', 75000000.00, 86250000.00, 'Thương hiệu: LuxPower\nModel: TRIP-20K\n\nThông số kỹ thuật:\n- On-grid/Backup: 20kW\n- Điện áp: 3 pha\n- Bảo hành: 5 năm', 'assets/img/products/hybrid-trip-20k.png', NULL, NULL, NULL, NULL, 1, 33, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(31, 1, 'Hybrid TRIP 25k', 85000000.00, 97750000.00, 'Thương hiệu: LuxPower\nModel: TRIP-25K\n\nThông số kỹ thuật:\n- On-grid/Backup: 25kW\n- Điện áp: 3 pha\n- Bảo hành: 5 năm', 'assets/img/products/hybrid-trip-25k.png', NULL, NULL, NULL, NULL, 1, 34, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(32, 1, 'LuxPower TRIP 25kW', 88000000.00, 101200000.00, 'Thương hiệu: LuxPower\nModel: TRIP-25KW\n\nThông số kỹ thuật:\n- On-grid/Backup: 25kW\n- Điện áp: 3 pha\n- Bảo hành: 5 năm', 'assets/img/products/luxpower-trip-25k.png', NULL, NULL, NULL, NULL, 1, 35, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(33, 1, 'Hybrid TRIP2 LB 3P 12k', 48250000.00, 48250000.00, 'Thương hiệu: LuxPower\nModel: TRIP2-LB-3P-12K\n\nThông số kỹ thuật:\n- On-grid/Backup: 12kW\n- Điện áp: 3 pha\n- Bảo hành: 5 năm', 'assets/img/products/hybrid-trip2-lb-3p-12k.png', NULL, NULL, NULL, NULL, 1, 36, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(34, 1, 'Hybrid TRIP2 LB 3P 15k', 51250000.00, 51250000.00, 'Thương hiệu: LuxPower\nModel: TRIP2-LB-3P-15K\n\nThông số kỹ thuật:\n- On-grid/Backup: 15kW\n- Điện áp: 3 pha\n- Bảo hành: 5 năm', 'assets/img/products/hybrid-trip2-lb-3p-15k.png', NULL, NULL, NULL, NULL, 1, 37, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(35, 1, 'CT Ngoài LuxPower SNA 6kW', 1500000.00, 1725000.00, 'Thương hiệu: LuxPower\nModel: CT-External-6K\n\nThông số kỹ thuật:\n- Chức năng: Hạt nhân đo dòng điện\n- Tương thích: Inverter LuxPower 6kW\n- Bảo hành: 1 năm', 'assets/img/products/ct-ngoai-luxpower-sna-6kw.png', NULL, NULL, NULL, NULL, 1, 38, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(36, 1, 'Growatt 110kW MAX', 185000000.00, 212750000.00, 'Thương hiệu: Growatt\nModel: MAX-110KW\n\nThông số kỹ thuật:\n- Công suất: 110kW\n- Điện áp: 3 pha\n- Bảo hành: 5 năm', 'assets/img/products/growatt-110kw-max-real.png', NULL, NULL, NULL, NULL, 1, 39, '2025-10-30 17:55:48', '2025-11-27 14:21:10'),
(37, 1, 'Gói Phụ Kiện 6-8kw', 7200000.00, 7200000.00, 'Các Phụ Kiện lắp đặt gói 6kw đến 8kw', '/assets/img/products/product_1762093315_690769030bd65.jpg', NULL, NULL, NULL, NULL, 0, 40, '2025-11-02 14:22:37', '2025-11-27 14:21:10'),
(38, 1, 'Biến Tần Huvi-Eco 6300W', 18500000.00, 16900000.00, 'HUVI-ECO 6300W (48V) Hybrid  \nCông suất đầu vào PV tối đa: 7000W\n1 MPPT: 27A. Hiệu suất  MPPT: >99,99%\nBảo hành: 2 năm 1 đổi mới 1 sau 2 năm sửa chữa (có phí) và Cho mượn máy trong quá trình sữa chữa\nĐA CHẾ ĐỘ:\n- Chế độ hòa lưới / On-grid Mode.  Chế độ độc lập / Off-grid Mode. Chế độ lai / Hybrid Mode.  Chế độ không pin / Battery\n- Kích thước: 600mm*520mm*300 mm/ 17,8 Kg\n- All In One - Tự động hoàn toàn cắm là chạy. Mang đi lại thuận tiên, có bánh xe. Cơ động không cần phải Đấu nối với Tủ Điện', '/assets/img/products/product_1764243474_69283812c8824.png', NULL, NULL, NULL, NULL, 1, 1, '2025-11-27 11:40:53', '2025-11-27 14:21:45'),
(39, 1, 'PIN GS POWER - Wifi - Bluetooth', 70000000.00, 29500000.00, 'PIN GS POWER - Wifi - Bluetooth\nBảo hiểm cháy nố PVI 16,3kw\nPin mới 100%\nPIN NHẬP KHẨU CO CQ\nChủng loại pin: \nCell-A loại S RJEteck 314ah.( Pin Cell LiFePO4)  \nMạch top 10 Trung JBD 150A 16s có tích hợp wifi bluetooth\nTuổi thọ trên 8.000 chu kỳ sạc xã. Pin mới 100%\nBẢO HÀNH CHÍNH HÃNG 5 Năm\n * Pin 52V và lưu trữ được 16,3kw', '/assets/img/products/product_1764252850_69285cb253c16.png', NULL, NULL, NULL, NULL, 1, 3, '2025-11-27 14:16:00', '2025-11-27 14:18:38'),
(41, 1, 'PIN LƯU TRỮ  32,6kw', 80000000.00, 44000000.00, 'Chủng loại pin: \n*Cell A- Cornex, hoặc Trina Solar, hoăc Lisen ( Pin Cell LiFePO4) Với tuổi thọ trên 6.000 chu kỳ \nsạc xã. Pin mới 100%\nBảo hành 3 năm tại HC Eco\n* Mỗi năm thêm bảo hành + 1,5 triêu/năm\n (Gia Hạn Bảo hành tới 10 năm)\n* Pin 52V và lưu trữ được 32,6kw gồm 32 Cell Pin A- Corne', '/assets/img/products/product_1764253790_6928605e548d2.png', NULL, NULL, NULL, NULL, 1, 41, '2025-11-27 14:32:58', '2025-11-27 14:32:58');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_categories`
--

CREATE TABLE `product_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `logo_url` varchar(500) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Đang đổ dữ liệu cho bảng `product_categories`
--

INSERT INTO `product_categories` (`id`, `name`, `logo_url`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Bảo Duy Solar', '/assets/img/categories/category_1761694964_690154f4117b8.jpg', 1, 1, '2025-10-27 11:31:07', '2025-10-28 23:42:44'),
(2, 'C - Home Building', '/assets/img/categories/category_1761694975_690154ffe28bd.jpg', 2, 1, '2025-10-27 11:31:07', '2025-10-28 23:42:55'),
(3, 'HC - Coffee & Restaurant', '/assets/img/categories/category_1761694990_6901550e031fa.jpg', 3, 1, '2025-10-27 12:38:01', '2025-10-28 23:43:10'),
(4, 'HC - Travel', '/assets/img/categories/category_1761695000_69015518e7924.jpg', 4, 1, '2025-10-27 16:23:55', '2025-10-28 23:43:32'),
(5, 'NEWPEN', '/assets/img/categories/category_1763956858_6923d87a97c31.png', 5, 1, '2025-11-24 04:00:58', '2025-11-24 04:00:58');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL COMMENT 'ID sản phẩm',
  `image_url` varchar(500) NOT NULL COMMENT 'Đường dẫn ảnh',
  `display_order` int(11) DEFAULT 0 COMMENT 'Thứ tự hiển thị',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Lưu nhiều ảnh cho mỗi sản phẩm';

--
-- Đang đổ dữ liệu cho bảng `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_url`, `display_order`, `created_at`) VALUES
(1, 38, '/assets/img/products/product_1764256671_69286b9f30042.jpg', 1, '2025-11-27 15:17:51'),
(2, 38, '/assets/img/products/product_1764256671_69286b9fbb1f0.jpg', 2, '2025-11-27 15:17:51'),
(3, 38, '/assets/img/products/product_1764256671_69286b9fe8786.jpg', 3, '2025-11-27 15:17:51'),
(4, 38, '/assets/img/products/product_1764256672_69286ba01cd48.jpg', 4, '2025-11-27 15:17:52');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `title` varchar(500) NOT NULL COMMENT 'Tiêu đề dự án',
  `description` text DEFAULT NULL COMMENT 'Mô tả dự án',
  `image_url` varchar(500) DEFAULT NULL COMMENT 'URL ảnh dự án chính (backward compatibility)',
  `video_url` varchar(500) DEFAULT NULL COMMENT 'URL video dự án chính (backward compatibility)',
  `media_gallery` text DEFAULT NULL COMMENT 'JSON array chứa nhiều ảnh/video: [{"type":"image","url":"...","order":1},{"type":"video","url":"...","order":2}]',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Trạng thái hiển thị',
  `display_order` int(11) DEFAULT 0 COMMENT 'Thứ tự hiển thị',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Đang đổ dữ liệu cho bảng `projects`
--

INSERT INTO `projects` (`id`, `title`, `description`, `image_url`, `video_url`, `media_gallery`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'Lắp Đặt Hệ Thống 5kW Tại Gia Đình Sài Gòn', 'Hệ thống điện mặt trời 5kW cho gia đình tại Quận 7, TP. Hồ Chí Minh. Sử dụng công nghệ pin cao cấp từ Jinko Solar và inverter Growatt.', '/uploads/project_images/project_image_1763553360_691db050b37eb.jpg', '', '[{\"type\":\"image\",\"url\":\"https://api.quangphuc.iotsinhvien.io.vn/uploads/project_images/project_691db0407c4814.38013487_1763553344.jpg\",\"order\":5},{\"type\":\"image\",\"url\":\"https://api.quangphuc.iotsinhvien.io.vn/uploads/project_images/project_691db043a15021.21592412_1763553347.jpg\",\"order\":6},{\"type\":\"image\",\"url\":\"https://api.quangphuc.iotsinhvien.io.vn/uploads/project_images/project_691db047e1ae80.03903906_1763553351.jpg\",\"order\":7},{\"type\":\"image\",\"url\":\"https://api.quangphuc.iotsinhvien.io.vn/uploads/project_images/project_691db04f027105.83872913_1763553359.jpg\",\"order\":8}]', 1, 1, '2025-10-27 12:00:00', '2025-11-19 11:56:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `promotions`
--

CREATE TABLE `promotions` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL COMMENT 'Tiêu đề khuyến mãi hiển thị trong trang quản trị',
  `image_url` varchar(500) DEFAULT NULL COMMENT 'Ảnh banner khuyến mãi',
  `target_link` varchar(500) DEFAULT NULL COMMENT 'Trang đích khi người dùng click',
  `target_pages` text NOT NULL COMMENT 'JSON array các trang sẽ hiển thị banner (VD: ["index.html","html/pricing.html"])',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Bật/tắt hiển thị',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Quản lý các banner khuyến mãi hiển thị nổi ở giữa trang';

--
-- Đang đổ dữ liệu cho bảng `promotions`
--

INSERT INTO `promotions` (`id`, `title`, `image_url`, `target_link`, `target_pages`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Ưu đãi Năng Lượng Sạch', '/uploads/promotion_images/promotion_1764003796_885dd8f4.png', 'html/pricing.html', '[\"index.html\",\"html/pricing.html\"]', 1, '2025-11-24 16:19:44', '2025-11-24 17:03:18'),
(2, 'Ưu đãi gói xây dựng cực Hot', '/uploads/promotion_images/promotion_1764064856_c1f3d778.png', 'https://c-homebuild.com/', '[\"index.html\"]', 1, '2025-11-25 08:53:36', '2025-11-25 10:01:01');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` int(11) NOT NULL,
  `identifier` varchar(255) NOT NULL COMMENT 'IP address or user identifier',
  `action` varchar(50) NOT NULL COMMENT 'Action being rate limited (login, register, api)',
  `attempts` int(11) DEFAULT 1,
  `first_attempt` timestamp NULL DEFAULT current_timestamp(),
  `last_attempt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `blocked_until` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `rate_limits`
--

INSERT INTO `rate_limits` (`id`, `identifier`, `action`, `attempts`, `first_attempt`, `last_attempt`, `blocked_until`) VALUES
(5, '171.225.184.238', 'register', 1, '2026-01-02 01:19:44', '2026-01-02 01:19:44', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reward_templates`
--

CREATE TABLE `reward_templates` (
  `id` int(11) NOT NULL,
  `reward_name` varchar(255) NOT NULL,
  `reward_type` enum('voucher','cash','gift') NOT NULL,
  `reward_value` decimal(15,2) DEFAULT NULL COMMENT 'Giá trị voucher/tiền mặt',
  `reward_description` text DEFAULT NULL COMMENT 'Mô tả chi tiết quà tặng',
  `reward_quantity` int(11) DEFAULT NULL COMMENT 'Số lượng (dùng cho quà tặng)',
  `reward_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Đang đổ dữ liệu cho bảng `reward_templates`
--

INSERT INTO `reward_templates` (`id`, `reward_name`, `reward_type`, `reward_value`, `reward_description`, `reward_quantity`, `reward_image`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Voucher giảm 500.000đ', 'voucher', 500000.00, 'Voucher giảm giá 500.000đ cho đơn hàng tiếp theo', NULL, NULL, 1, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(2, 'Voucher giảm 1.000.000đ', 'voucher', 1000000.00, 'Voucher giảm giá 1.000.000đ cho đơn hàng tiếp theo', NULL, NULL, 1, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(3, 'Tiền mặt 200.000đ', 'cash', 200000.00, 'Nhận ngay 200.000đ tiền mặt', NULL, NULL, 1, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(4, 'Tiền mặt 500.000đ', 'cash', 500000.00, 'Nhận ngay 500.000đ tiền mặt', NULL, NULL, 1, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(5, 'Chai nước giặt Omo', 'gift', NULL, 'Chai nước giặt Omo 3.8kg', 100, NULL, 1, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(6, 'Bộ dụng cụ gia đình', 'gift', NULL, 'Bộ dụng cụ gia đình 10 món', 50, NULL, 1, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(7, 'Voucher giam 1 trieu', 'voucher', 1000000.00, 'cho 0988919868', NULL, NULL, 1, '2025-10-28 14:55:38', '2025-10-28 14:55:38');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `solar_surveys`
--

CREATE TABLE `solar_surveys` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `region` varchar(50) NOT NULL COMMENT 'mien-bac, mien-trung, mien-nam',
  `phase` tinyint(4) NOT NULL COMMENT '1 or 3',
  `solar_panel_type` int(11) NOT NULL COMMENT '590 or 630',
  `monthly_bill` decimal(15,2) NOT NULL,
  `usage_time` varchar(50) NOT NULL COMMENT 'day, balanced, night',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Lưu thông tin khảo sát nhu cầu lắp đặt điện mặt trời';

--
-- Đang đổ dữ liệu cho bảng `solar_surveys`
--

INSERT INTO `solar_surveys` (`id`, `user_id`, `full_name`, `phone`, `region`, `phase`, `solar_panel_type`, `monthly_bill`, `usage_time`, `created_at`, `updated_at`) VALUES
(1, 2, 'Admin User', '0988919868', 'mien-bac', 3, 630, 2500000.00, 'balanced', '2025-10-28 00:04:38', '2025-10-28 00:04:38'),
(2, 2, 'Admin User', '0988919868', 'mien-trung', 3, 590, 10500000.00, 'night', '2025-10-30 19:41:17', '2025-10-30 19:41:17'),
(3, 2, 'Admin User', '0988919868', 'mien-trung', 1, 590, 2000000.00, 'day', '2025-10-30 21:44:29', '2025-10-30 21:44:29'),
(4, 2, 'Admin User', '0988919868', 'mien-trung', 1, 630, 2500000.00, 'night', '2025-11-01 08:35:13', '2025-11-01 08:35:13'),
(5, 2, 'Admin User', '0988919868', 'mien-trung', 1, 630, 2500000.00, 'night', '2025-11-01 08:56:11', '2025-11-01 08:56:11'),
(6, 5, 'giahuy', '0838347473', 'mien-trung', 1, 590, 4050000.00, 'balanced', '2025-11-02 13:56:32', '2025-11-02 13:56:32'),
(7, 2, 'Admin User', '0988919868', 'mien-trung', 1, 590, 2000000.00, 'day', '2025-11-02 17:04:00', '2025-11-02 17:04:00'),
(8, 2, 'Admin User', '0988919868', 'mien-trung', 1, 590, 2000000.00, 'day', '2025-11-02 18:15:32', '2025-11-02 18:15:32'),
(9, 2, 'Admin User', '0988919868', 'mien-trung', 1, 590, 2500000.00, 'balanced', '2025-11-27 12:44:24', '2025-11-27 12:44:24'),
(10, 2, 'Admin User', '0988919868', 'mien-bac', 1, 590, 2000000.00, 'balanced', '2025-11-27 14:47:25', '2025-11-27 14:47:25'),
(11, 2, 'Admin User', '0988919868', 'mien-trung', 1, 590, 25000000.00, 'day', '2025-11-27 17:00:02', '2025-11-27 17:00:02'),
(12, 2, 'Admin User', '0988919868', 'mien-bac', 1, 590, 25000000.00, 'day', '2025-11-27 17:15:25', '2025-11-27 17:15:25'),
(13, 2, 'Admin User', '0988919868', 'mien-bac', 1, 590, 25000000.00, 'day', '2025-11-27 17:24:30', '2025-11-27 17:24:30'),
(14, 2, 'Admin User', '0988919868', 'mien-bac', 1, 590, 25000000.00, 'day', '2025-11-27 18:00:06', '2025-11-27 18:00:06'),
(15, 2, 'Admin User', '0988919868', 'mien-bac', 1, 590, 25000000.00, 'day', '2025-11-27 18:18:40', '2025-11-27 18:18:40');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `survey_accessory_dependencies`
--

CREATE TABLE `survey_accessory_dependencies` (
  `id` int(11) NOT NULL,
  `accessory_config_id` int(11) NOT NULL COMMENT 'ID cấu hình phụ kiện (từ survey_product_configs)',
  `dependent_product_id` int(11) NOT NULL COMMENT 'ID sản phẩm phụ thuộc (ví dụ: inverter ID, pin ID, tấm pin ID...)',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Mapping phụ kiện với sản phẩm phụ thuộc - Chỉ hiển thị phụ kiện khi sản phẩm phụ thuộc được chọn';

--
-- Đang đổ dữ liệu cho bảng `survey_accessory_dependencies`
--

INSERT INTO `survey_accessory_dependencies` (`id`, `accessory_config_id`, `dependent_product_id`, `created_at`) VALUES
(6, 21, 1, '2025-11-03 02:00:22'),
(7, 21, 2, '2025-11-03 02:00:22'),
(8, 35, 5, '2025-11-03 02:15:47'),
(9, 35, 3, '2025-11-03 02:15:47'),
(10, 35, 4, '2025-11-03 02:15:47'),
(11, 35, 36, '2025-11-03 02:15:47'),
(12, 35, 8, '2025-11-03 02:15:47'),
(13, 35, 28, '2025-11-03 02:15:47'),
(14, 35, 29, '2025-11-03 02:15:47'),
(15, 35, 30, '2025-11-03 02:15:47'),
(16, 35, 33, '2025-11-03 02:15:47'),
(17, 35, 34, '2025-11-03 02:15:47'),
(18, 35, 32, '2025-11-03 02:15:47'),
(23, 36, 3, '2025-11-27 18:13:58'),
(24, 36, 4, '2025-11-27 18:13:58');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `survey_product_configs`
--

CREATE TABLE `survey_product_configs` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL COMMENT 'ID sản phẩm',
  `survey_category` enum('solar_panel','inverter','battery','electrical_cabinet','accessory') NOT NULL COMMENT 'Loại sản phẩm trong khảo sát',
  `phase_type` enum('1_phase','3_phase','both','none') DEFAULT 'none' COMMENT 'Loại pha (chỉ dùng cho inverter)',
  `price_type` enum('market_price','category_price') DEFAULT 'market_price' COMMENT 'Loại giá sử dụng',
  `panel_power_watt` int(11) DEFAULT NULL COMMENT 'Công suất tấm pin (W/tấm) - dùng cho khảo sát',
  `inverter_power_watt` int(11) DEFAULT NULL COMMENT 'Công suất inverter (W) - dùng cho khảo sát',
  `battery_capacity_kwh` decimal(10,2) DEFAULT NULL COMMENT 'Dung lượng 1 bộ pin (kWh) - dùng cho khảo sát',
  `cabinet_power_kw` decimal(10,2) DEFAULT NULL COMMENT 'Công suất tủ điện (kW) - dùng cho khảo sát',
  `accessory_unit` varchar(16) DEFAULT NULL COMMENT 'Đơn vị phụ kiện: bo/cai/met',
  `accessory_base_qty` decimal(10,2) DEFAULT NULL COMMENT 'Số lượng cơ bản theo đơn vị',
  `accessory_dependent_qty` decimal(10,2) DEFAULT NULL COMMENT 'Hệ số nhân theo đối tượng phụ thuộc',
  `accessory_dependent_target` enum('panel','inverter','battery','cabinet','project') DEFAULT NULL COMMENT 'Đối tượng phụ thuộc',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Có hiển thị trong khảo sát',
  `display_order` int(11) DEFAULT 0 COMMENT 'Thứ tự hiển thị',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Cấu hình sản phẩm cho trang khảo sát';

--
-- Đang đổ dữ liệu cho bảng `survey_product_configs`
--

INSERT INTO `survey_product_configs` (`id`, `product_id`, `survey_category`, `phase_type`, `price_type`, `panel_power_watt`, `inverter_power_watt`, `battery_capacity_kwh`, `cabinet_power_kw`, `accessory_unit`, `accessory_base_qty`, `accessory_dependent_qty`, `accessory_dependent_target`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'solar_panel', 'none', 'category_price', 590, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, '2025-10-30 17:58:44', '2025-10-30 17:58:44'),
(2, 2, 'solar_panel', 'none', 'category_price', 630, NULL, NULL, NULL, '', NULL, NULL, NULL, 1, 2, '2025-10-30 17:59:07', '2025-10-30 19:29:55'),
(3, 3, 'inverter', '1_phase', 'category_price', NULL, 5000, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, '2025-10-30 18:00:55', '2025-10-30 18:00:55'),
(4, 4, 'inverter', '1_phase', 'category_price', NULL, 6000, NULL, NULL, '', NULL, NULL, NULL, 1, 2, '2025-10-30 18:01:33', '2025-11-02 14:16:28'),
(5, 5, 'inverter', '1_phase', 'market_price', NULL, 12000, NULL, NULL, NULL, NULL, NULL, NULL, 1, 3, '2025-10-30 18:01:47', '2025-10-30 18:01:47'),
(6, 9, 'inverter', '1_phase', 'category_price', NULL, 12000, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2025-10-30 18:03:57', '2025-10-30 18:04:02'),
(9, 17, 'electrical_cabinet', '3_phase', 'category_price', NULL, NULL, NULL, 15.00, '', NULL, NULL, NULL, 0, 0, '2025-10-30 18:05:38', '2025-10-31 13:22:46'),
(10, 16, 'electrical_cabinet', '3_phase', 'category_price', NULL, NULL, NULL, 12.00, NULL, NULL, NULL, NULL, 1, 0, '2025-10-30 18:05:50', '2025-10-30 18:05:50'),
(11, 12, 'electrical_cabinet', '1_phase', 'category_price', NULL, NULL, NULL, 6.00, NULL, NULL, NULL, NULL, 1, 0, '2025-10-30 18:06:08', '2025-10-30 18:06:08'),
(12, 13, 'electrical_cabinet', '1_phase', 'category_price', NULL, NULL, NULL, 8.00, NULL, NULL, NULL, NULL, 1, 0, '2025-10-30 18:06:32', '2025-10-30 18:06:32'),
(13, 14, 'electrical_cabinet', '1_phase', 'category_price', NULL, NULL, NULL, 10.00, NULL, NULL, NULL, NULL, 1, 0, '2025-10-30 18:06:48', '2025-10-30 18:06:48'),
(14, 15, 'electrical_cabinet', '1_phase', 'category_price', NULL, NULL, NULL, 12.00, NULL, NULL, NULL, NULL, 1, 0, '2025-10-30 18:07:02', '2025-10-30 18:07:02'),
(15, 6, 'inverter', '1_phase', 'category_price', NULL, 6000, NULL, NULL, '', NULL, NULL, NULL, 0, 0, '2025-10-30 18:09:21', '2025-11-02 14:16:37'),
(16, 7, 'inverter', '1_phase', 'category_price', NULL, 8000, NULL, NULL, '', NULL, NULL, NULL, 0, 0, '2025-10-30 18:09:39', '2025-11-02 14:16:49'),
(17, 8, 'inverter', '1_phase', 'category_price', NULL, 10000, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, '2025-10-30 18:09:55', '2025-10-30 18:09:55'),
(21, 21, 'accessory', 'none', 'category_price', NULL, NULL, NULL, NULL, 'bo', 6.00, 1.00, 'panel', 1, 0, '2025-10-30 18:26:19', '2025-11-03 02:00:22'),
(22, 22, 'accessory', 'none', 'category_price', NULL, NULL, NULL, NULL, 'cai', 1.00, 1.00, 'panel', 0, 0, '2025-10-30 18:27:23', '2025-11-02 14:32:09'),
(23, 20, 'accessory', 'none', 'category_price', NULL, NULL, NULL, NULL, 'cai', 6.00, 1.00, 'panel', 0, 0, '2025-10-30 18:45:01', '2025-11-02 14:31:58'),
(24, 11, 'battery', 'none', 'category_price', NULL, NULL, 16.30, NULL, '', NULL, NULL, NULL, 1, 0, '2025-10-30 19:15:31', '2025-10-30 19:22:17'),
(25, 10, 'battery', 'none', 'category_price', NULL, NULL, 8.80, NULL, '', NULL, NULL, NULL, 1, 0, '2025-10-30 19:15:57', '2025-10-30 19:22:21'),
(26, 23, 'accessory', 'none', 'category_price', NULL, NULL, NULL, NULL, 'met', 100.00, 0.00, 'project', 1, 0, '2025-10-30 19:23:06', '2025-10-30 19:23:11'),
(27, 36, 'inverter', '3_phase', 'category_price', NULL, 110000, NULL, NULL, '', NULL, NULL, NULL, 1, 0, '2025-10-30 19:24:50', '2025-10-30 19:24:53'),
(28, 33, 'inverter', '3_phase', 'category_price', NULL, 12000, NULL, NULL, '', NULL, NULL, NULL, 1, 5, '2025-10-30 19:26:25', '2025-10-30 19:26:25'),
(29, 34, 'inverter', '3_phase', 'category_price', NULL, 15000, NULL, NULL, '', NULL, NULL, NULL, 1, 0, '2025-10-30 19:26:39', '2025-10-30 19:26:39'),
(30, 28, 'inverter', '3_phase', 'market_price', NULL, 10000, NULL, NULL, '', NULL, NULL, NULL, 1, 10, '2025-10-30 19:27:00', '2025-10-30 19:27:00'),
(31, 29, 'inverter', '3_phase', 'category_price', NULL, 15000, NULL, NULL, '', NULL, NULL, NULL, 1, 0, '2025-10-30 19:27:17', '2025-10-30 19:27:17'),
(32, 30, 'inverter', '3_phase', 'category_price', NULL, 20000, NULL, NULL, '', NULL, NULL, NULL, 1, 0, '2025-10-30 19:27:31', '2025-10-30 19:27:31'),
(33, 32, 'inverter', '3_phase', 'category_price', NULL, 25000, NULL, NULL, '', NULL, NULL, NULL, 1, 0, '2025-10-30 19:28:00', '2025-10-30 19:28:00'),
(34, 18, 'accessory', 'none', 'category_price', NULL, NULL, NULL, NULL, 'cai', 1.00, 1.00, 'inverter', 0, 0, '2025-10-30 19:33:35', '2025-11-02 14:28:58'),
(35, 19, 'accessory', 'none', 'category_price', NULL, NULL, NULL, NULL, 'cai', 1.00, 1.00, 'inverter', 1, 0, '2025-10-30 19:33:51', '2025-11-03 02:15:47'),
(36, 37, 'accessory', 'none', 'category_price', NULL, NULL, NULL, NULL, 'bo', 1.00, 0.00, 'inverter', 1, 0, '2025-11-02 14:23:51', '2025-11-27 18:13:58'),
(37, 39, 'battery', 'none', 'category_price', NULL, NULL, 16.30, NULL, '', NULL, NULL, NULL, 1, 2, '2025-11-27 14:38:15', '2025-11-27 14:38:15'),
(38, 41, 'battery', 'none', 'category_price', NULL, NULL, 32.60, NULL, '', NULL, NULL, NULL, 1, 3, '2025-11-27 14:38:32', '2025-11-27 14:38:32'),
(39, 38, 'inverter', '1_phase', 'category_price', NULL, 6300, NULL, NULL, '', NULL, NULL, NULL, 1, 4, '2025-11-27 14:38:59', '2025-11-27 14:38:59');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `survey_regions`
--

CREATE TABLE `survey_regions` (
  `id` int(11) NOT NULL,
  `region_code` varchar(50) NOT NULL COMMENT 'Mã khu vực (mien-bac, mien-trung, mien-nam)',
  `region_name` varchar(100) NOT NULL COMMENT 'Tên khu vực (Miền Bắc, Miền Trung, Miền Nam)',
  `display_content` varchar(255) NOT NULL COMMENT 'Nội dung hiển thị cho user (VD: Miền Bắc (4,4 giờ nắng/ngày))',
  `sun_hours` decimal(3,1) NOT NULL COMMENT 'Số giờ nắng trung bình/ngày để tính toán',
  `display_order` int(11) DEFAULT 0 COMMENT 'Thứ tự hiển thị',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Đang sử dụng',
  `notes` text DEFAULT NULL COMMENT 'Ghi chú',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Quản lý khu vực khảo sát điện mặt trời';

--
-- Đang đổ dữ liệu cho bảng `survey_regions`
--

INSERT INTO `survey_regions` (`id`, `region_code`, `region_name`, `display_content`, `sun_hours`, `display_order`, `is_active`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'mien-bac', 'Miền Bắc', 'Miền Bắc (3.5 đến 4.2 giờ nắng/ngày)', 3.7, 1, 1, '', '2025-10-30 17:55:49', '2025-11-02 15:57:33'),
(2, 'mien-trung', 'Miền Trung', 'Miền Trung (4.5 đến 5.0 giờ nắng/ngày)', 4.0, 2, 1, '', '2025-10-30 17:55:49', '2025-11-02 14:12:38'),
(3, 'mien-nam', 'Miền Nam', 'Miền Nam (5.0 đến 5.5 giờ nắng/ngày)', 4.4, 3, 1, '', '2025-10-30 17:55:49', '2025-11-02 15:57:33');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `survey_results`
--

CREATE TABLE `survey_results` (
  `id` int(11) NOT NULL,
  `survey_id` int(11) NOT NULL,
  `monthly_kwh` decimal(10,2) NOT NULL COMMENT 'Điện tiêu thụ hàng tháng (kWh)',
  `sun_hours` decimal(3,1) NOT NULL COMMENT 'Giờ nắng trung bình/ngày',
  `region_name` varchar(100) NOT NULL COMMENT 'Tên khu vực (Miền Bắc/Trung/Nam)',
  `panel_id` int(11) NOT NULL COMMENT 'ID loại tấm pin (590 hoặc 630)',
  `panel_name` varchar(255) NOT NULL COMMENT 'Tên tấm pin',
  `panel_power` decimal(5,3) NOT NULL COMMENT 'Công suất tấm pin (kW)',
  `panel_price` decimal(15,2) NOT NULL COMMENT 'Đơn giá 1 tấm pin',
  `panels_needed` int(11) NOT NULL COMMENT 'Số tấm pin cần thiết',
  `panel_cost` decimal(15,2) NOT NULL COMMENT 'Tổng tiền tấm pin',
  `energy_per_panel_per_day` decimal(10,3) NOT NULL COMMENT 'Năng lượng/tấm/ngày (kWh)',
  `total_capacity` decimal(10,2) NOT NULL COMMENT 'Tổng công suất hệ thống (kW)',
  `inverter_id` int(11) NOT NULL COMMENT 'ID biến tần được chọn',
  `inverter_name` varchar(255) NOT NULL,
  `inverter_capacity` decimal(10,2) NOT NULL COMMENT 'Công suất biến tần (kW)',
  `inverter_price` decimal(15,2) NOT NULL,
  `inverter_quantity` int(11) NOT NULL DEFAULT 1 COMMENT 'Số lượng biến tần cần thiết',
  `inverter_total_price` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Tổng giá biến tần (số lượng × đơn giá)',
  `cabinet_id` int(11) NOT NULL COMMENT 'ID tủ điện được chọn',
  `cabinet_name` varchar(255) NOT NULL,
  `cabinet_capacity` decimal(10,2) NOT NULL COMMENT 'Công suất tủ điện (kW)',
  `cabinet_price` decimal(15,2) NOT NULL,
  `battery_needed` decimal(10,2) NOT NULL COMMENT 'Dung lượng pin cần (kWh)',
  `battery_type` varchar(50) NOT NULL COMMENT '8cell or 16cell',
  `battery_id` int(11) NOT NULL COMMENT 'ID loại pin',
  `battery_name` varchar(255) NOT NULL COMMENT 'Tên pin lưu trữ',
  `battery_capacity` decimal(10,2) NOT NULL COMMENT 'Dung lượng/cell (kWh)',
  `battery_quantity` int(11) NOT NULL COMMENT 'Số lượng cell',
  `battery_unit_price` decimal(15,2) NOT NULL COMMENT 'Đơn giá/cell',
  `battery_cost` decimal(15,2) NOT NULL COMMENT 'Tổng tiền pin',
  `bach_z_qty` int(11) NOT NULL COMMENT 'Số lượng Bach Z',
  `bach_z_price` decimal(10,2) NOT NULL COMMENT 'Đơn giá Bach Z',
  `bach_z_cost` decimal(15,2) NOT NULL COMMENT 'Thành tiền Bach Z',
  `clip_qty` int(11) NOT NULL COMMENT 'Số lượng kẹp biên',
  `clip_price` decimal(10,2) NOT NULL COMMENT 'Đơn giá kẹp',
  `clip_cost` decimal(15,2) NOT NULL COMMENT 'Thành tiền kẹp',
  `jack_mc4_qty` int(11) NOT NULL COMMENT 'Số lượng Jack MC4',
  `jack_mc4_price` decimal(10,2) NOT NULL COMMENT 'Đơn giá Jack MC4',
  `jack_mc4_cost` decimal(15,2) NOT NULL COMMENT 'Thành tiền Jack MC4',
  `dc_cable_length` int(11) NOT NULL COMMENT 'Chiều dài dây DC (m)',
  `dc_cable_price` decimal(10,2) NOT NULL COMMENT 'Đơn giá dây DC/m',
  `dc_cable_cost` decimal(15,2) NOT NULL COMMENT 'Thành tiền dây DC',
  `accessories_cost` decimal(15,2) NOT NULL COMMENT 'Tổng phụ kiện (Bach Z + Clip + Jack + DC)',
  `labor_cost` decimal(15,2) NOT NULL COMMENT 'Công thợ lắp đặt',
  `transport_cost` decimal(15,2) DEFAULT 0.00 COMMENT 'Chi phí vận chuyển thiết bị',
  `total_cost_without_battery` decimal(15,2) NOT NULL COMMENT 'Tổng không tính pin',
  `total_cost` decimal(15,2) NOT NULL COMMENT 'Tổng chi phí dự án',
  `bill_breakdown` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Chi tiết bậc thang điện EVN' CHECK (json_valid(`bill_breakdown`)),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Lưu kết quả tính toán chi tiết từ khảo sát';

--
-- Đang đổ dữ liệu cho bảng `survey_results`
--

INSERT INTO `survey_results` (`id`, `survey_id`, `monthly_kwh`, `sun_hours`, `region_name`, `panel_id`, `panel_name`, `panel_power`, `panel_price`, `panels_needed`, `panel_cost`, `energy_per_panel_per_day`, `total_capacity`, `inverter_id`, `inverter_name`, `inverter_capacity`, `inverter_price`, `inverter_quantity`, `inverter_total_price`, `cabinet_id`, `cabinet_name`, `cabinet_capacity`, `cabinet_price`, `battery_needed`, `battery_type`, `battery_id`, `battery_name`, `battery_capacity`, `battery_quantity`, `battery_unit_price`, `battery_cost`, `bach_z_qty`, `bach_z_price`, `bach_z_cost`, `clip_qty`, `clip_price`, `clip_cost`, `jack_mc4_qty`, `jack_mc4_price`, `jack_mc4_cost`, `dc_cable_length`, `dc_cable_price`, `dc_cable_cost`, `accessories_cost`, `labor_cost`, `transport_cost`, `total_cost_without_battery`, `total_cost`, `bill_breakdown`, `created_at`) VALUES
(1, 1, 1000.00, 4.5, 'Miền Bắc', 2, 'Pin mặt trời 630W', 0.630, 2800000.00, 12, 33600000.00, 2.835, 7.56, 1, 'Inverter Luxpower', 6.00, 15000000.00, 1, 15000000.00, 1, 'Tủ điện', 6.00, 2000000.00, 0.00, '8cell', 1, 'Pin lưu trữ 8 cell', 8.30, 0, 15000000.00, 0.00, 12, 50000.00, 600000.00, 48, 10000.00, 480000.00, 24, 15000.00, 360000.00, 120, 20000.00, 2400000.00, 6000000.00, 3600000.00, 0.00, 60200000.00, 60200000.00, '[]', '2025-10-28 00:04:38'),
(2, 2, 2902.60, 6.3, 'Miền Trung', 1, 'Tấm Pin Jinko Solar 590W Tiger Neo (590W | 22.3%)', 0.590, 1800000.00, 28, 50400000.00, 3.717, 16.00, 36, 'Growatt 110kW MAX', 110.00, 212750000.00, 1, 212750000.00, 16, 'Tủ điện', 16.00, 0.00, 62.45, '16cell', 11, 'Cell A-Cornex LiFePO4 16 Cell', 16.30, 1, 25500000.00, 25500000.00, 168, 80000.00, 13440000.00, 0, 0.00, 0.00, 28, 50000.00, 1400000.00, 100, 30000.00, 3000000.00, 29970000.00, 9600000.00, 0.00, 369620000.00, 395120000.00, '[]', '2025-10-30 19:41:17'),
(3, 3, 628.00, 4.7, 'Miền Trung', 1, 'Tấm Pin Jinko Solar 590W Tiger Neo', 0.590, 1850000.00, 8, 14800000.00, 2.773, 4.45, 3, 'ECO Hybrid 5kW (Bản mới 2025)', 5.00, 14500000.00, 1, 14500000.00, 12, 'Tủ điện Hybrid 1 pha 6kW', 6.00, 1850000.00, 7.39, '16cell', 10, 'Cell BYD 173ah LiFePO4', 16.30, 1, 14500000.00, 14500000.00, 48, 80000.00, 3840000.00, 0, 0.00, 0.00, 8, 50000.00, 400000.00, 100, 30000.00, 3000000.00, 15770000.00, 6000000.00, 0.00, 46520000.00, 61420000.00, '[]', '2025-10-30 21:44:29'),
(4, 4, 761.80, 4.7, 'Miền Trung', 2, 'Tấm Pin Jinko Solar 630W Tiger Neo', 0.630, 1950000.00, 9, 17550000.00, 2.961, 5.40, 3, 'ECO Hybrid 5kW (Bản mới 2025)', 5.00, 14500000.00, 1, 14500000.00, 12, 'Tủ điện Hybrid 1 pha 6kW', 6.00, 1850000.00, 15.99, '8cell', 10, 'Cell BYD 173ah LiFePO4', 8.80, 2, 14500000.00, 29000000.00, 54, 80000.00, 4320000.00, 0, 0.00, 0.00, 9, 50000.00, 450000.00, 100, 30000.00, 3000000.00, 16300000.00, 6000000.00, 0.00, 64700000.00, 79200000.00, '[]', '2025-11-01 08:35:13'),
(5, 5, 761.80, 4.7, 'Miền Trung', 2, 'Tấm Pin Jinko Solar 630W Tiger Neo (630W | 22.5%)', 0.630, 1950000.00, 9, 17550000.00, 2.961, 5.40, 3, 'ECO Hybrid 5kW (Bản mới 2025)', 5.00, 14500000.00, 1, 14500000.00, 12, 'Tủ điện Hybrid 1 pha 6kW', 6.00, 1850000.00, 15.99, '8cell', 10, 'Cell BYD 173ah LiFePO4', 8.80, 2, 14500000.00, 29000000.00, 54, 80000.00, 4320000.00, 0, 0.00, 0.00, 9, 50000.00, 450000.00, 100, 30000.00, 3000000.00, 16300000.00, 6000000.00, 0.00, 50200000.00, 79200000.00, '[]', '2025-11-01 08:56:11'),
(6, 6, 1176.60, 4.7, 'Miền Trung', 1, 'Tấm Pin Jinko Solar 590W Tiger Neo (590W | 22.3%)', 0.590, 1850000.00, 15, 27750000.00, 2.773, 8.34, 6, 'Hybrid GEN-LB-EU 6K', 6.00, 21350000.00, 1, 21350000.00, 12, 'Tủ điện Hybrid 1 pha 6kW', 6.00, 1850000.00, 20.80, '16cell', 11, 'Cell A-Cornex LiFePO4 16 Cell', 16.30, 2, 25500000.00, 51000000.00, 90, 80000.00, 7200000.00, 0, 0.00, 0.00, 15, 50000.00, 750000.00, 100, 30000.00, 3000000.00, 19480000.00, 6000000.00, 0.00, 70430000.00, 121430000.00, '[]', '2025-11-02 13:56:32'),
(7, 7, 628.00, 4.0, 'Miền Trung', 1, 'Tấm Pin Jinko Solar 590W Tiger Neo', 0.590, 1850000.00, 9, 16650000.00, 2.360, 5.23, 3, 'ECO Hybrid 5kW (Bản mới 2025)', 5.00, 14500000.00, 1, 14500000.00, 12, 'Tủ điện Hybrid 1 pha 6kW', 6.00, 1850000.00, 7.08, '16cell', 11, 'Cell A-Cornex LiFePO4 16 Cell', 16.30, 1, 25500000.00, 25500000.00, 0, 0.00, 0.00, 0, 0.00, 0.00, 0, 0.00, 0.00, 100, 30000.00, 3000000.00, 16200000.00, 6000000.00, 0.00, 49200000.00, 76660000.00, '[]', '2025-11-02 17:04:00'),
(8, 8, 628.00, 4.0, 'Miền Trung', 1, 'Tấm Pin Jinko Solar 590W Tiger Neo', 0.590, 1850000.00, 9, 16650000.00, 2.360, 5.23, 3, 'ECO Hybrid 5kW (Bản mới 2025)', 5.00, 14500000.00, 1, 14500000.00, 12, 'Tủ điện Hybrid 1 pha 6kW', 6.00, 1850000.00, 7.08, '16cell', 11, 'Cell A-Cornex LiFePO4 16 Cell', 16.30, 1, 25500000.00, 25500000.00, 0, 0.00, 0.00, 0, 0.00, 0.00, 0, 0.00, 0.00, 100, 30000.00, 3000000.00, 16200000.00, 6000000.00, 10000000.00, 49200000.00, 86660000.00, '[]', '2025-11-02 18:15:32'),
(9, 9, 761.80, 4.0, 'Miền Trung', 1, 'Tấm Pin Jinko Solar 590W Tiger Neo', 0.590, 1850000.00, 11, 20350000.00, 2.360, 6.35, 3, 'ECO Hybrid 5kW (Bản mới 2025)', 5.00, 13850000.00, 1, 13850000.00, 12, 'Tủ điện Hybrid 1 pha 6kW', 6.00, 1850000.00, 12.98, '16cell', 11, 'Cell A-Cornex LiFePO4 16 Cell', 16.30, 1, 25500000.00, 25500000.00, 0, 0.00, 0.00, 0, 0.00, 0.00, 0, 0.00, 0.00, 100, 30000.00, 3000000.00, 18340000.00, 6000000.00, 10000000.00, 54390000.00, 89890000.00, '[]', '2025-11-27 12:44:24'),
(10, 10, 628.00, 3.7, 'Miền Bắc', 1, 'Tấm Pin Jinko Solar 590W Tiger Neo (590W | 22.3%)', 0.590, 1850000.00, 10, 18500000.00, 2.183, 5.66, 3, 'ECO Hybrid 5kW (Bản mới 2025)', 5.00, 13850000.00, 1, 13850000.00, 12, 'Tủ điện Hybrid 1 pha 6kW', 6.00, 1850000.00, 10.92, '16cell', 11, 'Cell A-Cornex LiFePO4 16 Cell', 16.30, 1, 25500000.00, 25500000.00, 0, 0.00, 0.00, 0, 0.00, 0.00, 0, 0.00, 0.00, 100, 30000.00, 3000000.00, 18250000.00, 6000000.00, 0.00, 52450000.00, 77950000.00, '[]', '2025-11-27 14:47:25'),
(11, 11, 6782.70, 4.0, 'Miền Trung', 1, 'Tấm Pin Jinko Solar 590W Tiger Neo (590W | 22.3%)', 0.590, 1850000.00, 96, 177600000.00, 2.360, 56.52, 4, 'ECO Hybrid 6kW', 6.00, 14500000.00, 1, 14500000.00, 12, 'Tủ điện Hybrid 1 pha 6kW', 6.00, 1850000.00, 75.52, '16cell', 41, 'PIN LƯU TRỮ  32,6kw', 32.60, 3, 44000000.00, 132000000.00, 0, 0.00, 0.00, 0, 0.00, 0.00, 0, 0.00, 0.00, 100, 30000.00, 3000000.00, 49990000.00, 30000000.00, 0.00, 330940000.00, 462940000.00, '[]', '2025-11-27 17:00:02'),
(12, 12, 6782.70, 3.7, 'Miền Bắc', 1, 'Tấm Pin Jinko Solar 590W Tiger Neo (590W | 22.3%)', 0.590, 1850000.00, 104, 192400000.00, 2.183, 61.11, 3, 'ECO Hybrid 5kW (Bản mới 2025)', 5.00, 13850000.00, 1, 13850000.00, 12, 'Tủ điện Hybrid 1 pha 6kW', 6.00, 1850000.00, 75.68, '16cell', 41, 'PIN LƯU TRỮ  32,6kw', 32.60, 3, 44000000.00, 132000000.00, 0, 0.00, 0.00, 0, 0.00, 0.00, 0, 0.00, 0.00, 100, 30000.00, 3000000.00, 53110000.00, 32400000.00, 0.00, 372010000.00, 504010000.00, '[]', '2025-11-27 17:15:25'),
(13, 13, 6782.70, 3.7, 'Miền Bắc', 1, 'Tấm Pin Jinko Solar 590W Tiger Neo (590W | 22.3%)', 0.590, 1850000.00, 104, 192400000.00, 2.183, 61.11, 8, 'Hybrid GEN-LB-EU 10K', 10.00, 39350000.00, 5, 196750000.00, 14, 'Tủ điện Hybrid 1 pha 10kW', 10.00, 2350000.00, 75.68, '16cell', 11, 'Cell A-Cornex LiFePO4 16 Cell', 16.30, 5, 25500000.00, 127500000.00, 0, 0.00, 0.00, 0, 0.00, 0.00, 0, 0.00, 0.00, 100, 30000.00, 3000000.00, 45910000.00, 32400000.00, 0.00, 437410000.00, 564910000.00, '[]', '2025-11-27 17:24:30'),
(14, 14, 6782.70, 3.7, 'Miền Bắc', 1, 'Tấm Pin Jinko Solar 590W Tiger Neo (590W | 22.3%)', 0.590, 1850000.00, 104, 192400000.00, 2.183, 61.11, 8, 'Hybrid GEN-LB-EU 10K', 10.00, 39350000.00, 5, 196750000.00, 14, 'Tủ điện Hybrid 1 pha 10kW', 10.00, 2350000.00, 75.68, '16cell', 11, 'Cell A-Cornex LiFePO4 16 Cell', 16.30, 5, 25500000.00, 127500000.00, 0, 0.00, 0.00, 0, 0.00, 0.00, 0, 0.00, 0.00, 100, 30000.00, 3000000.00, 53110000.00, 32400000.00, 0.00, 444610000.00, 572110000.00, '[]', '2025-11-27 18:00:06'),
(15, 15, 6782.70, 3.7, 'Miền Bắc', 1, 'Tấm Pin Jinko Solar 590W Tiger Neo (590W | 22.3%)', 0.590, 1850000.00, 104, 192400000.00, 2.183, 61.11, 8, 'Hybrid GEN-LB-EU 10K', 10.00, 39350000.00, 5, 196750000.00, 14, 'Tủ điện Hybrid 1 pha 10kW', 10.00, 2350000.00, 75.68, '16cell', 41, 'PIN LƯU TRỮ  32,6kw', 32.60, 3, 44000000.00, 132000000.00, 0, 0.00, 0.00, 0, 0.00, 0.00, 0, 0.00, 0.00, 100, 30000.00, 3000000.00, 45910000.00, 32400000.00, 0.00, 437410000.00, 569410000.00, '[]', '2025-11-27 18:18:40');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tinh`
--

CREATE TABLE `tinh` (
  `id` int(11) NOT NULL,
  `ten_tinh` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tinh`
--

INSERT INTO `tinh` (`id`, `ten_tinh`) VALUES
(1, 'THÀNH PHỐ HÀ NỘI'),
(4, 'THÀNH PHỐ HẢI PHÒNG'),
(32, 'THÀNH PHỐ HỒ CHÍ MINH'),
(45, 'THÀNH PHỐ ĐÀ NẴNG'),
(39, 'TỈNH AN GIANG'),
(31, 'TỈNH BÀ RỊA - VŨNG TÀU'),
(57, 'TỈNH BẮC GIANG'),
(58, 'TỈNH BẮC KẠN'),
(42, 'TỈNH BẠC LIÊU'),
(2, 'TỈNH BẮC NINH'),
(35, 'TỈNH BẾN TRE'),
(29, 'TỈNH BÌNH DƯƠNG'),
(27, 'TỈNH BÌNH PHƯỚC'),
(21, 'TỈNH BÌNH THUẬN'),
(17, 'TỈNH BÌNH ĐỊNH'),
(41, 'TỈNH CÀ MAU'),
(7, 'TỈNH CAO BẰNG'),
(23, 'TỈNH GIA LAI'),
(55, 'TỈNH HÀ NAM'),
(49, 'TỈNH HÀ TĨNH'),
(54, 'TỈNH HẢI DƯƠNG'),
(44, 'TỈNH HẬU GIANG'),
(61, 'TỈNH HÒA BÌNH'),
(5, 'TỈNH HƯNG YÊN'),
(19, 'TỈNH KHÁNH HÒA'),
(40, 'TỈNH KIÊN GIANG'),
(22, 'TỈNH KONTUM'),
(10, 'TỈNH LAI CHÂU'),
(26, 'TỈNH LÂM ĐỒNG'),
(14, 'TỈNH LẠNG SƠN'),
(8, 'TỈNH LÀO CAI'),
(33, 'TỈNH LONG AN'),
(52, 'TỈNH NAM ĐỊNH'),
(50, 'TỈNH NGHỆ AN'),
(6, 'TỈNH NINH BÌNH'),
(20, 'TỈNH NINH THUẬN'),
(60, 'TỈNH PHÚ THỌ'),
(18, 'TỈNH PHÚ YÊN'),
(48, 'TỈNH QUẢNG BÌNH'),
(15, 'TỈNH QUẢNG NAM'),
(16, 'TỈNH QUẢNG NGÃI'),
(3, 'TỈNH QUẢNG NINH'),
(47, 'TỈNH QUẢNG TRỊ'),
(43, 'TỈNH SÓC TRĂNG'),
(11, 'TỈNH SƠN LA'),
(28, 'TỈNH TÂY NINH'),
(53, 'TỈNH THÁI BÌNH'),
(13, 'TỈNH THÁI NGUYÊN'),
(51, 'TỈNH THANH HÓA'),
(46, 'TỈNH THỪA THIÊN HUẾ'),
(34, 'TỈNH TIỀN GIANG'),
(36, 'TỈNH TRÀ VINH'),
(59, 'TỈNH TUYÊN QUANG'),
(37, 'TỈNH VĨNH LONG'),
(56, 'TỈNH VĨNH PHÚC'),
(12, 'TỈNH YÊN BÁI'),
(24, 'TỈNH ĐẮK LẮK'),
(25, 'TỈNH ĐẮK NÔNG'),
(9, 'TỈNH ĐIỆN BIÊN'),
(30, 'TỈNH ĐỒNG NAI'),
(38, 'TỈNH ĐỒNG THÁP');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `full_name`, `username`, `phone`, `password`, `is_admin`, `created_at`, `updated_at`) VALUES
(1, 'Test User', 'testuser', '0123456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(2, 'Admin User', 'admin', '0988919868', '$2y$10$k8S9LHvAOtxAvDFTGmV7n.cyqvIuFbnlZGzZ.DcPzpOihPfnYWbF2', 1, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(3, 'Đỗ Quang Phúc', 'quangphuc', '0375779219', '$2y$10$r6M1D/MPVDVm9bXdlOaA4.NxfWO68iL2loDBpuZTySRWGnFVovhui', 0, '2025-10-27 11:57:05', '2025-10-27 11:57:05'),
(4, 'Nguyễn Minh Hải', 'hainm', '1234567899', '$2y$10$mjReWJD1Izqe1NrwrqqXkeyylvCl/YP68tGYc/pQbY/tj/Ojx/wfy', 1, '2025-10-28 14:22:57', '2025-10-28 14:24:56'),
(5, 'giahuy', 'giahuy', '0838347473', '$2y$10$uvthQoa1sOSob422EEpU5ODY2vqNwiTszRWlc/U.PukMdHAtfMxuq', 0, '2025-11-02 13:51:45', '2025-11-02 13:51:45');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_permissions`
--

CREATE TABLE `user_permissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'ID người dùng',
  `permission_key` varchar(50) NOT NULL COMMENT 'Khóa quyền (categories, products, survey, packages, orders, tickets, rewards, intro-posts, projects, dich-vu, users, home, contacts)',
  `can_view` tinyint(1) DEFAULT 0 COMMENT 'Quyền xem',
  `can_create` tinyint(1) DEFAULT 0 COMMENT 'Quyền tạo mới',
  `can_edit` tinyint(1) DEFAULT 0 COMMENT 'Quyền sửa',
  `can_delete` tinyint(1) DEFAULT 0 COMMENT 'Quyền xóa',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Lưu quyền truy cập các module của user';

--
-- Đang đổ dữ liệu cho bảng `user_permissions`
--

INSERT INTO `user_permissions` (`id`, `user_id`, `permission_key`, `can_view`, `can_create`, `can_edit`, `can_delete`, `created_at`, `updated_at`) VALUES
(2, 3, 'wheel', 1, 1, 1, 1, '2025-11-18 02:03:41', '2025-11-18 02:03:41');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `vouchers`
--

CREATE TABLE `vouchers` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_amount` decimal(15,2) NOT NULL,
  `description` text DEFAULT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `used_by_user_id` int(11) DEFAULT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Đang đổ dữ liệu cho bảng `vouchers`
--

INSERT INTO `vouchers` (`id`, `code`, `discount_amount`, `description`, `is_used`, `used_by_user_id`, `used_at`, `expires_at`, `created_at`) VALUES
(1, 'WELCOME500K', 500000.00, 'Voucher chào mừng khách hàng mới', 0, NULL, NULL, '2025-11-26 11:31:10', '2025-10-27 11:31:10'),
(2, 'NEWYEAR1M', 1000000.00, 'Voucher năm mới giảm 1 triệu', 0, NULL, NULL, '2025-12-26 11:31:10', '2025-10-27 11:31:10'),
(3, 'VC6900104027C86', 1000000.00, 'Voucher giảm 1.000.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-11-27 08:37:20', '2025-10-28 00:37:20'),
(4, 'VC6900D9CE9EC07', 1000000.00, 'Voucher giam 1 trieu - Từ vòng quay may mắn', 0, NULL, NULL, '2025-11-27 22:57:18', '2025-10-28 14:57:18'),
(5, 'VC69076D7B2DCB0', 1000000.00, 'Voucher giam 1 trieu - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-02 22:40:59', '2025-11-02 14:40:59'),
(6, 'VC69076DA8A9542', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-02 22:41:44', '2025-11-02 14:41:44'),
(7, 'VC69079F8962F7D', 1000000.00, 'Voucher giam 1 trieu - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:33', '2025-11-02 18:14:33'),
(8, 'VC69079F89E8FFD', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:33', '2025-11-02 18:14:33'),
(9, 'VC69079F8A45B53', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(10, 'VC69079F8A45ED8', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(11, 'VC69079F8A46667', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(12, 'VC69079F8A46EE8', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(13, 'VC69079F8A4709E', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(14, 'VC69079F8A475AB', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(15, 'VC69079F8A47982', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(16, 'VC69079F8A47B2B', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(17, 'VC69079F8A47CD6', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(18, 'VC69079F8A480E0', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(19, 'VC69079F8A48859', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(20, 'VC69079F8A48A2B', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(21, 'VC69079F8A48D39', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(22, 'VC69079F8A490D7', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(23, 'VC69079F8A4927E', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(24, 'VC69079F8A4A0CB', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(25, 'VC69079F8A4A3CA', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(26, 'VC69079F8A4A6A0', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(27, 'VC69079F8A4A98A', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(28, 'VC69079F8A4AB1D', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(29, 'VC69079F8A4AF2B', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(30, 'VC69079F8A4B0FA', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(31, 'VC69079F8A4B28B', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(32, 'VC69079F8A4B7D7', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(33, 'VC69079F8A4B976', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(34, 'VC69079F8A4BB05', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(35, 'VC69079F8A4BF25', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(36, 'VC69079F8A4C415', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(37, 'VC69079F8A4C5A7', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(38, 'VC69079F8A4C761', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(39, 'VC69079F8A4CB80', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(40, 'VC69079F8A4D0AF', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(41, 'VC69079F8A4D244', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(42, 'VC69079F8A4D3E9', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(43, 'VC69079F8A4D78C', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(44, 'VC69079F8A4D930', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(45, 'VC69079F8A4DBCC', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(46, 'VC69079F8A4DD7E', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(47, 'VC69079F8A4E16A', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(48, 'VC69079F8A4E7A9', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(49, 'VC69079F8A4EBD5', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(50, 'VC69079F8A4EEB3', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(51, 'VC69079F8A4F061', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(52, 'VC69079F8A4F30A', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(53, 'VC69079F8A4F4B4', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(54, 'VC69079F8A4F9F6', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(55, 'VC69079F8A4FBA0', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(56, 'VC69079F8A4FE87', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(57, 'VC69079F8A502A4', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(58, 'VC69079F8A50468', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(59, 'VC69079F8A50628', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(60, 'VC69079F8A507B8', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(61, 'VC69079F8A50AA5', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(62, 'VC69079F8A50C67', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(63, 'VC69079F8A51255', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(64, 'VC69079F8A514FD', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(65, 'VC69079F8A517D1', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(66, 'VC69079F8A51AC6', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(67, 'VC69079F8A51C81', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(68, 'VC69079F8A51E10', 500000.00, 'Voucher giảm 500.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(69, 'VC69079F8A51FA5', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:14:34', '2025-11-02 18:14:34'),
(70, 'VC6907A04200205', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(71, 'VC6907A04200800', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(72, 'VC6907A04201482', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(73, 'VC6907A042018C5', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(74, 'VC6907A04201B82', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(75, 'VC6907A04201E63', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(76, 'VC6907A042135DB', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(77, 'VC6907A042139FB', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(78, 'VC6907A04213CFC', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(79, 'VC6907A04214790', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(80, 'VC6907A04214946', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(81, 'VC6907A04214D8F', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(82, 'VC6907A04214F2A', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(83, 'VC6907A0421569D', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(84, 'VC6907A04215835', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(85, 'VC6907A042159CD', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(86, 'VC6907A04215CB0', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(87, 'VC6907A04215F7C', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(88, 'VC6907A04216126', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(89, 'VC6907A042162E1', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(90, 'VC6907A0421648B', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(91, 'VC6907A0421698D', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(92, 'VC6907A04216B4F', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(93, 'VC6907A04216D12', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(94, 'VC6907A04216EBA', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(95, 'VC6907A0421707D', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(96, 'VC6907A04217212', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(97, 'VC6907A042174D7', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(98, 'VC6907A0421766C', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(99, 'VC6907A0421791E', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(100, 'VC6907A04217AE2', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(101, 'VC6907A04217DBF', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(102, 'VC6907A042180B1', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(103, 'VC6907A0421849C', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(104, 'VC6907A04218632', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(105, 'VC6907A0421890F', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(106, 'VC6907A04218AD3', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(107, 'VC6907A04218C90', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(108, 'VC6907A04218F76', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(109, 'VC6907A0421A9CE', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(110, 'VC6907A0421AEF6', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(111, 'VC6907A0421B0A9', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(112, 'VC6907A0421B241', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(113, 'VC6907A04227E32', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(114, 'VC6907A04228016', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(115, 'VC6907A04228436', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(116, 'VC6907A04228719', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(117, 'VC6907A042288FC', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(118, 'VC6907A04228CF9', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(119, 'VC6907A04228FC5', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(120, 'VC6907A042292B1', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(121, 'VC6907A042296C3', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(122, 'VC6907A0422986E', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(123, 'VC6907A04229C6D', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(124, 'VC6907A04229E04', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(125, 'VC6907A0422A94D', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(126, 'VC6907A0422AAEE', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(127, 'VC6907A0422AC9E', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(128, 'VC6907A0422B08B', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(129, 'VC6907A0422B223', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:17:38', '2025-11-02 18:17:38'),
(130, 'VC6907A1B436483', 1000000.00, 'Voucher giảm 1.000.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:48', '2025-11-02 18:23:48'),
(131, 'VC6907A1BDB5652', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(132, 'VC6907A1BDB58C0', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(133, 'VC6907A1BDB60BE', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(134, 'VC6907A1BDB62B9', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(135, 'VC6907A1BDB64A8', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(136, 'VC6907A1BDB6A1F', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(137, 'VC6907A1BDB6EAD', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(138, 'VC6907A1BDB7086', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(139, 'VC6907A1BDB73CE', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(140, 'VC6907A1BDB76D7', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(141, 'VC6907A1BDB79EB', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(142, 'VC6907A1BDC8A03', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(143, 'VC6907A1BDC8EA8', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(144, 'VC6907A1BDC9072', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(145, 'VC6907A1BDC9388', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(146, 'VC6907A1BDC9EA2', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(147, 'VC6907A1BDCA1DD', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(148, 'VC6907A1BDCA383', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(149, 'VC6907A1BDCA562', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(150, 'VC6907A1BDCA88D', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(151, 'VC6907A1BDCADB3', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(152, 'VC6907A1BDCD754', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(153, 'VC6907A1BDCD90F', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(154, 'VC6907A1BDCDC1B', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(155, 'VC6907A1BDCDDBC', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(156, 'VC6907A1BDCDF75', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(157, 'VC6907A1BDCE11C', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(158, 'VC6907A1BDCE338', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(159, 'VC6907A1BDCE567', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(160, 'VC6907A1BDCE887', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(161, 'VC6907A1BDCEE5C', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(162, 'VC6907A1BDCF023', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(163, 'VC6907A1BDCF45C', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(164, 'VC6907A1BDCF62D', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(165, 'VC6907A1BDCF99D', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(166, 'VC6907A1BDCFC86', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(167, 'VC6907A1BDD007A', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(168, 'VC6907A1BDD04B8', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(169, 'VC6907A1BDD08AE', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(170, 'VC6907A1BDD0CBB', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(171, 'VC6907A1BDD0E7A', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(172, 'VC6907A1BDD100F', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(173, 'VC6907A1BDD11D2', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(174, 'VC6907A1BDD137B', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(175, 'VC6907A1BDD150F', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(176, 'VC6907A1BDD16B4', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(177, 'VC6907A1BDD1E20', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(178, 'VC6907A1BDD1FC1', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(179, 'VC6907A1BDD22AD', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(180, 'VC6907A1BDD244C', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(181, 'VC6907A1BDD25DF', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(182, 'VC6907A1BDD32DA', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(183, 'VC6907A1BDD3598', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(184, 'VC6907A1BDD3737', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(185, 'VC6907A1BDD3A00', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(186, 'VC6907A1BDD3B96', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(187, 'VC6907A1BDD3F9D', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(188, 'VC6907A1BDD4169', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:23:57', '2025-11-02 18:23:57'),
(189, 'VC6907A22954F3B', 500000.00, 'Voucher giảm 500.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:25:45', '2025-11-02 18:25:45'),
(190, 'VC6907A2371FF97', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:25:59', '2025-11-02 18:25:59'),
(191, 'VC6907A287F1BED', 200000.00, 'Voucher giảm 200.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:27:19', '2025-11-02 18:27:19'),
(192, 'VC6907A298C8A7C', 100000.00, 'Voucher giảm 100.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:27:36', '2025-11-02 18:27:36'),
(193, 'VC6907A6E390304', 500000.00, 'Voucher giảm 500.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-03 02:45:55', '2025-11-02 18:45:55'),
(194, 'VC69286D7D4F560', 500000.00, 'Voucher giảm 500.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-12-27 23:25:49', '2025-11-27 15:25:49');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `wheel_prizes`
--

CREATE TABLE `wheel_prizes` (
  `id` int(11) NOT NULL,
  `prize_name` varchar(255) NOT NULL,
  `prize_description` text DEFAULT NULL,
  `prize_value` varchar(255) DEFAULT NULL,
  `prize_icon` varchar(50) DEFAULT NULL,
  `prize_color` varchar(20) DEFAULT '#16a34a',
  `probability_weight` int(11) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Đang đổ dữ liệu cho bảng `wheel_prizes`
--

INSERT INTO `wheel_prizes` (`id`, `prize_name`, `prize_description`, `prize_value`, `prize_icon`, `prize_color`, `probability_weight`, `is_active`, `created_at`, `updated_at`) VALUES
(5, 'May Mắn lần sau', 'Không trúng, thử lại nhé!', 'May mắn lần sau', '?', '#9CA3AF', 5, 1, '2025-11-14 09:31:35', '2025-11-27 11:24:07'),
(6, 'Voucher 10.000 vnđ', 'Giảm 1.000.000đ cho đơn hàng > 30 triệu', '1.000.000đ', '?', '#EC4899', 1, 1, '2025-11-14 09:31:35', '2025-11-27 11:20:01'),
(7, 'Voucher 15,000 vnđ', 'Giảm ngay 500.000đ cho đơn hàng bất kỳ', '500.000đ', NULL, '#F59E0B', 3, 1, '2025-11-14 10:38:49', '2025-11-27 11:26:40'),
(8, 'Giảm 10% nước giặt', 'Tặng pin dự phòng mini HC', 'Quà tặng', NULL, '#3B82F6', 2, 1, '2025-11-14 10:38:49', '2025-11-27 11:23:49'),
(9, 'Giảm 15%', 'Giảm 15% cho gói khảo sát bất kỳ', '15%', NULL, '#10B981', 4, 1, '2025-11-14 10:38:49', '2025-11-14 10:38:49'),
(10, 'Giảm 20%', 'Miễn phí vệ sinh hệ pin 1 lần', 'Dịch vụ', NULL, '#6366F1', 2, 1, '2025-11-14 10:38:49', '2025-11-27 11:23:10'),
(11, 'May Mắn lần sau', 'Không trúng, thử lại nhé!', 'May mắn lần sau', NULL, '#9CA3AF', 5, 1, '2025-11-14 10:38:49', '2025-11-27 11:24:18'),
(12, '1 Chai nước ngọt', 'Giảm 1.000.000đ cho đơn hàng > 30 triệu', '1.000.000đ', NULL, '#EC4899', 1, 1, '2025-11-14 10:38:49', '2025-11-27 11:21:56'),
(13, 'Voucher 20,000 vnđ', NULL, NULL, NULL, '#16a34a', 1, 1, '2025-11-14 11:26:42', '2025-11-27 11:26:49'),
(14, 'May Mắn lần sau', NULL, NULL, NULL, '#16a34a', 1, 1, '2025-11-14 11:26:42', '2025-11-27 11:24:22'),
(15, 'Giảm 18%', NULL, NULL, NULL, '#16a34a', 1, 1, '2025-11-14 11:26:42', '2025-11-27 11:22:13'),
(16, 'Giảm 25%', NULL, NULL, NULL, '#16a34a', 1, 1, '2025-11-14 11:26:42', '2025-11-27 11:24:40'),
(17, 'May Mắn lần sau', NULL, NULL, NULL, '#16a34a', 1, 1, '2025-11-14 11:26:42', '2025-11-27 11:24:46'),
(19, 'Voucher 25,000 vnđ', NULL, NULL, NULL, '#16a34a', 1, 1, '2025-11-27 11:27:10', '2025-11-27 11:27:10'),
(20, 'May Mắn lần sau', NULL, NULL, NULL, '#16a34a', 1, 1, '2025-11-27 11:28:09', '2025-11-27 11:28:09'),
(21, 'May Mắn lần sau', NULL, NULL, NULL, '#16a34a', 1, 1, '2025-11-27 11:28:23', '2025-11-27 11:28:23');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_severity` (`severity`);

--
-- Chỉ mục cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `contact_channels`
--
ALTER TABLE `contact_channels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contact_channels_active` (`is_active`),
  ADD KEY `idx_contact_channels_display_order` (`display_order`),
  ADD KEY `idx_contact_channels_category` (`category`);

--
-- Chỉ mục cho bảng `dich_vu`
--
ALTER TABLE `dich_vu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dich_vu_active` (`is_active`),
  ADD KEY `idx_dich_vu_display_order` (`display_order`);

--
-- Chỉ mục cho bảng `electricity_prices`
--
ALTER TABLE `electricity_prices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_electricity_prices_tier` (`tier`),
  ADD KEY `idx_electricity_prices_active` (`is_active`),
  ADD KEY `idx_electricity_prices_effective_date` (`effective_date`);

--
-- Chỉ mục cho bảng `home_posts`
--
ALTER TABLE `home_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_home_posts_active` (`is_active`),
  ADD KEY `idx_home_posts_display_order` (`display_order`),
  ADD KEY `idx_home_posts_section` (`section_id`);

--
-- Chỉ mục cho bảng `intro_posts`
--
ALTER TABLE `intro_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_intro_posts_active` (`is_active`),
  ADD KEY `idx_intro_posts_display_order` (`display_order`);

--
-- Chỉ mục cho bảng `lottery_rewards`
--
ALTER TABLE `lottery_rewards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reward_template_id` (`reward_template_id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_won_at` (`won_at`),
  ADD KEY `idx_voucher_code` (`voucher_code`);

--
-- Chỉ mục cho bảng `lottery_tickets`
--
ALTER TABLE `lottery_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `idx_lottery_tickets_reward` (`pre_assigned_reward_id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_orders_status` (`order_status`),
  ADD KEY `idx_orders_approved` (`approved_by`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `order_vouchers`
--
ALTER TABLE `order_vouchers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_vouchers_order` (`order_id`),
  ADD KEY `idx_order_vouchers_voucher` (`voucher_id`);

--
-- Chỉ mục cho bảng `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_packages_category` (`category_id`),
  ADD KEY `idx_packages_active` (`is_active`);

--
-- Chỉ mục cho bảng `package_categories`
--
ALTER TABLE `package_categories`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `package_items`
--
ALTER TABLE `package_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `idx_package_items_product_id` (`product_id`);

--
-- Chỉ mục cho bảng `phuong`
--
ALTER TABLE `phuong`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_tinh` (`id_tinh`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_category_id` (`category_id`),
  ADD KEY `idx_products_is_active` (`is_active`),
  ADD KEY `idx_products_display_order` (`display_order`);

--
-- Chỉ mục cho bảng `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_categories_active` (`is_active`);

--
-- Chỉ mục cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_images_product_id` (`product_id`),
  ADD KEY `idx_product_images_display_order` (`display_order`);

--
-- Chỉ mục cho bảng `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_projects_active` (`is_active`),
  ADD KEY `idx_projects_display_order` (`display_order`);

--
-- Chỉ mục cho bảng `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_identifier_action` (`identifier`,`action`),
  ADD KEY `idx_blocked_until` (`blocked_until`);

--
-- Chỉ mục cho bảng `reward_templates`
--
ALTER TABLE `reward_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reward_templates_type` (`reward_type`);

--
-- Chỉ mục cho bảng `solar_surveys`
--
ALTER TABLE `solar_surveys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_surveys_user_id` (`user_id`),
  ADD KEY `idx_surveys_created_at` (`created_at`);

--
-- Chỉ mục cho bảng `survey_accessory_dependencies`
--
ALTER TABLE `survey_accessory_dependencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_accessory_dependency` (`accessory_config_id`,`dependent_product_id`),
  ADD KEY `dependent_product_id` (`dependent_product_id`);

--
-- Chỉ mục cho bảng `survey_product_configs`
--
ALTER TABLE `survey_product_configs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_product_survey` (`product_id`);

--
-- Chỉ mục cho bảng `survey_regions`
--
ALTER TABLE `survey_regions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `region_code` (`region_code`),
  ADD KEY `idx_survey_regions_code` (`region_code`),
  ADD KEY `idx_survey_regions_active` (`is_active`),
  ADD KEY `idx_survey_regions_order` (`display_order`);

--
-- Chỉ mục cho bảng `survey_results`
--
ALTER TABLE `survey_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_survey_results_survey_id` (`survey_id`);

--
-- Chỉ mục cho bảng `tinh`
--
ALTER TABLE `tinh`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ten_tinh` (`ten_tinh`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD KEY `idx_users_username` (`username`),
  ADD KEY `idx_users_phone` (`phone`);

--
-- Chỉ mục cho bảng `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_permission` (`user_id`,`permission_key`),
  ADD KEY `idx_user_permissions_user` (`user_id`),
  ADD KEY `idx_user_permissions_key` (`permission_key`);

--
-- Chỉ mục cho bảng `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `used_by_user_id` (`used_by_user_id`),
  ADD KEY `idx_vouchers_code` (`code`),
  ADD KEY `idx_vouchers_used` (`is_used`);

--
-- Chỉ mục cho bảng `wheel_prizes`
--
ALTER TABLE `wheel_prizes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_wheel_prizes_active` (`is_active`),
  ADD KEY `idx_wheel_prizes_weight` (`probability_weight`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT cho bảng `contact_channels`
--
ALTER TABLE `contact_channels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `dich_vu`
--
ALTER TABLE `dich_vu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `electricity_prices`
--
ALTER TABLE `electricity_prices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `home_posts`
--
ALTER TABLE `home_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `intro_posts`
--
ALTER TABLE `intro_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `lottery_rewards`
--
ALTER TABLE `lottery_rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=807;

--
-- AUTO_INCREMENT cho bảng `lottery_tickets`
--
ALTER TABLE `lottery_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=807;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT cho bảng `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

--
-- AUTO_INCREMENT cho bảng `order_vouchers`
--
ALTER TABLE `order_vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `package_categories`
--
ALTER TABLE `package_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `package_items`
--
ALTER TABLE `package_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT cho bảng `phuong`
--
ALTER TABLE `phuong`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT cho bảng `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `reward_templates`
--
ALTER TABLE `reward_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `solar_surveys`
--
ALTER TABLE `solar_surveys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT cho bảng `survey_accessory_dependencies`
--
ALTER TABLE `survey_accessory_dependencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT cho bảng `survey_product_configs`
--
ALTER TABLE `survey_product_configs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT cho bảng `survey_regions`
--
ALTER TABLE `survey_regions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `survey_results`
--
ALTER TABLE `survey_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT cho bảng `tinh`
--
ALTER TABLE `tinh`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `user_permissions`
--
ALTER TABLE `user_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=195;

--
-- AUTO_INCREMENT cho bảng `wheel_prizes`
--
ALTER TABLE `wheel_prizes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `lottery_rewards`
--
ALTER TABLE `lottery_rewards`
  ADD CONSTRAINT `lottery_rewards_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lottery_rewards_ibfk_2` FOREIGN KEY (`reward_template_id`) REFERENCES `reward_templates` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lottery_rewards_ibfk_3` FOREIGN KEY (`ticket_id`) REFERENCES `lottery_tickets` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `lottery_tickets`
--
ALTER TABLE `lottery_tickets`
  ADD CONSTRAINT `lottery_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lottery_tickets_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Các ràng buộc cho bảng `order_vouchers`
--
ALTER TABLE `order_vouchers`
  ADD CONSTRAINT `order_vouchers_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_vouchers_ibfk_2` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`);

--
-- Các ràng buộc cho bảng `packages`
--
ALTER TABLE `packages`
  ADD CONSTRAINT `packages_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `package_categories` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `package_items`
--
ALTER TABLE `package_items`
  ADD CONSTRAINT `fk_package_items_product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `package_items_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `phuong`
--
ALTER TABLE `phuong`
  ADD CONSTRAINT `phuong_ibfk_1` FOREIGN KEY (`id_tinh`) REFERENCES `tinh` (`id`);

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `solar_surveys`
--
ALTER TABLE `solar_surveys`
  ADD CONSTRAINT `solar_surveys_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `survey_accessory_dependencies`
--
ALTER TABLE `survey_accessory_dependencies`
  ADD CONSTRAINT `survey_accessory_dependencies_ibfk_1` FOREIGN KEY (`accessory_config_id`) REFERENCES `survey_product_configs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `survey_accessory_dependencies_ibfk_2` FOREIGN KEY (`dependent_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `survey_product_configs`
--
ALTER TABLE `survey_product_configs`
  ADD CONSTRAINT `survey_product_configs_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `survey_results`
--
ALTER TABLE `survey_results`
  ADD CONSTRAINT `survey_results_ibfk_1` FOREIGN KEY (`survey_id`) REFERENCES `solar_surveys` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `vouchers`
--
ALTER TABLE `vouchers`
  ADD CONSTRAINT `vouchers_ibfk_1` FOREIGN KEY (`used_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
