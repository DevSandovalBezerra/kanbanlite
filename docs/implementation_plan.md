# Implementation Plan: Functional Kanban Board

This plan outlines the steps to implement the core Kanban functionality (Projects, Boards, Columns, and Tasks) following a TDD-first approach and the Material/Scribbs design system.

## Proposed Changes

### Database & Persistence
- **[EXECUTE]** `php database/migrate.php` to initialize the MVP tables.
- **[NEW]** `app/Repositories/PdoBoardRepository.php`: Implementation of board storage.
- **[NEW]** `app/Repositories/PdoColumnRepository.php`: Implementation of column storage (handling position).
- **[NEW]** `app/Repositories/PdoTaskRepository.php`: Implementation of task storage (handling moves and position).

### Business Logic (Services)
- **[NEW]** `app/Services/Board/CreateBoardService.php`
- **[NEW]** `app/Services/Column/CreateColumnService.php`
- **[NEW]** `app/Services/Task/CreateTaskService.php`
- **[NEW]** `app/Services/Task/MoveTaskService.php`: Core logic for drag & drop updates (atomic).
- **[NEW]** `app/Services/Task/ArchiveTaskService.php`

### API & Routing
- **[MODIFY]** `routes/api.php`: Add endpoints for Boards, Columns, and Tasks as specified in the PRD (Section 41).
- **[NEW]** `app/Controllers/BoardController.php`
- **[NEW]** `app/Controllers/ColumnController.php`
- **[NEW]** `app/Controllers/TaskController.php`

### Frontend (User Interface)
- **[MODIFY]** `routes/web.php`: Add GET `/boards/{id}` route.
- **[NEW]** `templates/default/pages/kanban.php`: Main board view using Material Design 3.
- **[NEW]** `templates/default/assets/css/kanban.css`: Tailwind/Vanilla mix for Scribbs aesthetic.
- **[NEW]** `templates/default/assets/js/kanban.js`: Vanilla JS for Drag & Drop and API integration.

---

## Verification Plan

### Automated Tests
- **Backend Unit Tests**: For each Service, create a corresponding test in `tests/Unit/Services/` (e.g., `MoveTaskServiceTest.php`).
- **Integration Tests**: Verify PDO repositories against SQLite (mapped in `tests/Integration/`).
- **Functional Tests**: Test API endpoints using `phpunit` (mapped in `tests/Functional/Api/`).

**Command**: `vendor/bin/phpunit`

### Manual Verification
1. **Login**: Authenticate as a user.
2. **Setup**: Create a Project and a Board.
3. **Column Management**: Create "To Do", "Doing", "Done" columns.
4. **Task Management**:
    - Create a task in "To Do".
    - Drag the task to "Doing".
    - Verify the API call and visual feedback (status change).
    - Archive the task and verify it disappears from the active board.
5. **Responsiveness**: Check board Layout on tablet/mobile view (horizontal scroll).
