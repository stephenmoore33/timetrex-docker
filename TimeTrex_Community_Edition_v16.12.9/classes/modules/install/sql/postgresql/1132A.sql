CREATE TABLE custom_field_control (
    id uuid NOT NULL,
    company_id uuid NOT NULL,
	status_id smallint NOT NULL DEFAULT 10,
    parent_table varchar NOT NULL,
    type_id smallint NOT NULL,
    name varchar NOT NULL,
	display_order integer,
    default_value jsonb,
    width smallint,
    height smallint,
    is_required smallint NOT NULL DEFAULT 0,
    is_unique smallint NOT NULL DEFAULT 0,
    enable_search smallint NOT NULL DEFAULT 0,
	is_range_search smallint NOT NULL DEFAULT 0,
	is_advanced_validation smallint NOT NULL DEFAULT 0,
	help_text varchar,
	comment_text varchar,
    meta_data json,
	legacy_other_field_id smallint,
	created_date integer,
    created_by uuid,
    updated_date integer,
    updated_by uuid,
    deleted_date integer,
    deleted_by uuid,
    deleted smallint NOT NULL DEFAULT 0
);
CREATE UNIQUE INDEX custom_field_control_id ON custom_field_control(id);
CREATE INDEX custom_field_control_company_id_parent_table ON custom_field_control(company_id, parent_table);

ALTER TABLE company ADD custom_field jsonb;
CREATE INDEX company_custom_fields ON company USING GIN ( custom_field );

ALTER TABLE branch ADD custom_field jsonb;
CREATE INDEX branch_custom_fields ON branch USING GIN ( custom_field );

ALTER TABLE department ADD custom_field jsonb;
CREATE INDEX department_custom_fields ON department USING GIN ( custom_field );

ALTER TABLE users ADD custom_field jsonb;
CREATE INDEX users_custom_fields ON users USING GIN ( custom_field );

ALTER TABLE user_title ADD custom_field jsonb;
CREATE INDEX user_title_custom_fields ON user_title USING GIN ( custom_field );

ALTER TABLE user_contact ADD custom_field jsonb;
CREATE INDEX user_contact_custom_fields ON user_contact USING GIN ( custom_field );

ALTER TABLE legal_entity ADD custom_field jsonb;
CREATE INDEX legal_entity_custom_fields ON user_contact USING GIN ( custom_field );

ALTER TABLE punch_control ADD custom_field jsonb;
CREATE INDEX punch_control_custom_fields ON punch_control USING GIN ( custom_field );

ALTER TABLE schedule ADD custom_field jsonb;
CREATE INDEX schedule_custom_fields ON schedule USING GIN ( custom_field );

ALTER TABLE ui_kit ADD custom_field jsonb;
CREATE INDEX ui_kit_custom_fields ON ui_kit USING GIN ( custom_field );