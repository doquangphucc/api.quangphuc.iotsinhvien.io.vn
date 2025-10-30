-- =====================================================
-- One-off upgrade: add numeric survey-specific spec columns
-- Safe to run multiple times (IF NOT EXISTS)
-- DB: nangluongmattroi | Date: 2025-10-30
-- =====================================================

USE nangluongmattroi;

ALTER TABLE survey_product_configs
    ADD COLUMN IF NOT EXISTS panel_power_watt INT NULL COMMENT 'Công suất tấm pin (W/tấm) - khảo sát' AFTER price_type,
    ADD COLUMN IF NOT EXISTS inverter_power_watt INT NULL COMMENT 'Công suất inverter (W) - khảo sát' AFTER panel_power_watt,
    ADD COLUMN IF NOT EXISTS battery_capacity_kwh DECIMAL(10,2) NULL COMMENT 'Dung lượng 1 bộ pin (kWh) - khảo sát' AFTER inverter_power_watt,
    ADD COLUMN IF NOT EXISTS cabinet_power_kw DECIMAL(10,2) NULL COMMENT 'Công suất tủ điện (kW) - khảo sát' AFTER battery_capacity_kwh;

-- Verify
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_COMMENT
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA='nangluongmattroi' AND TABLE_NAME='survey_product_configs'
  AND COLUMN_NAME IN ('panel_power_watt','inverter_power_watt','battery_capacity_kwh','cabinet_power_kw');


