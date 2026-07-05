<?php session_start();
/*
    Canal de Ética e Ouvidoria — GESTÃO INTERNA (restrita à ouvidoria).
    Acesso somente para usuários explicitamente atribuídos à área "Canal de Ética"
    (usuarios_areas) — independente do nível. Nem admin vê se não for membro.
    Lista + detalhe das denúncias; ações ficam em canal_etica_action.php.
*/

require_once __DIR__ . "/" . "../../includes/include_geral_new.inc.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

/* Precisa estar logado */
if (!isset($_SESSION['s_logado']) || empty($_SESSION['s_logado'])) {
    echo "<script>top.window.location = '../../index.php'</script>";
    exit();
}

/* Localiza a área confidencial (robusto a charset) */
$eticaArea   = $conn->query("SELECT sis_id, sistema FROM sistemas WHERE sis_email = 'anonimo@mauagroup.com' OR sistema = 'Canal de Ética' LIMIT 1")->fetch();
$eticaAreaId = $eticaArea ? (int) $eticaArea['sis_id'] : 0;

/* Membro da ouvidoria = atribuído à área Canal de Ética (usuarios_areas) */
$userAreas   = array_filter(array_map('trim', explode(',', getUserAreas($conn, (int) $_SESSION['s_logado']))));
$isOuvidoria = ($eticaAreaId > 0 && in_array((string) $eticaAreaId, $userAreas, true));

/* Helpers ------------------------------------------------------------------- */
function etica_parse($descricao)
{
    $tipo = ''; $assunto = '';
    if (preg_match('/Tipo:\s*(.+)/', $descricao, $m))    { $tipo = trim($m[1]); }
    if (preg_match('/Assunto:\s*(.+)/', $descricao, $m)) { $assunto = trim($m[1]); }
    $parts  = preg_split('/\n\s*\n/', $descricao, 2);
    $relato = isset($parts[1]) ? trim($parts[1]) : trim(preg_replace('/^\[Canal de Ética\].*$/m', '', $descricao));
    return ['tipo' => $tipo, 'assunto' => $assunto, 'relato' => $relato];
}
function etica_status_badge($statCat)
{
    /* stat_cat: 1 aguardando/parado, 2 em atendimento, 3 fornecedor, 4 encerrado */
    switch ((int) $statCat) {
        case 4:  return 'bg-oc-teal';       // encerrado
        case 2:  return 'bg-info';          // em atendimento
        case 3:  return 'bg-warning';       // aguardando terceiro
        default: return 'bg-primary';       // aguardando
    }
}

/* ===========================================================================
   AJAX: detalhe de uma denúncia (fragmento p/ modal)
   =========================================================================== */
