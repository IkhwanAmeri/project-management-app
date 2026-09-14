# Project Management App

Laravel 12 project-management application using Laravel Breeze (Blade).

## Current Features

### Authentication

- Registration, login, logout, password reset, and email verification through Breeze.
- Project and task pages require an authenticated, verified user.

### Dashboard

The dashboard displays live data for the signed-in user's projects:

- Project count
- Open tasks (Todo, In Progress, Review)
- Tasks completed today
- Overdue tasks
- Up to five open tasks assigned to the user (with due-date colour coding)
- Recent comments from other project members (latest 5)

### Projects and Members

- Create, view, edit, and soft-delete projects.
- The creator is automatically added as the project `Owner`.
- Add `Manager` and `Member` users while creating or editing a project.
- View project information, dates, members, roles, and tasks.
- Slugs are generated automatically from project names.
- Project statuses: `planning`, `active`, `completed`, `cancelled`.

### Tasks and Subtasks

- Create, view, edit, soft-delete, and complete tasks.
- Assign tasks only to a member of the selected project.
- Search by title; filter by status, priority, and assignee; sort by due date.
- Statuses: `Todo`, `In Progress`, `Review`, `Completed`, `Cancelled`.
- Priorities: `Low`, `Medium`, `High`, `Critical`.
- Completing a task automatically fills `completed_at`.
- Create subtasks from any task using `parent_task_id`.
- Estimated hours and actual hours fields.

### Task Quick Actions

- **Duplicate** a task — creates a copy with `Todo` status and no `completed_at`.
- **Change status** from the task detail page (Owner/Manager only).
- **Change priority** from the task detail page (Owner/Manager only).
- **Assign / unassign** tasks to project members (Owner/Manager only).

### Kanban Board

- Per-project board at `/projects/{project}/kanban` with four columns: Todo, In Progress, Review, Completed.
- Drag-and-drop to move tasks between columns (Owner/Manager only).
- Cards display title, priority badge, due date, assignee avatar, and comment/attachment/subtask counts.
- Progress bar shows subtask completion percentage on each card.

### Task Comments

- Add comments to any task from the task detail page.
- Comments display the author name, relative timestamp, and an "edited" indicator when applicable.
- Recent comments from project members appear on the dashboard.
- The `CommentService` supports create, update, and delete operations.

### Task Attachments

- Upload files (max 10 MB) to any task from the task detail page.
- Download attachments with their original filename.
- Files are stored on the `public` disk under `tasks/{task_id}/`.
- The `AttachmentService` supports create and delete operations.

### Task Assignment Notifications

Task assignment uses this event flow:

```text
Manager
  -> TaskService
  -> TaskAssigned event
  -> SendTaskAssignedNotification listener
  -> TaskAssignedNotification
  -> notifications database table
```

Notifications are stored in the database. A notification dropdown in the navigation bar shows unread notifications with a count badge and individual "Mark read" buttons. The dropdown appears on both desktop and mobile layouts.

### Upcoming Deadline Reminders

- A scheduled command `tasks:notify-due-soon` runs daily at 08:00 and sends a database notification to every assignee whose task is due **tomorrow** and still open (`Todo`, `In Progress`, `Review`).
- Reminders are never duplicated per task/deadline (the command skips tasks already reminded for that date) and never fire for completed, cancelled, or unassigned tasks.
- Run it in dev with `php artisan schedule:work` (or run once with `php artisan tasks:notify-due-soon`). In production add a cron entry: `* * * * * php artisan schedule:run`.

### Activity Logging

- All major actions are logged through `ActivityService`: project creation, task creation/update/deletion, status changes, priority changes, assignment changes, duplication, subtask creation, comments, and file uploads.
- Activity records are stored in the `activities` table with user, project, task, action, description, and JSON properties.
- Timeline view at `/activities` shows a chronological feed with icons per action type and inline status/priority badges.
- Detail view at `/activities/{activity}` shows the full event with user, project, task link, and properties table.

### Reports

