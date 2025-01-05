ALTER TABLE pay_stub_amendment ADD COLUMN branch_id uuid DEFAULT null;
ALTER TABLE pay_stub_amendment ADD COLUMN department_id uuid DEFAULT null;
ALTER TABLE pay_stub_amendment ADD COLUMN job_id uuid DEFAULT null;
ALTER TABLE pay_stub_amendment ADD COLUMN job_item_id uuid DEFAULT null;