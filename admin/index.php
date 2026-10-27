<?php
/* Funil de vendas (kanban): uma coluna por etapa, cards arrastáveis */

require __DIR__ . '/../inc/crm.php';
$usuario = crm_exigir_login();
$db = crm_db();

$classe = (string) ($_GET['classe'] ?? '');
$classe = isset(CRM_CLASSES[$classe]) ? $classe : '';
$busca = crm_texto($_GET['q'] ?? '', 80);

$where = "(status IN ('novo','atendimento','agendada','realizada','proposta') OR (status IN ('ganho','perdido') AND fechado_em >= ?))";
$args = [date('Y-m-d H:i:s', time() - 30 * 86400)];
if ($classe !== '') {
    $where .= ' AND classe = ?';
    $args[] = $classe;
}
if ($busca !== '') {
    $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $busca) . '%';
    $where .= " AND (nome LIKE ? ESCAPE '\\' OR empresa LIKE ? ESCAPE '\\' OR whatsapp LIKE ? ESCAPE '\\')";
    array_push($args, $like, $like, $like);
}
$st = $db->prepare("SELECT * FROM leads WHERE $where");
$st->execute($args);

$colunas = [];
foreach (CRM_STATUS as $etapa => $nome) {
    $colunas[$etapa] = [];
}
foreach ($st->fetchAll() as $l) {
    if (isset($colunas[$l['status']])) {
        $colunas[$l['status']][] = $l;
    }
}
/* Ordem dentro da coluna: novos pelos mais recentes; o resto pela próxima ação */
foreach ($colunas as $etapa => &$cards) {
    usort($cards, function ($a, $b) use ($etapa) {
        if ($etapa === 'novo' || $etapa === 'ganho' || $etapa === 'perdido') {
            return strcmp((string) ($b['fechado_em'] ?: $b['criado_em']), (string) ($a['fechado_em'] ?: $a['criado_em']));
        }
        $pa = $a['proxima_acao_em'] ?: '9999';
        $pb = $b['proxima_acao_em'] ?: '9999';
        return $pa === $pb ? strcmp((string) $a['etapa_desde'], (string) $b['etapa_desde']) : strcmp($pa, $pb);
    });
}
unset($cards);

