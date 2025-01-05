CREATE TABLE system_job_queue (
    id uuid NOT NULL,
    batch_id uuid NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'::uuid,
    status_id smallint NOT NULL DEFAULT 10,
    priority smallint NOT NULL DEFAULT 50,
    name text NOT NULL,
    user_id uuid NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'::uuid,
    effective_date numeric(14, 4) NOT NULL,
    queued_date numeric(14, 4) NOT NULL,
    run_date numeric(14, 4),
    completed_date numeric(14, 4),
    retry_attempt integer NOT NULL DEFAULT 0,
    retry_max_attempt integer NOT NULL DEFAULT 0,
    class varchar(250),
    method varchar(250),
    extra_data JSON,
    arguments JSON,
    return_data JSON
);
CREATE UNIQUE INDEX system_job_queue_id ON system_job_queue(id);
CREATE INDEX system_job_queue_status_id ON system_job_queue(status_id);
CREATE INDEX system_job_queue_batch_id ON system_job_queue(batch_id);

ALTER TABLE user_default ADD enable_time_zone_auto_detect smallint NOT NULL DEFAULT 1;
ALTER TABLE roe ADD amended_roe_id UUID NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000'::uuid;

