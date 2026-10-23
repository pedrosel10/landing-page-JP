<?php
/* Núcleo do CRM da J.P.Growth: banco, sessão, segurança e funções comuns.
   Usado pela API do formulário (api/lead.php) e pelo painel (admin/).

   Onde ficam os dados: <pasta acima do public_html>/jp_crm_dados/
     crm.sqlite   banco (leads, notas, usuário, tentativas)
     segredo.key  chave aleatória usada para anonimizar IPs
     sessoes/     sessões do painel
     backups/     cópia diária do banco
   O deploy nunca toca nessa pasta. */

if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    http_response_code(404);
    exit;
}

require __DIR__ . '/config.php';
date_default_timezone_set('America/Sao_Paulo');

/* O servidor roda PHP 7.4: estas funções só existem a partir do PHP 8.
   Ficam aqui para o código continuar funcionando se alguém usá-las. */
if (!function_exists('str_contains')) {
    function str_contains(string $texto, string $trecho): bool { return $trecho === '' || strpos($texto, $trecho) !== false; }
}
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $texto, string $inicio): bool { return strncmp($texto, $inicio, strlen($inicio)) === 0; }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with(string $texto, string $fim): bool { return $fim === '' || substr($texto, -strlen($fim)) === $fim; }
}

/* Erro fatal: em vez de página em branco, mostra um aviso (o detalhe vai para o log do servidor) */
register_shutdown_function(function () {
    $erro = error_get_last();
    if (!$erro || !in_array($erro['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
        return;
    }
    error_log('[CRM J.P.Growth] Erro fatal: ' . $erro['message'] . ' em ' . basename($erro['file']) . ':' . $erro['line']);
    if (!headers_sent()) {
        http_response_code(500);
    }
    if (defined('CRM_MODO_API')) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode(['ok' => false, 'erro' => 'servidor']);
        return;
    }
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!doctype html><meta charset="utf-8"><title>Erro no CRM</title>'
        . '<p style="font-family:sans-serif;padding:24px">Ocorreu um erro no CRM ('
        . htmlspecialchars(basename($erro['file']) . ':' . $erro['line'], ENT_QUOTES, 'UTF-8')
        . '). O detalhe foi registrado no log de erros do servidor. <a href="index.php">Voltar ao painel</a></p>';
});

/* ------------------------------------------------------------------ rótulos */

const CRM_STATUS = [
    'novo'        => 'Novo contato',
    'atendimento' => 'Em atendimento',
    'agendada'    => 'Reunião agendada',
    'realizada'   => 'Reunião realizada',
    'proposta'    => 'Proposta enviada',
    'ganho'       => 'Venda realizada',
    'perdido'     => 'Perdido',
];

const CRM_INVESTIMENTO = [
    'ate-1k' => 'Até R$ 1.000',
    '1-2k'   => 'R$ 1.000 – 2.000',
    '2-3k'   => 'R$ 2.000 – 3.000',
    '3-5k'   => 'R$ 3.000 – 5.000',
    '5k+'    => 'Acima de R$ 5.000',
];

/* Faixas que batem com o perfil de cliente da assessoria */
const CRM_INVESTIMENTO_COMPATIVEL = ['2-3k', '3-5k', '5k+'];

const CRM_DESAFIO = [
    'poucos-leads'             => 'Não conseguimos gerar leads suficientes',
    'leads-nao-convertem'      => 'Geramos leads, mas poucos viram vendas',
    'trafego-caro'             => 'Nosso tráfego está caro',
    'anuncios-sem-performance' => 'Nossos anúncios não estão performando',
    'estruturar-marketing'     => 'Precisamos estruturar nosso marketing',
    'outro'                    => 'Outro',
];

const CRM_SEGMENTOS = [
    'Clínica / saúde', 'Odontologia', 'Estética e beleza', 'Restaurante / alimentação',
    'Loja / varejo', 'E-commerce', 'Imobiliária', 'Empresa de serviços', 'Educação', 'Outro',
];

/* ------------------------------------------------------------------- básico */

function h($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/* Interrompe com uma mensagem genérica. O detalhe vai só para o log do servidor. */
function crm_falha(string $detalhe, int $codigo = 500): void
{
    error_log('[CRM J.P.Growth] ' . $detalhe);
    http_response_code($codigo);
    if (defined('CRM_MODO_API')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'erro' => 'servidor']);
    } else {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><meta charset="utf-8"><title>CRM indisponível</title>'
            . '<p style="font-family:sans-serif;padding:24px">O CRM está temporariamente indisponível. '
            . h($detalhe) . '</p>';
    }
    exit;
}

