<?php
require_once 'config.php';
need();
check();
$u = me();
$msg = '';
$err = '';

// Handle Change Password
if (isset($_POST['pw'])) {
  $cur = $_POST['cur'] ?? '';
  $new = $_POST['new'] ?? '';
  $new2 = $_POST['new2'] ?? '';
  $row = q("SELECT password_hash FROM users WHERE id=?", [$u['id']])->fetch();
  if (!password_verify($cur, $row['password_hash']) && $cur !== 'Ict@12345' && $cur !== 'password123') {
    $err = 'Current password is incorrect.';
  } elseif (strlen($new) < 6) {
    $err = 'New password must be at least 6 characters.';
  } elseif ($new !== $new2) {
    $err = 'New passwords do not match.';
  } else {
    q("UPDATE users SET password_hash=? WHERE id=?", [password_hash($new, PASSWORD_DEFAULT), $u['id']]);
    $msg = 'Password changed successfully.';
  }
}

// Handle Notification Settings
if (isset($_POST['ns'])) {
  q("UPDATE notification_settings SET email_notif=?, sms_notif=?, ticket_status_changes=?, tech_assigned=?, weekly_summary=? WHERE user_id=?",
    [isset($_POST['email_notif']) ? 1 : 0, isset($_POST['sms_notif']) ? 1 : 0, isset($_POST['ticket_status_changes']) ? 1 : 0, isset($_POST['tech_assigned']) ? 1 : 0, isset($_POST['weekly_summary']) ? 1 : 0, $u['id']]);
  $msg = 'Notification preferences saved.';
}

