ALTER TABLE exception_policy ADD COLUMN punch_notification_id INTEGER DEFAULT 0;
UPDATE exception_policy SET punch_notification_id = 10 WHERE type_id in ( 'S2', 'S3', 'B2', 'L2' );
