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

/* ------------------------------ qualificação, do menos ao mais qualificado
   Cliente ideal: fatura a partir de R$ 100 mil por mês, já investe ou já investiu
   em tráfego pago, entende que precisa de verba relevante e constante e de um bom
   processo de atendimento, e topa pagar cerca de R$ 4 mil por mês pela assessoria.

   Desqualificado: o formulário falhou em um critério eliminatório (fora do perfil).
   Lead: parcialmente qualificado; tem parte do perfil e pede conversa ou nutrição.
   MQL: o formulário cumpre todos os critérios do perfil ideal.
   SQL: a conversa confirmou os 4 critérios obrigatórios e pelo menos 1 dos outros 2.
   O SQL vale mesmo que o formulário tenha dado outro nível: a conversa manda. */
const CRM_CRITERIOS_SQL = [
    'qual_orcamento' => ['Aceita o investimento', 'Topa pagar cerca de R$ 4 mil por mês pela assessoria, além da verba de anúncios.', true],
    'qual_decisor'   => ['Fala com quem decide', 'É dono, sócio ou quem aprova a contratação.', true],
    'qual_verba'     => ['Verba relevante e constante', 'Vai manter pelo menos R$ 2.500 a R$ 3 mil por mês em anúncios, todo mês.', true],
    'qual_operacao'  => ['Atendimento pronto', 'Tem quem responda os leads rápido e faça follow-up, ou topa estruturar isso.', true],
    'qual_dor'       => ['Dor clara', 'Tem um problema concreto que a assessoria resolve.', false],
    'qual_momento'   => ['Quer começar logo', 'Pretende começar em até 60 dias.', false],
];
const CRM_SQL_EXTRAS_MINIMO = 1;

const CRM_QUALIFICACAO = [
    'desqualificado' => 'Desqualificado',
    'lead'           => 'Lead',
    'mql'            => 'MQL',
    'sql'            => 'SQL',
];
const CRM_QUALIFICACAO_DICA = [
    'desqualificado' => 'Fora do perfil: falhou em um critério eliminatório do formulário.',
    'lead'           => 'Parcialmente qualificado: tem parte do perfil. Converse para entender ou nutra até o momento certo.',
    'mql'            => 'Perfil ideal pelo formulário. Confirme os critérios na conversa para virar SQL.',
    'sql'            => 'Perfil confirmado na conversa. Pronto para reunião e proposta.',
];

/* -------------------------------------------- pontuação do formulário
   0 a 100 pontos, para ordenar a prioridade dentro de cada nível:
   faturamento (25) + verba de anúncios (20) + preço da assessoria (15) + atendimento (12)
   + desafio (10) + papel na empresa (7) + segmento (6) + site/rede (3) + e-mail da empresa (2).
   O nível (desqualificado, lead ou MQL) vem dos critérios do perfil ideal, não da soma. */
const CRM_PONTOS_FATURAMENTO = ['ate-50k' => 0, '50-100k' => 10, '100-200k' => 18, '200-500k' => 22, '500k-1mi' => 25, '1-5mi' => 25, '5mi+' => 25];
const CRM_FATURAMENTO_MINIMO = ['100-200k', '200-500k', '500k-1mi', '1-5mi', '5mi+'];
const CRM_PONTOS_VERBA = ['nunca' => 0, 'nao-investe' => 0, 'parou' => 10, 'ate-2500' => 8, '2500-5k' => 15, '5-10k' => 18, '10-20k' => 20, '20-50k' => 20, '50k+' => 20];
const CRM_VERBA_NUNCA = ['nunca', 'nao-investe']; // "nao-investe": formulário de 24 a 27/10, tratado como nunca
const CRM_VERBA_RELEVANTE = ['2500-5k', '5-10k', '10-20k', '20-50k', '50k+'];
const CRM_PONTOS_ENCAIXE = ['agora' => 15, 'depois' => 7, 'nao-cabe' => 0];
const CRM_PONTOS_ATENDIMENTO = ['processo' => 12, 'equipe' => 9, 'dono' => 5, 'ninguem' => 0];
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

/* Os 5 critérios do perfil ideal, como o formulário respondeu.
   Cada um: [rótulo, status, resposta, o que falta]. Status: 'ok', 'parcial', 'nao'
   ou 'sem' (pergunta criada depois que o lead chegou). */
