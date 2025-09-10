<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/db.php';
require __DIR__ . '/CurlClient.php';
require __DIR__ . '/Brand.php';
require __DIR__ . '/BrandFactory.php';
require __DIR__ . '/BrandRepositoryInterface.php';
require __DIR__ . '/BrandRepository.php';

$config = require __DIR__ . '/config.php';
$client = new CurlClient($config['fipe']['base_url']);
$repo = new BrandRepository($pdo);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// normalize base if using subfolder; adjust to your setup. Example: if running at /fipe-api/api.php use routes like ?action=
$scriptName = basename(__FILE__);
$base = str_replace('/' . $scriptName, '', $_SERVER['SCRIPT_NAME']);

// Simple routing by query param (easier in PHP native). Use ?action=populate&type=carros
$action = $_GET['action'] ?? null;

try {
    if ($action === 'populate') {
        $type = $_GET['type'] ?? 'carros'; // carros | motos | caminhoes
        // endpoint path example: /carros/marcas
        $remotePath = "{$type}/marcas";
        $data = $client->get($remotePath);
        $count = 0;
        foreach ($data as $item) {
            $brand = BrandFactory::fromApi($item, $type);
            $repo->save($brand);
            $count++;
        }
        echo json_encode(['status' => 'ok', 'inserted_or_updated' => $count]);
        exit;
    } elseif ($action === 'brands') {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 200;
        $brands = $repo->findAll($limit);
        $out = array_map(function(Brand $b){
            return ['id'=>$b->id,'fipe_code'=>$b->fipe_code,'name'=>$b->name,'type'=>$b->type];
        }, $brands);
        echo json_encode($out, JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'unknown action', 'available' => ['populate','brands']]);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