if (isset($_GET['ajax']) && $_GET['ajax'] === 'detail') {
    header('Content-Type: text/html; charset=utf-8');
    if (!$isOuvidoria) { echo '<div class="p-4 text-danger">Acesso restrito.</div>'; exit(); }

    $numero = isset($_GET['numero']) ? (int) $_GET['numero'] : 0;
    $stmt = $conn->prepare("SELECT o.*, s.status AS status_nome, s.stat_cat, l.local AS setor_nome
                            FROM ocorrencias o
                            LEFT JOIN status s ON o.status = s.stat_id
                            LEFT JOIN localizacao l ON o.local = l.loc_id
                            WHERE o.numero = :n AND o.sistema = :a LIMIT 1");
    $stmt->execute([':n' => $numero, ':a' => $eticaAreaId]);
    $d = $stmt->fetch();
    if (!$d) { echo '<div class="p-4 text-muted">Manifestação não encontrada.</div>'; exit(); }

    $p = etica_parse($d['descricao']);
    $email = trim((string) $d['contato_email']);

    /* Assentamentos (notas internas + respostas) */
    $stA = $conn->prepare("SELECT a.*, u.nome AS autor FROM assentamentos a
                           LEFT JOIN usuarios u ON u.user_id = a.responsavel
                           WHERE a.ocorrencia = :n ORDER BY a.data ASC, a.numero ASC");
    $stA->execute([':n' => $numero]);
    $assentamentos = $stA->fetchAll();

    /* Lista de status */
    $statuses = $conn->query("SELECT stat_id, status FROM status ORDER BY status")->fetchAll();
    ?>
    <div class="etica-detail" data-numero="<?= $numero; ?>">
        <?= csrf_input(); ?>
        <div id="etica-action-result"></div>

        <div class="d-flex flex-wrap align-items-center mb-3" style="gap:10px">
            <span class="badge badge-light" style="font-size:.95rem">Protocolo <b>#<?= $numero; ?></b></span>
            <span class="badge <?= etica_status_badge($d['stat_cat']); ?> text-white"><?= htmlspecialchars($d['status_nome'] ?? '—'); ?></span>
            <?php if ($p['tipo']) { ?><span class="badge badge-secondary"><?= htmlspecialchars($p['tipo']); ?></span><?php } ?>
            <span class="text-muted ml-auto small"><i class="far fa-clock"></i> <?= dateScreen($d['data_abertura'], 0, 'd/m/Y H:i'); ?></span>
        </div>

        <?php if ($p['assunto']) { ?><h5 class="mb-2"><?= htmlspecialchars($p['assunto']); ?></h5><?php } ?>

        <div class="card mb-3">
            <div class="card-body" style="white-space:pre-wrap"><?= nl2br(htmlspecialchars($p['relato'])); ?></div>
        </div>

        <div class="row small text-muted mb-3">
            <div class="col-sm-6"><i class="fas fa-building"></i> Setor: <?= htmlspecialchars($d['setor_nome'] ?: 'Não informado'); ?></div>
            <div class="col-sm-6">
                <?php if ($email !== '') { ?>
                    <i class="fas fa-envelope text-info"></i> Contato: <b><?= htmlspecialchars($email); ?></b>
                <?php } else { ?>
                    <i class="fas fa-user-secret"></i> Sem e-mail — <b>anônimo</b>
                <?php } ?>
            </div>
        </div>

        <!-- Histórico de tratativas -->
        <h6 class="text-uppercase text-muted mb-2" style="letter-spacing:.04em;font-size:.72rem">Tratativas</h6>
        <?php if (empty($assentamentos)) { ?>
            <p class="text-muted small">Nenhuma tratativa registrada ainda.</p>
        <?php } else { ?>
            <ul class="list-unstyled">
            <?php foreach ($assentamentos as $a) {
                $priv = ((int) $a['asset_privated'] === 1);
            ?>
                <li class="mb-2 p-2 rounded" style="background:<?= $priv ? '#fff6e6' : '#eef7f1'; ?>">
                    <div class="small mb-1">
                        <?php if ($priv) { ?>
                            <span class="badge badge-warning"><i class="fas fa-lock"></i> Nota interna</span>
                        <?php } else { ?>
                            <span class="badge badge-success"><i class="fas fa-reply"></i> Resposta ao denunciante</span>
                        <?php } ?>
                        <span class="text-muted">· <?= htmlspecialchars($a['autor'] ?? '—'); ?> · <?= dateScreen($a['data'], 0, 'd/m/Y H:i'); ?></span>
                    </div>
                    <div style="white-space:pre-wrap"><?= nl2br(htmlspecialchars($a['assentamento'])); ?></div>
                </li>
            <?php } ?>
            </ul>
        <?php } ?>

        <hr>

        <!-- Ações -->
        <div class="form-row">
            <div class="form-group col-md-6">
                <label class="small font-weight-bold">Mudar status</label>
                <div class="input-group">
                    <select class="form-control" id="etica-status-sel">
                        <?php foreach ($statuses as $st) { ?>
                            <option value="<?= (int) $st['stat_id']; ?>" <?= ((int) $st['stat_id'] === (int) $d['status']) ? 'selected' : ''; ?>><?= htmlspecialchars($st['status']); ?></option>
                        <?php } ?>
                    </select>
                    <div class="input-group-append">
                        <button class="btn btn-primary" id="etica-status-btn"><i class="fas fa-save"></i> Salvar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="small font-weight-bold"><i class="fas fa-lock text-warning"></i> Nota interna (confidencial — o denunciante não vê)</label>
            <textarea class="form-control" id="etica-note-txt" rows="2" placeholder="Registre a apuração/tratativa interna"></textarea>
            <button class="btn btn-outline-secondary btn-sm mt-2" id="etica-note-btn"><i class="fas fa-plus"></i> Adicionar nota interna</button>
        </div>

        <div class="form-group">
            <label class="small font-weight-bold"><i class="fas fa-reply text-success"></i> Responder ao denunciante</label>
            <?php if ($email !== '') { ?>
                <textarea class="form-control" id="etica-reply-txt" rows="2" placeholder="Mensagem que será enviada por e-mail e ficará visível no protocolo do denunciante"></textarea>
                <button class="btn btn-success btn-sm mt-2" id="etica-reply-btn"><i class="fas fa-paper-plane"></i> Enviar resposta por e-mail</button>
            <?php } else { ?>
                <p class="text-muted small mb-0">O denunciante optou por não informar e-mail — não há como responder diretamente. Registre a tratativa como nota interna.</p>
            <?php } ?>
        </div>
    </div>

    <script>
        (function () {
            var $root = $('.etica-detail');
            var numero = $root.data('numero');
            var csrf = $root.find('#csrf').val();
            var csrfKey = $root.find('#csrf_session_key').val();

            function post(action, extra, $btn) {
                var data = $.extend({ action: action, numero: numero, csrf: csrf, csrf_session_key: csrfKey }, extra);
                if ($btn) { $btn.prop('disabled', true); }
                $.post('./canal_etica_action.php', data, null, 'json')
                    .done(function (r) {
                        if (r && r.success) {
                            $('#etica-detail-body').load('./canal_etica_gestao.php?ajax=detail&numero=' + numero);
                            reloadEticaList();
                        } else {
                            $('#etica-action-result').html('<div class="alert alert-warning py-2">' + ((r && r.message) || 'Não foi possível concluir.') + '</div>');
                            if ($btn) { $btn.prop('disabled', false); }
                        }
                    })
                    .fail(function () {
                        $('#etica-action-result').html('<div class="alert alert-danger py-2">Falha de comunicação. Tente novamente.</div>');
                        if ($btn) { $btn.prop('disabled', false); }
                    });
            }

            $('#etica-status-btn').on('click', function () {
                post('status', { status: $('#etica-status-sel').val() }, $(this));
            });
            $('#etica-note-btn').on('click', function () {
                var t = $.trim($('#etica-note-txt').val());
                if (t.length < 2) { $('#etica-note-txt').focus(); return; }
                post('note', { texto: t }, $(this));
            });
            $('#etica-reply-btn').on('click', function () {
                var t = $.trim($('#etica-reply-txt').val());
                if (t.length < 2) { $('#etica-reply-txt').focus(); return; }
                post('reply', { texto: t }, $(this));
            });
        })();
    </script>
    <?php
    exit();
}

/* ===========================================================================
   PÁGINA: lista das denúncias
   =========================================================================== */
$rows = [];
if ($isOuvidoria) {
    $st = $conn->prepare("SELECT o.numero, o.data_abertura, o.status, o.contato_email, o.descricao,
                                 s.status AS status_nome, s.stat_cat, l.local AS setor_nome
                          FROM ocorrencias o
                          LEFT JOIN status s ON o.status = s.stat_id
                          LEFT JOIN localizacao l ON o.local = l.loc_id
                          WHERE o.sistema = :a
                          ORDER BY o.data_abertura DESC");
    $st->execute([':a' => $eticaAreaId]);
    $rows = $st->fetchAll();
}
$totalAbertas = 0;
foreach ($rows as $r) { if ((int) $r['stat_cat'] !== 4) { $totalAbertas++; } }
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão do Canal de Ética</title>
    <link rel="stylesheet" type="text/css" href="../../includes/css/estilos.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/bootstrap/custom.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/css/ux_refresh.css" />
    <link rel="shortcut icon" href="../../includes/icons/favicon.ico">
    <style>
        .etica-gestao-head { display:flex; align-items:center; flex-wrap:wrap; gap:12px; }
        .etica-shield { width:44px; height:44px; border-radius:12px; display:inline-flex; align-items:center; justify-content:center; color:#146880; background:rgba(75,163,199,.14); font-size:20px; }
        .etica-tag-conf { font-size:.72rem; letter-spacing:.04em; text-transform:uppercase; font-weight:700; color:#8a5a0c; background:rgba(138,90,12,.12); padding:3px 10px; border-radius:999px; }
        table.etica-table td, table.etica-table th { vertical-align: middle; }
        .etica-trecho { max-width: 360px; }
    </style>
</head>

<body class="app-embedded-screen canal-etica-gestao">
    <div class="container-fluid app-screen-shell">

    <?php if (!$isOuvidoria) { ?>
        <div class="app-page-title"><div class="app-page-title-main"><h4><i class="fas fa-user-shield"></i> Canal de Ética</h4></div></div>
        <?= message('warning', 'Acesso restrito', 'Esta área é exclusiva da ouvidoria. Seu usuário não está autorizado a visualizar as manifestações do Canal de Ética.', '', '', true); ?>
    <?php } else { ?>

        <div class="app-page-title">
            <div class="app-page-title-main etica-gestao-head">
                <span class="etica-shield"><i class="fas fa-user-shield"></i></span>
                <div>
                    <h4 class="mb-0">Gestão do Canal de Ética</h4>
                    <p class="mb-0">Ouvidoria · <?= count($rows); ?> manifestaç<?= count($rows) === 1 ? 'ão' : 'ões'; ?> · <?= $totalAbertas; ?> em aberto</p>
                </div>
            </div>
            <span class="etica-tag-conf"><i class="fas fa-lock"></i> Confidencial</span>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover mb-0 etica-table">
                    <thead>
                        <tr class="header">
                            <th>Protocolo</th><th>Data</th><th>Tipo</th><th>Assunto / trecho</th><th>Setor</th><th>Status</th><th>Contato</th><th class="text-right">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rows)) { ?>
                            <tr><td colspan="8" class="text-center text-muted p-4">Nenhuma manifestação registrada ainda.</td></tr>
                        <?php } foreach ($rows as $r) {
                            $p = etica_parse($r['descricao']);
                            $resumo = $p['assunto'] !== '' ? $p['assunto'] : mb_substr($p['relato'], 0, 90) . (mb_strlen($p['relato']) > 90 ? '…' : '');
                        ?>
                            <tr>
                                <td class="font-weight-bold">#<?= (int) $r['numero']; ?></td>
                                <td class="text-nowrap small"><?= dateScreen($r['data_abertura'], 0, 'd/m/Y H:i'); ?></td>
                                <td><span class="badge badge-secondary"><?= htmlspecialchars($p['tipo'] ?: '—'); ?></span></td>
                                <td class="etica-trecho small"><?= htmlspecialchars($resumo); ?></td>
                                <td class="small"><?= htmlspecialchars($r['setor_nome'] ?: '—'); ?></td>
                                <td><span class="badge <?= etica_status_badge($r['stat_cat']); ?> text-white"><?= htmlspecialchars($r['status_nome'] ?? '—'); ?></span></td>
                                <td class="text-center"><?php if (trim((string) $r['contato_email']) !== '') { ?><i class="fas fa-envelope text-info" title="Deixou e-mail"></i><?php } else { ?><i class="fas fa-user-secret text-muted" title="Anônimo"></i><?php } ?></td>
                                <td class="text-right"><button class="btn btn-sm btn-primary etica-ver" data-numero="<?= (int) $r['numero']; ?>"><i class="fas fa-folder-open"></i> Ver</button></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal detalhe -->
        <div class="modal fade" id="etica-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-user-shield text-secondary"></i>&nbsp; Manifestação</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body" id="etica-detail-body"><div class="text-center p-4 text-muted"><i class="fas fa-spinner fa-spin"></i> Carregando…</div></div>
                </div>
            </div>
        </div>
    <?php } ?>
    </div>

    <script src="../../includes/components/jquery/jquery.js"></script>
    <script src="../../includes/components/bootstrap/js/bootstrap.bundle.js"></script>
    <script>
        function reloadEticaList() { /* recarrega a página no iframe após uma ação */
            setTimeout(function () { location.reload(); }, 700);
        }
        $(function () {
            $(document).on('click', '.etica-ver', function () {
                var n = $(this).data('numero');
                $('#etica-detail-body').html('<div class="text-center p-4 text-muted"><i class="fas fa-spinner fa-spin"></i> Carregando…</div>');
                $('#etica-detail-body').load('./canal_etica_gestao.php?ajax=detail&numero=' + n);
                $('#etica-modal').modal('show');
            });
        });
    </script>
</body>

</html>
