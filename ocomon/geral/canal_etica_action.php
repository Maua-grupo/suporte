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

/* Template do e-mail de retorno da ouvidoria (HTML de e-mail: tabelas + estilos inline,
   fontes web-safe, cores da marca do DESIGN.md). $texto é o conteúdo cru (será escapado). */
function etica_reply_email_html(string $texto, int $numero, string $siteUrl, string $trackUrl): string
{
    $msg  = nl2br(htmlspecialchars($texto, ENT_QUOTES, 'UTF-8'));
    $logo = ($siteUrl !== '')
        ? '<img src="' . htmlspecialchars($siteUrl, ENT_QUOTES) . '/includes/logos/MAIN_LOGO.png" alt="Maua Group" width="26" height="26" style="width:26px;height:26px;vertical-align:middle;border:0;margin-right:10px;">'
        : '';
    $track = '';
    if ($trackUrl !== '') {
        $track = '<table role="presentation" cellpadding="0" cellspacing="0" style="margin:22px 0 4px;"><tr>'
               . '<td style="border-radius:8px;background:#146880;">'
               . '<a href="' . htmlspecialchars($trackUrl, ENT_QUOTES) . '" style="display:inline-block;padding:11px 22px;color:#ffffff;text-decoration:none;font-size:14px;font-weight:bold;font-family:Arial,Helvetica,sans-serif;">Acompanhar minha manifestação</a>'
               . '</td></tr></table>';
    }
    return '
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef3f6;padding:24px 12px;font-family:Arial,Helvetica,sans-serif;">
  <tr><td align="center">
    <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border:1px solid #d7e1e8;border-radius:14px;overflow:hidden;">
      <tr><td style="background:#2b414b;padding:18px 28px;color:#ffffff;font-size:16px;font-weight:bold;">' . $logo . 'Canal de Ética e Ouvidoria</td></tr>
      <tr><td style="padding:28px;">
        <h1 style="margin:0 0 4px;color:#203f4c;font-size:20px;font-weight:bold;">Retorno da ouvidoria</h1>
        <p style="margin:0 0 20px;color:#5a7280;font-size:14px;">Referente ao protocolo <b style="color:#203f4c;">#' . $numero . '</b></p>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f8fa;border:1px solid #e3ebf0;border-radius:10px;">
          <tr><td style="padding:16px 18px;color:#18313f;font-size:15px;line-height:1.65;">' . $msg . '</td></tr>
        </table>
        ' . $track . '
        <p style="margin:22px 0 0;color:#5a7280;font-size:13px;line-height:1.5;"><b style="color:#146880;">Confidencial.</b> Sua identidade permanece protegida. Guarde o número de protocolo para acompanhar.</p>
      </td></tr>
      <tr><td style="background:#f5f8fa;border-top:1px solid #e3ebf0;padding:16px 28px;color:#7c8a93;font-size:12px;line-height:1.5;">Maua Group &middot; Canal de Ética e Ouvidoria &middot; mensagem confidencial.<br>Este é um e-mail automático de retorno — não é necessário respondê-lo.</td></tr>
    </table>
  </td></tr>
</table>';
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

            /* URL do site (logo + link de acompanhamento) e link global do protocolo. */
            $cfg      = getConfig($conn);
            $siteUrl  = rtrim((string) ($cfg['conf_ocomon_site'] ?? ''), '/');
            $trackUrl = '';
            $gt = $conn->prepare("SELECT gt_id FROM global_tickets WHERE gt_ticket = :n LIMIT 1");
            $gt->execute([':n' => $numero]);
            $gtRow = $gt->fetch();
            if ($gtRow && $siteUrl !== '') {
                $trackUrl = $siteUrl . '/ocomon/open_form/ticket_show_global.php?numero=' . $numero . '&id=' . urlencode((string) $gtRow['gt_id']);
            }

            $subject = "Canal de Ética — retorno sobre sua manifestação (protocolo #{$numero})";
            $body    = etica_reply_email_html($texto, $numero, $siteUrl, $trackUrl);
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
