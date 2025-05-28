# ⚡ QUICK COMMANDS CHEAT SHEET

## 🚀 **ESSENTIAL DEMO COMMANDS**

### **Start Application**
```bash
php artisan serve --host=127.0.0.1 --port=8000
```

### **Monitor OTP for Demo**
```bash
tail -f storage/logs/laravel.log | findstr "DEMO OTP"
```

### **Security Demo Commands**
```bash
# Show routes with security
php artisan route:list | head -20

# Create secure backup
php artisan app:backup-database --filename=demo-backup

# Show available backups
php artisan app:restore-database --interactive

# Check security headers
curl -I http://127.0.0.1:8000

# Show security logs
tail -20 storage/logs/laravel.log
```

### **Backup Commands**
```bash
php artisan app:backup-database
php artisan app:restore-database
php artisan backup:list
php artisan backup:run
```

### **System Status**
```bash
php artisan --version
php artisan migrate:status
php artisan config:cache
```

---

## 👥 **DEMO ACCOUNTS**

```
Admin Account:
Email: admin@demo.com
Password: demo1234
URL: http://127.0.0.1:8000/admin
OTP: Check logs with above command

Customer Account:
Email: customer@demo.com  
Password: demo1234
URL: http://127.0.0.1:8000
OTP: Check logs with above command
```

---

## 🎯 **PRESENTATION FLOW COMMANDS**

### **1. Opening Demo (Security Headers)**
```bash
curl -I http://127.0.0.1:8000
```

### **2. OTP Security Demo**
```bash
# In separate terminal
tail -f storage/logs/laravel.log | findstr "DEMO OTP"
# Then login with demo account
```

### **3. Backup Security Demo**
```bash
php artisan app:backup-database --filename=presentation-demo
php artisan backup:list
```

### **4. Admin Security Demo**
```bash
# Try to access admin as customer
# Show logs for unauthorized access
tail -5 storage/logs/laravel.log
```

### **5. Real-time Monitoring**
```bash
tail -f storage/logs/laravel.log | findstr "warning\|error\|security"
```

---

## 🔑 **KEY PHRASES**

- **"Military-grade AES-256-CBC encryption"**
- **"Enterprise-level security implementation"**  
- **"Fortune 500 company standards"**
- **"Production-ready architecture"**
- **"Zero SQL injection vulnerabilities"**
- **"Real-time security monitoring"**

---

## 🆘 **TROUBLESHOOTING**

### **If OTP doesn't appear in logs:**
```bash
# Check if demo accounts exist
php artisan tinker
User::where('email', 'admin@demo.com')->first();
exit
```

### **If application won't start:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan serve --host=127.0.0.1 --port=8000
```

### **If backup fails:**
```bash
# Check MySQL path in config/database.php
# Ensure BACKUP_PASSWORD is set in .env
```

---

## 📱 **QUICK TIPS**

1. **Always have two terminals open** - one for app, one for logs
2. **Test OTP flow before presentation** - Make sure it works
3. **Have backup demo ready** - Show encryption in action
4. **Know your security features** - Reference the documentation
5. **Practice the commands** - Smooth execution impresses

---

**REMEMBER: You have enterprise-grade security! Show it with confidence!** 🛡️✨
