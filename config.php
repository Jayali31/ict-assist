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
  echo '<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">';
  echo '<script src="https://cdn.tailwindcss.com"></script>';
  echo '<script src="https://unpkg.com/lucide@latest"></script>';
  echo '<script src="shared.js"></script>';
  echo '<link rel="stylesheet" href="assets/style.css">';
  echo '<script>';
  echo 'tailwind.config = { theme: { extend: {';
  echo '  fontFamily: { sans: [\'"DM Sans"\', \'system-ui\', \'sans-serif\'], display: [\'"Space Grotesk"\', \'sans-serif\'] },';
  echo '  colors: { neon: \'#1D5BDB\', pink: \'#F59E0B\', lime: \'#059669\' }';
  echo '} } };';
  echo '</script>';
  echo '<style>';
  echo 'body { background: #F4F6FB; }';
  echo '.card { background: #fff; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px rgba(15,23,42,.06); }';
  echo '.pulse-dot { animation: pulse 2s ease-in-out infinite; }';
  echo '@keyframes pulse { 50% { opacity: .35; } }';
  echo '@media (prefers-reduced-motion: reduce) { .pulse-dot { animation: none; } }';
  echo '.reveal { opacity: 0; transform: translateY(14px); animation: up .6s cubic-bezier(.2,.7,.2,1) forwards; animation-delay: var(--d, 0s); }';
  echo '@keyframes up { to { opacity: 1; transform: none; } }';
  echo '.lift { transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease; }';
  echo '.lift:hover { transform: translateY(-4px); box-shadow: 0 12px 24px -8px rgba(29,91,219,.25); }';
  echo '.float-a { animation: floaty 5s ease-in-out infinite; }';
  echo '.float-b { animation: floaty 6.5s ease-in-out infinite reverse; }';
  echo '@keyframes floaty { 50% { transform: translateY(-8px); } }';
  echo '.ping-ring { animation: ping 2s cubic-bezier(0,0,.2,1) infinite; }';
  echo '@keyframes ping { 75%, 100% { transform: scale(1.8); opacity: 0; } }';
  echo '.sec-title { display: flex; align-items: center; gap: .6rem; font-family: \'Space Grotesk\', sans-serif; font-weight: 700; color: #0f172a; font-size: 1rem; margin-bottom: .75rem; }';
  echo '.sec-title::before { content: \'\'; width: 4px; height: 18px; border-radius: 4px; background: linear-gradient(#1D5BDB, #60A5FA); }';
  echo '@media (prefers-reduced-motion: reduce) { .reveal { opacity: 1; transform: none; animation: none; } .float-a, .float-b, .ping-ring { animation: none; } .lift:hover { transform: none; } }';
  echo '.flow { stroke-dasharray: 4 10; animation: flow 1.6s linear infinite; }';
  echo '@keyframes flow { to { stroke-dashoffset: -28; } }';
  echo '.orbit { position: absolute; left: 50%; top: 50%; width: 40px; height: 40px; margin: -20px 0 0 -20px; animation: orbit var(--t, 28s) linear infinite; }';
  echo '@keyframes orbit { from { transform: rotate(var(--a)) translateX(var(--r)) rotate(calc(-1 * var(--a))); } to { transform: rotate(calc(var(--a) + 360deg)) translateX(var(--r)) rotate(calc(-1 * (var(--a) + 360deg))); } }';
  echo '.spin-slow { transform-origin: 190px 160px; animation: spin 40s linear infinite; }';
  echo '@keyframes spin { to { transform: rotate(360deg); } }';
  echo '.chip-link { transition: all .2s ease; }';
  echo '.chip-link:hover { background: #fff; color: #1D5BDB; transform: translateY(-2px); }';
  echo '@media (prefers-reduced-motion: reduce) { .flow, .orbit, .spin-slow { animation: none; } .orbit { transform: rotate(var(--a)) translateX(var(--r)) rotate(calc(-1 * var(--a))); } }';
  echo 'summary::-webkit-details-marker { display: none; }';
  echo 'a:focus-visible, button:focus-visible, summary:focus-visible { outline: 2px solid #1D5BDB; outline-offset: 2px; }';
  echo '</style>';
  echo '</head>';
  echo '<body class="font-sans text-slate-700 min-h-screen lg:flex">';

  if ($u) {
    $role = $u['role'];
    $roleDisplay = ucfirst($role);

    $isDash = in_array($cur, ['dashboard.php', 'employee-dashboard.html', 'coordinator-dashboard.html', 'staff-dashboard.html']);
    $isNewReq = in_array($cur, ['new_request.php', 'new-request.html']);
    $isTicket = in_array($cur, ['ticket_status.php', 'ticket-status.html', 'ticket.php']);
    $isNotif = in_array($cur, ['notifications.php', 'notifications.html']);
    $isProfile = in_array($cur, ['profile.php', 'profile.html']);
    $isReports = in_array($cur, ['reports.php']);

    $activeBg = $role === 'coordinator' ? 'bg-purple-600 text-white' : ($role === 'staff' ? 'bg-emerald-600 text-white' : 'bg-neon text-white');
    $portalSubtitle = $role === 'coordinator' ? 'Coordinator Portal' : ($role === 'staff' ? 'Technician Portal' : 'Service Desk Portal');

    $linkActive = 'flex items-center gap-3 px-3 py-2.5 rounded-xl '.$activeBg.' font-medium text-sm shadow-xs';
    $linkInactive = 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-white/5 font-medium text-sm transition';

    echo '<!-- Left Navigation Sidebar -->';
    echo '<aside id="sidebar" class="hidden lg:flex fixed lg:sticky top-0 z-40 h-screen w-64 bg-[#0B1530] border-r border-white/10 flex-col justify-between p-4 shrink-0 overflow-y-auto select-none">';
    echo '  <div>';
    echo '    <div class="p-3 flex items-center gap-3 mb-6">';
    echo '      <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-white"><i data-lucide="monitor" class="w-5 h-5"></i></div>';
    echo '      <div>';
    echo '        <h1 class="font-display text-lg font-bold text-white leading-tight">ICT Assist</h1>';
    echo '        <p class="text-xs text-slate-400">'.e($portalSubtitle).'</p>';
    echo '      </div>';
    echo '    </div>';

    echo '    <p class="text-xs font-bold text-slate-400 px-3 mb-2">'.e($roleDisplay).'</p>';
    echo '    <nav class="space-y-1 text-sm font-medium mb-6">';
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

    echo '    <p class="text-xs font-bold text-slate-400 px-3 mb-2">General</p>';
    echo '    <nav class="space-y-1 text-sm font-medium">';
    echo '      <a href="notifications.php" class="'.($isNotif ? $linkActive : $linkInactive).'"><i data-lucide="bell" class="w-4 h-4"></i><span>Notifications</span>'.($n ? '<span class="ml-auto bg-red-600 text-white text-[11px] font-bold rounded-full px-1.5">'.$n.'</span>' : '').'</a>';
    echo '      <a href="profile.php" class="'.($isProfile ? $linkActive : $linkInactive).'"><i data-lucide="user" class="w-4 h-4"></i><span>Profile</span></a>';
    echo '    </nav>';
    echo '  </div>';

    echo '  <!-- Bottom Switcher -->';
    echo '  <div class="space-y-2 pt-4 border-t border-white/10 text-sm">';
    echo '    <p class="text-xs text-slate-400">Switch view</p>';
    echo '    <div class="grid grid-cols-2 gap-2">';
    if ($role === 'employee') {
      echo '      <a href="switch_view.php?role=coordinator" class="py-2 bg-white/10 text-center rounded-lg hover:bg-neon text-white text-xs font-medium transition">Coordinator</a>';
      echo '      <a href="switch_view.php?role=staff" class="py-2 bg-white/10 text-center rounded-lg hover:bg-neon text-white text-xs font-medium transition">Staff</a>';
    } elseif ($role === 'coordinator') {
      echo '      <a href="switch_view.php?role=employee" class="py-2 bg-white/10 text-center rounded-lg hover:bg-purple-600 text-white text-xs font-medium transition">Employee</a>';
      echo '      <a href="switch_view.php?role=staff" class="py-2 bg-white/10 text-center rounded-lg hover:bg-purple-600 text-white text-xs font-medium transition">Staff</a>';
    } else {
      echo '      <a href="switch_view.php?role=employee" class="py-2 bg-white/10 text-center rounded-lg hover:bg-emerald-600 text-white text-xs font-medium transition">Employee</a>';
      echo '      <a href="switch_view.php?role=coordinator" class="py-2 bg-white/10 text-center rounded-lg hover:bg-emerald-600 text-white text-xs font-medium transition">Coordinator</a>';
    }
    echo '    </div>';
    echo '    <a href="logout.php" class="flex items-center justify-center gap-2 p-2 text-rose-400 hover:bg-rose-500/10 rounded-xl transition font-medium"><i data-lucide="log-out" class="w-4 h-4"></i>Sign Out</a>';
    echo '  </div>';
    echo '</aside>';

    echo '<!-- Main Layout Container -->';
    echo '<div class="flex-1 flex flex-col min-w-0">';
    echo '  <!-- Top Header Bar -->';
    echo '  <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-8 sticky top-0 z-30">';
    echo '    <div class="flex items-center gap-3">';
    echo '      <button id="menuBtn" class="lg:hidden p-2 -ml-2 rounded-lg hover:bg-slate-100" aria-label="Open menu"><i data-lucide="menu" class="w-5 h-5"></i></button>';
    echo '      <h2 class="font-display text-base font-bold text-slate-900">'.e($t).'</h2>';
    echo '    </div>';
    echo '    <div class="flex items-center gap-4">';
    echo '      <span class="hidden sm:flex items-center gap-2 text-xs text-slate-400"><span class="pulse-dot w-2 h-2 rounded-full bg-emerald-500"></span>All systems running</span>';
    echo '      <a href="notifications.php" class="relative text-amber-500 hover:text-amber-600 transition" aria-label="Notifications">';
    echo '        <i data-lucide="bell" class="w-5 h-5"></i>';
    if ($n > 0) {
      echo '        <span class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-red-600 text-white text-[10px] font-bold rounded-full flex items-center justify-center">'.$n.'</span>';
    }
    echo '      </a>';
    echo '      <a href="profile.php" class="w-9 h-9 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center transition" aria-label="Profile">'.e($initials).'</a>';
    echo '    </div>';
    echo '  </header>';
  }

  echo '  <main class="flex-1 p-4 sm:p-8 max-w-6xl mx-auto w-full space-y-6">';
}

function foot(){
  echo '  </main>';
  echo '</div>';
  echo '<script>';
  echo 'if(window.lucide) lucide.createIcons();';
  echo 'const sb = document.getElementById("sidebar");';
  echo 'const mb = document.getElementById("menuBtn");';
  echo 'if (mb && sb) { mb.addEventListener("click", () => sb.classList.toggle("hidden")); }';
  echo '</script>';
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
