/**
 * chat.js
 * ────────
 * WHY:    Provides the interactive chatbot experience in the browser.
 * WHAT:   - Renders welcome screen with quick-question shortcuts
 *         - Handles form submission and shows loading animation
 *         - Calls /api/chat via fetch() and renders the response
 *         - Displays source attribution links
 *         - Auto-resizes the textarea as the user types
 *         - Stores conversation history in memory for multi-turn chat
 * HOW:    Vanilla JS — no framework needed. Works on all modern browsers.
 */

"use strict";

// ── State ────────────────────────────────────────────────────────────────────
const history = [];   // [{role: "user"|"assistant", content: "..."}]
let isLoading = false;

// ── DOM refs ──────────────────────────────────────────────────────────────────
const chatBody  = document.getElementById("chatBody");
const chatForm  = document.getElementById("chatForm");
const userInput = document.getElementById("userInput");
const sendBtn   = document.getElementById("sendBtn");
const clearBtn  = document.getElementById("clearBtn");

// ── Quick questions for the welcome screen ────────────────────────────────────
const QUICK_QUESTIONS = [
  "Tuition fees for CSE?",
  "BBA admission requirements",
  "Scholarship eligibility?",
  "Campus facilities?",
  "Contact the admission office",
];

// ── Initialize ────────────────────────────────────────────────────────────────
function init() {
  renderWelcome();

  chatForm.addEventListener("submit", handleSubmit);
  clearBtn.addEventListener("click", clearChat);

  // Auto-resize textarea
  userInput.addEventListener("input", () => {
    userInput.style.height = "auto";
    userInput.style.height = Math.min(userInput.scrollHeight, 140) + "px";
  });

  // Send on Enter (Shift+Enter = new line)
  userInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter" && !e.shiftKey) {
      e.preventDefault();
      chatForm.requestSubmit();
    }
  });
}

// ── Welcome screen ────────────────────────────────────────────────────────────
function renderWelcome() {
  const card = document.createElement("div");
  card.className = "welcome-card";
  card.innerHTML = `
    <div class="emoji">🎓</div>
    <h2>Welcome to DIU Buddy</h2>
    <p>Ask me anything about Daffodil International University.<br/>
    I answer <strong>exclusively from official DIU sources</strong> — no guessing.</p>
    <div class="quick-questions">
      ${QUICK_QUESTIONS.map(q => `<button class="quick-btn" data-q="${q}">${q}</button>`).join("")}
    </div>
  `;

  // Quick question click handler
  card.querySelectorAll(".quick-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      if (isLoading) return;
      userInput.value = btn.dataset.q;
      chatForm.requestSubmit();
    });
  });

  chatBody.appendChild(card);
}

// ── Submit handler ────────────────────────────────────────────────────────────
async function handleSubmit(e) {
  e.preventDefault();
  const message = userInput.value.trim();
  if (!message || isLoading) return;

  // Clear input and reset height
  userInput.value = "";
  userInput.style.height = "auto";

  // Remove welcome card on first message
  const card = chatBody.querySelector(".welcome-card");
  if (card) card.remove();

  // Render user message
  appendMessage("user", message);
  history.push({ role: "user", content: message });

  // Show loading indicator
  const loadingEl = appendLoading();
  setLoading(true);

  try {
    const response = await fetch("/api/chat", {
      method:  "POST",
      headers: { "Content-Type": "application/json" },
      body:    JSON.stringify({ message, history: history.slice(-8) }),
    });

    if (!response.ok) {
      throw new Error(`Server error: ${response.status}`);
    }

    const data = await response.json();
    loadingEl.remove();

    // Render bot response
    appendMessage("bot", data.answer, data.sources);
    history.push({ role: "assistant", content: data.answer });

  } catch (err) {
    loadingEl.remove();
    appendMessage("bot",
      "⚠️ Sorry, I couldn't connect right now. Please try again or visit daffodilvarsity.edu.bd.",
      [], true
    );
    console.error("[DIU Buddy] Error:", err);
  } finally {
    setLoading(false);
  }
}

