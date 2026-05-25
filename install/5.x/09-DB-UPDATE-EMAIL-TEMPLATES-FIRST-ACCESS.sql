UPDATE `msgconfig`
SET
    `msg_fromname` = 'Mauá Suporte',
    `msg_subject` = 'Crie sua senha de acesso',
    `msg_body` = 'Ol&aacute; %usuario%,<br />Seu acesso ao portal foi criado com sucesso.<br />Login: <strong>%login%</strong><br />Use o bot&atilde;o ou link enviado neste e-mail para criar sua senha de acesso.<br />Este link expira em %hours% horas e pode ser usado uma &uacute;nica vez.<br />Se voc&ecirc; n&atilde;o reconhece este envio, ignore esta mensagem.',
    `msg_altbody` = 'Olá %usuario%,\r\nSeu acesso ao portal foi criado com sucesso.\r\nLogin: %login%\r\nUse o botão ou link enviado neste e-mail para criar sua senha de acesso.\r\nEste link expira em %hours% horas e pode ser usado uma única vez.\r\nSe você não reconhece este envio, ignore esta mensagem.'
WHERE `msg_event` = 'cadastro-usuario-from-admin';

UPDATE `msgconfig`
SET
    `msg_fromname` = 'Mauá Suporte',
    `msg_subject` = 'Redefina sua senha de acesso',
    `msg_body` = '<p>Ol&aacute; <strong>%usuario%</strong>,</p><p>Recebemos uma solicita&ccedil;&atilde;o para redefinir sua senha de acesso.</p><p>Use o bot&atilde;o ou link enviado neste e-mail para criar uma nova senha.</p><p>Este link expira em %hours% horas e pode ser usado uma &uacute;nica vez.</p><p>Se voc&ecirc; n&atilde;o solicitou essa a&ccedil;&atilde;o, ignore esta mensagem.</p>',
    `msg_altbody` = 'Olá %usuario%,\r\n\r\nRecebemos uma solicitação para redefinir sua senha de acesso.\r\n\r\nUse o botão ou link enviado neste e-mail para criar uma nova senha.\r\n\r\nEste link expira em %hours% horas e pode ser usado uma única vez.\r\n\r\nSe você não solicitou essa ação, ignore esta mensagem.'
WHERE `msg_event` = 'forget-password';
