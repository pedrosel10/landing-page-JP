<?php
/* Ficha do lead: dados completos, status, anotações e exclusão */

require __DIR__ . '/../inc/crm.php';
$usuario = crm_exigir_login();
$db = crm_db();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$st = $db->prepare('SELECT * FROM leads WHERE id = ?');
$st->execute([$id]);
$lead = $st->fetch();
if (!$lead) {
    http_response_code(404);
    crm_topo('Lead não encontrado', $usuario);
    echo '<main class="wrap"><div class="vazio"><b>Lead não encontrado.</b><p><a href="index.php">Voltar para a lista</a></p></div></main>';
    crm_rodape();
    exit;
}

/* ---------- ações (POST com token) */
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    crm_verificar_post();
    $acao = (string) ($_POST['acao'] ?? '');

    if ($acao === 'status') {
        $novo = (string) ($_POST['status'] ?? '');
        if (isset(CRM_STATUS[$novo]) && $novo !== $lead['status']) {
            $db->prepare('UPDATE leads SET status = ?, atualizado_em = ? WHERE id = ?')->execute([$novo, crm_agora(), $id]);
            $db->prepare('INSERT INTO notas (lead_id, criado_em, autor, texto) VALUES (?, ?, ?, ?)')
                ->execute([$id, crm_agora(), $usuario['usuario'], 'Status: ' . (CRM_STATUS[$lead['status']] ?? $lead['status']) . ' → ' . CRM_STATUS[$novo]]);
            crm_aviso('Status atualizado.');
        }
    } elseif ($acao === 'nota') {
        $texto = crm_texto($_POST['texto'] ?? '', 4000, true);
        if ($texto !== '') {
            $db->prepare('INSERT INTO notas (lead_id, criado_em, autor, texto) VALUES (?, ?, ?, ?)')
                ->execute([$id, crm_agora(), $usuario['usuario'], $texto]);
            $db->prepare('UPDATE leads SET atualizado_em = ? WHERE id = ?')->execute([crm_agora(), $id]);
            crm_aviso('Anotação salva.');
        }
    } elseif ($acao === 'excluir') {
        if (($_POST['confirmar'] ?? '') === 'EXCLUIR') {
            $db->prepare('DELETE FROM leads WHERE id = ?')->execute([$id]);
            crm_aviso('Lead excluído.');
            header('Location: index.php');
            exit;
        }
        crm_aviso('Para excluir, digite EXCLUIR no campo de confirmação.', 'erro');
    }
    header('Location: lead.php?id=' . $id);
    exit;
}

$st = $db->prepare('SELECT * FROM notas WHERE lead_id = ? ORDER BY criado_em DESC, id DESC');
$st->execute([$id]);
$notas = $st->fetchAll();

$primeiroNome = explode(' ', $lead['nome'])[0];
$mensagem = "Olá, {$primeiroNome}! Aqui é da J.P.Growth. Recebemos seu contato pelo site e queria entender melhor o momento da {$lead['empresa']}. Podemos conversar?";
$site = $lead['site_instagram'];
$linkSite = '';
if (preg_match('~^@?([A-Za-z0-9._]{1,30})$~', $site, $m) && !str_contains($site, '.')) {
    $linkSite = 'https://instagram.com/' . $m[1];
} elseif (preg_match('~^(https?://)?([a-z0-9-]+\.)+[a-z]{2,}(/\S*)?$~i', $site)) {
    $linkSite = preg_match('~^https?://~i', $site) ? $site : 'https://' . $site;
}

