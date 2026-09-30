# Manual Unificado do Usuário — ProGest HGVC
**Perfis: Solicitante, Almoxarife e Administrador**

---

## Sumário
0. [Credenciais e Setores de Teste (Ambiente de Homologação)](#0-credenciais-e-setores-de-teste-ambiente-de-homologação)
1. [Classificação Arquitetural dos Setores no ProGest](#1-classificação-arquitetural-dos-setores-no-progest)
2. [Módulo I: Perfil Solicitante (Setores sem Estoque)](#2-módulo-i-perfil-solicitante-setores-sem-estoque)
   - 2.1 [Montagem e Envio da Solicitação Assistencial](#21-montagem-e-envio-da-solicitação-assistencial)
   - 2.2 [Ciclo de Vida do Pedido e Significado dos Status](#22-ciclo-de-vida-do-pedido-e-significado-dos-status)
   - 2.3 [Regras de Proteção, Rascunhos e Cancelamento](#23-regras-de-proteção-rascunhos-e-cancelamento)
   - 2.4 [Impressão e Devolução de Sobras Assistenciais](#24-impressão-e-devolução-de-sobras-assistenciais)
3. [Módulo II: Perfil Almoxarife (Setores com Estoque e CAF)](#3-módulo-ii-perfil-almoxarife-setores-com-estoque-e-caf)
   - 3.1 [Central de Movimentações e Fila de Triagem](#31-central-de-movimentações-e-fila-de-triagem)
   - 3.2 [Comportamento Específico na CAF vs Setores com Estoque](#32-comportamento-específico-na-caf-vs-setores-com-estoque)
   - 3.3 [Atendimento de Pedidos e Travas de Quantidade](#33-atendimento-de-pedidos-e-travas-de-quantidade)
   - 3.4 [Alocação Inteligente de Lotes (Regra FIFO)](#34-alocação-inteligente-de-lotes-regra-fifo)
   - 3.5 [Auditoria e Recebimento de Devoluções](#35-auditoria-e-recebimento-de-devoluções)
   - 3.6 [Transferências Setoriais e Consumo Interno](#36-transferências-setoriais-e-consumo-interno)
4. [Módulo III: Perfil Administrador (Gestão Global, Cadastros e Auditoria)](#4-módulo-iii-perfil-administrador-gestão-global-cadastros-e-auditoria)
   - 4.1 [Entrada de Notas Fiscais no Estoque (Exclusivo CAF)](#41-entrada-de-notas-fiscais-no-estoque-exclusivo-caf)
   - 4.2 [Cadastros Ágeis sem Interrupção (Quick Add)](#42-cadastros-ágeis-sem-interrupção-quick-add)
   - 4.3 [Gestão de Setores e Vínculo de Distribuidores Autorizados](#43-gestão-de-setores-e-vínculo-de-distribuidores-autorizados)
   - 4.4 [Gestão de Usuários, Perfis e Restrições de Vínculo](#44-gestão-de-usuários-perfis-e-restrições-de-vínculo)
   - 4.5 [Cadastros Estruturantes (Polos, Produtos, Fornecedores e Grupos)](#45-cadastros-estruturantes-polos-produtos-fornecedores-e-grupos)
   - 4.6 [Central de Relatórios: Objetivos de Negócio, Acessos e Portaria 344](#46-central-de-relatórios-objetivos-de-negócio-acessos-e-portaria-344)

---

## 0. Credenciais e Setores de Teste (Ambiente de Homologação)

> [!IMPORTANT]
> **Aviso de Ambiente de Testes:**  
> Os usuários, credenciais de acesso e setores apresentados a seguir são **exclusivos para o ambiente de testes, treinamento e homologação**. Em ambiente de produção real, cada colaborador utilizará suas credenciais institucionais e os setores refletirão a escala oficial de lotação do hospital.

### 🔑 Credenciais Disponíveis para Testes

| Perfil | Colaborador / Nome | E-mail de Login | Senha | Setores Vinculados para Teste |
| :--- | :--- | :--- | :---: | :--- |
| **Super Admin** | ADMIN TI | `adminti@gmail.com` | `adminti` | Acesso irrestrito a todos os polos, setores e cadastros. |
| **Admin Geral** | ADMINISTRADOR GERAL (DEMO) | `admin.geral@progest.teste` | `Admin123` | Administrador dos Polos HGVC e HAP (cadastros, setores e relatórios). |
| **Almoxarife CAF** | ALMOXARIFE CAF HGVC | `almoxarife.caf@progest.teste` | `Admin123` | **CAF - Farmácia Central** e **Farmácia Satélite CC** (HGVC). |
| **Solicitante UTI** | SOLICITANTE UTI ADULTO | `solicitante.uti@progest.teste` | `Admin123` | **UTI Adulto** (HGVC) — Setor sem estoque (assistencial). |
| **Almoxarife HAP** | ALMOXARIFE CENTRAL HAP | `almoxarife.hap@progest.teste` | `Admin123` | **Almoxarifado Central** e **Farmácia Satélite** (HAP). |
| **Solicitante HAP** | SOLICITANTE CLÍNICA HAP | `solicitante.hap@progest.teste` | `Admin123` | **Clínica Médica** (HAP) — Setor sem estoque (assistencial). |

---

### 🏥 Setores Populados para Homologação e Simulação

A base de testes conta com cenários hiper-realistas nos seguintes setores:

1. **Polo HGVC (Hospital Geral de Vitória da Conquista):**
   - **CAF - Farmácia Central** *(Setor CAF / com estoque)*: Setor mestre abastecido com notas fiscais de fornecedores, lotes vigentes e controlados (Portaria 344/98). Utilize para testar entradas de NF e atendimento de transferências.
   - **Farmácia Satélite Centro Cirúrgico** *(Setor com estoque)*: Farmácia intermediária. Utilize para testar atendimento de solicitações da UTI ou requisição de reposição para a CAF via **Fazer Pedido**.
   - **UTI Adulto** *(Setor sem estoque / assistencial)*: Unidade assistencial pura. Utilize para testar o perfil Solicitante, a busca cega no menu **Fazer Pedido** e a rotina de devolução de sobras.

2. **Polo HAP (Hospital Afrânio Peixoto):**
   - **Almoxarifado Central HAP** *(Setor com estoque)*: Estoque central do polo secundário.
   - **Farmácia Satélite HAP** *(Setor com estoque)*: Ponto de dispensação farmacêutica.
   - **Clínica Médica HAP** *(Setor sem estoque / assistencial)*: Enfermaria clínica para simulação de pedidos assistenciais.

---

## 1. Classificação Arquitetural dos Setores no ProGest

Para manter a integridade fiscal e o controle de rastreabilidade, o ProGest divide todos os setores do complexo hospitalar em exatamente **três categorias operacionais**:

```
┌──────────────────────────────────────────────────────────────────────────────┐
│ 1. CAF (Central de Abastecimento Farmacêutico)                               │
│    • Coração do abastecimento hospitalar.                                    │
│    • ÚNICO setor autorizado a registrar entradas de NF de fornecedores.      │
│    • Não realiza requisições internas (é o topo do abastecimento hospitalar).│
└──────────────────────────────────────┬───────────────────────────────────────┘
                                       │ Fornece via Transferência (T)
                                       ▼
┌──────────────────────────────────────────────────────────────────────────────┐
│ 2. Setores com Estoque (ex: Farmácias Satélites, Almoxarifados Locais)       │
│    • Possuem inventário físico local e controle de lotes.                    │
│    • Não registram entradas por NF externa de compras.                       │
│    • Abastecem-se exclusivamente requisitando transferências à CAF.          │
│    • Atendem e dispensam materiais para setores assistenciais.               │
└──────────────────────────────────────┬───────────────────────────────────────┘
                                       │ Fornece via Saída/Dispensação (S)
                                       ▼
┌──────────────────────────────────────────────────────────────────────────────┐
│ 3. Setores sem Estoque (ex: UTI, Enfermarias, Pronto Atendimento)            │
│    • Setores puramente consumidores/assistenciais.                           │
│    • Não realizam compras e não possuem controle de saldo permanente.        │
│    • Solicitam itens aos distribuidores autorizados para consumo direto.     │
│    • Podem apenas devolver sobras intactas de procedimentos à farmácia.      │
└──────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Módulo I: Perfil Solicitante (Setores sem Estoque)

O perfil **Solicitante** atende equipes de enfermagem e assistentes que atuam em **setores sem estoque**.

### 2.1. Montagem e Envio da Solicitação Assistencial
1. No menu lateral, clique em **Fazer Pedido**.
2. Na aba **Montar Pedido**, escolha o tipo de item: **Medicamento** ou **Material**.
3. No campo de busca, digite o nome do item ou código SIMPAS.
   > [!NOTE]
   > **Busca Cega de Estoque:** A tela oculta o saldo da farmácia para que a equipe requisite estritamente a necessidade clínica do paciente, sem indução de consumo.
4. Defina a quantidade e clique em **Adicionar**.
5. Na aba **Finalizar Pedido**:
   - **Setor Distribuidor**: Selecione o setor com estoque autorizado a fornecer para a sua unidade (conforme matriz de distribuidores definida pela administração).
   - **Setor de Destino (Você)**: Exibe automaticamente o seu setor assistencial ativo.
   - **Observação (opcional)**: Registro de leito, justificativa clínica ou urgência.
6. Escolha a forma de gravação:
   - **Salvar como Rascunho**: Salva os itens localmente para conferência posterior sem acionar o distribuidor.
   - **Finalizar Pedido**: Envia imediatamente o pedido para a fila de atendimento do distribuidor.

### 2.2. Ciclo de Vida do Pedido e Significado dos Status
Na aba **Histórico**, o ciclo de atendimento é acompanhado por selos coloridos:

| Status | Badge | Significado Clínico-Operacional |
| :--- | :---: | :--- |
| **Rascunho** | Cinza | Pedido salvo localmente pelo solicitante. Não debita estoque nem aparece para a farmácia. |
| **Aguardando Análise** | Amarelo | Pedido encaminhado com sucesso. Aguarda triagem e separação no distribuidor. |
| **Atendido** | Verde | Pedido aprovado e dispensado pela farmácia. Itens prontos para uso no setor. |
| **Negado** | Vermelho | Pedido recusado pelo distribuidor. O motivo registrado pode ser lido em **Obs:**. |
| **Cancelado** | Cinza Escuro | Solicitação cancelada pelo solicitante enquanto ainda aguardava análise. |

### 2.3. Regras de Proteção, Rascunhos e Cancelamento
- **Gestão de Rascunhos:** Na linha com status **Rascunho**, você pode:
  - **Editar** (ícone de lápis): Reabre os itens no carrinho para alterar quantidades ou adicionar produtos. Ao concluir, use **Salvar Rascunho** ou **Enviar Pedido**.
  - **Enviar** (ícone de envio direto): Transfere o rascunho para **Aguardando Análise**.
  - **Excluir** (ícone de lixeira): Remove definitivamente o rascunho.
- **Cancelamento de Pedidos Enviados:** O botão **Cancelar** (ícone **X**) só fica ativo no status **Aguardando Análise**. Exige confirmação na janela de alerta.
- **Trava de Separação:** Assim que a farmácia inicia a conferência e atende a requisição, o botão de cancelamento é bloqueado para impedir divergência entre o físico e o sistema.

### 2.4. Impressão e Devolução de Sobras Assistenciais
- **Imprimir Guia:** Clique no botão **Imprimir** (ícone de impressora) para gerar o comprovante de dispensação com os lotes recebidos.
- **Devolução de Sobras:** Se um medicamento lacrado não foi utilizado (alta do paciente ou suspensão de conduta médica):
  1. Localize o pedido **Atendido** no **Histórico** e clique em **Devolver** (botão âmbar).
  2. Indique o lote recebido, a quantidade intacta a restituir e a justificativa.
  3. A solicitação de devolução é encaminhada à farmácia para conferência física.

---

## 3. Módulo II: Perfil Almoxarife (Setores com Estoque e CAF)

O perfil **Almoxarife** gerencia o inventário físico, realiza a triagem de pedidos enviados pelos Solicitantes (Módulo I) e processa as transferências.

### 3.1. Central de Movimentações e Fila de Triagem
Na aba **Movimentações** do **Setor Atual**:
- As solicitações enviadas com status **Aguardando Análise** entram na aba **Movimentações Pendentes** como **Pendente**.
- A tabela identifica o número do pedido (`#ID`), data/hora, setor solicitante (destino) e responsável pela requisição.

### 3.2. Comportamento Específico na CAF vs Setores com Estoque
O comportamento da interface varia dependendo de onde o usuário está conectado:

| Ação Operacional | Na CAF (Central) | Em Setores com Estoque (Satélites) |
| :--- | :---: | :---: |
| **Registrar Entrada de NF** | **Disponível** (único local de compras externas) | **Oculto** (não recebe NF de compras) |
| **Origem das Requisições** | Atende pedidos de satélites e assistenciais | Requisita à CAF via menu **Fazer Pedido** |
| **Aba Movimentações** | Triagem e liberação de transferências/saídas | Triagem e liberação de dispensações locais |

### 3.3. Atendimento de Pedidos e Travas de Quantidade
1. Na linha da solicitação, clique em **Aprovar** (ícone de verificação verde) ou em **Ver detalhes** ➔ **Analisar e Liberar**.
2. Na janela **Análise de Solicitação**, revise a tabela **Conferência de Itens e Estoque**:
   - O campo **Liberar** inicia preenchido com a quantidade pedida pela enfermagem.

> [!WARNING]
> **Trava de Teto Máximo:**  
> A quantidade a **Liberar** nunca pode ser superior à quantidade solicitada, nem superior ao saldo real disponível em estoque no seu setor.  
> Valores além do permitido colorem o campo em vermelho (*"Máximo permitido: X"*) e impedem a aprovação.

- **Atendimento Parcial e Zeramento:** Se houver estoque parcial, digite o saldo que pode ser entregue. Se o item estiver em falta, digite `0` (o item é rotulado como *"Não liberado"*).
- **Trava de Atendimento Vazio:** O sistema proíbe aprovar pedidos com todos os itens zerados. Se o setor não puder entregar nenhum item, clique no botão **Rejeitar Integramente** para registrar a negativa e devolver o pedido ao Solicitante.
3. Clique em **Confirmar Liberação** para concluir a baixa e registrar o atendente na auditoria.

### 3.4. Alocação Inteligente de Lotes (Regra FIFO)
Ao informar as quantidades no campo **Liberar**, o sistema aciona a calculadora **FIFO** (*First In, First Out* / Primeiro a Vencer, Primeiro a Sair):
- Medicamentos com **data de vencimento mais próxima** são alocados prioritariamente para evitar descarte por expiração.
- **Divisão Automática de Lotes:** Se o lote mais antigo não tiver saldo suficiente, o sistema esgota o primeiro lote e consome o restante do próximo lote mais antigo, informando: **Lote**, **Vencimento**, **Qtd disponível** e **Qtd a usar**.
- Divergências entre saldo agregado e lotes físicos são sinalizadas pela tarja *"Cobertura insuficiente em lotes"*.

### 3.5. Auditoria e Recebimento de Devoluções
Quando a enfermagem envia uma devolução (conforme Seção 2.4):
1. O pedido aparece no painel do distribuidor com o selo verde **Devolução Recebida** e a indicação *Devolução referente ao Pedido #XX*.
2. Clique no botão **Aprovar** na linha da devolução para abrir o modal **Recebimento de Devolução**.
3. Inspecione a integridade física das embalagens e lacres.
4. No campo **Aceitar**, valide a quantidade apta a retornar ao estoque (ou digite `0` se o produto foi violado).
5. Clique em **Confirmar Recebimento**. O saldo e o lote retornam formalmente ao inventário central.

### 3.6. Transferências Setoriais e Consumo Interno
- **Requisitar Abastecimento à CAF:** Em setores com estoque (satélites), qualquer reposição de insumos da CAF é feita exclusivamente acessando o menu **Fazer Pedido** (`/pedidos`), selecionando a CAF no campo **Setor Distribuidor** e seguindo o fluxo padrão do Módulo I. A aba **Movimentações** é destinada estritamente à análise, conferência e liberação das solicitações geradas.
- **Baixa por Consumo Interno:** Utilizada para baixas diretas sem requisição (quebra acidental de ampolas, descarte por contaminação, desinfecção de bancada ou testes de controle):
  1. Na aba **Estoque**, abra a listagem de lotes do produto e clique em **Baixa / Consumo Interno** (ícone de caixa com seta para baixo).
  2. Informe a **Quantidade a Consumir** (limitada ao saldo do lote) e a **Justificativa (Opcional)**.
  3. Clique em **Confirmar Baixa**. A dedução física no lote é imediata.

---

## 4. Módulo III: Perfil Administrador (Gestão Global, Cadastros e Auditoria)

O perfil **Administrador** possui acesso irrestrito a todos os módulos, gerencia os cadastros mestres, controla acessos e extrai os relatórios estratégicos.

### 4.1. Entrada de Notas Fiscais no Estoque (Exclusivo CAF)
Como estabelecido na Seção 1, a entrada de mercadorias por nota fiscal de compra externa é uma operação **estritamente realizada na CAF**:
1. Conecte-se à **CAF** e acesse a aba **Estoque**.
2. Clique no botão **Registrar entrada de estoque** (ou **Nova Entrada**).
3. Preencha o número da **Nota fiscal** e selecione o **Fornecedor** (busca ágil por Razão Social ou CNPJ).
4. No painel de itens:
   - Selecione o **Produto**, digite o **Lote**, **Data fabricação (opcional)** e **Data vencimento** (obrigatória e futura).
   - Defina a **Quantidade**.
   - **Modalidade de Valor:**
     - **Por unidade**: Digita o valor unitário. O subtotal do item é obtido por `Quantidade × Valor Unitário`.
     - **Valor total**: Digita o valor total faturado do item. O sistema calcula a fração unitária com precisão de até 4 casas decimais (*"Unitário ≈ R$ X,XXXX"*), eliminando distorções de arredondamento em compras em grande escala.
   - Clique em **Adicionar à lista**.
5. Confira o resumo no rodapé da tabela (**Total de itens**, **Total de unidades** e **Valor total dos produtos**).
6. Clique em **Registrar entrada**. O estoque central da CAF é imediatamente creditado com os novos lotes.

### 4.2. Cadastros Ágeis sem Interrupção (*Quick Add*)
Durante o lançamento da nota fiscal, se for identificado um fornecedor ou produto novo, o operador não precisa abandonar o formulário:
- **Novo Fornecedor:** Clique no botão **`+`** ao lado do campo Fornecedor. Cadastre CNPJ/CPF, Razão Social e telefone. Ao salvar, a janela se fecha e **o novo fornecedor é selecionado automaticamente na nota**, sem apagar os itens já digitados.
- **Novo Produto:** Clique no botão **`+`** ao lado do campo Produto. Preencha nome, código SIMPAS oficial, grupo de produto, unidade de medida e lista da Portaria 344 (se controlado). Caso o grupo ou a unidade de medida não existam, botões **`+`** secundários permitem cadastrá-los em cascata. Ao salvar, o produto já é alocado na nota fiscal em andamento.

### 4.3. Gestão de Setores e Vínculo de Distribuidores Autorizados
No menu **Setores** (`/setores`), o administrador gerencia a estrutura hospitalar:

1. **Cadastro e Edição de Setor (`ModalSetor.vue`):**
   - **Polo / Unidade**: Vincula o setor a uma unidade hospitalar.
   - **Nome do Setor** e **Descrição**.
   - **Chave "Controle de Estoque":**
     - Se ativada (`estoque: true`), o setor torna-se um **Setor com Estoque** (exibindo abas de estoque, lotes e inventário).
     - Se desativada (`estoque: false`), o setor torna-se um **Setor sem Estoque** (puramente solicitante/assistencial).
   - **Tipo de Produto**: Material, Medicamento ou Ambos.

2. **Vincular Setores Distribuidores Autorizados:**
   - Na mesma tela do setor, na seção de distribuidores, o sistema lista apenas setores que controlam estoque (`s.estoque === true` ou CAF).
   - O administrador seleciona o distribuidor desejado e clica em **Adicionar** (ou botão **`+`**).
   > [!IMPORTANT]
   > **Impacto no Solicitante:**  
   > Os distribuidores vinculados nesta tela são **exatamente os que aparecem** para a equipe assistencial no campo **Setor Distribuidor** na aba **Finalizar Pedido**. Se um setor não tiver distribuidores vinculados aqui, os profissionais daquele setor não conseguirão concluir pedidos.

### 4.4. Gestão de Usuários, Perfis e Restrições de Vínculo
No menu **Usuários** (`/users`), o administrador gerencia os colaboradores e suas permissões:

1. **Criar e Editar Colaborador:**
   - Preencha nome completo, CPF (formatado e validado), e-mail corporativo, telefone, regime de contratação (CLT, Estatutário, PJ, etc.), status e senha inicial.

2. **Gestão de Vínculos Setoriais (`ModalGestaoVinculos.vue`):**
   - No ProGest, o perfil de acesso é atribuído **por setor**. O mesmo profissional pode ter permissões distintas em setores diferentes (ex: Solicitante na UTI e Almoxarife na Farmácia Satélite).
   - Clique no botão de gestão de vínculos do colaborador, escolha o **Setor** e o **Perfil** desejado (*Administrador*, *Almoxarife* ou *Solicitante*).

3. **Restrições de Governança Aplicadas pelo Sistema:**
   - **Trava de Almoxarife:** O perfil **Almoxarife** só pode ser vinculado a setores que possuem controle de estoque (`setor.estoque === true` ou CAF). Em setores sem estoque (assistenciais), a opção Almoxarife fica **bloqueada**, pois setores sem estoque só comportam o perfil Solicitante (ou Administrador local).
   - **Trava de Administrador:** Somente Super Administradores ou Administradores vinculados à CAF possuem privilégio para conceder o perfil de Administrador em setores.

### 4.5. Cadastros Estruturantes (Polos, Produtos, Fornecedores e Grupos)
O ProGest conta com painéis dedicados para cada entidade do sistema:
- **Polos (`/polos`):** Cadastro de unidades hospitalares, com código, nome, sigla e endereço.
- **Produtos (`/produtos`):** Catálogo mestre de medicamentos e materiais. Contempla o **Código SIMPAS** oficial do Estado da Bahia (padrão SAEB: `XX.XX.XX.XXXXXXXX-X`), código de barras (EAN), ponto de ressuprimento (estoque mínimo) e sinalização de controle especial.
- **Fornecedores (`/fornecedores`):** Fabricantes e distribuidores externos, com CNPJ/CPF, razão social, nome fantasia e contatos.
- **Grupos de Produto (`/grupos-produto`):** Categorias dos itens (Medicamento, Material Penso, Fios Cirúrgicos, etc.) vinculadas ao tipo de dispensação.
- **Unidades de Medida (`/unidades-medida`):** Formas de apresentação (Frasco, Ampola, Comprimido, Caixa, Bisnaga).

### 4.6. Central de Relatórios: Objetivos de Negócio, Acessos e Portaria 344
No menu lateral **Relatórios** (`/relatorios`), o sistema centraliza 8 relatórios estruturados para auditoria, fiscalização sanitária e planejamento de compras:

| Relatório | Rota | Objetivo de Negócio | Quem tem Acesso? | Formatos de Exportação |
| :--- | :--- | :--- | :---: | :---: |
| **Relatório de Estoque** | `/relatorios/estoque` | Visão consolidada do inventário: saldo físico, estoque mínimo, valorização e status de ruptura por setor/polo. | Admin e Almoxarife | Excel e PDF |
| **Entradas de Estoque** | `/relatorios/entradas` | Rastreamento de aquisições: auditoria de notas fiscais recebidas na CAF, fornecedores e lotes. | Admin e Almoxarife | Excel e PDF |
| **Movimentações Gerais** | `/relatorios/movimentacoes` | Extrato global de trânsito interno: transferências (`T`), saídas diretas (`S`) e devoluções (`D`) entre setores. | Admin e Almoxarife | Excel e PDF |
| **Saídas de Estoque** | `/relatorios/saidas` | Auditoria de dispensações: compara quantidades solicitadas vs liberadas e identifica os aprovadores. | Admin e Almoxarife | Excel e PDF |
| **Entradas por Data** | `/relatorios/entradas-por-data` | Análise temporal de recebimentos: visualiza padrões de entrega de fornecedores agrupados por dia/período. | Admin e Almoxarife | Excel e PDF |
| **Saídas por Data** | `/relatorios/saidas-por-data` | Análise do consumo diário em 3 níveis (Dia ➔ Produtos ➔ Movimentações), apoiando previsões de compras. | Admin e Almoxarife | Excel e PDF |
| **Medicamentos Controlados** | `/relatorios/medicamentos-controlados` | Conformidade sanitária com a **Portaria SVS/MS nº 344/98** e prestação de contas ao CRF-BA. | Admin e Almoxarife | Excel e PDF |
| **Relatório de Usuários** | `/relatorios/usuarios` | Governança e segurança: auditoria de contas ativas, regimes de contratação, setores e perfis. | **Exclusivo Admin** | Excel e PDF |

#### A. Destaque Regulatório: Relatório da Portaria SVS/MS nº 344/98
- **Cabeçalho Institucional Oficial:** Apresenta Razão Social da Unidade Hospitalar, Polo e Jurisdição (CRF-BA).
- **Filtros Sanitários:** Permite filtrar por Polo, Setor, Lista da Portaria (*A1*, *A2*, *B1*, *B2*, *C1*, etc.) e período em **Movimento de** / **Movimento até**.
- **Indicadores em Tempo Real:** Totalizadores de itens em estoque, movimentações de entrada e saída no período, alertas de estoque mínimo e contagem de lotes vencidos ou a vencer em 30 dias.
- **Rastreabilidade de Lotes:** Tabela expansível que exibe número de lote, código SIMPAS oficial, validade e dias para o vencimento, com documento timbrado pronto para assinatura do Farmacêutico Responsável Técnico.

#### B. Conceito de Consumo Líquido nos Relatórios de Saída
Integrando as saídas operadas pelo Almoxarife (Módulo II) e as devoluções enviadas pela Enfermagem (Módulo I), o ProGest segrega o consumo em colunas explícitas:
- **Consumo Bruto:** Total de itens dispensados pela farmácia (Saídas Diretas `S` + Transferências `T`).
- **Devoluções Recebidas (`D`):** Sobras lacradas que retornaram dos setores assistenciais.
- **Cálculo de Consumo Líquido Real:**
  $$\text{Consumo Líquido} = (\text{Saídas Diretas} + \text{Transferências}) - \text{Devoluções Recebidas}$$
Essa metodologia impede que devoluções de sobras distorçam o consumo real do paciente, fornecendo aos gestores o custo hospitalar fidedigno por clínica médica.
