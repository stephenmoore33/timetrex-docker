UPDATE payroll_remittance_agency_event SET type_id = 'UI' WHERE type_id = 'PAYMENT+REPORT' AND payroll_remittance_agency_id IN ( SELECT id FROM payroll_remittance_agency WHERE agency_id IN ( '20:US:LA:00:0020', '20:US:MI:00:0020', '20:US:MN:00:0020', '20:US:NH:00:0020' ) );