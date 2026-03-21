# Plano de Implementação: CRUD de Usuários e Sistema de Permissões em Projetos

**Versão:** 2.0
**Data:** 2026-03-21
**Status:** Revisado — aguardando implementação
**Alterações v2.0:** Incorporação de sugestões de segurança (S01–S12) após revisão com brainstorming + security-coder

---

## 1. Contexto e Objetivo

O sistema atual possui autenticação por sessão e uma estrutura de papéis (`roles`, `role_permissions`, `user_roles`) já criada no banco mas sem uso aplicado. A tabela `project_members` existe com a coluna `role_in_project` mas sem mecanismo de convite.

**Objetivos:**

1. **CRUD de Usuários** — somente o usuário admin pode criar, editar, ativar/desativar, redefinir senha e excluir usuários da empresa.
2. **Convite em Projetos** — qualquer membro com papel de `owner` ou `manager` no projeto pode convidar outros usuários da empresa definindo o papel: `editor` ou `viewer`.
3. **Guarda de Permissões** — operações de escrita verificam **dois níveis**: (a) isolamento de tenant (recurso pertence à empresa da sessão) e (b) papel do usuário no projeto (`ProjectPolicy`).

---

## 2. Modelo de Papéis

### 2.1 Papel global no sistema (coluna `users.is_admin`)

| Valor | Descrição |
|-------|-----------|
| `true` | Administrador — acesso total ao CRUD de usuários da empresa |
| `false` | Membro comum — sem acesso ao gerenciamento de usuários |

Optamos por uma coluna booleana simples em vez de usar as tabelas `roles/user_roles` existentes para evitar complexidade desnecessária. As tabelas de papéis ficam disponíveis para evolução futura.

> **Cache de sessão:** `user_is_admin` é armazenado na sessão **apenas para fins de navegação/interface** (mostrar/ocultar menus). O `AdminMiddleware` **sempre revalida contra o banco** em ações críticas — nunca confia no valor da sessão para autorização.

### 2.2 Papel no projeto (`project_members.role_in_project`)

| Valor | Criar/editar/mover tarefas | Criar boards/colunas | Convidar membros (POST) | Alterar papel de membro (PATCH) | Editar projeto | Excluir projeto |
|-------|---|---|---|---|---|---|
| `owner` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| `manager` | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ |
| `editor` | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `viewer` | ❌ (somente leitura) | ❌ | ❌ | ❌ | ❌ | ❌ |

> **Clarificação (S12):** `manager` pode **adicionar** novos membros (`POST`), mas somente `owner` pode **alterar o papel** de membros existentes (`PATCH`).

O criador de um projeto é automaticamente inserido em `project_members` com papel `owner`.

---

## 3. Alterações no Banco de Dados

### Migration `009_users_admin_project_roles.sql`

