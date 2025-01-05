CREATE TABLE ui_kit (
	id uuid NOT NULL,
	company_id uuid NOT NULL,
	combo_box integer,
	combo_box_parent varchar(255),
	combo_box_child varchar(255),
	awesome_box_single uuid,
	textarea varchar(255),
	text_input varchar(255),
	password_input varchar(255),
	numeric_input numeric(20,4),
	checkbox smallint,
	wysiwg_text text,
	date date,
	time timestamp with time zone,
	tag varchar(255),
	other_json json,
	created_date integer,
	created_by uuid,
	updated_date integer,
	updated_by uuid,
	deleted_date integer,
	deleted_by uuid,
	deleted smallint NOT NULL DEFAULT 0
);
CREATE UNIQUE INDEX ui_kit_id ON ui_kit(id);

CREATE TABLE ui_kit_child (
	id uuid NOT NULL,
	parent_id uuid NOT NULL,
	company_id uuid NOT NULL,
	combo_box integer,
	text_input varchar(255),
	checkbox smallint,
	created_date integer,
	created_by uuid,
	updated_date integer,
	updated_by uuid,
	deleted_date integer,
	deleted_by uuid,
	deleted smallint NOT NULL DEFAULT 0
);
CREATE UNIQUE INDEX ui_kit_child_id ON ui_kit_child(id);