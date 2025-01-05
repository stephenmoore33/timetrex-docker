ALTER TABLE schedule ADD punch_tag_id JSONB;
ALTER TABLE recurring_schedule ADD punch_tag_id JSONB;
ALTER TABLE recurring_schedule_template ADD punch_tag_id JSONB;
ALTER TABLE user_date_total ADD punch_tag_id JSONB;
ALTER TABLE station ADD punch_tag_id JSONB;
ALTER TABLE punch_control ADD punch_tag_id JSONB;

ALTER TABLE users ADD default_punch_tag_id JSONB;

ALTER TABLE regular_time_policy ADD punch_tag_group_selection_type_id SMALLINT DEFAULT 10;
ALTER TABLE regular_time_policy ADD punch_tag_selection_type_id SMALLINT DEFAULT 10;
ALTER TABLE regular_time_policy ADD exclude_default_punch_tag SMALLINT DEFAULT 0;

ALTER TABLE contributing_shift_policy ADD punch_tag_group_selection_type_id SMALLINT DEFAULT 10;
ALTER TABLE contributing_shift_policy ADD punch_tag_selection_type_id SMALLINT DEFAULT 10;
ALTER TABLE contributing_shift_policy ADD exclude_default_punch_tag SMALLINT DEFAULT 0;

ALTER TABLE over_time_policy ADD punch_tag_group_selection_type_id SMALLINT DEFAULT 10;
ALTER TABLE over_time_policy ADD punch_tag_selection_type_id SMALLINT DEFAULT 10;
ALTER TABLE over_time_policy ADD exclude_default_punch_tag SMALLINT DEFAULT 0;

ALTER TABLE premium_policy ADD punch_tag_group_selection_type_id SMALLINT DEFAULT 10;
ALTER TABLE premium_policy ADD punch_tag_selection_type_id SMALLINT DEFAULT 10;
ALTER TABLE premium_policy ADD exclude_default_punch_tag SMALLINT DEFAULT 0;

ALTER TABLE branch ADD user_group_selection_type_id SMALLINT DEFAULT 10;
ALTER TABLE branch ADD user_title_selection_type_id SMALLINT DEFAULT 10;
ALTER TABLE branch ADD user_default_branch_selection_type_id SMALLINT DEFAULT 10;
ALTER TABLE branch ADD include_user_default_branch_id SMALLINT DEFAULT 1;
ALTER TABLE branch ADD user_default_department_selection_type_id SMALLINT DEFAULT 10;

ALTER TABLE department ADD user_group_selection_type_id SMALLINT DEFAULT 10;
ALTER TABLE department ADD user_title_selection_type_id SMALLINT DEFAULT 10;
ALTER TABLE department ADD user_punch_branch_selection_type_id SMALLINT DEFAULT 10;
ALTER TABLE department ADD user_default_department_selection_type_id SMALLINT DEFAULT 10;
ALTER TABLE department ADD include_user_default_department_id SMALLINT DEFAULT 1;

ALTER TABLE company_generic_map ADD created_date integer;
ALTER TABLE company_generic_map ADD created_by uuid;
ALTER TABLE company_generic_map ADD	updated_date integer;
ALTER TABLE company_generic_map ADD updated_by uuid;
ALTER TABLE company_generic_map ADD	deleted_date integer;
ALTER TABLE company_generic_map ADD deleted_by uuid;
ALTER TABLE company_generic_map ADD	deleted SMALLINT DEFAULT 0 NOT NULL;
UPDATE company_generic_map SET updated_date = EXTRACT( 'epoch' from now() );

DROP TABLE IF EXISTS department_branch;
DROP TABLE IF EXISTS department_branch_user;