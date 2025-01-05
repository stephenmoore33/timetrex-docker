ALTER TABLE user_default ADD COLUMN name CHARACTER VARYING DEFAULT 'Default' NOT NULL;
ALTER TABLE user_default ADD COLUMN hierarchy_control JSON;
ALTER TABLE user_default ADD COLUMN recurring_schedule JSON;
ALTER TABLE user_default ADD COLUMN display_order INTEGER DEFAULT 100;