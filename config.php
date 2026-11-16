<?php
// ===== إعدادات المتجر =====
const STORE_NAME = 'متجر الأنظمة';
const WHATSAPP   = '9647703956319';   // رقم الشركة بالصيغة الدولية (07703956319 → 964 + 7703956319)
const CURRENCY   = '$';
const EMAIL      = 'info@example.com';
const AUDIENCES  = ['gov' => 'جهة حكومية', 'health' => 'مستشفى أو عيادة', 'biz' => 'شركة أو متجر'];
const CATEGORIES = ['archive' => 'الأرشفة والمخاطبات', 'hr' => 'الموارد البشرية', 'health' => 'الأنظمة الصحية', 'sales' => 'المبيعات والمخازن'];
const TAGLINE    = 'أنظمة عربية جاهزة للجهات الحكومية والمستشفيات والشركات';
const DATA_DIR   = __DIR__ . '/data';   // يحفظ فيه ملف كلمة مرور المدير فقط

// ===== بيانات قاعدة MySQL (من لوحة InfinityFree ← MySQL Databases) =====
const DB_HOST = 'sqlXXX.infinityfree.com';
const DB_NAME = 'if0_XXXXXXXX_store';
const DB_USER = 'if0_XXXXXXXX';
const DB_PASS = 'ضع_كلمة_مرور_حساب_الاستضافة_هنا';

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