// ── Render a message bubble ───────────────────────────────────────────────────
function appendMessage(role, text, sources = [], isError = false) {
  const msg = document.createElement("div");
  msg.className = `message ${role}`;

  const avatar = document.createElement("div");
  avatar.className = "msg-avatar";
  avatar.textContent = role === "user" ? "👤" : "🎓";

  const bubble = document.createElement("div");
  bubble.className = `msg-bubble${isError ? " error" : ""}`;

  // Strip the LLM's inline '🔗 Verify at: <url>' line — the styled
  // verify-button section below already shows it more beautifully.
  const cleanText = text.replace(/\n*🔗\s*Verify at:\s*https?:\/\/\S+/gi, "").trim();
  bubble.innerHTML = formatText(cleanText);

  // Source attribution — styled "Verify Info" button section
  if (sources && sources.length > 0) {
    // Deduplicate by URL
    const seen = new Set();
    const uniqueSources = sources.filter(s => {
      if (!s.url || seen.has(s.url)) return false;
      seen.add(s.url);
      return true;
    });

    if (uniqueSources.length > 0) {
      const srcDiv = document.createElement("div");
      srcDiv.className = "sources-section";
      srcDiv.innerHTML = `
        <div class="sources-label">
          <span class="sources-icon">🔗</span>
          <span>Verify from official source${uniqueSources.length > 1 ? "s" : ""}</span>
        </div>
        <div class="sources-links">
          ${uniqueSources.map(s => `
            <a href="${s.url}" target="_blank" rel="noopener noreferrer" class="verify-btn">
              <span class="verify-btn-icon">↗</span>
              <span>${s.title}</span>
            </a>
          `).join("")}
        </div>
      `;
      bubble.appendChild(srcDiv);
    }
  }

  // Timestamp
  const time = document.createElement("div");
  time.className = "msg-time";
  time.textContent = new Date().toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
  bubble.appendChild(time);

  msg.appendChild(avatar);
  msg.appendChild(bubble);
  chatBody.appendChild(msg);
  scrollToBottom();
  return msg;
}

// ── Typing/loading indicator ──────────────────────────────────────────────────
function appendLoading() {
  const msg = document.createElement("div");
  msg.className = "message bot";
  msg.innerHTML = `
    <div class="msg-avatar">🎓</div>
    <div class="msg-bubble bot-bg">
      <div class="typing-indicator">
        <span></span><span></span><span></span>
      </div>
    </div>`;
  chatBody.appendChild(msg);
  scrollToBottom();
  return msg;
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function setLoading(state) {
  isLoading = state;
  sendBtn.disabled = state;
}

function scrollToBottom() {
  chatBody.scrollTop = chatBody.scrollHeight;
}

function clearChat() {
  chatBody.innerHTML = "";
  history.length = 0;
  renderWelcome();
}

/**
 * Convert plain text with basic markdown into HTML.
 * Handles: **bold**, bullet lists, numbered lists, newlines, bare URLs.
 * We do NOT use a full markdown parser to keep bundle size zero.
 */
function formatText(text) {
  if (!text) return "";

  // Step 1: Extract bare URLs BEFORE escaping so we can linkify them.
  // We temporarily replace them with placeholders.
  const urlPlaceholders = [];
  const urlRegex = /https?:\/\/[^\s<>"')]+/g;
  const textWithPlaceholders = text.replace(urlRegex, (url) => {
    try {
      const hostname = new URL(url).hostname.replace(/^www\./, "");
      const label = hostname; // e.g. "daffodilvarsity.edu.bd"
      const idx = urlPlaceholders.length;
      urlPlaceholders.push(
        `<a href="${url}" target="_blank" rel="noopener noreferrer" class="inline-link">${label}&nbsp;↗</a>`
      );
      return `\x00LINK${idx}\x00`;
    } catch {
      return url;
    }
  });

  // Step 2: Escape HTML, then apply markdown rules
  let html = textWithPlaceholders
    .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
    .replace(/\*\*(.+?)\*\*/g, "<strong>$1</strong>")
    .replace(/^[\-\*]\s+(.+)$/gm, "<li>$1</li>")
    .replace(/^\d+\.\s+(.+)$/gm, "<li>$1</li>")
    .replace(/(<li>.*<\/li>)/gs, (match) => `<ul>${match}</ul>`)
    .replace(/\n{2,}/g, "</p><p>")
    .replace(/\n/g, "<br>")
    .replace(/^(.+)$/, "<p>$1</p>");

  // Step 3: Restore link placeholders (they must NOT be HTML-escaped)
  html = html.replace(/\x00LINK(\d+)\x00/g, (_, idx) => urlPlaceholders[+idx]);

  return html;
}

// ── Boot ──────────────────────────────────────────────────────────────────────
init();
