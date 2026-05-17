# 🧠 AI Approach — Campus Buddy
### *"This is Real AI Thinking"*

> **Campus Buddy** is not a chatbot with a fixed FAQ script. It is a multi-context, retrieval-augmented, personalization-first AI system built to think about *each student individually* in real time. This document explains our full AI architecture — the decisions we made, the data we used, and the tools we built.

---

## 1. 🎯 The Core AI Problem We Solved

Most student help tools are static. They answer generic questions. They do not know *who you are*, *what class you have in 20 minutes*, or *that your quiz is tomorrow*.

We identified three AI problems that are genuinely hard to solve:

| Problem | Why It's Hard | Our Solution |
|---|---|---|
| "Am I free tomorrow afternoon?" | AI needs *your real schedule* to answer | RAG injects live DB schedule into prompt |
| "What should I study tonight?" | AI needs deadlines, gaps, and urgency | Multi-context prompt fusion |
| "Summarize my OOP notes" | File is a PDF or handwritten image | PDF Parser + Tesseract OCR pipeline |
| "What questions are in the past exams?" | Data lives in a database, not the model | Dynamic question bank prompt injection |

**Our key insight:** An LLM is only as smart as the context you give it. We built a full system to feed it the *right* context every single time.

---

## 2. 🤖 Models

### Primary Language Model: Groq API (Llama 3.x / Mixtral)

We chose **Groq** as our inference engine for one critical reason: **speed**.

Traditional AI APIs take 2–5 seconds to respond. Groq's LPU (Language Processing Unit) hardware delivers sub-second inference, making our chat interface feel instant and natural — not like waiting for a server.

```
Model:       Llama 3.3 / Mixtral 8x7B (via Groq API)
Endpoint:    https://api.groq.com/openai/v1/chat/completions
Max Tokens:  1536 (configurable via env: GROQ_MAX_TOKENS)
Temperature: 0.7 (balanced between creativity and factual grounding)
Stream:      true (Server-Sent Events for real-time typing effect)
```

### Why Not OpenAI / Gemini?
- **Speed**: Groq is 10–20x faster for student-facing UX
- **Cost**: Groq's free tier covers our entire hackathon
- **OpenAI-compatible API**: We can swap the model in one `.env` line without rewriting code

### Dual AI Personas (Same Model, Different Contexts)

| Persona | Who Uses It | Context Injected |
|---|---|---|
| **Buddy AI** | Authenticated university students | Schedule, tasks, announcements, profile |
| **Visitor AI** | Prospective students & public | DIU knowledge base, scholarship info, FAQs |

The same Groq endpoint powers both personas. The difference is entirely in our **RAG layer**.

---

## 3. 🔍 RAG — Retrieval-Augmented Generation

This is the **most important architectural decision** in Campus Buddy.

### What is RAG in Our System?

Before every message goes to Groq, our **`RAGService`** runs first. It:
1. Identifies the student making the request
2. Queries the MySQL database for their personal data
3. Builds a structured, rich system prompt with that data embedded
4. Sends the *entire enriched context* to the LLM

```
User sends message → RAGService builds context → Groq receives enriched prompt → Response
```

This means the LLM does not need to be trained on student data. It simply receives the data as structured text and reasons about it. This is **both safer and more accurate** than fine-tuning.

### What Data Does RAG Retrieve?

```php
// From RAGService.php — executed before every Buddy AI request

// Today's Schedule
DB::table('schedules')
    ->where('day', $today)          // Only today's day
    ->where('section', $user->section) // Only THIS student's section
    ->where('department', $user->department) // Only THIS student's department
    ->where('batch', $user->batch)  // Only THIS student's batch
    ->orderBy('time_slot')
    ->get(['course_title', 'course_code', 'time_slot', 'room_no', 'teacher_initial', 'type']);

// Pending Tasks (ordered by urgency)
DB::table('class_tasks')
    ->where('progress_status', '!=', 'completed')
    ->orderBy('deadline')   // Most urgent first
    ->limit(5)
    ->get(['title', 'deadline', 'type', 'course_code']);

// Recent Announcements (section-filtered)
DB::table('announcements')
    ->where('section', $user->section)
    ->orderByDesc('created_at')
    ->limit(3)
    ->get(['title', 'content']);
```

### The System Prompt Structure

The retrieved data is injected into a highly structured system prompt:

```
## STUDENT PROFILE
- Name: [Live DB value]
- Department: [Live DB value]
- Batch / Section: [Live DB value]

## TODAY'S SCHEDULE
- [Theory] Algorithm Design (CSE305): 8:00 AM - 9:30 AM | Room: 701 | Teacher: MHR
- [Lab] Database Lab (CSE303L): 10:00 AM - 12:30 PM | Room: Lab 2

## PENDING TASKS & DEADLINES
- [Quiz] Chapter 5 Quiz (CSE305) — Due: Tomorrow
- [Assignment] ER Diagram Submission (CSE303) — Due: In 3 Days

## RECENT ANNOUNCEMENTS
- **Mid-term Postponed**: Mid-term exams are rescheduled to next week...
```

