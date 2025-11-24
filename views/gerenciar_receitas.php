<?php include('header.php'); ?>

<main class="container">
    <h2 class="titulo">👨‍🍳 Gerenciar Receitas</h2>
    
    <div class="botoes-menu" style="margin-bottom: 30px;">
        <button id="btn-nova-receita" class="btn">➕ Nova Receita</button>
        <button id="btn-produzir" class="btn">🏭 Registrar Produção</button>
        <button id="btn-validade" class="btn">📅 Controle de Validade</button>
        <button id="btn-estatisticas" class="btn">📊 Estatísticas</button>
    </div>

    <!-- Formulário para nova receita -->
    <div id="form-nova-receita" style="display: none; margin-bottom: 30px;">
        <h3>Cadastrar Nova Receita</h3>
        <form id="form-receita" class="formulario">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label for="nome">Nome da Receita:</label>
                    <input type="text" id="nome" name="nome" required>
                </div>
                <div>
                    <label for="categoria">Categoria:</label>
                    <select id="categoria" name="categoria">
                        <option value="">Selecione...</option>
                        <option value="Bolos">Bolos</option>
                        <option value="Cupcakes">Cupcakes</option>
                        <option value="Tortas">Tortas</option>
                        <option value="Doces">Doces</option>
                        <option value="Salgados">Salgados</option>
                    </select>
                </div>
            </div>

            <label for="descricao">Descrição:</label>
            <textarea id="descricao" name="descricao" rows="2"></textarea>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                <div>
                    <label for="rendimento">Rendimento:</label>
                    <input type="number" id="rendimento" name="rendimento" step="0.01" value="1" required>
                </div>
                <div>
                    <label for="unidade_rendimento">Unidade:</label>
                    <select id="unidade_rendimento" name="unidade_rendimento">
                        <option value="un">Unidade</option>
                        <option value="kg">Quilograma</option>
                        <option value="L">Litro</option>
                        <option value="fatia">Fatia</option>
                        <option value="porcao">Porção</option>
                    </select>
                </div>
                <div>
                    <label for="tempo_preparo">Tempo (min):</label>
                    <input type="number" id="tempo_preparo" name="tempo_preparo" min="0">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label for="dificuldade">Dificuldade:</label>
                    <select id="dificuldade" name="dificuldade">
                        <option value="facil">Fácil</option>
                        <option value="medio" selected>Médio</option>
                        <option value="dificil">Difícil</option>
                    </select>
                </div>
                <div>
                    <label for="margem_lucro">Margem de Lucro (%):</label>
                    <input type="number" id="margem_lucro" name="margem_lucro" step="0.01" value="30" min="0" max="100">
                </div>
            </div>

            <div id="calculos-receita" style="margin-top: 15px; padding: 15px; background-color: #f0f8ff; border-radius: 8px; display: none;">
                <h4>💰 Cálculos da Receita</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                    <div>
                        <label>Custo Total:</label>
                        <p style="font-weight: bold; color: #dc3545;">R$ <span id="custo-total-receita">0,00</span></p>
                    </div>
                    <div>
                        <label>Margem de Lucro:</label>
                        <p style="font-weight: bold; color: #28a745;"><span id="margem-lucro-receita">0</span>%</p>
                    </div>
                    <div>
                        <label>Preço de Venda:</label>
                        <p style="font-weight: bold; color: #007bff;">R$ <span id="preco-venda-receita">0,00</span></p>
                    </div>
                </div>
            </div>

            <label for="instrucoes">Instruções de Preparo:</label>
            <textarea id="instrucoes" name="instrucoes" rows="4"></textarea>

            <button type="submit" class="btn-enviar">Salvar Receita</button>
            <button type="button" id="btn-cancelar-receita" class="btn" style="background-color: #6c757d;">Cancelar</button>
        </form>
    </div>

    <!-- Formulário para adicionar ingredientes -->
    <div id="form-ingredientes" style="display: none; margin-bottom: 30px;">
        <h3>Adicionar Ingredientes à Receita</h3>
        <form id="form-ingrediente" class="formulario">
            <input type="hidden" id="receita_id_ingrediente" name="receita_id">
            
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 15px;">
                <div>
                    <label for="insumo_id">Insumo:</label>
                    <select id="insumo_id" name="insumo_id" required>
                        <option value="">Selecione um insumo...</option>
                    </select>
                </div>
                <div>
                    <label for="quantidade_ingrediente">Quantidade:</label>
                    <input type="number" id="quantidade_ingrediente" name="quantidade" step="0.001" required>
                </div>
                <div>
                    <label for="unidade_medida_ingrediente">Unidade:</label>
                    <select id="unidade_medida_ingrediente" name="unidade_medida" required>
                        <option value="kg">kg</option>
                        <option value="g">g</option>
                        <option value="L">L</option>
                        <option value="ml">ml</option>
                        <option value="un">un</option>
                        <option value="cx">cx</option>
                        <option value="pct">pct</option>
                    </select>
                </div>
                <div>
                    <label for="ordem">Ordem:</label>
                    <input type="number" id="ordem" name="ordem" min="1" value="1">
                </div>
            </div>

            <label for="observacoes_ingrediente">Observações:</label>
            <input type="text" id="observacoes_ingrediente" name="observacoes">

            <button type="submit" class="btn-enviar">Adicionar Ingrediente</button>
            <button type="button" id="btn-cancelar-ingrediente" class="btn" style="background-color: #6c757d;">Cancelar</button>
        </form>
    </div>

    <!-- Formulário para registrar produção -->
    <div id="form-producao" style="display: none; margin-bottom: 30px;">
        <h3>Registrar Produção</h3>
        <form id="form-producao-form" class="formulario">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label for="receita_id_producao">Receita:</label>
                    <select id="receita_id_producao" name="receita_id" required>
                        <option value="">Selecione uma receita...</option>
                    </select>
                </div>
                <div>
                    <label for="quantidade_produzida">Quantidade Produzida:</label>
                    <input type="number" id="quantidade_produzida" name="quantidade_produzida" step="0.01" required>
                </div>
            </div>

            <label for="observacoes_producao">Observações:</label>
            <textarea id="observacoes_producao" name="observacoes" rows="3"></textarea>

            <button type="submit" class="btn-enviar">Registrar Produção</button>
            <button type="button" id="btn-cancelar-producao" class="btn" style="background-color: #6c757d;">Cancelar</button>
        </form>
    </div>

    <!-- Lista de receitas -->
    <div id="lista-receitas">
        <h3>Lista de Receitas</h3>
        <div id="receitas-container"></div>
    </div>

    <!-- Controle de validade -->
    <div id="controle-validade" style="margin-top: 30px; display: none;">
        <h3>📅 Controle de Validade</h3>
        <div class="botoes-menu" style="margin-bottom: 20px;">
            <button id="btn-cadastrar-lote" class="btn">➕ Cadastrar Lote</button>
            <button id="btn-verificar-validade" class="btn">🔍 Verificar Validade</button>
        </div>

        <!-- Formulário para cadastrar lote -->
        <div id="form-lote" style="display: none; margin-bottom: 30px;">
            <h4>Cadastrar Novo Lote</h4>
            <form id="form-lote-form" class="formulario">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label for="insumo_id_lote">Insumo:</label>
                        <select id="insumo_id_lote" name="insumo_id" required>
                            <option value="">Selecione um insumo...</option>
                        </select>
                    </div>
                    <div>
                        <label for="lote">Número do Lote:</label>
                        <input type="text" id="lote" name="lote" placeholder="Ex: L001">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                    <div>
                        <label for="quantidade_lote">Quantidade do Lote:</label>
                        <input type="number" id="quantidade_lote" name="quantidade_lote" step="0.001" required>
                    </div>
                    <div>
                        <label for="data_fabricacao">Data de Fabricação:</label>
                        <input type="date" id="data_fabricacao" name="data_fabricacao">
                    </div>
                    <div>
                        <label for="data_validade">Data de Validade:</label>
                        <input type="date" id="data_validade" name="data_validade" required>
                    </div>
                </div>

                <label for="observacoes_lote">Observações:</label>
                <textarea id="observacoes_lote" name="observacoes" rows="2"></textarea>

                <button type="submit" class="btn-enviar">Cadastrar Lote</button>
                <button type="button" id="btn-cancelar-lote" class="btn" style="background-color: #6c757d;">Cancelar</button>
            </form>
        </div>

        <div id="lotes-container"></div>
        <div id="alertas-validade-container"></div>
    </div>

    <div id="mensagem"></div>
