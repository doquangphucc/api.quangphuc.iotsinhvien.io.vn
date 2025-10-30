-- =====================================================
-- Upgrade script (hosting-safe): add survey spec columns
-- NOTE: Run ONCE only. Will fail with "Duplicate column" if run again.
-- DB: nangluongmattroi | Date: 2025-10-30
-- =====================================================

USE nangluongmattroi;

ALTER TABLE survey_product_configs
    ADD COLUMN panel_power_watt INT NULL COMMENT 'Công suất tấm pin (W/tấm) - khảo sát' AFTER price_type,
    ADD COLUMN inverter_power_watt INT NULL COMMENT 'Công suất inverter (W) - khảo sát' AFTER panel_power_watt,
    ADD COLUMN battery_capacity_kwh DECIMAL(10,2) NULL COMMENT 'Dung lượng 1 bộ pin (kWh) - khảo sát' AFTER inverter_power_watt,
    ADD COLUMN cabinet_power_kw DECIMAL(10,2) NULL COMMENT 'Công suất tủ điện (kW) - khảo sát' AFTER battery_capacity_kwh;


