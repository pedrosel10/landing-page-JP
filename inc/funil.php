<?php
/* Funil de inside sales da J.P.Growth: etapas, pontuação do formulário e
   qualificação MQL/SQL. Baseado nas práticas de Pipedrive (etapas com critério
   de entrada, próxima ação, alerta de negócio parado, motivo de perda),
   Kommo/Clint (funil visual e qualificação durante o atendimento) e no
   modelo BANT adaptado para uma assessoria de marketing.
   ATENÇÃO: o servidor roda PHP 7.4 (não use funções exclusivas do PHP 8). */

if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    http_response_code(404);
    exit;
}

/* ----------------------------------------------------------------- etapas
   'parado' = dias na mesma etapa até o card ganhar alerta (0 = sem alerta) */
const CRM_ETAPAS_INFO = [
    'novo'        => ['dica' => 'Chegou pelo formulário. Meta: primeiro contato em até 5 minutos.', 'parado' => 1],
    'atendimento' => ['dica' => 'Primeiro contato feito. Qualifique: orçamento, decisor, dor e momento.', 'parado' => 3],
    'agendada'    => ['dica' => 'Reunião de diagnóstico marcada. Confirme na véspera.', 'parado' => 7],
    'realizada'   => ['dica' => 'Diagnóstico feito. Próximo passo: enviar a proposta.', 'parado' => 3],
    'proposta'    => ['dica' => 'Proposta com o cliente. Faça follow-up a cada 2 ou 3 dias.', 'parado' => 7],
    'ganho'       => ['dica' => 'Contrato fechado.', 'parado' => 0],
    'perdido'     => ['dica' => 'Não avançou. O motivo ajuda a melhorar campanhas e abordagem.', 'parado' => 0],
];

const CRM_ETAPAS_ABERTAS = ['novo', 'atendimento', 'agendada', 'realizada', 'proposta'];

const CRM_MOTIVOS_PERDA = [
    'nao-respondeu'  => 'Não respondeu',
    'sem-orcamento'  => 'Sem orçamento',
    'sem-perfil'     => 'Fora do perfil',
    'nao-compareceu' => 'Não compareceu à reunião',
    'momento'        => 'Não é o momento',
    'concorrente'    => 'Escolheu outra empresa',
    'outro'          => 'Outro motivo',
];

/* ------------------------------------------------- qualificação MQL / SQL
   Lead: entrou pelo formulário.
   MQL (Marketing Qualified Lead): o formulário indica o perfil ideal (classe A;
   nos leads de antes de 27/10/2026, A ou B).
   SQL (Sales Qualified Lead): confirmado na conversa: orçamento E decisor,
   mais pelo menos 2 dos outros 3 critérios. */
const CRM_CRITERIOS_SQL = [
    'qual_orcamento' => ['Orçamento confirmado', 'Pode pagar a partir de R$ 4 mil por mês pela assessoria, além da verba de anúncios.', true],
    'qual_decisor'   => ['Fala com quem decide', 'É dono, sócio ou quem aprova a contratação.', true],
    'qual_dor'       => ['Dor clara', 'Tem um problema concreto que a assessoria resolve.', false],
    'qual_momento'   => ['Quer começar logo', 'Pretende começar em até 60 dias.', false],
    'qual_operacao'  => ['Operação comercial pronta', 'Tem quem atenda e feche os leads que vamos gerar.', false],
];

const CRM_QUALIFICACAO = [
    'lead' => 'Lead',
    'mql'  => 'MQL',
    'sql'  => 'SQL',
];

