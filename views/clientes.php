<?php include('header.php'); ?>

<main class="container">
    <h2 class="titulo">👩‍💼 Gerenciar Clientes</h2>
    
    <div class="botoes-menu" style="margin-bottom: 30px;">
        <button id="btn-novo-cliente" class="btn">➕ Novo Cliente</button>
    </div>

    <!-- Formulário para novo cliente -->
    <div id="form-novo-cliente" style="display: none; margin-bottom: 30px;">
        <h3>Cadastrar Novo Cliente</h3>
        <form id="form-cliente" class="formulario">
            <label for="nome_cliente">Nome do Cliente:</label>
            <input type="text" id="nome_cliente" name="nome_cliente" required>

            <label for="telefone_cliente">Telefone:</label>
            <input type="text" id="telefone_cliente" name="telefone_cliente" placeholder="Ex: 11987654321" required>

            <label for="endereço_cliente">Endereço:</label>
            <textarea id="endereço_cliente" name="endereço_cliente" rows="3" placeholder="Endereço completo do cliente"></textarea>

            <button type="submit" class="btn-enviar">Salvar Cliente</button>
            <button type="button" id="btn-cancelar" class="btn" style="background-color: #6c757d;">Cancelar</button>
        </form>
    </div>

    <!-- Formulário para editar cliente -->
    <div id="form-editar-cliente" style="display: none; margin-bottom: 30px;">
        <h3>Editar Cliente</h3>
        <form id="form-cliente-editar" class="formulario">
            <input type="hidden" id="id_cliente_editar" name="id_cliente">
            
            <label for="nome_cliente_editar">Nome do Cliente:</label>
            <input type="text" id="nome_cliente_editar" name="nome_cliente" required>

            <label for="telefone_cliente_editar">Telefone:</label>
            <input type="text" id="telefone_cliente_editar" name="telefone_cliente" required>

            <label for="endereço_cliente_editar">Endereço:</label>
            <textarea id="endereço_cliente_editar" name="endereço_cliente" rows="3"></textarea>

            <button type="submit" class="btn-enviar">Atualizar Cliente</button>
            <button type="button" id="btn-cancelar-editar" class="btn" style="background-color: #6c757d;">Cancelar</button>
        </form>
    </div>

    <!-- Lista de clientes -->
    <div id="lista-clientes">
        <h3>Lista de Clientes</h3>
        <div id="clientes-container"></div>
    </div>

    <div id="mensagem"></div>
</main>

<script>
let clientes = [];

// Carregar clientes
async function carregarClientes() {
    try {
        const response = await fetch('../api/clientes.php');
        const data = await response.json();
        
        if(data.success) {
            clientes = data.data;
            exibirClientes();
        } else {
            mostrarMensagem('Erro ao carregar clientes: ' + data.message, 'error');
        }
    } catch(error) {
        console.error('Erro ao carregar clientes:', error);
        mostrarMensagem('Erro ao carregar clientes', 'error');
    }
}