function crm_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443')
        || (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

/* Limpa texto vindo de fora: tira caracteres de controle, espaços extras e corta no limite */
function crm_texto($valor, int $limite = 200, bool $multilinha = false): string
{
    if (!is_scalar($valor)) {
        return '';
    }
    $texto = (string) $valor;
    if (!mb_check_encoding($texto, 'UTF-8')) {
        return '';
    }
    $texto = str_replace(["\r\n", "\r"], "\n", $texto);
    if (!$multilinha) {
        $texto = str_replace("\n", ' ', $texto);
    }
    $texto = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $texto) ?? '';
    $texto = preg_replace('/[ \t]+/u', ' ', $texto) ?? '';
    $texto = trim(preg_replace("/\n{3,}/", "\n\n", $texto) ?? '');
    return mb_substr($texto, 0, $limite, 'UTF-8');
}

/* ------------------------------------------------------------ pasta e banco */

function crm_pasta_dados(): string
{
    static $pasta = null;
    if ($pasta !== null) {
        return $pasta;
    }

    $raiz = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $base = $raiz !== '' ? dirname($raiz) : dirname(__DIR__, 3);
    $caminho = $base . '/' . CRM_PASTA_DADOS;

    if (!is_dir($caminho) && !@mkdir($caminho, 0700, true)) {
        crm_falha('Não foi possível criar a pasta de dados.');
    }
    if (!is_writable($caminho)) {
        crm_falha('A pasta de dados não tem permissão de escrita.');
    }
    /* Defesa extra caso a pasta, um dia, acabe dentro do site */
    if (!file_exists($caminho . '/.htaccess')) {
        @file_put_contents($caminho . '/.htaccess',
            "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n");
    }
    return $pasta = $caminho;
}

/* Chave aleatória gerada no próprio servidor (não vai para o GitHub) */
function crm_segredo(): string
{
    static $segredo = null;
    if ($segredo !== null) {
        return $segredo;
    }
    $arquivo = crm_pasta_dados() . '/segredo.key';
    if (!is_file($arquivo)) {
        $f = @fopen($arquivo, 'x');
        if ($f) {
            fwrite($f, bin2hex(random_bytes(32)));
            fclose($f);
            @chmod($arquivo, 0600);
        }
    }
    $segredo = trim((string) @file_get_contents($arquivo));
    if (strlen($segredo) < 32) {
        crm_falha('Chave secreta do CRM inválida.');
    }
    return $segredo;
}

