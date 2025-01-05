ALTER TABLE recurring_schedule_control ALTER COLUMN user_id SET NOT NULL;
DROP TABLE IF EXISTS recurring_schedule_user;

CREATE UNIQUE INDEX idempotent_request_id ON idempotent_request( id );
CREATE INDEX hierarchy_object_type_hierarchy_control_id_object_type_id ON hierarchy_object_type( hierarchy_control_id, object_type_id );
CREATE INDEX hierarchy_level_user_id ON hierarchy_level( user_id );