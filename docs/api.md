# Project Management System — API v1

REST API for the Project Management System, versioned under `/api/v1`.

## Base URL

All endpoints below are relative to your application's base URL, e.g.

- Local Laragon: `http://project-management-app.test/api/v1`
- `php artisan serve`: `http://localhost:8000/api/v1`

## Authentication

Every endpoint except `POST /api/v1/login` requires a Laravel Sanctum **bearer token**.

1. Call `POST /api/v1/login` with `email` and `password`.
2. The response contains a `token` (and the authenticated `user`).
3. Send the token on every subsequent request:

```
Authorization: Bearer TOKEN
```

Tokens are revoked by `POST /api/v1/logout`. The current token is verified on every request; requests with a missing or invalid token get `401 Unauthenticated.`.

All requests and responses use JSON (`Content-Type: application/json`).

## Roles and authorization

Each project has members with a role. The creator is automatically added as `Owner`.

| Role | View project/tasks | Create task | Update project | Update task | Delete task | Delete project | Add members |
|---|---|---|---|---|---|---|---|
| Owner | yes | yes | yes | yes | yes | yes | yes |
| Manager | yes | yes | yes | yes | yes | no | yes |
| Member | yes | yes | no | no | no | no | no |

Rule of thumb: **any project member may read and create tasks; owners/managers may modify; only the owner may delete a project.**

Members are added at project creation/update via the `members` array. They are only ever *added*, never removed, and the owner can never be re-added.

## Common data shapes

### User

| Field | Type | Example |
|---|---|---|
| `id` | integer | `1` |
| `name` | string | `Jane Doe` |
| `email` | string | `jane@example.com` |

### Project

| Field | Type | Notes |
|---|---|---|
| `id` | integer | |
| `name` | string | |
| `slug` | string | Auto-generated URL slug, unique even against soft-deleted projects |
| `description` | string or `null` | |
| `status` | string | `planning`, `active`, `completed` or `cancelled` |
| `start_date` | `YYYY-MM-DD` or `null` | |
| `end_date` | `YYYY-MM-DD` or `null` | Must be on/after `start_date` |
| `created_at` | ISO-8601 timestamp | |
| `owner` | object (User) | Only present when the endpoint loads it (noted per endpoint) |
| `members` | array of User | Only present when the endpoint loads it |
| `members_count` | integer | Only present when counted |
| `tasks` | array of Task | Only present when the endpoint loads it |

### Task

| Field | Type | Notes |
|---|---|---|
| `id` | integer | |
| `project_id` | integer | |
| `title` | string | |
| `description` | string or `null` | |
| `priority` | string | `Low`, `Medium`, `High`, `Critical` |
| `status` | string | `Todo`, `In Progress`, `Review`, `Completed`, `Cancelled` |
| `start_date` | `YYYY-MM-DD` or `null` | |
| `due_date` | `YYYY-MM-DD` or `null` | Must be on/after `start_date` |
| `completed_at` | ISO-8601 timestamp or `null` | Set automatically when status becomes `Completed`, cleared otherwise — never send it directly |
| `estimated_hours` | number (2 decimals) or `null` | |
| `actual_hours` | number (2 decimals) or `null` | |
| `created_at` | ISO-8601 timestamp | |
| `project` | object (Project) | Only when loaded |
| `assigned_user` | object (User) or `null` | Only when loaded |
| `creator` | object (User) | Only when loaded |
| `parent_task` | object (Task) or `null` | Only when loaded |
| `subtasks` | array of Task | Only when loaded |

Nested objects only appear when the response section explicitly lists them.

### Collections / pagination

List endpoints return a paginated collection (15 items per page by default). Request a page with `?page=N`.

```json
{
    "data": [ ... ],
    "links": {
        "first": "http://.../api/v1/projects?page=1",
        "last": "http://.../api/v1/projects?page=1",
        "prev": null,
        "next": null
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "links": [...],
        "path": "http://.../api/v1/projects",
        "per_page": 15,
        "to": 3,
        "total": 3
    }
}
```

---

## Authentication

### `POST /api/v1/login`

No authentication required.

**Request body**

| Field | Type | Validation |
|---|---|---|
| `email` | string | required, valid email |
| `password` | string | required |

**Example request**

```json
{
    "email": "jane@example.com",
    "password": "secret"
}
```

