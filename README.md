<div align="center">
  <img src="public/assets/landing/character.png" alt="Campus Buddy Logo" width="120" />

  <h1>🎓 Campus Buddy</h1>
  
  <p>
    <strong>An AI-Powered University Companion & Community Platform</strong>
  </p>

  <p>
    <img src="https://img.shields.io/badge/Laravel-12.0-FF2D20?style=for-the-badge&logo=laravel" alt="Laravel">
    <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php" alt="PHP">
    <img src="https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css" alt="Tailwind CSS">
    <img src="https://img.shields.io/badge/Groq_AI-Powered-000000?style=for-the-badge&logo=openai" alt="Groq AI">
  </p>
</div>

---

## 📖 About The Project

**Campus Buddy** is a comprehensive, AI-enhanced web application built for university students (initially tailored for Daffodil International University). It serves as a centralized hub to help students track their academic routines, manage assignments, connect with alumni, and access study materials instantly. 

At the core of the platform is **Buddy AI**—an intelligent assistant powered by the lightning-fast Groq API. Buddy AI can read and summarize uploaded PDF study notes using built-in OCR (Tesseract), answer questions about university admission rules, and generate personalized academic routines.

---

## ✨ Key Features

* 🤖 **Buddy AI Assistant**: A persistent, smart chat interface capable of answering academic queries, summarizing notes, and retrieving real-time university info. Includes distinct modes for Authenticated Students and Public Visitors.
* 📚 **Study Material & PDF Summarization**: Upload class notes and Question Banks. The built-in AI uses PDF parsing and Tesseract OCR to read documents and provide instant summaries or answer direct questions about the material.
* 📅 **Personalized Routines**: Manage weekly class schedules, upcoming quizzes, and presentation deadlines in an intuitive dashboard.
* 🤝 **Alumni & Community Networking**: A dynamic social board to connect with graduates. Includes customizable Alumni profile cards managed via the admin panel.
* 🛠️ **Filament Admin Dashboard**: A sleek, powerful backend built with Filament v3. Administrators can seamlessly manage users, documents, FAQs, and system metrics.
* 📄 **Dynamic Pitch Deck / Docs System**: A built-in presentation layer to showcase project architecture, team members, and real-time statistics.

---

## 💻 Tech Stack

**Frontend:**
* Blade Templates (Laravel)
* Tailwind CSS (Styling & Responsive Design)
* Alpine.js (Lightweight interactivity)
* Vanilla JavaScript & CSS (Custom animations)

**Backend:**
* PHP 8.2+
* Laravel 12.0 Framework
* Filament Admin Panel v3
* MySQL / SQLite Database

**AI & Utilities:**
* Groq API (LLM Integration)
* Tesseract OCR (Image text extraction)
* Smalot PDFParser (PDF text extraction)

---

## 🚀 Getting Started

To get a local copy up and running, follow these simple steps.

### Prerequisites
Before installing, ensure your system has the following installed:
* PHP >= 8.2
* Composer >= 2.x
* Node.js & NPM >= 18.x
* **Tesseract OCR Engine** (Required for AI Image Reading)
  * Linux: `sudo apt-get install tesseract-ocr`
  * Mac: `brew install tesseract`

### Installation

1. **Clone the repository**
   ```sh
   git clone https://github.com/yourusername/campus-buddy.git
   cd campus-buddy
   ```

2. **Install PHP Dependencies**
   ```sh
   composer install
   ```

3. **Install NPM Packages & Build Assets**
   ```sh
   npm install
   npm run build
   ```

4. **Environment Setup**
   Copy the example environment file and configure your variables (Database, Groq API Key):
   ```sh
   cp .env.example .env
   php artisan key:generate
   ```

5. **Run Migrations & Seed the Database**
   Our database seeders contain a full snapshot of starter data (Users, FAQs, Alumni, etc.):
   ```sh
   php artisan migrate:fresh --seed
   ```

6. **Link Storage (For Uploads)**
   ```sh
   php artisan storage:link
   ```

7. **Start the Development Server**
   ```sh
   npm run dev      # In one terminal
   php artisan serve # In another terminal
   ```

---

## 🧠 AI Configuration (Groq)

To enable Buddy AI, you must add your Groq API key to the `.env` file:
```env
GROQ_API_KEY="gsk_your_api_key_here"
GROQ_BASE_URL="https://api.groq.com/openai/v1"
GROQ_MAX_TOKENS=1536
```
*Note: We recommend setting `QUEUE_CONNECTION=sync` for local testing if you don't want to run a separate background queue worker for PDF processing.*

---

## 📄 License

Distributed under the MIT License. See `LICENSE` for more information.

---

<div align="center">
  <i>Developed with ❤️ for the Campus Buddy Project</i>
</div>
