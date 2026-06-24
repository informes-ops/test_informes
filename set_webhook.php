<?php
/* ============================================================
   Registra el webhook del bot. Ábrelo UNA vez en el navegador
   y cuando veas {"ok":true,...} BÓRRALO o renómbralo.
   ============================================================ */

$BOT_TOKEN      = '8902642078:AAHSqay9kippBSkGyu0uN9eJbAKgD4DW69Q';     
$WEBHOOK_URL    = 'https://zgroupinformes.com/telegram_bot.php';    
$WEBHOOK_SECRET = '123456';                  

header('Content-Type: text/plain; charset=utf-8');

$ch = curl_init("https://api.telegram.org/bot{$BOT_TOKEN}/setWebhook");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'url'                  => $WEBHOOK_URL,
    'secret_token'         => $WEBHOOK_SECRET,
    'allowed_updates'      => ['message'],
    'drop_pending_updates' => true,
]));
$res = curl_exec($ch);
curl_close($ch);

echo "Respuesta de Telegram:\n\n";
echo $res . "\n\n";
echo "Si dice \"ok\":true -> listo. BORRA este archivo del servidor.\n";
echo "Para apagar el bot luego, abre:\n";
echo "https://api.telegram.org/bot{$BOT_TOKEN}/deleteWebhook\n";
