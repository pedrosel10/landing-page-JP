/* CRM J.P.Growth: comportamentos do painel */

/* Pede confirmação antes de ações destrutivas (ex.: excluir lead) */
document.addEventListener('submit', function (evento) {
  var form = evento.target;
  var pergunta = form.getAttribute('data-confirmar');
  if (pergunta && !window.confirm(pergunta)) {
    evento.preventDefault();
  }
});

/* Leads novos viram "vistos" sozinhos quando alguém mexe na lista (mouse, rolagem,
   toque ou teclado), com a aba visível. O destaque continua até recarregar a página,
   para dar tempo de ver quais chegaram. */
(function () {
  var alvo = document.querySelector('[data-auto-visto]');
  if (!alvo || !window.fetch || !window.FormData) {
    return;
  }
  var ids = alvo.getAttribute('data-ids');
  var eventos = ['pointermove', 'pointerdown', 'touchstart', 'wheel', 'scroll', 'keydown'];
  var inicio = Date.now();
  var timer = null;
  var feito = false;

  function parar() {
    eventos.forEach(function (nome) {
      window.removeEventListener(nome, agendar, true);
    });
  }

  function marcar() {
    timer = null;
    if (feito || document.visibilityState !== 'visible') {
      return;
    }
    feito = true;
    parar();
    var dados = new FormData();
    dados.append('csrf', alvo.getAttribute('data-csrf'));
    dados.append('ids', ids);
    fetch('vistos.php', { method: 'POST', body: dados, credentials: 'same-origin' })
      .then(function (resposta) { return resposta.ok ? resposta.json() : null; })
      .then(function (resultado) {
        if (!resultado || !resultado.ok) {
          return;
        }
        var contador = document.querySelector('.bar-nav .contador');
        if (contador) {
          if (resultado.restantes > 0) {
            contador.textContent = resultado.restantes;
          } else {
            contador.parentNode.removeChild(contador);
          }
        }
        document.title = (resultado.restantes > 0 ? '(' + resultado.restantes + ') ' : '')
          + document.title.replace(/^\(\d+\)\s*/, '');
        var aviso = alvo.querySelector('[data-aviso-visto]');
        if (aviso && resultado.marcados > 0) {
          aviso.textContent = 'Marcados como vistos. Na próxima visita, aparecem normais na lista.';
        }
        var secao = alvo.querySelector('.naovistos');
        if (secao) {
          secao.classList.add('ja-visto');
        }
      })
      .catch(function () { /* sem conexão: tenta de novo na próxima visita */ });
  }

  function agendar() {
    /* ignora rolagens automáticas logo ao carregar (ex.: voltar no navegador) */
    if (feito || timer || Date.now() - inicio < 400 || document.visibilityState !== 'visible') {
      return;
    }
    timer = setTimeout(marcar, 1200);
  }

  eventos.forEach(function (nome) {
    window.addEventListener(nome, agendar, { capture: true, passive: true });
  });
  document.addEventListener('visibilitychange', function () {
    if (document.visibilityState !== 'visible' && timer) {
      clearTimeout(timer);
      timer = null;
    }
  });
})();
