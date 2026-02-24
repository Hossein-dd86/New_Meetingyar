<?php
// config/sms.php
return [
    'url' => env('SMS_URL'),
    'username' => env('SMS_USERNAME', 'admin'),  // نام کاربری مدیا پیامک
    'password' => env('SMS_PASSWORD', '123'),    // پسورد
    'from' => env('SMS_FROM', '1000'),           // شماره فرستنده
];
