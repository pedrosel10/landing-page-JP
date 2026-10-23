/* CRM J.P.Growth: pede confirmação antes de ações destrutivas (ex.: excluir lead) */
document.addEventListener('submit', function (evento) {
  var form = evento.target;
  var pergunta = form.getAttribute('data-confirmar');
  if (pergunta && !window.confirm(pergunta)) {
    evento.preventDefault();
  }
});
