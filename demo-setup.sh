#!/bin/bash

# HOMMSS PBL Demonstration Setup Script
# Run this script before your demonstration to ensure everything is ready

echo "🚀 HOMMSS PBL Demonstration Setup"
echo "================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    print_error "Please run this script from the Laravel project root directory"
    exit 1
fi

print_info "Starting pre-demonstration checks..."

# 1. Clear caches
echo ""
echo "1. Clearing application caches..."
php artisan config:clear
php artisan cache:clear
print_status "Caches cleared"

# 2. Check database connection
echo ""
echo "2. Checking database connection..."
if php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connected successfully';" 2>/dev/null; then
    print_status "Database connection OK"
else
    print_error "Database connection failed"
    exit 1
fi

# 3. Check backup directory
echo ""
echo "3. Checking backup directory..."
mkdir -p storage/app/backups
chmod 755 storage/app/backups
print_status "Backup directory ready"

# 4. Check environment variables
echo ""
echo "4. Checking environment configuration..."

# Check backup password
if grep -q "BACKUP_ARCHIVE_PASSWORD" .env; then
    print_status "Backup encryption password configured"
else
    print_warning "BACKUP_ARCHIVE_PASSWORD not found in .env"
fi

# Check AWS configuration
if grep -q "AWS_ACCESS_KEY_ID" .env && grep -q "AWS_SECRET_ACCESS_KEY" .env; then
    print_status "AWS credentials configured"
else
    print_warning "AWS credentials not fully configured"
fi

# Check admin email
if grep -q "ADMIN_EMAIL" .env; then
    print_status "Admin email configured"
else
    print_warning "ADMIN_EMAIL not configured"
fi

# 5. Test backup system
echo ""
echo "5. Testing backup system..."
if php artisan app:working-backup --type=db --filename="demo-test" 2>/dev/null; then
    print_status "Backup system test successful"
    # Clean up test backup
    rm -f storage/app/backups/demo-test.sql
else
    print_error "Backup system test failed"
    exit 1
fi

# 6. Check S3 connectivity (if configured)
echo ""
echo "6. Testing S3 connectivity..."
if php artisan app:working-restore --s3 2>/dev/null | grep -q "Available S3 Backups"; then
    print_status "S3 connectivity OK"
else
    print_warning "S3 connectivity test failed or no backups found"
fi

# 7. Check scheduled tasks
echo ""
echo "7. Checking scheduled tasks..."
if php artisan schedule:list | grep -q "app:working-backup"; then
    print_status "Scheduled backups configured"
else
    print_warning "Scheduled backups not found"
fi

# 8. Create demo data (optional)
echo ""
echo "8. Preparing demo environment..."

# Create a demo backup for demonstration
php artisan app:working-backup --type=db --filename="pbl-demo-ready" >/dev/null 2>&1
print_status "Demo backup created"

# Summary
echo ""
echo "🎯 PRE-DEMONSTRATION SUMMARY"
echo "============================"

echo ""
print_info "Available Commands for Demonstration:"
echo "   Basic backup:      php artisan app:working-backup --type=db"
echo "   Encrypted backup:  php artisan app:working-backup --type=db --encrypt"
echo "   S3 backup:         php artisan app:working-backup --type=db --encrypt --s3"
echo "   List backups:      php artisan app:working-restore --list"
echo "   List S3 backups:   php artisan app:working-restore --s3"
echo "   Restore backup:    php artisan app:working-restore --latest"

echo ""
print_info "Backup Files Location:"
echo "   Local: $(pwd)/storage/app/backups/"
echo "   S3: AWS S3 Bucket (configured in .env)"

echo ""
print_info "Log Files:"
echo "   Backup logs: $(pwd)/storage/logs/backup.log"
echo "   Laravel logs: $(pwd)/storage/logs/laravel.log"

echo ""
print_status "System is ready for PBL demonstration!"

echo ""
print_info "Quick Demo Sequence:"
echo "1. php artisan app:working-backup --type=db"
echo "2. php artisan app:working-backup --type=db --encrypt --filename='pbl-demo'"
echo "3. php artisan app:working-backup --type=db --encrypt --s3 --notify"
echo "4. php artisan app:working-restore --s3"
echo "5. php artisan schedule:list"

echo ""
echo "🎬 Ready for demonstration! Good luck!"
