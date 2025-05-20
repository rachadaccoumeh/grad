-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2025 at 10:11 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `roomgenius_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `name`, `email`, `message`, `created_at`, `is_read`) VALUES
(1, 'lora', 'lora@gmail.com', 'Hello,\\r\\nI am interested in learning more about the features of RoomGenius. Could you please provide me with more information about how the platform works and the available services? Looking forward to hearing from you.\\r\\n\\r\\nThank you!', '2025-04-11 19:22:12', 1),
(3, 'fatima', 'fatima@gmail.com', 'thanks for your services', '2025-04-24 19:14:09', 1),
(4, 'carl', 'carldash22@gmail.com', 'hope you fix our problems ', '2025-04-24 19:33:27', 1),
(5, 'nasa', 'nasa@gmail.com', 'I came across your website and I’m really interested in learning more about your services. Could you please provide more details about what you offer and how I can get started? I’m especially curious about [mention specific service or feature].\\r\\nLooking forward to your response!', '2025-04-24 19:35:35', 1),
(7, 'anna', 'anna@gmail.com', 'can you help to register some issues appears in the registration form', '2025-04-25 17:22:13', 1),
(8, 'carla', 'carla@gmail.com', 'happy for you', '2025-04-25 17:23:06', 0),
(9, 'sandi', 'sandisaid@gmail.com', 'thank you ', '2025-04-30 22:35:17', 0);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_id` varchar(20) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `zip_code` varchar(20) NOT NULL,
  `governorate` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `latitude` decimal(10,6) NOT NULL,
  `longitude` decimal(10,6) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `shipping` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `card_name` varchar(100) NOT NULL,
  `card_number` varchar(25) NOT NULL,
  `card_expiry` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` varchar(20) NOT NULL,
  `product_id` int(10) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_purchase` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset`
--

CREATE TABLE `password_reset` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expiry` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset`
--

INSERT INTO `password_reset` (`id`, `user_id`, `token`, `expiry`, `created_at`) VALUES
(10, 7, '3b44c3b2758773f6949da4ca96834a31b0a0c7a9228fd04bfb06bac6b1c56e16', '2025-05-11 23:34:50', '2025-05-11 20:34:50'),
(12, 14, 'b74718a7e81046aa483cdba3d11c6f90d12764540940a73d3ef9e30f56eac70f', '2025-05-12 00:22:51', '2025-05-11 21:22:51'),
(18, 1, 'e26263b09f5ad01aa7521a36e7277833b9d238b45f9b784275903d892c8919b6', '2025-05-13 01:10:22', '2025-05-12 22:10:22');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_id` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `style` varchar(50) NOT NULL,
  `category` varchar(50) NOT NULL,
  `image_path` varchar(1000) NOT NULL,
  `date_added` date NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_id`, `name`, `description`, `price`, `style`, `category`, `image_path`, `date_added`, `stock_quantity`, `is_featured`, `created_at`, `updated_at`) VALUES
(7, 'K10', 'Green Kitchen', 'Sleek minimalist aesthetic with eco-friendly materials and smart storage solutions.', 15000.00, 'Minimalist', 'Kitchen', 'photos/1747328581_IMG_4765.jpg', '2025-05-09', 5, 1, '2025-05-09 20:40:05', '2025-05-15 17:03:01'),
(9, 'D1', 'Dining Table', 'Elegant wooden dining table suitable for 6 people, featuring rich cherry wood with traditional carved details.\r\n', 400.00, 'Traditional', 'Dining Room', 'photos/1747326957_1747326813_1746822627_IMG_4740.JPG', '2025-05-15', 5, 1, '2025-05-14 22:38:37', '2025-05-15 16:35:57'),
(10, 'L1', 'Modern Sofa', 'Contemporary three-seater sofa with clean lines, comfortable cushions, and stylish modern design\r\n', 600.00, 'Modern', 'Living Room', 'photos/1747327409_IMG_4743.jpg', '2025-05-15', 5, 1, '2025-05-14 22:45:38', '2025-05-15 16:43:29'),
(11, 'B2', 'King Size Bed', 'Luxurious king-size bed with upholstered headboard, solid wood frame, and elegant design for a comfortable nights sleep.\r\n', 850.00, 'Luxury', 'Bedroom', 'photos/1747327820_1746822981_IMG_4742.JPG', '2025-05-15', 3, 1, '2025-05-14 22:58:08', '2025-05-15 16:50:20'),
(16, 'O1', 'Office Desk', 'Minimalist office desk with spacious work surface, built-in cable management, and modern sleek design for productivity.\r\n', 300.00, 'Minimalist', 'Office', 'photos/1747328208_IMG_4744.JPG', '2025-05-15', 5, 1, '2025-05-15 13:22:21', '2025-05-15 16:56:48'),
(17, 'K1', 'Kitchen Cabinet', 'Custom kitchen cabinet system with soft-close drawers, ample storage, and high-quality materials for a functional kitchen.\r\n', 3000.00, 'Modern', 'Kitchen', 'photos/1747328729_IMG_4745.JPG', '2025-05-15', 3, 1, '2025-05-15 13:26:31', '2025-05-15 17:05:29'),
(18, 'k3', 'Beige and green kitchen', 'Warm rustic design with natural materials and earthy tones for a cozy atmosphere.', 10000.00, 'Rustic', 'Kitchen', 'photos/1747316242_Rustick.JPG', '2025-05-15', 5, 1, '2025-05-15 13:37:22', '2025-05-15 13:49:05'),
(19, 'K4', 'White and beige Kitchen', 'Sleek minimalist aesthetic with clean surfaces and smart storage solutions.', 8500.00, 'Minimalist', 'Kitchen', 'photos/1747316889_Minimalist1.JPG', '2025-05-15', 5, 1, '2025-05-15 13:48:09', '2025-05-15 13:48:09'),
(20, 'O13', 'Scandinavian Office Nook', 'Scandinavian-inspired workspace featuring light wood desk with hairpin legs, ergonomic bent plywood chair, minimalist wall shelving, and functional task lighting in matte white finish.', 15200.00, 'Minimalist', 'Office', 'photos/1747330554_O12.JPG', '2025-05-15', 7, 1, '2025-05-15 13:51:05', '2025-05-15 17:35:54'),
(21, 'K6', 'White/yellow/blue kitchen', 'Classic traditional style with elegant details and timeless appeal.', 22500.00, 'Traditional', 'Kitchen', 'photos/1747317147_IMG_4752.JPG', '2025-05-15', 5, 1, '2025-05-15 13:52:27', '2025-05-15 13:52:27'),
(22, 'K7', 'Green and orange kitchen', 'Efficient space usage with clean lines and bold color accents.', 7800.00, 'Minimalist', 'Kitchen', 'photos/1747317248_IMG_4750.JPG', '2025-05-15', 5, 1, '2025-05-15 13:54:08', '2025-05-15 13:54:08'),
(23, 'K8', 'Orange and green kitchen', 'Clean lines and minimalist design with high-end appliances and ample storage space.', 12500.00, 'Modern', 'Kitchen', 'photos/1747317489_IMG_4755.JPG', '2025-05-15', 5, 1, '2025-05-15 13:58:09', '2025-05-15 13:58:09'),
(24, 'K9', 'Yellow kitchen', 'Warm traditional design with classic elements and comfortable layout.', 8500.00, 'Traditional', 'Kitchen', 'photos/1747317618_IMG_4764.jpg', '2025-05-15', 5, 1, '2025-05-15 14:00:18', '2025-05-15 14:00:18'),
(26, 'K11', 'Arabic modern blue kitchen', 'Fusion of traditional Arabic elements with modern design, featuring blue accents.', 18500.00, 'Modern', 'Kitchen', 'photos/1747317807_IMG_4766.jpg', '2025-05-15', 5, 1, '2025-05-15 14:03:27', '2025-05-15 14:03:27'),
(27, 'K12', 'White and green kitchen', 'Charming rustic design with natural wood elements and vintage-inspired fixtures.', 9800.00, 'Rustic', 'Kitchen', 'photos/1747317936_IMG_4767.jpg', '2025-05-15', 5, 1, '2025-05-15 14:05:36', '2025-05-15 14:05:36'),
(28, 'K13', 'Baby Blue kitchen', 'Bold industrial design with metal finishes, exposed elements, and contemporary blue color scheme.', 14500.00, 'Industrial', 'Kitchen', 'photos/1747318006_IMG_4771.jpg', '2025-05-15', 5, 1, '2025-05-15 14:06:46', '2025-05-15 14:06:46'),
(29, 'K14', 'Wooden Shelf', 'Elegant wooden shelf with minimalist design, perfect for displaying kitchen items and storing essentials.', 300.00, 'Modern', 'Kitchen', 'photos/1747318188_IMG_4774.jpg', '2025-05-15', 10, 1, '2025-05-15 14:09:48', '2025-05-15 14:09:48'),
(30, 'K15', 'Modern Shelf', 'Contemporary shelf design with clean lines and durable construction, adding functionality to any kitchen space.', 600.00, 'Minimalist', 'Kitchen', 'photos/1747318276_IMG_4773.jpg', '2025-05-15', 10, 1, '2025-05-15 14:11:16', '2025-05-15 14:11:16'),
(31, 'K16', 'Shelf for Cups', 'Specialized shelf designed for displaying and organizing cups and mugs with traditional styling elements.', 350.00, 'Traditional', 'Kitchen', 'photos/1747318366_IMG_4772.JPG', '2025-05-15', 12, 1, '2025-05-15 14:12:46', '2025-05-15 14:12:46'),
(32, 'K17', 'Wooden Storage', 'Rustic wooden storage solution with practical compartments for kitchen essentials and decorative items.', 250.00, 'Rustic', 'Kitchen', 'photos/1747318485_IMG_4776.jpg', '2025-05-15', 14, 1, '2025-05-15 14:14:45', '2025-05-15 14:14:45'),
(33, 'O10', 'Creative Minimalist Studio', 'Clean, distraction-free creative space with streamlined desk, ergonomic mesh chair, floating shelves, and modular storage cubes in white and natural wood finishes.', 4200.00, 'Minimalist', 'Office', 'photos/1747338047_O9.JPG', '2025-05-15', 4, 1, '2025-05-15 15:39:36', '2025-05-15 19:40:47'),
(34, 'GR11', 'Esports Battle Station', 'Competition-ready gaming setup with high-performance desk featuring mousepad surface, adjustable monitor arms, premium gaming chair with team color accents, and dedicated stream control deck mount.', 1950.00, 'Modern', 'Game Room', 'photos/1747326148_G11.JPG', '2025-05-15', 7, 1, '2025-05-15 16:22:28', '2025-05-15 16:22:28'),
(35, 'P5', 'Minimalist Prayer Nook', 'Sleek, minimalist prayer corner with floating prayer shelf, subtle qibla direction marker, slim profile Quran holder, and hidden storage compartments. Simple clean lines with natural oak finish and customizable accent lighting.', 799.00, 'Minimalist', 'Prayer Room', 'photos/1747326292_Py5.JPG', '2025-05-15', 10, 1, '2025-05-15 16:24:52', '2025-05-15 16:24:52'),
(36, 'GY2', 'Premium Home Gym Suite', 'Luxury all-in-one gym setup with power rack, Smith machine, functional trainer cables, leg press attachment, adjustable benches, dumbbell rack, weight plate storage, and integrated touchscreen for workout guidance.', 7899.00, 'Industrial', 'Gym', 'photos/1747326599_Gy2.JPG', '2025-05-15', 6, 1, '2025-05-15 16:29:59', '2025-05-15 16:29:59'),
(37, 'K2', 'Yellow and blue kitchen', 'Clean lines and minimalist design with high-end appliances and ample storage space.', 12500.00, 'Modern', 'Kitchen', 'photos/1747327075_IMG_4754.JPG', '2025-05-15', 4, 1, '2025-05-15 16:37:55', '2025-05-15 16:37:55'),
(38, 'B4', 'Urban Loft Bedroom Suite', 'Industrial bedroom collection featuring a metal frame queen bed with wooden slats, pipe-inspired nightstands, and a factory-style wardrobe with exposed hardware.', 5200.00, 'Industrial', 'Bedroom', 'photos/1747327497_Br3.JPG', '2025-05-15', 5, 1, '2025-05-15 16:44:57', '2025-05-15 16:44:57'),
(39, 'L2', 'industrial living room', 'Industrial-style living room with a large gray sofa, wooden crate coffee tables, open shelving, and lush green plants.', 10000.00, 'Industrial', 'Living Room', 'photos/1747327920_Industrial1.JPG', '2025-05-15', 5, 1, '2025-05-15 16:52:00', '2025-05-15 16:52:00'),
(40, 'G4', 'Bohemian Hanging Egg Chair', 'Statement hanging chair with bohemian-inspired woven design, sturdy powder-coated frame, plush weather-resistant cushions, and adjustable height. Perfect for creating a cozy reading nook in your garden.', 799.00, 'Bohemian', 'Garden', 'photos/1747336358_Ga4.JPG', '2025-05-15', 5, 1, '2025-05-15 16:58:31', '2025-05-15 19:12:38'),
(41, 'C2', 'Farmhouse Armoire Closet', 'Rustic freestanding armoire crafted from reclaimed barn wood with natural grain patterns. Features full-length hanging space, adjustable shelves, and two deep drawers with vintage-inspired hardware for a charming countryside aesthetic.', 1299.00, 'Rustic', 'Closet', 'photos/1747328959_C2.JPG', '2025-05-15', 3, 1, '2025-05-15 17:09:19', '2025-05-15 17:09:19'),
(42, 'L8', 'Modern blue LR', 'blue living room with goldern view and green plants and with a small table and a painting with colors blue and white', 6500.00, 'Modern', 'Living Room', 'photos/1747329361_Modern3.JPG', '2025-05-15', 5, 1, '2025-05-15 17:16:01', '2025-05-15 17:16:01'),
(43, 'K5', 'White and blue kitchen', 'Contemporary design with high-tech features and ergonomic layout.', 15200.00, 'Modern', 'Kitchen', 'photos/1747332036_IMG_4753.JPG', '2025-05-15', 5, 1, '2025-05-15 18:00:36', '2025-05-15 18:00:36'),
(44, 'G1', 'Modular Outdoor Sofa Set', 'Contemporary modular outdoor seating featuring weather-resistant fabric, powder-coated aluminum frame, and easy rearrangement options for flexible layouts. Includes 3 corner pieces and 2 center pieces.', 899.00, 'Modern', 'Garden', 'photos/1747337015_Ga1.JPG', '2025-05-15', 5, 1, '2025-05-15 18:56:01', '2025-05-15 19:23:35'),
(45, 'G7', 'Fire Pit Conversation Set', 'Rustic outdoor living set featuring propane fire pit table with lava rocks, four deep-seated armchairs with weather-resistant cushions, and reclaimed wood accents. Perfect for evening gatherings year-round.', 1299.00, 'Rustic', 'Garden', 'photos/1747335452_Ga7.jpg', '2025-05-15', 5, 1, '2025-05-15 18:57:32', '2025-05-15 18:57:32'),
(46, 'G6', 'Adjustable Sunlounger Set', 'Set of two adjustable loungers with modern silhouette, five reclining positions, all-weather mesh fabric, rust-resistant aluminum frame, and built-in wheels for easy repositioning to follow the sun.', 599.00, 'Modern', 'Garden', 'photos/1747335556_Ga6.JPG', '2025-05-15', 5, 1, '2025-05-15 18:59:16', '2025-05-15 18:59:16'),
(48, 'G3', 'Foldable Bistro Set', 'Space-saving bistro set perfect for balconies or small patios. Includes two foldable chairs and a compact round table, all made from powder-coated steel with sleek minimalist design and quick-dry materials.', 349.00, 'Minimalist', 'Garden', 'photos/1747335758_Ga3.JPG', '2025-05-15', 5, 1, '2025-05-15 19:02:38', '2025-05-15 19:02:38'),
(49, 'G2', 'Acacia Wood Dining Set', 'Rustic farmhouse-style dining set crafted from sustainable acacia hardwood with natural grain patterns. Features extendable table (seats 6-8) and matching bench with built-in storage compartment.', 1499.00, 'Rustic', 'Garden', 'photos/1747335840_Ga2.JPG', '2025-05-15', 5, 1, '2025-05-15 19:04:00', '2025-05-15 19:04:00'),
(51, 'G5', 'Cast Aluminum Gazebo Set', 'Elegant Victorian-inspired gazebo seating set featuring ornate cast aluminum construction, tempered glass tabletop, UV-resistant canopy, and comfortable cushioned seating for 6 people. Perfect for garden entertaining.', 2499.00, 'Industrial', 'Garden', 'photos/1747336516_Ga5.JPG', '2025-05-15', 5, 1, '2025-05-15 19:15:16', '2025-05-15 19:15:16'),
(52, 'G8', 'Teak Garden Bench', 'Traditional English garden bench crafted from premium grade A teak wood with naturally high oil content for exceptional weather resistance. Features classic slatted design, curved backrest, and arms that develop a beautiful silver patina over time.', 429.00, 'Classic', 'Garden', 'photos/1747337085_Ga8.JPG', '2025-05-15', 5, 1, '2025-05-15 19:24:45', '2025-05-15 19:24:45'),
(53, 'O7', 'Collaborative Hub Workstation', 'Modern collaborative workspace with height-adjustable desks, acoustic privacy panels, ergonomic seating for four, and integrated technology hub with wireless charging stations.', 7900.00, 'Modern', 'Office', 'photos/1747337623_IMG_5920.jpg', '2025-05-15', 5, 1, '2025-05-15 19:33:43', '2025-05-15 19:33:43'),
(54, 'O8', 'Corner Executive Office Package', 'Complete corner office solution with L-shaped executive desk, premium ergonomic chair, visitor seating area, custom storage wall, and integrated meeting space with presentation capabilities.', 12300.00, 'Executive', 'Office', 'photos/1747338601_O7.JPG', '2025-05-15', 5, 1, '2025-05-15 19:50:01', '2025-05-15 19:50:01'),
(55, 'O2', 'Ergonomic Executive Workspace', 'Contemporary office setup featuring an electric standing desk with memory settings, ergonomic chair with lumbar support, and modular storage solutions with cable management system.', 6800.00, 'Modern', 'Office', 'photos/1747338946_O1.JPG', '2025-05-15', 5, 1, '2025-05-15 19:55:46', '2025-05-15 19:55:46'),
(56, 'O4', 'Urban Loft Office Collection', 'Industrial workspace featuring reclaimed wood and steel desk with built-in power outlets, pipe-frame bookshelves, and adjustable task lighting with exposed bulbs.', 4500.00, 'Industrial', 'Office', 'photos/1747339037_O3.JPG', '2025-05-15', 6, 1, '2025-05-15 19:57:17', '2025-05-15 19:57:17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'lora', 'lora@gmail.com', '$2y$10$zqpBMx4urTNRVmeKjxjNu.vHlILeVsP5LL9iEz9XniAApQvMtG/k6', 'customer', '2025-04-11 16:45:42', '2025-04-11 16:45:42'),
(3, 'fatima', 'fatima@gmail.com', '$2y$10$8fD6FSwf93Uk/sJ/hQ2rfujpbxKRzXvRV0yZVL4YbAQJ.gA7AYH9q', 'customer', '2025-04-16 11:02:05', '2025-04-16 11:02:05'),
(4, 'nancy', 'nancy@gmail.com', '$2y$10$z5MPawDycUzhZuHZ7tA5xu4W8sbMbskv2J5istFk3RFLjqh9B6/Aq', 'customer', '2025-04-20 11:15:53', '2025-04-20 11:15:53'),
(5, 'carl', 'carldash22@gmail.com', '$2y$10$aIyT8ybbuFqQll4T9Ln3pup/MIdpdaV2UjVBRzrtHP787pe4SjiT2', 'customer', '2025-04-20 11:38:01', '2025-04-20 11:38:01'),
(6, 'celinedion', 'celindion@gmail.com', '$2y$10$8TI42wnpI6F6gY.dLGO8/OwTBN6h9s65MzsAqouuLv1mnj.1XhnoS', 'customer', '2025-04-20 13:55:22', '2025-04-20 13:55:22'),
(7, 'wael', 'waelmortada@gmail.com', '$2y$10$jgVqW5S65WXitmn/CmJJZudpxNLtnwlwc9ZXOr4ilK/xr1vhlBCeG', 'customer', '2025-04-20 14:04:00', '2025-04-20 14:04:00'),
(8, 'admin', 'adminexample@gmail.com', '$2y$10$ENrDNnJXZ0G1BT3Jr5zx1e0sq.w6mqfvLkZcO8qRLfdQ3tDFpDgc6', 'admin', '2025-04-24 20:55:18', '2025-04-24 20:55:18'),
(9, 'istikbal', 'istikbal@gmail.com', '$2y$10$gdRuCHPX43Zu.pHm6hJwIOGrmt6SaGI9x5UI0Pqb.r5tA8.UIszTG', 'companies', '2025-04-25 18:58:18', '2025-04-25 18:58:18'),
(10, 'zymta', 'zymta@gmail.com', '$2y$10$6DsKv0gNp0Ju2y8Mkd81OOsftv9aYLGaZoF3Amdmajfie0AAVSCgu', 'companies', '2025-04-25 19:59:27', '2025-04-25 19:59:27'),
(11, 'madame coco ', 'madamecoco@gmail.com', '$2y$10$VkDHTSPoRkZgv9VxLkmFB.zTGnCWxMMyeXuCLax.2b8/.1YpmKJJy', 'companies', '2025-04-25 20:43:06', '2025-04-25 20:43:06'),
(12, 'aihome', 'aihome@gmail.com', '$2y$10$lc8JE.sECehifKBfqGz1TeyAQ8ic13Cl/nZNaT523hlCEIWmMMssa', 'companies', '2025-04-25 21:04:30', '2025-04-25 21:04:30'),
(13, 'zoo', 'zoo22@gmail.com', '$2y$10$7DyiFOXddTn1k4F6BeVwB.9KFBtl.ZOKH7nwImghuOEhaIbOS3MqG', 'companies', '2025-04-25 21:20:23', '2025-04-25 21:20:23'),
(14, 'sandi', 'sandisaid@gmail.com', '$2y$10$.hjt4/.bW5SIu8eM905PpeYO7WtTUc5a8vvSOm3cDcjrbty3mz36a', 'customer', '2025-04-30 21:32:48', '2025-04-30 21:32:48'),
(15, 'nour', 'nourobeid@gmail.com', '$2y$10$ix/LGKbN4xTu4LAoFvYVvem.OfgPZkAC9nUCzbd1KAPYtYMB5CS0S', 'customer', '2025-05-03 06:22:00', '2025-05-03 06:22:00'),
(16, 'nesrine', 'nesrinejamal@gmail.com', '$2y$10$6OHdiNtyEmVGKTR1GIx4yeWl5yrA6jzfWabsQgvCzlx2k5a8Vo5P6', 'customer', '2025-05-06 17:32:26', '2025-05-06 17:32:26'),
(17, 'menesa', 'menesakaram@gmail.com', '$2y$10$0TnYRG//xsSzomemxTNI2e5fNMFo9jDJlaMqmcfKXBwZVg8r.Gb/K', 'customer', '2025-05-11 20:09:45', '2025-05-11 20:09:45'),
(18, 'najwa', 'najwahamzeh@gmail.com', '$2y$10$6Rq58.Ndz/rKeYOavOA9duU3j3yhroce3NpVI/jqoTLmWXQJPLkSa', 'customer', '2025-05-11 20:11:46', '2025-05-11 20:11:46'),
(19, 'ikea', 'ikea@gmail.com', '$2y$10$24GfnbhwgPYVGv9OcLJOPOVBj2yp9R5a4DAKr6sqC.bDyj.qcUqLe', 'companies', '2025-05-13 16:15:08', '2025-05-13 16:15:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `password_reset`
--
ALTER TABLE `password_reset`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`) USING BTREE,
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_reset`
--
ALTER TABLE `password_reset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `password_reset`
--
ALTER TABLE `password_reset`
  ADD CONSTRAINT `password_reset_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE products ADD COLUMN size VARCHAR(50) AFTER category;

--
-- Table structure for table `custom_requests`
--

CREATE TABLE `custom_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `product_type` varchar(100) NOT NULL,
  `style` varchar(100) NOT NULL,
  `material` varchar(100) NOT NULL,
  `wood_type` varchar(100) DEFAULT NULL,
  `fabric_type` varchar(100) DEFAULT NULL,
  `color` varchar(100) NOT NULL,
  `finish_type` varchar(100) NOT NULL,
  `dimensions` varchar(100) NOT NULL,
  `add_ons` text DEFAULT NULL,
  `special_requests` text DEFAULT NULL,
  `budget` decimal(10,2) NOT NULL,
  `estimated_price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','in_progress','completed','rejected') NOT NULL DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `custom_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- Run this in your database to add the payment_status column
