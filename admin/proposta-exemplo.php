<?php
/* Prévia do modelo de proposta com um cliente de exemplo (só para quem está logado).
   Serve para revisar o design e os textos sem gerar link para nenhum lead. */

require __DIR__ . '/../inc/crm.php';
$usuario = crm_usuario_logado();
if (!$usuario) {
    header('Location: login.php');
    exit;
}

crm_proposta_mostrar([
    'exemplo'        => true,
    'nome'           => 'Marina',
    'empresa'        => 'Clínica Aurora',
    'segmento'       => 'Clínica / saúde',
    'faturamento'    => 'R$ 200 a 500 mil',
    'verba_atual'    => 'R$ 2.500 a 5 mil',
    'atendimento'    => 'Pessoa ou equipe dedicada, sem processo definido',
    'desafio'        => 'Gero leads, mas as vendas não acompanham',
    'desafio_curto'  => 'os leads chegam, mas as vendas não acompanham',
    'obs'            => [
        'Os anúncios ativos falam de preço e promoção, não do motivo para escolher a clínica.',
        'O link da bio leva direto para o WhatsApp, sem nenhuma página no caminho.',
        'Não achamos o Pixel da Meta no site: as campanhas não aprendem com quem agenda.',
    ],
    'caso'           => 'captacao',
    'data'           => crm_data_extenso(null),
    'prazo_contrato' => '5 dias úteis',
    'assessoria'     => 3000,
    'verba_minima'   => 3000,
    'whatsapp'       => CRM_PROPOSTA_WHATSAPP,
    'rastreio'       => null,
], 'Modelo de proposta · J.P.Growth');
