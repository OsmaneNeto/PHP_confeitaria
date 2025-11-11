<?php include('header.php'); ?>

<main class="container">
    <h2 class="titulo">📦 Gerenciar Insumos</h2>
    
    <div class="botoes-menu" style="margin-bottom: 30px;">
        <button id="btn-novo-insumo" class="btn">➕ Novo Insumo</button>
        <button id="btn-verificar-alertas" class="btn">⚠️ Verificar Alertas</button>
        <button id="btn-estatisticas" class="btn">📊 Estatísticas</button>
    </div>

    <!-- Formulário para novo/editar insumo -->
    <div id="form-novo-insumo" style="display: none; margin-bottom: 30px;">
        <h3 id="titulo-form">Cadastrar Novo Insumo</h3>
        <form id="form-insumo" class="formulario">
            <input type="hidden" id="insumo_id" name="id">
            
            <label for="nome">Nome do Insumo:</label>
            <input type="text" id="nome" name="nome" required>

            <label for="descricao">Descrição:</label>
            <textarea id="descricao" name="descricao" rows="3"></textarea>

            <label for="unidade_compra">Unidade de Compra:</label>
            <select id="unidade_compra" name="unidade_compra" required>
                <option value="">Selecione...</option>
                <option value="kg">Quilograma (kg) - será armazenado em gramas</option>
                <option value="g">Grama (g)</option>
                <option value="L">Litro (L) - será armazenado em mililitros</option>
                <option value="ml">Mililitro (ml)</option>
                <option value="un">Unidade (un)</option>
                <option value="cx">Caixa (cx)</option>
                <option value="pct">Pacote (pct)</option>
            </select>
            <small style="color: #666;">Nota: Insumos em kg serão armazenados em gramas, e em L serão armazenados em ml</small>

            <label for="estoque_atual">Estoque Atual (na unidade de compra):</label>
            <input type="number" id="estoque_atual" name="estoque_atual" step="0.001" value="0">

            <label for="estoque_minimo">Estoque Mínimo (na unidade de compra):</label>
            <input type="number" id="estoque_minimo" name="estoque_minimo" step="0.001" value="0">

            <label for="custo_unitario_atual">Custo Unitário Atual (R$):</label>
            <input type="number" id="custo_unitario_atual" name="custo_unitario_atual" step="0.01" value="0">
            <small style="color: #666;">Preço por unidade de compra (ex: R$/kg, R$/L, R$/un)</small>

            <label for="categoria">Categoria:</label>
            <input type="text" id="categoria" name="categoria" placeholder="Ex: Ingredientes Básicos">

            <label for="fornecedor">Fornecedor:</label>
            <input type="text" id="fornecedor" name="fornecedor">

            <button type="submit" class="btn-enviar" id="btn-salvar">Salvar Insumo</button>
            <button type="button" id="btn-cancelar" class="btn" style="background-color: #6c757d;">Cancelar</button>
        </form>
    </div>

    <!-- Lista de insumos -->
    <div id="lista-insumos">
        <h3>Lista de Insumos</h3>
        <div id="insumos-container"></div>
    </div>

    <!-- Alertas -->
    <div id="alertas-container" style="margin-top: 30px;">
        <h3>⚠️ Alertas de Estoque</h3>
        <div id="alertas-lista"></div>
    </div>

    <!-- Estatísticas -->
    <div id="estatisticas-container" style="margin-top: 30px; display: none;">
        <h3>📊 Estatísticas</h3>
        <div id="estatisticas-conteudo"></div>
    </div>

    <div id="mensagem"></div>
</main>

<script>
let insumos = [];
let alertas = [];
let modoEdicao = false;

// Função para converter valor de exibição (g/ml para kg/L)
function converterParaExibicao(valor, unidade_compra, fator_conversao) {
    if(unidade_compra === 'kg' && fator_conversao === 1000) {
        return (valor / 1000).toFixed(3);
    } else if(unidade_compra === 'L' && fator_conversao === 1000) {
        return (valor / 1000).toFixed(3);
    }
    return parseFloat(valor).toFixed(3);
}