```sql
-- Adiciona flag de admin ao usuário
ALTER TABLE users
  ADD COLUMN is_admin TINYINT(1) NOT NULL DEFAULT 0 AFTER status;

-- Amplia project_members para suportar convites
ALTER TABLE project_members
  ADD COLUMN invited_by BIGINT UNSIGNED NULL AFTER role_in_project,
  ADD COLUMN accepted_at DATETIME NULL AFTER invited_by,
  ADD CONSTRAINT project_members_invited_by_fk
    FOREIGN KEY (invited_by) REFERENCES users(id);

-- Audit trail de ações administrativas (S07)
CREATE TABLE admin_audit_log (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    actor_id    BIGINT UNSIGNED NOT NULL COMMENT 'Quem executou a ação',
    action      VARCHAR(50)     NOT NULL COMMENT 'Ex: user_created, user_disabled, role_changed',
    target_user_id BIGINT UNSIGNED NULL COMMENT 'Usuário afetado, se aplicável',
    meta        JSON            NULL    COMMENT 'Snapshot antes/depois da alteração',
    created_at  DATETIME        NOT NULL,
    CONSTRAINT fk_audit_actor FOREIGN KEY (actor_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

> **Nota:** Não usamos tokens de convite por e-mail nesta versão — o fluxo é direto. O sistema não tem SMTP configurado.

---

## 4. Arquitetura de Arquivos

```
app/
├── Controllers/
│   ├── UserController.php              # NOVO — CRUD de usuários (admin only)
│   └── ProjectMemberController.php     # NOVO — convidar/remover membros, alterar papel
├── Services/
│   ├── User/
│   │   ├── CreateUserService.php       # NOVO
│   │   ├── UpdateUserService.php       # NOVO
│   │   ├── ToggleUserStatusService.php # NOVO (ativar/desativar)
│   │   └── ResetPasswordService.php   # NOVO (S04)
│   └── ProjectMember/
│       ├── AddMemberService.php        # NOVO
│       ├── UpdateMemberRoleService.php # NOVO
│       └── RemoveMemberService.php     # NOVO
├── Repositories/
│   ├── UserRepository.php              # Interface NOVA (estende o existente)
│   ├── PdoUserRepository.php           # ATUALIZADO — adicionar métodos CRUD
│   └── PdoProjectMemberRepository.php  # NOVO
├── DTO/
│   ├── UserDTO.php                     # NOVO
│   └── ProjectMemberDTO.php            # NOVO
├── Middleware/
│   └── AdminMiddleware.php             # NOVO — revalida is_admin no banco
├── Policies/
│   └── ProjectPolicy.php              # NOVO — verifica papel no projeto
└── Validators/
    ├── CreateUserValidator.php         # NOVO
    ├── UpdateUserValidator.php         # NOVO
    └── InviteMemberValidator.php       # NOVO

database/migrations/mysql/
└── 009_users_admin_project_roles.sql  # NOVO

routes/
├── api.php     # ATUALIZADO — novos endpoints
└── web.php     # ATUALIZADO — página /admin/users e /projects/{id}/members

