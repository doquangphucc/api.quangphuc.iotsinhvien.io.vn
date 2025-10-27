#!/bin/bash
# Fix upload permissions for intro posts
# Run this on your production server

echo "🔧 Fixing upload permissions..."

# Change to project directory
cd /var/www/api.quangphuc.iotsinhvien.io.vn || exit

# Create upload directories if they don't exist
mkdir -p uploads/intro_images
mkdir -p uploads/intro_videos

# Set permissions
echo "📁 Setting permissions for uploads directory..."
chmod -R 755 uploads/

# Set ownership to web server user (usually www-data for Apache/Nginx)
echo "👤 Setting ownership..."
chown -R www-data:www-data uploads/ 2>/dev/null || echo "Note: Could not change ownership (may require sudo)"

# Verify permissions
echo "✅ Current permissions:"
ls -la uploads/

echo "✅ Done! Upload directories are ready."
echo ""
echo "If you still get permission errors, try:"
echo "  sudo chmod -R 777 uploads/"
echo "  sudo chown -R www-data:www-data uploads/"
