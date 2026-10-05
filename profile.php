<?php require 'config.php'; need(); check(); $u = me(); $msg = '';
if (isset($_POST['pw'])) {
  $row = q("SELECT password_hash FROM users WHERE id=?", [$u['id']])->fetch();
  if (!password_verify($_POST['cur'], $row['password_hash'])) $msg = 'Current password is wrong.';
  elseif (strlen($_POST['new']) < 8 || $_POST['new'] !== $_POST['new2']) $msg = 'New password must be 8+ chars and match.';
  else { q("UPDATE users SET password_hash=? WHERE id=?", [password_hash($_POST['new'], PASSWORD_DEFAULT), $u['id']]); $msg = 'Password changed.'; }
}
if (isset($_POST['ns'])) {
  q("UPDATE notification_settings SET email_notif=?,sms_notif=?,ticket_status_changes=?,tech_assigned=?,weekly_summary=? WHERE user_id=?",
    [isset($_POST['email_notif']),isset($_POST['sms_notif']),isset($_POST['ticket_status_changes']),isset($_POST['tech_assigned']),isset($_POST['weekly_summary']),$u['id']]);
  $msg = 'Settings saved.';
}
$p = q("SELECT u.*, d.name dept, b.name branch FROM users u LEFT JOIN departments d ON d.id=u.department_id LEFT JOIN branches b ON b.id=u.branch_id WHERE u.id=?", [$u['id']])->fetch();
$s = q("SELECT * FROM notification_settings WHERE user_id=?", [$u['id']])->fetch();
head('Profile'); if ($msg) echo '<p class="ok">'.e($msg).'</p>'; ?>
<div class="card"><b><?= e($p['full_name']) ?></b> — <?= e($p['role_title']) ?><br><?= e($p['email']) ?> · <?= e($p['phone']) ?><br><?= e($p['dept']) ?> · <?= e($p['branch']) ?></div>
<form method="post" class="card narrow"><?= tok() ?><h2>Notification Settings</h2>
<?php foreach (['email_notif'=>'Email notifications','sms_notif'=>'SMS notifications','ticket_status_changes'=>'Ticket status changes','tech_assigned'=>'Technician assigned','weekly_summary'=>'Weekly summary'] as $k=>$l)
  echo '<label class="chk"><input type="checkbox" name="'.$k.'"'.($s[$k]?' checked':'').'> '.$l.'</label>'; ?>
<button name="ns" value="1">Save</button></form>
<form method="post" class="card narrow"><?= tok() ?><h2>Change Password</h2>
  <input type="password" name="cur" placeholder="Current password" required><input type="password" name="new" placeholder="New password" required><input type="password" name="new2" placeholder="Confirm new password" required>
  <button name="pw" value="1">Change</button></form>
<?php foot();
