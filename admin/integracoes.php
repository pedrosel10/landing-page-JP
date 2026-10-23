<?php
/* Integrações: Meta (API de Conversões) e Google Ads (importação de conversões).
   Token e chave ficam no banco, fora do site; nunca aparecem completos na tela. */

require __DIR__ . '/../inc/crm.php';
$usuario = crm_exigir_login();
$db = crm_db();
$resultadoTeste = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    crm_verificar_post();
    $acao = (string) ($_POST['acao'] ?? '');

    if ($acao === 'salvar_meta') {
        $pixel = preg_replace('/\D/', '', (string) ($_POST['meta_pixel'] ?? '')) ?? '';
        $token = trim((string) ($_POST['meta_token'] ?? ''));
        $teste = preg_replace('/[^A-Za-z0-9]/', '', (string) ($_POST['meta_teste'] ?? '')) ?? '';
        $versao = trim((string) ($_POST['meta_versao'] ?? ''));
        $valores = [
            'meta_ativo'  => isset($_POST['meta_ativo']) ? '1' : '0',
            'meta_pixel'  => substr($pixel, 0, 25),
            'meta_teste'  => substr($teste, 0, 30),
            'meta_versao' => preg_match('/^v\d{2}\.\d$/', $versao) ? $versao : CRM_META_VERSAO_PADRAO,
        ];
        foreach (array_keys(CRM_EVENTOS) as $ev) {
            $valores['meta_ev_' . $ev] = isset($_POST['meta_ev_' . $ev]) ? '1' : '0';
        }
        if ($token !== '') {
            if (!preg_match('/^[A-Za-z0-9_\-]{40,600}$/', $token)) {
                crm_aviso('O token de acesso parece inválido. Copie de novo no Gerenciador de Eventos.', 'erro');
                header('Location: integracoes.php');
                exit;
            }
            $valores['meta_token'] = $token;
        }
        crm_config_salvar($valores);
        crm_aviso('Configuração da Meta salva.');
    } elseif ($acao === 'remover_token') {
        crm_config_salvar(['meta_token' => '', 'meta_ativo' => '0']);
        crm_aviso('Token da Meta removido e integração desativada.');
    } elseif ($acao === 'testar_meta') {
        if (crm_config('meta_pixel') === '' || crm_config('meta_token') === '') {
            crm_aviso('Salve o ID do conjunto de dados e o token antes de testar.', 'erro');
        } elseif (crm_config('meta_teste') === '') {
            crm_aviso('Preencha o "código de teste" (Gerenciador de Eventos → Testar eventos) para não misturar o teste com dados reais.', 'erro');
        } else {
            $falso = [
                'id' => 0, 'email' => 'teste-crm@jpgrowth.com.br', 'whatsapp' => '11999990000', 'nome' => 'Teste CRM',
                'criado_em' => crm_agora(), 'pagina' => 'https://pedroselestrim.com/jp/', 'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '', 'fbc' => '', 'fbp' => '', 'valor_mensal' => 0,
            ];
            $corpo = crm_meta_payload($falso, 'lead', 'jpcrm-teste-' . time());
            $corpo['access_token'] = crm_config('meta_token');
            $url = crm_meta_base() . '/' . rawurlencode(crm_config('meta_versao', CRM_META_VERSAO_PADRAO)) . '/' . rawurlencode(crm_config('meta_pixel')) . '/events';
            list($codigo, $resposta) = crm_http_post_json($url, $corpo, 8);
            $json = json_decode((string) $resposta, true);
            if ($codigo === 200 && (int) ($json['events_received'] ?? 0) >= 1) {
                crm_aviso('Teste recebido pela Meta. Confira em Gerenciador de Eventos → Testar eventos.');
            } else {
                crm_aviso('A Meta recusou o teste (HTTP ' . $codigo . '): ' . (string) ($json['error']['message'] ?? substr((string) $resposta, 0, 200)), 'erro');
            }
        }
    } elseif ($acao === 'reenviar') {
        $db->exec("UPDATE envios SET status = 'pendente', tentativas = 0 WHERE status = 'erro'");
        crm_processar_envios(20);
        crm_aviso('Reenvio feito. Veja o resultado na tabela abaixo.');
    } elseif ($acao === 'salvar_google') {
        $valores = ['google_ativo' => isset($_POST['google_ativo']) ? '1' : '0'];
        foreach (['mql', 'sql', 'agendada', 'ganho'] as $ev) {
            $valores['google_conv_' . $ev] = crm_texto($_POST['google_conv_' . $ev] ?? '', 100);
        }
        crm_config_salvar($valores);
        crm_aviso('Configuração do Google Ads salva.');
    } elseif ($acao === 'gerar_chave_google') {
        $chave = bin2hex(random_bytes(24));
        crm_config_salvar(['google_chave_hash' => hash('sha256', $chave), 'google_chave_criada' => crm_agora()]);
        $_SESSION['google_url_nova'] = $chave;
        crm_aviso('Novo endereço gerado. Copie agora: ele só aparece uma vez.');
    }
    header('Location: integracoes.php');
    exit;
}

