<?php
// استقبال الطلبات: يحفظ الطلب ويعيد رابط واتساب. الأسعار تُحسب من قاعدة البيانات لا من المتصفح.
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

function fail(string $m, int $c = 400) { http_response_code($c); echo json_encode(['ok' => false, 'error' => $m], JSON_UNESCAPED_UNICODE); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('طريقة غير مسموحة', 405);

$in    = json_decode(file_get_contents('php://input'), true) ?: [];
$name  = trim((string)($in['name'] ?? ''));
$phone = trim((string)($in['phone'] ?? ''));
$note  = mb_substr(trim((string)($in['note'] ?? '')), 0, 500);
if ($name === '' || mb_strlen($name) > 120) fail('أدخل الاسم');
if (!preg_match('/^[0-9+\s\-()]{6,20}$/', $phone)) fail('رقم الهاتف غير صحيح');
$req = is_array($in['items'] ?? null) ? array_slice($in['items'], 0, 20) : [];
if (!$req) fail('السلة فارغة');

$pdo = db(); $st = $pdo->prepare('SELECT id,name,price FROM products WHERE id=? AND active=1');
$items = []; $total = 0;
foreach ($req as $r) {
    $st->execute([(int)($r['id'] ?? 0)]);
    if (!$p = $st->fetch()) continue;
    $q = max(1, min(20, (int)($r['q'] ?? 1)));
    $items[] = ['id' => (int)$p['id'], 'name' => $p['name'], 'price' => (float)$p['price'], 'q' => $q];
    $total += $p['price'] * $q;
}
if (!$items) fail('الأنظمة المطلوبة غير متاحة');

$pdo->prepare('INSERT INTO orders(name,phone,note,items,total) VALUES(?,?,?,?,?)')
    ->execute([$name, $phone, $note, json_encode($items, JSON_UNESCAPED_UNICODE), $total]);
$id = $pdo->lastInsertId();

$lines = array_map(fn($i) => "- {$i['name']} × {$i['q']}: " . number_format($i['price'] * $i['q']) . ' ' . CURRENCY, $items);
$msg = "طلب جديد رقم #$id\nالاسم: $name\nالهاتف: $phone\n" . implode("\n", $lines) . "\nالإجمالي: " . number_format($total) . ' ' . CURRENCY . "\nملاحظات: " . ($note ?: '—');
echo json_encode(['ok' => true, 'id' => $id, 'wa' => 'https://wa.me/' . WHATSAPP . '?text=' . rawurlencode($msg)], JSON_UNESCAPED_UNICODE);
