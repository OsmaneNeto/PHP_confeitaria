<?php include('header.php'); ?>

<main class="container">
    <h2 class="titulo">👨‍🍳 Gerenciar Receitas</h2>
    
    <div class="botoes-menu" style="margin-bottom: 30px;">
        <button id="btn-nova-receita" class="btn">➕ Nova Receita</button>
        <button id="btn-validade" class="btn">📅 Controle de Validade</button>
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
                    <label for="rendimento">Rendimento (unidades):</label>
                    <input type="number" id="rendimento" name="rendimento" step="1" value="1" min="1" required>
                </div>
            </div>

            <div>
                <label for="margem_lucro">Margem de Lucro (%):</label>
                <input type="number" id="margem_lucro" name="margem_lucro" step="0.01" value="30" min="0" max="100">
                <small style="color: var(--gray-500); font-size: 0.875rem;">A margem de lucro será aplicada sobre o custo unitário da receita</small>
            </div>

            <div id="calculos-receita" style="margin-top: 15px; padding: 15px; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); border-radius: var(--radius); display: none; color: white;">
                <h4 style="color: white; margin-bottom: 1rem;">💰 Cálculos da Receita</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                    <div>
                        <label style="color: rgba(255,255,255,0.9);">Custo Total:</label>
                        <p style="font-weight: bold; font-size: 1.125rem;">R$ <span id="custo-total-receita">0,00</span></p>
                    </div>
                    <div>
                        <label style="color: rgba(255,255,255,0.9);">Margem de Lucro:</label>
                        <p style="font-weight: bold; font-size: 1.125rem;"><span id="margem-lucro-receita">0</span>%</p>
                    </div>
                    <div>
                        <label style="color: rgba(255,255,255,0.9);">Preço de Venda:</label>
                        <p style="font-weight: bold; font-size: 1.125rem;">R$ <span id="preco-venda-receita">0,00</span></p>
                    </div>
                </div>
            </div>

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
                    <h4>${receita.nome_receita || receita.nome}</h4>
                    <p><strong>Rendimento:</strong> ${receita.rendimento_receita || receita.rendimento} unidades</p>
                    <p><strong>Custo Total:</strong> R$ ${parseFloat(receita.custo_total_mp || receita.custo_total || 0).toFixed(2)}</p>
                    <p><strong>Custo Unitário:</strong> R$ ${parseFloat(receita.custo_unitario || 0).toFixed(2)}</p>
                    <p><strong>Taxa de Lucro:</strong> ${parseFloat((receita.taxa_lucro_receita || receita.margem_lucro / 100 || 0) * 100).toFixed(1)}%</p>
                    <p><strong>Preço de Venda:</strong> R$ ${parseFloat(receita.preco_venda_sugerido || 0).toFixed(2)}</p>
                </div>
                <div>
                    <button onclick="editarReceita(${receita.id_receita || receita.id})" class="btn" style="margin: 2px;">✏️ Editar</button>
                    <button onclick="adicionarIngrediente(${receita.id_receita || receita.id})" class="btn" style="margin: 2px;">➕ Ingredientes</button>
                    <button onclick="verIngredientes(${receita.id_receita || receita.id})" class="btn" style="margin: 2px;">👁️ Ver Detalhes</button>
                    <button onclick="excluirReceita(${receita.id_receita || receita.id})" class="btn" style="background-color: #dc3545; margin: 2px;">🗑️ Excluir</button>
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
        const response = await fetch(`../api/receitas.php?id=${receitaId}`);
        
        if(!response.ok) {
            mostrarMensagem('Erro ao carregar receita: ' + response.statusText, 'error');
            return;
        }
        
        const data = await response.json();
        console.log('Dados da receita recebidos:', data);
        
        if(data.success && data.data) {
            const receita = data.data;
            const ingredientes = receita.ingredientes || [];
            
            let ingredientesHtml = '<div style="max-height: 400px; overflow-y: auto;">';
            // Tentar múltiplas formas de obter o nome
            const nomeReceita = receita.nome_receita || receita.nome || receita.data?.nome_receita || 'Receita #' + (receita.id_receita || receita.id || receitaId);
            ingredientesHtml += `<h4 style="margin-bottom: 1rem; color: var(--primary);">${nomeReceita}</h4>`;
            ingredientesHtml += '<h5 style="margin-bottom: 0.75rem; color: var(--gray-700);">Ingredientes:</h5>';
            
            if(ingredientes.length > 0) {
                ingredientesHtml += '<table style="width: 100%; border-collapse: collapse;">';
                ingredientesHtml += '<thead><tr style="background-color: var(--gray-100);">';
                ingredientesHtml += '<th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid var(--gray-300);">Insumo</th>';
                ingredientesHtml += '<th style="padding: 0.75rem; text-align: right; border-bottom: 2px solid var(--gray-300);">Quantidade</th>';
                ingredientesHtml += '<th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid var(--gray-300);">Unidade</th>';
                ingredientesHtml += '</tr></thead><tbody>';
                
                ingredientes.forEach(ingrediente => {
                    ingredientesHtml += '<tr style="border-bottom: 1px solid var(--gray-200);">';
                    ingredientesHtml += `<td style="padding: 0.75rem;">${ingrediente.nome_insumo || ingrediente.insumo_nome || 'N/A'}</td>`;
                    ingredientesHtml += `<td style="padding: 0.75rem; text-align: right;">${parseFloat(ingrediente.quantidade_gasta_insumo || ingrediente.quantidade || 0).toFixed(3)}</td>`;
                    ingredientesHtml += `<td style="padding: 0.75rem;">${ingrediente.unidade_medida || 'un'}</td>`;
                    ingredientesHtml += '</tr>';
                });
                
                ingredientesHtml += '</tbody></table>';
            } else {
                ingredientesHtml += '<p style="color: var(--gray-500); font-style: italic;">Nenhum ingrediente cadastrado para esta receita.</p>';
            }
            
            ingredientesHtml += '</div>';
            
            // Criar modal para exibir ingredientes
            mostrarModalIngredientes(ingredientesHtml);
        } else {
            mostrarMensagem('Erro ao carregar ingredientes: ' + (data.message || 'Erro desconhecido'), 'error');
        }
    } catch(error) {
        console.error('Erro ao carregar ingredientes:', error);
        mostrarMensagem('Erro ao carregar ingredientes da receita', 'error');
    }
}