$base = (crm_https() ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'pedroselestrim.com') . dirname(rtrim(crm_caminho_admin(), '/'));
$urlGoogle = '';
if (!empty($_SESSION['google_url_nova'])) {
    $urlGoogle = rtrim($base, '/') . '/api/google-conversoes.php?chave=' . $_SESSION['google_url_nova'];
    unset($_SESSION['google_url_nova']);
}

$metaStatus = crm_meta_pronta() ? ['Ativa', 'ok'] : (crm_config('meta_ativo') === '1' ? ['Incompleta', 'aviso'] : ['Desativada', 'off']);
$googleStatus = crm_config('google_ativo') === '1' && crm_config('google_chave_hash') !== '' ? ['Ativa', 'ok'] : ['Desativada', 'off'];
$token = crm_config('meta_token');

$envios = $db->query('SELECT e.*, l.nome FROM envios e LEFT JOIN leads l ON l.id = e.lead_id ORDER BY e.id DESC LIMIT 30')->fetchAll();
$erros = (int) $db->query("SELECT COUNT(*) FROM envios WHERE status = 'erro'")->fetchColumn();
$comGclid = (int) $db->query("SELECT COUNT(*) FROM leads WHERE gclid <> ''")->fetchColumn();
$comFbc = (int) $db->query("SELECT COUNT(*) FROM leads WHERE fbc <> ''")->fetchColumn();

