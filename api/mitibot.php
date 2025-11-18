<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
start_session();
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['ok'=>false,'error'=>'unauthorized']);
    exit;
}
header('Content-Type: application/json');

// Basic rate limit: 20 requests per 60s per session
$now = time();
$_SESSION['mitibot_rl'] = $_SESSION['mitibot_rl'] ?? [];
$_SESSION['mitibot_rl'] = array_filter($_SESSION['mitibot_rl'], fn($t)=> $t > $now - 60);
if (count($_SESSION['mitibot_rl']) >= 20) {
    echo json_encode(['ok'=>false,'error'=>'rate_limited']);
    exit;
}
$_SESSION['mitibot_rl'][] = $now;

$raw = file_get_contents('php://input');
$body = json_decode($raw, true) ?: [];
$q = trim((string)($body['message'] ?? ''));
if ($q === '') {
    echo json_encode(['ok'=>true,'html'=>'<p>Please enter a question about indigenous trees, planting, soils, or survival tips.</p>']);
    exit;
}

// Simple intent routing and local knowledge base
$kbPath = ROOT_PATH . '/data/mitibot_kb.json';
$kb = [];
if (file_exists($kbPath)) {
    $kb = json_decode(file_get_contents($kbPath), true) ?: [];
}

function reply_html($title, $items){
    $out = '<h3>'.htmlspecialchars($title).'</h3><ul>';
    foreach ($items as $i) $out .= '<li>'.htmlspecialchars($i).'</li>';
    $out .= '</ul>';
    return $out;
}

$lower = strtolower($q);
$html = '';
$suggestions = [
  'Spacing recommendations for grevillea',
  'Best soils for mango or avocado',
  'Drought-resistant indigenous species list',
  'How to identify common fungal diseases',
  'Watering and mulching best practices',
];

// Quick heuristics
if (str_contains($lower,'drought') || str_contains($lower,'dry')) {
    $list = $kb['drought_resistant'] ?? ['Acacia xanthophloea','Croton megalocarpus','Cordia africana','Olea africana'];
    $html = reply_html('Drought-resistant indigenous species', $list);
}
elseif (str_contains($lower,'spacing') || str_contains($lower,'space')) {
    $spacing = $kb['spacing'] ?? [
      'Grevillea robusta: 2.5m–3m between trees; 3m–4m between rows',
      'Cypress: 2m–2.5m between trees; 2.5m–3m between rows',
      'Mango: 7m–10m depending on variety and management',
      'Avocado: 6m–8m spacing; ensure good drainage'
    ];
    $html = reply_html('Spacing recommendations', $spacing);
}
elseif (str_contains($lower,'soil') || str_contains($lower,'soils')) {
    $soil = $kb['soils'] ?? [
      'Grevillea: well-drained loams; avoid waterlogging',
      'Mango: deep, well-drained loams; pH 5.5–7.5',
      'Avocado: loose, well-drained soils; pH 5.0–7.0',
      'Cypress: deep soils; avoid shallow hardpans'
    ];
    $html = reply_html('Soil suitability', $soil);
}
elseif (str_contains($lower,'disease') || str_contains($lower,'pest')) {
    $d = $kb['diseases'] ?? [
      'Grevillea: damping-off in seedlings; ensure sterile media and avoid overwatering',
      'Mango: anthracnose; prune for airflow and apply copper-based sprays if needed',
      'Avocado: root rot (Phytophthora); ensure drainage and avoid waterlogging',
      'Cypress: cypress canker; remove infected branches and sanitize tools'
    ];
    $html = reply_html('Common diseases and management', $d);
}
elseif (str_contains($lower,'survival') || str_contains($lower,'best practice') || str_contains($lower,'practice')) {
    $bp = $kb['best_practices'] ?? [
      'Plant at onset of rains; use mulching to conserve soil moisture',
      'Stake young trees and protect from livestock',
      'Water deeply but infrequently; prioritize early establishment',
      'Use compost or well-decomposed manure; avoid fresh manure on roots',
      'Regularly inspect for pests/diseases and prune dead/weak branches'
    ];
    $html = reply_html('Best practices for survival and growth', $bp);
}
elseif (str_contains($lower,'plant') || str_contains($lower,'conditions') || str_contains($lower,'altitude')) {
    $pc = $kb['planting'] ?? [
      'Ideal planting at onset of long or short rains depending on region',
      'For high altitudes (1500–2200m), select cold-tolerant species and protect seedlings from frost',
      'Dig wide planting holes (e.g., 60cm x 60cm x 60cm), mix topsoil with compost',
      'Water thoroughly after planting and mulch to reduce evaporation'
    ];
    $html = reply_html('Optimal planting conditions', $pc);
}
else {
    // fallback: combine some guidance blocks
    $html = '<p>I can help with drought-resistant species, spacing, soils, diseases, and best practices for indigenous trees.</p>';
    $html .= reply_html('Quick tips', [
      'Mulch to retain moisture and suppress weeds',
      'Choose site with proper drainage and sunlight',
      'Match species to altitude and rainfall patterns',
      'Water regularly during establishment phase',
    ]);
}

// Richening: append sources if present
$sources = $kb['sources'] ?? [
  ['title'=>'KEFRI indigenous species guide','url'=>'https://www.kefri.org/'],
  ['title'=>'FAO Forestry best practices','url'=>'https://www.fao.org/forestry/en/']
];
$html .= '<div class="muted" style="font-size:12px;margin-top:6px">References: ' .
  implode(', ', array_map(fn($s)=>'<a href="'.htmlspecialchars($s['url']).'" target="_blank" rel="noopener">'.htmlspecialchars($s['title']).'</a>', $sources)) .
  '</div>';

echo json_encode(['ok'=>true,'html'=>$html,'suggestions'=>$suggestions,'sources'=>$sources]);
