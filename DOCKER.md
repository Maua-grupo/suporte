# Execucao local com Docker

Este projeto foi preparado para rodar com Apache + PHP 7.4 + MySQL via Docker Compose.

## 0) Pre-requisitos (Windows sem Docker)

1. Instalar WSL2 (PowerShell como Administrador):

```powershell
wsl --install
wsl --set-default-version 2
```

2. Reiniciar o Windows.

3. Instalar Docker Desktop e habilitar `Use the WSL 2 based engine`.

4. Validar instalacao:

```powershell
docker --version
docker compose version
```

## 1) Preparar variaveis

Copie o arquivo de exemplo e ajuste as credenciais:

```bash
cp .env.example .env
```

Edite `.env` com as credenciais desejadas para o banco local do container.

## 2) Subir ambiente

```bash
docker compose up -d --build
```

Aplicacao: http://localhost:8083
Banco local do container (para clientes externos): localhost:3307

## 3) Estrutura SQL inicial

No primeiro `docker compose up` em volume novo, a estrutura SQL e dados basicos sao importados automaticamente.

Se quiser forcar uma carga limpa novamente:

```bash
docker compose down -v
docker compose up -d --build
```

Se precisar importar manualmente em um banco ja existente:

```bash
docker exec -i ocomon_db mysql -u root -p"${MYSQL_ROOT_PASSWORD}" ${MYSQL_DATABASE} < install/5.x/01-DB_OCOMON_5.x-FRESH_INSTALL_STRUCTURE_AND_BASIC_DATA.sql
```

Se voce estiver no PowerShell, prefira executar em duas etapas:

```powershell
docker exec -i ocomon_db mysql -u root -prootpass ocomon_5 < .\install\5.x\01-DB_OCOMON_5.x-FRESH_INSTALL_STRUCTURE_AND_BASIC_DATA.sql
```

Apos importar, ajuste usuario/senha no proprio banco se necessario.

## 4) Configuracao de conexao

A aplicacao agora usa estas variaveis de ambiente para conexao:

- `MYSQL_DATABASE`
- `MYSQL_USER`
- `MYSQL_PASSWORD`
- `SQL_SERVER`
- `SQL_PORT`

Observacoes:

- `MYSQL_DATABASE`, `MYSQL_USER` e `MYSQL_PASSWORD` sao a fonte principal para nome do banco, usuario e senha.
- `SQL_DB`, `SQL_USER` e `SQL_PASSWD` continuam aceitos apenas como fallback legado.
- `SQL_SERVER` e `SQL_PORT` seguem separados porque o setup atual precisa informar host e porta do banco para a aplicacao.

Essas variaveis sao injetadas pelo `docker-compose.yml` nos servicos da aplicacao.

## 5) Encerrar ambiente

```bash
docker compose down
```

Para remover volumes de banco (apaga dados locais):

```bash
docker compose down -v
```

## Notas

- A API usa `.htaccess`, por isso o ambiente Docker usa Apache com `mod_rewrite` habilitado.
- O diretorio `api/ocomon_api/storage` precisa estar gravavel dentro do container.
- Se houver erro de permissao para escrita, execute no container web:

```bash
docker exec -it ocomon_web bash -lc "chown -R www-data:www-data /var/www/html/api/ocomon_api/storage /var/www/html/includes/logs"
```
