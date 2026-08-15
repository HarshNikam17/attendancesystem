<?php
// ai_chat.php
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/cors.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$userMessage = trim($data['message'] ?? '');

if (!$userMessage) {
    echo json_encode(["success" => false, "reply" => "Please enter a message."]);
    exit;
}

$apiKey = getenv("OPENAI_API_KEY") ?: "YOUR_OPENAI_API_KEY"; 

if ($apiKey === "YOUR_OPENAI_API_KEY" || empty($apiKey)) {
    $reply = generateSmartFallbackResponse($userMessage);
    echo json_encode(["success" => true, "reply" => $reply]);
    exit;
}

$payload = [
    "model" => "gpt-3.5-turbo",
    "messages" => [
        [
            "role" => "system", 
            "content" => "You are EduPro AI, an advanced, friendly, and helpful general-purpose assistant. Answer any questions the user asks accurately and clearly."
        ],
        [
            "role" => "user", 
            "content" => $userMessage
        ]
    ],
    "temperature" => 0.7
];

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $apiKey
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || $response === false) {
    $reply = generateSmartFallbackResponse($userMessage);
    echo json_encode(["success" => true, "reply" => $reply]);
    exit;
}

$responseData = json_decode($response, true);
$aiReply = $responseData['choices'][0]['message']['content'] ?? generateSmartFallbackResponse($userMessage);

echo json_encode(["success" => true, "reply" => $aiReply]);

function generateSmartFallbackResponse($msg) {
    $lower = strtolower($msg);
    if (strpos($lower, 'hello') !== false || strpos($lower, 'hi') !== false || strpos($lower, 'hey') !== false) {
        return "Hello! I am EduPro AI. I can answer any questions you have, help you write text, explain concepts, or assist with your workspace.";
    }
    if (strpos($lower, 'how are you') !== false) {
        return "I'm doing great, thank you! How can I help you today?";
    }
    if (strpos($lower, 'export') !== false || strpos($lower, 'csv') !== false) {
        return "You can export your attendance roster anytime using the 'Export CSV' button on your toolbar.";
    }
    if (strpos($lower, 'batch') !== false || strpos($lower, 'import') !== false) {
        return "Use the 'Batch Import' button to paste multiple member rows (ID, Name, Phone) and import them all at once instantly!";
    }
    if (strpos($lower, 'dark mode') !== false || strpos($lower, 'night mode') !== false) {
        return "You can toggle Night Mode instantly under the Settings Hub in your sidebar!";
    }
    return "That's a great question! As EduPro AI, I'm here to help you with anything you need—whether it's general knowledge, brainstorming, or managing your workspace records. Let me know how I can assist further!";
}
?>