**Successful response — `200 OK`**

```json
{
    "user": {
        "id": 1,
        "name": "Jane Doe",
        "email": "jane@example.com"
    },
    "token": "1|abcdef..."
}
```

**Errors**

| Status | Body |
|---|---|
| `422` | `{"message": "Validation failed.", "errors": {...}}` for a malformed request |
| `422` | `{"message": "The provided credentials do not match our records.", "errors": {"email": ["The provided credentials do not match our records."]}}` for wrong credentials |

### `POST /api/v1/logout`

Authentication required.

Revokes the token used for this request (other tokens for the same user remain valid).

**Successful response — `204 No Content`** (empty body)

**Errors:** `401`

### `GET /api/v1/user`

Authentication required. Returns the authenticated user.

**Successful response — `200 OK`**

```json
{
    "data": {
        "id": 1,
        "name": "Jane Doe",
        "email": "jane@example.com"
    }
}
```

**Errors:** `401`

---

## Projects

### `GET /api/v1/projects`

Authentication required. Lists the projects the authenticated user belongs to (as Owner, Manager or Member), newest first.

**Query parameters** (optional)

| Parameter | Type | Notes |
|---|---|---|
| `page` | integer | Pagination page (default 15 per page) |

**Successful response — `200 OK`** — paginated Project collection. Each project includes `owner` and `members_count`.

```json
{
    "data": [
        {
            "id": 1,
            "name": "Website Redesign",
            "slug": "website-redesign",
            "description": "Refresh the company site",
            "status": "active",
            "start_date": "2026-08-01",
            "end_date": "2026-12-31",
            "created_at": "2026-08-27T10:00:00Z",
            "owner": {
                "id": 1,
                "name": "Jane Doe",
                "email": "jane@example.com"
            },
            "members_count": 3
        }
    ],
    "links": { ... },
    "meta": { ... }
}
```

**Errors:** `401`

### `POST /api/v1/projects`

Authentication required. Creates a project and adds the caller as `Owner`. Any `members` supplied are added to that owner membership.

**Request body**

| Field | Type | Validation |
|---|---|---|
| `name` | string | required, max 255 |
| `description` | string | optional |
| `status` | string | optional; one of `planning`, `active`, `completed`, `cancelled` |
| `start_date` | date (`YYYY-MM-DD`) | optional |
| `end_date` | date (`YYYY-MM-DD`) | optional; must be on/after `start_date` |
| `members` | array | optional |
| `members[].user_id` | integer | required per item; must exist in `users`; must be distinct within the array |
| `members[].role` | string | required per item; `Manager` or `Member` |

**Example request**

```json
{
    "name": "Website Redesign"
}
```

**Successful response — `201 Created`** — single Project object including `owner` and `members_count`.

```json
{
    "data": {
        "id": 1,
        "name": "Website Redesign",
        "slug": "website-redesign",
        "description": null,
        "status": "planning",
        "start_date": null,
        "end_date": null,
        "created_at": "2026-08-27T10:00:00Z",
        "owner": {
            "id": 1,
            "name": "Jane Doe",
            "email": "jane@example.com"
        },
        "members_count": 1
    }
}
```

**Errors:** `401`, `422`, `500`

### `GET /api/v1/projects/{project}`

Authentication required. **Authorization:** caller must be a member of the project (any role).

**Path parameters**

| Parameter | Type |
|---|---|
| `project` | integer — project ID |

**Successful response — `200 OK`** — single Project object including `owner`, `members`, `members_count`, and `tasks` (each task carries `assigned_user` when assigned).

```json
{
    "data": {
        "id": 1,
        "name": "Website Redesign",
        "slug": "website-redesign",
        "description": "Refresh the company site",
        "status": "active",
        "start_date": "2026-08-01",
        "end_date": "2026-12-31",
        "created_at": "2026-08-27T10:00:00Z",
        "owner": { "id": 1, "name": "Jane Doe", "email": "jane@example.com" },
        "members": [
            { "id": 1, "name": "Jane Doe", "email": "jane@example.com" },
            { "id": 2, "name": "John Smith", "email": "john@example.com" }
        ],
        "members_count": 2,
        "tasks": [
            {
                "id": 10,
                "project_id": 1,
                "title": "Design landing page",
                "description": "Mobile-first layout",
                "priority": "High",
                "status": "In Progress",
                "start_date": null,
                "due_date": "2026-09-15",
                "completed_at": null,
                "estimated_hours": "8.00",
                "actual_hours": null,
                "created_at": "2026-08-27T11:00:00Z",
                "assigned_user": { "id": 2, "name": "John Smith", "email": "john@example.com" }
            }
        ]
    }
}
```

