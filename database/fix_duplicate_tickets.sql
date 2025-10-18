-- Fix duplicate lottery tickets issue
-- Add unique constraint to prevent duplicate tickets for same order

-- First, remove any duplicate tickets (keep only the first one for each order)
DELETE t1 FROM lottery_tickets t1
INNER JOIN lottery_tickets t2 
WHERE 
    t1.order_id = t2.order_id 
    AND t1.order_id IS NOT NULL
    AND t1.id > t2.id;

-- Add unique constraint on order_id to prevent future duplicates
ALTER TABLE lottery_tickets 
ADD UNIQUE KEY unique_order_ticket (order_id);

-- Note: This will fail if there are still duplicates after the DELETE above
-- If it fails, run the DELETE statement again

-- Verify no duplicates remain
SELECT order_id, COUNT(*) as count 
FROM lottery_tickets 
WHERE order_id IS NOT NULL
GROUP BY order_id 
HAVING count > 1;

-- If the above query returns no results, you're good!

