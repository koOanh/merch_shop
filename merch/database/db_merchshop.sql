-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 06, 2025 lúc 04:15 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `db_merchshop`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `banner`
--

CREATE TABLE `banner` (
  `banner_id` int(11) NOT NULL,
  `banner_subtitle` varchar(50) NOT NULL,
  `banner_title` text NOT NULL,
  `banner_items_price` int(10) NOT NULL,
  `banner_image` varchar(50) NOT NULL,
  `banner_status` binary(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `banner`
--

INSERT INTO `banner` (`banner_id`, `banner_subtitle`, `banner_title`, `banner_items_price`, `banner_image`, `banner_status`) VALUES
(1, 'Short n\' Sweet Collection', 'Latest sale merch', 20, 'banner-1.jpg', 0x31),
(2, 'Trending merch', 'Cute n\' girly', 15, 'banner-2.jpg', 0x31),
(3, 'Sale Offer', 'Cheap and sweet', 29, 'banner-3.jpg', 0x31);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `category`
--

CREATE TABLE `category` (
  `id` int(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  `img` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` binary(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `category`
--

INSERT INTO `category` (`id`, `name`, `img`, `created_at`, `updated_at`, `status`) VALUES
(4, 'Merchandise', 'perfume.svg', '2022-11-08 17:05:38', '2025-04-22 00:22:50', 0x31),
(5, 'Music', 'cosmetics.svg', '2022-11-08 17:05:38', '2025-04-22 00:23:02', 0x31),
(6, 'Collections', 'glasses.svg', '2022-11-08 17:05:38', '2025-04-22 00:23:33', 0x31);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `category_bar`
--

CREATE TABLE `category_bar` (
  `id` int(10) NOT NULL,
  `category_title` varchar(50) NOT NULL,
  `category_quantity` int(10) NOT NULL,
  `category_img` varchar(50) NOT NULL,
  `category_status` binary(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `category_bar`
--

INSERT INTO `category_bar` (`id`, `category_title`, `category_quantity`, `category_img`, `category_status`) VALUES
(1, 'Dress & frock', 53, 'coat.svg', 0x31),
(2, 'Glasses & lens', 68, 'glasses.svg', 0x31),
(3, 'Shorts & jeans', 84, 'shorts.svg', 0x31),
(4, 'T-shirts', 35, 'tee.svg', 0x31),
(5, 'Jacket', 16, 'jacket.svg', 0x31),
(6, 'Watch', 27, 'watch.svg', 0x31),
(7, 'Hat & caps', 39, 'hat.svg', 0x31);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `collections`
--

CREATE TABLE `collections` (
  `id` int(10) NOT NULL,
  `perfume_category_name` varchar(50) NOT NULL,
  `perfume_category_quantity` int(10) DEFAULT 0,
  `perfume_category_status` binary(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `collections`
--

INSERT INTO `collections` (`id`, `perfume_category_name`, `perfume_category_quantity`, `perfume_category_status`) VALUES
(1, 'Tour', 12, 0x31),
(2, 'Short n Sweet', 60, 0x31),
(3, 'Emails i cant sent', 50, 0x31),
(4, 'Fragrance', 87, 0x31);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `customer`
--

CREATE TABLE `customer` (
  `customer_id` int(100) NOT NULL,
  `customer_fname` varchar(50) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_pwd` varchar(100) NOT NULL,
  `customer_phone` varchar(15) NOT NULL,
  `customer_address` text NOT NULL,
  `customer_role` varchar(50) NOT NULL DEFAULT 'normal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `customer`
--

INSERT INTO `customer` (`customer_id`, `customer_fname`, `customer_email`, `customer_pwd`, `customer_phone`, `customer_address`, `customer_role`) VALUES
(9, 'kimo', 'kimo@gmail.com', 'kimo@gmail.com', '03469589557', 'vietnam', 'admin'),
(24, 'kimo', '149oanh@gmail.com', '149oanh@gmail.com', '923456123421', '149oanh@gmail.com', 'normal'),
(25, 'do thi kim oanh', 'kimokasdlfl@gmail.com', 'kimokasdlfl@gmail.com', '921234512345', 'Hoai Duc', 'normal');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `deal_of_the_day`
--

CREATE TABLE `deal_of_the_day` (
  `deal_id` int(10) NOT NULL,
  `deal_title` text NOT NULL,
  `deal_description` text NOT NULL,
  `deal_net_price` double(10,2) NOT NULL,
  `deal_discounted_price` double(10,2) NOT NULL,
  `available_deal` int(10) NOT NULL,
  `sold_deal` int(10) NOT NULL,
  `deal_image` varchar(50) NOT NULL,
  `deal_status` binary(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `deal_of_the_day`
--

INSERT INTO `deal_of_the_day` (`deal_id`, `deal_title`, `deal_description`, `deal_net_price`, `deal_discounted_price`, `available_deal`, `sold_deal`, `deal_image`, `deal_status`) VALUES
(1, 'fruitcake store exclusive LP', 'Rare olive green colour.', 200.00, 150.00, 40, 20, 'deal1.jpg', 0x31),
(2, 'Nonsense / A Nonsense Christmas 7in', 'Burning red colour', 250.00, 190.00, 40, 15, 'deal2.jpg', 0x31);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `merchandise`
--

CREATE TABLE `merchandise` (
  `id` int(10) NOT NULL,
  `footwear_category_name` varchar(50) NOT NULL,
  `footwear_category_quantity` int(10) DEFAULT 0,
  `footwear_category_status` binary(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `merchandise`
--

INSERT INTO `merchandise` (`id`, `footwear_category_name`, `footwear_category_quantity`, `footwear_category_status`) VALUES
(1, 'Accessories', 45, 0x31),
(2, 'Apparel', 75, 0x31);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `music`
--

CREATE TABLE `music` (
  `id` int(10) NOT NULL,
  `cloth_category_name` varchar(50) NOT NULL,
  `cloth_category_quantity` int(10) DEFAULT 0,
  `coloth_category_status` binary(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `music`
--

INSERT INTO `music` (`id`, `cloth_category_name`, `cloth_category_quantity`, `coloth_category_status`) VALUES
(1, 'Vinyl', 300, 0x31),
(4, 'Physical', 50, 0x31),
(5, 'Digital', 87, 0x31);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_fname` varchar(100) DEFAULT NULL,
  `customer_lname` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(30) DEFAULT NULL,
  `address_house` varchar(255) DEFAULT NULL,
  `address_street` varchar(255) DEFAULT NULL,
  `address_city` varchar(100) DEFAULT NULL,
  `address_postcode` varchar(20) DEFAULT NULL,
  `address_country` varchar(100) DEFAULT NULL,
  `paypal_order_id` varchar(50) NOT NULL,
  `paypal_transaction_id` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL,
  `order_status` varchar(50) NOT NULL DEFAULT 'Pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`order_id`, `customer_email`, `customer_fname`, `customer_lname`, `customer_phone`, `address_house`, `address_street`, `address_city`, `address_postcode`, `address_country`, `paypal_order_id`, `paypal_transaction_id`, `amount`, `currency`, `order_status`, `order_date`) VALUES
(9, 'oanh@gmail.com', 'oanh', 'kim', '0923842347', '56', 'Yen Xa', 'Tan Trieu Thanh Tri Ha Noi', '100000', 'Vietnam', '36B23039SP295051L', '9JB90725EH328153F', 58.00, 'USD', 'Paid', '2025-05-05 14:41:19'),
(10, 'oanh@gmail.com', 'oanh', 'kim', '0923842347', '56', 'Yen Xa', 'Tan Trieu Thanh Tri Ha Noi', '100000', 'Vietnam', '5MU35487FE118072X', '66R136757Y7964832', 103.00, 'USD', 'Paid', '2025-05-05 14:42:39'),
(11, 'oanh3@gmail.com', 'kim', 'kim', '0923842347', '56', 'Yen Xa', 'Tan Trieu Thanh Tri Ha Noi', '100000', 'Vietnam', '5V587715YG164430M', '3HV43259MR992183S', 158.00, 'USD', 'Paid', '2025-05-05 14:52:26');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `product_id` int(100) NOT NULL,
  `product_catag` varchar(100) NOT NULL,
  `product_title` varchar(255) NOT NULL,
  `product_price` int(100) NOT NULL,
  `product_desc` text NOT NULL,
  `product_date` varchar(50) NOT NULL,
  `product_img` text NOT NULL,
  `product_left` int(100) NOT NULL,
  `product_author` varchar(100) NOT NULL,
  `category_id` int(10) DEFAULT NULL,
  `section_id` int(10) DEFAULT NULL,
  `discounted_price` double(10,2) DEFAULT NULL,
  `image_1` varchar(50) NOT NULL,
  `image_2` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` binary(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`product_id`, `product_catag`, `product_title`, `product_price`, `product_desc`, `product_date`, `product_img`, `product_left`, `product_author`, `category_id`, `section_id`, `discounted_price`, `image_1`, `image_2`, `created_at`, `updated_at`, `status`) VALUES
(1, 'women', 'vinyl', 75, 'Short n\' Sweet (Deluxe) D2C Exclusive 2LP', '', 'pic1.jpg', 50, 'admin fahad', NULL, 7, 48.00, 'jacket-4.jpg', NULL, '2023-06-16 18:33:06', '2025-04-22 01:36:38', 0x31),
(2, 'women', 'vinyl', 56, 'Taste 7in Single', '', 'pic2.jpg', 50, 'admin fahad', NULL, 7, 45.00, 'shirt-2.jpg', NULL, '2023-06-16 18:33:06', '2025-04-22 01:36:59', 0x31),
(3, 'women', 'vinyl', 65, 'Bed Chem 7in', '', 'pic3.jpg', 50, 'admin fahad', NULL, 7, 58.00, 'jacket-6.jpg', NULL, '2023-06-16 18:33:06', '2025-04-22 01:38:18', 0x31),
(4, 'women', 'accessories', 25, 'busy woman hat', '', 'pic4.jpg', 50, 'admin fahad', NULL, 7, 35.00, 'clothes-4.jpg', NULL, '2023-06-16 18:33:06', '2025-04-22 01:38:28', 0x31),
(5, 'women', 'accessories', 105, 'espresso mini cup & saucer', '', 'pic5.jpg', 50, 'admin fahad', NULL, 7, 99.00, 'shoe-2_1.jpg', NULL, '2023-06-16 18:33:06', '2025-04-22 01:38:34', 0x31),
(6, 'women', 'accessories', 170, 'short n\' sweet mug', '', 'pic6.jpg', 50, 'admin fahad', NULL, 7, 150.00, 'watch-4.jpg', NULL, '2023-06-16 18:33:06', '2025-04-22 01:38:40', 0x31),
(7, 'women', 'Apparel\n', 120, 'got you blocked crop tank\n\n', '', 'pic7.jpg', 50, 'admin fahad', NULL, 7, 100.00, 'watch-2.jpg', NULL, '2023-06-16 18:33:06', '2025-04-22 01:41:35', 0x31),
(8, 'women', 'Apparel\n', 25, 'that\'s that me crop tee', '', 'pic8.jpg', 50, 'admin fahad', NULL, 7, 30.00, 'party-wear-2.jpg', NULL, '2023-06-16 18:33:06', '2025-04-22 01:42:25', 0x31),
(9, 'women', 'cd', 45, 'Short n\' Sweet (Deluxe) CD', '', 'pic9.jpg', 50, 'admin fahad', NULL, 7, 32.00, 'jacket-2.jpg', NULL, '2023-06-16 18:33:06', '2025-04-22 01:43:05', 0x31),
(10, 'men', 'cd', 64, 'Short n\' Sweet Alternate Cover CD', '', 'pic10.jpg', 50, 'admin fahad', NULL, 7, 58.00, 'sports-4.jpg', NULL, '2023-06-16 18:33:06', '2025-04-22 01:43:25', 0x31);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

CREATE TABLE `reviews` (
  `id` int(10) NOT NULL,
  `name` varchar(30) NOT NULL,
  `review` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `reviews`
--

INSERT INTO `reviews` (`id`, `name`, `review`) VALUES
(1, 'Iqso Fhd', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quidem ad\r\n            fugiat, itaque dolore culpa ipsa fuga, illum, maxime exercitationem\r\n            commodi nihil nobis nulla similique quibusdam sed expedita provident'),
(2, 'IFAD', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quidem ad\r\n            fugiat, itaque dolore culpa ipsa fuga, illum, maxime exercitationem\r\n            commodi nihil nobis nulla similique quibusdam sed expedita provident'),
(3, 'Eva Silk', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quidem ad\r\n            fugiat, itaque dolore culpa ipsa fuga, illum, maxime exercitationem\r\n            commodi nihil nobis nulla similique quibusdam sed expedita provident');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `section`
--

CREATE TABLE `section` (
  `id` int(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  `status` binary(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `section`
--

INSERT INTO `section` (`id`, `name`, `status`) VALUES
(2, 'new_arrival', 0x31),
(3, 'trending', 0x31),
(4, 'top_rated', 0x31),
(5, 'deal_of_day', 0x31),
(6, 'best_seller', 0x31),
(7, 'new_products', 0x31);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `settings`
--

CREATE TABLE `settings` (
  `website_name` varchar(60) NOT NULL,
  `website_logo` varchar(50) NOT NULL,
  `website_footer` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `settings`
--

INSERT INTO `settings` (`website_name`, `website_logo`, `website_footer`) VALUES
('Sabrina Capenter Shop', 'HCA-E-COMMERCE.png', 'Sabrina Capenter Shop');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `banner`
--
ALTER TABLE `banner`
  ADD PRIMARY KEY (`banner_id`);

--
-- Chỉ mục cho bảng `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `category_bar`
--
ALTER TABLE `category_bar`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `collections`
--
ALTER TABLE `collections`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `customer_email` (`customer_email`);

--
-- Chỉ mục cho bảng `deal_of_the_day`
--
ALTER TABLE `deal_of_the_day`
  ADD PRIMARY KEY (`deal_id`);

--
-- Chỉ mục cho bảng `merchandise`
--
ALTER TABLE `merchandise`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `music`
--
ALTER TABLE `music`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `paypal_order_id` (`paypal_order_id`),
  ADD UNIQUE KEY `paypal_transaction_id` (`paypal_transaction_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Chỉ mục cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `banner`
--
ALTER TABLE `banner`
  MODIFY `banner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `category_bar`
--
ALTER TABLE `category_bar`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
