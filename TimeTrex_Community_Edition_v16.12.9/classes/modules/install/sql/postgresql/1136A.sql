--Fix issue where the same statements in 1070A may not have been applied due to a timing of the release or that they were in a different schema group and got moved;
ALTER TABLE qualification ADD COLUMN IF NOT EXISTS source_type_id integer NOT NULL DEFAULT 10;
ALTER TABLE qualification ADD COLUMN IF NOT EXISTS visibility_type_id integer NOT NULL DEFAULT 10;