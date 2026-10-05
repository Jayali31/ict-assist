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
  <div class="rounded-3xl bg-[#0f1e4d] text-white p-8 shadow-xl flex flex-col sm:flex-row justify-between sm:items-center gap-4">
    <div>
      <span class="text-xs text-blue-300 font-semibold tracking-wide">Good morning,</span>
      <h2 class="text-2xl font-extrabold mt-1"><?= e($u['name']) ?></h2>
      <p class="text-xs text-blue-200 mt-1"><?= $c['Pending'] ?> open · <?= $c['In Progress'] ?> in progress · <?= $c['Resolved'] ?> resolved</p>
    </div>
    <a href="new_request.php" class="bg-white hover:bg-slate-100 text-slate-800 text-xs font-semibold px-4 py-2.5 rounded-xl shadow-md transition flex items-center space-x-1.5 self-start sm:self-auto">
      <i data-lucide="plus" class="w-3.5 h-3.5 text-blue-600"></i><span>Submit New Request</span>
    </a>
  </div>

  <!-- Most Recent Ticket -->
  <div>
    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Your Most Recent Ticket</p>
    <?php if ($recentTicket): ?>
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs hover:border-blue-200 transition">
      <div class="flex justify-between items-start">
        <div>
          <span class="text-xs font-bold text-blue-600">#<?= e($recentTicket['ticket_no']) ?></span>
          <h3 class="text-sm font-bold text-slate-800 mt-1"><?= e($recentTicket['title']) ?></h3>
          <p class="text-xs text-slate-400 mt-0.5"><?= e($recentTicket['department'] ?: 'IT Department') ?> · <?= e($recentTicket['branch'] ?: 'Main') ?> · <?= e(substr($recentTicket['created_at'], 0, 16)) ?></p>
        </div>
        <div><?= badge($recentTicket['status']) ?></div>
      </div>
      <div class="flex items-center gap-3 mt-3">
        <a href="ticket.php?id=<?= $recentTicket['id'] ?>" class="inline-block text-xs font-semibold text-blue-600 border border-blue-200 px-3 py-1.5 rounded-lg hover:bg-blue-50 transition">View Details</a>
        <?php if ($recentTicket['assigned_to']): ?>
        <span class="text-xs text-slate-500">Technician: <strong class="text-slate-700"><?= e($recentTicket['assigned_to']) ?></strong></span>
        <?php endif; ?>
      </div>
    </div>
    <?php else: ?>
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs text-center text-xs text-slate-400">
      You have not submitted any tickets yet. Click "Submit New Request" above to get IT assistance.
    </div>
    <?php endif; ?>
  </div>

  <!-- Announcements -->
  <div>
    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Announcements</p>
    <div class="space-y-3">
      <div class="bg-white p-4 rounded-xl border-l-4 border-amber-400 shadow-xs">
        <p class="text-xs text-slate-700"><strong>Email server maintenance</strong> today, 2:00 PM – 4:00 PM. You may be briefly signed out of Outlook.</p>
        <p class="text-[11px] text-slate-400 mt-1">Posted this morning</p>
      </div>
      <div class="bg-white p-4 rounded-xl border-l-4 border-blue-400 shadow-xs">
        <p class="text-xs text-slate-700"><strong>Wi-Fi upgrade</strong> at Colombo Head Office this weekend. Expect brief outages on Saturday.</p>
        <p class="text-[11px] text-slate-400 mt-1">Posted yesterday</p>
      </div>
    </div>
  </div>

  <!-- Quick Help Accordion -->
  <div>
    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Quick Help</p>
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs divide-y divide-slate-100 overflow-hidden">
      <details class="group px-4 py-3">
        <summary class="flex items-center justify-between cursor-pointer list-none">
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600"><i data-lucide="printer" class="w-4 h-4"></i></div>
            <span class="text-xs font-semibold text-slate-700">Printer not printing?</span>
          </div>
          <i data-lucide="chevron-down" class="w-4 h-4 text-slate-300 transition-transform group-open:rotate-180"></i>
        </summary>
        <p class="text-xs text-slate-500 mt-3 pl-11 leading-relaxed">
          Check the printer has paper and isn't showing an error light. Try restarting it from the power switch. Still stuck? Submit a request under "Printer" or "Hardware" category.
        </p>
      </details>

      <details class="group px-4 py-3">
        <summary class="flex items-center justify-between cursor-pointer list-none">
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500"><i data-lucide="wifi" class="w-4 h-4"></i></div>
            <span class="text-xs font-semibold text-slate-700">Can't connect to Wi-Fi?</span>
          </div>
          <i data-lucide="chevron-down" class="w-4 h-4 text-slate-300 transition-transform group-open:rotate-180"></i>
        </summary>
        <p class="text-xs text-slate-500 mt-3 pl-11 leading-relaxed">
          Forget the network on your device and reconnect using the password on the noticeboard. If it still fails after two tries, submit a "Network" request.
        </p>
      </details>

      <details class="group px-4 py-3">
        <summary class="flex items-center justify-between cursor-pointer list-none">
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-500"><i data-lucide="lock" class="w-4 h-4"></i></div>
            <span class="text-xs font-semibold text-slate-700">Forgot your password?</span>
          </div>
          <i data-lucide="chevron-down" class="w-4 h-4 text-slate-300 transition-transform group-open:rotate-180"></i>
        </summary>
        <p class="text-xs text-slate-500 mt-3 pl-11 leading-relaxed">
          Use "Forgot?" on the sign-in screen to reset it yourself. If you don't receive the reset email within 5 minutes, contact the ICT helpdesk directly.
        </p>
      </details>
    </div>
  </div>

  <!-- Quick Actions -->
  <div>
    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Quick Actions</p>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <a href="new_request.php" class="bg-white p-5 rounded-2xl border border-slate-200 hover:border-blue-500 hover:shadow-md transition shadow-xs flex flex-col items-center text-center group">
        <div class="w-9 h-9 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mb-2 group-hover:scale-110 transition"><i data-lucide="plus" class="w-4 h-4"></i></div>
        <h4 class="text-xs font-bold text-slate-800">New Request</h4>
      </a>
      <a href="#tickets-section" class="bg-white p-5 rounded-2xl border border-slate-200 hover:border-blue-500 hover:shadow-md transition shadow-xs flex flex-col items-center text-center group">
        <div class="w-9 h-9 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mb-2 group-hover:scale-110 transition"><i data-lucide="ticket" class="w-4 h-4"></i></div>
        <h4 class="text-xs font-bold text-slate-800">My Tickets</h4>
      </a>
      <a href="notifications.php" class="bg-white p-5 rounded-2xl border border-slate-200 hover:border-blue-500 hover:shadow-md transition shadow-xs flex flex-col items-center text-center group">
        <div class="w-9 h-9 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center mb-2 group-hover:scale-110 transition"><i data-lucide="bell" class="w-4 h-4"></i></div>
        <h4 class="text-xs font-bold text-slate-800">Notifications</h4>
      </a>
      <a href="#tickets-section" class="bg-white p-5 rounded-2xl border border-slate-200 hover:border-blue-500 hover:shadow-md transition shadow-xs flex flex-col items-center text-center group">
        <div class="w-9 h-9 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center mb-2 group-hover:scale-110 transition"><i data-lucide="history" class="w-4 h-4"></i></div>
        <h4 class="text-xs font-bold text-slate-800">History</h4>
      </a>
    </div>
  </div>

  <!-- All My Tickets Table Section -->
  <div id="tickets-section" class="pt-2">
    <div class="flex items-center justify-between mb-3">
      <div>
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">All Support Tickets</p>
        <h3 class="text-sm font-bold text-slate-800">Submitted Requests (<?= count($rows) ?>)</h3>
      </div>
      <a href="new_request.php" class="text-xs font-bold text-blue-600 hover:underline flex items-center gap-1">
        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Create New
      </a>
    </div>
    <?php ticketsTable($rows); ?>
  </div>

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

<?php foot(); ?>