**The LLM reads this block as its "ground truth".** It cannot hallucinate classes or deadlines because the prompt explicitly says: *"Do NOT make up class times. If data is not available, say so honestly."*

### Caching Strategy

RAG queries are expensive if run on every keystroke. We solve this with **10-minute per-user caching**:

```php
$context = Cache::remember("rag_ctx_{$user->id}", 600, fn() => $this->fetchStudentContext($user));
```

This means:
- First message: ~80ms DB query
- All subsequent messages within 10 minutes: ~0ms (from cache)
- After 10 minutes: automatically refreshes with latest data

---

## 4. 📊 Data — What We Use and How

Our AI is grounded in **6 live data sources** from our MySQL database:

| Table | Data | Used For |
|---|---|---|
| `users` | Student profile (dept, batch, section, major) | Context personalization |
| `schedules` | Weekly class timetable | Today's classes, free slots, study planning |
| `class_tasks` | Tasks with deadlines & types | Urgency detection, study advice |
| `announcements` | Section-specific notices | Real-time campus updates |
| `question_banks` | Past exam papers (approved by admin) | AI-powered exam practice |
| `materials` | PDF notes and hand-written scans | AI-powered summarization |
| `events` | University events calendar | Routine advisor context |

### Specificity as a Feature

The data filtering is deliberately hyper-specific. A student in:
- **Department**: CSE
- **Batch**: 62
- **Section**: B
- **Major**: (none)

...will *only* see tasks, schedules, and announcements for **CSE, Batch 62, Section B**. They never see data from other sections or departments. This specificity is what makes the AI responses feel accurate rather than generic.

---

## 5. 🎭 Personalization — Going Beyond "Hello [Name]"

True personalization means the AI's *reasoning changes* based on who is asking.

### Multi-Dimensional Student Context

We extract **5 dimensions of student identity** and use all of them:

```
Department → Filters which schedules/tasks are relevant
Batch      → Academic year context (junior/senior queries differ)
Section    → Hyper-specific class grouping
Major      → Sub-specialization filter (e.g., CS vs. IoT track)
Name       → Warm, personal communication
```

### Urgency-Aware Task Reasoning

Our `buildTaskTipPrompt()` function computes **deadline urgency mathematically** before calling the AI:

```php
$daysLeft = round(now()->diffInDays($deadline, false));

if ($daysLeft < 0)    $urgency = 'OVERDUE by ' . abs($daysLeft) . ' days';
elseif ($daysLeft == 0) $urgency = 'DUE TODAY — urgent';
elseif ($daysLeft <= 2) $urgency = "only {$daysLeft} day(s) left — approaching fast";
elseif ($daysLeft <= 7) $urgency = "{$daysLeft} days left — manageable";
else                  $urgency = "{$daysLeft} days left — plenty of time";
```

This computed urgency string is injected into the prompt. The AI doesn't calculate time — we do. We give the AI **pre-processed semantic context** so it focuses on advice, not arithmetic.

### Routine Advisor: Context Fusion

The most sophisticated personalization is the **Routine Advisor**. It fuses 4 data sources into a single reasoning context:

```
Full Weekly Schedule  +  Pending Tasks  +  Upcoming Events  →  Groq  →  Personalized Study Plan
```

The AI is instructed to *cross-reference* these sources:
> "If they have a quiz tomorrow, explicitly schedule study time for it today during a free slot."

This is true **multi-context reasoning** — the AI synthesizes schedule gaps, deadline urgency, and event conflicts to generate a study plan that a generic chatbot could never produce.

---

## 6. 🛠️ Tools — The AI's Extended Capabilities

Beyond conversation, our AI is connected to specialized tools that run pipelines of their own.

### Tool 1: PDF Text Extraction Pipeline

When a student uploads study notes (PDF), our AI doesn't just see a filename. It **reads the document**:

```
[PDF Upload]
    → smalot/pdfparser  → Extract raw text from PDF pages
    → Text injected into buildNotesSummaryPrompt()
    → Groq generates: Key Topics + Explanations + Practice Questions
```

**Result:** Students get a structured academic summary of their own notes instantly.

### Tool 2: Tesseract OCR Pipeline (Image Reading)

Question Bank papers are often **scanned images** (JPG/PNG), not searchable text. We built an OCR sub-system:

```
[Question Paper Image]
    → Tesseract OCR Engine  → Raw text extracted from image
    → Text cached permanently (never re-OCR the same image)
    → Injected into buildQuestionBankPrompt()
    → Groq generates: Practice questions, explanations, study strategies
```

The caching is permanent (`rememberForever`) because a scanned image never changes:

