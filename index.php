<?php
require_once __DIR__ . '/db.php';
$P = [];
foreach (db()->query('SELECT * FROM products WHERE active=1 ORDER BY sort,id') as $r) {
    $specs = [];
    foreach (preg_split('/\R/u', (string)$r['specs']) as $l) {
        if (strpos($l, ':') !== false) { [$k, $v] = array_map('trim', explode(':', $l, 2)); $specs[] = [$k, $v]; }
    }
    $P[] = [
        'id' => (int)$r['id'], 'cat' => (string)($r['category'] ?? ''), 'name' => $r['name'], 'solves' => $r['solves'], 'desc' => $r['descr'],
        'features' => array_values(array_filter(array_map('trim', preg_split('/\R/u', (string)$r['features'])))),
        'specs' => $specs, 'price' => (float)$r['price'], 'hue' => (int)$r['hue'],
        'aud' => array_values(array_filter(explode(',', (string)$r['audiences']))),
        'demo' => $r['demo'], 'shot' => $r['shot'],
    ];
}
$J = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= h(STORE_NAME) ?> | <?= h(TAGLINE) ?></title>
<meta name="description" content="<?= h(TAGLINE) ?>">
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--teal:#22a39e;--teal-d:#1b8782;--teal-l:#e8f5f4;--ink:#1a1a1a;--mut:#5f6368;--line:#eeecec;--bg:#f6f8f8;--r:12px}
*{box-sizing:border-box;margin:0}
html{scroll-behavior:smooth;scroll-padding-top:130px;-webkit-text-size-adjust:100%}
body{font-family:"IBM Plex Sans Arabic",Tahoma,sans-serif;background:#fff;color:var(--ink);line-height:1.7;overflow-x:hidden}
.wrap{max-width:1250px;margin:auto;padding:0 1rem}
button,.btn{font:inherit;font-weight:600;cursor:pointer;border:0;border-radius:8px;padding:.55rem 1.1rem;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:.4rem;min-height:44px;text-align:center}
:focus-visible{outline:3px solid var(--teal);outline-offset:2px}
.pri{background:var(--teal);color:#fff}.pri:hover{background:var(--teal-d)}
.out{background:#fff;color:var(--teal-d);border:1.5px solid var(--teal)}.out:hover{background:var(--teal-l)}
.dark{background:var(--ink);color:#fff}.wa{background:#25d366;color:#fff}
.ghost{background:transparent;color:var(--mut);text-decoration:underline;font-weight:500;min-height:36px}
svg.i{width:22px;height:22px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;flex:none}

/* شريط الإعلانات المتحرك */
.ticker{background:var(--teal);color:#fff;overflow:hidden;white-space:nowrap;font-weight:600;font-size:.92rem;padding:.45rem 0}
.ticker{direction:ltr}.ticker div{display:inline-block;padding-left:100%;animation:tk 38s linear infinite}
.ticker span{margin-inline:1.6rem}.ticker span::before{content:"•";margin-inline-end:1.6rem;opacity:.8}
@keyframes tk{to{transform:translateX(-100%)}}

/* الرأس */
header{background:#fff;position:sticky;top:0;z-index:20;box-shadow:0 1px 0 var(--line)}
.bar{display:flex;align-items:center;gap:1.4rem;padding-block:.8rem}
.logo{display:flex;align-items:center;gap:.5rem;font-weight:700;font-size:1.3rem;color:var(--ink);text-decoration:none;white-space:nowrap}
.logo i{width:38px;height:38px;border-radius:10px;background:var(--teal);color:#fff;display:grid;place-items:center;font-style:normal;font-size:1.1rem}
.logo b{color:var(--teal)}
.search{flex:1;position:relative;max-width:640px;margin-inline:auto}
.search input{width:100%;padding:.65rem 1.2rem .65rem 3rem;border:1.5px solid var(--line);border-radius:99px;font:inherit;background:#fff}
.search input:focus{border-color:var(--teal);outline:0}
.search svg{position:absolute;inset-inline-end:auto;left:1rem;top:50%;transform:translateY(-50%);color:var(--ink);width:20px;height:20px}
.icons{display:flex;gap:.2rem;align-items:center}
.ib{background:transparent;color:var(--ink);padding:.4rem;min-width:44px;position:relative;border-radius:50%}
.ib:hover{background:var(--bg)}
#cartCount{position:absolute;top:2px;inset-inline-start:0;background:var(--teal);color:#fff;border-radius:99px;min-width:19px;height:19px;font-size:.72rem;display:grid;place-items:center;padding:0 4px}
#cartCount:empty,#cartCount[data-n="0"]{display:none}
.cats{background:var(--bg);border-top:1px solid var(--line)}
.cats .wrap{display:flex;gap:.2rem;overflow-x:auto;scrollbar-width:none}
.cats .wrap::-webkit-scrollbar{display:none}
.cats button{background:transparent;color:var(--ink);font-weight:600;font-size:.95rem;border-radius:0;white-space:nowrap;border-bottom:3px solid transparent;min-height:48px;padding-inline:1rem}
.cats button:hover,.cats button.on{color:var(--teal-d);border-bottom-color:var(--teal)}

/* البانر */
.hero{position:relative;overflow:hidden;background:var(--bg)}
.slides{display:flex;transition:transform .6s cubic-bezier(.4,0,.2,1)}
.slide{min-width:100%;display:grid;grid-template-columns:1fr 1fr;align-items:center;gap:2rem;padding:2.5rem max(1rem,calc((100vw - 1250px)/2 + 1rem)) 3.2rem;min-height:400px;background:linear-gradient(110deg,hsl(var(--h) 60% 97%) 0%,hsl(var(--h) 55% 91%) 100%)}
.slide small{color:var(--teal-d);font-weight:700;letter-spacing:.02em}
.slide h2{font-size:clamp(1.7rem,4vw,2.9rem);line-height:1.3;font-weight:700;margin:.3rem 0 .6rem}
.slide p{color:var(--mut);max-width:44ch;font-size:1.05rem;margin-bottom:1.4rem}
.slide .pr{font-weight:700;font-size:1.15rem;margin-bottom:1rem}.slide .row{display:flex;gap:.6rem;flex-wrap:wrap}
.slide .art{max-width:520px;width:100%;margin-inline:auto}
.dots{position:absolute;bottom:1rem;inset-inline:0;display:flex;justify-content:center;gap:.5rem}
.dots button{width:10px;height:10px;min-height:0;padding:0;border-radius:99px;background:#00000030}.dots button.on{background:var(--teal);width:28px}
.arr{position:absolute;top:50%;transform:translateY(-50%);width:44px;height:44px;border-radius:50%;background:#ffffffe6;color:var(--ink);box-shadow:0 2px 10px rgba(0,0,0,.15);padding:0}
.arr.p{inset-inline-start:1rem}.arr.n{inset-inline-end:1rem}

/* نوافذ الرسم التوضيحي */
.win{background:#fff;border-radius:12px;border:1px solid var(--line);box-shadow:0 14px 36px rgba(0,0,0,.12);overflow:hidden;color:var(--ink)}
.wb{display:flex;gap:5px;align-items:center;padding:.5rem .7rem;background:#f1f3f3;font-size:.72rem;color:var(--mut)}
.wb i{width:8px;height:8px;border-radius:50%;background:#d3d8d8}.wb span{margin-inline-start:.5rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.wc{display:grid;grid-template-columns:72px 1fr;height:150px}
.sb{background:hsl(var(--h) 55% 22%);padding:10px 8px;display:flex;flex-direction:column;gap:7px}
.sb b{height:8px;border-radius:4px;background:#ffffff40}.sb b:first-child{background:hsl(var(--h) 85% 68%)}
.mn{padding:10px;display:flex;flex-direction:column;gap:8px}
.tiles{display:grid;grid-template-columns:repeat(3,1fr);gap:6px}
.tiles u{height:34px;border-radius:6px;background:hsl(var(--h) 75% 94%);border:1px solid hsl(var(--h) 55% 86%)}
.rows{display:flex;flex-direction:column;gap:6px}.rows s{height:10px;border-radius:5px;background:#eceae4}.rows s:nth-child(2){width:82%}.rows s:nth-child(3){width:64%}
.big .wc{height:260px;grid-template-columns:110px 1fr}.big .tiles u{height:58px}.big .rows s{height:13px}.big .sb{gap:12px;padding:16px 12px}.big .sb b{height:10px}
.shot{width:100%;display:block;border-radius:12px;object-fit:cover;object-position:top;aspect-ratio:16/10;background:#fff}
.big.shot,img.big{aspect-ratio:auto;max-height:340px;object-fit:contain;box-shadow:0 14px 36px rgba(0,0,0,.12)}

/* التصنيفات */
.sec{padding-block:2.2rem;border-bottom:1px solid var(--line)}
.sec:last-of-type{border-bottom:0}
.sh{display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-bottom:1.4rem;flex-wrap:wrap}
.sh h2{font-size:1.75rem;font-weight:700}
.tilesc{display:grid;grid-template-columns:repeat(4,1fr);gap:1.2rem}
.tc{background:none;border:0;padding:0;text-align:center;display:block;color:var(--ink);min-height:0}
.tc .th{border-radius:16px;overflow:hidden;background:linear-gradient(135deg,#eef1f1,#d4e9ec);aspect-ratio:2/1;display:grid;place-items:center;padding:1rem;transition:transform .2s}
.tc:hover .th{transform:translateY(-3px)}
.tc .th .win{width:62%;box-shadow:0 6px 16px rgba(0,0,0,.1)}.tc .th .wc{height:70px;grid-template-columns:34px 1fr}.tc .th .tiles u{height:16px}.tc .th .rows s{height:6px}.tc .th .wb{padding:.25rem .5rem}.tc .th .wb span{display:none}
.tc strong{display:block;margin-top:.7rem;font-size:1rem}.tc small{color:var(--mut)}

/* الأعمدة التقنية بدل العلامات التجارية */
.tech{display:flex;justify-content:space-between;align-items:center;gap:1.4rem;flex-wrap:wrap;opacity:.85}
.tech span{font-weight:700;font-size:1.35rem;color:#444;letter-spacing:.01em}.tech span:nth-child(3n+1){color:var(--teal-d)}
.tech span{font-family:Tahoma,"IBM Plex Sans Arabic",sans-serif}

/* الفلتر حسب الجهة */
.aud{display:flex;gap:.5rem;overflow-x:auto;scrollbar-width:none;align-items:center;margin-bottom:1.2rem}
.aud::-webkit-scrollbar{display:none}
.aud strong{white-space:nowrap}
.aud button{flex:none;background:#fff;border:1.5px solid var(--line);color:var(--ink);font-weight:500;border-radius:99px;min-height:40px;white-space:nowrap}
.aud button.on{background:var(--teal);border-color:var(--teal);color:#fff}

/* بطاقات المنتجات */
.grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1.3rem}
.card{display:flex;flex-direction:column;background:#fff;border:1px solid var(--line);border-radius:var(--r);overflow:hidden;transition:box-shadow .2s,transform .2s}
.card:hover{box-shadow:0 10px 28px rgba(0,0,0,.09);transform:translateY(-2px)}
.thumb{background:var(--bg);padding:1.1rem 1.1rem 0;cursor:pointer;min-height:170px;display:flex;flex-direction:column;justify-content:flex-end;position:relative}
.thumb .win{border-bottom-left-radius:0;border-bottom-right-radius:0;border-bottom:0;box-shadow:none}
.thumb .shot{border-radius:10px 10px 0 0}
.badge{position:absolute;top:.7rem;inset-inline-start:.7rem;background:var(--teal);color:#fff;font-size:.72rem;font-weight:700;padding:.1rem .6rem;border-radius:99px;z-index:1}
.cb{padding:1rem;display:flex;flex-direction:column;gap:.45rem;flex:1}
.cb h3{font-size:1rem;line-height:1.5;font-weight:600;min-height:3em;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;cursor:pointer}
.cb p{color:var(--mut);font-size:.88rem;flex:1;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
.tg{display:flex;gap:.3rem;flex-wrap:wrap}.tg span{font-size:.72rem;background:var(--teal-l);color:var(--teal-d);border-radius:99px;padding:0 .6rem;font-weight:500}
.price{font-size:1.3rem;font-weight:700}.price small{display:block;font-weight:400;font-size:.75rem;color:var(--mut)}
.acts{display:grid;grid-template-columns:1fr auto;gap:.4rem;margin-top:.3rem}
.acts .ib{border:1.5px solid var(--line);border-radius:8px;min-height:44px}
.empty{grid-column:1/-1;text-align:center;color:var(--mut);padding:3rem 1rem}

/* مزايا + خطوات + أسئلة */
.perks{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem}
.perk{display:flex;gap:.8rem;align-items:flex-start;padding:1rem;background:var(--bg);border-radius:var(--r)}
.perk svg{color:var(--teal);width:30px;height:30px}.perk h3{font-size:1rem}.perk p{color:var(--mut);font-size:.88rem}
.steps{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;counter-reset:s}
.steps div{background:#fff;border:1px solid var(--line);border-radius:var(--r);padding:1.2rem;counter-increment:s}
.steps div::before{content:counter(s);display:grid;place-items:center;width:36px;height:36px;border-radius:50%;background:var(--teal);color:#fff;font-weight:700;margin-bottom:.5rem}
.steps h3{font-size:1.05rem}.steps p{color:var(--mut);font-size:.92rem}
details{border:1px solid var(--line);border-radius:10px;padding:.8rem 1rem;margin-bottom:.5rem}summary{cursor:pointer;font-weight:600}details p{color:var(--mut);margin-top:.4rem}

/* التذييل */
footer{background:#111;color:#c5c9c9;margin-top:2rem;padding-top:2.5rem;font-size:.92rem}
.fg{display:grid;grid-template-columns:1.4fr 1fr 1fr 1fr;gap:2rem;padding-bottom:2rem}
footer h4{color:#fff;margin-bottom:.7rem;font-size:1rem}
footer a{color:#c5c9c9;text-decoration:none;display:block;padding:.15rem 0;cursor:pointer}footer a:hover{color:var(--teal)}
.copy{border-top:1px solid #2a2a2a;padding:1rem 0;text-align:center;font-size:.85rem}
.fab{position:fixed;bottom:calc(1rem + env(safe-area-inset-bottom));inset-inline-end:1rem;z-index:15;border-radius:99px;box-shadow:0 6px 18px rgba(0,0,0,.25);padding:.75rem 1.2rem}

/* النوافذ والسلة */
.modal{position:fixed;inset:0;background:rgba(0,0,0,.55);display:none;align-items:center;justify-content:center;z-index:40;padding:1rem}.modal.open{display:flex}
.mb{background:#fff;border-radius:16px;width:100%;max-width:680px;max-height:90vh;overflow:auto;padding:1.3rem}
.mb.wide{max-width:1000px;height:88vh;display:flex;flex-direction:column;padding:.8rem}
.mb iframe{flex:1;width:100%;border:1px solid var(--line);border-radius:8px;background:#fff}
.mb h2{font-size:1.4rem;margin:.8rem 0 .3rem}.mb ul{padding-inline-start:1.2rem;margin:.4rem 0}
table{width:100%;border-collapse:collapse;margin:.6rem 0;font-size:.92rem}td{padding:.45rem .2rem;border-bottom:1px solid var(--line)}td:first-child{color:var(--mut);width:34%}
.scrim{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:49;display:none}.scrim.open{display:block}
.drawer{position:fixed;top:0;bottom:0;inset-inline-end:0;width:min(420px,100%);background:#fff;z-index:50;transform:translateX(-105%);transition:transform .25s;display:flex;flex-direction:column;padding:1rem;box-shadow:0 0 40px rgba(0,0,0,.2)}
.drawer.open{transform:none}#items{flex:1;overflow:auto}
.it{display:flex;justify-content:space-between;gap:.5rem;padding:.7rem 0;border-bottom:1px solid var(--line)}
.tot{display:flex;justify-content:space-between;font-weight:700;font-size:1.15rem;padding:.8rem 0}
.drawer input,.drawer textarea{width:100%;padding:.6rem;margin-bottom:.4rem;border:1.5px solid var(--line);border-radius:10px;font:inherit;font-size:16px}
.drawer input:focus,.drawer textarea:focus{border-color:var(--teal);outline:0}
.xb{background:var(--bg);color:var(--ink)}.ok{background:#e7f6ec;border:1px solid #b9e3c6;border-radius:12px;padding:1rem;margin:.6rem 0}
input,textarea{font-size:16px}

@media (max-width:1000px){
 .grid{grid-template-columns:repeat(3,1fr)}.perks{grid-template-columns:repeat(2,1fr)}.fg{grid-template-columns:1fr 1fr}
 .slide{grid-template-columns:1fr}.slide .art{max-width:420px;order:-1}.slide{min-height:0;padding-block:1.6rem 3rem}
}
@media (max-width:760px){
 html{scroll-padding-top:110px}
 .bar{flex-wrap:wrap;gap:.6rem;padding-block:.6rem}.logo{font-size:1.1rem;order:1;flex:1}.icons{order:2}.search{order:3;flex-basis:100%;max-width:none}
 .grid{grid-template-columns:repeat(2,1fr);gap:.8rem}.tilesc{grid-template-columns:repeat(2,1fr);gap:.8rem}
 .sh h2{font-size:1.35rem}.sec{padding-block:1.5rem}
 .cb{padding:.7rem}.cb p{display:none}.cb h3{font-size:.92rem}.price{font-size:1.1rem}
 .thumb{padding:.6rem .6rem 0;min-height:110px}.thumb .wc{height:90px;grid-template-columns:44px 1fr}.thumb .tiles u{height:22px}
 .acts{grid-template-columns:1fr}.acts .ib{display:none}
 .steps{grid-template-columns:1fr}.perks{grid-template-columns:1fr}
 .arr{display:none}.slide .row .btn{flex:1}
 .fab{width:54px;height:54px;padding:0;justify-content:center;border-radius:50%}.fab span{display:none}
 .modal{padding:0;align-items:flex-end}.mb{max-width:100%;border-radius:18px 18px 0 0;max-height:92vh;padding-bottom:calc(1.3rem + env(safe-area-inset-bottom))}
 .mb.wide{height:100dvh;max-height:100dvh;border-radius:0}
 .fg{grid-template-columns:1fr}.big .wc{height:190px;grid-template-columns:80px 1fr}
}
@media (max-width:380px){.grid{grid-template-columns:1fr}}
@media (prefers-reduced-motion:reduce){.drawer,.slides{transition:none}.ticker div{animation:none;padding:0}html{scroll-behavior:auto}}
</style>
</head>
<body>
<div class="ticker" aria-hidden="true"><div><?php for ($k = 0; $k < 3; $k++): ?><span>تثبيت وتدريب مجاني مع كل نظام</span><span>واجهة عربية كاملة (RTL)</span><span>نسخة تجريبية قبل الشراء</span><span>دعم فني بعد التسليم</span><?php endfor; ?></div></div>

<header>
  <div class="wrap bar">
    <a class="logo" href="#top" id="brand"></a>
    <form class="search" role="search" onsubmit="return false"><input id="q" type="search" placeholder="عمّ تبحث؟ (أرشفة، موظفين، مبيعات...)" aria-label="بحث"><svg class="i" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></form>
    <div class="icons">
      <a class="ib btn" id="hdWa" target="_blank" rel="noopener" aria-label="واتساب"><svg class="i" viewBox="0 0 24 24"><path d="M4 4h16a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1H9l-5 4V5a1 1 0 0 1 1-1z"/></svg></a>
      <button class="ib" id="cartBtn" aria-label="السلة"><svg class="i" viewBox="0 0 24 24"><path d="M3 4h2l2.5 11h10L20 7H6.2"/><circle cx="9" cy="19.5" r="1.3"/><circle cx="17" cy="19.5" r="1.3"/></svg><span id="cartCount" data-n="0"></span></button>
    </div>
  </div>
  <nav class="cats" aria-label="التصنيفات"><div class="wrap" id="catNav"></div></nav>
</header>

<section class="hero" id="top" aria-roledescription="carousel" aria-label="أنظمة مميزة">
  <div class="slides" id="slides"></div>
  <button class="arr p" id="prev" aria-label="السابق"><svg class="i" viewBox="0 0 24 24"><path d="m9 6 6 6-6 6"/></svg></button>
  <button class="arr n" id="next" aria-label="التالي"><svg class="i" viewBox="0 0 24 24"><path d="m15 6-6 6 6 6"/></svg></button>
  <div class="dots" id="dots"></div>
</section>

<div id="home">
  <section class="wrap sec" id="catTilesSec"><div class="sh"><h2>تسوّق حسب التصنيف</h2></div><div class="tilesc" id="catTiles"></div></section>
  <section class="wrap sec"><div class="sh" style="justify-content:center;margin-bottom:1rem"><h2>تقنيات نعمل بها</h2></div><div class="tech" id="tech"></div></section>
</div>

<main class="wrap sec" id="list"></main>

<section class="wrap sec"><div class="perks">
  <div class="perk"><svg class="i" viewBox="0 0 24 24"><path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6z"/><path d="m9 12 2 2 4-4"/></svg><div><h3>تثبيت وتدريب</h3><p>نثبّت النظام ونُدرّب فريقك عليه.</p></div></div>
  <div class="perk"><svg class="i" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="13" rx="2"/><path d="M8 21h8M12 17v4"/></svg><div><h3>جرّب قبل الشراء</h3><p>نسخة تجريبية تتصفحها كما سيراها فريقك.</p></div></div>
  <div class="perk"><svg class="i" viewBox="0 0 24 24"><ellipse cx="12" cy="6" rx="8" ry="3"/><path d="M4 6v6c0 1.7 3.6 3 8 3s8-1.3 8-3V6M4 12v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/></svg><div><h3>بياناتك عندك</h3><p>على جهازك أو خادمك ولا تغادر جهتك.</p></div></div>
  <div class="perk"><svg class="i" viewBox="0 0 24 24"><path d="M4 4h16a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1H9l-5 4V5a1 1 0 0 1 1-1z"/></svg><div><h3>دعم بعد التسليم</h3><p>فترة دعم مجانية وعقد صيانة اختياري.</p></div></div>
</div></section>

<section class="wrap sec"><div class="sh"><h2>كيف تحصل على نظامك</h2></div><div class="steps">
  <div><h3>جرّب النظام</h3><p>افتح النسخة التجريبية وتصفّح الشاشات كما سيراها فريقك.</p></div>
  <div><h3>اطلب وادفع</h3><p>أضف النظام إلى السلة وأرسل الطلب، ونؤكد معك الاتفاق عبر واتساب.</p></div>
  <div><h3>نثبّت وندرّب</h3><p>نثبّت النظام على جهازك أو خادمك وندرّب المستخدمين، ثم ندعمك بعد التسليم.</p></div>
</div></section>

<section class="wrap sec"><div class="sh"><h2>أسئلة شائعة</h2></div>
  <details><summary>هل أحتاج خبرة تقنية؟</summary><p>لا. نتولى التثبيت والإعداد، والنظام بواجهة عربية بسيطة ندرّبك عليها.</p></details>
  <details><summary>أين تُحفظ بياناتي؟</summary><p>على جهازك أو خادمك أنت، ولا تغادر جهتك.</p></details>
  <details><summary>هل يمكن تعديل النظام ليناسبني؟</summary><p>نعم، نعدّل الحقول والتقارير والهوية البصرية بعد الاتفاق على المطلوب.</p></details>
  <details><summary>ماذا لو واجهت مشكلة بعد الشراء؟</summary><p>تتضمن كل نسخة فترة دعم مجانية، ويمكن تمديدها بعقد صيانة سنوي.</p></details>
</section>

<footer>
  <div class="wrap fg">
    <div><h4 id="fBrand"></h4><p><?= h(TAGLINE) ?></p></div>
    <div><h4>التصنيفات</h4><div id="fCats"></div></div>
    <div><h4>المتجر</h4><a href="#list">كل الأنظمة</a><a href="#top">الصفحة الرئيسية</a></div>
    <div><h4>تواصل معنا</h4><a id="fWa" target="_blank" rel="noopener">واتساب</a><a id="fMail"></a></div>
  </div>
  <div class="copy" id="foot"></div>
</footer>
<a class="btn wa fab" id="fab" target="_blank" rel="noopener" aria-label="تواصل عبر واتساب"><svg class="i" viewBox="0 0 24 24"><path d="M4 4h16a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1H9l-5 4V5a1 1 0 0 1 1-1z"/></svg><span>تواصل عبر واتساب</span></a>

<div class="modal" id="mDetail"><div class="mb" id="dBox" role="dialog" aria-modal="true"></div></div>
<div class="modal" id="mDemo"><div class="mb wide">
  <div style="display:flex;gap:.5rem;align-items:center;margin-bottom:.6rem;flex-wrap:wrap"><button class="xb" id="demoClose">إغلاق</button><strong id="demoTitle" style="flex:1"></strong><a id="demoOpen" class="btn dark" target="_blank" rel="noopener">فتح في صفحة مستقلة</a></div>
  <iframe id="frame" title="مراجعة النظام" sandbox="allow-scripts allow-forms allow-same-origin"></iframe>
</div></div>
<div class="scrim" id="scrim"></div>
<aside class="drawer" id="drawer" aria-label="السلة">
  <div style="display:flex;justify-content:space-between;align-items:center"><h2>سلة الطلبات</h2><button class="xb" id="cartClose">إغلاق</button></div>
  <div id="items"></div><div class="tot"><span>الإجمالي</span><span id="total"></span></div>
  <div id="done"></div>
  <input id="cName" placeholder="الاسم / الجهة" maxlength="120"><input id="cPhone" placeholder="رقم الهاتف" inputmode="tel" maxlength="20"><textarea id="cNote" rows="2" maxlength="500" placeholder="ملاحظات (اختياري)"></textarea>
  <button class="pri" id="checkout">إرسال الطلب</button>
</aside>

<script>
const CFG=<?= json_encode(['name' => STORE_NAME, 'cur' => CURRENCY, 'wa' => WHATSAPP, 'mail' => EMAIL, 'aud' => AUDIENCES, 'cats' => CATEGORIES], $J) ?>;
const PRODUCTS=<?= json_encode($P, $J) ?>;
const AUD={all:"الكل",...CFG.aud};
const CATS=Object.entries(CFG.cats).filter(([k])=>PRODUCTS.some(p=>p.cat===k));
const $=s=>document.querySelector(s);
const esc=s=>String(s??"").replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c]));
let cat="all",aud="all",slide=0,cart=[],timer=null;
try{cart=JSON.parse(localStorage.getItem("cart")||"[]")}catch(e){}
cart=cart.filter(c=>PRODUCTS.some(p=>p.id==c.id));
const money=n=>n.toLocaleString("ar-EG")+" "+CFG.cur;
const wa=t=>`https://wa.me/${CFG.wa}?text=${encodeURIComponent(t)}`;
const byId=id=>PRODUCTS.find(p=>p.id==id);
const mock=(p,big)=>p.shot?`<img class="shot ${big?'big':''}" src="${esc(p.shot)}" alt="واجهة ${esc(p.name)}" loading="lazy">`:`<div class="win ${big?'big':''}" style="--h:${+p.hue}"><div class="wb"><i></i><i></i><i></i><span>${esc(p.name)}</span></div><div class="wc"><div class="sb"><b></b><b></b><b></b><b></b><b></b></div><div class="mn"><div class="tiles"><u></u><u></u><u></u></div><div class="rows"><s></s><s></s><s></s></div></div></div></div>`;

/* الهوية */
const parts=CFG.name.trim().split(/\s+/);
$("#brand").innerHTML=`<i>${esc(CFG.name.trim().charAt(0))}</i><span>${esc(parts.slice(0,-1).join(" "))} <b>${esc(parts.slice(-1)[0])}</b></span>`;
$("#fBrand").textContent=CFG.name;
$("#foot").textContent=`© ${new Date().getFullYear()} ${CFG.name}. جميع الحقوق محفوظة.`;
$("#fMail").textContent=CFG.mail;$("#fMail").href="mailto:"+CFG.mail;
$("#fab").href=wa("مرحباً، أرغب بالاستفسار عن الأنظمة المعروضة.");$("#hdWa").href=$("#fab").href;$("#fWa").href=$("#fab").href;

/* شريط التصنيفات */
function catNav(){
  $("#catNav").innerHTML=`<button class="${cat=='all'?'on':''}" data-c="all">كل الأنظمة</button>`+CATS.map(([k,v])=>`<button class="${cat==k?'on':''}" data-c="${esc(k)}">${esc(v)}</button>`).join("");
  $("#fCats").innerHTML=CATS.map(([k,v])=>`<a data-c="${esc(k)}">${esc(v)}</a>`).join("");
}
/* البانر */
function hero(){
  const feat=PRODUCTS.slice(0,5);
  if(!feat.length){$(".hero").style.display="none";return}
  $("#slides").innerHTML=feat.map(p=>`<div class="slide" style="--h:${+p.hue}"><div><small>${esc(CFG.cats[p.cat]||"نظام جاهز")}</small><h2>${esc(p.name)}</h2><p>${esc(p.solves)}</p><div class="pr">يبدأ من ${money(p.price)} • يشمل التثبيت والتدريب</div>
   <div class="row"><button class="pri" data-info="${p.id}">تسوّق الآن</button>${p.demo?`<button class="out" data-demo="${p.id}">جرّب النظام</button>`:`<a class="btn out" target="_blank" rel="noopener" href="${wa('مرحباً، أرغب بالاستفسار عن: '+p.name)}">اسأل عنه</a>`}</div></div>
   <div class="art">${mock(p,true)}</div></div>`).join("");
  $("#dots").innerHTML=feat.map((_,i)=>`<button data-s="${i}" aria-label="شريحة ${i+1}"></button>`).join("");
  go(0);
}
function go(i){
  const n=document.querySelectorAll(".slide").length;if(!n)return;
  slide=(i+n)%n;$("#slides").style.transform=`translateX(${slide*100}%)`;
  document.querySelectorAll("#dots button").forEach((b,k)=>b.classList.toggle("on",k==slide));
  clearInterval(timer);if(!matchMedia("(prefers-reduced-motion:reduce)").matches)timer=setInterval(()=>go(slide+1),6500);
}
/* التصنيفات والتقنيات */
function tiles(){
  $("#catTiles").innerHTML=CATS.map(([k,v])=>{const l=PRODUCTS.filter(p=>p.cat==k),p=l[0];return `<button class="tc" data-c="${esc(k)}"><div class="th">${mock(p)}</div><strong>${esc(v)}</strong><small>${l.length} ${l.length>2?"أنظمة":"نظام"}</small></button>`}).join("");
  const tech=new Set();PRODUCTS.forEach(p=>p.specs.forEach(([k,v])=>{if(/البرمجة|قاعدة/.test(k))v.split("/").forEach(x=>tech.add(x.trim()))}));
  $("#tech").innerHTML=[...tech].map(t=>`<span>${esc(t)}</span>`).join("");
  $("#catTilesSec").style.display=CATS.length>1?"":"none";
}
/* بطاقة منتج */
const card=p=>`<article class="card"><div class="thumb" data-info="${p.id}">${p.demo?'<span class="badge">تجربة متاحة</span>':''}${mock(p)}</div><div class="cb">
 <div class="tg">${p.aud.map(f=>`<span>${esc(AUD[f]||f)}</span>`).join("")}</div>
 <h3 data-info="${p.id}">${esc(p.name)}</h3><p>${esc(p.solves)}</p>
 <div class="price">${money(p.price)}<small>يشمل التثبيت والتدريب</small></div>
 <div class="acts"><button class="pri" data-add="${p.id}">أضف للسلة</button><a class="ib" target="_blank" rel="noopener" href="${wa('مرحباً، أرغب بالاستفسار عن: '+p.name)}" aria-label="تواصل مع البائع"><svg class="i" viewBox="0 0 24 24"><path d="M4 4h16a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1H9l-5 4V5a1 1 0 0 1 1-1z"/></svg></a></div></div></article>`;
function audBar(){return `<div class="aud"><strong>الجهة:</strong>${Object.entries(AUD).map(([k,v])=>`<button class="${k==aud?'on':''}" data-a="${esc(k)}">${esc(v)}</button>`).join("")}</div>`}
/* القائمة */
function render(){
  const q=$("#q").value.trim(),filtering=q||cat!="all"||aud!="all";
  $("#home").style.display=filtering?"none":"";
  const ok=p=>(cat=="all"||p.cat==cat)&&(aud=="all"||p.aud.includes(aud))&&(p.name+p.solves+p.desc).includes(q);
  let h;
  if(filtering){
    const l=PRODUCTS.filter(ok),title=q?`نتائج البحث عن «${esc(q)}»`:(CFG.cats[cat]||"كل الأنظمة");
    h=`<div class="sh"><h2>${title}</h2><button class="out" data-c="all" data-reset>عرض كل الأنظمة</button></div>${audBar()}<div class="grid">${l.length?l.map(card).join(""):'<div class="empty">لا توجد أنظمة مطابقة. جرّب تصنيفاً آخر أو كلمة أخرى.</div>'}</div>`;
  }else{
    let groups=CATS.map(([k,v])=>[k,v,PRODUCTS.filter(p=>p.cat==k)]);
    const rest=PRODUCTS.filter(p=>!CFG.cats[p.cat]);if(rest.length)groups.push(["","أنظمة أخرى",rest]);
    if(PRODUCTS.length<=8)groups=[["","الأنظمة المتاحة",PRODUCTS]]; /* كتالوج صغير: شبكة واحدة بدل صفوف شبه فارغة */
    h=audBar()+groups.map(([k,v,l])=>`<section style="margin-bottom:2.4rem"><div class="sh"><h2>${esc(v)}</h2>${k?`<button class="pri" data-c="${esc(k)}">عرض ${esc(v)} ←</button>`:""}</div><div class="grid">${l.map(card).join("")}</div></section>`).join("");
    if(!PRODUCTS.length)h='<div class="empty">لا توجد أنظمة معروضة حالياً.</div>';
  }
  $("#list").innerHTML=h;
}
function info(id){const p=byId(id);
 $("#dBox").innerHTML=`${mock(p,true)}<h2>${esc(p.name)}</h2><p>${esc(p.desc)}</p><h3 style="margin-top:.8rem;font-size:1.05rem">المزايا</h3><ul>${p.features.map(f=>`<li>${esc(f)}</li>`).join("")}</ul>
 <h3 style="font-size:1.05rem">المواصفات الفنية</h3><table>${p.specs.map(([k,v])=>`<tr><td>${esc(k)}</td><td>${esc(v)}</td></tr>`).join("")}</table>
 <div class="price">${money(p.price)}<small>السعر الأساسي • يشمل التثبيت والتدريب</small></div>
 <div style="display:grid;gap:.5rem;margin-top:.8rem"><button class="pri" data-add="${p.id}">أضف للسلة</button>${p.demo?`<button class="out" data-demo="${p.id}">جرّب النظام</button>`:""}<button class="xb" data-close>إغلاق</button></div>`;
 $("#mDetail").classList.add("open")}
function demo(id){const p=byId(id);$("#mDetail").classList.remove("open");$("#demoTitle").textContent=p.name;$("#frame").src=p.demo;$("#demoOpen").href=p.demo;$("#mDemo").classList.add("open")}
function openCart(o){$("#drawer").classList.toggle("open",o);$("#scrim").classList.toggle("open",o)}
function drawCart(){
 const n=cart.reduce((a,c)=>a+c.q,0);$("#cartCount").textContent=n||"";$("#cartCount").dataset.n=n;
 $("#items").innerHTML=cart.length?cart.map(c=>{const p=byId(c.id);return `<div class="it"><div>${esc(p.name)}<br><small>${money(p.price)} × ${c.q}</small></div><button class="xb" data-rm="${c.id}">حذف</button></div>`}).join(""):`<p class="empty">السلة فارغة. أضف نظاماً من القائمة.</p>`;
 $("#total").textContent=money(cart.reduce((a,c)=>a+byId(c.id).price*c.q,0));
 try{localStorage.setItem("cart",JSON.stringify(cart))}catch(e){}}
function setCat(c,scroll){cat=c;aud="all";$("#q").value="";catNav();render();if(scroll)$("#list").scrollIntoView()}
document.addEventListener("click",e=>{
 const t=e.target.closest("[data-a],[data-c],[data-s],[data-info],[data-demo],[data-add],[data-rm],[data-close],#prev,#next"),d=t?t.dataset:{};
 if(!t&&e.target.id=="mDetail")$("#mDetail").classList.remove("open");
 if(!t)return;
 if(t.id=="prev")go(slide-1);else if(t.id=="next")go(slide+1);
 else if(d.s!==undefined)go(+d.s);
 else if(d.a){aud=d.a;render();const o=$(".aud .on");o&&o.scrollIntoView({inline:"center",block:"nearest"})}
 else if(d.c!==undefined){e.preventDefault();setCat(d.c,true)}
 else if(d.info)info(d.info);
 else if(d.demo)demo(d.demo);
 else if(d.add){const c=cart.find(x=>x.id==d.add);c?c.q++:cart.push({id:+d.add,q:1});$("#done").innerHTML="";drawCart();$("#mDetail").classList.remove("open");openCart(true)}
 else if(d.rm){cart=cart.filter(x=>x.id!=d.rm);drawCart()}
 else if(d.close!==undefined)$("#mDetail").classList.remove("open");
});
$(".hero").addEventListener("mouseenter",()=>clearInterval(timer));$(".hero").addEventListener("mouseleave",()=>go(slide));
$("#cartBtn").onclick=()=>openCart(true);$("#cartClose").onclick=()=>openCart(false);$("#scrim").onclick=()=>openCart(false);
$("#demoClose").onclick=()=>{$("#mDemo").classList.remove("open");$("#frame").src="about:blank"};
$("#q").oninput=()=>{cat="all";aud="all";catNav();render()};
$("#checkout").onclick=async()=>{
 if(!cart.length)return alert("السلة فارغة");
 const b=$("#checkout");b.disabled=true;
 try{
  const r=await fetch("order.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({name:$("#cName").value,phone:$("#cPhone").value,note:$("#cNote").value,items:cart})});
  const j=await r.json();
  if(!j.ok)alert(j.error||"تعذّر إرسال الطلب");
  else{cart=[];drawCart();$("#done").innerHTML=`<div class="ok">تم استلام طلبك رقم #${+j.id}. أكمل الطلب بإرساله إلينا في واتساب.<br><a class="btn wa" style="margin-top:.5rem" href="${esc(j.wa)}" target="_blank" rel="noopener">أرسل الطلب في واتساب</a></div>`}
 }catch(err){alert("تعذّر الاتصال بالخادم")}
 b.disabled=false};
document.addEventListener("keydown",e=>{if(e.key=="Escape"){document.querySelectorAll(".modal.open").forEach(x=>x.classList.remove("open"));openCart(false)}});
catNav();hero();tiles();render();drawCart();
</script>
</body>
</html>
