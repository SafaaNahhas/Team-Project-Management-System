## Introduction
This project is a Team Project Management System built using Laravel, designed to manage projects, tasks, and user roles. The API provides full CRUD functionality for projects and tasks, with advanced features including role-based access control, custom query scopes, and enhanced pivot table capabilities. The system is structured to adhere to RESTful standards, ensuring accurate HTTP status codes, data validation, and error handling.

## Prerequisites
PHP >= 8.0
Composer
Laravel >= 9.0
MySQL or any other database supported by Laravel
Postman for testing API endpoints
## Setup
1. **Clone the project:**:

git clone https://github.com/SafaaNahhas/Team-Project-Management-System.git
cd TeamProjectManagement
## Install backend dependencies:
composer install
Create the .env file:

cp .env.example .env
## Modify the .env file to set up your database connection:


DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
## Generate the application key:


php artisan key:generate
## Run migrations:

php artisan migrate
## Start the local server:


php artisan serve
You can now access the project at http://localhost:8000.

## Project Structure
- `ProjectController.php`: Handles API requests related to projects, such as creating, updating, deleting, and retrieving projects.
- `TaskController.php`: Handles API requests related to tasks, including CRUD operations and task assignment.
- `UserController.php`: Manages API requests related to user management, including creating and updating user profiles.
- `AuthController.php`: Manages API requests related to user authentication, including registration, login, and token management.
- `ProjectService.php`: Contains business logic for managing projects.
- `TaskService.php`: Contains business logic for managing tasks.
- `UserService.php`: Contains business logic for managing users.
- `AuthService.php`: Contains business logic for user authentication, including validating credentials and generating JWT tokens.
- `ApiResponseService.php`: Formats and returns standardized API responses.
- `ProjectRequest.php`: A Form Request class for validating data in projects.
- `TaskRequest.php`: A Form Request class for validating data in tasks.
- `UserRequest.php`: A Form Request class for validating data in users.

## Advanced Features
1. Filtering

Projects and tasks can be filtered using query parameters.
2. Project Management:

Users can create, view, update, and delete projects.
Each project has attributes like name and description.
Managers can manage tasks within their projects.
3. Task Management:

Users can create, view, update, and delete tasks.
Each task includes title, description, priority, due_date, and status.
Managers can assign tasks to users, and only assigned users can modify the task's details or mark it as completed.
Admins have full control over all tasks, including permanent deletion.
4. User Management:

The API allows for creating, viewing, updating, and deleting users.
Users are assigned roles (Admin, Manager, Developer, Tester), each with different access levels to task operations.
5. Role-Based Access Control (RBAC):

Different roles are implemented using Laravel's permissions system.
Admins have full permissions for all projects and tasks.
Managers can create, assign, and manage tasks within their projects.
Developers can update task statuses.
Testers can add notes to tasks.
6. JWT Authentication:

JWT (JSON Web Tokens) is used for securing endpoints.
Only authenticated users can access and perform operations on tasks and projects.
7. Soft Deletes:

Tasks and users are soft-deleted, allowing recovery if needed.
Admins can permanently delete records.
8. Task Assignment:

Managers can assign tasks to users, who then have exclusive access to modify the tasks.
9. Date Handling:

Task due dates are managed using Carbon, with custom formatting (e.g., d-m-Y H
).
Tasks can be marked as "overdue" if the due date has passed.
10. Task Scopes:

Custom query scopes are provided to filter tasks by priority and status.
11. Advanced Pivot Table Management:

The project_user pivot table includes additional fields like role, contribution_hours, and last_activity.
12. Using Eloquent Relationships:

1. hasManyThrough: Retrieve tasks associated with projects through user relationships.
whereRelation: Filter tasks based on status and priority using custom conditions.
2. latestOfMany and oldestOfMany: Retrieve the latest or oldest tasks based on dates.
3. ofMany: Retrieve the highest priority task with a specific condition.
Seeders
13. PermissionsSeeder: Creates roles and permissions in the database.
1. Admin: Full permissions for tasks and users.
2. Manager: Permissions to create, assign, and manage tasks.
3. Developer: Limited to updating task statuses.
4. Tester: Can add notes to tasks.
## Postman Collection
A Postman collection is provided to test the API endpoints. Import it into your Postman application to run the requests.

Postman Documentation
https://documenter.getpostman.com/view/34501481/2sAXqpA4o5
