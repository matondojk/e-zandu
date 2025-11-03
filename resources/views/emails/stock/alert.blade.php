<x-mail::message>
# ⚠️ Alerta de Stock Baixo

Prezado(a) Gestor(a),

O nosso sistema automático detetou **{{ $productCount }}** produtos cujo stock atual está abaixo do nível mínimo definido ({{ $minimumStock }} unidades).

Em anexo segue o relatório em PDF detalhado com a lista completa dos produtos e as suas respetivas quantidades atuais.

Por favor, reveja a lista para planear as próximas compras ou ajustes de produção.

<x-mail::button :url="url('/admin/products')">
Ver Produtos no Painel
</x-mail::button>

Obrigado,
{{ config('app.name') }} - Sistema de Gestão
</x-mail::message>
