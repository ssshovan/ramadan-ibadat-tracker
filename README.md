# 🌙 Bismillahi Ramadan Ibadat Tracker

A complete Laravel web application for tracking daily ibadat (worship) during Ramadan, built for CSE470 Software Engineering course.

![Laravel](https://img.shields.io/badge/Laravel-10.x-red?style=flat-square&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=flat-square&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap)

---

## ✨ Features

### 📿 Daily Ibadat Tracking
- ✅ 5 Daily Prayers (Fajr, Dhuhr, Asr, Maghrib, Isha)
- ✅ Fasting (Roza) tracking
- ✅ Quran recitation (pages read)
- ✅ Charity (amount + acts)
- ✅ Daily notes & reflections
- ✅ Progress bar visualization

### 🔥 Streak System
- Separate streaks for each category
- Automatic streak calculation
- Milestone badges (7, 14, 21, 30 days)
- Streak history and insights

### 👨‍👩‍👧‍👦 Family System
- Create family with unique code
- Join family using code
- Parent and Child roles
- View family members' progress
- Parent dashboard for monitoring
- Family leaderboard

### 📊 Reports & Analytics
- Daily summary
- Weekly reports with breakdown
- Progress charts (bar, pie, line)
- Category-wise statistics
- Milestone achievements

### 🎨 UI/UX
- Clean Bootstrap 5 design
- Islamic-themed color scheme
- Responsive for all devices
- Motivational quotes (Hadith/Ayat)
- Daily reminders

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 10.x (PHP 8.1+) |
| Frontend | Blade + Bootstrap 5 |
| Database | MySQL (XAMPP) |
| Auth | Laravel Breeze |
| Charts | Chart.js |
| Architecture | Strict MVC |

---

## 🚀 Quick Start

### Prerequisites
- XAMPP (PHP 8.1+, MySQL, Apache)
- Composer
- Node.js & NPM

### Installation

```bash
# 1. Clone repository
git clone https://github.com/yourusername/ramadan-tracker.git
cd ramadan-tracker

# 2. Install PHP dependencies
composer install

# 3. Install Laravel Breeze
composer require laravel/breeze --dev
php artisan breeze:install blade

# 4. Install NPM dependencies
npm install
npm run build

# 5. Configure environment
cp .env.example .env
php artisan key:generate

# 6. Update .env with your database credentials
DB_DATABASE=ramadan_tracker
DB_USERNAME=root
DB_PASSWORD=

# 7. Create database in phpMyAdmin
# CREATE DATABASE ramadan_tracker;

# 8. Run migrations
php artisan migrate

# 9. (Optional) Seed test data
php artisan db:seed

# 10. Start development server
php artisan serve
```

Visit: http://localhost:8000

---

## 📁 Project Structure

```
ramadan-tracker/
├── app/
│   ├── Http/Controllers/     # MVC Controllers
│   ├── Models/               # Eloquent Models
│   └── Services/             # Business Logic
├── database/
│   └── migrations/           # All 10 migrations
├── resources/
│   └── views/                # Blade templates
├── routes/
│   └── web.php               # All routes
└── docs/
    ├── 01-SETUP-INSTRUCTIONS.md
    ├── 02-SYSTEM-DESIGN.md
    └── 03-GITHUB-COMMIT-PLAN.md
```

---

## 🗄️ Database Schema

### Tables

| Table | Description |
|-------|-------------|
| `users` | User accounts with profile fields |
| `ibadat_logs` | Daily ibadat records (1 per user per day) |
| `prayers` | Prayer completion status (5 per day) |
| `charity_records` | Charity donations and acts |
| `quran_logs` | Quran reading progress |
| `streaks` | User streaks by category |
| `families` | Family groups |
| `family_members` | Family memberships (pivot) |
| `notes` | Daily notes and reflections |
| `milestones` | User achievements |

### Relationships

```
users ──1:N──► ibadat_logs ──1:N──► prayers
           ──1:1──► charity_records
           ──1:1──► quran_logs
           ──1:N──► notes
           
users ──1:N──► streaks
users ──1:N──► milestones
users ──N:M──► families (via family_members)
```

---

## 📸 Screenshots

### Dashboard
- Daily progress overview
- Streak summary
- Weekly chart
- Family progress

### Daily Tracking
- Prayer checklist
- Fasting toggle
- Quran pages input
- Charity form
- Notes section

### Streaks
- Streak cards
- Calendar view
- Milestone progress

### Family
- Family dashboard
- Member list
- Progress comparison
- Parent dashboard

---

## 🎯 MVC Architecture

### Controllers (Logic)
- `DashboardController` - Main dashboard
- `IbadatController` - Daily tracking CRUD
- `StreakController` - Streak management
- `FamilyController` - Family operations
- `ReportController` - Analytics & reports
- `ProfileController` - User profile

### Models (Data)
- `User` - User with relationships
- `IbadatLog` - Daily log with progress calc
- `Prayer` - Prayer with toggle methods
- `Streak` - Streak with increment/reset
- `Family` - Family with code generation
- And more...

### Views (UI)
- Blade templates with Bootstrap 5
- Responsive design
- Islamic-themed styling

### Services (Business Logic)
- `StreakService` - Streak calculations
- `ProgressService` - Progress analytics
- `FamilyService` - Family operations

---

## 🔐 Authentication

Laravel Breeze provides:
- Registration
- Login
- Password reset
- Email verification
- Profile management

---

## 📊 Charts

Chart.js integration for:
- Weekly progress line chart
- Category breakdown pie chart
- Family progress doughnut chart
- Prayer completion bar chart

---

## 🏆 Milestones

Automatic badges for:
- 7-day streaks (Bronze)
- 14-day streaks (Silver)
- 21-day streaks (Gold)
- 30-day streaks (Trophy)
- Perfect day (100% completion)
- Complete Ramadan

---

## 🧪 Testing

```bash
# Run PHPUnit tests
php artisan test

# Run specific test
php artisan test --filter=UserTest
```

---

## 📝 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/stats | Quick stats |
| GET | /api/daily-summary | Daily summary |
| GET | /api/family/{id}/progress | Family progress |

---

## 🚀 Deployment

### Shared Hosting
```bash
# 1. Upload files (excluding vendor, node_modules)
# 2. Run composer install on server
# 3. Configure .env
# 4. Run migrations
# 5. Set public directory as web root
```

### VPS/Dedicated
```bash
# Use Laravel Forge or manual setup
# Configure Nginx/Apache
# Set up SSL
# Configure queue workers
# Set up cron jobs
```

---

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing`)
3. Commit changes (`git commit -m 'feat: Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing`)
5. Open Pull Request

---

## 📜 License

This project is open-source for educational purposes.

---

## 🙏 Acknowledgments

- Laravel Framework
- Laravel Breeze
- Bootstrap 5
- Chart.js
- All hadith and Quranic quotes sources

---

## 📧 Contact

For questions or support:
- Email: your.email@example.com
- GitHub Issues: [Create Issue](https://github.com/yourusername/ramadan-tracker/issues)

---

## 🌟 Dua

*"O Allah, accept our fasting, our prayers, and our good deeds in this blessed month of Ramadan."*

---

**Built with ❤️ for CSE470 Software Engineering**

**Bismillahi - In the name of Allah** 🌙
