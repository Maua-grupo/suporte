<?php session_start();
/*
    Canal de Ética e Ouvidoria — formulário público (sem login).
    Registra a manifestação como chamado sob o usuário "Anônimo", na área
    confidencial "Canal de Ética". Parte pública apenas.
*/

require_once __DIR__ . "/" . "../../includes/include_geral_new.inc.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

/* Nunca deve ser acessado por quem já está logado (é canal público). */
if (isset($_SESSION['s_logado']) && $_SESSION['s_logado'] == 1) {
    echo "<script>top.window.location = '../../index.php'</script>";
    exit();
}

/* Localiza os registros estruturais do canal (usuário Anônimo + área). */
$anonUser = $conn->query("SELECT user_id FROM usuarios WHERE email = 'anonimo@mauagroup.com' LIMIT 1")->fetch();
$eticaArea = $conn->query("SELECT sis_id FROM sistemas WHERE sistema = 'Canal de Ética' LIMIT 1")->fetch();
$canalAtivo = ($anonUser && $eticaArea);

$tiposManifestacao = ['Denúncia', 'Reclamação', 'Sugestão', 'Elogio', 'Outro apontamento'];

/* Setores/Departamentos para seleção opcional (gerenciáveis em Admin > Departamentos). */
$setores = function_exists('getDepartments') ? getDepartments($conn) : [];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Canal de Ética e Ouvidoria</title>
    <link rel="stylesheet" type="text/css" href="../../includes/components/fontawesome/css/all.min.css" />
    <link rel="stylesheet" type="text/css" href="../../includes/css/canal_etica.css" />
    <link rel="shortcut icon" href="../../includes/icons/favicon.ico">
</head>

