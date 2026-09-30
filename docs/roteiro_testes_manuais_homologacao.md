# Guia Prático de Testes Manuais de Homologação — Sistema ProGest

Este guia foi elaborado a partir de uma varredura completa no código-fonte real do **ProGest** (frontend Vue 3 + backend Laravel + banco MySQL). Ele substitui roteiros teóricos genéricos por testes objetivos com as **rotas, credenciais, setores, regras de permissão e regras de integridade realmente implementadas no sistema**.

---

## 📋 Tabela de Credenciais Oficiais para Testes (Release 1.0.0)

| Perfil | Nome no Sistema | E-mail de Acesso | Senha Padrão | Setores Autorizados no Banco |
|---|---|---|---|---|
| **Solicitante UTI** | SOLICITANTE UTI ADULTO | `solicitante.uti@progest.teste` | `Admin123` | **UTI Adulto (Exemplo)** (Polo HGVC — setor assistencial) |
| **Almoxarife CAF** | ALMOXARIFE CAF HGVC | `almoxarife.caf@progest.teste` | `Admin123` | **CAF (Exemplo)** e **Farmácia Satélite CC (Exemplo)** |
| **Solicitante HAP** | SOLICITANTE CLÍNICA HAP | `solicitante.hap@progest.teste` | `Admin123` | **Clínica Médica HAP (Exemplo)** (Polo HAP) |
| **Almoxarife HAP** | ALMOXARIFE CENTRAL HAP | `almoxarife.hap@progest.teste` | `Admin123` | **Almoxarifado Central HAP (Exemplo)** e **Farmácia Satélite HAP (Exemplo)** |
| **Admin Geral** | ADMINISTRADOR GERAL (DEMO) | `admin.geral@progest.teste` | `Admin123` | Setores de exemplo + Gestão de Polos e Relatórios |
| **Super Admin** | ADMIN TI (Produção) | `adminti@gmail.com` | `adminti` | Bypass irrestrito (CRUD de Polos, Auditoria e Configurações Globais) |

> [!TIP]
> **Como zerar e carregar a base de demonstração do zero:**
> ```bash
> # Via Docker:
> docker exec backend-progest-base-progest-api-1 php artisan migrate:fresh --seed
> docker exec backend-progest-base-progest-api-1 php artisan db:seed --class=DemonstracaoSistemaSeeder
> 
> # Ou via Terminal Local:
> php artisan migrate:fresh --seed
> php artisan db:seed --class=DemonstracaoSistemaSeeder
> ```
> O comando `migrate:fresh --seed` faz o drop de todas as tabelas (zerando completamente o banco) e recria a estrutura oficial limpa. Em seguida, o `DemonstracaoSistemaSeeder` carrega os cenários hospitalares com 100% de consistência entre estoque físico, lotes e histórico de movimentações.

---

## 🧪 ROTEIRO 1: Ciclo Operacional Completo (Solicitação, Atendimento, Consumo e Devolução)

**Objetivo:** Validar o fluxo de ponta a ponta de um medicamento desde o pedido do enfermeiro até a devolução do que sobrou ao almoxarifado.

* **Navegador A (Solicitante):** `solicitante.uti@progest.teste` / `Admin123`  
  *Setor Selecionado:* **UTI Adulto (Exemplo)** (sem estoque físico próprio).
* **Navegador B (Almoxarife):** `almoxarife.caf@progest.teste` / `Admin123`  
  *Setor Selecionado:* **CAF - Central de Abastecimento Farmacêutico (Exemplo)**.

---

### Passo 1: Criação da Solicitação & Validação de Interface (Navegador A)
1. Faça login no Navegador A como `solicitante.uti@progest.teste` (senha `Senha@123`). Na tela `/setor-selection`, selecione o setor **UTI Adulto (Exemplo)**.
2. Clique no menu lateral **"Fazer Pedido"** (rota `/pedidos`).
3. **Validação do Tutorial Contextual:**
   - No canto inferior direito, clique no botão flutuante **`?`**.
   - Verifique se abre o cartão flutuante com o título **"Central de Pedidos e Requisições"**, finalidade e os 5 passos operacionais.
   - Clique no botão de fechar (`X`) ou fora dele e certifique-se de que a tela não trava nem oculta botões vitais.
