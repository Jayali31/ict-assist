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
    'created_at' => substr($r['created_at'], 0, 10)
  ];
}

head('Ticket Status');
?>

<div class="space-y-4">
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
    <div>
      <h2 class="text-lg font-bold text-slate-900">Incident Ticket Status</h2>
      <p class="text-xs text-slate-500">Track and filter the current status of all support incidents.</p>
    </div>
    <div class="flex gap-1 text-xs bg-slate-200 p-1 rounded-xl" id="filter-tabs">
      <button type="button" onclick="setFilter('All')" id="btn-All" class="px-3 py-1 rounded-lg bg-white font-semibold text-blue-600 shadow-xs transition">All (<?= count($ticketsData) ?>)</button>
      <button type="button" onclick="setFilter('Pending')" id="btn-Pending" class="px-3 py-1 rounded-lg text-slate-600 hover:text-slate-900 transition">Pending</button>
      <button type="button" onclick="setFilter('In Progress')" id="btn-InProgress" class="px-3 py-1 rounded-lg text-slate-600 hover:text-slate-900 transition">In Progress</button>
      <button type="button" onclick="setFilter('Resolved')" id="btn-Resolved" class="px-3 py-1 rounded-lg text-slate-600 hover:text-slate-900 transition">Resolved</button>
    </div>
  </div>

  <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-xs">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 uppercase tracking-wider text-[10px]">
          <tr>
            <th class="py-3 px-6">ID</th>
            <th class="py-3 px-4">Title</th>
            <th class="py-3 px-4">Category</th>
            <th class="py-3 px-4">Priority</th>
            <th class="py-3 px-4">Status</th>
            <th class="py-3 px-4">Assigned Tech</th>
          </tr>
        </thead>
        <tbody id="table-body" class="divide-y divide-slate-100"></tbody>
      </table>
    </div>
  </div>
</div>

<script>
  const allTickets = <?= json_encode($ticketsData) ?>;

  function renderTable(filter = 'All') {
    const list = filter === 'All' ? allTickets : allTickets.filter(t => t.status === filter);
    const tbody = document.getElementById('table-body');
    
    if (list.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" class="py-8 text-center text-xs text-slate-400">No tickets found for "${filter}".</td></tr>`;
      return;
    }

    tbody.innerHTML = list.map(t => `
      <tr class="hover:bg-slate-50 transition cursor-pointer" onclick="window.location.href='ticket.php?id=${t.id}'">
        <td class="py-3.5 px-6 font-mono font-bold text-blue-600 hover:underline">#${escapeHtml(t.ticket_no)}</td>
        <td class="py-3.5 px-4 font-semibold text-slate-800">
          <a href="ticket.php?id=${t.id}" class="hover:text-blue-600">${escapeHtml(t.title)}</a>
          <div class="text-[10px] text-slate-400">${escapeHtml(t.branch)} · ${t.created_at}</div>
        </td>
        <td class="py-3.5 px-4 text-slate-500">${escapeHtml(t.category)}</td>
        <td class="py-3.5 px-4">
          <span class="px-2 py-0.5 rounded text-[10px] font-bold ${
            t.priority === 'High' ? 'bg-rose-50 text-rose-600 border border-rose-100' :
            t.priority === 'Medium' ? 'bg-amber-50 text-amber-700 border border-amber-100' :
            'bg-slate-100 text-slate-600 border border-slate-200'
          }">${escapeHtml(t.priority)}</span>
        </td>
        <td class="py-3.5 px-4">
          <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold ${
            t.status === 'Resolved' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' :
            t.status === 'In Progress' ? 'bg-amber-50 text-amber-700 border border-amber-100' :
            'bg-blue-50 text-blue-700 border border-blue-100'
          }">${escapeHtml(t.status)}</span>
        </td>
        <td class="py-3.5 px-4 text-slate-600">
          ${t.assignedTo ? escapeHtml(t.assignedTo) : '<span class="text-slate-400 italic">Unassigned</span>'}
        </td>
      </tr>
    `).join('');
  }

  function setFilter(filter) {
    const tabs = ['All', 'Pending', 'In Progress', 'Resolved'];
    tabs.forEach(tab => {
      const id = 'btn-' + tab.replace(' ', '');
      const btn = document.getElementById(id);
      if (btn) {
        if (tab === filter) {
          btn.className = 'px-3 py-1 rounded-lg bg-white font-semibold text-blue-600 shadow-xs transition';
        } else {
          btn.className = 'px-3 py-1 rounded-lg text-slate-600 hover:text-slate-900 transition';
        }
      }
    });
    renderTable(filter);
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  renderTable('All');
</script>

<?php foot(); ?>
