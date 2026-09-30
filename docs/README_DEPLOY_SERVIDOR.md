# 🚀 Guia de Handoff e Deploy em Servidor — Sistema ProGest

Este documento contém todas as instruções técnicas, arquiteturais e operacionais necessárias para que um novo desenvolvedor, DevOps ou administrador de sistemas possa **clonar, configurar, subir e manter** o ecossistema hospitalar ProGest em qualquer servidor Linux (AWS EC2, VPS DigitalOcean/Linode, servidor dedicado ou ambiente on-premises).

---

## 📂 1. Mapeamento de Repositórios e Estrutura de Arquivos

O sistema é modular e distribuído em **três repositórios Git oficiais**. Para executar a aplicação completa em um servidor, clone os três projetos no mesmo diretório base (por exemplo, na pasta de um usuário ou em `/opt/progest`):

```
/opt/progest/ (ou ~/progest/)
├── traefik-proxy/           ➜ Repositório: https://github.com/progest-hgvc/traefik-proxy.git
├── backend-progest-base/    ➜ Repositório: https://github.com/progest-hgvca/backend-progest-base.git
├── frontend-progest-base/   ➜ Repositório: https://github.com/progest-hgvca/frontend-progest-base.git
│
├── README.md                 ➜ Visão geral do projeto e guia de inicialização rápida
├── README_DEPLOY_SERVIDOR.md ➜ Este guia completo de handoff e subida em novo servidor
├── manual_do_usuario.md      ➜ Manual completo de uso (Solicitante, Almoxarife e Administrador)
├── perfis_implementados.md   ➜ Mapeamento de regras de negócio, permissões e governança
├── mapeamento_fluxo_setores.md ➜ Classificação setorial (CAF, com estoque, sem estoque) e fluxos
├── roteiro_testes_manuais_homologacao.md ➜ 7 fases e 35 cenários de teste para validação
├── GUIA_DEPLOY_AWS.md        ➜ Passo a passo específico para instâncias AWS EC2 (Ubuntu)
├── relatorio_mudancas.md     ➜ Histórico de refatoração, travas transacionais e ledger
├── iniciar_local.ps1         ➜ Script PowerShell para inicialização rápida em ambiente Windows
└── parar_local.ps1           ➜ Script PowerShell para parada limpa em ambiente Windows
```

---

## 📦 2. Quais Arquivos Devem Ser Enviados ao Git

Ao passar o projeto adiante, garanta que os seguintes arquivos estejam devidamente comitados e versionados:

### A. Repositório `backend-progest-base`
- Código-fonte da API em Laravel 10 (`app/`, `routes/`, `config/`, etc.).
- Migrações do banco de dados (`database/migrations/`).
- Seeders oficiais:
  - `DatabaseSeeder.php`: Base limpa oficial de produção (62 setores, 417 produtos SIMPAS, unidades e Super Admin `adminti@gmail.com`).
  - `DemonstracaoSistemaSeeder.php`: Cenário de homologação hospitalar com histórico de 90 dias, lotes, NFs na CAF, devoluções e dispensações FIFO.
- Arquivos de configuração de container:
  - `Dockerfile`: Imagem PHP 8.2 FPM com extensões MySQL, Redis, GD e Zip.
  - `docker-compose.yml`, `docker-compose.local.yml` e `docker-compose.aws.yml`.
  - `.env.example`, `.env.docker.example` e `.env.aws.example` (modelos de variáveis de ambiente sem credenciais sensíveis).

### B. Repositório `frontend-progest-base`
- Código-fonte da SPA em Vue.js 3 + Vite (`src/`, `public/`).
- Configurações de layout, design system e ícones (`tailwind.config.js`, `postcss.config.js`).
- Arquivos de deploy:
  - `Dockerfile`: Multi-stage build com Node 20 para compilação estática (`npm run build`) e Nginx Alpine leve para servir os assets.
  - `nginx.conf`: Roteamento interno com fallback de rotas SPA (`try_files $uri $uri/ /index.html`).
  - `docker-compose.yml`, `docker-compose.local.yml` e `docker-compose.aws.yml`.
  - `.env.example` e `.env.aws.example` com o apontamento da variável `VITE_API_URL`.

### C. Repositório `traefik-proxy`
- Arquivos de orquestração do proxy reverso:
  - `docker-compose.yml`, `docker-compose.local.yml` e `docker-compose.aws.yml`.
  - `.env.example` e regras de roteamento Traefik v2.11.

