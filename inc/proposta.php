<?php
/* Proposta comercial de cada lead.
   A página /proposta/?t=CÓDIGO monta a proposta com os dados da ficha do lead.
   O código é aleatório (128 bits): só quem recebe o link abre a página, e ele
   pode ser trocado ou desativado na ficha a qualquer momento. Nenhum dado de
   cliente fica no código do site (que é público no GitHub). */

if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    http_response_code(404);
    exit;
}

/* Cases que a página sabe mostrar (precisam bater com a lista CASOS do modelo) */
const CRM_PROPOSTA_CASOS = [
    'captacao'        => 'Educação · captação de leads (1.606 leads em 25 dias)',
    'educacao-pos'    => 'Educação · pós-graduação (R$ 1,4 mi em 25 dias)',
    'educacao-online' => 'Educação · vendas online (529 vendas, ROAS 18,26)',
    'ecommerce'       => 'E-commerce · livros (R$ 5,8 mil viraram R$ 97 mil)',
    'industria'       => 'Indústria · B2B no Google (2.750 conversões)',
    'demanda'         => 'Google Ads · geração de demanda (9.234 conversões)',
];

/* Case sugerido pelo segmento do lead (os demais começam pelo de captação) */
const CRM_PROPOSTA_CASO_SEGMENTO = [
    'Educação'                  => 'educacao-pos',
    'E-commerce'                => 'ecommerce',
    'Loja / varejo'             => 'ecommerce',
    'Empresa de serviços'       => 'industria',
    'Restaurante / alimentação' => 'demanda',
];

/* Frase "Você comentou que ..." sugerida a partir do desafio do formulário */
const CRM_PROPOSTA_DESAFIO_FALA = [
    'poucos-leads'             => 'não está chegando cliente suficiente',
    'leads-nao-convertem'      => 'os leads chegam, mas as vendas não acompanham',
    'cac-alto'                 => 'o custo por cliente está alto demais para crescer com lucro',
    'lucro-incerto'            => 'não dá para saber se os anúncios trazem lucro de verdade',
    'sem-clareza'              => 'não está claro de onde vêm os resultados',
    'resultado-oscila'         => 'o resultado varia demais de um mês para o outro',
    'agencia-sem-resultado'    => 'a agência ou o gestor atual não está entregando',
    'estruturar-marketing'     => 'o marketing precisa ser estruturado do zero',
    'trafego-caro'             => 'o tráfego está caro',
    'anuncios-sem-performance' => 'os anúncios não estão performando',
];

/* Seções da página, na ordem. O rastreio guarda até qual delas o cliente chegou. */
const CRM_PROPOSTA_SECOES = [
    'inicio' => 'Capa', 'manifesto' => 'Manifesto', 'quem' => 'Quem somos', 'valores' => 'Visão e valores',
    'cenario' => 'O seu cenário', 'problema' => 'O problema', 'pedacos' => 'Em pedaços', 'sistema' => 'O sistema',
    'frentes' => '8 frentes', 'diferenca' => 'A diferença', 'prova' => 'Prova', 'marca' => 'Branding',
    'socios' => 'Quem conduz', 'noventa' => 'Os primeiros 90 dias', 'combinado' => 'O combinado',
    'investimento' => 'Investimento', 'passos' => 'Próximos passos', 'fim' => 'Encerramento',
];

const CRM_PROPOSTA_MESES = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho',
    'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];

function crm_proposta_token(): string
{
    return bin2hex(random_bytes(16));
}

function crm_proposta_do_lead(int $leadId): ?array
{
    $st = crm_db()->prepare('SELECT * FROM propostas WHERE lead_id = ?');
    $st->execute([$leadId]);
    $p = $st->fetch();
    return $p ?: null;
}

/* Só devolve proposta ativa e com código no formato certo */
function crm_proposta_por_token(string $token): ?array
{
    if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
        return null;
    }
    $st = crm_db()->prepare('SELECT * FROM propostas WHERE token = ? AND ativa = 1');
    $st->execute([$token]);
    $p = $st->fetch();
    return $p ?: null;
}

/* Sugestão para o formulário de um lead que ainda não tem proposta */
function crm_proposta_sugestao(array $lead): array
{
    return [
        'obs1' => '', 'obs2' => '', 'obs3' => '',
        'desafio_curto'  => CRM_PROPOSTA_DESAFIO_FALA[$lead['desafio']] ?? '',
        'caso'           => CRM_PROPOSTA_CASO_SEGMENTO[$lead['segmento']] ?? 'captacao',
        'assessoria'     => 3000,
        'verba_minima'   => 3000,
        'prazo_contrato' => '5 dias úteis',
    ];
}