/* -------------------------------------------- pontuação do formulário
   0 a 100 pontos, só com as respostas do formulário (perfil/fit):
   faturamento (30) + verba de anúncios (25) + encaixe no preço (15) + desafio (10)
   + segmento (8) + papel na empresa (7) + site/rede (3) + e-mail da empresa (2).
   A classe segue regras de corte, não só a soma:
   · C sempre: fatura até R$ 50 mil, diz que a assessoria não cabe no orçamento,
     ou investe até R$ 2.500 em anúncios e fatura menos de R$ 100 mil.
   · A: fatura R$ 100 mil+, investe R$ 2.500+ em anúncios e quer começar agora.
   · O resto: B com 45 pontos ou mais, C abaixo disso.
   Só classe A vira MQL, para a Meta aprender com quem está no perfil. */
const CRM_PONTOS_FATURAMENTO = ['ate-50k' => 0, '50-100k' => 12, '100-200k' => 22, '200-500k' => 27, '500k-1mi' => 30, '1-5mi' => 30, '5mi+' => 30];
const CRM_FATURAMENTO_MINIMO = ['100-200k', '200-500k', '500k-1mi', '1-5mi', '5mi+'];
const CRM_PONTOS_VERBA = ['nao-investe' => 4, 'ate-2500' => 8, '2500-5k' => 18, '5-10k' => 22, '10-20k' => 25, '20-50k' => 25, '50k+' => 25];
const CRM_VERBA_ABAIXO = ['nao-investe', 'ate-2500'];
const CRM_PONTOS_ENCAIXE = ['agora' => 15, 'depois' => 7, 'nao-cabe' => 0];
const CRM_PONTOS_CARGO = ['socio' => 7, 'diretor' => 5, 'marketing' => 3, 'outro' => 1];
const CRM_SEGMENTOS_MARGEM_BAIXA = ['Restaurante / alimentação', 'Loja / varejo'];

/* Regra anterior (leads de antes de 27/10/2026, sem faturamento):
   verba de anúncios (45) + desafio (25) + segmento (15) + site/rede (10) + e-mail da empresa (5).
   A >= 70 · B >= 45 · C abaixo disso. Quem ainda não anunciava ficava no máximo em B.
   (Faixas de investimento total em marketing: até R$ 1 mil sempre C; até R$ 2 mil, no máximo B.) */
const CRM_PONTOS_INVESTIMENTO = [
    'nao-investe' => 8, 'ate-5k' => 22, '5-10k' => 34, '10-20k' => 40,
    '20-40k' => 45, '40-80k' => 45, '80-150k' => 45, '150-300k' => 45, '300-500k' => 45, '500k+' => 45,
    'ate-1k' => 0, '1-2k' => 12, '2-3k' => 25, '3-5k' => 32, '5k+' => 40,
];
const CRM_PONTOS_DESAFIO = [
    'leads-nao-convertem'      => 25, // já gera volume: precisa do sistema completo
    'cac-alto'                 => 25, // já investe e quer escalar
    'agencia-sem-resultado'    => 22, // já investe e está insatisfeito
    'lucro-incerto'            => 20,
    'resultado-oscila'         => 20,
    'sem-clareza'              => 18,
    'poucos-leads'             => 16,
    'estruturar-marketing'     => 12,
    'outro'                    => 5,
    'trafego-caro'             => 22, // formulário antigo
    'anuncios-sem-performance' => 22, // formulário antigo
];
const CRM_SEGMENTOS_PRIORITARIOS = ['Clínica / saúde', 'Odontologia', 'Estética e beleza', 'Imobiliária', 'Empresa de serviços', 'Educação'];
const CRM_EMAILS_GRATUITOS = ['gmail.com', 'hotmail.com', 'outlook.com', 'live.com', 'yahoo.com', 'yahoo.com.br', 'icloud.com', 'bol.com.br', 'uol.com.br', 'terra.com.br', 'msn.com', 'me.com', 'protonmail.com'];

const CRM_CLASSES = [
    'A' => 'Quente',
    'B' => 'Morno',
    'C' => 'Frio',
];

