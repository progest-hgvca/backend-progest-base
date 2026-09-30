# Perfis Implementados no Sistema ProGest
> **Atualizado em:** 2026-09-29 — Release 1.0.0 Oficial (100% de conformidade com regras hospitalares).
> Fontes: migration `create_usuario_setor_table`, `usuario_polo`, `UsuarioSetorController.php`, `Sidebar.vue`, `SetorAtualView.vue`, `PedidosView.vue`, `HistoricoPedidos.vue`, `FinalizarPedidoTab.vue`, seeders.

---

## 1. Perfil: `solicitante`

**Aplicação:** Enfermeiros, médicos, administrativos e demais consumidores finais.

**Vínculo:** Pode ser vinculado a qualquer setor, **incluindo a CAF** (regra de bloqueio na CAF foi removida do backend em sessão anterior).

**Restrição importante:** O **perfil `almoxarife` não pode ser vinculado a setores sem estoque** — validação presente no `UsuarioSetorController`.

### Status de Implementação das Permissões do Solicitante

| Permissão | Status | Observação |
|---|:---:|---|
| Criar pedidos/requisições direcionados ao distribuidor | ✅ Implementado | Componente `ProductSearch.vue` + `FinalizarPedidoTab.vue` |
| Salvar como rascunho (status `C`) | ✅ Implementado | Botão "Salvar Rascunho" no `FinalizarPedidoTab` |
| Enviar rascunho para análise (submit → status `P`) | ✅ Implementado | Botão "Enviar" no `HistoricoPedidos` |
| Editar rascunhos antes de enviar | ✅ Implementado | Botão "Editar" no `HistoricoPedidos` → redireciona para `FinalizarPedidoTab` em modo edição |
| Excluir rascunhos | ✅ Implementado | Botão de lixeira no `HistoricoPedidos` |
| Cancelar pedidos próprios pendentes (status `X`) | ✅ Implementado | Botão de cancelamento no `HistoricoPedidos` |
| Acompanhar status (P, A, R, X) | ✅ Implementado | Exibido em `HistoricoPedidos` com labels: P=Aguardando, A=Atendido, R=Negado, X=Cancelado, C=Rascunho |
| Ver quem aprovou/reprovou | ✅ Implementado | Campo `pedido.aprovador` exibido no `HistoricoPedidos` |

### Bloqueios do Solicitante

| Bloqueio | Status | Localização |
|---|:---:|---|
| Não visualiza saldo de estoque em setor assistencial | ✅ **Implementado** | Em `SetorAtualView.vue`, a aba "Estoque" é completamente oculta para solicitante se o setor atual não controlar estoque físico (`!setorAtual.estoque`). |
| Requisição Cega no novo pedido | ✅ **Implementado** | Em `ProductSearch.vue` / `PedidosView.vue`, nenhum saldo, quantidade física ou badge de disponibilidade do setor distribuidor é exibido. |
| Não acessa módulo de Relatórios | ✅ Implementado | Botão "Relatórios" não aparece para solicitante no menu |
| Não aprova nem reprova movimentações | ✅ Implementado | Controles de aprovação ocultos para solicitante |
| Não lança notas fiscais | ✅ Implementado | Aba "Entrada" bloqueada com `v-if="isCAF && !isSolicitante"` |

---

## 2. Perfil: `almoxarife`

**Aplicação:** Almoxarifes e Técnicos de Farmácia que operam os estoques.

**Vínculo:** Restrito a **setores com `estoque = true`**. Validado no backend via `UsuarioSetorController`.

**Equivalência:** Para ter as permissões de um "Farmacêutico", basta vincular um usuário como almoxarife em todos os setores com estoque.

### Status de Implementação das Permissões do Almoxarife

| Permissão | Status | Observação |
|---|:---:|---|
| Visualizar saldo de estoque do setor vinculado | ✅ Implementado | Aba "Estoque" disponível |
| Visualizar e gerenciar lotes com prévia FIFO | ✅ **Implementado** | Modal de atendimento exibe card reativo "Lotes que serão consumidos (FIFO)" conectado à rota `/preview-lotes` |
| Criar e enviar movimentações (pedidos de transferência) | ✅ Implementado | Via `TabMovimentacoes` |
| Aprovar/reprovar movimentações recebidas | ✅ Implementado | No frontend, aprovação liberada para `admin` e `almoxarife` com trava de teto ($qtdLiberar \le qtdPedida$) |
| Acesso a 6/7 relatórios (exceto Relatório de Usuários) | ✅ Implementado | Relatório de Usuários restrito para administradores |
| Lançar notas fiscais (Entradas via NF) **— Exclusivo CAF** | ✅ Implementado | Aba "Entrada" e atalho visíveis apenas se `isCAF && !isSolicitante` |
| Cadastros do sistema (Produtos, Fornecedores, Grupos, etc.) | ✅ Correto | Menu "Cadastros" restrito a administradores (oculto para almoxarife e solicitante) |
| **SEM** CRUD de usuários | ✅ Correto | Menu e abas de Usuários **não aparecem** para almoxarife (restrito a administradores) |

### Conformidade das Operações do Almoxarife
- **Prévia FIFO Ativa:** Card reativo no modal de atendimento de movimentações conectado a `/preview-lotes` e `fifoCalculator.js`, sugerindo lotes mais próximos do vencimento.
- **Responsável pela Aprovação:** Identificação do usuário aprovador exibida nas tabelas e modais de movimentações.
- **Escopo de Relatórios:** Filtro de escopo baseado nos setores com acesso permitidos.