/* Campos do formulário da ficha, limpos e com limite (só estas colunas são gravadas) */
function crm_proposta_campos(array $post): array
{
    $reais = function ($valor, int $padrao): int {
        $n = (int) round(crm_valor_br((string) $valor));
        return ($n >= 100 && $n <= 1000000) ? $n : $padrao;
    };
    $caso = (string) ($post['caso'] ?? '');
    return [
        'obs1'           => crm_texto($post['obs1'] ?? '', 220),
        'obs2'           => crm_texto($post['obs2'] ?? '', 220),
        'obs3'           => crm_texto($post['obs3'] ?? '', 220),
        'desafio_curto'  => rtrim(crm_texto($post['desafio_curto'] ?? '', 140), '. '),
        'caso'           => isset(CRM_PROPOSTA_CASOS[$caso]) ? $caso : 'captacao',
        'assessoria'     => $reais($post['assessoria'] ?? '', 3000),
        'verba_minima'   => $reais($post['verba_minima'] ?? '', 3000),
        'prazo_contrato' => crm_texto($post['prazo_contrato'] ?? '', 40) ?: '5 dias úteis',
    ];
}

/* Cria ou atualiza a proposta do lead (o link continua o mesmo ao atualizar) */
function crm_proposta_salvar(int $leadId, array $campos, string $autor): void
{
    $db = crm_db();
    $agora = crm_agora();
    $atual = crm_proposta_do_lead($leadId);
    if ($atual) {
        $sets = [];
        $args = [];
        foreach ($campos as $coluna => $valor) {
            $sets[] = "$coluna = ?";
            $args[] = $valor;
        }
        $sets[] = 'atualizado_em = ?';
        $args[] = $agora;
        $args[] = $atual['id'];
        $db->prepare('UPDATE propostas SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($args);
        crm_nota($leadId, $autor, 'Proposta atualizada (o link continua o mesmo).');
        return;
    }
    $campos += [
        'lead_id' => $leadId, 'token' => crm_proposta_token(), 'ativa' => 1,
        'criado_em' => $agora, 'atualizado_em' => $agora, 'criado_por' => $autor,
    ];
    $colunas = array_keys($campos);
    $db->prepare('INSERT INTO propostas (' . implode(', ', $colunas) . ') VALUES ('
        . implode(', ', array_fill(0, count($colunas), '?')) . ')')->execute(array_values($campos));
    crm_nota($leadId, $autor, 'Proposta gerada.');
}

/* Troca o código: o link antigo para de funcionar na hora */
function crm_proposta_novo_link(int $leadId, string $autor): void
{
    crm_db()->prepare('UPDATE propostas SET token = ?, ativa = 1, atualizado_em = ? WHERE lead_id = ?')
        ->execute([crm_proposta_token(), crm_agora(), $leadId]);
    crm_nota($leadId, $autor, 'Link da proposta trocado. O link antigo não abre mais.');
}

function crm_proposta_ativar(int $leadId, bool $ativa, string $autor): void
{
    crm_db()->prepare('UPDATE propostas SET ativa = ?, atualizado_em = ? WHERE lead_id = ?')
        ->execute([$ativa ? 1 : 0, crm_agora(), $leadId]);
    crm_nota($leadId, $autor, $ativa ? 'Link da proposta reativado.' : 'Link da proposta desativado.');
}

/* Endereço público da proposta (o painel fica em /jp/admin, a proposta em /jp/proposta) */
function crm_proposta_url(string $token, bool $previa = false): string
{
    $admin = rtrim(crm_caminho_admin(), '/');
    $base = rtrim(str_replace('\\', '/', dirname($admin)), '/');
    $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
    if (!preg_match('/^[a-z0-9.\-]+(:\d+)?$/i', $host)) {
        $host = 'localhost';
    }
    return (crm_https() ? 'https://' : 'http://') . $host . $base . '/proposta/?t=' . $token . ($previa ? '&previa=1' : '');
}

/* "2026-11-24 15:00:00" → "24 de novembro de 2026" (sem data: hoje) */
function crm_data_extenso(?string $data): string
{
    $tz = new DateTimeZone('America/Sao_Paulo');
    $d = $data ? DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data, $tz) : false;
    if (!$d) {
        $d = new DateTimeImmutable('now', $tz);
    }
    return (int) $d->format('j') . ' de ' . CRM_PROPOSTA_MESES[(int) $d->format('n') - 1] . ' de ' . $d->format('Y');
}

