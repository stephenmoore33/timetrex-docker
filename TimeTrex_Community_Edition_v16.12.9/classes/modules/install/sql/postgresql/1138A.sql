ALTER TABLE users ADD COLUMN mfa_type_id smallint NOT NULL DEFAULT 0;
ALTER TABLE users ADD COLUMN mfa_json JSON DEFAULT NULL;
ALTER TABLE company ADD COLUMN mfa_json JSON DEFAULT NULL;
ALTER TABLE authentication DROP COLUMN flags;
ALTER TABLE authentication ADD COLUMN reauthenticated_date integer;
ALTER TABLE authentication ADD COLUMN other_json JSON DEFAULT NULL;

CREATE TABLE authentication_trusted_device (
	id uuid NOT NULL,
	user_id uuid NOT NULL,
	device_id varchar NOT NULL,
	device_user_agent varchar NOT NULL,
	ip_address character varying(45),
	location varchar,
	created_date integer,
	created_by uuid,
	updated_date integer,
	updated_by uuid,
	deleted_date integer,
	deleted_by uuid,
	deleted smallint NOT NULL DEFAULT 0
);
CREATE UNIQUE INDEX authentication_trusted_device_id ON authentication_trusted_device(id);
CREATE INDEX authentication_trusted_device_user_id_device_id ON authentication_trusted_device(user_id, device_id);