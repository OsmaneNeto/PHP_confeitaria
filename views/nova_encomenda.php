<?php include('header.php'); ?>

<main class="container">
    <h2 class="titulo">🧾 Nova Encomenda</h2>
    
    <form id="form-encomenda" class="formulario">
        <label for="id_cliente">Cliente:</label>
        <select id="id_cliente" name="id_cliente" required>
            <option value="">Selecione um cliente...</option>
        </select>

        <label for="data_pedido">Data do Pedido:</label>
        <input type="date" id="data_pedido" name="data_pedido" value="<?php echo date('Y-m-d'); ?>" required>

        <label for="data_entrega_retirada">Data de Entrega/Retirada:</label>
        <input type="date" id="data_entrega_retirada" name="data_entrega_retirada" required>

        <div style="margin-top: 20px;">
            <h3>Itens da Encomenda</h3>
            <div id="itens-container">
                <!-- Itens serão adicionados aqui dinamicamente -->
            </div>
            <button type="button" id="btn-adicionar-item" class="btn" style="margin-top: 10px;">➕ Adicionar Item</button>
        </div>

        <div style="margin-top: 20px; padding: 15px; background-color: #f0f8ff; border-radius: 8px;">
            <h3>💰 Resumo</h3>
            <p><strong>Valor Total:</strong> R$ <span id="valor-total">0,00</span></p>
        </div>

        <div style="margin-top: 20px;">
            <label>
                <input type="checkbox" id="status_pagamento" name="status_pagamento" value="1">
                Pagamento realizado
            </label>
        </div>

        <button type="submit" class="btn-enviar">Criar Encomenda</button>
        <a href="listar_encomendas.php" class="btn" style="background-color: #6c757d; text-decoration: none; display: inline-block;">Cancelar</a>
    </form>

    <div id="mensagem"></div>
</main>

<script>
let receitas = [];
let itensEncomenda = [];
let encomendaId = null;

// Carregar clientes
async function carregarClientes() {
    try {
        const response = await fetch('../api/clientes.php');
        const data = await response.json();
        
        if(data.success) {
            const select = document.getElementById('id_cliente');
            data.data.forEach(cliente => {
                const option = document.createElement('option');
                option.value = cliente.id_cliente;
                option.textContent = cliente.nome_cliente;
                select.appendChild(option);
            });
        }
    } catch(error) {
        console.error('Erro ao carregar clientes:', error);
        mostrarMensagem('Erro ao carregar clientes', 'error');
    }
}

// Carregar receitas
async function carregarReceitas() {
    try {
        const response = await fetch('../api/receitas.php');
        const data = await response.json();
        
        if(data.success) {
            receitas = data.data;
        }
    } catch(error) {
        console.error('Erro ao carregar receitas:', error);
        mostrarMensagem('Erro ao carregar receitas', 'error');
    }
}

// Adicionar item à encomenda
function adicionarItem() {
    const itemId = Date.now();
    itensEncomenda.push({
        id: itemId,
        id_receita: '',
        quantidade: 1
    });
    
    exibirItens();
}

// Remover item da encomenda
function removerItem(itemId) {
    itensEncomenda = itensEncomenda.filter(item => item.id !== itemId);
    exibirItens();
    calcularValorTotal();
}

