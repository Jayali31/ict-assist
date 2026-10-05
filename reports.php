<?php require 'config.php'; need('coordinator');
$cat = q("SELECT * FROM v_category_breakdown")->fetchAll(); $tech = q("SELECT * FROM v_technician_stats ORDER BY resolved_count DESC")->fetchAll();
$tot = max(1, array_sum(array_column($cat, 'total')));
head('Reports'); ?>
<h2>Incidents by Category</h2>
<?php foreach ($cat as $c) echo '<div class="bar"><span>'.e($c['category']).' ('.$c['total'].')</span><i style="width:'.round($c['total']/$tot*100).'%"></i></div>'; ?>
<h2>Technician Performance</h2>
<table><tr><th>Technician</th><th>Specialization</th><th>Status</th><th>Active</th><th>Resolved</th></tr>
<?php foreach ($tech as $t) echo '<tr><td>'.e($t['name']).'</td><td>'.e($t['specialization']).'</td><td>'.e($t['status']).'</td><td>'.$t['active_count'].'</td><td>'.$t['resolved_count'].'</td></tr>'; ?></table>
<?php foot();
