-- Fix Mixed Content: Update HTTP URLs to HTTPS in database
-- Run this script to fix existing URLs that were saved as http://

-- Fix home_posts table
UPDATE home_posts 
SET image_url = REPLACE(image_url, 'http://', 'https://') 
WHERE image_url LIKE 'http://%';

UPDATE home_posts 
SET media_gallery = REPLACE(media_gallery, 'http://', 'https://') 
WHERE media_gallery LIKE '%http://%';

-- Fix intro_posts table (if exists)
UPDATE intro_posts 
SET image_url = REPLACE(image_url, 'http://', 'https://') 
WHERE image_url LIKE 'http://%';

UPDATE intro_posts 
SET video_url = REPLACE(video_url, 'http://', 'https://') 
WHERE video_url LIKE 'http://%';

-- Fix project_posts table (if exists)
UPDATE project_posts 
SET image_url = REPLACE(image_url, 'http://', 'https://') 
WHERE image_url LIKE 'http://%';

UPDATE project_posts 
SET media_gallery = REPLACE(media_gallery, 'http://', 'https://') 
WHERE media_gallery LIKE '%http://%';

-- Verify changes
SELECT 'home_posts' as table_name, COUNT(*) as records_with_http 
FROM home_posts 
WHERE image_url LIKE 'http://%' OR media_gallery LIKE '%http://%'
UNION ALL
SELECT 'intro_posts', COUNT(*) 
FROM intro_posts 
WHERE image_url LIKE 'http://%' OR video_url LIKE 'http://%'
UNION ALL
SELECT 'project_posts', COUNT(*) 
FROM project_posts 
WHERE image_url LIKE 'http://%' OR media_gallery LIKE '%http://%';

