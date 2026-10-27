# Landing page + CRM da J.P.Growth

- **Landing page:** `index.html` (página única), publicada em `https://pedroselestrim.com/jp`.
- **CRM de leads:** `https://pedroselestrim.com/jp/admin`. O formulário da página grava
  cada lead no CRM pela API `api/lead.php`.

## Estrutura

| Pasta | O que é |
|---|---|
| `index.html`, `logos/`, `fotos/`, `marca/`, `marcas/`, `prints/` | Landing page |
| `api/lead.php` | Recebe o formulário e grava o lead |
| `admin/` | Painel do CRM (login, lista, ficha do lead, exportar CSV, trocar senha) |
| `inc/` | Núcleo do CRM (banco, sessão, segurança). Bloqueado para acesso direto |

## Como o CRM funciona (inside sales)

**Funil:** Novo contato → Em atendimento → Reunião agendada → Reunião realizada →
Proposta enviada → Venda realizada (pede o valor mensal) ou Perdido (pede o motivo).
O funil em kanban (`admin/index.php`) permite arrastar os cards; cada etapa tem um prazo
e o card ganha o alerta "parado há X dias" quando passa dele. Cada lead tem uma
**próxima ação com data**; as atrasadas ficam em vermelho.

**Pontuação do formulário (0 a 100) → classe:** faturamento (30), verba mensal de
anúncios (25), resposta ao preço da assessoria (15), desafio (10), segmento (8),
papel na empresa (7), site ou rede social (3) e e-mail da empresa (2).
A classe segue regras de corte:
- **C sempre:** fatura até R$ 50 mil, diz que a assessoria (a partir de R$ 4 mil) não
  cabe no orçamento, ou investe até R$ 2.500 em anúncios e fatura menos de R$ 100 mil.
- **A:** fatura R$ 100 mil+, investe R$ 2.500+ em anúncios e quer começar agora.
- **O resto:** B com 45 pontos ou mais, C abaixo disso.

Leads de antes de 27/10/2026 (sem faturamento) mantêm a regra anterior. Regras em
`inc/funil.php`. A página mostra uma mensagem de agradecimento diferente para cada
classe: A tem prioridade, C não recebe promessa de reunião.

**Lead → MQL → SQL:** vira MQL automaticamente quando o formulário é classe A (leads
antigos: A ou B). Quem se declara sócio ou dono já entra com "decisor" marcado.
Vira SQL no atendimento, quando a lista confirma orçamento e decisor, mais 2 de 3:
dor clara, quer começar em até 60 dias, operação comercial pronta.

**Integrações (`admin/integracoes.php`):**
- Meta (API de Conversões): envia Lead, MQL, SQL, Schedule (reunião) e Purchase
  (venda, com valor), com e-mail e telefone em SHA-256 e o clique do anúncio (fbc).
  Eventos com erro ficam numa fila e são reenviados.
- Google Ads: arquivo diário de conversões off-line (`api/google-conversoes.php`,
  protegido por chave) com GCLID, nome da conversão, data e valor. Sem dados pessoais.
- A landing page guarda fbclid, gclid e UTMs por 90 dias no navegador do visitante.

## Onde ficam os leads (e por que o deploy não apaga)

O banco SQLite fica em `~/jp_crm_dados/` no servidor, **um nível acima do `public_html`**
e fora deste repositório:

```
~/jp_crm_dados/
  crm.sqlite     banco de dados (leads, anotações, usuário)
  segredo.key    chave aleatória gerada no servidor
  sessoes/       sessões do painel
  backups/       cópia diária do banco (últimos 30 dias)
```

O deploy (`.cpanel.yml`) só copia código para `~/public_html/jp/` e nunca apaga nada,
então atualizar a página ou o CRM não mexe nos leads. Mudanças futuras no banco são
feitas em `crm_migrar()` (`inc/crm.php`), sempre acrescentando, nunca apagando.

Requisito do servidor: PHP 8+ com a extensão **pdo_sqlite** ativa
(cPanel → Select PHP Version → Extensions).

## Primeiro acesso ao CRM

1. Faça o deploy e abra `https://pedroselestrim.com/jp/admin`.
2. Na tela **Primeiro acesso**, informe a chave de instalação (enviada em particular,
   nunca no repositório), o usuário e a senha.
3. Pronto: a tela de instalação desaparece e passa a valer só o login.

A senha nunca fica no código: só o hash dela, guardado no banco, fora do site.

## Segurança

- Senhas com hash bcrypt; nada sensível no repositório (que é público).
- Login com bloqueio: 5 senhas erradas por IP em 15 min bloqueiam o IP por 15 min.
- Sessão com cookie `HttpOnly`, `Secure` e `SameSite=Strict`, expira após 2 h parado
  (12 h no máximo) e cai se o navegador mudar. Trocar a senha encerra outras sessões.
- Proteção CSRF em todas as ações do painel.
- Tudo que vem do formulário é limpo, validado e exibido como texto (contra XSS).
- Consultas ao banco só com parâmetros (contra SQL injection).
- Cabeçalhos de segurança (CSP, X-Frame-Options, nosniff) e `noindex` no painel.
- API do formulário: só POST, só do próprio site, limite de 5 envios por IP por hora,
  isca contra robôs. O CSV exportado neutraliza fórmulas.

## Deploy no cPanel

### Primeira vez

1. No cPanel, abra **Git™ Version Control** → **Create**.
2. **Clone URL:** `https://github.com/pedrosel10/landing-page-JP.git` ·
   **Repository Path:** `repositories/landing-page-JP` (fora do `public_html`).
3. **Manage** → **Pull or Deploy** → **Deploy HEAD Commit**.

### Atualizações

1. `git push` para o GitHub.
2. cPanel → **Git™ Version Control** → **Manage** → **Pull or Deploy** →
   **Update from Remote** e depois **Deploy HEAD Commit**.

## Teste local

```bash
php -S 127.0.0.1:4322 -t landing-page
```

Os dados locais ficam em `../jp_crm_dados/` (fora do repositório).

## Teste A/B da headline

`https://pedroselestrim.com/jp/?v=b` mostra a variante "Pare de contratar marketing em pedaços".