function crm_perfil_ideal(array $lead): array
{
    $fat = (string) ($lead['faturamento'] ?? '');
    $inv = (string) ($lead['investimento'] ?? '');
    $enc = (string) ($lead['encaixe'] ?? '');
    $atd = (string) ($lead['atendimento'] ?? '');
    $resp = function (array $lista, string $chave): string {
        return $lista[$chave] ?? 'não informado';
    };
    $nunca = in_array($inv, CRM_VERBA_NUNCA, true);

    if (in_array($fat, CRM_FATURAMENTO_MINIMO, true)) {
        $f = ['ok', ''];
    } elseif ($fat === '50-100k') {
        $f = ['parcial', 'fatura entre R$ 50 mil e R$ 100 mil'];
    } else {
        $f = ['nao', 'fatura até R$ 50 mil'];
    }

    if (in_array($inv, CRM_VERBA_RELEVANTE, true)) {
        $v = ['ok', ''];
    } elseif ($inv === 'parou') {
        $v = ['parcial', 'está com os anúncios parados (confirme na conversa quanto investia)'];
    } elseif ($inv === 'ate-2500') {
        $v = ['parcial', 'investe menos de R$ 2.500 por mês'];
    } else {
        $v = ['nao', ''];
    }

    if ($atd === '') {
        $a = ['sem', ''];
    } elseif ($atd === 'processo' || $atd === 'equipe') {
        $a = ['ok', ''];
    } elseif ($atd === 'dono') {
        $a = ['parcial', 'os leads são atendidos quando dá, sem alguém dedicado'];
    } else {
        $a = ['nao', 'ainda não tem quem atenda os leads'];
    }

    if ($enc === 'agora') {
        $e = ['ok', ''];
    } elseif ($enc === 'depois') {
        $e = ['parcial', 'quer começar só daqui a alguns meses'];
    } else {
        $e = ['nao', 'disse que a assessoria ainda não cabe no orçamento'];
    }

    return [
        'faturamento' => ['Fatura R$ 100 mil+ por mês', $f[0], $resp(CRM_FATURAMENTO, $fat), $f[1]],
        'trafego'     => ['Já investe ou já investiu em tráfego', $nunca ? 'nao' : 'ok', $resp(CRM_INVESTIMENTO, $inv), $nunca ? 'nunca investiu em tráfego' : ''],
        'verba'       => ['Verba de R$ 2.500+ por mês em anúncios', $v[0], $resp(CRM_INVESTIMENTO, $inv), $v[1]],
        'atendimento' => ['Tem quem atenda os leads', $a[0], $atd === '' ? 'pergunta criada depois deste lead' : $resp(CRM_ATENDIMENTO, $atd), $a[1]],
        'preco'       => ['Topa a assessoria de R$ 4 mil+ por mês', $e[0], $resp(CRM_ENCAIXE, $enc), $e[1]],
    ];
}

/* Nível pelo formulário: desqualificado (critério eliminatório), MQL (perfil
   ideal completo) ou lead (o resto). Devolve [nível, explicação]. */
function crm_nivel_formulario(array $lead, array $perfil): array
{
    $fat = (string) ($lead['faturamento'] ?? '');
    $inv = (string) ($lead['investimento'] ?? '');
    $fatOk = in_array($fat, CRM_FATURAMENTO_MINIMO, true);
    $nunca = in_array($inv, CRM_VERBA_NUNCA, true);

    $eliminatorios = [
        [$fat === 'ate-50k', 'fatura até R$ 50 mil por mês'],
        [($lead['encaixe'] ?? '') === 'nao-cabe', 'disse que a assessoria ainda não cabe no orçamento'],
        [$nunca && !$fatOk, 'nunca investiu em tráfego e fatura menos de R$ 100 mil'],
        [$inv === 'ate-2500' && !$fatOk, 'investe até R$ 2.500 e fatura menos de R$ 100 mil'],
        [$nunca && ($lead['atendimento'] ?? '') === 'ninguem', 'nunca investiu em tráfego e não tem quem atenda os leads'],
    ];
    foreach ($eliminatorios as $regra) {
        if ($regra[0]) {
            return ['desqualificado', 'Desqualificado porque ' . $regra[1] . '.'];
        }
    }

    /* Para MQL: tudo "ok". Anúncios parados contam (o lead já investiu) e a
       pergunta de atendimento pode faltar nos leads que chegaram antes dela. */
    $faltas = [];
    foreach ($perfil as $chave => $c) {
        $aceito = $c[1] === 'ok' || $c[1] === 'sem' || ($chave === 'verba' && $inv === 'parou');
        if (!$aceito && $c[3] !== '') {
            $faltas[] = $c[3];
        }
    }
    if (!$faltas) {
        return ['mql', 'MQL: cumpre todos os critérios do perfil ideal.'];
    }
    return ['lead', 'Lead (parcialmente qualificado) porque ' . implode('; ', $faltas) . '.'];
}

