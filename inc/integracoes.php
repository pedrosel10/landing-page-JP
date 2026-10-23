<?php
/* Integrações do CRM com as plataformas de anúncio.

   META (API de Conversões): o CRM envia eventos do funil para o Pixel/conjunto de
   dados da Meta, com e-mail e telefone criptografados (SHA-256) e o clique do
   anúncio (fbc). Assim o algoritmo aprende quais leads viram SQL, reunião e venda.
     Lead      → no envio do formulário (origem: site)
     MQL / SQL → quando o lead é qualificado (origem: CRM)
     Schedule  → quando a reunião é agendada (origem: CRM)
     Purchase  → quando a venda é realizada, com o valor mensal (origem: CRM)
   Os eventos ficam numa fila (tabela envios) e são reenviados se a Meta falhar.

   GOOGLE ADS (importação de conversões off-line): o Google Ads busca todo dia um
   CSV com o GCLID dos leads e as conversões do funil (api/google-conversoes.php).

   Tokens e chaves ficam na tabela config do banco (fora do site e do GitHub). */

if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    http_response_code(404);
    exit;
}

const CRM_EVENTOS = [
    'lead'     => ['meta' => 'Lead',     'rotulo' => 'Formulário enviado'],
    'mql'      => ['meta' => 'MQL',      'rotulo' => 'Virou MQL'],
    'sql'      => ['meta' => 'SQL',      'rotulo' => 'Virou SQL'],
    'agendada' => ['meta' => 'Schedule', 'rotulo' => 'Reunião agendada'],
    'ganho'    => ['meta' => 'Purchase', 'rotulo' => 'Venda realizada'],
];

const CRM_META_VERSAO_PADRAO = 'v23.0';

/* ------------------------------------------------------------- config */

function crm_config(string $chave, string $padrao = ''): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (crm_db()->query('SELECT chave, valor FROM config') as $linha) {
            $cache[$linha['chave']] = (string) $linha['valor'];
        }
    }
    if (func_num_args() === 3) { // reset interno após gravar
        $cache = null;
        return '';
    }
    return array_key_exists($chave, $cache) ? $cache[$chave] : $padrao;
}

function crm_config_salvar(array $valores): void
{
    $st = crm_db()->prepare('INSERT OR REPLACE INTO config (chave, valor) VALUES (?, ?)');
    foreach ($valores as $chave => $valor) {
        $st->execute([$chave, (string) $valor]);
    }
    crm_config('', '', true);
}

function crm_meta_pronta(): bool
{
    return crm_config('meta_ativo') === '1' && crm_config('meta_pixel') !== '' && crm_config('meta_token') !== '';
}

/* ---------------------------------------------------------------- fila */