**Errors:** `401`, `403`, `404`

### `PUT /api/v1/projects/{project}`
### `PATCH /api/v1/projects/{project}`

Authentication required. **Authorization:** caller must be `Owner` or `Manager`.

**Path parameters**

| Parameter | Type |
|---|---|
| `project` | integer — project ID |

**Request body** (full update — `name` and `status` are required)

| Field | Type | Validation |
|---|---|---|
| `name` | string | required, max 255 |
| `description` | string | optional |
| `status` | string | required; `planning`, `active`, `completed`, `cancelled` |
| `start_date` | date (`YYYY-MM-DD`) | optional |
| `end_date` | date (`YYYY-MM-DD`) | optional; on/after `start_date` |
| `members` | array | optional; added without removing existing members |

`members` items use the same rules as project creation: `user_id` (required, integer, distinct, must exist) + `role` (required, `Manager` or `Member`).

**Example request**

```json
{
    "name": "Website Redesign 2026",
    "status": "active",
    "description": "Refresh the company site",
    "members": [
        { "user_id": 2, "role": "Member" }
    ]
}
```

**Successful response — `200 OK`** — single Project object including `owner` and `members_count`.

**Errors:** `401`, `403`, `404`, `422`, `500`

### `DELETE /api/v1/projects/{project}`

Authentication required. **Authorization:** caller must be `Owner`.

Soft-deletes the project (and any immediately nested data). The resource becomes unreachable — subsequent requests for it return `404`.

**Path parameters**

| Parameter | Type |
|---|---|
| `project` | integer — project ID |

**Successful response — `204 No Content`** (empty body)

**Errors:** `401`, `403`, `404`

---

## Tasks

### `GET /api/v1/projects/{project}/tasks`

Authentication required. **Authorization:** caller must be a member of the project (any role). Newest first.

**Path parameters**

| Parameter | Type |
|---|---|
| `project` | integer — project ID |

**Query parameters** (all optional)

| Parameter | Type | Notes |
|---|---|---|
| `search` | string | Substring match on task title |
| `status` | string | Exact match: `Todo`, `In Progress`, `Review`, `Completed`, `Cancelled` |
| `priority` | string | Exact match: `Low`, `Medium`, `High`, `Critical` |
| `assignee` | integer | Expert the user's `id`; lists tasks assigned to that user |
| `page` | integer | Pagination page (default 15 per page) |

**Successful response — `200 OK`** — paginated Task collection. Each task includes `assigned_user` (or `null`) and `creator`.

**Errors:** `401`, `403`, `404`

### `POST /api/v1/projects/{project}/tasks`

Authentication required. **Authorization:** any member of the project.

Creates a task. `completed_at` is derived from `status` by the server.

**Path parameters**

| Parameter | Type |
|---|---|
| `project` | integer — project ID |

**Request body**

| Field | Type | Validation |
|---|---|---|
| `title` | string | required, max 255 |
| `description` | string | optional |
| `priority` | string | required; `Low`, `Medium`, `High`, `Critical` |
| `status` | string | required; `Todo`, `In Progress`, `Review`, `Completed`, `Cancelled` |
| `assigned_to` | integer | optional; must be a **user id who is a member of this project** |
| `start_date` | date (`YYYY-MM-DD`) | optional |
| `due_date` | date (`YYYY-MM-DD`) | optional; on/after `start_date` |
| `estimated_hours` | number | optional; min 0 |
| `actual_hours` | number | optional; min 0 |
| `parent_task_id` | integer | optional; must be a **task id in this project** |

`parent_task_id` rules enforced automatically:

- A task cannot be its own parent.
- Nesting is limited to one level — a subtask cannot itself have subtasks.

**Example request**

```json
{
    "title": "Design landing page",
    "priority": "High",
    "status": "Todo",
    "due_date": "2026-09-15",
    "assigned_to": 2
}
```

**Successful response — `201 Created`** — single Task object including `assigned_user` and `creator`.