/* Site ou rede social e e-mail com domínio próprio: sinais de empresa estruturada */
function crm_itens_presenca(array $lead, int $maxSite, int $maxRede, int $maxEmail): array
{
    $site = trim((string) ($lead['site_instagram'] ?? ''));
    if (preg_match('~^(https?://)?(www\.)?[a-z0-9-]+(\.[a-z0-9-]+)*\.[a-z]{2,}(/\S*)?$~i', $site) && strpos($site, '@') !== 0) {
        $item = ['Tem site próprio', $maxSite, $maxSite];
    } elseif ($site !== '') {
        $item = ['Tem Instagram ou rede social', $maxRede, $maxSite];
    } else {
        $item = ['Sem site ou rede informada', 0, $maxSite];
    }
    $dominio = strtolower((string) substr(strrchr((string) ($lead['email'] ?? ''), '@') ?: '', 1));
    $pEmail = ($dominio !== '' && !in_array($dominio, CRM_EMAILS_GRATUITOS, true)) ? $maxEmail : 0;
    return [$item, [$pEmail ? 'E-mail com domínio da empresa' : 'E-mail pessoal (Gmail, Hotmail...)', $pEmail, $maxEmail]];
}

/* Devolve ['total' => int, 'classe' => 'A'|'B'|'C', 'regra' => texto, 'itens' => [[rótulo, pontos, máximo], ...]] */
function crm_pontuar(array $lead): array
{
    $fat = (string) ($lead['faturamento'] ?? '');
    if ($fat === '') {
        return crm_pontuar_v4($lead);
    }
    $inv = (string) ($lead['investimento'] ?? '');
    $enc = (string) ($lead['encaixe'] ?? '');
    $des = (string) ($lead['desafio'] ?? '');
    $seg = (string) ($lead['segmento'] ?? '');
    $cargo = (string) ($lead['cargo'] ?? '');

    if (in_array($seg, CRM_SEGMENTOS_PRIORITARIOS, true)) {
        $pSeg = 8;
    } elseif (in_array($seg, CRM_SEGMENTOS_MARGEM_BAIXA, true)) {
        $pSeg = 2;
    } else {
        $pSeg = ($seg === 'Outro' || $seg === '') ? 3 : 6;
    }
    $itens = [
        ['Faturamento: ' . (CRM_FATURAMENTO[$fat] ?? 'não informado'), CRM_PONTOS_FATURAMENTO[$fat] ?? 0, 30],
        ['Verba: ' . (CRM_INVESTIMENTO[$inv] ?? 'não informada'), CRM_PONTOS_VERBA[$inv] ?? 0, 25],
        ['Preço da assessoria: ' . (CRM_ENCAIXE[$enc] ?? 'não respondeu'), CRM_PONTOS_ENCAIXE[$enc] ?? 0, 15],
        ['Desafio: ' . (CRM_DESAFIO[$des] ?? 'não informado'), (int) round((CRM_PONTOS_DESAFIO[$des] ?? 0) * 10 / 25), 10],
        ['Segmento: ' . ($seg !== '' ? $seg : 'não informado'), $pSeg, 8],
        ['Papel: ' . (CRM_CARGO[$cargo] ?? 'não informado'), CRM_PONTOS_CARGO[$cargo] ?? 0, 7],
    ];
    $itens = array_merge($itens, crm_itens_presenca($lead, 3, 2, 2));
    $total = 0;
    foreach ($itens as $item) {
        $total += $item[1];
    }

    $fatOk = in_array($fat, CRM_FATURAMENTO_MINIMO, true);
    $verbaOk = !in_array($inv, CRM_VERBA_ABAIXO, true);
    if ($fat === 'ate-50k') {
        $classe = 'C';
        $regra = 'Fatura até R$ 50 mil por mês: sempre C.';
    } elseif ($enc === 'nao-cabe') {
        $classe = 'C';
        $regra = 'Disse que a assessoria ainda não cabe no orçamento: sempre C.';
    } elseif ($inv === 'ate-2500' && !$fatOk) {
        $classe = 'C';
        $regra = 'Investe até R$ 2.500 em anúncios e fatura menos de R$ 100 mil: sempre C.';
    } elseif ($fatOk && $verbaOk && $enc === 'agora') {
        $classe = 'A';
        $regra = 'Fatura R$ 100 mil+, investe R$ 2.500+ em anúncios e quer começar agora: A.';
    } else {
        $classe = $total >= 45 ? 'B' : 'C';
        $falta = [];
        if (!$fatOk) {
            $falta[] = 'fatura menos de R$ 100 mil';
        }
        if (!$verbaOk) {
            $falta[] = $inv === 'nao-investe' ? 'ainda não anuncia' : 'investe menos de R$ 2.500';
        }
        if ($enc !== 'agora') {
            $falta[] = 'quer começar só daqui a alguns meses';
        }
        $regra = 'Não é A porque ' . implode(', ', $falta) . '. Com 45 pontos ou mais fica B; abaixo disso, C.';
    }

    return ['total' => min(100, $total), 'classe' => $classe, 'regra' => $regra, 'itens' => $itens];
}

