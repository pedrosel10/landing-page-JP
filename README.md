# Landing page — Assessoria de marketing completa

Página única (HTML + CSS + JS em `index.html`) com os logos em `logos/`.
Publicada em `https://SEU-DOMINIO/jp` via cPanel.

## Deploy no cPanel

### Primeira vez

1. No cPanel, abra **Git™ Version Control** → **Create**.
2. Preencha:
   - **Clone a Repository:** ativado
   - **Clone URL:** `https://github.com/pedrosel10/landing-page-JP.git`
   - **Repository Path:** `repositories/landing-page-JP` (fora do `public_html`)
   - **Repository Name:** `landing-page-JP`
3. Clique em **Create** e aguarde o clone.
4. Na lista, clique em **Manage** → aba **Pull or Deploy** → **Deploy HEAD Commit**.
5. Acesse `https://SEU-DOMINIO/jp`.

O arquivo `.cpanel.yml` copia `index.html` e `logos/` para `~/public_html/jp/`.
Se o domínio for um domínio adicional (document root fora do `public_html`),
ajuste o `DEPLOYPATH` no `.cpanel.yml` antes do deploy.

### Atualizações

1. Envie as alterações para o GitHub (`git push`).
2. No cPanel: **Git™ Version Control** → **Manage** → **Pull or Deploy**.
3. Clique em **Update from Remote** e depois em **Deploy HEAD Commit**.

## Antes de divulgar

- Configure `FORM_ENDPOINT` no fim do `index.html` (webhook do CRM, n8n, Make etc.).
  Sem isso, o formulário mostra a mensagem de sucesso mas **o lead não é salvo**.
- Busque por `TROCAR` e `CONFERIR` no `index.html` para os pontos a personalizar
  (marca, cases reais, fotos, CNPJ, política de privacidade).

## Teste A/B da headline

`https://SEU-DOMINIO/jp/?v=b` mostra a variante "Pare de contratar marketing em pedaços".
