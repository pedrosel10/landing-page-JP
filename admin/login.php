<?php
/* Login do CRM. Enquanto não existe nenhum usuário, vira a tela de primeiro acesso:
   pede a chave de instalação e cria o login. Depois disso, a instalação some. */

require __DIR__ . '/../inc/crm.php';
crm_cabecalhos_admin();

if (crm_usuario_logado()) {
    header('Location: index.php');
    exit;
}

$db = crm_db();
$instalacao = (int) $db->query('SELECT COUNT(*) FROM usuarios')->fetchColumn() === 0;
$erro = '';
$ipChave = crm_chave_ip();
$usuarioDigitado = $instalacao ? 'j&p' : '';

function bloqueado(string $ipChave, string $usuario): bool
{
    return crm_contar_tentativas('login', $ipChave, CRM_LOGIN_JANELA) >= CRM_LOGIN_MAX_FALHAS
        || ($usuario !== '' && crm_contar_tentativas('login-usuario', hash('sha256', mb_strtolower($usuario)), CRM_LOGIN_JANELA) >= CRM_LOGIN_MAX_FALHAS_USUARIO);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    crm_verificar_post();
    $usuarioDigitado = crm_texto($_POST['usuario'] ?? '', 40);
    $senha = (string) ($_POST['senha'] ?? '');
    crm_atraso_login();

    if (bloqueado($ipChave, $usuarioDigitado)) {
        $erro = 'Muitas tentativas. Aguarde 15 minutos e tente de novo.';
    } elseif ($instalacao) {
        $chave = strtoupper(trim((string) ($_POST['chave'] ?? '')));
        $confirmacao = (string) ($_POST['confirmar'] ?? '');
        if (!password_verify($chave, CRM_CHAVE_INSTALACAO_HASH)) {
            crm_registrar_tentativa('login', $ipChave);
            $erro = 'Chave de instalação inválida.';
        } elseif (mb_strlen($usuarioDigitado) < 3) {
            $erro = 'O usuário precisa ter pelo menos 3 caracteres.';
        } elseif (mb_strlen($senha) < 6 || mb_strlen($senha) > 200) {
            $erro = 'A senha precisa ter pelo menos 6 caracteres.';
        } elseif (!hash_equals($senha, $confirmacao)) {
            $erro = 'As senhas não conferem.';
        } else {
            try {
                $db->exec('BEGIN IMMEDIATE');
                if ((int) $db->query('SELECT COUNT(*) FROM usuarios')->fetchColumn() === 0) {
                    $db->prepare('INSERT INTO usuarios (usuario, senha_hash, senha_fraca, criado_em) VALUES (?, ?, ?, ?)')
                        ->execute([$usuarioDigitado, password_hash($senha, PASSWORD_DEFAULT), crm_senha_fraca($senha) ? 1 : 0, crm_agora()]);
                }
                $db->exec('COMMIT');
            } catch (Throwable $e) {
                $db->exec('ROLLBACK');
                crm_falha('Erro ao criar usuário: ' . $e->getMessage());
            }
            $st = $db->prepare('SELECT * FROM usuarios WHERE usuario = ?');
            $st->execute([$usuarioDigitado]);
            $novo = $st->fetch();
            if ($novo && password_verify($senha, $novo['senha_hash'])) {
                crm_limpar_tentativas('login', $ipChave);
                crm_entrar($novo);
                crm_aviso('Acesso criado. Bem-vindo ao CRM!');
                header('Location: index.php');
                exit;
            }
            $erro = 'Não foi possível criar o acesso. Recarregue a página.';
        }
    } else {
        $st = $db->prepare('SELECT * FROM usuarios WHERE usuario = ?');
        $st->execute([$usuarioDigitado]);
        $conta = $st->fetch();
        /* Mesmo sem usuário, roda o password_verify num hash qualquer: o tempo de
           resposta fica igual e não revela quais usuários existem */
        $hash = $conta['senha_hash'] ?? '$2y$12$WEc49YhL/kIF2C6gVfYuaeKeNxrPWMDwV1aLE6LLZqNvgO59dzZfa';
        if (password_verify($senha, $hash) && $conta) {
            if (password_needs_rehash($conta['senha_hash'], PASSWORD_DEFAULT)) {
                $db->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?')->execute([password_hash($senha, PASSWORD_DEFAULT), $conta['id']]);
            }
            crm_limpar_tentativas('login', $ipChave);
            crm_entrar($conta);
            header('Location: index.php');
            exit;
        }
        crm_registrar_tentativa('login', $ipChave);
        if ($usuarioDigitado !== '') {
            crm_registrar_tentativa('login-usuario', hash('sha256', mb_strtolower($usuarioDigitado)));
        }
        $erro = 'Usuário ou senha inválidos.';
    }
}

crm_topo($instalacao ? 'Primeiro acesso' : 'Entrar', null, 'pagina-login');
?>
<main class="login">
  <div class="login-card">
    <div class="login-brand"><img src="../marca/icone.svg" alt="" width="34" height="34"><img src="../marca/logotipo.svg" alt="J.P.Growth" width="150" height="28"></div>
    <h1><?= $instalacao ? 'Primeiro acesso ao CRM' : 'Entrar no CRM' ?></h1>
    <?php if ($instalacao): ?>
      <p class="muted">Use a chave de instalação para criar o login do painel. Esta tela só aparece uma vez.</p>
    <?php endif; ?>
    <?php if (isset($_GET['saiu'])): ?><div class="alerta alerta-ok">Você saiu do painel.</div><?php endif; ?>
    <?php if ($erro): ?><div class="alerta alerta-erro" role="alert"><?= h($erro) ?></div><?php endif; ?>
    <form method="post" autocomplete="on" class="form">
      <?= crm_csrf_campo() ?>
      <?php if ($instalacao): ?>
        <label>Chave de instalação<input name="chave" required autocomplete="off" spellcheck="false" placeholder="XXXXX-XXXXX-XXXXX-XXXXX"></label>
      <?php endif; ?>
      <label>Usuário<input name="usuario" required autocomplete="username" value="<?= h($usuarioDigitado) ?>" maxlength="40"></label>
      <label>Senha<input type="password" name="senha" required autocomplete="<?= $instalacao ? 'new-password' : 'current-password' ?>" maxlength="200"></label>
      <?php if ($instalacao): ?>
        <label>Confirmar senha<input type="password" name="confirmar" required autocomplete="new-password" maxlength="200"></label>
      <?php endif; ?>
      <button type="submit" class="btn btn-primario"><?= $instalacao ? 'Criar acesso' : 'Entrar' ?></button>
    </form>
  </div>
</main>
<?php crm_rodape();