ALTER TABLE `custom_requests` 
ADD COLUMN IF NOT EXISTS `payment_status` ENUM('pending', 'paid') NOT NULL DEFAULT 'pending' AFTER `status`;

-- Update the status column to include all options
ALTER TABLE `custom_requests` 
MODIFY COLUMN `status` ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending';

ALTER TABLE `custom_requests` 
ADD COLUMN `address` varchar(255) DEFAULT NULL AFTER `payment_status`,
ADD COLUMN `city` varchar(100) DEFAULT NULL AFTER `address`,
ADD COLUMN `zip_code` varchar(20) DEFAULT NULL AFTER `city`,
ADD COLUMN `governorate` varchar(100) DEFAULT NULL AFTER `zip_code`,
ADD COLUMN `phone` varchar(20) DEFAULT NULL AFTER `governorate`,
ADD COLUMN `latitude` decimal(10,6) DEFAULT NULL AFTER `phone`,
ADD COLUMN `longitude` decimal(10,6) DEFAULT NULL AFTER `latitude`,
ADD COLUMN `payment_method` varchar(50) DEFAULT NULL AFTER `longitude`,
ADD COLUMN `card_name` varchar(100) DEFAULT NULL AFTER `payment_method`,
ADD COLUMN `card_number` varchar(25) DEFAULT NULL AFTER `card_name`,
ADD COLUMN `card_expiry` varchar(10) DEFAULT NULL AFTER `card_number`,
ADD COLUMN `order_id` varchar(20) DEFAULT NULL AFTER `id`,
ADD UNIQUE KEY `order_id` (`order_id`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
