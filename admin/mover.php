<?php
/* Move um card de etapa (arrastar e soltar no funil). Responde em JSON.
   Venda e perda pedem dados extras: nesses casos devolve "precisa" e a página
   abre a ficha do lead com o formulário certo. */

require __DIR__ . '/../inc/crm.php';
crm_cabecalhos_admin();
header('Content-Type: application/json; charset=utf-8');

$usuario = crm_usuario_logado();
if (!$usuario) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'erro' => 'Sessão expirada. Entre de novo.']);
    exit;
}
crm_verificar_post();

$id = (int) ($_POST['id'] ?? 0);
$etapa = (string) ($_POST['etapa'] ?? '');

if ($etapa === 'ganho' || $etapa === 'perdido') {
    echo json_encode(['ok' => false, 'precisa' => $etapa]);
    exit;
}

$erro = crm_mudar_etapa($id, $etapa, $usuario['usuario']);
if ($erro !== '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'erro' => $erro]);
    exit;
}
crm_processar_envios(2);
echo json_encode(['ok' => true]);
