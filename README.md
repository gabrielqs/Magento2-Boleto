# Magento2-Boleto
Módulo Magento 2 para emissão de boletos, com geração de arquivos de remessa, e processamento de arquivos de retorno

## Instalação
Utilize o composer: `composer require gabrielqs/boleto`

## Configuração
Todas as configurações estão disponíveis juntamente com as demais opções de pagamento

## Utilização
Arquivos de remessa serão gerados diariamente. Eles estarão disponíveis em Vendas -> Arquivos de Remessa, e serão também enviados para os e-mails configurados em Lojas -> Configurações -> Métodos de Pagamento.

Arquivos de retorno devem ser enviados utilizando a seção Vendas -> Arquivos de Retorno. Serão criadas faturas para todos os pedidos pagos, constantes no arquivo de retorno, e que tenham valor igual ou superior ao valor do pedido.

## Bancos
Atualmente os bancos homologados são Bradesco e Itaú