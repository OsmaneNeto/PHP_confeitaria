<?php include('header.php'); ?>

<main class="container">
    <h2 class="titulo">📋 Gerenciar Encomendas</h2>
    
    <div class="botoes-menu" style="margin-bottom: 30px;">
        <button id="btn-nova-encomenda" class="btn">➕ Nova Encomenda</button>
        <button id="btn-pendentes-hoje" class="btn">📅 Pendentes Hoje</button>
        <button id="btn-estatisticas" class="btn">📊 Estatísticas</button>
    </div>

    <!-- Formulário para nova/editar encomenda -->
    <div id="form-nova-encomenda" style="display: none; margin-bottom: 30px;">
        <h3 id="titulo-form-encomenda">Nova Encomenda</h3>
        <form id="form-encomenda" class="formulario">
            <input type="hidden" id="encomenda_id" name="id">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label for="cliente_nome">Nome do Cliente:</label>
                    <input type="text" id="cliente_nome" name="cliente_nome" required>
                </div>
                <div>
                    <label for="cliente_telefone">Telefone:</label>
                    <input type="text" id="cliente_telefone" name="cliente_telefone" placeholder="(00) 00000-0000">
                </div>
            </div>

            <label for="cliente_email">E-mail:</label>
            <input type="email" id="cliente_email" name="cliente_email">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label for="receita_id_encomenda">Receita:</label>
                    <select id="receita_id_encomenda" name="receita_id" required>
                        <option value="">Selecione uma receita...</option>
                    </select>
                </div>
                <div>
                    <label for="quantidade_encomenda">Quantidade:</label>
                    <input type="number" id="quantidade_encomenda" name="quantidade" step="0.01" value="1" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                <div>
                    <label for="preco_unitario_encomenda">Preço Unitário (R$):</label>
                    <input type="number" id="preco_unitario_encomenda" name="preco_unitario" step="0.01" required>
                </div>
                <div>
                    <label for="preco_total_encomenda">Preço Total (R$):</label>
                    <input type="number" id="preco_total_encomenda" name="preco_total" step="0.01" readonly style="background-color: #f0f0f0;">
                </div>
                <div>
                    <label for="data_entrega">Data de Entrega:</label>
                    <input type="date" id="data_entrega" name="data_entrega" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label for="status_encomenda">Status:</label>
                    <select id="status_encomenda" name="status">
                        <option value="pendente">Pendente</option>
                        <option value="em_producao">Em Produção</option>
                        <option value="pronta">Pronta</option>
                        <option value="entregue">Entregue</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>
            </div>

            <label for="observacoes_encomenda">Observações:</label>
            <textarea id="observacoes_encomenda" name="observacoes" rows="3"></textarea>

            <button type="submit" class="btn-enviar" id="btn-salvar-encomenda">Salvar Encomenda</button>
            <button type="button" id="btn-cancelar-encomenda" class="btn" style="background-color: #6c757d;">Cancelar</button>
        </form>
    </div>

    <!-- Filtros -->
    <div style="margin-bottom: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 8px;">
        <label for="filtro-status">Filtrar por Status:</label>
        <select id="filtro-status" style="margin-left: 10px; padding: 5px;">
            <option value="">Todos</option>
            <option value="pendente">Pendente</option>
            <option value="em_producao">Em Produção</option>
            <option value="pronta">Pronta</option>
            <option value="entregue">Entregue</option>
            <option value="cancelada">Cancelada</option>
        </select>
    </div>

    <!-- Lista de encomendas -->
    <div id="lista-encomendas">
        <h3>Lista de Encomendas</h3>
        <div id="encomendas-container"></div>
    </div>

    <!-- Estatísticas -->
    <div id="estatisticas-container" style="margin-top: 30px; display: none;">
        <h3>📊 Estatísticas</h3>
        <div id="estatisticas-conteudo"></div>
    </div>

    <div id="mensagem"></div>
</main>

<script>
let encomendas = [];
let receitas = [];
let modoEdicao = false;

// Carregar receitas
async function carregarReceitas() {
    try {
        const response = await fetch('../api/receitas.php');
        const data = await response.json();
        
        if(data.success) {
            receitas = data.data;
            preencherSelectReceitas();
        }
    } catch(error) {
        console.error('Erro ao carregar receitas:', error);
    }
}

