<?php
/* Lista de leads com busca e filtros (etapa e qualificação) */

require __DIR__ . '/../inc/crm.php';
$usuario = crm_exigir_login();
$db = crm_db();

$busca  = crm_texto($_GET['q'] ?? '', 80);
$status = (string) ($_GET['status'] ?? '');
$qual   = (string) ($_GET['qual'] ?? '');
$status = isset(CRM_STATUS[$status]) ? $status : '';
$qual   = isset(CRM_QUALIFICACAO[$qual]) ? $qual : '';
$pagina = max(1, (int) ($_GET['p'] ?? 1));
$porPagina = 50;

$where = [];
$args = [];
if ($busca !== '') {
    $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $busca) . '%';
    $where[] = "(nome LIKE ? ESCAPE '\\' OR empresa LIKE ? ESCAPE '\\' OR email LIKE ? ESCAPE '\\' OR whatsapp LIKE ? ESCAPE '\\')";
    array_push($args, $like, $like, $like, $like);
}
if ($status !== '') {
    $where[] = 'status = ?';
    $args[] = $status;
}
if ($qual !== '') {
    $where[] = 'qualificacao = ?';
    $args[] = $qual;
}
$sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$st = $db->prepare("SELECT COUNT(*) FROM leads $sqlWhere");
$st->execute($args);
$total = (int) $st->fetchColumn();
$paginas = max(1, (int) ceil($total / $porPagina));
$pagina = min($pagina, $paginas);

$st = $db->prepare("SELECT * FROM leads $sqlWhere ORDER BY criado_em DESC, id DESC LIMIT $porPagina OFFSET " . (($pagina - 1) * $porPagina));
$st->execute($args);
$leads = $st->fetchAll();

$naoVistos = $db->query('SELECT * FROM leads WHERE visto_em IS NULL ORDER BY criado_em DESC, id DESC LIMIT 12')->fetchAll();
$totalNaoVistos = crm_total_nao_vistos();
$idsAuto = [];
foreach (array_merge($naoVistos, $leads) as $l) {
    if ($l['visto_em'] === null) {
        $idsAuto[(int) $l['id']] = true;
    }
}
$idsAuto = array_values(array_diff(array_keys($idsAuto), $_SESSION['nao_auto'] ?? []));
$agora = date('Y-m-d H:i:s');

function link_filtro(array $mudar): string
{
    $atual = array_filter([
        'q' => $_GET['q'] ?? '', 'status' => $_GET['status'] ?? '', 'qual' => $_GET['qual'] ?? '',
    ], 'strlen');
    $novo = array_filter(array_merge($atual, $mudar), function ($v) {
        return $v !== '' && $v !== null;
    });
    return 'lista.php' . ($novo ? '?' . http_build_query($novo) : '');
}

