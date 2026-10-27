<?php
declare(strict_types=1);

const DB_HOST = 'sql101.infinityfree.com';
const DB_NAME = 'if0_42944056_wildvista';
const DB_USER = 'if0_42944056';
const DB_PASS = 'Dont2026care';
const SITE_NAME = 'Wildvista';
const SITE_TAGLINE = 'Explore Arusha Your Way';
const BASE_PATH = '/';
const CURRENCY = 'USD';
const ADMIN_EMAIL = 'akilykaaya@gmail.com';
const ADMIN_PHONE = '+255 794 872 433';
const SUPPORT_PHONE_2 = '+255 621 385 106';

function db(): mysqli {
    static $db;
    if ($db instanceof mysqli) return $db;
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($db->connect_errno) die('Database connection failed. Edit app/config/config.php.');
    $db->set_charset('utf8mb4');
    return $db;
}