</main>

<script>
let receitas = [];
let insumos = [];
let lotes = [];
let alertasValidade = [];

// Carregar receitas
async function carregarReceitas() {
    try {
        const response = await fetch('../api/receitas.php');
        const data = await response.json();
        
        if(data.success) {
            receitas = data.data;
            exibirReceitas();
        }
    } catch(error) {
        console.error('Erro ao carregar receitas:', error);
    }
}

// Carregar insumos
async function carregarInsumos() {
    try {
        const response = await fetch('../api/insumos.php');
        const data = await response.json();
        
        if(data.success) {
            insumos = data.data;
            preencherSelectInsumos();
        }
    } catch(error) {
        console.error('Erro ao carregar insumos:', error);
    }
}

// Preencher selects de insumos
function preencherSelectInsumos() {
    const selects = ['insumo_id', 'insumo_id_lote'];
    selects.forEach(selectId => {
        const select = document.getElementById(selectId);
        if(select) {
            select.innerHTML = '<option value="">Selecione um insumo...</option>';
            insumos.forEach(insumo => {
                const option = document.createElement('option');
                option.value = insumo.id;
                option.textContent = `${insumo.nome} (${insumo.unidade_medida})`;
                select.appendChild(option);
            });
        }
    });
}

// Exibir receitas
function exibirReceitas() {
    const container = document.getElementById('receitas-container');
    container.innerHTML = '';

    receitas.forEach(receita => {
        const div = document.createElement('div');
        div.className = 'receita-card';
        div.style.cssText = `
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            background-color: #fff;
        `;
        
        div.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div style="flex: 1;">
                    <h4>${receita.nome}</h4>
                    <p><strong>Categoria:</strong> ${receita.categoria || 'Não definida'}</p>
                    <p><strong>Rendimento:</strong> ${receita.rendimento} ${receita.unidade_rendimento}</p>
                    <p><strong>Tempo:</strong> ${receita.tempo_preparo} min | <strong>Dificuldade:</strong> ${receita.dificuldade}</p>
                    <p><strong>Custo Total:</strong> R$ ${parseFloat(receita.custo_total).toFixed(2)}</p>
                    <p><strong>Margem de Lucro:</strong> ${parseFloat(receita.margem_lucro).toFixed(1)}%</p>
                    <p><strong>Preço de Venda:</strong> R$ ${parseFloat(receita.preco_venda_sugerido).toFixed(2)}</p>
                </div>
                <div>
                    <button onclick="editarReceita(${receita.id})" class="btn" style="margin: 2px;">✏️ Editar</button>
                    <button onclick="adicionarIngrediente(${receita.id})" class="btn" style="margin: 2px;">➕ Ingredientes</button>
                    <button onclick="verIngredientes(${receita.id})" class="btn" style="margin: 2px;">👁️ Ver</button>
                    <button onclick="calcularPrecosReceita(${receita.id}, ${receita.margem_lucro})" class="btn" style="margin: 2px;">💰 Calcular</button>
                    <button onclick="atualizarMargemLucro(${receita.id}, prompt('Nova margem de lucro (%):', ${receita.margem_lucro}))" class="btn" style="margin: 2px;">📊 Margem</button>
                    <button onclick="excluirReceita(${receita.id})" class="btn" style="background-color: #dc3545; margin: 2px;">🗑️ Excluir</button>
                </div>
            </div>
        `;
        
        container.appendChild(div);
    });
}

// Adicionar ingrediente à receita
async function adicionarIngrediente(receitaId) {
    document.getElementById('receita_id_ingrediente').value = receitaId;
    document.getElementById('form-ingredientes').style.display = 'block';
}

// Ver ingredientes da receita
async function verIngredientes(receitaId) {
    try {
        const response = await fetch(`../api/receitas.php?ingredientes=1&receita_id=${receitaId}`);
        const data = await response.json();
        
        if(data.success) {
            let ingredientesHtml = '<h4>Ingredientes:</h4><ul>';
            data.data.forEach(ingrediente => {
                ingredientesHtml += `<li>${ingrediente.quantidade} ${ingrediente.unidade_medida} de ${ingrediente.insumo_nome}</li>`;
            });
            ingredientesHtml += '</ul>';
            
            mostrarMensagem(ingredientesHtml, 'info');
        }
    } catch(error) {
        console.error('Erro ao carregar ingredientes:', error);
    }
}

// Salvar receita
document.getElementById('form-receita').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = {
        criar_receita: true,
        nome: document.getElementById('nome').value,
        descricao: document.getElementById('descricao').value,
        categoria: document.getElementById('categoria').value,
        rendimento: document.getElementById('rendimento').value,
        unidade_rendimento: document.getElementById('unidade_rendimento').value,
        tempo_preparo: document.getElementById('tempo_preparo').value,
        dificuldade: document.getElementById('dificuldade').value,
        instrucoes: document.getElementById('instrucoes').value,
        margem_lucro: document.getElementById('margem_lucro').value
    };
    
    try {
        const response = await fetch('../api/receitas.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if(data.success) {
            mostrarMensagem('Receita criada com sucesso!', 'success');
            document.getElementById('form-nova-receita').style.display = 'none';
            document.getElementById('form-receita').reset();
            carregarReceitas();
        } else {
            mostrarMensagem(data.message, 'error');
        }
    } catch(error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao criar receita', 'error');
    }
});

// Adicionar ingrediente
document.getElementById('form-ingrediente').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = {
        adicionar_ingrediente: true,
        receita_id: document.getElementById('receita_id_ingrediente').value,
        insumo_id: document.getElementById('insumo_id').value,
        quantidade: document.getElementById('quantidade_ingrediente').value,
        unidade_medida: document.getElementById('unidade_medida_ingrediente').value,
        observacoes: document.getElementById('observacoes_ingrediente').value,
        ordem: document.getElementById('ordem').value
    };
    
    try {
        const response = await fetch('../api/receitas.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if(data.success) {
            mostrarMensagem('Ingrediente adicionado com sucesso!', 'success');
            document.getElementById('form-ingredientes').style.display = 'none';
            document.getElementById('form-ingrediente').reset();
            carregarReceitas();
            
            // Recalcular preços automaticamente após adicionar ingrediente
            const receitaId = formData.receita_id;
            setTimeout(() => {
                calcularPrecosReceita(receitaId, 30); // Margem padrão de 30%
            }, 500);
        } else {
            mostrarMensagem(data.message, 'error');
        }
    } catch(error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao adicionar ingrediente', 'error');
    }
});

// Registrar produção
document.getElementById('form-producao-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = {
        registrar_producao: true,
        receita_id: document.getElementById('receita_id_producao').value,
        quantidade_produzida: document.getElementById('quantidade_produzida').value,
        observacoes: document.getElementById('observacoes_producao').value
    };
    
    try {
        const response = await fetch('../api/receitas.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if(data.success) {
            mostrarMensagem('Produção registrada com sucesso!', 'success');
            document.getElementById('form-producao').style.display = 'none';
            document.getElementById('form-producao-form').reset();
            carregarReceitas();
        } else {
            mostrarMensagem(data.message, 'error');
        }
    } catch(error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao registrar produção', 'error');
    }
});

// Cadastrar lote
document.getElementById('form-lote-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = {
        cadastrar_lote: true,
        insumo_id: document.getElementById('insumo_id_lote').value,
        lote: document.getElementById('lote').value,
        quantidade_lote: document.getElementById('quantidade_lote').value,
        data_fabricacao: document.getElementById('data_fabricacao').value,
        data_validade: document.getElementById('data_validade').value,
        observacoes: document.getElementById('observacoes_lote').value
    };
    
    try {
        const response = await fetch('../api/validade.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if(data.success) {
            mostrarMensagem('Lote cadastrado com sucesso!', 'success');
            document.getElementById('form-lote').style.display = 'none';
            document.getElementById('form-lote-form').reset();
            carregarLotes();
        } else {
            mostrarMensagem(data.message, 'error');
        }
    } catch(error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao cadastrar lote', 'error');
    }
});

// Carregar lotes
async function carregarLotes() {
    try {
        const response = await fetch('../api/validade.php');
        const data = await response.json();
        
        if(data.success) {
            lotes = data.data;
            exibirLotes();
        }
    } catch(error) {
        console.error('Erro ao carregar lotes:', error);
    }
}

// Exibir lotes
function exibirLotes() {
    const container = document.getElementById('lotes-container');
    container.innerHTML = '<h4>Lotes Cadastrados</h4>';

    lotes.forEach(lote => {
        const div = document.createElement('div');
        div.style.cssText = `
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            background-color: ${lote.status === 'vencido' ? '#f8d7da' : lote.status === 'proximo_vencer' ? '#fff3cd' : '#d4edda'};
        `;
        
        div.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <h5>${lote.insumo_nome} - Lote: ${lote.lote}</h5>
                    <p><strong>Quantidade:</strong> ${lote.quantidade_atual} ${lote.unidade_medida}</p>
                    <p><strong>Validade:</strong> ${new Date(lote.data_validade).toLocaleDateString()}</p>
                    <p><strong>Status:</strong> ${lote.status}</p>
                </div>
                <div>
                    <button onclick="excluirLote(${lote.id})" class="btn" style="background-color: #dc3545; margin: 2px;">🗑️ Excluir</button>
                </div>
            </div>
        `;
        
        container.appendChild(div);
    });
}

