<?php
/* Propostas: a versão genérica (sem dados de cliente, com link próprio) e a
   lista das propostas geradas para os leads, com a leitura de cada uma. */

require __DIR__ . '/../inc/crm.php';

/* ?abrir=1 mostra a versão genérica para apresentar: só para quem está logado,
   não conta abertura e funciona mesmo com o link desativado */
if (isset($_GET['abrir'])) {
    if (!crm_usuario_logado()) {
        header('Location: login.php');
        exit;
    }
    $dados = crm_proposta_geral_dados(crm_proposta_geral());
    $dados['rastreio'] = null;
    crm_proposta_mostrar($dados, 'Proposta · J.P.Growth');
    exit;
}

$usuario = crm_exigir_login();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    crm_verificar_post();
    $acao = (string) ($_POST['acao'] ?? '');
    if ($acao === 'salvar') {
        crm_proposta_geral_salvar($_POST);
        crm_aviso('Versão genérica salva. O link continua o mesmo.');
    } elseif ($acao === 'link') {
        crm_proposta_geral_novo_link();
        crm_aviso('Link novo gerado. O link antigo da versão genérica não abre mais.');
    } elseif ($acao === 'ativa') {
        $ativa = ($_POST['ativa'] ?? '') === '1';
        crm_proposta_geral_ativar($ativa);
        crm_aviso($ativa ? 'Link da versão genérica reativado.' : 'Link da versão genérica desativado.');
    }
    header('Location: proposta-generica.php');
    exit;
}