/* Coloca o evento na fila da Meta (uma vez por lead e evento) */
function crm_enfileirar(int $leadId, string $evento): void
{
    if (!isset(CRM_EVENTOS[$evento]) || !crm_meta_pronta() || crm_config('meta_ev_' . $evento, '1') !== '1') {
        return;
    }
    crm_db()->prepare("INSERT OR IGNORE INTO envios (lead_id, plataforma, evento, event_id, status, tentativas, resposta, criado_em)
        VALUES (?, 'meta', ?, ?, 'pendente', 0, '', ?)")
        ->execute([$leadId, $evento, 'jpcrm-' . $leadId . '-' . $evento, crm_agora()]);
}

/* Envia o que estiver pendente (e tenta de novo os erros, até 5 vezes, a cada 15 min) */
function crm_processar_envios(int $limite = 5): void
{
    if (!crm_meta_pronta()) {
        return;
    }
    $db = crm_db();
    $st = $db->prepare("SELECT * FROM envios WHERE plataforma = 'meta'
        AND (status = 'pendente' OR (status = 'erro' AND tentativas < 5 AND (ultima_tentativa IS NULL OR ultima_tentativa < ?)))
        ORDER BY id LIMIT " . (int) $limite);
    $st->execute([date('Y-m-d H:i:s', time() - 900)]);
    foreach ($st->fetchAll() as $envio) {
        crm_meta_enviar($envio);
    }
}

function crm_meta_enviar(array $envio): bool
{
    $db = crm_db();
    $st = $db->prepare('SELECT * FROM leads WHERE id = ?');
    $st->execute([$envio['lead_id']]);
    $lead = $st->fetch();
    if (!$lead) {
        $db->prepare("UPDATE envios SET status = 'cancelado', resposta = 'Lead excluído' WHERE id = ?")->execute([$envio['id']]);
        return false;
    }

    $corpo = crm_meta_payload($lead, $envio['evento'], $envio['event_id']);
    $url = crm_meta_base() . '/' . rawurlencode(crm_config('meta_versao', CRM_META_VERSAO_PADRAO)) . '/'
        . rawurlencode(crm_config('meta_pixel')) . '/events';
    $corpo['access_token'] = crm_config('meta_token');
    list($codigo, $resposta) = crm_http_post_json($url, $corpo, 6);

    $json = json_decode((string) $resposta, true);
    $ok = $codigo === 200 && is_array($json) && (int) ($json['events_received'] ?? 0) >= 1;
    $resumo = $ok ? 'Recebido pela Meta' : ('HTTP ' . $codigo . ': ' . (string) ($json['error']['message'] ?? substr((string) $resposta, 0, 300)));
    $db->prepare('UPDATE envios SET status = ?, tentativas = tentativas + 1, resposta = ?, ultima_tentativa = ?, enviado_em = ? WHERE id = ?')
        ->execute([$ok ? 'enviado' : 'erro', mb_substr($resumo, 0, 500), crm_agora(), $ok ? crm_agora() : null, $envio['id']]);
    return $ok;
}

/* Endereço da API da Meta (só muda em testes locais, direto no banco) */
function crm_meta_base(): string
{
    $base = crm_config('meta_base', 'https://graph.facebook.com');
    return preg_match('~^https://graph\.facebook\.com$|^http://127\.0\.0\.1:\d+$~', $base) ? $base : 'https://graph.facebook.com';
}

/* Monta o evento no formato da API de Conversões.
   E-mail, telefone, nome e país vão normalizados e com hash SHA-256;
   fbc, fbp, IP e navegador vão sem hash (exigência da Meta). */
function crm_meta_payload(array $lead, string $evento, string $eventId): array
{
    $sha = function ($valor) {
        return hash('sha256', $valor);
    };
    $ud = [];

    $email = mb_strtolower(trim((string) $lead['email']));
    if ($email !== '') {
        $ud['em'] = [$sha($email)];
    }
    $fone = preg_replace('/\D/', '', (string) $lead['whatsapp']) ?? '';
    if ($fone !== '') {
        if (strlen($fone) <= 11) {
            $fone = '55' . $fone;
        }
        $ud['ph'] = [$sha($fone)];
    }
    $nomes = preg_split('/\s+/u', trim(mb_strtolower(preg_replace('/[^\p{L}\s]/u', '', (string) $lead['nome']) ?? '')), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    if ($nomes) {
        $ud['fn'] = [$sha($nomes[0])];
        if (count($nomes) > 1) {
            $ud['ln'] = [$sha($nomes[count($nomes) - 1])];
        }
    }
    $ud['country'] = [$sha('br')];
    $ud['external_id'] = [$sha('jpgrowth-lead-' . $lead['id'])];
    if (!empty($lead['fbc'])) {
        $ud['fbc'] = $lead['fbc'];
    }
    if (!empty($lead['fbp'])) {
        $ud['fbp'] = $lead['fbp'];
    }

    $evt = [
        'event_name' => CRM_EVENTOS[$evento]['meta'],
        'event_id'   => $eventId,
    ];

    if ($evento === 'lead') {
        /* Evento do próprio formulário: origem "site", com IP e navegador do visitante */
        $criado = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $lead['criado_em'], new DateTimeZone('America/Sao_Paulo'));
        $evt['event_time'] = $criado ? max($criado->getTimestamp(), time() - 6 * 86400) : time();
        $evt['action_source'] = 'website';
        $evt['event_source_url'] = $lead['pagina'] !== '' ? $lead['pagina'] : 'https://pedroselestrim.com/jp/';
        if (!empty($lead['ip'])) {
            $ud['client_ip_address'] = $lead['ip'];
        }
        if (!empty($lead['user_agent'])) {
            $ud['client_user_agent'] = $lead['user_agent'];
        }
    } else {
        /* Eventos do funil: origem "CRM" */
        $evt['event_time'] = time();
        $evt['action_source'] = 'system_generated';
        $evt['custom_data'] = ['event_source' => 'crm', 'lead_event_source' => 'J.P.Growth CRM'];
    }

    if ($evento === 'ganho' && (float) $lead['valor_mensal'] > 0) {
        $evt['custom_data'] = array_merge($evt['custom_data'] ?? [], [
            'value'    => (float) $lead['valor_mensal'],
            'currency' => 'BRL',
        ]);
    }

    $evt['user_data'] = $ud;
    $corpo = ['data' => [$evt]];
    $teste = crm_config('meta_teste');
    if ($teste !== '') {
        $corpo['test_event_code'] = $teste;
    }
    return $corpo;
}

/* POST JSON com tempo limite. Devolve [código HTTP, corpo da resposta]. */
function crm_http_post_json(string $url, array $corpo, int $timeout): array
{
    $json = json_encode($corpo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $json,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT        => $timeout,
        ]);
        $resposta = curl_exec($ch);
        $codigo = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $erro = curl_error($ch);
        curl_close($ch);
        return [$codigo, $resposta === false ? 'Falha de conexão: ' . $erro : (string) $resposta];
    }
    $contexto = stream_context_create(['http' => [
        'method' => 'POST', 'header' => "Content-Type: application/json\r\n", 'content' => $json,
        'timeout' => $timeout, 'ignore_errors' => true,
    ]]);
    $resposta = @file_get_contents($url, false, $contexto);
    $codigo = 0;
    foreach ($http_response_header ?? [] as $cabecalho) {
        if (preg_match('~^HTTP/\S+\s+(\d{3})~', $cabecalho, $m)) {
            $codigo = (int) $m[1];
        }
    }
    return [$codigo, $resposta === false ? 'Falha de conexão' : (string) $resposta];
}

/* Encerra a resposta para o navegador e continua rodando em segundo plano
   (o visitante não espera a Meta responder) */
function crm_liberar_resposta(): bool
{
    if (function_exists('litespeed_finish_request')) {
        litespeed_finish_request();
        return true;
    }
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
        return true;
    }
    return false;
}

/* ------------------------------------------------------------- google */

function crm_google_chave_valida(string $chave): bool
{
    $hash = crm_config('google_chave_hash');
    return crm_config('google_ativo') === '1' && $hash !== '' && strlen($chave) >= 32 && hash_equals($hash, hash('sha256', $chave));
}
