-- =====================================================================
--  UMKM MANAGEMENT SYSTEM - STRUKTUR DATABASE
--  DBMS    : MySQL 8.0+ / MariaDB 10.4+ (XAMPP)
--  Charset : utf8mb4
--
--  Konvensi:
--   - Nama tabel & kolom bahasa Inggris
--   - Primary key  : id_<nama>   (contoh: users.id_user)
--   - Foreign key  : nama sama dengan PK yang dirujuk (contoh: items.id_category)
--   - Timestamp    : created_at, updated_at
--
--  Modul:
--   1. User & Profil Usaha          : businesses, users
--   2. Item & Stok                  : categories, units, items, stock_histories
--   3. Transaksi Penjualan          : customers, transactions, transaction_details
--   4. Pengeluaran, Hutang, Piutang : expense_categories, expenses, suppliers,
--                                     debts, debt_payments,
--                                     receivables, receivable_payments
--   5. Laporan & Target Usaha       : reports, revenue_targets
--
--  Cara import: phpMyAdmin -> Import, atau
--               mysql -u root -p < umkm_management.sql
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `umkm_management`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `umkm_management`;

DROP VIEW IF EXISTS `v_low_stock_items`, `v_best_selling_items`, `v_daily_sales_trend`, `v_monthly_profit_loss`;
DROP PROCEDURE IF EXISTS `sp_cancel_transaction`;
DROP TABLE IF EXISTS
  `revenue_targets`, `reports`,
  `receivable_payments`, `receivables`, `debt_payments`, `debts`, `suppliers`,
  `expenses`, `expense_categories`,
  `transaction_details`, `transactions`, `customers`,
  `stock_histories`, `items`, `units`, `categories`,
  `users`, `businesses`;


-- =====================================================================
-- MODUL 1 : USER & PROFIL USAHA
-- =====================================================================

