# Plano de Melhorias: Kanban Card Corporativo

> **Contexto:** O projeto já possui as bases do card (título, descrição, prioridade, responsável, deadline, status).
> Este plano foca nas funcionalidades **ausentes ou incompletas** identificadas para um Kanban corporativo robusto.

---

## Diagnóstico: O que já existe vs o que falta

| Campo | Status Atual | Ação Necessária |
|-------|-------------|-----------------|
| Título | ✅ Implementado (`tasks.title`) | Nenhuma |
| Descrição (texto) | ✅ Implementado (`tasks.description`) | Adicionar suporte a rich text |
| Prioridade | ✅ Implementado (`tasks.priority`: low/medium/high) | Adicionar nível `critical` |
| Responsável | ✅ Implementado (`tasks.assigned_to`) | Nenhuma |
| Data de vencimento | ✅ Implementado (`tasks.deadline`) | Nenhuma |
| Status workflow | ✅ Implementado (`tasks.status`) | Nenhuma |
| Comentários | ⚠️ Tabela existe, controller incompleto | Completar implementação |
| Histórico / Audit | ⚠️ Tabela existe, não é preenchida | Ativar tracking |
| **Anexos / Imagens** | ❌ Ausente | Implementar do zero |
| **Labels / Tags** | ❌ Ausente | Implementar do zero |
| **Checklist / Subtarefas** | ❌ Ausente | Implementar do zero |
| **Story Points** | ❌ Ausente | Adicionar coluna na tabela |
| **Dependências** | ❌ Ausente | Implementar do zero |

---

## Melhorias por Fase

---

### FASE 1 — Completar Implementações Existentes
**Prioridade: Alta | Esforço: Baixo**

#### 1.1 — Completar Sistema de Comentários

**Objetivo:** A tabela `task_comments` já existe. Falta a camada de API e UI.

**Database:** Nenhuma alteração necessária.
```sql
-- Tabela já existente:
-- task_comments (id, task_id, user_id, body, created_at)
```

**Arquivos a criar:**
```
app/DTO/CommentDTO.php
app/Repositories/CommentRepository.php          ← interface
app/Repositories/PdoCommentRepository.php       ← implementação PDO
app/Services/Comment/CreateCommentService.php
app/Services/Comment/DeleteCommentService.php
app/Controllers/CommentController.php
```

**Rotas a adicionar em `routes/api.php`:**
```
GET    /api/tasks/{id}/comments     → CommentController::index()
POST   /api/tasks/{id}/comments     → CommentController::create()
DELETE /api/comments/{id}           → CommentController::delete()
```

**UI:** Feed de comentários no modal de detalhes do card (já existe o modal em `kanban.php`).

---

#### 1.2 — Ativar Histórico / Audit Trail

**Objetivo:** A tabela `task_history` já existe mas não é alimentada. Ativar o tracking automático nas operações de Task.

**Arquivos a modificar:**
- `app/Repositories/PdoTaskRepository.php` — adicionar chamadas de insert em `task_history` nas operações `update()` e `move()`
- `app/Services/Task/MoveTaskService.php` — logar a mudança de coluna

**Payload do histórico:**
```php
// Exemplo de entrada na task_history:
[
  'task_id'   => $taskId,
  'action'    => 'moved',          // ou 'updated', 'priority_changed', etc.
  'old_value' => json_encode(['column_id' => $from]),
  'new_value' => json_encode(['column_id' => $to]),
  'user_id'   => $currentUserId,
]
```

---

### FASE 2 — Novos Campos no Card
**Prioridade: Alta | Esforço: Médio**

#### 2.1 — Labels / Tags

**Objetivo:** Etiquetas coloridas para categorização visual de cards.

