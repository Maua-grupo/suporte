<?php

namespace OcomonApi\Support;

use OcomonApi\Support\Email;
use OcomonApi\Support\Message;

class NewUserNotification
{
    /** @var \PDO */
    private $conn;

    /** @var Message */
    private $message;

    public function __construct(\PDO $conn)
    {
        $this->conn = $conn;
        $this->message = new Message();
    }

    public function sendAdminRegistration(array $userData): bool
    {
        $config = \getConfig($this->conn);
        $mailConfig = \getMailConfig($this->conn);
        $template = \getEventMailConfig($this->conn, 'cadastro-usuario-from-admin');
        $portalUrl = \getAppUrl($config);

        if (empty($portalUrl)) {
            $this->message->error('URL do portal não configurada para o envio do e-mail.');
            return false;
        }

        if (
            empty($userData['email']) ||
            empty($userData['user_id']) ||
            empty($userData['access_token']) ||
            empty($template['msg_subject']) ||
            empty($template['msg_body'])
        ) {
            $this->message->error('Configuração insuficiente para notificar o novo usuário.');
            return false;
        }

        $firstAccessLink = \buildPasswordActionLink((int)$userData['user_id'], $userData['access_token'], $config);
        $content = \transvars($template['msg_body'], [
            '%login%' => $userData['login'],
            '%usuario%' => $userData['name'],
            '%site%' => "<a href='{$portalUrl}'>{$portalUrl}</a>",
            '%forget_link%' => "<a href='{$firstAccessLink}'>{$firstAccessLink}</a>",
            '%hours%' => '6'
        ]);

        if (strpos($content, $firstAccessLink) === false) {
            $content .= "<br /><br />Este link expira em 6 horas e pode ser usado uma única vez.";
        }

        $vars = [
            '%login%' => $userData['login'],
            '%usuario%' => $userData['name'],
            '%site%' => "<a href='{$portalUrl}'>{$portalUrl}</a>",
            '%forget_link%' => "<a href='{$firstAccessLink}'>{$firstAccessLink}</a>",
            '%hours%' => '6'
        ];

        $body = \buildAccessMailTemplate(
            \TRANS('CREATE_PASSWORD_EMAIL_HEADLINE'),
            $content,
            \TRANS('CREATE_PASSWORD'),
            $firstAccessLink,
            $portalUrl
        );

        $deliveryMethod = (!empty($mailConfig['mail_queue']) ? 'queue' : 'send');

        $mail = (new Email())->bootstrap(
            \transvars($template['msg_subject'], $vars),
            $body,
            $userData['email'],
            $userData['name']
        );

        if (!$mail->{$deliveryMethod}()) {
            $error = $mail->message()->getText();
            $this->message->error($error ?: 'Falha ao enviar o e-mail de boas-vindas.');
            return false;
        }

        return true;
    }

    public function message(): Message
    {
        return $this->message;
    }
}
