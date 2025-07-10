<?php
header('Content-Type: application/json');

$api_key = getenv('OPENAI_API_KEY');
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || !isset($data['product_name'], $data['goal'], $data['tone'])) {
  echo json_encode(['error' => 'Input tidak valid']);
  exit;
}

$prompt = "Buatkan script video TikTok untuk produk " . $data['product_name'] .
          " dengan tujuan " . $data['goal'] .
          " dan gaya bahasa yang " . $data['tone'] . ".";

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'Content-Type: application/json',
  'Authorization: Bearer ' . $api_key
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  'model' => 'gpt-4o',
  'messages' => [
    ['role' => 'user', 'content' => $prompt]
  ]
]));

$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
  echo json_encode(['error' => $err]);
} else {
  $result = json_decode($response, true);
  echo json_encode(['output' => $result['choices'][0]['message']['content'] ?? '']);
}
?>