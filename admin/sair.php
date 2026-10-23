<?php
/* Sair do painel (só por POST com token, para ninguém deslogar você por um link) */

require __DIR__ . '/../inc/crm.php';
crm_cabecalhos_admin();
crm_sessao();
crm_verificar_post();
crm_encerrar_sessao();
header('Location: login.php?saiu=1');
exit;
