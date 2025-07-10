<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  echo json_encode(["error" => "Metode tidak diizinkan"]);
  exit;
}

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input["product_name"], $input["goal"], $input["tone"])) {
  echo json_encode(["error" => "Data tidak lengkap"]);
  exit;
}

$apiKey = getenv("OPENAI_API_KEY"); // Ambil dari environment Vercel

$prompt = "Buatkan script TikTok untuk produk {$input['product_name']}, dengan tujuan {$input['goal']} dan tone {$input['tone']}. Gunakan gaya yang menarik dan engaging.";

// Kirim ke OpenAI API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Authorization: Bearer $apiKey",
  "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  "model" => "gpt-4",
  "messages" => [
    ["role" => "system", "content" => "Kamu adalah copywriter TikTok berpengalaman."],
    ["role" => "user", "content" => $prompt]
  ],
  "temperature" => 0.7
]));

$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
  echo json_encode(["error" => "Curl error: $err"]);
  exit;
}

$data = json_decode($response, true);
$output = $data["choices"][0]["message"]["content"] ?? "Gagal mendapatkan hasil.";

echo json_encode(["output" => $output]);