// Função para obter unidade de exibição
function obterUnidadeExibicao(unidade_compra, fator_conversao) {
    if(unidade_compra === 'kg' && fator_conversao === 1000) {
        return 'kg';
    } else if(unidade_compra === 'L' && fator_conversao === 1000) {
        return 'L';
    }
    return unidade_compra;
}

// Carregar insumos
async function carregarInsumos() {
    try {
        const response = await fetch('../api/insumos.php');
        const data = await response.json();
        
        if(data.success) {
            insumos = data.data;
            exibirInsumos();
        }
    } catch(error) {
        console.error('Erro ao carregar insumos:', error);
    }
}

// Exibir insumos na tela
function exibirInsumos() {
    const container = document.getElementById('insumos-container');
    container.innerHTML = '';

    insumos.forEach(insumo => {
        const div = document.createElement('div');
        div.className = 'insumo-card';
        
        // Converter valores para exibição
        const estoqueExibicao = converterParaExibicao(insumo.estoque_atual, insumo.unidade_compra, insumo.fator_conversao);
        const minimoExibicao = converterParaExibicao(insumo.estoque_minimo, insumo.unidade_compra, insumo.fator_conversao);
        const unidadeExibicao = obterUnidadeExibicao(insumo.unidade_compra, insumo.fator_conversao);
        
        div.style.cssText = `
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            background-color: ${parseFloat(insumo.estoque_atual) <= parseFloat(insumo.estoque_minimo) ? '#fff3cd' : '#fff'};
        `;
        
        div.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <h4>${insumo.nome}</h4>
                    <p><strong>Categoria:</strong> ${insumo.categoria || 'Não definida'}</p>
                    <p><strong>Estoque:</strong> ${estoqueExibicao} ${unidadeExibicao}</p>
                    <p><strong>Mínimo:</strong> ${minimoExibicao} ${unidadeExibicao}</p>
                    <p><strong>Custo Unitário:</strong> R$ ${parseFloat(insumo.custo_unitario_atual).toFixed(2)}/${unidadeExibicao}</p>
                    <p><strong>Fornecedor:</strong> ${insumo.fornecedor || 'Não informado'}</p>
                </div>
                <div>
                    <button onclick="editarInsumo(${insumo.id})" class="btn" style="margin: 2px;">✏️ Editar</button>
                    <button onclick="excluirInsumo(${insumo.id})" class="btn" style="background-color: #dc3545; margin: 2px;">🗑️ Excluir</button>
                </div>
            </div>
        `;
        
        container.appendChild(div);
    });
}

// Editar insumo
async function editarInsumo(id) {
    try {
        const response = await fetch(`../api/insumos.php?id=${id}`);
        const data = await response.json();
        
        if(data.success) {
            const insumo = data.data;
            modoEdicao = true;
            
            // Preencher formulário
            document.getElementById('insumo_id').value = insumo.id;
            document.getElementById('nome').value = insumo.nome;
            document.getElementById('descricao').value = insumo.descricao || '';
            document.getElementById('unidade_compra').value = insumo.unidade_compra;
            document.getElementById('custo_unitario_atual').value = insumo.custo_unitario_atual;
            document.getElementById('categoria').value = insumo.categoria || '';
            document.getElementById('fornecedor').value = insumo.fornecedor || '';
            
            // Converter valores para exibição
            const estoqueExibicao = converterParaExibicao(insumo.estoque_atual, insumo.unidade_compra, insumo.fator_conversao);
            const minimoExibicao = converterParaExibicao(insumo.estoque_minimo, insumo.unidade_compra, insumo.fator_conversao);
            
            document.getElementById('estoque_atual').value = estoqueExibicao;
            document.getElementById('estoque_minimo').value = minimoExibicao;
            
            document.getElementById('titulo-form').textContent = 'Editar Insumo';
            document.getElementById('btn-salvar').textContent = 'Atualizar Insumo';
            document.getElementById('form-novo-insumo').style.display = 'block';
        }
    } catch(error) {
        console.error('Erro ao carregar insumo:', error);
        mostrarMensagem('Erro ao carregar insumo para edição', 'error');
    }
}

// Excluir insumo
async function excluirInsumo(id) {
    if(!confirm('Tem certeza que deseja excluir este insumo? Esta ação não pode ser desfeita.')) {
        return;
    }
    
    try {
        const response = await fetch('../api/insumos.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        });
        
        const data = await response.json();
        
        if(data.success) {
            mostrarMensagem('Insumo excluído com sucesso!', 'success');
            carregarInsumos();
        } else {
            mostrarMensagem(data.message, 'error');
        }
    } catch(error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao excluir insumo', 'error');
    }
}

// Verificar alertas
async function verificarAlertas() {
    try {
        const response = await fetch('../api/alertas.php?verificar_alertas=1');
        const data = await response.json();
        
        if(data.success) {
            mostrarMensagem(`Verificação concluída! ${data.data.alertas_gerados} novos alertas gerados.`, 'success');
            carregarAlertas();
        }
    } catch(error) {
        console.error('Erro ao verificar alertas:', error);
        mostrarMensagem('Erro ao verificar alertas', 'error');
    }
}

// Carregar alertas
async function carregarAlertas() {
    try {
        const response = await fetch('../api/alertas.php?nao_visualizados=1');
        const data = await response.json();
        
        if(data.success) {
            alertas = data.data;
            exibirAlertas();
        }
    } catch(error) {
        console.error('Erro ao carregar alertas:', error);
    }
}

// Exibir alertas
function exibirAlertas() {
    const container = document.getElementById('alertas-lista');
    
    if(alertas.length === 0) {
        container.innerHTML = '<p style="color: green;">✅ Nenhum alerta ativo!</p>';
        return;
    }
    
    container.innerHTML = '';
    
    alertas.forEach(alerta => {
        const div = document.createElement('div');
        div.style.cssText = `
            border: 1px solid #dc3545;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            background-color: #f8d7da;
        `;
        
        div.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <h4>⚠️ ${alerta.tipo_alerta === 'estoque_zerado' ? 'ESTOQUE ZERADO' : 'ESTOQUE MÍNIMO'}</h4>
                    <p><strong>Insumo:</strong> ${alerta.insumo_nome}</p>
                    <p><strong>Estoque Atual:</strong> ${alerta.quantidade_atual} ${alerta.unidade_compra || 'un'}</p>
                    <p><strong>Estoque Mínimo:</strong> ${alerta.quantidade_minima} ${alerta.unidade_compra || 'un'}</p>
                    <p><strong>Data do Alerta:</strong> ${new Date(alerta.data_alerta).toLocaleString()}</p>
                </div>
                <div>
                    <button onclick="marcarAlertaVisualizado(${alerta.id})" class="btn">✅ Marcar como Visualizado</button>
                </div>
            </div>
        `;
        
        container.appendChild(div);
    });
}

