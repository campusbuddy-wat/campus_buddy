# Campus Buddy – Technical Documentation

This document outlines the technical stack, database management, and implemented module functionalities of the **Campus Buddy** project.

---

### **🛠 Technical Stack**

*   **Backend**: 
    *   **PHP ^8.2** – Core server-side language.
    *   **Laravel ^12.0** – Modern MVC framework for secure and scalable architecture.
*   **Frontend**:
    *   **Blade Templating Engine** – Native Laravel engine for component-based UI.
    *   **Vanilla CSS3** – Custom-built design system (using `.page-container` grid) focused on premium aesthetics and responsiveness.
    *   **Vanilla JavaScript (ES6+)** – Client-side logic for interactivity and staggered scroll animations.
*   **Administration**:
    *   **Filament ^3.2** – Advanced admin panel for resource and user management.
*   **AI Integration**:
    *   **Buddy AI** – Integrated AI assistant for student support and academic insights.
*   **Asset Management**:
    *   **Vite** – For lightning-fast frontend building and hot-module replacement.

---

### **🗄 Database Management**

*   **DBMS**: **MySQL** (using InnoDB engine for ACID compliance).
*   **ORM**: **Eloquent ORM** – For object-oriented database interactions.
*   **Migration System**: Laravel Migrations for version-controlled schema management.
*   **Storage Drivers**: 
    *   `database` driver for Session and Cache management.
    *   `local` filesystem driver for academic resource storage (PDFs/Notes).

---

### **📦 Implemented Modules & Functionalities**

*   **Dashboard**
    *   Unified overview of student activities and recent updates.
    *   Quick-access links to all campus resources.
*   **Routine**
    *   Dynamic weekly class schedule management with real-time status indicators.
*   **Class Tasks**
    *   Centralized tracking for Assignments, Quizzes, and Presentations.
    *   Deadline management and status monitoring.
*   **PDF & Notes**
    *   Department-specific repository for academic materials.
    *   Support for document uploads and structured browsing.
*   **Community & Posts**
    *   Social interaction hub for students to share updates and ask questions.
    *   Talent networking for skill-based collaboration.
*   **Alumni Network**
    *   Database of former students for mentorship and career networking.
    *   Filtered search by industry and expertise.
*   **Clubs**
    *   Directory for campus organizations and extracurricular event tracking.
*   **Question Bank**
    *   Accessible archive of past semester exam papers (Midterms/Finals).
*   **Buddy AI Assistant**
    *   Integrated study assistant providing real-time academic guidance.

---
*Last Updated: April 5, 2026*
