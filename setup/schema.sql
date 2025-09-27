-- Bu dosya kurulum.php tarafından okunur.
-- Veritabanı şemasının en güncel halidir.

CREATE TABLE IF NOT EXISTS `kullanicilar` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ad_soyad` varchar(255) NOT NULL,
  `kullanici_adi` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefon` varchar(20) DEFAULT NULL,
  `sifre` varchar(255) NOT NULL,
  `rol` enum('admin','bas_ogretmen','ogretmen','veli','ogrenci') NOT NULL,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `telegram_chat_id` varchar(255) DEFAULT NULL,
  `telegram_baglanti_kodu` varchar(50) DEFAULT NULL,
  `gecici_sifre` varchar(255) DEFAULT NULL,
  `zoom_id` varchar(255) DEFAULT NULL,
  `zoom_parola` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kullanici_adi_UNIQUE` (`kullanici_adi`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Diğer tablolar aynı kalır...
CREATE TABLE IF NOT EXISTS `branslar` (
  `id` int NOT NULL AUTO_INCREMENT,
  `brans_adi` varchar(255) NOT NULL,
  `brans_kisaltma` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `siniflar` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sinif_adi` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `odevler` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ogretmen_id` int NOT NULL,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sinif_id` int NOT NULL,
  `brans_id` int NOT NULL,
  `baslik` varchar(255) NOT NULL,
  `aciklama` text,
  `odev_kodu` varchar(50) DEFAULT NULL,
  `teslim_tarihi` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `odev_kodu_UNIQUE` (`odev_kodu`),
  KEY `fk_odevler_ogretmen_idx` (`ogretmen_id`),
  KEY `fk_odevler_sinif_idx` (`sinif_id`),
  KEY `fk_odevler_brans_idx` (`brans_id`),
  CONSTRAINT `fk_odevler_brans` FOREIGN KEY (`brans_id`) REFERENCES `branslar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_odevler_ogretmen` FOREIGN KEY (`ogretmen_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_odevler_sinif` FOREIGN KEY (`sinif_id`) REFERENCES `siniflar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `odev_teslimleri` (
  `id` int NOT NULL AUTO_INCREMENT,
  `odev_id` int NOT NULL,
  `ogrenci_id` int NOT NULL,
  `teslim_tarihi` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `durum` enum('Teslim Edildi - Veli Onayı Bekliyor','Teslim Edildi - Veli Tarafından Onaylandı','Notlandırıldı','Reddedildi','Teslim Etmedi') NOT NULL DEFAULT 'Teslim Etmedi',
  `notu` int DEFAULT NULL,
  `ogretmen_yorumu` text,
  `veli_onay_tarihi` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `odev_ogrenci_unique` (`odev_id`,`ogrenci_id`),
  KEY `fk_teslim_ogrenci_idx` (`ogrenci_id`),
  CONSTRAINT `fk_teslim_odev` FOREIGN KEY (`odev_id`) REFERENCES `odevler` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_teslim_ogrenci` FOREIGN KEY (`ogrenci_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `odev_dosyalari` (
  `id` int NOT NULL AUTO_INCREMENT,
  `teslim_id` int NOT NULL,
  `dosya_yolu` varchar(255) NOT NULL,
  `yukleme_tarihi` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `yukleyen_kullanici_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_dosya_teslim_idx` (`teslim_id`),
  KEY `fk_dosya_kullanici_idx` (`yukleyen_kullanici_id`),
  CONSTRAINT `fk_dosya_kullanici` FOREIGN KEY (`yukleyen_kullanici_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_dosya_teslim` FOREIGN KEY (`teslim_id`) REFERENCES `odev_teslimleri` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `anketler` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ogretmen_id` int NOT NULL,
  `sinif_id` int NOT NULL,
  `anket_basligi` varchar(255) NOT NULL,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_anket_ogretmen_idx` (`ogretmen_id`),
  KEY `fk_anket_sinif_idx` (`sinif_id`),
  CONSTRAINT `fk_anket_ogretmen` FOREIGN KEY (`ogretmen_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_anket_sinif` FOREIGN KEY (`sinif_id`) REFERENCES `siniflar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `anket_secenekleri` (
  `id` int NOT NULL AUTO_INCREMENT,
  `anket_id` int NOT NULL,
  `secenek_metni` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_secenek_anket_idx` (`anket_id`),
  CONSTRAINT `fk_secenek_anket` FOREIGN KEY (`anket_id`) REFERENCES `anketler` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `anket_cevaplari` (
  `id` int NOT NULL AUTO_INCREMENT,
  `anket_id` int NOT NULL,
  `secilen_secenek_id` int NOT NULL,
  `ogrenci_id` int NOT NULL,
  `cevap_tarihi` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `anket_ogrenci_unique` (`anket_id`,`ogrenci_id`),
  KEY `fk_cevap_secenek_idx` (`secilen_secenek_id`),
  KEY `fk_cevap_ogrenci_idx` (`ogrenci_id`),
  CONSTRAINT `fk_cevap_anket` FOREIGN KEY (`anket_id`) REFERENCES `anketler` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cevap_ogrenci` FOREIGN KEY (`ogrenci_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cevap_secenek` FOREIGN KEY (`secilen_secenek_id`) REFERENCES `anket_secenekleri` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `ogrenci_sinif_iliskisi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ogrenci_id` int NOT NULL,
  `sinif_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ogrenci_sinif_unique` (`ogrenci_id`,`sinif_id`),
  KEY `fk_osi_ogrenci_idx` (`ogrenci_id`),
  KEY `fk_osi_sinif_idx` (`sinif_id`),
  CONSTRAINT `fk_osi_ogrenci` FOREIGN KEY (`ogrenci_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_osi_sinif` FOREIGN KEY (`sinif_id`) REFERENCES `siniflar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `ogretmen_brans_iliskisi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ogretmen_id` int NOT NULL,
  `brans_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ogretmen_brans_unique` (`ogretmen_id`,`brans_id`),
  KEY `fk_obi_ogretmen_idx` (`ogretmen_id`),
  KEY `fk_obi_brans_idx` (`brans_id`),
  CONSTRAINT `fk_obi_brans` FOREIGN KEY (`brans_id`) REFERENCES `branslar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_obi_ogretmen` FOREIGN KEY (`ogretmen_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `ogretmen_sinif_iliskisi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ogretmen_id` int NOT NULL,
  `sinif_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ogretmen_sinif_unique` (`ogretmen_id`,`sinif_id`),
  KEY `fk_otsi_ogretmen_idx` (`ogretmen_id`),
  KEY `fk_otsi_sinif_idx` (`sinif_id`),
  CONSTRAINT `fk_otsi_ogretmen` FOREIGN KEY (`ogretmen_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_otsi_sinif` FOREIGN KEY (`sinif_id`) REFERENCES `siniflar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `veli_ogrenci_iliskisi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `veli_id` int NOT NULL,
  `ogrenci_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `veli_ogrenci_unique` (`veli_id`,`ogrenci_id`),
  KEY `fk_voi_veli_idx` (`veli_id`),
  KEY `fk_voi_ogrenci_idx` (`ogrenci_id`),
  CONSTRAINT `fk_voi_ogrenci` FOREIGN KEY (`ogrenci_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_voi_veli` FOREIGN KEY (`veli_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `ders_programi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sinif_id` int NOT NULL,
  `gun` int NOT NULL COMMENT '1: Pazartesi, ..., 7: Pazar',
  `baslangic_saati` time NOT NULL,
  `bitis_saati` time NOT NULL,
  `brans_id` int NOT NULL,
  `ogretmen_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_program_sinif_idx` (`sinif_id`),
  KEY `fk_program_brans_idx` (`brans_id`),
  KEY `fk_program_ogretmen_idx` (`ogretmen_id`),
  CONSTRAINT `fk_program_brans` FOREIGN KEY (`brans_id`) REFERENCES `branslar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_program_ogretmen` FOREIGN KEY (`ogretmen_id`) REFERENCES `kullanicilar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_program_sinif` FOREIGN KEY (`sinif_id`) REFERENCES `siniflar` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;