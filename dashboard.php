<?php
require 'config.php';
need();
$u = me();
check();

$tech = null;
if ($u['role'] === 'staff') {
  $tech = q("SELECT * FROM technicians WHERE user_id=?", [$u['id']])->fetch();
  if (isset($_POST['status']) && in_array($_POST['status'], ['Free','Busy'])) {
    q("UPDATE technicians SET status=? WHERE id=?", [$_POST['status'], $tech['id']]);
    $tech['status'] = $_POST['status'];
  }
  $rows = q("SELECT * FROM v_tickets WHERE assigned_tech_id=? ORDER BY FIELD(status,'In Progress','Pending','Resolved'), FIELD(priority,'High','Medium','Low')", [$tech['id']])->fetchAll();
} elseif ($u['role'] === 'employee') {
  $rows = q("SELECT * FROM v_tickets WHERE created_by=(SELECT name FROM users WHERE id=?) ORDER BY id DESC", [$u['id']])->fetchAll();
} else {
  $rows = q("SELECT * FROM v_tickets ORDER BY FIELD(status,'Pending','In Progress','Resolved'), FIELD(priority,'High','Medium','Low'), id DESC")->fetchAll();
}

$c = ['Pending' => 0, 'In Progress' => 0, 'Resolved' => 0];
foreach ($rows as $r) {
  if (isset($c[$r['status']])) {
    $c[$r['status']]++;
  }
}

$pageTitle = ['employee' => 'Dashboard', 'coordinator' => 'Coordinator Dashboard', 'staff' => 'Technician Console'][$u['role']];
head($pageTitle);

$recentTicket = $rows[0] ?? null;
?>

