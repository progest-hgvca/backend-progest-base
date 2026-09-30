# 🛡️ Matriz de Perfis e Permissões — ProGest

> **Documento de Handoff Técnico** — Especificação formal dos níveis de acesso, travas de segurança e escopos do sistema.  
> Para o guia visual passo a passo na interface, consulte o **[manual_do_usuario.md](manual_do_usuario.md)**.  
> Para a distribuição física e classificação de setores, consulte o **[mapeamento_fluxo_setores.md](mapeamento_fluxo_setores.md)**.

---

## 1. Visão Geral dos Perfis de Acesso

O ProGest implementa controle de acesso baseado em papéis por setor (*Role-Based Access Control* setorial via tabela `usuario_setor` e polar via `usuario_polo`), dividido em três perfis fundamentais:

```
                    ┌──────────────────────────────────────────────┐
                    │            Super Admin (adminti)             │
                    │   Bypass global irrestrito em todo o sistema │
                    └──────────────────────┬───────────────────────┘
                                           │
         ┌─────────────────────────────────┼─────────────────────────────────┐
         ▼                                 ▼                                 ▼
┌──────────────────┐             ┌──────────────────┐             ┌──────────────────┐
│   Administrador  │             │    Almoxarife    │             │   Solicitante    │
│  Gestão setorial,│             │ Operação física, │             │  Consumo final,  │
│  catálogo (CAF)  │             │  lotes e FIFO    │             │ requisição cega  │
│    e equipe      │             │ (só c/ estoque)  │             │   em /pedidos    │
└──────────────────┘             └──────────────────┘             └──────────────────┘
```

---

## 2. Especificação por Perfil

### 2.1. Perfil: `solicitante`
* **Público-alvo:** Profissionais de saúde (enfermagem, médicos, técnicos) e colaboradores administrativos.
* **Vínculo Setorial:** Setores sem estoque (assistenciais e administrativos) e na CAF.
* **Criação de Pedidos:** Realizada **exclusivamente** no módulo **Fazer Pedido** (`/pedidos`), com busca cega de catálogo (sem exposição de saldos físicos do distribuidor).
* **Ciclo de Vida do Pedido:**
  - Salva rascunhos (`C`), edita e exclui itens antes do envio.
  - Submete pedidos para triagem (`P`).
  - Pode cancelar pedidos próprios enquanto estiverem pendentes (`X`).
  - Acompanha status e parecer do almoxarife/aprovador.
* **Travas e Bloqueios:**
  - ❌ **Sem aba Estoque:** Oculta em setores assistenciais (`estoque = false`).
  - ❌ **Sem aba Movimentações:** Não acessa `TabMovimentacoes`.
  - ❌ **Sem Aprovação:** Não pode aprovar, reprovar ou atender pedidos.
  - ❌ **Sem Entrada de NF:** Não lança notas fiscais.
  - ❌ **Sem Relatórios:** Menu de relatórios completamente inacessível.

---

### 2.2. Perfil: `almoxarife`
* **Público-alvo:** Almoxarifes e farmacêuticos operadores de estoque.
* **Vínculo Setorial:** **Restrito a setores com `estoque = true`** (validação no backend em `UsuarioSetorController`). Não pode ser vinculado a setores sem estoque.
* **Gestão de Movimentações:**
  - Analisa requisições pendentes na aba **Movimentações** (`TabMovimentacoes`).
  - Atende pedidos com alocação automática sugerida via critério **FIFO/FEFO** (`/preview-lotes`).
  - Permite atendimento parcial ou reprovação com justificativa registrada.
  - Bloqueio de segurança: não permite liberar quantidade superior à solicitada ($qtdLiberar \le qtdPedida$).
* **Pedidos de Ressuprimento:** Quando o almoxarife precisa repor o estoque local a partir da CAF, a requisição é criada via módulo **Pedidos** (`/pedidos`), selecionando a CAF como distribuidor.
* **Entrada de Notas Fiscais:** Visível e permitida **exclusivamente na CAF** (`isCAF && !isSolicitante`).
* **Relatórios:** Acesso a 6 relatórios operacionais (Estoque, Validades, Movimentações, Consumo, Portaria 344 e Setores Consumidores).
* **Travas e Bloqueios:**
  - ❌ **Sem CRUD de Usuários:** Menu e abas de usuários bloqueados.
  - ❌ **Sem Cadastros Globais:** Não cria produtos, grupos, unidades ou setores.
  - ❌ **Sem Relatório de Usuários:** Restrito a administradores.

---

### 2.3. Perfil: `admin`
* **Público-alvo:** Coordenadores de farmácia, diretores e administradores de TI.
* **Níveis de Abrangência:**

| Nível | Escopo | Capacidades Exclusivas |
|---|---|---|
| **Admin de Setor** | Setor específico | Herda almoxarife + gerencia usuários vinculados ao setor (aba **Equipe**). |
| **Admin de Polo** | Todos os setores do Polo | Cria/edita setores do polo e gerencia usuários de todo o polo. |
| **Admin da CAF** | CAF (Matriz) | Cadastra Produtos, Grupos de Produtos, Unidades de Medida e Fornecedores. Cria setores e gerencia equipe. |
| **Super Admin** | Raiz (`adminti@gmail.com`) | Bypass irrestrito. Criação de Polos, edição de qualquer setor, invisível em listagens. |

---

## 3. Matriz Comparativa de Capacidades

| Funcionalidade / Módulo | Solicitante | Almoxarife | Admin Setor | Admin Polo | Admin CAF | Super Admin |
|---|:---:|:---:|:---:|:---:|:---:|:---:|
| Fazer Pedido / Rascunhos (`/pedidos`) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Cancelar Próprio Pedido Pendente | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Ver Aba Estoque Físico | ❌* | ✅ | ✅ | ✅ | ✅ | ✅ |
| Atender / Reprovar Movimentações | ❌ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Alocação FIFO de Lotes | ❌ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Registrar Entrada de NF | ❌ | ✅ (só CAF) | ❌ | ❌ | ✅ (só CAF) | ✅ |
| Acessar Relatórios Operacionais (6) | ❌ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Acessar Relatório de Usuários | ❌ | ❌ | ✅ | ✅ | ✅ | ✅ |
| Gerenciar Usuários do Setor | ❌ | ❌ | ✅ | ✅ | ✅ | ✅ |
| Cadastrar / Editar Setores | ❌ | ❌ | ❌ | ✅ (polo) | ✅ | ✅ |
| Cadastrar Produtos e Fornecedores | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Cadastrar / Editar Polos | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Vinculação a Setor sem Estoque | ✅ | ❌ (bloqueado) | ✅ | ✅ | ✅ | ✅ |

*\* Em setores com estoque físico, o solicitante visualiza a aba Estoque apenas em modo leitura (`readOnly`), mantendo a busca cega ao fazer pedidos.*

---

## 4. Detalhes de Autenticação e Segurança

* **Autenticação:** Tokens de sessão via Laravel Sanctum (`auth:sanctum`).
* **Identificação de CAF:** Verificada no frontend e backend quando o nome do setor contém `"CAF"` ou `"FARMÁCIA CENTRAL"`.
* **Super Admin:** Credencial raiz hardcoded com bypass via e-mail `adminti@gmail.com` e senha do seeder `adminti`.
