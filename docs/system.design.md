# System Design: Kanban Modular Enterprise

## 1. Design Philosophy
The interface is based on **Material Design 3 (M3)** infused with a clean, content-focused "Scribble" aesthetic. The goal is a premium, professional tool that feels light yet powerful.

## 2. Visual Identity (Tokens)

### Color Palette (Tailwind-compatible)
| Role | Color (HSL/Hex) | Usage |
| :--- | :--- | :--- |
| **Primary** | #6200EE (Deep Violet) | Actions, selected states |
| **Secondary** | #03DAC6 (Teal) | Accents, success sub-states |
| **Background** | #F8F9FA (Light Gray) | Main workspace background |
| **Surface** | #FFFFFF (White) | Cards, modals, sidebars |
| **Text Primary** | #1C1B1F (Deep Charcoal) | Headlines, body text |
| **Text Secondary**| #49454F (Muted Gray) | Supporting text, captions |

### Status Colors
| Status | Color | Description |
| :--- | :--- | :--- |
| **To Do** | Gray-200 | Initial state, awaiting action |
| **In Progress** | Blue-500 | Active work |
| **Review** | Amber-500 | Awaiting feedback/validation |
| **Done** | Green-500 | Completed tasks |
| **Blocked** | Red-500 | Issues preventing progress |

### Priority Badges
- **High**: Red-100 Text: Red-800 + Icon (Triangle)
- **Medium**: Orange-100 Text: Orange-800 + Icon (Circle)
- **Low**: Blue-100 Text: Blue-800 + Icon (Square)

## 3. Component Design

### Kanban Board
- **Layout**: Horizontal scrolling container with `display: flex`.
- **Columns**:
    - Minimum width: 300px, Maximum width: 400px.
    - Header: Sticky, contains Title, Task Count, and "More" menu.
    - Empty State: Dotted border with a "Create Task" placeholder.
- **Drag & Drop**:
    - Ghost state: Card becomes semi-transparent when dragged.
    - Placeholder: Highlighted area showing where the card will land.

### Task Cards
- **Structure**:
    - **Header**: Tags/Priority and Task ID.
    - **Body**: Bold Title, truncated Description (max 3 lines).
    - **Footer**: Assignee (Avatar), Due Date (with icon), Comment/Attachment icons with counts.
- **Elevation**: Level 1 (Resting), Level 3 (Hover/Dragging).
- **Transitions**: 200ms ease-in-out for hover and movement.

### Typography
- **Headfont**: Inter or Roboto (Modern Sans-serif).
- **Scale**:
    - H1: 24px (Board Title)
    - H2: 18px (Column Title)
    - Body: 14px (Card text)
    - Caption: 12px (Meta info)

## 4. User Experience (Interaction)
- **Modal View**: Clicking a card opens a full-screen or large modal for details, comments, and history.
- **Micro-interactions**: 
    - Subtle bounce when a card is dropped.
    - Ripple effects on buttons (Material standard).
    - Progress bars for tasks with checklists.

## 5. Technical Stack (UI)
- **Styling**: Tailwind CSS (for tokens) + Vanilla CSS (for complex layouts).
- **Interactions**: Vanilla JS (Drag & Drop API or lightweight library like SortableJS).
- **Icons**: Material Symbols (Outlined) for a modern look.
