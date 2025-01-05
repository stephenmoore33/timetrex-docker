ALTER TABLE regular_time_policy ADD COLUMN contributing_pay_code_policy_id UUID DEFAULT 'ffffffff-ffff-ffff-ffff-ffffffffffff';
ALTER TABLE over_time_policy ADD COLUMN contributing_pay_code_policy_id UUID DEFAULT 'ffffffff-ffff-ffff-ffff-ffffffffffff';
ALTER TABLE premium_policy ADD COLUMN contributing_pay_code_policy_id UUID DEFAULT 'ffffffff-ffff-ffff-ffff-ffffffffffff';