**Migration:**
```sql
-- arquivo: database/migrations/mysql/004_labels.sql

CREATE TABLE labels (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id  INT UNSIGNED NOT NULL,
    name        VARCHAR(60)  NOT NULL,
    color       VARCHAR(7)   NOT NULL DEFAULT '#6200EE',  -- hex color
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    UNIQUE KEY uq_company_label (company_id, name)
);

CREATE TABLE task_labels (
    task_id     INT UNSIGNED NOT NULL,
    label_id    INT UNSIGNED NOT NULL,
    PRIMARY KEY (task_id, label_id),
    FOREIGN KEY (task_id)  REFERENCES tasks(id)  ON DELETE CASCADE,
    FOREIGN KEY (label_id) REFERENCES labels(id) ON DELETE CASCADE
);
```

**Arquivos a criar:**
```
app/DTO/LabelDTO.php
app/Repositories/LabelRepository.php
app/Repositories/PdoLabelRepository.php
app/Services/Label/CreateLabelService.php
app/Services/Label/AttachLabelService.php
app/Services/Label/DetachLabelService.php
app/Controllers/LabelController.php
```

**Rotas:**
```
GET    /api/labels                  → LabelController::index()       (por company)
POST   /api/labels                  → LabelController::create()
DELETE /api/labels/{id}             → LabelController::delete()
POST   /api/tasks/{id}/labels       → LabelController::attach()
DELETE /api/tasks/{id}/labels/{lid} → LabelController::detach()
```

**TaskDTO:** Adicionar campo `labels: array` populado via JOIN no `PdoTaskRepository::findById()`.

**UI:** Pílulas coloridas no card e seletor de tags no modal de detalhes.

---

#### 2.2 — Checklist / Subtarefas

**Objetivo:** Lista de itens menores com progresso visual (ex: `3/5 concluídos`).

**Migration:**
```sql
-- arquivo: database/migrations/mysql/005_checklists.sql

CREATE TABLE task_checklists (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id     INT UNSIGNED NOT NULL,
    title       VARCHAR(120) NOT NULL DEFAULT 'Checklist',
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
);

CREATE TABLE task_checklist_items (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    checklist_id INT UNSIGNED NOT NULL,
    body         VARCHAR(255) NOT NULL,
    is_done      TINYINT(1)   NOT NULL DEFAULT 0,
    position     SMALLINT     NOT NULL DEFAULT 0,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (checklist_id) REFERENCES task_checklists(id) ON DELETE CASCADE
);
```

**Arquivos a criar:**
```
app/DTO/ChecklistDTO.php
app/DTO/ChecklistItemDTO.php
app/Repositories/ChecklistRepository.php
app/Repositories/PdoChecklistRepository.php
app/Services/Checklist/CreateChecklistService.php
app/Services/Checklist/AddChecklistItemService.php
app/Services/Checklist/ToggleChecklistItemService.php
app/Services/Checklist/DeleteChecklistItemService.php
app/Controllers/ChecklistController.php
```

**Rotas:**
```
POST   /api/tasks/{id}/checklists              → ChecklistController::create()
POST   /api/checklists/{id}/items              → ChecklistController::addItem()
PATCH  /api/checklist-items/{id}/toggle        → ChecklistController::toggle()
DELETE /api/checklist-items/{id}               → ChecklistController::deleteItem()
```

**UI:**
- Barra de progresso no card resumido (ex: `▓▓▓░░ 3/5`)
- Lista interativa de checkboxes no modal de detalhes

---

#### 2.3 — Story Points / Estimativa de Esforço

**Objetivo:** Campo numérico simples para estimativa de complexidade (escala Fibonacci: 1, 2, 3, 5, 8, 13).

**Migration:**
```sql
-- arquivo: database/migrations/mysql/006_story_points.sql

ALTER TABLE tasks
    ADD COLUMN story_points TINYINT UNSIGNED NULL DEFAULT NULL
    COMMENT 'Fibonacci: 1,2,3,5,8,13,21'
    AFTER priority;
```

**Arquivos a modificar:**
- `app/DTO/TaskDTO.php` — adicionar propriedade `storyPoints: ?int`
- `app/Repositories/PdoTaskRepository.php` — incluir `story_points` nos SELECTs e INSERTs
- `app/Controllers/TaskController.php` — aceitar `story_points` no payload de create/update

