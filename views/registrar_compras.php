<?php include('header.php'); ?>

<main class="container">
    <h2 class="titulo">🛒 Registrar Compra de Insumos</h2>
    
    <form id="form-compras" class="formulario" method="POST" action="">
        <label for="insumo_id">Insumo:</label>
        <select id="insumo_id" name="insumo_id" required>
            <option value="">Selecione um insumo...</option>
        </select>

        <label for="quantidade">Quantidade:</label>
        <input type="number" id="quantidade" name="quantidade" step="0.001" required>

        <label for="preco_total">Preço Total (R$):</label>
        <input type="number" id="preco_total" name="preco_total" step="0.01" required>

        <label for="data_compra">Data da Compra:</label>
        <input type="date" id="data_compra" name="data_compra" value="<?php echo date('Y-m-d'); ?>" required>

        <label for="data_validade">Data de Validade (opcional):</label>
        <input type="date" id="data_validade" name="data_validade">

        <button type="submit" class="btn-enviar">Registrar Compra</button>
    </form>

    <div id="mensagem"></div>
    <div id="custo-unitario" style="margin-top: 20px; padding: 15px; background-color: #f0f8ff; border-radius: 8px; display: none;">
        <h3>💰 Cálculo de Custo</h3>
        <p><strong>Custo Unitário:</strong> R$ <span id="custo-unitario-valor">0,00</span></p>
    </div>
</main>

<script>
// Carregar lista de insumos
async function carregarInsumos() {
    try {
        const response = await fetch('../api/insumos.php');
        const data = await response.json();
        
        if(data.success) {
            const select = document.getElementById('insumo_id');
            data.data.forEach(insumo => {
                const option = document.createElement('option');
                option.value = insumo.id;
                option.textContent = `${insumo.nome} (${insumo.unidade_medida}) - Estoque: ${insumo.estoque_atual}`;
                select.appendChild(option);
            });
        }
    } catch(error) {
        console.error('Erro ao carregar insumos:', error);
    }
}

// Calcular custo unitário em tempo real
function calcularCustoUnitario() {
    const quantidade = parseFloat(document.getElementById('quantidade').value) || 0;
    const precoTotal = parseFloat(document.getElementById('preco_total').value) || 0;
    
    if(quantidade > 0 && precoTotal > 0) {
        const custoUnitario = precoTotal / quantidade;
        document.getElementById('custo-unitario-valor').textContent = custoUnitario.toFixed(2);
        document.getElementById('custo-unitario').style.display = 'block';
    } else {
        document.getElementById('custo-unitario').style.display = 'none';
    }
}

// Registrar compra
document.getElementById("form-compras").addEventListener("submit", async function(e) {
    e.preventDefault();
    
    // Calcular custo unitário
    const quantidade = parseFloat(document.getElementById('quantidade').value) || 0;
    const precoTotal = parseFloat(document.getElementById('preco_total').value) || 0;
    const custoUnitario = quantidade > 0 ? precoTotal / quantidade : 0;
    
    const formData = {
        id_insumo: document.getElementById('insumo_id').value,
        quantidade_compra: quantidade,
        custo_unitario: custoUnitario,
        data_compra: document.getElementById('data_compra').value,
        data_validade: document.getElementById('data_validade').value || null
    };
    
    try {
        const response = await fetch('../api/compras.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        // Verificar se a resposta é válida
        const text = await response.text();
        if(!text || text.trim() === '') {
            throw new Error('Resposta vazia do servidor');
        }
        
        let data;
        try {
            data = JSON.parse(text);
        } catch(parseError) {
            console.error('Erro ao fazer parse do JSON:', parseError);
            console.error('Resposta recebida:', text);
            throw new Error('Resposta inválida do servidor. Verifique o console.');
        }
        
        if(data.success) {
            document.getElementById("mensagem").innerHTML = `
                <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-top: 20px;">
                    <h3>✅ Compra registrada com sucesso!</h3>
                    <p><strong>Custo Unitário Calculado:</strong> R$ ${data.data.custo_unitario.toFixed(2)}</p>
                    <p><strong>ID da Compra:</strong> ${data.data.id}</p>
                </div>
            `;
            
            // Limpar formulário
            document.getElementById("form-compras").reset();
            document.getElementById('data_compra').value = new Date().toISOString().split('T')[0];
            document.getElementById('custo-unitario').style.display = 'none';
            
            // Recarregar lista de insumos para atualizar estoque
            carregarInsumos();
        } else {
            document.getElementById("mensagem").innerHTML = `
                <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-top: 20px;">
                    <h3>❌ Erro ao registrar compra</h3>
                    <p>${data.message}</p>
                </div>
            `;
        }
    } catch(error) {
        console.error('Erro:', error);
        let errorMessage = 'Não foi possível conectar com o servidor.';
        
        // Tentar obter mais informações do erro
        if(error.message) {
            errorMessage += ' ' + error.message;
        }
        
        document.getElementById("mensagem").innerHTML = `
            <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-top: 20px;">
                <h3>❌ Erro de conexão</h3>
                <p>${errorMessage}</p>
                <p style="font-size: 0.875rem; margin-top: 10px;">Verifique o console do navegador para mais detalhes.</p>
            </div>
        `;
    }
});

// Event listeners para cálculo em tempo real
document.getElementById('quantidade').addEventListener('input', calcularCustoUnitario);
document.getElementById('preco_total').addEventListener('input', calcularCustoUnitario);

// Carregar insumos ao inicializar a página
carregarInsumos();
</script>

<?php include('footer.php'); ?>
