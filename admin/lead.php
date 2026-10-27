<?php
/* Ficha do lead: etapa do funil, próxima ação, qualificação MQL/SQL,
   pontuação do formulário, dados, anotações e integrações */

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
    echo '<main class="wrap"><div class="vazio"><b>Lead não encontrado.</b><p><a href="index.php">Voltar ao funil</a></p></div></main>';
    crm_rodape();
    exit;
}

/* datetime-local ("2026-10-23T14:30") → "2026-10-23 14:30:00" */
function crm_data_formulario($valor): ?string
{
    $valor = (string) $valor;
    return preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $valor) ? str_replace('T', ' ', $valor) . ':00' : null;
}
function para_input(?string $data): string
{
    return $data ? str_replace(' ', 'T', substr($data, 0, 16)) : '';
}

/* ---------- ações (POST com token) */
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    crm_verificar_post();
    $acao = (string) ($_POST['acao'] ?? '');
    $volta = 'lead.php?id=' . $id;

    if ($acao === 'etapa') {
        $etapa = (string) ($_POST['etapa'] ?? '');
        $erro = crm_mudar_etapa($id, $etapa, $usuario['usuario'], [
            'valor' => $_POST['valor'] ?? '', 'motivo' => $_POST['motivo'] ?? '',
        ]);
        if ($erro !== '') {
            crm_aviso($erro, 'erro');
            $volta .= '&fechar=' . rawurlencode($etapa) . '#fechar';
        } else {
            crm_aviso('Etapa atualizada: ' . CRM_STATUS[$etapa] . '.');
        }
    } elseif ($acao === 'proxima') {
        $db->prepare('UPDATE leads SET proxima_acao = ?, proxima_acao_em = ?, reuniao_em = ?, atualizado_em = ? WHERE id = ?')
            ->execute([crm_texto($_POST['proxima'] ?? '', 160), crm_data_formulario($_POST['proxima_em'] ?? ''),
                crm_data_formulario($_POST['reuniao_em'] ?? ''), crm_agora(), $id]);
        crm_aviso('Próxima ação salva.');
    } elseif ($acao === 'qualificacao') {
        $sets = [];
        $args = [];
        foreach (CRM_CRITERIOS_SQL as $campo => $info) {
            $sets[] = "$campo = ?";
            $args[] = isset($_POST[$campo]) ? 1 : 0;
        }
        $args[] = $id;
        $db->prepare('UPDATE leads SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($args);
        crm_atualizar_qualificacao($id);
        crm_aviso('Qualificação atualizada.');
    } elseif ($acao === 'nota') {
        $texto = crm_texto($_POST['texto'] ?? '', 4000, true);
        if ($texto !== '') {
            crm_nota($id, $usuario['usuario'], $texto);
            $db->prepare('UPDATE leads SET atualizado_em = ? WHERE id = ?')->execute([crm_agora(), $id]);
            crm_aviso('Anotação salva.');
        }
    } elseif ($acao === 'nao_visto') {
        $db->prepare('UPDATE leads SET visto_em = NULL WHERE id = ?')->execute([$id]);
        /* Neste navegador ele não é remarcado sozinho: fica esperando o sócio ver */
        $_SESSION['nao_auto'] = array_slice(array_values(array_unique(array_merge($_SESSION['nao_auto'] ?? [], [$id]))), -100);
        crm_aviso('Lead marcado como não visto.');
        $volta = 'index.php';
    } elseif ($acao === 'excluir') {
        if (($_POST['confirmar'] ?? '') === 'EXCLUIR') {
            $db->prepare('DELETE FROM leads WHERE id = ?')->execute([$id]);
            $db->prepare("UPDATE envios SET status = 'cancelado' WHERE lead_id = ? AND status IN ('pendente','erro')")->execute([$id]);
            crm_aviso('Lead excluído.');
            header('Location: index.php');
            exit;
        }
        crm_aviso('Para excluir, digite EXCLUIR no campo de confirmação.', 'erro');
    }
    crm_processar_envios(2);
    header('Location: ' . $volta);
    exit;
}

/* Abrir a ficha marca o lead como visto */
if (!empty($_SESSION['nao_auto'])) {
    $_SESSION['nao_auto'] = array_values(array_diff($_SESSION['nao_auto'], [$id]));
}
if ($lead['visto_em'] === null) {
    $lead['visto_em'] = crm_agora();
    $db->prepare('UPDATE leads SET visto_em = ? WHERE id = ? AND visto_em IS NULL')->execute([$lead['visto_em'], $id]);
}

