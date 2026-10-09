```php
<?php
// ==========================================
// Vercel Serverless Telegram Bot (Optimized & Safe)
// ==========================================

error_reporting(E_ALL);
ini_set('display_errors', '0');

$token = getenv('BOT_TOKEN') ?: "8996318701:AAH2dufo_cQT4Yyca3B4rNItRQO3hIdAo7k"; 
define("API_KEY", $token);

// إعداد Webhook التلقائي
$server_name = $_SERVER['SERVER_NAME'] ?? 'localhost';
$script_name = $_SERVER['SCRIPT_NAME'] ?? '/index.php';

function bot($method, $datas = []) {
    $url = "https://api.telegram.org/bot" . API_KEY . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    $res = curl_exec($ch);
    if (curl_error($ch)) {
        return null;
    } else {
        return json_decode($res);
    }
}

function safe_file_get_contents($filename) {
    if (file_exists($filename) && is_readable($filename)) {
        $content = @file_get_contents($filename);
        if ($content !== false) {
            return $content;
        }
    }
    return "";
}

function safe_json_decode($filename, $assoc = true) {
    $content = safe_file_get_contents($filename);
    if (empty($content)) {
        return [];
    }
    $decoded = json_decode($content, $assoc);
    return is_array($decoded) ? $decoded : [];
}

function safe_file_put_contents($filename, $data) {
    $target_path = $filename;
    if (strpos($filename, 'data/') === 0 || strpos($filename, '/') === false) {
        $tmp_dir = sys_get_temp_dir() . '/bot_data';
        if (!is_dir($tmp_dir)) {
            @mkdir($tmp_dir, 0755, true);
        }
        $target_path = $tmp_dir . '/' . basename($filename);
    }
    
    $dir = dirname($target_path);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    
    @file_put_contents($target_path, $data);
}

// استقبال الـ Update من تيليجرام
$input = safe_file_get_contents('php://input');
$update = json_decode($input);
if (!$update) {
    // عرض رابط ربط الويب هوك في حال تم فتح الملف من المتصفح مباشرة
    echo "setWebhook ~> <a href=\"https://api.telegram.org/bot" . API_KEY . "/setwebhook?url=https://" . $server_name . "" . $script_name . "\">اضغط هنا لربط الويب هوك تلقائياً</a>";
    exit;
}

$message = $update->message ?? null;
$callback_query = $update->callback_query ?? null;

$chat_id = $message->chat->id ?? ($callback_query->message->chat->id ?? null);
$from_id = $message->from->id ?? ($callback_query->from->id ?? null);
$first_name = $message->from->first_name ?? ($callback_query->from->first_name ?? "");
$text = $message->text ?? "";
$tc = $message->chat->type ?? "";

$admin = "1022146710";
$Dev = [$admin, "5203138115"];
$eri = [$admin, "5203138115"];

// جلب بيانات المستخدمين
$user = safe_json_decode("data/user.json", true);
if (empty($user)) {
    $user = ["userlist" => [], "grouplist" => []];
}

// تسجيل الأعضاء والمجموعات
if ($tc == 'private' && $from_id) {
    if (!isset($user["userlist"]) || !in_array($from_id, $user["userlist"])) {
        $user["userlist"][] = $from_id;
        safe_file_put_contents("data/user.json", json_encode($user, JSON_UNESCAPED_UNICODE));
    }
} elseif (($tc == 'group' || $tc == 'supergroup') && $chat_id) {
    if (!isset($user["grouplist"]) || !in_array($chat_id, $user["grouplist"])) {
        $user["grouplist"][] = $chat_id;
        safe_file_put_contents("data/user.json", json_encode($user, JSON_UNESCAPED_UNICODE));
    }
}

// الرد على أمر /start
if ($text == "/start" && $tc == "private") {
    if (in_array($from_id, $Dev) || in_array($from_id, $eri)) {
        bot('sendmessage', [
            'chat_id' => $chat_id,
            'text' => "⋄︙ أهلاً عزيزي - [$first_name](tg://user?id=$from_id)\n⋄︙ إليك لوحة المطور الخاصة بوتك الشامل يعمل بنظام Vercel الآمن",
            'parse_mode' => "markdown",
            'reply_markup' => json_encode([
                'keyboard' => [
                    [['text' => "⋄ قسم البدء"], ['text' => "⋄ قسم الإذاعة"]],
                    [['text' => "⋄ الإحصائيات"], ['text' => "⋄ المشتركين"]]
                ],
                'resize_keyboard' => true
            ])
        ]);
    } else {
        bot('sendmessage', [
            'chat_id' => $chat_id,
            'text' => "أهلاً بك في البوت الخدمي المتطور عيوني."
        ]);
    }
} 
// قسم الإحصائيات
elseif ($text == "⋄ الإحصائيات") {
    $users_count = isset($user["userlist"]) ? count($user["userlist"]) : 0;
    $groups_count = isset($user["grouplist"]) ? count($user["grouplist"]) : 0;
    bot('sendmessage', [
        'chat_id' => $chat_id,
        'text' => "📊 إحصائيات البوت:\n- المشتركين: $users_count\n- المجموعات: $groups_count"
    ]);
}
?>
```
