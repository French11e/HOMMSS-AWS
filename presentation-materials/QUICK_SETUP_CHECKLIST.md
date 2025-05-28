# QUICK SETUP CHECKLIST FOR PBL PRESENTATION
**5-Minute Pre-Presentation Setup**

---

## CRITICAL SETUP STEPS

### **1. DATABASE SETUP (2 minutes)**
```bash
# Set MySQL password to admin
sudo mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'admin';"

# Create database
mysql -u root -p'admin' -e "CREATE DATABASE IF NOT EXISTS hommss_aws CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
cd /var/www/html/HOMMSS-PHP
php artisan migrate --force

# Clear caches
php artisan config:clear
php artisan cache:clear
```

### **2. VERIFY SETUP (1 minute)**
```bash
# Test database connection
php artisan tinker --execute="echo 'Database: ' . DB::connection()->getDatabaseName();"

# Check if application starts
php artisan --version

# Verify routes
php artisan route:list | head -5
```

### **3. PREPARE TERMINALS (1 minute)**
```bash
# Terminal 1: Application (keep ready)
cd /var/www/html/HOMMSS-PHP

# Terminal 2: Log monitoring (keep ready)
cd /var/www/html/HOMMSS-PHP

# Terminal 3: Commands (keep ready)
cd /var/www/html/HOMMSS-PHP
```

### **4. TEST DEMO FLOW (1 minute)**
```bash
# Quick test - start app
php artisan serve --host=0.0.0.0 --port=8000 &

# Test if accessible
curl -I http://localhost:8000

# Stop test
pkill -f "artisan serve"
```

---

## PRESENTATION COMMANDS READY

### **Terminal 1: Application Server**
```bash
# Command ready to run:
php artisan serve --host=0.0.0.0 --port=8000
```

### **Terminal 2: Log Monitoring**
```bash
# Command ready to run:
tail -f storage/logs/laravel.log
```

### **Terminal 3: Demo Commands**
```bash
# Commands ready to demonstrate:
php artisan route:list | head -20
php artisan migrate:status
curl -I http://localhost:8000
php artisan list | grep backup
ls -la app/Http/Controllers/
```

---

## DEMO CREDENTIALS

### **Admin Login:**
- **URL:** http://your-server-ip:8000/admin
- **Email:** admin@demo.com
- **Password:** demo1234
- **OTP:** Will appear in Terminal 2 logs

### **Customer Login:**
- **URL:** http://your-server-ip:8000/login
- **Email:** customer@demo.com
- **Password:** demo1234
- **OTP:** Will appear in Terminal 2 logs

---

## BACKUP COMMANDS (If Needed)

### **If OTP Demo Fails:**
```bash
# Show security code
cat app/Http/Controllers/Auth/LoginController.php | grep -A 5 "generateOtp"

# Show professional structure
ls -la app/Http/
find app/ -name "*Controller.php" | head -5
```

### **If Application Won't Start:**
```bash
# Show code quality
cat composer.json | grep -A 5 "require"
ls -la database/migrations/
cat README.md | head -10
```

### **If Database Issues:**
```bash
# Show migration files
ls -la database/migrations/
cat .env | grep DB_ | head -5
```

---

## SUCCESS INDICATORS

### **What Should Work:**
- Database connection successful
- Application starts without errors
- Routes display properly
- Logs show real-time monitoring
- Security headers present

### **What to Emphasize:**
- **Professional code organization**
- **Enterprise-grade security**
- **Real-time monitoring**
- **Production-ready configuration**
- **Industry best practices**

---

## EMERGENCY FALLBACKS

### **If Technical Issues Occur:**
1. **Show documentation quality** (presentation-materials/)
2. **Demonstrate code structure** (app/ directory)
3. **Explain security features** (from code files)
4. **Highlight professional practices** (migrations, middleware)

### **Key Files to Reference:**
- `SECURITY_SCAN_REPORT_UPDATED.md` - Shows 98/100 security score
- `app/Http/Controllers/` - Professional code structure
- `app/Http/Middleware/` - Security implementation
- `database/migrations/` - Professional database design

---

## FINAL CHECKLIST

**Before Starting Presentation:**
- [ ] Database password set to "admin"
- [ ] Database "hommss_aws" created
- [ ] Migrations run successfully
- [ ] Application starts without errors
- [ ] Three terminals ready
- [ ] Demo credentials memorized
- [ ] Backup commands prepared

**During Presentation:**
- [ ] Emphasize enterprise-grade features
- [ ] Show real-time monitoring
- [ ] Demonstrate security implementation
- [ ] Highlight professional practices
- [ ] Connect to real-world applications

---

**You're ready to showcase a truly professional, enterprise-grade system!**