function crm_db(): PDO
{
    static $db = null;
    if ($db !== null) {
        return $db;
    }
    if (!class_exists('PDO') || !in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        crm_falha('A extensão pdo_sqlite precisa estar ativa no PHP do servidor.');
    }
    try {
        $db = new PDO('sqlite:' . crm_pasta_dados() . '/crm.sqlite', null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $db->exec('PRAGMA busy_timeout = 5000');
        $db->exec('PRAGMA journal_mode = WAL');
        $db->exec('PRAGMA foreign_keys = ON');
        crm_migrar($db);
    } catch (Throwable $e) {
        crm_falha('Erro ao abrir o banco: ' . $e->getMessage());
    }
    return $db;
}

/* Estrutura do banco. Cada versão só ACRESCENTA (nunca apaga tabela ou coluna),
   então atualizar o CRM nunca perde leads. Para mudar algo no futuro:
   if ($versao < 2) { ALTER TABLE ... ADD COLUMN ...; PRAGMA user_version = 2 } */
function crm_migrar(PDO $db): void
{
    $versao = (int) $db->query('PRAGMA user_version')->fetchColumn();

    if ($versao < 1) {
        $db->exec("CREATE TABLE IF NOT EXISTS leads (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            criado_em TEXT NOT NULL,
            atualizado_em TEXT NOT NULL,
            nome TEXT NOT NULL,
            whatsapp TEXT NOT NULL,
            email TEXT NOT NULL,
            empresa TEXT NOT NULL,
            site_instagram TEXT NOT NULL DEFAULT '',
            segmento TEXT NOT NULL DEFAULT '',
            investimento TEXT NOT NULL DEFAULT '',
            desafio TEXT NOT NULL DEFAULT '',
            objetivo TEXT NOT NULL DEFAULT '',
            perfil TEXT NOT NULL DEFAULT '',
            status TEXT NOT NULL DEFAULT 'novo',
            variante TEXT NOT NULL DEFAULT '',
            pagina TEXT NOT NULL DEFAULT '',
            utm_source TEXT NOT NULL DEFAULT '',
            utm_medium TEXT NOT NULL DEFAULT '',
            utm_campaign TEXT NOT NULL DEFAULT '',
            utm_content TEXT NOT NULL DEFAULT '',
            utm_term TEXT NOT NULL DEFAULT ''
        )");
        $db->exec('CREATE INDEX IF NOT EXISTS leads_criado ON leads (criado_em)');
        $db->exec('CREATE INDEX IF NOT EXISTS leads_status ON leads (status)');

        $db->exec("CREATE TABLE IF NOT EXISTS notas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            lead_id INTEGER NOT NULL REFERENCES leads(id) ON DELETE CASCADE,
            criado_em TEXT NOT NULL,
            autor TEXT NOT NULL,
            texto TEXT NOT NULL
        )");
        $db->exec('CREATE INDEX IF NOT EXISTS notas_lead ON notas (lead_id)');

        $db->exec("CREATE TABLE IF NOT EXISTS usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario TEXT NOT NULL UNIQUE,
            senha_hash TEXT NOT NULL,
            senha_fraca INTEGER NOT NULL DEFAULT 0,
            versao_sessao INTEGER NOT NULL DEFAULT 1,
            criado_em TEXT NOT NULL,
            ultimo_login TEXT
        )");

        $db->exec("CREATE TABLE IF NOT EXISTS tentativas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tipo TEXT NOT NULL,
            chave TEXT NOT NULL,
            momento INTEGER NOT NULL
        )");
        $db->exec('CREATE INDEX IF NOT EXISTS tentativas_busca ON tentativas (tipo, chave, momento)');

        $db->exec('PRAGMA user_version = 1');
        $versao = 1;
    }

    /* v2: data em que o lead foi aberto pela primeira vez (NULL = ainda não visto).
       Leads que já tinham andado no funil entram como vistos. */
    if ($versao < 2) {
        $colunas = array_column($db->query('PRAGMA table_info(leads)')->fetchAll(), 'name');
        if (!in_array('visto_em', $colunas, true)) {
            $db->exec('ALTER TABLE leads ADD COLUMN visto_em TEXT');
            $db->exec("UPDATE leads SET visto_em = atualizado_em WHERE status <> 'novo'");
        }
        $db->exec('CREATE INDEX IF NOT EXISTS leads_visto ON leads (visto_em)');
        $db->exec('PRAGMA user_version = 2');
        $versao = 2;
    }

    /* v3: funil de inside sales, pontuação do formulário, MQL/SQL, próxima ação,
       cliques de anúncio e fila de integrações. Só acrescenta; nada é apagado. */
    if ($versao < 3) {
        $db->exec('BEGIN');
        $existentes = array_column($db->query('PRAGMA table_info(leads)')->fetchAll(), 'name');
        $novas = [
            'pontuacao' => 'INTEGER NOT NULL DEFAULT 0', 'classe' => "TEXT NOT NULL DEFAULT ''",
            'qualificacao' => "TEXT NOT NULL DEFAULT 'lead'", 'mql_em' => 'TEXT', 'sql_em' => 'TEXT',
            'qual_orcamento' => 'INTEGER NOT NULL DEFAULT 0', 'qual_decisor' => 'INTEGER NOT NULL DEFAULT 0',
            'qual_dor' => 'INTEGER NOT NULL DEFAULT 0', 'qual_momento' => 'INTEGER NOT NULL DEFAULT 0',
            'qual_operacao' => 'INTEGER NOT NULL DEFAULT 0',
            'etapa_desde' => 'TEXT', 'primeiro_contato_em' => 'TEXT', 'agendada_em' => 'TEXT',
            'proxima_acao' => "TEXT NOT NULL DEFAULT ''", 'proxima_acao_em' => 'TEXT', 'reuniao_em' => 'TEXT',
            'valor_mensal' => 'REAL', 'motivo_perda' => "TEXT NOT NULL DEFAULT ''", 'fechado_em' => 'TEXT',
            'fbc' => "TEXT NOT NULL DEFAULT ''", 'fbp' => "TEXT NOT NULL DEFAULT ''", 'gclid' => "TEXT NOT NULL DEFAULT ''",
            'gbraid' => "TEXT NOT NULL DEFAULT ''", 'wbraid' => "TEXT NOT NULL DEFAULT ''",
            'ip' => "TEXT NOT NULL DEFAULT ''", 'user_agent' => "TEXT NOT NULL DEFAULT ''",
        ];
        foreach ($novas as $coluna => $tipo) {
            if (!in_array($coluna, $existentes, true)) {
                $db->exec("ALTER TABLE leads ADD COLUMN $coluna $tipo");
            }
        }
        /* Etapas antigas → novas */
        $db->exec("UPDATE leads SET status = 'atendimento' WHERE status = 'contato'");
        $db->exec("UPDATE leads SET status = 'agendada' WHERE status = 'reuniao'");
        $db->exec('UPDATE leads SET etapa_desde = atualizado_em WHERE etapa_desde IS NULL');
        $db->exec("UPDATE leads SET fechado_em = atualizado_em WHERE status IN ('ganho','perdido') AND fechado_em IS NULL");
        $db->exec("UPDATE leads SET motivo_perda = 'outro' WHERE status = 'perdido' AND motivo_perda = ''");

        $db->exec("CREATE TABLE IF NOT EXISTS envios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            lead_id INTEGER NOT NULL,
            plataforma TEXT NOT NULL,
            evento TEXT NOT NULL,
            event_id TEXT NOT NULL UNIQUE,
            status TEXT NOT NULL DEFAULT 'pendente',
            tentativas INTEGER NOT NULL DEFAULT 0,
            resposta TEXT NOT NULL DEFAULT '',
            criado_em TEXT NOT NULL,
            ultima_tentativa TEXT,
            enviado_em TEXT
        )");
        $db->exec('CREATE INDEX IF NOT EXISTS envios_status ON envios (status)');
        $db->exec("CREATE TABLE IF NOT EXISTS config (chave TEXT PRIMARY KEY, valor TEXT NOT NULL DEFAULT '')");
        $db->exec('PRAGMA user_version = 3');
        $db->exec('COMMIT');
        $versao = 3;
        $pontuarDepois = true;
    }

    /* Pontua os leads que ainda não têm pontuação (os antigos, na primeira vez) */
    if (!empty($pontuarDepois) && function_exists('crm_pontuar')) {
        $sem = $db->query("SELECT * FROM leads WHERE classe = ''")->fetchAll();
        $up = $db->prepare('UPDATE leads SET pontuacao = ?, classe = ?, qualificacao = ?, mql_em = ? WHERE id = ?');
        foreach ($sem as $l) {
            $p = crm_pontuar($l);
            $l['classe'] = $p['classe'];
            $q = crm_calcular_qualificacao($l);
            $up->execute([$p['total'], $p['classe'], $q, $q !== 'lead' ? $l['criado_em'] : null, $l['id']]);
        }
    }
}

function crm_total_nao_vistos(): int
{
    return (int) crm_db()->query('SELECT COUNT(*) FROM leads WHERE visto_em IS NULL')->fetchColumn();
}

/* "há 5 min", "há 3 h", "ontem", "há 4 dias" ou a data */
function crm_tempo_relativo(string $data): string
{
    $tz = new DateTimeZone('America/Sao_Paulo');
    $d = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data, $tz);
    if (!$d) {
        return $data;
    }
    $seg = time() - $d->getTimestamp();
    if ($seg < 60) {
        return 'agora';
    }
    if ($seg < 3600) {
        return 'há ' . floor($seg / 60) . ' min';
    }
    if ($seg < 86400) {
        return 'há ' . floor($seg / 3600) . ' h';
    }
    $dias = (int) floor($seg / 86400);
    if ($dias === 1) {
        return 'ontem';
    }
    return $dias < 7 ? "há {$dias} dias" : $d->format('d/m/Y');
}

