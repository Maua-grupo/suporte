<?php session_start();
/*
    Canal de Ética e Ouvidoria — processamento do formulário público.
    Grava a manifestação como chamado (ocorrencias) sob o usuário "Anônimo",
    na área confidencial "Canal de Ética". Retorna protocolo + link de acompanhamento.
*/

require_once __DIR__ . "/" . "../../includes/include_basics_only.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

/* ---- Rate-limit por IP (baseado em arquivo; sem depender de DDL/tabela) -----
   Invisível e acessível: não pede nada de quem envia. Se o diretório temporário
   não for gravável, degrada em silêncio para as demais camadas anti-spam. */
function getClientIp(): string
{
    foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) {
            $ip = trim(explode(',', $_SERVER[$k])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return '';
}

/* Retorna '' se liberado, ou a mensagem de bloqueio. Grava o timestamp se $record=true. */
function eticaIpThrottle(string $ip, bool $record): string
{
    if ($ip === '') {
        return '';
    }
    $cooldown    = 15;   // segundos entre envios do mesmo IP
    $window      = 600;  // janela de 10 minutos
    $maxInWindow = 5;    // no máximo 5 envios por IP na janela
    $now = time();

    $file = sys_get_temp_dir() . '/etica_rl_' . md5($ip) . '.txt';
    $fh = @fopen($file, 'c+');
    if (!$fh) {
        return '';
    }
    @flock($fh, LOCK_EX);
    $raw = stream_get_contents($fh);
    $times = array_values(array_filter(
        array_map('intval', explode(',', trim((string) $raw))),
        function ($t) use ($now, $window) {
            return $t > 0 && ($now - $t) < $window;
        }
    ));

    $msg = '';
    if (!empty($times)) {
        if (($now - max($times)) < $cooldown) {
            $msg = 'Aguarde alguns instantes antes de enviar outra manifestação.';
        } elseif (count($times) >= $maxInWindow) {
            $msg = 'Você atingiu o limite de envios por agora. Tente novamente mais tarde.';
        }
    }

    if ($msg === '' && $record) {
        $times[] = $now;
        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, implode(',', $times));
    }
    @flock($fh, LOCK_UN);
    @fclose($fh);
    return $msg;
}

$now  = date("Y-m-d H:i:s");
$post = $_POST;

$data = [
    'success'      => true,
    'message'      => '',
    'field_id'     => '',
    'protocol'     => '',
    'tracking_uri' => '',
];

/* Somente o action esperado */
if (!isset($post['action']) || $post['action'] !== 'open') {
    $data['success'] = false;
    $data['message'] = 'Requisição inválida.';
    echo json_encode($data);
    return false;
}

/* CSRF */
if (!csrf_verify($post)) {
    $data['success'] = false;
    $data['message'] = 'Formulário expirado. Recarregue a página e tente novamente.';
    echo json_encode($data);
    return false;
}

/* --- Anti-spam sem captcha (acessível: não exige nada de quem envia) -------- */
/* 1) Honeypot: campo oculto que só robôs preenchem. */
if (!empty($post['confirm_url'])) {
    $data['success'] = false;
    $data['message'] = 'Não foi possível processar o envio.';
    echo json_encode($data);
    return false;
}
/* 2) Tempo mínimo de preenchimento: envio instantâneo indica robô. */
if (empty($_SESSION['etica_form_ts'])) {
    $data['success'] = false;
    $data['message'] = 'Formulário expirado. Recarregue a página e tente novamente.';
    echo json_encode($data);
    return false;
}
if ((time() - (int) $_SESSION['etica_form_ts']) < 2) {
    $data['success'] = false;
    $data['message'] = 'Envio muito rápido — confira sua manifestação e tente novamente.';
    echo json_encode($data);
    return false;
}
/* 3) Rate-limit por sessão: evita flood de envios. */
if (!empty($_SESSION['etica_last_ok']) && (time() - (int) $_SESSION['etica_last_ok']) < 30) {
    $data['success'] = false;
    $data['message'] = 'Aguarde alguns instantes antes de enviar outra manifestação.';
    echo json_encode($data);
    return false;
}
/* 4) Rate-limit por IP: robusto contra bots que trocam de sessão/cookie. */
$clientIp = getClientIp();
$ipBlock  = eticaIpThrottle($clientIp, false);
if ($ipBlock !== '') {
    $data['success'] = false;
    $data['message'] = $ipBlock;
    echo json_encode($data);
    return false;
}

/* Localiza os registros estruturais do canal (robusto p/ produção) */
$anonUser  = $conn->query("SELECT user_id FROM usuarios WHERE email = 'anonimo@mauagroup.com' LIMIT 1")->fetch();
$eticaArea = $conn->query("SELECT sis_id FROM sistemas WHERE sistema = 'Canal de Ética' LIMIT 1")->fetch();
if (!$anonUser || !$eticaArea) {
    $data['success'] = false;
    $data['message'] = 'O Canal de Ética não está configurado neste ambiente.';
    echo json_encode($data);
    return false;
}
$anonId = (int) $anonUser['user_id'];
$areaId = (int) $eticaArea['sis_id'];

