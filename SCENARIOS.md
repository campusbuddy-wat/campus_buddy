# Exhaustive Project Scenarios: Campus Buddy

This document outlines every possible user interaction and workflow within the Campus Buddy platform, categorized by module.

---

## 🔐 1. Authentication & Account Management
### **Scenario-1: New User Registration**
*   Request to join the platform.
*   Enter Name, University ID, Batch, Department, Email, and Password.
*   Submit registration.
*   **Result:** Account created; basic academic profile initialized.

### **Scenario-2: User Authentication (Login)**
*   Request access to the dashboard.
*   Enter Email and Password.
*   System validates credentials.
*   **Result:** User session started; redirected to personal dashboard.

### **Scenario-3: Account Settings Update**
*   Request to change account details (Settings page).
*   Modify Name, Email, or update Password.
*   Submit changes.
*   **Result:** System updates secure credentials and profile metadata.

### **Scenario-4: Advanced Profile Management**
*   Request to update academic specifics (Department, Batch, Semester, Major).
*   Select new values from dropdowns.
*   **Result:** Dashboard and resource filters update to match the new academic context.

### **Scenario-5: Profile Picture Personalization**
*   Request to upload, change, or delete a profile image.
*   Select image file (max 2MB).
*   **Result:** Image is stored securely; old image is cleaned from server; UI updates across the site.

---

## 📅 2. Academic Life Management
### **Scenario-6: Dynamic Routine Creation**
*   Request to add a class to the schedule.
*   Enter Subject, Day, Time, and Teacher.
*   **Result:** Class successfully added to the dynamic routine tracker.

### **Scenario-7: Real-Time Routine Status**
*   Navigate to the Routine page.
*   System checks current time against the schedule.
*   **Result:** Current class is highlighted, and time until the next class is calculated.

### **Scenario-8: Class Task Tracking (Assignments/Quizzes)**
*   Request to add a deadline (Assignment, Presentation, or Quiz).
*   Enter title, subject, and due date.
*   **Result:** Task appears in the "To-Do" section with a countdown.

### **Scenario-9: Task Status Management**
*   Select an existing task.
*   Toggle status between "Pending" and "Completed."
*   **Result:** Task progress bar updates; task moves to bottom of list.

### **Scenario-10: Task Modification/Deletion**
*   Request to edit task details or delete an accidental entry.
*   Modify fields or click "Delete."
*   **Result:** Database is updated; task list refreshes instantly.

---

## 📚 3. Resources & Question Bank
### **Scenario-11: Study Material Shared Upload**
*   Select "Upload Resource."
*   Attach PDF/Notes, select department, and provide a description.
*   **Result:** File stored in the local storage driver; accessible to the department.

### **Scenario-12: Resource Browsing & Download**
*   Browse the department-specific repository.
*   Click "View" or "Download" on a resource.
*   **Result:** Resource opens in-browser or triggers a download via the MaterialController.

### **Scenario-13: Question Bank Filtering**
*   Navigate to the Question Bank.
*   Filter by Session (Mid/Final), Year, and Subject.
*   **Result:** System displays the specific archive of past exam papers.

---

## 🤝 4. Networking & Community
### **Scenario-14: Alumni Network Enrollment**
*   Request to register as an alumnus.
*   Provide professional expertise, industry links, and graduation year.
*   **Result:** Profile enters "Pending Approval" state.

### **Scenario-15: Alumni Profile Search**
*   Access the Alumni page.
*   Filter by Expertise or Industry (e.g., Software Engineering).
*   **Result:** List of relevant mentors and networking contacts is displayed.

### **Scenario-16: Community Posting**
*   Compose a thought or question in the Community Hub.
*   Submit the post.
*   **Result:** Post appears in the feed for all students in the batch/department.

### **Scenario-17: Community Interaction (Comments/Replies)**
*   View a post and click "Reply."
*   Type a comment or a nested reply to an existing comment.
*   **Result:** Real-time update of the interaction thread via the CommentController.

### **Scenario-18: Talent Card Generation**
*   Request to showcase a skill.
*   Add expertise, bio, and social media links (LinkedIn/GitHub).
*   **Result:** A premium visually-styled card is generated in the Talent Showcase.

---

## 🤖 5. Advanced Support & Admin
### **Scenario-19: Buddy AI Academic Query**
*   Open the AI assistant.
*   Type an academic question or request a study summary.
*   **Result:** AI provides tailored advice and real-time guidance.

### **Scenario-20: Reading Announcements & News**
*   Navigate to the Announcements section.
*   View the latest campus-wide updates.
*   **Result:** Student stays informed about deadlines and events.

### **Scenario-21: Campus Club & Event Discovery**
*   Browse the Club Directory.
*   View upcoming events and club descriptions.
*   **Result:** User understands extracurricular opportunities.

### **Scenario-22: Admin Resource Moderation (Filament)**
*   Admin logs into the specialized Panel.
*   Identifies spam posts or incorrect routine data.
*   **Result:** Content is deleted or edited directly in the database.

### **Scenario-23: Alumni Approval Workflow**
*   Admin reviews pending alumni registrations.
*   Verifies identity and clicks "Approve."
*   **Result:** Alumnus profile becomes visible to the entire student body.