// Carregar estatísticas
async function carregarEstatisticas() {
    try {
        const response = await fetch('../api/alertas.php?estatisticas=1');
        const data = await response.json();
        
        if(data.success) {
            const stats = data.data;
            const container = document.getElementById('estatisticas-conteudo');
            
            container.innerHTML = `
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div style="background-color: #e3f2fd; padding: 15px; border-radius: 8px;">
                        <h4>Total de Alertas</h4>
                        <p style="font-size: 24px; font-weight: bold;">${stats.total_alertas}</p>
                    </div>
                    <div style="background-color: #fff3e0; padding: 15px; border-radius: 8px;">
                        <h4>Não Visualizados</h4>
                        <p style="font-size: 24px; font-weight: bold;">${stats.alertas_nao_visualizados}</p>
                    </div>
                    <div style="background-color: #fce4ec; padding: 15px; border-radius: 8px;">
                        <h4>Estoque Zerado</h4>
                        <p style="font-size: 24px; font-weight: bold;">${stats.alertas_estoque_zerado}</p>
                    </div>
                    <div style="background-color: #f3e5f5; padding: 15px; border-radius: 8px;">
                        <h4>Estoque Mínimo</h4>
                        <p style="font-size: 24px; font-weight: bold;">${stats.alertas_estoque_minimo}</p>
                    </div>
                </div>
            `;
        }
    } catch(error) {
        console.error('Erro ao carregar estatísticas:', error);
    }
}