// Exibir clientes na tela
function exibirClientes() {
    const container = document.getElementById('clientes-container');
    container.innerHTML = '';

    if(clientes.length === 0) {
        container.innerHTML = '<p style="color: #666; text-align: center; padding: 20px;">Nenhum cliente cadastrado ainda.</p>';
        return;
    }

    clientes.forEach(cliente => {
        const div = document.createElement('div');
        div.className = 'cliente-card';
        div.style.cssText = `
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            background-color: #fff;
        `;
        
        div.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <h4>${cliente.nome_cliente}</h4>
                    <p><strong>Telefone:</strong> ${formatarTelefone(cliente.telefone_cliente)}</p>
                    <p><strong>Endereço:</strong> ${cliente.endereço_cliente || 'Não informado'}</p>
                </div>
                <div>
                    <button onclick="editarCliente(${cliente.id_cliente})" class="btn" style="margin: 2px;">✏️ Editar</button>
                    <button onclick="excluirCliente(${cliente.id_cliente})" class="btn" style="background-color: #dc3545; margin: 2px;">🗑️ Excluir</button>
                </div>
            </div>
        `;
        
        container.appendChild(div);
    });
}

// Formatar telefone
function formatarTelefone(telefone) {
    if(!telefone) return 'Não informado';
    const tel = telefone.toString();
    if(tel.length === 11) {
        return `(${tel.substring(0,2)}) ${tel.substring(2,7)}-${tel.substring(7)}`;
    } else if(tel.length === 10) {
        return `(${tel.substring(0,2)}) ${tel.substring(2,6)}-${tel.substring(6)}`;
    }
    return tel;
}

// Salvar novo cliente
document.getElementById('form-cliente').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = {
        nome_cliente: document.getElementById('nome_cliente').value,
        telefone_cliente: document.getElementById('telefone_cliente').value.replace(/\D/g, ''),
        endereço_cliente: document.getElementById('endereço_cliente').value
    };
    
    try {
        const response = await fetch('../api/clientes.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if(data.success) {
            mostrarMensagem('Cliente cadastrado com sucesso!', 'success');
            document.getElementById('form-novo-cliente').style.display = 'none';
            document.getElementById('form-cliente').reset();
            carregarClientes();
        } else {
            mostrarMensagem(data.message || 'Erro ao cadastrar cliente', 'error');
        }
    } catch(error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao cadastrar cliente', 'error');
    }
});

// Editar cliente
async function editarCliente(id) {
    try {
        const response = await fetch(`../api/clientes.php?id=${id}`);
        const data = await response.json();
        
        if(data.success) {
            const cliente = data.data;
            document.getElementById('id_cliente_editar').value = cliente.id_cliente;
            document.getElementById('nome_cliente_editar').value = cliente.nome_cliente;
            document.getElementById('telefone_cliente_editar').value = cliente.telefone_cliente;
            document.getElementById('endereço_cliente_editar').value = cliente.endereço_cliente || '';
            
            document.getElementById('form-novo-cliente').style.display = 'none';
            document.getElementById('form-editar-cliente').style.display = 'block';
        } else {
            mostrarMensagem('Erro ao carregar dados do cliente', 'error');
        }
    } catch(error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao carregar dados do cliente', 'error');
    }
}

// Atualizar cliente
document.getElementById('form-cliente-editar').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = {
        id_cliente: document.getElementById('id_cliente_editar').value,
        nome_cliente: document.getElementById('nome_cliente_editar').value,
        telefone_cliente: document.getElementById('telefone_cliente_editar').value.replace(/\D/g, ''),
        endereço_cliente: document.getElementById('endereço_cliente_editar').value
    };
    
    try {
        const response = await fetch('../api/clientes.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if(data.success) {
            mostrarMensagem('Cliente atualizado com sucesso!', 'success');
            document.getElementById('form-editar-cliente').style.display = 'none';
            document.getElementById('form-cliente-editar').reset();
            carregarClientes();
        } else {
            mostrarMensagem(data.message || 'Erro ao atualizar cliente', 'error');
        }
    } catch(error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao atualizar cliente', 'error');
    }
});

// Excluir cliente
async function excluirCliente(id) {
    if(!confirm('Tem certeza que deseja excluir este cliente?')) {
        return;
    }
    
    try {
        const response = await fetch('../api/clientes.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id_cliente: id })
        });
        
        const data = await response.json();
        
        if(data.success) {
            mostrarMensagem('Cliente excluído com sucesso!', 'success');
            carregarClientes();
        } else {
            mostrarMensagem(data.message || 'Erro ao excluir cliente', 'error');
        }
    } catch(error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao excluir cliente', 'error');
    }
}

// Mostrar mensagem
function mostrarMensagem(texto, tipo) {
    const container = document.getElementById('mensagem');
    const cor = tipo === 'success' ? '#d4edda' : '#f8d7da';
    const textoCor = tipo === 'success' ? '#155724' : '#721c24';
    
    container.innerHTML = `
        <div style="background-color: ${cor}; color: ${textoCor}; padding: 15px; border-radius: 8px; margin-top: 20px;">
            ${texto}
        </div>
    `;
    
    setTimeout(() => {
        container.innerHTML = '';
    }, 3000);
}

// Event listeners
document.getElementById('btn-novo-cliente').addEventListener('click', function() {
    document.getElementById('form-novo-cliente').style.display = 'block';
    document.getElementById('form-editar-cliente').style.display = 'none';
});

document.getElementById('btn-cancelar').addEventListener('click', function() {
    document.getElementById('form-novo-cliente').style.display = 'none';
    document.getElementById('form-cliente').reset();
});

document.getElementById('btn-cancelar-editar').addEventListener('click', function() {
    document.getElementById('form-editar-cliente').style.display = 'none';
    document.getElementById('form-cliente-editar').reset();
});

// Carregar dados iniciais
carregarClientes();
</script>

<?php include('footer.php'); ?>

