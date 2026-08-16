<?php
require_once __DIR__ . '/../config/config.php';

$products = $pdo->query('SELECT id, name, image FROM products ORDER BY id')->fetchAll();
$uploadDir = UPLOAD_PRODUCTS_DIR;

$keywordMap = [
    1 => 'laptop', 2 => 'earbuds', 3 => 'camera', 4 => 'watch', 5 => 'powerbank',
    6 => 'keyboard', 7 => 'charger', 8 => 'speaker', 9 => 'smarthome', 10 => 'headphones',
    11 => 'sweater', 12 => 'chinos', 13 => 'shoes', 14 => 'shoes', 15 => 'jacket',
    16 => 'belt', 17 => 'tshirt', 18 => 'jacket', 19 => 'scarf', 20 => 'sneakers',
    21 => 'vacuum', 22 => 'cookware', 23 => 'pillow', 24 => 'diffuser', 25 => 'bottle',
    26 => 'blanket', 27 => 'lamp', 28 => 'cuttingboard', 29 => 'robotvacuum', 30 => 'candle',
    31 => 'serum', 32 => 'mask', 33 => 'cleansingbrush', 34 => 'hairtreatment', 35 => 'deodorant',
    36 => 'facialroller', 37 => 'bathsalt', 38 => 'lipstick', 39 => 'nailfile', 40 => 'moisturizer',
    41 => 'dumbbell', 42 => 'yogamat', 43 => 'resistancebands', 44 => 'bottle', 45 => 'foamroller',
    46 => 'jumprope', 47 => 'weightbench', 48 => 'cyclinggloves', 49 => 'hammock', 50 => 'backpack',
    51 => 'turntable', 52 => 'boardgame', 53 => 'gamecontroller', 54 => 'streaming', 55 => 'puzzle',
    56 => 'projector', 57 => 'guitar', 58 => 'graphicnovel', 59 => 'videogame', 60 => 'gamingheadset',
    61 => 'officechair', 62 => 'standingdesk', 63 => 'mousekeyboard', 64 => 'deskorganizer', 65 => 'notebook',
    66 => 'laptopstand', 67 => 'lamp', 68 => 'planner', 69 => 'fountainpen', 70 => 'shredder',
    71 => 'protein', 72 => 'multivitamin', 73 => 'fishoil', 74 => 'kitchenscale', 75 => 'tea',
    76 => 'electrolyte', 77 => 'posturecorrector', 78 => 'bloodpressure', 79 => 'collagen', 80 => 'firstaid',
    81 => 'jumpstarter', 82 => 'cushion', 83 => 'phonemount', 84 => 'carwash', 85 => 'pressuregauge',
    86 => 'dashcam', 87 => 'carvacuum', 88 => 'floormat', 89 => 'cargocarrier', 90 => 'motorcyclehelmet',
    91 => 'dogbed', 92 => 'petfeeder', 93 => 'cattower',     94 => 'leash', 95 => 'cattoy',
    96 => 'petbrush', 97 => 'petbowl', 98 => 'dogtreats', 99 => 'aquariumlight', 100 => 'birdcage',
    101 => 'gardenbed', 102 => 'gardenlights', 103 => 'gardentools', 104 => 'gardenhose', 105 => 'stringlights',
    106 => 'compostbin', 107 => 'patioumbrella', 108 => 'birdfeeder', 109 => 'campingchair', 110 => 'grill',
    111 => 'coffee', 112 => 'tea', 113 => 'honey', 114 => 'oliveoil', 115 => 'chocolate',
    116 => 'trailmix', 117 => 'sparklingwater', 118 => 'pastasauce', 119 => 'coldbrew', 120 => 'spicerack',
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS      => 5,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_USERAGENT      => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER     => ['Accept: image/webp,image/apng,image/*,*/*;q=0.8'],
]);

$success = 0;
$fail    = 0;
$updated = 0;

foreach ($products as $p) {
    $id       = (int) $p['id'];
    $name     = trim($p['name']);
    $keyword  = $keywordMap[$id] ?? 'product';

    $targetName = 'product-' . str_pad((string) $id, 3, '0', STR_PAD_LEFT) . '.jpg';
    $targetPath = $uploadDir . $targetName;

    if (file_exists($targetPath) && filesize($targetPath) > 1024) {
        $success++;
        continue;
    }

    $sources = [
        'https://loremflickr.com/400/400/' . rawurlencode($keyword) . '?lock=' . $id,
        'https://loremflickr.com/400/400/' . rawurlencode($keyword) . '?random=' . ($id + 500),
    ];

    $downloaded = false;

    foreach ($sources as $src) {
        curl_setopt($ch, CURLOPT_URL, $src);
        $data = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $ctype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        if ($code >= 200 && $code < 300 && !empty($data) && strlen($data) > 2048) {
            if (strpos($ctype, 'image') === 0) {
                if (file_put_contents($targetPath, $data) !== false) {
                    $stmt = $pdo->prepare('UPDATE products SET image = ? WHERE id = ?');
                    $stmt->execute([$targetName, $id]);
                    $downloaded = true;
                    $updated++;
                    break;
                }
            }
        }
    }

    if ($downloaded) {
        echo "[ok]   #{$id} {$name} ({$keyword})\n";
        $success++;
    } else {
        echo "[fail] #{$id} {$name} ({$keyword})\n";
        $fail++;
    }
}

curl_close($ch);

echo "\nDone. success={$success} fail={$fail} updated={$updated}\n";