4. **Distribuidor Autorizado:**
   - Na aba "Montar Pedido", verifique o campo de seleção de distribuidor. Para a UTI Adulto (Exemplo), o sistema lista os distribuidores autorizados: **CAF - Central de Abastecimento Farmacêutico (Exemplo)** e **Farmácia Satélite Centro Cirúrgico (Exemplo)**. Selecione a **CAF**.
5. **Busca Cega e Inclusão no Carrinho:**
   - No campo de busca de produtos, digite `Dipirona`.
   - Observe a **Requisição Cega**: o sistema **não** exibe o saldo disponível no distribuidor.
   - Localize `DIPIRONA, sodica 500 mg/ml ampola 2mL` (Cód. SIMPAS: `65.02.19.00001234-9`).
   - Informe a quantidade desejada (ex: `50`) e adicione ao carrinho.
6. **Envio da Solicitação:**
   - Abra o carrinho/resumo do pedido, informe uma observação (ex: *"Reposição de plantão noturno"*) e clique em **"Enviar Pedido"**.
   - O sistema emitirá toast de sucesso e redirecionará para a aba **"Histórico de Pedidos"**.
   - Verifique que o pedido aparece com o status **Pendente** (badge amarelo/laranja). Mantenha essa tela visível.

---

### Passo 2: Atendimento com Bloqueio Anti-Negativo, FIFO e Trava de Teto (Navegador B)
1. No Navegador B, logue como `almoxarife.caf@progest.teste` (senha `Senha@123`) e selecione **CAF - Central de Abastecimento Farmacêutico (Exemplo)**.
2. Acesse a rota `/setor-atual` e clique na aba **"Movimentações"**.
3. Na sub-aba **"Pendentes"**, localize o pedido recém-criado pela UTI Adulto (Exemplo).
4. Clique em **"Visualizar / Atender"**.
5. **Prévia Reativa FIFO:**
   - Observe o card de prévia dos lotes que serão debitados: o sistema consulta `/preview-lotes` e lista os lotes ordenados por data de vencimento mais próxima.
6. **Trava de Teto de Solicitação:**
   - Tente alterar a quantidade liberada para um valor maior que a quantidade solicitada (ex: `60` para um pedido de `50`).
   - Observe a validação e bloqueio impedindo liberação superior ao requisitado.
7. **Confirmação do Atendimento:**
   - Mantenha a quantidade liberada válida e confirme o atendimento. O estoque será deduzido dos lotes via FIFO rigoroso.
5. **Teste de Borda 1 (Quantidades Inválidas):**
   - No campo "Qtd. Liberada", tente informar `0` ou valor negativo (`-5`) e clique em aprovar.
   - **Resultado Esperado:** O sistema bloqueia a aprovação e exige uma quantidade estritamente maior que zero.
6. **Teste de Borda 2 (Estoque Insuficiente):**
   - Tente liberar uma quantidade manifestamente superior ao saldo do lote em estoque (ex: `99999`).
   - **Resultado Esperado:** Mensagem clara informando saldo insuficiente no lote/setor.
7. **Aprovação Real:**
   - Informe uma quantidade liberada válida (ex: `10` unidades).
   - O sistema sugere o lote com vencimento mais próximo (critério **FIFO**).
   - Clique em **"Aprovar Solicitação"**.
8. **Validação de Auditoria ("Aprovado por"):**
   - Abra os detalhes da movimentação aprovada.
   - Verifique se consta explicitamente o nome de quem aprovou: **Arthur Almoxarife**.
