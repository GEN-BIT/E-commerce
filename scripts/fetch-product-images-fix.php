<?php
require_once __DIR__ . '/../config/config.php';

$products = $pdo->query('SELECT id, name, description FROM products ORDER BY id')->fetchAll();
$uploadDir = UPLOAD_PRODUCTS_DIR;

$success = 0;
$fail = 0;

foreach ($products as $p) {
    $id = (int) $p['id'];
    $name = trim($p['name']);

    $targetName = 'product-' . str_pad((string) $id, 3, '0', STR_PAD_LEFT) . '.jpg';
    $targetPath = $uploadDir . $targetName;

    if (file_exists($targetPath) && filesize($targetPath) > 2048) {
        echo "[skip] #{$id} {$name}\n";
        $success++;
        continue;
    }

    $keywords = getKeywords($id, $name);
    $downloaded = false;

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => ['Accept: image/webp,image/apng,image/*,*/*;q=0.8'],
    ]);

    foreach ($keywords as $kw) {
        $src = 'https://loremflickr.com/400/400/' . rawurlencode($kw) . '?lock=' . ($id * 7 + 13);
        curl_setopt($ch, CURLOPT_URL, $src);
        $data = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $ctype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $size = strlen($data);

        if ($code >= 200 && $code < 300 && !empty($data) && $size > 2048) {
            if (strpos($ctype, 'image') === 0) {
                if (file_put_contents($targetPath, $data) !== false) {
                    $stmt = $pdo->prepare('UPDATE products SET image = ? WHERE id = ?');
                    $stmt->execute([$targetName, $id]);
                    echo "[ok]   #{$id} {$name} ({$kw})\n";
                    $downloaded = true;
                    break;
                }
            }
        }
    }

    curl_close($ch);

    if ($downloaded) {
        $success++;
    } else {
        echo "[fail] #{$id} {$name}\n";
        $fail++;
    }

    usleep(150000);
}

echo "\nDone. success={$success} fail={$fail}\n";

