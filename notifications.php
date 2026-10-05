<?php require 'config.php'; need(); check(); $u = me();
if (isset($_POST['all'])) { q("UPDATE notifications SET is_read=1 WHERE user_id=?", [$u['id']]); header('Location: notifications.php'); exit; }
$rows = q("SELECT * FROM notifications WHERE user_id=? ORDER BY id DESC LIMIT 100", [$u['id']])->fetchAll();
head('Notifications'); ?>
<form method="post" class="inline"><?= tok() ?><button name="all" value="1">Mark all as read</button></form>
<?php foreach ($rows as $n) echo '<div class="card n '.e($n['type']).($n['is_read']?' read':'').'"><b>'.e($n['title']).'</b><br>'.e($n['subtitle']).' <span class="muted">'.e($n['created_at']).'</span>'.($n['ticket_id'] ? ' <a href="ticket.php?id='.$n['ticket_id'].'">View</a>' : '').'</div>';
if (!$rows) echo '<p class="muted">No notifications.</p>'; foot();
