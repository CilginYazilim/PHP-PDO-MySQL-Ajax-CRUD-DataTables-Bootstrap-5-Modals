-- ===============================================================
--  PHP PDO MySQL Ajax CRUD  |  Veritabanı Kurulum Dosyası
--  cilginyazilim.com
-- ---------------------------------------------------------------
--  KURULUM (iki yoldan biri):
--    1) Terminal :  mysql -u root -p < crud.sql
--    2) phpMyAdmin > İçe Aktar > Dosya seç > crud.sql > Başlat
--
--  NOT: Bu dosya veritabanını da oluşturur, ayrıca elle
--       "crud" veritabanı açmanıza gerek yoktur.
-- ===============================================================

-- AUTO_INCREMENT sütuna 0 yazılırsa otomatik değer üretilmesin.
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
-- Tarihler Türkiye saat dilimine göre yorumlansın.
SET time_zone = "+03:00";
-- Bağlantı karakter setini utf8mb4 yap (Türkçe karakter + emoji desteği).
SET NAMES utf8mb4;

-- ---------------------------------------------------------------
--  1) Veritabanı
-- ---------------------------------------------------------------
--  utf8mb4  : Her Unicode karakteri (ç, ğ, ş, emoji...) saklayabilir.
--  unicode_ci: Sıralama/karşılaştırma dile duyarlı ve büyük-küçük
--              harf ayrımı yapmaz (ci = case insensitive).
CREATE DATABASE IF NOT EXISTS `crud`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `crud`;