```php
$text = Cache::rememberForever('ocr_qbank_' . md5($fullPath), function() use ($fullPath) {
    $ocr = new TesseractOCR($fullPath);
    return $ocr->run(); // Run once, cache forever
});
```

### Tool 3: AI-Powered Auto-Fill (Admin Tool)

When an admin uploads a question paper image, our AI **automatically fills in all metadata fields** — course name, tags, difficulty, exam type (mid/final/quiz) — using the OCR pipeline + Groq:

```
Admin uploads image → OCR reads text → Groq extracts metadata → Admin reviews → One-click Approve
```

This eliminates hours of manual data entry and ensures the question bank grows rapidly with minimal admin effort.

### Tool 4: Daily Briefing Generator

Every student's dashboard greets them with an **AI-generated daily briefing** — a 3-5 sentence personalized summary of their day:

```
Context: Today's classes + upcoming deadlines + current time
Prompt constraint: Under 120 words, warm tone, 1-2 emojis
Output: "Good morning, Washim! You have Algorithm Design at 8AM 
         followed by DB Lab. Your Quiz 5 is due tomorrow — 
         try to revise Chapter 6 during your afternoon gap. You've got this! 🎯"
```

This is entirely AI-generated, but grounded in the student's real data — it is never a template.

---

## 7. 🏗️ Architecture Overview

```
┌─────────────────────────────────────────────────────────┐
│                     USER INTERFACE                      │
│  [Student Chat]  [Visitor Chat]  [Dashboard]  [Notes]   │
└──────────────────────────┬──────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────┐
│                   CONTEXT LAYER (RAG)                   │
│                                                         │
│  RAGService.php                                         │
│  ├── buildStudentSystemPrompt()  → Student AI           │
│  ├── buildVisitorSystemPrompt()  → Public AI            │
│  ├── buildRoutineAdvisorPrompt() → Study Planner        │
│  ├── buildNotesSummaryPrompt()   → PDF Summarizer       │
│  ├── buildQuestionBankPrompt()   → Exam Prep            │
│  ├── buildTaskTipPrompt()        → Task Advisor         │
│  └── buildBriefingPrompt()       → Daily Briefing       │
│                                                         │
│  [MySQL DB Query] → [Cache Layer] → [Prompt Builder]    │
└──────────────────────────┬──────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────┐
│                  PROCESSING TOOLS                       │
│  ├── smalot/pdfparser    → Extract text from PDFs       │
│  └── Tesseract OCR       → Extract text from images     │
└──────────────────────────┬──────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────┐
│                   GROQ API (LLM)                        │
│  Model: Llama 3.3 / Mixtral 8x7B                        │
│  Mode: Streaming (Server-Sent Events)                   │
│  Output: Markdown-formatted, student-aware response     │
└─────────────────────────────────────────────────────────┘
```

---

## 8. ✅ Design Decisions That Matter

| Decision | Alternative | Why We Chose This |
|---|---|---|
| RAG over Fine-tuning | Fine-tune a custom model | RAG is dynamic; fine-tuning bakes in stale data |
| Groq over OpenAI | OpenAI GPT-4o | 10x faster, free tier, same API standard |
| Streaming responses | Waiting for full response | UX feels natural and responsive |
| DB-level filtering (not AI filtering) | Ask AI to filter data | Precise, tamper-proof, computationally cheap |
| 10-minute context cache | No cache (re-query every message) | Balances freshness with DB load |
| Permanent OCR cache | Re-run OCR each time | Images never change; OCR is expensive |
| Sync queue for PDF processing | Background queue worker | Works without extra infrastructure on free hosting |

---

## 9. 🔒 Safety & Guardrails

Our AI is constrained at the prompt level to prevent misuse:

1. **No Hallucination on Schedules**: Prompt explicitly forbids inventing class times or deadlines
2. **No Personal Data Leaks**: Visitor AI has zero access to any student's personal data
3. **No System Exposure**: AI is forbidden from revealing API keys, DB structure, or internal logic
4. **Academic Focus**: Off-topic requests are gently redirected, not ignored
5. **Source Transparency**: When data is missing, AI directs students to official sources, not guesses

---

## 10. 🚀 What Makes This "Real AI Thinking"

| Capability | Generic Chatbot | Campus Buddy AI |
|---|---|---|
| "What class do I have now?" | ❌ Cannot answer | ✅ Queries your live schedule |
| "Is my quiz urgent?" | ❌ Does not know deadlines | ✅ Calculates days left, flags urgency |
| "Summarize my uploaded notes" | ❌ Cannot read files | ✅ OCR + PDF parsing pipeline |
| "Give me practice questions from past papers" | ❌ No real data | ✅ Injects actual DB question data |
| "Plan my study week" | ❌ Generic advice | ✅ Fuses schedule, tasks, events |
| Admits when it doesn't know | ❌ Hallucinates | ✅ Redirects to real sources |

---

*Built for the DIU Hackathon 2026 — Campus Buddy Team*
