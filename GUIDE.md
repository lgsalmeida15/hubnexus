# 📘 Guia de Arquitetura - HubNexus

Este documento descreve a organização e os padrões da aplicação HubNexus.

## 📂 Estrutura de Pastas

A aplicação segue uma estrutura organizada para separar a lógica de negócio da camada de apresentação:

```text
hubnexus/
├── app/                # Lógica central da aplicação (Classes, Configurações)
│   ├── Auth.php        # Gerenciamento de autenticação e sessões
│   ├── Config.php      # Conexão com banco e carregamento de .env
│   └── bootstrap.php   # Inicializador global da aplicação
├── database/           # Scripts SQL e migrações
│   └── schema.sql      # Estrutura do banco de dados
├── public/             # Arquivos acessíveis via navegador (Entry points)
│   ├── api/            # Endpoints JSON para AJAX
│   ├── assets/         # Recursos estáticos (CSS, JS, Imagens)
│   ├── includes/       # Componentes de layout (Header, Footer)
│   ├── index.php       # Dashboard (Hub)
│   ├── login.php       # Tela de acesso
│   ├── empresas.php    # Gerenciamento de PVs
│   └── usuarios.php    # Gerenciamento de usuários
├── .env                # Variáveis de ambiente
└── projeto.md          # Documentação do escopo
```

## 🏗️ Padrões de Desenvolvimento

### 1. Estrutura de Páginas (CRUD)
Para manter a consistência, todas as novas entidades (ex: `empresas_omie`) devem seguir o padrão de 3 páginas:
- `entidade.php`: Listagem em tabela com filtros e ações.
- `entidade_create.php`: Formulário de criação em página cheia.
- `entidade_edit.php`: Formulário de edição carregando dados existentes.

### 2. Hierarquia de Navegação
A aplicação deve respeitar os 4 níveis de profundidade:
1. **Hub** (`index.php`) -> 2. **Integração** (`redecard.php`) -> 3. **Entidade** (`empresas.php` - Ponto de Venda) -> 4. **Ação** (`create/edit`).

### 3. Autenticação e Autorização
A classe `App\Auth` centraliza o controle de acesso.
- **Perfis**: `admin` (total), `edit` (pode alterar dados), `view` (apenas leitura).
- **Proteção**: Use `Auth::requireLogin()` ou `Auth::requireRole('admin')` no topo dos arquivos públicos.

### 2. Banco de Dados
- Utiliza **PDO** com PostgreSQL.
- A conexão é gerenciada por `App\Config::getDatabaseConnection()`.
- Variáveis de conexão devem ser configuradas no arquivo `.env`.

### 3. Frontend e UX
- **Framework**: Bootstrap 5.3 com customizações via `assets/css/style.css`.
- **Tema**: Suporte nativo a Dark/Light mode com persistência em `localStorage`.
- **Componentes**: Layout centralizado em `includes/header.php` e `includes/footer.php`.
- **Cores**: Identidade visual focada no tom `#4b0081`.
- **Performance**: Consultas via AJAX com overlays de carregamento e paginação otimizada.

## 🚀 Como Executar

1. Configure o seu servidor web (Apache/Nginx) para apontar a raiz para a pasta `public/`.
2. Crie o banco de dados no PostgreSQL e execute o script `database/schema.sql`.
3. Renomeie/Configure o arquivo `.env` com suas credenciais locais.
4. Acesse via navegador.

---
*Este guia serve como referência para manter a consistência do projeto à medida que ele cresce.*