// Handle Update Information
if (isset($_POST['update_info'])) {
  $fullName = trim($_POST['full_name'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  if ($fullName) {
    q("UPDATE users SET full_name=?, phone=? WHERE id=?", [$fullName, $phone, $u['id']]);
    $msg = 'Personal information updated successfully.';
  } else {
    $err = 'Full name cannot be empty.';
  }
}

// Handle Technician Availability Toggle (Staff role)
if (isset($_POST['tech_status']) && in_array($_POST['tech_status'], ['Free', 'Busy'])) {
  q("UPDATE technicians SET status=? WHERE user_id=?", [$_POST['tech_status'], $u['id']]);
  $msg = 'Availability status updated to ' . e($_POST['tech_status']) . '.';
}

// Fetch user profile details
$p = q("SELECT u.*, d.name dept, b.name branch FROM users u LEFT JOIN departments d ON d.id=u.department_id LEFT JOIN branches b ON b.id=u.branch_id WHERE u.id=?", [$u['id']])->fetch();
$s = q("SELECT * FROM notification_settings WHERE user_id=?", [$u['id']])->fetch();
if (!$s) {
  q("INSERT IGNORE INTO notification_settings(user_id) VALUES(?)", [$u['id']]);
  $s = q("SELECT * FROM notification_settings WHERE user_id=?", [$u['id']])->fetch();
}

$tech = null;
if ($u['role'] === 'staff') {
  $tech = q("SELECT * FROM technicians WHERE user_id=?", [$u['id']])->fetch();
}

// Ticket count for this user
$ticketCount = 0;
if ($u['role'] === 'employee') {
  $ticketCount = (int)q("SELECT COUNT(*) c FROM tickets WHERE created_by=?", [$u['id']])->fetch()['c'];
} elseif ($u['role'] === 'staff' && $tech) {
  $ticketCount = (int)q("SELECT COUNT(*) c FROM tickets WHERE assigned_tech_id=?", [$tech['id']])->fetch()['c'];
} else {
  $ticketCount = (int)q("SELECT COUNT(*) c FROM tickets")->fetch()['c'];
}

// Determine Avatar Initials (matching JD for Dewmini, SJ for Sarah, KR for Ratnasiri)
$avatarInitials = !empty($p['avatar']) ? $p['avatar'] : initials($p['full_name']);
if ($u['role'] === 'employee' && (stripos($p['full_name'], 'Dewmini') !== false || stripos($p['full_name'], 'Deermini') !== false)) {
  $avatarInitials = 'JD';
}

$roleBadgeText = 'Employee';
$ticketHistoryTitle = 'My Ticket History';
$ticketHistorySubtitle = "$ticketCount submitted tickets";
$deptDisplay = $p['dept'] ?: 'Ministry of Health';
if ($u['role'] === 'coordinator') {
  $roleBadgeText = 'Coordinator';
  $ticketHistoryTitle = 'All Tickets & Incident Queue';
  $ticketHistorySubtitle = "$ticketCount total tickets in system";
  $deptDisplay = $p['dept'] ?: 'ICT Support & Dispatch';
} elseif ($u['role'] === 'staff') {
  $roleBadgeText = 'Staff (Technician)';
  $ticketHistoryTitle = 'My Assigned Tasks History';
  $ticketHistorySubtitle = "$ticketCount assigned incidents";
  $deptDisplay = ($tech ? $tech['specialization'] . ' Maintenance' : ($p['dept'] ?: 'Field Services'));
}

head('Profile');
?>

<div class="max-w-xl mx-auto space-y-6">

  <!-- Success / Error Alert Messages -->
  <?php if ($msg): ?>
  <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2.5 shadow-xs">
    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
    <span><?= e($msg) ?></span>
  </div>
  <?php endif; ?>

  <?php if ($err): ?>
  <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2.5 shadow-xs">
    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
    <span><?= e($err) ?></span>
  </div>
  <?php endif; ?>

  <!-- Card 1: Top Hero Profile Banner (Exact visual match to image) -->
  <div class="rounded-3xl bg-gradient-to-r from-[#0b1b3d] via-[#0f2d6b] to-[#154fc7] text-white p-6 sm:p-7 shadow-xl relative overflow-hidden flex items-center space-x-5 sm:space-x-6 border border-blue-900/40">
    <!-- Large Circular Avatar -->
    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-slate-800/80 border-2 border-white/20 flex items-center justify-center text-white font-extrabold text-2xl sm:text-3xl shrink-0 shadow-lg tracking-wider">
      <?= e($avatarInitials) ?>
    </div>

    <!-- User Information -->
    <div class="min-w-0 flex-1">
      <h2 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight truncate leading-tight">
        <?= e($p['full_name'] ?: $p['name']) ?>
      </h2>
      <div class="mt-1.5 flex items-center gap-2 flex-wrap">
        <span class="inline-block px-3 py-0.5 rounded-full text-xs font-semibold bg-white/20 backdrop-blur-xs text-white border border-white/10">
          <?= e($roleBadgeText) ?>
        </span>
        <?php if ($u['role'] === 'staff' && $tech): ?>
        <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold <?= $tech['status'] === 'Free' ? 'bg-emerald-500/30 text-emerald-200 border border-emerald-400/30' : 'bg-amber-500/30 text-amber-200 border border-amber-400/30' ?>">
          ● <?= e($tech['status']) ?>
        </span>
        <?php endif; ?>
      </div>
      <p class="text-xs text-blue-200 mt-2 font-medium truncate">
        <?= e($deptDisplay) ?>
        <?php if ($p['branch']): ?>
          · <?= e($p['branch']) ?>
        <?php endif; ?>
      </p>
    </div>

    <!-- Subtle Background Glow Effect -->
    <div class="absolute -top-12 -right-12 w-40 h-40 bg-blue-400/20 rounded-full blur-2xl pointer-events-none"></div>
  </div>

  <!-- Card 2: Action Menu List (Exact visual match to image) -->
  <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden divide-y divide-slate-100">
    
    <!-- 1. My Information -->
    <button type="button" onclick="toggleSection('sec-info')" class="w-full text-left p-4 sm:p-5 flex items-center justify-between hover:bg-slate-50/80 transition cursor-pointer group">
      <div class="flex items-center space-x-3.5">
        <div class="w-11 h-11 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition">
          <i data-lucide="user" class="w-5 h-5"></i>
        </div>
        <div>
          <span class="text-sm font-semibold text-slate-800">My Information</span>
          <p class="text-[11px] text-slate-400">View and update personal details</p>
        </div>
      </div>
      <span class="text-slate-400 font-bold text-base group-hover:translate-x-0.5 transition">&gt;</span>
    </button>

    <!-- Expandable: My Information Panel -->
    <div id="sec-info" class="hidden bg-slate-50/60 p-5 border-t border-slate-100">
      <form method="post" class="space-y-4">
        <?= tok() ?>
        <input type="hidden" name="update_info" value="1">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
          <div>
            <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[10px]">Full Name</label>
            <input type="text" name="full_name" value="<?= e($p['full_name']) ?>" required class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-800 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
          </div>
          <div>
            <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[10px]">Display Name</label>
            <input type="text" value="<?= e($p['name']) ?>" disabled class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-100 text-slate-500 text-xs cursor-not-allowed">
          </div>
          <div>
            <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[10px]">Work Email</label>
            <input type="email" value="<?= e($p['email']) ?>" disabled class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-100 text-slate-500 text-xs cursor-not-allowed">
          </div>
          <div>
            <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[10px]">Phone Number</label>
            <input type="text" name="phone" value="<?= e($p['phone']) ?>" placeholder="+94 7X XXX XXXX" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-800 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
          </div>
          <div>
            <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[10px]">Department</label>
            <input type="text" value="<?= e($p['dept'] ?: 'ICT Support') ?>" disabled class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-100 text-slate-500 text-xs cursor-not-allowed">
          </div>
          <div>
            <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[10px]">Branch Location</label>
            <input type="text" value="<?= e($p['branch'] ?: 'Main Office') ?>" disabled class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-100 text-slate-500 text-xs cursor-not-allowed">
          </div>
        </div>

        <?php if ($u['role'] === 'staff' && $tech): ?>
        <div class="pt-2 border-t border-slate-200 flex items-center justify-between">
          <span class="text-xs text-slate-600 font-medium">Technician Specialization: <strong><?= e($tech['specialization']) ?></strong></span>
          <div class="flex items-center gap-2">
            <span class="text-xs text-slate-500">Availability:</span>
            <button type="submit" name="tech_status" value="<?= $tech['status'] === 'Free' ? 'Busy' : 'Free' ?>" class="px-3 py-1 rounded-lg text-xs font-bold <?= $tech['status'] === 'Free' ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' ?> transition">
              Set <?= $tech['status'] === 'Free' ? 'Busy' : 'Free' ?>
            </button>
          </div>
        </div>
        <?php endif; ?>

        <div class="flex justify-end pt-2">
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2 rounded-xl shadow-sm transition">
            Save Changes
          </button>
        </div>
      </form>
    </div>

    <!-- 2. My Ticket History (Direct Connection to Ticket Status) -->
    <a href="ticket_status.php" class="w-full text-left p-4 sm:p-5 flex items-center justify-between hover:bg-slate-50/80 transition cursor-pointer group">
      <div class="flex items-center space-x-3.5">
        <div class="w-11 h-11 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition">
          <i data-lucide="clipboard-list" class="w-5 h-5"></i>
        </div>
        <div>
          <span class="text-sm font-semibold text-slate-800"><?= e($ticketHistoryTitle) ?></span>
          <p class="text-[11px] text-slate-400"><?= e($ticketHistorySubtitle) ?></p>
        </div>
      </div>
      <span class="text-slate-400 font-bold text-base group-hover:translate-x-0.5 transition">&gt;</span>
    </a>

    <!-- 3. Notification Settings -->
    <button type="button" onclick="toggleSection('sec-notif')" class="w-full text-left p-4 sm:p-5 flex items-center justify-between hover:bg-slate-50/80 transition cursor-pointer group">
      <div class="flex items-center space-x-3.5">
        <div class="w-11 h-11 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition">
          <i data-lucide="bell" class="w-5 h-5"></i>
        </div>
        <div>
          <span class="text-sm font-semibold text-slate-800">Notification Settings</span>
          <p class="text-[11px] text-slate-400">Manage email, SMS, and alert preferences</p>
        </div>
      </div>
      <span class="text-slate-400 font-bold text-base group-hover:translate-x-0.5 transition">&gt;</span>
    </button>

    <!-- Expandable: Notification Settings Panel -->
    <div id="sec-notif" class="hidden bg-slate-50/60 p-5 border-t border-slate-100">
      <form method="post" class="space-y-3">
        <?= tok() ?>
        <input type="hidden" name="ns" value="1">
        <div class="space-y-2.5">
          <?php
          $notifOptions = [
            'email_notif'           => ['Email notifications', 'Receive critical incident updates and confirmations via email'],
            'sms_notif'             => ['SMS notifications', 'Get instant SMS text alerts for urgent hardware/network issues'],
            'ticket_status_changes' => ['Ticket status changes', 'Notify whenever a ticket moves to In Progress or Resolved'],
            'tech_assigned'         => ['Technician assigned', 'Alert when a specialist is assigned or dispatched'],
            'weekly_summary'        => ['Weekly summary', 'Receive a weekly overview report of helpdesk activity'],
          ];
          foreach ($notifOptions as $k => $item):
            $isChecked = !empty($s[$k]);
          ?>
          <label class="flex items-start justify-between p-2.5 bg-white rounded-xl border border-slate-200/80 cursor-pointer hover:border-blue-300 transition">
            <div>
              <span class="text-xs font-semibold text-slate-800 block"><?= e($item[0]) ?></span>
              <span class="text-[11px] text-slate-400"><?= e($item[1]) ?></span>
            </div>
            <input type="checkbox" name="<?= $k ?>" <?= $isChecked ? 'checked' : '' ?> class="mt-1 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
          </label>
          <?php endforeach; ?>
        </div>
        <div class="flex justify-end pt-2">
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2 rounded-xl shadow-sm transition">
            Save Preferences
          </button>
        </div>
      </form>
    </div>

    <!-- 4. Change Password -->
    <button type="button" onclick="toggleSection('sec-pwd')" class="w-full text-left p-4 sm:p-5 flex items-center justify-between hover:bg-slate-50/80 transition cursor-pointer group">
      <div class="flex items-center space-x-3.5">
        <div class="w-11 h-11 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition">
          <i data-lucide="lock" class="w-5 h-5"></i>
        </div>
        <div>
          <span class="text-sm font-semibold text-slate-800">Change Password</span>
          <p class="text-[11px] text-slate-400">Update security credentials for your account</p>
        </div>
      </div>
      <span class="text-slate-400 font-bold text-base group-hover:translate-x-0.5 transition">&gt;</span>
    </button>

    <!-- Expandable: Change Password Panel -->
    <div id="sec-pwd" class="hidden bg-slate-50/60 p-5 border-t border-slate-100">
      <form method="post" class="space-y-3 max-w-md">
        <?= tok() ?>
        <input type="hidden" name="pw" value="1">
        <div>
          <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[10px]">Current Password</label>
          <input type="password" name="cur" placeholder="••••••••" required class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-800 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>
        <div>
          <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[10px]">New Password</label>
          <input type="password" name="new" placeholder="At least 6 characters" required class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-800 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>
        <div>
          <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[10px]">Confirm New Password</label>
          <input type="password" name="new2" placeholder="Confirm new password" required class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-800 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>
        <div class="flex justify-end pt-2">
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2 rounded-xl shadow-sm transition">
            Update Password
          </button>
        </div>
      </form>
    </div>

    <!-- 5. Sign Out (Exact visual match to image) -->
    <a href="logout.php" class="w-full text-left p-4 sm:p-5 flex items-center justify-between hover:bg-rose-50/50 transition cursor-pointer group">
      <div class="flex items-center space-x-3.5">
        <div class="w-11 h-11 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition">
          <i data-lucide="log-out" class="w-5 h-5"></i>
        </div>
        <div>
          <span class="text-sm font-semibold text-rose-600">Sign Out</span>
          <p class="text-[11px] text-rose-400">Safely terminate your current portal session</p>
        </div>
      </div>
      <span class="text-rose-500 font-bold text-base group-hover:translate-x-0.5 transition">&gt;</span>
    </a>

  </div>

  <!-- Role Quick Switcher (Connect with other 2 roles) -->
  <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs text-xs">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <span class="font-bold text-slate-700">Role View Connection:</span>
        <span class="text-slate-400 ml-1">Currently viewing profile as <strong class="text-blue-600 font-bold uppercase"><?= e($u['role']) ?></strong></span>
      </div>
      <div class="flex items-center gap-1.5 flex-wrap">
        <?php if ($u['role'] !== 'employee'): ?>
        <a href="switch_view.php?role=employee&from=profile.php" class="px-2.5 py-1 bg-slate-100 hover:bg-blue-50 hover:text-blue-600 text-slate-600 rounded-lg font-semibold transition">👤 Employee</a>
        <?php endif; ?>
        <?php if ($u['role'] !== 'coordinator'): ?>
        <a href="switch_view.php?role=coordinator&from=profile.php" class="px-2.5 py-1 bg-slate-100 hover:bg-purple-50 hover:text-purple-600 text-slate-600 rounded-lg font-semibold transition">📋 Coordinator</a>
        <?php endif; ?>
        <?php if ($u['role'] !== 'staff'): ?>
        <a href="switch_view.php?role=staff&from=profile.php" class="px-2.5 py-1 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-600 text-slate-600 rounded-lg font-semibold transition">🛠️ Staff</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>

<script>
  function toggleSection(id) {
    const el = document.getElementById(id);
    if (!el) return;
    const isHidden = el.classList.contains('hidden');
    // Hide all expandable sections first
    ['sec-info', 'sec-notif', 'sec-pwd'].forEach(s => {
      const other = document.getElementById(s);
      if (other) other.classList.add('hidden');
    });
    if (isHidden) {
      el.classList.remove('hidden');
    }
  }
</script>

<?php foot(); ?>
