<?php
/* Configuração do CRM da J.P.Growth.
   ATENÇÃO: este arquivo vai para o GitHub, que é público. Nunca coloque senhas aqui.
   O login e a senha ficam só no banco de dados, com hash, fora da pasta do site. */

if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    http_response_code(404);
    exit;
}

/* Pasta dos dados (banco, chave secreta, sessões e backups), criada UM NÍVEL ACIMA
   do public_html. O deploy só copia arquivos para public_html/jp, então nada
   aqui dentro é apagado ou sobrescrito quando o CRM é atualizado. */
const CRM_PASTA_DADOS = 'jp_crm_dados';

/* Hash da chave de instalação. A chave é usada uma única vez, no primeiro acesso a
   /jp/admin, para criar o login. Depois que existe um usuário, a instalação some. */
const CRM_CHAVE_INSTALACAO_HASH = '$2y$12$2mDs/VBdgWqiuBq7RHGZs.Tpj8/6JtgR4ojFLVqunDy/Sk3py7.jG';

/* Sessão do painel */
const CRM_SESSAO_OCIOSA = 7200;    // 2 h sem uso: pede login de novo
const CRM_SESSAO_MAXIMA = 43200;   // 12 h no máximo, mesmo em uso

/* Proteção contra tentativa e erro no login */
const CRM_LOGIN_MAX_FALHAS = 5;    // falhas por IP...
const CRM_LOGIN_JANELA = 900;      // ...a cada 15 min bloqueiam aquele IP por 15 min
const CRM_LOGIN_MAX_FALHAS_USUARIO = 10; // falhas no mesmo usuário (vindas de qualquer IP)

/* Proteção do formulário contra spam */
const CRM_LEAD_MAX_POR_IP = 5;     // envios por IP por hora
const CRM_LEAD_MAX_GERAL = 300;    // envios por hora, somando todos

/* Backups automáticos do banco (1 por dia, feitos quando alguém abre o painel) */
const CRM_BACKUPS_MANTIDOS = 30;

/* E-mail que recebe aviso a cada lead novo. Vazio = desligado. */
const CRM_EMAIL_AVISO = '';
