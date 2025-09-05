// Seleciona a janela modal
var modal = document.getElementById("myModal");

// Seleciona o botão de fechar
// var closeBtn = document.getElementsByClassName("close")[0];

// Quando a página é carregada, a janela modal é exibida
window.onload = function() {
  modal.style.display = "block";
}

// Quando o usuário clicar no botão de fechar ou fora da janela modal, a janela modal é fechada
closeBtn.onclick = function() {
  modal.style.display = "none";
}

window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}