document.getElementById('endereco').addEventListener('blur', function () {
    const cep = this.value.replace(/\D/g, '');
  
    if (cep.length !== 8) {
      alert('CEP inválido! Deve conter 8 números.');
      return;
    }
  
    fetch(`https://viacep.com.br/ws/${cep}/json/`)
      .then(response => response.json())
      .then(data => {
        if (data.erro) {
          alert('CEP não encontrado!');
          limparCampos();
          return;
        }
  
        document.getElementById('logradouro').value = data.logradouro || '';
        document.getElementById('bairro').value = data.bairro || '';
        document.getElementById('cidade').value = data.localidade || '';
        document.getElementById('estado').value = data.uf || '';
      })
      .catch(() => {
        alert('Erro ao consultar o CEP.');
        limparCampos();
      });
  });
  
  function limparCampos() {
    document.getElementById('logradouro').value = '';
    document.getElementById('bairro').value = '';
    document.getElementById('cidade').value = '';
    document.getElementById('estado').value = '';
  }
  
  