-- Profil usaha
CREATE TABLE `businesses` (
  `id_business`   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_owner`      BIGINT UNSIGNED NULL COMMENT 'Pemilik usaha -> users.id_user (FK ditambahkan di bawah)',
  `business_name` VARCHAR(150) NOT NULL,
  `business_type` VARCHAR(100) NOT NULL COMMENT 'Kuliner, retail, jasa, dll',
  `address`       TEXT NULL,
  `phone`         VARCHAR(20) NULL,
  `logo`          VARCHAR(255) NULL COMMENT 'Path file logo',
  `created_at`    TIMESTAMP NULL DEFAULT NULL,
  `updated_at`    TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_business`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Akun pemilik & karyawan (kasir)
CREATE TABLE `users` (
  `id_user`     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_business` BIGINT UNSIGNED NULL COMMENT 'NULL saat pemilik baru registrasi & belum buat profil usaha',
  `name`        VARCHAR(150) NOT NULL,
  `email`       VARCHAR(150) NOT NULL,
  `phone`       VARCHAR(20) NULL,
  `password`    VARCHAR(255) NOT NULL COMMENT 'Hash bcrypt',
  `role`        ENUM('owner','cashier') NOT NULL DEFAULT 'cashier' COMMENT 'owner = pemilik, cashier = kasir',
  `is_active`   TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0 = akun dinonaktifkan',
  `created_at`  TIMESTAMP NULL DEFAULT NULL,
  `updated_at`  TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_business` (`id_business`),
  CONSTRAINT `fk_users_business` FOREIGN KEY (`id_business`)
    REFERENCES `businesses` (`id_business`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `businesses`
  ADD KEY `idx_businesses_owner` (`id_owner`),
  ADD CONSTRAINT `fk_businesses_owner` FOREIGN KEY (`id_owner`)
    REFERENCES `users` (`id_user`) ON DELETE SET NULL;


-- =====================================================================
-- MODUL 2 : ITEM & STOK
-- =====================================================================

CREATE TABLE `categories` (
  `id_category`   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_business`   BIGINT UNSIGNED NOT NULL,
  `category_name` VARCHAR(100) NOT NULL,
  `created_at`    TIMESTAMP NULL DEFAULT NULL,
  `updated_at`    TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_category`),
  UNIQUE KEY `uq_categories_name` (`id_business`, `category_name`),
  CONSTRAINT `fk_categories_business` FOREIGN KEY (`id_business`)
    REFERENCES `businesses` (`id_business`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `units` (
  `id_unit`     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_business` BIGINT UNSIGNED NOT NULL,
  `unit_name`   VARCHAR(50) NOT NULL COMMENT 'pcs, kg, porsi, jam, dll',
  `created_at`  TIMESTAMP NULL DEFAULT NULL,
  `updated_at`  TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_unit`),
  UNIQUE KEY `uq_units_name` (`id_business`, `unit_name`),
  CONSTRAINT `fk_units_business` FOREIGN KEY (`id_business`)
    REFERENCES `businesses` (`id_business`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kategori/satuan yang masih dipakai item TIDAK bisa dihapus (RESTRICT)
CREATE TABLE `items` (
  `id_item`       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_business`   BIGINT UNSIGNED NOT NULL,
  `id_category`   BIGINT UNSIGNED NULL,
  `id_unit`       BIGINT UNSIGNED NULL,
  `item_name`     VARCHAR(150) NOT NULL,
  `selling_price` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Harga jual',
  `cost_price`    DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Harga modal',
  `is_service`    TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = "Tanpa stok" (layanan/jasa)',
  `stock`         DECIMAL(12,2) NOT NULL DEFAULT 0,
  `min_stock`     DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT 'Batas peringatan stok menipis',
  `photo`         VARCHAR(255) NULL,
  `created_at`    TIMESTAMP NULL DEFAULT NULL,
  `updated_at`    TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_item`),
  KEY `idx_items_name` (`id_business`, `item_name`),
  KEY `idx_items_category` (`id_category`),
  KEY `idx_items_unit` (`id_unit`),
  CONSTRAINT `fk_items_business` FOREIGN KEY (`id_business`)
    REFERENCES `businesses` (`id_business`) ON DELETE CASCADE,
  CONSTRAINT `fk_items_category` FOREIGN KEY (`id_category`)
    REFERENCES `categories` (`id_category`) ON DELETE RESTRICT,
  CONSTRAINT `fk_items_unit` FOREIGN KEY (`id_unit`)
    REFERENCES `units` (`id_unit`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Riwayat perubahan stok (stok masuk, koreksi, terjual, dikembalikan karena batal)
CREATE TABLE `stock_histories` (
  `id_stock_history` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_item`          BIGINT UNSIGNED NOT NULL,
  `id_user`          BIGINT UNSIGNED NULL,
  `id_transaction`   BIGINT UNSIGNED NULL COMMENT 'Terisi jika perubahan berasal dari transaksi',
  `type`             ENUM('in','adjustment','sale','cancellation') NOT NULL
                     COMMENT 'in = stok masuk, adjustment = koreksi, sale = terjual, cancellation = transaksi batal',
  `quantity`         DECIMAL(12,2) NOT NULL COMMENT 'Positif = bertambah, negatif = berkurang',
  `stock_before`     DECIMAL(12,2) NOT NULL,
  `stock_after`      DECIMAL(12,2) NOT NULL,
  `note`             VARCHAR(255) NULL,
  `created_at`       TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_stock_history`),
  KEY `idx_stock_histories_item` (`id_item`),
  KEY `idx_stock_histories_user` (`id_user`),
  KEY `idx_stock_histories_transaction` (`id_transaction`),
  CONSTRAINT `fk_stock_histories_item` FOREIGN KEY (`id_item`)
    REFERENCES `items` (`id_item`) ON DELETE CASCADE,
  CONSTRAINT `fk_stock_histories_user` FOREIGN KEY (`id_user`)
    REFERENCES `users` (`id_user`) ON DELETE SET NULL
  -- FK ke transactions ditambahkan setelah tabel transactions dibuat
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
-- MODUL 3 : TRANSAKSI PENJUALAN
-- =====================================================================

CREATE TABLE `customers` (
  `id_customer`   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_business`   BIGINT UNSIGNED NOT NULL,
  `customer_name` VARCHAR(150) NOT NULL,
  `phone`         VARCHAR(20) NULL,
  `address`       TEXT NULL,
  `created_at`    TIMESTAMP NULL DEFAULT NULL,
  `updated_at`    TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_customer`),
  KEY `idx_customers_name` (`id_business`, `customer_name`),
  CONSTRAINT `fk_customers_business` FOREIGN KEY (`id_business`)
    REFERENCES `businesses` (`id_business`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Batalkan transaksi = status 'cancelled' (lihat sp_cancel_transaction)
CREATE TABLE `transactions` (
  `id_transaction`   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_business`      BIGINT UNSIGNED NOT NULL,
  `id_customer`      BIGINT UNSIGNED NULL COMMENT 'Pelanggan opsional',
  `id_user`          BIGINT UNSIGNED NULL COMMENT 'Kasir yang mencatat',
  `invoice_number`   VARCHAR(50) NOT NULL COMMENT 'No. nota, contoh: INV-20261106-0001',
  `transaction_date` DATETIME NOT NULL,
  `total_amount`     DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Total belanja',
  `paid_amount`      DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Uang diterima / DP',
  `payment_method`   ENUM('cash','transfer','qris','e-wallet') NOT NULL DEFAULT 'cash',
  `payment_status`   ENUM('paid','unpaid','down_payment') NOT NULL DEFAULT 'paid'
                     COMMENT 'paid = lunas, unpaid = belum lunas, down_payment = DP',
  `status`           ENUM('completed','cancelled') NOT NULL DEFAULT 'completed',
  `note`             VARCHAR(255) NULL,
  `created_at`       TIMESTAMP NULL DEFAULT NULL,
  `updated_at`       TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_transaction`),
  UNIQUE KEY `uq_transactions_invoice` (`id_business`, `invoice_number`),
  KEY `idx_transactions_date` (`id_business`, `transaction_date`),
  KEY `idx_transactions_customer` (`id_customer`),
  KEY `idx_transactions_user` (`id_user`),
  CONSTRAINT `fk_transactions_business` FOREIGN KEY (`id_business`)
    REFERENCES `businesses` (`id_business`) ON DELETE CASCADE,
  CONSTRAINT `fk_transactions_customer` FOREIGN KEY (`id_customer`)
    REFERENCES `customers` (`id_customer`) ON DELETE SET NULL,
  CONSTRAINT `fk_transactions_user` FOREIGN KEY (`id_user`)
    REFERENCES `users` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `stock_histories`
  ADD CONSTRAINT `fk_stock_histories_transaction` FOREIGN KEY (`id_transaction`)
    REFERENCES `transactions` (`id_transaction`) ON DELETE SET NULL;

-- Nama & harga item disimpan ulang di sini agar nota lama tetap benar
-- walau harga item berubah atau item dihapus.
CREATE TABLE `transaction_details` (
  `id_transaction_detail` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_transaction`        BIGINT UNSIGNED NOT NULL,
  `id_item`               BIGINT UNSIGNED NULL,
  `item_name`             VARCHAR(150) NOT NULL,
  `selling_price`         DECIMAL(15,2) NOT NULL,
  `cost_price`            DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Untuk hitung laba',
  `quantity`              DECIMAL(12,2) NOT NULL,
  `subtotal`              DECIMAL(15,2) NOT NULL COMMENT 'selling_price x quantity',
  PRIMARY KEY (`id_transaction_detail`),
  KEY `idx_transaction_details_transaction` (`id_transaction`),
  KEY `idx_transaction_details_item` (`id_item`),
  CONSTRAINT `fk_transaction_details_transaction` FOREIGN KEY (`id_transaction`)
    REFERENCES `transactions` (`id_transaction`) ON DELETE CASCADE,
  CONSTRAINT `fk_transaction_details_item` FOREIGN KEY (`id_item`)
    REFERENCES `items` (`id_item`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
-- MODUL 4 : PENGELUARAN, HUTANG & PIUTANG
-- =====================================================================

CREATE TABLE `expense_categories` (
  `id_expense_category` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_business`         BIGINT UNSIGNED NOT NULL,
  `category_name`       VARCHAR(100) NOT NULL COMMENT 'Bahan, Gaji, Sewa, Listrik, dll',
  `created_at`          TIMESTAMP NULL DEFAULT NULL,
  `updated_at`          TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_expense_category`),
  UNIQUE KEY `uq_expense_categories_name` (`id_business`, `category_name`),
  CONSTRAINT `fk_expense_categories_business` FOREIGN KEY (`id_business`)
    REFERENCES `businesses` (`id_business`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kategori pengeluaran yang masih dipakai TIDAK bisa dihapus (RESTRICT)
CREATE TABLE `expenses` (
  `id_expense`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_business`         BIGINT UNSIGNED NOT NULL,
  `id_expense_category` BIGINT UNSIGNED NOT NULL,
  `id_user`             BIGINT UNSIGNED NULL,
  `expense_date`        DATE NOT NULL,
  `amount`              DECIMAL(15,2) NOT NULL,
  `note`                VARCHAR(255) NULL,
  `created_at`          TIMESTAMP NULL DEFAULT NULL,
  `updated_at`          TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_expense`),
  KEY `idx_expenses_date` (`id_business`, `expense_date`),
  KEY `idx_expenses_category` (`id_expense_category`),
  KEY `idx_expenses_user` (`id_user`),
  CONSTRAINT `fk_expenses_business` FOREIGN KEY (`id_business`)
    REFERENCES `businesses` (`id_business`) ON DELETE CASCADE,
  CONSTRAINT `fk_expenses_category` FOREIGN KEY (`id_expense_category`)
    REFERENCES `expense_categories` (`id_expense_category`) ON DELETE RESTRICT,
  CONSTRAINT `fk_expenses_user` FOREIGN KEY (`id_user`)
    REFERENCES `users` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `suppliers` (
  `id_supplier`   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_business`   BIGINT UNSIGNED NOT NULL,
  `supplier_name` VARCHAR(150) NOT NULL,
  `phone`         VARCHAR(20) NULL,
  `address`       TEXT NULL,
  `created_at`    TIMESTAMP NULL DEFAULT NULL,
  `updated_at`    TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_supplier`),
  KEY `idx_suppliers_business` (`id_business`),
  CONSTRAINT `fk_suppliers_business` FOREIGN KEY (`id_business`)
    REFERENCES `businesses` (`id_business`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Hutang usaha ke supplier
CREATE TABLE `debts` (
  `id_debt`     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_business` BIGINT UNSIGNED NOT NULL,
  `id_supplier` BIGINT UNSIGNED NOT NULL,
  `id_user`     BIGINT UNSIGNED NULL,
  `note`        VARCHAR(255) NULL,
  `amount`      DECIMAL(15,2) NOT NULL COMMENT 'Total hutang',
  `paid_amount` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Akumulasi dari debt_payments',
  `debt_date`   DATE NOT NULL,
  `due_date`    DATE NULL COMMENT 'Jatuh tempo',
  `status`      ENUM('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid'
                COMMENT 'unpaid = belum lunas, partial = dicicil, paid = lunas',
  `created_at`  TIMESTAMP NULL DEFAULT NULL,
  `updated_at`  TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_debt`),
  KEY `idx_debts_due` (`id_business`, `status`, `due_date`),
  KEY `idx_debts_supplier` (`id_supplier`),
  KEY `idx_debts_user` (`id_user`),
  CONSTRAINT `fk_debts_business` FOREIGN KEY (`id_business`)
    REFERENCES `businesses` (`id_business`) ON DELETE CASCADE,
  CONSTRAINT `fk_debts_supplier` FOREIGN KEY (`id_supplier`)
    REFERENCES `suppliers` (`id_supplier`) ON DELETE RESTRICT,
  CONSTRAINT `fk_debts_user` FOREIGN KEY (`id_user`)
    REFERENCES `users` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cicilan / pelunasan hutang
CREATE TABLE `debt_payments` (
  `id_debt_payment` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_debt`         BIGINT UNSIGNED NOT NULL,
  `id_user`         BIGINT UNSIGNED NULL,
  `amount`          DECIMAL(15,2) NOT NULL,
  `payment_date`    DATE NOT NULL,
  `note`            VARCHAR(255) NULL,
  `created_at`      TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_debt_payment`),
  KEY `idx_debt_payments_debt` (`id_debt`),
  KEY `idx_debt_payments_user` (`id_user`),
  CONSTRAINT `fk_debt_payments_debt` FOREIGN KEY (`id_debt`)
    REFERENCES `debts` (`id_debt`) ON DELETE CASCADE,
  CONSTRAINT `fk_debt_payments_user` FOREIGN KEY (`id_user`)
    REFERENCES `users` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Piutang pelanggan (bisa berasal dari transaksi belum lunas / DP)
CREATE TABLE `receivables` (
  `id_receivable`   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_business`     BIGINT UNSIGNED NOT NULL,
  `id_customer`     BIGINT UNSIGNED NOT NULL,
  `id_transaction`  BIGINT UNSIGNED NULL COMMENT 'Opsional: transaksi asal piutang',
  `id_user`         BIGINT UNSIGNED NULL,
  `note`            VARCHAR(255) NULL,
  `amount`          DECIMAL(15,2) NOT NULL COMMENT 'Total piutang',
  `paid_amount`     DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Akumulasi dari receivable_payments',
  `receivable_date` DATE NOT NULL,
  `due_date`        DATE NULL COMMENT 'Jatuh tempo',
  `status`          ENUM('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid'
                    COMMENT 'unpaid = belum lunas, partial = dicicil, paid = lunas',
  `created_at`      TIMESTAMP NULL DEFAULT NULL,
  `updated_at`      TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_receivable`),
  KEY `idx_receivables_due` (`id_business`, `status`, `due_date`),
  KEY `idx_receivables_customer` (`id_customer`),
  KEY `idx_receivables_transaction` (`id_transaction`),
  KEY `idx_receivables_user` (`id_user`),
  CONSTRAINT `fk_receivables_business` FOREIGN KEY (`id_business`)
    REFERENCES `businesses` (`id_business`) ON DELETE CASCADE,
  CONSTRAINT `fk_receivables_customer` FOREIGN KEY (`id_customer`)
    REFERENCES `customers` (`id_customer`) ON DELETE RESTRICT,
  CONSTRAINT `fk_receivables_transaction` FOREIGN KEY (`id_transaction`)
    REFERENCES `transactions` (`id_transaction`) ON DELETE SET NULL,
  CONSTRAINT `fk_receivables_user` FOREIGN KEY (`id_user`)
    REFERENCES `users` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cicilan / pelunasan piutang
CREATE TABLE `receivable_payments` (
  `id_receivable_payment` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_receivable`         BIGINT UNSIGNED NOT NULL,
  `id_user`               BIGINT UNSIGNED NULL,
  `amount`                DECIMAL(15,2) NOT NULL,
  `payment_date`          DATE NOT NULL,
  `note`                  VARCHAR(255) NULL,
  `created_at`            TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_receivable_payment`),
  KEY `idx_receivable_payments_receivable` (`id_receivable`),
  KEY `idx_receivable_payments_user` (`id_user`),
  CONSTRAINT `fk_receivable_payments_receivable` FOREIGN KEY (`id_receivable`)
    REFERENCES `receivables` (`id_receivable`) ON DELETE CASCADE,
  CONSTRAINT `fk_receivable_payments_user` FOREIGN KEY (`id_user`)
    REFERENCES `users` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
-- MODUL 5 : LAPORAN & TARGET USAHA
-- =====================================================================

-- Laporan tersimpan: periode + filter + ringkasan angka saat dibuat
CREATE TABLE `reports` (
  `id_report`     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_business`   BIGINT UNSIGNED NOT NULL,
  `id_user`       BIGINT UNSIGNED NULL,
  `report_name`   VARCHAR(150) NOT NULL,
  `period_type`   ENUM('daily','weekly','monthly') NOT NULL COMMENT 'harian / mingguan / bulanan',
  `start_date`    DATE NOT NULL,
  `end_date`      DATE NOT NULL,
  `filters`       JSON NULL COMMENT 'Filter tersimpan, contoh: {"id_category":[1,2],"payment_method":"cash"}',
  `total_revenue` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Omzet',
  `total_cost`    DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Total modal',
  `total_expense` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Total pengeluaran',
  `net_profit`    DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Omzet - modal - pengeluaran',
  `created_at`    TIMESTAMP NULL DEFAULT NULL,
  `updated_at`    TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_report`),
  KEY `idx_reports_period` (`id_business`, `period_type`, `start_date`),
  KEY `idx_reports_user` (`id_user`),
  CONSTRAINT `fk_reports_business` FOREIGN KEY (`id_business`)
    REFERENCES `businesses` (`id_business`) ON DELETE CASCADE,
  CONSTRAINT `fk_reports_user` FOREIGN KEY (`id_user`)
    REFERENCES `users` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Target omzet
CREATE TABLE `revenue_targets` (
  `id_revenue_target` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_business`       BIGINT UNSIGNED NOT NULL,
  `period_type`       ENUM('daily','weekly','monthly') NOT NULL DEFAULT 'monthly',
  `start_date`        DATE NOT NULL,
  `end_date`          DATE NOT NULL,
  `target_amount`     DECIMAL(15,2) NOT NULL,
  `created_at`        TIMESTAMP NULL DEFAULT NULL,
  `updated_at`        TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_revenue_target`),
  KEY `idx_revenue_targets_period` (`id_business`, `start_date`, `end_date`),
  CONSTRAINT `fk_revenue_targets_business` FOREIGN KEY (`id_business`)
    REFERENCES `businesses` (`id_business`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;


-- =====================================================================
-- VIEW UNTUK DASHBOARD
-- =====================================================================

-- Peringatan stok menipis
CREATE VIEW `v_low_stock_items` AS
SELECT i.id_business, i.id_item, i.item_name, c.category_name, u.unit_name,
       i.stock, i.min_stock
FROM items i
LEFT JOIN categories c ON c.id_category = i.id_category
LEFT JOIN units u      ON u.id_unit = i.id_unit
WHERE i.is_service = 0 AND i.stock <= i.min_stock;

-- Item terlaris (hanya transaksi yang tidak dibatalkan)
CREATE VIEW `v_best_selling_items` AS
SELECT t.id_business, d.id_item, d.item_name,
       SUM(d.quantity)                             AS total_sold,
       SUM(d.subtotal)                             AS total_revenue,
       SUM(d.subtotal - d.cost_price * d.quantity) AS total_profit
FROM transaction_details d
JOIN transactions t ON t.id_transaction = d.id_transaction
WHERE t.status = 'completed'
GROUP BY t.id_business, d.id_item, d.item_name;

-- Tren penjualan harian (untuk grafik)
CREATE VIEW `v_daily_sales_trend` AS
SELECT t.id_business, DATE(t.transaction_date) AS sales_date,
       COUNT(DISTINCT t.id_transaction)            AS transaction_count,
       SUM(d.subtotal)                             AS revenue,
       SUM(d.subtotal - d.cost_price * d.quantity) AS gross_profit
FROM transactions t
JOIN transaction_details d ON d.id_transaction = t.id_transaction
WHERE t.status = 'completed'
GROUP BY t.id_business, DATE(t.transaction_date);

-- Laba rugi bulanan = omzet - modal - pengeluaran
CREATE VIEW `v_monthly_profit_loss` AS
SELECT x.id_business, x.month,
       SUM(x.revenue)                                 AS revenue,
       SUM(x.cost)                                    AS cost,
       SUM(x.expense)                                 AS expense,
       SUM(x.revenue) - SUM(x.cost) - SUM(x.expense)  AS net_profit
FROM (
  SELECT t.id_business, DATE_FORMAT(t.transaction_date, '%Y-%m') AS month,
         d.subtotal AS revenue, d.cost_price * d.quantity AS cost, 0 AS expense
  FROM transactions t
  JOIN transaction_details d ON d.id_transaction = t.id_transaction
  WHERE t.status = 'completed'
  UNION ALL
  SELECT e.id_business, DATE_FORMAT(e.expense_date, '%Y-%m'), 0, 0, e.amount
  FROM expenses e
) x
GROUP BY x.id_business, x.month;


-- =====================================================================
-- STORED PROCEDURE : Batalkan transaksi + kembalikan stok otomatis
-- Pemakaian: CALL sp_cancel_transaction(<id_transaction>, <id_user>);
-- =====================================================================
DELIMITER $$
CREATE PROCEDURE `sp_cancel_transaction`(
  IN p_id_transaction BIGINT UNSIGNED,
  IN p_id_user        BIGINT UNSIGNED
)
BEGIN
  DECLARE v_status VARCHAR(20);

  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;

  START TRANSACTION;

  SELECT status INTO v_status
  FROM transactions WHERE id_transaction = p_id_transaction FOR UPDATE;

  IF v_status IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Transaksi tidak ditemukan';
  END IF;
  IF v_status = 'cancelled' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Transaksi sudah dibatalkan';
  END IF;

  -- Catat riwayat stok yang dikembalikan
  INSERT INTO stock_histories (id_item, id_user, id_transaction, type, quantity,
                               stock_before, stock_after, note, created_at)
  SELECT i.id_item, p_id_user, p_id_transaction, 'cancellation', d.qty,
         i.stock, i.stock + d.qty, 'Stok dikembalikan karena transaksi dibatalkan', NOW()
  FROM (SELECT id_item, SUM(quantity) AS qty FROM transaction_details
        WHERE id_transaction = p_id_transaction AND id_item IS NOT NULL
        GROUP BY id_item) d
  JOIN items i ON i.id_item = d.id_item
  WHERE i.is_service = 0;

  -- Kembalikan stok item
  UPDATE items i
  JOIN (SELECT id_item, SUM(quantity) AS qty FROM transaction_details
        WHERE id_transaction = p_id_transaction AND id_item IS NOT NULL
        GROUP BY id_item) d ON d.id_item = i.id_item
  SET i.stock = i.stock + d.qty, i.updated_at = NOW()
  WHERE i.is_service = 0;

  -- Tandai transaksi dibatalkan
  UPDATE transactions
  SET status = 'cancelled', updated_at = NOW()
  WHERE id_transaction = p_id_transaction;

  COMMIT;
END$$
DELIMITER ;


-- =====================================================================
-- DATA AWAL (contoh, boleh dihapus)
-- Password akun contoh: password
-- =====================================================================
INSERT INTO `businesses` (`id_business`, `id_owner`, `business_name`, `business_type`, `address`, `phone`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Warung Berkah', 'Kuliner', 'Jl. Merdeka No. 10, Jakarta', '081234567890', NOW(), NOW());

INSERT INTO `users` (`id_user`, `id_business`, `name`, `email`, `password`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Pemilik Usaha', 'owner@example.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner',   1, NOW(), NOW()),
(2, 1, 'Kasir Satu',    'cashier@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cashier', 1, NOW(), NOW());

UPDATE `businesses` SET `id_owner` = 1 WHERE `id_business` = 1;

INSERT INTO `categories` (`id_category`, `id_business`, `category_name`, `created_at`, `updated_at`) VALUES
(1, 1, 'Makanan', NOW(), NOW()),
(2, 1, 'Minuman', NOW(), NOW()),
(3, 1, 'Layanan', NOW(), NOW());

INSERT INTO `units` (`id_unit`, `id_business`, `unit_name`, `created_at`, `updated_at`) VALUES
(1, 1, 'porsi', NOW(), NOW()),
(2, 1, 'gelas', NOW(), NOW()),
(3, 1, 'jam',   NOW(), NOW());

INSERT INTO `items` (`id_business`, `id_category`, `id_unit`, `item_name`, `selling_price`, `cost_price`, `is_service`, `stock`, `min_stock`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Nasi Goreng',  18000, 9000, 0,  50, 10, NOW(), NOW()),
(1, 1, 1, 'Mie Ayam',     15000, 7000, 0,   8, 10, NOW(), NOW()),
(1, 2, 2, 'Es Teh Manis',  5000, 1500, 0, 100, 20, NOW(), NOW()),
(1, 3, 3, 'Sewa Tempat',  50000,    0, 1,   0,  0, NOW(), NOW());

INSERT INTO `expense_categories` (`id_business`, `category_name`, `created_at`, `updated_at`) VALUES
(1, 'Bahan',   NOW(), NOW()),
(1, 'Gaji',    NOW(), NOW()),
(1, 'Sewa',    NOW(), NOW()),
(1, 'Listrik', NOW(), NOW());

INSERT INTO `customers` (`id_business`, `customer_name`, `phone`, `created_at`, `updated_at`) VALUES
(1, 'Budi Santoso', '081211112222', NOW(), NOW());

INSERT INTO `suppliers` (`id_business`, `supplier_name`, `phone`, `created_at`, `updated_at`) VALUES
(1, 'Toko Sembako Jaya', '081233334444', NOW(), NOW());

INSERT INTO `revenue_targets` (`id_business`, `period_type`, `start_date`, `end_date`, `target_amount`, `created_at`, `updated_at`) VALUES
(1, 'monthly', '2026-11-01', '2026-11-30', 15000000, NOW(), NOW());