<?php if ($u['role'] === 'employee'): ?>
  <!-- Hero Banner -->
  <section class="reveal relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#05080F] via-[#0B1A45] to-[#1F5AE6] shadow-lg p-6 sm:p-10 min-h-[380px] flex items-center">
    <!-- Background photo: fades in from the right -->
    <img src="https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1400&q=70" alt="" class="absolute inset-y-0 right-0 w-3/4 h-full object-cover opacity-40 mix-blend-screen" style="-webkit-mask-image:linear-gradient(to right,transparent,#000 60%);mask-image:linear-gradient(to right,transparent,#000 60%)" onerror="this.remove()">
    <div class="absolute -right-24 -top-24 w-80 h-80 rounded-full bg-blue-400/30 blur-3xl"></div>

    <div class="relative max-w-xl z-10">
      <span class="inline-flex items-center gap-2 text-xs font-medium text-blue-100 bg-white/10 border border-white/20 rounded-full px-3 py-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>ICT Helpdesk is online</span>
      <span id="today" class="hidden sm:inline-flex ml-2 text-xs text-blue-200"></span>
      <p id="greeting" class="text-sm font-medium text-slate-200 mt-4"><?= date('H') < 12 ? 'Good morning,' : (date('H') < 17 ? 'Good afternoon,' : 'Good evening,') ?></p>
      <h2 class="font-display text-4xl sm:text-5xl font-bold text-white mt-1"><?= e($u['name']) ?></h2>
      <p class="text-slate-200 mt-3">Tech trouble? Send a request and track it right here.</p>
      <div class="flex flex-wrap gap-3 mt-6">
        <a href="new_request.php" class="inline-flex items-center gap-2 bg-white text-neon font-bold text-sm px-6 py-3.5 rounded-xl shadow-md hover:bg-slate-100 transition"><i data-lucide="plus" class="w-4 h-4"></i>Submit New Request</a>
        <a href="ticket_status.php" class="inline-flex items-center gap-2 bg-white/10 border border-white/30 text-white font-medium text-sm px-6 py-3.5 rounded-xl hover:bg-white/20 transition">My tickets</a>
      </div>
      <div class="flex flex-wrap items-center gap-2 mt-6">
        <span class="text-xs text-blue-200 mr-1">Common issues:</span>
        <a href="new_request.php" class="chip-link inline-flex items-center gap-1.5 text-xs font-medium text-white bg-white/10 border border-white/20 rounded-full px-3 py-1.5"><i data-lucide="printer" class="w-3.5 h-3.5"></i>Printer</a>
        <a href="new_request.php" class="chip-link inline-flex items-center gap-1.5 text-xs font-medium text-white bg-white/10 border border-white/20 rounded-full px-3 py-1.5"><i data-lucide="wifi" class="w-3.5 h-3.5"></i>Wi-Fi</a>
        <a href="new_request.php" class="chip-link inline-flex items-center gap-1.5 text-xs font-medium text-white bg-white/10 border border-white/20 rounded-full px-3 py-1.5"><i data-lucide="lock" class="w-3.5 h-3.5"></i>Password</a>
        <a href="new_request.php" class="chip-link inline-flex items-center gap-1.5 text-xs font-medium text-white bg-white/10 border border-white/20 rounded-full px-3 py-1.5"><i data-lucide="mail" class="w-3.5 h-3.5"></i>Email</a>
      </div>
    </div>

    <!-- Creative visual: helpdesk chip with orbiting service icons -->
    <div class="hidden lg:block absolute right-4 top-1/2 -translate-y-1/2 w-[380px] h-[320px]" aria-hidden="true">
      <svg viewBox="0 0 380 320" class="w-full h-full" fill="none">
        <defs>
          <linearGradient id="chip" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#60A5FA"/><stop offset="1" stop-color="#1D4ED8"/></linearGradient>
          <radialGradient id="aura"><stop offset="0" stop-color="#60A5FA" stop-opacity=".55"/><stop offset="1" stop-color="#60A5FA" stop-opacity="0"/></radialGradient>
          <filter id="gl"><feGaussianBlur stdDeviation="6"/></filter>
        </defs>
        <circle cx="190" cy="160" r="150" fill="url(#aura)"/>
        <circle class="spin-slow" cx="190" cy="160" r="112" stroke="#93C5FD" stroke-opacity=".45" stroke-width="1.5" stroke-dasharray="3 9"/>
        <circle cx="190" cy="160" r="72" stroke="#93C5FD" stroke-opacity=".3" stroke-width="1"/>
        <g transform="translate(20,20)" stroke="#60A5FA" stroke-opacity=".8" stroke-width="2" stroke-linecap="round">
          <path class="flow" d="M0 70h70l20 20h40M0 140h60l15-15h55M0 210h80l20-20h30M340 60h-80l-20 20h-40M340 150h-70l-15-15h-45M340 220h-90l-20-20h-30"/>
          <path class="flow" d="M150 0v60M190 0v40l-20 20M150 280v-60M190 280v-45l-15-15"/>
        </g>
        <g transform="translate(20,20)">
          <rect x="105" y="85" width="130" height="110" rx="22" fill="#3B82F6" opacity=".55" filter="url(#gl)"/>
          <rect x="105" y="85" width="130" height="110" rx="22" fill="url(#chip)" stroke="#BFDBFE" stroke-opacity=".6" stroke-width="2"/>
          <rect x="125" y="105" width="90" height="70" rx="12" fill="#0B1A45" opacity=".55"/>
          <path d="M148 140l16 16 30-34" stroke="#fff" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>
        </g>
      </svg>
      <!-- orbiting service icons -->
      <div class="orbit" style="--a:-90deg;--r:112px;--t:30s"><span class="w-10 h-10 rounded-full bg-white/15 backdrop-blur border border-white/30 text-white flex items-center justify-center shadow-lg"><i data-lucide="headphones" class="w-[18px] h-[18px]"></i></span></div>
      <div class="orbit" style="--a:-18deg;--r:112px;--t:30s"><span class="w-10 h-10 rounded-full bg-white/15 backdrop-blur border border-white/30 text-sky-200 flex items-center justify-center shadow-lg"><i data-lucide="wifi" class="w-[18px] h-[18px]"></i></span></div>
      <div class="orbit" style="--a:54deg;--r:112px;--t:30s"><span class="w-10 h-10 rounded-full bg-white/15 backdrop-blur border border-white/30 text-white flex items-center justify-center shadow-lg"><i data-lucide="printer" class="w-[18px] h-[18px]"></i></span></div>
      <div class="orbit" style="--a:126deg;--r:112px;--t:30s"><span class="w-10 h-10 rounded-full bg-white/15 backdrop-blur border border-white/30 text-amber-300 flex items-center justify-center shadow-lg"><i data-lucide="lock" class="w-[18px] h-[18px]"></i></span></div>
      <div class="orbit" style="--a:198deg;--r:112px;--t:30s"><span class="w-10 h-10 rounded-full bg-white/15 backdrop-blur border border-white/30 text-emerald-300 flex items-center justify-center shadow-lg"><i data-lucide="mail" class="w-[18px] h-[18px]"></i></span></div>
      <!-- floating status cards -->
      <div class="float-a absolute top-0 right-0 bg-white/10 backdrop-blur border border-white/25 rounded-xl px-3 py-2 text-white text-xs shadow-lg">
        <p class="font-semibold flex items-center gap-1.5"><i data-lucide="printer" class="w-3.5 h-3.5"></i>#<?= $recentTicket ? e($recentTicket['ticket_no']) : 'TK-2024' ?></p><p class="text-blue-100"><?= $recentTicket ? e($recentTicket['status']) : 'In progress' ?></p>
      </div>
      <div class="float-b absolute -bottom-2 -left-6 bg-white/10 backdrop-blur border border-white/25 rounded-xl px-3 py-2 text-white text-xs shadow-lg">
        <p class="font-semibold flex items-center gap-1.5"><i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-300"></i><?= $c['Resolved'] ?> resolved</p><p class="text-blue-100">Fast support</p>
      </div>
    </div>
  </section>

  <!-- Stats -->
  <?php 
    $tot = max(1, $c['Pending'] + $c['In Progress'] + $c['Resolved']);
    $pOpen = round(($c['Pending'] / $tot) * 100);
    $pProg = round(($c['In Progress'] / $tot) * 100);
    $pRes = round(($c['Resolved'] / $tot) * 100);
  ?>
  <section class="grid grid-cols-3 gap-4">
    <div class="reveal lift card rounded-2xl p-5" style="--d:0.1s">
      <div class="flex items-center justify-between">
        <span class="w-11 h-11 rounded-xl bg-blue-50 text-neon flex items-center justify-center"><i data-lucide="inbox" class="w-5 h-5"></i></span>
        <span class="font-display text-4xl font-bold text-slate-900"><?= $c['Pending'] ?></span>
      </div>
      <p class="text-sm font-medium text-slate-600 mt-3">Open</p>
      <div class="h-1.5 rounded-full bg-slate-100 mt-2 overflow-hidden"><div class="h-full rounded-full bg-neon" style="width:<?= $pOpen ?>%"></div></div>
    </div>
    <div class="reveal lift card rounded-2xl p-5" style="--d:0.2s">
      <div class="flex items-center justify-between">
        <span class="w-11 h-11 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center"><i data-lucide="loader" class="w-5 h-5"></i></span>
        <span class="font-display text-4xl font-bold text-slate-900"><?= $c['In Progress'] ?></span>
      </div>
      <p class="text-sm font-medium text-slate-600 mt-3">In progress</p>
      <div class="h-1.5 rounded-full bg-slate-100 mt-2 overflow-hidden"><div class="h-full rounded-full bg-amber-400" style="width:<?= $pProg ?>%"></div></div>
    </div>
    <div class="reveal lift card rounded-2xl p-5" style="--d:0.3s">
      <div class="flex items-center justify-between">
        <span class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center"><i data-lucide="check-circle-2" class="w-5 h-5"></i></span>
        <span class="font-display text-4xl font-bold text-slate-900"><?= $c['Resolved'] ?></span>
      </div>
      <p class="text-sm font-medium text-slate-600 mt-3">Resolved</p>
      <div class="h-1.5 rounded-full bg-slate-100 mt-2 overflow-hidden"><div class="h-full rounded-full bg-emerald-500" style="width:<?= $pRes ?>%"></div></div>
    </div>
  </section>

  <div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">

      <!-- Most Recent Ticket -->
      <section class="reveal lift card rounded-3xl p-6" style="--d:.4s">
        <?php if ($recentTicket): 
          $st = $recentTicket['status'];
          $hasTech = !empty($recentTicket['assigned_to']);
          $s1 = true;
          $s2 = $hasTech || $st === 'In Progress' || $st === 'Resolved';
          $s3 = $st === 'In Progress' || $st === 'Resolved';
          $s4 = $st === 'Resolved';
          $progW = $s4 ? '100%' : ($s3 ? '66%' : ($s2 ? '33%' : '0%'));
        ?>
        <div class="flex justify-between items-start gap-4">
          <div class="flex gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-neon flex items-center justify-center shrink-0"><i data-lucide="printer" class="w-6 h-6"></i></div>
            <div>
              <p class="text-xs font-bold text-neon">#<?= e($recentTicket['ticket_no']) ?> · Your most recent ticket</p>
              <h3 class="text-lg font-bold text-slate-900"><?= e($recentTicket['title']) ?></h3>
              <p class="text-sm text-slate-500"><?= e($recentTicket['department'] ?: 'IT Department') ?> · <?= e(substr($recentTicket['created_at'], 0, 16)) ?></p>
            </div>
          </div>
          <div><?= badge($recentTicket['status']) ?></div>
        </div>
        <ol class="relative grid grid-cols-4 mt-7 text-center text-xs font-medium">
          <div class="absolute top-4 left-[12.5%] right-[12.5%] h-0.5 bg-slate-200"><div class="h-full bg-gradient-to-r from-neon to-amber-400" style="width:<?= $progW ?>"></div></div>
          <li class="relative"><span class="mx-auto w-8 h-8 rounded-full <?= $s1 ? 'bg-neon text-white' : 'bg-slate-200 text-slate-400' ?> flex items-center justify-center ring-4 ring-white"><i data-lucide="check" class="w-4 h-4"></i></span><span class="block mt-2 <?= $s1 ? 'text-neon' : 'text-slate-500' ?>">Submitted</span></li>
          <li class="relative"><span class="mx-auto w-8 h-8 rounded-full <?= $s2 ? 'bg-neon text-white' : 'bg-slate-200 text-slate-400' ?> flex items-center justify-center ring-4 ring-white"><i data-lucide="user-check" class="w-4 h-4"></i></span><span class="block mt-2 <?= $s2 ? 'text-neon' : 'text-slate-500' ?>">Assigned</span></li>
          <li class="relative"><span class="relative mx-auto w-8 h-8 rounded-full <?= $s3 ? 'bg-amber-400 text-white' : 'bg-slate-200 text-slate-400' ?> flex items-center justify-center ring-4 ring-white"><?php if ($s3 && !$s4): ?><span class="ping-ring absolute inset-0 rounded-full bg-amber-400/60"></span><?php endif; ?><i data-lucide="wrench" class="relative w-4 h-4"></i></span><span class="block mt-2 <?= $s3 ? 'text-amber-600' : 'text-slate-500' ?>">In progress</span></li>
          <li class="relative"><span class="mx-auto w-8 h-8 rounded-full <?= $s4 ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-400' ?> flex items-center justify-center ring-4 ring-white"><i data-lucide="flag" class="w-4 h-4"></i></span><span class="block mt-2 <?= $s4 ? 'text-emerald-600' : 'text-slate-500' ?>">Resolved</span></li>
        </ol>
        <div class="flex items-center gap-3 mt-5">
          <a href="ticket.php?id=<?= $recentTicket['id'] ?>" class="inline-flex items-center gap-2 text-sm font-bold text-neon border border-neon hover:bg-blue-50 px-4 py-2 rounded-lg transition">View Details<i data-lucide="arrow-right" class="w-4 h-4"></i></a>
          <?php if ($recentTicket['assigned_to']): ?>
          <span class="text-xs text-slate-500">Technician: <strong class="text-slate-700"><?= e($recentTicket['assigned_to']) ?></strong></span>
          <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-6 text-sm text-slate-400">
          <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
          You have not submitted any tickets yet. Click "Submit New Request" above to get IT assistance.
        </div>
        <?php endif; ?>
      </section>

      <!-- Quick Actions -->
      <section>
        <h3 class="sec-title">Quick actions</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <a href="new_request.php" class="reveal lift card group rounded-2xl p-5 text-center" style="--d:0.5s">
            <span class="mx-auto w-12 h-12 rounded-2xl bg-blue-50 text-neon flex items-center justify-center transition group-hover:scale-110"><i data-lucide="plus" class="w-6 h-6"></i></span>
            <p class="text-sm font-bold text-slate-900 mt-3">New Request</p><p class="text-xs text-slate-500">Report a problem</p></a>
          <a href="ticket_status.php" class="reveal lift card group rounded-2xl p-5 text-center" style="--d:0.6s">
            <span class="mx-auto w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center transition group-hover:scale-110"><i data-lucide="ticket" class="w-6 h-6"></i></span>
            <p class="text-sm font-bold text-slate-900 mt-3">My Tickets</p><p class="text-xs text-slate-500">Track progress</p></a>
          <a href="notifications.php" class="reveal lift card group rounded-2xl p-5 text-center" style="--d:0.7s">
            <span class="mx-auto w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center transition group-hover:scale-110"><i data-lucide="bell" class="w-6 h-6"></i></span>
            <p class="text-sm font-bold text-slate-900 mt-3">Notifications</p><p class="text-xs text-slate-500">See updates</p></a>
          <a href="ticket_status.php" class="reveal lift card group rounded-2xl p-5 text-center" style="--d:0.8s">
            <span class="mx-auto w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center transition group-hover:scale-110"><i data-lucide="history" class="w-6 h-6"></i></span>
            <p class="text-sm font-bold text-slate-900 mt-3">History</p><p class="text-xs text-slate-500">Past requests</p></a>
        </div>
      </section>

      <!-- Quick Help -->
      <section>
        <h3 class="sec-title">Quick help</h3>
        <div class="card rounded-2xl divide-y divide-slate-100">
          <details class="group px-5 py-4">
            <summary class="flex items-center justify-between cursor-pointer list-none">
              <span class="flex items-center gap-3 text-sm font-bold text-slate-900"><span class="w-9 h-9 rounded-lg bg-indigo-50 flex items-center justify-center"><i data-lucide="printer" class="w-4 h-4 text-neon"></i></span>Printer not printing?</span>
              <i data-lucide="chevron-down" class="w-4 h-4 text-slate-500 transition-transform group-open:rotate-180"></i>
            </summary>
            <p class="text-sm text-slate-500 mt-3 pl-12 leading-relaxed">Check the printer has paper and isn't showing an error light. Try restarting it from the power switch. Still stuck? Submit a request under "Printer" category.</p>
          </details>
          <details class="group px-5 py-4">
            <summary class="flex items-center justify-between cursor-pointer list-none">
              <span class="flex items-center gap-3 text-sm font-bold text-slate-900"><span class="w-9 h-9 rounded-lg bg-indigo-50 flex items-center justify-center"><i data-lucide="wifi" class="w-4 h-4 text-pink"></i></span>Can't connect to Wi-Fi?</span>
              <i data-lucide="chevron-down" class="w-4 h-4 text-slate-500 transition-transform group-open:rotate-180"></i>
            </summary>
            <p class="text-sm text-slate-500 mt-3 pl-12 leading-relaxed">Forget the network on your device and reconnect using the password on the noticeboard. If it still fails after two tries, submit a "Network" request.</p>
          </details>
          <details class="group px-5 py-4">
            <summary class="flex items-center justify-between cursor-pointer list-none">
              <span class="flex items-center gap-3 text-sm font-bold text-slate-900"><span class="w-9 h-9 rounded-lg bg-indigo-50 flex items-center justify-center"><i data-lucide="lock" class="w-4 h-4 text-lime"></i></span>Forgot your password?</span>
              <i data-lucide="chevron-down" class="w-4 h-4 text-slate-500 transition-transform group-open:rotate-180"></i>
            </summary>
            <p class="text-sm text-slate-500 mt-3 pl-12 leading-relaxed">Use "Forgot password?" on the sign-in screen to reset it yourself. If you don't receive the reset email within 5 minutes, contact the ICT helpdesk below.</p>
          </details>
        </div>
      </section>
    </div>

    <!-- Right column -->
    <aside class="space-y-6">
      <section>
        <h3 class="sec-title">Announcements</h3>
        <div class="space-y-3">
          <div class="reveal lift card rounded-2xl p-4 flex gap-3 border-l-2 border-l-amber-400" style="--d:.5s">
            <i data-lucide="mail" class="w-5 h-5 text-amber-400 shrink-0 mt-0.5"></i>
            <div><p class="text-sm text-slate-600"><span class="bg-rose-100 text-rose-600 text-[10px] font-bold px-1.5 py-0.5 rounded mr-1 align-middle">TODAY</span><strong class="text-slate-900">Email server maintenance</strong> today, 2:00 PM – 4:00 PM. You may be briefly signed out of Outlook.</p><p class="text-xs text-slate-500 mt-1">Posted this morning</p></div>
          </div>
          <div class="reveal lift card rounded-2xl p-4 flex gap-3 border-l-2 border-l-neon" style="--d:.6s">
            <i data-lucide="wifi" class="w-5 h-5 text-neon shrink-0 mt-0.5"></i>
            <div><p class="text-sm text-slate-600"><strong class="text-slate-900">Wi-Fi upgrade</strong> at Colombo Head Office this weekend. Expect brief outages on Saturday.</p><p class="text-xs text-slate-500 mt-1">Posted yesterday</p></div>
          </div>
        </div>
      </section>

      <!-- Urgent Banner -->
      <section class="reveal relative overflow-hidden rounded-3xl p-6 text-center bg-gradient-to-br from-[#0B1530] to-[#1F5AE6] text-white shadow-lg" style="--d:.7s">
        <div class="absolute -right-10 -bottom-10 w-40 h-40 rounded-full bg-blue-400/30 blur-2xl"></div>
        <div class="relative w-14 h-14 mx-auto mb-3">
          <span class="ping-ring absolute inset-0 rounded-full bg-rose-400/60"></span>
          <span class="relative w-14 h-14 rounded-full bg-rose-500 text-white flex items-center justify-center"><i data-lucide="phone-call" class="w-6 h-6"></i></span>
        </div>
        <p class="relative text-sm text-blue-100">Urgent issue? Call the ICT Helpdesk</p>
        <a href="tel:0112345678" class="relative inline-block font-display text-2xl font-bold text-white hover:text-amber-300 transition mt-1">011 234 5678</a>
      </section>
    </aside>
  </div>

  <!-- All My Tickets Table Section -->
  <section id="tickets-section" class="pt-4">
    <div class="flex items-center justify-between mb-3">
      <div>
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">All Support Tickets</p>
        <h3 class="font-display text-base font-bold text-slate-800">Submitted Requests (<?= count($rows) ?>)</h3>
      </div>
      <a href="new_request.php" class="text-xs font-bold text-neon hover:underline flex items-center gap-1">
        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Create New
      </a>
    </div>
    <?php ticketsTable($rows); ?>
  </section>

  <p class="text-center text-xs text-slate-500 pt-2">ICT Assist v1.0 (Web) · ICT Unit</p>

