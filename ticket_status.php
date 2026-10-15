<?php
require 'config.php';
need();
$u = me();

if ($u['role'] === 'employee') {
  $rows = q("SELECT * FROM v_tickets WHERE created_by=(SELECT name FROM users WHERE id=?) ORDER BY id DESC", [$u['id']])->fetchAll();
} elseif ($u['role'] === 'staff') {
  $tech = q("SELECT id FROM technicians WHERE user_id=?", [$u['id']])->fetch();
  $rows = q("SELECT * FROM v_tickets WHERE assigned_tech_id=? ORDER BY id DESC", [$tech['id'] ?? 0])->fetchAll();
} else {
  $rows = q("SELECT * FROM v_tickets ORDER BY id DESC")->fetchAll();
}

$ticketsData = [];
foreach ($rows as $r) {
  $ticketsData[] = [
    'id'         => $r['id'],
    'ticket_no'  => $r['ticket_no'],
    'title'      => $r['title'],
    'category'   => $r['category'],
    'priority'   => $r['priority'],
    'status'     => $r['status'],
    'branch'     => $r['branch'] ?: 'Main Branch',
    'department' => $r['department'] ?: '',
    'assignedTo' => $r['assigned_to'] ?: '',
    'assignedPhoto' => $r['assigned_to_photo'] ?: '',
    'created_at' => substr($r['created_at'], 0, 10)
  ];
}

head('Ticket Status');
?>

<div class="space-y-4">
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  .ticket-status-page { font-family: 'Plus Jakarta Sans', sans-serif; }
  .ticket-status-page .font-head { font-family: 'Bricolage Grotesque', sans-serif; }
  @media (prefers-reduced-motion: reduce) {
    .ticket-status-page *, .ticket-status-page *::before, .ticket-status-page *::after { transition: none !important; }
  }
</style>

<div class="ticket-status-page">
  <div class="flex flex-wrap items-end justify-between gap-4">
    <div>
      <h2 class="font-head font-extrabold text-2xl md:text-3xl text-slate-900">Incident ticket status</h2>
      <p class="text-sm text-slate-500 mt-1">Track and filter the current status of all support incidents.</p>
    </div>
    <div id="tabs" class="flex flex-wrap gap-1 bg-white border border-slate-200 rounded-2xl p-1.5 shadow-sm" role="tablist" aria-label="Filter tickets by status"></div>
  </div>

  <div class="overflow-x-auto mt-4 -mx-2 px-2">
    <table class="w-full min-w-[920px] border-separate border-spacing-y-3 text-sm">
      <thead>
        <tr class="text-left text-xs font-semibold text-slate-400">
          <th class="px-5 pb-1 font-semibold">Ticket</th>
          <th class="px-3 pb-1 font-semibold">Issue</th>
          <th class="px-3 pb-1 font-semibold">Category</th>
          <th class="px-3 pb-1 font-semibold">Priority</th>
          <th class="px-3 pb-1 font-semibold w-48">Status</th>
          <th class="px-5 pb-1 font-semibold">Assigned tech</th>
        </tr>
      </thead>
      <tbody id="rows"></tbody>
    </table>
  </div>
  <div id="empty" class="hidden text-center py-14 bg-white rounded-3xl border border-dashed border-slate-300">
    <i data-lucide="inbox" class="w-10 h-10 mx-auto text-slate-300"></i>
    <p class="font-head font-bold mt-3">No tickets here</p>
    <p class="text-sm text-slate-500">Try another status.</p>
  </div>
</div>