9. **Sincronização no Solicitante:**
   - Volte ao Navegador A (UTI 3), recarregue o Histórico de Pedidos: o pedido agora exibe status **Atendido / Aprovado** e exibe quem aprovou.

---

### Passo 3: Consumo Interno e Integridade de Lote (Navegador B)
1. No Navegador B (`arthuralmoxarife@gmail.com`), acesse a aba **"Estoque"** (`/setor-atual`).
2. Localize a `Dipirona`. Clique em **"Ver Lotes"**. Anote o saldo atual e o código do lote.
3. Clique no botão **"Consumo Interno"** (Baixa Imediata):
   - **Teste de Borda:** Selecione o lote e digite uma quantidade maior que o saldo do lote. Tente salvar: o sistema bloqueia e alerta sobre falta de saldo.
   - **Consumo Válido:** Selecione o lote, informe `2` unidades e informe a justificativa *"Ampola danificada no transporte"*.
   - Confirme a baixa.
4. **Verificação:**
   - O saldo do lote diminui imediatamente em exatas 2 unidades.
   - Na listagem de movimentações, o registro foi gerado com tipo **C** (Consumo Interno), sem afetar os pedidos de outros setores.

---

### Passo 4: Devolução pelo Solicitante com Recomposição de Estoque (A → B)
1. No Navegador A (UTI 3), abra o **"Histórico de Pedidos"** e localize o pedido atendido no Passo 2.
2. Clique no botão **"Devolver"** (ícone âmbar `mdi-keyboard-return`).
3. O modal **"Devolução do Pedido"** se abrirá:
   - Verifique se os produtos e os números de lote originais são exibidos corretamente na tabela.
   - **Teste de Borda 1:** Deixe as quantidades em `0` e tente enviar. O botão *"Enviar Solicitação"* permanece desabilitado com alerta em âmbar.
   - **Teste de Borda 2:** Digite uma quantidade maior do que a atendida (ex: `15` para `10` atendidas). O campo fica com borda vermelha e alerta informando que a devolução não pode exceder o liberado.
   - **Devolução Válida:** Informe `3` unidades no campo "A Devolver", preencha o motivo *"Sobra de procedimento cirúrgico"* e clique em **"Enviar Solicitação"**.
4. O modal fecha e exibe mensagem de sucesso.
5. No Navegador B (Almoxarife na Farmácia de Dispensação):
   - Vá na aba **"Movimentações"**. Surge uma solicitação de **Devolução (`D`)** aguardando recebimento.
   - Abra e aprove o recebimento da devolução.
   - Volte à aba **"Estoque"** e confira os lotes: o saldo daquele lote específico foi **recomposto com exatamente +3 unidades**.

---

## 🧪 ROTEIRO 2: Matriz de Permissões, Gestão de Usuários e Produtos

**Objetivo:** Garantir que usuários comuns não acessem telas administrativas e que a atribuição de perfis respeite as restrições sanitárias do hospital.

* **Navegador A (Admin Geral):** `pabloadmin@gmail.com` / `Admin123`
* **Navegador B (Almoxarife):** `arthuralmoxarife@gmail.com` / `Admin123`

---

### Passo 1: Vínculo de Usuários e Regra de Estoque Obrigatório (Navegador A)
1. No Navegador A, acesse o menu lateral **"Usuários"** (`/users`).
2. Localize o usuário `Arthur Almoxarife` e clique em **"Editar Setores"** ou **"Vincular Setores"**.
3. **Teste da Regra de Negócio (Bloqueio Sanitário):**
   - Tente associar `Arthur Almoxarife` com o perfil `almoxarife` em um setor **SEM estoque físico** (ex: `CLÍNICA MÉDICA` ou `UTI 1`).
   - Tente salvar.
   - **Resultado Esperado:** O sistema recusa o vínculo e emite alerta: *"Almoxarifes só podem ser vinculados a setores que controlam estoque ativo."*
