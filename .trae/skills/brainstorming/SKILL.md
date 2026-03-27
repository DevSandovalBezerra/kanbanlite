---
name: "brainstorming"
description: "Gera um plano de implementação e analisa impactos/risco. Invoque quando o usuário pedir um plano, estudo de impacto ou alternativas antes de codar."
---

# Brainstorming

## Objetivo

Produzir rapidamente um plano acionável para mudanças complexas, incluindo impacto em banco de dados, backend, UI, segurança, testes e migração.

## Quando invocar

Invoque quando o usuário pedir explicitamente por:
- um plano (ex.: “faça um plano”, “roadmap”, “passos”)
- estudo de impacto (ex.: “impacto”, “quais partes quebram”, “migração”)
- alternativas de arquitetura/implementação
- avaliação de risco e medidas de mitigação

## Saída esperada

1. **Resumo do objetivo**: o que muda do ponto de vista do usuário.
2. **Assunções**: decisões tomadas sem confirmação (para registrar e ajustar depois).
3. **Impacto por camada**:
   - Banco: tabelas novas/alteradas, índices, migração e backfill.
   - Backend: rotas, políticas/autorização, serviços, repositórios.
   - UI: páginas, menus, fluxos, validações.
   - Segurança: acesso, criptografia, auditoria, logs, CSRF.
   - Performance: consultas críticas, cache, N+1.
4. **Plano de implementação**: passos em ordem segura (com checkpoints).
5. **Plano de testes**: unit, integração, funcional, casos de borda.
6. **Plano de rollout**: migração, compatibilidade, flags (se necessário).

## Checklist de impacto (perguntas)

- O que passa a ser “por empresa” vs “por projeto”?
- Existe acesso por link direto (ID) que precisa negar?
- Há endpoints API consumidos pelo frontend que precisam filtrar?
- Quais consultas precisam de índice para `project_id`?
- O dado novo é sensível? Precisa criptografar em repouso?
- Quem pode ver/editar? Dono do projeto, admin, convidados?

## Template de resposta (copiar/colar)

### Objetivo

...

### Assunções

- ...

### Impacto

- Banco: ...
- Backend: ...
- UI: ...
- Segurança: ...
- Testes: ...

### Plano

1. ...