crm_topo('Integrações', $usuario);
?>
<main class="wrap estreito-medio">
  <h1>Integrações</h1>
  <p class="muted">Devolva os dados do funil para a Meta e o Google, para que as campanhas aprendam quais leads viram SQL, reunião e venda.</p>

  <section class="cartao integ">
    <div class="integ-cab"><h2>Meta Ads · API de Conversões</h2><span class="estado estado-<?= h($metaStatus[1]) ?>"><?= h($metaStatus[0]) ?></span></div>
    <ol class="passos">
      <li>No <b>Gerenciador de Eventos</b> da Meta, abra o seu Pixel (conjunto de dados) e copie o <b>ID</b>.</li>
      <li>Em <b>Configurações → API de Conversões</b>, clique em <b>Gerar token de acesso</b> e cole abaixo.</li>
      <li>Para testar: em <b>Testar eventos</b>, copie o código (ex.: TEST1234), salve aqui e clique em <b>Enviar teste</b>. Depois apague o código para valer de verdade.</li>
      <li>Nas campanhas, crie uma <b>conversão personalizada</b> com o evento <b>SQL</b> ou <b>Schedule</b> e otimize para ela quando tiver volume (a Meta pede cerca de 50 por semana por conjunto). Até lá, otimize para <b>Lead</b>.</li>
    </ol>
    <form method="post" class="form">
      <?= crm_csrf_campo() ?>
      <input type="hidden" name="acao" value="salvar_meta">
      <label class="toggle"><input type="checkbox" name="meta_ativo" value="1"<?= crm_config('meta_ativo') === '1' ? ' checked' : '' ?>><span>Enviar eventos para a Meta</span></label>
      <div class="duas">
        <label>ID do conjunto de dados (Pixel)<input name="meta_pixel" inputmode="numeric" value="<?= h(crm_config('meta_pixel')) ?>" placeholder="Ex.: 123456789012345"></label>
        <label>Código de teste (opcional)<input name="meta_teste" value="<?= h(crm_config('meta_teste')) ?>" placeholder="Ex.: TEST1234"></label>
      </div>
      <label>Token de acesso<input type="password" name="meta_token" autocomplete="off" placeholder="<?= $token !== '' ? 'Token salvo (termina em ' . h(substr($token, -4)) . '). Deixe em branco para manter.' : 'Cole aqui o token gerado na Meta' ?>"></label>
      <fieldset class="eventos">
        <legend>Eventos enviados</legend>
        <?php foreach (CRM_EVENTOS as $ev => $info): ?>
          <label><input type="checkbox" name="meta_ev_<?= h($ev) ?>" value="1"<?= crm_config('meta_ev_' . $ev, '1') === '1' ? ' checked' : '' ?>> <?= h($info['rotulo']) ?> <code><?= h($info['meta']) ?></code></label>
        <?php endforeach; ?>
      </fieldset>
      <details class="avancado"><summary>Avançado</summary>
        <label>Versão da API da Meta<input name="meta_versao" value="<?= h(crm_config('meta_versao', CRM_META_VERSAO_PADRAO)) ?>" placeholder="v23.0"></label>
      </details>
      <div class="botoes">
        <button type="submit" class="btn btn-primario">Salvar</button>
      </div>
    </form>
    <div class="botoes">
      <form method="post"><?= crm_csrf_campo() ?><input type="hidden" name="acao" value="testar_meta"><button type="submit" class="btn btn-claro">Enviar teste</button></form>
      <?php if ($token !== ''): ?><form method="post" data-confirmar="Remover o token e desativar a integração?"><?= crm_csrf_campo() ?><input type="hidden" name="acao" value="remover_token"><button type="submit" class="linkbtn">Remover token</button></form><?php endif; ?>
    </div>
    <p class="muted regra"><?= $comFbc ?> lead(s) chegaram com clique de anúncio da Meta (fbclid). E-mail, telefone e nome vão criptografados (SHA-256), como a Meta exige.</p>
  </section>

  <section class="cartao integ">
    <div class="integ-cab"><h2>Google Ads · Importação de conversões</h2><span class="estado estado-<?= h($googleStatus[1]) ?>"><?= h($googleStatus[0]) ?></span></div>
    <ol class="passos">
      <li>No Google Ads, crie as ações de conversão do tipo <b>Importação → cliques</b> (ex.: “CRM SQL”, “CRM Reunião”, “CRM Venda”) e escreva os nomes iguais abaixo.</li>
      <li>Clique em <b>Gerar endereço</b> e copie o link.</li>
      <li>Em <b>Metas → Conversões → Uploads → Programações</b>, escolha <b>HTTPS</b>, cole o link e defina a frequência <b>diária</b>.</li>
      <li>Só entram leads que chegaram por anúncio do Google (com gclid), nos últimos 30 dias. O arquivo não leva nenhum dado pessoal.</li>
    </ol>
    <?php if ($urlGoogle !== ''): ?>
      <div class="url-nova"><b>Endereço para o Google Ads</b> (copie agora, ele não aparece de novo):<code><?= h($urlGoogle) ?></code></div>
    <?php elseif (crm_config('google_chave_hash') !== ''): ?>
      <p class="muted">Endereço já gerado<?= crm_config('google_chave_criada') !== '' ? ' em ' . h(crm_data(crm_config('google_chave_criada'))) : '' ?>. Se perdeu o link, gere outro (o anterior deixa de funcionar).</p>
    <?php endif; ?>
    <form method="post" class="form">
      <?= crm_csrf_campo() ?>
      <input type="hidden" name="acao" value="salvar_google">
      <label class="toggle"><input type="checkbox" name="google_ativo" value="1"<?= crm_config('google_ativo') === '1' ? ' checked' : '' ?>><span>Liberar o arquivo de conversões para o Google Ads</span></label>
      <div class="duas">
        <label>Conversão quando vira MQL<input name="google_conv_mql" value="<?= h(crm_config('google_conv_mql')) ?>" placeholder="Deixe vazio para não enviar"></label>
        <label>Conversão quando vira SQL<input name="google_conv_sql" value="<?= h(crm_config('google_conv_sql')) ?>" placeholder="Ex.: CRM SQL"></label>
        <label>Conversão de reunião agendada<input name="google_conv_agendada" value="<?= h(crm_config('google_conv_agendada')) ?>" placeholder="Ex.: CRM Reunião"></label>
        <label>Conversão de venda (com valor)<input name="google_conv_ganho" value="<?= h(crm_config('google_conv_ganho')) ?>" placeholder="Ex.: CRM Venda"></label>
      </div>
      <div class="botoes"><button type="submit" class="btn btn-primario">Salvar</button></div>
    </form>
    <form method="post" data-confirmar="<?= crm_config('google_chave_hash') !== '' ? 'Gerar um novo endereço? O anterior deixa de funcionar.' : 'Gerar o endereço de importação?' ?>">
      <?= crm_csrf_campo() ?><input type="hidden" name="acao" value="gerar_chave_google">
      <button type="submit" class="btn btn-claro">Gerar endereço</button>
    </form>
    <p class="muted regra"><?= $comGclid ?> lead(s) chegaram com clique de anúncio do Google (gclid).</p>
  </section>

  <section class="cartao">
    <div class="integ-cab"><h2>Últimos eventos enviados à Meta</h2>
      <?php if ($erros): ?><form method="post"><?= crm_csrf_campo() ?><input type="hidden" name="acao" value="reenviar"><button type="submit" class="linkbtn">Reenviar <?= $erros ?> com erro</button></form><?php endif; ?>
    </div>
    <?php if (!$envios): ?>
      <p class="muted">Nenhum evento ainda.</p>
    <?php else: ?>
      <ul class="envios envios-log">
        <?php foreach ($envios as $e): ?>
          <li>
            <span><b><?= h(CRM_EVENTOS[$e['evento']]['meta'] ?? $e['evento']) ?></b> · <?= $e['nome'] !== null ? '<a href="lead.php?id=' . (int) $e['lead_id'] . '">' . h($e['nome']) . '</a>' : 'lead excluído' ?>
              <small><?= h(crm_data($e['criado_em'])) ?><?= $e['resposta'] !== '' ? ' · ' . h($e['resposta']) : '' ?></small></span>
            <span class="envio envio-<?= h($e['status']) ?>"><?= h(ucfirst($e['status'])) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>
</main>
<?php crm_rodape();