/* ---------- indicadores dos últimos 30 dias */
$desde = date('Y-m-d H:i:s', time() - 30 * 86400);
$st = $db->prepare("SELECT COUNT(*) total,
    SUM(qualificacao IN ('mql','sql')) mql,
    SUM(qualificacao = 'sql') sqls,
    SUM(agendada_em IS NOT NULL) reunioes,
    SUM(status = 'ganho') vendas,
    SUM(CASE WHEN status = 'ganho' THEN valor_mensal ELSE 0 END) receita
    FROM leads WHERE criado_em >= ?");
$st->execute([$desde]);
$k = $st->fetch();
$st = $db->prepare('SELECT criado_em, primeiro_contato_em FROM leads WHERE primeiro_contato_em IS NOT NULL AND criado_em >= ?');
$st->execute([$desde]);
$tempos = [];
foreach ($st->fetchAll() as $t) {
    $a = strtotime($t['criado_em']);
    $b = strtotime($t['primeiro_contato_em']);
    if ($a && $b && $b >= $a) {
        $tempos[] = $b - $a;
    }
}
sort($tempos);
$mediana = $tempos ? $tempos[(int) floor(count($tempos) / 2)] : null;
$semContato = count($colunas['novo']);
$agora = date('Y-m-d H:i:s');
$atrasadas = 0;
foreach (CRM_ETAPAS_ABERTAS as $e) {
    foreach ($colunas[$e] as $l) {
        if ($l['proxima_acao_em'] && $l['proxima_acao_em'] < $agora) {
            $atrasadas++;
        }
    }
}
function pct($parte, $todo): string
{
    return $todo > 0 ? round($parte / $todo * 100) . '%' : '–';
}
function tempo_legivel(?int $seg): string
{
    if ($seg === null) {
        return '–';
    }
    if ($seg < 3600) {
        return max(1, (int) round($seg / 60)) . ' min';
    }
    return $seg < 86400 ? round($seg / 3600, 1) . ' h' : round($seg / 86400, 1) . ' dias';
}

$idsNaoVistos = [];
foreach ($colunas as $cards) {
    foreach ($cards as $l) {
        if ($l['visto_em'] === null) {
            $idsNaoVistos[] = (int) $l['id'];
        }
    }
}
$idsNaoVistos = array_values(array_diff($idsNaoVistos, $_SESSION['nao_auto'] ?? []));

crm_topo('Funil', $usuario, 'pagina-funil');
?>
<main class="funil-pagina"<?php if ($idsNaoVistos): ?> data-auto-visto data-ids="<?= h(implode(',', $idsNaoVistos)) ?>" data-csrf="<?= h(crm_csrf()) ?>"<?php endif; ?>>
  <div class="wrap">
    <div class="cabeca">
      <div>
        <h1>Funil de vendas</h1>
        <p class="muted">Arraste os cards entre as etapas ou abra o lead para atualizar.</p>
      </div>
      <form class="funil-filtros" method="get" action="index.php">
        <input type="search" name="q" value="<?= h($busca) ?>" placeholder="Buscar lead" aria-label="Buscar lead">
        <select name="classe" aria-label="Classe">
          <option value="">Todas as classes</option>
          <?php foreach (CRM_CLASSES as $c => $nome): ?>
            <option value="<?= h($c) ?>"<?= $classe === $c ? ' selected' : '' ?>><?= h($c . ' · ' . $nome) ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-escuro" type="submit">Filtrar</button>
        <?php if ($classe !== '' || $busca !== ''): ?><a class="limpar" href="index.php">Limpar</a><?php endif; ?>
      </form>
    </div>

    <section class="kpis kpis-6" aria-label="Indicadores dos últimos 30 dias">
      <div class="kpi"><span>Leads · 30 dias</span><b><?= (int) $k['total'] ?></b><small>recebidos</small></div>
      <div class="kpi"><span>MQL</span><b><?= (int) $k['mql'] ?></b><small><?= pct($k['mql'], $k['total']) ?> dos leads</small></div>
      <div class="kpi"><span>SQL</span><b><?= (int) $k['sqls'] ?></b><small><?= pct($k['sqls'], $k['mql']) ?> dos MQL</small></div>
      <div class="kpi"><span>Reuniões</span><b><?= (int) $k['reunioes'] ?></b><small>agendadas</small></div>
      <div class="kpi kpi-destaque"><span>Vendas</span><b><?= (int) $k['vendas'] ?></b><small>R$ <?= number_format((float) $k['receita'], 0, ',', '.') ?>/mês</small></div>
      <div class="kpi"><span>1º contato</span><b><?= h(tempo_legivel($mediana)) ?></b><small>tempo mediano</small></div>
    </section>

    <?php if ($semContato || $atrasadas): ?>
      <div class="alertas-funil">
        <?php if ($semContato): ?><span class="alerta-chip alerta-chip-forte"><?= $semContato ?> lead<?= $semContato > 1 ? 's' : '' ?> aguardando o 1º contato</span><?php endif; ?>
        <?php if ($atrasadas): ?><span class="alerta-chip"><?= $atrasadas === 1 ? '1 próxima ação atrasada' : $atrasadas . ' próximas ações atrasadas' ?></span><?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="quadro" data-quadro data-csrf="<?= h(crm_csrf()) ?>">
    <?php foreach ($colunas as $etapa => $cards): ?>
      <?php $soma = 0; foreach ($cards as $c) { $soma += (float) $c['valor_mensal']; } ?>
      <section class="coluna coluna-<?= h($etapa) ?>" data-etapa="<?= h($etapa) ?>" aria-label="<?= h(CRM_STATUS[$etapa]) ?>">
        <header class="coluna-cab" title="<?= h(CRM_ETAPAS_INFO[$etapa]['dica']) ?>">
          <b><?= h(CRM_STATUS[$etapa]) ?></b>
          <span class="coluna-qtd" data-qtd><?= count($cards) ?></span>
          <?php if (($etapa === 'ganho' || $etapa === 'proposta') && $soma > 0): ?><small>R$ <?= number_format($soma, 0, ',', '.') ?>/mês</small><?php endif; ?>
          <?php if ($etapa === 'ganho' || $etapa === 'perdido'): ?><small>últimos 30 dias</small><?php endif; ?>
        </header>
        <div class="coluna-cards" data-soltar>
          <?php foreach ($cards as $l): ?>
            <?php
            $parado = crm_parado($l);
            $acaoAtrasada = $l['proxima_acao_em'] && $l['proxima_acao_em'] < $agora;
            ?>
            <a class="card<?= $l['visto_em'] === null ? ' card-novo' : '' ?>" href="lead.php?id=<?= (int) $l['id'] ?>" draggable="true" data-id="<?= (int) $l['id'] ?>">
              <span class="card-selos">
                <?php if ($l['visto_em'] === null): ?><span class="ponto" title="Ainda não visto"></span><?php endif; ?>
                <?= crm_selo_classe($l['classe']) ?>
                <?php if ($l['qualificacao'] !== 'lead'): ?><?= crm_selo_qualificacao($l['qualificacao']) ?><?php endif; ?>
              </span>
              <b class="card-nome"><?= h($l['nome']) ?></b>
              <small class="card-empresa"><?= h($l['empresa']) ?></small>
              <span class="card-info"><?= h(crm_resumo_perfil($l)) ?></span>
              <?php if ($etapa === 'novo'): ?>
                <span class="card-tempo">Chegou <?= h(crm_tempo_relativo($l['criado_em'])) ?></span>
              <?php elseif ($etapa === 'ganho'): ?>
                <span class="card-valor">R$ <?= number_format((float) $l['valor_mensal'], 2, ',', '.') ?>/mês</span>
              <?php elseif ($etapa === 'perdido'): ?>
                <span class="card-tempo"><?= h(CRM_MOTIVOS_PERDA[$l['motivo_perda']] ?? '') ?></span>
              <?php endif; ?>
              <?php if ($l['proxima_acao'] !== '' && in_array($etapa, CRM_ETAPAS_ABERTAS, true)): ?>
                <span class="card-acao<?= $acaoAtrasada ? ' atrasada' : '' ?>"><?= $l['proxima_acao_em'] ? h(crm_data($l['proxima_acao_em'])) . ' · ' : '' ?><?= h($l['proxima_acao']) ?></span>
              <?php elseif (in_array($etapa, ['atendimento', 'agendada', 'realizada', 'proposta'], true)): ?>
                <span class="card-acao sem">Sem próxima ação</span>
              <?php endif; ?>
              <?php if ($parado): ?><span class="card-parado">Parado há <?= $parado ?> dia<?= $parado > 1 ? 's' : '' ?></span><?php endif; ?>
            </a>
          <?php endforeach; ?>
          <?php if (!$cards): ?><p class="coluna-vazia">Nenhum lead</p><?php endif; ?>
        </div>
      </section>
    <?php endforeach; ?>
  </div>
</main>
<?php crm_rodape();
