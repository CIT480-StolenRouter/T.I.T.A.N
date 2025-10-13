<?php //START OF PHP
declare(strict_types=1);
session_start();
$error = null;

if(isset($_POST["logout"])) {
  session_destroy();
  header("Location: index.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && 
    isset($_POST['login'])) {

    // Pull inputs (no storing raw password in session) also-- ?? '' checks if it exists and NOT empty
    $email    = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        http_response_code(400);
        require_once __DIR__ . '/config/errorcode.php';
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        require_once __DIR__ . '/config/errorcode.php';
        exit;
    }

    // DB connection
    require_once __DIR__ . '/config/db.php'; // always define $pdo

    // Ensure PDO throws exceptions (in case db.php didnt)
    if ($pdo instanceof PDO) {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    try {
        // Fetch user by email column
        $stmt = $pdo->prepare(
            'SELECT emp_id, emp_email, emp_passwordhash, role
             FROM empusers
             WHERE emp_email = :email
             LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['emp_passwordhash'])) {

            // Opportunistic rehash
            if (password_needs_rehash($user['emp_passwordhash'], PASSWORD_DEFAULT)) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $upd = $pdo->prepare(
                    'UPDATE empusers SET emp_passwordhash = :h WHERE emp_id = :emp_id'
                );
                $upd->execute([':h' => $newHash, ':emp_id' => (int)$user['emp_id']]);
            }

            // Session Variables
            session_regenerate_id(true);
            $_SESSION['emp_id']    = (int)$user['emp_id'];
            $_SESSION['emp_email'] = $user['emp_email'];
            $_SESSION['role']      = $user['role'];

            // Role redirect (ADD THESE LATER, NOT IMPLEMENTED)
            $role = strtolower(trim((string)$user['role']));
            switch ($role) {
                case 'admin':
                    $dest = 'admin.php';
                    break;
                case 'employee':
                    $dest = 'home.php';
                    break;
                case 'user':
                default:
                    $dest = 'index.php';
                    break;
            }

            header('Location: ' . $dest);
            exit;
        }

        http_response_code(401);
        exit('Invalid email or password');

    } catch (Throwable $e) {
        error_log('Login error: ' . $e->getMessage());
        http_response_code(500);
        exit('Server error');
    }
}

?> <!-- END OF PHP -->
<!DOCTYPE html> <!-- START OF HTML -->
<html lang = "eng"> 
    <head>
        <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>T.I.T.A.N – Main Page</title>
  
    <link rel="stylesheet" href="CSS/websiteStyle.css">
    </head>
    <!-- AI Chatbox -->
<button id="ai-chat-toggle" aria-label="Open AI chat">💬</button>

<div id="ai-chat" aria-live="polite" aria-label="AI chat dialog">
  <header>
    <strong>AI Assistant</strong>
    <button id="ai-chat-close" aria-label="Close">✕</button>
  </header>

  <div id="ai-chat-messages">
    <div class="msg bot">Hi! Ask me anything about tickets, SLAs, or workflows.</div>
  </div>

  <form id="ai-chat-form" autocomplete="off">
    <input id="ai-chat-input" type="text" placeholder="Type your question…" />
    <button type="submit">Send</button>
  </form>