crm_topo($lead['nome'], $usuario);
?>
<main class="wrap">
  <a class="voltar" href="index.php">← Todos os leads</a>

  <div class="ficha-cabeca">
    <div>
      <h1><?= h($lead['nome']) ?></h1>
      <p class="muted"><?= h($lead['empresa']) ?> · recebido em <?= h(crm_data($lead['criado_em'])) ?></p>
      <div class="selos"><?= crm_selo_status($lead['status']) ?> <?= crm_selo_perfil($lead['perfil']) ?></div>
    </div>
    <div class="ficha-acoes">
      <a class="btn btn-primario" href="<?= h(crm_link_whatsapp($lead['whatsapp'], $mensagem)) ?>" target="_blank" rel="noopener noreferrer">Chamar no WhatsApp</a>
      <a class="btn btn-claro" href="mailto:<?= h(rawurlencode($lead['email'])) ?>">Enviar e-mail</a>
    </div>
  </div>

  <div class="ficha">
    <section class="cartao">
      <h2>Status do atendimento</h2>
      <form method="post" class="status-form">
        <?= crm_csrf_campo() ?>
        <input type="hidden" name="acao" value="status">
        <input type="hidden" name="id" value="<?= (int) $id ?>">
        <select name="status" aria-label="Status">
          <?php foreach (CRM_STATUS as $valor => $rotulo): ?>
            <option value="<?= h($valor) ?>"<?= $lead['status'] === $valor ? ' selected' : '' ?>><?= h($rotulo) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-escuro">Salvar</button>
      </form>

      <h2>Dados do formulário</h2>
      <dl class="dados">
        <div><dt>WhatsApp</dt><dd><?= h($lead['whatsapp']) ?></dd></div>
        <div><dt>E-mail</dt><dd><?= h($lead['email']) ?></dd></div>
        <div><dt>Empresa</dt><dd><?= h($lead['empresa']) ?></dd></div>
        <div><dt>Site / Instagram</dt><dd><?php if ($linkSite): ?><a href="<?= h($linkSite) ?>" target="_blank" rel="noopener noreferrer nofollow"><?= h($site) ?></a><?php else: ?><?= h($site) ?><?php endif; ?></dd></div>
        <div><dt>Segmento</dt><dd><?= h($lead['segmento']) ?></dd></div>
        <div><dt>Investimento mensal</dt><dd><?= h(CRM_INVESTIMENTO[$lead['investimento']] ?? $lead['investimento']) ?></dd></div>
        <div class="largo"><dt>Principal desafio</dt><dd><?= h(CRM_DESAFIO[$lead['desafio']] ?? $lead['desafio']) ?></dd></div>
        <div class="largo"><dt>O que quer melhorar</dt><dd class="texto-livre"><?= $lead['objetivo'] !== '' ? nl2br(h($lead['objetivo'])) : '<span class="muted">Não preenchido</span>' ?></dd></div>
      </dl>

      <h2>Origem</h2>
      <dl class="dados dados-origem">
        <div><dt>utm_source</dt><dd><?= h($lead['utm_source'] ?: '—') ?></dd></div>
        <div><dt>utm_medium</dt><dd><?= h($lead['utm_medium'] ?: '—') ?></dd></div>
        <div><dt>utm_campaign</dt><dd><?= h($lead['utm_campaign'] ?: '—') ?></dd></div>
        <div><dt>utm_content</dt><dd><?= h($lead['utm_content'] ?: '—') ?></dd></div>
        <div><dt>utm_term</dt><dd><?= h($lead['utm_term'] ?: '—') ?></dd></div>
        <div><dt>Headline</dt><dd><?= $lead['variante'] === 'b' ? 'Variante B' : 'Variante A' ?></dd></div>
      </dl>
    </section>

    <section class="cartao">
      <h2>Anotações</h2>
      <form method="post" class="nota-form">
        <?= crm_csrf_campo() ?>
        <input type="hidden" name="acao" value="nota">
        <input type="hidden" name="id" value="<?= (int) $id ?>">
        <textarea name="texto" rows="3" maxlength="4000" required placeholder="Ex.: liguei, pediu proposta para a próxima semana"></textarea>
        <button type="submit" class="btn btn-escuro">Adicionar anotação</button>
      </form>
      <?php if (!$notas): ?>
        <p class="muted">Nenhuma anotação ainda.</p>
      <?php else: ?>
        <ol class="notas">
          <?php foreach ($notas as $n): ?>
            <li><div class="nota-meta"><?= h($n['autor']) ?> · <?= h(crm_data($n['criado_em'])) ?></div><div class="texto-livre"><?= nl2br(h($n['texto'])) ?></div></li>
          <?php endforeach; ?>
        </ol>
      <?php endif; ?>

      <details class="perigo">
        <summary>Excluir este lead</summary>
        <form method="post" data-confirmar="Excluir este lead definitivamente?">
          <?= crm_csrf_campo() ?>
          <input type="hidden" name="acao" value="excluir">
          <input type="hidden" name="id" value="<?= (int) $id ?>">
          <p class="muted">A exclusão apaga o lead e as anotações. Os backups diários guardam uma cópia por 30 dias.</p>
          <label>Digite EXCLUIR para confirmar<input name="confirmar" autocomplete="off" required></label>
          <button type="submit" class="btn btn-perigo">Excluir lead</button>
        </form>
      </details>
    </section>
  </div>
</main>
<?php crm_rodape();
