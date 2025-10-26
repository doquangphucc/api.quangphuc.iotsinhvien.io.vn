-- Add highlights column to packages table

ALTER TABLE packages 
ADD COLUMN highlights TEXT COMMENT 'JSON array of highlights: [{"title":"...", "content":"..."}]' 
AFTER payback_period;
