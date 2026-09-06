<?php
declare(strict_types=1);

use App\Auth\Middleware;
use League\Glide\ServerFactory;

require_once __DIR__ . '/../../../bootstrap.php';

// 1. Authorise
Middleware::check('STUDENT_READ');

// 2. Validate index
$indexNo = (int)($_GET['index'] ?? 0);
if (!$indexNo) {
    http_response_code(400);
    exit('Missing index');
}

// 3. Fetch student
$student = (new \App\Models\Student)->findById($indexNo);
if (!$student || !$student['photo']) {
    http_response_code(404);
    exit('Not found');
}

// 4. Configure Glide
$server = ServerFactory::create([
    'source' => __DIR__ . '/../../../storage/uploads/student_profiles',
    'cache'  => __DIR__ . '/../../../storage/cache/glide',
]);

// 5. Build parameters
$params = [
    'w'   => (int)($_GET['w'] ?? 32),
    'h'   => (int)($_GET['h'] ?? 32),
    'fit' => $_GET['fit'] ?? 'crop',
    'q'   => (int)($_GET['q'] ?? 80),
];

// 6. Stream the image
$server->outputImage($student['photo'], $params);