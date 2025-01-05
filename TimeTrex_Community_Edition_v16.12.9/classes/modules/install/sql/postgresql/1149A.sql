ALTER TABLE accrual_policy ADD COLUMN eligible_period_id smallint DEFAULT 0;
ALTER TABLE accrual_policy ADD COLUMN minimum_eligible_time integer DEFAULT 0;
ALTER TABLE accrual_policy ADD COLUMN minimum_eligible_apply_retroactive smallint DEFAULT 1;
ALTER TABLE accrual_policy ADD COLUMN maximum_eligible_time integer DEFAULT 0;
ALTER TABLE accrual_policy ADD COLUMN eligible_contributing_shift_policy_id UUID DEFAULT NULL;