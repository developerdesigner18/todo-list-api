# Laravel Todo API

A RESTful API built with Laravel 12 for managing todos with file attachments. This project includes user authentication using Laravel Sanctum and comprehensive API documentation with Swagger.

## Features

- 🔐 **User Authentication** - Login system with Laravel Sanctum
- 📝 **Todo Management** - Full CRUD operations for todos
- 📎 **File Attachments** - Upload and manage files with todos
- 📖 **API Documentation** - Interactive Swagger documentation
- 🔍 **Pagination** - Paginated todo listings
- 🛡️ **Security** - Token-based authentication
- 📱 **RESTful Design** - Clean and consistent API endpoints

## Tech Stack

- **Framework**: Laravel 12
- **Database**: MySql (configurable)
- **Authentication**: Laravel Sanctum
- **Documentation**: L5-Swagger (OpenAPI 3.0)
- **File Storage**: Laravel Storage
- **Testing**: PHPUnit

## API Endpoints

### Authentication
- \`POST /api/login\` - User login
- \`POST /api/register\` - User registration

### Todos
- \`GET /api/todos\` - List todos (paginated)
- \`POST /api/todos\` - Create new todo
- \`GET /api/todos/{id}\` - Get specific todo
- \`PUT /api/todos/{id}\` - Update todo
- \`DELETE /api/todos/{id}\` - Delete todo

## Prerequisites

Before you begin, ensure you have the following installed:

- **PHP** >= 8.2
- **Composer** >= 2.0
- **Git**

## Installation & Setup

### 1. Clone the Repository

```bash
git clone <repository-url> todo-api
cd todo-api
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install
```

### 3. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup

The project uses MySql by default. Please change this accordingly.
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

```bash
# Run migrations
php artisan migrate

# Seed the database (optional)
php artisan db:seed
```

### 5. Storage Setup

```bash
# Create storage link
php artisan storage:link

# Set proper permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 6. Generate API Documentation

```bash
# Generate API documentation
php artisan l5-swagger:generate
```

### 8. Start the Development Server

```bash
php artisan serve
```

The application will be available at: \`http://localhost:8000\`

## Usage

### 1. Access API Documentation

Visit: \`http://localhost:8000/api/documentation\`

The interactive Swagger documentation allows you to:
- View all available endpoints
- Test API calls directly
- See request/response examples
- Understand authentication requirements

### 2. Authentication

To use protected endpoints, you need to authenticate:

1. **Login via API**:
   ```bash
   curl -X POST http://localhost:8000/api/login \\
   -H "Content-Type: application/json" \\
   -d '{"email": "user@example.com", "password": "password"}'
   ```

2. **Use the returned token** in subsequent requests:
   ```bash
   curl -X GET http://localhost:8000/api/todos \\
   -H "Authorization: Bearer YOUR_TOKEN_HERE"
   ```

### 3. Creating Todos

```bash
# Create todo with file
curl -X POST http://localhost:8000/api/todos \\
-H "Authorization: Bearer YOUR_TOKEN" \\
-F "title=My Todo" \\
-F "description=Todo description" \\
-F "file=@/path/to/file.pdf"
```

### 4. File Uploads

The API supports file uploads with todos. Supported file types and size limits are configured in the validation rules.

## Project Structure

```
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AuthController.php
│   │   │       └── TodoController.php
│   │   └── Traits/
│   │       └── ResponseTrait.php
│   └── Models/
│       ├── Todo.php
│       └── User.php
├── database/
│   └── migrations/
├── routes/
│   └── api.php
├── storage/
│   └── app/
│       └── public/
│           └── uploads/
└── config/
└── l5-swagger.php
```
