<?php
/* Exporta todos os leads em CSV (abre direto no Excel / Planilhas Google) */

require __DIR__ . '/../inc/crm.php';
crm_exigir_login();

$colunas = [
    'id' => 'ID', 'criado_em' => 'Recebido em', 'nome' => 'Nome', 'empresa' => 'Empresa',
    'whatsapp' => 'WhatsApp', 'email' => 'E-mail', 'site_instagram' => 'Site/Instagram',
    'segmento' => 'Segmento', 'investimento' => 'Investimento', 'perfil' => 'Perfil',
    'desafio' => 'Desafio', 'objetivo' => 'O que quer melhorar', 'status' => 'Status',
    'utm_source' => 'utm_source', 'utm_medium' => 'utm_medium', 'utm_campaign' => 'utm_campaign',
    'utm_content' => 'utm_content', 'utm_term' => 'utm_term',
];

/* Impede que uma célula vire fórmula no Excel (=, +, -, @ no começo) */
function celula($valor): string
{
    $valor = (string) $valor;
    return preg_match('/^[=+\-@\t\r]/', $valor) ? "'" . $valor : $valor;
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="leads-jpgrowth-' . date('Y-m-d') . '.csv"');

$saida = fopen('php://output', 'w');
fwrite($saida, "\xEF\xBB\xBF");
fputcsv($saida, array_values($colunas), ';', '"', '\\');

$st = crm_db()->query('SELECT ' . implode(', ', array_keys($colunas)) . ' FROM leads ORDER BY criado_em DESC, id DESC');
while ($l = $st->fetch()) {
    $l['investimento'] = CRM_INVESTIMENTO[$l['investimento']] ?? $l['investimento'];
    $l['desafio'] = CRM_DESAFIO[$l['desafio']] ?? $l['desafio'];
    $l['status'] = CRM_STATUS[$l['status']] ?? $l['status'];
    $l['perfil'] = $l['perfil'] === 'compativel' ? 'No perfil' : 'Abaixo do perfil';
    $l['criado_em'] = crm_data($l['criado_em']);
    fputcsv($saida, array_map('celula', array_values($l)), ';', '"', '\\');
}
fclose($saida);
