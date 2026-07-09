# Campus Buddy — Storage & Database FAQ

> **Last updated:** July 2026 (local dev session)
> This document answers exactly where every piece of data and every file in Campus Buddy lives — locally or in the cloud.

---

## 🗄️ Database Questions

### Q1 — Where is the main database?
**A:** All structured data (users, schedules, posts, notes metadata, chat history, question bank, events, clubs, etc.) is stored in a **local MySQL database** running on your own machine.

| Setting | Value |
|---|---|
| Driver | `mysql` |
| Host | `127.0.0.1` (localhost) |
| Database name | `User_DB` |
| Cache driver | `database` (same local MySQL) |
| Session driver | `database` (same local MySQL) |

No database data is sent to any cloud service. Everything stays on your machine.

---

### Q2 — Which features read and write to the local database?
**A:** Every feature that stores records uses the local MySQL DB:

| Feature | Tables Used |
|---|---|
| User accounts & auth | `users` |
| Class routine / schedule | `schedules` |
| Notes & class materials (metadata) | `materials` |
| Community posts & likes | `posts`, `likes`, `comments`, `comment_likes` |
| Buddy AI chat history | `ai_chats` |
| Question bank | `question_banks` |
| Campus events | `events` |
| Clubs | `clubs` |
| Alumni directory | `alumni_registrations` |
| Talent showcase | `talents` |
| Announcements | `announcements` |
| Docs / Team | `docs_sections`, `docs_settings`, `docs_team_members` |

---

## 🌥️ Cloudinary Questions

### Q3 — What is Cloudinary used for?
**A:** Cloudinary is a cloud media storage service. Campus Buddy uses it to host **uploaded images and documents** so they are available on any device/internet connection, not just your local machine.

---

### Q4 — Which features still upload files to Cloudinary?

| Feature | What is uploaded | Cloudinary folder |
|---|---|---|
| **Profile image** (signup & profile edit) | User avatar photo | `profile_images/` |
| **Community posts** | Post attachment (image/PDF) | `posts/attachments/` |
| **Campus events** | Event banner/image | `events/` |
| **Question bank** | Exam paper scans / PDFs | `question_banks/` |
| **Alumni directory** | Profile photo + company logo | `alumni/` |

> **Status:** These features **still use Cloudinary** and have not been changed.

---

### Q5 — Which features were changed to use local storage instead of Cloudinary?

| Feature | Old behaviour | New behaviour (local dev) |
|---|---|---|
| **Class materials / PDF notes upload** | Uploaded to Cloudinary as raw file | **Saved locally** to `storage/app/public/materials/` |

> **Why changed?** Files stored on Cloudinary get a raw extensionless URL (e.g. `ox4ai6suqkmjsk7vpkek`) with no MIME type header. This broke:
> - The **AI PDF/Note Summarizer** (could not parse the file)
> - The **View / Download** buttons (downloaded as unreadable binary file)
>
> Saving locally means the file is readable directly by the PHP parser with no HTTP round-trip.

---

### Q6 — I uploaded materials before this change. Are those old files still on Cloudinary?
**A:** Yes. Files uploaded **before** the local-storage switch have Cloudinary URLs like `https://res.cloudinary.com/...` stored in the database. Of the 17 materials currently in the database:

| Storage | Count |
|---|---|
| Cloudinary (old uploads) | 11 |
| Local disk (new uploads) | 6 |

The app handles both automatically — it detects whether the path is an HTTP URL (Cloudinary) or a relative path (local) and routes accordingly.

---

### Q7 — Can the AI Summarizer read files from both local and Cloudinary?
**A:** Yes, both are now supported:

| File location | How the AI reads it |
|---|---|
| **Local** (`storage/app/public/...`) | Reads the file directly from disk — fastest |
| **Cloudinary** (`https://...`) | Downloads the file to a temporary file on the server, parses it, then deletes the temp file |

After reading the file content, it is extracted as text (PDF → `smalot/pdfparser`, PPTX → `ZipArchive` XML reader, DOCX/DOC → `phpoffice/phpword`) and sent to the Groq AI API.

---

## 🤖 AI / External API Questions

### Q8 — What external API does the AI use?
**A:** All AI features (Buddy AI chat, PDF Summarizer, Routine Advisor, Daily Briefing, Question Bank) use the **Groq Cloud API** (not a local model).

