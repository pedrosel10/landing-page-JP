<?php
/* Arquivo de conversões off-line para o Google Ads (importação programada por HTTPS).
   O Google Ads busca este endereço todo dia. Ele só responde com a chave secreta
   gerada em Integrações, e não traz nenhum dado pessoal: só o GCLID do clique,
   o nome da conversão, a data e o valor. */

define('CRM_MODO_API', true);
require __DIR__ . '/../inc/crm.php';

header('X-Robots-Tag: noindex');
header('Cache-Control: no-store');

if (!crm_google_chave_valida((string) ($_GET['chave'] ?? ''))) {
    http_response_code(404);
    exit;
}

/* evento => [coluna com a data, chave da config com o nome da conversão] */
$mapa = [
    'mql'      => ['mql_em', 'google_conv_mql'],
    'sql'      => ['sql_em', 'google_conv_sql'],
    'agendada' => ['agendada_em', 'google_conv_agendada'],
    'ganho'    => ['fechado_em', 'google_conv_ganho'],
];
$desde = date('Y-m-d H:i:s', time() - 30 * 86400);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: inline; filename="conversoes-crm.csv"');
$saida = fopen('php://output', 'w');
fwrite($saida, "Parameters:TimeZone=America/Sao_Paulo\n");
/* Linhas no formato do modelo do Google: aspas só quando o valor tiver vírgula ou aspas */
function linha_csv($saida, array $campos): void
{
    foreach ($campos as $i => $campo) {
        $campo = str_replace(["\r", "\n"], ' ', (string) $campo);
        $campos[$i] = strpbrk($campo, ',"') !== false ? '"' . str_replace('"', '""', $campo) . '"' : $campo;
    }
    fwrite($saida, implode(',', $campos) . "\n");
}
linha_csv($saida, ['Google Click ID', 'Conversion Name', 'Conversion Time', 'Conversion Value', 'Conversion Currency']);

foreach ($mapa as $evento => $info) {
    $nome = crm_config($info[1]);
    if ($nome === '') {
        continue;
    }
    $coluna = $info[0];
    $sql = "SELECT gclid, $coluna AS quando, valor_mensal FROM leads WHERE gclid <> '' AND $coluna IS NOT NULL AND $coluna >= ?";
    if ($evento === 'ganho') {
        $sql .= " AND status = 'ganho'";
    }
    $st = crm_db()->prepare($sql);
    $st->execute([$desde]);
    while ($linha = $st->fetch()) {
        $valor = $evento === 'ganho' && (float) $linha['valor_mensal'] > 0 ? number_format((float) $linha['valor_mensal'], 2, '.', '') : '';
        linha_csv($saida, [$linha['gclid'], $nome, $linha['quando'], $valor, $valor !== '' ? 'BRL' : '']);
    }
}
fclose($saida);
