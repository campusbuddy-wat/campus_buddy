<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DocsSectionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('docs_sections')->delete();
        
        \DB::table('docs_sections')->insert(array (
            0 => 
            array (
                'id' => 1,
                'slug' => 'problem',
                'title' => 'The Problem',
                'icon' => 'fa-triangle-exclamation',
                'content' => '<div class="docs-problem-grid">
<div class="problem-item"><div class="problem-icon"><i class="fas fa-clock"></i></div><h4>Information Overload</h4><p>Students waste valuable time searching for lecture notes, PDFs, or exam questions buried in endless chat histories across WhatsApp, Telegram, and email threads.</p></div>
<div class="problem-item"><div class="problem-icon"><i class="fas fa-calendar-xmark"></i></div><h4>Time Management Struggles</h4><p>Constantly changing class routines and unorganized task tracking lead to missed deadlines and mounting academic stress.</p></div>
<div class="problem-item"><div class="problem-icon"><i class="fas fa-brain"></i></div><h4>Lack of Personalized Learning</h4><p>Students lack accessible, intelligent tools to quickly summarize heavy course materials or generate practice questions tailored to their syllabi.</p></div>
<div class="problem-item"><div class="problem-icon"><i class="fas fa-users-slash"></i></div><h4>Disconnected Campus Community</h4><p>No centralized, professional space exists for students to seek mentorship from seniors, connect with alumni, or engage in academic discussions.</p></div>
</div>',
                'sort_order' => 1,
                'is_visible' => 1,
                'created_at' => '2026-05-12 12:30:34',
                'updated_at' => '2026-05-12 12:30:34',
            ),
            1 => 
            array (
                'id' => 2,
                'slug' => 'solution',
                'title' => 'Our Solution',
                'icon' => 'fa-lightbulb',
                'content' => '<div class="solution-overview">
<p class="solution-lead">Campus Buddy functions as a centralized, role-aware web application that consolidates the entire university workflow into a single intelligent dashboard.</p>
<div class="solution-features">
<div class="solution-feature"><div class="feature-number">01</div><h4>Academic Organization Engine</h4><p>Dynamic, database-driven class routines with real-time tracking of current day and time. Integrated personal task manager maps assignments and exams against daily schedules.</p></div>
<div class="solution-feature"><div class="feature-number">02</div><h4>AI-Powered Resource Hub</h4><p>Centralized repository for PDF notes and past exam questions with AI-driven instant summaries and automatic practice question generation from uploaded syllabi.</p></div>
<div class="solution-feature"><div class="feature-number">03</div><h4>Campus Community Portal</h4><p>Dedicated social forum with posts, comments, and discussions. Alumni directory allows verified graduates to manage profiles and offer mentorship.</p></div>
<div class="solution-feature"><div class="feature-number">04</div><h4>Role-Based Personalization</h4><p>Strict RBAC system managed via Filament admin panel. Personalized dashboard data, AI interactions, and routine visibility for every user type.</p></div>
</div>
</div>',
                'sort_order' => 2,
                'is_visible' => 1,
                'created_at' => '2026-05-12 12:30:34',
                'updated_at' => '2026-05-12 12:30:34',
            ),
            2 => 
            array (
                'id' => 3,
                'slug' => 'why-now',
                'title' => 'Why Now',
                'icon' => 'fa-rocket',
                'content' => '<div class="why-now-content">
<div class="why-now-item"><div class="why-icon"><i class="fas fa-microchip"></i></div><h4>AI Maturity</h4><p>LLMs are now capable of understanding academic content at a level that enables meaningful note summarization and intelligent question generation — capabilities that were impossible just 2 years ago.</p></div>
<div class="why-now-item"><div class="why-icon"><i class="fas fa-graduation-cap"></i></div><h4>Post-Pandemic Digital Shift</h4><p>Universities have permanently shifted to hybrid models. Students now expect digital-first tools for their academic workflow, not paper-based systems.</p></div>
<div class="why-now-item"><div class="why-icon"><i class="fas fa-mobile-screen-button"></i></div><h4>Mobile-First Generation</h4><p>Today\'s university students are digital natives who demand seamless, app-like experiences. Fragmented solutions are no longer acceptable.</p></div>
</div>',
                'sort_order' => 3,
                'is_visible' => 1,
                'created_at' => '2026-05-12 12:30:34',
                'updated_at' => '2026-05-12 12:30:34',
            ),
            3 => 
            array (
                'id' => 4,
                'slug' => 'product',
                'title' => 'Product Overview',
                'icon' => 'fa-laptop-code',
                'content' => '<div class="product-overview">
<div class="product-module"><span class="module-badge">Core</span><h4>📅 Dynamic Routines</h4><p>Real-time class schedule management with auto-highlighting of current classes and day-specific filtering.</p></div>
<div class="product-module"><span class="module-badge">Core</span><h4>📝 Class Tasks</h4><p>Assignment, quiz, and presentation tracking with deadlines, status indicators, and completion workflows.</p></div>
<div class="product-module"><span class="module-badge">Core</span><h4>📄 Notes & PDF Hub</h4><p>Department-specific repository with structured browsing, upload capabilities, and organized resource management.</p></div>
<div class="product-module"><span class="module-badge">AI</span><h4>🤖 Buddy AI Chat</h4><p>Intelligent study assistant providing real-time academic guidance, note summarization, and personalized learning support.</p></div>
<div class="product-module"><span class="module-badge">AI</span><h4>❓ Question Bank</h4><p>AI-powered automatic question generation from uploaded syllabi and exam paper archive with multi-file support.</p></div>
<div class="product-module"><span class="module-badge">Social</span><h4>💬 Community Forum</h4><p>Social interaction hub with posts, comments, replies, likes, and threaded discussions for campus engagement.</p></div>
<div class="product-module"><span class="module-badge">Social</span><h4>🎓 Alumni Network</h4><p>Verified alumni directory with profile management, industry filtering, and mentorship connectivity.</p></div>
<div class="product-module"><span class="module-badge">Social</span><h4>🌟 Talent Showcase</h4><p>Skill-based collaboration platform where students can showcase their talents and connect professionally.</p></div>
</div>',
                'sort_order' => 4,
                'is_visible' => 1,
                'created_at' => '2026-05-12 12:30:34',
                'updated_at' => '2026-05-12 12:30:34',
            ),
            4 => 
            array (
                'id' => 5,
                'slug' => 'traction',
                'title' => 'Traction & Metrics',
                'icon' => 'fa-chart-line',
                'content' => '<div class="traction-note"><p>📊 <strong>Live Data</strong> — These metrics are pulled directly from the production database in real-time.</p></div>
<div class="metrics-grid" id="live-metrics">
<!-- Metrics are injected dynamically via the controller -->
</div>',
                'sort_order' => 5,
                'is_visible' => 1,
                'created_at' => '2026-05-12 12:30:34',
                'updated_at' => '2026-05-12 12:30:34',
            ),
            5 => 
            array (
                'id' => 6,
                'slug' => 'competition',
                'title' => 'Competitive Landscape',
                'icon' => 'fa-chess',
                'content' => '<div class="competition-table-wrap">
<table class="competition-table">
<thead><tr><th>Feature</th><th>Campus Buddy</th><th>Google Classroom</th><th>WhatsApp Groups</th><th>Moodle</th></tr></thead>
<tbody>
<tr><td>AI Note Summarization</td><td class="check">✓</td><td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td></tr>
<tr><td>Auto Question Generation</td><td class="check">✓</td><td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td></tr>
<tr><td>Dynamic Class Routines</td><td class="check">✓</td><td class="partial">~</td><td class="cross">✗</td><td class="partial">~</td></tr>
<tr><td>Community & Social Forum</td><td class="check">✓</td><td class="cross">✗</td><td class="partial">~</td><td class="partial">~</td></tr>
<tr><td>Alumni Network</td><td class="check">✓</td><td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td></tr>
<tr><td>Talent Showcase</td><td class="check">✓</td><td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td></tr>
<tr><td>Role-Based Access (RBAC)</td><td class="check">✓</td><td class="check">✓</td><td class="cross">✗</td><td class="check">✓</td></tr>
<tr><td>Personalized AI for Each Role</td><td class="check">✓</td><td class="cross">✗</td><td class="cross">✗</td><td class="cross">✗</td></tr>
</tbody>
</table>
</div>',
                'sort_order' => 6,
                'is_visible' => 1,
                'created_at' => '2026-05-12 12:30:34',
                'updated_at' => '2026-05-12 12:30:34',
            ),
            6 => 
            array (
                'id' => 7,
                'slug' => 'unique-advantage',
                'title' => 'Unique Advantage',
                'icon' => 'fa-trophy',
                'content' => '<div class="advantage-content">
<div class="advantage-main"><h3>The All-in-One Academic Operating System</h3><p>While competitors focus on one aspect — scheduling OR materials OR community — Campus Buddy is the <strong>only platform</strong> that unifies all three pillars with AI intelligence layered on top.</p></div>
<div class="advantage-points">
<div class="advantage-point"><span class="adv-num">1</span><h4>Data Network Effect</h4><p>Every uploaded PDF, every question generated, and every community interaction enriches the AI, making it smarter and more personalized over time.</p></div>
<div class="advantage-point"><span class="adv-num">2</span><h4>University-Native Design</h4><p>Built from the ground up for university workflows (semesters, sections, batches, departments) — not retrofitted from generic project management tools.</p></div>
<div class="advantage-point"><span class="adv-num">3</span><h4>Zero Migration Cost</h4><p>Students can start using Campus Buddy alongside their existing tools and gradually consolidate, eliminating switching cost barriers.</p></div>
</div>
</div>',
                'sort_order' => 7,
                'is_visible' => 1,
                'created_at' => '2026-05-12 12:30:34',
                'updated_at' => '2026-05-12 12:30:34',
            ),
            7 => 
            array (
                'id' => 8,
                'slug' => 'architecture',
                'title' => 'System Architecture',
                'icon' => 'fa-sitemap',
                'content' => '<div class="arch-diagram">
<div class="arch-layer arch-frontend"><div class="layer-label">Frontend Layer</div><div class="arch-boxes"><div class="arch-box">Blade Templates</div><div class="arch-box">Vanilla CSS3</div><div class="arch-box">JavaScript ES6+</div><div class="arch-box">GSAP Animations</div></div></div>
<div class="arch-arrow"><i class="fas fa-arrow-down"></i></div>
<div class="arch-layer arch-backend"><div class="layer-label">Backend Layer</div><div class="arch-boxes"><div class="arch-box">Laravel 12 (MVC)</div><div class="arch-box">Eloquent ORM</div><div class="arch-box">Filament Admin</div><div class="arch-box">Laravel Queues</div></div></div>
<div class="arch-arrow"><i class="fas fa-arrow-down"></i></div>
<div class="arch-layer arch-ai"><div class="layer-label">AI / ML Layer</div><div class="arch-boxes"><div class="arch-box">LLM API (Gemini/OpenAI)</div><div class="arch-box">RAG Pipeline</div><div class="arch-box">PDF Parser</div><div class="arch-box">Ollama (Llama 3)</div></div></div>
<div class="arch-arrow"><i class="fas fa-arrow-down"></i></div>
<div class="arch-layer arch-data"><div class="layer-label">Data Layer</div><div class="arch-boxes"><div class="arch-box">MySQL (InnoDB)</div><div class="arch-box">Vector DB</div><div class="arch-box">Object Storage</div><div class="arch-box">Redis Cache</div></div></div>
</div>',
                'sort_order' => 8,
                'is_visible' => 1,
                'created_at' => '2026-05-12 12:30:34',
                'updated_at' => '2026-05-12 12:30:34',
            ),
            8 => 
            array (
                'id' => 9,
                'slug' => 'tech-stack',
                'title' => 'Technology Stack',
                'icon' => 'fa-layer-group',
                'content' => '<div class="stack-grid">
<div class="stack-category"><h4><i class="fas fa-server"></i> Backend</h4><div class="stack-items"><span class="stack-chip">PHP 8.2</span><span class="stack-chip">Laravel 12</span><span class="stack-chip">Eloquent ORM</span><span class="stack-chip">Sanctum Auth</span></div></div>
<div class="stack-category"><h4><i class="fas fa-palette"></i> Frontend</h4><div class="stack-items"><span class="stack-chip">Blade Templates</span><span class="stack-chip">Vanilla CSS3</span><span class="stack-chip">JavaScript ES6+</span><span class="stack-chip">GSAP</span><span class="stack-chip">Vite</span></div></div>
<div class="stack-category"><h4><i class="fas fa-database"></i> Database</h4><div class="stack-items"><span class="stack-chip">MySQL (InnoDB)</span><span class="stack-chip">Vector DB</span><span class="stack-chip">Redis</span></div></div>
<div class="stack-category"><h4><i class="fas fa-robot"></i> AI / ML</h4><div class="stack-items"><span class="stack-chip">Gemini API</span><span class="stack-chip">OpenAI API</span><span class="stack-chip">Ollama + Llama 3</span><span class="stack-chip">RAG Pipeline</span></div></div>
<div class="stack-category"><h4><i class="fas fa-tools"></i> Admin & DevOps</h4><div class="stack-items"><span class="stack-chip">Filament v3</span><span class="stack-chip">Chart.js</span><span class="stack-chip">Git</span><span class="stack-chip">Composer</span></div></div>
</div>',
                'sort_order' => 9,
                'is_visible' => 1,
                'created_at' => '2026-05-12 12:30:34',
                'updated_at' => '2026-05-12 12:30:34',
            ),
            9 => 
            array (
                'id' => 10,
                'slug' => 'ai-layer',
                'title' => 'AI & Intelligence Layer',
                'icon' => 'fa-brain',
                'content' => '<div class="ai-content">
<div class="ai-flow">
<div class="ai-step"><div class="step-icon"><i class="fas fa-file-pdf"></i></div><h4>1. Document Ingestion</h4><p>PDFs and syllabi uploaded by students are parsed using PHP-based text extraction libraries.</p></div>
<div class="ai-connector"><i class="fas fa-arrow-right"></i></div>
<div class="ai-step"><div class="step-icon"><i class="fas fa-scissors"></i></div><h4>2. Semantic Chunking</h4><p>Extracted text is split into 512-1024 token chunks, preserving academic context and paragraph boundaries.</p></div>
<div class="ai-connector"><i class="fas fa-arrow-right"></i></div>
<div class="ai-step"><div class="step-icon"><i class="fas fa-database"></i></div><h4>3. Embedding & Storage</h4><p>Chunks are embedded using text-embedding models and indexed in a Vector Database for fast retrieval.</p></div>
<div class="ai-connector"><i class="fas fa-arrow-right"></i></div>
<div class="ai-step"><div class="step-icon"><i class="fas fa-magnifying-glass"></i></div><h4>4. RAG Retrieval</h4><p>When a student asks a question, top-K relevant chunks are retrieved and passed as context to the LLM.</p></div>
<div class="ai-connector"><i class="fas fa-arrow-right"></i></div>
<div class="ai-step"><div class="step-icon"><i class="fas fa-wand-magic-sparkles"></i></div><h4>5. AI Generation</h4><p>The LLM generates accurate summaries, practice questions, and personalized academic insights.</p></div>
</div>
<div class="ai-models"><h4>Models Used</h4><div class="model-chips"><span class="model-chip cloud">Gemini 1.5 Flash <small>Cloud</small></span><span class="model-chip cloud">GPT-4o-mini <small>Cloud</small></span><span class="model-chip local">Llama 3 8B <small>Local via Ollama</small></span></div></div>
</div>',
                'sort_order' => 10,
                'is_visible' => 1,
                'created_at' => '2026-05-12 12:30:34',
                'updated_at' => '2026-05-12 12:30:34',
            ),
            10 => 
            array (
                'id' => 11,
                'slug' => 'roadmap',
                'title' => 'Product Roadmap',
                'icon' => 'fa-road',
                'content' => '<div class="roadmap-timeline">
<div class="roadmap-phase completed"><div class="phase-marker"></div><div class="phase-content"><span class="phase-label">Completed</span><h4>Phase 1 — Foundation</h4><ul><li>Core MVC architecture with Laravel 12</li><li>User authentication & RBAC</li><li>Dashboard, Routine, and Task management</li><li>Community forum with posts & comments</li><li>Alumni networking directory</li><li>Filament admin panel</li></ul></div></div>
<div class="roadmap-phase current"><div class="phase-marker"></div><div class="phase-content"><span class="phase-label">In Progress</span><h4>Phase 2 — AI Integration</h4><ul><li>Buddy AI Chat assistant</li><li>PDF note summarization via LLM</li><li>Automatic question generation from syllabi</li><li>RAG pipeline for accurate academic answers</li><li>Local Llama 3 integration via Ollama</li></ul></div></div>
<div class="roadmap-phase upcoming"><div class="phase-marker"></div><div class="phase-content"><span class="phase-label">Q3 2026</span><h4>Phase 3 — Personalization</h4><ul><li>Custom AI dataset training</li><li>Personalized routine recommendations</li><li>Smart notification system</li><li>Mobile app (React Native)</li></ul></div></div>
<div class="roadmap-phase future"><div class="phase-marker"></div><div class="phase-content"><span class="phase-label">Q4 2026+</span><h4>Phase 4 — Scale</h4><ul><li>Multi-university support</li><li>Marketplace for study materials</li><li>AI-powered career counseling</li><li>API platform for third-party integrations</li></ul></div></div>
</div>',
                'sort_order' => 11,
                'is_visible' => 1,
                'created_at' => '2026-05-12 12:30:34',
                'updated_at' => '2026-05-12 12:30:34',
            ),
            11 => 
            array (
                'id' => 12,
                'slug' => 'security',
                'title' => 'Security & Privacy',
                'icon' => 'fa-shield-halved',
                'content' => '<div class="security-grid">
<div class="security-item"><i class="fas fa-lock"></i><h4>Authentication</h4><p>Laravel Sanctum session-based authentication with CSRF protection and rate-limited login attempts.</p></div>
<div class="security-item"><i class="fas fa-user-shield"></i><h4>Role-Based Access Control</h4><p>Strict RBAC via Laravel Policies and Gates. Students, Alumni, Admins, and Guests each have tailored permissions.</p></div>
<div class="security-item"><i class="fas fa-key"></i><h4>Data Encryption</h4><p>All passwords are hashed using bcrypt. Sensitive environment variables are stored securely in .env files.</p></div>
<div class="security-item"><i class="fas fa-eye-slash"></i><h4>AI Data Privacy</h4><p>RAG pipeline ensures AI only accesses documents the user\'s role permits. No cross-cohort data leakage.</p></div>
</div>',
                'sort_order' => 12,
                'is_visible' => 1,
                'created_at' => '2026-05-12 12:30:34',
                'updated_at' => '2026-05-12 12:30:34',
            ),
            12 => 
            array (
                'id' => 13,
                'slug' => 'changelog',
                'title' => 'Changelog',
                'icon' => 'fa-clock-rotate-left',
                'content' => '<div class="changelog-list">
<div class="changelog-entry"><div class="version">v2.0</div><div class="changelog-details"><span class="changelog-date">May 2026</span><h4>AI Integration & Documentation</h4><ul><li>Buddy AI Chat integration</li><li>AI-powered PDF summarization</li><li>Automatic question generation</li><li>Live /docs documentation system</li><li>RAG pipeline implementation</li></ul></div></div>
<div class="changelog-entry"><div class="version">v1.5</div><div class="changelog-details"><span class="changelog-date">April 2026</span><h4>Community & Alumni</h4><ul><li>Community forum with threaded comments</li><li>Alumni registration and networking</li><li>Talent showcase module</li><li>District associations</li><li>Mobile responsiveness overhaul</li></ul></div></div>
<div class="changelog-entry"><div class="version">v1.0</div><div class="changelog-details"><span class="changelog-date">March 2026</span><h4>Foundation Release</h4><ul><li>Core Laravel architecture</li><li>User authentication & RBAC</li><li>Dashboard with live metrics</li><li>Dynamic class routines</li><li>Class task management</li><li>Notes & PDF repository</li><li>Filament admin panel</li></ul></div></div>
</div>',
                'sort_order' => 13,
                'is_visible' => 1,
                'created_at' => '2026-05-12 12:30:34',
                'updated_at' => '2026-05-12 12:30:34',
            ),
        ));
        
        
    }
}