function getKeywords(int $id, string $name): array
{
    $map = [
        1  => ['laptop', 'notebook', 'computer'],
        2  => ['earbuds', 'earphones', 'headphones'],
        3  => ['camera', 'actioncamera', 'digitalcamera'],
        4  => ['watch', 'smartwatch', 'fitnesswatch'],
        5  => ['powerbank', 'charger', 'battery'],
        6  => ['keyboard', 'mechanicalkeyboard', 'gamingkeyboard'],
        7  => ['charger', 'wirelesscharger', 'chargingpad'],
        8  => ['speaker', 'bluetoothspeaker', 'portablespeaker'],
        9  => ['smarthome', 'smarthub', 'homeautomation'],
        10 => ['headphones', 'audioheadset', 'headset'],
        11 => ['sweater', 'woolsweater', 'knitwear'],
        12 => ['chinos', 'pants', 'trousers'],
        13 => ['shoes', 'sneakers', 'runningshoes'],
        14 => ['shoes', 'sneakers', 'runningshoes'],
        15 => ['jacket', 'denimjacket', 'truckerjacket'],
        16 => ['belt', 'leatherbelt', 'fashionbelt'],
        17 => ['tshirt', 'tshirt', 'crewneck'],
        18 => ['jacket', 'rainjacket', 'waterproofjacket'],
        19 => ['scarf', 'woolscarf', 'winterscarf'],
        20 => ['sneakers', 'shoes', 'casualshoes'],
        21 => ['vacuum', 'cordlessvacuum', 'stickvacuum'],
        22 => ['cookware', 'kitchen', 'pots'],
        23 => ['pillow', 'memoryfoam', 'bedpillow'],
        24 => ['diffuser', 'aromatherapy', 'essentialoil'],
        25 => ['bottle', 'waterbottle', 'steelbottle'],
        26 => ['blanket', 'throwblanket', 'linenblanket'],
        27 => ['lamp', 'desklamp', 'usblamp'],
        28 => ['cuttingboard', 'bamboo', 'kitchenboard'],
        29 => ['robotvacuum', 'roboticvacuum', 'vacuumrobot'],
        30 => ['candle', 'soycandle', 'scentedcandle'],
        31 => ['serum', 'facialsrum', 'skincare'],
        32 => ['mask', 'facemask', 'charcoalmask'],
        33 => ['brush', 'cleansingbrush', 'facebrush'],
        34 => ['hairtreatment', 'hairoil', 'hairserum'],
        35 => ['deodorant', 'naturaldeodorant', 'organicdeodorant'],
        36 => ['roller', 'facialroller', 'jaderoller'],
        37 => ['bathsalt', 'lavenderbath', 'bathsoak'],
        38 => ['lipstick', 'mattelipstick', 'makeup'],
        39 => ['nailfile', 'nailcare', 'electricnailfile'],
        40 => ['moisturizer', 'hyaluronicacid', 'facecream'],
        41 => ['dumbbell', 'adjustabledumbbell', 'freeweights'],
        42 => ['yogamat', 'exercisemat', 'fitnessmat'],
        43 => ['resistancebands', 'exercisebands', 'fitnessbands'],
        44 => ['bottle', 'sportsbottle', 'waterbottle'],
        45 => ['foamroller', 'gymroller', 'exerciseroller'],
        46 => ['jumprope', 'speedrope', 'skippingrope'],
        47 => ['weightbench', 'adjustablebench', 'workoutbench'],
        48 => ['cyclinggloves', 'bikegloves', 'bicyclegloves'],
        49 => ['hammock', 'campinghammock', 'outdoorhammock'],
        50 => ['backpack', 'hikingbackpack', 'outdoorbackpack'],
        51 => ['turntable', 'bluetoothturntable', 'vinylplayer'],
        52 => ['boardgame', 'familygame', 'boardgames'],
        53 => ['controller', 'gamepad', 'gamingcontroller'],
        54 => ['streaming', 'mediaplayer', 'streamingdevice'],
        55 => ['puzzle', 'jigsawpuzzle', '1000piecepuzzle'],
        56 => ['projector', 'portableprojector', 'miniprojector'],
        57 => ['guitar', 'acousticguitar', 'beginnerguitar'],
        58 => ['graphicnovel', 'comicbook', 'comiccollection'],
        59 => ['videogame', 'arcadeconsole', 'miniconsole'],
        60 => ['headset', 'gamingheadset', 'gamingheadphones'],
        61 => ['officechair', 'meshchair', 'ergonomicchair'],
        62 => ['standingdesk', 'deskconverter', 'heightadjustabledesk'],
        63 => ['mousekeyboard', 'comboset', 'peripheralcombo'],
        64 => ['deskorganizer', 'officeorganizer', 'desktidy'],
        65 => ['notebook', 'dotgridnotebook', 'journal'],
        66 => ['laptopstand', 'adjustablestand', 'laptopriser'],
        67 => ['lamp', 'desklamp', 'usblamp'],
        68 => ['planner', 'calendarplanner', 'whiteboardplanner'],
        69 => ['fountainpen', 'penset', 'executivepen'],
        70 => ['shredder', 'documentshredder', 'papershredder'],
        71 => ['protein', 'wheyprotein', 'proteinpowder'],
        72 => ['multivitamin', 'dailyvitamins', 'vitaminsupplement'],
        73 => ['fishoil', 'omega3', 'fishoilsoftgels'],
        74 => ['kitchenscale', 'foodscale', 'digitalscale'],
        75 => ['tea', 'herbaltea', 'chamomiletea'],
        76 => ['electrolyte', 'hydrationmix', 'sportsdrink'],
        77 => ['posturecorrector', 'backbrace', 'posturesupport'],
        78 => ['bloodpressure', 'bpmonitor', 'bloodpressurecuff'],
        79 => ['collagen', 'collagenpeptides', 'collagenpowder'],
        80 => ['firstaid', 'medicalkit', 'emergencykit'],
        81 => ['jumpstarter', 'carjumpstarter', 'batterybooster'],
        82 => ['cushion', 'seatcushion', 'memoryfoamcushion'],
        83 => ['phonemount', 'carphonemount', 'phoneholder'],
        84 => ['carwash', 'microfiber', 'carcleaning'],
        85 => ['tirepressure', 'pressuregauge', 'digitalgauge'],
        86 => ['dashcam', 'dashcamera', 'carcamera'],
        87 => ['vacuum', 'handheldvacuum', 'carvacuum'],
        88 => ['floormats', 'carfloormats', 'allweathermats'],
        89 => ['cargocarrier', 'roofcargo', 'roofbag'],
        90 => ['motorcyclehelmet', 'helmet', 'fullfacehelmet'],
        91 => ['dogbed', 'petbed', 'orthopedicdogbed'],
        92 => ['petfeeder', 'automaticfeeder', 'dogfeeder'],
        93 => ['catscratchingpost', 'scratchingtower', 'cattree'],
        94 => ['dogleash', 'retractableleash', 'petleash'],
        95 => ['cattoy', 'interactivecattoy', 'catwand'],
        96 => ['petbrush', 'groomingbrush', 'dogbrush'],
        97 => ['petbowls', 'dogbowls', 'stainlesssteelbowls'],
        98 => ['dogtreats', 'pettreats', 'grainfreetreats'],
        99 => ['aquariumlight', 'fishtanklight', 'ledaquarium'],
        100 => ['birdcage', 'parrotcage', 'birdcagestand'],
        101 => ['gardenbed', 'raisedbed', 'gardenkit'],
        102 => ['gardenlights', 'solarlights', 'pathwaylights'],
        103 => ['gardentools', 'toolset', 'gardeningtools'],
        104 => ['gardenhose', 'expandablehose', 'hose'],
        105 => ['stringlights', 'outdoorlights', 'patiolights'],
        106 => ['compostbin', 'composter', 'tumblingcompost'],
        107 => ['patiumbrella', 'umbrella', 'gardenumbrella'],
        108 => ['birdfeeder', 'squirrelproof', 'wildbirdfeeder'],
        109 => ['campingchair', 'foldingchair', 'outdoorchair'],
        110 => ['grill', 'portablegrill', 'charcoalgrill'],
        111 => ['coffee', 'coffeebeans', 'singleorigincoffee'],
        112 => ['tea', 'herbaltea', 'teasampler'],
        113 => ['honey', 'rawhoney', 'wildflowerhoney'],
        114 => ['oliveoil', 'extravirginoliveoil', 'oliveoilbottle'],
        115 => ['chocolate', 'darkchocolate', 'chocolatebar'],
        116 => ['trailmix', 'nutmix', 'healthysnack'],
        117 => ['sparklingwater', 'seltzer', 'sparklingwaterpack'],
        118 => ['pastasauce', 'tomatosauce', 'gourmetsauce'],
        119 => ['coldbrew', 'coffeeconcentrate', 'coldbrewcoffee'],
        120 => ['spicerack', 'spicejars', 'spiceset'],
    ];

    return $map[$id] ?? [$name, 'product'];
}