/* Devolve ['total' => int, 'nivel' => desqualificado|lead|mql, 'classe' => 'A'|'B'|'C',
   'regra' => texto, 'itens' => [[rótulo, pontos, máximo], ...], 'perfil' => crm_perfil_ideal()] */
function crm_pontuar(array $lead): array
{
    $fat = (string) ($lead['faturamento'] ?? '');
    if ($fat === '') {
        return crm_pontuar_v4($lead);
    }
    $inv = (string) ($lead['investimento'] ?? '');
    $enc = (string) ($lead['encaixe'] ?? '');
    $atd = (string) ($lead['atendimento'] ?? '');
    $des = (string) ($lead['desafio'] ?? '');
    $seg = (string) ($lead['segmento'] ?? '');
    $cargo = (string) ($lead['cargo'] ?? '');

    if (in_array($seg, CRM_SEGMENTOS_PRIORITARIOS, true)) {
        $pSeg = 6;
    } elseif (in_array($seg, CRM_SEGMENTOS_MARGEM_BAIXA, true)) {
        $pSeg = 1;
    } else {
        $pSeg = ($seg === 'Outro' || $seg === '') ? 2 : 5;
    }
    $itens = [
        ['Faturamento: ' . (CRM_FATURAMENTO[$fat] ?? 'não informado'), CRM_PONTOS_FATURAMENTO[$fat] ?? 0, 25],
        ['Verba: ' . (CRM_INVESTIMENTO[$inv] ?? 'não informada'), CRM_PONTOS_VERBA[$inv] ?? 0, 20],
        ['Preço da assessoria: ' . (CRM_ENCAIXE[$enc] ?? 'não respondeu'), CRM_PONTOS_ENCAIXE[$enc] ?? 0, 15],
        $atd === ''
            ? ['Atendimento: não perguntado (lead anterior à pergunta)', 6, 12]
            : ['Atendimento: ' . (CRM_ATENDIMENTO[$atd] ?? 'não informado'), CRM_PONTOS_ATENDIMENTO[$atd] ?? 0, 12],
        ['Desafio: ' . (CRM_DESAFIO[$des] ?? 'não informado'), (int) round((CRM_PONTOS_DESAFIO[$des] ?? 0) * 10 / 25), 10],
        ['Papel: ' . (CRM_CARGO[$cargo] ?? 'não informado'), CRM_PONTOS_CARGO[$cargo] ?? 0, 7],
        ['Segmento: ' . ($seg !== '' ? $seg : 'não informado'), $pSeg, 6],
    ];
    $itens = array_merge($itens, crm_itens_presenca($lead, 3, 2, 2));
    $total = 0;
    foreach ($itens as $item) {
        $total += $item[1];
    }

    $perfil = crm_perfil_ideal($lead);
    list($nivel, $regra) = crm_nivel_formulario($lead, $perfil);
    $classe = ['mql' => 'A', 'lead' => 'B', 'desqualificado' => 'C'][$nivel];

    return ['total' => min(100, $total), 'nivel' => $nivel, 'classe' => $classe, 'regra' => $regra, 'itens' => $itens, 'perfil' => $perfil];
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

    /* Na regra anterior, A e B eram MQL; C continua como lead */
    $nivel = $classe === 'C' ? 'lead' : 'mql';
    return ['total' => min(100, $total), 'nivel' => $nivel, 'classe' => $classe, 'itens' => $itens, 'perfil' => [],
        'regra' => 'Lead do formulário antigo (sem faturamento): ' . CRM_QUALIFICACAO[$nivel] . ' pela regra da época, pontuação ' . min(100, $total) . ' (A e B eram MQL).'];
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

/* Qualificação: SQL pelo checklist da conversa; senão, o nível do formulário */
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
    if ($obrigatorios && $extras >= CRM_SQL_EXTRAS_MINIMO) {
        return 'sql';
    }
    return crm_pontuar($lead)['nivel'];
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

function crm_selo_pontos(int $pontos): string
{
    return '<span class="selo selo-pontos" title="Pontuação do formulário (0 a 100): prioridade dentro do nível">' . $pontos . ' pts</span>';
}

function crm_selo_qualificacao(string $q): string
{
    if (!isset(CRM_QUALIFICACAO[$q])) {
        return '';
    }
    return '<span class="selo selo-q-' . h($q) . '" title="' . h(CRM_QUALIFICACAO_DICA[$q]) . '">' . h(CRM_QUALIFICACAO[$q]) . '</span>';
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
