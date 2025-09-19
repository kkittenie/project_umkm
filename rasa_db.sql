-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 19, 2025 at 01:24 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rasa_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id_category` int NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id_category`, `category_name`) VALUES
(1, 'Main Course'),
(2, 'Appetizer'),
(3, 'Drinks'),
(4, 'Dessert');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied') DEFAULT 'unread',
  `date_submitted` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `admin_notes` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `status`, `date_submitted`, `admin_notes`) VALUES
(1, 'sofi', 'sofi@gmail.com', 'hai', 'ppp', 'read', '2025-09-19 03:29:45', NULL),
(2, 'sofiiii', 'sofi@gmail.com', 'p', 'haii', 'unread', '2025-09-19 04:15:14', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `detail`
--

CREATE TABLE `detail` (
  `id_detail` int NOT NULL,
  `id_transaction` int NOT NULL,
  `id_product` int NOT NULL,
  `amount` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail`
--

INSERT INTO `detail` (`id_detail`, `id_transaction`, `id_product`, `amount`) VALUES
(11, 18, 47, 2),
(12, 20, 42, 2),
(13, 21, 49, 1),
(14, 25, 46, 1),
(15, 26, 47, 3),
(16, 27, 53, 1),
(17, 28, 53, 1),
(18, 29, 42, 3),
(19, 30, 47, 2),
(20, 32, 46, 1);

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` int NOT NULL,
  `stock` int NOT NULL,
  `photo` text NOT NULL,
  `id_category` int NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`id`, `name`, `price`, `stock`, `photo`, `id_category`, `description`) VALUES
(42, 'Chocolate Chip Cookies', 20000, 3, 'prod_13.jpg', 4, 'Crispy on the edges, soft in the center, and loaded with rich chocolate chips—these cookies are the perfect mix of sweetness and comfort in every bite.'),
(43, 'Raspberry Pancake', 12000, 8, 'prod-3.jpg', 4, 'Fluffy pancakes layered with juicy raspberries and a touch of sweetness, creating the perfect balance of soft, tart, and fresh flavors.'),
(44, 'Cherry Mojito', 15000, 7, 'prod8.jpg', 3, 'A refreshing mix of sweet cherry, fresh mint, and zesty lime, blended with sparkling soda for the perfect balance of fruity and minty freshness.'),
(45, 'Mie Goreng Jawa', 30000, 4, 'prod_1.jpg', 1, 'A savory Javanese-style fried noodle made with authentic spices, fresh vegetables, and a hint of sweetness. Rich in flavor and comforting in every bite, it’s the perfect meal to satisfy your craving for traditional Indonesian taste.'),
(46, 'Dimsum Mentai', 10000, 5, 'prod2.jpg', 2, 'Soft and juicy dimsum topped with creamy, savory mentai sauce, then lightly torched for a smoky finish. A perfect fusion of classic dimsum and modern flavor.'),
(47, 'Nasi Goreng Seafood', 26000, -1, 'prod-20.jpg', 1, 'A flavorful Indonesian fried rice cooked with fresh seafood, aromatic spices, and a touch of smokiness. Every bite is rich, savory, and satisfying—perfect for seafood lovers who crave an authentic taste of the sea.'),
(48, 'Cinnamon Sugar Donut', 16000, 3, 'prod-15.jpg', 4, 'Soft, fluffy, and coated in a sweet cinnamon-sugar blend, this donut delivers the perfect balance of warmth and sweetness. A classic treat that melts in your mouth with every bite.'),
(49, 'Nasi Ayam Bakar', 29000, 3, 'prod-19.jpg', 1, 'Fragrant rice served with tender grilled chicken, marinated in rich Indonesian spices and perfectly charred for a smoky, savory flavor. A wholesome and satisfying traditional favorite.'),
(50, 'Sausage Pasta', 27000, 3, 'prod-21.jpg', 2, 'Al dente pasta tossed with savory sausage and rich, flavorful sauce, creating a hearty and comforting dish in every bite.'),
(51, 'Chicken Katsu', 25000, 3, 'prod-22.jpg', 1, 'Crispy golden chicken cutlet with tender, juicy meat inside—served with a savory sauce for the perfect balance of crunch and flavor.'),
(53, 'Latte', 18000, 9, 'prod-16.jpg', 3, 'A creamy blend of rich espresso and velvety steamed milk, creating a smooth and comforting coffee classic for any time of day.'),
(54, 'Lemon Tea', 10000, 10, '1758253132_68ccd04cd8924.jpg', 3, 'A perfect blend of smooth tea and zesty lemon, giving you a light, refreshing taste in every sip. Enjoy it hot to relax or chilled for a burst of freshness—your ideal drink for any moment.');

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

CREATE TABLE `transaction` (
  `id_transaction` int NOT NULL,
  `id_user` int NOT NULL,
  `date` datetime NOT NULL,
  `total_price` int NOT NULL,
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `status` varchar(50) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transaction`
--

INSERT INTO `transaction` (`id_transaction`, `id_user`, `date`, `total_price`, `payment_method`, `status`) VALUES
(18, 23, '2025-09-16 11:15:24', 52000, 'cod', 'pending'),
(20, 23, '2025-09-16 11:20:25', 40000, 'bank_transfer', 'pending'),
(21, 23, '2025-09-16 11:24:34', 29000, 'bank_transfer', 'cancelled'),
(25, 23, '2025-09-16 12:01:43', 10000, 'bank_transfer', 'confirmed'),
(26, 23, '2025-09-16 21:12:02', 78000, 'dana', 'pending'),
(27, 23, '2025-09-16 21:13:12', 18000, 'dana', 'confirmed'),
(28, 23, '2025-09-16 21:17:51', 18000, 'bank_transfer', 'shipped'),
(29, 23, '2025-09-17 11:29:49', 60000, 'bank_transfer', 'shipped'),
(30, 23, '2025-09-18 18:46:37', 52000, 'bank_transfer', 'confirmed'),
(32, 23, '2025-09-18 21:27:19', 10000, 'bank_transfer', 'processing');

--
-- Triggers `transaction`
--
DELIMITER $$
CREATE TRIGGER `transaction_after_confirm` AFTER UPDATE ON `transaction` FOR EACH ROW BEGIN
  IF OLD.status = 'pending' AND NEW.status = 'confirmed' THEN
    UPDATE product p
    JOIN detail d ON p.id = d.id_product
    SET p.stock = p.stock - d.amount
    WHERE d.id_transaction = NEW.id_transaction;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `phone`, `email`, `address`, `password`, `role`, `profile_picture`) VALUES
(9, 'admin', 'admin', '123456', 'admin@gmail.com', '', '111111', 'admin', NULL),
(23, 'Safirah Almira', 'sofi', '0882000768044', 'sofi@gmail.com', 'Jalan Dr. Sutomo', '111111', 'user', 'uploads/profile_pictures/profile_23_1758204901.jpg'),
(24, 'adinda', 'adinda', '123456789', 'adinda@gmail.com', 'Jalan jalan', '222222', 'user', NULL),
(25, 'Cyra Ghasanna Nuraqilah Yusup', 'cyra', '12345131782', 'cyra@gmail.com', 'test', 'reset_08b9d8ec', 'user', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id_category`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `detail`
--
ALTER TABLE `detail`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_transaksi` (`id_transaction`),
  ADD KEY `id_produk` (`id_product`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `price` (`price`),
  ADD KEY `stock` (`stock`),
  ADD KEY `id_category` (`id_category`),
  ADD KEY `price_2` (`price`);

--
-- Indexes for table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`id_transaction`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_user_2` (`id_user`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id_category` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `detail`
--
ALTER TABLE `detail`
  MODIFY `id_detail` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `transaction`
--
ALTER TABLE `transaction`
  MODIFY `id_transaction` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail`
--
ALTER TABLE `detail`
  ADD CONSTRAINT `detail_ibfk_1` FOREIGN KEY (`id_transaction`) REFERENCES `transaction` (`id_transaction`),
  ADD CONSTRAINT `detail_ibfk_2` FOREIGN KEY (`id_product`) REFERENCES `product` (`id`);

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`id_category`) REFERENCES `category` (`id_category`);

--
-- Constraints for table `transaction`
--
ALTER TABLE `transaction`
  ADD CONSTRAINT `transaction_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
