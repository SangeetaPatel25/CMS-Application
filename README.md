Features
User Authentication (Laravel Sanctum)

Login & Logout APIs

Seeder for Admin and Author roles

Role-Based Access Control

Admin: Full access to categories and all articles

Author: Can manage only their own articles

Articles

Create, Read, Update, Delete (CRUD)

Fields: Title, Slug (auto-generated), Content, Summary (auto-generated), Categories, Status, Published Date, Author

Asynchronous slug and summary generation using AI

Categories

CRUD operations (Admin only)

Filtering & Search

List articles with filters: Category, Status, Date Range

Tech Stack
Backend: Laravel 10 (PHP 8.1+)

Database: MySQL

Authentication: Laravel Sanctum

AI Integration: OpenAI (or any LLM)

Queue System: Laravel Queue for async slug/summary generation

Getting Started
1. Clone the Repository

git clone https://github.com/SangeetaPatel25/CMS-Application.git
cd CMS-Application
2. Install Dependencies

composer install

3. .env
DB_DATABASE=your_db
DB_USERNAME=your_user
DB_PASSWORD=your_pass

QUEUE_CONNECTION=database
OPENAI_API_KEY=your_openai_key
4. Generate App Key

php artisan key:generate
5. Run Migrations and Seeders

php artisan migrate --seed
This will also create default users (Admin & Author).

6. Start Development Server
bash
Copy
Edit
php artisan serve
7. Run Queue Worker (for async AI jobs)

php artisan queue:work
Default Users
Role	Email	Password
Admin	admin@example.com	password
Author	author@example.com	password

API Endpoints Overview
Authentication
POST /api/login

POST /api/logout

Articles
GET /api/articles

GET /api/articles/{id}

POST /api/articles

PUT /api/articles/{id}

DELETE /api/articles/{id}

Categories (Admin Only)
GET /api/categories

POST /api/categories

PUT /api/categories/{id}

DELETE /api/categories/{id}

Slug & Summary AI Integration
Articles automatically trigger a queued job (SlugSummaryJob) after creation/update.

This job uses your configured LLM (e.g., OpenAI) to:

Generate a unique slug from title/content

Generate a 2–3 sentence summary from the article content

License
This project is open-source and licensed under the MIT License.

Contact
Developed by Sangeeta Patel
GitHub: https://github.com/SangeetaPatel5
Feel free to open issues or contribute!
