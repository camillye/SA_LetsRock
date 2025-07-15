
function validarFormulario() {
    const numeroCartao = document.getElementById("ccnum").value;
    const apenasNumeros = /^\d{13,16}$/;
  
    if (!apenasNumeros.test(numeroCartao)) {
      alert("Número do cartão inválido. Digite entre 13 e 16 dígitos numéricos.");
      return false;
    }
  
    return true; // se tudo estiver OK
  }
  