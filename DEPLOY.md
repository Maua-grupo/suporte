# Deploy automático via FTP

O workflow `.github/workflows/deploy.yml` valida os arquivos PHP e publica a
aplicação sempre que houver um `push` na branch `main`. Também é possível
executá-lo manualmente pela aba **Actions**.

O lint usa PHP 7.4 para validar o código da aplicação e ignora diretórios
`vendor`. O projeto contém dependências distintas para PHP 7.4 e PHP 8.1+, e
seleciona a versão compatível durante a execução.

## Configuração no GitHub

Crie o environment `production` em **Settings > Environments**. Dentro dele,
cadastre estes secrets:

- `FTP_SERVER`: domínio do servidor, sem `ftp://`;
- `FTP_USERNAME`: usuário FTP;
- `FTP_PASSWORD`: senha FTP;
- `FTP_SERVER_DIR`: diretório remoto, sempre terminado em `/`, por exemplo
  `/public_html/suporte/`;
- `FTP_PROTOCOL`: use `ftps` (recomendado), `ftps-legacy` ou `ftp`;
- `FTP_PORT`: normalmente `21`.

O usuário FTP deve ter acesso restrito ao diretório da aplicação. O diretório
informado em `FTP_SERVER_DIR` deve ser exclusivo deste projeto.

## Arquivos preservados no servidor

O deploy não envia nem remove:

- `.env`;
- `includes/config.inc.php`;
- arquivos de log `includes/logs/*.txt`;
- arquivos de Docker, metadados do Git/GitHub e `Archive.zip`.

Esses arquivos de configuração devem ser criados diretamente no servidor.
O mecanismo incremental mantém um arquivo `.ftp-deploy-sync-state.json` no
servidor para identificar o que mudou entre deploys; não o apague.

## Primeiro deploy

1. Faça backup dos arquivos e do banco de dados do servidor.
2. Confirme que `.env` e `includes/config.inc.php` existem no servidor.
3. Faça um push para `main` ou execute o workflow manualmente.
4. Confira o resultado em **Actions > Deploy para producao**.

Se a hospedagem não oferece FTPS, altere `FTP_PROTOCOL` para `ftp`. FTP simples
transmite a credencial sem criptografia e deve ser usado somente quando não
existir uma opção segura.
