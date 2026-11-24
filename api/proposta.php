<?php
/* Rastreio de leitura da proposta. A página manda (via sendBeacon):
     {"t": código, "ev": "abriu" | "leu", "secao": id da seção, "seg": segundos na tela}
   e o CRM guarda quantas vezes abriu, até onde leu e por quanto tempo.
   Não guarda IP nem nada que identifique o aparelho. */

define('CRM_MODO_API', true);
require __DIR__ . '/../inc/crm.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Robots-Tag: noindex');
header('Cache-Control: no-store');

function responder(int $codigo): void
{
    http_response_code($codigo);
    echo json_encode(['ok' => $codigo < 300]);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    responder(405);
}

/* Só aceita avisos vindos do próprio site */
$origem = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origem !== '' && parse_url($origem, PHP_URL_HOST) !== parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST)) {
    responder(403);
}

$bruto = file_get_contents('php://input', false, null, 0, 2001);
if ($bruto === false || strlen($bruto) > 2000) {
    responder(413);
}
$dados = json_decode($bruto, true);
if (!is_array($dados)) {
    responder(400);
}

/* Limite generoso por IP: uma leitura normal manda poucos avisos */
$ip = crm_chave_ip();
if (crm_contar_tentativas('proposta', $ip, 3600) >= 120) {
    responder(429);
}
crm_registrar_tentativa('proposta', $ip);

$proposta = crm_proposta_por_token((string) ($dados['t'] ?? ''));
if (!$proposta) {
    responder(404);
}
$evento = ($dados['ev'] ?? '') === 'abriu' ? 'abriu' : 'leu';
$secao = (string) ($dados['secao'] ?? '');
$segundos = is_numeric($dados['seg'] ?? null) ? (int) $dados['seg'] : 0;

crm_proposta_registrar($proposta, $evento, $secao, $segundos);
responder(200);
