# Case Use Descriptions: Campus Buddy

### Case Description-01: Registration

| Parameter | Value |
| :--- | :--- |
| **Use Case** | Registration |
| **Goal** | Users can register to sign in to the system. |
| **Precondition** | Users must have access to the registration portal. |
| **Success End Condition** | Notification: "Successfully Registered!" |
| **Failed End Condition** | Notification: "Submission Not Submitted" |
| **Primary Actors:** | Students, Teachers, Parents, Administrators |
| **Secondary Actors:** | N/A |
| **Trigger** | User requests a registration form to fill up |
| **Description / Main Success Scenario** | 1. Press "Registration" Button <br> 2. Provide registration form <br> 3. Enter Information <br> 4. Press "Submit" Button <br> 5. Information saved <br> 6. The system saves the details and shows them "Successfully Registered!" notification |
| **Alternative Flows** | 2.1 System Doesn't work. <br> 2.1.a Try Again Later! <br> 4.1 The user did not fill up the details! <br> 4.1.a Checked by the system & Notified by "Please Fill Up the Box". <br> 5.1 The system did not respond <br> 5.1.a Show Error Message. <br> 6.1 The system doesn't save the details. <br> 6.1.a Notification: "Details did not Save" |
| **Quality Requirements** | The user will fill up all the details within 30 minutes. |

---

### Case Description-02: Login

| Parameter | Value |
| :--- | :--- |
| **Use Case** | Login |
| **Goal** | Users can access their personalized dashboard. |
| **Precondition** | User must have a registered account. |
| **Success End Condition** | User is redirected to the dashboard. |
| **Failed End Condition** | Notification: "Invalid Credentials" |
| **Primary Actors:** | Students, Teachers, Parents, Administrators |
| **Secondary Actors:** | N/A |
| **Trigger** | User requests access to the dashboard. |
| **Description / Main Success Scenario** | 1. Navigate to Login Page <br> 2. Provide Login credentials (Email/Password) <br> 3. Press "Login" Button <br> 4. System validates credentials <br> 5. User session initialized <br> 6. Redirect to Dashboard |
| **Alternative Flows** | 3.1 Incorrect Password/Email <br> 3.1.a System displays error message <br> 4.1 System timeout <br> 4.1.a Show connection error message |
| **Quality Requirements** | The login process should complete within 3 seconds on a stable connection. |

---

### Case Description-03: Account Settings Update

| Parameter | Value |
| :--- | :--- |
| **Use Case** | Account Settings Update |
| **Goal** | Users can modify their security and basic profile information. |
| **Precondition** | User must be logged in. |
| **Success End Condition** | Notification: "Profile Updated Successfully" |
| **Failed End Condition** | Notification: "Update Failed" |
| **Primary Actors:** | Students, Teachers, Parents, Administrators |
| **Secondary Actors:** | N/A |
| **Trigger** | User navigates to Settings page. |
| **Description / Main Success Scenario** | 1. Access Settings Menu <br> 2. Modify specific fields (Name, Password, etc.) <br> 3. Press "Save Changes" <br> 4. System validates inputs <br> 5. Database is updated <br> 6. Show success notification |
| **Alternative Flows** | 2.1 Password mismatch in confirmation field <br> 2.1.a Show "Passwords do not match" <br> 4.1 Input validation error (e.g., short password) <br> 4.1.a Show field-specific error |
| **Quality Requirements** | Changes should reflect immediately after the reload. |

---

### Case Description-04: Advanced Profile Management

| Parameter | Value |
| :--- | :--- |
| **Use Case** | Advanced Profile Management |
| **Goal** | Synchronize system content with user's academic status. |
| **Precondition** | User must be logged in. |
| **Success End Condition** | Dashboard content updates to match new department/batch. |
| **Failed End Condition** | Profile remains unchanged. |
| **Primary Actors:** | Students |
| **Secondary Actors:** | N/A |
| **Trigger** | User updates department or batch in profile. |
| **Description / Main Success Scenario** | 1. Open Academic Profile settings <br> 2. Select new Department, Batch, or Semester <br> 3. Press "Update Context" <br> 4. System saves metadata <br> 5. Redirect to Dashboard <br> 6. Content (Routine/Resources) filters update |
| **Alternative Flows** | 2.1 Mandatory field left empty <br> 2.1.a Highlight empty field <br> 4.1 Database connection error <br> 4.1.a Show generic error message |
| **Quality Requirements** | Filtered results should update within 1 second. |

---

### Case Description-05: Profile Picture Personalization

