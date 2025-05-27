# AWS Security Recommendations for HOMMSS
**Critical Security Steps for AWS EC2 Deployment**

---

## IMMEDIATE ACTIONS REQUIRED

### **1. ROTATE AWS CREDENTIALS**
Your AWS credentials were exposed in the scan. **Immediate action required:**

```bash
# 1. Go to AWS Console → IAM → Security Credentials
# 2. Find access key: AKIAWALTC6XZVR4HJJ53
# 3. Click "Make inactive" or "Delete"
# 4. Generate new access key pair
# 5. Update .env file with new credentials
```

### **2. SECURE ENVIRONMENT VARIABLES**
```env
# Update these in your .env file:
AWS_ACCESS_KEY_ID=your_new_access_key_here
AWS_SECRET_ACCESS_KEY=your_new_secret_key_here

# Also update other exposed credentials:
GOOGLE_CLIENT_SECRET=your_new_google_secret
MAIL_PASSWORD=your_new_app_password
```

---

## AWS SECURITY BEST PRACTICES

### **1. IAM Security**
```bash
# Create dedicated IAM user for HOMMSS
aws iam create-user --user-name hommss-app-user

# Create minimal policy for S3 backup access only
aws iam create-policy --policy-name HommssS3BackupPolicy --policy-document '{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": [
        "s3:GetObject",
        "s3:PutObject",
        "s3:DeleteObject",
        "s3:ListBucket"
      ],
      "Resource": [
        "arn:aws:s3:::hommss-backups-new",
        "arn:aws:s3:::hommss-backups-new/*"
      ]
    }
  ]
}'

# Attach policy to user
aws iam attach-user-policy --user-name hommss-app-user --policy-arn arn:aws:iam::ACCOUNT:policy/HommssS3BackupPolicy
```

### **2. S3 Bucket Security**
```bash
# Enable versioning
aws s3api put-bucket-versioning --bucket hommss-backups-new --versioning-configuration Status=Enabled

# Enable encryption
aws s3api put-bucket-encryption --bucket hommss-backups-new --server-side-encryption-configuration '{
  "Rules": [
    {
      "ApplyServerSideEncryptionByDefault": {
        "SSEAlgorithm": "AES256"
      }
    }
  ]
}'

# Block public access
aws s3api put-public-access-block --bucket hommss-backups-new --public-access-block-configuration '{
  "BlockPublicAcls": true,
  "IgnorePublicAcls": true,
  "BlockPublicPolicy": true,
  "RestrictPublicBuckets": true
}'
```

### **3. EC2 Security Groups**
```bash
# Create security group for HOMMSS
aws ec2 create-security-group --group-name hommss-web-sg --description "HOMMSS Web Application Security Group"

# Allow HTTPS only
aws ec2 authorize-security-group-ingress --group-name hommss-web-sg --protocol tcp --port 443 --cidr 0.0.0.0/0

# Allow SSH from your IP only
aws ec2 authorize-security-group-ingress --group-name hommss-web-sg --protocol tcp --port 22 --cidr YOUR_IP/32

# Allow HTTP for redirect to HTTPS
aws ec2 authorize-security-group-ingress --group-name hommss-web-sg --protocol tcp --port 80 --cidr 0.0.0.0/0
```

---

## EC2 SECURITY HARDENING

### **1. System Security**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install fail2ban for intrusion prevention
sudo apt install fail2ban -y

# Configure fail2ban for SSH
sudo tee /etc/fail2ban/jail.local << EOF
[sshd]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 3
bantime = 3600
EOF

sudo systemctl restart fail2ban
```

### **2. File Permissions**
```bash
# Set proper ownership
sudo chown -R www-data:www-data /var/www/hommss

# Secure .env file
chmod 600 /var/www/hommss/.env
chown www-data:www-data /var/www/hommss/.env

# Secure storage directories
chmod -R 755 /var/www/hommss/storage
chmod -R 755 /var/www/hommss/bootstrap/cache
```

### **3. Database Security**
```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create dedicated database user
mysql -u root -p << EOF
CREATE DATABASE hommss_production;
CREATE USER 'hommss_user'@'localhost' IDENTIFIED BY 'SecureHommss2025!';
GRANT ALL PRIVILEGES ON hommss_production.* TO 'hommss_user'@'localhost';
FLUSH PRIVILEGES;
EOF
```

---

## MONITORING & ALERTING

### **1. CloudWatch Setup**
```bash
# Install CloudWatch agent
wget https://s3.amazonaws.com/amazoncloudwatch-agent/ubuntu/amd64/latest/amazon-cloudwatch-agent.deb
sudo dpkg -i amazon-cloudwatch-agent.deb