<?php elseif ($u['role'] === 'coordinator'): ?>
  <!-- Coordinator View -->
  <div class="rounded-3xl bg-[#1e1b4b] text-white p-8 shadow-xl flex flex-col sm:flex-row justify-between sm:items-center gap-4">
    <div>
      <span class="text-xs text-purple-300 font-semibold tracking-wide">Coordinator Incident Operations</span>
      <h2 class="text-2xl font-extrabold mt-1"><?= e($u['name']) ?></h2>
      <p class="text-xs text-purple-200 mt-1"><?= count($rows) ?> total · <?= $c['Pending'] ?> pending dispatch · <?= $c['In Progress'] ?> active · <?= $c['Resolved'] ?> resolved</p>
    </div>
    <a href="reports.php" class="bg-white hover:bg-slate-100 text-purple-950 text-xs font-semibold px-4 py-2.5 rounded-xl shadow-md transition flex items-center space-x-1.5 self-start sm:self-auto">
      <i data-lucide="bar-chart-2" class="w-3.5 h-3.5 text-purple-700"></i><span>View Performance Reports</span>
    </a>
  </div>

  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs text-center">
      <p class="text-xs text-slate-500 font-medium">Total Tickets</p>
      <p class="text-2xl font-extrabold text-slate-800 mt-1"><?= count($rows) ?></p>
    </div>
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs text-center">
      <p class="text-xs text-amber-600 font-medium">Pending Assignment</p>
      <p class="text-2xl font-extrabold text-amber-600 mt-1"><?= $c['Pending'] ?></p>
    </div>
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs text-center">
      <p class="text-xs text-blue-600 font-medium">In Progress</p>
      <p class="text-2xl font-extrabold text-blue-600 mt-1"><?= $c['In Progress'] ?></p>
    </div>
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs text-center">
      <p class="text-xs text-emerald-600 font-medium">Resolved</p>
      <p class="text-2xl font-extrabold text-emerald-600 mt-1"><?= $c['Resolved'] ?></p>
    </div>
  </div>

  <div id="tickets-section" class="pt-2">
    <div class="flex items-center justify-between mb-3">
      <div>
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Incident Queue</p>
        <h3 class="text-sm font-bold text-slate-800">Dispatch &amp; Technician Assignment</h3>
      </div>
      <a href="reports.php" class="text-xs font-semibold text-blue-600 hover:underline">View Analytics &rarr;</a>
    </div>
    <?php ticketsTable($rows); ?>
  </div>