```json
{
    "data": {
        "id": 10,
        "project_id": 1,
        "title": "Design landing page",
        "description": null,
        "priority": "High",
        "status": "Todo",
        "start_date": null,
        "due_date": "2026-09-15",
        "completed_at": null,
        "estimated_hours": null,
        "actual_hours": null,
        "created_at": "2026-08-27T11:00:00Z",
        "assigned_user": { "id": 2, "name": "John Smith", "email": "john@example.com" },
        "creator": { "id": 1, "name": "Jane Doe", "email": "jane@example.com" }
    }
}
```

**Errors:** `401`, `403`, `404`, `422`, `500`

### `GET /api/v1/projects/{project}/tasks/{task}`

Authentication required. **Authorization:** caller must be a member of the project (any role).

Purely an alternate URL for the same operation — behaves identically to `GET /api/v1/tasks/{task}` (same authorization, same response shape, `404` when the task is missing or does not belong to the project).

### `GET /api/v1/tasks/{task}`

Authentication required. **Authorization:** caller must be a member of the task's project (any role).

**Path parameters**

| Parameter | Type |
|---|---|
| `task` | integer — task ID |

**Successful response — `200 OK`** — single Task object. This is the most complete shape: includes `project`, `assigned_user`, `creator`, `parent_task` (or `null`), and `subtasks` (each subtask carries its own `assigned_user`).

**Errors:** `401`, `403`, `404`

### `PUT /api/v1/tasks/{task}`
### `PATCH /api/v1/tasks/{task}`

Authentication required. **Authorization:** caller must be `Owner` or `Manager` of the task's project.

**Path parameters**

| Parameter | Type |
|---|---|
| `task` | integer — task ID |

**Request body** — same fields and rules as task creation (`title`, `priority`, `status` are required; the rest optional).

Changing `status` to `Completed` sets `completed_at` (server-side); moving away from `Completed` clears it. Changing `assigned_to` triggers an assignment (and, when the assignee differs, a database notification for the new assignee). A task can be assigned only to a member of its project.

**Example request**

```json
{
    "title": "Design landing page",
    "priority": "High",
    "status": "Completed",
    "due_date": "2026-09-15"
}
```

**Successful response — `200 OK`** — single Task object including `assigned_user` and `creator`.

**Errors:** `401`, `403`, `404`, `422`, `500`

### `DELETE /api/v1/tasks/{task}`

Authentication required. **Authorization:** caller must be `Owner` or `Manager` of the task's project.

Soft-deletes the task. Subsequent requests for it return `404`.

**Path parameters**

| Parameter | Type |
|---|---|
| `task` | integer — task ID |

**Successful response — `204 No Content`** (empty body)

**Errors:** `401`, `403`, `404`

---

## Errors

All error responses are JSON with at least a `message`. Validation errors add an `errors` object keyed by field.

| Status | Meaning | Body |
|---|---|---|
| `401` | Not authenticated — token missing/invalid/expired | `{"message": "Unauthenticated."}` |
| `403` | Authenticated but not authorized (wrong project role) | `{"message": "You are not authorized to perform this action."}` |
| `404` | Resource not found (or not in URL — a deleted project/task) | `{"message": "Resource not found."}` |
| `422` | Validation failed | `{"message": "Validation failed.", "errors": {"field": ["message"]}}` |
| `500` | Server error — no internal details are exposed | `{"message": "An unexpected error occurred."}` |

**Example `422`**

```json
{
    "message": "Validation failed.",
    "errors": {
        "title": ["The title field is required."]
    }
}
```

Notes

- `429` (rate limiting) is **not currently enforced** on the API — no throttling is configured for these routes, so you should not expect or rely on a `429`. The web login form is rate limited separately, but the API is not.
- API responses never leak stack traces or internal class names, regardless of `APP_DEBUG`.

---

## Getting started (client flow)

1. `POST /api/v1/login` with `email` + `password` → store `token`.
2. Send `Authorization: Bearer {token}` on all other calls.
3. `GET /api/v1/user` to fetch the current user.
4. `GET /api/v1/projects` to list your projects.
5. `GET /api/v1/projects/{project}/tasks` to list a project's tasks.
6. `POST /api/v1/projects` or `POST /api/v1/projects/{project}/tasks` to create records.
7. `POST /api/v1/logout` to revoke the token when the client signs out.