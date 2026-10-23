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

/* Funil: arrastar e soltar os cards entre as etapas (mouse ou trackpad).
   No celular, toque no card para abrir a ficha e mudar a etapa por lá.
   Venda e perda abrem a ficha, porque pedem valor ou motivo. */
(function () {
  var quadro = document.querySelector('[data-quadro]');
  if (!quadro || !window.fetch || !window.FormData) {
    return;
  }
  var arrastado = null;
  var origem = null;

  function zonaDe(alvo) {
    return alvo && alvo.closest ? alvo.closest('[data-soltar]') : null;
  }
  function limparAlvos() {
    Array.prototype.forEach.call(quadro.querySelectorAll('.alvo'), function (z) { z.classList.remove('alvo'); });
  }
  function atualizarContagens() {
    Array.prototype.forEach.call(quadro.querySelectorAll('.coluna'), function (coluna) {
      var n = coluna.querySelectorAll('.card').length;
      var qtd = coluna.querySelector('[data-qtd]');
      if (qtd) {
        qtd.textContent = n;
      }
      var vazio = coluna.querySelector('.coluna-vazia');
      if (vazio) {
        vazio.hidden = n > 0;
      }
    });
  }
  function devolver(card, para) {
    para.insertBefore(card, para.firstChild);
    atualizarContagens();
  }

  quadro.addEventListener('dragstart', function (e) {
    var card = e.target.closest ? e.target.closest('.card') : null;
    if (!card) {
      return;
    }
    arrastado = card;
    origem = card.parentNode;
    card.classList.add('arrastando');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', card.getAttribute('data-id'));
  });
  quadro.addEventListener('dragend', function () {
    if (arrastado) {
      arrastado.classList.remove('arrastando');
    }
    limparAlvos();
  });
  quadro.addEventListener('dragover', function (e) {
    var zona = zonaDe(e.target);
    if (!zona || !arrastado) {
      return;
    }
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    if (!zona.classList.contains('alvo')) {
      limparAlvos();
      zona.classList.add('alvo');
    }
  });
  quadro.addEventListener('drop', function (e) {
    var zona = zonaDe(e.target);
    if (!zona || !arrastado) {
      return;
    }
    e.preventDefault();
    limparAlvos();
    var card = arrastado;
    var de = origem;
    arrastado = null;
    card.classList.remove('arrastando');
    if (zona === de) {
      return;
    }
    var id = card.getAttribute('data-id');
    var etapa = zona.parentNode.getAttribute('data-etapa');
    if (etapa === 'ganho' || etapa === 'perdido') {
      window.location.href = 'lead.php?id=' + id + '&fechar=' + etapa + '#fechar';
      return;
    }
    devolver(card, zona);
    var dados = new FormData();
    dados.append('csrf', quadro.getAttribute('data-csrf'));
    dados.append('id', id);
    dados.append('etapa', etapa);
    fetch('mover.php', { method: 'POST', body: dados, credentials: 'same-origin' })
      .then(function (resposta) { return resposta.json().catch(function () { return { ok: false }; }); })
      .then(function (resultado) {
        if (resultado && resultado.ok) {
          card.classList.remove('card-novo');
          return;
        }
        devolver(card, de);
        if (resultado && resultado.precisa) {
          window.location.href = 'lead.php?id=' + id + '&fechar=' + resultado.precisa + '#fechar';
          return;
        }
        window.alert((resultado && resultado.erro) || 'Não foi possível mover o lead. Tente de novo.');
      })
      .catch(function () {
        devolver(card, de);
        window.alert('Sem conexão. O lead não foi movido.');
      });
  });
})();
