ALTER TABLE pay_formula_policy ADD COLUMN average_days INTEGER DEFAULT 0;
UPDATE cron SET minute = '6, 21, 36, 51' WHERE name = 'TimeClockSync';
CREATE INDEX company_generic_map_company_id_object_type_id_map_id ON company_generic_map(company_id, object_type_id, map_id);