</div>

    <body> 
       <header>
        <div class="container nav" >
            <div class="brand">
                <div class = "brand-badge" aria-hidden="true"></div>
                <strong>T.I.T.A.N</strong>
            </div>
            <nav class=" menu" aria-labels="Main">
                
                    <a href="#overview">Overview</a>
                    <a href="#navigation">Navigation</a>
                    <a href="#products">Product</a>
                    <a href="#ai">AI</a>
                    <a href="#service-center">Service Center</a>
                    <a href="#live-chat">Live Chat</a>
                    <a href="#resources">Resources</a>

            </nav>
            <form action="index.php" method="post">
              <input type="submit" name="logout" value="logout">
            </form>
            <div>
              <?php echo $_SESSION['emp_email']; ?>
            </div>
            <div class="actions">
               <button class="btn btn-primary" onclick="scrollToId('login')">Login</button>
               <button class="btn btn-ghost" onclick="scrollToId('login')">Sign Up</button>
            </div>
    </div>
    </header>
   <section id="overview" class="hero">
    <div class="container grid grid-2" style="align-items:center">
      <div>
        <h1>Resolve faster. <span style="color:var(--light-purple)">T.I.T.A.N.</span></h1>
        <p class="lead">An all‑in‑one ticketing platform with AI, workforce management, and real‑time insights built to scale your service operations.</p>
        <div style="margin-top:1rem;display:flex;gap:.5rem">
          <button class="btn btn-primary" onclick="scrollToId('products')">Get Started</button>
          <button class="btn btn-outline">View Demo</button>
        </div>
        <form onsubmit="event.preventDefault()" style="margin-top:1rem;display:flex;gap:.5rem;max-width:420px">
          <input aria-label="Work email" placeholder="Work email" style="flex:1;border-radius:999px;border:none;padding:.7rem .9rem" />
          <button class="btn" style="background:var(--dark-purple);color:var(--whiteII)">Notify Me</button>
        </form>
      </div>
      <div>
        <div class="feature-grid">
          <div class="card"><h3>Ticketing</h3><p class="muted">Unified omni‑channel tickets with SLAs, priorities, and custom forms.</p></div>
          <div class="card"><h3>Automation</h3><p class="muted">No‑code rules, escalations, and event‑driven workflows.</p></div>
          <div class="card"><h3>AI Integration</h3><p class="muted">Summaries, suggested replies, and intelligent routing.</p></div>
          <div class="card"><h3>Insight</h3><p class="muted">Dashboards, CSAT, first‑response, and resolution analytics.</p></div>
        </div>
      </div>
    </div>
  </section>

  <!-- NAVIGATION PAGE (MAIN TABS) -->
  <section id="navigation" class="container" style="padding:2rem 0">
    <h2 style="margin:0 0 .25rem 0">Main Navigation</h2>
    <p class="muted" style="margin:0 0 1rem 0">Quick access to core areas.</p>

    <div class="tabs" id="tabs">
      <div class="tab-list" role="tablist">
        <button class="tab-button" role="tab" aria-selected="true" data-panel="tab-overview">Overview</button>
        <button class="tab-button" role="tab" data-panel="tab-login">Login / Sign Up</button>
        <button class="tab-button" role="tab" data-panel="tab-resources">Resources</button>
        <button class="tab-button" role="tab" data-panel="tab-ai">AI</button>
        <button class="tab-button" role="tab" data-panel="tab-service">Service Center</button>
        <button class="tab-button" role="tab" data-panel="tab-live">Live Chat</button>
      </div>

      <div id="tab-overview" class="tab-panel active">
        <div class="grid" style="grid-template-columns:repeat(3,1fr);gap:1rem">
          <div class="card"><h3>End‑User Inbox</h3><p class="muted">Branded portal for customers to view and update requests.</p></div>
          <div class="card"><h3>Workforce Management</h3><p class="muted">Forecasting, queues, schedules, and performance.</p></div>
          <div class="card"><h3>Collaboration</h3><p class="muted">Private notes, @mentions, swarming.</p></div>
          <div class="card"><h3>Workflow Automation</h3><p class="muted">Drag‑and‑drop flows and integrations.</p></div>
          <div class="card"><h3>Ticketing</h3><p class="muted">Multi‑channel intake, SLAs, macros.</p></div>
          <div class="card"><h3>Insight</h3><p class="muted">Team and customer analytics.</p></div>
        </div>
      </div>

      <div id="tab-login" class="tab-panel">
        <div class="card" id="login">
          <h3>Login / Sign Up</h3>
          <div class="grid" style="grid-template-columns:1fr 1fr;gap:1rem">
            <form action="index.php" method="post"> <!--onsubmit="event.preventDefault()"-->
              <label>Email<br /><input type="text" name="email" required placeholder="Email" style="width:100%;border:none;border-radius:12px;padding:.7rem"/></label><br /><br />
              <label>Password<br /><input type="password" name="password" required placeholder="Password" style="width:100%;border:none;border-radius:12px;padding:.7rem"/></label><br /><br />
              <input type="submit" name="login" value="login" class="btn btn-primary">
            </form>
            <form onsubmit="event.preventDefault()">
              <label>Work email<br /><input type="email" placeholder="Work email" style="width:100%;border:none;border-radius:12px;padding:.7rem"/></label><br /><br />
              <button class="btn btn-ghost">Sign Up</button>
            </form>
          </div>
        </div>
      </div>

      <div id="tab-resources" class="tab-panel">
        <div class="grid" id="resources" style="grid-template-columns:repeat(3,1fr);gap:1rem">
          <div class="card"><h3>Docs</h3><p class="muted">Everything you need to build and run support at scale.</p></div>
          <div class="card"><h3>API</h3><p class="muted">REST & webhooks for automation.</p></div>
          <div class="card"><h3>Release Notes</h3><p class="muted">What’s new and improved.</p></div>
          <div class="card"><h3>Tutorials</h3><p class="muted">Guided tours and recipes.</p></div>
          <div class="card"><h3>Status</h3><p class="muted">System health and uptime.</p></div>
          <div class="card"><h3>Community</h3><p class="muted">Best practices and forums.</p></div>
        </div>
      </div>

      <div id="tab-ai" class="tab-panel">
        <div class="card" id="ai">
          <h3>AI Integration</h3>
          <ul class="muted">
            <li>Reply Suggestions</li>
            <li>Summaries</li>
            <li>Intent & Routing</li>
          </ul>
        </div>
      </div>

      <div id="tab-service" class="tab-panel">
        <div class="card" id="service-center">
          <h3>Service Center</h3>
          <p class="muted">Central place for end‑users to submit, track, and update requests.</p>
        </div>
      </div>

      <div id="tab-live" class="tab-panel">
        <div class="card" id="live-chat">
          <h3>Live Chat</h3>
          <p class="muted">Real‑time support widget with routing, presence, and conversation history.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- PRODUCT TAB (cards) -->
  <section id="products" class="container" style="padding:2rem 0">
    <h2 style="margin:0 0 .25rem 0">Product</h2>
    <p class="muted" style="margin:0 0 1rem 0">Explore the platform modules.</p>
    <div class="product-grid">
      <article class="product"><h3>Ticketing</h3><p>Multi‑channel intake, SLAs, and macros.</p></article>
      <article class="product"><h3>Workforce Management</h3><p>Staffing, skills, and adherence.</p></article>
      <article class="product"><h3>Collaboration</h3><p>Private notes, @mentions, and swarming.</p></article>
      <article class="product"><h3>Workflow Automation</h3><p>Drag‑and‑drop flows and integrations.</p></article>
    </div>
  </section>

  <!-- INSIGHT -->
  <section id="insight" class="container" style="padding:2rem 0">
    <div class="card">
      <h3 style="display:flex;align-items:center;gap:.5rem">Insights Dashboard</h3>
      <div class="metrics" style="margin-top:.5rem">
        <div class="metric"><div class="k">First Response</div><div class="v">&lt; </div></div>
        <div class="metric"><div class="k">Resolution</div><div class="v"></div></div>
        <div class="metric"><div class="k">CSAT</div><div class="v"></div></div>
      </div>
    </div>
  </section>
  <!-- SERVICE TAB (detailed sections) -->
  <section id="enduser-inbox" class="container" style="padding:2rem 0">
    <h2>End‑User Inbox</h2>
    <p class="muted">A branded portal for customers to view and update requests.</p>
  </section>
  <section id="workforce-management" class="container" style="padding:2rem 0">
    <h2>Workforce Management</h2>
    <p class="muted">Forecasting, queues, schedules, and agent performance.</p>
  </section>
  <section id="ticketing" class="container" style="padding:2rem 0">
    <h2>Ticketing</h2>
    <p class="muted">Unified omni‑channel ticketing with SLAs and custom workflows.</p>
  </section>
  <section id="automation" class="container" style="padding:2rem 0">
    <h2>Automation</h2>
    <p class="muted">Trigger‑based rules, escalations, and workflow orchestration.</p>
  </section>

  <!-- FOOTER / RESOURCE TAB -->
  <footer>
    <div class="container foot-grid">
      <div class="foot">
        <h4>Resources</h4>
        <ul class="muted">
          <li>Documentation</li>
          <li>API Reference</li>
          <li>Guides</li>
          <li>Status</li>
        </ul>
      </div>
      <div class="foot">
        <h4>Service</h4>
        <ul class="muted">
          <li>Service Center</li>
          <li>End‑User Inbox</li>
          <li>Live Chat</li>
        </ul>
      </div>
      <div class="foot">
        <h4>Product</h4>
        <ul class="muted">
          <li>Ticketing</li>
          <li>Workforce Management</li>
          <li>Collaboration</li>
          <li>Workflow Automation</li>
        </ul>
      </div>
      <div class="foot">
        <h4>Stay in the loop</h4>
        <form onsubmit="event.preventDefault()" style="display:flex;gap:.5rem">
          <input placeholder="Email" style="flex:1;border:none;border-radius:999px;padding:.6rem .9rem" />
          <button class="btn btn-primary">Subscribe</button>
        </form>
      </div>
    </div>
    <div class="container" style="margin-top:1.25rem;display:flex;justify-content:space-between;color:var(--greyish-white);font-size:.85rem">
      <span>© <span id="year"></span> TicketWorks</span>
      <span>Privacy · Terms</span>
    </div>
  </footer>

  <script>
    // Year in footer
    document.getElementById('year').textContent = new Date().getFullYear();

    // Smooth scroll helper
    function scrollToId(id){
      const el = document.getElementById(id);
      if(el){ el.scrollIntoView({behavior:'smooth', block:'start'}); }
    }

    // Tabs behavior
    const tabList = document.querySelector('.tab-list');
    const buttons = tabList.querySelectorAll('.tab-button');
    const panels = document.querySelectorAll('.tab-panel');

    tabList.addEventListener('click', (e)=>{
      const btn = e.target.closest('.tab-button');
      if(!btn) return;
      const id = btn.getAttribute('data-panel');
      buttons.forEach(b=>b.setAttribute('aria-selected','false'));
      btn.setAttribute('aria-selected','true');
      panels.forEach(p=>p.classList.remove('active'));
      document.getElementById(id).classList.add('active');
    });
  </script>
</body>
</html>
