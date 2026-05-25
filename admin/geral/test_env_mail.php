<?php session_start();

if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
    $_SESSION['session_expired'] = 1;
    echo "<script>top.window.location = '../../index.php'</script>";
    exit;
}

require_once __DIR__ . "/" . "../../includes/include_basics_only.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use OcomonApi\Support\Email;
use includes\classes\ConnectPDO;

header('Content-Type: application/json; charset=UTF-8');

$conn = ConnectPDO::getInstance();
$user = getUsers($conn, $_SESSION['s_uid']);

$recipientEmail = (!empty($_POST['recipient_email']) ? noHtml($_POST['recipient_email']) : ($user['email'] ?? ''));
$recipientName = ($user['nome'] ?? $_SESSION['s_usuario_nome'] ?? 'Usuário');

$response = [
    'success' => false,
    'message' => ''
];

if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Defina um e-mail de destino válido para o teste.';
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

$mail = (new Email())->bootstrap(
    'Teste de envio SMTP',
    'Este é um teste de envio usando a configuração atual de ambiente/aplicação.',
    $recipientEmail,
    $recipientName
);

if (!$mail->sendTest()) {
    $response['message'] = $mail->message()->getText();
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

$response['success'] = true;
$response['message'] = "E-mail de teste enviado para {$recipientEmail}.";
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
