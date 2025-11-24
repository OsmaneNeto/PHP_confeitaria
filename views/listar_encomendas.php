<?php include('header.php'); ?>

<main class="container">
    <h2 class="titulo">Encomendas</h2>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; gap: 1rem; flex-wrap: wrap;">
        <p class="text-muted" style="margin: 0;">Gerencie todas as suas encomendas</p>
        <div style="display: flex; gap: 0.75rem;">
            <a href="nova_encomenda.php" class="btn">➕ Nova Encomenda</a>
            <button id="btn-filtrar" class="btn" style="background: var(--gray-500);">🔍 Filtrar</button>
        </div>
    </div>

    <!-- Filtros -->
    <div id="filtros-container" style="display: none; margin-bottom: 2rem; padding: 1.5rem; background: var(--gray-50); border-radius: var(--radius); border: 1px solid var(--gray-200);">
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
    <div id="modal-detalhes" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 1000; animation: fadeIn 0.2s;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: var(--radius); max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: var(--shadow-lg);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>Detalhes da Encomenda</h3>
                <button onclick="fecharModal()" class="btn" style="background-color: #dc3545;">✕ Fechar</button>
            </div>
            <div id="detalhes-conteudo"></div>
        </div>
    </div>

    <!-- Modal para atualizar status de produção -->
    <div id="modal-status-producao" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 1001; animation: fadeIn 0.2s;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: var(--radius); max-width: 400px; width: 90%; box-shadow: var(--shadow-lg);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3>Atualizar Status de Produção</h3>
                <button type="button" id="btn-fechar-modal-producao" class="btn" style="background-color: #6c757d;">✕</button>
            </div>
            <div>
                <label for="select-status-producao" style="display: block; margin-bottom: 0.75rem; font-weight: 500;">Selecione o novo status:</label>
                <select id="select-status-producao" style="width: 100%; padding: 0.75rem; border: 1.5px solid var(--gray-300); border-radius: var(--radius-sm); margin-bottom: 1.5rem;">
                    <option value="0">Não Iniciado</option>
                    <option value="1">Em Produção</option>
                    <option value="2">Pronto</option>
                    <option value="3">Entregue</option>
                </select>
                <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                    <button type="button" id="btn-cancelar-producao-modal" class="btn" style="background-color: #6c757d;">Cancelar</button>
                    <button type="button" id="btn-confirmar-producao" class="btn">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para atualizar status de pagamento -->
    <div id="modal-status-pagamento" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 1001; animation: fadeIn 0.2s;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: var(--radius); max-width: 400px; width: 90%; box-shadow: var(--shadow-lg);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3>Atualizar Status de Pagamento</h3>
                <button type="button" id="btn-fechar-modal-pagamento" class="btn" style="background-color: #6c757d;">✕</button>
            </div>
            <div>
                <label for="select-status-pagamento" style="display: block; margin-bottom: 0.75rem; font-weight: 500;">Selecione o novo status:</label>
                <select id="select-status-pagamento" style="width: 100%; padding: 0.75rem; border: 1.5px solid var(--gray-300); border-radius: var(--radius-sm); margin-bottom: 1.5rem;">
                    <option value="0">Não Pago</option>
                    <option value="1">Pago</option>
                </select>
                <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                    <button type="button" id="btn-cancelar-pagamento-modal" class="btn" style="background-color: #6c757d;">Cancelar</button>
                    <button type="button" id="btn-confirmar-pagamento" class="btn">Confirmar</button>
                </div>
            </div>
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
        container.innerHTML = '<p class="text-center text-muted" style="padding: 3rem;">Nenhuma encomenda encontrada.</p>';
        return;
    }

    encomendas.forEach(encomenda => {
        const div = document.createElement('div');
        div.className = 'encomenda-card';
        div.className = 'encomenda-card';
        div.style.cssText = '';
        
        const statusProducaoLabels = ['Não Iniciado', 'Em Produção', 'Pronto', 'Entregue'];
        const statusProducao = statusProducaoLabels[encomenda.status_producao] || 'Desconhecido';
        const statusPagamento = encomenda.status_pagamento == 1 ? 'Pago' : 'Não Pago';
        const coresProducao = ['#ffc107', '#17a2b8', '#28a745', '#6c757d'];
        const corProducao = coresProducao[encomenda.status_producao] || '#6c757d';
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

// Ver detalhes da encomenda
async function verDetalhes(id) {
    try {
        const response = await fetch(`../api/encomendas.php?id=${id}`);
        const data = await response.json();
        
        if(data.success) {
            const encomenda = data.data;
            const container = document.getElementById('detalhes-conteudo');
            
            const statusProducaoLabels = ['Não Iniciado', 'Em Produção', 'Pronto', 'Entregue'];
            const statusProducao = statusProducaoLabels[encomenda.status_producao] || 'Desconhecido';
            const statusPagamento = encomenda.status_pagamento == 1 ? 'Pago' : 'Não Pago';
            
            // Cores para os status de produção
            const coresProducao = ['#ffc107', '#17a2b8', '#28a745', '#6c757d'];
            const corProducao = coresProducao[encomenda.status_producao] || '#6c757d';
            const corPagamento = encomenda.status_pagamento == 1 ? '#28a745' : '#dc3545';
            
            let itensHtml = '';
            if(encomenda.itens && encomenda.itens.length > 0) {
                itensHtml = '<h4>Itens:</h4><ul>';
                encomenda.itens.forEach(item => {
                    itensHtml += `<li>${item.nome_receita} - Quantidade: ${item.quantidate_vendida} - Preço Unitário: R$ ${parseFloat(item.preco_venda_sugerido).toFixed(2)}</li>`;
                });
                itensHtml += '</ul>';
            }
            
            // Formatar telefone
            const telefoneFormatado = encomenda.telefone_cliente ? formatarTelefone(encomenda.telefone_cliente) : 'N/A';
            
            container.innerHTML = `
                <div>
                    <h4 style="margin-bottom: 1rem; color: var(--primary);">Informações da Encomenda</h4>
                    <p><strong>ID:</strong> #${encomenda.id_encomenda}</p>
                    <p><strong>Data do Pedido:</strong> ${formatarData(encomenda.data_pedido)}</p>
                    <p><strong>Data de Entrega:</strong> ${formatarData(encomenda.data_entrega_retirada)}</p>
                    <p><strong>Status de Produção:</strong> <span style="background-color: ${corProducao}; color: white; padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.875rem;">${statusProducao}</span></p>
                    <p><strong>Status de Pagamento:</strong> <span style="background-color: ${corPagamento}; color: white; padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.875rem;">${statusPagamento}</span></p>
                    <p><strong>Valor Total:</strong> R$ ${parseFloat(encomenda.valor_total).toFixed(2)}</p>
                    
                    <h4 style="margin-top: 1.5rem; margin-bottom: 1rem; color: var(--primary);">Informações do Cliente</h4>
                    <p><strong>Nome:</strong> ${encomenda.nome_cliente || 'N/A'}</p>
                    <p><strong>Telefone:</strong> ${telefoneFormatado}</p>
                    <p><strong>Endereço:</strong> ${encomenda.endereço_cliente || 'N/A'}</p>
                    
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

// Variáveis globais para os modais
var encomendaIdProducao = null;
var encomendaIdPagamento = null;

// Abrir modal de status de produção
window.atualizarStatusProducao = function(id) {
    encomendaIdProducao = id;
    const encomenda = encomendas.find(e => e.id_encomenda == id);
    const select = document.getElementById('select-status-producao');
    if(encomenda) {
        select.value = encomenda.status_producao || 0;
    }
    document.getElementById('modal-status-producao').style.display = 'block';
}

// Fechar modal de produção
window.fecharModalProducao = function() {
    document.getElementById('modal-status-producao').style.display = 'none';
    encomendaIdProducao = null;
}

// Confirmar atualização de status de produção
window.confirmarStatusProducao = async function() {
    if(!encomendaIdProducao) {
        console.error('ID da encomenda não definido');
        return;
    }
    
    const select = document.getElementById('select-status-producao');
    if(!select) {
        console.error('Select de status não encontrado');
        return;
    }
    
    const novoStatus = parseInt(select.value);
    console.log('Atualizando status de produção:', encomendaIdProducao, 'para', novoStatus);
    
    try {
        // Usar GET com parâmetros na URL - mais simples e confiável
        const url = `../api/encomendas.php?acao=atualizar_status_producao&id_encomenda=${encomendaIdProducao}&status_producao=${novoStatus}`;
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        });
        
        const text = await response.text();
        console.log('Resposta bruta:', text);
        
        if(!text || text.trim() === '') {
            mostrarMensagem('Erro: Resposta vazia do servidor', 'error');
            return;
        }
        
        const data = JSON.parse(text);
        console.log('Resposta da API:', data);
        
        if(data.success) {
            mostrarMensagem('Status de produção atualizado!', 'success');
            fecharModalProducao();
            carregarEncomendas();
        } else {
            mostrarMensagem('Erro ao atualizar status: ' + (data.message || 'Erro desconhecido'), 'error');
        }
    } catch(error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao atualizar status: ' + error.message, 'error');
    }
}

// Abrir modal de status de pagamento
window.atualizarStatusPagamento = function(id) {
    encomendaIdPagamento = id;
    const encomenda = encomendas.find(e => e.id_encomenda == id);
    const select = document.getElementById('select-status-pagamento');
    if(encomenda) {
        select.value = encomenda.status_pagamento || 0;
    }
    document.getElementById('modal-status-pagamento').style.display = 'block';
}

// Fechar modal de pagamento
window.fecharModalPagamento = function() {
    document.getElementById('modal-status-pagamento').style.display = 'none';
    encomendaIdPagamento = null;
}

// Confirmar atualização de status de pagamento
window.confirmarStatusPagamento = async function() {
    if(!encomendaIdPagamento) {
        console.error('ID da encomenda não definido');
        return;
    }
    
    const select = document.getElementById('select-status-pagamento');
    if(!select) {
        console.error('Select de status não encontrado');
        return;
    }
    
    const novoStatus = parseInt(select.value);
    console.log('Atualizando status de pagamento:', encomendaIdPagamento, 'para', novoStatus);
    
    try {
        // Usar GET com parâmetros na URL - mais simples e confiável
        const url = `../api/encomendas.php?acao=atualizar_status_pagamento&id_encomenda=${encomendaIdPagamento}&status_pagamento=${novoStatus}`;
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        });
        
        const text = await response.text();
        console.log('Resposta bruta:', text);
        
        if(!text || text.trim() === '') {
            mostrarMensagem('Erro: Resposta vazia do servidor', 'error');
            return;
        }
        
        const data = JSON.parse(text);
        console.log('Resposta da API:', data);
        
        if(data.success) {
            mostrarMensagem('Status de pagamento atualizado!', 'success');
            fecharModalPagamento();
            carregarEncomendas();
        } else {
            mostrarMensagem('Erro ao atualizar status: ' + (data.message || 'Erro desconhecido'), 'error');
        }
    } catch(error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao atualizar status: ' + error.message, 'error');
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
    const cor = tipo === 'success' ? 'var(--success)' : 'var(--danger)';
    const bgCor = tipo === 'success' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)';
    
    container.innerHTML = `
        <div style="background-color: ${bgCor}; color: ${cor}; border-left: 4px solid ${cor};">
            ${texto}
        </div>
    `;
    
    setTimeout(() => {
        container.innerHTML = '';
    }, 4000);
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

// Fechar modais ao clicar fora
document.getElementById('modal-detalhes').addEventListener('click', function(e) {
    if(e.target === this) {
        fecharModal();
    }
});

document.getElementById('modal-status-producao').addEventListener('click', function(e) {
    if(e.target === this) {
        fecharModalProducao();
    }
});

document.getElementById('modal-status-pagamento').addEventListener('click', function(e) {
    if(e.target === this) {
        fecharModalPagamento();
    }
});

// Event listeners para os botões de confirmar
document.getElementById('btn-confirmar-producao').addEventListener('click', function() {
    confirmarStatusProducao();
});

document.getElementById('btn-cancelar-producao-modal').addEventListener('click', function() {
    fecharModalProducao();
});

document.getElementById('btn-fechar-modal-producao').addEventListener('click', function() {
    fecharModalProducao();
});

document.getElementById('btn-confirmar-pagamento').addEventListener('click', function() {
    confirmarStatusPagamento();
});

document.getElementById('btn-cancelar-pagamento-modal').addEventListener('click', function() {
    fecharModalPagamento();
});

document.getElementById('btn-fechar-modal-pagamento').addEventListener('click', function() {
    fecharModalPagamento();
});

// Carregar dados iniciais
carregarClientes();
carregarEncomendas();
</script>

<?php include('footer.php'); ?>