4. **Vínculo Válido:**
   - Selecione um setor **COM estoque** (ex: `SATÉLITE DA EMERGÊNCIA`).
   - Salve a alteração: a operação é concluída com sucesso sem erros de permissão.

---

### Passo 2: Gestão de Produtos pelo Administrador (Navegador A)
1. No Navegador A, acesse o menu **"Cadastros > Produtos"** (`/produtos`).
2. Clique em **"Novo Produto"**.
3. Preencha os dados:
   - **Nome:** `SORO FISIOLÓGICO 0,9% BOLSA 500ML - TESTE HOMOLOGAÇÃO`
   - **Código SIMPAS / SIMPASS:** `650219009999001`
   - **Grupo de Produto:** Selecione `Medicamentos` ou `Soluções Parenterais`.
   - **Unidade de Medida:** Selecione `Frasco` ou `Bolsa`.
4. Salve o produto.
5. Busque na listagem pelo nome criado e confirme se todos os dados foram salvos corretamente.

---

### Passo 3: Conferência de Bloqueio no Almoxarife (Navegador B)
1. No Navegador B (logado como `arthuralmoxarife@gmail.com`):
   - Inspecione o menu lateral: confirme que o menu **"Usuários"** NÃO é exibido para o almoxarife.
   - **Tentativa de Invasão de URL:** No navegador, force o endereço digitando manualmente `http://localhost/users` (ou a URL do app).
   - **Resultado Esperado:** O guard de rotas do Vue Router intercepta a navegação e redireciona o usuário para `/setor-atual` ou `/home`, impedindo a visualização da tela.
2. Inspecione o menu **"Cadastros > Produtos"**:
   - Se o almoxarife estiver selecionado em uma farmácia satélite que não é a CAF, verifique se os botões de criação/exclusão de catálogo de produtos ficam bloqueados/ocultos.

---

## 🧪 ROTEIRO 3: Relatórios, Impressão Formal e Exportações

**Objetivo:** Validar o layout oficial de impressão da requisição hospitalar e a fidedignidade dos relatórios em PDF e Excel.

---

### Passo 1: Impressão da Requisição de Materiais (`imprimirPedido.js`)
1. No Histórico de Pedidos ou na aba de Movimentações, localize um pedido no status **Aprovado**.
2. Clique no botão com ícone de **Impressora** (`imprimirPedido`).
3. A janela de impressão nativa do navegador será aberta. Verifique minuciosamente:
   - **Orientação da Folha:** Deve abrir automaticamente em **Modo Paisagem (Landscape)**.
   - **Cabeçalho:** Logo do hospital no canto superior esquerdo e título centralizado *"REQUISIÇÃO DE MATERIAIS"*.
   - **Identificação do Pedido:** Exibe Data do Pedido em formato estrito `DD/MM/YYYY` (sem timestamp longo em UTC), Solicitante, Setor Solicitante, Setor Fornecedor e Status.
   - **Tabela de Itens:** Contém as colunas:
     - `#` (Item)
     - `Cód. SIMPASS`
     - `Descrição do Item`
     - `Unidade`
     - `Lote` (com detalhamento das quantidades de cada lote atendido)
     - `Qtd. Solicitada`
     - `Qtd. Atendida`
   - **Rodapé de Assinaturas:** Abaixo da tabela, consta a frase centralizada em negrito:  
     **"Carimbar ou assinar por extenso"**  
     Abaixo dela, devem existir os **4 quadros de assinatura**:
     1. Solicitado por: [Nome do Solicitante]
     2. Autorizado por: [Espaço para carimbo da chefia]
     3. Atendido por: [Setor Distribuidor]
     4. Recebido por: [Espaço para assinatura do portador]

---