// Preencher select de receitas
function preencherSelectReceitas() {
    const select = document.getElementById('receita_id_encomenda');
    select.innerHTML = '<option value="">Selecione uma receita...</option>';
    receitas.forEach(receita => {
        const option = document.createElement('option');
        option.value = receita.id;
        option.textContent = `${receita.nome} - R$ ${parseFloat(receita.preco_venda_sugerido).toFixed(2)}`;
        option.dataset.preco = receita.preco_venda_sugerido;
        select.appendChild(option);
    });
}

// Carregar encomendas
async function carregarEncomendas(status = '') {
    try {
        const url = status ? `../api/encomendas.php?status=${status}` : '../api/encomendas.php';
        const response = await fetch(url);
        const data = await response.json();
        
        if(data.success) {
            encomendas = data.data;
            exibirEncomendas();
        }
    } catch(error) {
        console.error('Erro ao carregar encomendas:', error);
    }
}

// Exibir encomendas
function exibirEncomendas() {
    const container = document.getElementById('encomendas-container');
    container.innerHTML = '';

    if(encomendas.length === 0) {
        container.innerHTML = '<p>Nenhuma encomenda encontrada.</p>';
        return;
    }

    encomendas.forEach(encomenda => {
        const div = document.createElement('div');
        div.className = 'encomenda-card';
        
        const statusColors = {
            'pendente': '#fff3cd',
            'em_producao': '#cfe2ff',
            'pronta': '#d1e7dd',
            'entregue': '#d4edda',
            'cancelada': '#f8d7da'
        };
        
        div.style.cssText = `
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            background-color: ${statusColors[encomenda.status] || '#fff'};
        `;
        
        div.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div style="flex: 1;">
                    <h4>${encomenda.cliente_nome}</h4>
                    <p><strong>Receita:</strong> ${encomenda.receita_nome}</p>
                    <p><strong>Quantidade:</strong> ${encomenda.quantidade} | <strong>Preço Unitário:</strong> R$ ${parseFloat(encomenda.preco_unitario).toFixed(2)}</p>
                    <p><strong>Preço Total:</strong> R$ ${parseFloat(encomenda.preco_total).toFixed(2)}</p>
                    <p><strong>Data de Entrega:</strong> ${new Date(encomenda.data_entrega).toLocaleDateString()}</p>
                    <p><strong>Status:</strong> <span style="text-transform: capitalize;">${encomenda.status.replace('_', ' ')}</span></p>
                    ${encomenda.cliente_telefone ? `<p><strong>Telefone:</strong> ${encomenda.cliente_telefone}</p>` : ''}
                    ${encomenda.observacoes ? `<p><strong>Observações:</strong> ${encomenda.observacoes}</p>` : ''}
                </div>
                <div>
                    <button onclick="editarEncomenda(${encomenda.id})" class="btn" style="margin: 2px;">✏️ Editar</button>
                    <button onclick="atualizarStatusEncomenda(${encomenda.id}, '${encomenda.status}')" class="btn" style="margin: 2px;">🔄 Status</button>
                    <button onclick="excluirEncomenda(${encomenda.id})" class="btn" style="background-color: #dc3545; margin: 2px;">🗑️ Excluir</button>
                </div>
            </div>
        `;
        
        container.appendChild(div);
    });
}

// Editar encomenda
async function editarEncomenda(id) {
    try {
        const response = await fetch(`../api/encomendas.php?id=${id}`);
        const data = await response.json();
        
        if(data.success) {
            const enc = data.data;
            modoEdicao = true;
            
            document.getElementById('encomenda_id').value = enc.id;
            document.getElementById('cliente_nome').value = enc.cliente_nome;
            document.getElementById('cliente_telefone').value = enc.cliente_telefone || '';
            document.getElementById('cliente_email').value = enc.cliente_email || '';
            document.getElementById('receita_id_encomenda').value = enc.receita_id;
            document.getElementById('quantidade_encomenda').value = enc.quantidade;
            document.getElementById('preco_unitario_encomenda').value = enc.preco_unitario;
            document.getElementById('preco_total_encomenda').value = enc.preco_total;
            document.getElementById('data_entrega').value = enc.data_entrega;
            document.getElementById('status_encomenda').value = enc.status;
            document.getElementById('observacoes_encomenda').value = enc.observacoes || '';
            
            document.getElementById('titulo-form-encomenda').textContent = 'Editar Encomenda';
            document.getElementById('btn-salvar-encomenda').textContent = 'Atualizar Encomenda';
            document.getElementById('form-nova-encomenda').style.display = 'block';
        }
    } catch(error) {
        console.error('Erro ao carregar encomenda:', error);
        mostrarMensagem('Erro ao carregar encomenda para edição', 'error');
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
            body: JSON.stringify({ id: id })
        });
        
        const data = await response.json();
        
        if(data.success) {
            mostrarMensagem('Encomenda excluída com sucesso!', 'success');
            carregarEncomendas();
        } else {
            mostrarMensagem(data.message, 'error');
        }
    } catch(error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao excluir encomenda', 'error');
    }
}

// Atualizar status da encomenda
async function atualizarStatusEncomenda(id, statusAtual) {
    const statuses = ['pendente', 'em_producao', 'pronta', 'entregue', 'cancelada'];
    const indexAtual = statuses.indexOf(statusAtual);
    const proximoStatus = statuses[indexAtual + 1] || statuses[0];
    
    if(!confirm(`Alterar status de "${statusAtual.replace('_', ' ')}" para "${proximoStatus.replace('_', ' ')}"?`)) {
        return;
    }
    
    try {
        const response = await fetch('../api/encomendas.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                atualizar_status: true,
                id: id,
                status: proximoStatus
            })
        });
        
        const data = await response.json();
        
        if(data.success) {
            mostrarMensagem('Status atualizado com sucesso!', 'success');
            carregarEncomendas();
        } else {
            mostrarMensagem(data.message, 'error');
        }
    } catch(error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao atualizar status', 'error');
    }
}

// Calcular preço total
function calcularPrecoTotal() {
    const quantidade = parseFloat(document.getElementById('quantidade_encomenda').value) || 0;
    const precoUnitario = parseFloat(document.getElementById('preco_unitario_encomenda').value) || 0;
    const precoTotal = quantidade * precoUnitario;
    document.getElementById('preco_total_encomenda').value = precoTotal.toFixed(2);
}

// Salvar encomenda
document.getElementById('form-encomenda').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = {
        cliente_nome: document.getElementById('cliente_nome').value,
        cliente_telefone: document.getElementById('cliente_telefone').value,
        cliente_email: document.getElementById('cliente_email').value,
        receita_id: document.getElementById('receita_id_encomenda').value,
        quantidade: document.getElementById('quantidade_encomenda').value,
        preco_unitario: document.getElementById('preco_unitario_encomenda').value,
        preco_total: document.getElementById('preco_total_encomenda').value,
        data_entrega: document.getElementById('data_entrega').value,
        status: document.getElementById('status_encomenda').value,
        observacoes: document.getElementById('observacoes_encomenda').value
    };
    
    if(modoEdicao) {
        formData.id = document.getElementById('encomenda_id').value;
    }
    
    try {
        const method = modoEdicao ? 'PUT' : 'POST';
        const response = await fetch('../api/encomendas.php', {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if(data.success) {
            mostrarMensagem(modoEdicao ? 'Encomenda atualizada com sucesso!' : 'Encomenda criada com sucesso!', 'success');
            document.getElementById('form-nova-encomenda').style.display = 'none';
            document.getElementById('form-encomenda').reset();
            modoEdicao = false;
            document.getElementById('titulo-form-encomenda').textContent = 'Nova Encomenda';
            document.getElementById('btn-salvar-encomenda').textContent = 'Salvar Encomenda';
            carregarEncomendas();
        } else {
            mostrarMensagem(data.message, 'error');
        }
    } catch(error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao salvar encomenda', 'error');
    }
});

// Carregar estatísticas
async function carregarEstatisticas() {
    try {
        const response = await fetch('../api/encomendas.php?estatisticas=1');
        const data = await response.json();
        
        if(data.success) {
            const stats = data.data;
            const container = document.getElementById('estatisticas-conteudo');
            
            container.innerHTML = `
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div style="background-color: #e3f2fd; padding: 15px; border-radius: 8px;">
                        <h4>Total</h4>
                        <p style="font-size: 24px; font-weight: bold;">${stats.total_encomendas}</p>
                    </div>
                    <div style="background-color: #fff3e0; padding: 15px; border-radius: 8px;">
                        <h4>Pendentes</h4>
                        <p style="font-size: 24px; font-weight: bold;">${stats.encomendas_pendentes}</p>
                    </div>
                    <div style="background-color: #cfe2ff; padding: 15px; border-radius: 8px;">
                        <h4>Em Produção</h4>
                        <p style="font-size: 24px; font-weight: bold;">${stats.encomendas_em_producao}</p>
                    </div>
                    <div style="background-color: #d1e7dd; padding: 15px; border-radius: 8px;">
                        <h4>Prontas</h4>
                        <p style="font-size: 24px; font-weight: bold;">${stats.encomendas_prontas}</p>
                    </div>
                    <div style="background-color: #d4edda; padding: 15px; border-radius: 8px;">
                        <h4>Entregues</h4>
                        <p style="font-size: 24px; font-weight: bold;">${stats.encomendas_entregues}</p>
                    </div>
                    <div style="background-color: #f8d7da; padding: 15px; border-radius: 8px;">
                        <h4>Canceladas</h4>
                        <p style="font-size: 24px; font-weight: bold;">${stats.encomendas_canceladas}</p>
                    </div>
                    <div style="background-color: #d1ecf1; padding: 15px; border-radius: 8px;">
                        <h4>Valor Total</h4>
                        <p style="font-size: 24px; font-weight: bold;">R$ ${parseFloat(stats.valor_total).toFixed(2)}</p>
                    </div>
                    <div style="background-color: #d4edda; padding: 15px; border-radius: 8px;">
                        <h4>Valor Entregue</h4>
                        <p style="font-size: 24px; font-weight: bold;">R$ ${parseFloat(stats.valor_entregue).toFixed(2)}</p>
                    </div>
                </div>
            `;
        }
    } catch(error) {
        console.error('Erro ao carregar estatísticas:', error);
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
document.getElementById('btn-nova-encomenda').addEventListener('click', function() {
    modoEdicao = false;
    document.getElementById('form-encomenda').reset();
    document.getElementById('encomenda_id').value = '';
    document.getElementById('titulo-form-encomenda').textContent = 'Nova Encomenda';
    document.getElementById('btn-salvar-encomenda').textContent = 'Salvar Encomenda';
    document.getElementById('form-nova-encomenda').style.display = 'block';
});

document.getElementById('btn-cancelar-encomenda').addEventListener('click', function() {
    document.getElementById('form-nova-encomenda').style.display = 'none';
    document.getElementById('form-encomenda').reset();
    modoEdicao = false;
});

document.getElementById('btn-pendentes-hoje').addEventListener('click', function() {
    carregarEncomendas('pendente');
});

document.getElementById('btn-estatisticas').addEventListener('click', function() {
    const container = document.getElementById('estatisticas-container');
    if(container.style.display === 'none') {
        container.style.display = 'block';
        carregarEstatisticas();
    } else {
        container.style.display = 'none';
    }
});

document.getElementById('filtro-status').addEventListener('change', function() {
    const status = this.value;
    carregarEncomendas(status);
});

// Quando selecionar receita, preencher preço unitário
document.getElementById('receita_id_encomenda').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    if(option.dataset.preco) {
        document.getElementById('preco_unitario_encomenda').value = parseFloat(option.dataset.preco).toFixed(2);
        calcularPrecoTotal();
    }
});

// Calcular preço total quando quantidade ou preço unitário mudar
document.getElementById('quantidade_encomenda').addEventListener('input', calcularPrecoTotal);
document.getElementById('preco_unitario_encomenda').addEventListener('input', calcularPrecoTotal);

// Carregar dados iniciais
carregarReceitas();
carregarEncomendas();
</script>

<?php include('footer.php'); ?>