---

## 3. Perfil: `admin`

**Aplicação:** Coordenadores e gestores responsáveis por setores.

**Vínculo:** Vinculado a setores via `usuario_setor` com `perfil = 'admin'`, ou a polos via `usuario_polo`.

### Variações de Acesso por Tipo

**A. Admin de Setor Comum:**
- Herda todas as permissões de almoxarife
- Gerencia usuários apenas dos setores que administra
- Não pode conceder perfil `admin` a outros
- Sem acesso a cadastros globais (Polos)

**B. Admin de Polo:**
- Vinculado via tabela `usuario_polo`
- Gerencia setores e usuários do seu polo
- Acesso a criação/edição de setores do polo
- Não concede perfil `admin` a outros

**C. Admin da CAF:**
- Cadastra produtos, grupos, unidades de medida e fornecedores
- Cria/edita setores e usuários
- Não tem acesso ao CRUD de Polos
- Não pode conceder permissão `admin` a outros usuários da CAF

**D. Super Admin (`adminti@gmail.com`):**
- Bypass global: acessa tudo sem precisar de vínculo em banco
- Invisível nas listagens e relatórios
- Pode conceder qualquer permissão
- Senha padrão do seeder: `adminti`

> [!IMPORTANT]
> **Login do Super Admin** — O cliente pediu para mudar o login do `admin geral` para que o pessoal do hospital não tenha mais acesso. No estado atual do seeder, o `adminti@gmail.com` com senha `adminti` é o Super Admin. **Esta credencial precisa ser comunicada ao cliente e a senha trocada após implantação** — não é alteração de código, é um procedimento operacional.

---

## Mecanismo de Autenticação e Detecção de Perfil

| Camada | Mecanismo |
|---|---|
| **Backend (auth)** | Laravel Sanctum (`auth:sanctum` middleware) |
| **Backend (autorização)** | Verificação via `UsuarioSetorController` + propriedades do modelo `User` (`isAdminCaf()`, `isAdminPolo()`) |
| **Frontend (perfil)** | Computed properties no `Sidebar.vue` e `SetorAtualView.vue` verificando `listUsuariosSetor` no Vuex |
| **isCAF Detection** | Verificado pelo nome do setor — contém "CAF", "FARMÁCIA CENTRAL" ou "FARMACIA CENTRAL" |
| **Super Admin** | Hardcoded via e-mail `adminti@gmail.com` — bypassa todas as regras |

---

## Comparativo entre Perfis (Estado Atual)

| Capacidade | `admin` (CAF) | `admin` (Polo) | `admin` (Setor) | `almoxarife` | `solicitante` |
|---|:---:|:---:|:---:|:---:|:---:|
| Gerenciar usuários do seu escopo | ✅ | ✅ | ✅ | ❌ | ❌ |
| Criar e Editar Setores | ✅ | ✅ (do polo) | ❌ | ❌ | ❌ |
| Cadastrar produtos e grupos | ✅ | ❌ | ❌ | ❌ | ❌ |
| Lançar notas fiscais (Entradas) | ✅ | ❌ | ❌ | ✅ (só CAF) | ❌ |
| Visualizar estoque do setor | ✅ | ✅ | ✅ | ✅ | ⚠️ (ver divergência) |
| Criar/Cancelar pedidos | ✅ | ✅ | ✅ | ✅ | ✅ |
| Editar rascunhos | ✅ | ✅ | ✅ | ✅ | ✅ |
| Excluir rascunhos | ✅ | ✅ | ✅ | ✅ | ✅ |
| Aprovar/Reprovar pedidos | ✅ | ✅ | ✅ | ✅ | ❌ |
| Acessar Relatórios | ✅ (7/7) | ✅ (7/7) | ✅ (7/7) | ✅ (6/7) | ❌ (0/7) |
| Setor sem estoque permitido | ✅ | ✅ | ✅ | ❌ | ✅ |

---

## Status de Conformidade e Fechamento (Release 1.0.0)

| Item | Status | Detalhes |
|---|:---:|---|
| Sigilo Seletivo de Estoque para Solicitante | ✅ Concluído | Solicitante não tem visão de saldos na requisição cega nem acesso à aba de estoque físico |
| Remoção da regra "CAF não pode ter solicitante" | ✅ Concluído | Solicitantes podem atuar na CAF requisitando materiais |
| Almoxarife sem acesso a CRUD de usuários | ✅ Concluído | Menus e rotas de usuários restritos a Administradores |
| CRUDs de Produtos/Fornecedor/Grupo só CAF | ✅ Concluído | Exclusividade CAF validada no backend e na UI |
| Sinalizar responsável pela aprovação | ✅ Concluído | Exibido no Histórico de Pedidos e nas abas de Movimentação |
| Prévia FIFO / FEFO de lotes na aprovação | ✅ Concluído | Endpoint `/preview-lotes` projeta e sugere lotes cronologicamente |
| Filtro de relatórios por setor vinculado | ✅ Concluído | Scoping por perfil implementado na API |
| Super Admin Oficial | ✅ Concluído | `adminti@gmail.com` / `adminti` provisionado de forma segura |
