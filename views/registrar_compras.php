<?php include('header.php'); ?>

<main class="container">
    <h2 class="titulo">🛒 Registrar Compra de Insumos</h2>
    
    <form id="form-compras" class="formulario" method="POST" action="">
        <label for="nome_produto">Nome do Produto:</label>
        <input type="text" id="nome_produto" name="nome_produto" required>

        <label for="quantidade">Quantidade (ex: 5kg, 2L, 10un):</label>
        <input type="text" id="quantidade" name="quantidade" required>

        <label for="preco_total">Preço Total (R$):</label>
        <input type="number" id="preco_total" name="preco_total" step="0.01" required>

        <button type="submit" class="btn-enviar">Registrar Compra</button>
    </form>

    <div id="mensagem"></div>
</main>

<script>
document.getElementById("form-compras").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const nome = document.getElementById("nome_produto").value;
    const qtd = document.getElementById("quantidade").value;
    const preco = document.getElementById("preco_total").value;
    
    document.getElementById("mensagem").innerHTML = `
        <p>✅ Compra registrada com sucesso!</p>
        <p><strong>Produto:</strong> ${nome}</p>
        <p><strong>Quantidade:</strong> ${qtd}</p>
        <p><strong>Preço Total:</strong> R$ ${parseFloat(preco).toFixed(2)}</p>
    `;
});
</script>

<?php include('footer.php'); ?>