-- ---------------------------------------------------------------
--  2) users tablosu
-- ---------------------------------------------------------------
--  Dosyayı tekrar çalıştırdığınızda hata almamak için önce siler.
--  DİKKAT: Bu satır mevcut verilerinizi siler!
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  -- INT UNSIGNED : Negatif olmayan tam sayı, ID için ideal.
  -- AUTO_INCREMENT : Her yeni kayıtta otomatik 1 artar.
  `id`      INT UNSIGNED NOT NULL AUTO_INCREMENT,

  `name`    VARCHAR(150) NOT NULL,
  `surname` VARCHAR(150) NOT NULL,

  -- Sadece dosya ADI tutulur (örn: "a1b2c3.png"), tam yol değil.
  -- Böylece klasör yapısı değişirse veritabanına dokunmak gerekmez.
  -- Görsel yoksa boş string ('') kalır, bu yüzden DEFAULT '' verildi.
  `image`   VARCHAR(191) NOT NULL DEFAULT '',

  -- Kayıt oluşturulduğunda o anın tarih/saati otomatik yazılır.
  `tarih`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

  -- Birincil anahtar: Her satırı benzersiz tanımlar.
  PRIMARY KEY (`id`),

  -- İNDEKSLER: Arama ve sıralama yapılan sütunlara indeks eklemek,
  -- tablo büyüdükçe sorguları kat kat hızlandırır. Bu üç sütun
  -- DataTables tarafından arama/sıralama için kullanılıyor.
  KEY `idx_users_name` (`name`),
  KEY `idx_users_surname` (`surname`),
  KEY `idx_users_tarih` (`tarih`)
)
-- InnoDB : Transaction ve foreign key destekler, MyISAM'e göre
--          çok daha güvenli ve moderndir. MySQL varsayılanıdır.
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
--  3) Örnek veriler (50 kayıt)
-- ---------------------------------------------------------------
--  Not: Kayıtların bir kısmı bilerek görselsiz bırakılmıştır;
--  böylece arayüzdeki "baş harf rozeti" (avatar placeholder)
--  özelliğini de örnekte görebilirsiniz.
INSERT INTO `users` (`id`, `name`, `surname`, `image`, `tarih`) VALUES
(1, 'Evren', 'ÇILGIN', '2090273627.png', '2025-01-06 19:34:27'),
(2, 'Taha', 'BAYAR', '1131702286.png', '2025-01-07 08:42:28'),
(3, 'Zeynep', 'TURAN', '', '2025-01-08 10:59:56'),
(4, 'Mustafa', 'YILMAZ', '1832783644.png', '2025-01-08 18:44:32'),
(5, 'Elif', 'KAYA', '2090273627.png', '2025-01-09 17:47:28'),
(6, 'Ahmet', 'DEMİR', '', '2025-01-11 11:49:05'),
(7, 'Ayşe', 'ŞAHİN', '1130289535.png', '2025-01-12 21:17:02'),
(8, 'Mehmet', 'ÇELİK', '1832783644.png', '2025-01-14 04:24:09'),
(9, 'Fatma', 'YILDIZ', '', '2025-01-15 23:58:29'),
(10, 'Emre', 'YILDIRIM', '1131702286.png', '2025-01-16 16:30:24'),
(11, 'Selin', 'ÖZTÜRK', '1130289535.png', '2025-01-17 17:06:42'),
(12, 'Burak', 'AYDIN', '', '2025-01-17 18:08:06'),
(13, 'Merve', 'ÖZDEMİR', '2090273627.png', '2025-01-19 01:30:32'),
(14, 'Onur', 'ARSLAN', '1131702286.png', '2025-01-19 20:00:51'),
(15, 'Ceren', 'DOĞAN', '', '2025-01-20 14:09:31'),
(16, 'Kaan', 'KILIÇ', '1832783644.png', '2025-01-20 22:05:15'),
(17, 'Büşra', 'ASLAN', '2090273627.png', '2025-01-21 08:36:59'),
(18, 'Serkan', 'ÇETİN', '', '2025-01-22 16:06:12'),
(19, 'Gizem', 'KARA', '1130289535.png', '2025-01-24 10:30:31'),
(20, 'Barış', 'KOÇ', '1832783644.png', '2025-01-25 07:19:52'),
(21, 'Deniz', 'KURT', '', '2025-01-26 01:28:52'),
(22, 'Hakan', 'ÖZKAN', '1131702286.png', '2025-01-27 19:52:10'),
(23, 'İrem', 'ŞİMŞEK', '1130289535.png', '2025-01-29 12:43:32'),
(24, 'Yusuf', 'POLAT', '', '2025-01-29 20:10:46'),
(25, 'Melis', 'ÖZER', '2090273627.png', '2025-01-30 22:06:37'),
(26, 'Cem', 'KORKMAZ', '1131702286.png', '2025-01-31 03:44:01'),
(27, 'Esra', 'ÇAKIR', '', '2025-01-31 18:25:27'),
(28, 'Volkan', 'ERDOĞAN', '1832783644.png', '2025-02-01 08:14:52'),
(29, 'Şeyma', 'GÜNEŞ', '2090273627.png', '2025-02-01 14:27:09'),
(30, 'Uğur', 'AKSOY', '', '2025-02-03 03:12:55'),
(31, 'Pınar', 'BULUT', '1130289535.png', '2025-02-04 20:02:24'),
(32, 'Tolga', 'TAŞ', '1832783644.png', '2025-02-04 21:02:35'),
(33, 'Nazlı', 'KAPLAN', '', '2025-02-06 16:07:07'),
(34, 'Görkem', 'SOYLU', '1131702286.png', '2025-02-08 01:23:35'),
(35, 'Damla', 'ATEŞ', '1130289535.png', '2025-02-09 07:56:33'),
(36, 'Berk', 'GÜLER', '', '2025-02-10 02:16:27'),
(37, 'Sude', 'BOZKURT', '2090273627.png', '2025-02-10 18:54:39'),
(38, 'Alper', 'TEKİN', '1131702286.png', '2025-02-11 10:55:00'),
(39, 'Ebru', 'ACAR', '', '2025-02-13 09:17:40'),
(40, 'Sinan', 'BARAN', '1832783644.png', '2025-02-15 08:26:15'),
(41, 'Aslı', 'SEZER', '2090273627.png', '2025-02-16 06:25:42'),
(42, 'Furkan', 'KOCA', '', '2025-02-17 21:37:35'),
(43, 'Nesrin', 'UZUN', '1130289535.png', '2025-02-18 17:36:38'),
(44, 'Okan', 'AVCI', '1832783644.png', '2025-02-19 06:17:27'),
(45, 'Tuğçe', 'KESKİN', '', '2025-02-20 05:21:28'),
(46, 'Murat', 'ÜNAL', '1131702286.png', '2025-02-21 08:10:22'),
(47, 'Yasemin', 'GÜL', '1130289535.png', '2025-02-22 02:55:23'),
(48, 'Halil', 'DURMAZ', '', '2025-02-22 18:23:50'),
(49, 'Beyza', 'SARI', '2090273627.png', '2025-02-23 10:36:41'),
(50, 'Ozan', 'TOPAL', '1131702286.png', '2025-02-23 23:28:04');

-- ---------------------------------------------------------------
--  4) ESKİ SÜRÜMDEN YÜKSELTME
-- ---------------------------------------------------------------
--  Zaten dolu bir "users" tablonuz varsa ve verilerinizi
--  KAYBETMEK İSTEMİYORSANIZ, yukarıdaki DROP/CREATE yerine
--  aşağıdaki komutları çalıştırın:
--
--    ALTER TABLE `users` ENGINE=InnoDB;
--    ALTER TABLE `users` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
--    ALTER TABLE `users` MODIFY `image` VARCHAR(191) NOT NULL DEFAULT '';
--    ALTER TABLE `users` ADD KEY `idx_users_name` (`name`),
--                        ADD KEY `idx_users_surname` (`surname`),
--                        ADD KEY `idx_users_tarih` (`tarih`);
-- ===============================================================