crm_topo('Lista de leads', $usuario);
?>
<main class="wrap"<?php if ($idsAuto): ?> data-auto-visto data-ids="<?= h(implode(',', $idsAuto)) ?>" data-csrf="<?= h(crm_csrf()) ?>"<?php endif; ?>>
  <div class="cabeca">
    <div>
      <h1>Lista de leads</h1>
      <p class="muted">Todos os contatos recebidos, com busca e filtros.</p>
    </div>
    <a class="btn btn-claro" href="exportar.php">Exportar CSV</a>
  </div>

  <?php if ($naoVistos): ?>
  <section class="naovistos" aria-label="Novos ainda não vistos">
    <div class="naovistos-cab">
      <h2><span class="ponto"></span>Novos, ainda não vistos <span class="contador"><?= $totalNaoVistos ?></span></h2>
      <span class="naovistos-dica" data-aviso-visto>Viram “vistos” assim que você mexe na página.</span>
    </div>
    <div class="naovistos-grade">
      <?php foreach ($naoVistos as $n): ?>
        <a class="nv" href="lead.php?id=<?= (int) $n['id'] ?>">
          <span class="nv-quando"><?= h(crm_tempo_relativo($n['criado_em'])) ?></span>
          <b><?= h($n['nome']) ?></b>
          <small><?= h($n['empresa']) ?></small>
          <span class="nv-info"><?= h(crm_resumo_perfil($n)) ?></span>
          <span class="nv-rodape"><?= crm_selo_qualificacao($n['qualificacao']) ?><span class="nv-abrir">Abrir →</span></span>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <form class="filtros filtros-3" method="get" action="lista.php">
    <input type="search" name="q" value="<?= h($busca) ?>" placeholder="Buscar por nome, empresa, e-mail ou WhatsApp" aria-label="Buscar">
    <select name="status" aria-label="Etapa">
      <option value="">Todas as etapas</option>
      <?php foreach (CRM_STATUS as $valor => $rotulo): ?>
        <option value="<?= h($valor) ?>"<?= $status === $valor ? ' selected' : '' ?>><?= h($rotulo) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="qual" aria-label="Qualificação">
      <option value="">Todas as qualificações</option>
      <?php foreach (CRM_QUALIFICACAO as $q => $nome): ?>
        <option value="<?= h($q) ?>"<?= $qual === $q ? ' selected' : '' ?>><?= h($nome) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-escuro">Filtrar</button>
    <?php if ($sqlWhere): ?><a class="limpar" href="lista.php">Limpar</a><?php endif; ?>
  </form>

  <?php if (!$leads): ?>
    <div class="vazio"><b><?= $sqlWhere ? 'Nenhum lead com esses filtros.' : 'Nenhum lead ainda.' ?></b><p><?= $sqlWhere ? '<a href="lista.php">Ver todos</a>' : 'Assim que alguém preencher o formulário, o contato aparece aqui.' ?></p></div>
  <?php else: ?>
    <p class="contagem"><?= $total ?> lead<?= $total === 1 ? '' : 's' ?></p>
    <div class="tabela tabela-lista" role="table" aria-label="Leads">
      <div class="linha linha-cab" role="row">
        <span role="columnheader">Recebido</span><span role="columnheader">Lead</span><span role="columnheader">Pontos</span>
        <span role="columnheader">Qualificação</span><span role="columnheader">Etapa</span><span role="columnheader">Próxima ação</span><span role="columnheader"></span>
      </div>
      <?php foreach ($leads as $l): ?>
        <?php $atrasada = $l['proxima_acao_em'] && $l['proxima_acao_em'] < $agora && in_array($l['status'], CRM_ETAPAS_ABERTAS, true); ?>
        <div class="linha<?= $l['visto_em'] === null ? ' linha-nova' : '' ?>" role="row">
          <span class="c-data" role="cell"><?= h(crm_data($l['criado_em'])) ?></span>
          <span class="c-lead" role="cell"><a href="lead.php?id=<?= (int) $l['id'] ?>"><b><?php if ($l['visto_em'] === null): ?><span class="ponto" title="Ainda não visto"></span><?php endif; ?><?= h($l['nome']) ?></b><small><?= h($l['empresa']) ?> · <?= h($l['segmento']) ?></small></a></span>
          <span role="cell"><?= crm_selo_pontos((int) $l['pontuacao']) ?></span>
          <span role="cell"><?= crm_selo_qualificacao($l['qualificacao']) ?></span>
          <span role="cell"><?= crm_selo_status($l['status']) ?></span>
          <span class="c-acao<?= $atrasada ? ' atrasada' : '' ?>" role="cell"><?= $l['proxima_acao'] !== '' ? h(($l['proxima_acao_em'] ? crm_data($l['proxima_acao_em']) . ' · ' : '') . $l['proxima_acao']) : '<span class="muted">Sem próxima ação</span>' ?></span>
          <span class="c-acoes" role="cell">
            <a class="mini" href="<?= h(crm_link_whatsapp($l['whatsapp'])) ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a>
            <a class="mini mini-claro" href="lead.php?id=<?= (int) $l['id'] ?>">Abrir</a>
          </span>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if ($paginas > 1): ?>
      <nav class="paginas" aria-label="Páginas">
        <?php if ($pagina > 1): ?><a href="<?= h(link_filtro(['p' => $pagina - 1])) ?>">← Anterior</a><?php endif; ?>
        <span>Página <?= $pagina ?> de <?= $paginas ?></span>
        <?php if ($pagina < $paginas): ?><a href="<?= h(link_filtro(['p' => $pagina + 1])) ?>">Próxima →</a><?php endif; ?>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
</main>
<?php crm_rodape();