### Passo 2: Relatório Financeiro e Sigilo de Custos
1. No Navegador A (logado como `pabloadmin@gmail.com` ou `adminti@gmail.com`):
   - Acesse o menu **"Relatórios > Financeiro"** (`/relatorios/financeiro`).
   - Alterne a chave entre **Entradas** e **Saídas**.
   - Defina um período e filtre por setor.
   - Verifique se a coluna `Valor Total` (Quantidade × Valor Unitário) é calculada corretamente.
   - Verifique o rodapé: o somatório total deve ser apresentado formatado no padrão monetário brasileiro (`R$ 1.250,00`).
2. No Navegador B (logado como Solicitante `jeansolicitante@gmail.com`):
   - Tente acessar a URL `/relatorios/financeiro`.
   - O acesso deve ser **negado**, protegendo o sigilo financeiro dos contratos e compras hospitalares.

---

### Passo 3: Exportação em Planilha Excel (XLSX) e PDF
1. Acesse o **"Relatório de Estoque"** (`/relatorios/estoque`) ou **"Relatório de Entradas"** (`/relatorios/entradas`).
2. Clique no botão **"Exportar Excel"**:
   - Abra a planilha gerada no Excel ou Google Planilhas.
   - **Validação de Tipo Numérico:** Clique na célula de quantidade ou valor. Aplique uma fórmula simples `=SOMA(...)`.
   - **Critério:** A fórmula deve somar normalmente. Os dados **não podem estar gravados como texto/strings com aspas**.
3. Clique no botão **"Exportar PDF"**:
   - Abra o PDF gerado.
   - Verifique se o cabeçalho hospitalar está presente e se nenhuma coluna foi cortada nas bordas da página.

---

## 🧪 ROTEIRO 4: Entrada por Nota Fiscal e Abastecimento Raiz (Exclusivo CAF)

**Objetivo:** Validar o recebimento formal de notas fiscais de compras no estoque central do hospital.

* **Perfil:** `arthuralmoxarife@gmail.com` ou `pabloadmin@gmail.com`  
* **Setor Ativo:** **CENTRAL DE ABASTECIMENTO FARMACÊUTICO (CAF)** (Setor ID 1).

---

### Passo a Passo:
1. No seletor de setor, escolha a **CAF**.
2. Acesse `/setor-atual` e clique na aba **"Entrada"** (esta aba aparece exclusivamente na CAF para almoxarifes e administradores).
3. Clique no botão **"Nova Entrada"**.
4. Preencha os campos da Nota Fiscal:
   - **Fornecedor:** Selecione um fornecedor cadastrado (ex: `Cristália Produtos Químicos Farmacêuticos Ltda`).
   - **Número da NF:** `NF-HOMOLOG-2026`
   - **Data de Emissão:** Data de hoje.
   - **Valor Total:** `R$ 2.500,00`
5. Adicione os itens recebidos:
   - **Produto:** Selecione `AMOXICILINA, 500mg`.
   - **Lote:** `LOTE-CRIS-88`
   - **Data de Validade:** Data de 1 ano à frente.
   - **Quantidade:** `50`
   - **Valor Unitário:** `R$ 50,00`
6. Clique em **"Salvar Entrada"**.
7. **Verificação de Crédito:**
   - Acesse a aba **"Estoque"** da CAF.
   - Busque por `AMOXICILINA`.
   - Abra a visualização de lotes: o lote `LOTE-CRIS-88` deve constar com saldo de `50` unidades, status Disponível (`D`), pronto para distribuição para as farmácias satélites e clínicas.

---

## 🧪 ROTEIRO 5: Ciclo de Vida do Rascunho (Draft 'C') e Cancelamento Prévio

**Objetivo:** Validar a segurança de pedidos montados em etapas sem impacto prévio no estoque e o cancelamento pelo solicitante antes do atendimento.

* **Navegador A (Solicitante):** `jeansolicitante@gmail.com` na **CLÍNICA MÉDICA** (Setor ID 5).
* **Navegador B (Almoxarife):** `arthuralmoxarife@gmail.com` na **FARMÁCIA DE DISPENSAÇÃO** (Setor ID 2).

