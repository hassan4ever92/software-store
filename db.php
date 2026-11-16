<?php
require_once __DIR__ . '/config.php';

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0775, true);
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('CREATE TABLE IF NOT EXISTS products(
        id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL, solves TEXT, descr TEXT,
        features TEXT, specs TEXT, price DECIMAL(12,2) NOT NULL DEFAULT 0, hue INT DEFAULT 215,
        audiences VARCHAR(100), demo VARCHAR(255), shot VARCHAR(255), active TINYINT DEFAULT 1, sort INT DEFAULT 0
    ) CHARACTER SET utf8mb4');
    $pdo->exec('CREATE TABLE IF NOT EXISTS orders(
        id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255), phone VARCHAR(40), note TEXT, items TEXT,
        total DECIMAL(12,2), status VARCHAR(20) DEFAULT "new", created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) CHARACTER SET utf8mb4');
    // ترقية تلقائية: إضافة عمود التصنيف للمتاجر المنشورة سابقاً
    if (!$pdo->query("SHOW COLUMNS FROM products LIKE 'category'")->fetch()) {
        $pdo->exec("ALTER TABLE products ADD category VARCHAR(30) NOT NULL DEFAULT ''");
        $map = ['archive' => '%أرشفة%', 'hr' => '%موظفين%', 'health' => '%مرضى%', 'sales' => '%مبيعات%'];
        $up = $pdo->prepare("UPDATE products SET category=? WHERE category='' AND name LIKE ?");
        foreach ($map as $k => $like) $up->execute([$k, $like]);
        $pdo->exec("UPDATE products SET category='health' WHERE category='' AND name LIKE '%دم%'");
    }
    if ((int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn() === 0) seed($pdo);
    return $pdo;
}

function seed(PDO $pdo): void {
    $rows = [
     ['نظام أرشفة الكتب والمخاطبات','لا تضيع الكتب الرسمية بعد اليوم: ابحث عن أي كتاب صادر أو وارد في ثوانٍ.','أرشفة الكتب الرسمية وربطها بالمرفقات مع تتبّع حركة الكتاب بين الأقسام.',"ترقيم تلقائي للصادر والوارد\nرفع ملفات PDF وصور\nبحث بالتاريخ والجهة\nسجل تدقيق لكل عملية","لغة البرمجة: PHP 8\nقاعدة البيانات: MySQL\nالخادم: Windows Server + IIS\nالواجهة: عربي RTL\nالترخيص: نسخة لجهة واحدة",1200,215,'gov,biz','archive'],
     ['نظام إدارة شؤون الموظفين','ملفات الموظفين والإجازات والأوامر الإدارية في مكان واحد منظّم.','إدارة بيانات الموظفين والإجازات والأوامر الإدارية مع تقارير جاهزة للطباعة.',"ملف شخصي لكل موظف\nإجازات وغيابات\nأوامر إدارية\nتقارير PDF وExcel","لغة البرمجة: Node.js\nقاعدة البيانات: SQL Server\nالخادم: Windows Server\nالواجهة: عربي RTL\nالترخيص: نسخة لجهة واحدة",1500,265,'gov,biz','hr'],
     ['نظام إدارة المرضى','سجل واحد لكل مريض: زياراته وفحوصاته ومواعيده.','تسجيل المرضى وإدارة الزيارات والفحوصات والتقارير الطبية داخل المنشأة.',"سجل مريض موحّد\nحجز المواعيد\nنتائج الفحوصات\nصلاحيات حسب الدور","لغة البرمجة: PHP 8\nقاعدة البيانات: MySQL\nالخادم: IIS\nالواجهة: عربي RTL\nالترخيص: نسخة لمنشأة واحدة",1800,170,'health','health'],
     ['نظام إدارة مصارف الدم','اعرف مخزون الدم لكل فصيلة وتنبّه قبل انتهاء الصلاحية.','إدارة المتبرعين والوحدات المخزّنة وطلبات الصرف مع تنبيهات انتهاء الصلاحية.',"سجل المتبرعين\nمخزون حسب الفصيلة\nتنبيه انتهاء الصلاحية\nتقارير الصرف","لغة البرمجة: Python/Flask\nقاعدة البيانات: PostgreSQL\nالخادم: Windows Server\nالواجهة: عربي RTL\nالترخيص: نسخة لمستشفى واحد",2000,350,'health','health'],
     ['نظام إدارة المبيعات','فواتيرك ومخزونك وحسابات عملائك دون دفاتر ولا أخطاء.','مبيعات ومخزون للمحال والشركات الصغيرة مع طباعة الفواتير وكشف حساب العملاء.',"فواتير بيع وشراء\nإدارة المخزون\nكشف حساب العملاء\nتقارير يومية وشهرية","لغة البرمجة: Python/Flask\nقاعدة البيانات: SQLite\nالخادم: محلي أو سحابي\nالواجهة: عربي RTL\nالترخيص: نسخة لمتجر واحد",900,30,'biz','sales'],
    ];
    $st = $pdo->prepare('INSERT INTO products(name,solves,descr,features,specs,price,hue,audiences,category,sort) VALUES(?,?,?,?,?,?,?,?,?,?)');
    foreach ($rows as $i => $r) { $r[] = $i; $st->execute($r); }
}