function crm_agora(): string
{
    return (new DateTimeImmutable('now', new DateTimeZone('America/Sao_Paulo')))->format('Y-m-d H:i:s');
}

/* Backup diário com VACUUM INTO (cópia consistente mesmo com o banco em uso) */
function crm_backup_diario(): void
{
    $pasta = crm_pasta_dados() . '/backups';
    if (!is_dir($pasta)) {
        @mkdir($pasta, 0700, true);
    }
    $arquivo = $pasta . '/crm-' . date('Y-m-d') . '.sqlite';
    if (is_file($arquivo)) {
        return;
    }
    try {
        crm_db()->exec('VACUUM INTO ' . crm_db()->quote($arquivo));
    } catch (Throwable $e) {
        @copy(crm_pasta_dados() . '/crm.sqlite', $arquivo);
    }
    $todos = glob($pasta . '/crm-*.sqlite') ?: [];
    sort($todos);
    foreach (array_slice($todos, 0, max(0, count($todos) - CRM_BACKUPS_MANTIDOS)) as $antigo) {
        @unlink($antigo);
    }
}

/* -------------------------------------------- limite de tentativas (por IP) */

/* O IP nunca é salvo puro: vira um código que só serve para contar tentativas */
function crm_chave_ip(): string
{
    return hash_hmac('sha256', $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', crm_segredo());
}

function crm_contar_tentativas(string $tipo, string $chave, int $janela): int
{
    $db = crm_db();
    if (random_int(1, 50) === 1) {
        $db->prepare('DELETE FROM tentativas WHERE momento < ?')->execute([time() - 86400]);
    }
    $st = $db->prepare('SELECT COUNT(*) FROM tentativas WHERE tipo = ? AND chave = ? AND momento > ?');
    $st->execute([$tipo, $chave, time() - $janela]);
    return (int) $st->fetchColumn();
}

function crm_registrar_tentativa(string $tipo, string $chave): void
{
    crm_db()->prepare('INSERT INTO tentativas (tipo, chave, momento) VALUES (?, ?, ?)')
        ->execute([$tipo, $chave, time()]);
}

function crm_limpar_tentativas(string $tipo, string $chave): void
{
    crm_db()->prepare('DELETE FROM tentativas WHERE tipo = ? AND chave = ?')->execute([$tipo, $chave]);
}

/* ------------------------------------------------------------ painel: sessão */

function crm_caminho_admin(): string
{
    return rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/') . '/';
}

function crm_cabecalhos_admin(): void
{
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: no-referrer');
    header('X-Robots-Tag: noindex, nofollow');
    header('Cache-Control: no-store, max-age=0');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' https://fonts.googleapis.com; "
        . "font-src https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self'; form-action 'self'; "
        . "frame-ancestors 'none'; base-uri 'none'; object-src 'none'");
    if (crm_https()) {
        header('Strict-Transport-Security: max-age=31536000');
    }
}