**UI:** Badge numérico no card + campo select no modal com os valores Fibonacci.

---

### FASE 3 — Sistema de Anexos e Imagens
**Prioridade: Média | Esforço: Alto**

#### 3.1 — Upload de Arquivos / Imagens

**Objetivo:** Permitir anexar arquivos (imagens, PDFs, docs) a um card.

**Configuração de Storage:**
```
public/uploads/tasks/{task_id}/{uuid}.{ext}
```

**Migration:**
```sql
-- arquivo: database/migrations/mysql/007_attachments.sql

CREATE TABLE task_attachments (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id     INT UNSIGNED NOT NULL,
    uploaded_by INT UNSIGNED NOT NULL,
    filename    VARCHAR(255) NOT NULL,   -- nome original
    filepath    VARCHAR(500) NOT NULL,   -- caminho relativo no servidor
    mime_type   VARCHAR(100) NOT NULL,
    size_bytes  INT UNSIGNED NOT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id)     REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);
```

**Arquivos a criar:**
```
app/DTO/AttachmentDTO.php
app/Repositories/AttachmentRepository.php
app/Repositories/PdoAttachmentRepository.php
app/Services/Attachment/UploadAttachmentService.php   ← validação + move_uploaded_file()
app/Services/Attachment/DeleteAttachmentService.php   ← remove arquivo físico + registro
app/Controllers/AttachmentController.php
```

**Rotas:**
```
POST   /api/tasks/{id}/attachments   → AttachmentController::upload()   (multipart/form-data)
DELETE /api/attachments/{id}         → AttachmentController::delete()
GET    /api/tasks/{id}/attachments   → AttachmentController::index()
```

**Regras de validação:**
- Tamanho máximo: 10 MB por arquivo
- Tipos permitidos: `image/jpeg`, `image/png`, `image/gif`, `image/webp`, `application/pdf`, `text/plain`, `application/msword`, `application/vnd.openxmlformats-officedocument.wordprocessingml.document`
- Geração de UUID v4 para evitar colisões de nomes

**UI:**
- Área de drop (drag & drop) no modal do card
- Galeria de thumbnails para imagens
- Lista de arquivos com ícone por tipo e botão de download/exclusão

---

#### 3.2 — Rich Text na Descrição (com imagens embutidas)

**Objetivo:** Substituir o textarea simples por um editor rico com suporte a imagens inline.

**Abordagem recomendada:** Editor leve em Vanilla JS (sem dependências pesadas).

**Opções avaliadas:**
| Editor | Peso | Licença | Recomendação |
|--------|------|---------|--------------|
| **Quill.js** | ~400 KB | BSD | ✅ Recomendado |
| TipTap | ~200 KB | MIT | ✅ Alternativa |
| TinyMCE | ~2 MB | LGPL | ❌ Muito pesado |

**Implementação:**
1. Incluir Quill.js via CDN no `layouts/base.html.php`
2. Configurar toolbar com: bold, italic, link, image upload, listas
3. Imagens: upload via endpoint `/api/tasks/{id}/attachments` e inserção como tag `<img>` no HTML
4. Armazenar conteúdo como HTML sanitizado no campo `tasks.description` (ampliar para TEXT/LONGTEXT se necessário)

**Sanitização server-side obrigatória** (prevenir XSS):
```php
// Usar HTMLPurifier ou strip_tags com allowlist
$allowed = '<p><br><strong><em><ul><ol><li><a><img><h2><h3><blockquote>';
$safeHtml = strip_tags($rawHtml, $allowed);
```

---

### FASE 4 — Dependências entre Cards
**Prioridade: Baixa | Esforço: Médio**

#### 4.1 — Task Dependencies

**Objetivo:** Indicar que um card bloqueia ou é bloqueado por outro.

