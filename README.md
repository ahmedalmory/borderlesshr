# BorderlessHR - Job Board API

A RESTful API for a Job Board platform where Companies can post jobs and Candidates can apply.

## Features

- **Authentication** - Separate authentication for Companies and Candidates using Laravel Passport
- **Job Management** - Companies can create, update, and delete job posts
- **Public Job Listing** - Paginated list of jobs with filtering options
- **Job Applications** - Candidates can apply to jobs with cover letter and resume
- **Queued Processing** - Resume uploads are processed in the background
- **Caching** - Public job listings are cached for improved performance
- **Dashboard Data** - Statistics for Companies and Candidates

## Requirements

- PHP 8.1+
- MySQL 5.7+
- Composer
- Laravel 10+

## Installation

1. Clone the repository:
```bash
git clone https://github.com/ahmedalmory/borderlesshr.git
cd borderlesshr
```

2. Install dependencies:
```bash
composer install
```

3. Copy the environment file:
```bash
cp .env.example .env
```

4. Edit `.env` with your database credentials:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=borderlesshr
DB_USERNAME=root
DB_PASSWORD=your_password
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Run migrations:
```bash
php artisan migrate
```

7. Install Laravel Passport:
```bash
php artisan passport:install
```

8. Create symbolic link for storage:
```bash
php artisan storage:link
```

9. Start the development server:
```bash
php artisan serve
```

## API Documentation

### Authentication

#### Company Registration
```
POST /api/company/register
```
Request body:
```json
{
    "name": "Company Name",
    "email": "company@example.com",
    "password": "password",
    "password_confirmation": "password",
    "website": "https://example.com",
    "description": "Company description"
}
```

#### Company Login
```
POST /api/company/login
```
Request body:
```json
{
    "email": "company@example.com",
    "password": "password"
}
```

#### Candidate Registration
```
POST /api/candidate/register
```
Request body:
```json
{
    "name": "Candidate Name",
    "email": "candidate@example.com",
    "password": "password",
    "password_confirmation": "password",
    "phone": "1234567890",
    "bio": "Candidate bio"
}
```

#### Candidate Login
```
POST /api/candidate/login
```
Request body:
```json
{
    "email": "candidate@example.com",
    "password": "password"
}
```

### Jobs

#### List Public Jobs
```
GET /api/jobs
```
Query parameters:
- `location` - Filter by location
- `is_remote` - Filter by remote status (true/false)
- `keyword` - Search in title and description

#### Get Job Details
```
GET /api/jobs/{id}
```

#### Company: List Own Jobs
```
GET /api/company/jobs
```
*Requires company authentication*

#### Company: Create Job
```
POST /api/company/jobs
```
*Requires company authentication*

Request body:
```json
{
    "title": "Job Title",
    "description": "Job description",
    "location": "Job location",
    "salary_range": "Salary range",
    "is_remote": true,
    "published_at": "2023-04-18T00:00:00Z"
}
```

#### Company: Update Job
```
PUT /api/company/jobs/{id}
```
*Requires company authentication*

Request body: Same as create job

#### Company: Delete Job
```
DELETE /api/company/jobs/{id}
```
*Requires company authentication*

### Job Applications

#### Candidate: Apply for Job
```
POST /api/candidate/jobs/{jobId}/apply
```
*Requires candidate authentication*

Request body (form-data):
```
cover_letter: "Cover letter text"
resume: [file]
```

#### Candidate: List Own Applications
```
GET /api/candidate/applications
```
*Requires candidate authentication*

#### Company: List Applications for Job
```
GET /api/company/jobs/{jobId}/applications
```
*Requires company authentication*

### Dashboard Data

#### Company: Dashboard
```
GET /api/company/dashboard
```
*Requires company authentication*

#### Candidate: Dashboard
```
GET /api/candidate/dashboard
```
*Requires candidate authentication*

## Design Choices

1. **Separate Authentication** - Used separate tables and authentication guards for Companies and Candidates to maintain clear separation of concerns.

2. **Soft Deletes** - Implemented soft deletes for jobs to maintain history and prevent data loss.

3. **Job Queues** - Used Laravel's queue system to process file uploads in the background, improving API response times.

4. **Caching** - Implemented 5-minute caching for public job listings to reduce database load and improve performance.

5. **API Tokens** - Used Laravel Passport for secure API token-based authentication.

## Future Improvements

1. **Email Verification** - Add email verification for new registrations.

2. **Job Categories** - Implement job categories for better organization and filtering.

3. **Advanced Search** - Implement more advanced search features like salary range filtering.

4. **Notifications** - Add notifications for job applications and status updates.

5. **Admin Panel** - Create an admin panel for managing the platform.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
