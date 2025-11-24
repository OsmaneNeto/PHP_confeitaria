<?php include('header.php'); ?>

<main class="container">
    <h2 class="titulo">📦 Encomendas</h2>
    
    <div class="botoes-menu" style="margin-bottom: 30px;">
        <a href="nova_encomenda.php" class="btn">➕ Nova Encomenda</a>
        <button id="btn-filtrar" class="btn">🔍 Filtrar</button>
    </div>

    <!-- Filtros -->
    <div id="filtros-container" style="display: none; margin-bottom: 30px; padding: 15px; background-color: #f8f9fa; border-radius: 8px;">
        <h3>Filtros</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
            <div>
                <label for="filtro-status-producao">Status de Produção:</label>
                <select id="filtro-status-producao">
                    <option value="">Todos</option>
                    <option value="0">Não Iniciada</option>
                    <option value="1">Em Produção</option>
                    <option value="2">Concluída</option>
                </select>
            </div>
            <div>
                <label for="filtro-status-pagamento">Status de Pagamento:</label>
                <select id="filtro-status-pagamento">
                    <option value="">Todos</option>
                    <option value="0">Não Pago</option>
                    <option value="1">Pago</option>
                </select>
            </div>
            <div>
                <label for="filtro-cliente">Cliente:</label>
                <select id="filtro-cliente">
                    <option value="">Todos</option>
                </select>
            </div>
        </div>
        <button type="button" id="btn-aplicar-filtros" class="btn" style="margin-top: 10px;">Aplicar Filtros</button>
        <button type="button" id="btn-limpar-filtros" class="btn" style="background-color: #6c757d; margin-top: 10px;">Limpar Filtros</button>
    </div>

    <!-- Lista de encomendas -->
    <div id="lista-encomendas">
        <h3>Lista de Encomendas</h3>
        <div id="encomendas-container"></div>
    </div>

    <!-- Modal para detalhes da encomenda -->
    <div id="modal-detalhes" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 8px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>Detalhes da Encomenda</h3>
                <button onclick="fecharModal()" class="btn" style="background-color: #dc3545;">✕ Fechar</button>
            </div>
            <div id="detalhes-conteudo"></div>
        </div>
    </div>

    <div id="mensagem"></div>
</main>

<script>
let encomendas = [];
let clientes = [];
let filtros = {
    status_producao: '',
    status_pagamento: '',
    id_cliente: ''
};

// Carregar encomendas
async function carregarEncomendas() {
    try {
        let url = '../api/encomendas.php';
        const params = new URLSearchParams();
        
        if(filtros.id_cliente) {
            params.append('id_cliente', filtros.id_cliente);
        }
        
        if(params.toString()) {
            url += '?' + params.toString();
        }
        
        const response = await fetch(url);
        const data = await response.json();
        
        if(data.success) {
            encomendas = data.data;
            
            // Aplicar filtros de status
            if(filtros.status_producao !== '') {
                encomendas = encomendas.filter(e => e.status_producao == filtros.status_producao);
            }
            if(filtros.status_pagamento !== '') {
                encomendas = encomendas.filter(e => e.status_pagamento == filtros.status_pagamento);
            }
            
            exibirEncomendas();
        } else {
            mostrarMensagem('Erro ao carregar encomendas: ' + data.message, 'error');
        }
    } catch(error) {
        console.error('Erro ao carregar encomendas:', error);
        mostrarMensagem('Erro ao carregar encomendas', 'error');
    }
}

// Carregar clientes para filtro
async function carregarClientes() {
    try {
        const response = await fetch('../api/clientes.php');
        const data = await response.json();
        
        if(data.success) {
            clientes = data.data;
            const select = document.getElementById('filtro-cliente');
            clientes.forEach(cliente => {
                const option = document.createElement('option');
                option.value = cliente.id_cliente;
                option.textContent = cliente.nome_cliente;
                select.appendChild(option);
            });
        }
    } catch(error) {
        console.error('Erro ao carregar clientes:', error);
    }
}