- Report hub at `/reports` with two report types.
- **User Task Report** — per-member task breakdown by status and priority, overdue count, completion rate, estimated/actual hours, and a task table. Defaults to your own tasks; Owners/Managers can pick any member of a project they manage.
- **Project Report** — progress, status/priority breakdown, completion rate, hours, unassigned tasks, member workload (assigned/open/estimated per member), and a task table.
- Both reports export as **CSV** (`?format=csv`) or **PDF** (`?format=pdf`).

### REST API (v1)

- JSON API versioned under `/api/v1`, authenticated with Laravel Sanctum bearer tokens.
- Endpoints: login/logout/current user, project CRUD, and task CRUD (nested under projects or standalone) — scoped to the caller's project membership and role.
- Consistent JSON error responses (`401`, `403`, `404`, `422`, `500`) that never leak internal details.
- Complete endpoint reference with request/response examples: [`docs/api.md`](docs/api.md).

## Structure

```text
app/
|-- Events/
|   `-- TaskAssigned.php
|-- Http/
|   |-- Controllers/
|   |   |-- Api/V1/ (3 REST API controllers for auth, projects, tasks)
|   |   |-- Auth/ (9 Breeze auth controllers)
|   |   |-- ActivityController.php
|   |   |-- CalendarController.php
|   |   |-- DashboardController.php
|   |   |-- NotificationController.php
|   |   |-- ProfileController.php
|   |   |-- ProjectController.php
|   |   |-- TaskAttachmentController.php
|   |   |-- TaskCommentController.php
|   |   `-- TaskController.php
|   `-- Requests/
|       |-- Api/V1/ (request validation for the REST API, incl. ApiRequest base)
|       |-- Auth/
|       |   `-- LoginRequest.php
|       |-- ProfileUpdateRequest.php
|       |-- StoreProjectRequest.php
|       |-- StoreTaskAttachmentRequest.php
|       |-- StoreTaskCommentRequest.php
|       |-- StoreTaskRequest.php
|       |-- UpdateProjectRequest.php
|       |-- UpdateTaskRequest.php
|       `-- UpdateTaskStatusRequest.php
|-- Listeners/
|   `-- SendTaskAssignedNotification.php
|-- Models/
|   |-- Activity.php
|   |-- Project.php
|   |-- ProjectMember.php
|   |-- Task.php
|   |-- TaskAttachment.php
|   `-- TaskComment.php
|-- Notifications/
|   |-- TaskAssignedNotification.php
|   `-- TaskCompletedNotification.php
|-- Policies/
|   |-- ProjectPolicy.php
|   `-- TaskPolicy.php
|-- Resources/ (API resources: User, Project, Task, Comment)
`-- Services/
    |-- ActivityService.php
    |-- AttachmentService.php
    |-- CommentService.php
    |-- ProjectService.php
    `-- TaskService.php
```

## API Documentation

See [`docs/api.md`](docs/api.md) for the full REST API reference: authentication, authorization roles, endpoints with request bodies and examples, validation rules, and error responses.

## Setup

Requirements: PHP 8.2+, Composer, Node.js/npm, and MySQL.

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
```

Set your MySQL connection in `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=project_management
DB_USERNAME=root
DB_PASSWORD=
```

Create the database if necessary, then run:

```bash
php artisan migrate
php artisan storage:link
npm run build
php artisan serve
```

For frontend development, use `npm run dev`. Visit `http://127.0.0.1:8000`.

## Manual Testing Guide

### Authentication

1. Register two or more users at `/register`.
2. Verify the email if required by your local setup.
3. Log in and open `/dashboard`.

### Projects and Members

1. Open **Projects** and click **New project**.
2. Complete the project fields.
3. Under **Add Members**, click **Add another**, select a registered user, and choose `Member` or `Manager`.
4. Submit and confirm the creator is `Owner` and selected users appear under **Members**.
5. Edit the project and add another member; existing members should remain.

### Tasks and Subtasks

1. Open a project and click **Create Task**.
2. Enter the title, priority, status, project-member assignee, dates, and estimated hours.
3. Submit and confirm the task appears in the project task list.
4. Open the task and check project, assignee, creator, status, priority, and hours.
5. Click **Add Subtask**, create a subtask, and confirm it appears under **Subtasks** on the parent task.
6. Click **Mark Completed** and confirm the status changes to `Completed`.

### Task Search and Filters