// Salvar insumo
document.getElementById('form-insumo').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const unidadeCompra = document.getElementById('unidade_compra').value;
    let estoqueAtual = parseFloat(document.getElementById('estoque_atual').value) || 0;
    let estoqueMinimo = parseFloat(document.getElementById('estoque_minimo').value) || 0;
    
    // Converter para armazenamento (kg -> g, L -> ml)
    if(unidadeCompra === 'kg') {
        estoqueAtual = estoqueAtual * 1000;
        estoqueMinimo = estoqueMinimo * 1000;
    } else if(unidadeCompra === 'L') {
        estoqueAtual = estoqueAtual * 1000;
        estoqueMinimo = estoqueMinimo * 1000;
    }
    
    const formData = {
        nome: document.getElementById('nome').value,
        descricao: document.getElementById('descricao').value,
        unidade_compra: unidadeCompra,
        estoque_atual: estoqueAtual,
        estoque_minimo: estoqueMinimo,
        custo_unitario_atual: document.getElementById('custo_unitario_atual').value,
        categoria: document.getElementById('categoria').value,
        fornecedor: document.getElementById('fornecedor').value
    };
    
    // Se estiver editando, adicionar ID e usar PUT
    if(modoEdicao) {
        formData.id = document.getElementById('insumo_id').value;
    }
    
    try {
        const method = modoEdicao ? 'PUT' : 'POST';
        const response = await fetch('../api/insumos.php', {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if(data.success) {
            mostrarMensagem(modoEdicao ? 'Insumo atualizado com sucesso!' : 'Insumo cadastrado com sucesso!', 'success');
            document.getElementById('form-novo-insumo').style.display = 'none';
            document.getElementById('form-insumo').reset();
            modoEdicao = false;
            document.getElementById('titulo-form').textContent = 'Cadastrar Novo Insumo';
            document.getElementById('btn-salvar').textContent = 'Salvar Insumo';
            carregarInsumos();
        } else {
            mostrarMensagem(data.message, 'error');
        }
    } catch(error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao salvar insumo', 'error');
    }
});

// Marcar alerta como visualizado
async function marcarAlertaVisualizado(alertaId) {
    try {
        const response = await fetch('../api/alertas.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                marcar_visualizado: true,
                alerta_id: alertaId
            })
        });
        
        const data = await response.json();
        
        if(data.success) {
            mostrarMensagem('Alerta marcado como visualizado', 'success');
            carregarAlertas();
        } else {
            mostrarMensagem(data.message, 'error');
        }
    } catch(error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao marcar alerta', 'error');
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
document.getElementById('btn-novo-insumo').addEventListener('click', function() {
    modoEdicao = false;
    document.getElementById('form-insumo').reset();
    document.getElementById('insumo_id').value = '';
    document.getElementById('titulo-form').textContent = 'Cadastrar Novo Insumo';
    document.getElementById('btn-salvar').textContent = 'Salvar Insumo';
    document.getElementById('form-novo-insumo').style.display = 'block';
});

document.getElementById('btn-cancelar').addEventListener('click', function() {
    document.getElementById('form-novo-insumo').style.display = 'none';
    document.getElementById('form-insumo').reset();
    modoEdicao = false;
});

document.getElementById('btn-verificar-alertas').addEventListener('click', verificarAlertas);

document.getElementById('btn-estatisticas').addEventListener('click', function() {
    const container = document.getElementById('estatisticas-container');
    if(container.style.display === 'none') {
        container.style.display = 'block';
        carregarEstatisticas();
    } else {
        container.style.display = 'none';
    }
});

// Carregar dados iniciais
carregarInsumos();
carregarAlertas();
</script>

<?php include('footer.php'); ?>
