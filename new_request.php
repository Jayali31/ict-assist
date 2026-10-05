<?php require 'config.php'; need('employee'); check(); $u = me();
$info = q("SELECT u.department_id, u.branch_id, d.name dept, b.name branch FROM users u LEFT JOIN departments d ON d.id=u.department_id LEFT JOIN branches b ON b.id=u.branch_id WHERE u.id=?", [$u['id']])->fetch();
$cats = q("SELECT * FROM categories")->fetchAll(); $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? ''); $desc = trim($_POST['description'] ?? '');
  $cat = (int)($_POST['category'] ?? 0); $pri = $_POST['priority'] ?? 'Medium'; $file = null;
  if (!$title || !$desc || !$cat || !in_array($pri, ['High','Medium','Low'])) $err = 'Please fill all fields.';
  if (!$err && !empty($_FILES['file']['name'])) {
    $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','gif','pdf']) || $_FILES['file']['size'] > 5*1024*1024) $err = 'Attachment must be jpg/png/gif/pdf under 5MB.';
    else { $file = bin2hex(random_bytes(8)).'.'.$ext; move_uploaded_file($_FILES['file']['tmp_name'], __DIR__.'/uploads/'.$file); }
  }
  if (!$err) {
    q("INSERT INTO tickets(title,description,category_id,priority,department_id,branch_id,created_by,attachment) VALUES(?,?,?,?,?,?,?,?)",
      [$title,$desc,$cat,$pri,$info['department_id'],$info['branch_id'],$u['id'],$file]);
    $id = $pdo->lastInsertId();
    q("INSERT INTO ticket_updates(ticket_id,author_id,note) VALUES(?,?,?)", [$id,$u['id'],'Ticket submitted by '.$u['name']]);
    foreach (q("SELECT id FROM users WHERE role='coordinator'")->fetchAll() as $c)
      notify($c['id'], $id, "New ticket #TX-$id", "$title ($pri priority)", $pri=='High' ? 'warning' : 'info');
    header("Location: ticket.php?id=$id"); exit;
  }
}
head('New Request'); ?>
<form method="post" enctype="multipart/form-data" class="card narrow"><?= tok() ?>
  <?php if ($err) echo '<p class="err">'.e($err).'</p>'; ?>
  <label>Branch</label><input value="<?= e($info['branch']) ?>" disabled>
  <label>Department</label><input value="<?= e($info['dept']) ?>" disabled>
  <label>Title</label><input name="title" required maxlength="200">
  <label>Category</label><select name="category"><?php foreach ($cats as $c) echo '<option value="'.$c['id'].'">'.e($c['name']).'</option>'; ?></select>
  <label>Priority</label><select name="priority"><option>High</option><option selected>Medium</option><option>Low</option></select>
  <label>Description</label><textarea name="description" rows="5" required></textarea>
  <label>Attachment (optional)</label><input type="file" name="file">
  <button>Submit Request</button>
</form>
<?php foot();