// Verificar alertas de validade
async function verificarAlertasValidade() {
    try {
        const response = await fetch('../api/validade.php?verificar_alertas=1');
        const data = await response.json();
        
        if(data.success) {
            mostrarMensagem(data.message, 'success');
            carregarAlertasValidade();
        }
    } catch(error) {
        console.error('Erro ao verificar alertas:', error);
    }
}

// Carregar alertas de validade
async function carregarAlertasValidade() {
    try {
        const response = await fetch('../api/validade.php?alertas=1');
        const data = await response.json();
        
        if(data.success) {
            alertasValidade = data.data;
            exibirAlertasValidade();
        }
    } catch(error) {
        console.error('Erro ao carregar alertas:', error);
    }
}

// Exibir alertas de validade
function exibirAlertasValidade() {
    const container = document.getElementById('alertas-validade-container');
    container.innerHTML = '<h4>Alertas de Validade</h4>';

    if(alertasValidade.length === 0) {
        container.innerHTML += '<p style="color: green;">✅ Nenhum alerta ativo!</p>';
        return;
    }

    alertasValidade.forEach(alerta => {
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
                    <h5>⚠️ ${alerta.tipo_alerta === 'vencido' ? 'LOTE VENCIDO' : 'PRÓXIMO AO VENCIMENTO'}</h5>
                    <p><strong>Insumo:</strong> ${alerta.insumo_nome}</p>
                    <p><strong>Lote:</strong> ${alerta.lote}</p>
                    <p><strong>Validade:</strong> ${new Date(alerta.data_validade).toLocaleDateString()}</p>
                    <p><strong>Quantidade:</strong> ${alerta.quantidade_atual} ${alerta.unidade_medida}</p>
                </div>
                <div>
                    <button onclick="marcarAlertaVisualizado(${alerta.id})" class="btn">✅ Visualizado</button>
                </div>
            </div>
        `;
        
        container.appendChild(div);
    });
}

// Marcar alerta como visualizado
async function marcarAlertaVisualizado(alertaId) {
    try {
        const response = await fetch('../api/validade.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                marcar_alerta_visualizado: true,
                alerta_id: alertaId
            })
        });
        
        const data = await response.json();
        
        if(data.success) {
            mostrarMensagem('Alerta marcado como visualizado', 'success');
            carregarAlertasValidade();
        } else {
            mostrarMensagem(data.message, 'error');
        }
    } catch(error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao marcar alerta', 'error');
    }
}

// Excluir receita
async function excluirReceita(receitaId) {
    if(confirm('Tem certeza que deseja excluir esta receita? Esta ação não pode ser desfeita.')) {
        try {
            const response = await fetch('../api/receitas.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: receitaId
                })
            });
            
            const data = await response.json();
            
            if(data.success) {
                mostrarMensagem('Receita excluída com sucesso!', 'success');
                carregarReceitas();
            } else {
                mostrarMensagem(data.message, 'error');
            }
        } catch(error) {
            console.error('Erro:', error);
            mostrarMensagem('Erro ao excluir receita', 'error');
        }
    }
}

// Calcular preços da receita
async function calcularPrecosReceita(receitaId, margemLucro) {
    if(receitaId && margemLucro >= 0) {
        try {
            const response = await fetch(`../api/receitas.php?calcular_preco=1&receita_id=${receitaId}&margem_lucro=${margemLucro}`);
            const data = await response.json();
            
            if(data.success) {
                document.getElementById('custo-total-receita').textContent = data.data.custo_total.toFixed(2);
                document.getElementById('margem-lucro-receita').textContent = data.data.margem_lucro.toFixed(1);
                document.getElementById('preco-venda-receita').textContent = data.data.preco_venda.toFixed(2);
                document.getElementById('calculos-receita').style.display = 'block';
            }
        } catch(error) {
            console.error('Erro ao calcular preços:', error);
        }
    }
}

// Atualizar margem de lucro de uma receita
async function atualizarMargemLucro(receitaId, margemLucro) {
    try {
        const response = await fetch('../api/receitas.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                atualizar_margem: true,
                receita_id: receitaId,
                margem_lucro: margemLucro
            })
        });
        
        const data = await response.json();
        
        if(data.success) {
            mostrarMensagem('Margem de lucro atualizada com sucesso!', 'success');
            carregarReceitas();
        } else {
            mostrarMensagem(data.message, 'error');
        }
    } catch(error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao atualizar margem de lucro', 'error');
    }
}

// Mostrar mensagem
function mostrarMensagem(texto, tipo) {
    const container = document.getElementById('mensagem');
    const cor = tipo === 'success' ? '#d4edda' : tipo === 'error' ? '#f8d7da' : '#d1ecf1';
    const textoCor = tipo === 'success' ? '#155724' : tipo === 'error' ? '#721c24' : '#0c5460';
    
    container.innerHTML = `
        <div style="background-color: ${cor}; color: ${textoCor}; padding: 15px; border-radius: 8px; margin-top: 20px;">
            ${texto}
        </div>
    `;
    
    setTimeout(() => {
        container.innerHTML = '';
    }, 5000);
}

// Event listeners
document.getElementById('btn-nova-receita').addEventListener('click', function() {
    document.getElementById('form-nova-receita').style.display = 'block';
});

document.getElementById('btn-cancelar-receita').addEventListener('click', function() {
    document.getElementById('form-nova-receita').style.display = 'none';
    document.getElementById('form-receita').reset();
});

document.getElementById('btn-cancelar-ingrediente').addEventListener('click', function() {
    document.getElementById('form-ingredientes').style.display = 'none';
    document.getElementById('form-ingrediente').reset();
});

document.getElementById('btn-produzir').addEventListener('click', function() {
    document.getElementById('form-producao').style.display = 'block';
    // Preencher select de receitas
    const select = document.getElementById('receita_id_producao');
    select.innerHTML = '<option value="">Selecione uma receita...</option>';
    receitas.forEach(receita => {
        const option = document.createElement('option');
        option.value = receita.id;
        option.textContent = receita.nome;
        select.appendChild(option);
    });
});

document.getElementById('btn-cancelar-producao').addEventListener('click', function() {
    document.getElementById('form-producao').style.display = 'none';
    document.getElementById('form-producao-form').reset();
});

document.getElementById('btn-validade').addEventListener('click', function() {
    const container = document.getElementById('controle-validade');
    if(container.style.display === 'none') {
        container.style.display = 'block';
        carregarLotes();
        carregarAlertasValidade();
    } else {
        container.style.display = 'none';
    }
});

document.getElementById('btn-cadastrar-lote').addEventListener('click', function() {
    document.getElementById('form-lote').style.display = 'block';
});

document.getElementById('btn-cancelar-lote').addEventListener('click', function() {
    document.getElementById('form-lote').style.display = 'none';
    document.getElementById('form-lote-form').reset();
});

document.getElementById('btn-verificar-validade').addEventListener('click', verificarAlertasValidade);

// Carregar dados iniciais
carregarReceitas();
carregarInsumos();

// Event listener para calcular preços quando margem de lucro muda
document.getElementById('margem_lucro').addEventListener('input', function() {
    const margemLucro = parseFloat(this.value) || 0;
    if(margemLucro > 0) {
        // Se há uma receita sendo editada, calcular preços
        const receitaId = document.getElementById('receita_id_ingrediente').value;
        if(receitaId) {
            calcularPrecosReceita(receitaId, margemLucro);
        }
    }
});
</script>

<?php include('footer.php'); ?>
