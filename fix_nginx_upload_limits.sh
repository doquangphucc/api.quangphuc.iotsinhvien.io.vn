#!/bin/bash
# Script to fix Nginx upload limits for intro media
# Run this on your production server

echo "🔧 Fixing Nginx upload limits for intro media uploads..."

# Backup current Nginx config
echo "📦 Backing up current Nginx configuration..."
sudo cp /etc/nginx/sites-available/api.quangphuc.iotsinhvien.io.vn /etc/nginx/sites-available/api.quangphuc.iotsinhvien.io.vn.backup_$(date +%Y%m%d_%H%M%S)

# Find and replace client_max_body_size in /api/ location block
echo "📝 Updating client_max_body_size for /api/ location..."
sudo sed -i 's/client_max_body_size 10M/client_max_body_size 100M/g' /etc/nginx/sites-available/api.quangphuc.iotsinhvien.io.vn

# Update PHP upload limits in fastcgi_param
echo "📝 Updating PHP upload limits..."
sudo sed -i 's/upload_max_filesize=5M/upload_max_filesize=50M/g' /etc/nginx/sites-available/api.quangphuc.iotsinhvien.io.vn
sudo sed -i 's/post_max_size=10M/post_max_size=60M/g' /etc/nginx/sites-available/api.quangphuc.iotsinhvien.io.vn
sudo sed -i 's/max_execution_time=300/max_execution_time=600/g' /etc/nginx/sites-available/api.quangphuc.iotsinhvien.io.vn
sudo sed -i 's/max_input_time=300/max_input_time=600/g' /etc/nginx/sites-available/api.quangphuc.iotsinhvien.io.vn

# Add memory_limit if not exists
if ! grep -q "memory_limit=" /etc/nginx/sites-available/api.quangphuc.iotsinhvien.io.vn; then
    echo "📝 Adding memory_limit=256M..."
    sudo sed -i '/max_input_time=600/a\                                     memory_limit=256M' /etc/nginx/sites-available/api.quangphuc.iotsinhvien.io.vn
fi

# Test Nginx configuration
echo "🧪 Testing Nginx configuration..."
sudo nginx -t

if [ $? -eq 0 ]; then
    echo "✅ Nginx configuration is valid!"
    echo "🔄 Reloading Nginx..."
    sudo systemctl reload nginx
    
    if [ $? -eq 0 ]; then
        echo "✅ Nginx reloaded successfully!"
        echo "🎉 Upload limits updated:"
        echo "   - client_max_body_size: 100M"
        echo "   - upload_max_filesize: 50M"
        echo "   - post_max_size: 60M"
        echo "   - max_execution_time: 600s"
        echo "   - memory_limit: 256M"
    else
        echo "❌ Failed to reload Nginx"
        exit 1
    fi
else
    echo "❌ Nginx configuration test failed!"
    echo "ℹ️  Restore from backup: cp /etc/nginx/sites-available/api.quangphuc.iotsinhvien.io.vn.backup_* /etc/nginx/sites-available/api.quangphuc.iotsinhvien.io.vn"
    exit 1
fi
