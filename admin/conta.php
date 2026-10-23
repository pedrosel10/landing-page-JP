<?php
/* Troca de senha. Ao trocar, todas as outras sessões abertas são encerradas. */

require __DIR__ . '/../inc/crm.php';
$usuario = crm_exigir_login();
$db = crm_db();
$erro = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    crm_verificar_post();
    $atual = (string) ($_POST['atual'] ?? '');
    $nova = (string) ($_POST['nova'] ?? '');
    $confirmar = (string) ($_POST['confirmar'] ?? '');
    crm_atraso_login();

    $st = $db->prepare('SELECT * FROM usuarios WHERE id = ?');
    $st->execute([$usuario['id']]);
    $conta = $st->fetch();

    if (crm_contar_tentativas('login', crm_chave_ip(), CRM_LOGIN_JANELA) >= CRM_LOGIN_MAX_FALHAS) {
        $erro = 'Muitas tentativas. Aguarde 15 minutos e tente de novo.';
    } elseif (!$conta || !password_verify($atual, $conta['senha_hash'])) {
        crm_registrar_tentativa('login', crm_chave_ip());
        $erro = 'A senha atual está incorreta.';
    } elseif (mb_strlen($nova) < 8 || mb_strlen($nova) > 200) {
        $erro = 'A nova senha precisa ter pelo menos 8 caracteres.';
    } elseif (!hash_equals($nova, $confirmar)) {
        $erro = 'A confirmação não confere com a nova senha.';
    } elseif (hash_equals($atual, $nova)) {
        $erro = 'A nova senha precisa ser diferente da atual.';
    } else {
        $db->prepare('UPDATE usuarios SET senha_hash = ?, senha_fraca = ?, versao_sessao = versao_sessao + 1 WHERE id = ?')
            ->execute([password_hash($nova, PASSWORD_DEFAULT), crm_senha_fraca($nova) ? 1 : 0, $conta['id']]);
        $st = $db->prepare('SELECT * FROM usuarios WHERE id = ?');
        $st->execute([$conta['id']]);
        crm_entrar($st->fetch());
        crm_aviso(crm_senha_fraca($nova)
            ? 'Senha trocada. Ela ainda é simples: o ideal é ter 10+ caracteres misturando letras, números e símbolos.'
            : 'Senha trocada com sucesso.');
        header('Location: conta.php');
        exit;
    }
}

crm_topo('Conta', $usuario);
?>
<main class="wrap estreito">
  <a class="voltar" href="index.php">← Todos os leads</a>
  <h1>Conta</h1>
  <p class="muted">Usuário: <b><?= h($usuario['usuario']) ?></b></p>

  <section class="cartao">
    <h2>Trocar senha</h2>
    <?php if ($erro): ?><div class="alerta alerta-erro" role="alert"><?= h($erro) ?></div><?php endif; ?>
    <form method="post" class="form">
      <?= crm_csrf_campo() ?>
      <input type="text" name="usuario" value="<?= h($usuario['usuario']) ?>" autocomplete="username" class="oculto" tabindex="-1" aria-hidden="true">
      <label>Senha atual<input type="password" name="atual" required autocomplete="current-password" maxlength="200"></label>
      <label>Nova senha<input type="password" name="nova" required minlength="8" autocomplete="new-password" maxlength="200"></label>
      <label>Confirmar nova senha<input type="password" name="confirmar" required minlength="8" autocomplete="new-password" maxlength="200"></label>
      <p class="muted">Use 10 ou mais caracteres misturando letras maiúsculas, minúsculas, números e símbolos. Ao trocar, outras sessões abertas são encerradas.</p>
      <button type="submit" class="btn btn-primario">Salvar nova senha</button>
    </form>
  </section>
</main>
<?php crm_rodape();
