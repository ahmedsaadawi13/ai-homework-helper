# AI Homework Helper

A complete multi-tenant SaaS platform for AI-powered homework assistance. Built for students, parents, teachers, and educational institutions.

## Features

- **Multi-Tenant Architecture**: Single database supporting multiple education accounts (schools, tutoring centers, families)
- **AI-Powered Help**: Instant step-by-step solutions and explanations for homework questions
- **Quiz Generation**: AI-generated quizzes for practice and assessment
- **Student Management**: Track students, classes, subjects, and performance
- **Subscription Plans**: Flexible plans with usage limits and quota enforcement
- **REST API**: External integration support for homework request submission
- **Role-Based Access**: Platform admin, tenant admin, teacher, and student roles
- **Analytics & Reports**: Performance tracking and usage statistics

## Technology Stack

- **Backend**: PHP 7.0+ (pure MVC, no frameworks)
- **Database**: MySQL 5.7+ / MariaDB
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Architecture**: Custom lightweight MVC
- **Security**: PDO prepared statements, CSRF protection, password hashing

## Requirements

- PHP 7.0 or higher (7.4+ recommended)
- MySQL 5.7+ or MariaDB 10.2+
- Apache 2.4+ or Nginx
- mod_rewrite enabled (Apache)
- PHP Extensions:
  - PDO
  - pdo_mysql
  - mbstring
  - fileinfo

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/ai-homework-helper.git
cd ai-homework-helper
```

### 2. Configure Environment

Copy the example environment file:

```bash
cp .env.example .env
```

Edit `.env` and configure your database credentials:

```env
DB_HOST=localhost
DB_NAME=ai_homework_helper
DB_USER=root
DB_PASS=your_password
```

### 3. Create Database

Create a MySQL database:

```bash
mysql -u root -p
```

```sql
CREATE DATABASE ai_homework_helper CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 4. Import Database Schema

Import the database schema with seed data:

```bash
mysql -u root -p ai_homework_helper < database.sql
```

This will create all tables and insert demo data including:
- 4 subscription plans
- 2 demo tenants (Greenwood High School, Johnson Family)
- 8 demo users with different roles
- Sample students, classes, subjects
- Sample homework requests and AI responses
- Sample quizzes and quiz results

### 5. Set Permissions

Ensure the storage directory is writable:

```bash
chmod -R 755 storage/
chmod -R 755 storage/uploads/
```

### 6. Configure Web Server

#### Apache

The project includes `.htaccess` files for Apache. Ensure `mod_rewrite` is enabled:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Create a virtual host or point your document root to the `public/` directory.

Example Apache virtual host:

```apache
<VirtualHost *:80>
    ServerName ai-homework.local
    DocumentRoot /var/www/ai-homework-helper/public

    <Directory /var/www/ai-homework-helper/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/ai-homework-error.log
    CustomLog ${APACHE_LOG_DIR}/ai-homework-access.log combined
</VirtualHost>
```

#### Nginx

Example Nginx configuration:

```nginx
server {
    listen 80;
    server_name ai-homework.local;
    root /var/www/ai-homework-helper/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### 7. Access the Application

Open your browser and navigate to:

```
http://localhost
```

Or your configured domain/virtual host.

## Demo Credentials

The system comes with pre-seeded demo accounts:

| Role | Email | Password |
|------|-------|----------|
| Platform Admin | admin@aihomework.com | password123 |
| School Admin | principal@greenwood-high.edu | password123 |
| Teacher | emily.davis@greenwood-high.edu | password123 |
| Student | alex.thompson@student.greenwood.edu | password123 |

## Usage

### For Platform Admins

1. Login with platform admin credentials
2. Access tenant management at `/admin/tenants`
3. Create and manage education accounts
4. Monitor system-wide activity

### For Tenant Admins (Schools/Tutoring Centers)

1. Login with tenant admin credentials
2. Manage subscription at `/admin/subscription`
3. Add students at `/students/create`
4. Create classes at `/classes/create`
5. Add subjects at `/subjects/create`
6. View usage statistics on the dashboard

### For Teachers

1. Login with teacher credentials
2. View assigned classes and students
3. Review homework requests at `/homework`
4. Generate quizzes at `/ai/quiz/generate`
5. Monitor student performance

### For Students

1. Login with student credentials
2. Submit homework help requests at `/homework/create`
3. Get AI-powered assistance
4. Take practice quizzes
5. View performance statistics

## REST API Documentation

The platform provides a REST API for external integrations.

### Authentication

All API requests require an API key passed in the `X-API-KEY` header.

```bash
X-API-KEY: your_tenant_api_key_here
```

Find your API key in the tenant settings or database (`tenants.api_key`).

### Endpoints

#### Create Homework Help Request

**POST** `/api/v1/homework/create`

Create a new homework help request.

**Headers:**
```
Content-Type: application/json
X-API-KEY: your_api_key_here
```

**Request Body:**
```json
{
  "student_id": 1,
  "subject_id": 2,
  "title": "Help with quadratic equations",
  "description": "I need help solving x^2 + 5x + 6 = 0"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "homework_id": 42,
  "message": "Homework help request created successfully"
}
```

**Error Response (400 Bad Request):**
```json
{
  "error": "Field 'student_id' is required"
}
```

**Error Response (401 Unauthorized):**
```json
{
  "error": "Invalid API key"
}
```

#### Get Homework Details

**GET** `/api/v1/homework/{id}`

Retrieve homework request details and AI responses.

**Headers:**
```
X-API-KEY: your_api_key_here
```

**Response (200 OK):**
```json
{
  "success": true,
  "homework": {
    "id": 42,
    "title": "Help with quadratic equations",
    "description": "I need help solving x^2 + 5x + 6 = 0",
    "student_name": "Alex Thompson",
    "subject_name": "Mathematics",
    "status": "answered",
    "created_at": "2025-01-22 10:00:00"
  },
  "responses": [
    {
      "response_type": "ai",
      "content": "To solve the quadratic equation...",
      "step_by_step": "Step 1: Identify a=1, b=5, c=6...",
      "created_at": "2025-01-22 10:05:00"
    }
  ]
}
```

### API Example with cURL

```bash
# Create homework request
curl -X POST https://your-domain.com/api/v1/homework/create \
  -H "Content-Type: application/json" \
  -H "X-API-KEY: your_api_key_here" \
  -d '{
    "student_id": 1,
    "subject_id": 2,
    "title": "Help with fractions",
    "description": "How do I divide fractions?"
  }'