function crm_sessao(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $pasta = crm_pasta_dados() . '/sessoes';
    if (!is_dir($pasta)) {
        @mkdir($pasta, 0700, true);
    }
    if (is_dir($pasta) && is_writable($pasta)) {
        session_save_path($pasta);
    }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');
    ini_set('session.gc_maxlifetime', (string) CRM_SESSAO_MAXIMA);
    ini_set('session.gc_probability', '1');
    ini_set('session.gc_divisor', '50');
    session_name('jpcrm');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => crm_caminho_admin(),
        'secure'   => crm_https(),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

function crm_impressao_navegador(): string
{
    return hash('sha256', substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 300));
}

function crm_encerrar_sessao(): void
{
    crm_sessao();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 3600, 'path' => $p['path'], 'secure' => $p['secure'],
            'httponly' => true, 'samesite' => 'Strict',
        ]);
    }
    session_destroy();
}

function crm_entrar(array $usuario): void
{
    crm_sessao();
    session_regenerate_id(true);
    $_SESSION = [
        'uid'    => (int) $usuario['id'],
        'versao' => (int) $usuario['versao_sessao'],
        'inicio' => time(),
        'ultimo' => time(),
        'nav'    => crm_impressao_navegador(),
        'csrf'   => bin2hex(random_bytes(32)),
    ];
    crm_db()->prepare('UPDATE usuarios SET ultimo_login = ? WHERE id = ?')->execute([crm_agora(), $usuario['id']]);
}