<?php elseif ($u['role'] === 'staff'): ?>
  <!-- Staff Technician Console -->
  <div class="rounded-3xl bg-[#064e3b] text-white p-8 shadow-xl flex flex-col sm:flex-row justify-between sm:items-center gap-4">
    <div>
      <span class="text-xs text-emerald-300 font-semibold tracking-wide">Technician Operations Console</span>
      <h2 class="text-2xl font-extrabold mt-1"><?= e($u['name']) ?></h2>
      <p class="text-xs text-emerald-200 mt-1"><?= count($rows) ?> assigned · <?= $c['In Progress'] ?> active · <?= $c['Resolved'] ?> resolved</p>
    </div>
    <?php if ($tech): ?>
    <form method="post" class="flex items-center gap-3 bg-white/10 p-2 px-3 rounded-2xl backdrop-blur-xs border border-white/15">
      <?= tok() ?>
      <span class="text-xs font-medium text-emerald-100">Status: <strong class="text-white font-bold"><?= e($tech['status']) ?></strong></span>
      <button name="status" value="<?= $tech['status'] === 'Free' ? 'Busy' : 'Free' ?>" class="bg-white hover:bg-emerald-50 text-emerald-950 font-bold text-xs py-1.5 px-3 rounded-xl shadow-xs transition">
        Set <?= $tech['status'] === 'Free' ? 'Busy' : 'Free' ?>
      </button>
    </form>
    <?php endif; ?>
  </div>

  <div class="grid grid-cols-3 gap-4">
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs text-center">
      <p class="text-xs text-slate-500 font-medium">Assigned Tasks</p>
      <p class="text-2xl font-extrabold text-slate-800 mt-1"><?= count($rows) ?></p>
    </div>
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs text-center">
      <p class="text-xs text-emerald-600 font-medium">In Progress</p>
      <p class="text-2xl font-extrabold text-emerald-600 mt-1"><?= $c['In Progress'] ?></p>
    </div>
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs text-center">
      <p class="text-xs text-blue-600 font-medium">Completed</p>
      <p class="text-2xl font-extrabold text-blue-600 mt-1"><?= $c['Resolved'] ?></p>
    </div>
  </div>

  <div id="tickets-section" class="pt-2">
    <div class="flex items-center justify-between mb-3">
      <div>
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Work Queue</p>
        <h3 class="text-sm font-bold text-slate-800">Your Assigned Incidents</h3>
      </div>
    </div>
    <?php ticketsTable($rows); ?>
  </div>
<?php endif; ?>

<!-- Urgent Helpdesk Call Banner -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 text-center text-xs text-slate-500 flex items-center justify-center gap-2">
  <i data-lucide="phone-call" class="w-4 h-4 text-rose-500"></i>
  <span>Urgent issue? Call the ICT Helpdesk <strong class="text-slate-800 font-bold">011 234 5678</strong></span>
</div>

<p class="text-center text-[11px] text-slate-400 pt-2">ICT Assist v1.0 (Web) · ICT Unit &copy; 2026</p>

<script>
  const todayEl = document.getElementById('today');
  if (todayEl) {
    todayEl.textContent = '· ' + new Date().toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long' });
  }
</script>
<?php foot(); ?>
