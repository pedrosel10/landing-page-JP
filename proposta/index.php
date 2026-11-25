<?php
/* Proposta do cliente: /proposta/?t=CÓDIGO
   O código vem da ficha do lead no CRM. Sem código válido (ou com o link
   desativado), a página responde "não encontrada" e não revela nada.
   ?previa=1 abre a mesma proposta sem contar como leitura do cliente
   (é o link "Abrir para apresentar" da ficha). */

require __DIR__ . '/../inc/crm.php';

$token = (string) ($_GET['t'] ?? '');
$rastreio = isset($_GET['previa']) ? null : ['t' => $token, 'url' => '../api/proposta.php'];

/* Versão genérica (sem dados de cliente), com o link da tela "Proposta genérica" */
$geral = crm_proposta_geral_por_token($token);
if ($geral) {
    $dados = crm_proposta_geral_dados($geral);
    $dados['rastreio'] = $rastreio;
    crm_proposta_mostrar($dados, 'Proposta · J.P.Growth');
    exit;
}

$proposta = crm_proposta_por_token($token);
$lead = null;
if ($proposta) {
    $st = crm_db()->prepare('SELECT * FROM leads WHERE id = ?');
    $st->execute([$proposta['lead_id']]);
    $lead = $st->fetch() ?: null;
}

if (!$proposta || !$lead) {
    http_response_code(404);
    header('Content-Type: text/html; charset=utf-8');
    header('X-Robots-Tag: noindex, nofollow');
    header('Referrer-Policy: no-referrer');
    header('Cache-Control: no-store');
    ?><!doctype html>
<html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow"><title>Proposta não encontrada · J.P.Growth</title>
<style>body{margin:0;min-height:100vh;display:grid;place-items:center;background:#EEE9E1;color:#1E1412;font:16px/1.5 system-ui,sans-serif;padding:24px}main{max-width:420px}h1{font-size:28px;letter-spacing:-.03em;margin:0 0 8px}p{color:#5E514B;margin:0 0 6px}a{color:#7A2E33}</style>
</head><body><main><h1>Proposta não encontrada</h1>
<p>O link pode ter sido digitado errado ou não estar mais ativo.</p>
<p>Fale com a equipe da J.P.Growth pelo <a href="mailto:contato@pedroselestrim.com">contato@pedroselestrim.com</a> e mandamos um link novo.</p>
</main></body></html>
<?php
    exit;
}

$dados = crm_proposta_dados($proposta, $lead);
$dados['rastreio'] = $rastreio;
crm_proposta_mostrar($dados, 'Proposta · ' . $dados['empresa'] . ' · J.P.Growth');