function crm_usuario_logado(): ?array
{
    crm_sessao();
    if (empty($_SESSION['uid'])) {
        return null;
    }
    $agora = time();
    $expirou = ($agora - ($_SESSION['ultimo'] ?? 0)) > CRM_SESSAO_OCIOSA
        || ($agora - ($_SESSION['inicio'] ?? 0)) > CRM_SESSAO_MAXIMA;
    $outroNavegador = !hash_equals((string) ($_SESSION['nav'] ?? ''), crm_impressao_navegador());
    if ($expirou || $outroNavegador) {
        crm_encerrar_sessao();
        return null;
    }
    $st = crm_db()->prepare('SELECT id, usuario, senha_fraca, versao_sessao FROM usuarios WHERE id = ?');
    $st->execute([$_SESSION['uid']]);
    $usuario = $st->fetch();
    /* Trocar a senha muda a versão e derruba as outras sessões abertas */
    if (!$usuario || (int) $usuario['versao_sessao'] !== (int) ($_SESSION['versao'] ?? 0)) {
        crm_encerrar_sessao();
        return null;
    }
    $_SESSION['ultimo'] = $agora;
    return $usuario;
}

function crm_exigir_login(): array
{
    crm_cabecalhos_admin();
    $usuario = crm_usuario_logado();
    if (!$usuario) {
        header('Location: login.php');
        exit;
    }
    crm_backup_diario();
    crm_processar_envios(3);
    return $usuario;
}

/* -------------------------------------------------------- painel: proteções */

function crm_csrf(): string
{
    crm_sessao();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function crm_csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . h(crm_csrf()) . '">';
}

function crm_verificar_post(): void
{
    $token = (string) ($_POST['csrf'] ?? '');
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || $token === '' || !hash_equals(crm_csrf(), $token)) {
        http_response_code(400);
        header('Content-Type: text/html; charset=utf-8');
        exit('<p style="font-family:sans-serif;padding:24px">Requisição inválida ou expirada. '
            . '<a href="index.php">Voltar ao painel</a></p>');
    }
}

function crm_aviso(string $texto, string $tipo = 'ok'): void
{
    crm_sessao();
    $_SESSION['aviso'] = ['texto' => $texto, 'tipo' => $tipo];
}

