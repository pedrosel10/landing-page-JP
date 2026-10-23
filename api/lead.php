<?php
/* Recebe o formulário da landing page e grava o lead no CRM.
   Responde sempre em JSON. A página só mostra "Recebemos suas informações"
   quando volta ok=true, ou seja, quando o lead foi GRAVADO no banco. */

define('CRM_MODO_API', true);
require __DIR__ . '/../inc/crm.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Robots-Tag: noindex');
header('Cache-Control: no-store');

function responder(int $codigo, array $corpo): void
{
    http_response_code($codigo);
    echo json_encode($corpo, JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    responder(405, ['ok' => false, 'erro' => 'metodo']);
}

/* Só aceita envios feitos a partir do próprio site */
$origem = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origem !== '' && parse_url($origem, PHP_URL_HOST) !== parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST)) {
    responder(403, ['ok' => false, 'erro' => 'origem']);
}

/* Corpo pequeno: um lead não passa de alguns KB */
$bruto = file_get_contents('php://input', false, null, 0, 20001);
if ($bruto === false || strlen($bruto) > 20000) {
    responder(413, ['ok' => false, 'erro' => 'tamanho']);
}
$dados = json_decode($bruto, true);
if (!is_array($dados)) {
    $dados = $_POST;
}

/* Isca anti-robô: o campo fica escondido, só robô preenche. Finge sucesso e não grava. */
if (crm_texto($dados['website'] ?? '') !== '') {
    responder(200, ['ok' => true]);
}

/* Limite de envios (contra spam e contra alguém lotar o banco) */
$ip = crm_chave_ip();
if (crm_contar_tentativas('lead', $ip, 3600) >= CRM_LEAD_MAX_POR_IP
    || crm_contar_tentativas('lead', 'geral', 3600) >= CRM_LEAD_MAX_GERAL) {
    responder(429, ['ok' => false, 'erro' => 'limite']);
}

$lead = [
    'nome'           => crm_texto($dados['nome'] ?? '', 120),
    'whatsapp'       => crm_texto($dados['whatsapp'] ?? '', 30),
    'email'          => crm_texto($dados['email'] ?? '', 160),
    'empresa'        => crm_texto($dados['empresa'] ?? '', 160),
    'site_instagram' => crm_texto($dados['site_instagram'] ?? '', 200),
    'segmento'       => crm_texto($dados['segmento'] ?? '', 60),
    'investimento'   => crm_texto($dados['investimento'] ?? '', 20),
    'desafio'        => crm_texto($dados['desafio'] ?? '', 40),
    'objetivo'       => crm_texto($dados['objetivo'] ?? '', 2000, true),
    'variante'       => crm_texto($dados['variante_headline'] ?? '', 4),
    'pagina'         => crm_texto($dados['pagina'] ?? '', 300),
    'utm_source'     => crm_texto($dados['utm_source'] ?? '', 120),
    'utm_medium'     => crm_texto($dados['utm_medium'] ?? '', 120),
    'utm_campaign'   => crm_texto($dados['utm_campaign'] ?? '', 200),
    'utm_content'    => crm_texto($dados['utm_content'] ?? '', 200),
    'utm_term'       => crm_texto($dados['utm_term'] ?? '', 200),
];

/* Validação: mesmos campos obrigatórios do formulário */
$erros = [];
foreach (['nome', 'whatsapp', 'email', 'empresa', 'site_instagram'] as $campo) {
    if ($lead[$campo] === '') {
        $erros[] = $campo;
    }
}
$digitos = preg_replace('/\D/', '', $lead['whatsapp']) ?? '';
if (strlen($digitos) < 10 || strlen($digitos) > 13) {
    $erros[] = 'whatsapp';
}
if (!filter_var($lead['email'], FILTER_VALIDATE_EMAIL)) {
    $erros[] = 'email';
}
if (!in_array($lead['segmento'], CRM_SEGMENTOS, true)) {
    $erros[] = 'segmento';
}
if (!isset(CRM_INVESTIMENTO[$lead['investimento']])) {
    $erros[] = 'investimento';
}
if (!isset(CRM_DESAFIO[$lead['desafio']])) {
    $erros[] = 'desafio';
}
if (($dados['consentimento'] ?? '') !== 'sim') {
    $erros[] = 'consentimento';
}
if ($erros) {
    crm_registrar_tentativa('lead', $ip);
    responder(422, ['ok' => false, 'erro' => 'validacao', 'campos' => array_values(array_unique($erros))]);
}

/* O perfil é recalculado aqui: não confiamos no que veio do navegador */
$lead['perfil'] = in_array($lead['investimento'], CRM_INVESTIMENTO_COMPATIVEL, true) ? 'compativel' : 'abaixo_do_perfil';
$lead['criado_em'] = $lead['atualizado_em'] = crm_agora();

try {
    $colunas = array_keys($lead);
    $sql = 'INSERT INTO leads (' . implode(', ', $colunas) . ') VALUES (' . implode(', ', array_fill(0, count($colunas), '?')) . ')';
    crm_db()->prepare($sql)->execute(array_values($lead));
    crm_registrar_tentativa('lead', $ip);
    crm_registrar_tentativa('lead', 'geral');
} catch (Throwable $e) {
    crm_falha('Erro ao gravar lead: ' . $e->getMessage());
}

/* Aviso por e-mail (opcional). Falha no e-mail não afeta o lead já gravado. */
if (CRM_EMAIL_AVISO !== '') {
    $corpo = "Novo lead pela landing page:\n\n"
        . "Nome: {$lead['nome']}\nEmpresa: {$lead['empresa']}\nWhatsApp: {$lead['whatsapp']}\n"
        . "E-mail: {$lead['email']}\nSegmento: {$lead['segmento']}\n"
        . 'Investimento: ' . CRM_INVESTIMENTO[$lead['investimento']] . "\n"
        . 'Perfil: ' . ($lead['perfil'] === 'compativel' ? 'No perfil' : 'Abaixo do perfil') . "\n";
    @mail(CRM_EMAIL_AVISO, '=?UTF-8?B?' . base64_encode('Novo lead: ' . $lead['nome']) . '?=', $corpo,
        "Content-Type: text/plain; charset=UTF-8\r\n");
}

responder(200, ['ok' => true]);