// Exibir encomendas
function exibirEncomendas() {
    const container = document.getElementById('encomendas-container');
    container.innerHTML = '';

    if(encomendas.length === 0) {
        container.innerHTML = '<p style="color: #666; text-align: center; padding: 20px;">Nenhuma encomenda encontrada.</p>';
        return;
    }

    encomendas.forEach(encomenda => {
        const div = document.createElement('div');
        div.className = 'encomenda-card';
        div.style.cssText = `
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            background-color: #fff;
        `;
        
        const statusProducao = ['Não Iniciada', 'Em Produção', 'Concluída'][encomenda.status_producao] || 'Desconhecido';
        const statusPagamento = encomenda.status_pagamento == 1 ? 'Pago' : 'Não Pago';
        const corProducao = encomenda.status_producao == 0 ? '#ffc107' : encomenda.status_producao == 1 ? '#17a2b8' : '#28a745';
        const corPagamento = encomenda.status_pagamento == 1 ? '#28a745' : '#dc3545';
        
        div.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div style="flex: 1;">
                    <h4>Encomenda #${encomenda.id_encomenda}</h4>
                    <p><strong>Cliente:</strong> ${encomenda.nome_cliente || 'N/A'}</p>
                    <p><strong>Data do Pedido:</strong> ${formatarData(encomenda.data_pedido)}</p>
                    <p><strong>Data de Entrega:</strong> ${formatarData(encomenda.data_entrega_retirada)}</p>
                    <p><strong>Valor Total:</strong> R$ ${parseFloat(encomenda.valor_total).toFixed(2)}</p>
                    <div style="margin-top: 10px;">
                        <span style="background-color: ${corProducao}; color: white; padding: 5px 10px; border-radius: 4px; margin-right: 5px;">
                            ${statusProducao}
                        </span>
                        <span style="background-color: ${corPagamento}; color: white; padding: 5px 10px; border-radius: 4px;">
                            ${statusPagamento}
                        </span>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <button onclick="verDetalhes(${encomenda.id_encomenda})" class="btn">👁️ Ver Detalhes</button>
                    <button onclick="atualizarStatusProducao(${encomenda.id_encomenda})" class="btn" style="background-color: #17a2b8;">⚙️ Status Produção</button>
                    <button onclick="atualizarStatusPagamento(${encomenda.id_encomenda})" class="btn" style="background-color: #28a745;">💳 Status Pagamento</button>
                    <button onclick="excluirEncomenda(${encomenda.id_encomenda})" class="btn" style="background-color: #dc3545;">🗑️ Excluir</button>
                </div>
            </div>
        `;
        
        container.appendChild(div);
    });
}

// Formatar data
function formatarData(data) {
    if(!data) return 'N/A';
    const date = new Date(data + 'T00:00:00');
    return date.toLocaleDateString('pt-BR');
}

// Ver detalhes da encomenda
async function verDetalhes(id) {
    try {
        const response = await fetch(`../api/encomendas.php?id=${id}`);
        const data = await response.json();
        
        if(data.success) {
            const encomenda = data.data;
            const container = document.getElementById('detalhes-conteudo');
            
            const statusProducao = ['Não Iniciada', 'Em Produção', 'Concluída'][encomenda.status_producao] || 'Desconhecido';
            const statusPagamento = encomenda.status_pagamento == 1 ? 'Pago' : 'Não Pago';
            
            let itensHtml = '';
            if(encomenda.itens && encomenda.itens.length > 0) {
                itensHtml = '<h4>Itens:</h4><ul>';
                encomenda.itens.forEach(item => {
                    itensHtml += `<li>${item.nome_receita} - Quantidade: ${item.quantidate_vendida} - Preço Unitário: R$ ${parseFloat(item.preco_venda_sugerido).toFixed(2)}</li>`;
                });
                itensHtml += '</ul>';
            }
            
            container.innerHTML = `
                <div>
                    <p><strong>ID:</strong> ${encomenda.id_encomenda}</p>
                    <p><strong>Cliente:</strong> ${encomenda.nome_cliente || 'N/A'}</p>
                    <p><strong>Telefone:</strong> ${encomenda.telefone_cliente || 'N/A'}</p>
                    <p><strong>Endereço:</strong> ${encomenda.endereço_cliente || 'N/A'}</p>
                    <p><strong>Data do Pedido:</strong> ${formatarData(encomenda.data_pedido)}</p>
                    <p><strong>Data de Entrega:</strong> ${formatarData(encomenda.data_entrega_retirada)}</p>
                    <p><strong>Status de Produção:</strong> ${statusProducao}</p>
                    <p><strong>Status de Pagamento:</strong> ${statusPagamento}</p>
                    <p><strong>Valor Total:</strong> R$ ${parseFloat(encomenda.valor_total).toFixed(2)}</p>
                    ${itensHtml}
                </div>
            `;
            
            document.getElementById('modal-detalhes').style.display = 'block';
        } else {
            mostrarMensagem('Erro ao carregar detalhes da encomenda', 'error');
        }
    } catch(error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao carregar detalhes da encomenda', 'error');
    }
}

// Fechar modal
function fecharModal() {
    document.getElementById('modal-detalhes').style.display = 'none';
}

// Atualizar status de produção
async function atualizarStatusProducao(id) {
    const statusAtual = encomendas.find(e => e.id_encomenda == id)?.status_producao || 0;
    const novoStatus = (statusAtual + 1) % 3; // Alterna entre 0, 1, 2
    
    try {
        const response = await fetch('../api/encomendas.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                atualizar_status_producao: true,
                id_encomenda: id,
                status_producao: novoStatus
            })
        });
        
        const data = await response.json();
        
        if(data.success) {
            mostrarMensagem('Status de produção atualizado!', 'success');
            carregarEncomendas();
        } else {
            mostrarMensagem('Erro ao atualizar status: ' + data.message, 'error');
        }
    } catch(error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao atualizar status', 'error');
    }
}

// Atualizar status de pagamento
async function atualizarStatusPagamento(id) {
    const statusAtual = encomendas.find(e => e.id_encomenda == id)?.status_pagamento || 0;
    const novoStatus = statusAtual == 0 ? 1 : 0; // Alterna entre 0 e 1
    
    try {
        const response = await fetch('../api/encomendas.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                atualizar_status_pagamento: true,
                id_encomenda: id,
                status_pagamento: novoStatus
            })
        });
        
        const data = await response.json();
        
        if(data.success) {
            mostrarMensagem('Status de pagamento atualizado!', 'success');
            carregarEncomendas();
        } else {
            mostrarMensagem('Erro ao atualizar status: ' + data.message, 'error');
        }
    } catch(error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao atualizar status', 'error');
    }
}

// Excluir encomenda
async function excluirEncomenda(id) {
    if(!confirm('Tem certeza que deseja excluir esta encomenda?')) {
        return;
    }
    
    try {
        const response = await fetch('../api/encomendas.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id_encomenda: id })
        });
        
        const data = await response.json();
        
        if(data.success) {
            mostrarMensagem('Encomenda excluída com sucesso!', 'success');
            carregarEncomendas();
        } else {
            mostrarMensagem('Erro ao excluir encomenda: ' + data.message, 'error');
        }
    } catch(error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao excluir encomenda', 'error');
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
document.getElementById('btn-filtrar').addEventListener('click', function() {
    const container = document.getElementById('filtros-container');
    container.style.display = container.style.display === 'none' ? 'block' : 'none';
});

document.getElementById('btn-aplicar-filtros').addEventListener('click', function() {
    filtros.status_producao = document.getElementById('filtro-status-producao').value;
    filtros.status_pagamento = document.getElementById('filtro-status-pagamento').value;
    filtros.id_cliente = document.getElementById('filtro-cliente').value;
    carregarEncomendas();
});

document.getElementById('btn-limpar-filtros').addEventListener('click', function() {
    document.getElementById('filtro-status-producao').value = '';
    document.getElementById('filtro-status-pagamento').value = '';
    document.getElementById('filtro-cliente').value = '';
    filtros = {
        status_producao: '',
        status_pagamento: '',
        id_cliente: ''
    };
    carregarEncomendas();
});

// Fechar modal ao clicar fora
document.getElementById('modal-detalhes').addEventListener('click', function(e) {
    if(e.target === this) {
        fecharModal();
    }
});

// Carregar dados iniciais
carregarClientes();
carregarEncomendas();
</script>

<?php include('footer.php'); ?>