| Parameter | Value |
| :--- | :--- |
| **Use Case** | Profile Picture Personalization |
| **Goal** | Users can personalize their visual identity. |
| **Precondition** | Image file available on user device. |
| **Success End Condition** | New image displayed in Topbar and Profile. |
| **Failed End Condition** | Notification: "Upload Failed" |
| **Primary Actors:** | Students, Teachers, Parents, Administrators |
| **Secondary Actors:** | Storage Server |
| **Trigger** | User clicks on profile image to change it. |
| **Description / Main Success Scenario** | 1. Trigger "Change Image" <br> 2. Select file from device <br> 3. Press "Upload" <br> 4. System validates file type/size <br> 5. Old image is deleted <br> 6. New image is stored and displayed |
| **Alternative Flows** | 4.1 File exceeds 2MB limit <br> 4.1.a Notification: "File size too large" <br> 4.2 Invalid file format <br> 4.2.a Notification: "Only PNG/JPG allowed" |
| **Quality Requirements** | Upload should complete within 5 seconds for a 2MB file. |

---

### Case Description-06: Dynamic Routine Creation

| Parameter | Value |
| :--- | :--- |
| **Use Case** | Dynamic Routine Creation |
| **Goal** | Students can maintain a digital class schedule. |
| **Precondition** | User logged in as Student. |
| **Success End Condition** | Class added to the routine list. |
| **Failed End Condition** | Notification: "Scheduling Conflict" or "Error" |
| **Primary Actors:** | Students |
| **Secondary Actors:** | N/A |
| **Trigger** | User clicks "Add Class" on Routine page. |
| **Description / Main Success Scenario** | 1. Open Routine creation modal <br> 2. Enter Class Name, Day, Time, and Room <br> 3. Press "Add to Schedule" <br> 4. System validates for overlaps <br> 5. Database record created <br> 6. Routine view refreshes |
| **Alternative Flows** | 4.1 Time overlap with existing class <br> 4.1.a Prompt: "Time slot already taken" <br> 2.1 Missing required field <br> 2.1.a Highlight required field |
| **Quality Requirements** | Data should be stored persistently across sessions. |

---

### Case Description-07: Real-Time Routine Status

| Parameter | Value |
| :--- | :--- |
| **Use Case** | Real-Time Routine Status |
| **Goal** | View current academic engagement automatically. |
| **Precondition** | Routine must have entries. |
| **Success End Condition** | Current class is highlighted in UI. |
| **Failed End Condition** | System shows generic schedule list. |
| **Primary Actors:** | Students |
| **Secondary Actors:** | System Clock |
| **Trigger** | User navigates to Routine page. |
| **Description / Main Success Scenario** | 1. Access Routine page <br> 2. System fetches current server time <br> 3. Match time with schedule entries <br> 4. Highlight "Ongoing" class <br> 5. Calculate countdown to next class <br> 6. Update UI timers |
| **Alternative Flows** | 3.1 No class currently scheduled <br> 3.1.a Show "No classes now" message |
| **Quality Requirements** | Status calculation must be accurate to the server minute. |

---

### Case Description-08: Class Task Tracking

| Parameter | Value |
| :--- | :--- |
| **Use Case** | Class Task Tracking |
| **Goal** | Manage academic deadlines like Assignments/Quizzes. |
| **Precondition** | Student is enrolled in subjects. |
| **Success End Condition** | Task added to "To-Do" list with countdown. |
| **Failed End Condition** | Error message displayed. |
| **Primary Actors:** | Students |
| **Secondary Actors:** | N/A |
| **Trigger** | User clicks "Add New Task". |
| **Description / Main Success Scenario** | 1. Open Add Task modal <br> 2. Enter Title, Category, and Deadline Date <br> 3. Press "Create Task" <br> 4. System saves task <br> 5. Task list refreshes <br> 6. Countdown starts displaying |
| **Alternative Flows** | 2.1 Past date selected for deadline <br> 2.1.a Show: "Date cannot be in the past" |
| **Quality Requirements** | Countdown should update in real-time or on refresh. |

---

### Case Description-09: Task Status Management

| Parameter | Value |
| :--- | :--- |
| **Use Case** | Task Status Management |
| **Goal** | Track progress of academic tasks. |
| **Precondition** | Task must exist in the list. |
| **Success End Condition** | Task marked as "Completed" and progress bar updates. |
| **Failed End Condition** | Status toggle fails. |
| **Primary Actors:** | Students |
| **Secondary Actors:** | N/A |
| **Trigger** | User clicks on status toggle/checkbox. |
| **Description / Main Success Scenario** | 1. Select a pending task <br> 2. Click "Mark as Done" <br> 3. System updates database status <br> 4. Task moves to "Completed" section <br> 5. Progress bar increments <br> 6. UI notifies update |
| **Alternative Flows** | 3.1 Database write failure <br> 3.1.a Notification: "Action failed, retry" |
| **Quality Requirements** | UI update should be snappy (under 500ms). |

