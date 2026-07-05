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
$captcha = isset($post['captcha']) ? trim(noHtml($post['captcha'])) : '';
$wantsEmail = (isset($post['wants_email']) && $post['wants_email'] == '1');
$email   = ($wantsEmail && isset($post['contato_email'])) ? trim(noHtml($post['contato_email'])) : '';

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

/* Captcha (case-insensitive) */
if ($captcha === '' || empty($_SESSION['captcha']) || strtolower($captcha) !== strtolower($_SESSION['captcha'])) {
    $data['success'] = false;
    $data['field_id'] = 'captcha';
    $data['message'] = 'O texto da imagem não confere. Tente novamente.';
    echo json_encode($data);
    return false;
}
/* Consome o captcha para impedir reuso */
unset($_SESSION['captcha']);

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
        '" . $areaId . "', 'Anônimo', :email, '', '-1',
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
} catch (Exception $e) {
    $data['success'] = false;
    $data['message'] = 'Não foi possível registrar sua manifestação agora. Tente novamente em instantes.';
    echo json_encode($data);
    return false;
}

echo json_encode($data);
return false;