/* Dados que a página recebe. Só o necessário: nada de telefone, e-mail ou origem. */
function crm_proposta_dados(array $p, array $lead): array
{
    $partes = preg_split('/\s+/u', trim((string) $lead['nome'])) ?: [''];
    $faturamento = CRM_FATURAMENTO[$lead['faturamento']] ?? '';
    $verba = CRM_INVESTIMENTO[$lead['investimento']] ?? '';
    return [
        'exemplo'        => false,
        'nome'           => $partes[0],
        'empresa'        => (string) $lead['empresa'],
        'segmento'       => $lead['segmento'] !== '' ? (string) $lead['segmento'] : 'Não informado',
        'faturamento'    => $faturamento !== '' ? preg_replace('/ por mês$/u', '', $faturamento) : 'Não informado',
        'verba_atual'    => $verba !== '' ? preg_replace('/ em anúncios$/u', '', $verba) : 'Não informado',
        'atendimento'    => CRM_ATENDIMENTO[$lead['atendimento']] ?? 'Não informado',
        'desafio'        => CRM_DESAFIO[$lead['desafio']] ?? 'Não informado',
        'desafio_curto'  => (string) $p['desafio_curto'],
        'obs'            => array_values(array_filter([(string) $p['obs1'], (string) $p['obs2'], (string) $p['obs3']], 'strlen')),
        'caso'           => (string) $p['caso'],
        'data'           => crm_data_extenso($lead['reuniao_em'] ?: $p['criado_em']),
        'prazo_contrato' => (string) $p['prazo_contrato'],
        'assessoria'     => (int) $p['assessoria'],
        'verba_minima'   => (int) $p['verba_minima'],
        'whatsapp'       => CRM_PROPOSTA_WHATSAPP,
    ];
}

/* JSON que pode ir direto dentro de <script> sem risco de fechar a tag */
function crm_proposta_json(array $dados): string
{
    return (string) json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

/* Cabeçalhos da página pública: fora do Google, sem vazar o link e só o script dela */
function crm_proposta_cabecalhos(string $nonce): void
{
    header('Content-Type: text/html; charset=utf-8');
    header('X-Robots-Tag: noindex, nofollow');
    header('Referrer-Policy: no-referrer');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Cache-Control: no-store, max-age=0');
    header("Content-Security-Policy: default-src 'self'; script-src 'nonce-{$nonce}'; "
        . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; "
        . "img-src 'self' data:; connect-src 'self'; form-action 'none'; frame-ancestors 'none'; "
        . "base-uri 'none'; object-src 'none'");
    if (crm_https()) {
        header('Strict-Transport-Security: max-age=31536000');
    }
}

/* Mostra a página com os dados informados */
function crm_proposta_mostrar(array $dados, string $titulo): void
{
    $nonce = base64_encode(random_bytes(16));
    crm_proposta_cabecalhos($nonce);
    if (!defined('CRM_PROPOSTA_MODELO')) {
        define('CRM_PROPOSTA_MODELO', true);
    }
    $propostaJson = crm_proposta_json($dados);
    $paginaTitulo = $titulo;
    $paginaDescricao = 'Proposta de operação de marketing preparada para ' . $dados['empresa'] . '.';
    require __DIR__ . '/proposta-modelo.php';
}

/* Registra o que a página mandou: "abriu" (1x por aba) ou "leu" (seção e tempo) */
function crm_proposta_registrar(array $p, string $evento, string $secao, int $segundos): void
{
    $indice = array_search($secao, array_keys(CRM_PROPOSTA_SECOES), true);
    $indice = $indice === false ? 0 : (int) $indice;
    $agora = crm_agora();
    $db = crm_db();
    if ($evento === 'abriu') {
        $db->prepare('UPDATE propostas SET visitas = visitas + 1, primeira_visita = COALESCE(primeira_visita, ?),
            ultima_visita = ?, secao_max = MAX(secao_max, ?) WHERE id = ?')
            ->execute([$agora, $agora, $indice, $p['id']]);
        if (empty($p['primeira_visita'])) {
            crm_nota((int) $p['lead_id'], 'sistema', 'O cliente abriu a proposta pela primeira vez.');
        }
        return;
    }
    $db->prepare('UPDATE propostas SET secao_max = MAX(secao_max, ?), tempo_total = tempo_total + ?, ultima_visita = ? WHERE id = ?')
        ->execute([$indice, max(0, min($segundos, 3600)), $agora, $p['id']]);
}

/* "Leu até Investimento (16 de 18)" */
function crm_proposta_leitura(array $p): string
{
    $nomes = array_values(CRM_PROPOSTA_SECOES);
    $i = max(0, min((int) $p['secao_max'], count($nomes) - 1));
    return 'Leu até ' . $nomes[$i] . ' (' . ($i + 1) . ' de ' . count($nomes) . ')';
}

function crm_proposta_tempo(int $segundos): string
{
    if ($segundos < 60) {
        return 'menos de 1 min';
    }
    $min = (int) round($segundos / 60);
    return $min < 60 ? $min . ' min' : floor($min / 60) . ' h ' . ($min % 60) . ' min';
}
