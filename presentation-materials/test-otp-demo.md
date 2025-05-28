# OTP Demo Test Instructions

## Quick Test for Presentation

### Step 1: Start Application
```bash
php artisan serve --host=127.0.0.1 --port=8000
```

### Step 2: Monitor Logs (in separate terminal)
```bash
tail -f storage/logs/laravel.log | grep "DEMO OTP"
```

### Step 3: Test Login Flow
1. Go to: http://127.0.0.1:8000/login
2. Enter: `admin@demo.com` / `demo1234`
3. Click "Login"
4. Should redirect to OTP page
5. Check terminal for log entry like:
   ```
   [2025-01-XX XX:XX:XX] local.INFO: DEMO OTP Generated {"email":"admin@demo.com","otp":"123456","message":"Use this OTP for demo login"}
   ```
6. Copy the 6-digit OTP and enter it
7. Should successfully log in

### Expected Log Output
```
[timestamp] local.INFO: DEMO OTP Generated {"email":"admin@demo.com","otp":"XXXXXX","message":"Use this OTP for demo login"}
```

### For Presentation
- Keep the log terminal visible
- Explain: "For demo purposes, we log the OTP - in production this would only be sent via email"
- Emphasize the security: "Notice the OTP is hashed in the database for security"

### Demo Accounts
- Admin: admin@demo.com / demo1234
- Customer: customer@demo.com / demo1234

Both require OTP verification for complete security demonstration.
