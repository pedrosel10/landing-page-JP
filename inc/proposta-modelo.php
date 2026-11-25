<?php
/* Modelo da página de proposta. Não é aberto direto: quem usa é proposta/index.php
   (link do cliente) e admin/proposta-exemplo.php (prévia com dados de exemplo).
   Recebe: $propostaJson (dados do cliente, já em JSON seguro), $paginaTitulo,
   $paginaDescricao e $nonce (libera só o script desta página). */
if (!defined('CRM_PROPOSTA_MODELO')) {
    http_response_code(404);
    exit;
}
?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<meta name="referrer" content="no-referrer">
<title><?= h($paginaTitulo) ?></title>
<meta property="og:title" content="<?= h($paginaTitulo) ?>">
<meta property="og:description" content="<?= h($paginaDescricao) ?>">
<meta name="description" content="<?= h($paginaDescricao) ?>">
<link rel="icon" href="../marca/icone.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Caveat:wght@500;600;700&family=Instrument+Serif:ital@0;1&family=Inter+Tight:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --paper:#EEE9E1;--paper-2:#E6DFD3;--card:#F6F2EC;--sheet:#FBF9F5;--note:#EADFC9;
  --ink:#1E1412;--ink-2:#5E514B;--mute:#8F847B;
  --rule:rgba(30,20,18,.17);--rule-2:rgba(30,20,18,.08);
  --wine:#7A2E33;--wine-h:#64242A;--wine-lt:#C9575E;--wine-tint:rgba(122,46,51,.07);
  --brown:#2A1512;--brown-2:#361E1A;--brown-3:#48302B;
  --on-dark:#F1ECE4;--on-dark-mute:#A39188;--rule-dark:rgba(241,236,228,.13);
  --tape:rgba(206,190,158,.62);
  --f:"Inter Tight",system-ui,-apple-system,"Segoe UI",sans-serif;
  --serif:"Instrument Serif",Georgia,serif;
  --hand:"Caveat","Bradley Hand",cursive;
  --mono:"JetBrains Mono",ui-monospace,SFMono-Regular,Menlo,monospace;
  --max:1320px;--gut:clamp(16px,3vw,36px);--hdr:58px;
  --ease:cubic-bezier(.16,1,.3,1);
  --estrela:url("data:image/svg+xml,%3Csvg viewBox='0 0 35 35' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M17.4999 17.5L14.5927 31.432C14.2057 33.2727 15.6121 35 17.4999 35C19.3783 35 20.7847 33.2727 20.4072 31.432L17.4999 17.5ZM17.5 17.5L25.2967 29.4026C26.3255 30.979 28.5437 31.2055 29.8746 29.8746C31.2055 28.5437 30.979 26.3255 29.4026 25.2967L17.5 17.5ZM17.5 17.501L31.432 20.4083C33.2727 20.7953 35 19.3888 35 17.501C35 15.6227 33.2727 14.2162 31.432 14.5938L17.5 17.501ZM17.5 17.4995L29.4026 9.70287C30.979 8.67401 31.2055 6.45583 29.8746 5.12493C28.5437 3.79402 26.3255 4.02056 25.2967 5.59688L17.5 17.4995ZM17.5001 17.5L20.4073 3.56796C20.7943 1.72735 19.3879 0 17.5001 0C15.6217 0 14.2153 1.72735 14.5928 3.56796L17.5001 17.5ZM17.4999 17.4995L9.70323 5.59688C8.67438 4.02056 6.4562 3.79402 5.12529 5.12493C3.79439 6.45583 4.02092 8.67401 5.59725 9.70287L17.4999 17.4995ZM17.5 17.5004L3.56796 14.5932C1.72735 14.2062 0 15.6126 0 17.5004C0 19.3788 1.72735 20.7852 3.56796 20.4077L17.5 17.5004ZM17.4999 17.5L5.59725 25.2967C4.02092 26.3255 3.79439 28.5437 5.12529 29.8746C6.4562 31.2055 8.67438 30.979 9.70323 29.4026L17.4999 17.5Z'/%3E%3C/svg%3E");
}
*,*::before,*::after{box-sizing:border-box}
html{-webkit-text-size-adjust:100%}
@media (prefers-reduced-motion:no-preference){html{scroll-behavior:smooth}}
body{margin:0;background:var(--paper);color:var(--ink);font:400 16px/1.55 var(--f);-webkit-font-smoothing:antialiased;overflow-x:clip}
body::after{content:"";position:fixed;inset:0;pointer-events:none;z-index:80;opacity:.6;mix-blend-mode:multiply;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='180' height='180'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='3' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 .12 0 0 0 0 .08 0 0 0 0 .07 0 0 0 .085 0'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E")}
img{max-width:100%;display:block}
button{font:inherit;color:inherit}
a{color:inherit}
h1,h2,h3,h4,p,ul,ol,dl,dd,figure{margin:0}
ul,ol{padding:0;list-style:none}
.sr{position:absolute!important;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap}
.mono{font-family:var(--mono);font-size:11.5px;line-height:1.5;letter-spacing:.12em;text-transform:uppercase}
.mini{font-size:11px;color:var(--ink-2)}
.dark .mini{color:var(--on-dark-mute)}

/* Moldura: as linhas verticais da grade correm a página inteira */
.wrap{max-width:var(--max);margin:0 auto;border-left:1px solid var(--rule);border-right:1px solid var(--rule);position:relative}
.sec>.wrap:first-child{border-top:1px solid var(--rule)}
.dark{background:var(--brown);color:var(--on-dark)}
.dark .wrap,.dark .sec>.wrap:first-child{border-color:var(--rule-dark)}
.pad{padding:clamp(64px,9vw,128px) var(--gut)}
[data-nome]{scroll-margin-top:var(--hdr)}

/* Tipografia */
.eyebrow{display:inline-flex;align-items:center;gap:10px;margin-bottom:22px;font:500 11.5px/1 var(--mono);letter-spacing:.15em;text-transform:uppercase;color:var(--ink-2)}
.eyebrow::before{content:"";width:12px;height:12px;flex:none;background:var(--wine);-webkit-mask:var(--estrela) center/contain no-repeat;mask:var(--estrela) center/contain no-repeat}
.dark .eyebrow{color:var(--on-dark-mute)}
.dark .eyebrow::before{background:var(--wine-lt)}
.display{font-weight:800;text-transform:uppercase;letter-spacing:-.055em;line-height:.84}
.h2{font-size:clamp(36px,5.2vw,72px);line-height:.98;letter-spacing:-.042em;font-weight:600;text-wrap:balance}
.h2-s{font-size:clamp(30px,3.8vw,52px)}
.h3{font-size:clamp(26px,2.6vw,36px);line-height:1.04;letter-spacing:-.035em;font-weight:600;text-wrap:balance}
.h2 em,.h3 em,.serif{font-family:var(--serif);font-style:italic;font-weight:400;letter-spacing:-.012em;color:var(--ink-2)}
.h2 em,.h3 em{font-size:1.07em}
.dark .h2 em,.dark .h3 em,.dark .serif{color:var(--on-dark-mute)}
.lead{font-size:clamp(17px,1.45vw,20px);line-height:1.5;color:var(--ink-2);max-width:58ch}
.dark .lead{color:var(--on-dark-mute)}
.hand{font-family:var(--hand);font-weight:600;color:var(--wine);font-size:clamp(22px,2vw,28px);line-height:1.02;letter-spacing:0;text-transform:none}
.hand.tinta{color:var(--ink)}
.dark .hand{color:var(--wine-lt)}
.estrela-i{display:inline-block;width:.8em;height:.8em;background:currentColor;-webkit-mask:var(--estrela) center/contain no-repeat;mask:var(--estrela) center/contain no-repeat;vertical-align:-.05em}

/* Elementos de papel */
.fita{position:absolute;z-index:3;width:96px;height:28px;top:-13px;left:50%;background:var(--tape);transform:translateX(-50%) rotate(-4deg);mix-blend-mode:multiply;box-shadow:0 1px 0 rgba(0,0,0,.05)}
.polaroid{position:relative;background:var(--sheet);padding:12px 12px 18px;box-shadow:0 22px 44px -22px rgba(42,21,18,.5),0 2px 6px rgba(42,21,18,.12);transform:rotate(var(--r,0deg))}
.carimbo{display:inline-block;font:600 11.5px/1 var(--mono);letter-spacing:.18em;text-transform:uppercase;color:var(--wine);border:2px solid currentColor;padding:8px 11px 7px;border-radius:4px;transform:rotate(-7deg);opacity:.88}
.rv .carimbo,.rv.carimbo{transition:transform .55s cubic-bezier(.5,1.8,.5,1) calc(var(--d,0s) + .5s),opacity .3s calc(var(--d,0s) + .5s)}
.rv:not(.on) .carimbo,.rv.carimbo:not(.on){transform:rotate(-14deg) scale(1.7);opacity:0}
.pill{display:inline-flex;align-items:center;gap:8px;border:1px solid var(--rule);border-radius:999px;padding:8px 13px 7px;font:500 11px/1 var(--mono);letter-spacing:.12em;text-transform:uppercase;color:var(--ink-2);background:transparent}
.dark .pill{border-color:var(--rule-dark);color:var(--on-dark-mute)}
.pill-vinho{background:var(--wine);border-color:var(--wine);color:#F6F1EA}
.btn{display:inline-flex;align-items:center;gap:12px;background:var(--wine);color:#F6F1EA;font:500 16px/1 var(--f);padding:18px 24px;border-radius:6px;text-decoration:none;border:0;cursor:pointer;transition:background .2s,transform .2s}
.btn:hover{background:var(--wine-h);transform:translateY(-1px)}
.btn svg{width:18px;height:18px}

/* Revelação no scroll */
.rv{opacity:0;transform:translateY(28px);transition:opacity .9s var(--ease) var(--d,0s),transform .9s var(--ease) var(--d,0s)}
.rv.on{opacity:1;transform:none}
.polaroid.rv{transform:translateY(40px) rotate(calc(var(--r,0deg) * 2.2))}
.polaroid.rv.on{transform:rotate(var(--r,0deg))}

/* Setas desenhadas à mão (desenham quando o bloco aparece) */
.seta{fill:none;stroke:var(--wine);stroke-width:2.3;stroke-linecap:round;stroke-linejoin:round;overflow:visible;display:block}
.dark .seta{stroke:var(--wine-lt)}
.seta path{stroke-dasharray:1 2;stroke-dashoffset:1.02;transition:stroke-dashoffset .9s var(--ease) calc(var(--d,0s) + .35s)}
.seta path+path{transition-duration:.35s;transition-delay:calc(var(--d,0s) + 1.1s)}
.on .seta path,.seta.on path{stroke-dashoffset:0}

/* Cabeçalho */
.hdr{position:fixed;top:0;left:0;right:0;z-index:60;background:rgba(238,233,225,.9);-webkit-backdrop-filter:blur(12px);backdrop-filter:blur(12px)}
.hdr .wrap{display:grid;grid-template-columns:auto 1fr auto auto;align-items:stretch;height:var(--hdr);border-bottom:1px solid var(--rule)}
.hdr .wrap>*{display:flex;align-items:center;padding:0 clamp(14px,1.6vw,22px)}
.hdr .wrap>*+*{border-left:1px solid var(--rule)}
.hdr-logo{gap:9px;color:var(--ink);text-decoration:none}
.hdr-logo .lg-icone{height:28px;width:auto;color:var(--wine)}
.hdr-logo .lg-texto{height:19px;width:auto;color:var(--ink)}
.hdr-meta{flex-direction:column;align-items:flex-start!important;justify-content:center;gap:1px;font-size:10.5px;color:var(--ink-2)}
.hdr-meta b{color:var(--ink);font-weight:600}
.hdr-nav{gap:2px}
.hdr-nav a{font:500 10.5px/1 var(--mono);letter-spacing:.13em;text-transform:uppercase;text-decoration:none;color:var(--ink-2);padding:9px 10px;border-radius:4px;transition:background .2s,color .2s}
.hdr-nav a:hover,.hdr-nav a.ativo{color:var(--ink);background:var(--rule-2)}
.hdr-conta{gap:10px;font-size:10.5px;min-width:190px;justify-content:flex-end}
.hdr-conta b{color:var(--wine);font-weight:600}
.hdr-prog{position:absolute;left:0;right:0;bottom:-1px;height:2px}
.hdr-prog i{display:block;height:100%;background:var(--wine);transform-origin:0 50%;transform:scaleX(0)}
main{padding-top:var(--hdr);overflow-x:clip}
@media (max-width:1080px){.hdr-nav{display:none!important}}
@media (max-width:640px){.hdr-meta{display:none!important}.hdr .wrap{grid-template-columns:1fr auto}.hdr-conta{min-width:0}}

/* Controles fixos de apresentação */
.ctl{position:fixed;right:18px;bottom:18px;z-index:55;display:flex;align-items:center;gap:6px;background:var(--brown);color:var(--on-dark);border-radius:999px;padding:6px;box-shadow:0 10px 30px -10px rgba(42,21,18,.6)}
.ctl button{width:38px;height:38px;border-radius:50%;border:0;background:transparent;color:inherit;cursor:pointer;display:grid;place-items:center;transition:background .2s}
.ctl button:hover{background:rgba(255,255,255,.1)}
.ctl svg{width:16px;height:16px}
.ctl span{font-size:10px;color:var(--on-dark-mute);padding:0 4px}
@media (pointer:coarse){.ctl{display:none}}
.aviso-exemplo{display:none;position:fixed;left:18px;bottom:18px;z-index:55;background:var(--wine);color:#F6F1EA;border-radius:999px;padding:9px 14px 8px;font-size:10px}
.is-exemplo .aviso-exemplo{display:block}

/* Selo giratório */
.selo-giro{width:clamp(104px,10vw,136px);height:auto;color:var(--ink);animation:giro 26s linear infinite}
.selo-giro text{font:600 12.5px var(--mono);letter-spacing:.06em;fill:currentColor}
.selo-giro .miolo{fill:var(--wine)}
.dark .selo-giro{color:var(--on-dark)}
.dark .selo-giro .miolo{fill:var(--wine-lt)}
@keyframes giro{to{transform:rotate(360deg)}}

/* ============ 00 CAPA ============ */
.hero .wrap{border-top:0!important}
.hero-l1{padding:clamp(26px,3.6vw,44px) var(--gut) 0}
.hero-l1 .eyebrow{margin-bottom:clamp(14px,2vw,22px)}
.hero-h1{font-size:clamp(52px,17vw,246px);white-space:nowrap}
.hero-meio{position:relative;display:grid;grid-template-columns:1fr minmax(280px,1.3fr) 1fr;border-top:1px solid var(--rule);border-bottom:1px solid var(--rule);margin-top:clamp(14px,2vw,22px)}
.hero-col{padding:clamp(22px,2.6vw,34px) var(--gut);display:flex;flex-direction:column;gap:18px}
.hero-col.esq{border-right:1px solid var(--rule)}
.hero-col.dir{border-left:1px solid var(--rule);justify-content:flex-end}
.hero-lema{font-weight:700;text-transform:uppercase;letter-spacing:-.02em;font-size:clamp(18px,1.7vw,23px);line-height:1.02}
.traco{display:block;width:34px;height:2px;background:var(--ink)}
.hero-col .selo-giro{margin-top:auto}
.hero-col.dir p:first-child{font-size:17px;line-height:1.4;color:var(--ink-2);max-width:26ch}
.hero-col.dir p:first-child b{color:var(--ink);font-weight:600}
.hero-estrela{position:relative;display:grid;place-items:center;min-height:clamp(320px,37vw,500px);overflow:hidden}
.mira{position:absolute;inset:0;width:100%;height:100%;fill:none;stroke:var(--rule);stroke-width:1}
.mira .tr{stroke-dasharray:3 5}
.estrela-hero{position:relative;width:min(70%,390px);aspect-ratio:1;color:var(--wine);filter:drop-shadow(0 30px 44px rgba(122,46,51,.28));will-change:transform}
.estrela-hero svg{width:100%;height:100%;fill:currentColor}
.nota-colada{position:absolute;z-index:4;right:calc(33.333% - 54px);top:30px;width:154px;padding:22px 16px 18px;background:var(--note);transform:rotate(6deg);box-shadow:0 14px 26px -16px rgba(42,21,18,.5);text-align:center}
.nota-colada .hand{font-size:24px}
.hero-l3{display:grid;grid-template-columns:auto 1fr auto;align-items:stretch;border-bottom:1px solid var(--rule)}
.hero-l3>*{display:flex;align-items:center;padding:clamp(14px,1.8vw,24px) var(--gut)}
.h-pesado{font-size:clamp(46px,9.6vw,138px);border-right:1px solid var(--rule)}
.h-italico{font-size:clamp(42px,7.6vw,116px);line-height:.95;color:var(--ink-2)}
.hero-acoes{border-left:1px solid var(--rule);font-size:11px;line-height:1.7;color:var(--ink-2)}
.hero-rod{display:grid;grid-template-columns:repeat(4,1fr)}
.hero-rod>*{padding:16px var(--gut);font-size:10.5px;color:var(--ink-2)}
.hero-rod>*+*{border-left:1px solid var(--rule)}
.hero-rod b{color:var(--ink);font-weight:600}
.rolar{display:flex;align-items:center;gap:8px}
.rolar i{width:7px;height:7px;border-radius:50%;background:var(--wine);animation:pisca 1.6s ease-in-out infinite}
@keyframes pisca{50%{opacity:.25}}
@media (max-width:860px){
  .hero-meio{grid-template-columns:1fr 1fr}
  .hero-estrela{grid-column:1/-1;order:-1;border-bottom:1px solid var(--rule)}
  .hero-col.esq{border-right:0}
  .nota-colada{right:14px;top:16px;width:128px;padding:18px 12px 14px}
  .nota-colada .hand{font-size:20px}
  .hero-l3{grid-template-columns:1fr}
  .h-pesado{border-right:0;border-bottom:1px solid var(--rule)}
  .hero-acoes{display:none!important}
  .hero-rod{grid-template-columns:1fr 1fr}
  .hero-rod>*:nth-child(3){border-left:0}
  .hero-rod>*:nth-child(n+3){border-top:1px solid var(--rule)}
}
@media (max-width:560px){
  .hero-meio{grid-template-columns:1fr}
  .hero-col.dir{border-left:0;border-top:1px solid var(--rule)}
  .hero-col .selo-giro{display:none}
}

/* ============ 01 MANIFESTO ============ */
.mani-topo{display:grid;grid-template-columns:auto 1fr auto}
.mani-topo>*{padding:clamp(16px,2vw,26px) var(--gut);display:flex;align-items:center}
.mani-a,.mani-c{font-size:clamp(40px,8.2vw,118px);border-right:1px solid var(--rule)}
.mani-b{grid-column:2/4;font-size:clamp(38px,6.6vw,100px);line-height:.95}
.mani-a,.mani-b{border-bottom:1px solid var(--rule)}
.mani-c{color:var(--wine)}
.mani-btn{position:relative;justify-content:center;gap:18px;flex-wrap:wrap}
.btn-publicar{position:relative;display:inline-flex;align-items:center;gap:8px;background:#1877F2;color:#fff;font:600 15px/1 var(--f);padding:12px 20px;border-radius:8px;box-shadow:0 10px 24px -12px rgba(24,119,242,.7);filter:saturate(.55)}
.btn-publicar::after{content:"";position:absolute;inset:-14px -20px;border:2.4px solid var(--wine);border-radius:50%;transform:rotate(-4deg)}
.mani-btn .hand{font-size:22px;max-width:15ch}
.mani-d{border-left:1px solid var(--rule);font-size:11px;line-height:1.8;color:var(--ink-2)}
.mani-corpo{display:grid;grid-template-columns:minmax(200px,.6fr) 2fr;border-top:1px solid var(--rule)}
.mani-lado{padding:clamp(32px,4vw,56px) var(--gut);border-right:1px solid var(--rule)}
.mani-lado .num{font:500 64px/1 var(--mono);color:var(--rule);letter-spacing:-.04em;margin-top:24px}
.scrub-box{padding:clamp(40px,6vw,88px) var(--gut) clamp(56px,7vw,104px)}
.scrub-box p{font-size:clamp(25px,3.15vw,44px);line-height:1.17;letter-spacing:-.03em;font-weight:500;max-width:30ch}
.scrub-box p+p{margin-top:.62em}
.w{color:rgba(30,20,18,.15);transition:color .35s}
.w.on{color:var(--ink)}
.scrub-box mark{background:none;color:inherit;position:relative;white-space:nowrap}
.scrub-box mark .w.on{color:var(--wine)}
.sublinha{position:absolute;left:-4%;bottom:-.18em;width:108%;height:.42em;overflow:visible}
.sublinha path{fill:none;stroke:var(--wine);stroke-width:3;stroke-linecap:round;stroke-dasharray:1 2;stroke-dashoffset:1.02;transition:stroke-dashoffset .8s var(--ease)}
.sublinha.on path{stroke-dashoffset:0}
@media (max-width:760px){
  .mani-topo{grid-template-columns:1fr}
  .mani-a,.mani-c{border-right:0}
  .mani-b{grid-column:auto}
  .mani-c{border-bottom:1px solid var(--rule)}
  .mani-d{border-left:0;border-top:1px solid var(--rule)}
  .mani-btn{padding-top:34px!important;padding-bottom:34px!important}
  .mani-corpo{grid-template-columns:1fr}
  .mani-lado{border-right:0;border-bottom:1px solid var(--rule)}
  .mani-lado .num{display:none}
}

/* ============ 02 QUEM SOMOS ============ */
.bento{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
.b-card{position:relative;background:var(--card);border:1px solid var(--rule);border-radius:20px;padding:clamp(22px,2.4vw,32px);display:flex;flex-direction:column;gap:12px;overflow:hidden}
.b-grande{grid-column:span 2;grid-row:span 2;justify-content:space-between;gap:28px}
.b-grande .h2{font-size:clamp(34px,4.2vw,58px)}
.b-soma{display:flex;flex-wrap:wrap;align-items:center;gap:8px;font:500 14px var(--mono);color:var(--mute)}
.b-num .mono{font-size:10.5px;color:var(--ink-2);display:flex;align-items:center;gap:8px}
.b-num b{font-weight:700;font-size:clamp(42px,4.6vw,66px);line-height:.9;letter-spacing:-.05em;margin-top:auto}
.b-num p{font-size:14px;color:var(--ink-2);line-height:1.4}
.b-card.escuro{background:var(--brown);color:var(--on-dark);border-color:var(--brown)}
.b-card.escuro .mono,.b-card.escuro p{color:var(--on-dark-mute)}
.b-card.vinho{background:var(--wine);color:#F6F1EA;border-color:var(--wine)}
.b-card.vinho .mono,.b-card.vinho p{color:rgba(246,241,234,.72)}
.b-card.vinho b{font-size:clamp(32px,3.2vw,46px)}
.b-num{min-height:210px}
@media (max-width:900px){.bento{grid-template-columns:1fr 1fr}.b-grande{grid-column:1/-1;grid-row:auto}.b-num{min-height:180px}}
@media (max-width:420px){.b-num b{font-size:38px}.b-card.vinho b{font-size:27px}}
.faixa{background:var(--brown);color:var(--on-dark);overflow:hidden;border-top:1px solid var(--brown);white-space:nowrap}
.faixa-trilho{display:inline-flex;animation:corre 38s linear infinite;padding:18px 0}
.faixa-trilho span{display:inline-flex;align-items:center;gap:26px;padding-right:26px;font-weight:700;text-transform:uppercase;letter-spacing:-.01em;font-size:clamp(17px,1.6vw,22px)}
.faixa-trilho span i{width:16px;height:16px;background:var(--wine-lt);-webkit-mask:var(--estrela) center/contain no-repeat;mask:var(--estrela) center/contain no-repeat}
@keyframes corre{to{transform:translateX(-50%)}}
.logos-wrap{display:grid;grid-template-columns:minmax(200px,auto) 1fr;align-items:center;border-top:0!important}
.logos-wrap>p{padding:28px var(--gut);border-right:1px solid var(--rule);color:var(--ink-2);font-size:10.5px;max-width:30ch}
.logos{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-around;gap:22px 36px;padding:24px var(--gut)}
.logos img{height:clamp(22px,2.2vw,30px);width:auto;filter:grayscale(1) contrast(1.1);mix-blend-mode:multiply;opacity:.62}
@media (max-width:700px){.logos-wrap{grid-template-columns:1fr}.logos-wrap>p{border-right:0;border-bottom:1px solid var(--rule);max-width:none}.logos{justify-content:center}}

/* ============ 03 VISÃO E VALORES ============ */
.lema{display:flex;flex-direction:column;gap:4px;margin:6px 0 clamp(44px,6vw,72px);overflow:hidden}
.lema .display{font-size:clamp(46px,9.4vw,136px);will-change:transform}
.lema .serif{font-size:clamp(44px,8.6vw,128px);line-height:.95;color:var(--wine-lt)!important;padding-left:clamp(0px,8vw,140px);will-change:transform}
.mv{display:grid;grid-template-columns:1fr 1fr;border-top:1px solid var(--rule-dark)}
.mv>div{padding:28px 0 0}
.mv>div+div{border-left:1px solid var(--rule-dark);padding-left:clamp(20px,3vw,40px)}
.mv>div:first-child{padding-right:clamp(20px,3vw,40px)}
.mv .mono{color:var(--wine-lt);font-size:10.5px}
.mv p{font-size:clamp(19px,1.8vw,24px);line-height:1.35;letter-spacing:-.015em;margin-top:12px;color:var(--on-dark)}
@media (max-width:760px){.mv{grid-template-columns:1fr}.mv>div+div{border-left:0;padding-left:0;border-top:1px solid var(--rule-dark);margin-top:28px}.mv>div:first-child{padding-right:0}}
.val-head{padding:clamp(48px,6vw,80px) var(--gut) clamp(28px,3vw,40px)}
.valores{display:grid;grid-template-columns:repeat(3,1fr);border-top:1px solid var(--rule-dark)}
.valores li{position:relative;padding:clamp(26px,3vw,40px) var(--gut) clamp(34px,4vw,52px);border-bottom:1px solid var(--rule-dark);min-height:250px;display:flex;flex-direction:column;gap:14px}
.valores li:not(:nth-child(3n+1)){border-left:1px solid var(--rule-dark)}
.valores .n{font:500 12px var(--mono);color:var(--on-dark-mute);letter-spacing:.1em}
.valores h3{font-size:clamp(23px,2.1vw,30px);letter-spacing:-.03em;line-height:1.05;font-weight:600}
.valores p{color:var(--on-dark-mute);font-size:16px;line-height:1.5;max-width:34ch}
.valores li::after{content:"";position:absolute;top:clamp(26px,3vw,40px);right:var(--gut);width:18px;height:18px;background:var(--wine-lt);-webkit-mask:var(--estrela) center/contain no-repeat;mask:var(--estrela) center/contain no-repeat;opacity:0;transform:rotate(-90deg) scale(.4);transition:all .6s var(--ease) calc(var(--d,0s) + .3s)}
.valores li.on::after{opacity:1;transform:none}
.valores li.destaque{background:rgba(201,87,94,.07)}
.valores .hand{margin-top:auto;font-size:24px;display:flex;align-items:flex-end;gap:8px}
.valores .hand svg{width:46px;height:36px;flex:none}
@media (max-width:900px){.valores{grid-template-columns:1fr 1fr}.valores li:not(:nth-child(3n+1)){border-left:0}.valores li:nth-child(even){border-left:1px solid var(--rule-dark)}}
@media (max-width:600px){.valores{grid-template-columns:1fr}.valores li:nth-child(even){border-left:0}.valores li{min-height:0}}

/* ============ 04 O SEU CENÁRIO ============ */
.cen-head{margin-bottom:clamp(40px,5vw,64px)}
.cen-grid{display:grid;grid-template-columns:1.05fr 1fr;gap:clamp(28px,4vw,64px);align-items:start}
.cen-grid.sem-notas{grid-template-columns:minmax(0,720px)}
.ficha{position:relative;background:var(--sheet);border:1px solid var(--rule);border-radius:4px;box-shadow:0 24px 50px -30px rgba(42,21,18,.45)}
.ficha::before{content:"";position:absolute;inset:0;background:repeating-linear-gradient(transparent 0 35px,var(--rule-2) 35px 36px);pointer-events:none;opacity:.6}
.ficha-top{display:flex;justify-content:space-between;gap:12px;padding:16px 22px;border-bottom:1px solid var(--rule);font-size:10.5px;color:var(--ink-2)}
.ficha-top b{color:var(--wine);font-weight:600}
.ficha dl{position:relative;padding:8px 22px 24px}
.ficha dl>div{display:grid;grid-template-columns:minmax(120px,.8fr) 1.4fr;gap:14px;padding:15px 0;border-bottom:1px dashed var(--rule)}
.ficha dt{font:500 10.5px/1.5 var(--mono);letter-spacing:.12em;text-transform:uppercase;color:var(--mute);padding-top:3px}
.ficha dd{font-size:17px;font-weight:500;line-height:1.35}
.ficha dd.forte{color:var(--wine)}
.ficha .carimbo{position:absolute;right:18px;bottom:-20px;background:var(--sheet)}
.ficha-pe{padding:0 22px 20px;font-size:10px;color:var(--mute)}
.notas-tit{font-size:10.5px;color:var(--ink-2);margin-bottom:26px}
.notas{display:flex;flex-direction:column;gap:22px}
.nota{position:relative;background:var(--note);padding:26px 24px 22px;box-shadow:0 16px 30px -20px rgba(42,21,18,.55);transform:rotate(var(--r,0deg))}
.nota.rv{transform:translateY(30px) rotate(calc(var(--r,0deg) * 3))}
.nota.rv.on{transform:rotate(var(--r,0deg))}
.nota .mono{font-size:10.5px;color:var(--wine);display:block;margin-bottom:6px}
.nota .hand{font-size:clamp(23px,2vw,27px);line-height:1.08}
.nota:nth-child(3){margin-left:6%}.nota:nth-child(4){margin-right:5%}
.cen-pergunta{display:flex;align-items:flex-start;gap:12px;margin:clamp(44px,5vw,64px) 0 0 clamp(0px,6vw,90px)}
.cen-pergunta svg{width:74px;height:60px;flex:none;transform:scaleY(-1)}
.cen-pergunta .hand{font-size:clamp(28px,2.6vw,36px);margin-top:28px}
@media (max-width:860px){.cen-grid{grid-template-columns:1fr}.ficha dl>div{grid-template-columns:1fr;gap:4px}}

/* ============ 05 O PROBLEMA ============ */
.prob-head{display:grid;grid-template-columns:1.2fr 1fr;gap:clamp(24px,4vw,64px);align-items:end;margin-bottom:clamp(36px,5vw,60px)}
.vaza{position:relative;background:var(--card);border:1px solid var(--rule);border-radius:20px;padding:clamp(18px,2.6vw,36px) clamp(10px,2vw,28px) 0;overflow:hidden}
.vaza svg{width:100%;height:auto;display:block;overflow:visible}
.vz-l{stroke:var(--ink);stroke-width:2}
.vz-no rect{fill:var(--sheet);stroke:var(--ink);stroke-width:1.5}
.vz-no text{font:600 12px var(--mono);letter-spacing:.1em;text-transform:uppercase;fill:var(--ink)}
.vz-no.ini rect{fill:var(--ink)}
.vz-no.ini text{fill:var(--on-dark)}
.vz-no.fim rect{fill:var(--wine);stroke:var(--wine);transform-box:fill-box;transform-origin:center}
.vz-no.fim text{fill:#F6F1EA}
.vz-d{fill:var(--ink)}
.vz-d.cai{fill:var(--wine-lt)}
.vz-fuga{font:600 21px var(--hand);fill:var(--wine)}
.vz-crack{stroke:var(--wine);stroke-width:2;stroke-linecap:round;fill:none}
.vaza-placar{display:grid;grid-template-columns:repeat(3,1fr);border-top:1px solid var(--rule);margin:clamp(10px,2vw,22px) calc(-1 * clamp(10px,2vw,28px)) 0;font-size:10.5px;color:var(--ink-2)}
.vaza-placar>span{padding:16px clamp(14px,2vw,28px);display:flex;flex-direction:column;gap:6px}
.vaza-placar>span+span{border-left:1px solid var(--rule)}
.vaza-placar b{font:700 clamp(26px,2.6vw,36px)/1 var(--f);letter-spacing:-.04em;color:var(--ink)}
.vaza-placar>span:last-child b{color:var(--wine)}
.pecas{display:grid;grid-template-columns:repeat(4,1fr);border:1px solid var(--rule);border-radius:20px;margin-top:14px;overflow:hidden}
.peca{position:relative;padding:26px clamp(18px,2vw,26px) 30px;display:flex;flex-direction:column;gap:10px;min-height:200px}
.peca+.peca{border-left:1px solid var(--rule)}
.peca .mono{font-size:10.5px;color:var(--mute)}
.peca h3{font-size:22px;font-weight:600;letter-spacing:-.03em}
.peca p{color:var(--ink-2);font-size:15.5px;line-height:1.45}
.peca .carimbo{margin-top:auto;align-self:flex-start;font-size:10px;padding:7px 9px 6px}
.prob-voce{display:grid;grid-template-columns:auto 1fr;gap:18px;align-items:center;margin-top:clamp(40px,5vw,60px);padding:26px clamp(20px,3vw,36px);border:1.5px dashed var(--wine);border-radius:16px}
.prob-voce .hand{font-size:clamp(26px,2.5vw,34px);max-width:24ch}
.prob-voce p:last-child{color:var(--ink-2);font-size:17px;max-width:40ch}
@media (max-width:900px){.prob-head{grid-template-columns:1fr}.pecas{grid-template-columns:1fr 1fr}.peca:nth-child(3){border-left:0}.peca:nth-child(n+3){border-top:1px solid var(--rule)}.prob-voce{grid-template-columns:1fr}}
@media (max-width:560px){.pecas{grid-template-columns:1fr}.peca+.peca{border-left:0;border-top:1px solid var(--rule)}.peca{min-height:0}.vaza-placar{grid-template-columns:1fr}.vaza-placar>span+span{border-left:0;border-top:1px solid var(--rule)}.vaza-placar>span{flex-direction:row;justify-content:space-between;align-items:center}}

/* ============ Cenas fixas (pinned) ============ */
.cena{position:relative;height:var(--alt,300vh)}
.fixo{position:sticky;top:0;height:100vh;height:100svh;padding-top:var(--hdr);overflow:hidden}
.fixo>.wrap{height:100%;border-top:1px solid var(--rule)}
.dark .fixo>.wrap{border-top-color:var(--rule-dark)}

/* ============ 06 EM PEDAÇOS ============ */
.ped-in{display:flex;flex-direction:column;height:100%;padding:clamp(28px,4vh,52px) var(--gut) clamp(20px,3vh,36px)}
.ped-tit{display:grid}
.ped-tit>*{grid-area:1/1;transition:opacity .4s}
.ped-tit .h2{font-size:clamp(30px,4.4vw,62px);max-width:22ch}
.palco{position:relative;flex:1;min-height:0;margin:clamp(10px,2vh,24px) 0}
.palco-svg{position:absolute;inset:0;width:100%;height:100%;overflow:visible}
.palco-svg line{stroke:var(--ink);stroke-width:1.3;stroke-dasharray:4 6;opacity:.55}
.palco-linha{fill:none;stroke:var(--wine);stroke-width:3;stroke-dasharray:1 2;stroke-dashoffset:1.02;stroke-linecap:round}
.forn{position:absolute;left:0;top:0;width:clamp(150px,15.5vw,206px);background:var(--sheet);border:1px solid var(--rule);border-radius:12px;padding:15px 16px 16px;box-shadow:0 18px 36px -22px rgba(42,21,18,.55);will-change:transform;transition:border-color .4s,background .4s,box-shadow .4s}
.forn .forn-n{font:500 10px var(--mono);letter-spacing:.12em;text-transform:uppercase;color:var(--mute);display:flex;align-items:center;gap:6px}
.forn b{display:block;font-size:18px;letter-spacing:-.025em;line-height:1.1;margin:8px 0 10px;font-weight:600}
.forn .forn-m{font:500 10.5px var(--mono);letter-spacing:.08em;text-transform:uppercase;color:var(--ink-2);border-top:1px dashed var(--rule);padding-top:9px;display:block}
.forn .forn-m em{font-style:normal;color:var(--ink)}
.forn.unido{border-color:var(--wine);box-shadow:0 18px 40px -22px rgba(122,46,51,.6)}
.forn.unido .forn-n{color:var(--wine)}
.forn.unido .forn-m em{color:var(--wine)}
.ped-nota{position:absolute;z-index:5;transition:opacity .4s;pointer-events:none}
.ped-n1{left:52%;top:46%;transform:rotate(-4deg)}
.ped-n2{left:50%;bottom:4%;transform:translateX(-50%) rotate(-2deg);white-space:nowrap}
.ped-pills{display:flex;flex-wrap:wrap;gap:8px;transition:opacity .5s,transform .5s}
@media (max-width:760px){
  .forn{width:min(64vw,250px);padding:11px 14px 12px}
  .forn b{font-size:16px;margin:4px 0 6px}
  .forn .forn-m{padding-top:6px;font-size:10px}
  .ped-n1{left:auto;right:0;top:40%;max-width:9ch}
  .ped-n2{bottom:-2%}
  .ped-pills .pill{font-size:9.5px;padding:7px 10px 6px}
}

/* ============ 07 O SISTEMA ============ */
.sis-head{display:grid;grid-template-columns:1.1fr 1fr;gap:clamp(24px,4vw,64px);align-items:end;margin-bottom:clamp(36px,5vw,64px)}
.trilha{position:relative;padding:64px 0 96px 58px}
.trilha-svg{position:absolute;left:0;top:0;overflow:visible;pointer-events:none}
.trilha-base{fill:none;stroke:var(--rule);stroke-width:2;stroke-dasharray:2 7;stroke-linecap:round}
.trilha-linha{fill:none;stroke:var(--wine);stroke-width:3;stroke-linecap:round}
.trilha-volta{fill:none;stroke:var(--wine-lt);stroke-width:2;stroke-dasharray:1 2;stroke-dashoffset:1.02;transition:stroke-dashoffset 1.6s var(--ease)}
.trilha-volta.on{stroke-dashoffset:0}
.trilha-volta-seta{fill:none;stroke:var(--wine-lt);stroke-width:2;stroke-linecap:round;opacity:0;transition:opacity .3s 1.4s}
.trilha-volta.on~.trilha-volta-seta{opacity:1}
.lead-g circle{transition:fill .3s}
.lead-g .lead-anel{fill:none;stroke:var(--ink);stroke-width:1.5;opacity:.35}
.lead-g .lead-pt{fill:var(--ink)}
.lead-g text{font:600 10px var(--mono);letter-spacing:.12em;text-transform:uppercase;fill:var(--ink)}
.lead-g.venda .lead-pt{fill:var(--wine)}
.lead-g.venda .lead-anel{stroke:var(--wine)}
.lead-g.venda text{fill:var(--wine)}
.nos{display:flex;flex-direction:column;gap:clamp(28px,5vw,64px)}
.no{display:grid;grid-template-columns:1fr 160px 1fr;align-items:center}
.no-pino{grid-column:2;grid-row:1;width:18px;height:18px;border-radius:50%;background:var(--paper);border:2px solid var(--rule);position:relative;z-index:2;transition:all .4s var(--ease)}
.no[data-lado="e"] .no-pino{justify-self:start;margin-left:6px}
.no[data-lado="d"] .no-pino{justify-self:end;margin-right:6px}
.no.on .no-pino{border-color:var(--wine);background:var(--wine);box-shadow:0 0 0 7px rgba(122,46,51,.12)}
.no-card{grid-row:1;position:relative;background:var(--card);border:1px solid var(--rule);border-radius:18px;padding:clamp(20px,2.2vw,28px);opacity:.42;transform:scale(.97);transition:opacity .5s,transform .6s var(--ease),border-color .5s,box-shadow .5s}
.no[data-lado="e"] .no-card{grid-column:1}
.no[data-lado="d"] .no-card{grid-column:3}
.no.on .no-card{opacity:1;transform:none;border-color:rgba(30,20,18,.28);box-shadow:0 22px 40px -30px rgba(42,21,18,.5)}
.no-card .no-n{font:500 11px var(--mono);letter-spacing:.12em;color:var(--mute);display:flex;justify-content:space-between;gap:10px}
.no-card .no-n span:last-child{color:var(--wine)}
.no-card h3{font-size:clamp(26px,2.5vw,34px);letter-spacing:-.04em;margin:10px 0 6px;font-weight:600}
.no-card p{color:var(--ink-2);font-size:16px}
.no-chip{display:inline-flex;align-items:center;gap:8px;margin-top:16px;font:500 10.5px var(--mono);letter-spacing:.1em;text-transform:uppercase;background:var(--paper);border:1px solid var(--rule);border-radius:999px;padding:7px 11px 6px;color:var(--ink-2)}
.no-chip b{color:var(--ink);font-weight:600}
.no-card.seu{background:transparent;border:1.5px dashed var(--wine)}
.no-card.seu .hand{margin-top:14px;font-size:24px}
.volta-nota{position:absolute;left:66px;bottom:44px;font-size:24px;opacity:0;transition:opacity .5s 1s}
.volta-nota.on{opacity:1}
.sis-fim{margin-top:clamp(40px,5vw,64px);padding-top:clamp(28px,3vw,40px);border-top:1px solid var(--rule)}
@media (max-width:900px){
  .sis-head{grid-template-columns:1fr}
  .trilha{padding:40px 0 40px 0}
  .no{grid-template-columns:44px 1fr}
  .no-pino{grid-column:1!important;justify-self:center!important;margin:0!important}
  .no .no-card{grid-column:2!important}
  .no[data-lado="d"] .no-pino{transform:translateX(7px)}
  .no[data-lado="e"] .no-pino{transform:translateX(-7px)}
  .volta-nota{position:static;opacity:1;margin:24px 0 0 44px}
}

/* ============ 08 AS 8 FRENTES ============ */
.est-in{display:grid;grid-template-columns:1fr 1.1fr;gap:clamp(20px,4vw,64px);align-items:center;height:100%;padding:clamp(20px,3vh,40px) var(--gut)}
.est-txt .h2{font-size:clamp(34px,4.6vw,64px)}
.est-txt .lead{margin-top:18px;font-size:17px}
.est-painel{margin-top:clamp(20px,3.5vh,40px);border-top:1px solid var(--rule);padding-top:22px;min-height:150px}
.est-painel .mono{font-size:11px;color:var(--wine)}
.est-painel h3{font-size:clamp(26px,2.6vw,36px);letter-spacing:-.04em;margin:8px 0;font-weight:600}
.est-painel p{color:var(--ink-2);font-size:17px;max-width:40ch}
.est-painel>*{transition:opacity .3s,transform .4s var(--ease)}
.est-painel.troca>*{opacity:0;transform:translateY(8px)}
.est-lista{display:grid;grid-template-columns:1fr 1fr;gap:4px 18px;margin-top:22px;font-size:10.5px;color:var(--mute)}
.est-lista li{transition:color .3s}
.est-lista li.on{color:var(--ink)}
.est-lista li.ativo{color:var(--wine)}
.est-box{position:relative;width:min(100%,calc(100svh - var(--hdr) - 90px));aspect-ratio:1;margin:0 auto}
.est-svg{position:absolute;left:15%;top:15%;width:70%;height:70%;overflow:visible}
.pet{fill:transparent;stroke:var(--ink);stroke-width:.18;stroke-dasharray:.6 .5;transform-box:view-box;transform-origin:50% 50%;transform:scale(.55);opacity:.45;transition:transform .7s var(--ease),fill .4s,opacity .4s,stroke .4s}
.pet.on{fill:var(--brown-3);stroke:transparent;transform:scale(1);opacity:1}
.pet.ativo{fill:var(--wine)}
.est-centro{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:18%;aspect-ratio:1;border-radius:50%;background:var(--paper);border:1px solid var(--rule);display:grid;place-items:center;text-align:center;font-size:clamp(8.5px,.8vw,10.5px);color:var(--ink);z-index:2;box-shadow:0 0 0 8px rgba(238,233,225,.6)}
.pet-lab{position:absolute;transform:translate(-50%,-50%);white-space:nowrap;font-size:10.5px;color:var(--mute);transition:color .3s,opacity .3s;opacity:.5}
.pet-lab b{font-weight:600}
.pet-lab.on{opacity:1;color:var(--ink)}
.pet-lab.ativo{color:var(--wine)}
@media (max-width:860px){
  .est-in{grid-template-columns:1fr;align-content:start;gap:8px;padding-top:22px}
  .est-txt .lead,.est-lista{display:none}
  .est-txt .h2{font-size:clamp(28px,8vw,40px)}
  .est-box{width:min(86vw,46svh);margin-top:6px}
  .est-painel{order:3;margin-top:8px;padding-top:14px;min-height:0}
  .est-painel p{font-size:15.5px}
  .pet-lab .nome{display:none}
}

/* ============ 09 COMPARATIVO ============ */
.cmp-head{display:grid;grid-template-columns:1.1fr 1fr;gap:clamp(24px,4vw,64px);align-items:end;margin-bottom:clamp(32px,4vw,52px)}
.cmp{border:1px solid var(--rule);border-radius:20px;overflow:hidden}
.cmp-h,.cmp-r{display:grid;grid-template-columns:1fr 1.15fr 1.5fr}
.cmp-h>*,.cmp-r>*{padding:20px clamp(16px,2vw,28px)}
.cmp-h>*+*,.cmp-r>*+*{border-left:1px solid var(--rule)}
.cmp-r{border-top:1px solid var(--rule)}
.cmp-h{font:600 11px var(--mono);letter-spacing:.14em;text-transform:uppercase}
.cmp-h .cmp-ag{color:var(--mute)}
.cmp-h .cmp-jp{background:var(--wine);color:#F6F1EA;display:flex;align-items:center;gap:10px}
.cmp-l{font:500 10.5px/1.5 var(--mono);letter-spacing:.12em;text-transform:uppercase;color:var(--ink-2)}
.cmp-a{color:var(--mute);font-size:16px;position:relative}
.cmp-a s{text-decoration-color:rgba(122,46,51,.55);text-decoration-thickness:1.5px}
.cmp-j{background:var(--wine-tint);font-size:16.5px;font-weight:500}
.cmp-r.ultima .cmp-j{color:var(--wine);font-weight:600}
.cmp-nota{display:flex;align-items:center;justify-content:flex-end;gap:10px;margin-top:18px}
.cmp-nota svg{width:64px;height:44px;transform:scaleX(-1) rotate(-12deg)}
@media (max-width:760px){
  .cmp-head{grid-template-columns:1fr}
  .cmp-h{display:none}
  .cmp-r{grid-template-columns:1fr;border-top:0}
  .cmp-r+.cmp-r{border-top:1px solid var(--rule)}
  .cmp-r>*+*{border-left:0}
  .cmp-r>*{padding:10px 18px}
  .cmp-l{padding-top:18px!important}
  .cmp-a::before{content:"Agência: ";font:500 10px var(--mono);letter-spacing:.1em;text-transform:uppercase}
  .cmp-j{padding-bottom:16px!important}
  .cmp-j::before{content:"J.P.Growth: ";font:600 10px var(--mono);letter-spacing:.1em;text-transform:uppercase;color:var(--wine)}
}

/* ============ 10 PROVA ============ */
.prova-head{margin-bottom:clamp(36px,5vw,64px)}
.destaque{display:grid;grid-template-columns:1fr 1.25fr;gap:clamp(30px,5vw,72px);align-items:center}
.destaque .polaroid{order:2;width:100%}
.destaque-txt{order:1}
.foto-btn{display:block;border:0;padding:0;background:none;cursor:zoom-in;width:100%}
.destaque .foto-btn img{width:100%;height:auto;max-height:440px;object-fit:contain;background:#fff}
.destaque figcaption{margin-top:14px;font-size:10px;color:var(--ink-2);display:flex;justify-content:space-between;gap:8px}
.destaque-txt .carimbo{margin-bottom:22px}
.destaque-txt .seg{font-size:11px;color:var(--ink-2)}
.destaque-txt .h3{font-size:clamp(32px,3.6vw,50px);margin:12px 0 26px}
.d-stats{display:grid;grid-template-columns:repeat(3,1fr);border-top:1px solid var(--rule);border-bottom:1px solid var(--rule)}
.d-stats>div{padding:16px 14px 16px 0}
.d-stats>div+div{border-left:1px solid var(--rule);padding-left:16px}
.d-stats dt{font:500 10px var(--mono);letter-spacing:.12em;text-transform:uppercase;color:var(--mute)}
.d-stats dd{font-size:clamp(24px,2.4vw,34px);font-weight:700;letter-spacing:-.04em;margin-top:4px}
.d-nota{margin-top:18px;color:var(--ink-2);font-size:15.5px;max-width:48ch}
.d-hand{display:flex;align-items:flex-end;gap:10px;margin-top:22px}
.d-hand svg{width:70px;height:48px;flex:none;transform:scaleX(-1)}
.obras-head{display:flex;justify-content:space-between;gap:16px;margin:clamp(56px,7vw,96px) 0 18px;padding-bottom:14px;border-bottom:1px solid var(--rule);font-size:10.5px;color:var(--ink-2)}
.obras{display:grid;grid-template-columns:repeat(5,1fr);gap:16px}
.obra{display:flex;flex-direction:column;gap:10px;text-align:left;border:0;background:none;padding:0;cursor:zoom-in}
.obra .thumb{position:relative;aspect-ratio:4/3;overflow:hidden;border-radius:10px;background:var(--sheet);border:1px solid var(--rule);padding:10px 10px 34px}
.obra .thumb img{width:100%;height:100%;object-fit:contain;filter:grayscale(.3) sepia(.1);transition:transform .8s var(--ease),filter .4s}
.obra:hover .thumb img{transform:scale(1.05);filter:none}
.obra .thumb::after{content:"Ver print";position:absolute;left:10px;bottom:10px;font:500 9.5px var(--mono);letter-spacing:.12em;text-transform:uppercase;background:var(--sheet);border-radius:999px;padding:6px 9px 5px;color:var(--ink)}
.obra .mono{font-size:10px;color:var(--mute);display:flex;justify-content:space-between}
.obra b{font-size:16.5px;line-height:1.25;letter-spacing:-.02em;font-weight:600}
@media (max-width:1000px){.obras{grid-template-columns:none;grid-auto-flow:column;grid-auto-columns:minmax(200px,44%);overflow-x:auto;scroll-snap-type:x mandatory;padding-bottom:8px}.obra{scroll-snap-align:start}}
@media (max-width:860px){.destaque{grid-template-columns:1fr}.d-stats dd{font-size:21px;white-space:nowrap}}
.depos-wrap{border-top:1px solid var(--rule)}
.depos-head{display:grid;grid-template-columns:1.2fr 1fr;gap:24px;align-items:end;margin-bottom:clamp(32px,4vw,48px)}
.depos{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
.depo{background:var(--card);border:1px solid var(--rule);border-radius:18px;padding:16px;display:flex;flex-direction:column;gap:14px}
.depo-top{display:flex;justify-content:space-between;font:500 10px var(--mono);letter-spacing:.12em;text-transform:uppercase;color:var(--ink-2)}
.depo-top span:last-child{color:#3F7A55}
.chat{background:#E9E1D3;border-radius:12px;padding:12px 10px;display:flex;flex-direction:column;gap:6px;flex:1}
.msg{max-width:92%;font-size:13.5px;line-height:1.35;padding:7px 10px 6px;border-radius:9px;background:#fff;box-shadow:0 1px 0 rgba(0,0,0,.06)}
.msg time{display:block;text-align:right;font-size:10px;color:var(--mute);margin-top:2px}
.msg.out{align-self:flex-end;background:#DCF3CF}
.depo-pe{display:flex;flex-direction:column;gap:12px}
.depo-pe p{font-weight:600;font-size:15.5px;line-height:1.3;letter-spacing:-.015em}
.depo-pe button{align-self:flex-start;border:1px solid var(--rule);background:transparent;border-radius:999px;padding:7px 11px 6px;font:500 10px var(--mono);letter-spacing:.12em;text-transform:uppercase;cursor:zoom-in;color:var(--ink-2)}
.depo-pe button:hover{border-color:var(--wine);color:var(--wine)}
@media (max-width:1000px){.depos{grid-template-columns:1fr 1fr}.depos-head{grid-template-columns:1fr}}
@media (max-width:560px){.depos{grid-template-columns:1fr}}

/* ============ 11 BRANDING ============ */
.marca-head{display:grid;grid-template-columns:1.2fr 1fr;gap:clamp(24px,4vw,64px);align-items:end}
.parede{display:flex;flex-direction:column;gap:14px;padding:clamp(8px,2vw,24px) 0 clamp(48px,6vw,80px);overflow:hidden;-webkit-mask:linear-gradient(90deg,transparent,#000 12%,#000 88%,transparent);mask:linear-gradient(90deg,transparent,#000 12%,#000 88%,transparent)}
.fila{display:flex;gap:14px;width:max-content;will-change:transform}
.tile{position:relative;flex:none;width:clamp(118px,12vw,168px);aspect-ratio:9/16;border-radius:14px;overflow:hidden;background:#141414}
.tile img.bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.tile img.lg{position:absolute;left:12.5%;top:35%;width:75%;height:30%;object-fit:contain;transition:transform .5s var(--ease)}
.tile:hover img.lg{transform:scale(1.08)}
.marca-pe{display:flex;justify-content:space-between;gap:16px;padding:20px var(--gut);border-top:1px solid var(--rule-dark)!important;font-size:10.5px;color:var(--on-dark-mute)}
@media (max-width:860px){.marca-head{grid-template-columns:1fr}}

/* ============ 12 QUEM CONDUZ ============ */
.socios{display:grid;grid-template-columns:1fr 1fr;gap:clamp(36px,6vw,96px);align-items:center}
.socios-fotos{position:relative;display:grid;grid-template-columns:1fr 1fr;gap:clamp(14px,2vw,26px);padding:20px clamp(6px,1.5vw,18px);max-width:520px;width:100%;justify-self:center}
.socios-fotos .polaroid img{aspect-ratio:4/5;object-fit:cover;object-position:50% 20%;width:100%;filter:grayscale(.9) sepia(.22) contrast(1.06) brightness(.98);transition:filter .6s}
.socios-fotos .polaroid:hover img{filter:none}
.socios-fotos .polaroid:nth-child(2){margin-top:56px}
.socios-fotos figcaption{margin-top:12px;font-size:25px;line-height:1}
.socios-fotos figcaption small{display:block;font-size:19px;color:var(--ink-2);margin-top:2px}
.socio{padding:22px 0;border-top:1px solid var(--rule)}
.socio .mono{font-size:10.5px;color:var(--wine)}
.socio h3{font-size:24px;letter-spacing:-.03em;margin:6px 0 6px;font-weight:600}
.socio p{color:var(--ink-2);font-size:16px;max-width:46ch}
.socios-txt .h2{margin-bottom:clamp(24px,3vw,36px)}
.certs{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px}
.socios-hand{display:flex;align-items:center;gap:10px;margin-top:22px}
.socios-hand svg{width:60px;height:40px;transform:rotate(180deg)}
@media (max-width:860px){.socios{grid-template-columns:1fr}}

/* ============ 13 OS PRIMEIROS 90 DIAS ============ */
.noventa{display:grid;grid-template-columns:1fr 1.05fr;gap:clamp(36px,6vw,96px);align-items:start}
.noventa-esq{position:sticky;top:calc(var(--hdr) + 48px)}
.semanas{display:grid;grid-template-columns:repeat(6,1fr);gap:6px;margin-top:clamp(28px,4vw,44px)}
.sem{position:relative;aspect-ratio:1/1.05;border:1px solid var(--rule);border-radius:8px;display:flex;flex-direction:column;justify-content:space-between;padding:8px;font:500 10px var(--mono);letter-spacing:.06em;color:var(--mute);transition:background .4s,color .4s,border-color .4s}
.sem i{width:6px;height:6px;border-radius:50%;background:var(--rule);align-self:flex-end;transition:background .4s}
.sem.on{color:#F6F1EA;border-color:transparent}
.sem.on i{background:rgba(246,241,234,.85)}
.sem.on[data-f="1"]{background:var(--brown-3)}
.sem.on[data-f="2"]{background:#6B4A3F}
.sem.on[data-f="3"]{background:#9A4A4F}
.sem.on[data-f="4"]{background:var(--wine)}
.legenda{display:flex;flex-wrap:wrap;gap:6px 16px;margin-top:14px;font-size:10px;color:var(--ink-2)}
.legenda span{display:inline-flex;align-items:center;gap:6px}
.legenda span::before{content:"";width:9px;height:9px;border-radius:2px;background:var(--c)}
.legenda .rel::before{border-radius:50%;width:6px;height:6px;background:var(--ink-2)}
.noventa-hand{display:flex;align-items:flex-start;gap:8px;margin-top:28px}
.noventa-hand svg{width:56px;height:46px;flex:none;transform:rotate(200deg)}
.fases{position:relative;padding-left:44px}
.fases-linha{position:absolute;left:8px;top:10px;bottom:10px;width:2px;background:var(--rule)}
.fases-linha b{position:absolute;inset:0;background:var(--wine);transform-origin:50% 0;transform:scaleY(0)}
.fase{position:relative;padding:4px 0 clamp(52px,8vh,88px)}
.fase::before{content:"";position:absolute;left:-44px;top:6px;width:18px;height:18px;border-radius:50%;background:var(--paper);border:2px solid var(--rule);transition:all .4s var(--ease)}
.fase.on::before{background:var(--wine);border-color:var(--wine);box-shadow:0 0 0 7px rgba(122,46,51,.12)}
.fase .mono{font-size:10.5px;color:var(--mute);transition:color .4s}
.fase.on .mono{color:var(--wine)}
.fase h3{font-size:clamp(30px,3vw,42px);letter-spacing:-.045em;margin:6px 0 8px;font-weight:600}
.fase p{color:var(--ink-2);font-size:17px;max-width:42ch}
.fase>*{opacity:.4;transition:opacity .5s}
.fase.on>*{opacity:1}
.fase.seg{padding-bottom:8px}
.fase.seg h3{font-family:var(--serif);font-style:italic;font-weight:400;letter-spacing:-.01em}
@media (max-width:860px){.noventa{grid-template-columns:1fr}.noventa-esq{position:static}}

/* ============ 14 O COMBINADO ============ */
.pacto-head{text-align:center;display:grid;justify-items:center;margin-bottom:clamp(40px,5vw,64px)}
.pacto{display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:0}
.pacto-col{background:var(--card);border:1px solid var(--rule);border-radius:20px;padding:clamp(26px,3vw,40px);transition:transform 1s var(--ease),opacity 1s}
.pacto-col.nossa{border-right:0;border-radius:20px 0 0 20px}
.pacto-col.sua{border-left:0;border-radius:0 20px 20px 0;background:var(--sheet)}
.pacto:not(.on) .nossa{transform:translateX(-60px);opacity:0}
.pacto:not(.on) .sua{transform:translateX(60px);opacity:0}
.pacto-col .mono{font-size:10.5px;color:var(--wine)}
.pacto-col h3{font-size:clamp(28px,2.8vw,38px);letter-spacing:-.04em;margin:8px 0 18px;font-weight:600}
.pacto-col li{position:relative;padding:12px 0 12px 26px;border-top:1px dashed var(--rule);font-size:16.5px;line-height:1.4}
.pacto-col li::before{content:"";position:absolute;left:0;top:17px;width:12px;height:12px;background:var(--wine);-webkit-mask:var(--estrela) center/contain no-repeat;mask:var(--estrela) center/contain no-repeat}
.pacto-col.sua li::before{background:var(--ink)}
.lacre{position:relative;z-index:2;width:clamp(104px,11vw,150px);aspect-ratio:1;align-self:stretch;display:grid;place-items:center;background:linear-gradient(90deg,var(--card) 50%,var(--sheet) 50%);border-top:1px solid var(--rule);border-bottom:1px solid var(--rule)}
.lacre svg{width:100%;height:auto;filter:drop-shadow(0 10px 14px rgba(42,21,18,.35))}
.pacto.on .lacre svg{animation:carimba .75s cubic-bezier(.3,1.6,.5,1) .9s both}
.pacto:not(.on) .lacre svg{opacity:0}
@keyframes carimba{from{opacity:0;transform:scale(2.3) rotate(-30deg)}60%{opacity:1}to{opacity:1;transform:scale(1) rotate(-8deg)}}
.pacto-pe{text-align:center;margin-top:clamp(36px,4vw,52px);display:grid;justify-items:center;gap:6px}
.pacto-pe p:first-child{font-weight:600;font-size:clamp(19px,1.8vw,24px);letter-spacing:-.02em}
.pacto-pe p:last-child{color:var(--ink-2)}
@media (max-width:860px){
  .pacto{grid-template-columns:1fr}
  .pacto-col.nossa{border-right:1px solid var(--rule);border-bottom:0;border-radius:20px 20px 0 0}
  .pacto-col.sua{border-left:1px solid var(--rule);border-top:0;border-radius:0 0 20px 20px}
  .lacre{width:100%;aspect-ratio:auto;height:120px;background:linear-gradient(180deg,var(--card) 50%,var(--sheet) 50%);border:1px solid var(--rule);border-top:0;border-bottom:0}
  .lacre svg{width:110px}
}

/* ============ 15 INVESTIMENTO ============ */
.inv-head{display:grid;grid-template-columns:1.15fr 1fr;gap:clamp(24px,4vw,64px);align-items:end;margin-bottom:clamp(36px,5vw,56px)}
.inv-bento{display:grid;grid-template-columns:1.2fr 1fr;grid-template-rows:auto auto;gap:14px}
.inv-card{position:relative;border-radius:22px;padding:clamp(24px,3vw,38px);border:1px solid var(--rule);background:var(--card);display:flex;flex-direction:column;gap:12px;overflow:hidden}
.inv-card .mono{font-size:10.5px;color:var(--ink-2)}
.inv-card .valor{font-size:clamp(52px,6.4vw,92px);font-weight:700;letter-spacing:-.06em;line-height:.9;margin-top:6px}
.inv-card .valor small{font-size:.26em;font-weight:500;letter-spacing:0;color:var(--ink-2);margin-left:6px}
.inv-card .de{font:500 12px var(--mono);letter-spacing:.1em;text-transform:uppercase;color:var(--ink-2)}
.inv-card p{color:var(--ink-2);font-size:16px;max-width:44ch}
.inv-card.time{grid-row:span 2}
.inv-chips{display:flex;flex-wrap:wrap;gap:6px;margin-top:auto;padding-top:10px}
.inv-chips span{font:500 10px var(--mono);letter-spacing:.1em;text-transform:uppercase;border:1px solid var(--rule);border-radius:999px;padding:7px 10px 6px;color:var(--ink-2)}
.inv-card.verba{background:var(--brown);border-color:var(--brown);color:var(--on-dark)}
.inv-card.verba .mono,.inv-card.verba p,.inv-card.verba .de,.inv-card.verba .valor small{color:var(--on-dark-mute)}
.inv-card.total{background:var(--wine);border-color:var(--wine);color:#F6F1EA}
.inv-card.total .mono{color:rgba(246,241,234,.75)}
.inv-card.total .valor{font-size:clamp(40px,4.4vw,62px);color:#F6F1EA}
.inv-card.time .valor{color:var(--ink)}
.inv-card.verba .valor{color:var(--on-dark)}
.barra{display:flex;height:42px;border-radius:10px;overflow:hidden;margin-top:6px;font:600 10px var(--mono);letter-spacing:.1em;text-transform:uppercase}
.barra span{display:flex;align-items:center;padding:0 12px;flex:1}
.barra span:first-child{background:#F6F1EA;color:var(--wine)}
.barra span:last-child{background:rgba(246,241,234,.16);color:#F6F1EA;justify-content:flex-end}
.inv-card.total .hand{color:#F6F1EA;font-size:24px;margin-top:4px}
.ritmo{margin-top:14px;display:grid;grid-template-columns:1fr 1.2fr;gap:0;border:1px solid var(--rule);border-radius:22px;overflow:hidden;background:var(--sheet)}
.ritmo>div{padding:clamp(24px,3vw,38px)}
.ritmo-esq{border-right:1px solid var(--rule)}
.ritmo-esq .h3{margin-bottom:26px}
.ritmo label{display:flex;justify-content:space-between;align-items:baseline;gap:12px;font:500 10.5px var(--mono);letter-spacing:.12em;text-transform:uppercase;color:var(--ink-2)}
.ritmo output{font:700 clamp(30px,3vw,42px)/1 var(--f);letter-spacing:-.05em;color:var(--ink);text-transform:none}
input[type=range]{-webkit-appearance:none;appearance:none;width:100%;height:28px;background:transparent;margin:18px 0 6px;cursor:pointer;--pct:0%}
input[type=range]::-webkit-slider-runnable-track{height:8px;border-radius:99px;background:linear-gradient(90deg,var(--wine) var(--pct),var(--rule) var(--pct))}
input[type=range]::-moz-range-track{height:8px;border-radius:99px;background:linear-gradient(90deg,var(--wine) var(--pct),var(--rule) var(--pct))}
input[type=range]::-webkit-slider-thumb{-webkit-appearance:none;width:28px;height:28px;margin-top:-10px;border-radius:50%;background:var(--sheet);border:3px solid var(--wine);box-shadow:0 4px 10px rgba(42,21,18,.25)}
input[type=range]::-moz-range-thumb{width:22px;height:22px;border-radius:50%;background:var(--sheet);border:3px solid var(--wine)}
input[type=range]:focus-visible{outline:2px solid var(--wine);outline-offset:4px;border-radius:8px}
.escala{display:flex;justify-content:space-between;font:500 10px var(--mono);letter-spacing:.08em;color:var(--mute)}
.ritmo-dir{display:flex;flex-direction:column;gap:14px}
.nivel-top{display:flex;justify-content:space-between;align-items:center;gap:12px}
.nivel-nome{font-size:clamp(34px,3.6vw,50px);font-weight:700;letter-spacing:-.05em;line-height:1}
.medidor{display:flex;gap:5px}
.medidor i{width:30px;height:10px;border-radius:3px;background:var(--rule);transition:background .3s}
.medidor i.on{background:var(--wine)}
.nivel-desc{font-size:18px;color:var(--ink-2);min-height:3em;max-width:44ch}
.conta-mes{margin-top:auto;border-top:1px dashed var(--rule);padding-top:16px;display:flex;justify-content:space-between;align-items:flex-end;gap:14px;flex-wrap:wrap}
.conta-mes .mono{font-size:10.5px;color:var(--ink-2)}
.conta-mes b{display:block;font-size:clamp(28px,2.8vw,38px);letter-spacing:-.045em;font-weight:700;margin-top:4px}
.conta-mes small{font:500 11px var(--mono);letter-spacing:.06em;color:var(--mute)}
.porque{margin-top:14px;border:1px solid var(--rule);border-radius:16px;padding:0 clamp(20px,2.6vw,30px)}
.porque summary{cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center;gap:12px;padding:20px 0;font-weight:600;font-size:17px;letter-spacing:-.015em}
.porque summary::-webkit-details-marker{display:none}
.porque summary::after{content:"+";font:400 24px/1 var(--mono);color:var(--wine);transition:transform .3s}
.porque[open] summary::after{transform:rotate(45deg)}
.porque p{color:var(--ink-2);padding:0 0 22px;max-width:70ch}
@media (max-width:900px){.inv-head{grid-template-columns:1fr}.inv-bento{grid-template-columns:1fr}.inv-card.time{grid-row:auto}.ritmo{grid-template-columns:1fr}.ritmo-esq{border-right:0;border-bottom:1px solid var(--rule)}}

/* ============ 16 PRÓXIMOS PASSOS ============ */
.passos{display:grid;grid-template-columns:1fr auto 1fr auto 1fr auto 1fr;align-items:stretch;margin-top:clamp(36px,5vw,56px)}
.passo{background:var(--card);border:1px solid var(--rule);border-radius:18px;padding:22px 20px 24px;display:flex;flex-direction:column;gap:8px}
.passo .mono{font-size:10.5px;color:var(--wine)}
.passo b{font-size:19px;letter-spacing:-.025em;line-height:1.2;font-weight:600}
.passo p{font-size:15px;color:var(--ink-2)}
.passo-seta{display:grid;place-items:center;width:56px}
.passo-seta svg{width:46px;height:30px}
.final{display:grid;grid-template-columns:1fr auto;gap:28px;align-items:end;margin-top:clamp(72px,10vw,140px);padding-top:clamp(40px,5vw,64px);border-top:1px solid var(--rule)}
.final-t .display{font-size:clamp(44px,8.4vw,124px)}
.final-t .serif{display:block;font-size:clamp(46px,8vw,120px);line-height:.95;color:var(--wine)!important;margin-top:4px}
.final-acao{display:grid;justify-items:end;gap:20px;text-align:right}
.final-acao p{color:var(--ink-2);max-width:30ch}
.final-acao .final-fala{color:var(--wine);font-size:clamp(24px,2.2vw,30px);max-width:18ch}
@media (max-width:900px){
  .passos{grid-template-columns:1fr}
  .passo-seta{width:100%;height:44px}
  .passo-seta svg{transform:rotate(90deg)}
  .final{grid-template-columns:1fr}
  .final-acao{justify-items:start;text-align:left}
}

/* ============ 17 ENCERRAMENTO ============ */
.fim .pad{text-align:center;display:grid;justify-items:center;gap:26px}
.fim .lema-fim .display{display:block;font-size:clamp(46px,10vw,150px)}
.fim .lema-fim .serif{display:block;font-size:clamp(46px,9.4vw,140px);line-height:.95;color:var(--wine-lt)!important}
.rodape{display:grid;grid-template-columns:auto 1fr auto auto;border-top:1px solid var(--rule-dark)!important}
.rodape>*{padding:22px var(--gut);display:flex;align-items:center;font-size:10.5px;color:var(--on-dark-mute)}
.rodape>*+*{border-left:1px solid var(--rule-dark)}
.rodape a{text-decoration:none}
.rodape a:hover{color:var(--on-dark)}
.rodape .lg{display:flex;gap:9px;align-items:center;color:var(--on-dark)}
.rodape .lg .lg-icone{height:26px;width:auto;color:var(--wine-lt)}
.rodape .lg .lg-texto{height:17px;width:auto;color:var(--on-dark)}
.rodape a{overflow-wrap:anywhere}
@media (max-width:860px){.rodape{grid-template-columns:1fr 1fr}.rodape>*:nth-child(3){border-left:0}.rodape>*:nth-child(n+3){border-top:1px solid var(--rule-dark)}}
@media (max-width:560px){.rodape{grid-template-columns:1fr}.rodape>*+*{border-left:0!important;border-top:1px solid var(--rule-dark)}}

/* Lightbox dos prints */
.lb{border:0;padding:0;background:transparent;max-width:min(92vw,560px);max-height:92vh;overflow:visible}
.lb::backdrop{background:rgba(30,20,18,.82);-webkit-backdrop-filter:blur(4px);backdrop-filter:blur(4px)}
.lb-in{background:var(--sheet);border-radius:14px;overflow:hidden;display:flex;flex-direction:column;max-height:88vh}
.lb-in .lb-img{overflow:auto}
.lb-in img{width:100%;height:auto}
.lb-in p{padding:12px 16px;font-size:10.5px;color:var(--ink-2);border-top:1px solid var(--rule)}
.lb-x{position:absolute;top:-14px;right:-14px;width:38px;height:38px;border-radius:50%;border:0;background:var(--wine);color:#F6F1EA;cursor:pointer;font:500 18px/1 var(--f)}

@media (prefers-reduced-motion:reduce){
  *,*::before,*::after{animation-duration:.01ms!important;animation-iteration-count:1!important;transition-duration:.01ms!important;transition-delay:0s!important}
}
</style>
</head>
<body>

<!-- Estrela da marca (o "detalhezinho"), reutilizada com <use> -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <symbol id="estrela" viewBox="0 0 35 35"><path d="M17.4999 17.5L14.5927 31.432C14.2057 33.2727 15.6121 35 17.4999 35C19.3783 35 20.7847 33.2727 20.4072 31.432L17.4999 17.5Z"/><path d="M17.5 17.5L25.2967 29.4026C26.3255 30.979 28.5437 31.2055 29.8746 29.8746C31.2055 28.5437 30.979 26.3255 29.4026 25.2967L17.5 17.5Z"/><path d="M17.5 17.501L31.432 20.4083C33.2727 20.7953 35 19.3888 35 17.501C35 15.6227 33.2727 14.2162 31.432 14.5938L17.5 17.501Z"/><path d="M17.5 17.4995L29.4026 9.70287C30.979 8.67401 31.2055 6.45583 29.8746 5.12493C28.5437 3.79402 26.3255 4.02056 25.2967 5.59688L17.5 17.4995Z"/><path d="M17.5001 17.5L20.4073 3.56796C20.7943 1.72735 19.3879 0 17.5001 0C15.6217 0 14.2153 1.72735 14.5928 3.56796L17.5001 17.5Z"/><path d="M17.4999 17.4995L9.70323 5.59688C8.67438 4.02056 6.4562 3.79402 5.12529 5.12493C3.79439 6.45583 4.02092 8.67401 5.59725 9.70287L17.4999 17.4995Z"/><path d="M17.5 17.5004L3.56796 14.5932C1.72735 14.2062 0 15.6126 0 17.5004C0 19.3788 1.72735 20.7852 3.56796 20.4077L17.5 17.5004Z"/><path d="M17.4999 17.5L5.59725 25.2967C4.02092 26.3255 3.79439 28.5437 5.12529 29.8746C6.4562 31.2055 8.67438 30.979 9.70323 29.4026L17.4999 17.5Z"/></symbol>
</svg>

<header class="hdr">
  <div class="wrap">
    <a class="hdr-logo" href="#inicio" aria-label="J.P.Growth, voltar ao início"><svg class="lg-icone" viewBox="0 0 159 154" aria-hidden="true"><g>
<path d="M36.79 85.07C35.15 81.62 31.53 79.82 27.92 79.82C26.11 79.82 24.47 80.14 22.83 81.13L21.84 79.82C23.65 75.38 28.74 72.1 33.17 72.1C34.49 72.1 35.8 72.43 36.79 73.08V69.31C35.31 66.02 31.2 64.05 26.93 64.05C22.66 64.05 18.72 65.86 16.92 69.96L15.93 68.65C17.08 57.97 25.46 52.06 33.01 52.06C34.32 52.06 35.64 52.23 36.79 52.55V24.96L32.52 27.59L31.2 25.62C43.69 18.07 56.33 10.02 68.65 0L70.46 2.13C65.53 6.41 64.05 13.63 64.05 21.02V110.36C64.05 117.1 58.79 122.52 53.54 126.13L37.61 137.13C35.97 138.28 34.82 139.43 31.7 141.24C24.8 139.6 17.08 131.71 10.84 131.71C7.06 131.71 3.28 134.18 3.28 138.45C3.28 141.73 5.91 143.87 9.2 143.87C11.5 143.87 13.63 142.22 14.94 139.92L17.24 142.06C15.44 144.69 12.15 147.15 8.54 147.15C4.11 147.15 0 143.04 0 138.28C0 133.52 3.61 130.4 6.08 128.1L10.51 124.16C12.48 122.35 17.74 118.25 25.95 118.25C29.4 118.25 33.17 119.07 36.79 120.54V85.07ZM36.95 14.45C35.31 15.6 33.17 16.42 31.37 17.08C26.44 16.59 21.68 14.45 16.59 14.45C13.3 14.45 8.21 17.08 8.21 21.19C8.21 23.81 10.18 26.28 13.3 26.28C15.44 26.28 17.08 24.96 17.9 22.83L20.36 24.47C19.05 27.1 16.26 29.23 12.97 29.23C9.03 29.23 5.09 25.95 5.09 21.68C5.09 17.41 8.87 14.29 12.15 11.17L18.23 5.42C21.51 2.3 25.78 0 30.87 0C36.29 0 41.22 3.12 46.81 3.12C48.45 3.12 50.91 2.46 52.06 1.81L53.21 0.99L54.52 3.29L36.95 14.45ZM46.81 29.89C46.81 24.47 49.1 19.38 52.39 14.78C48.12 18.07 43.69 20.86 39.42 23.49V121.7C42.37 123.17 45 124.98 46.81 127.28V29.89Z" fill="currentColor"/>
<path d="M75.38 25.95C75.38 18.72 72.09 14.95 67 14.95C64.05 14.95 60.6 16.26 57.15 18.89L55.51 16.75C62.4 9.03 71.11 3.94 79.48 3.94C86.71 3.94 92.46 8.21 92.46 18.89V24.96L122.02 0C131.22 5.09 157.99 11.83 158.81 52.55C158.81 95.42 125.63 107.08 107.57 119.07C104.12 113.81 98.7 110.2 92.62 109.05V123.17C92.62 126.79 92.95 130.73 95.42 135.33L94.59 136.48C86.55 140.25 80.14 145.67 74.56 153.72L72.75 153.23C74.72 146.17 75.38 137.95 75.38 129.74V107.24C69.96 107.24 64.05 110.69 60.93 117.26L59.45 116.11C60.76 107.24 66.84 99.69 75.38 96.4V73.25C73.74 69.8 70.12 67.99 66.67 67.99C64.87 67.99 63.06 68.32 61.58 69.31L60.43 67.99C62.24 63.56 67.33 60.27 71.77 60.27C73.08 60.27 74.39 60.6 75.38 61.26V57.48C74.06 54.52 70.94 53.21 67.33 53.21C63.23 53.21 58.63 55.02 55.67 58.14L54.52 56.82C57.31 48.28 64.38 43.19 71.27 43.19C72.75 43.19 74.06 43.36 75.38 43.69V25.95ZM92.62 28.9V95.42C95.42 95.42 98.04 95.75 100.83 96.4V21.68L92.62 28.9ZM103.46 56.99C111.34 50.42 127.44 43.03 138.61 38.92C132.04 20.69 117.09 16.59 108.55 15.6L103.46 20.04V56.99ZM139.43 41.55C128.26 45.49 111.18 53.54 103.46 59.78V73.25C113.81 66.84 130.72 60.77 141.73 57.97C141.56 51.57 140.74 46.15 139.43 41.55ZM141.73 61.09C130.56 63.56 113.64 69.63 103.46 76.2V96.9C109.7 98.37 113.97 101.82 119.72 107.24C134.34 97.55 141.4 82.12 141.73 61.09Z" fill="currentColor"/>
</g></svg><svg class="lg-texto" viewBox="0 0 358 66" aria-hidden="true"><g>
<path d="M8.93 65.05C6.20333 65.05 4.03333 64.3033 2.42 62.81C0.806667 61.3167 0 59.5067 0 57.38C0 55.5467 0.52 54.1567 1.56 53.21C2.60667 52.27 3.77333 51.8 5.06 51.8C6.4 51.8 7.51667 52.26 8.41 53.18C9.30333 54.0933 9.75 55.1967 9.75 56.49C9.75 57.53 9.52667 58.3867 9.08 59.06C8.63333 59.7267 8.41 60.4067 8.41 61.1C8.41 61.6 8.58333 61.9733 8.93 62.22C9.28 62.47 9.7 62.59 10.2 62.59C11.0933 62.59 11.9233 62.17 12.69 61.33C13.4567 60.4834 14.0767 58.98 14.55 56.82C15.0233 54.6667 15.26 51.63 15.26 47.71V0.890015H25.23V47.71C25.23 50.0433 24.7833 52.25 23.89 54.33C22.9967 56.4167 21.78 58.2633 20.24 59.87C18.7067 61.4833 16.96 62.75 15 63.67C13.04 64.59 11.0167 65.05 8.93 65.05ZM5.58 2.75001V0.890015H31.93V2.75001H5.58Z" fill="currentColor"/>
<path d="M40.6502 57.9C39.1635 57.9 37.8869 57.3667 36.8202 56.3C35.7535 55.2334 35.2202 53.9567 35.2202 52.47C35.2202 50.9834 35.7535 49.7067 36.8202 48.64C37.8869 47.5734 39.1635 47.04 40.6502 47.04C42.1369 47.04 43.4135 47.5734 44.4802 48.64C45.5469 49.7067 46.0835 50.9834 46.0902 52.47C46.0969 53.9567 45.5602 55.2334 44.4802 56.3C43.4002 57.3667 42.1235 57.9 40.6502 57.9Z" fill="currentColor"/>
<path d="M70.9799 31.56V29.32H77.1599C80.5332 29.32 83.2599 28.96 85.3399 28.24C87.4199 27.52 88.9332 26.1567 89.8799 24.15C90.8265 22.1433 91.2999 19.2533 91.2999 15.48C91.2999 11.96 90.9132 9.28001 90.1399 7.44001C89.3732 5.60668 88.2199 4.36668 86.6799 3.72001C85.1465 3.07335 83.2132 2.75001 80.8799 2.75001H55.1299V0.890015H83.8599C87.4265 0.890015 90.6365 1.53668 93.4899 2.83001C96.3432 4.11668 98.6132 5.89001 100.3 8.15001C101.987 10.41 102.83 13.0033 102.83 15.93C102.83 19.0033 101.803 21.72 99.7499 24.08C97.6899 26.4333 94.7499 28.2667 90.9299 29.58C87.1032 30.9 82.5132 31.56 77.1599 31.56H70.9799ZM55.1299 57.01V55.15H82.9599V57.01H55.1299ZM63.3099 55.89V0.890015H73.2899V55.89H63.3099Z" fill="currentColor"/>
<path d="M98.44 57.9C96.9533 57.9 95.6733 57.3667 94.6 56.3C93.5267 55.2334 92.9967 53.9567 93.01 52.47C93.0233 50.9834 93.5533 49.7067 94.6 48.64C95.6467 47.5734 96.9267 47.04 98.44 47.04C99.9533 47.04 101.23 47.5734 102.27 48.64C103.31 49.7067 103.843 50.9834 103.87 52.47C103.897 53.9567 103.363 55.2334 102.27 56.3C101.177 57.3667 99.9 57.9 98.44 57.9Z" fill="currentColor"/>
<g>
<path d="M137.65 57.9C133.784 57.9 130.31 57.1067 127.23 55.52C124.15 53.9333 121.534 51.7867 119.38 49.08C117.227 46.3733 115.564 43.3233 114.39 39.93C113.217 36.5367 112.634 33.05 112.64 29.47C112.64 25.6033 113.3 21.8967 114.62 18.35C115.934 14.8033 117.757 11.65 120.09 8.89C122.417 6.13667 125.157 3.96667 128.31 2.38C131.464 0.793333 134.9 0 138.62 0C140.954 0 143.11 0.373333 145.09 1.12C147.077 1.86 148.987 3.02333 150.82 4.61L154.1 0.89H155.96V17.71H154.1C153.554 14.9367 152.684 12.37 151.49 10.01C150.304 7.65667 148.68 5.77 146.62 4.35C144.56 2.93667 141.894 2.23 138.62 2.23C136.534 2.23 134.637 2.62667 132.93 3.42C131.217 4.21333 129.727 5.61667 128.46 7.63C127.194 9.63667 126.227 12.44 125.56 16.04C124.887 19.64 124.55 24.24 124.55 29.84C124.55 35.0067 124.847 39.2633 125.44 42.61C126.04 45.9567 126.887 48.5733 127.98 50.46C129.067 52.3467 130.417 53.66 132.03 54.4C133.644 55.1467 135.444 55.52 137.43 55.52C140.65 55.52 143.167 54.74 144.98 53.18C146.794 51.6133 147.7 49.4167 147.7 46.59V35.95H158.79V37.73C158.79 41.95 157.81 45.56 155.85 48.56C153.89 51.5667 151.31 53.8733 148.11 55.48C144.91 57.0933 141.424 57.9 137.65 57.9ZM138.54 37.06V35.2H163.1V37.06H138.54Z" fill="currentColor"/>
<path d="M166.23 57.01V55.15H172.11V25.38H166.23V23.52H180.3V29.4H180.59C181.83 26.72 183.257 24.92 184.87 24C186.483 23.08 187.987 22.62 189.38 22.62C190.967 22.62 192.217 23.08 193.13 24C194.05 24.92 194.51 26.2233 194.51 27.91C194.51 29.45 194.05 30.58 193.13 31.3C192.217 32.0133 191.19 32.37 190.05 32.37C189.103 32.37 188.247 32.11 187.48 31.59C186.707 31.07 186.32 30.34 186.32 29.4C186.32 29.1 186.36 28.84 186.44 28.62C186.51 28.39 186.55 28.16 186.55 27.91C186.55 27.66 186.47 27.44 186.32 27.24C186.18 27.04 185.85 26.94 185.36 26.94C184.713 26.94 183.993 27.45 183.2 28.47C182.407 29.4833 181.723 30.8467 181.15 32.56C180.577 34.2733 180.293 36.17 180.3 38.25V55.15H186.99V57.01H166.23Z" fill="currentColor"/>
<path d="M216.32 57.9C213.887 57.9 211.617 57.4033 209.51 56.41C207.403 55.4167 205.543 54.09 203.93 52.43C202.317 50.77 201.063 48.8867 200.17 46.78C199.277 44.6733 198.83 42.5 198.83 40.26C198.83 38.02 199.277 35.85 200.17 33.75C201.063 31.65 202.317 29.7667 203.93 28.1C205.543 26.4333 207.403 25.1033 209.51 24.11C211.617 23.1167 213.887 22.62 216.32 22.62C218.753 22.62 221.023 23.1167 223.13 24.11C225.237 25.1033 227.097 26.4333 228.71 28.1C230.323 29.7667 231.577 31.65 232.47 33.75C233.363 35.85 233.81 38.02 233.81 40.26C233.81 42.5 233.363 44.6733 232.47 46.78C231.577 48.8867 230.323 50.77 228.71 52.43C227.097 54.09 225.237 55.4167 223.13 56.41C221.023 57.4033 218.753 57.9 216.32 57.9ZM216.32 55.67C218.2 55.67 219.713 55.2467 220.86 54.4C222 53.56 222.83 52.0367 223.35 49.83C223.87 47.6233 224.13 44.4333 224.13 40.26C224.13 36.0933 223.87 32.8933 223.35 30.66C222.83 28.4267 222 26.9033 220.86 26.09C219.713 25.27 218.2 24.86 216.32 24.86C214.433 24.86 212.92 25.27 211.78 26.09C210.633 26.9033 209.8 28.4267 209.28 30.66C208.76 32.8933 208.5 36.0933 208.5 40.26C208.5 44.4333 208.76 47.6233 209.28 49.83C209.8 52.0367 210.633 53.56 211.78 54.4C212.92 55.2467 214.433 55.67 216.32 55.67Z" fill="currentColor"/>
<path d="M251.22 57.9L239.76 25.38H235.15V23.52H253.16V25.38H248.92L255.46 45.4H255.76L263.5 23.52H266.33L274.22 45.4H274.52L281.14 25.38H275.63V23.52H289.1V25.38H283.75L272.51 57.9H270.35L262.24 35.05H261.94L253.38 57.9H251.22Z" fill="currentColor"/>
<path d="M301.68 57.98C299.1 57.98 297.177 57.4566 295.91 56.41C294.644 55.3633 294.01 53.8 294.01 51.72V25.45H287.99V23.59C289.724 23.4433 291.347 22.7633 292.86 21.55C294.374 20.33 295.764 18.7766 297.03 16.89C298.297 15.01 299.327 12.9533 300.12 10.72H302.2V23.59H310.76V25.45H302.2V51.2C302.2 53.4333 303.12 54.55 304.96 54.55C306 54.55 306.954 54.19 307.82 53.47C308.687 52.7566 309.594 51.5533 310.54 49.86L312.03 50.68C310.79 53.4133 309.374 55.3133 307.78 56.38C306.194 57.4466 304.16 57.98 301.68 57.98Z" fill="currentColor"/>
<path d="M315.82 2.75001V0.890015H329.89V2.75001H315.82ZM315.82 57.01V55.15H334.72V57.01H315.82ZM321.7 55.89V2.08001H329.89V55.89H321.7ZM343.8 55.89V30.74C343.8 28.8533 343.394 27.45 342.58 26.53C341.767 25.61 340.61 25.1533 339.11 25.16C337.424 25.16 335.874 25.8267 334.46 27.16C333.047 28.5 331.93 30.3233 331.11 32.63C330.297 34.9433 329.89 37.5634 329.89 40.49L328.4 29.7H330.18C331.274 27.66 332.737 25.97 334.57 24.63C336.41 23.29 338.794 22.62 341.72 22.62C344.747 22.62 347.214 23.4033 349.12 24.97C351.034 26.53 351.99 28.4533 351.99 30.74V55.89H343.8ZM338.15 57.01V55.15H357.05V57.01H338.15Z" fill="currentColor"/>
</g>
</g></svg></a>
    <div class="hdr-meta mono"><span>Proposta de operação</span><span>Preparada para <b data-campo="empresa"></b></span></div>
    <nav class="hdr-nav" aria-label="Seções">
      <a href="#manifesto">Manifesto</a><a href="#cenario">Seu cenário</a><a href="#sistema">O sistema</a><a href="#prova">Prova</a><a href="#investimento">Investimento</a>
    </nav>
    <div class="hdr-conta mono"><b id="secNum">00</b><span id="secNome">Capa</span></div>
  </div>
  <div class="hdr-prog"><i id="prog"></i></div>
</header>

<main>

<!-- 00 · CAPA -->
<section class="sec hero" id="inicio" data-nome="Capa">
  <div class="wrap">
    <h1 class="sr">O sistema por trás do seu crescimento</h1>
    <div class="hero-l1">
      <p class="eyebrow" data-geral="Proposta de operação · J.P.Growth">Reunião estratégica · <span data-campo="empresa"></span></p>
      <div class="display hero-h1" aria-hidden="true">O sistema</div>
    </div>
    <div class="hero-meio">
      <div class="hero-col esq">
        <p class="hero-lema">Vender mais hoje.<br>Valer mais amanhã.</p>
        <i class="traco"></i>
        <p class="mono mini">Estratégia &amp; performance<br>Criativos &amp; comunicação<br>Dados &amp; relatório</p>
        <svg class="selo-giro" viewBox="0 0 200 200" aria-hidden="true"><defs><path id="cg1" d="M100,100 m-76,0 a76,76 0 1,1 152,0 a76,76 0 1,1 -152,0"/></defs><text><textPath href="#cg1" textLength="474" lengthAdjust="spacing">VENDER MAIS HOJE ✦ VALER MAIS AMANHÃ ✦</textPath></text><use class="miolo" href="#estrela" x="80" y="80" width="40" height="40"/></svg>
      </div>
      <div class="hero-estrela" aria-hidden="true">
        <svg class="mira" viewBox="0 0 400 400" preserveAspectRatio="xMidYMid meet"><circle cx="200" cy="200" r="186" class="tr"/><circle cx="200" cy="200" r="138"/><circle cx="200" cy="200" r="70" class="tr"/><path d="M200 0V400M0 200H400" class="tr"/><path d="M200 8v14M200 378v14M8 200h14M378 200h14"/></svg>
        <div class="estrela-hero" id="estrelaHero"><svg viewBox="0 0 35 35"><use href="#estrela"/></svg></div>
      </div>
      <div class="hero-col dir">
        <p data-geral="Proposta de operação de marketing para empresas prontas para crescer.">Proposta de operação de marketing preparada para <b data-campo="empresa"></b>.</p>
        <i class="traco"></i>
        <p class="mono mini">Com João Paulo B. Ferreira<br>e Pedro Selestrim</p>
      </div>
      <div class="nota-colada"><i class="fita"></i><p class="hand tinta">feita sob medida pra <span data-campo="empresa"></span> ✓</p></div>
    </div>
    <div class="hero-l3" aria-hidden="true">
      <div class="display h-pesado">Por trás</div>
      <div class="serif h-italico">do seu crescimento.</div>
      <div class="mono hero-acoes">Estratégia<br>Tráfego<br>Criativo<br>Venda</div>
    </div>
    <div class="hero-rod mono">
      <span data-geral="Assessoria de marketing completa">Para <b data-campo="nome"></b> · <b data-campo="empresa"></b></span>
      <span data-campo="data"></span>
      <span>J.P.Growth · assessoria completa</span>
      <span class="rolar"><i></i>Apresentação ao vivo</span>
    </div>
  </div>
</section>

<!-- 01 · MANIFESTO -->
<section class="sec" id="manifesto" data-nome="Manifesto">
  <div class="wrap">
    <div class="mani-topo" aria-label="Marketing não é apertar o botão Publicar">
      <div class="display mani-a">Marketing<br>não é</div>
      <div class="serif mani-b">apertar o botão</div>
      <div class="display mani-c">“Publicar.”</div>
      <div class="mani-btn rv">
        <span class="btn-publicar" aria-hidden="true">Publicar</span>
        <p class="hand">o resultado não nasce aqui</p>
      </div>
      <div class="mono mani-d">Entender<br>Criar<br>Medir<br>Repetir</div>
    </div>
    <div class="mani-corpo">
      <div class="mani-lado">
        <p class="eyebrow">No que a gente acredita</p>
        <p class="num">01</p>
      </div>
      <div class="scrub-box" id="scrub">
        <p>Anúncio bonito não paga boleto. Seguidor também não.</p>
        <p>O que paga é o caminho inteiro: a oferta clara, a mensagem certa, a página que converte e alguém pronto para atender.</p>
        <p data-parada>A gente acredita que bom gosto e número não brigam. Trabalham juntos.</p>
        <p>Que o empresário tem o direito de saber para onde vai cada real.</p>
        <p data-parada>E que crescimento de verdade não depende de sorte, de viral ou de mês bom. Depende de <mark>sistema.<svg class="sublinha" viewBox="0 0 100 12" preserveAspectRatio="none" aria-hidden="true"><path pathLength="1" d="M2 8 C 22 3, 48 4, 68 6 S 92 9, 98 4"/></svg></mark></p>
      </div>
    </div>
  </div>
</section>

<!-- 02 · QUEM SOMOS -->
<section class="sec" id="quem" data-nome="Quem somos">
  <div class="wrap pad">
    <div class="bento">
      <article class="b-card b-grande rv">
        <div>
          <p class="eyebrow">Quem somos</p>
          <h2 class="h2">Uma assessoria de marketing completa. <em>Conduzida pelos sócios.</em></h2>
        </div>
        <div>
          <p class="lead">A J.P.Growth junta duas metades que quase nunca andam juntas: performance e criação. Estratégia, tráfego e dados de um lado. Marca, criativos e comunicação do outro. Tudo num time só, com os sócios dentro da operação.</p>
          <p class="b-soma" style="margin-top:22px"><span class="pill">Performance</span>+<span class="pill">Criação</span>=<span class="pill pill-vinho">Um time só</span></p>
        </div>
      </article>
      <article class="b-card b-num rv" style="--d:.08s"><span class="mono"><i class="estrela-i" style="color:var(--wine)"></i>Empresas atendidas</span><b class="conta" data-para="100" data-pre="+">+100</b><p>Clínicas, educação, indústria, varejo e serviços.</p></article>
      <article class="b-card b-num escuro rv" style="--d:.16s"><span class="mono">Mídia gerenciada</span><b class="conta" data-para="3" data-pre="+R$ " data-suf=" mi">+R$ 3 mi</b><p>Investidos com acompanhamento semanal.</p></article>
      <article class="b-card b-num rv" style="--d:.24s"><span class="mono">Em performance</span><b class="conta" data-para="4" data-suf="+ anos">4+ anos</b><p>Rodando campanhas que precisam vender.</p></article>
      <article class="b-card b-num vinho rv" style="--d:.32s"><span class="mono">Certificados</span><b>Meta &amp; Google</b><p>Especialistas certificados nas duas maiores plataformas de mídia.</p></article>
    </div>
  </div>
  <div class="faixa" aria-label="Tráfego pago, copy, roteiros, criativos, landing pages, identidade, dados e relatório">
    <div class="faixa-trilho" aria-hidden="true">
      <span>Tráfego pago<i></i>Copy<i></i>Roteiros<i></i>Criativos<i></i>Landing pages<i></i>Identidade<i></i>Dados<i></i>Relatório<i></i></span>
      <span>Tráfego pago<i></i>Copy<i></i>Roteiros<i></i>Criativos<i></i>Landing pages<i></i>Identidade<i></i>Dados<i></i>Relatório<i></i></span>
    </div>
  </div>
  <div class="wrap logos-wrap">
    <p class="mono">Nossa equipe já atendeu empresas como</p>
    <div class="logos">
      <img src="../logos/skyfit.webp" alt="Skyfit" loading="lazy">
      <img src="../logos/v4-company.webp" alt="V4 Company" loading="lazy">
      <img src="../logos/guerrilha-way.webp" alt="Guerrilha Way" loading="lazy">
      <img src="../logos/maria-mater.webp" alt="Maria Mater" loading="lazy">
      <img src="../logos/cultor-de-livros.webp" alt="Cultor de Livros" loading="lazy">
      <img src="../logos/bike-and-fly.webp" alt="Bike and Fly" loading="lazy">
      <img src="../logos/fma.webp" alt="FMA" loading="lazy">
    </div>
  </div>
</section>

<!-- 03 · VISÃO E VALORES -->
<section class="sec dark" id="valores" data-nome="Visão e valores">
  <div class="wrap pad">
    <p class="eyebrow">Para onde a gente vai</p>
    <h2 class="lema" id="lema"><span class="display">Vender mais hoje.</span><span class="serif">Valer mais amanhã.</span></h2>
    <div class="mv">
      <div class="rv"><span class="mono">Missão</span><p>Transformar o marketing de empresas prontas para crescer em um sistema previsível de aquisição de clientes.</p></div>
      <div class="rv" style="--d:.12s"><span class="mono">Visão</span><p>Ser a assessoria que une performance e marca: empresas que crescem em vendas e em valor ao mesmo tempo, e que nos indicam por isso.</p></div>
    </div>
  </div>
  <div class="wrap">
    <div class="val-head">
      <p class="eyebrow">Nossos valores</p>
      <h2 class="h2">O jeito J.P.Growth <em>de trabalhar.</em></h2>
    </div>
    <ol class="valores">
      <li class="rv"><span class="n">01</span><h3>Sistema, não botão.</h3><p>Nada sai solto. Cada anúncio, página e mensagem trabalha pela próxima peça.</p></li>
      <li class="rv" style="--d:.08s"><span class="n">02</span><h3>Venda acima de vaidade.</h3><p>Curtida, alcance e lead barato não pagam boleto. A gente mede o que entra no caixa.</p></li>
      <li class="rv" style="--d:.16s"><span class="n">03</span><h3>Número na mesa.</h3><p>Toda semana você vê para onde foi cada real. Sem relatório maquiado, sem métrica escondida.</p></li>
      <li class="rv"><span class="n">04</span><h3>Bom gosto também vende.</h3><p>Marca bem feita reduz objeção, sustenta preço e faz o cliente lembrar de você.</p></li>
      <li class="rv" style="--d:.08s"><span class="n">05</span><h3>Sócio na operação.</h3><p>Quem vende o projeto é quem faz o projeto. Você fala com quem decide, não com um intermediário.</p></li>
      <li class="rv destaque" style="--d:.16s"><span class="n">06</span><h3>Verdade antes do contrato.</h3><p>Se não for o momento da sua empresa, a gente fala. Preferimos perder uma venda a perder a confiança.</p>
        <p class="hand"><svg class="seta" viewBox="0 0 90 70" aria-hidden="true"><path pathLength="1" d="M84 62 C 60 64, 30 52, 14 14"/><path pathLength="1" d="M14 14 L 10 30 M14 14 L 27 23"/></svg>o mais importante pra hoje</p></li>
    </ol>
  </div>
</section>

<!-- 04 · O SEU CENÁRIO -->
<section class="sec" id="cenario" data-nome="O seu cenário">
  <div class="wrap pad">
    <div class="cen-head">
      <p class="eyebrow">O seu cenário</p>
      <h2 class="h2">O que entendemos <em>da <span data-campo="empresa"></span>.</em></h2>
    </div>
    <div class="cen-grid">
      <div class="ficha rv">
        <div class="ficha-top mono"><span>Ficha · <b data-campo="empresa"></b></span><span>Recebida pelo site</span></div>
        <dl>
          <div><dt>Segmento</dt><dd data-campo="segmento"></dd></div>
          <div><dt>Faturamento mensal</dt><dd data-campo="faturamento"></dd></div>
          <div><dt>Anúncios hoje</dt><dd data-campo="verba_atual"></dd></div>
          <div><dt>Quem atende os leads</dt><dd data-campo="atendimento"></dd></div>
          <div><dt>Principal desafio</dt><dd class="forte" data-campo="desafio"></dd></div>
        </dl>
        <p class="ficha-pe mono">O que você nos contou no formulário</p>
        <span class="carimbo">Analisado ✓</span>
      </div>
      <div class="notas">
        <p class="mono notas-tit">O que vimos antes da conversa</p>
        <div class="nota rv" style="--r:-1.6deg"><i class="fita"></i><span class="mono">01</span><p class="hand tinta" data-obs="0"></p></div>
        <div class="nota rv" style="--r:1.4deg;--d:.12s"><i class="fita"></i><span class="mono">02</span><p class="hand tinta" data-obs="1"></p></div>
        <div class="nota rv" style="--r:-1deg;--d:.24s"><i class="fita"></i><span class="mono">03</span><p class="hand tinta" data-obs="2"></p></div>
      </div>
    </div>
    <div class="cen-pergunta rv">
      <svg class="seta" viewBox="0 0 90 70" aria-hidden="true"><path pathLength="1" d="M84 8 C 62 6, 30 16, 14 56"/><path pathLength="1" d="M14 56 L 10 40 M14 56 L 28 48"/></svg>
      <p class="hand">Está certo? O que ficou de fora?</p>
    </div>
  </div>
</section>

<!-- 05 · O PROBLEMA -->
<section class="sec" id="problema" data-nome="O problema">
  <div class="wrap pad">
    <div class="prob-head">
      <div>
        <p class="eyebrow">O que costuma acontecer</p>
        <h2 class="h2">Talvez o problema <em>não seja o seu tráfego.</em></h2>
      </div>
      <p class="lead">Dá para aumentar a verba, trocar a campanha e testar anúncio novo toda semana. Mas se uma peça do caminho falha, aumentar o investimento só aumenta o desperdício.</p>
    </div>
    <div class="vaza rv" id="vaza">
      <svg id="vazaSvg" role="img" aria-label="Leads entram pelo anúncio e vazam em cada peça desconectada: criativo, oferta, página e comercial. Poucos chegam à venda."></svg>
      <div class="vaza-placar mono" aria-live="off">
        <span>Leads que entraram <b id="vzE">0</b></span>
        <span>Viraram venda <b id="vzV">0</b></span>
        <span>Aproveitamento <b id="vzP">0%</b></span>
      </div>
    </div>
    <div class="pecas">
      <div class="peca rv"><span class="mono">01 · Criativo</span><h3>Não para o scroll</h3><p>Nem comunica valor. A pessoa passa direto.</p><span class="carimbo">Desconectado</span></div>
      <div class="peca rv" style="--d:.1s"><span class="mono">02 · Oferta</span><h3>Não convence</h3><p>Não deixa claro por que escolher você.</p><span class="carimbo">Desconectado</span></div>
      <div class="peca rv" style="--d:.2s"><span class="mono">03 · Landing page</span><h3>Não converte</h3><p>Recebe o clique, mas não transforma a visita em contato.</p><span class="carimbo">Desconectado</span></div>
      <div class="peca rv" style="--d:.3s"><span class="mono">04 · Comercial</span><h3>Não responde a tempo</h3><p>O lead chega e ninguém acompanha enquanto ele está quente.</p><span class="carimbo">Desconectado</span></div>
    </div>
    <div class="prob-voce rv">
      <p class="hand">Você comentou que <span data-campo="desafio_curto"></span>.</p>
      <p>Quase sempre a causa está em uma dessas quatro peças, e não na campanha.</p>
    </div>
  </div>
</section>

<!-- 06 · EM PEDAÇOS (cena fixa) -->
<section class="sec cena" id="pedacos" data-nome="Em pedaços" data-paradas="2" style="--alt:290vh">
  <div class="fixo">
    <div class="wrap">
      <div class="ped-in">
        <p class="eyebrow" id="pedEb">Como costuma ser</p>
        <div class="ped-tit">
          <h2 class="h2" id="pedT1">5 fornecedores, 5 briefings <em>e ninguém responsável pelo todo.</em></h2>
          <p class="h2" id="pedT2" style="opacity:0">1 time, 1 estratégia <em>e 1 responsável pelo resultado.</em></p>
        </div>
        <div class="palco" id="palco">
          <svg class="palco-svg" id="palcoSvg" aria-hidden="true"><g id="emaranhado"></g><path class="palco-linha" id="palcoLinha" pathLength="1"/></svg>
          <div class="forn"><span class="forn-n" data-a="Fornecedor 01" data-b="J.P.Growth">Fornecedor 01</span><b>Gestor de tráfego</b><span class="forn-m">mede: <em data-a="cliques" data-b="venda">cliques</em></span></div>
          <div class="forn"><span class="forn-n" data-a="Fornecedor 02" data-b="J.P.Growth">Fornecedor 02</span><b>Designer</b><span class="forn-m">mede: <em data-a="peças entregues" data-b="venda">peças entregues</em></span></div>
          <div class="forn"><span class="forn-n" data-a="Fornecedor 03" data-b="J.P.Growth">Fornecedor 03</span><b>Copywriter</b><span class="forn-m">mede: <em data-a="textos entregues" data-b="venda">textos entregues</em></span></div>
          <div class="forn"><span class="forn-n" data-a="Fornecedor 04" data-b="J.P.Growth">Fornecedor 04</span><b>Estrategista</b><span class="forn-m">mede: <em data-a="reuniões" data-b="venda">reuniões</em></span></div>
          <div class="forn"><span class="forn-n" data-a="Fornecedor 05" data-b="J.P.Growth">Fornecedor 05</span><b>Analista de dados</b><span class="forn-m">mede: <em data-a="relatórios" data-b="venda">relatórios</em></span></div>
          <p class="hand ped-nota ped-n1" id="pedN1">cada um defende<br>a própria métrica</p>
          <p class="hand ped-nota ped-n2" id="pedN2" style="opacity:0">um time só, uma meta só: venda</p>
        </div>
        <ul class="ped-pills" id="pedPills" style="opacity:0">
          <li class="pill">1 time</li><li class="pill">1 estratégia</li><li class="pill">1 relatório</li><li class="pill pill-vinho">1 responsável pelo resultado</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- 07 · O SISTEMA -->
<section class="sec" id="sistema" data-nome="O sistema">
  <div class="wrap pad">
    <div class="sis-head">
      <div>
        <p class="eyebrow">O sistema J.P.Growth</p>
        <h2 class="h2">Cada peça trabalha <em>pela próxima.</em></h2>
      </div>
      <p class="lead">Acompanhe o caminho de um lead. Em cada etapa, alguém da operação garante que ele chegue inteiro na próxima.</p>
    </div>
    <div class="trilha" id="trilha">
      <svg class="trilha-svg" id="trilhaSvg" aria-hidden="true">
        <defs><marker id="pontaVolta" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse"><path d="M1 1 L8 5 L1 9" fill="none" stroke="#C9575E" stroke-width="1.6" stroke-linecap="round"/></marker></defs>
        <path class="trilha-base" id="trilhaBase"/>
        <path class="trilha-linha" id="trilhaLinha"/>
        <path class="trilha-volta" id="trilhaVolta" pathLength="1" marker-end="url(#pontaVolta)"/>
        <g class="lead-g" id="leadG"><circle class="lead-anel" r="13"/><circle class="lead-pt" r="6.5"/><text x="18" y="4" id="leadTxt">lead</text></g>
      </svg>
      <ol class="nos">
        <li class="no" data-lado="e" data-parada><i class="no-pino"></i><div class="no-card"><p class="no-n"><span>01</span><span>Centro de tudo</span></p><h3>Estratégia</h3><p>Define para quem, o quê e por quê. Antes de qualquer anúncio.</p><span class="no-chip">Diagnóstico · plano de ação</span></div></li>
        <li class="no" data-lado="d" data-parada><i class="no-pino"></i><div class="no-card"><p class="no-n"><span>02</span><span>Atenção</span></p><h3>Tráfego</h3><p>Traz a pessoa certa até você, no Meta e no Google.</p><span class="no-chip">CTR <b>2,41%</b> · CPC <b>R$ 2,90</b></span></div></li>
        <li class="no" data-lado="e" data-parada><i class="no-pino"></i><div class="no-card"><p class="no-n"><span>03</span><span>Interesse</span></p><h3>Copy</h3><p>Transforma atenção em interesse. Uma frase muda o resultado.</p><span class="no-chip">Headline A <b>0,9%</b> → B <b>2,7%</b></span></div></li>
        <li class="no" data-lado="d" data-parada><i class="no-pino"></i><div class="no-card"><p class="no-n"><span>04</span><span>Oferta</span></p><h3>Criativo</h3><p>Comunica a oferta em estático, carrossel e vídeo.</p><span class="no-chip">Feed · Story · Carrossel</span></div></li>
        <li class="no" data-lado="e" data-parada><i class="no-pino"></i><div class="no-card"><p class="no-n"><span>05</span><span>Contato</span></p><h3>Landing page</h3><p>Transforma interesse em contato, com a mensagem do anúncio.</p><span class="no-chip">Conversão <b>21,4%</b></span></div></li>
        <li class="no" data-lado="d" data-parada><i class="no-pino"></i><div class="no-card seu"><p class="no-n"><span>06</span><span>Venda</span></p><h3>Seu comercial</h3><p>Transforma contato em venda. É aqui que o lead vira cliente.</p><p class="hand">essa parte é de vocês. por isso a gente conversa com o comercial toda semana</p></div></li>
        <li class="no" data-lado="e" data-parada><i class="no-pino"></i><div class="no-card"><p class="no-n"><span>07</span><span>Aprendizado</span></p><h3>Dados</h3><p>Mostram o que funcionou e voltam para a estratégia. Toda semana.</p><span class="no-chip">Relatório toda segunda</span></div></li>
      </ol>
      <p class="hand volta-nota" id="voltaNota">↺ e volta pra estratégia: é um ciclo, não uma linha</p>
    </div>
    <div class="sis-fim rv">
      <p class="h2 h2-s">Não entregamos só anúncios. <em>Construímos o sistema por trás deles.</em></p>
    </div>
  </div>
</section>

<!-- 08 · AS 8 FRENTES (cena fixa) -->
<section class="sec cena" id="frentes" data-nome="8 frentes" data-paradas="8" style="--alt:420vh">
  <div class="fixo">
    <div class="wrap">
      <div class="est-in">
        <div class="est-txt">
          <p class="eyebrow">O que você recebe</p>
          <h2 class="h2">8 frentes. <em>Um centro só.</em></h2>
          <p class="lead">Tudo o que o seu marketing precisa, preso à mesma estratégia e medido pelo mesmo número: venda.</p>
          <div class="est-painel" id="estPainel" aria-live="polite">
            <p class="mono" id="estN">01 / 08</p>
            <h3 id="estT">Tráfego pago</h3>
            <p id="estD">Meta Ads, Google Ads e outras mídias que fizerem sentido para o negócio.</p>
          </div>
          <ol class="est-lista mono" id="estLista"></ol>
        </div>
        <div class="est-box" id="estBox">
          <svg class="est-svg" viewBox="0 0 35 35" aria-hidden="true">
            <path class="pet" d="M17.5001 17.5L20.4073 3.56796C20.7943 1.72735 19.3879 0 17.5001 0C15.6217 0 14.2153 1.72735 14.5928 3.56796L17.5001 17.5Z"/>
            <path class="pet" d="M17.5 17.4995L29.4026 9.70287C30.979 8.67401 31.2055 6.45583 29.8746 5.12493C28.5437 3.79402 26.3255 4.02056 25.2967 5.59688L17.5 17.4995Z"/>
            <path class="pet" d="M17.5 17.501L31.432 20.4083C33.2727 20.7953 35 19.3888 35 17.501C35 15.6227 33.2727 14.2162 31.432 14.5938L17.5 17.501Z"/>
            <path class="pet" d="M17.5 17.5L25.2967 29.4026C26.3255 30.979 28.5437 31.2055 29.8746 29.8746C31.2055 28.5437 30.979 26.3255 29.4026 25.2967L17.5 17.5Z"/>
            <path class="pet" d="M17.4999 17.5L14.5927 31.432C14.2057 33.2727 15.6121 35 17.4999 35C19.3783 35 20.7847 33.2727 20.4072 31.432L17.4999 17.5Z"/>
            <path class="pet" d="M17.4999 17.5L5.59725 25.2967C4.02092 26.3255 3.79439 28.5437 5.12529 29.8746C6.4562 31.2055 8.67438 30.979 9.70323 29.4026L17.4999 17.5Z"/>
            <path class="pet" d="M17.5 17.5004L3.56796 14.5932C1.72735 14.2062 0 15.6126 0 17.5004C0 19.3788 1.72735 20.7852 3.56796 20.4077L17.5 17.5004Z"/>
            <path class="pet" d="M17.4999 17.4995L9.70323 5.59688C8.67438 4.02056 6.4562 3.79402 5.12529 5.12493C3.79439 6.45583 4.02092 8.67401 5.59725 9.70287L17.4999 17.4995Z"/>
          </svg>
          <span class="est-centro mono">Estra&shy;tégia</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 09 · COMPARATIVO -->
<section class="sec" id="diferenca" data-nome="A diferença">
  <div class="wrap pad">
    <div class="cmp-head">
      <div>
        <p class="eyebrow">Por que não uma agência de tráfego</p>
        <h2 class="h2">A diferença está <em>em tudo que vem antes e depois do anúncio.</em></h2>
      </div>
    </div>
    <div class="cmp">
      <div class="cmp-h"><span></span><span class="cmp-ag">Agência de tráfego</span><span class="cmp-jp"><i class="estrela-i"></i>J.P.Growth</span></div>
      <div class="cmp-r rv"><span class="cmp-l">Ponto de partida</span><span class="cmp-a"><s>Subir campanhas</s></span><span class="cmp-j">Entender negócio, oferta, público e comercial</span></div>
      <div class="cmp-r rv" style="--d:.06s"><span class="cmp-l">Escopo</span><span class="cmp-a"><s>Anúncios</s></span><span class="cmp-j">Estratégia, tráfego, copy, roteiros, criativos, páginas, marca e dados</span></div>
      <div class="cmp-r rv" style="--d:.12s"><span class="cmp-l">Criativos</span><span class="cmp-a"><s>Você manda o material</s></span><span class="cmp-j">Roteirizados e produzidos por nós</span></div>
      <div class="cmp-r rv" style="--d:.18s"><span class="cmp-l">Landing page</span><span class="cmp-a"><s>Fica por sua conta</s></span><span class="cmp-j">Faz parte da operação</span></div>
      <div class="cmp-r rv" style="--d:.24s"><span class="cmp-l">Quem te atende</span><span class="cmp-a"><s>Um atendente que repassa recado</s></span><span class="cmp-j">Os sócios que fazem a estratégia</span></div>
      <div class="cmp-r rv" style="--d:.3s"><span class="cmp-l">Acompanhamento</span><span class="cmp-a"><s>Métricas da plataforma</s></span><span class="cmp-j">Relatório semanal e dashboard de leads e vendas</span></div>
      <div class="cmp-r rv ultima" style="--d:.36s"><span class="cmp-l">O número que importa</span><span class="cmp-a"><s>Custo por clique, custo por lead</s></span><span class="cmp-j">Custo por venda</span></div>
    </div>
    <div class="cmp-nota rv">
      <p class="hand">lead barato que não vira venda não paga boleto</p>
      <svg class="seta" viewBox="0 0 90 70" aria-hidden="true"><path pathLength="1" d="M84 62 C 60 64, 30 52, 14 14"/><path pathLength="1" d="M14 14 L 10 30 M14 14 L 27 23"/></svg>
    </div>
  </div>
</section>

<!-- 10 · PROVA -->
<section class="sec" id="prova" data-nome="Prova">
  <div class="wrap pad">
    <div class="prova-head">
      <p class="eyebrow">Prova</p>
      <h2 class="h2">Prova visual <em>vale mais que promessa.</em></h2>
    </div>
    <div class="destaque">
      <figure class="polaroid rv" style="--r:-2.4deg">
        <i class="fita"></i>
        <button class="foto-btn" type="button" id="dBtn"><img id="dImg" alt="" loading="lazy"></button>
        <figcaption class="mono"><span id="dCap"></span><span>Clique para ampliar</span></figcaption>
      </figure>
      <div class="destaque-txt rv" style="--d:.12s">
        <span class="carimbo">Dados reais</span>
        <p class="mono seg" id="dSeg"></p>
        <h3 class="h3" id="dTit"></h3>
        <dl class="d-stats" id="dStats"></dl>
        <p class="d-nota" id="dNota"></p>
        <p class="hand d-hand"><svg class="seta" viewBox="0 0 90 70" aria-hidden="true"><path pathLength="1" d="M84 8 C 62 6, 30 16, 14 56"/><path pathLength="1" d="M14 56 L 10 40 M14 56 L 28 48"/></svg>escolhemos esse porque é o mais parecido com o seu caso</p>
      </div>
    </div>
    <div class="obras-head mono"><span>Outros resultados</span><span>Clique para ver o print real</span></div>
    <div class="obras" id="obras"></div>
  </div>
  <div class="wrap pad depos-wrap">
    <div class="depos-head">
      <div>
        <p class="eyebrow">No dia a dia</p>
        <h3 class="h2 h2-s">O que os clientes <em>falam no WhatsApp.</em></h3>
      </div>
      <p class="mono mini">Conversas reais. Nomes, fotos e telefones ocultos para preservar os clientes.</p>
    </div>
    <div class="depos">
      <article class="depo rv"><div class="depo-top"><span>Imobiliário</span><span>WhatsApp</span></div><div class="chat"><p class="msg out">Fomos de R$ 113,00 no mês passado para R$ 51,00 por lead neste mês. Eles continuam qualificados?<time>11:01</time></p><p class="msg">Então, estranhamente mudou pra melhor<time>11:02</time></p><p class="msg">Respondi na mesma hora que entrou e já agendamos pra unidade de 2 milhões<time>11:02</time></p></div><div class="depo-pe"><p>Custo por lead de R$ 113 para R$ 51, com leads ainda melhores.</p><button type="button" data-print="../prints/depo-custo-por-lead.jpg" data-cap="Conversa real no grupo do cliente (nome e foto ocultos)">Ver conversa real</button></div></article>
      <article class="depo rv" style="--d:.08s"><div class="depo-top"><span>Educação</span><span>WhatsApp</span></div><div class="chat"><p class="msg">Bom dia pessoal<time>11:55</time></p><p class="msg">Tudo bem ?<time>11:55</time></p><p class="msg">Sábado fechamos 2 matrículas da internet 🚀🧡🥰<time>11:55</time></p></div><div class="depo-pe"><p>2 matrículas fechadas num sábado, vindas da internet.</p><button type="button" data-print="../prints/depo-matriculas.jpg" data-cap="Conversa real no grupo do cliente (nome e foto ocultos)">Ver conversa real</button></div></article>
      <article class="depo rv" style="--d:.16s"><div class="depo-top"><span>Transportes</span><span>WhatsApp</span></div><div class="chat"><p class="msg out">Só nesse mês o Google apontou mais de 30 conversões. Como estão as vendas?<time>11:14</time></p><p class="msg">Estão muito boa<time>11:15</time></p><p class="msg">Melhor que no mês passado<time>11:15</time></p></div><div class="depo-pe"><p>Mais de 30 conversões no Google e vendas acima do mês anterior.</p><button type="button" data-print="../prints/depo-vendas.jpg" data-cap="Conversa real no grupo do cliente (nomes e fotos ocultos)">Ver conversa real</button></div></article>
      <article class="depo rv" style="--d:.24s"><div class="depo-top"><span>Saúde</span><span>WhatsApp</span></div><div class="chat"><p class="msg">O trabalho de programação e design que o Pedro presta para nós é impecável em todos os aspectos.<time>19:24</time></p><p class="msg">Temos uma parceria de anos que não decepciona.<time>19:24</time></p></div><div class="depo-pe"><p>Uma parceria de anos, com sites de excelência.</p><button type="button" data-print="../prints/depo-zanata.jpg" data-cap="Depoimento real enviado por WhatsApp (contato, telefone e fotos ocultos)">Ver conversa real</button></div></article>
    </div>
  </div>
</section>

<!-- 11 · BRANDING -->
<section class="sec dark" id="marca" data-nome="Branding">
  <div class="wrap pad marca-head">
    <div>
      <p class="eyebrow">Branding</p>
      <h2 class="h2">Performance traz o cliente. <em>Marca faz ele escolher você.</em></h2>
    </div>
    <p class="lead">Quando a marca transmite o valor que a empresa tem, o anúncio converte mais, o preço deixa de ser a primeira objeção e o cliente volta. Por isso identidade e posicionamento fazem parte da operação.</p>
  </div>
  <div class="parede" id="parede" aria-label="Marcas criadas pela equipe">
    <div class="fila" id="fila1"></div>
    <div class="fila" id="fila2"></div>
  </div>
  <div class="wrap marca-pe mono"><span>Marcas que ajudamos a construir</span><span>Identidade · posicionamento · aplicação</span></div>
</section>

<!-- 12 · QUEM CONDUZ -->
<section class="sec" id="socios" data-nome="Quem conduz">
  <div class="wrap pad socios">
    <div class="socios-fotos">
      <figure class="polaroid rv" style="--r:-3.5deg"><i class="fita"></i><img src="../fotos/socio-01.webp" alt="João Paulo B. Ferreira" loading="lazy" width="1086" height="1448"><figcaption class="hand tinta">João Paulo<small>estratégia &amp; números</small></figcaption></figure>
      <figure class="polaroid rv" style="--r:3deg;--d:.15s"><i class="fita"></i><img src="../fotos/socio-02.webp" alt="Pedro Selestrim" loading="lazy" width="1086" height="1448"><figcaption class="hand tinta">Pedro<small>marca &amp; criativos</small></figcaption></figure>
    </div>
    <div class="socios-txt">
      <p class="eyebrow">Quem conduz a operação</p>
      <h2 class="h2">Você fala com <em>quem constrói a estratégia.</em></h2>
      <div class="socio rv"><p class="mono">Estratégia &amp; Performance</p><h3>João Paulo B. Ferreira</h3><p>Cuida do plano, das campanhas e dos números. É quem olha o custo por venda toda semana e decide onde colocar cada real.</p></div>
      <div class="socio rv" style="--d:.1s"><p class="mono">Criativos &amp; Comunicação</p><h3>Pedro Selestrim</h3><p>Cuida da marca, dos criativos, das páginas e da mensagem. É quem garante que o anúncio pare o scroll e que a página sustente a promessa.</p></div>
      <div class="certs"><span class="pill">Meta Certified</span><span class="pill">Google Ads Certified</span></div>
      <p class="hand socios-hand rv"><svg class="seta" viewBox="0 0 90 70" aria-hidden="true"><path pathLength="1" d="M84 8 C 62 6, 30 16, 14 56"/><path pathLength="1" d="M14 56 L 10 40 M14 56 L 28 48"/></svg>do diagnóstico ao relatório de segunda, somos nós dois</p>
    </div>
  </div>
</section>

<!-- 13 · OS PRIMEIROS 90 DIAS -->
<section class="sec" id="noventa" data-nome="Os primeiros 90 dias">
  <div class="wrap pad noventa">
    <div class="noventa-esq">
      <p class="eyebrow">Como começa</p>
      <h2 class="h2">Sem promessa milagrosa. <em>Com etapa, entrega e número.</em></h2>
      <div class="semanas" id="semanas" aria-hidden="true"></div>
      <div class="legenda mono"><span style="--c:var(--brown-3)">Diagnóstico</span><span style="--c:#6B4A3F">Fundação</span><span style="--c:#9A4A4F">Testes</span><span style="--c:var(--wine)">Escala</span><span class="rel">Relatório de segunda</span></div>
      <p class="hand noventa-hand"><svg class="seta" viewBox="0 0 90 70" aria-hidden="true"><path pathLength="1" d="M84 8 C 62 6, 30 16, 14 56"/><path pathLength="1" d="M14 56 L 10 40 M14 56 L 28 48"/></svg>quem promete escala na semana 1 está vendendo sorte</p>
    </div>
    <ol class="fases" id="fases">
      <i class="fases-linha"><b id="fasesLinha"></b></i>
      <li class="fase" data-de="0" data-parada><p class="mono">Semanas 1 e 2</p><h3>Diagnóstico</h3><p>Mergulho no negócio, na oferta, nos números e no comercial. Sai o plano de ação.</p></li>
      <li class="fase" data-de="2" data-parada><p class="mono">Semanas 3 e 4</p><h3>Fundação</h3><p>Rastreamento (Pixel e conversões), landing page e primeiros criativos no ar.</p></li>
      <li class="fase" data-de="4" data-parada><p class="mono">Semanas 5 a 8</p><h3>Testes</h3><p>Público, oferta e criativo. O que não vende sai, o que vende fica.</p></li>
      <li class="fase" data-de="8" data-parada><p class="mono">Semanas 9 a 12</p><h3>Escala</h3><p>Mais força no que já provou que vende.</p></li>
      <li class="fase seg" data-de="12"><p class="mono">Toda segunda</p><h3>Relatório</h3><p>Número e próximo passo, sem enrolação.</p></li>
    </ol>
  </div>
</section>

<!-- 14 · O COMBINADO -->
<section class="sec" id="combinado" data-nome="O combinado">
  <div class="wrap pad">
    <div class="pacto-head">
      <p class="eyebrow">O combinado</p>
      <h2 class="h2">Resultado é trabalho <em>dos dois lados da mesa.</em></h2>
    </div>
    <div class="pacto rv" id="pacto">
      <div class="pacto-col nossa">
        <p class="mono">J.P.Growth</p>
        <h3>Nossa parte</h3>
        <ul>
          <li>Estratégia, tráfego, copy, roteiros, criativos, landing pages, marca e dados.</li>
          <li>Relatório toda segunda e dashboard aberto.</li>
          <li>Os sócios na operação, com canal direto.</li>
          <li>Ajuste semanal com o seu comercial.</li>
        </ul>
      </div>
      <div class="lacre" aria-hidden="true">
        <svg viewBox="0 0 120 120"><path d="M60 6c8 0 11 5 18 7s13-1 18 5 2 12 6 18 10 9 10 16-6 10-6 17 5 12 1 18-11 4-16 9-4 12-11 14-11-3-18-3-12 6-19 3-6-10-11-14-13-2-17-8 1-12-2-18-9-11-7-18 8-9 10-16-1-13 5-18 12-1 18-5 11-7 21-7z" fill="#7A2E33"/><circle cx="60" cy="60" r="34" fill="none" stroke="#5A1F24" stroke-width="2"/><circle cx="60" cy="60" r="29" fill="none" stroke="#9A464C" stroke-width="1" opacity=".7"/><use href="#estrela" x="43" y="43" width="34" height="34" fill="#4E1A1F"/></svg>
      </div>
      <div class="pacto-col sua">
        <p class="mono" style="color:var(--ink-2)"><span data-campo="empresa"></span></p>
        <h3>Sua parte</h3>
        <ul>
          <li>Verba de anúncios constante, todo mês.</li>
          <li>Alguém atendendo os contatos rápido, de preferência em minutos.</li>
          <li>Retorno de quais contatos viraram venda.</li>
          <li>Acessos e aprovações sem travar a operação.</li>
        </ul>
      </div>
    </div>
    <div class="pacto-pe rv">
      <p>Essa assessoria não é para todo mundo.</p>
      <p>É para empresas prontas para crescer.</p>
    </div>
  </div>
</section>

<!-- 15 · INVESTIMENTO -->
<section class="sec" id="investimento" data-nome="Investimento">
  <div class="wrap pad">
    <div class="inv-head">
      <div>
        <p class="eyebrow">Investimento</p>
        <h2 class="h2">O time inteiro. <em>Pelo custo de uma contratação.</em></h2>
      </div>
      <p class="lead">Montar essa operação dentro de casa exige no mínimo gestor de tráfego, designer, copywriter e analista. São quatro salários, quatro encargos e quatro pessoas para gerenciar.</p>
    </div>
    <div class="inv-bento">
      <article class="inv-card time rv">
        <p class="mono">Assessoria J.P.Growth</p>
        <p class="valor" id="vAssessoria">R$ 4.000<small>/mês</small></p>
        <p>O time inteiro trabalhando na sua operação, conduzido pelos sócios.</p>
        <div class="inv-chips"><span>Estratégia</span><span>Tráfego pago</span><span>Copy</span><span>Roteiros</span><span>Criativos</span><span>Landing pages</span><span>Identidade</span><span>Dados</span><span>Relatório + dashboard</span></div>
      </article>
      <article class="inv-card verba rv" style="--d:.1s">
        <p class="mono">Verba de anúncios</p>
        <p class="de">a partir de</p>
        <p class="valor" id="vVerbaMin" style="margin-top:-4px">R$ 4.000<small>/mês</small></p>
        <p>Vai direto para Meta e Google, em nome da sua empresa. Sem teto: você decide a velocidade.</p>
      </article>
      <article class="inv-card total rv" style="--d:.2s">
        <p class="mono">Para começar</p>
        <p class="valor" id="vTotal">R$ 8 mil<small style="color:rgba(246,241,234,.7)">/mês</small></p>
        <div class="barra"><span>Time</span><span>Anúncios</span></div>
      </article>
    </div>
    <div class="ritmo rv">
      <div class="ritmo-esq">
        <p class="eyebrow">Você escolhe a velocidade</p>
        <h3 class="h3">Quanto mais verba, <em>mais rápido a gente aprende.</em></h3>
        <label for="verba">Verba por mês <output id="verbaOut" for="verba">R$ 4.000</output></label>
        <input type="range" id="verba" min="4000" max="20000" step="500" value="4000" aria-describedby="nivelDesc">
        <div class="escala"><span>R$ 4 mil</span><span>R$ 10 mil</span><span>R$ 20 mil+</span></div>
      </div>
      <div class="ritmo-dir">
        <div class="nivel-top"><p class="nivel-nome" id="nivelNome">Começar</p><div class="medidor" id="medidor"><i class="on"></i><i></i><i></i></div></div>
        <p class="nivel-desc" id="nivelDesc">Um canal principal (Meta ou Google, conforme o negócio). Foco em validar oferta e criativo.</p>
        <div class="conta-mes">
          <div><span class="mono">Investimento do mês</span><b id="contaTotal">R$ 8.000</b></div>
          <small id="contaQuebra">R$ 4.000 time + R$ 4.000 anúncios</small>
        </div>
      </div>
    </div>
    <details class="porque">
      <summary>Por que no mínimo R$ 4 mil de verba?</summary>
      <p>Abaixo disso, as campanhas geram poucos dados por semana, o algoritmo aprende devagar e cada teste demora demais para dar resposta. Você pagaria a assessoria por meses sem saber o que funciona. Preferimos começar certo a começar barato. Para ter uma ideia: um cliente de e-commerce investiu R$ 5,8 mil num mês e vendeu R$ 97 mil.</p>
    </details>
  </div>
</section>

<!-- 16 · PRÓXIMOS PASSOS -->
<section class="sec" id="passos" data-nome="Próximos passos">
  <div class="wrap pad">
    <p class="eyebrow">Próximos passos</p>
    <h2 class="h2">Do sim <em>à operação rodando.</em></h2>
    <ol class="passos rv">
      <li class="passo"><span class="mono">Hoje</span><b>Você aprova a proposta</b><p>Tiramos as últimas dúvidas aqui mesmo.</p></li>
      <li class="passo-seta" aria-hidden="true"><svg class="seta" viewBox="0 0 60 40" style="--d:.2s"><path pathLength="1" d="M4 24 C 18 8, 38 30, 54 16"/><path pathLength="1" d="M54 16 L 42 14 M54 16 L 49 27"/></svg></li>
      <li class="passo"><span class="mono">Em até <span data-campo="prazo_contrato"></span></span><b>Contrato e acessos</b><p>Meta, Google, site e Instagram.</p></li>
      <li class="passo-seta" aria-hidden="true"><svg class="seta" viewBox="0 0 60 40" style="--d:.5s"><path pathLength="1" d="M4 24 C 18 8, 38 30, 54 16"/><path pathLength="1" d="M54 16 L 42 14 M54 16 L 49 27"/></svg></li>
      <li class="passo"><span class="mono">Semana 1</span><b>Reunião de início</b><p>Com vocês e com quem atende os leads.</p></li>
      <li class="passo-seta" aria-hidden="true"><svg class="seta" viewBox="0 0 60 40" style="--d:.8s"><path pathLength="1" d="M4 24 C 18 8, 38 30, 54 16"/><path pathLength="1" d="M54 16 L 42 14 M54 16 L 49 27"/></svg></li>
      <li class="passo"><span class="mono">Semana 2</span><b>Plano de ação aprovado</b><p>E a operação começa a rodar.</p></li>
    </ol>
    <div class="final">
      <h2 class="final-t"><span class="display">Vamos construir o sistema</span> <span class="serif">da <span data-campo="empresa"></span>?</span></h2>
      <div class="final-acao rv">
        <svg class="selo-giro" viewBox="0 0 200 200" aria-hidden="true"><defs><path id="cg2" d="M100,100 m-76,0 a76,76 0 1,1 152,0 a76,76 0 1,1 -152,0"/></defs><text><textPath href="#cg2" textLength="474" lengthAdjust="spacing">VENDER MAIS HOJE ✦ VALER MAIS AMANHÃ ✦</textPath></text><use class="miolo" href="#estrela" x="80" y="80" width="40" height="40"/></svg>
        <p class="hand final-fala">fazendo sentido, a gente já combina os próximos passos agora, nesta conversa</p>
      </div>
    </div>
  </div>
</section>

<!-- 17 · ENCERRAMENTO -->
<section class="sec dark fim" id="fim" data-nome="Encerramento">
  <div class="wrap pad">
    <svg class="selo-giro" viewBox="0 0 200 200" aria-hidden="true"><defs><path id="cg3" d="M100,100 m-76,0 a76,76 0 1,1 152,0 a76,76 0 1,1 -152,0"/></defs><text><textPath href="#cg3" textLength="474" lengthAdjust="spacing">J.P.GROWTH ✦ ASSESSORIA COMPLETA ✦</textPath></text><use class="miolo" href="#estrela" x="80" y="80" width="40" height="40"/></svg>
    <p class="lema-fim"><span class="display">Vender mais hoje.</span><span class="serif">Valer mais amanhã.</span></p>
  </div>
  <div class="wrap rodape mono">
    <span class="lg"><svg class="lg-icone" viewBox="0 0 159 154" aria-hidden="true"><g>
<path d="M36.79 85.07C35.15 81.62 31.53 79.82 27.92 79.82C26.11 79.82 24.47 80.14 22.83 81.13L21.84 79.82C23.65 75.38 28.74 72.1 33.17 72.1C34.49 72.1 35.8 72.43 36.79 73.08V69.31C35.31 66.02 31.2 64.05 26.93 64.05C22.66 64.05 18.72 65.86 16.92 69.96L15.93 68.65C17.08 57.97 25.46 52.06 33.01 52.06C34.32 52.06 35.64 52.23 36.79 52.55V24.96L32.52 27.59L31.2 25.62C43.69 18.07 56.33 10.02 68.65 0L70.46 2.13C65.53 6.41 64.05 13.63 64.05 21.02V110.36C64.05 117.1 58.79 122.52 53.54 126.13L37.61 137.13C35.97 138.28 34.82 139.43 31.7 141.24C24.8 139.6 17.08 131.71 10.84 131.71C7.06 131.71 3.28 134.18 3.28 138.45C3.28 141.73 5.91 143.87 9.2 143.87C11.5 143.87 13.63 142.22 14.94 139.92L17.24 142.06C15.44 144.69 12.15 147.15 8.54 147.15C4.11 147.15 0 143.04 0 138.28C0 133.52 3.61 130.4 6.08 128.1L10.51 124.16C12.48 122.35 17.74 118.25 25.95 118.25C29.4 118.25 33.17 119.07 36.79 120.54V85.07ZM36.95 14.45C35.31 15.6 33.17 16.42 31.37 17.08C26.44 16.59 21.68 14.45 16.59 14.45C13.3 14.45 8.21 17.08 8.21 21.19C8.21 23.81 10.18 26.28 13.3 26.28C15.44 26.28 17.08 24.96 17.9 22.83L20.36 24.47C19.05 27.1 16.26 29.23 12.97 29.23C9.03 29.23 5.09 25.95 5.09 21.68C5.09 17.41 8.87 14.29 12.15 11.17L18.23 5.42C21.51 2.3 25.78 0 30.87 0C36.29 0 41.22 3.12 46.81 3.12C48.45 3.12 50.91 2.46 52.06 1.81L53.21 0.99L54.52 3.29L36.95 14.45ZM46.81 29.89C46.81 24.47 49.1 19.38 52.39 14.78C48.12 18.07 43.69 20.86 39.42 23.49V121.7C42.37 123.17 45 124.98 46.81 127.28V29.89Z" fill="currentColor"/>
<path d="M75.38 25.95C75.38 18.72 72.09 14.95 67 14.95C64.05 14.95 60.6 16.26 57.15 18.89L55.51 16.75C62.4 9.03 71.11 3.94 79.48 3.94C86.71 3.94 92.46 8.21 92.46 18.89V24.96L122.02 0C131.22 5.09 157.99 11.83 158.81 52.55C158.81 95.42 125.63 107.08 107.57 119.07C104.12 113.81 98.7 110.2 92.62 109.05V123.17C92.62 126.79 92.95 130.73 95.42 135.33L94.59 136.48C86.55 140.25 80.14 145.67 74.56 153.72L72.75 153.23C74.72 146.17 75.38 137.95 75.38 129.74V107.24C69.96 107.24 64.05 110.69 60.93 117.26L59.45 116.11C60.76 107.24 66.84 99.69 75.38 96.4V73.25C73.74 69.8 70.12 67.99 66.67 67.99C64.87 67.99 63.06 68.32 61.58 69.31L60.43 67.99C62.24 63.56 67.33 60.27 71.77 60.27C73.08 60.27 74.39 60.6 75.38 61.26V57.48C74.06 54.52 70.94 53.21 67.33 53.21C63.23 53.21 58.63 55.02 55.67 58.14L54.52 56.82C57.31 48.28 64.38 43.19 71.27 43.19C72.75 43.19 74.06 43.36 75.38 43.69V25.95ZM92.62 28.9V95.42C95.42 95.42 98.04 95.75 100.83 96.4V21.68L92.62 28.9ZM103.46 56.99C111.34 50.42 127.44 43.03 138.61 38.92C132.04 20.69 117.09 16.59 108.55 15.6L103.46 20.04V56.99ZM139.43 41.55C128.26 45.49 111.18 53.54 103.46 59.78V73.25C113.81 66.84 130.72 60.77 141.73 57.97C141.56 51.57 140.74 46.15 139.43 41.55ZM141.73 61.09C130.56 63.56 113.64 69.63 103.46 76.2V96.9C109.7 98.37 113.97 101.82 119.72 107.24C134.34 97.55 141.4 82.12 141.73 61.09Z" fill="currentColor"/>
</g></svg><svg class="lg-texto" viewBox="0 0 358 66" aria-hidden="true"><g>
<path d="M8.93 65.05C6.20333 65.05 4.03333 64.3033 2.42 62.81C0.806667 61.3167 0 59.5067 0 57.38C0 55.5467 0.52 54.1567 1.56 53.21C2.60667 52.27 3.77333 51.8 5.06 51.8C6.4 51.8 7.51667 52.26 8.41 53.18C9.30333 54.0933 9.75 55.1967 9.75 56.49C9.75 57.53 9.52667 58.3867 9.08 59.06C8.63333 59.7267 8.41 60.4067 8.41 61.1C8.41 61.6 8.58333 61.9733 8.93 62.22C9.28 62.47 9.7 62.59 10.2 62.59C11.0933 62.59 11.9233 62.17 12.69 61.33C13.4567 60.4834 14.0767 58.98 14.55 56.82C15.0233 54.6667 15.26 51.63 15.26 47.71V0.890015H25.23V47.71C25.23 50.0433 24.7833 52.25 23.89 54.33C22.9967 56.4167 21.78 58.2633 20.24 59.87C18.7067 61.4833 16.96 62.75 15 63.67C13.04 64.59 11.0167 65.05 8.93 65.05ZM5.58 2.75001V0.890015H31.93V2.75001H5.58Z" fill="currentColor"/>
<path d="M40.6502 57.9C39.1635 57.9 37.8869 57.3667 36.8202 56.3C35.7535 55.2334 35.2202 53.9567 35.2202 52.47C35.2202 50.9834 35.7535 49.7067 36.8202 48.64C37.8869 47.5734 39.1635 47.04 40.6502 47.04C42.1369 47.04 43.4135 47.5734 44.4802 48.64C45.5469 49.7067 46.0835 50.9834 46.0902 52.47C46.0969 53.9567 45.5602 55.2334 44.4802 56.3C43.4002 57.3667 42.1235 57.9 40.6502 57.9Z" fill="currentColor"/>
<path d="M70.9799 31.56V29.32H77.1599C80.5332 29.32 83.2599 28.96 85.3399 28.24C87.4199 27.52 88.9332 26.1567 89.8799 24.15C90.8265 22.1433 91.2999 19.2533 91.2999 15.48C91.2999 11.96 90.9132 9.28001 90.1399 7.44001C89.3732 5.60668 88.2199 4.36668 86.6799 3.72001C85.1465 3.07335 83.2132 2.75001 80.8799 2.75001H55.1299V0.890015H83.8599C87.4265 0.890015 90.6365 1.53668 93.4899 2.83001C96.3432 4.11668 98.6132 5.89001 100.3 8.15001C101.987 10.41 102.83 13.0033 102.83 15.93C102.83 19.0033 101.803 21.72 99.7499 24.08C97.6899 26.4333 94.7499 28.2667 90.9299 29.58C87.1032 30.9 82.5132 31.56 77.1599 31.56H70.9799ZM55.1299 57.01V55.15H82.9599V57.01H55.1299ZM63.3099 55.89V0.890015H73.2899V55.89H63.3099Z" fill="currentColor"/>
<path d="M98.44 57.9C96.9533 57.9 95.6733 57.3667 94.6 56.3C93.5267 55.2334 92.9967 53.9567 93.01 52.47C93.0233 50.9834 93.5533 49.7067 94.6 48.64C95.6467 47.5734 96.9267 47.04 98.44 47.04C99.9533 47.04 101.23 47.5734 102.27 48.64C103.31 49.7067 103.843 50.9834 103.87 52.47C103.897 53.9567 103.363 55.2334 102.27 56.3C101.177 57.3667 99.9 57.9 98.44 57.9Z" fill="currentColor"/>
<g>
<path d="M137.65 57.9C133.784 57.9 130.31 57.1067 127.23 55.52C124.15 53.9333 121.534 51.7867 119.38 49.08C117.227 46.3733 115.564 43.3233 114.39 39.93C113.217 36.5367 112.634 33.05 112.64 29.47C112.64 25.6033 113.3 21.8967 114.62 18.35C115.934 14.8033 117.757 11.65 120.09 8.89C122.417 6.13667 125.157 3.96667 128.31 2.38C131.464 0.793333 134.9 0 138.62 0C140.954 0 143.11 0.373333 145.09 1.12C147.077 1.86 148.987 3.02333 150.82 4.61L154.1 0.89H155.96V17.71H154.1C153.554 14.9367 152.684 12.37 151.49 10.01C150.304 7.65667 148.68 5.77 146.62 4.35C144.56 2.93667 141.894 2.23 138.62 2.23C136.534 2.23 134.637 2.62667 132.93 3.42C131.217 4.21333 129.727 5.61667 128.46 7.63C127.194 9.63667 126.227 12.44 125.56 16.04C124.887 19.64 124.55 24.24 124.55 29.84C124.55 35.0067 124.847 39.2633 125.44 42.61C126.04 45.9567 126.887 48.5733 127.98 50.46C129.067 52.3467 130.417 53.66 132.03 54.4C133.644 55.1467 135.444 55.52 137.43 55.52C140.65 55.52 143.167 54.74 144.98 53.18C146.794 51.6133 147.7 49.4167 147.7 46.59V35.95H158.79V37.73C158.79 41.95 157.81 45.56 155.85 48.56C153.89 51.5667 151.31 53.8733 148.11 55.48C144.91 57.0933 141.424 57.9 137.65 57.9ZM138.54 37.06V35.2H163.1V37.06H138.54Z" fill="currentColor"/>
<path d="M166.23 57.01V55.15H172.11V25.38H166.23V23.52H180.3V29.4H180.59C181.83 26.72 183.257 24.92 184.87 24C186.483 23.08 187.987 22.62 189.38 22.62C190.967 22.62 192.217 23.08 193.13 24C194.05 24.92 194.51 26.2233 194.51 27.91C194.51 29.45 194.05 30.58 193.13 31.3C192.217 32.0133 191.19 32.37 190.05 32.37C189.103 32.37 188.247 32.11 187.48 31.59C186.707 31.07 186.32 30.34 186.32 29.4C186.32 29.1 186.36 28.84 186.44 28.62C186.51 28.39 186.55 28.16 186.55 27.91C186.55 27.66 186.47 27.44 186.32 27.24C186.18 27.04 185.85 26.94 185.36 26.94C184.713 26.94 183.993 27.45 183.2 28.47C182.407 29.4833 181.723 30.8467 181.15 32.56C180.577 34.2733 180.293 36.17 180.3 38.25V55.15H186.99V57.01H166.23Z" fill="currentColor"/>
<path d="M216.32 57.9C213.887 57.9 211.617 57.4033 209.51 56.41C207.403 55.4167 205.543 54.09 203.93 52.43C202.317 50.77 201.063 48.8867 200.17 46.78C199.277 44.6733 198.83 42.5 198.83 40.26C198.83 38.02 199.277 35.85 200.17 33.75C201.063 31.65 202.317 29.7667 203.93 28.1C205.543 26.4333 207.403 25.1033 209.51 24.11C211.617 23.1167 213.887 22.62 216.32 22.62C218.753 22.62 221.023 23.1167 223.13 24.11C225.237 25.1033 227.097 26.4333 228.71 28.1C230.323 29.7667 231.577 31.65 232.47 33.75C233.363 35.85 233.81 38.02 233.81 40.26C233.81 42.5 233.363 44.6733 232.47 46.78C231.577 48.8867 230.323 50.77 228.71 52.43C227.097 54.09 225.237 55.4167 223.13 56.41C221.023 57.4033 218.753 57.9 216.32 57.9ZM216.32 55.67C218.2 55.67 219.713 55.2467 220.86 54.4C222 53.56 222.83 52.0367 223.35 49.83C223.87 47.6233 224.13 44.4333 224.13 40.26C224.13 36.0933 223.87 32.8933 223.35 30.66C222.83 28.4267 222 26.9033 220.86 26.09C219.713 25.27 218.2 24.86 216.32 24.86C214.433 24.86 212.92 25.27 211.78 26.09C210.633 26.9033 209.8 28.4267 209.28 30.66C208.76 32.8933 208.5 36.0933 208.5 40.26C208.5 44.4333 208.76 47.6233 209.28 49.83C209.8 52.0367 210.633 53.56 211.78 54.4C212.92 55.2467 214.433 55.67 216.32 55.67Z" fill="currentColor"/>
<path d="M251.22 57.9L239.76 25.38H235.15V23.52H253.16V25.38H248.92L255.46 45.4H255.76L263.5 23.52H266.33L274.22 45.4H274.52L281.14 25.38H275.63V23.52H289.1V25.38H283.75L272.51 57.9H270.35L262.24 35.05H261.94L253.38 57.9H251.22Z" fill="currentColor"/>
<path d="M301.68 57.98C299.1 57.98 297.177 57.4566 295.91 56.41C294.644 55.3633 294.01 53.8 294.01 51.72V25.45H287.99V23.59C289.724 23.4433 291.347 22.7633 292.86 21.55C294.374 20.33 295.764 18.7766 297.03 16.89C298.297 15.01 299.327 12.9533 300.12 10.72H302.2V23.59H310.76V25.45H302.2V51.2C302.2 53.4333 303.12 54.55 304.96 54.55C306 54.55 306.954 54.19 307.82 53.47C308.687 52.7566 309.594 51.5533 310.54 49.86L312.03 50.68C310.79 53.4133 309.374 55.3133 307.78 56.38C306.194 57.4466 304.16 57.98 301.68 57.98Z" fill="currentColor"/>
<path d="M315.82 2.75001V0.890015H329.89V2.75001H315.82ZM315.82 57.01V55.15H334.72V57.01H315.82ZM321.7 55.89V2.08001H329.89V55.89H321.7ZM343.8 55.89V30.74C343.8 28.8533 343.394 27.45 342.58 26.53C341.767 25.61 340.61 25.1533 339.11 25.16C337.424 25.16 335.874 25.8267 334.46 27.16C333.047 28.5 331.93 30.3233 331.11 32.63C330.297 34.9433 329.89 37.5634 329.89 40.49L328.4 29.7H330.18C331.274 27.66 332.737 25.97 334.57 24.63C336.41 23.29 338.794 22.62 341.72 22.62C344.747 22.62 347.214 23.4033 349.12 24.97C351.034 26.53 351.99 28.4533 351.99 30.74V55.89H343.8ZM338.15 57.01V55.15H357.05V57.01H338.15Z" fill="currentColor"/>
</g>
</g></svg></span>
    <span>© 2026 J.P.Growth · CNPJ 61.512.589/0001-00</span>
    <a href="mailto:contato@pedroselestrim.com">contato@pedroselestrim.com</a>
    <a href="https://pedroselestrim.com/jp/">pedroselestrim.com/jp</a>
  </div>
</section>

</main>

<div class="ctl" aria-label="Navegação da apresentação">
  <button type="button" id="ctlAnt" aria-label="Voltar uma etapa"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 13V3M4 7l4-4 4 4"/></svg></button>
  <span class="mono">↑ ↓</span>
  <button type="button" id="ctlProx" aria-label="Avançar uma etapa"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 3v10M4 9l4 4 4-4"/></svg></button>
</div>
<p class="aviso-exemplo mono">Dados de exemplo</p>

<dialog class="lb" id="lb">
  <button class="lb-x" type="button" id="lbX" aria-label="Fechar">×</button>
  <div class="lb-in"><div class="lb-img"><img id="lbImg" alt=""></div><p class="mono" id="lbCap"></p></div>
</dialog>

<script nonce="<?= h($nonce) ?>">
/* =====================================================================
   DADOS DO CLIENTE
   Vêm da ficha do lead no CRM (bloco "Proposta"). Nada do cliente fica
   no código do site: a página só existe para quem tem o link.
   ===================================================================== */
const PROPOSTA = <?= $propostaJson ?>;

/* Cases reais (os mesmos do site), com o print de cada um */
const CASOS = {
  'educacao-pos': { seg: 'Educação · Pós-graduação', tit: 'R$ 1,4 milhão faturado em 25 dias.', stats: [['Faturamento','R$ 1,40 mi'],['ROAS','5,10'],['Vendas','201']], nota: 'Dashboard de setembro de 2025 cruzando investimento em mídia e vendas fechadas.', img: '../prints/dash-faturamento-set25.jpg' },
  'educacao-online': { seg: 'Educação · Vendas online', tit: '529 vendas e R$ 870 mil em receita.', stats: [['Vendas','529'],['ROAS','18,26'],['Investido','R$ 47,7 mil']], nota: 'Campanhas de vendas no Meta Ads: cada R$ 1 investido voltou R$ 18,26 em vendas.', img: '../prints/meta-vendas-perpetuo.jpg' },
  'ecommerce': { seg: 'E-commerce · Livros', tit: 'R$ 5,8 mil viraram R$ 97 mil em vendas.', stats: [['Vendas','511'],['ROAS','16,62'],['Custo/venda','R$ 11,45']], nota: 'Novembro de 2025, Meta Ads: verba enxuta e operação ajustada toda semana.', img: '../prints/meta-ecommerce-livros.jpg' },
  'industria': { seg: 'Indústria · B2B', tit: '2.750 conversões a R$ 31,69 cada.', stats: [['Conversões','2.750'],['Taxa de conv.','6,31%'],['Investido','R$ 87,1 mil']], nota: 'Campanha de pesquisa no Google para geração de leads B2B, otimizada desde julho de 2023.', img: '../prints/google-ads-pesquisa.jpg' },
  'demanda': { seg: 'Google Ads · Geração de demanda', tit: '9.234 conversões a R$ 4,71 cada.', stats: [['Conversões','9.234'],['Custo/conv.','R$ 4,71'],['Investido','R$ 43,5 mil']], nota: 'Conta com várias campanhas ativas, acompanhada no dia a dia desde dezembro de 2024.', img: '../prints/google-ads-conversoes-foto.jpg' },
  'captacao': { seg: 'Educação · Captação de leads', tit: '1.606 leads em 25 dias.', stats: [['Leads','1.606'],['Custo/lead','R$ 48,59'],['Conversão','19,16%']], nota: 'Captação no Meta Ads com 19% de conversão na página: de cada 5 visitas, quase 1 virou contato.', img: '../prints/meta-leads-jan26.jpg' }
};

const FRENTES = [
  ['Tráfego pago', 'Meta Ads, Google Ads e outras mídias que fizerem sentido para o negócio.'],
  ['Copy', 'Anúncios, ofertas e páginas com mensagem que vira ação.'],
  ['Roteiros', 'Vídeos pensados para vender, por público e etapa do funil.'],
  ['Criativos', 'Estáticos, carrosséis e vídeos que param o scroll e comunicam valor.'],
  ['Landing pages', 'Páginas estruturadas para transformar o clique em contato.'],
  ['Identidade', 'Posicionamento e uma marca que transmite o valor que a empresa tem.'],
  ['Dados', 'Pixel e conversões configurados para decidir com número.'],
  ['Relatório', 'Toda segunda, com número e próximo passo. E um dashboard aberto.']
];

(() => {
  'use strict';
  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => [...c.querySelectorAll(s)];
  const reduz = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const clamp = (v, a = 0, b = 1) => Math.min(b, Math.max(a, v));
  const lerp = (a, b, t) => a + (b - a) * t;
  const ss = (a, b, v) => { const t = clamp((v - a) / (b - a)); return t * t * (3 - 2 * t); };
  const reais = n => 'R$ ' + Math.round(n).toLocaleString('pt-BR');
  const NS = 'http://www.w3.org/2000/svg';
  const mk = (tag, attrs = {}, pai) => { const e = document.createElementNS(NS, tag); for (const k in attrs) e.setAttribute(k, attrs[k]); if (pai) pai.appendChild(e); return e; };
  const el = (tag, cls, txt) => { const e = document.createElement(tag); if (cls) e.className = cls; if (txt != null) e.textContent = txt; return e; };

  /* ---------- 1. Preenche os dados do cliente (sempre como texto) ---------- */
  const tira = s => { const n = $(s); if (n) n.remove(); };
  if (PROPOSTA.generica) {
    /* Versão genérica: sem a seção do cliente, sem "você comentou" e sem nomes */
    tira('#cenario'); tira('.prob-voce'); tira('.d-hand');
    $$('[data-geral]').forEach(n => { n.textContent = n.dataset.geral; });
  }
  /* Ficha "O seu cenário": linha vazia some; ficha sem linhas e sem notas tira a seção */
  $$('.ficha dd[data-campo]').forEach(dd => { const v = PROPOSTA[dd.dataset.campo]; if (v == null || v === '') dd.parentElement.remove(); });
  if ($('.ficha') && !$$('.ficha dl > div').length) tira('.ficha');
  $$('[data-campo]').forEach(n => { const v = PROPOSTA[n.dataset.campo]; if (v != null && v !== '') n.textContent = v; });
  const obs = (PROPOSTA.obs || []).filter(Boolean);
  $$('[data-obs]').forEach(n => { const v = obs[+n.dataset.obs]; if (v) n.textContent = v; else n.closest('.nota').remove(); });
  if (!obs.length) { tira('.notas'); if ($('.cen-grid')) $('.cen-grid').classList.add('sem-notas'); }
  if ($('#cenario') && !$('.ficha') && !$('.nota')) tira('#cenario');
  if (!PROPOSTA.desafio_curto) tira('.prob-voce');
  if (PROPOSTA.exemplo) document.documentElement.classList.add('is-exemplo');

  /* ---------- 2. Revelação no scroll ---------- */
  const io = new IntersectionObserver(es => es.forEach(e => {
    if (e.isIntersecting) { e.target.classList.add('on'); io.unobserve(e.target); }
  }), { rootMargin: '0px 0px -12% 0px' });
  $$('.rv').forEach(n => reduz ? n.classList.add('on') : io.observe(n));

  /* Contadores dos números */
  const ioConta = new IntersectionObserver(es => es.forEach(e => {
    if (!e.isIntersecting) return;
    ioConta.unobserve(e.target);
    const n = e.target, para = +n.dataset.para, pre = n.dataset.pre || '', suf = n.dataset.suf || '';
    if (reduz) return;
    const t0 = performance.now(), dur = 1400;
    const passo = t => { const k = clamp((t - t0) / dur); const v = Math.round(para * (1 - Math.pow(1 - k, 3))); n.textContent = pre + v + suf; if (k < 1) requestAnimationFrame(passo); };
    requestAnimationFrame(passo);
  }), { threshold: .6 });
  $$('.conta').forEach(n => ioConta.observe(n));

  /* ---------- 3. Registro de cenas ligadas ao scroll ---------- */
  const cenas = [];
  const cena = (alvo, fn, modo) => { if (alvo) cenas.push({ alvo, fn, modo }); };
  const progresso = c => {
    const r = c.alvo.getBoundingClientRect(), vh = innerHeight;
    if (c.modo === 'fixo') return clamp(-r.top / Math.max(1, r.height - vh));
    if (c.modo === 'passagem') return clamp((vh - r.top) / (r.height + vh));
    return clamp((vh * .58 - r.top) / Math.max(1, r.height)); // 'desenho'
  };

  /* Capa: estrela gira com o scroll */
  const estrelaHero = $('#estrelaHero');

  /* Manifesto: palavras acendem conforme a leitura */
  const scrub = $('#scrub'); const palavras = [];
  if (scrub) {
    const quebra = no => {
      [...no.childNodes].forEach(f => {
        if (f.nodeType === 3) {
          const partes = f.textContent.split(/(\s+)/); const frag = document.createDocumentFragment();
          partes.forEach(p => { if (!p) return; if (/^\s+$/.test(p)) frag.appendChild(document.createTextNode(p)); else { const s = el('span', 'w', p); palavras.push(s); frag.appendChild(s); } });
          f.replaceWith(frag);
        } else if (f.nodeType === 1 && f.tagName !== 'svg') quebra(f);
      });
    };
    quebra(scrub);
    let acesas = -1; const sub = $('.sublinha', scrub);
    cena(scrub, p => {
      const n = Math.round(ss(0, .82, p) * palavras.length);
      if (n === acesas) return; acesas = n;
      palavras.forEach((w, i) => w.classList.toggle('on', i < n));
      sub.classList.toggle('on', n >= palavras.length);
    }, 'desenho');
  }

  /* Visão: as duas linhas do lema deslizam em sentidos opostos */
  const lema = $('#lema');
  if (lema) { const [l1, l2] = lema.children; cena(lema, p => { l1.style.transform = `translateX(${lerp(4, -6, p)}%)`; l2.style.transform = `translateX(${lerp(-5, 4, p)}%)`; }, 'passagem'); }

  /* ---------- 4. O problema: leads vazando entre peças desconectadas ---------- */
  (() => {
    const svg = $('#vazaSvg'); if (!svg) return;
    const NOMES = ['Anúncio', 'Criativo', 'Oferta', 'Página', 'Comercial', 'Venda'];
    const FUGA = ['não parou o scroll', 'não entendeu a oferta', 'não converteu', 'ninguém respondeu'];
    const CHANCE = [.38, .33, .38, .44];
    const eE = $('#vzE'), eV = $('#vzV'), eP = $('#vzP');
    let V = null, W, H, nos, vaos, gPontos, pontos = [], espera = 0, entraram = 0, venderam = 0, rodando = false, ultimo = 0, rectFim;
    const xy = (s, c) => V ? [c, s] : [s, c];
    function montar() {
      V = innerWidth < 760; W = V ? 340 : 1100; H = V ? 720 : 250;
      svg.setAttribute('viewBox', `0 0 ${W} ${H}`); svg.textContent = '';
      pontos = []; entraram = 0; venderam = 0;
      const N = NOMES.length, meio = V ? 22 : 56, eixo = V ? 104 : 150;
      nos = NOMES.map((n, i) => ({ s: V ? 34 + i * (H - 68) / (N - 1) : 66 + i * (W - 132) / (N - 1), c: eixo }));
      const gl = mk('g', {}, svg); gPontos = mk('g', {}, svg); const gn = mk('g', {}, svg); const gt = mk('g', {}, svg);
      vaos = [];
      for (let i = 0; i < N - 1; i++) {
        const a = nos[i].s + meio, b = nos[i + 1].s - meio, c = nos[i].c;
        if (i === 0) { const [x1, y1] = xy(a, c), [x2, y2] = xy(b, c); mk('line', { x1, y1, x2, y2, class: 'vz-l' }, gl); continue; }
        const m = (a + b) / 2, g = V ? 13 : 17; vaos.push({ s: m, k: i - 1 });
        let [x1, y1] = xy(a, c), [x2, y2] = xy(m - g, c); mk('line', { x1, y1, x2, y2, class: 'vz-l' }, gl);
        [x1, y1] = xy(m + g, c); [x2, y2] = xy(b, c); mk('line', { x1, y1, x2, y2, class: 'vz-l' }, gl);
        // rachaduras nas pontas
        const [ra, rb] = [xy(m - g, c), xy(m + g, c)];
        const d = V ? `M${ra[0] - 6} ${ra[1] - 3} L${ra[0]} ${ra[1]} L${ra[0] + 6} ${ra[1] - 3} M${rb[0] - 6} ${rb[1] + 3} L${rb[0]} ${rb[1]} L${rb[0] + 6} ${rb[1] + 3}`
                    : `M${ra[0] - 3} ${ra[1] - 6} L${ra[0]} ${ra[1]} L${ra[0] - 3} ${ra[1] + 6} M${rb[0] + 3} ${rb[1] - 6} L${rb[0]} ${rb[1]} L${rb[0] + 3} ${rb[1] + 6}`;
        mk('path', { d, class: 'vz-crack' }, gl);
        const [tx, ty] = V ? [c + 44, m + 6] : [m, c - 46];
        const t = mk('text', { x: tx, y: ty, class: 'vz-fuga', 'text-anchor': V ? 'start' : 'middle' }, gt); t.textContent = FUGA[i - 1];
      }
      nos.forEach((n, i) => {
        const [x, y] = xy(n.s, n.c), w = V ? 148 : 112, h = 44;
        const g = mk('g', { class: 'vz-no' + (i === 0 ? ' ini' : '') + (i === N - 1 ? ' fim' : ''), transform: `translate(${x},${y})` }, gn);
        const r = mk('rect', { x: -w / 2, y: -h / 2, width: w, height: h, rx: h / 2 }, g);
        const t = mk('text', { x: 0, y: 4.5, 'text-anchor': 'middle' }, g); t.textContent = NOMES[i];
        if (i === N - 1) rectFim = r;
      });
      atualizaPlacar();
    }
    function atualizaPlacar() { eE.textContent = entraram; eV.textContent = venderam; eP.textContent = entraram > 10 ? Math.round(venderam / entraram * 100) + '%' : '0%'; }
    function nasce() {
      const p = { s: nos[0].s, c: nos[0].c + (Math.random() - .5) * 12, vs: (V ? 118 : 170) * (.85 + Math.random() * .3), vc: 0, vao: 0, cai: false, a: 1 };
      p.el = mk('circle', { r: V ? 4 : 4.6, class: 'vz-d' }, gPontos); pontos.push(p); entraram++;
    }
    function passo(dt) {
      espera -= dt; if (espera <= 0) { nasce(); espera = .2 + Math.random() * .16; }
      const fimS = nos[nos.length - 1].s;
      for (let i = pontos.length - 1; i >= 0; i--) {
        const p = pontos[i];
        if (!p.cai) {
          p.s += p.vs * dt;
          const v = vaos[p.vao];
          if (v && p.s >= v.s - 3) { if (Math.random() < CHANCE[v.k]) { p.cai = true; p.el.classList.add('cai'); } p.vao++; }
          if (p.s >= fimS) { venderam++; p.el.remove(); pontos.splice(i, 1); rectFim.animate([{ transform: 'scale(1.14)' }, { transform: 'scale(1)' }], { duration: 380, easing: 'ease-out' }); continue; }
        } else {
          p.vc += (V ? -1 : 1) * 560 * dt; p.c += p.vc * dt; p.s += p.vs * .22 * dt; p.a -= dt * 1.15;
          if (p.a <= 0) { p.el.remove(); pontos.splice(i, 1); continue; }
          p.el.setAttribute('opacity', p.a.toFixed(2));
        }
        const [x, y] = xy(p.s, p.c); p.el.setAttribute('cx', x.toFixed(1)); p.el.setAttribute('cy', y.toFixed(1));
      }
      atualizaPlacar();
    }
    function laco(t) { if (!rodando) return; const dt = Math.min(.05, (t - ultimo) / 1000 || 0); ultimo = t; passo(dt); requestAnimationFrame(laco); }
    montar();
    new IntersectionObserver(([e]) => {
      if (e.isIntersecting && !reduz) { if (!rodando) { rodando = true; ultimo = performance.now(); requestAnimationFrame(laco); } }
      else rodando = false;
    }).observe(svg);
    addEventListener('resize', () => { if ((innerWidth < 760) !== V) montar(); });
  })();

  /* ---------- 5. Em pedaços: 5 fornecedores viram 1 time ---------- */
  (() => {
    const sec = $('#pedacos'), palco = $('#palco'); if (!sec) return;
    const forns = $$('.forn', palco), svg = $('#palcoSvg'), emar = $('#emaranhado'), linha = $('#palcoLinha');
    const t1 = $('#pedT1'), t2 = $('#pedT2'), n1 = $('#pedN1'), n2 = $('#pedN2'), pills = $('#pedPills');
    const INI = { d: [[.14, .26, -9], [.44, .16, 6], [.76, .3, -6], [.27, .74, 7], [.64, .72, -8]], m: [[.34, .1, -7], [.64, .3, 6], [.36, .5, -5], [.62, .7, 8], [.36, .9, -6]] };
    const FIM = { d: [[.1, .5, 0], [.3, .5, 0], [.5, .5, 0], [.7, .5, 0], [.9, .5, 0]], m: [[.5, .1, 0], [.5, .3, 0], [.5, .5, 0], [.5, .7, 0], [.5, .9, 0]] };
    const PARES = [[0, 2], [1, 3], [2, 4], [0, 4], [1, 4], [0, 3], [2, 3]];
    const fios = PARES.map(() => mk('line', {}, emar));
    let PW = 0, PH = 0, modo = 'd', unido = null;
    const medir = () => {
      PW = palco.clientWidth; PH = palco.clientHeight; modo = innerWidth < 760 ? 'm' : 'd';
      svg.setAttribute('viewBox', `0 0 ${PW} ${PH}`);
      linha.setAttribute('d', 'M' + FIM[modo].map(([x, y]) => `${x * PW} ${y * PH}`).join(' L'));
    };
    medir(); addEventListener('resize', medir);
    cena(sec, p => {
      const t = ss(.1, .6, p), t2v = ss(.62, .9, p), ini = INI[modo], fim = FIM[modo], pts = [];
      forns.forEach((f, i) => {
        const x = lerp(ini[i][0], fim[i][0], t) * PW, y = lerp(ini[i][1], fim[i][1], t) * PH, r = lerp(ini[i][2], 0, t);
        f.style.transform = `translate(${x}px,${y}px) translate(-50%,-50%) rotate(${r}deg)`; pts.push([x, y]);
      });
      fios.forEach((l, k) => { const [a, b] = PARES[k]; l.setAttribute('x1', pts[a][0]); l.setAttribute('y1', pts[a][1]); l.setAttribute('x2', pts[b][0]); l.setAttribute('y2', pts[b][1]); });
      emar.style.opacity = 1 - ss(.08, .42, p);
      linha.style.strokeDashoffset = 1.02 * (1 - t2v);
      t1.style.opacity = 1 - ss(.5, .6, p); t2.style.opacity = ss(.6, .7, p);
      n1.style.opacity = 1 - ss(.08, .3, p); n2.style.opacity = ss(.8, .95, p);
      pills.style.opacity = ss(.72, .9, p); pills.style.transform = `translateY(${(1 - ss(.72, .9, p)) * 14}px)`;
      const u = t > .92;
      if (u !== unido) { unido = u; $('#pedEb').textContent = u ? 'Como a gente trabalha' : 'Como costuma ser'; forns.forEach(f => { f.classList.toggle('unido', u); $$('[data-a]', f).forEach(s => { s.textContent = u ? s.dataset.b : s.dataset.a; }); }); }
    }, 'fixo');
  })();

  /* ---------- 6. O sistema: o caminho do lead se desenha com o scroll ---------- */
  (() => {
    const trilha = $('#trilha'); if (!trilha) return;
    const svg = $('#trilhaSvg'), base = $('#trilhaBase'), linha = $('#trilhaLinha'), volta = $('#trilhaVolta'), leadG = $('#leadG'), leadTxt = $('#leadTxt'), nota = $('#voltaNota');
    const nos = $$('.no', trilha);
    let L = 0, marcas = [], fechado = false, venda = null;
    const medir = () => {
      const R = trilha.getBoundingClientRect(), W = R.width, H = R.height;
      svg.setAttribute('width', W); svg.setAttribute('height', H); svg.setAttribute('viewBox', `0 0 ${W} ${H}`);
      const P = nos.map(n => { const r = $('.no-pino', n).getBoundingClientRect(); return [r.left + r.width / 2 - R.left, r.top + r.height / 2 - R.top]; });
      let d = `M ${P[0][0]} 0 L ${P[0][0]} ${P[0][1]}`;
      for (let i = 1; i < P.length; i++) { const [x0, y0] = P[i - 1], [x1, y1] = P[i], k = (y1 - y0) * .55; d += ` C ${x0} ${y0 + k}, ${x1} ${y1 - k}, ${x1} ${y1}`; }
      base.setAttribute('d', d); linha.setAttribute('d', d);
      L = linha.getTotalLength(); linha.style.strokeDasharray = L;
      marcas = P.map(([, y]) => { let lo = 0, hi = L; for (let k = 0; k < 24; k++) { const m = (lo + hi) / 2; if (linha.getPointAtLength(m).y < y) lo = m; else hi = m; } return lo; });
      if (innerWidth > 900) {
        const f = P[0], l = P[P.length - 1], x = 20, yb = H - 26;
        volta.setAttribute('d', `M ${l[0]} ${l[1] + 14} L ${l[0]} ${yb - 30} Q ${l[0]} ${yb}, ${l[0] - 30} ${yb} L ${x + 22} ${yb} Q ${x} ${yb}, ${x} ${yb - 22} L ${x} 44 Q ${x} 22, ${x + 22} 22 L ${f[0] - 16} 22`);
        volta.style.display = '';
      } else volta.style.display = 'none';
    };
    medir(); addEventListener('resize', medir);
    if (document.fonts) document.fonts.ready.then(() => { medir(); tick(); });
    cena(trilha, p => {
      const comp = L * p;
      linha.style.strokeDashoffset = L - comp;
      const pt = linha.getPointAtLength(Math.max(.1, comp));
      leadG.setAttribute('transform', `translate(${pt.x},${pt.y})`);
      leadG.style.opacity = p > 0 && p < 1 ? 1 : (p >= 1 ? 1 : 0);
      nos.forEach((n, i) => n.classList.toggle('on', comp >= marcas[i] - 4));
      const v = comp >= marcas[5] - 4;
      if (v !== venda) { venda = v; leadG.classList.toggle('venda', v); leadTxt.textContent = v ? 'venda' : 'lead'; }
      const f = p > .985;
      if (f !== fechado) { fechado = f; volta.classList.toggle('on', f); nota.classList.toggle('on', f); }
    }, 'desenho');
  })();

  /* ---------- 7. As 8 frentes: a estrela se monta ponta por ponta ---------- */
  (() => {
    const sec = $('#frentes'), box = $('#estBox'); if (!sec) return;
    const pets = $$('.pet', box), painel = $('#estPainel'), eN = $('#estN'), eT = $('#estT'), eD = $('#estD'), lista = $('#estLista');
    const labs = FRENTES.map((f, i) => {
      const s = el('span', 'pet-lab mono'); const b = el('b', null, String(i + 1).padStart(2, '0')); const nm = el('span', 'nome', ' ' + f[0]);
      s.append(b, nm); const a = (-90 + i * 45) * Math.PI / 180;
      s.style.left = (50 + Math.cos(a) * 45.5) + '%'; s.style.top = (50 + Math.sin(a) * 45.5) + '%';
      box.appendChild(s); return s;
    });
    const itens = FRENTES.map((f, i) => { const li = el('li', null, String(i + 1).padStart(2, '0') + ' ' + f[0]); lista.appendChild(li); return li; });
    let atual = -1, tTroca;
    cena(sec, p => {
      const i = Math.round(clamp(p * 1.04) * 7);
      if (i === atual) return;
      const primeira = atual === -1; atual = i;
      [pets, labs, itens].forEach(grupo => grupo.forEach((n, k) => { n.classList.toggle('on', k <= i); n.classList.toggle('ativo', k === i); }));
      const preenche = () => { eN.textContent = String(i + 1).padStart(2, '0') + ' / 08'; eT.textContent = FRENTES[i][0]; eD.textContent = FRENTES[i][1]; painel.classList.remove('troca'); };
      clearTimeout(tTroca);
      if (primeira || reduz) preenche(); else { painel.classList.add('troca'); tTroca = setTimeout(preenche, 180); }
    }, 'fixo');
  })();

  /* ---------- 8. Prova: case em destaque + outros cases ---------- */
  const lb = $('#lb'), lbImg = $('#lbImg'), lbCap = $('#lbCap');
  const abrePrint = (src, cap) => { lbImg.src = src; lbImg.alt = cap; lbCap.textContent = cap; if (lb.showModal) lb.showModal(); };
  $('#lbX').addEventListener('click', () => lb.close());
  lb.addEventListener('click', e => { if (e.target === lb) lb.close(); });
  (() => {
    const chave = CASOS[PROPOSTA.caso] ? PROPOSTA.caso : 'educacao-pos', c = CASOS[chave];
    $('#dImg').src = c.img; $('#dImg').alt = 'Print real: ' + c.tit;
    $('#dCap').textContent = c.seg; $('#dSeg').textContent = c.seg; $('#dTit').textContent = c.tit; $('#dNota').textContent = c.nota;
    const dl = $('#dStats'); c.stats.forEach(([k, v]) => { const d = el('div'); d.append(el('dt', null, k), el('dd', null, v)); dl.appendChild(d); });
    $('#dBtn').addEventListener('click', () => abrePrint(c.img, 'Print real · ' + c.seg));
    const obras = $('#obras'); let n = 1;
    Object.entries(CASOS).forEach(([k, o]) => {
      if (k === chave) return;
      const b = el('button', 'obra rv'); b.type = 'button'; b.style.setProperty('--d', (n * .06) + 's');
      const th = el('span', 'thumb'); const im = el('img'); im.src = o.img; im.alt = ''; im.loading = 'lazy'; th.appendChild(im);
      const meta = el('span', 'mono'); meta.append(el('span', null, String(n).padStart(3, '0')), el('span', null, o.seg.split(' · ')[0]));
      b.append(th, meta, el('b', null, o.tit));
      b.addEventListener('click', () => abrePrint(o.img, 'Print real · ' + o.seg));
      obras.appendChild(b); reduz ? b.classList.add('on') : io.observe(b); n++;
    });
    $$('[data-print]').forEach(b => b.addEventListener('click', () => abrePrint(b.dataset.print, b.dataset.cap)));
  })();

  /* ---------- 9. Branding: mural de marcas que corre com o scroll ---------- */
  (() => {
    const f1 = $('#fila1'), f2 = $('#fila2'); if (!f1) return;
    const A = [['dolce',1],['telora',1],['pistacchio',1],['green',1],['soberana',1],['querubini',1],['vitanova',1],['aracruz',1],['liso',1],['360',1],['moncao',1],['janedson',1],['anderson',1],['jp',1]];
    const B = [['pistacchio',3],['360',2],['telora',4],['dolce',3],['green',4],['jp',3],['aracruz',2],['vitanova',3],['querubini',2],['soberana',3],['janedson',2],['moncao',3],['liso',2],['anderson',2]];
    const tile = ([m, i]) => { const t = el('div', 'tile'); const bg = el('img', 'bg'); bg.src = `../marcas/${m}/fundo-${i}.webp`; bg.alt = ''; bg.loading = 'lazy'; const lg = el('img', 'lg'); lg.src = `../marcas/${m}/logo-${i}.svg`; lg.alt = ''; lg.loading = 'lazy'; t.append(bg, lg); return t; };
    [...A, ...A.slice(0, 6)].forEach(x => f1.appendChild(tile(x)));
    [...B, ...B.slice(0, 6)].forEach(x => f2.appendChild(tile(x)));
    cena($('#marca'), p => { f1.style.transform = `translateX(${lerp(-4, -34, p)}%)`; f2.style.transform = `translateX(${lerp(-34, -6, p)}%)`; }, 'passagem');
  })();

  /* ---------- 10. Os primeiros 90 dias ---------- */
  (() => {
    const fases = $('#fases'), grade = $('#semanas'); if (!fases) return;
    const fase = s => s <= 2 ? 1 : s <= 4 ? 2 : s <= 8 ? 3 : 4;
    const sems = Array.from({ length: 12 }, (_, k) => { const s = el('span', 'sem'); s.dataset.f = fase(k + 1); s.append(el('span', null, 'S' + (k + 1)), el('i')); grade.appendChild(s); return s; });
    const itens = $$('.fase', fases), barra = $('#fasesLinha');
    cena(fases, p => {
      barra.style.transform = `scaleY(${p})`;
      const sem = Math.floor(clamp(p * 1.06) * 12.001);
      sems.forEach((s, k) => s.classList.toggle('on', k < sem));
      itens.forEach(it => it.classList.toggle('on', sem > +it.dataset.de || (sem >= 12 && +it.dataset.de === 12) || (+it.dataset.de === 0 && p > .01)));
    }, 'desenho');
  })();

  /* ---------- 11. Investimento: controle de verba ---------- */
  (() => {
    const r = $('#verba'); if (!r) return;
    const out = $('#verbaOut'), nome = $('#nivelNome'), desc = $('#nivelDesc'), med = $$('#medidor i'), tot = $('#contaTotal'), qb = $('#contaQuebra');
    const A = PROPOSTA.assessoria, VMIN = PROPOSTA.verba_minima;
    r.min = VMIN;
    $('#vAssessoria').firstChild.textContent = reais(A);
    $('#vVerbaMin').firstChild.textContent = reais(VMIN);
    $('#vTotal').firstChild.textContent = 'R$ ' + ((A + VMIN) / 1000).toLocaleString('pt-BR') + ' mil';
    const NIVEIS = [
      [10000, 'Escalar', 'Mais públicos, mais praças e escala no que já provou que vende.', 3],
      [6000, 'Acelerar', 'Meta e Google trabalhando juntos. Mais testes por mês e remarketing para quem já visitou.', 2],
      [0, 'Começar', 'Um canal principal (Meta ou Google, conforme o negócio). Foco em validar oferta e criativo.', 1]
    ];
    const atualiza = () => {
      const v = +r.value, max = +r.max, pct = (v - +r.min) / (max - +r.min) * 100;
      r.style.setProperty('--pct', pct + '%');
      out.textContent = reais(v) + (v >= max ? '+' : '');
      const [, n, d, k] = NIVEIS.find(x => v >= x[0]);
      if (nome.textContent !== n) { nome.textContent = n; desc.textContent = d; }
      med.forEach((m, i) => m.classList.toggle('on', i < k));
      tot.textContent = reais(A + v) + (v >= max ? '+' : '');
      qb.textContent = `${reais(A)} time + ${reais(v)} anúncios`;
    };
    r.addEventListener('input', atualiza); atualiza();
  })();

  /* ---------- 12. Laço principal: cenas, barra de progresso, seção atual ---------- */
  const secoes = $$('main [data-nome]'), prog = $('#prog'), sN = $('#secNum'), sNome = $('#secNome');
  const links = $$('.hdr-nav a');
  let pedido = false, secAtual = -1, secMax = 0;
  function tick() {
    pedido = false;
    const y = scrollY, vh = innerHeight, H = document.documentElement.scrollHeight - vh;
    prog.style.transform = `scaleX(${H > 0 ? y / H : 0})`;
    if (estrelaHero && !reduz) estrelaHero.style.transform = `rotate(${y * .07}deg)`;
    cenas.forEach(c => c.fn(reduz ? 1 : progresso(c)));
    let i = 0; secoes.forEach((s, k) => { if (s.getBoundingClientRect().top <= vh * .4) i = k; });
    if (i > secMax) secMax = i;
    if (i !== secAtual) {
      secAtual = i; sN.textContent = String(i).padStart(2, '0'); sNome.textContent = secoes[i].dataset.nome;
      links.forEach(a => a.classList.toggle('ativo', a.getAttribute('href') === '#' + secoes[i].id));
    }
  }
  const pede = () => { if (!pedido) { pedido = true; requestAnimationFrame(tick); } };
  addEventListener('scroll', pede, { passive: true });
  addEventListener('resize', pede);
  tick();

  /* ---------- 13. Modo apresentação: ↑ ↓ pulam de etapa em etapa ---------- */
  const hdrH = () => $('.hdr').offsetHeight;
  function paradas() {
    const ys = [], vh = innerHeight, h0 = hdrH();
    secoes.forEach(s => {
      const topo = s.getBoundingClientRect().top + scrollY, n = +s.dataset.paradas || 0, alt = s.offsetHeight;
      if (n > 1) { const curso = alt - vh; for (let k = 0; k < n; k++) ys.push(topo + curso * (k / (n - 1))); return; }
      ys.push(Math.max(0, topo - h0));
      // seções mais altas que a tela ganham paradas intermediárias (uma "tela" por vez)
      if (alt > vh * 1.15 && !s.querySelector('[data-parada]')) for (let y = topo - h0 + vh * .8; y < topo + alt - vh * .6; y += vh * .8) ys.push(y);
    });
    $$('[data-parada]').forEach(n => { if (n.closest('.cena')) return; const r = n.getBoundingClientRect(); ys.push(r.top + scrollY + r.height / 2 - vh * .55); });
    const ord = ys.map(Math.round).sort((a, b) => a - b), fim = [];
    ord.forEach(v => { if (!fim.length || v - fim[fim.length - 1] > 120) fim.push(v); });
    return fim;
  }
  function vai(dir) {
    const ys = paradas(), y = scrollY;
    const alvo = dir > 0 ? ys.find(v => v > y + 6) : [...ys].reverse().find(v => v < y - 6);
    if (alvo != null) scrollTo({ top: alvo, behavior: reduz ? 'auto' : 'smooth' });
  }
  addEventListener('keydown', e => {
    if (e.metaKey || e.ctrlKey || e.altKey) return;
    const t = e.target; if (t && (t.closest('input,textarea,select,[contenteditable]') || (lb.open))) return;
    const prox = ['ArrowDown', 'ArrowRight', 'PageDown'].includes(e.key) || (e.key === ' ' && !e.shiftKey);
    const ant = ['ArrowUp', 'ArrowLeft', 'PageUp'].includes(e.key) || (e.key === ' ' && e.shiftKey);
    if (prox || ant) { e.preventDefault(); vai(prox ? 1 : -1); }
  });
  $('#ctlProx').addEventListener('click', () => vai(1));
  $('#ctlAnt').addEventListener('click', () => vai(-1));

  /* ---------- 14. Rastreio de leitura: avisa o CRM quando o cliente abre,
     até que seção chegou e quanto tempo ficou com a página na tela ---------- */
  (() => {
    const R = PROPOSTA.rastreio;
    if (!R || !navigator.sendBeacon) return;
    const ids = secoes.map(s => s.id);
    let visivelDesde = document.visibilityState === 'visible' ? Date.now() : 0, acumulado = 0, abriu = false;
    const envia = ev => {
      if (visivelDesde) { acumulado += Date.now() - visivelDesde; visivelDesde = document.visibilityState === 'visible' ? Date.now() : 0; }
      const corpo = JSON.stringify({ t: R.t, ev, secao: ids[secMax] || 'inicio', seg: Math.round(acumulado / 1000) });
      acumulado = 0;
      try { navigator.sendBeacon(R.url, new Blob([corpo], { type: 'text/plain' })); } catch (e) { /* sem rede: tudo bem */ }
    };
    const abre = () => {
      if (abriu || document.visibilityState !== 'visible') return;
      abriu = true;
      let chave = 'jp-prop-' + R.t, ja = false;
      try { ja = sessionStorage.getItem(chave) === '1'; sessionStorage.setItem(chave, '1'); } catch (e) { /* aba anônima */ }
      if (!ja) envia('abriu');
    };
    setTimeout(abre, 1200);
    document.addEventListener('visibilitychange', () => {
      if (document.visibilityState === 'visible') { if (!visivelDesde) visivelDesde = Date.now(); abre(); }
      else if (abriu) envia('leu');
    });
    addEventListener('pagehide', () => { if (abriu) envia('leu'); });
  })();
})();
</script>
</body>
</html>
