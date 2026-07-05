<?php session_start();
/*
    Canal de Ética e Ouvidoria — formulário público (sem login).
    Registra a manifestação como chamado sob o usuário "Anônimo", na área
    confidencial "Canal de Ética". Parte pública apenas.
    Anti-spam sem captcha: honeypot + tempo mínimo de preenchimento + rate-limit
    de sessão (não exclui ninguém — acessível por padrão).
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

/* Marca o instante de renderização — usado no processo p/ rejeitar envios
   instantâneos (bots). Envio humano leva alguns segundos. */
if ($canalAtivo) {
    $_SESSION['etica_form_ts'] = time();
}

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
                <div class="etica-confirm-badge is-warn">
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
                <input type="hidden" name="action" value="open">

                <!-- Honeypot: invisível para pessoas (inclusive leitores de tela); bots preenchem. -->
                <div class="etica-hp" aria-hidden="true">
                    <label for="confirm_url">Não preencha este campo</label>
                    <input type="text" id="confirm_url" name="confirm_url" tabindex="-1" autocomplete="off">
                </div>

                <!-- Região de erro/status, anunciada a leitores de tela. -->
                <div id="etica-result" role="alert" aria-live="assertive"></div>

                <div class="etica-field">
                    <label for="tipo">Tipo de manifestação <span class="req">*</span></label>
                    <select class="etica-select" id="tipo" name="tipo" required>
                        <option value="" selected disabled>Selecione o tipo…</option>
                        <?php foreach ($tiposManifestacao as $tipo) { ?>
                            <option value="<?= htmlspecialchars($tipo, ENT_QUOTES); ?>"><?= htmlspecialchars($tipo, ENT_QUOTES); ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="etica-field">
                    <label for="assunto">Assunto <small class="etica-optional">(opcional)</small></label>
                    <input class="etica-input" type="text" id="assunto" name="assunto" maxlength="150" placeholder="Um resumo curto da sua manifestação">
                </div>

                <div class="etica-field">
                    <label for="descricao">Descrição <span class="req">*</span></label>
                    <textarea class="etica-textarea" id="descricao" name="descricao" maxlength="5000" rows="6"
                        placeholder="Conte o que aconteceu com suas palavras."
                        aria-describedby="descricao_hint"></textarea>
                    <div class="etica-field-foot">
                        <div class="etica-hint" id="descricao_hint">Quando possível, descreva <strong>o que</strong> aconteceu, <strong>quando</strong>, <strong>onde</strong> e <strong>quem</strong> esteve envolvido. Você não é obrigado a se identificar.</div>
                        <div class="etica-counter"><span id="descricao_count">0</span>/5000</div>
                    </div>
                </div>

                <?php if (!empty($setores)) { ?>
                <div class="etica-field">
                    <label for="setor">Setor / Departamento relacionado <small class="etica-optional">(opcional)</small></label>
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

                <p class="etica-reassure"><i class="fas fa-lock" aria-hidden="true"></i> Sua manifestação é registrada com sigilo. Você não precisa se identificar.</p>

                <div class="etica-actions">
                    <button type="submit" class="etica-btn etica-btn-primary" id="etica-submit">
                        <i class="fas fa-paper-plane" aria-hidden="true"></i>&nbsp;<span class="etica-submit-label">Enviar manifestação</span>
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
            var $form = $('#etica-form');
            if ($form.length === 0) { return; }

            /* Contador de caracteres da descrição */
            var $desc = $('#descricao');
            var $count = $('#descricao_count');
            function updateCount() { $count.text($desc.val().length); }
            $desc.on('input', updateCount);
            updateCount();

            /* Revela o campo de e-mail apenas quando a pessoa opta por informar. */
            $('#wants_email').on('change', function () {
                var on = $(this).is(':checked');
                $('#email_box').prop('hidden', !on);
                if (!on) { $('#contato_email').val('').removeClass('is-invalid'); }
                else { $('#contato_email').focus(); }
            });

            $('input, select, textarea').on('input change', function () {
                $(this).removeClass('is-invalid').removeAttr('aria-invalid');
            });

            function showAlert(kind, message) {
                var $al = $('<div class="etica-alert"></div>').addClass('etica-alert-' + kind).text(message);
                $('#etica-result').empty().append($al);
            }

            $form.on('submit', function (e) {
                e.preventDefault();
                var $btn = $('#etica-submit');
                var $label = $('.etica-submit-label');
                var labelText = $label.text();
                $btn.prop('disabled', true);
                $label.text('Enviando…');

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
                        showAlert('warning', response.message || 'Verifique os campos e tente novamente.');
                        $('input, select, textarea').removeClass('is-invalid').removeAttr('aria-invalid');
                        if (response.field_id) {
                            $('#' + response.field_id).addClass('is-invalid').attr('aria-invalid', 'true').focus();
                        }
                        $btn.prop('disabled', false);
                        $label.text(labelText);
                    } else {
                        renderConfirmation(response);
                    }
                }).fail(function () {
                    showAlert('danger', 'Não foi possível enviar sua manifestação agora. Tente novamente em instantes.');
                    $btn.prop('disabled', false);
                    $label.text(labelText);
                });
            });

            function renderConfirmation(r) {
                var track = '';
                if (r.tracking_uri) {
                    track = '<a class="etica-btn etica-btn-ghost" href="' + r.tracking_uri + '" target="_top"><i class="fas fa-search" aria-hidden="true"></i> Acompanhar manifestação</a>';
                }
                var html =
                    '<div class="etica-confirm" role="status">' +
                        '<div class="etica-confirm-badge"><i class="fas fa-check-circle" aria-hidden="true"></i></div>' +
                        '<h2 id="etica-confirm-title" tabindex="-1">Manifestação registrada</h2>' +
                        '<p>Recebemos sua manifestação com sigilo. A equipe responsável vai analisá-la; se você informou um e-mail, o retorno virá por lá.</p>' +
                        '<div class="etica-protocol">' +
                            '<small>Protocolo</small>' +
                            '<b id="etica-protocol-num">' + r.protocol + '</b>' +
                            '<button type="button" class="etica-copy" id="etica-copy" data-protocol="' + r.protocol + '">' +
                                '<i class="fas fa-copy" aria-hidden="true"></i> <span class="etica-copy-label">Copiar protocolo</span>' +
                            '</button>' +
                        '</div>' +
                        '<p class="etica-protocol-note"><i class="fas fa-exclamation-circle" aria-hidden="true"></i> Guarde este número. Se você não informou e-mail, ele é a <strong>única</strong> forma de acompanhar de forma anônima.</p>' +
                        '<div class="etica-actions etica-actions-center">' +
                            track +
                            '<a class="etica-btn etica-btn-primary etica-btn-auto" href="../../login.php" target="_top"><i class="fas fa-check" aria-hidden="true"></i> Concluir</a>' +
                        '</div>' +
                    '</div>';
                $('#etica-card').html(html);
                window.scrollTo({ top: 0, behavior: 'smooth' });

                /* Anuncia e leva o foco ao título da confirmação (leitores de tela). */
                var titleEl = document.getElementById('etica-confirm-title');
                if (titleEl) { titleEl.focus(); }

                /* Copiar protocolo */
                $('#etica-copy').on('click', function () {
                    var num = $(this).data('protocol');
                    var $b = $(this);
                    var done = function () {
                        $b.addClass('is-copied');
                        $b.find('.etica-copy-label').text('Copiado!');
                        setTimeout(function () {
                            $b.removeClass('is-copied');
                            $b.find('.etica-copy-label').text('Copiar protocolo');
                        }, 2200);
                    };
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(String(num)).then(done, function () { fallbackCopy(String(num), done); });
                    } else {
                        fallbackCopy(String(num), done);
                    }
                });

                function fallbackCopy(text, cb) {
                    var t = document.createElement('textarea');
                    t.value = text; t.setAttribute('readonly', ''); t.style.position = 'absolute'; t.style.left = '-9999px';
                    document.body.appendChild(t); t.select();
                    try { document.execCommand('copy'); cb(); } catch (err) { /* silencioso */ }
                    document.body.removeChild(t);
                }
            }
        });
    </script>
</body>

</html>