---

### Passo a Passo:
1. No Navegador A, monte um pedido de 3 itens diferentes.
2. Em vez de enviar, clique em **"Salvar como Rascunho"** (status `C`).
3. **Validação de Invisibilidade:**
   - No Navegador B (Almoxarife), abra a fila de solicitações pendentes.
   - **Critério:** O pedido **NÃO pode aparecer** na fila do almoxarife.
   - No Navegador B, verifique o estoque dos produtos: **nenhum item foi bloqueado ou subtraído**.
4. **Edição do Rascunho:**
   - No Navegador A, vá em "Histórico de Pedidos". O pedido aparece com badge **"Rascunho"**.
   - Clique no botão **"Editar"** (lápis).
   - Adicione mais um item e altere uma das quantidades.
   - Salve novamente como rascunho.
5. **Submissão do Pedido:**
   - No Histórico de Pedidos, clique em **"Enviar Pedido"**.
   - O status muda de `C` para `P` (Pendente).
6. **Visibilidade no Fornecedor:**
   - No Navegador B, atualize a tela: o pedido agora surge instantaneamente na fila para ser atendido.
7. **Cancelamento pelo Solicitante:**
   - No Navegador A, antes que o almoxarife aprove o pedido, clique em **"Cancelar Solicitação"**.
   - O status muda para `X` (Cancelado).
   - No Navegador B, o pedido é automaticamente removido da fila de atendimento pendente.

---

## 🧪 ROTEIRO 6: Remanejamento Livre entre Farmácias (Cross-Docking)

**Objetivo:** Validar a regra de remanejamento hospitalar onde farmácias com estoque podem solicitar itens entre si.

* **Perfil:** `arthuralmoxarife@gmail.com` (operando entre farmácias satélites).
* **Setor Origem:** **CAF** (Setor ID 1).
* **Setor Destino:** **FARMÁCIA DE DISPENSAÇÃO** (Setor ID 2).

---

### Passo a Passo:
1. Selecione o setor ativo **FARMÁCIA DE DISPENSAÇÃO**.
2. Acesse `/setor-atual` > aba **"Movimentações"** > **"Nova Transferência"**.
3. Selecione:
   - **Setor Origem (Fornecedor):** `CENTRAL DE ABASTECIMENTO FARMACÊUTICO (CAF)`
   - **Setor Destino (Receptor):** `FARMÁCIA DE DISPENSAÇÃO`
   - **Tipo:** Transferência (`T`)
4. Inclua um item com saldo na CAF (ex: `AMPICILINA`).
5. Envie o pedido de transferência.
6. Mude seu setor ativo para a **CAF**:
   - O pedido estará na fila de atendimento da CAF.
   - Atenda e aprove a solicitação.
7. **Validação de Baixa e Entrada Cruzada:**
   - O estoque da CAF do item é debitado pelo lote mais antigo (FIFO).
   - O estoque da Farmácia de Dispensação é imediatamente creditado com o mesmo lote e quantidade transferida.

---

## ✅ Checklist Rápido de Homologação Final

- [ ] Solicitação enviada da UTI para a Farmácia sem erros de digitação numérica.
- [ ] Atendimento no distribuidor com bloqueio de valores negativos e saldo insuficiente.
- [ ] Nome do almoxarife aprovador gravado e exibido no pedido.
- [ ] Devolução parcial com validação de limite máximo liberado.
- [ ] Bloqueio de perfil almoxarife em setores sem controle de estoque físico.
- [ ] Bloqueio de acesso a telas de usuários para perfis não administradores.
- [ ] Impressão landscape em papel com layout de 4 assinaturas e Cód. SIMPASS.
- [ ] Exportações de relatórios em Excel com colunas de número reais (somáveis) e PDF sem cortes.
- [ ] Entrada formal de Nota Fiscal com lote e validade na CAF.
- [ ] Rascunhos invisíveis ao fornecedor até submissão formal.
