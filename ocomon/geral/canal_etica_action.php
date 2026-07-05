<?php session_start();
/*
    Canal de Ética — ações da gestão interna (status / nota interna / resposta).
    Restrito a membros da ouvidoria (atribuídos à área Canal de Ética). Retorna JSON.
*/

require_once __DIR__ . "/" . "../../includes/include_basics_only.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use OcomonApi\Support\Email;
use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$data = ['success' => false, 'message' => ''];

function etica_out($ok, $msg)
{
    echo json_encode(['success' => $ok, 'message' => $msg]);
    exit();
}

if (!isset($_SESSION['s_logado']) || empty($_SESSION['s_logado'])) {
    etica_out(false, 'Sessão expirada. Faça login novamente.');
}

/* Localiza a área e valida a associação (ouvidoria) */
$eticaArea   = $conn->query("SELECT sis_id FROM sistemas WHERE sis_email = 'anonimo@mauagroup.com' OR sistema = 'Canal de Ética' LIMIT 1")->fetch();
$eticaAreaId = $eticaArea ? (int) $eticaArea['sis_id'] : 0;
$userAreas   = array_filter(array_map('trim', explode(',', getUserAreas($conn, (int) ($_SESSION['s_uid'] ?? 0)))));
if ($eticaAreaId <= 0 || !in_array((string) $eticaAreaId, $userAreas, true)) {
    etica_out(false, 'Acesso restrito à ouvidoria.');
}

/* CSRF */
if (!csrf_verify($_POST)) {
    etica_out(false, 'Formulário expirado. Reabra a manifestação e tente novamente.');
}

$user   = (int) ($_SESSION['s_uid'] ?? 0);
$numero = isset($_POST['numero']) ? (int) $_POST['numero'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';
$now    = date('Y-m-d H:i:s');

/* A manifestação precisa existir E pertencer à área confidencial */
$stmt = $conn->prepare("SELECT numero, contato_email, status FROM ocorrencias WHERE numero = :n AND sistema = :a LIMIT 1");
$stmt->execute([':n' => $numero, ':a' => $eticaAreaId]);
$ticket = $stmt->fetch();
if (!$ticket) {
    etica_out(false, 'Manifestação não encontrada.');
}

try {
    if ($action === 'status') {
        $newStatus = (int) ($_POST['status'] ?? 0);
        $chk = $conn->prepare("SELECT stat_id FROM status WHERE stat_id = :s LIMIT 1");
        $chk->execute([':s' => $newStatus]);
        if (!$chk->fetch()) {
            etica_out(false, 'Status inválido.');
        }
        $up = $conn->prepare("UPDATE ocorrencias SET status = :s WHERE numero = :n AND sistema = :a");
        $up->execute([':s' => $newStatus, ':n' => $numero, ':a' => $eticaAreaId]);

        /* Log de auditoria + etapa (consistência com o sistema de chamados) */
        $lg = $conn->prepare("INSERT INTO ocorrencias_log (log_numero, log_quem, log_data, log_status, log_descricao, log_tipo_edicao)
                              VALUES (:n, :q, :d, :s, 'Status alterado pela ouvidoria (Canal de Ética)', 1)");
        $lg->execute([':n' => $numero, ':q' => $user, ':d' => $now, ':s' => $newStatus]);
        if (function_exists('insert_ticket_stage')) {
            insert_ticket_stage($conn, $numero, 'edit', $newStatus);
        }
        etica_out(true, 'Status atualizado.');
    }

    if ($action === 'note') {
        $texto = trim(noHtml($_POST['texto'] ?? ''));
        if (mb_strlen($texto) < 2) {
            etica_out(false, 'Escreva a nota antes de salvar.');
        }
        $ins = $conn->prepare("INSERT INTO assentamentos (ocorrencia, assentamento, `data`, responsavel, tipo_assentamento, asset_privated)
                               VALUES (:n, :t, :d, :u, 8, 1)");
        $ins->execute([':n' => $numero, ':t' => $texto, ':d' => $now, ':u' => $user]);
        etica_out(true, 'Nota interna registrada.');
    }

    if ($action === 'reply') {
        $texto = trim(noHtml($_POST['texto'] ?? ''));
        if (mb_strlen($texto) < 2) {
            etica_out(false, 'Escreva a resposta antes de enviar.');
        }
        $email = trim((string) $ticket['contato_email']);
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            etica_out(false, 'Esta manifestação não tem e-mail de contato válido.');
        }

        /* Registra como assentamento PÚBLICO (o denunciante vê no acompanhamento) */
        $ins = $conn->prepare("INSERT INTO assentamentos (ocorrencia, assentamento, `data`, responsavel, tipo_assentamento, asset_privated)
                               VALUES (:n, :t, :d, :u, 8, 0)");
        $ins->execute([':n' => $numero, ':t' => $texto, ':d' => $now, ':u' => $user]);

        /* Envio pelo MESMO mecanismo do restante do OcoMon: respeita a fila
           (mail_queue) e usa a config de e-mail (.env/mailconfig via classe Email).
           A resposta também já fica visível no protocolo do denunciante
           (assentamento público inserido acima), mesmo que o e-mail falhe. */
        $mailSent = false;
        $mailErr  = '';
        try {
            $rowconfmail = getMailConfig($conn);
            $sendMethod  = (!empty($rowconfmail['mail_queue'])) ? 'queue' : 'send';
            $subject = "Canal de Ética — retorno sobre sua manifestação (protocolo #{$numero})";
            $body    = nl2br(htmlspecialchars($texto)) . "<br><br><small>Este é um retorno da ouvidoria referente ao protocolo #{$numero}. Sua identidade permanece protegida.</small>";
            $mail = (new Email())->bootstrap($subject, $body, $email, 'Canal de Ética', $numero);
            $mailSent = (bool) $mail->{$sendMethod}();
            if (!$mailSent) {
                $mailErr = trim(strip_tags(html_entity_decode((string) $mail->message()->getText())));
            }
        } catch (Throwable $e) {
            $mailSent = false;
            $mailErr = $e->getMessage();
        }
        $mailNote = $mailSent
            ? 'Resposta enviada por e-mail e registrada no protocolo.'
            : ('Resposta registrada no protocolo do denunciante. O e-mail não pôde ser enviado' . ($mailErr !== '' ? ' — ' . $mailErr : '') . '.');
        etica_out(true, $mailNote);
    }

    etica_out(false, 'Ação desconhecida.');
} catch (Throwable $e) {
    etica_out(false, 'Não foi possível concluir a ação agora.');
}