<body class="etica-body">

    <header class="etica-topbar">
        <span class="etica-logo"><img src="../../MAIN_LOGO.svg" alt="Maua Group"></span>
        <a class="etica-back" href="../../login.php"><i class="fas fa-arrow-left" aria-hidden="true"></i> Voltar ao acesso</a>
    </header>

    <main class="etica-shell">
        <div class="etica-card" id="etica-card">

        <?php if (!$canalAtivo) { ?>

            <div class="etica-confirm">
                <div class="etica-confirm-badge" style="color:#8a5a0c;background:rgba(138,90,12,.12);border-color:rgba(138,90,12,.24)">
                    <i class="fas fa-tools" aria-hidden="true"></i>
                </div>
                <h2>Canal em configuração</h2>
                <p>O Canal de Ética ainda não foi ativado neste ambiente. Procure a equipe responsável para concluir a configuração.</p>
                <a class="etica-btn etica-btn-ghost" href="../../login.php"><i class="fas fa-arrow-left" aria-hidden="true"></i> Voltar ao acesso</a>
            </div>

        <?php } else { ?>

            <div class="etica-hero">
                <div class="etica-hero-badge"><i class="fas fa-user-shield" aria-hidden="true"></i></div>
                <h1>Canal de Ética e Ouvidoria</h1>
                <p class="etica-lead">Um espaço seguro e confidencial para registrar denúncias, reclamações, sugestões e outros apontamentos. Você não precisa fazer login nem se identificar.</p>
            </div>

            <div class="etica-assurances">
                <div class="etica-assurance">
                    <span class="ico"><i class="fas fa-user-secret" aria-hidden="true"></i></span>
                    <b>Anônimo</b>
                    <span>Sem login e sem seus dados. Você decide o que informar.</span>
                </div>
                <div class="etica-assurance">
                    <span class="ico"><i class="fas fa-lock" aria-hidden="true"></i></span>
                    <b>Confidencial</b>
                    <span>Sua manifestação é tratada com sigilo pela equipe responsável.</span>
                </div>
                <div class="etica-assurance">
                    <span class="ico"><i class="fas fa-hashtag" aria-hidden="true"></i></span>
                    <b>Rastreável</b>
                    <span>Você recebe um número de protocolo para acompanhar.</span>
                </div>
            </div>

            <form class="etica-form" id="etica-form" autocomplete="off">
                <?= csrf_input(); ?>
                <div id="etica-result"></div>

                <div class="etica-field">
                    <label for="tipo">Tipo de manifestação <span class="req">*</span></label>
                    <select class="etica-select" id="tipo" name="tipo">
                        <?php foreach ($tiposManifestacao as $tipo) { ?>
                            <option value="<?= htmlspecialchars($tipo, ENT_QUOTES); ?>"><?= htmlspecialchars($tipo, ENT_QUOTES); ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="etica-field">
                    <label for="assunto">Assunto <small style="font-weight:400;color:#5a7280">(opcional)</small></label>
                    <input class="etica-input" type="text" id="assunto" name="assunto" maxlength="150" placeholder="Um resumo curto da sua manifestação">
                </div>

                <div class="etica-field">
                    <label for="descricao">Descrição <span class="req">*</span></label>
                    <textarea class="etica-textarea" id="descricao" name="descricao" maxlength="5000" placeholder="Descreva o que aconteceu com o máximo de detalhes que puder: o que, quando, onde e quem esteve envolvido. Evite incluir dados que não sejam necessários."></textarea>
                    <div class="etica-hint">Quanto mais claro o relato, melhor a apuração. Você não é obrigado a se identificar.</div>
                </div>

                <?php if (!empty($setores)) { ?>
                <div class="etica-field">
                    <label for="setor">Setor / Departamento relacionado <small style="font-weight:400;color:#5a7280">(opcional)</small></label>
                    <select class="etica-select" id="setor" name="setor">
                        <option value="">Prefiro não indicar</option>
                        <?php foreach ($setores as $s) { ?>
                            <option value="<?= (int) $s['loc_id']; ?>"><?= htmlspecialchars($s['local'], ENT_QUOTES); ?></option>
                        <?php } ?>
                    </select>
                    <div class="etica-hint">Se souber, indique o setor a que sua manifestação se refere.</div>
                </div>
                <?php } ?>

                <div class="etica-optin">
                    <label class="etica-optin-head" for="wants_email">
                        <input type="checkbox" id="wants_email" name="wants_email" value="1">
                        <span class="txt">
                            <b>Quero informar um e-mail para retorno (opcional)</b>
                            <span>Deixando em branco, sua manifestação permanece totalmente anônima. O e-mail serve apenas para eventual contato da equipe.</span>
                        </span>
                    </label>
                    <div class="etica-optin-body" id="email_box" hidden>
                        <input class="etica-input" type="email" id="contato_email" name="contato_email" placeholder="seu-email@exemplo.com" autocomplete="off">
                    </div>
                </div>

                <div class="etica-field">
                    <label for="captcha">Confirme que você não é um robô <span class="req">*</span></label>
                    <div class="etica-captcha-row">
                        <span class="etica-captcha-img" id="img_captcha"></span>
                        <span class="etica-captcha-reload" id="reload_captcha" title="Gerar outra imagem"><i class="fas fa-sync-alt" aria-hidden="true"></i></span>
                        <input class="etica-input etica-captcha-input" type="text" id="captcha" name="captcha" placeholder="Digite os caracteres da imagem" autocomplete="off">
                    </div>
                </div>

                <input type="hidden" name="action" value="open">

                <div class="etica-actions">
                    <button type="submit" class="etica-btn etica-btn-primary" id="etica-submit">
                        <i class="fas fa-paper-plane" aria-hidden="true"></i>&nbsp; Enviar manifestação
                    </button>
                </div>
            </form>

        <?php } ?>

        </div>

        <div class="etica-foot">Maua Group · Canal de Ética e Ouvidoria · uso confidencial</div>
    </main>

    <script src="../../includes/components/jquery/jquery.js"></script>
    <script>
        $(function () {

            function generateCaptcha() {
                $.ajax({ url: './set_captcha.php', method: 'POST', dataType: 'json' })
                    .done(function (response) {
                        if (response.captcha) {
                            $('#img_captcha').html('<img src="' + response.captcha + '" alt="captcha">');
                        }
                    });
            }

            if ($('#etica-form').length === 0) { return; }

            generateCaptcha();
            $('#reload_captcha').on('click', generateCaptcha);

            /* Revela o campo de e-mail apenas quando a pessoa opta por informar. */
            $('#wants_email').on('change', function () {
                var on = $(this).is(':checked');
                $('#email_box').prop('hidden', !on);
                if (!on) { $('#contato_email').val('').removeClass('is-invalid'); }
                else { $('#contato_email').focus(); }
            });

            $('input, select, textarea').on('input change', function () { $(this).removeClass('is-invalid'); });

            $('#etica-form').on('submit', function (e) {
                e.preventDefault();
                var $btn = $('#etica-submit');
                $btn.prop('disabled', true);

                $.ajax({
                    url: './denuncia_form_process.php',
                    method: 'POST',
                    data: new FormData(this),
                    dataType: 'json',
                    cache: false,
                    processData: false,
                    contentType: false
                }).done(function (response) {
                    if (!response.success) {
                        $('#etica-result').html('<div class="etica-alert etica-alert-warning">' + response.message + '</div>');
                        $('input, select, textarea').removeClass('is-invalid');
                        if (response.field_id) { $('#' + response.field_id).addClass('is-invalid').focus(); }
                        $btn.prop('disabled', false);
                        generateCaptcha();
                        $('#captcha').val('');
                    } else {
                        renderConfirmation(response);
                    }
                }).fail(function () {
                    $('#etica-result').html('<div class="etica-alert etica-alert-danger">Não foi possível enviar sua manifestação agora. Tente novamente em instantes.</div>');
                    $btn.prop('disabled', false);
                    generateCaptcha();
                });
            });

            function renderConfirmation(r) {
                var track = '';
                if (r.tracking_uri) {
                    track = '<a class="etica-btn etica-btn-ghost" href="' + r.tracking_uri + '" target="_top"><i class="fas fa-search" aria-hidden="true"></i> Acompanhar manifestação</a>';
                }
                var html =
                    '<div class="etica-confirm">' +
                        '<div class="etica-confirm-badge"><i class="fas fa-check-circle" aria-hidden="true"></i></div>' +
                        '<h2>Manifestação registrada</h2>' +
                        '<p>Obrigado. Sua manifestação foi recebida com sigilo e será encaminhada à equipe responsável. Guarde o número de protocolo abaixo para acompanhar.</p>' +
                        '<div class="etica-protocol"><small>Protocolo</small><b>' + r.protocol + '</b></div>' +
                        '<div class="etica-actions" style="justify-content:center">' +
                            track +
                            '<a class="etica-btn etica-btn-primary" href="../../login.php" target="_top" style="flex:0 1 auto"><i class="fas fa-check" aria-hidden="true"></i> Concluir</a>' +
                        '</div>' +
                    '</div>';
                $('#etica-card').html(html);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    </script>
</body>

</html>
