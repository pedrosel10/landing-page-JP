<?php
/* Marca como vistos os leads que estavam na tela quando alguém interagiu com a lista.
   Chamado pelo assets/admin.js. Responde em JSON. */

require __DIR__ . '/../inc/crm.php';
crm_cabecalhos_admin();
header('Content-Type: application/json; charset=utf-8');

if (!crm_usuario_logado()) {
    http_response_code(401);
    echo json_encode(['ok' => false]);
    exit;
}
crm_verificar_post();

/* Só números, sem repetir, no máximo 200 por vez */
$ids = array_map('intval', explode(',', (string) ($_POST['ids'] ?? '')));
$ids = array_values(array_unique(array_filter($ids, function ($id) {
    return $id > 0;
})));
$ids = array_slice($ids, 0, 200);

/* Leads que você mesmo marcou como "não visto" nesta sessão ficam de fora:
   eles esperam o sócio ver (ou alguém abrir a ficha) */
$ids = array_values(array_diff($ids, $_SESSION['nao_auto'] ?? []));

$marcados = 0;
if ($ids) {
    $marcas = implode(', ', array_fill(0, count($ids), '?'));
    $st = crm_db()->prepare("UPDATE leads SET visto_em = ? WHERE visto_em IS NULL AND id IN ($marcas)");
    $st->execute(array_merge([crm_agora()], $ids));
    $marcados = $st->rowCount();
}

echo json_encode(['ok' => true, 'marcados' => $marcados, 'restantes' => crm_total_nao_vistos()]);