/* Regra anterior, para os leads que chegaram sem faturamento */
function crm_pontuar_v4(array $lead): array
{
    $itens = [];

    $inv = (string) ($lead['investimento'] ?? '');
    $itens[] = ['Verba: ' . (CRM_INVESTIMENTO[$inv] ?? 'não informada'), CRM_PONTOS_INVESTIMENTO[$inv] ?? 0, 45];

    $des = (string) ($lead['desafio'] ?? '');
    $itens[] = ['Desafio: ' . (CRM_DESAFIO[$des] ?? 'não informado'), CRM_PONTOS_DESAFIO[$des] ?? 0, 25];

    $seg = (string) ($lead['segmento'] ?? '');
    $pSeg = in_array($seg, CRM_SEGMENTOS_PRIORITARIOS, true) ? 15 : ($seg === 'Outro' || $seg === '' ? 4 : 10);
    $itens[] = ['Segmento: ' . ($seg !== '' ? $seg : 'não informado'), $pSeg, 15];

    $itens = array_merge($itens, crm_itens_presenca($lead, 10, 6, 5));

    $total = 0;
    foreach ($itens as $item) {
        $total += $item[1];
    }

    $classe = $total >= 70 ? 'A' : ($total >= 45 ? 'B' : 'C');
    if ($inv === 'ate-1k') {
        $classe = 'C';
    } elseif (($inv === '1-2k' || $inv === 'nao-investe') && $classe === 'A') {
        $classe = 'B';
    }

    return ['total' => min(100, $total), 'classe' => $classe, 'regra' => 'Regra anterior (formulário sem faturamento): A 70+, B 45 a 69, C abaixo de 45. Quem ainda não anunciava fica no máximo em B.', 'itens' => $itens];
}

/* Resumo curto para cards e listas: "Clínica / saúde · Fatura R$ 100 a 200 mil · R$ 5 a 10 mil em anúncios" */
function crm_resumo_perfil(array $l): string
{
    $partes = [(string) ($l['segmento'] ?? '')];
    $fat = (string) ($l['faturamento'] ?? '');
    if (isset(CRM_FATURAMENTO[$fat])) {
        $partes[] = 'Fatura ' . str_replace(['Até', 'Mais de', ' por mês'], ['até', 'mais de', ''], CRM_FATURAMENTO[$fat]);
    }
    $partes[] = CRM_INVESTIMENTO[$l['investimento'] ?? ''] ?? '';
    return implode(' · ', array_filter($partes, 'strlen'));
}

