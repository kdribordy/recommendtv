-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 08, 2014 at 08:41 PM
-- Server version: 5.5.35
-- PHP Version: 5.3.10-1ubuntu3.9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `recommendtv`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(10) NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `subcategories`
--

CREATE TABLE IF NOT EXISTS `subcategories` (
  `subcategory_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(25) NOT NULL,
  PRIMARY KEY (`subcategory_id`,`category_id`),
  KEY `ix_subcategories_category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `suggestions`
--

CREATE TABLE IF NOT EXISTS `suggestions` (
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `subcategory_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`),
  KEY `ix_suggestions_category_id` (`category_id`),
  KEY `ix_suggestions_subcategory_id` (`subcategory_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` varchar(64) NOT NULL,
  `zip_code` varchar(5) NOT NULL,
  `service_id` varchar(15) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `ux_users_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_category_preferences`
--

CREATE TABLE IF NOT EXISTS `user_category_preferences` (
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `preference` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`category_id`),
  KEY `ix_user_category_references_user_id` (`user_id`),
  KEY `ix_user_category_preferences_category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_subcategory_preferences`
--

CREATE TABLE IF NOT EXISTS `user_subcategory_preferences` (
  `user_id` int(11) NOT NULL,
  `subcategory_id` int(11) NOT NULL,
  `preference` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`subcategory_id`),
  KEY `ix_user_subcategory_preferences_subcategory_id` (`subcategory_id`),
  KEY `ix_user_subcategory_preferences_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD CONSTRAINT `subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `suggestions`
--
ALTER TABLE `suggestions`
  ADD CONSTRAINT `suggestions_ibfk_2` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`subcategory_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `suggestions_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_category_preferences`
--
ALTER TABLE `user_category_preferences`
  ADD CONSTRAINT `user_category_preferences_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_category_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_subcategory_preferences`
--
ALTER TABLE `user_subcategory_preferences`
  ADD CONSTRAINT `user_subcategory_preferences_ibfk_2` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`subcategory_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_subcategory_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
