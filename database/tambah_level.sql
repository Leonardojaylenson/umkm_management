-- =====================================================================
--  UPDATE DATABASE: TAMBAH FITUR LEVEL PENGGUNA
--
--  Untuk database umkm_management yang SUDAH diimport sebelumnya
--  (data tidak hilang). Jalankan SEKALI saja lewat phpMyAdmin -> SQL.
--
--  Yang dilakukan:
--   1. Membuat tabel levels dan level_menus
--   2. Setiap usaha otomatis dapat 2 level: Pemilik dan Karyawan
--   3. Kolom users.role diganti menjadi users.id_level
--      (owner -> level Pemilik, cashier -> level Karyawan)
--
--  Kalau ingin mulai dari awal, cukup import ulang umkm_management.sql
-- =====================================================================

USE `umkm_management`;

CREATE TABLE `levels` (
  `id_level`    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_business` BIGINT UNSIGNED NOT NULL,
  `level_name`  VARCHAR(100) NOT NULL,
  `is_owner`    TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = level pemilik',
  `created_at`  TIMESTAMP NULL DEFAULT NULL,
  `updated_at`  TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_level`),
  UNIQUE KEY `uq_levels_name` (`id_business`, `level_name`),
  CONSTRAINT `fk_levels_business` FOREIGN KEY (`id_business`)
    REFERENCES `businesses` (`id_business`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `level_menus` (
  `id_level_menu` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_level`      BIGINT UNSIGNED NOT NULL,
  `menu`          VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id_level_menu`),
  UNIQUE KEY `uq_level_menus` (`id_level`, `menu`),
  CONSTRAINT `fk_level_menus_level` FOREIGN KEY (`id_level`)
    REFERENCES `levels` (`id_level`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Level untuk setiap usaha yang sudah ada
INSERT INTO `levels` (`id_business`, `level_name`, `is_owner`, `created_at`, `updated_at`)
SELECT `id_business`, 'Pemilik', 1, NOW(), NOW() FROM `businesses`;

INSERT INTO `levels` (`id_business`, `level_name`, `is_owner`, `created_at`, `updated_at`)
SELECT `id_business`, 'Karyawan', 0, NOW(), NOW() FROM `businesses`;

-- Level Karyawan boleh membuka: item, transaksi (dashboard selalu boleh) (bisa diubah pemilik di menu Pengaturan Level)
INSERT INTO `level_menus` (`id_level`, `menu`)
SELECT l.`id_level`, m.`menu`
FROM `levels` l
JOIN (SELECT 'item' AS menu UNION ALL SELECT 'transaksi') m
WHERE l.`is_owner` = 0;

-- Pindahkan role lama ke level baru, lalu hapus kolom role
ALTER TABLE `users` ADD COLUMN `id_level` BIGINT UNSIGNED NULL AFTER `id_business`;

UPDATE `users` u
JOIN `levels` l ON l.`id_business` = u.`id_business` AND l.`is_owner` = (u.`role` = 'owner')
SET u.`id_level` = l.`id_level`;

ALTER TABLE `users`
  ADD KEY `idx_users_level` (`id_level`),
  ADD CONSTRAINT `fk_users_level` FOREIGN KEY (`id_level`)
    REFERENCES `levels` (`id_level`) ON DELETE RESTRICT,
  DROP COLUMN `role`;
