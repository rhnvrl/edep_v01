-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Anamakine: localhost:3306
-- Üretim Zamanı: 27 Eyl 2025, 17:25:42
-- Sunucu sürümü: 10.11.14-MariaDB
-- PHP Sürümü: 8.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `edep2025_odevtakip`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `anketler`
--

CREATE TABLE `anketler` (
  `id` int(11) NOT NULL,
  `ogretmen_id` int(11) NOT NULL,
  `sinif_id` int(11) NOT NULL,
  `anket_basligi` varchar(255) NOT NULL,
  `olusturma_tarihi` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `anketler`
--

INSERT INTO `anketler` (`id`, `ogretmen_id`, `sinif_id`, `anket_basligi`, `olusturma_tarihi`) VALUES
(1, 13, 1, 'anket1', '2025-09-07 22:17:04'),
(2, 13, 1, 'sdfsdf', '2025-09-08 11:00:13'),
(3, 13, 1, 'asd', '2025-09-08 12:30:29'),
(4, 13, 1, 'asdefdf dfg dfgdfg', '2025-09-08 15:34:20'),
(5, 13, 1, 'ank', '2025-09-08 15:40:33'),
(6, 13, 1, 'asd', '2025-09-10 10:43:06'),
(7, 13, 1, 'Anket', '2025-09-12 14:17:40');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `anket_cevaplari`
--

CREATE TABLE `anket_cevaplari` (
  `id` int(11) NOT NULL,
  `anket_id` int(11) NOT NULL,
  `ogrenci_id` int(11) NOT NULL,
  `secilen_secenek_id` int(11) NOT NULL,
  `cevap_tarihi` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `anket_cevaplari`
--

INSERT INTO `anket_cevaplari` (`id`, `anket_id`, `ogrenci_id`, `secilen_secenek_id`, `cevap_tarihi`) VALUES
(1, 1, 20, 1, '2025-09-07 22:18:35'),
(2, 2, 20, 3, '2025-09-08 15:44:34'),
(3, 5, 20, 10, '2025-09-08 15:44:36'),
(4, 4, 20, 8, '2025-09-08 15:44:39'),
(5, 3, 20, 5, '2025-09-08 15:44:41');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `anket_secenekleri`
--

CREATE TABLE `anket_secenekleri` (
  `id` int(11) NOT NULL,
  `anket_id` int(11) NOT NULL,
  `secenek_metni` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `anket_secenekleri`
--

INSERT INTO `anket_secenekleri` (`id`, `anket_id`, `secenek_metni`) VALUES
(1, 1, 'a'),
(2, 1, 'b'),
(3, 2, 'sdfsd'),
(4, 2, 'dfgdf'),
(5, 3, 'asd'),
(6, 3, 'dsf'),
(7, 4, '3324'),
(8, 4, '2'),
(9, 5, '1'),
(10, 5, '2'),
(11, 5, '3'),
(12, 6, 'a'),
(13, 6, 'b'),
(14, 6, 'c'),
(15, 7, 'a'),
(16, 7, 'b'),
(17, 7, 'c');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `bot_sessions`
--

CREATE TABLE `bot_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `platform` enum('telegram','whatsapp') NOT NULL,
  `platform_user_id` varchar(255) NOT NULL COMMENT 'Telegram Chat ID veya WhatsApp Telefon Numarası',
  `current_action` varchar(100) NOT NULL COMMENT 'Örn: uploading_images, awaiting_approval',
  `context_data` text DEFAULT NULL COMMENT 'JSON formatında veri, örn: {"odev_kodu": "MAT123", "teslim_id": 45}',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `bot_sessions`
--

INSERT INTO `bot_sessions` (`id`, `user_id`, `platform`, `platform_user_id`, `current_action`, `context_data`, `updated_at`) VALUES
(1, 17, 'telegram', '7584309829', 'uploading_images', '{\"odev_kodu\":\"fen28\",\"teslim_id\":9}', '2025-09-09 18:37:52');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `branslar`
--

CREATE TABLE `branslar` (
  `id` int(11) NOT NULL,
  `brans_adi` varchar(100) NOT NULL,
  `brans_kisaltma` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `branslar`
--

INSERT INTO `branslar` (`id`, `brans_adi`, `brans_kisaltma`) VALUES
(1, 'Matematik', 'MAT'),
(2, 'Türkçe', 'TUR'),
(4, 'Fen Bilimleri', 'FEN'),
(5, 'T.C. İnkılap Tarihi ve Atatürkçülük', 'INK'),
(6, 'Din Kültürü ve Ahlâk Bilgisi', 'DIN'),
(7, 'İngilizce', 'ING'),
(9, 'Rehberlik', 'REH');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ders_programi`
--

CREATE TABLE `ders_programi` (
  `id` int(11) NOT NULL,
  `sinif_id` int(11) NOT NULL,
  `gun` int(11) NOT NULL COMMENT '1: Pazartesi, 2: Salı, ..., 7: Pazar',
  `baslangic_saati` time NOT NULL,
  `bitis_saati` time NOT NULL,
  `brans_id` int(11) NOT NULL,
  `ogretmen_id` int(11) NOT NULL,
  `son_hatirlatma_tarihi` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `ders_programi`
--

INSERT INTO `ders_programi` (`id`, `sinif_id`, `gun`, `baslangic_saati`, `bitis_saati`, `brans_id`, `ogretmen_id`, `son_hatirlatma_tarihi`) VALUES
(3, 1, 1, '23:00:00', '23:50:00', 4, 13, '2025-09-22'),
(4, 1, 1, '23:50:00', '23:59:00', 4, 13, '2025-09-22'),
(5, 1, 2, '00:01:00', '00:50:00', 7, 14, '2025-09-23'),
(6, 1, 2, '00:40:00', '01:50:00', 7, 16, '2025-09-23'),
(7, 3, 3, '14:00:00', '15:00:00', 4, 13, '2025-09-24'),
(8, 3, 3, '16:00:00', '17:00:00', 7, 13, '2025-09-24'),
(9, 6, 1, '19:00:00', '20:00:00', 6, 13, '2025-09-22');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `gelen_mesajlar`
--

CREATE TABLE `gelen_mesajlar` (
  `id` int(11) NOT NULL,
  `mesaj_id` varchar(255) NOT NULL,
  `gonderen_tel` varchar(20) NOT NULL,
  `mesaj_icerigi` text NOT NULL,
  `gonderen_adi` varchar(100) DEFAULT NULL,
  `alis_tarihi` timestamp NOT NULL DEFAULT current_timestamp(),
  `okundu` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kullanicilar`
--

CREATE TABLE `kullanicilar` (
  `id` int(11) NOT NULL,
  `kullanici_adi` varchar(50) NOT NULL,
  `sifre` varchar(255) NOT NULL,
  `gecici_sifre` varchar(255) DEFAULT NULL,
  `zoom_id` varchar(255) DEFAULT NULL,
  `zoom_parola` varchar(255) DEFAULT NULL,
  `ad_soyad` varchar(100) NOT NULL,
  `rol` enum('admin','bas_ogretmen','ogretmen','veli','ogrenci') NOT NULL,
  `telefon` varchar(20) DEFAULT NULL,
  `telegram_chat_id` varchar(50) DEFAULT NULL,
  `telegram_linking_code` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `kayit_tarihi` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `kullanicilar`
--

INSERT INTO `kullanicilar` (`id`, `kullanici_adi`, `sifre`, `gecici_sifre`, `zoom_id`, `zoom_parola`, `ad_soyad`, `rol`, `telefon`, `telegram_chat_id`, `telegram_linking_code`, `email`, `kayit_tarihi`) VALUES
(1, 'admin', '$2y$10$rZKWLwQt9McU3QTntrSpqOmG.HfsXV28kI7jU0XxBFdFNZcQkYLYW', NULL, NULL, NULL, 'Erhan VAROL', 'admin', '5073771881', NULL, NULL, 'erhanvarol@gmail.com', '2025-07-26 20:18:21'),
(13, 'ogretmen1', '$2y$10$l0QdxDkq8UdvCwHPoWz//.JcrQhcfLT0O1cfnZIRfHCFhN22qjjbm', NULL, '123 456 7887', '12346s', 'Ogretmen 1', 'ogretmen', '', '', 'OGRT-37A2F1B0', 'ogretmen1@mail.com', '2025-07-29 11:14:45'),
(14, 'ogretmen2', '$2y$10$M2KEUYpOs4aQjR58xruO5.7DIFhKWumSYnkJb.QKZgPxw.Ey95aZ6', NULL, NULL, NULL, 'Ogretmen 2', 'ogretmen', '', '', 'OGRT-838C79D1', 'ogretmen2@mail.com', '2025-07-29 11:15:30'),
(15, 'basogretmen', '$2y$10$Jrl87Mos4HXJHAhiCgbHkeL15bd1Ngko5nZd.Oh/2oRLG3.i6hH5S', NULL, NULL, NULL, 'Baş Öğretmen', 'bas_ogretmen', '', '', NULL, 'basogretmen@mail.com', '2025-07-29 11:16:09'),
(16, 'ogretmen3', '$2y$10$bKO9P185WlcnJhfApCsBO.5TIqS7geECrCX2GBtcF4lXaBEWBvYDy', NULL, NULL, NULL, 'Ogretmen 3', 'ogretmen', '', '', 'OGRT-00778ABA', 'ogretmen3@mail.com', '2025-07-29 11:16:51'),
(17, 'veli1', '$2y$10$fFuvKyggAl7bqj8O/hPyautz0HsdUrA8gcxUy3SESF6m5976Z50Qy', 'G5vwC30R', NULL, NULL, 'Veli 1', 'veli', '+905073771881', '7584309829', 'VELI-C3802188', 'veli1@mail.com', '2025-07-29 11:17:35'),
(18, 'veli2', '$2y$10$L.WjDl9ug2vf9HVdKEHHxuaRXG75ysP1cY7YiUyP4.a7N813zUsaG', NULL, NULL, NULL, 'Veli 2', 'veli', '', '', 'VELI-5D6F3B37', 'veli2@mail.com', '2025-07-29 11:18:00'),
(19, 'veli3', '$2y$10$T5oQYUuoSs9BJ6Vo7g6gAuGRRDcsR9NJdwFEzL9X0O5alJf/kSMTS', NULL, NULL, NULL, 'Veli 3', 'veli', '', '', 'VELI-2FF7AF88', 'veli3@mail.com', '2025-07-29 11:18:24'),
(20, 'ogrenci1', '$2y$10$uJ2qbnjOlSL/2KO.CAScx.ZaSbZuyPDNaUj9gk2T/gsqZef4JDaHC', NULL, NULL, NULL, 'Ogrenci 1', 'ogrenci', '', '', NULL, '', '2025-07-29 11:18:49'),
(21, 'ogrenci2', '$2y$10$VPQGNstDSREPNi.TPfU7s.sEBFHmou0E9W6qGP1rPdd6zk80GbUpm', NULL, NULL, NULL, 'Ogrenci 2', 'ogrenci', '', '', NULL, '', '2025-07-29 11:19:08'),
(22, 'ogrenci3', '$2y$10$xlmYzdjqF5aLxb8F4PNhs..gCMYrrYM4ZbEbPVqSz6yy3XYO4WvTG', NULL, NULL, NULL, 'Ogrenci 3', 'ogrenci', '', '', NULL, '', '2025-07-29 11:19:30'),
(23, 'alyaturkel', '$2y$10$/AbSYpAF/.H7oF.EOa4CNeoQ8VFK5dv7p2TKmHDwOKsvo4mqDC80q', NULL, NULL, NULL, 'Alya Türkel', 'ogrenci', '', '', NULL, '', '2025-09-09 19:33:50'),
(24, 'alimithatkaratag', '$2y$10$DnBcDTSwo/yfCBywgEQtmuptrnB.IjjFtluEY/5Re00MCMtjpx74q', NULL, NULL, NULL, 'Ali Mithat Karatağ', 'ogrenci', '', '', NULL, '', '2025-09-09 19:34:25'),
(25, 'kubraturkel', '$2y$10$naPradVyUXw00DBFOTnm/uCvSwu8X6n8nCfzMZFLigZKblQ6lFGJ.', NULL, NULL, NULL, 'Kübra Türkel', 'veli', '+905313774581', '', 'VELI-2E046B35', '', '2025-09-09 19:35:22'),
(26, 'bilalkaratag', '$2y$10$b.nOeR/9X2h92Aj3cq31UOm/EgP3/ZpxbaCSoXAbANteOeCn.ozhO', NULL, NULL, NULL, 'Bilal Karatağ', 'veli', '+905438801987', '', 'VELI-94219231', '', '2025-09-09 19:36:27');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `odevler`
--

CREATE TABLE `odevler` (
  `id` int(11) NOT NULL,
  `ogretmen_id` int(11) NOT NULL,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp(),
  `sinif_id` int(11) NOT NULL,
  `brans_id` int(11) NOT NULL,
  `baslik` varchar(255) NOT NULL,
  `aciklama` text DEFAULT NULL,
  `odev_kodu` varchar(20) DEFAULT NULL,
  `verilis_tarihi` timestamp NULL DEFAULT current_timestamp(),
  `teslim_tarihi` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `odevler`
--

INSERT INTO `odevler` (`id`, `ogretmen_id`, `olusturma_tarihi`, `sinif_id`, `brans_id`, `baslik`, `aciklama`, `odev_kodu`, `verilis_tarihi`, `teslim_tarihi`) VALUES
(2, 16, '2025-09-07 17:43:51', 1, 6, 'Siyer nedir', 'Siyer nedir araştırılacak', 'DIN2', '2025-07-30 19:34:14', '2025-08-03 22:34:00'),
(25, 13, '2025-09-07 22:01:42', 1, 4, 'dna 1', 'sdfs s dsd f asd asd', 'FEN25', '2025-09-07 22:01:42', '2025-09-28 01:01:00'),
(27, 13, '2025-09-08 18:07:53', 1, 4, 'asdasd', 'adas', 'FEN27', '2025-09-08 18:07:53', '2025-09-25 21:07:00'),
(28, 13, '2025-09-08 19:55:54', 1, 4, 'aaaa', 'aaa', 'FEN28', '2025-09-08 19:55:54', '2025-09-25 22:55:00'),
(29, 13, '2025-09-09 10:26:32', 1, 4, 'Homework 1', 'page 61', 'FEN29', '2025-09-09 10:26:32', '2025-09-21 23:59:00'),
(30, 13, '2025-09-09 10:30:41', 1, 4, 'Homework 2', 'page 61 - 65', 'FEN30', '2025-09-09 10:30:41', '2025-09-28 23:59:00'),
(32, 13, '2025-09-09 19:47:54', 3, 1, 'Toplama', 'Toplayın bre...\r\n\r\n56454646545 + 56459465464 = ?', 'MAT32', '2025-09-09 19:47:54', '2025-09-14 23:47:00'),
(35, 13, '2025-09-09 19:53:54', 3, 4, 'Toplama', '1+1 = ?', 'FEN35', '2025-09-09 19:53:54', '2025-09-24 22:53:00'),
(37, 13, '2025-09-09 19:57:37', 3, 1, 'çıkarma', '2-1 = ?', 'MAT37', '2025-09-09 19:57:37', '2025-09-17 22:57:00'),
(41, 13, '2025-09-10 09:01:46', 1, 4, 'aabb', 'cc', 'FEN41', '2025-09-10 09:01:46', '2025-09-19 12:01:00'),
(43, 13, '2025-09-10 09:12:22', 3, 4, 'asdasd', 'asdasdas', 'FEN43', '2025-09-10 09:12:22', '2025-09-25 12:12:00');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `odev_dosyalari`
--

CREATE TABLE `odev_dosyalari` (
  `id` int(11) NOT NULL,
  `teslim_id` int(11) NOT NULL,
  `dosya_yolu` varchar(255) NOT NULL,
  `yukleyen_kullanici_id` int(11) NOT NULL,
  `yukleme_tarihi` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `odev_dosyalari`
--

INSERT INTO `odev_dosyalari` (`id`, `teslim_id`, `dosya_yolu`, `yukleyen_kullanici_id`, `yukleme_tarihi`) VALUES
(3, 2, 'uploads/odevler/tg_2_1753984836_688baf447cc53.jpg', 17, '2025-07-31 18:00:36'),
(4, 2, 'uploads/odevler/tg_2_1753984837_688baf4564593.jpg', 17, '2025-07-31 18:00:37'),
(5, 2, 'uploads/odevler/1754820546_68986fc264f6b_vylogoSade.png', 17, '2025-08-10 10:09:06'),
(6, 2, 'uploads/odevler/1754820546_68986fc265616_asd.png', 17, '2025-08-10 10:09:06'),
(16, 6, 'uploads/odevler/1757283463_68be04873f57e_asd.png', 17, '2025-09-07 22:17:43'),
(17, 6, 'uploads/odevler/1757283463_68be04873f78b_EgePasta7yas.jpg', 17, '2025-09-07 22:17:43'),
(18, 8, 'uploads/odevler/tg_8_1757414232_68c003581f507.jpg', 17, '2025-09-09 10:37:12'),
(19, 10, 'uploads/odevler/odev_10_68c430a8505e17.34403396.png', 17, '2025-09-12 14:39:36'),
(20, 11, 'uploads/odevler/odev_11_68c433b7c07329.78276056.png', 20, '2025-09-12 14:52:39'),
(21, 11, 'uploads/odevler/odev_11_68c433b7c0be25.45820051.png', 20, '2025-09-12 14:52:39'),
(22, 12, 'uploads/odevler/odev_12_68c4353adfee96.29450664.jpg', 17, '2025-09-12 14:59:06'),
(23, 12, 'uploads/odevler/odev_12_68c4353ae02449.42713898.jpg', 17, '2025-09-12 14:59:06'),
(24, 12, 'uploads/odevler/odev_12_68c4353ae05018.05750149.jpg', 17, '2025-09-12 14:59:06');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `odev_teslimleri`
--

CREATE TABLE `odev_teslimleri` (
  `id` int(11) NOT NULL,
  `odev_id` int(11) NOT NULL,
  `ogrenci_id` int(11) NOT NULL,
  `teslim_tarihi` timestamp NULL DEFAULT NULL,
  `veli_onay_tarihi` datetime DEFAULT NULL,
  `puan` int(11) DEFAULT NULL,
  `ogretmen_notu` text DEFAULT NULL,
  `durum` enum('Teslim Edildi - Veli Onayı Bekliyor','Teslim Edildi - Veli Tarafından Onaylandı','Notlandırıldı','Reddedildi','Teslim Etmedi') NOT NULL DEFAULT 'Teslim Etmedi',
  `notu` int(11) DEFAULT NULL,
  `ogretmen_yorumu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `odev_teslimleri`
--

INSERT INTO `odev_teslimleri` (`id`, `odev_id`, `ogrenci_id`, `teslim_tarihi`, `veli_onay_tarihi`, `puan`, `ogretmen_notu`, `durum`, `notu`, `ogretmen_yorumu`) VALUES
(2, 2, 20, '2025-07-31 18:00:36', NULL, NULL, NULL, 'Teslim Edildi - Veli Tarafından Onaylandı', NULL, NULL),
(6, 25, 20, '2025-09-07 22:17:43', '2025-09-08 01:17:46', NULL, NULL, 'Notlandırıldı', 100, 'aferin'),
(8, 30, 20, '2025-09-09 10:37:12', NULL, NULL, NULL, 'Teslim Edildi - Veli Onayı Bekliyor', NULL, NULL),
(9, 28, 20, NULL, NULL, NULL, NULL, 'Teslim Edildi - Veli Onayı Bekliyor', NULL, NULL),
(10, 37, 20, '2025-09-12 14:39:36', '2025-09-12 17:39:40', NULL, NULL, 'Notlandırıldı', 100, 'aferin'),
(11, 43, 20, NULL, '2025-09-12 17:58:33', NULL, NULL, 'Notlandırıldı', 60, ''),
(12, 32, 20, '2025-09-12 14:59:06', '2025-09-12 17:59:10', NULL, NULL, 'Notlandırıldı', 90, '');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ogrenci_sinif_iliskisi`
--

CREATE TABLE `ogrenci_sinif_iliskisi` (
  `ogrenci_id` int(11) NOT NULL,
  `sinif_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `ogrenci_sinif_iliskisi`
--

INSERT INTO `ogrenci_sinif_iliskisi` (`ogrenci_id`, `sinif_id`) VALUES
(20, 3),
(21, 1),
(22, 2),
(23, 3),
(23, 5),
(24, 3);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ogretmen_brans_iliskisi`
--

CREATE TABLE `ogretmen_brans_iliskisi` (
  `ogretmen_id` int(11) NOT NULL,
  `brans_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `ogretmen_brans_iliskisi`
--

INSERT INTO `ogretmen_brans_iliskisi` (`ogretmen_id`, `brans_id`) VALUES
(13, 1),
(13, 4),
(13, 6),
(13, 7),
(14, 1),
(16, 2),
(16, 5),
(16, 6);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ogretmen_sinif_iliskisi`
--

CREATE TABLE `ogretmen_sinif_iliskisi` (
  `ogretmen_id` int(11) NOT NULL,
  `sinif_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `ogretmen_sinif_iliskisi`
--

INSERT INTO `ogretmen_sinif_iliskisi` (`ogretmen_id`, `sinif_id`) VALUES
(13, 1),
(13, 2),
(13, 3),
(14, 2),
(16, 1),
(16, 2);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `siniflar`
--

CREATE TABLE `siniflar` (
  `id` int(11) NOT NULL,
  `sinif_adi` varchar(100) NOT NULL,
  `seviye` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `siniflar`
--

INSERT INTO `siniflar` (`id`, `sinif_adi`, `seviye`) VALUES
(1, 'A Sınıfı', 8),
(2, 'B Sınıfı', 8),
(3, 'Veletler Sınıfı', 8),
(4, '8TMF1', 8),
(5, '8SOS1', 8),
(6, '8ING1', 8);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `veli_ogrenci_iliskisi`
--

CREATE TABLE `veli_ogrenci_iliskisi` (
  `veli_id` int(11) NOT NULL,
  `ogrenci_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `veli_ogrenci_iliskisi`
--

INSERT INTO `veli_ogrenci_iliskisi` (`veli_id`, `ogrenci_id`) VALUES
(17, 20),
(18, 21),
(25, 23),
(26, 24);

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `anketler`
--
ALTER TABLE `anketler`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ogretmen_id` (`ogretmen_id`),
  ADD KEY `sinif_id` (`sinif_id`);

--
-- Tablo için indeksler `anket_cevaplari`
--
ALTER TABLE `anket_cevaplari`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `anket_id` (`anket_id`,`ogrenci_id`),
  ADD KEY `ogrenci_id` (`ogrenci_id`),
  ADD KEY `secilen_secenek_id` (`secilen_secenek_id`);

--
-- Tablo için indeksler `anket_secenekleri`
--
ALTER TABLE `anket_secenekleri`
  ADD PRIMARY KEY (`id`),
  ADD KEY `anket_id` (`anket_id`);

--
-- Tablo için indeksler `bot_sessions`
--
ALTER TABLE `bot_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_platform_unique` (`user_id`,`platform`),
  ADD KEY `fk_session_user_idx` (`user_id`);

--
-- Tablo için indeksler `branslar`
--
ALTER TABLE `branslar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `brans_adi` (`brans_adi`),
  ADD UNIQUE KEY `brans_kisaltma` (`brans_kisaltma`);

--
-- Tablo için indeksler `ders_programi`
--
ALTER TABLE `ders_programi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_program_sinif_idx` (`sinif_id`),
  ADD KEY `fk_program_brans_idx` (`brans_id`),
  ADD KEY `fk_program_ogretmen_idx` (`ogretmen_id`);

--
-- Tablo için indeksler `gelen_mesajlar`
--
ALTER TABLE `gelen_mesajlar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mesaj_id` (`mesaj_id`);

--
-- Tablo için indeksler `kullanicilar`
--
ALTER TABLE `kullanicilar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kullanici_adi` (`kullanici_adi`),
  ADD UNIQUE KEY `telegram_linking_code` (`telegram_linking_code`);

--
-- Tablo için indeksler `odevler`
--
ALTER TABLE `odevler`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `odev_kodu` (`odev_kodu`),
  ADD KEY `ogretmen_id` (`ogretmen_id`),
  ADD KEY `sinif_id` (`sinif_id`),
  ADD KEY `fk_odevler_branslar_idx` (`brans_id`);

--
-- Tablo için indeksler `odev_dosyalari`
--
ALTER TABLE `odev_dosyalari`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teslim_id` (`teslim_id`),
  ADD KEY `yukleyen_kullanici_id` (`yukleyen_kullanici_id`);

--
-- Tablo için indeksler `odev_teslimleri`
--
ALTER TABLE `odev_teslimleri`
  ADD PRIMARY KEY (`id`),
  ADD KEY `odev_id` (`odev_id`),
  ADD KEY `ogrenci_id` (`ogrenci_id`);

--
-- Tablo için indeksler `ogrenci_sinif_iliskisi`
--
ALTER TABLE `ogrenci_sinif_iliskisi`
  ADD PRIMARY KEY (`ogrenci_id`,`sinif_id`),
  ADD KEY `sinif_id` (`sinif_id`);

--
-- Tablo için indeksler `ogretmen_brans_iliskisi`
--
ALTER TABLE `ogretmen_brans_iliskisi`
  ADD PRIMARY KEY (`ogretmen_id`,`brans_id`),
  ADD KEY `brans_id` (`brans_id`);

--
-- Tablo için indeksler `ogretmen_sinif_iliskisi`
--
ALTER TABLE `ogretmen_sinif_iliskisi`
  ADD PRIMARY KEY (`ogretmen_id`,`sinif_id`),
  ADD KEY `sinif_id` (`sinif_id`);

--
-- Tablo için indeksler `siniflar`
--
ALTER TABLE `siniflar`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `veli_ogrenci_iliskisi`
--
ALTER TABLE `veli_ogrenci_iliskisi`
  ADD PRIMARY KEY (`veli_id`,`ogrenci_id`),
  ADD KEY `ogrenci_id` (`ogrenci_id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `anketler`
--
ALTER TABLE `anketler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Tablo için AUTO_INCREMENT değeri `anket_cevaplari`
--
ALTER TABLE `anket_cevaplari`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Tablo için AUTO_INCREMENT değeri `anket_secenekleri`
--
ALTER TABLE `anket_secenekleri`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Tablo için AUTO_INCREMENT değeri `bot_sessions`
--
ALTER TABLE `bot_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `branslar`
--
ALTER TABLE `branslar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Tablo için AUTO_INCREMENT değeri `ders_programi`
--
ALTER TABLE `ders_programi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Tablo için AUTO_INCREMENT değeri `gelen_mesajlar`
--
ALTER TABLE `gelen_mesajlar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `kullanicilar`
--
ALTER TABLE `kullanicilar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Tablo için AUTO_INCREMENT değeri `odevler`
--
ALTER TABLE `odevler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- Tablo için AUTO_INCREMENT değeri `odev_dosyalari`
--
ALTER TABLE `odev_dosyalari`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Tablo için AUTO_INCREMENT değeri `odev_teslimleri`
--
ALTER TABLE `odev_teslimleri`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Tablo için AUTO_INCREMENT değeri `siniflar`
--
ALTER TABLE `siniflar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `anketler`
--
ALTER TABLE `anketler`
  ADD CONSTRAINT `anketler_ibfk_1` FOREIGN KEY (`ogretmen_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `anketler_ibfk_2` FOREIGN KEY (`sinif_id`) REFERENCES `siniflar` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `anket_cevaplari`
--
ALTER TABLE `anket_cevaplari`
  ADD CONSTRAINT `anket_cevaplari_ibfk_1` FOREIGN KEY (`anket_id`) REFERENCES `anketler` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `anket_cevaplari_ibfk_2` FOREIGN KEY (`ogrenci_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `anket_cevaplari_ibfk_3` FOREIGN KEY (`secilen_secenek_id`) REFERENCES `anket_secenekleri` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `anket_secenekleri`
--
ALTER TABLE `anket_secenekleri`
  ADD CONSTRAINT `anket_secenekleri_ibfk_1` FOREIGN KEY (`anket_id`) REFERENCES `anketler` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `bot_sessions`
--
ALTER TABLE `bot_sessions`
  ADD CONSTRAINT `fk_session_user` FOREIGN KEY (`user_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `ders_programi`
--
ALTER TABLE `ders_programi`
  ADD CONSTRAINT `fk_program_brans` FOREIGN KEY (`brans_id`) REFERENCES `branslar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_program_ogretmen` FOREIGN KEY (`ogretmen_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_program_sinif` FOREIGN KEY (`sinif_id`) REFERENCES `siniflar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `odevler`
--
ALTER TABLE `odevler`
  ADD CONSTRAINT `fk_odevler_branslar` FOREIGN KEY (`brans_id`) REFERENCES `branslar` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `odevler_ibfk_1` FOREIGN KEY (`ogretmen_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `odevler_ibfk_2` FOREIGN KEY (`sinif_id`) REFERENCES `siniflar` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `odev_dosyalari`
--
ALTER TABLE `odev_dosyalari`
  ADD CONSTRAINT `odev_dosyalari_ibfk_1` FOREIGN KEY (`teslim_id`) REFERENCES `odev_teslimleri` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `odev_dosyalari_ibfk_2` FOREIGN KEY (`yukleyen_kullanici_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `odev_teslimleri`
--
ALTER TABLE `odev_teslimleri`
  ADD CONSTRAINT `odev_teslimleri_ibfk_1` FOREIGN KEY (`odev_id`) REFERENCES `odevler` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `odev_teslimleri_ibfk_2` FOREIGN KEY (`ogrenci_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `ogrenci_sinif_iliskisi`
--
ALTER TABLE `ogrenci_sinif_iliskisi`
  ADD CONSTRAINT `ogrenci_sinif_iliskisi_ibfk_1` FOREIGN KEY (`ogrenci_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ogrenci_sinif_iliskisi_ibfk_2` FOREIGN KEY (`sinif_id`) REFERENCES `siniflar` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `ogretmen_brans_iliskisi`
--
ALTER TABLE `ogretmen_brans_iliskisi`
  ADD CONSTRAINT `ogretmen_brans_iliskisi_ibfk_1` FOREIGN KEY (`ogretmen_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ogretmen_brans_iliskisi_ibfk_2` FOREIGN KEY (`brans_id`) REFERENCES `branslar` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `ogretmen_sinif_iliskisi`
--
ALTER TABLE `ogretmen_sinif_iliskisi`
  ADD CONSTRAINT `ogretmen_sinif_iliskisi_ibfk_1` FOREIGN KEY (`ogretmen_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ogretmen_sinif_iliskisi_ibfk_2` FOREIGN KEY (`sinif_id`) REFERENCES `siniflar` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `veli_ogrenci_iliskisi`
--
ALTER TABLE `veli_ogrenci_iliskisi`
  ADD CONSTRAINT `veli_ogrenci_iliskisi_ibfk_1` FOREIGN KEY (`veli_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `veli_ogrenci_iliskisi_ibfk_2` FOREIGN KEY (`ogrenci_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