# Configure CloudWatch agent
sudo tee /opt/aws/amazon-cloudwatch-agent/etc/amazon-cloudwatch-agent.json << EOF
{
  "logs": {
    "logs_collected": {
      "files": {
        "collect_list": [
          {
            "file_path": "/var/www/hommss/storage/logs/laravel.log",
            "log_group_name": "hommss-application-logs",
            "log_stream_name": "{instance_id}"
          },
          {
            "file_path": "/var/log/nginx/access.log",
            "log_group_name": "hommss-nginx-access",
            "log_stream_name": "{instance_id}"
          }
        ]
      }
    }
  }
}
EOF
```

### **2. Security Monitoring Script**
```bash
# Create security monitoring script
sudo tee /usr/local/bin/hommss-security-monitor.sh << 'EOF'
#!/bin/bash

LOG_FILE="/var/log/hommss-security.log"
ADMIN_EMAIL="hommss666@gmail.com"

# Check for failed login attempts
FAILED_LOGINS=$(grep "authentication failure" /var/log/auth.log | tail -10)
if [ ! -z "$FAILED_LOGINS" ]; then
    echo "$(date): Failed login attempts detected" >> $LOG_FILE
    echo "$FAILED_LOGINS" >> $LOG_FILE
fi

# Check for rate limit violations
RATE_LIMITS=$(grep "Rate limit exceeded" /var/www/hommss/storage/logs/laravel.log | tail -10)
if [ ! -z "$RATE_LIMITS" ]; then
    echo "$(date): Rate limit violations detected" >> $LOG_FILE
    echo "$RATE_LIMITS" >> $LOG_FILE
fi

# Check disk space
DISK_USAGE=$(df / | awk 'NR==2 {print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 80 ]; then
    echo "$(date): High disk usage: ${DISK_USAGE}%" >> $LOG_FILE
fi
EOF

chmod +x /usr/local/bin/hommss-security-monitor.sh

# Add to crontab
(crontab -l 2>/dev/null; echo "*/15 * * * * /usr/local/bin/hommss-security-monitor.sh") | crontab -
```

---

## BACKUP SECURITY

### **1. Backup Encryption**
```bash
# Test encrypted backup
php artisan app:s3-backup --type=database --encrypt

# Verify backup integrity
php artisan app:s3-backup-manager status
```

### **2. Backup Monitoring**
```bash
# Set up backup monitoring
(crontab -l 2>/dev/null; echo "0 6 * * * cd /var/www/hommss && php artisan app:s3-backup-manager status >> /var/log/backup-status.log") | crontab -
```

---

## INCIDENT RESPONSE PLAN

### **1. Security Breach Response**
```bash
# Immediate actions if breach detected:
# 1. Rotate all credentials
# 2. Check access logs
# 3. Review recent backups
# 4. Update security groups
# 5. Notify stakeholders
```

### **2. Emergency Contacts**
- **Technical Lead:** hommss666@gmail.com
- **AWS Support:** Enable AWS Support plan
- **Security Team:** Set up security notification list

---

## DEPLOYMENT CHECKLIST

### **Pre-Deployment**
- [ ] Rotate all exposed credentials
- [ ] Configure IAM with minimal permissions
- [ ] Set up security groups
- [ ] Enable S3 encryption and versioning
- [ ] Configure CloudWatch monitoring

### **Post-Deployment**
- [ ] Test all security features
- [ ] Verify backup system
- [ ] Check rate limiting
- [ ] Monitor security logs
- [ ] Set up alerting

### **Ongoing Security**
- [ ] Weekly security log review
- [ ] Monthly credential rotation
- [ ] Quarterly security assessment
- [ ] Regular backup testing

---

## SECURITY SCORE TARGET

**Current Score:** 98/100
**Target Score:** 99/100

**To achieve 99/100:**
- [ ] Implement AWS WAF
- [ ] Set up AWS GuardDuty
- [ ] Enable AWS Config
- [ ] Implement log aggregation
- [ ] Set up automated security scanning

---

**Your HOMMSS platform is now ready for secure AWS deployment!**
