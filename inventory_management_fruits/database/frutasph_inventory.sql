-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 04, 2026 at 02:55 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `frutasph_inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `monthly_sales`
--

CREATE TABLE `monthly_sales` (
  `id` int(11) NOT NULL,
  `month` varchar(10) NOT NULL,
  `sales` decimal(12,2) DEFAULT 0.00,
  `expenses` decimal(12,2) DEFAULT 0.00,
  `year` int(11) DEFAULT 2025
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `monthly_sales`
--

INSERT INTO `monthly_sales` (`id`, `month`, `sales`, `expenses`, `year`) VALUES
(1, 'Mar', 42500.00, 24000.00, 2025),
(2, 'Apr', 58900.00, 31000.00, 2025),
(3, 'May', 71200.00, 38500.00, 2025),
(4, 'Jun', 65800.00, 35000.00, 2025),
(5, 'Jul', 83400.00, 44000.00, 2025),
(6, 'Aug', 91200.00, 49000.00, 2025);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `origin` varchar(100) DEFAULT NULL,
  `unit` varchar(20) DEFAULT 'kg',
  `price` decimal(10,2) NOT NULL,
  `cost` decimal(10,2) DEFAULT 0.00,
  `stock` int(11) DEFAULT 0,
  `min_stock` int(11) DEFAULT 0,
  `description` text DEFAULT NULL,
  `season` varchar(50) DEFAULT NULL,
  `image` varchar(10) DEFAULT '?',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `origin`, `unit`, `price`, `cost`, `stock`, `min_stock`, `description`, `season`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Carabao Mango', 'Mango', 'Guimaras, Iloilo', 'kg', 120.00, 70.00, 245, 50, 'World-famous sweet mango variety', 'Mar–Jun', '🥭', '2026-09-04 09:02:58', '2026-09-04 09:02:58'),
(2, 'Lakatan Banana', 'Banana', 'Davao del Norte', 'bunch', 85.00, 45.00, 312, 80, 'Sweet, aromatic yellow banana', 'Year-round', '🍌', '2026-09-04 09:02:58', '2026-09-04 09:02:58'),
(3, 'Philippine Pineapple', 'Pineapple', 'Bukidnon', 'pc', 65.00, 30.00, 178, 40, 'Juicy, sweet Del Monte pineapple', 'Year-round', '🍍', '2026-09-04 09:02:58', '2026-09-04 09:02:58'),
(4, 'Rambutan', 'Exotic', 'Camiguin', 'kg', 95.00, 55.00, 89, 30, 'Red spiky tropical fruit', 'Jul–Oct', '🔴', '2026-09-04 09:02:58', '2026-09-04 09:02:58'),
(5, 'Lanzones', 'Exotic', 'Camiguin', 'kg', 110.00, 60.00, 23, 40, 'Sweet-sour clustered fruit', 'Sep–Nov', '🟡', '2026-09-04 09:02:58', '2026-09-04 09:02:58'),
(6, 'Durian', 'Exotic', 'Davao City', 'pc', 350.00, 180.00, 42, 15, 'King of fruits, strong aroma', 'Aug–Nov', '🟤', '2026-09-04 09:02:58', '2026-09-04 09:02:58'),
(7, 'Papaya', 'Papaya', 'Laguna', 'pc', 55.00, 25.00, 156, 50, 'Orange flesh, sweet tropical papaya', 'Year-round', '🍈', '2026-09-04 09:02:58', '2026-09-04 09:02:58'),
(8, 'Calamansi', 'Citrus', 'Batangas', 'kg', 75.00, 35.00, 800, 30, 'Philippine lime, sour citrus', 'Year-round', '🟢', '2026-09-04 09:02:58', '2026-09-04 09:02:58'),
(9, 'Sineguelas', 'Exotic', 'Pangasinan', 'kg', 90.00, 50.00, 67, 25, 'Small red plum-like fruit', 'Mar–Jun', '🍑', '2026-09-04 09:02:58', '2026-09-04 09:02:58'),
(10, 'Santol', 'Exotic', 'Quezon', 'kg', 80.00, 40.00, 134, 35, 'Cotton fruit with sweet-sour flavor', 'Jun–Sep', '⚪', '2026-09-04 09:02:58', '2026-09-04 09:02:58'),
(11, 'Jackfruit', 'Exotic', 'Mindanao', 'kg', 60.00, 28.00, 98, 30, 'Giant sweet tropical fruit', 'Mar–Jun', '🟠', '2026-09-04 09:02:58', '2026-09-04 09:02:58'),
(12, 'Star Apple', 'Exotic', 'Laguna', 'pc', 45.00, 20.00, 201, 60, 'Purple milky sweet fruit', 'Dec–Mar', '⭐', '2026-09-04 09:02:58', '2026-09-04 09:02:58');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `type` enum('IN','OUT') NOT NULL,
  `qty` int(11) NOT NULL,
  `date` date DEFAULT curdate(),
  `by_user` varchar(100) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `product_id`, `type`, `qty`, `date`, `by_user`, `note`, `created_at`) VALUES
(1, 1, 'IN', 100, '2025-08-01', 'Maria Santos', 'Delivery from Guimaras', '2026-09-04 09:02:58'),
(2, 2, 'OUT', 50, '2025-08-02', 'Juan dela Cruz', 'Sold to Jollibee', '2026-09-04 09:02:58'),
(3, 3, 'OUT', 30, '2025-08-03', 'Ana Reyes', 'Retail sale', '2026-09-04 09:02:58'),
(4, 5, 'IN', 80, '2025-08-04', 'Maria Santos', 'Seasonal delivery', '2026-09-04 09:02:58'),
(5, 6, 'OUT', 12, '2025-08-05', 'Juan dela Cruz', 'Restaurant order', '2026-09-04 09:02:58'),
(6, 8, 'OUT', 40, '2025-08-06', 'Ana Reyes', 'Juice factory order', '2026-09-04 09:02:58'),
(7, 1, 'OUT', 75, '2025-08-07', 'Maria Santos', 'Export batch', '2026-09-04 09:02:58'),
(8, 4, 'IN', 60, '2025-08-08', 'Juan dela Cruz', 'Farm delivery', '2026-09-04 09:02:58'),
(9, 7, 'OUT', 25, '2025-08-09', 'Ana Reyes', 'Supermarket restock', '2026-09-04 09:02:58'),
(10, 11, 'IN', 150, '2025-08-10', 'Maria Santos', 'Bulk delivery', '2026-09-04 09:02:58');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Staff') DEFAULT 'Staff',
  `avatar` varchar(10) DEFAULT NULL,
  `joined` date DEFAULT curdate(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `avatar`, `joined`, `created_at`) VALUES
(1, 'dan arvin', 'danarvinpolintan@yahoo.com', '$2y$10$fg/pqODkMUUQijfSgk6RouR2gWYp4S1TpYSRbfXLTvU3mrs8/HDqO', 'Admin', 'DA', '2024-01-15', '2026-09-04 09:02:58'),
(6, 'dan arvin Polintan', 'danarvinflautapolintan@gmail.com', '$2y$10$CSbJTG9lPnxR3Hi612wYuuNgqPArhfURgAvwv5I2MflhBAMV03WGy', 'Admin', 'DA', '2026-09-04', '2026-09-04 09:30:48'),
(7, 'Admin', 'Admin@yahoo.com', '$2y$10$YMiKd4wMyle2Rsmm6NNbYOBKaGG42MZ55L5XP1jrqQ7l6kURU.iHa', 'Admin', 'A', '2026-09-04', '2026-09-04 12:55:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `monthly_sales`
--
ALTER TABLE `monthly_sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_month_year` (`month`,`year`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

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
-- AUTO_INCREMENT for table `monthly_sales`
--
ALTER TABLE `monthly_sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