> [!TIP]
> **Documentação de Apoio no Git:**  
> Os manuais e guias de teste (`manual_do_usuario.md`, `roteiro_testes_manuais_homologacao.md`, `perfis_implementados.md` e este `README_DEPLOY_SERVIDOR.md`) estão salvos na raiz do projeto e também espelhados na pasta `backend-progest-base/docs/`, garantindo que qualquer membro da equipe tenha acesso à documentação diretamente via `git clone`.

---

## 🛠️ 3. Pré-requisitos do Novo Servidor

- **Sistema Operacional Recomendado:** Ubuntu 22.04 LTS ou Ubuntu 24.04 LTS (x86_64).
- **Recursos Mínimos de Hardware:**
  - 1 vCPU / 1 GB de Memória RAM (obrigatório configurar SWAP de 2GB).
  - 10 GB de disco livre.
- **Portas que devem ser liberadas no Firewall (UFW / Security Group):**
  - Porta `22` (SSH): Acesso administrativo ao servidor.
  - Porta `80` (HTTP): Acesso web da aplicação e emissão de certificados.
  - Porta `443` (HTTPS): Acesso web seguro criptografado.
  - Porta `8080` (Opcional): Dashboard visual do Traefik.

---

## ⚙️ 4. Passo a Passo Completo para Subir o Sistema no Servidor

### Passo 1: Preparação do Sistema Operacional e SWAP
Conecte-se ao servidor via SSH e execute os comandos para criar a memória virtual SWAP (evita travamento do MySQL e do build do frontend):
```bash
# 1. Configurar SWAP de 2GB
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab

# 2. Instalar Docker e Docker Compose Plugin
sudo apt-get update
sudo apt-get install -y ca-certificates curl gnupg lsb-release
sudo mkdir -p /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin
sudo usermod -aG docker $USER

# Aplicar grupo docker imediatamente sem deslogar
newgrp docker
```

---

### Passo 2: Criar a Rede Docker Compartilhada
Todos os containers comunicam-se através de uma rede bridge externa gerenciada pelo Traefik:
```bash
docker network create traefik-public
```

---

### Passo 3: Clonar e Subir o Traefik Proxy
```bash
# 1. Clonar repositório
cd ~
git clone https://github.com/progest-hgvc/traefik-proxy.git
cd ~/traefik-proxy

# 2. Subir o Traefik
docker compose -f docker-compose.aws.yml up -d
```
*O Traefik ficará escutando na porta 80 e roteará automaticamente as requisições para a API e para o Frontend com base nos cabeçalhos HTTP.*

---

### Passo 4: Clonar, Configurar e Subir o Backend (Laravel + MySQL)
```bash
# 1. Clonar repositório
cd ~
git clone https://github.com/progest-hgvca/backend-progest-base.git
cd ~/backend-progest-base

# 2. Configurar o arquivo de variáveis de ambiente
cp .env.aws.example .env.aws

# 3. Informar o IP Público ou Domínio do Servidor no .env.aws
# Substitua SEU_IP_OU_DOMINIO pelo IP do servidor (ex: 18.232.55.1 ou progest.hospital.ba.gov.br)
sed -i 's/APP_DOMAIN=IP_DA_AWS/APP_DOMAIN=SEU_IP_OU_DOMINIO/g' .env.aws

# 4. Construir as imagens e iniciar os containers (API + Banco de Dados MySQL 8)
docker compose -f docker-compose.aws.yml up -d --build

# 5. Gerar a chave criptográfica da aplicação Laravel (APP_KEY)
KEY=$(docker compose -f docker-compose.aws.yml exec -T progest-api php artisan key:generate --show)
sed -i "s|^APP_KEY=.*|APP_KEY=$KEY|" .env.aws

# 6. Reiniciar o container da API para registrar a nova chave
docker compose -f docker-compose.aws.yml up -d progest-api
```

---

### Passo 5: Inicializar o Banco de Dados (Migrações e Dados Iniciais)
Escolha **uma** das duas opções de carga no banco de dados:

#### Opção A: Base de Produção Limpa (Oficial)
Cria toda a estrutura de tabelas, cadastra os 62 setores oficiais do hospital, 417 produtos do catálogo oficial SIMPAS e o Super Administrador raiz:
```bash
docker compose -f docker-compose.aws.yml exec progest-api php artisan migrate:fresh --seed
```

#### Opção B: Base com Demonstração e Homologação Completa
Executa a base limpa e adiciona cenários de teste hiper-realistas (histórico de 90 dias, lotes, NFs na CAF, devoluções e dispensações FIFO):
```bash
docker compose -f docker-compose.aws.yml exec progest-api php artisan migrate:fresh --seed
docker compose -f docker-compose.aws.yml exec progest-api php artisan db:seed --class=DemonstracaoSistemaSeeder
```

---