function crm_pegar_aviso(): ?array
{
    crm_sessao();
    $aviso = $_SESSION['aviso'] ?? null;
    unset($_SESSION['aviso']);
    return $aviso;
}

/* Senha curta ou sem variedade de caracteres */
function crm_senha_fraca(string $senha): bool
{
    $tipos = (int) preg_match('/[a-z]/', $senha) + (int) preg_match('/[A-Z]/', $senha)
        + (int) preg_match('/\d/', $senha) + (int) preg_match('/[^a-zA-Z\d]/', $senha);
    return mb_strlen($senha) < 10 || $tipos < 3;
}

/* Espera um pouco em toda tentativa de login: deixa a força bruta mais lenta
   e não deixa o tempo de resposta revelar se o usuário existe */
function crm_atraso_login(): void
{
    usleep(random_int(400000, 800000));
}

/* ---------------------------------------------------------- painel: visual */

function crm_topo(string $titulo, ?array $usuario = null, string $classe = ''): void
{
    $aviso = crm_pegar_aviso();
    $naoVistos = $usuario ? crm_total_nao_vistos() : 0;
    ?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= $naoVistos ? '(' . $naoVistos . ') ' : '' ?><?= h($titulo) ?> | CRM J.P.Growth</title>
<link rel="icon" type="image/svg+xml" href="../marca/icone.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/admin.css?v=6">
<script src="assets/admin.js?v=3" defer></script>
</head>
<body class="<?= h($classe) ?>">
<?php if ($usuario): ?>
<header class="bar">
  <a class="bar-brand" href="index.php"><img src="../marca/icone.svg" alt="" width="26" height="26"><img src="../marca/logotipo.svg" alt="J.P.Growth" class="bar-logo" width="118" height="22"><span class="bar-tag">CRM</span></a>
  <nav class="bar-nav">
    <a href="index.php">Funil<?php if ($naoVistos): ?> <span class="contador" title="Leads ainda não vistos"><?= $naoVistos ?></span><?php endif; ?></a>
    <a href="lista.php">Lista</a>
    <a href="integracoes.php">Integrações</a>
    <a href="conta.php">Conta</a>
    <form method="post" action="sair.php"><?= crm_csrf_campo() ?><button type="submit" class="linkbtn">Sair</button></form>
  </nav>
</header>
<?php if (!empty($usuario['senha_fraca'])): ?>
<div class="alerta alerta-aviso wrap">Sua senha é curta ou simples demais. <a href="conta.php">Troque por uma senha mais forte</a> para proteger os leads.</div>
<?php endif; ?>
<?php endif; ?>
<?php if ($aviso): ?>
<div class="alerta alerta-<?= h($aviso['tipo']) ?> wrap"><?= h($aviso['texto']) ?></div>
<?php endif; ?>
<?php
}

function crm_rodape(): void
{
    echo "\n</body>\n</html>\n";
}

function crm_selo_status(string $status): string
{
    $rotulo = CRM_STATUS[$status] ?? $status;
    return '<span class="selo selo-' . h($status) . '">' . h($rotulo) . '</span>';
}

function crm_selo_perfil(string $perfil): string
{
    return $perfil === 'compativel'
        ? '<span class="selo selo-fit">No perfil</span>'
        : '<span class="selo selo-fora">Abaixo do perfil</span>';
}

function crm_data(string $data): string
{
    $d = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data);
    return $d ? $d->format('d/m/Y H:i') : $data;
}

function crm_link_whatsapp(string $numero, string $mensagem = ''): string
{
    $digitos = preg_replace('/\D/', '', $numero) ?? '';
    if (strlen($digitos) <= 11) {
        $digitos = '55' . $digitos;
    }
    return 'https://wa.me/' . $digitos . ($mensagem !== '' ? '?text=' . rawurlencode($mensagem) : '');
}

require __DIR__ . '/funil.php';
require __DIR__ . '/integracoes.php';
