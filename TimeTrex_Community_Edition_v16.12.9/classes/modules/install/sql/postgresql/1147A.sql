ALTER TABLE pay_stub_entry_account ADD group_id UUID DEFAULT '00000000-0000-0000-0000-000000000000';
ALTER TABLE pay_stub_entry_account ADD custom_field jsonb;
CREATE INDEX pay_stub_entry_account_custom_fields ON pay_stub_entry_account USING GIN ( custom_field );
