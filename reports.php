<?php
require 'config.php';
need('coordinator');

$cat = q("SELECT * FROM v_category_breakdown")->fetchAll();
$tech = q("SELECT * FROM v_technician_stats ORDER BY resolved_count DESC, active_count DESC")->fetchAll();
$tot = max(1, array_sum(array_column($cat, 'total')));

$colors = [
  'Hardware'  => 'bg-blue-600',
  'Network'   => 'bg-emerald-500',
  'Software'  => 'bg-amber-500',
  'Telephone' => 'bg-purple-500'
];

head('Reports & Stats');
?>

<div class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
      <h2 class="text-xl font-bold text-slate-900">Incident Analytics &amp; Performance Reports</h2>
      <p class="text-xs text-slate-500">Real-time statistics across incident categories and technician dispatch.</p>
    </div>
    <a href="dashboard.php" class="text-xs font-semibold text-purple-600 hover:text-purple-800 transition flex items-center gap-1 self-start sm:self-auto">
      <span>&larr; Back to Dashboard</span>
    </a>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Requests by Category Card -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-bold text-slate-800">Requests by Category</h3>
        <span class="text-xs text-slate-400 font-medium">Total: <strong><?= $tot ?></strong></span>
      </div>
      <div class="space-y-4 text-xs">
        <?php foreach ($cat as $c): 
          $color = $colors[$c['category']] ?? 'bg-blue-600';
          $pct = round(($c['total'] / $tot) * 100);
        ?>
        <div>
          <div class="flex justify-between mb-1.5 font-medium">
            <span class="text-slate-700"><?= e($c['category']) ?></span>
            <strong class="text-slate-900"><?= $c['total'] ?> Request<?= $c['total'] != 1 ? 's' : '' ?></strong>
          </div>
          <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
            <div class="<?= $color ?> h-full rounded-full transition-all duration-500" style="width: <?= max(5, $pct) ?>%"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Technician Performance Card -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-bold text-slate-800">Technician Performance</h3>
        <span class="text-xs text-slate-400 font-medium"><?= count($tech) ?> Technicians</span>
      </div>
      <div class="space-y-3 text-xs">
        <?php foreach ($tech as $t): ?>
        <div class="flex justify-between items-center p-3.5 bg-slate-50 hover:bg-slate-100/70 rounded-xl transition">
          <div>
            <span class="font-bold text-slate-800"><?= e($t['name']) ?></span>
            <span class="text-slate-400 font-normal ml-1">(<?= e($t['specialization']) ?>)</span>
            <span class="ml-2 text-[10px] px-2 py-0.5 rounded-full font-semibold <?= $t['status'] === 'Free' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' ?>">
              <?= e($t['status']) ?>
            </span>
          </div>
          <div class="text-right">
            <span class="font-bold text-emerald-600"><?= $t['resolved_count'] ?> Resolved</span>
            <span class="text-[11px] text-slate-400 ml-2"><?= $t['active_count'] ?> active</span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php foot(); ?>