templates/default/pages/
├── admin-users.php        # NOVO — listagem e CRUD de usuários
└── project-members.php    # NOVO — gerenciar membros do projeto
```

---

## 5. Endpoints da API

### 5.1 Usuários (requer `AdminMiddleware`)

| Método | Rota | Ação |
|--------|------|------|
| `GET` | `/api/admin/users` | Lista todos os usuários da empresa |
| `POST` | `/api/admin/users` | Cria novo usuário |
| `PATCH` | `/api/admin/users?id=X` | Atualiza nome, e-mail, is_admin *(S02)* |
| `POST` | `/api/admin/users/toggle-status` | Ativa ou desativa usuário |
| `POST` | `/api/admin/users/reset-password` | Redefine senha de um usuário *(S04)* |
| `DELETE` | `/api/admin/users?id=X` | Remove usuário (soft-delete via `status='deleted'`) |

**Payload `POST /api/admin/users`:**
```json
{
  "name": "João Silva",
  "email": "joao@empresa.com",
  "password": "Senha@123",
  "is_admin": false
}
```

**Payload `POST /api/admin/users/reset-password` (S04):**
```json
{
  "user_id": 5,
  "new_password": "NovaSenha@456"
}
```

**Regras de negócio:**
- Não é possível remover ou desativar o próprio usuário logado.
- Não é possível remover ou desativar o único admin restante da empresa. *(S11)*
- E-mail deve ser único dentro da empresa.
- Política de senha: mínimo 8 caracteres, ao menos 1 letra maiúscula e 1 número. *(S06)*
- Ao soft-deletar um usuário que é `owner` de projetos, o sistema bloqueia a operação e retorna a lista dos projetos afetados. *(S05)*
- Toda ação de criação, edição, desativação, reset de senha e exclusão gera registro em `admin_audit_log`. *(S07)*

### 5.2 Membros do Projeto

| Método | Rota | Ação | Permissão exigida |
|--------|------|------|-------------------|
| `GET` | `/api/project-members?project_id=X` | Lista membros | qualquer membro do projeto |
| `POST` | `/api/project-members` | Adiciona membro ao projeto | `owner` ou `manager` |
| `PATCH` | `/api/project-members?id=X` | Altera papel do membro | `owner` apenas *(S12)* |
| `DELETE` | `/api/project-members?id=X` | Remove membro do projeto | `owner` (ou próprio usuário saindo) |

**Payload `POST /api/project-members`:**
```json
{
  "project_id": 1,
  "user_id": 5,
  "role_in_project": "editor"
}
```

**Regras de negócio:**
- O `owner` não pode rebaixar a si mesmo para outro papel (evitar projeto sem dono).
- Um usuário só pode ser membro de um projeto uma vez.
- Apenas usuários com `status = 'active'` da **mesma empresa** (`company_id` da sessão) podem ser adicionados — validação obrigatória dentro do `AddMemberService`. *(S09)*

---

## 6. Middleware, Guards e Políticas

### `AdminMiddleware`

Aplicado nas rotas `/api/admin/*`. Verifica:
1. Sessão ativa (já garantida pelo `AuthMiddleware` global).
2. `is_admin = true` consultando **diretamente o banco** (nunca usa cache de sessão). *(A5 corrigida)*
3. Retorna `403 Forbidden` se não for admin.

### `ProjectPolicy`

Classe utilitária usada dentro dos controllers para verificar o papel do usuário no projeto.

```php
// Exemplo de uso dentro de BoardController
$policy = new ProjectPolicy($projectMemberRepo, $session);
if (!$policy->canWrite($projectId)) {
    return HttpResponse::json(['error' => 'forbidden'], 403);
}
```

**Métodos:**
- `canView(int $projectId): bool` — viewer ou superior
- `canWrite(int $projectId): bool` — editor ou superior
- `canManageBoard(int $projectId): bool` — manager ou superior
- `canInvite(int $projectId): bool` — manager ou superior
- `canAlterRoles(int $projectId): bool` — owner apenas *(S12)*
- `canManageProject(int $projectId): bool` — owner apenas

### Duplo Guard nos controllers existentes (S08)

A aplicação de `ProjectPolicy` em controllers existentes (Board, Column, Task) deve seguir **dois passos obrigatórios e sequenciais**:

```php
// Passo 1 — Isolamento de tenant: o recurso pertence à empresa da sessão?
$companyId = (int) $session->get('company_id');
$projectId = $this->taskRepo->resolveProjectId($taskId); // (S01)
if (!$this->projectRepo->belongsToCompany($projectId, $companyId)) {
    return HttpResponse::json(['error' => 'not_found'], 404); // 404, não 403 (evitar enumeração)
}

// Passo 2 — Autorização: o usuário tem papel suficiente no projeto?
if (!$policy->canWrite($projectId)) {
    return HttpResponse::json(['error' => 'forbidden'], 403);
}
```

> Retornar `404` no Passo 1 evita revelar ao atacante que o recurso existe em outra empresa.

### Resolução de `project_id` a partir de recursos aninhados (S01)

Os controllers de Task e Column precisam do `project_id` para aplicar a `ProjectPolicy`. Isso exige métodos de lookup na cadeia task → column → board → project:

```php
// TaskRepository — novo método
public function resolveProjectId(int $taskId): ?int;
// Implementação: SELECT b.project_id FROM tasks t JOIN columns c ON c.id=t.column_id JOIN boards b ON b.id=c.board_id WHERE t.id=?

// ColumnRepository — novo método
public function resolveProjectId(int $columnId): ?int;
// Implementação: SELECT b.project_id FROM columns c JOIN boards b ON b.id=c.board_id WHERE c.id=?
```

---

## 7. Sessão — dados adicionais em cache

Após o login, armazenar na sessão:
- `user_is_admin` — **exclusivamente para navegação/UI** (mostrar/ocultar menus de admin).

```php
// Em AuthService::login() após sucesso:
$session->set('user_is_admin', (bool) $user->isAdmin);
```

**Comportamento esperado quando `is_admin` é revogado por outro admin (S03):**
- Ações críticas são bloqueadas imediatamente (AdminMiddleware revalida no banco).
- Menus de admin permanecem visíveis na UI até o próximo login do usuário afetado.
- Esse comportamento é **aceitável e esperado** — sem implementação adicional necessária.

---

## 8. Tratamento de usuário soft-deletado (S10)

Quando `tasks.assigned_to` aponta para um usuário com `status='deleted'`, a query de listagem de tasks deve tratar graciosamente:

```sql
-- PdoTaskRepository::findByColumnId — ajuste necessário
SELECT t.*, u.name AS assignee_name
FROM tasks t
LEFT JOIN users u ON u.id = t.assigned_to
-- Resultado: assignee_name = NULL para usuários deletados
-- A camada de apresentação exibe "Usuário removido" quando NULL
```

Nenhuma alteração nos services é necessária — apenas na query de listagem e no frontend.

---

## 9. Política de Senha (S06)

Aplicada em `CreateUserValidator`, `UpdateUserValidator` e `ResetPasswordService`:

```
- Mínimo 8 caracteres
- Ao menos 1 letra maiúscula (A-Z)
- Ao menos 1 dígito numérico (0-9)
```

Regex de validação: `/^(?=.*[A-Z])(?=.*\d).{8,}$/`

Mensagem de erro padronizada: `"A senha deve ter no mínimo 8 caracteres, uma letra maiúscula e um número."`

---

## 10. Rotas Web

| Rota | Template | Descrição | Proteção |
|------|----------|-----------|----------|
| `GET /admin/users` | `pages/admin-users.php` | Painel de gerenciamento de usuários | `is_admin` no banco |
| `GET /projects/{id}/members` | `pages/project-members.php` | Gerenciar membros de um projeto | membro do projeto |

---

## 11. Impacto em Funcionalidades Existentes

| Componente | Mudança |
|------------|---------|
| `AuthService` | Adicionar `isAdmin` ao `UserAuthRecord` e gravá-lo na sessão |
| `PdoUserRepository::findActiveByEmail()` | Incluir `is_admin` no SELECT |
| `ProjectController::create()` | Após criar projeto, inserir criador em `project_members` com papel `owner` |
| `BoardController::create()` | Duplo guard: tenant check + `ProjectPolicy::canManageBoard()` |
| `ColumnController::create/reorder()` | Duplo guard: tenant check + `ProjectPolicy::canManageBoard()` |
| `TaskController::create/update/move()` | Duplo guard: tenant check + `ProjectPolicy::canWrite()` |
| `TaskRepository` | Novo método `resolveProjectId(int $taskId): ?int` |
| `ColumnRepository` | Novo método `resolveProjectId(int $columnId): ?int` |
| `PdoTaskRepository::findByColumnId()` | LEFT JOIN em `users` para `assignee_name` gracioso |
| `GET /api/users` | Manter como está (lista membros da empresa para assignment de tarefas) |

---

## 12. Ordem de Implementação (sprints)

### Sprint 1 — Base
1. Migration `009_users_admin_project_roles.sql` (inclui `admin_audit_log`)
2. `UserDTO`, atualizar `UserAuthRecord` com `isAdmin`
3. Atualizar `PdoUserRepository` para incluir `is_admin`
4. Atualizar `AuthService` para salvar `user_is_admin` na sessão
5. `AdminMiddleware` (revalida no banco)
6. `CreateUserValidator` e `UpdateUserValidator` com política de senha

### Sprint 2 — CRUD de Usuários
7. `CreateUserService`, `UpdateUserService`, `ToggleUserStatusService`, `ResetPasswordService`
8. `UserController` com todos os métodos (incluindo reset-password)
9. Registrar rotas `/api/admin/users` em `api.php`
10. Template `admin-users.php` com tabela + modal de criação/edição/reset
11. Rota web `/admin/users` em `web.php`

### Sprint 3 — Membros e Permissões de Projeto
12. `ProjectMemberDTO`
13. `PdoProjectMemberRepository`
14. Métodos `resolveProjectId()` em `TaskRepository` e `ColumnRepository`
15. `AddMemberService` (com validação de `company_id`), `UpdateMemberRoleService`, `RemoveMemberService`
16. `InviteMemberValidator`
17. `ProjectMemberController`
18. `ProjectPolicy` (incluindo `canAlterRoles()`)
19. Registrar rotas `/api/project-members` em `api.php`
20. Atualizar `ProjectController::create()` para inserir owner em `project_members`
21. Aplicar duplo guard em Board, Column, Task controllers
22. Ajuste em `PdoTaskRepository::findByColumnId()` para assignee gracioso
23. Template `project-members.php`
24. Rota web `/projects/{id}/members` em `web.php`

### Sprint 4 — Testes
25. Testes unitários: Services, Validators, ProjectPolicy
26. Testes de integração: Repositories (SQLite)
27. Testes funcionais/API: endpoints admin e project-members
28. Testes de segurança: IDOR cross-tenant, acesso sem papel suficiente, último admin

---

## 13. Critérios de Aceite

- [ ] Admin consegue criar, editar, ativar/desativar, redefinir senha e excluir usuários.
- [ ] Não-admin recebe 403 ao acessar `/api/admin/*`.
- [ ] AdminMiddleware revalida `is_admin` no banco (não usa cache de sessão).
- [ ] Senha com menos de 8 chars, sem maiúscula ou sem número é rejeitada com mensagem clara.
- [ ] Sistema bloqueia remoção ou desativação do último admin da empresa.
- [ ] Sistema bloqueia soft-delete de `owner` que ainda possui projetos ativos, retornando lista dos projetos.
- [ ] Toda ação administrativa (criar/editar/desativar/deletar usuário) gera registro em `admin_audit_log`.
- [ ] Criador de projeto é inserido automaticamente como `owner` em `project_members`.
- [ ] Owner/manager pode adicionar membros com papel `editor` ou `viewer`.
- [ ] Apenas `owner` pode alterar o papel de membros existentes (PATCH).
- [ ] Usuário com papel `viewer` recebe 403 ao tentar criar/mover tarefas.
- [ ] Usuário com papel `editor` consegue criar e mover tarefas.
- [ ] Usuário autenticado **não** consegue acessar recursos de outra empresa (tenant isolation).
- [ ] `AddMemberService` rejeita `user_id` de empresa diferente da sessão.
- [ ] Tasks com `assigned_to` de usuário deletado exibem "Usuário removido" sem erro.
- [ ] Sessão armazena `user_is_admin` apenas para UI; ações críticas revalidam no banco.
- [ ] Todos os novos endpoints têm cobertura de teste.

---

## 14. Decisões Arquiteturais Registradas

| Decisão | Alternativa descartada | Motivo |
|---------|------------------------|--------|
| Coluna `is_admin` em `users` | Usar tabela `user_roles` existente | Simplicidade — papel admin é binário e global; `user_roles` existe para futura expansão |
| Sem token de convite por e-mail | Fluxo de e-mail com link | Fora do escopo desta versão; sistema não tem SMTP configurado |
| `ProjectPolicy` como classe utilitária | Middleware por rota | Políticas precisam de contexto (`project_id`) que varia por chamada; middleware global seria complexo demais |
| Soft-delete de usuário (`status='deleted'`) | DELETE físico com CASCADE | Preserva integridade referencial com tarefas, comentários e histórico atribuídos ao usuário |
| `AdminMiddleware` revalida no banco | Confiar em `user_is_admin` da sessão | Sessão pode estar desatualizada se outro admin revogar privilégios; segurança crítica exige fonte de verdade |
| Duplo guard (tenant + policy) nos controllers | Policy única que faz tudo | Separação de responsabilidades — isolamento de tenant e autorização de papel são concerns distintos; 404 no tenant evita enumeração |
| Bloquear soft-delete de owner com projetos ativos | Auto-promover manager a owner | Mais seguro — transferência de ownership é decisão humana explícita |
| `resolveProjectId()` nos repositories | Receber `project_id` direto no request | Não expor `project_id` desnecessariamente na API; resolver internamente evita spoofing |
| Audit log em tabela dedicada (`admin_audit_log`) | Log em arquivo | Consultável via SQL, associável a usuários, preservado com o banco |
