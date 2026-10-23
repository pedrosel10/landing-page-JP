<?php
/* Lista de leads com indicadores, busca e filtros */

require __DIR__ . '/../inc/crm.php';
$usuario = crm_exigir_login();
$db = crm_db();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    crm_verificar_post();
    if (($_POST['acao'] ?? '') === 'marcar_vistos') {
        $n = $db->prepare('UPDATE leads SET visto_em = ? WHERE visto_em IS NULL');
        $n->execute([crm_agora()]);
        crm_aviso($n->rowCount() === 1 ? '1 lead marcado como visto.' : $n->rowCount() . ' leads marcados como vistos.');
    }
    header('Location: index.php');
    exit;
}

/* Novos ainda não abertos por ninguém */
$naoVistos = $db->query("SELECT id, criado_em, nome, empresa, whatsapp, segmento, investimento, perfil
    FROM leads WHERE visto_em IS NULL ORDER BY criado_em DESC, id DESC LIMIT 12")->fetchAll();
$totalNaoVistos = crm_total_nao_vistos();

/* ---------- filtros (GET) */
$busca  = crm_texto($_GET['q'] ?? '', 80);
$status = (string) ($_GET['status'] ?? '');
$perfil = (string) ($_GET['perfil'] ?? '');
$status = isset(CRM_STATUS[$status]) ? $status : '';
$perfil = in_array($perfil, ['compativel', 'abaixo_do_perfil'], true) ? $perfil : '';
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
if ($perfil !== '') {
    $where[] = 'perfil = ?';
    $args[] = $perfil;
}
$sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$st = $db->prepare("SELECT COUNT(*) FROM leads $sqlWhere");
$st->execute($args);
$total = (int) $st->fetchColumn();
$paginas = max(1, (int) ceil($total / $porPagina));
$pagina = min($pagina, $paginas);

$st = $db->prepare("SELECT id, criado_em, nome, empresa, whatsapp, email, segmento, investimento, perfil, status, visto_em
    FROM leads $sqlWhere ORDER BY criado_em DESC, id DESC LIMIT $porPagina OFFSET " . (($pagina - 1) * $porPagina));
$st->execute($args);
$leads = $st->fetchAll();

/* ---------- indicadores (sempre do total, sem filtro) */
$inicioMes = (new DateTimeImmutable('first day of this month 00:00', new DateTimeZone('America/Sao_Paulo')))->format('Y-m-d H:i:s');
$st = $db->prepare("SELECT
    COUNT(*) AS total,
    SUM(status = 'novo') AS novos,
    SUM(perfil = 'compativel') AS fit,
    SUM(status IN ('reuniao','proposta')) AS andamento,
    SUM(status = 'ganho') AS ganhos,
    SUM(criado_em >= ?) AS mes
    FROM leads");
$st->execute([$inicioMes]);
$k = $st->fetch();

function link_filtro(array $mudar): string
{
    $atual = array_filter([
        'q' => $_GET['q'] ?? '', 'status' => $_GET['status'] ?? '', 'perfil' => $_GET['perfil'] ?? '',
    ], 'strlen');
    $novo = array_filter(array_merge($atual, $mudar), fn($v) => $v !== '' && $v !== null);
    return 'index.php' . ($novo ? '?' . http_build_query($novo) : '');
}

crm_topo('Leads', $usuario);
?>
<main class="wrap">
  <div class="cabeca">
    <div>
      <h1>Leads</h1>
      <p class="muted">Contatos recebidos pelo formulário da landing page.</p>
    </div>
    <a class="btn btn-claro" href="exportar.php">Exportar CSV</a>
  </div>

  <?php if ($naoVistos): ?>
  <section class="naovistos" aria-label="Novos ainda não vistos">
    <div class="naovistos-cab">
      <h2><span class="ponto"></span>Novos, ainda não vistos <span class="contador"><?= $totalNaoVistos ?></span></h2>
      <form method="post"><?= crm_csrf_campo() ?><input type="hidden" name="acao" value="marcar_vistos"><button type="submit" class="linkbtn">Marcar todos como vistos</button></form>
    </div>
    <div class="naovistos-grade">
      <?php foreach ($naoVistos as $n): ?>
        <a class="nv" href="lead.php?id=<?= (int) $n['id'] ?>">
          <span class="nv-quando"><?= h(crm_tempo_relativo($n['criado_em'])) ?></span>
          <b><?= h($n['nome']) ?></b>
          <small><?= h($n['empresa']) ?></small>
          <span class="nv-info"><?= h($n['segmento']) ?> · <?= h(CRM_INVESTIMENTO[$n['investimento']] ?? $n['investimento']) ?></span>
          <span class="nv-rodape"><?= crm_selo_perfil($n['perfil']) ?><span class="nv-abrir">Abrir →</span></span>
        </a>
      <?php endforeach; ?>
    </div>
    <?php if ($totalNaoVistos > count($naoVistos)): ?><p class="muted">Mostrando os <?= count($naoVistos) ?> mais recentes de <?= $totalNaoVistos ?> não vistos.</p><?php endif; ?>
  </section>
  <?php endif; ?>

  <section class="kpis" aria-label="Indicadores">
    <a class="kpi" href="index.php"><span>Total</span><b><?= (int) $k['total'] ?></b><small><?= (int) $k['mes'] ?> este mês</small></a>
    <a class="kpi kpi-destaque" href="<?= h(link_filtro(['status' => 'novo', 'p' => null])) ?>"><span>Novos</span><b><?= (int) $k['novos'] ?></b><small>aguardando contato</small></a>
    <a class="kpi" href="<?= h(link_filtro(['perfil' => 'compativel', 'p' => null])) ?>"><span>No perfil</span><b><?= (int) $k['fit'] ?></b><small>R$ 2 mil+ por mês</small></a>
    <a class="kpi" href="<?= h(link_filtro(['status' => 'reuniao', 'p' => null])) ?>"><span>Em andamento</span><b><?= (int) $k['andamento'] ?></b><small>reunião ou proposta</small></a>
    <a class="kpi" href="<?= h(link_filtro(['status' => 'ganho', 'p' => null])) ?>"><span>Fechados</span><b><?= (int) $k['ganhos'] ?></b><small>clientes</small></a>
  </section>

  <form class="filtros" method="get" action="index.php">
    <input type="search" name="q" value="<?= h($busca) ?>" placeholder="Buscar por nome, empresa, e-mail ou WhatsApp" aria-label="Buscar">
    <select name="status" aria-label="Status">
      <option value="">Todos os status</option>
      <?php foreach (CRM_STATUS as $valor => $rotulo): ?>
        <option value="<?= h($valor) ?>"<?= $status === $valor ? ' selected' : '' ?>><?= h($rotulo) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="perfil" aria-label="Perfil">
      <option value="">Todos os perfis</option>
      <option value="compativel"<?= $perfil === 'compativel' ? ' selected' : '' ?>>No perfil</option>
      <option value="abaixo_do_perfil"<?= $perfil === 'abaixo_do_perfil' ? ' selected' : '' ?>>Abaixo do perfil</option>
    </select>
    <button type="submit" class="btn btn-escuro">Filtrar</button>
    <?php if ($busca !== '' || $status !== '' || $perfil !== ''): ?><a class="limpar" href="index.php">Limpar</a><?php endif; ?>
  </form>

  <?php if (!$leads): ?>
    <div class="vazio">
      <?php if ($k['total'] == 0): ?>
        <b>Nenhum lead ainda.</b><p>Assim que alguém preencher o formulário da landing page, o contato aparece aqui.</p>
      <?php else: ?>
        <b>Nenhum lead com esses filtros.</b><p><a href="index.php">Ver todos</a></p>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <p class="contagem"><?= $total ?> lead<?= $total === 1 ? '' : 's' ?><?= $sqlWhere ? ' encontrados' : '' ?></p>
    <div class="tabela" role="table" aria-label="Leads">
      <div class="linha linha-cab" role="row">
        <span role="columnheader">Recebido</span><span role="columnheader">Lead</span><span role="columnheader">Segmento</span>
        <span role="columnheader">Investimento</span><span role="columnheader">Perfil</span><span role="columnheader">Status</span><span role="columnheader"></span>
      </div>
      <?php foreach ($leads as $l): ?>
        <div class="linha<?= $l['visto_em'] === null ? ' linha-nova' : '' ?>" role="row">
          <span class="c-data" role="cell"><?= h(crm_data($l['criado_em'])) ?></span>
          <span class="c-lead" role="cell"><a href="lead.php?id=<?= (int) $l['id'] ?>"><b><?php if ($l['visto_em'] === null): ?><span class="ponto" title="Ainda não visto"></span><?php endif; ?><?= h($l['nome']) ?></b><small><?= h($l['empresa']) ?></small></a></span>
          <span class="c-seg" role="cell"><?= h($l['segmento']) ?></span>
          <span class="c-inv" role="cell"><?= h(CRM_INVESTIMENTO[$l['investimento']] ?? $l['investimento']) ?></span>
          <span role="cell"><?= crm_selo_perfil($l['perfil']) ?></span>
          <span role="cell"><?= crm_selo_status($l['status']) ?></span>
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