---

### Case Description-10: Task Modification/Deletion

| Parameter | Value |
| :--- | :--- |
| **Use Case** | Task Modification/Deletion |
| **Goal** | Correct errors in task entries or remove redundant tasks. |
| **Precondition** | Task must exist. |
| **Success End Condition** | Task updated or removed from list. |
| **Failed End Condition** | Notification: "Delete/Edit Failed" |
| **Primary Actors:** | Students |
| **Secondary Actors:** | N/A |
| **Trigger** | User clicks Edit or Delete icon on a task. |
| **Description / Main Success Scenario** | 1. Trigger Edit/Delete action <br> 2. Confirm deletion in prompt (if deleting) <br> 3. Update details in modal (if editing) <br> 4. System updates/deletes record <br> 5. List refreshes <br> 6. Success message shown |
| **Alternative Flows** | 2.1 User cancels deletion <br> 2.1.a Task remains unchanged |
| **Quality Requirements** | Deletion should require a confirmation step to prevent accidents. |

---

### Case Description-11: Study Material Shared Upload

| Parameter | Value |
| :--- | :--- |
| **Use Case** | Study Material Shared Upload |
| **Goal** | Collaborative resource sharing among students. |
| **Precondition** | User has a digital resource (PDF/Note). |
| **Success End Condition** | Resource visible in the Browse section for the department. |
| **Failed End Condition** | File rejected. |
| **Primary Actors:** | Students |
| **Secondary Actors:** | Admin Moderation |
| **Trigger** | User clicks "Upload Resource". |
| **Description / Main Success Scenario** | 1. Open Upload form <br> 2. Attach PDF/Notes file <br> 3. Select Department and Category <br> 4. Provide description <br> 5. Press "Upload" <br> 6. System saves file and metadata |
| **Alternative Flows** | 2.1 Unsupported file format <br> 2.1.a Show: "Please upload PDF or Image" <br> 5.1 Storage limit reached <br> 5.1.a Show: "Storage full" |
| **Quality Requirements** | Uploaded materials should be categorized correctly by metadata. |

---

### Case Description-12: Resource Browsing & Download

| Parameter | Value |
| :--- | :--- |
| **Use Case** | Resource Browsing & Download |
| **Goal** | Easy access to academic materials. |
| **Precondition** | Resources must be available in the repository. |
| **Success End Condition** | Resource opens in-browser or downloads to device. |
| **Failed End Condition** | Resource unavailable/Error. |
| **Primary Actors:** | Students |
| **Secondary Actors:** | N/A |
| **Trigger** | User clicks "View" or "Download". |
| **Description / Main Success Scenario** | 1. Navigate to Resources catalog <br> 2. Filter by category (Notes/PDFs) <br> 3. Select a resource <br> 4. Click Download/View <br> 5. System initiates file stream <br> 6. Resource delivered to user |
| **Alternative Flows** | 4.1 File not found on server <br> 4.1.a Show generic error message |
| **Quality Requirements** | Downloads should be served with appropriate MIME types for preview. |

---

### Case Description-13: Question Bank Filtering

| Parameter | Value |
| :--- | :--- |
| **Use Case** | Question Bank Filtering |
| **Goal** | Quick access to past exam papers. |
| **Precondition** | Question bank has data. |
| **Success End Condition** | List of specific papers matching filters displayed. |
| **Failed End Condition** | "No Results Found" message. |
| **Primary Actors:** | Students |
| **Secondary Actors:** | N/A |
| **Trigger** | User applies filter on Question Bank page. |
| **Description / Main Success Scenario** | 1. Open Question Bank <br> 2. Select Year (e.g., 2024) <br> 3. Select Session (Mid/Final) <br> 4. Select Subject <br> 5. Click Filter <br> 6. System displays relevant papers |
| **Alternative Flows** | 6.1 No matches for combination <br> 6.1.a Show "Try clearing filters" |
| **Quality Requirements** | Filtering should happen without full page reload if possible (AJAX). |

---

### Case Description-14: Alumni Network Enrollment