// Exibir itens
function exibirItens() {
    const container = document.getElementById('itens-container');
    container.innerHTML = '';
    
    itensEncomenda.forEach((item, index) => {
        const div = document.createElement('div');
        div.style.cssText = `
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            background-color: #fff;
        `;
        
        div.innerHTML = `
            <div style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 10px; align-items: end;">
                <div>
                    <label>Receita:</label>
                    <select class="receita-select" data-item-id="${item.id}" required>
                        <option value="">Selecione uma receita...</option>
                        ${receitas.map(receita => `
                            <option value="${receita.id_receita}" ${item.id_receita == receita.id_receita ? 'selected' : ''}
                                    data-preco="${receita.preco_venda_sugerido}">
                                ${receita.nome_receita} - R$ ${parseFloat(receita.preco_venda_sugerido).toFixed(2)}
                            </option>
                        `).join('')}
                    </select>
                </div>
                <div>
                    <label>Quantidade:</label>
                    <input type="number" class="quantidade-input" data-item-id="${item.id}" 
                           value="${item.quantidade}" min="1" required>
                </div>
                <div>
                    <button type="button" onclick="removerItem(${item.id})" class="btn" 
                            style="background-color: #dc3545;">🗑️</button>
                </div>
            </div>
        `;
        
        container.appendChild(div);
    });
    
    // Adicionar event listeners
    document.querySelectorAll('.receita-select').forEach(select => {
        select.addEventListener('change', function() {
            const itemId = parseInt(this.getAttribute('data-item-id'));
            const item = itensEncomenda.find(i => i.id === itemId);
            if(item) {
                item.id_receita = this.value;
                calcularValorTotal();
            }
        });
    });
    
    document.querySelectorAll('.quantidade-input').forEach(input => {
        input.addEventListener('input', function() {
            const itemId = parseInt(this.getAttribute('data-item-id'));
            const item = itensEncomenda.find(i => i.id === itemId);
            if(item) {
                item.quantidade = parseInt(this.value) || 1;
                calcularValorTotal();
            }
        });
    });
}

// Calcular valor total
function calcularValorTotal() {
    let total = 0;
    
    itensEncomenda.forEach(item => {
        if(item.id_receita) {
            const receita = receitas.find(r => r.id_receita == item.id_receita);
            if(receita) {
                total += parseFloat(receita.preco_venda_sugerido) * (item.quantidade || 1);
            }
        }
    });
    
    document.getElementById('valor-total').textContent = total.toFixed(2);
}

// Criar encomenda
document.getElementById('form-encomenda').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if(itensEncomenda.length === 0) {
        mostrarMensagem('Adicione pelo menos um item à encomenda', 'error');
        return;
    }
    
    if(itensEncomenda.some(item => !item.id_receita || !item.quantidade)) {
        mostrarMensagem('Preencha todos os itens corretamente', 'error');
        return;
    }
    
    // Criar encomenda
    const encomendaData = {
        criar_encomenda: true,
        id_cliente: document.getElementById('id_cliente').value,
        data_pedido: document.getElementById('data_pedido').value,
        data_entrega_retirada: document.getElementById('data_entrega_retirada').value,
        status_pagamento: document.getElementById('status_pagamento').checked ? 1 : 0,
        status_producao: 0,
        valor_total: 0
    };
    
    try {
        // Primeiro criar a encomenda
        const responseEncomenda = await fetch('../api/encomendas.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(encomendaData)
        });
        
        const dataEncomenda = await responseEncomenda.json();
        
        if(!dataEncomenda.success) {
            mostrarMensagem('Erro ao criar encomenda: ' + dataEncomenda.message, 'error');
            return;
        }
        
        encomendaId = dataEncomenda.data.id_encomenda;
        
        // Adicionar itens
        for(const item of itensEncomenda) {
            const itemData = {
                adicionar_item: true,
                id_encomenda: encomendaId,
                id_receita: item.id_receita,
                quantidate_vendida: item.quantidade
            };
            
            const responseItem = await fetch('../api/encomendas.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(itemData)
            });
            
            const dataItem = await responseItem.json();
            
            if(!dataItem.success) {
                mostrarMensagem('Erro ao adicionar item: ' + dataItem.message, 'error');
                return;
            }
        }
        
        mostrarMensagem('Encomenda criada com sucesso!', 'success');
        
        // Limpar formulário
        setTimeout(() => {
            window.location.href = 'listar_encomendas.php';
        }, 1500);
        
    } catch(error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao criar encomenda', 'error');
    }
});

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
    
    if(tipo === 'success') {
        setTimeout(() => {
            container.innerHTML = '';
        }, 3000);
    }
}

// Event listeners
document.getElementById('btn-adicionar-item').addEventListener('click', adicionarItem);

// Carregar dados iniciais
carregarClientes();
carregarReceitas().then(() => {
    // Adicionar primeiro item automaticamente
    adicionarItem();
});
</script>

<?php include('footer.php'); ?>

