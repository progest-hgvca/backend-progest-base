# Documentação Arquitetural: Fluxo de Movimentações, Pedidos e Rascunhos

## 1. Ausência de Tabela "Pedidos"
Na arquitetura do Progest, **não existe** uma tabela física ou entidade chamada `pedidos`. 
Todo o fluxo de solicitação de suprimentos, sejam eles pedidos entre setores, transferências, saídas ou abastecimentos, é centralizado e modelado através da entidade/tabela **`movimentacoes`**. 

A diferenciação lógica do que o usuário chama de "Pedido" dá-se pelo campo `tipo`:
- `tipo = 'S'` (Saída/Solicitação): Representa um "Pedido" feito por um setor (Setor Solicitante) a um estoque fornecedor/distribuidor (Setor Origem).
- `tipo = 'E'` (Entrada): Refere-se a registros diretos de entrada de notas fiscais/abastecimento primário.

Portanto, quando as regras de negócios, views Vue.js ou Controllers falarem sobre "Pedidos" ou "Solicitações", na camada de persistência trata-se de um registro de `Movimentacao` com `tipo = 'S'`.

## 2. O Ciclo de Vida do Rascunho (Status 'C')
O status de Rascunho foi introduzido para permitir que usuários montem pedidos longos de forma iterativa antes de enviá-los de fato à farmácia ou almoxarifado.

### 2.1. O que é o Rascunho?
Um Rascunho é um registro de `Movimentacao` criado na base de dados com o campo `status_solicitacao = 'C'` (Criado/Rascunho). 
Neste estágio:
- **Não há trava de estoque:** Os itens inseridos no rascunho não deduzem, reservam ou impactam o saldo atual do estoque do setor origem.
- **Visibilidade Estrita:** O rascunho é visível **apenas** para o setor solicitante. O setor de origem (CAF, Dispensação, Almoxarifado) não enxerga essa movimentação no seu painel de "Pedidos Recebidos" ou "Pendentes" enquanto o status for `C`.

### 2.2. Transição para Pendente (Envio do Pedido)
Quando o usuário clica em "Enviar Pedido" na interface:
1. O backend recebe a instrução para processar a movimentação.
2. O sistema altera o campo `status_solicitacao` de `'C'` (Rascunho) para `'P'` (Pendente).
3. **Ponto de Inflexão de Visibilidade:** A partir do momento em que se torna `'P'`, o pedido aparece na fila de atendimento do Setor Origem.
4. **Alocação/Reserva:** Somente após virar Pendente, o fluxo (se implementado com reserva prévia) ou o atendimento subsequente começam a considerar as quantidades demandadas.

### 2.3. Diagrama do Fluxo Básico de Status
1. **Rascunho ('C')** -> Criado pelo solicitante, itens podem ser adicionados/removidos, invisível ao fornecedor, sem impacto no saldo.
2. **Pendente ('P')** -> Solicitante submete o pedido, trava de edição habilitada para o solicitante, torna-se visível ao fornecedor.
3. **Atendido ('A') / Atendido Parcialmente / Cancelado ('X')** -> O fornecedor atua sobre o pedido aprovando e bipando itens (se houver), deduzindo definitivamente o estoque no fechamento do movimento (alteração do saldo nos lotes).