$st = $db->prepare('SELECT * FROM notas WHERE lead_id = ? ORDER BY criado_em DESC, id DESC');
$st->execute([$id]);
$notas = $st->fetchAll();
$st = $db->prepare('SELECT * FROM envios WHERE lead_id = ? ORDER BY id');
$st->execute([$id]);
$envios = $st->fetchAll();

$pontos = crm_pontuar($lead);
$fechar = (string) ($_GET['fechar'] ?? '');
$agora = date('Y-m-d H:i:s');
$aberto = in_array($lead['status'], CRM_ETAPAS_ABERTAS, true);
$acaoAtrasada = $aberto && $lead['proxima_acao_em'] && $lead['proxima_acao_em'] < $agora;

$primeiroNome = explode(' ', $lead['nome'])[0];
$mensagem = "Olá, {$primeiroNome}! Aqui é da J.P.Growth. Recebemos seu contato pelo site e queria entender melhor o momento da {$lead['empresa']}. Podemos conversar?";
$site = $lead['site_instagram'];
$linkSite = '';
if (preg_match('~^@?([A-Za-z0-9._]{1,30})$~', $site, $m) && strpos($site, '.') === false) {
    $linkSite = 'https://instagram.com/' . $m[1];
} elseif (preg_match('~^(https?://)?([a-z0-9-]+\.)+[a-z]{2,}(/\S*)?$~i', $site)) {
    $linkSite = preg_match('~^https?://~i', $site) ? $site : 'https://' . $site;
}
$ordem = array_flip(CRM_ETAPAS_ABERTAS);
$posAtual = $ordem[$lead['status']] ?? 99;