**Migration:**
```sql
-- arquivo: database/migrations/mysql/008_dependencies.sql

CREATE TABLE task_dependencies (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id        INT UNSIGNED NOT NULL,   -- card que depende
    depends_on_id  INT UNSIGNED NOT NULL,   -- card bloqueante
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (task_id, depends_on_id),
    FOREIGN KEY (task_id)       REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (depends_on_id) REFERENCES tasks(id) ON DELETE CASCADE,
    CHECK (task_id != depends_on_id)
);
```

**Arquivos a criar:**
```
app/Services/Task/AddDependencyService.php     ← valida ciclos antes de inserir
app/Services/Task/RemoveDependencyService.php
app/Controllers/DependencyController.php
```

**Rotas:**
```
POST   /api/tasks/{id}/dependencies          → DependencyController::add()
DELETE /api/tasks/{id}/dependencies/{depId}  → DependencyController::remove()
```

**UI:** Seção "Bloqueado por" no modal com links para os cards dependentes.

---

## Resumo de Arquivos a Criar / Modificar

### Novos Arquivos (total: ~35)
```
database/migrations/mysql/
  004_labels.sql
  005_checklists.sql
  006_story_points.sql
  007_attachments.sql
  008_dependencies.sql

app/DTO/
  CommentDTO.php
  LabelDTO.php
  ChecklistDTO.php
  ChecklistItemDTO.php
  AttachmentDTO.php

app/Repositories/
  CommentRepository.php          + PdoCommentRepository.php
  LabelRepository.php            + PdoLabelRepository.php
  ChecklistRepository.php        + PdoChecklistRepository.php
  AttachmentRepository.php       + PdoAttachmentRepository.php

app/Services/
  Comment/CreateCommentService.php
  Comment/DeleteCommentService.php
  Label/CreateLabelService.php
  Label/AttachLabelService.php
  Label/DetachLabelService.php
  Checklist/CreateChecklistService.php
  Checklist/AddChecklistItemService.php
  Checklist/ToggleChecklistItemService.php
  Checklist/DeleteChecklistItemService.php
  Attachment/UploadAttachmentService.php
  Attachment/DeleteAttachmentService.php
  Task/AddDependencyService.php
  Task/RemoveDependencyService.php

app/Controllers/
  CommentController.php
  LabelController.php
  ChecklistController.php
  AttachmentController.php
  DependencyController.php
```

### Arquivos a Modificar
```
app/DTO/TaskDTO.php                    ← + storyPoints, labels[]
app/Repositories/PdoTaskRepository.php ← + story_points, history tracking
app/Controllers/TaskController.php     ← + story_points no payload
routes/api.php                         ← + novas rotas
templates/default/pages/kanban.php     ← + UI dos novos campos no modal
public/assets/js/kanban.js             ← + handlers para novos endpoints
```

---

## Ordem de Implementação Recomendada

```
Sprint 1 (Completar existentes)
  └─ 1.1 Comentários
  └─ 1.2 Histórico / Audit

Sprint 2 (Novos campos simples)
  └─ 2.1 Labels / Tags
  └─ 2.3 Story Points

Sprint 3 (Checklist)
  └─ 2.2 Checklist / Subtarefas

Sprint 4 (Arquivos)
  └─ 3.1 Anexos e upload
  └─ 3.2 Rich text na descrição

Sprint 5 (Avançado)
  └─ 4.1 Dependências entre cards
```

---

## Convenções a Seguir

- **Padrão de código:** PHP 8.3+, `declare(strict_types=1)`, classes `readonly` para DTOs
- **Repositórios:** sempre implementar interface + classe PDO concreta
- **Testes:** cada nova feature deve ter unit test (Service) + integration test (Repository)
- **SQL:** usar prepared statements em todos os repositórios
- **Uploads:** nunca salvar arquivos fora de `public/uploads/`, validar MIME server-side
- **Sanitização:** sempre sanitizar HTML da descrição server-side (HTMLPurifier ou strip_tags com allowlist)
- **Migrations:** numerar sequencialmente e nunca alterar migrations já executadas
