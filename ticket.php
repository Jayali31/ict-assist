<?php require 'config.php'; need(); check(); $u = me(); $id = (int)($_GET['id'] ?? 0);
$t = q("SELECT v.*, t.created_by uid, t.assigned_tech_id FROM v_tickets v JOIN tickets t ON t.id=v.id WHERE v.id=?", [$id])->fetch();
if (!$t) exit('Ticket not found');
$myTech = $u['role']=='staff' ? q("SELECT id FROM technicians WHERE user_id=?", [$u['id']])->fetch()['id'] : null;
if (($u['role']=='employee' && $t['uid'] != $u['id']) || ($u['role']=='staff' && $t['assigned_tech_id'] != $myTech)) { http_response_code(403); exit('Forbidden'); }
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($u['role']=='coordinator' && isset($_POST['tech_id']) && $t['status']!='Resolved') {
    $tc = q("SELECT te.id, te.user_id, us.name FROM technicians te JOIN users us ON us.id=te.user_id WHERE te.id=?", [(int)$_POST['tech_id']])->fetch();
    if ($tc) {
      q("UPDATE tickets SET assigned_tech_id=?, status='In Progress' WHERE id=?", [$tc['id'],$id]);
      q("INSERT INTO ticket_updates(ticket_id,author_id,note,new_status) VALUES(?,?,?,'In Progress')", [$id,$u['id'],'Assigned to '.$tc['name']]);
      notify($t['uid'], $id, "Technician assigned to your ticket #TX-$id", $tc['name'].' will attend to your request.');
      notify($tc['user_id'], $id, "New task #TX-$id", $t['title'], $t['priority']=='High' ? 'warning' : 'info');
    }
  }
  if ($u['role']=='staff' && in_array($_POST['status'] ?? '', ['In Progress','Resolved'])) {
    $st = $_POST['status']; $note = trim($_POST['note'] ?? '') ?: "Status changed to $st";
    q("UPDATE tickets SET status=?, resolved_at=IF(?='Resolved',NOW(),NULL) WHERE id=?", [$st,$st,$id]);
    q("INSERT INTO ticket_updates(ticket_id,author_id,note,new_status) VALUES(?,?,?,?)", [$id,$u['id'],$note,$st]);
    notify($t['uid'], $id, "Status update on #TX-$id: $st", $note, $st=='Resolved' ? 'success' : 'info');
  }
  header("Location: ticket.php?id=$id"); exit;
}
$t = q("SELECT v.*, t.created_by uid, t.assigned_tech_id FROM v_tickets v JOIN tickets t ON t.id=v.id WHERE v.id=?", [$id])->fetch();
$log = q("SELECT tu.*, u.name FROM ticket_updates tu LEFT JOIN users u ON u.id=tu.author_id WHERE ticket_id=? ORDER BY tu.id", [$id])->fetchAll();
head($t['ticket_no'].' - '.$t['title']); ?>
<div class="card">
  <p><?= badge($t['status']) ?> <?= badge($t['priority']) ?> <?= e($t['category']) ?></p>
  <p><?= nl2br(e($t['description'])) ?></p>
  <p class="muted">Raised by <?= e($t['created_by']) ?> · <?= e($t['department']) ?> · <?= e($t['branch']) ?> · <?= e($t['created_at']) ?><br>Assigned to: <?= e($t['assigned_to'] ?: 'Not assigned') ?></p>
  <?php if ($t['attachment']) echo '<p><a href="uploads/'.e($t['attachment']).'" target="_blank">View attachment</a></p>'; ?>
</div>
<h2>Timeline</h2><ul class="log"><?php foreach ($log as $l) echo '<li>'.e($l['note']).' <span class="muted">— '.e($l['name'] ?: 'System').', '.e($l['created_at']).'</span></li>'; ?></ul>
<?php if ($u['role']=='coordinator' && $t['status']!='Resolved'): $techs = q("SELECT * FROM v_technician_stats")->fetchAll(); ?>
<form method="post" class="card narrow"><?= tok() ?><h2>Assign Technician</h2>
  <select name="tech_id"><?php foreach ($techs as $x) echo '<option value="'.$x['technician_id'].'"'.($x['specialization']==$t['category']?' selected':'').'>'.e($x['name'].' - '.$x['specialization'].' ('.$x['status'].', '.$x['active_count'].' active)').'</option>'; ?></select>
  <button>Assign</button></form>
<?php endif; if ($u['role']=='staff' && $t['status']!='Resolved'): ?>
<form method="post" class="card narrow"><?= tok() ?><h2>Update Progress</h2>
  <select name="status"><option>In Progress</option><option>Resolved</option></select>
  <textarea name="note" rows="3" placeholder="Diagnostic notes"></textarea><button>Update</button></form>
<?php endif; foot();
