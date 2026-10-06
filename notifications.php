<?php
require_once 'config.php';
need();
check();
$u = me();

if (isset($_POST['all'])) {
  q("UPDATE notifications SET is_read=1 WHERE user_id=?", [$u['id']]);
  header('Location: notifications.php');
  exit;
}

$rows = q("SELECT * FROM notifications WHERE user_id=? ORDER BY id DESC LIMIT 100", [$u['id']])->fetchAll();
$unreadCount = 0;
foreach ($rows as $r) {
  if (!$r['is_read']) $unreadCount++;
}

head('Notifications');
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
  <div>
    <h2 class="text-xl font-extrabold text-slate-800">Your Notifications</h2>
    <p class="text-xs text-slate-500 mt-0.5">
      <?= count($rows) ?> total · <span class="font-bold <?= $unreadCount ? 'text-rose-600' : 'text-slate-500' ?>"><?= $unreadCount ?> unread</span>
    </p>
  </div>
  <?php if ($unreadCount > 0): ?>
  <form method="post" class="inline">
    <?= tok() ?>
    <button name="all" value="1" class="bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold px-3.5 py-2 rounded-xl border border-slate-200 shadow-xs transition flex items-center gap-2">
      <i data-lucide="check-check" class="w-4 h-4 text-emerald-600"></i>
      <span>Mark all as read</span>
    </button>
  </form>
  <?php endif; ?>
</div>

<?php if (!$rows): ?>
<div class="bg-white rounded-3xl border border-slate-200 p-12 text-center shadow-xs">
  <div class="w-14 h-14 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center mx-auto mb-3">
    <i data-lucide="bell-off" class="w-7 h-7"></i>
  </div>
  <h3 class="text-sm font-bold text-slate-700">No notifications yet</h3>
  <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">You're all caught up! Updates regarding tickets, tasks, assignments, and incident alerts will appear here.</p>
</div>
<?php else: ?>
<div class="space-y-3">
  <?php foreach ($rows as $n):
    $isRead = (bool)$n['is_read'];
    $type = $n['type'] ?? 'info';
    
    $borderCls = 'border-l-4 border-blue-500';
    $icon = 'info';
    $iconColor = 'text-blue-500 bg-blue-50';
    if ($type === 'warning') {
      $borderCls = 'border-l-4 border-amber-500';
      $icon = 'alert-triangle';
      $iconColor = 'text-amber-500 bg-amber-50';
    } elseif ($type === 'success') {
      $borderCls = 'border-l-4 border-emerald-500';
      $icon = 'check-circle';
      $iconColor = 'text-emerald-500 bg-emerald-50';
    }
  ?>
  <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 <?= $borderCls ?> shadow-xs flex items-start justify-between gap-4 <?= $isRead ? 'opacity-75 hover:opacity-100' : '' ?> transition">
    <div class="flex items-start space-x-3.5">
      <div class="w-9 h-9 rounded-xl <?= $iconColor ?> flex items-center justify-center shrink-0 mt-0.5">
        <i data-lucide="<?= $icon ?>" class="w-4 h-4"></i>
      </div>
      <div>
        <div class="flex items-center gap-2">
          <h4 class="text-xs font-bold text-slate-800"><?= e($n['title']) ?></h4>
          <?php if (!$isRead): ?>
          <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
          <?php endif; ?>
        </div>
        <p class="text-xs text-slate-600 mt-1 leading-relaxed"><?= e($n['subtitle']) ?></p>
        <span class="inline-block text-[11px] text-slate-400 mt-1.5"><?= e($n['created_at']) ?></span>
      </div>
    </div>
    <?php if (!empty($n['ticket_id'])): ?>
    <a href="ticket.php?id=<?= (int)$n['ticket_id'] ?>" class="shrink-0 text-xs font-semibold text-blue-600 border border-blue-200 px-3 py-1.5 rounded-lg hover:bg-blue-50 transition flex items-center gap-1">
      <span>View</span>
      <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
    </a>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php foot(); ?>
