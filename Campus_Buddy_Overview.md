# 🎓 Campus Buddy - Project Overview

**Campus Buddy** is a specialized social and academic management platform built for university students and alumni. It combines administrative efficiency with a community-focused networking interface.

---

## 🛠 1. Technology Stack (The TALL Stack +)
Your project follows the modern Laravel ecosystem, often referred to as the **TALL** stack, but customized for your specific needs:

*   **Backend:** [PHP 8.x](https://www.php.net/) with [Laravel 11](https://laravel.com/) (The core engine).
*   **Frontend UI:** [Blade Templating](https://laravel.com/docs/master/blade) (Dynamic HTML) + [Vanilla CSS](https://developer.mozilla.org/en-US/docs/Web/CSS) (for custom animations).
*   **Admin Dashboard:** [Filament V3](https://filamentphp.com/) (TALL Stack based admin panel using Livewire, Alpine.js, and Tailwind).
*   **Database:** [MySQL](https://www.mysql.com/) (Relational data storage).
*   **Asset Bundler:** [Vite](https://vitejs.dev/) (For compiling JS and CSS).

---

## 🏛 2. Architecture: MVC-Plus
The project follows the **Model-View-Controller (MVC)** architectural pattern, but extends it with specialized layers for administrative management.

### A. The Core MVC
1.  **Model (`app/Models/`)**: The **Data Layer**.
    *   Example: `User.php`, `Announcement.php`, `AcademicSchedule.php`.
    *   *Job:* Knows the database structure and how data relates (e.g., A Student has many Tasks).
2.  **View (`resources/views/`)**: The **Presentation Layer**.
    *   Example: `alumni.blade.php`, `community.blade.php`, `talents.blade.php`.
    *   *Job:* The HTML and CSS users see. Uses **Blade Components** (`resources/views/components/`) for reusable UI like cards.
3.  **Controller (`app/Http/Controllers/`)**: The **Logic Layer**.
    *   Example: `AlumniController.php`, `ScheduleController.php`.
    *   *Job:* The "Waiter" that takes a user's request, gets data from the Model, and serves it to the View.

### B. The Administrative Layer (Filament)
*   **Resources (`app/Filament/Resources/`)**:
    *   *Job:* Automates the MVC pattern for the Admin panel. It provides the "Control Center" where admins can approve alumni, manage students, and update community posts without writing code for every page.

---

## 📂 3. File Responsibilities Breakdown

| Folder / File | Primary Responsibility |
| :--- | :--- |
| **`app/Models/User.php`** | Manages authentication, user security, and campus-specific data (ID, Batch, Dept). |
| **`app/Providers/AppServiceProvider.php`** | Global "Startup Manager." Sets security rate limits and shares data (like Topbar info) site-wide. |
| **`app/Http/Controllers/`** | Handles all logic for the **Public Website** (Signup, viewing posts, downloading notes). |
| **`app/Filament/Resources/`** | Handles all logic for the **Admin Panel** (Approving talent cards, deleting spam). |
| **`resources/views/components/`** | Your "UI Library." Contains reusable card designs (`alumni-card`, `post-card`) to keep design consistent. |
| **`resources/js/bootstrap.js`** | Configures background communication (Axios) between the browser and the server. |
| **`public/build/manifest.json`** | A map for Vite to ensure users always see the latest version of your CSS/JS (Cache Busting). |
| **`.env`** | The "Secret Box." Holds your database passwords, app keys, and private settings. |

---

## 🚀 4. Key Workflows

1.  **Student Sign Up:** 
    *   Handled by `User` model (storing data) and standard Authentication Controllers.
2.  **Alumni Registration:**
    *   Alumni submits a form → Managed by `AlumniController`.
    *   Admin reviews/approves → Managed by `AlumniRegistrationResource` (Filament).
3.  **Community Interaction:**
    *   Students post updates → Handled via `CommentController` and `post-card.blade.php`.
4.  **Academic Life:**
    *   Management of classes, routines, and task reminders via `ScheduleController` and `RoutineController`.

---

## ✨ 5. Design Philosophy
The project prioritizes a **Premium Academic Aesthetic**. Instead of using generic templates, it uses custom CSS gradients, staggered entrance animations (in `alumni.js` and `community.js`), and a unique "ID Card" styling for talents to give the campus internal tools a modern, professional feel.