crm_topo($lead['nome'], $usuario);
?>
<main class="wrap">
  <a class="voltar" href="index.php">← Funil</a>

  <div class="ficha-cabeca">
    <div>
      <h1><?= h($lead['nome']) ?></h1>
      <p class="muted"><?= h($lead['empresa']) ?> · recebido em <?= h(crm_data($lead['criado_em'])) ?></p>
      <div class="selos">
        <?= crm_selo_status($lead['status']) ?>
        <?= crm_selo_qualificacao($lead['qualificacao']) ?>
        <?= crm_selo_pontos((int) $lead['pontuacao']) ?>
      </div>
    </div>
    <div class="ficha-acoes">
      <a class="btn btn-primario" href="<?= h(crm_link_whatsapp($lead['whatsapp'], $mensagem)) ?>" target="_blank" rel="noopener noreferrer">Chamar no WhatsApp</a>
      <a class="btn btn-claro" href="mailto:<?= h(rawurlencode($lead['email'])) ?>">Enviar e-mail</a>
    </div>
  </div>

  <section class="cartao etapas-cartao">
    <h2>Etapa do funil</h2>
    <form method="post" class="etapas">
      <?= crm_csrf_campo() ?>
      <input type="hidden" name="acao" value="etapa">
      <input type="hidden" name="id" value="<?= (int) $id ?>">
      <?php foreach (CRM_ETAPAS_ABERTAS as $i => $etapa): ?>
        <button type="submit" name="etapa" value="<?= h($etapa) ?>" class="etapa<?= $lead['status'] === $etapa ? ' atual' : '' ?><?= $i < $posAtual && $aberto ? ' feita' : '' ?>" title="<?= h(CRM_ETAPAS_INFO[$etapa]['dica']) ?>">
          <span><?= $i + 1 ?></span><?= h(CRM_STATUS[$etapa]) ?>
        </button>
      <?php endforeach; ?>
    </form>
    <p class="muted etapa-dica"><?= h(CRM_ETAPAS_INFO[$lead['status']]['dica']) ?>
      <?php if ($lead['status'] === 'ganho'): ?> Valor: <b>R$ <?= number_format((float) $lead['valor_mensal'], 2, ',', '.') ?>/mês</b>.<?php endif; ?>
      <?php if ($lead['status'] === 'perdido'): ?> Motivo: <b><?= h(CRM_MOTIVOS_PERDA[$lead['motivo_perda']] ?? '') ?></b>.<?php endif; ?>
    </p>

    <div class="fechamento" id="fechar">
      <details class="fechar fechar-ganho"<?= $fechar === 'ganho' ? ' open' : '' ?>>
        <summary>Venda realizada</summary>
        <form method="post">
          <?= crm_csrf_campo() ?>
          <input type="hidden" name="acao" value="etapa"><input type="hidden" name="etapa" value="ganho"><input type="hidden" name="id" value="<?= (int) $id ?>">
          <label>Valor mensal do contrato (R$)<input name="valor" inputmode="decimal" placeholder="Ex.: 2.500,00" required value="<?= $lead['valor_mensal'] ? h(number_format((float) $lead['valor_mensal'], 2, ',', '.')) : '' ?>"></label>
          <button type="submit" class="btn btn-ganho">Registrar venda</button>
        </form>
      </details>
      <details class="fechar fechar-perdido"<?= $fechar === 'perdido' ? ' open' : '' ?>>
        <summary>Marcar como perdido</summary>
        <form method="post">
          <?= crm_csrf_campo() ?>
          <input type="hidden" name="acao" value="etapa"><input type="hidden" name="etapa" value="perdido"><input type="hidden" name="id" value="<?= (int) $id ?>">
          <label>Motivo
            <select name="motivo" required>
              <option value="">Escolha o motivo</option>
              <?php foreach (CRM_MOTIVOS_PERDA as $valor => $rotulo): ?>
                <option value="<?= h($valor) ?>"<?= $lead['motivo_perda'] === $valor ? ' selected' : '' ?>><?= h($rotulo) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <button type="submit" class="btn btn-perigo">Registrar perda</button>
        </form>
      </details>
    </div>
  </section>

  <div class="ficha">
    <div class="ficha-col">
      <section class="cartao<?= $acaoAtrasada ? ' cartao-alerta' : '' ?>">
        <h2>Próxima ação<?= $acaoAtrasada ? ' · atrasada' : '' ?></h2>
        <form method="post" class="form proxima-form">
          <?= crm_csrf_campo() ?>
          <input type="hidden" name="acao" value="proxima"><input type="hidden" name="id" value="<?= (int) $id ?>">
          <label>O que fazer<input name="proxima" maxlength="160" value="<?= h($lead['proxima_acao']) ?>" placeholder="Ex.: ligar para confirmar a reunião"></label>
          <div class="duas">
            <label>Quando<input type="datetime-local" name="proxima_em" value="<?= h(para_input($lead['proxima_acao_em'])) ?>"></label>
            <label>Data da reunião<input type="datetime-local" name="reuniao_em" value="<?= h(para_input($lead['reuniao_em'])) ?>"></label>
          </div>
          <button type="submit" class="btn btn-escuro">Salvar</button>
        </form>
        <dl class="mini-dados">
          <div><dt>1º contato</dt><dd><?= $lead['primeiro_contato_em'] ? h(crm_duracao($lead['criado_em'], $lead['primeiro_contato_em'])) . ' após chegar' : 'ainda não feito' ?></dd></div>
          <div><dt>Nesta etapa há</dt><dd><?= crm_dias_desde($lead['etapa_desde']) ?> dia(s)</dd></div>
        </dl>
      </section>

      <section class="cartao">
        <h2>Qualificação</h2>
        <ol class="escada" aria-label="Nível de qualificação">
          <?php foreach (CRM_QUALIFICACAO as $q => $nome): ?>
            <li class="escada-<?= h($q) ?><?= $lead['qualificacao'] === $q ? ' atual' : '' ?>"<?= $lead['qualificacao'] === $q ? ' aria-current="step"' : '' ?>><?= h($nome) ?></li>
          <?php endforeach; ?>
        </ol>
        <p class="qual-status"><?= h(CRM_QUALIFICACAO_DICA[$lead['qualificacao']] ?? '') ?><?= $lead['qualificacao'] === 'sql' && $lead['sql_em'] ? ' SQL desde ' . h(crm_data($lead['sql_em'])) . '.' : '' ?></p>

        <?php if ($pontos['perfil']): ?>
          <h3 class="sub">Perfil ideal pelo formulário</h3>
          <ul class="perfil-ideal">
            <?php foreach ($pontos['perfil'] as $c): ?>
              <li class="pi-<?= h($c[1]) ?>"><span class="pi-ic" aria-hidden="true"><?= ['ok' => '✓', 'parcial' => '~', 'nao' => '✕', 'sem' => '?'][$c[1]] ?></span><span><b><?= h($c[0]) ?></b><small><?= h($c[2]) ?></small></span></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
        <p class="muted regra"><?= h($pontos['regra']) ?></p>

        <h3 class="sub">Confirmado na conversa</h3>
        <form method="post" class="checklist">
          <?= crm_csrf_campo() ?>
          <input type="hidden" name="acao" value="qualificacao"><input type="hidden" name="id" value="<?= (int) $id ?>">
          <?php foreach (CRM_CRITERIOS_SQL as $campo => $info): ?>
            <label class="crit">
              <input type="checkbox" name="<?= h($campo) ?>" value="1"<?= !empty($lead[$campo]) ? ' checked' : '' ?>>
              <span><b><?= h($info[0]) ?><?= $info[2] ? ' *' : '' ?></b><small><?= h($info[1]) ?></small></span>
            </label>
          <?php endforeach; ?>
          <p class="muted regra">* obrigatórios. Vira SQL com os 4 obrigatórios e pelo menos 1 dos outros 2, mesmo que o formulário tenha dado outro nível.</p>
          <button type="submit" class="btn btn-escuro">Salvar qualificação</button>
        </form>
      </section>

      <section class="cartao">
        <h2>Dados do formulário</h2>
        <dl class="dados">
          <div><dt>WhatsApp</dt><dd><?= h($lead['whatsapp']) ?></dd></div>
          <div><dt>E-mail</dt><dd><?= h($lead['email']) ?></dd></div>
          <div><dt>Empresa</dt><dd><?= h($lead['empresa']) ?></dd></div>
          <div><dt>Site / Instagram</dt><dd><?php if ($linkSite): ?><a href="<?= h($linkSite) ?>" target="_blank" rel="noopener noreferrer nofollow"><?= h($site) ?></a><?php elseif ($site !== ''): ?><?= h($site) ?><?php else: ?><span class="muted">Não informado</span><?php endif; ?></dd></div>
          <div><dt>Segmento</dt><dd><?= h($lead['segmento']) ?></dd></div>
          <?php if ($lead['faturamento'] !== ''): ?><div><dt>Faturamento</dt><dd><?= h(CRM_FATURAMENTO[$lead['faturamento']] ?? $lead['faturamento']) ?></dd></div><?php endif; ?>
          <div><dt>Verba de anúncios por mês</dt><dd><?= h(CRM_INVESTIMENTO[$lead['investimento']] ?? $lead['investimento']) ?></dd></div>
          <?php if ($lead['atendimento'] !== ''): ?><div><dt>Quem atende os contatos</dt><dd><?= h(CRM_ATENDIMENTO[$lead['atendimento']] ?? $lead['atendimento']) ?></dd></div><?php endif; ?>
          <?php if ($lead['cargo'] !== ''): ?><div><dt>Papel na empresa</dt><dd><?= h(CRM_CARGO[$lead['cargo']] ?? $lead['cargo']) ?></dd></div><?php endif; ?>
          <?php if ($lead['encaixe'] !== ''): ?><div class="largo"><dt>Assessoria a partir de R$ 4 mil/mês</dt><dd><?= h(CRM_ENCAIXE[$lead['encaixe']] ?? $lead['encaixe']) ?></dd></div><?php endif; ?>
          <div class="largo"><dt>Principal desafio</dt><dd><?= h(CRM_DESAFIO[$lead['desafio']] ?? $lead['desafio']) ?></dd></div>
          <?php if ($lead['objetivo'] !== ''): ?><div class="largo"><dt>O que quer melhorar</dt><dd class="texto-livre"><?= nl2br(h($lead['objetivo'])) ?></dd></div><?php endif; ?>
        </dl>
        <h2>Origem</h2>
        <dl class="dados dados-origem">
          <div><dt>utm_source</dt><dd><?= h($lead['utm_source'] ?: 'não informado') ?></dd></div>
          <div><dt>utm_medium</dt><dd><?= h($lead['utm_medium'] ?: 'não informado') ?></dd></div>
          <div><dt>utm_campaign</dt><dd><?= h($lead['utm_campaign'] ?: 'não informado') ?></dd></div>
          <div><dt>utm_content</dt><dd><?= h($lead['utm_content'] ?: 'não informado') ?></dd></div>
          <div><dt>Clique da Meta</dt><dd><?= $lead['fbc'] !== '' ? 'Sim (fbclid)' : 'Não' ?></dd></div>
          <div><dt>Clique do Google</dt><dd><?= $lead['gclid'] !== '' ? 'Sim (gclid)' : ($lead['gbraid'] !== '' || $lead['wbraid'] !== '' ? 'Sim (iOS)' : 'Não') ?></dd></div>
        </dl>
      </section>
    </div>

    <div class="ficha-col">
      <section class="cartao">
        <h2>Pontuação do formulário</h2>
        <div class="placar placar-<?= h($pontos['nivel']) ?>">
          <b><?= (int) $pontos['total'] ?></b><span>de 100 · ordena a prioridade dentro do nível</span>
        </div>
        <ul class="pontos">
          <?php foreach ($pontos['itens'] as $item): ?>
            <li><span><?= h($item[0]) ?></span><b><?= (int) $item[1] ?>/<?= (int) $item[2] ?></b></li>
          <?php endforeach; ?>
        </ul>
      </section>

      <section class="cartao">
        <h2>Histórico e anotações</h2>
        <form method="post" class="nota-form">
          <?= crm_csrf_campo() ?>
          <input type="hidden" name="acao" value="nota"><input type="hidden" name="id" value="<?= (int) $id ?>">
          <textarea name="texto" rows="3" maxlength="4000" required placeholder="Ex.: liguei, pediu proposta para a próxima semana"></textarea>
          <button type="submit" class="btn btn-escuro">Adicionar anotação</button>
        </form>
        <?php if (!$notas): ?>
          <p class="muted">Nenhuma anotação ainda.</p>
        <?php else: ?>
          <ol class="notas">
            <?php foreach ($notas as $n): ?>
              <li<?= $n['autor'] === 'sistema' ? ' class="nota-sistema"' : '' ?>><div class="nota-meta"><?= h($n['autor']) ?> · <?= h(crm_data($n['criado_em'])) ?></div><div class="texto-livre"><?= nl2br(h($n['texto'])) ?></div></li>
            <?php endforeach; ?>
          </ol>
        <?php endif; ?>
      </section>

      <section class="cartao">
        <h2>Eventos enviados às plataformas</h2>
        <?php if (!$envios): ?>
          <p class="muted">Nenhum evento enviado para a Meta.<?= crm_meta_pronta() ? '' : ' A integração ainda não está ativa (veja Integrações).' ?></p>
        <?php else: ?>
          <ul class="envios">
            <?php foreach ($envios as $e): ?>
              <li><span><?= h(CRM_EVENTOS[$e['evento']]['rotulo'] ?? $e['evento']) ?> <small>Meta · <?= h(CRM_EVENTOS[$e['evento']]['meta'] ?? '') ?></small></span><span class="envio envio-<?= h($e['status']) ?>" title="<?= h($e['resposta']) ?>"><?= h(ucfirst($e['status'])) ?></span></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
        <?php if ($lead['gclid'] !== ''): ?><p class="muted">Google Ads: as conversões deste lead entram no arquivo diário de importação.</p><?php endif; ?>
      </section>

      <section class="cartao">
        <form method="post" class="visto-form">
          <?= crm_csrf_campo() ?>
          <input type="hidden" name="acao" value="nao_visto"><input type="hidden" name="id" value="<?= (int) $id ?>">
          <span class="muted">Visto em <?= h(crm_data($lead['visto_em'])) ?></span>
          <button type="submit" class="linkbtn">Marcar como não visto</button>
        </form>
        <details class="perigo">
          <summary>Excluir este lead</summary>
          <form method="post" data-confirmar="Excluir este lead definitivamente?">
            <?= crm_csrf_campo() ?>
            <input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= (int) $id ?>">
            <p class="muted">A exclusão apaga o lead e as anotações. Os backups diários guardam uma cópia por 30 dias.</p>
            <label>Digite EXCLUIR para confirmar<input name="confirmar" autocomplete="off" required></label>
            <button type="submit" class="btn btn-perigo">Excluir lead</button>
          </form>
        </details>
      </section>
    </div>
  </div>
</main>
<?php crm_rodape();
