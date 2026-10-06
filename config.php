<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$pdo = new PDO('mysql:host=localhost;dbname=ict_assist;charset=utf8mb4', 'root', '', [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES); }
function q($sql, $p = []){ global $pdo; $s = $pdo->prepare($sql); $s->execute($p); return $s; }
function me(){ return $_SESSION['u'] ?? null; }
function need($role = null){
  if (!me()) { header('Location: login.html'); exit; }
  if ($role && me()['role'] !== $role) { http_response_code(403); exit('Forbidden'); }
}
function csrf(){ return $_SESSION['t'] ?? ($_SESSION['t'] = bin2hex(random_bytes(16))); }
function check(){
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['t'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
           || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
           || isset($_POST['ajax']);
    if ($isAjax && me()) {
      return;
    }
    if (!hash_equals(csrf(), (string)$token)) {
      exit('Bad token');
    }
  }
}
function tok(){ return '<input type="hidden" name="t" value="'.csrf().'">'; }
function notify($uid, $tid, $title, $sub, $type = 'info'){
  q("INSERT INTO notifications(user_id,ticket_id,title,subtitle,type) VALUES(?,?,?,?,?)", [$uid,$tid,$title,$sub,$type]);
}
function badge($s){
  $map = [
    'pending' => 'bg-amber-50 text-amber-700 border border-amber-200',
    'inprogress' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    'resolved' => 'bg-blue-50 text-blue-700 border border-blue-200',
    'high' => 'bg-rose-50 text-rose-700 border border-rose-200',
    'medium' => 'bg-amber-50 text-amber-700 border border-amber-200',
    'low' => 'bg-slate-100 text-slate-600 border border-slate-200',
  ];
  $k = strtolower(str_replace(' ','',$s));
  $cls = $map[$k] ?? 'bg-slate-100 text-slate-600 border border-slate-200';
  return '<span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-semibold '.$cls.'">'.e($s).'</span>';
}

function initials($name){
  $parts = preg_split('/[\s\._-]+/', trim((string)$name));
  $in = '';
  foreach ($parts as $p) {
    if ($p !== '') $in .= strtoupper($p[0]);
    if (strlen($in) >= 2) break;
  }
  return $in ?: 'US';
}

