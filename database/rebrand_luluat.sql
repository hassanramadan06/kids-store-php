-- =============================================================
-- Luluat Almisbah — rebrand migration
-- Run this once on an existing kids_store database to update the
-- site_name + contact email stored in the `settings` table.
-- Safe to run multiple times.
-- =============================================================
USE `kids_store`;

UPDATE `settings` SET `value` = 'لؤلؤة المصباح'              WHERE `key_name` = 'site_name_ar';
UPDATE `settings` SET `value` = 'Luluat Almisbah'             WHERE `key_name` = 'site_name_en';
UPDATE `settings` SET `value` = 'contact@luluat-almisbah.local' WHERE `key_name` = 'contact_email';