# Get homework details
curl -X GET https://your-domain.com/api/v1/homework/1 \
  -H "X-API-KEY: your_api_key_here"
```

## Subscription Plans

The system includes 4 default subscription plans:

| Plan | Price/Month | Students | Classes | AI Requests/Month | Storage |
|------|-------------|----------|---------|-------------------|---------|
| Free | $0 | 5 | 2 | 100 | 100 MB |
| School Basic | $49 | 50 | 10 | 1,000 | 1 GB |
| School Pro | $99 | 200 | 50 | 5,000 | 5 GB |
| Enterprise | $299 | 10,000 | 1,000 | 50,000 | 50 GB |

Limits are enforced automatically. When a tenant reaches their limit, they receive warnings and are prevented from exceeding quotas.

## Testing

### Manual Testing Checklist

#### Authentication
- [ ] User can register a new tenant account
- [ ] User can login with valid credentials
- [ ] User cannot login with invalid credentials
- [ ] User can logout successfully
- [ ] CSRF protection works on all forms

#### Student Management
- [ ] Create new student
- [ ] View student list with pagination
- [ ] View student details
- [ ] Update student information
- [ ] Delete student
- [ ] Search students by name

#### Homework & AI
- [ ] Create homework help request
- [ ] Upload homework image
- [ ] Request AI assistance
- [ ] AI generates step-by-step solution
- [ ] View homework responses
- [ ] Update homework status

#### Subscription & Limits
- [ ] Student creation blocked when limit reached
- [ ] Class creation blocked when limit reached
- [ ] AI request blocked when monthly limit reached
- [ ] Usage statistics display correctly
- [ ] Change subscription plan
- [ ] Simulate payment

#### API
- [ ] Create homework via API with valid API key
- [ ] API rejects requests with invalid API key
- [ ] API returns correct error messages
- [ ] Get homework details via API

#### Tenant Isolation
- [ ] Users can only see data from their own tenant
- [ ] Cross-tenant data access is prevented
- [ ] API key only accesses own tenant data

### Automated Testing

Run the included test scripts:

```bash
php tests/run_tests.php
```

## Project Structure

```
ai-homework-helper/
├── app/
│   ├── controllers/      # Controllers for handling requests
│   ├── models/          # Database models
│   ├── views/           # View templates
│   ├── core/            # Core MVC framework classes
│   └── helpers/         # Helper classes (Auth, FileUpload, etc.)
├── config/              # Configuration files
├── public/              # Public web root
│   ├── index.php        # Application entry point
│   ├── css/             # Stylesheets
│   ├── js/              # JavaScript files
│   └── .htaccess        # Apache rewrite rules
├── storage/             # File uploads and storage
│   └── uploads/         # Uploaded files
├── tests/               # Test scripts
├── database.sql         # Database schema and seed data
├── .env.example         # Environment configuration example
├── .htaccess            # Root Apache config
└── README.md            # This file
```

## Security Features

- **SQL Injection Protection**: All queries use PDO prepared statements
- **CSRF Protection**: Token validation on all state-changing operations
- **Password Security**: Passwords hashed using `password_hash()`
- **Input Validation**: Server-side validation and sanitization
- **File Upload Security**: Type and size validation, unique filenames
- **Tenant Isolation**: Automatic tenant_id filtering on all queries
- **Role-Based Access**: Fine-grained permission checks
- **Session Security**: Secure session handling

## Performance Optimization

- Database indexes on frequently queried columns
- Pagination on all list pages
- Prepared statement caching
- Minimal dependencies for fast load times

## Troubleshooting

### Database Connection Errors

Check your `.env` file has correct credentials:
```env
DB_HOST=localhost
DB_NAME=ai_homework_helper
DB_USER=root
DB_PASS=your_password
```

### 404 Errors on All Pages

Ensure mod_rewrite is enabled (Apache):
```bash
sudo a2enmod rewrite
```

### File Upload Errors

Check storage directory permissions:
```bash
chmod -R 755 storage/
```

### White Screen / No Output

Enable error display in `.env`:
```env
APP_ENV=development
APP_DEBUG=true
```

## Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is licensed under the MIT License.

## Support

For issues and questions:
- Open an issue on GitHub
- Email: support@aihomework.com

## Credits

Developed as a demonstration of clean, beginner-friendly PHP MVC architecture for multi-tenant SaaS applications.

---

**Note**: This is a demonstration project with a mock AI engine. For production use, integrate with a real AI service (OpenAI GPT, Google Gemini, etc.).