function head($t){
  $u = me();
  $n = 0;
  if ($u) {
    $n = (int)q("SELECT COUNT(*) c FROM notifications WHERE user_id=? AND is_read=0", [$u['id']])->fetch()['c'];
  }
  $initials = $u ? initials($u['name']) : 'US';
  $cur = basename($_SERVER['PHP_SELF'] ?? '');

  echo '<!DOCTYPE html>';
  echo '<html lang="en">';
  echo '<head>';
  echo '<meta charset="UTF-8">';
  echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
  echo '<title>'.e($t).' - ICT Assist</title>';
  echo '<script src="https://cdn.tailwindcss.com"></script>';
  echo '<script src="https://unpkg.com/lucide@latest"></script>';
  echo '<script src="shared.js"></script>';
  echo '<link rel="stylesheet" href="assets/style.css">';
  echo '</head>';
  echo '<body class="bg-slate-100 flex min-h-screen text-slate-800">';

  if ($u) {
    $role = $u['role'];
    $roleDisplay = ucfirst($role);

    $isDash = in_array($cur, ['dashboard.php', 'employee-dashboard.html', 'coordinator-dashboard.html', 'staff-dashboard.html']);
    $isNewReq = in_array($cur, ['new_request.php', 'new-request.html']);
    $isTicket = in_array($cur, ['ticket_status.php', 'ticket-status.html', 'ticket.php']);
    $isNotif = in_array($cur, ['notifications.php', 'notifications.html']);
    $isProfile = in_array($cur, ['profile.php', 'profile.html']);
    $isReports = in_array($cur, ['reports.php']);

    $activeColor = $role === 'coordinator' ? 'bg-purple-600' : ($role === 'staff' ? 'bg-emerald-600' : 'bg-blue-600');
    $logoColor = $role === 'coordinator' ? 'bg-purple-600' : ($role === 'staff' ? 'bg-emerald-600' : 'bg-blue-600');
    $portalSubtitle = $role === 'coordinator' ? 'Coordinator Dispatch' : ($role === 'staff' ? 'Technician Console' : 'Employee Portal');

    $linkActive = 'flex items-center space-x-3 px-3 py-2.5 rounded-xl '.$activeColor.' text-white font-semibold text-xs shadow-xs';
    $linkInactive = 'flex items-center space-x-3 px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 text-xs font-semibold transition';

    echo '<!-- Left Navigation Sidebar -->';
    echo '<aside class="w-64 bg-[#111827] text-slate-300 min-h-screen flex flex-col justify-between p-4 border-r border-slate-800 shrink-0 select-none">';
    echo '  <div>';
    echo '    <div class="p-3 border-b border-slate-800 flex items-center space-x-3 mb-4">';
    echo '      <div class="w-9 h-9 rounded-xl '.$logoColor.' flex items-center justify-center text-white shadow-md shadow-blue-500/20"><i data-lucide="monitor" class="w-5 h-5"></i></div>';
    echo '      <div>';
    echo '        <h1 class="text-sm font-bold text-white tracking-tight">ICT Assist</h1>';
    echo '        <p class="text-[11px] text-slate-400">'.e($portalSubtitle).'</p>';
    echo '      </div>';
    echo '    </div>';

    echo '    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider px-3 mb-1">'.e($roleDisplay).'</p>';
    echo '    <nav class="space-y-1 text-xs font-semibold mb-4">';
    echo '      <a href="dashboard.php" class="'.($isDash ? $linkActive : $linkInactive).'"><i data-lucide="layout-dashboard" class="w-4 h-4"></i><span>Dashboard</span></a>';
    
    if ($role === 'employee') {
      echo '      <a href="new_request.php" class="'.($isNewReq ? $linkActive : $linkInactive).'"><i data-lucide="plus-circle" class="w-4 h-4"></i><span>New Request</span></a>';
      echo '      <a href="ticket_status.php" class="'.($isTicket ? $linkActive : $linkInactive).'"><i data-lucide="clock" class="w-4 h-4"></i><span>Ticket Status</span></a>';
    } elseif ($role === 'coordinator') {
      echo '      <a href="ticket_status.php" class="'.($isTicket ? $linkActive : $linkInactive).'"><i data-lucide="clock" class="w-4 h-4"></i><span>All Tickets</span></a>';
      echo '      <a href="reports.php" class="'.($isReports ? $linkActive : $linkInactive).'"><i data-lucide="bar-chart-3" class="w-4 h-4"></i><span>Reports & Stats</span></a>';
    } elseif ($role === 'staff') {
      echo '      <a href="ticket_status.php" class="'.($isTicket ? $linkActive : $linkInactive).'"><i data-lucide="wrench" class="w-4 h-4"></i><span>Assigned Tasks</span></a>';
    }
    echo '    </nav>';

    echo '    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider px-3 mb-1">General</p>';
    echo '    <nav class="space-y-1 text-xs font-semibold">';
    echo '      <a href="notifications.php" class="'.($isNotif ? $linkActive : $linkInactive).'"><i data-lucide="bell" class="w-4 h-4"></i><span>Notifications</span>'.($n ? '<span class="ml-auto bg-rose-500 text-white text-[10px] px-1.5 py-0.5 rounded-full font-bold">'.$n.'</span>' : '').'</a>';
    echo '      <a href="profile.php" class="'.($isProfile ? $linkActive : $linkInactive).'"><i data-lucide="user" class="w-4 h-4"></i><span>Profile</span></a>';
    echo '    </nav>';
    echo '  </div>';

    echo '  <!-- Bottom Switcher -->';
    echo '  <div class="space-y-2 pt-4 border-t border-slate-800 text-xs">';
    echo '    <p class="text-[11px] text-slate-500">Switch View:</p>';
    echo '    <div class="grid grid-cols-2 gap-1">';
    if ($role === 'employee') {
      echo '      <a href="switch_view.php?role=coordinator" class="p-1.5 bg-slate-800 text-center rounded hover:bg-blue-600 text-white text-[11px] transition">Coordinator</a>';
      echo '      <a href="switch_view.php?role=staff" class="p-1.5 bg-slate-800 text-center rounded hover:bg-blue-600 text-white text-[11px] transition">Staff</a>';
    } elseif ($role === 'coordinator') {
      echo '      <a href="switch_view.php?role=employee" class="p-1.5 bg-slate-800 text-center rounded hover:bg-blue-600 text-white text-[11px] transition">Employee</a>';
      echo '      <a href="switch_view.php?role=staff" class="p-1.5 bg-slate-800 text-center rounded hover:bg-blue-600 text-white text-[11px] transition">Staff</a>';
    } else {
      echo '      <a href="switch_view.php?role=employee" class="p-1.5 bg-slate-800 text-center rounded hover:bg-blue-600 text-white text-[11px] transition">Employee</a>';
      echo '      <a href="switch_view.php?role=coordinator" class="p-1.5 bg-slate-800 text-center rounded hover:bg-blue-600 text-white text-[11px] transition">Coordinator</a>';
    }
    echo '    </div>';
    echo '    <a href="logout.php" class="block p-2 text-center text-rose-400 hover:bg-rose-500/10 rounded-xl transition font-medium">Sign Out</a>';
    echo '  </div>';
    echo '</aside>';

    echo '<!-- Main Layout Container -->';
    echo '<div class="flex-1 flex flex-col min-w-0 min-h-screen">';
    echo '  <!-- Top Header Bar -->';
    echo '  <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 md:px-8 sticky top-0 z-20">';
    echo '    <h2 class="text-sm font-bold text-slate-800">'.e($t).'</h2>';
    echo '    <div class="flex items-center space-x-4">';
    echo '      <a href="notifications.php" class="relative text-slate-400 hover:text-slate-600 transition" title="Notifications">';
    echo '        <i data-lucide="bell" class="w-5 h-5"></i>';
    if ($n > 0) {
      echo '        <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-rose-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center">'.$n.'</span>';
    }
    echo '      </a>';
    echo '      <a href="profile.php" class="flex items-center space-x-2 text-slate-700 hover:text-blue-600 transition">';
    echo '        <div class="w-8 h-8 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center shadow-xs">'.e($initials).'</div>';
    echo '        <span class="text-xs font-semibold hidden sm:inline">'.e($u['name']).'</span>';
    echo '      </a>';
    echo '    </div>';
    echo '  </header>';
  }

  echo '  <main class="flex-1 p-6 md:p-8 max-w-5xl mx-auto w-full space-y-6">';
}