$g = crm_proposta_geral_garantir();
$url = crm_proposta_url($g['token']);
$mensagem = "Olá! Aqui está a proposta da J.P.Growth: {$url}";
$enviadas = crm_db()->query('SELECT p.*, l.id AS lid, l.nome AS lead_nome, l.empresa AS lead_empresa, l.status AS lead_status
    FROM propostas p JOIN leads l ON l.id = p.lead_id
    ORDER BY COALESCE(p.ultima_visita, p.atualizado_em) DESC LIMIT 200')->fetchAll();

crm_topo('Propostas', $usuario);
?>
<main class="wrap">
  <div class="cabeca">
    <div>
      <h1>Propostas</h1>
      <p class="muted">A versão genérica, para quem não tem ficha, e as propostas geradas para cada lead.</p>
    </div>
  </div>

  <div class="prop-pagina">
    <section class="cartao proposta-cartao">
      <h2>Versão genérica</h2>
      <p class="prop-intro">A mesma página de proposta, sem a seção "O seu cenário", sem nome de empresa e sem a frase "Você comentou que…". Serve para indicações, eventos e quem não passou pelo formulário.</p>
      <?php if ($g['ativa']): ?>
        <div class="prop-link">
          <code><?= h($url) ?></code>
          <button type="button" class="btn btn-claro" data-copiar="<?= h($url) ?>">Copiar link</button>
        </div>
      <?php else: ?>
        <p class="prop-off">Link desativado: quem abrir vê "proposta não encontrada". Vocês ainda conseguem apresentar pelo botão abaixo.</p>
      <?php endif; ?>
      <div class="prop-acoes">
        <a class="btn btn-escuro" href="proposta-generica.php?abrir=1" target="_blank" rel="noopener noreferrer">Abrir para apresentar</a>
        <?php if ($g['ativa']): ?>
          <a class="btn btn-primario" href="<?= h('https://wa.me/?text=' . rawurlencode($mensagem)) ?>" target="_blank" rel="noopener noreferrer">Enviar pelo WhatsApp</a>
        <?php endif; ?>
      </div>
      <?php if ($g['visitas'] > 0): ?>
        <dl class="mini-dados prop-leitura">
          <div><dt>Aberturas</dt><dd><?= $g['visitas'] ?></dd></div>
          <div><dt>Última vez</dt><dd><?= h(crm_tempo_relativo($g['ultima_visita'])) ?></dd></div>
          <div><dt>Tempo somado</dt><dd><?= h(crm_proposta_tempo($g['tempo_total'])) ?></dd></div>
        </dl>
        <p class="dica-pe">Soma de todo mundo que recebeu o link genérico. O "Abrir para apresentar" não conta.</p>
      <?php else: ?>
        <p class="prop-nao-abriu">Ninguém abriu o link genérico ainda.</p>
      <?php endif; ?>

      <details class="prop-editar">
        <summary>Ajustes da versão genérica</summary>
        <form method="post" class="form prop-form">
          <?= crm_csrf_campo() ?>
          <input type="hidden" name="acao" value="salvar">
          <label>Case em destaque
            <select name="caso">
              <?php foreach (CRM_PROPOSTA_CASOS as $chave => $rotulo): ?>
                <option value="<?= h($chave) ?>"<?= $g['caso'] === $chave ? ' selected' : '' ?>><?= h($rotulo) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <div class="duas">
            <label>Assessoria (R$ por mês)<input name="assessoria" inputmode="numeric" value="<?= (int) $g['assessoria'] ?>"></label>
            <label>Verba mínima (R$ por mês)<input name="verba_minima" inputmode="numeric" value="<?= (int) $g['verba_minima'] ?>"></label>
          </div>
          <label>Prazo para contrato e acessos<input name="prazo_contrato" maxlength="40" value="<?= h($g['prazo_contrato']) ?>"></label>
          <button type="submit" class="btn btn-escuro">Salvar</button>
        </form>
      </details>

      <div class="prop-rodape">
        <form method="post" data-confirmar="Gerar um link novo? Quem já recebeu o link genérico não vai conseguir abrir.">
          <?= crm_csrf_campo() ?>
          <input type="hidden" name="acao" value="link">
          <button type="submit" class="linkbtn">Gerar link novo</button>
        </form>
        <form method="post">
          <?= crm_csrf_campo() ?>
          <input type="hidden" name="acao" value="ativa">
          <input type="hidden" name="ativa" value="<?= $g['ativa'] ? '0' : '1' ?>">
          <button type="submit" class="linkbtn"><?= $g['ativa'] ? 'Desativar link' : 'Reativar link' ?></button>
        </form>
        <a class="linkbtn" href="proposta-exemplo.php" target="_blank" rel="noopener noreferrer">Ver o modelo com um cliente de exemplo</a>
      </div>
    </section>

    <section class="cartao">
      <h2>Propostas dos leads</h2>
      <?php if (!$enviadas): ?>
        <p class="muted">Nenhuma proposta gerada ainda. Gere pela ficha do lead, no bloco "Proposta".</p>
      <?php else: ?>
        <div class="tabela tabela-prop" role="table" aria-label="Propostas dos leads">
          <div class="linha linha-cab" role="row">
            <span role="columnheader">Lead</span><span role="columnheader">Etapa</span><span role="columnheader">Aberturas</span>
            <span role="columnheader">Última vez</span><span role="columnheader">Até onde leu</span>
          </div>
          <?php foreach ($enviadas as $p): ?>
            <div class="linha" role="row">
              <span class="c-lead" role="cell"><a href="lead.php?id=<?= (int) $p['lid'] ?>#proposta"><b><?= h($p['lead_nome']) ?></b><small><?= h($p['lead_empresa']) ?><?= (int) $p['ativa'] === 1 ? '' : ' · link desativado' ?></small></a></span>
              <span role="cell"><?= crm_selo_status($p['lead_status']) ?></span>
              <span class="c-num" role="cell"><?= (int) $p['visitas'] ?></span>
              <span class="c-seg" role="cell"><?= $p['ultima_visita'] ? h(crm_tempo_relativo($p['ultima_visita'])) : '<span class="muted">não abriu</span>' ?></span>
              <span class="c-seg" role="cell"><?= (int) $p['visitas'] > 0 ? h(crm_proposta_leitura($p)) : '' ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </div>
</main>
<?php crm_rodape();
