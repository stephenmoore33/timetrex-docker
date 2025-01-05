CREATE TABLE notification (
	id uuid NOT NULL,
	user_id uuid NOT NULL,
	sent_status_id smallint NOT NULL DEFAULT 10,
	status_id smallint NOT NULL DEFAULT 10,
	priority_id smallint NOT NULL DEFAULT 5,
	acknowledged_type_id smallint NOT NULL DEFAULT 10,
	acknowledged_status_id smallint NOT NULL DEFAULT 10,
	type_id varchar(100),
	object_type_id integer,
	object_id uuid NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'::uuid,
	sent_device_id integer,
	effective_date timestamp with time zone NOT NULL,
	title_short varchar(100),
	title_long varchar(200),
	sub_title_short varchar(100),
	body_short_text text,
	body_long_text text,
	body_long_html text,
	payload_data json,
	time_to_live integer,
	created_date integer,
	created_by uuid,
	updated_date integer,
	updated_by uuid,
	deleted_date integer,
	deleted_by uuid,
	deleted smallint NOT NULL DEFAULT 0
);
CREATE UNIQUE INDEX notification_id ON notification(id);
CREATE INDEX notification_sent_status_id_status_id ON notification(sent_status_id, status_id);
CREATE INDEX notification_user_id_status_id ON notification(user_id, status_id);

CREATE TABLE user_preference_notification (
    id uuid NOT NULL,
    user_id uuid NOT NULL,
    status_id smallint,
    type_id varchar(100),
    priority_id smallint NOT NULL DEFAULT 5,
    device_id integer,
    created_date integer,
    created_by uuid,
    updated_date integer,
    updated_by uuid,
    deleted_date integer,
    deleted_by uuid,
    deleted smallint NOT NULL DEFAULT 0,
    other_json JSON DEFAULT NULL
);
CREATE UNIQUE INDEX user_preference_notification_id ON user_preference_notification(id);
CREATE INDEX user_preference_notification_user_id ON user_preference_notification(user_id);

CREATE TABLE device_token (
	id uuid NOT NULL,
	user_id uuid NOT NULL,
	platform_id smallint,
	device_token varchar(250),
	user_agent varchar(300),
	created_date integer,
	created_by uuid,
	updated_date integer,
	updated_by uuid,
	deleted_date integer,
	deleted_by uuid,
	deleted smallint NOT NULL DEFAULT 0
);
CREATE UNIQUE INDEX device_token_id ON device_token(id);
CREATE INDEX device_token_user_id ON device_token(user_id);

CREATE TABLE user_default_preference_notification (
	id uuid NOT NULL,
	user_default_id uuid NOT NULL,
	status_id smallint,
	type_id varchar(100),
	priority_id smallint NOT NULL DEFAULT 5,
	device_id integer,
	created_date integer,
	created_by uuid,
	updated_date integer,
	updated_by uuid,
	deleted_date integer,
	deleted_by uuid,
	deleted smallint NOT NULL DEFAULT 0,
	other_json JSON DEFAULT NULL
);
CREATE UNIQUE INDEX user_default_preference_notification_id ON user_default_preference_notification(id);
CREATE INDEX user_default_preference_notification_user_default_id ON user_default_preference_notification(user_default_id);

ALTER TABLE user_preference ADD notification_duration INTEGER DEFAULT 120;
ALTER TABLE user_preference ADD notification_status_id SMALLINT DEFAULT 1;
ALTER TABLE user_preference ADD browser_permission_ask_date TIMESTAMP WITH TIME ZONE;

ALTER TABLE holiday_policy ADD holiday_display_days INTEGER DEFAULT 381;

ALTER TABLE pay_period_schedule ADD create_days_in_advance SMALLINT DEFAULT 16;
ALTER TABLE pay_period_schedule ADD auto_close_after_days SMALLINT DEFAULT 3;

ALTER TABLE accrual_policy ADD COLUMN excess_rollover_accrual_policy_account_id uuid DEFAULT '00000000-0000-0000-0000-000000000000';
ALTER TABLE pay_formula_policy ADD COLUMN accrual_balance_threshold int DEFAULT 0;
ALTER TABLE pay_formula_policy ADD COLUMN accrual_balance_threshold_fallback_accrual_policy_account_id uuid DEFAULT '00000000-0000-0000-0000-000000000000';

ALTER TABLE kpi ADD display_order INTEGER DEFAULT 1000;