function foot(){
  echo '  </main>';
  echo '</div>';
  echo '<script>if(window.lucide) lucide.createIcons();</script>';
  echo '</body></html>';
}

function ticketsTable($rows){
  if (!$rows) {
    echo '<div class="bg-white p-6 rounded-2xl border border-slate-200 text-center text-xs text-slate-400 shadow-xs">No tickets found.</div>';
    return;
  }
  echo '<div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">';
  echo '  <div class="overflow-x-auto">';
  echo '    <table class="w-full text-left text-xs border-collapse">';
  echo '      <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">';
  echo '        <tr>';
  echo '          <th class="py-3 px-4">Ticket</th>';
  echo '          <th class="py-3 px-4">Title</th>';
  echo '          <th class="py-3 px-4">Category</th>';
  echo '          <th class="py-3 px-4">Priority</th>';
  echo '          <th class="py-3 px-4">Status</th>';
  echo '          <th class="py-3 px-4">Assigned To</th>';
  echo '          <th class="py-3 px-4">Date</th>';
  echo '        </tr>';
  echo '      </thead>';
  echo '      <tbody class="divide-y divide-slate-100">';
  foreach ($rows as $r) {
    echo '      <tr class="hover:bg-slate-50/80 transition cursor-pointer" onclick="window.location.href=\'ticket.php?id='.$r['id'].'\'">';
    echo '        <td class="py-3.5 px-4 font-bold text-blue-600"><a href="ticket.php?id='.$r['id'].'" class="hover:underline">'.e($r['ticket_no']).'</a></td>';
    echo '        <td class="py-3.5 px-4 font-semibold text-slate-800"><a href="ticket.php?id='.$r['id'].'" class="hover:text-blue-600">'.e($r['title']).'</a></td>';
    echo '        <td class="py-3.5 px-4 text-slate-600">'.e($r['category']).'</td>';
    echo '        <td class="py-3.5 px-4">'.badge($r['priority']).'</td>';
    echo '        <td class="py-3.5 px-4">'.badge($r['status']).'</td>';
    echo '        <td class="py-3.5 px-4 text-slate-500">'.e($r['assigned_to'] ?: '—').'</td>';
    echo '        <td class="py-3.5 px-4 text-slate-400">'.e(substr($r['created_at'], 0, 10)).'</td>';
    echo '      </tr>';
  }
  echo '      </tbody>';
  echo '    </table>';
  echo '  </div>';
  echo '</div>';
}