| Setting | Value |
|---|---|
| API Provider | Groq (https://groq.com) |
| Model | `llama-3.3-70b-versatile` |
| Max tokens per response | `3072` |
| Temperature | `0.7` |
| Timeout | `60 seconds` |

The API key is stored in your local `.env` file as `GROQ_API_KEY`. Nothing from your database is sent to Groq except the context/prompt text the AI needs to answer questions.

---

### Q9 — Does the AI store my conversations anywhere in the cloud?
**A:** No. Conversation messages are stored only in your **local MySQL database** (`ai_chats` table). The Groq API receives only the current session message content to generate a reply — it does not persist anything.

---

## 📁 File View / Download Questions

### Q10 — Why did View / Download used to give an unreadable file?
**A:** Cloudinary stores raw uploads with no file extension in the URL and returns them with a generic `application/octet-stream` MIME type. Browsers do not know what to do with that, so they save it as a nameless binary file.

---

### Q11 — How does View / Download work now?
**A:** Both buttons now go through a **secure Laravel backend proxy** (`/notes/view/{id}` and `/notes/download/{id}`):

1. The backend fetches the file content (local disk or Cloudinary)
2. Sets the correct MIME type (e.g. `application/pdf` for PDFs, `application/vnd.openxmlformats-officedocument.presentationml.presentation` for PPTX)
3. Sets a proper filename from the material title and extension (e.g. `types-of-machine-learning.pptx`)
4. Streams it to the browser

This means **View** opens natively in the browser tab, and **Download** saves a properly named, openable file.

---

## 🧩 Quick Reference Summary

| Feature | Data in | Files in |
|---|---|---|
| User accounts | Local MySQL | Local MySQL |
| Class schedule / routine | Local MySQL | — |
| Notes / class materials (metadata) | Local MySQL | **New → Local disk** / Old → Cloudinary |
| Community posts | Local MySQL | Cloudinary |
| Buddy AI chat history | Local MySQL | — |
| Question bank | Local MySQL | Cloudinary |
| Campus events | Local MySQL | Cloudinary |
| Clubs | Local MySQL | — |
| Profile images | Local MySQL | Cloudinary |
| Alumni directory | Local MySQL | Cloudinary |
| AI responses (Groq) | Local MySQL | — (API only) |

---

> **Note:** The local-storage switch for class materials applies to your **local development environment only** and has not been pushed to the live GitHub repository.

---

## 🛠️ Self-Debugging & Error Troubleshooting

### Q12 — I got a "500 Internal Server Error". How do I find out what caused it?
**A:** In a Laravel application, there are **three primary ways** to see the exact error message, filename, and line number causing the crash:

#### Method 1: Check Laravel's Log File (Most Reliable)
Whenever Laravel encounters a crash, it writes a detailed error stack trace to the log file on disk.
- **Location:** `storage/logs/laravel.log` (relative to the project root directory).
- **How to read it via Terminal:**
  - View the last 50 lines of logs:
    ```bash
    tail -n 50 storage/logs/laravel.log
    ```
  - View live logs in real time as they happen:
    ```bash
    tail -f storage/logs/laravel.log
    ```

#### Method 2: Enable Browser Debug Mode
If your local development environment shows a generic, grey "500 Server Error" screen, it means detailed error reporting is turned off.
1. Open the `.env` file in your project root.
2. Find the `APP_DEBUG` key and set it to `true`:
   ```env
   APP_DEBUG=true
   ```
3. Clear your configuration cache so Laravel registers the change:
   ```bash
   php artisan config:clear
   ```
4. Refresh the webpage. You will now see a detailed error page (Ignition) pointing to the exact line of code that failed.

#### Method 3: Watch the Artisan Serve Console Output
If you are running the application using `php artisan serve`, look at the terminal window where the serve command is running.
- When a `500` error occurs, the serve logs will show the incoming request and will frequently print the class name and message of the exception that occurred (e.g., `Class "SomeClass" not found` or `QueryException`).

#### Method 4: Use Laravel Pail (Real-Time Terminal Tailing)
If you are using Laravel 11+, you can run Pail to watch exceptions stream into your terminal in real time:
```bash
php artisan pail
```