| Parameter | Value |
| :--- | :--- |
| **Use Case** | Alumni Network Enrollment |
| **Goal** | Expand the mentorship pool for current students. |
| **Precondition** | User is a graduate/alumni. |
| **Success End Condition** | Registration enters "Pending Approval" status. |
| **Failed End Condition** | Error in registration form. |
| **Primary Actors:** | Alumni |
| **Secondary Actors:** | Administrators |
| **Trigger** | User submits alumni registration form. |
| **Description / Main Success Scenario** | 1. Fill Alumni Registration form <br> 2. Provide Expertise, LinkedIn link, and Bio <br> 3. Submit Form <br> 4. System validates inputs <br> 5. Create "Inactive" alumni record <br> 6. Notify user "Pending Admin Approval" |
| **Alternative Flows** | 1.1 Incomplete required professional info <br> 1.1.a Prompt for missing details |
| **Quality Requirements** | Data must be stored in a specialized 'pending' state. |

---

### Case Description-15: Alumni Profile Search

| Parameter | Value |
| :--- | :--- |
| **Use Case** | Alumni Profile Search |
| **Goal** | Connect students with specific industry mentors. |
| **Precondition** | Approved alumni must exist. |
| **Success End Condition** | Cards of relevant alumni are displayed. |
| **Failed End Condition** | No alumni match the search. |
| **Primary Actors:** | Students |
| **Secondary Actors:** | N/A |
| **Trigger** | User enters criteria in Alumni page search. |
| **Description / Main Success Scenario** | 1. Open Alumni directory <br> 2. Enter Expertise (e.g. "DevOps") <br> 3. Set Filter <br> 4. System queries alumni table <br> 5. Display dynamic cards <br> 6. User views contact/social links |
| **Alternative Flows** | 4.1 Search query too broad <br> 4.1.a Show hints for better search |
| **Quality Requirements** | High-quality visual cards should load with entrance animations. |

---

### Case Description-16: Community Posting

| Parameter | Value |
| :--- | :--- |
| **Use Case** | Community Posting |
| **Goal** | Share thoughts, news, or queries with the batch. |
| **Precondition** | User must be logged in. |
| **Success End Condition** | Post appears in the public feed. |
| **Failed End Condition** | Post rejected (spam check). |
| **Primary Actors:** | Students, Alumni, Teachers |
| **Secondary Actors:** | N/A |
| **Trigger** | User clicks "Post" in Community Hub. |
| **Description / Main Success Scenario** | 1. Type post content in text area <br> 2. Add optional media (if supported) <br> 3. Click "Submit Post" <br> 4. System stores post <br> 5. Post prepended to feed <br> 6. Success notification |
| **Alternative Flows** | 1.1 Content too short/Empty <br> 1.1.a Disable Submit button |
| **Quality Requirements** | Feed updates should appear chronologically. |

---

### Case Description-17: Community Interaction (Comments/Replies)

| Parameter | Value |
| :--- | :--- |
| **Use Case** | Community Interaction |
| **Goal** | Discussion and engagement on community posts. |
| **Precondition** | Post must exist. |
| **Success End Condition** | Comment/Reply nested under the target. |
| **Failed End Condition** | Submission error. |
| **Primary Actors:** | Students, Alumni, Teachers |
| **Secondary Actors:** | N/A |
| **Trigger** | User clicks "Reply" on a post or comment. |
| **Description / Main Success Scenario** | 1. Click Reply button <br> 2. Input text in reply box <br> 3. Click "Comment" <br> 4. System links comment to parent ID <br> 5. Thread updates dynamically <br> 6. Content is highlighted |
| **Alternative Flows** | 4.1 Post deleted during typing <br> 4.1.a Show: "Post no longer exists" |
| **Quality Requirements** | Nested replies should be visually indented for clarity. |

---

### Case Description-18: Talent Card Generation

| Parameter | Value |
| :--- | :--- |
| **Use Case** | Talent Card Generation |
| **Goal** | Showcase student skills to the campus community. |
| **Precondition** | User has skills to share. |
| **Success End Condition** | Visually styled "ID Card" appears in Talent Showcase. |
| **Failed End Condition** | Form submission error. |
| **Primary Actors:** | Students |
| **Secondary Actors:** | N/A |
| **Trigger** | User clicks "Showcase My Talent". |
| **Description / Main Success Scenario** | 1. Provide expertise area and bio <br> 2. Add social links (GitHub/Dribbble) <br> 3. Select theme/style (optional) <br> 4. Submit form <br> 5. System generates visual card <br> 6. Card becomes public |
| **Alternative Flows** | 2.1 Invalid URL for social link <br> 2.1.a Reject with validation error |
| **Quality Requirements** | The card should have a "premium" aesthetic design. |

---

### Case Description-19: Buddy AI Academic Query