$channelRow = $conn->query("SELECT id FROM channels WHERE name = 'Canal de Ética' LIMIT 1")->fetch();
$channelId  = ($channelRow ? (int) $channelRow['id'] : 1);

$prioRow = getDefaultPriority($conn);
$prio    = (!empty($prioRow['pr_cod']) ? (int) $prioRow['pr_cod'] : 2);

$statusId = 1; /* Aguardando atendimento */

/* --- Entrada --------------------------------------------------------------- */
$tiposPermitidos = ['Denúncia', 'Reclamação', 'Sugestão', 'Elogio', 'Outro apontamento'];
$tipo    = isset($post['tipo']) ? trim(noHtml($post['tipo'])) : '';
$assunto = isset($post['assunto']) ? trim(noHtml($post['assunto'])) : '';
$relato  = isset($post['descricao']) ? trim(noHtml($post['descricao'])) : '';
$wantsEmail = (isset($post['wants_email']) && $post['wants_email'] == '1');
$email   = ($wantsEmail && isset($post['contato_email'])) ? trim(noHtml($post['contato_email'])) : '';

/* Setor/Departamento (opcional): valida contra localizacao; -1 = não informado */
$setor = (isset($post['setor']) && $post['setor'] !== '') ? (int) $post['setor'] : -1;
if ($setor > 0) {
    $chkSetor = $conn->query("SELECT loc_id FROM localizacao WHERE loc_id = " . $setor . " LIMIT 1")->fetch();
    if (!$chkSetor) { $setor = -1; }
}

/* --- Validação ------------------------------------------------------------- */
if (!in_array($tipo, $tiposPermitidos, true)) {
    $data['success'] = false;
    $data['field_id'] = 'tipo';
    $data['message'] = 'Selecione o tipo de manifestação.';
    echo json_encode($data);
    return false;
}

if ($relato === '' || mb_strlen($relato) < 10) {
    $data['success'] = false;
    $data['field_id'] = 'descricao';
    $data['message'] = 'Descreva sua manifestação com um pouco mais de detalhe (mínimo de 10 caracteres).';
    echo json_encode($data);
    return false;
}

if ($wantsEmail && $email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $data['success'] = false;
    $data['field_id'] = 'contato_email';
    $data['message'] = 'O e-mail informado não parece válido. Corrija ou desmarque a opção de retorno.';
    echo json_encode($data);
    return false;
}

/* --- Monta a descrição (tipo + assunto no topo, relato em seguida) --------- */
$descricao  = "[Canal de Ética] Tipo: " . $tipo . "\n";
if ($assunto !== '') {
    $descricao .= "Assunto: " . $assunto . "\n";
}
$descricao .= "\n" . $relato;

/* --- Inserção do chamado --------------------------------------------------- */
$sql = "INSERT INTO ocorrencias
    (
        client,
        problema, descricao, instituicao, equipamento,
        sistema, contato, contato_email, telefone, `local`,
        operador, data_abertura, data_fechamento, `status`, data_atendimento,
        aberto_por, oco_scheduled, oco_scheduled_to,
        oco_real_open_date, date_first_queued, oco_prior, oco_channel, oco_tag,
        profile_id
    )
    VALUES
    (
        " . dbField(null) . ",
        '-1', :descricao, '-1', '',
        '" . $areaId . "', 'Anônimo', :email, '', '" . $setor . "',
        '" . $anonId . "', '{$now}', null, '" . $statusId . "', null,
        '" . $anonId . "', '0', null,
        '{$now}', null, '" . $prio . "', '" . $channelId . "', " . dbField(null, 'text') . ",
        " . dbField(null) . "
    )";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':descricao', $descricao, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    $numero = (int) $conn->lastInsertId();

    /* Link global de acompanhamento (protocolo rastreável sem login) */
    $gtId = random64();
    $qryGt = "INSERT INTO global_tickets (gt_ticket, gt_id) VALUES (" . $numero . ", '" . $gtId . "')";
    $conn->exec($qryGt);

    /* Consistência com o restante do sistema de chamados */
    insert_ticket_stage($conn, $numero, 'start', $statusId);
    firstLog($conn, $numero, 0, 1);

    $data['success']      = true;
    $data['protocol']     = (string) $numero;
    $data['tracking_uri'] = "ticket_show_global.php?numero=" . $numero . "&id=" . urlencode($gtId);

    /* Marca o envio p/ rate-limit e invalida o timestamp do formulário. */
    $_SESSION['etica_last_ok'] = time();
    unset($_SESSION['etica_form_ts']);
    /* Registra o envio para o rate-limit por IP. */
    eticaIpThrottle($clientIp, true);
} catch (Exception $e) {
    $data['success'] = false;
    $data['message'] = 'Não foi possível registrar sua manifestação agora. Tente novamente em instantes.';
    echo json_encode($data);
    return false;
}

echo json_encode($data);
return false;
