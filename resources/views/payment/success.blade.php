<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-g">
    <title>Pagamento Confirmado</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 20px auto; padding: 20px; text-align: center; border: 1px solid #25D366; border-radius: 8px; }
        h1 { color: #128C7E; }
    </style>
</head>
<body>
    <h1>✅ Pagamento Recebido!</h1>
    <p>Obrigado! O seu pagamento para o Pedido #{{ $cart->id }} foi recebido com sucesso.</p>
    <p>Enviámos a fatura final para o seu WhatsApp.</p>
    <p>Pode fechar esta janela.</p>
</body>
</html>