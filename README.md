# Project Management App

Laravel 12 project-management application using Laravel Breeze (Blade).

## Current Features

### Authentication

- Registration, login, logout, password reset, and email verification through Breeze.
- Project and task pages require an authenticated, verified user.

### Projects and Members

- Create, view, edit, and soft-delete projects.
- The creator is automatically added as the project `Owner`.
- Add `Manager` and `Member` users while creating or editing a project.
- View project information, dates, members, roles, and tasks.
- Slugs are generated automatically from project names.

### Tasks and Subtasks

- Create, view, edit, soft-delete, and complete tasks.
- Assign tasks only to a member of the selected project.
- Search by title; filter by status, priority, and assignee; sort by due date.
- Statuses: `Todo`, `In Progress`, `Review`, `Completed`, `Cancelled`.
- Priorities: `Low`, `Medium`, `High`, `Critical`.
- Completing a task automatically fills `completed_at`.
- Create subtasks from any task using `parent_task_id`.

### Dashboard

The dashboard displays live data for the signed-in user's projects:

- Project count
- Open tasks
- Tasks completed today
- Overdue tasks
- Up to five active tasks assigned to that user

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

Notifications are stored in the database. A notification-centre screen has not yet been built, so verify them through the database or automated test.

### Supporting Models and Services

The following foundations are ready:

- `TaskComment` and `CommentService`
- `TaskAttachment` and `AttachmentService`
- `Activity` and `ActivityService`
- `TaskCompletedNotification`

Comments, attachments, activity history, and a notification-centre UI do not have routes/screens yet.

## Structure

```text
app/
|-- Events/TaskAssigned.php
|-- Listeners/SendTaskAssignedNotification.php
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
`-- Services/
    |-- ActivityService.php
    |-- AttachmentService.php
    |-- CommentService.php
    |-- ProjectService.php
    `-- TaskService.php
```

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
4. Switch sorting between earliest and latest due dates.
5. Use the **Complete** action for an unfinished task.

### Dashboard

1. Create tasks with different statuses and due dates.
2. Assign some tasks to the signed-in user.
3. Open `/dashboard` and confirm its metrics and **My Tasks** list update.

### Assignment Notifications

1. Log in as the project owner/manager.
2. Create a task assigned to another project member, or edit an existing task and change the assignee.
3. Verify the notification with Tinker:

```bash
php artisan tinker
```

```php
App\Models\User::find(<assignee-id>)->notifications()->latest()->first();
```

The notification type should be `App\Notifications\TaskAssignedNotification`.

## Automated Checks

Run all tests:

```bash
php artisan test
```

Run the assignment-notification test only:

```bash
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

- `projects`
- `project_members`
- `tasks`
- `task_comments`
- `task_attachments`
- `activities`
- `notifications`