### Passo 6: Clonar, Configurar e Subir o Frontend (Vue 3 / Vite)
```bash
# 1. Clonar repositório
cd ~
git clone https://github.com/progest-hgvca/frontend-progest-base.git
cd ~/frontend-progest-base

# 2. Configurar variáveis de ambiente do Frontend
cp .env.aws.example .env.aws

# 3. Informar o mesmo IP ou Domínio configurado no Backend
sed -i 's/APP_DOMAIN=IP_DA_AWS/APP_DOMAIN=SEU_IP_OU_DOMINIO/g' .env.aws

# 4. Construir e subir o container do Frontend
docker compose -f docker-compose.aws.yml up -d --build
```

---

## 🔑 5. Credenciais de Acesso ao Sistema

Após a conclusão da subida dos containers, o sistema estará acessível no navegador em:
`http://SEU_IP_OU_DOMINIO`

### A. Credencial Raiz de Produção:
* **E-mail:** `adminti@gmail.com`
* **Senha:** `adminti`
* **Perfil:** Super Admin com permissão irrestrita a todos os módulos, setores e cadastros.

### B. Credenciais do Cenário de Demonstração / Homologação:
Se você executou o `DemonstracaoSistemaSeeder`, as seguintes contas estarão ativas para validação imediata:

| Perfil | Colaborador / Nome | E-mail de Login | Senha | Setores Vinculados |
| :--- | :--- | :--- | :---: | :--- |
| **Admin Geral** | ADMINISTRADOR GERAL | `admin.geral@progest.teste` | `Admin123` | Administrador dos Polos HGVC e HAP |
| **Almoxarife CAF** | ALMOXARIFE CAF HGVC | `almoxarife.caf@progest.teste` | `Admin123` | **CAF - Farmácia Central** e **Satélite CC** |
| **Solicitante UTI** | SOLICITANTE UTI ADULTO | `solicitante.uti@progest.teste` | `Admin123` | **UTI Adulto** (Setor assistencial / sem estoque) |
| **Almoxarife HAP** | ALMOXARIFE CENTRAL HAP | `almoxarife.hap@progest.teste` | `Admin123` | **Almoxarifado Central HAP** e **Satélite HAP** |
| **Solicitante HAP** | SOLICITANTE CLÍNICA HAP | `solicitante.hap@progest.teste` | `Admin123` | **Clínica Médica HAP** (Setor sem estoque) |

---

## 🛠️ 6. Rotinas de Manutenção e Atualização

### Como Atualizar o Código Após Novos Commits no GitHub:
```bash
# 1. Atualizar e recompilar o Backend
cd ~/backend-progest-base
git pull origin main
docker compose -f docker-compose.aws.yml up -d --build
docker compose -f docker-compose.aws.yml exec progest-api php artisan migrate --force

# 2. Atualizar e recompilar o Frontend
cd ~/frontend-progest-base
git pull origin main
docker compose -f docker-compose.aws.yml up -d --build
```

### Como Visualizar Logs em Tempo Real:
```bash
# Logs do Proxy Traefik:
docker logs traefik -f --tail 100

# Logs da API Backend:
docker logs backend-progest-base-progest-api-1 -f --tail 100

# Logs do Frontend Nginx:
docker logs frontend-progest-base-progest-web-1 -f --tail 100
```

### Como Fazer Backup do Banco de Dados MySQL:
```bash
# Gerar dump do banco de dados comprimido com data e hora:
docker exec backend-progest-base-progest-db-1 mysqldump -u progest_user -pprogest_secret progest_db | gzip > ~/backup_progest_$(date +%Y%m%d_%H%M%S).sql.gz
```

---

## 📚 7. Documentos Complementares

Para aprofundamento das regras de negócio, testes e operação clínica, consulte:
1. **[manual_do_usuario.md](manual_do_usuario.md):** Manual completo dos perfis Solicitante, Almoxarife e Administrador, abordando desde a criação de pedidos em busca cega até o Relatório de Medicamentos Controlados da Portaria 344/98.
2. **[perfis_implementados.md](perfis_implementados.md):** Detalhamento das travas de segurança por perfil e permissões setoriais.
3. **[mapeamento_fluxo_setores.md](mapeamento_fluxo_setores.md):** Classificação setorial (CAF, Setores com Estoque, Setores sem Estoque) e mapa topológico da rede de distribuição.
4. **[roteiro_testes_manuais_homologacao.md](roteiro_testes_manuais_homologacao.md):** Roteiro com 7 fases e 35 cenários de homologação hospitalar para validar o sistema antes da entrada oficial em operação.
5. **[relatorio_mudancas.md](relatorio_mudancas.md):** Registro das otimizações arquiteturais, Ledger imutável de estoque e travas transacionais.
