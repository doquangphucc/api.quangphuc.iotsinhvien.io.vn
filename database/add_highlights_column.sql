-- Add highlights column to packages table
-- Run this SQL file on your existing database

ALTER TABLE packages 
ADD COLUMN IF NOT EXISTS highlights TEXT COMMENT 'JSON array of highlights: [{"title":"...", "content":"..."}]' 
AFTER payback_period;