<script>
const userRole = <?= json_encode($u['role']) ?>;
const tickets = <?= json_encode($ticketsData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const catIcon = { Printer: 'printer', Hardware: 'cpu', Network: 'wifi', Software: 'app-window', Telephone: 'phone' };
const priorities = {
  High: { hex: '#ef4444', pill: 'bg-red-50 text-red-700', dot: 'bg-red-500' },
  Medium: { hex: '#fbbf24', pill: 'bg-amber-50 text-amber-800', dot: 'bg-amber-400' },
  Low: { hex: '#22c55e', pill: 'bg-green-50 text-green-700', dot: 'bg-green-500' }
};
const statuses = {
  Pending: { pill: userRole === 'coordinator' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-blue-50 text-blue-700', fill: userRole === 'coordinator' ? 'bg-purple-600' : 'bg-blue-600', step: 1 },
  'In Progress': { pill: 'bg-amber-50 text-amber-800', fill: 'bg-amber-400', step: 3 },
  Resolved: { pill: 'bg-emerald-50 text-emerald-700', fill: 'bg-emerald-500', step: 4 }
};
const statusFilters = ['All', 'Pending', 'In Progress', 'Resolved'];
let activeFilter = 'All';

function escapeHtml(value) {
  return String(value ?? '').replace(/[&<>"']/g, character => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  })[character]);
}

function renderTabs() {
  const isCoord = userRole === 'coordinator';
  const isStaff = userRole === 'staff';
  const outlineClass = isCoord ? 'focus-visible:outline-purple-400' : (isStaff ? 'focus-visible:outline-emerald-400' : 'focus-visible:outline-blue-300');
  const badgeActive = isCoord ? 'text-purple-300' : (isStaff ? 'text-emerald-300' : 'text-blue-300');

  document.getElementById('tabs').innerHTML = statusFilters.map(status => {
    const count = status === 'All' ? tickets.length : tickets.filter(ticket => ticket.status === status).length;
    const selected = status === activeFilter;
    const activeBg = isCoord ? 'bg-purple-900 text-white shadow' : (isStaff ? 'bg-emerald-800 text-white shadow' : 'bg-slate-900 text-white shadow');
    return `<button type="button" role="tab" aria-selected="${selected}" data-filter="${status}" class="px-4 py-2 rounded-xl text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 ${outlineClass} ${selected ? activeBg : 'text-slate-500 hover:bg-slate-100'}">${status}<span class="ml-2 text-xs ${selected ? badgeActive : 'text-slate-400'}">${count}</span></button>`;
  }).join('');
}

function renderRows() {
  const isCoord = userRole === 'coordinator';
  const isStaff = userRole === 'staff';
  const rows = tickets.filter(ticket => activeFilter === 'All' || ticket.status === activeFilter);
  document.getElementById('empty').classList.toggle('hidden', rows.length > 0);
  document.getElementById('rows').innerHTML = rows.map(ticket => {
    const priority = priorities[ticket.priority] || priorities.Medium;
    const status = statuses[ticket.status] || statuses.Pending;
    const cellHover = isCoord ? 'group-hover:bg-purple-50/50' : (isStaff ? 'group-hover:bg-emerald-50/50' : 'group-hover:bg-blue-50/60');
    const cell = `bg-white py-4 border-y border-slate-200 ${cellHover} transition`;
    const progress = [1, 2, 3, 4].map(step =>
      `<span class="h-1.5 flex-1 rounded-full ${step <= status.step ? status.fill : 'bg-slate-200'}"></span>`
    ).join('');
    const ticketUrl = `ticket.php?id=${encodeURIComponent(ticket.id)}`;
    const badgeCls = isCoord 
      ? 'font-mono text-xs font-bold text-purple-700 bg-purple-50 px-2.5 py-1 rounded-lg hover:underline hover:bg-purple-100'
      : (isStaff ? 'font-mono text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg hover:underline hover:bg-emerald-100' : 'font-mono text-xs font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg hover:underline');
    const titleHoverCls = isCoord ? 'hover:text-purple-700' : (isStaff ? 'hover:text-emerald-700' : 'hover:text-blue-700');
    const avatarBg = isCoord ? 'bg-purple-900' : (isStaff ? 'bg-emerald-800' : 'bg-slate-900');
    const unassignedCls = isCoord ? 'text-purple-500 bg-purple-50/60 border border-purple-200' : (isStaff ? 'text-emerald-600 bg-emerald-50/60 border border-emerald-200' : 'text-slate-400 border border-dashed border-slate-300');

    const techAvatar = ticket.assignedPhoto
      ? `<img src="uploads/${escapeHtml(ticket.assignedPhoto)}" alt="${escapeHtml(ticket.assignedTo)}" class="w-8 h-8 rounded-full object-cover border border-slate-200 shrink-0">`
      : `<span class="w-8 h-8 rounded-full ${avatarBg} text-white text-[11px] font-bold grid place-items-center shrink-0">${escapeHtml(ticket.assignedTo.split(/[ .]+/).filter(Boolean).slice(0, 2).map(part => part[0]).join('').toUpperCase())}</span>`;

    const assigned = ticket.assignedTo
      ? `<span class="inline-flex items-center gap-2">${techAvatar}${escapeHtml(ticket.assignedTo)}</span>`
      : `<span class="inline-flex items-center gap-2 text-xs italic ${unassignedCls} rounded-full px-3 py-1.5"><i data-lucide="user-plus" class="w-3.5 h-3.5"></i>Unassigned</span>`;

    return `<tr class="group">
      <td class="${cell} pl-5 pr-3 rounded-l-2xl" style="border-left:5px solid ${priority.hex}">
        <a href="${ticketUrl}" class="${badgeCls}">#${escapeHtml(ticket.ticket_no)}</a></td>
      <td class="${cell} px-3">
        <a href="${ticketUrl}" class="font-semibold text-slate-900 ${titleHoverCls}">${escapeHtml(ticket.title)}</a>
        <p class="text-xs text-slate-400 mt-0.5">${escapeHtml(ticket.branch)} &middot; ${escapeHtml(ticket.created_at)}</p></td>
      <td class="${cell} px-3">
        <span class="inline-flex items-center gap-2 text-slate-600"><span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-500 grid place-items-center"><i data-lucide="${catIcon[ticket.category] || 'ticket'}" class="w-4 h-4"></i></span>${escapeHtml(ticket.category)}</span></td>
      <td class="${cell} px-3">
        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full ${priority.pill}"><span class="w-2 h-2 rounded-full ${priority.dot}"></span>${escapeHtml(ticket.priority)}</span></td>
      <td class="${cell} px-3">
        <span class="inline-block text-xs font-bold px-3 py-1 rounded-full ${status.pill}">${escapeHtml(ticket.status)}</span>
        <div class="flex gap-1 mt-2 w-32" aria-hidden="true">${progress}</div></td>
      <td class="${cell} pl-3 pr-5 rounded-r-2xl border-r border-slate-200">${assigned}</td>
    </tr>`;
  }).join('');
  if (window.lucide) lucide.createIcons();
}

function render() {
  renderTabs();
  renderRows();
}

document.getElementById('tabs').addEventListener('click', event => {
  const button = event.target.closest('[data-filter]');
  if (!button) return;
  activeFilter = button.dataset.filter;
  render();
});

render();
</script>

<?php foot(); ?>
