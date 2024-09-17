<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Team Project Management System API

This is a Team Project Management API built with Laravel 10. It allows project and task management with role-based access control. The system enables the creation, editing, and deletion of projects and users, along with assigning roles such as Super Admin, Manager, Developer, and Tester.

### Features

- Project Management: Create, update, and delete projects.
- Task Management: Add, update, and delete tasks under projects.
- Role-based Access Control: Role-specific permissions for Super Admin, Manager, Developer, and Tester.
- Task Assignment: Assign users to tasks based on roles.
- Task Filtering: Filter tasks by status and priority.
- Project User Pivot Table: Track additional information such as user roles, contribution hours, and last activity.
- Eloquent Relationships:
   - `hasManyThrough`: Retrieve tasks related to the project a user is working on.
   - `whereRelation`: Filter tasks by specific status or priority.
   - `oldestOfMany` & `latestOfMany`: Get the oldest or latest task in a project.
   - `ofMany()` with condition: Fetch the highest priority task based on a specific title condition.


## Requirements

- PHP 8.0 or higher
- Composer
- Laravel 10
- MySQL or any compatible database


## Installation

1. **Clone the Repository:**
```
git clone https://github.com/amalSheikhdaher/Task_Management_System.git
```

2. **Install Dependencies:**
```
composer install
```

3. **Set up the environment:**

   Copy the `.env.example` file and configure the database settings and other environment variables.
```
cp .env.example .env
php artisan key:generate
```

Set the following variables:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

4. **Run Migrations Run migrations to create the necessary tables.**

```
 php artisan migrate
```

5. **Seed the database:**


```
php artisan db:seed
```

6. **Serve the application:**

```
php artisan serve
```

Your application will be accessible at `http://localhost:8000`.

## Models

### Project Model

- Defines the relationship with `tasks` and `users` via `hasMany` and `belongsToMany`.
- Uses `hasManyThrough` to fetch tasks a user is working on through projects.

### Task Model

- Tracks task details such as title, description, status, priority, due date, and note.
- Belongs to a project and has many users.
- Supports `latestOfMany` and `oldestOfMany` to retrieve the most recent or oldest task in a project.

### User Model

- Stores user details like name, email, and password.
- Uses `belongsToMany` to connect users to projects via the `project_user` pivot table.
- Allows role-based filtering using `whereRelation`.

### Pivot Table: `project_user`

- Contains additional fields `role`, `contribution_hours`, and `last_activity`.

## Migrations

- projects: Defines the project structure with fields like `name` and `description`.
- tasks: Stores task information (title, description, status, priority, etc.).
- users: Basic user information (name, email, password).
- project_user: A pivot table that links users and projects with additional fields like `role`, `contribution_hours`, and `last_activity`.

## Services

The service layer handles the business logic, separating it from the controller.

## Middleware

Middleware is used to ensure role-based access control.
- `role`: Custom middleware that checks the user's role before accessing certain routes.

### Middleware Example
- Super Admin can create, edit, and delete projects, and assign users.
- Manager can manage tasks (add, update, and delete).
- Developer can only update task status.
- Tester can add notes to tasks.

## API Endpoints

Here are some of the primary endpoints for managing projects:

- **List Projects:**
  - **GET** `/api/projects`
  - Retrieves a list of all projects.

- **Get Project Details:**
  - **GET** `/api/projects/{project}`
  - Retrieves details of a specific Project.

- **Create a Project:**
  - **POST** `/api/projects`
  - Creates a new project. Requires a JSON body with project details.

- **Update a Project:**
  - **PUT** `/api/projects/{project}`
  - Updates details of a specific project. Requires a JSON body with updated project details.

- **Delete a Project:**
  - **DELETE** `/api/projects/{project}`
  - Deletes a specific project.


## Conclusion

This API offers a comprehensive project management solution with role-based access control, task management, and advanced Eloquent relationships using Laravel 10. The setup includes services, controllers, and middleware to streamline project and task handling, with form requests and resources ensuring that input is validated and output is structured