// Função para mostrar modal de ingredientes
function mostrarModalIngredientes(conteudo) {
    // Remover modal existente se houver
    const modalExistente = document.getElementById('modal-ingredientes');
    if(modalExistente) {
        modalExistente.remove();
    }
    
    // Criar modal
    const modal = document.createElement('div');
    modal.id = 'modal-ingredientes';
    modal.style.cssText = 'display: block; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 1000; animation: fadeIn 0.2s;';
    
    modal.innerHTML = `
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: var(--radius); max-width: 700px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: var(--shadow-lg);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3>Detalhes da Receita</h3>
                <button type="button" id="btn-fechar-modal-ingredientes" class="btn" style="background-color: #6c757d;">✕ Fechar</button>
            </div>
            <div id="conteudo-ingredientes">${conteudo}</div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Event listener para fechar
    document.getElementById('btn-fechar-modal-ingredientes').addEventListener('click', function() {
        modal.remove();
    });
    
    // Fechar ao clicar fora
    modal.addEventListener('click', function(e) {
        if(e.target === modal) {
            modal.remove();
        }
    });
}

// Salvar receita
document.getElementById('form-receita').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const idEditar = document.getElementById('receita_id_editar')?.value;
    const isEdit = idEditar && idEditar > 0;
    
    const formData = {
        criar_receita: !isEdit,
        nome_receita: document.getElementById('nome').value,
        nome: document.getElementById('nome').value,
        rendimento_receita: parseInt(document.getElementById('rendimento').value) || 1,
        rendimento: parseInt(document.getElementById('rendimento').value) || 1,
        margem_lucro: parseFloat(document.getElementById('margem_lucro').value) || 0,
        taxa_lucro_receita: (parseFloat(document.getElementById('margem_lucro').value) || 0) / 100
    };
    
    if(isEdit) {
        formData.id_receita = idEditar;
    }
    
    try {
        const response = await fetch('../api/receitas.php', {
            method: isEdit ? 'PUT' : 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if(data.success) {
            mostrarMensagem(isEdit ? 'Receita atualizada com sucesso!' : 'Receita criada com sucesso!', 'success');
            document.getElementById('form-nova-receita').style.display = 'none';
            document.getElementById('form-receita').reset();
            const idInput = document.getElementById('receita_id_editar');
            if(idInput) idInput.remove();
            carregarReceitas();
        } else {
            mostrarMensagem(data.message || 'Erro ao salvar receita', 'error');
        }
    } catch(error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao salvar receita', 'error');
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
                // Cálculo de preços removido
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

// Editar receita
async function editarReceita(id) {
    try {
        const response = await fetch(`../api/receitas.php?id=${id}`);
        const data = await response.json();
        
        if(data.success) {
            const receita = data.data;
            document.getElementById('nome').value = receita.nome_receita || receita.nome || '';
            document.getElementById('rendimento').value = receita.rendimento_receita || receita.rendimento || 1;
            document.getElementById('margem_lucro').value = (receita.taxa_lucro_receita || receita.margem_lucro / 100 || 0) * 100;
            
            // Criar um campo hidden para o ID
            let idInput = document.getElementById('receita_id_editar');
            if(!idInput) {
                idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.id = 'receita_id_editar';
                document.getElementById('form-receita').appendChild(idInput);
            }
            idInput.value = receita.id_receita || receita.id;
            
            document.getElementById('form-nova-receita').style.display = 'block';
            document.getElementById('form-nova-receita').scrollIntoView({ behavior: 'smooth' });
        } else {
            mostrarMensagem('Erro ao carregar dados da receita', 'error');
        }
    } catch(error) {
        console.error('Erro:', error);
        mostrarMensagem('Erro ao carregar dados da receita', 'error');
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
                    id_receita: receitaId,
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

// Código de produção removido

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

// Event listener para margem de lucro removido
</script>

<?php include('footer.php'); ?>