| Parameter | Value |
| :--- | :--- |
| **Use Case** | Buddy AI Academic Query |
| **Goal** | Get instant academic assistance. |
| **Precondition** | AI module is active. |
| **Success End Condition** | AI provides a relevant text response. |
| **Failed End Condition** | "AI Busy" or Timeout. |
| **Primary Actors:** | Students |
| **Secondary Actors:** | AI API Provider |
| **Trigger** | User types message in Buddy AI interface. |
| **Description / Main Success Scenario** | 1. Open AI Assistant <br> 2. Enter academic query <br> 3. Press "Ask Buddy" <br> 4. System sends query to backend <br> 5. AI generates response <br> 6. Response rendered in chat bubble |
| **Alternative Flows** | 5.1 API key limit reached <br> 5.1.a Show: "Service currently unavailable" |
| **Quality Requirements** | Response should start streaming within 2 seconds. |

---

### Case Description-20: Reading Announcements & News

| Parameter | Value |
| :--- | :--- |
| **Use Case** | Reading Announcements & News |
| **Goal** | Stay informed about campus events. |
| **Precondition** | Admin must have posted news. |
| **Success End Condition** | User views full content of news items. |
| **Failed End Condition** | News feed won't load. |
| **Primary Actors:** | Students, Teachers, Parents |
| **Secondary Actors:** | N/A |
| **Trigger** | User opens Announcements section. |
| **Description / Main Success Scenario** | 1. Navigate to Announcements <br> 2. Click on a news headline <br> 3. Full article expands <br> 4. View attached media/dates <br> 5. Mark as read (optional) |
| **Alternative Flows** | 3.1 Article restricted to specific batch <br> 3.1.a Redirect to permission error |
| **Quality Requirements** | Most recent news should always be at the top. |

---

### Case Description-21: Club & Event Discovery

| Parameter | Value |
| :--- | :--- |
| **Use Case** | Club & Event Discovery |
| **Goal** | Explore extracurricular activities. |
| **Precondition** | Club directory populated. |
| **Success End Condition** | User views club details/events. |
| **Failed End Condition** | Directory empty. |
| **Primary Actors:** | Students |
| **Secondary Actors:** | Club Admins |
| **Trigger** | User clicks on "Clubs" menu. |
| **Description / Main Success Scenario** | 1. Browse list of Clubs <br> 2. Click on a club for details <br> 3. View upcoming events timeline <br> 4. Select "Interested" <br> 5. System adds to user's interested list |
| **Alternative Flows** | 4.1 Event full <br> 4.1.a Show status: "Closed" |
| **Quality Requirements** | Images for clubs should load with placeholders if slow. |

---

### Case Description-22: Admin Resource Moderation

| Parameter | Value |
| :--- | :--- |
| **Use Case** | Admin Resource Moderation |
| **Goal** | Maintain platform quality and safety. |
| **Precondition** | User is an Administrator. |
| **Success End Condition** | Content is deleted or modified globally. |
| **Failed End Condition** | Change not saved. |
| **Primary Actors:** | Administrators |
| **Secondary Actors:** | N/A |
| **Trigger** | Admin logs into Filament Panel. |
| **Description / Main Success Scenario** | 1. Access Moderation Resource <br> 2. Locate reported/spam content <br> 3. Select Edit or Delete <br> 4. Execute action <br> 5. System sweeps database and file storage <br> 6. Notification: "Content Removed" |
| **Alternative Flows** | 4.1 Permission denied for specific role <br> 4.1.a Show auth error |
| **Quality Requirements** | Global changes must be immediate and irreversible (after confirmation). |

---

### Case Description-23: Alumni Approval Workflow

| Parameter | Value |
| :--- | :--- |
| **Use Case** | Alumni Approval Workflow |
| **Goal** | Verify alumni credentials before public display. |
| **Precondition** | Pending alumni registrations exist. |
| **Success End Condition** | Alumnus moves from "Pending" to "Approved" (Visible). |
| **Failed End Condition** | Registration remains pending or is rejected. |
| **Primary Actors:** | Administrators |
| **Secondary Actors:** | Alumni (Target) |
| **Trigger** | Admin opens "Pending Approvals" in Panel. |
| **Description / Main Success Scenario** | 1. Review pending alumni list <br> 2. Verify identity/certificate <br> 3. Click "Approve" <br> 4. System updates record status to 'active' <br> 5. Alumnus profile becomes visible <br> 6. Automated welcome email sent |
| **Alternative Flows** | 3.1 Reject button click <br> 3.1.a Record deleted and user notified |
| **Quality Requirements** | Verification process should take less than 1 minute for a prepared admin. |