1. Open **Tasks** in the navigation.
2. Search by task title.
3. Filter by status, priority, and assignee.
4. Switch sorting between due date, newest, oldest, priority, and recently updated.
5. Use the **Complete** action for an unfinished task.

### Task Quick Actions

1. Open a task detail page as Owner or Manager.
2. Click the **...** menu to see Duplicate, Change Status, Change Priority, and Assign options.
3. **Duplicate** — confirm a copy appears with `Todo` status.
4. **Change Status** — pick a new status and confirm it updates.
5. **Change Priority** — pick a new priority and confirm it updates.
6. **Assign** — pick a project member and confirm the assignee changes.
7. Open the same task as a Member — confirm the quick action menu is hidden.

### Kanban Board

1. Open a project and click **Board** in the header.
2. Confirm tasks appear in the correct status columns.
3. Drag a task card from one column to another (Owner or Manager only).
4. Confirm the status updates and the card moves to the new column.
5. Reload the page and confirm the task stayed in the new column.
6. Open as a Member — confirm attempting to drag a card shows an error toast and the card does not move.

### Task Comments

1. Open a task detail page.
2. Type a comment in the form and submit.
3. Confirm the comment appears with your name and timestamp.
4. Verify recent comments appear on the dashboard.

### Task Attachments

1. Open a task detail page.
2. Choose a file (max 10 MB) and submit the attachment form.
3. Confirm the file appears in the attachments list with its name, size, and uploader.
4. Click the download link and verify the file downloads correctly.

### Notifications

1. Log in as user A and open a project.
2. Create or edit a task and assign it to user B (another project member).
3. Log in as user B.
4. Check the bell icon in the navigation bar for an unread notification count.
5. Open the notification dropdown and verify the assignment notification appears.
6. Click **Mark read** and confirm the notification disappears from the dropdown.

### Dashboard

1. Create tasks with different statuses and due dates.
2. Assign some tasks to the signed-in user.
3. Add comments on tasks in your projects.
4. Open `/dashboard` and confirm its metrics, **My Tasks** list, and recent comments update.

### Activity Log

1. Click **Activity** in the navigation bar.
2. Confirm the timeline shows your recent actions with correct icons and descriptions.
3. Click an activity to open the detail page.
4. Confirm the detail page shows user, project, task link, and properties.
5. Try accessing an activity from another project as a non-member — confirm 403 Forbidden.

### Reports

1. Open **Reports** in the navigation bar.
2. Under **User Task Report**, pick a project (or leave empty for all projects) and click **Generate report**.
3. Confirm the stat cards, status/priority bars, hours, and the task table reflect your tasks. Owners/Managers can switch to another member when a project is selected.
4. Click **Download CSV** and **Download PDF** to verify both exports.
5. Open a **Project Report** from the hub and confirm member workload plus the task table render, then download the CSV/PDF.

## Automated Checks

Run all tests:

```bash
php artisan test
```

Run a specific test file:

```bash
php artisan test --filter=TaskDueSoonCommandTest
php artisan test --filter=KanbanDragDropTest
php artisan test --filter=ActivityLoggingTest
php artisan test --filter=Phase5ReviewTest
php artisan test --filter=TaskAssignmentNotificationTest
```

Verify event discovery:

```bash
php artisan event:list
```

Expected listener:

```text
App\Events\TaskAssigned
  -> App\Listeners\SendTaskAssignedNotification@handle
```

## Database Tables

- `projects` -- name, slug, description, status, dates, soft deletes
- `project_members` -- user, project, role (`Owner`/`Manager`/`Member`)
- `tasks` -- title, description, priority, status, assignee, creator, dates, hours, subtask hierarchy, soft deletes
- `task_comments` -- task, user, comment text, soft deletes
- `task_attachments` -- task, user, file metadata
- `activities` -- user, project, task, action, description, JSON properties
- `notifications` -- type, notifiable, data, read status

## Not Built Yet

- Comment edit/delete (service methods exist but no routes or UI)
- Attachment delete (service method exists but no route or UI)
- Project/task restore from soft-delete
- Project member removal
- Mark-all-notifications-read
- `TaskCompletedNotification` dispatch (class exists but is never sent)