/* Qualificação a partir da classe do formulário e do checklist do atendimento */
function crm_calcular_qualificacao(array $lead): string
{
    $obrigatorios = true;
    $extras = 0;
    foreach (CRM_CRITERIOS_SQL as $campo => $info) {
        $marcado = !empty($lead[$campo]);
        if ($info[2]) {
            $obrigatorios = $obrigatorios && $marcado;
        } elseif ($marcado) {
            $extras++;
        }
    }
    if ($obrigatorios && $extras >= 2) {
        return 'sql';
    }
    /* Formulário atual: só classe A é MQL. Leads antigos: A ou B. */
    $classesMql = ($lead['faturamento'] ?? '') !== '' ? ['A'] : ['A', 'B'];
    return in_array($lead['classe'] ?? '', $classesMql, true) ? 'mql' : 'lead';
}

/* Recalcula e grava a qualificação. Na primeira vez que vira MQL ou SQL,
   registra a data e manda o evento para as integrações (Meta / Google). */
function crm_atualizar_qualificacao(int $id): void
{
    $db = crm_db();
    $st = $db->prepare('SELECT * FROM leads WHERE id = ?');
    $st->execute([$id]);
    $lead = $st->fetch();
    if (!$lead) {
        return;
    }
    $nova = crm_calcular_qualificacao($lead);
    $agora = crm_agora();
    $sets = ['qualificacao = ?'];
    $args = [$nova];
    if (($nova === 'mql' || $nova === 'sql') && empty($lead['mql_em'])) {
        $sets[] = 'mql_em = ?';
        $args[] = $agora;
        crm_enfileirar($id, 'mql');
    }
    if ($nova === 'sql' && empty($lead['sql_em'])) {
        $sets[] = 'sql_em = ?';
        $args[] = $agora;
        crm_enfileirar($id, 'sql');
    }
    if ($nova !== $lead['qualificacao']) {
        crm_nota($id, 'sistema', 'Qualificação: ' . CRM_QUALIFICACAO[$lead['qualificacao']] . ' → ' . CRM_QUALIFICACAO[$nova]);
    }
    $args[] = $id;
    $db->prepare('UPDATE leads SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($args);
}

function crm_nota(int $leadId, string $autor, string $texto): void
{
    crm_db()->prepare('INSERT INTO notas (lead_id, criado_em, autor, texto) VALUES (?, ?, ?, ?)')
        ->execute([$leadId, crm_agora(), $autor, $texto]);
}

/* Move o lead de etapa. Venda exige valor; perda exige motivo.
   Devolve '' se deu certo ou a mensagem de erro. */
function crm_mudar_etapa(int $id, string $nova, string $autor, array $dados = []): string
{
    if (!isset(CRM_STATUS[$nova])) {
        return 'Etapa inválida.';
    }
    $db = crm_db();
    $st = $db->prepare('SELECT * FROM leads WHERE id = ?');
    $st->execute([$id]);
    $lead = $st->fetch();
    if (!$lead) {
        return 'Lead não encontrado.';
    }
    if ($lead['status'] === $nova) {
        return '';
    }

    $agora = crm_agora();
    $sets = ['status = ?', 'etapa_desde = ?', 'atualizado_em = ?'];
    $args = [$nova, $agora, $agora];
    $detalhe = '';

    if ($nova === 'ganho') {
        $valor = crm_valor_br((string) ($dados['valor'] ?? ''));
        if ($valor <= 0) {
            return 'Informe o valor mensal do contrato para registrar a venda.';
        }
        $sets[] = 'valor_mensal = ?';
        $args[] = $valor;
        $sets[] = 'fechado_em = ?';
        $args[] = $agora;
        $sets[] = "motivo_perda = ''";
        $detalhe = ' (R$ ' . number_format($valor, 2, ',', '.') . '/mês)';
    } elseif ($nova === 'perdido') {
        $motivo = (string) ($dados['motivo'] ?? '');
        if (!isset(CRM_MOTIVOS_PERDA[$motivo])) {
            return 'Escolha o motivo da perda.';
        }
        $sets[] = 'motivo_perda = ?';
        $args[] = $motivo;
        $sets[] = 'fechado_em = ?';
        $args[] = $agora;
        $detalhe = ' (' . CRM_MOTIVOS_PERDA[$motivo] . ')';
    } else {
        $sets[] = 'fechado_em = NULL';
        $sets[] = "motivo_perda = ''";
    }

    /* Tempo até o primeiro contato (speed to lead) */
    if ($lead['status'] === 'novo' && empty($lead['primeiro_contato_em'])) {
        $sets[] = 'primeiro_contato_em = ?';
        $args[] = $agora;
    }
    if ($nova === 'agendada') {
        $sets[] = 'agendada_em = COALESCE(agendada_em, ?)';
        $args[] = $agora;
    }
    if (empty($lead['visto_em'])) {
        $sets[] = 'visto_em = ?';
        $args[] = $agora;
    }

    $args[] = $id;
    $db->prepare('UPDATE leads SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($args);
    crm_nota($id, $autor, 'Etapa: ' . CRM_STATUS[$lead['status']] . ' → ' . CRM_STATUS[$nova] . $detalhe);

    if ($nova === 'agendada') {
        crm_enfileirar($id, 'agendada');
    } elseif ($nova === 'ganho') {
        crm_enfileirar($id, 'ganho');
    }
    return '';
}

/* "1.500,00", "1500,5" ou "1500.50" viram 1500.0 / 1500.5 / 1500.5 */
function crm_valor_br(string $texto): float
{
    $texto = preg_replace('/[^\d,.]/', '', $texto) ?? '';
    if (strpos($texto, ',') !== false) {
        $texto = str_replace(['.', ','], ['', '.'], $texto);
    }
    return round((float) $texto, 2);
}

/* Dias inteiros desde uma data (horário de Brasília) */
function crm_dias_desde(?string $data): int
{
    if (!$data) {
        return 0;
    }
    $d = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data, new DateTimeZone('America/Sao_Paulo'));
    return $d ? (int) floor((time() - $d->getTimestamp()) / 86400) : 0;
}

/* Card parado há mais tempo que o limite da etapa */
function crm_parado(array $lead): int
{
    $limite = CRM_ETAPAS_INFO[$lead['status']]['parado'] ?? 0;
    if ($limite <= 0) {
        return 0;
    }
    $dias = crm_dias_desde($lead['etapa_desde'] ?? $lead['criado_em']);
    return $dias >= $limite ? $dias : 0;
}

function crm_selo_classe(string $classe, ?int $pontos = null): string
{
    if (!isset(CRM_CLASSES[$classe])) {
        return '';
    }
    $texto = $classe . ' · ' . CRM_CLASSES[$classe] . ($pontos !== null ? ' · ' . $pontos . ' pts' : '');
    return '<span class="selo selo-classe-' . strtolower($classe) . '" title="Pontuação do formulário">' . h($texto) . '</span>';
}

function crm_selo_qualificacao(string $q): string
{
    if (!isset(CRM_QUALIFICACAO[$q])) {
        return '';
    }
    return '<span class="selo selo-q-' . h($q) . '">' . h(CRM_QUALIFICACAO[$q]) . '</span>';
}

/* Duração legível entre duas datas ("4 min", "2 h", "3 dias") */
function crm_duracao(?string $inicio, ?string $fim): string
{
    if (!$inicio || !$fim) {
        return '';
    }
    $tz = new DateTimeZone('America/Sao_Paulo');
    $a = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $inicio, $tz);
    $b = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $fim, $tz);
    if (!$a || !$b) {
        return '';
    }
    $seg = max(0, $b->getTimestamp() - $a->getTimestamp());
    if ($seg < 3600) {
        return max(1, (int) round($seg / 60)) . ' min';
    }
    if ($seg < 86400) {
        return round($seg / 3600, 1) . ' h';
    }
    return (int) round($seg / 86400) . ' dias';
}
