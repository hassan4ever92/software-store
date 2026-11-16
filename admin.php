<?php
// لوحة الإدارة: رئيسية + الأنظمة + الطلبات
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
session_start();
require_once __DIR__ . '/db.php';
$pdo = db();
$hashFile = DATA_DIR . '/admin.hash';
$err = '';
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
function csrf_ok(): bool { return hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? '')); }

function upload(string $field, array $exts, string $dir, int $max, ?string &$err = null): ?string {
    if (empty($_FILES[$field]['name'])) return null;
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        $m = [1 => 'حجم الملف أكبر من الحد المسموح في الاستضافة', 2 => 'حجم الملف أكبر من الحد المسموح', 3 => 'لم يكتمل رفع الملف', 6 => 'مجلد الرفع المؤقت مفقود', 7 => 'تعذّرت الكتابة على القرص', 8 => 'تم إيقاف الرفع'];
        $err = $m[$f['error']] ?? 'خطأ رفع رقم ' . $f['error']; return null;
    }
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $exts, true)) { $err = 'نوع الملف غير مسموح (.' . $ext . ')'; return null; }
    if ($f['size'] > $max) { $err = 'حجم الملف ' . round($f['size'] / 1048576, 1) . ' ميغابايت وهو أكبر من الحد (' . round($max / 1048576) . ' ميغابايت)'; return null; }
    if (!in_array($ext, ['html', 'htm'], true) && @getimagesize($f['tmp_name']) === false) { $err = 'الملف ليس صورة صالحة'; return null; }
    $abs = __DIR__ . '/uploads/' . $dir;
    if (!is_dir($abs)) @mkdir($abs, 0755, true);
    if (!is_writable($abs)) @chmod($abs, 0777);
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    if (@move_uploaded_file($f['tmp_name'], "$abs/$name") || @copy($f['tmp_name'], "$abs/$name")) return "uploads/$dir/$name";
    $err = 'تعذّر حفظ الملف، تحقق من صلاحيات المجلد uploads/' . $dir; return null;
}
function drop(?string $rel): void {
    if ($rel && strpos($rel, 'uploads/') === 0 && strpos($rel, '..') === false) @unlink(__DIR__ . '/' . $rel);
}
function wa_phone(string $p): string { // رقم عراقي محلي 07xx → 9647xx
    $d = preg_replace('/\D/', '', $p);
    if (strpos($d, '00') === 0) $d = substr($d, 2);
    if (strpos($d, '0') === 0) $d = '964' . substr($d, 1);
    return $d;
}
function pill(string $s): string {
    $m = ['new' => ['جديد', 'n'], 'contacted' => ['تم التواصل', 'c'], 'done' => ['مكتمل', 'd']];
    [$t, $c] = $m[$s] ?? $m['new'];
    return '<span class="pill ' . $c . '">' . $t . '</span>';
}
$ST = ['new' => 'جديد', 'contacted' => 'تم التواصل', 'done' => 'مكتمل'];
$IC = [
 'home' => '<path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/>',
 'box' => '<path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5z"/><path d="M3 7.5 12 12l9-4.5M12 12v9"/>',
 'cart' => '<path d="M5 3h14v18l-3-2-2 2-2-2-2 2-2-2-3 2z"/><path d="M9 8h6M9 12h6"/>',
];
$CSS = <<<'CSS'
:root{--ink:#13203a;--paper:#f6f4ef;--card:#fff;--mut:#5b6475;--line:#e6e2d8;--sun:#f5a524;--mint:#12a150;--red:#c5382c;--blue:#1a4fd6}
*{box-sizing:border-box;margin:0}
body{font-family:"Segoe UI",Tahoma,sans-serif;background:var(--paper);color:var(--ink);line-height:1.7;padding-bottom:76px;-webkit-text-size-adjust:100%}
a{color:var(--blue)}h1{font-size:1.3rem}h3{font-size:1.05rem;margin-bottom:.6rem}
.top{position:sticky;top:0;z-index:10;background:var(--ink);color:#fff}
.top .in{max-width:1100px;margin:auto;padding:.7rem 1rem;display:flex;align-items:center;gap:1rem}
.top .brand{font-weight:700;flex:1}.top nav{display:none;gap:.3rem}
.top a{color:#c9d2e3;text-decoration:none;padding:.4rem .8rem;border-radius:8px}.top a.on{background:#ffffff1f;color:#fff}
.tabbar{position:fixed;bottom:0;inset-inline:0;background:#fff;border-top:1px solid var(--line);display:grid;grid-template-columns:repeat(3,1fr);z-index:10;padding-bottom:env(safe-area-inset-bottom)}
.tabbar a{display:flex;flex-direction:column;align-items:center;padding:.45rem 0;font-size:.78rem;text-decoration:none;color:var(--mut)}
.tabbar a.on{color:var(--ink);font-weight:700}.tabbar svg{width:22px;height:22px;fill:none;stroke:currentColor;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round}.tabbar a.on svg{color:#d98a0b}
.w{max-width:1100px;margin:auto;padding:1rem}
.card{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:1rem;margin-bottom:1rem}
.hd{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:1rem}.hd h1{flex:1}
.stats{display:grid;grid-template-columns:1fr 1fr;gap:.7rem;margin-bottom:1rem}
.stat{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:.9rem}.stat b{display:block;font-size:1.6rem;line-height:1.3}.stat span{color:var(--mut);font-size:.85rem}
.btn,button{font:inherit;font-weight:600;border:0;border-radius:10px;padding:.5rem 1rem;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;min-height:42px;background:var(--ink);color:#fff}
.g{background:var(--mint)}.r{background:#fdecea;color:var(--red)}.s{background:#fff;color:var(--ink);border:1px solid var(--line)}.y{background:var(--sun);color:var(--ink)}.sm{min-height:36px;padding:.25rem .8rem;font-size:.88rem}
input,textarea,select{width:100%;padding:.65rem;border:1px solid #d9d4c7;border-radius:10px;font:inherit;font-size:16px;background:#fff}
label{display:block;margin:.8rem 0 .25rem;font-weight:600;font-size:.92rem}
.pill{font-size:.78rem;padding:.1rem .65rem;border-radius:99px;font-weight:600;white-space:nowrap}.pill.n{background:#fff3d6;color:#8a5a00}.pill.c{background:#e6eeff;color:#1a4fd6}.pill.d,.pill.v{background:#e2f5e9;color:#0d7a3a}.pill.h{background:#eee;color:#666}
.e,.m{padding:.7rem 1rem;border-radius:10px;margin-bottom:1rem}.e{background:#fde8e6;border:1px solid #f3b8b2}.m{background:#e7f6ec;border:1px solid #b9e3c6}
.items{display:grid;gap:.8rem;margin-bottom:1rem}.items .card{margin:0}
.it{display:grid;grid-template-columns:72px 1fr;gap:.2rem .8rem;align-items:center}
.ph{width:72px;height:72px;border-radius:12px;object-fit:cover;background:hsl(var(--h) 70% 92%);display:grid;place-items:center;font-weight:700;font-size:1.4rem;color:hsl(var(--h) 50% 30%)}
.nm{font-weight:700;line-height:1.45}.mt{color:var(--mut);font-size:.85rem}
.act{display:flex;gap:.4rem;flex-wrap:wrap;margin-top:.6rem;grid-column:1/-1}.act form{display:inline}
.row2{display:grid;grid-template-columns:1fr;gap:0 1rem}
.chk{display:flex;gap:.5rem;flex-wrap:wrap}.chk label{display:flex;align-items:center;gap:.4rem;font-weight:400;margin:0;background:var(--paper);padding:.5rem .8rem;border-radius:10px}.chk input{width:auto}
.savebar{position:sticky;bottom:80px;background:#fffffff2;border:1px solid var(--line);border-radius:14px;padding:.7rem;display:flex;gap:.6rem;box-shadow:0 -4px 16px rgba(0,0,0,.07)}.savebar .g{flex:1}
.pv{max-width:100%;max-height:180px;border-radius:10px;border:1px solid var(--line);margin:.4rem 0;display:block}
.filters{display:flex;gap:.4rem;overflow-x:auto;margin-bottom:1rem;scrollbar-width:none}.filters a{flex:none;text-decoration:none;color:var(--ink);background:#fff;border:1px solid var(--line);padding:.35rem .9rem;border-radius:99px;font-size:.9rem}.filters a.on{background:var(--ink);color:#fff;border-color:var(--ink)}
.oh{display:flex;justify-content:space-between;gap:.5rem;align-items:center;margin-bottom:.3rem}
.sw{display:inline-block;width:26px;height:26px;border-radius:8px;vertical-align:middle;border:1px solid var(--line)}
.note{font-size:.82rem;color:var(--mut);margin-bottom:1rem}
.lg{max-width:400px;margin:12vh auto 0}
@media(min-width:760px){body{padding-bottom:0}.tabbar{display:none}.top nav{display:flex}.stats{grid-template-columns:repeat(4,1fr)}.row2{grid-template-columns:1fr 1fr}.items{grid-template-columns:1fr 1fr}.savebar{bottom:1rem}}
CSS;
$head = '<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><meta name="robots" content="noindex"><title>لوحة الإدارة</title><style>' . $CSS . '</style></head><body>';

// ----- الدخول -----
if (isset($_GET['logout'])) { $_SESSION = []; session_destroy(); header('Location: admin.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_SESSION['admin'])) {
    $pw = (string)($_POST['password'] ?? '');
    if (!csrf_ok()) $err = 'انتهت الجلسة، أعد المحاولة';
    elseif (!file_exists($hashFile)) {
        if (strlen($pw) < 8) $err = 'كلمة المرور 8 أحرف على الأقل';
        elseif ($pw !== ($_POST['password2'] ?? '')) $err = 'كلمتا المرور غير متطابقتين';
        else { file_put_contents($hashFile, password_hash($pw, PASSWORD_DEFAULT)); session_regenerate_id(true); $_SESSION['admin'] = 1; header('Location: admin.php'); exit; }
    } elseif (password_verify($pw, trim(file_get_contents($hashFile)))) {
        session_regenerate_id(true); $_SESSION['admin'] = 1; header('Location: admin.php'); exit;
    } else { sleep(1); $err = 'كلمة المرور غير صحيحة'; }
}
if (empty($_SESSION['admin'])) {
    $first = !file_exists($hashFile);
    echo $head . '<div class="w"><div class="card lg"><h1>' . ($first ? 'إنشاء كلمة مرور المدير' : 'تسجيل الدخول') . '</h1>';
    if ($err) echo '<p class="e" style="margin-top:.8rem">' . h($err) . '</p>';
    echo '<form method="post"><input type="hidden" name="csrf" value="' . h($_SESSION['csrf']) . '"><label>كلمة المرور</label><input type="password" name="password" required autofocus>';
    if ($first) echo '<label>تأكيد كلمة المرور</label><input type="password" name="password2" required>';
    echo '<p style="margin-top:1rem"><button class="g" style="width:100%">' . ($first ? 'حفظ ودخول' : 'دخول') . '</button></p></form></div></div></body></html>';
    exit;
}

// ----- العمليات -----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) { http_response_code(400); exit('طلب غير صالح'); }
    $a = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($a === 'save') {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') $err = 'اسم النظام مطلوب';
        else {
            $aud = implode(',', array_intersect(array_keys(AUDIENCES), (array)($_POST['aud'] ?? [])));
            $old = $id ? $pdo->query('SELECT demo,shot FROM products WHERE id=' . $id)->fetch() : false;
            $old = $old ?: ['demo' => '', 'shot' => ''];
            $errs = []; $e1 = null; $e2 = null;
            $shot = upload('shot', ['png', 'jpg', 'jpeg', 'webp'], 'shots', 8_000_000, $e1);
            $demoUp = upload('demofile', ['html', 'htm'], 'demos', 10_000_000, $e2);
            if ($e1) $errs[] = 'لقطة الشاشة: ' . $e1;
            if ($e2) $errs[] = 'ملف المعاينة: ' . $e2;
            $_SESSION['flash'] = implode(' | ', $errs);
            $demo = $demoUp ?: trim($_POST['demo'] ?? '');
            if ($demoUp) drop($old['demo']);
            if ($shot) drop($old['shot']);
            $shotFinal = $shot ?: (!empty($_POST['rmshot']) ? '' : ($old['shot'] ?? ''));
            if (!$shot && !empty($_POST['rmshot'])) drop($old['shot']);
            $v = [$name, trim($_POST['solves'] ?? ''), trim($_POST['descr'] ?? ''), trim($_POST['features'] ?? ''), trim($_POST['specs'] ?? ''),
                  max(0, (float)($_POST['price'] ?? 0)), max(0, min(360, (int)($_POST['hue'] ?? 215))), $aud, $demo, $shotFinal, array_key_exists($_POST['category'] ?? '', CATEGORIES) ? $_POST['category'] : '',
                  empty($_POST['active']) ? 0 : 1, (int)($_POST['sort'] ?? 0)];
            if ($id) { $v[] = $id; $pdo->prepare('UPDATE products SET name=?,solves=?,descr=?,features=?,specs=?,price=?,hue=?,audiences=?,demo=?,shot=?,category=?,active=?,sort=? WHERE id=?')->execute($v); }
            else $pdo->prepare('INSERT INTO products(name,solves,descr,features,specs,price,hue,audiences,demo,shot,category,active,sort) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute($v);
            header('Location: admin.php?tab=products&m=saved'); exit;
        }
    } elseif ($a === 'toggle' && $id) {
        $pdo->prepare('UPDATE products SET active=1-active WHERE id=?')->execute([$id]); header('Location: admin.php?tab=products&m=1'); exit;
    } elseif ($a === 'del' && $id) {
        $o = $pdo->query('SELECT demo,shot FROM products WHERE id=' . $id)->fetch();
        if ($o) { drop($o['demo']); drop($o['shot']); }
        $pdo->prepare('DELETE FROM products WHERE id=?')->execute([$id]); header('Location: admin.php?tab=products&m=1'); exit;
    } elseif ($a === 'status' && $id) {
        $s = array_key_exists($_POST['status'] ?? '', $ST) ? $_POST['status'] : 'new';
        $pdo->prepare('UPDATE orders SET status=? WHERE id=?')->execute([$s, $id]); header('Location: admin.php?tab=orders&f=' . urlencode($_GET['f'] ?? 'all')); exit;
    } elseif ($a === 'odel' && $id) {
        $pdo->prepare('DELETE FROM orders WHERE id=?')->execute([$id]); header('Location: admin.php?tab=orders'); exit;
    }
}

$tab = $_GET['tab'] ?? 'home';
if (!in_array($tab, ['home', 'products', 'orders'], true)) $tab = 'home';
$editing = isset($_GET['edit']) || isset($_GET['new']);
if ($editing) $tab = 'products';
$tk = '<input type="hidden" name="csrf" value="' . h($_SESSION['csrf']) . '">';
$nav = ['home' => 'الرئيسية', 'products' => 'الأنظمة', 'orders' => 'الطلبات'];
$newOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status='new'")->fetchColumn();

echo $head;
?>
<header class="top"><div class="in"><div class="brand"><?= h(STORE_NAME) ?> · الإدارة</div>
<nav><?php foreach ($nav as $k => $t) echo '<a class="' . ($tab === $k ? 'on' : '') . '" href="admin.php?tab=' . $k . '">' . $t . ($k === 'orders' && $newOrders ? ' (' . $newOrders . ')' : '') . '</a>'; ?><a href="index.php" target="_blank">المتجر</a></nav>
<a href="admin.php?logout=1">خروج</a></div></header>
<nav class="tabbar"><?php foreach ($nav as $k => $t) echo '<a class="' . ($tab === $k ? 'on' : '') . '" href="admin.php?tab=' . $k . '"><svg viewBox="0 0 24 24">' . $IC[$k === 'home' ? 'home' : ($k === 'products' ? 'box' : 'cart')] . '</svg>' . $t . ($k === 'orders' && $newOrders ? ' (' . $newOrders . ')' : '') . '</a>'; ?></nav>
<div class="w">
<?php
if ($err) echo '<p class="e">' . h($err) . '</p>';
if (!empty($_SESSION['flash'])) { echo '<p class="e">تم حفظ بيانات النظام، لكن لم يُرفع ملف: ' . h($_SESSION['flash']) . '</p>'; unset($_SESSION['flash']); }
elseif (isset($_GET['m'])) echo '<p class="m">تمت العملية بنجاح</p>';

if ($tab === 'home') {
    $np = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    $na = (int)$pdo->query('SELECT COUNT(*) FROM products WHERE active=1')->fetchColumn();
    $nt = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
    $sum = (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status='done'")->fetchColumn();
    ?>
    <div class="hd"><h1>نظرة عامة</h1><a class="btn g sm" href="admin.php?new=1">+ إضافة نظام</a></div>
    <div class="stats">
      <div class="stat"><b><?= $na ?>/<?= $np ?></b><span>أنظمة ظاهرة في المتجر</span></div>
      <div class="stat"><b><?= $newOrders ?></b><span>طلبات جديدة</span></div>
      <div class="stat"><b><?= $nt ?></b><span>إجمالي الطلبات</span></div>
      <div class="stat"><b><?= number_format($sum) ?> <?= h(CURRENCY) ?></b><span>مبيعات مكتملة</span></div>
    </div>
    <div class="card"><div class="oh"><h3>آخر الطلبات</h3><a href="admin.php?tab=orders">عرض الكل</a></div>
    <?php $any = false; foreach ($pdo->query('SELECT * FROM orders ORDER BY id DESC LIMIT 5') as $o) { $any = true;
        echo '<div class="oh" style="padding:.5rem 0;border-top:1px solid var(--line)"><div><b>#' . $o['id'] . ' ' . h($o['name']) . '</b><div class="mt">' . h($o['created_at']) . '</div></div><div style="text-align:left">' . pill($o['status']) . '<div class="mt">' . number_format($o['total']) . ' ' . h(CURRENCY) . '</div></div></div>'; }
    if (!$any) echo '<p class="mt">لا توجد طلبات بعد.</p>'; ?></div>
    <?php
} elseif ($tab === 'orders') {
    $f = $_GET['f'] ?? 'all';
    if (!array_key_exists($f, $ST)) $f = 'all';
    $cnt = ['all' => (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn()];
    foreach ($ST as $k => $t) $cnt[$k] = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status='$k'")->fetchColumn();
    echo '<div class="hd"><h1>الطلبات</h1></div><div class="filters">';
    foreach (['all' => 'الكل'] + $ST as $k => $t) echo '<a class="' . ($f === $k ? 'on' : '') . '" href="admin.php?tab=orders&f=' . $k . '">' . $t . ' (' . $cnt[$k] . ')</a>';
    echo '</div><div class="items">';
    $q = $f === 'all' ? $pdo->query('SELECT * FROM orders ORDER BY id DESC LIMIT 200') : $pdo->prepare('SELECT * FROM orders WHERE status=? ORDER BY id DESC LIMIT 200');
    if ($f !== 'all') $q->execute([$f]);
    $any = false;
    foreach ($q as $o) { $any = true;
        $it = implode('', array_map(fn($i) => '<div>• ' . h($i['name']) . ' × ' . (int)$i['q'] . '</div>', json_decode($o['items'], true) ?: []));
        $wa = 'https://wa.me/' . wa_phone($o['phone']) . '?text=' . rawurlencode('مرحباً ' . $o['name'] . '، بخصوص طلبك رقم #' . $o['id'] . ' من ' . STORE_NAME);
        echo '<div class="card"><div class="oh"><b>#' . $o['id'] . ' · ' . h($o['name']) . '</b>' . pill($o['status']) . '</div><div class="mt">' . h($o['created_at']) . '</div>';
        echo '<div style="margin:.6rem 0">' . $it . '</div>' . ($o['note'] ? '<div class="mt">ملاحظات: ' . h($o['note']) . '</div>' : '');
        echo '<div class="oh" style="margin-top:.5rem"><b>' . number_format($o['total']) . ' ' . h(CURRENCY) . '</b><span class="mt" dir="ltr">' . h($o['phone']) . '</span></div>';
        echo '<div class="act"><a class="btn y sm" target="_blank" rel="noopener" href="' . h($wa) . '">رد عبر واتساب</a><a class="btn s sm" href="tel:' . h($o['phone']) . '">اتصال</a>';
        echo '<form method="post" action="admin.php?f=' . h($f) . '">' . $tk . '<input type="hidden" name="action" value="status"><input type="hidden" name="id" value="' . $o['id'] . '"><select name="status" onchange="this.form.submit()" style="min-height:36px;padding:.2rem .5rem;width:auto">';
        foreach ($ST as $k => $t) echo '<option value="' . $k . '"' . ($o['status'] === $k ? ' selected' : '') . '>' . $t . '</option>';
        echo '</select></form><form method="post" onsubmit="return confirm(\'حذف الطلب؟\')">' . $tk . '<input type="hidden" name="action" value="odel"><input type="hidden" name="id" value="' . $o['id'] . '"><button class="r sm">حذف</button></form></div></div>';
    }
    echo '</div>' . ($any ? '' : '<p class="mt">لا توجد طلبات في هذا القسم.</p>');
} elseif ($editing) {
    $id = (int)($_GET['edit'] ?? 0);
    $p = $id ? $pdo->query('SELECT * FROM products WHERE id=' . $id)->fetch() : false;
    $p = $p ?: ['id' => 0, 'name' => '', 'solves' => '', 'descr' => '', 'features' => '', 'specs' => '', 'price' => 0, 'hue' => 215, 'audiences' => '', 'demo' => '', 'shot' => '', 'category' => '', 'active' => 1, 'sort' => 0];
    $sel = explode(',', (string)$p['audiences']);
    ?>
    <div class="hd"><h1><?= $p['id'] ? 'تعديل نظام' : 'إضافة نظام' ?></h1><a class="btn s sm" href="admin.php?tab=products">رجوع</a></div>
    <form method="post" enctype="multipart/form-data"><?= $tk ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
    <div class="card"><h3>المعلومات الأساسية</h3>
      <label>اسم النظام</label><input name="name" required value="<?= h($p['name']) ?>">
      <label>جملة بسيطة تشرح ما يحله النظام (تظهر في البطاقة)</label><input name="solves" value="<?= h($p['solves']) ?>">
      <label>وصف النظام</label><textarea name="descr" rows="3"><?= h($p['descr']) ?></textarea>
      <div class="row2"><div><label>السعر الأساسي (<?= h(CURRENCY) ?>)</label><input type="number" step="any" min="0" inputmode="decimal" name="price" value="<?= h($p['price']) ?>"></div>
      <div><label>الترتيب في المتجر</label><input type="number" name="sort" value="<?= (int)$p['sort'] ?>"></div></div>
      <label>تصنيف النظام (يحدد القسم الذي يظهر فيه في المتجر)</label><select name="category"><option value="">— بدون تصنيف —</option><?php foreach (CATEGORIES as $k => $t) echo '<option value="' . $k . '"' . ($p['category'] === $k ? ' selected' : '') . '>' . h($t) . '</option>'; ?></select>
      <label>الفئات المستهدفة</label><div class="chk"><?php foreach (AUDIENCES as $k => $t) echo '<label><input type="checkbox" name="aud[]" value="' . $k . '"' . (in_array($k, $sel, true) ? ' checked' : '') . '> ' . h($t) . '</label>'; ?></div>
    </div>
    <div class="card"><h3>المزايا والمواصفات</h3>
      <label>المزايا (كل ميزة في سطر)</label><textarea name="features" rows="5"><?= h($p['features']) ?></textarea>
      <label>المواصفات الفنية (كل سطر: الاسم: القيمة)</label><textarea name="specs" rows="5"><?= h($p['specs']) ?></textarea>
    </div>
    <div class="card"><h3>الصورة والمعاينة</h3>
      <label>لقطة شاشة للنظام (png / jpg / webp، حتى 8 ميغابايت)</label>
      <?php if ($p['shot']) echo '<img class="pv" src="' . h($p['shot']) . '" alt=""><label style="font-weight:400;display:flex;gap:.4rem;align-items:center"><input type="checkbox" style="width:auto" name="rmshot" value="1"> حذف الصورة الحالية</label>'; ?>
      <img class="pv" id="pvShot" hidden alt=""><input type="file" name="shot" accept="image/png,image/jpeg,image/webp" data-prev="pvShot" data-max="8000000">
      <label>ملف HTML للمعاينة (حتى 10 ميغابايت)</label>
      <input type="file" name="demofile" accept=".html,.htm,text/html" data-prev="pvDemo" data-max="10000000"><div class="mt" id="pvDemo"></div>
      <label>أو مسار ملف المعاينة</label><input name="demo" dir="ltr" value="<?= h($p['demo']) ?>" placeholder="uploads/demos/archive.html">
      <?php if ($p['demo']) echo '<a class="mt" target="_blank" href="' . h($p['demo']) . '">فتح ملف المعاينة الحالي</a>'; ?>
      <label>لون النظام في الرسم التوضيحي (عند عدم وجود صورة) <span class="sw" id="sw" style="background:hsl(<?= (int)$p['hue'] ?> 70% 80%)"></span></label>
      <input type="range" name="hue" min="0" max="360" value="<?= (int)$p['hue'] ?>" oninput="sw.style.background='hsl('+this.value+' 70% 80%)'" style="padding:0">
    </div>
    <div class="card"><label class="chk" style="margin:0;display:flex;font-weight:400"><span style="display:flex;gap:.5rem;align-items:center"><input type="checkbox" style="width:auto" name="active" value="1"<?= $p['active'] ? ' checked' : '' ?>> ظاهر في المتجر</span></label></div>
    <div class="savebar"><button class="g">حفظ</button><a class="btn s" href="admin.php?tab=products">إلغاء</a></div>
    </form>
    <script>document.querySelectorAll('[data-prev]').forEach(i=>i.addEventListener('change',()=>{const f=i.files[0],t=document.getElementById(i.dataset.prev);if(!f)return;if(f.size>+i.dataset.max){alert('حجم الملف أكبر من الحد المسموح ('+(i.dataset.max/1e6)+' ميغابايت)');i.value='';return}if(t.tagName==='IMG'){t.src=URL.createObjectURL(f);t.hidden=false}else t.textContent=f.name}));</script>
    <?php
} else {
    $wr = fn($d) => is_writable(__DIR__ . '/uploads/' . $d) ? 'نعم' : '<b style="color:#c5382c">لا</b>';
    echo '<div class="hd"><h1>الأنظمة</h1><a class="btn g sm" href="admin.php?new=1">+ إضافة نظام</a></div><div class="items">';
    $any = false;
    foreach ($pdo->query('SELECT * FROM products ORDER BY sort,id') as $p) { $any = true;
        $aud = implode('، ', array_map(fn($k) => AUDIENCES[$k] ?? $k, array_filter(explode(',', (string)$p['audiences']))));
        echo '<div class="card"><div class="it">' . ($p['shot'] ? '<img class="ph" src="' . h($p['shot']) . '" alt="">' : '<div class="ph" style="--h:' . (int)$p['hue'] . '">' . h(mb_substr(str_replace('نظام ', '', $p['name']), 0, 1)) . '</div>');
        echo '<div><div class="nm">' . h($p['name']) . '</div><div class="mt">' . number_format($p['price']) . ' ' . h(CURRENCY) . ($aud ? ' · ' . h($aud) : '') . '</div><div style="margin-top:.2rem"><span class="pill ' . ($p['active'] ? 'v' : 'h') . '">' . ($p['active'] ? 'ظاهر' : 'مخفي') . '</span>' . ($p['demo'] ? ' <span class="pill c">معاينة</span>' : '') . '</div></div>';
        echo '<div class="act"><a class="btn s sm" href="admin.php?edit=' . $p['id'] . '">تعديل</a>';
        echo '<form method="post">' . $tk . '<input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="' . $p['id'] . '"><button class="s sm" style="border:1px solid var(--line)">' . ($p['active'] ? 'إخفاء' : 'إظهار') . '</button></form>';
        echo '<form method="post" onsubmit="return confirm(\'حذف النظام نهائياً؟\')">' . $tk . '<input type="hidden" name="action" value="del"><input type="hidden" name="id" value="' . $p['id'] . '"><button class="r sm">حذف</button></form></div></div></div>';
    }
    echo '</div>' . ($any ? '' : '<p class="mt">لا توجد أنظمة بعد.</p>');
    echo '<p class="note">فحص الرفع: أقصى حجم للملف ' . h(ini_get('upload_max_filesize')) . ' | أقصى حجم للطلب ' . h(ini_get('post_max_size')) . ' | الكتابة في uploads/shots: ' . $wr('shots') . ' | uploads/demos: ' . $wr('demos') . '</p>';
}
?>
</div></body></html>
