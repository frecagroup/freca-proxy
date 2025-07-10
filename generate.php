<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: Content-Type');
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

$product = htmlspecialchars($input["product_name"]);
$goal = htmlspecialchars($input["goal"]);
$tone = htmlspecialchars($input["tone"]);

$apiKey = getenv("OPENAI_API_KEY");
$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $apiKey
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "model" => "gpt-4o",
    "messages" => [
        ["role" => "system", "content" => "Kamu adalah copywriter TikTok yang jago bikin script. Buat script menarik sesuai format TikTok."],
        ["role" => "user", "content" => "Buatkan script video TikTok untuk produk $product dengan tujuan $goal dan gaya penyampaian $tone"]
    ],
    "temperature" => 0.7
]));

$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo json_encode(["error" => curl_error($ch)]);
    exit;
}
curl_close($ch);

$data = json_decode($response, true);
if (!isset($data["choices"][0]["message"]["content"])) {
    echo json_encode(["error" => "Gagal mendapatkan respon dari OpenAI"]);
    exit;
}

echo json_encode(["output" => $data["choices"][0]["message"]["content"]]);
?>
