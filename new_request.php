<?php
require 'config.php';
need('employee');
check();
$u = me();

$info = q("SELECT u.department_id, u.branch_id, d.name dept, b.name branch 
           FROM users u 
           LEFT JOIN departments d ON d.id=u.department_id 
           LEFT JOIN branches b ON b.id=u.branch_id 
           WHERE u.id=?", [$u['id']])->fetch();

$err = '';
$toast = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $rawCat = trim($_POST['category'] ?? 'Hardware');
  // Strip emojis if present, e.g. "🖨️ Printer" -> "Printer"
  $cleanCatName = trim(preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', '', $rawCat));
  if (empty($cleanCatName)) $cleanCatName = 'Hardware';

  $catMap = [
    'Printer'   => 1,
    'Hardware'  => 1,
    'Network'   => 2,
    'Software'  => 3,
    'Telephone' => 4
  ];

  $catId = (int)($_POST['category_id'] ?? 0);
  if (!$catId) {
    $catId = $catMap[$cleanCatName] ?? 1;
  }

  $pri = trim($_POST['priority'] ?? 'Medium');
  if (!in_array($pri, ['High', 'Medium', 'Low'])) {
    $pri = 'Medium';
  }

  $desc = trim($_POST['description'] ?? '');
  $title = trim($_POST['title'] ?? '');
  if (!$title && $desc) {
    $title = $cleanCatName . ' - ' . (strlen($desc) > 60 ? substr($desc, 0, 57) . '...' : $desc);
  }

  if (!$desc) {
    $err = 'Please enter a description for your request.';
  }

  $file = null;
  $uploadField = !empty($_FILES['photo']['name']) ? 'photo' : (!empty($_FILES['file']['name']) ? 'file' : null);
  if (!$err && $uploadField && !empty($_FILES[$uploadField]['name'])) {
    $ext = strtolower(pathinfo($_FILES[$uploadField]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf']) || $_FILES[$uploadField]['size'] > 10 * 1024 * 1024) {
      $err = 'Attachment must be an image (jpg/png/gif/webp) or PDF under 10MB.';
    } else {
      $file = bin2hex(random_bytes(8)) . '.' . $ext;
      move_uploaded_file($_FILES[$uploadField]['tmp_name'], __DIR__ . '/uploads/' . $file);
    }
  }

  if (!$err) {
    q("INSERT INTO tickets(title, description, category_id, priority, department_id, branch_id, created_by, attachment) 
       VALUES(?, ?, ?, ?, ?, ?, ?, ?)",
      [$title, $desc, $catId, $pri, $info['department_id'], $info['branch_id'], $u['id'], $file]);
    
    $id = $pdo->lastInsertId();
    q("INSERT INTO ticket_updates(ticket_id, author_id, note) VALUES(?, ?, ?)", 
      [$id, $u['id'], 'Ticket submitted by ' . $u['name']]);

    foreach (q("SELECT id FROM users WHERE role='coordinator'")->fetchAll() as $c) {
      notify($c['id'], $id, "New ticket #TX-$id", "$title ($pri priority)", $pri === 'High' ? 'warning' : 'info');
    }

    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
           || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
           || isset($_POST['ajax']);

    if ($isAjax) {
      header('Content-Type: application/json');
      echo json_encode([
        'success'   => true,
        'redirect'  => "ticket.php?id=$id",
        'ticket_id' => $id,
        'message'   => 'Request submitted successfully.'
      ]);
      exit;
    }

    header("Location: ticket.php?id=$id");
    exit;
  }
}

head('New Request');
?>

<style>
  :root {
    --blue: #1d4ed8;
    --blue-soft: #e8f0fe;
    --ink: #1e293b;
    --label: #475f82;
    --bg: #f5f6fa;
    --line: #e2e8f0;
    --red: #ef4444;
    --amber: #fbbf24;
    --green: #4ade80;
  }

  .nr-form {
    padding: 10px 0 40px;
    max-width: 940px;
  }
  .nr-form label, .nr-form .label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: var(--label);
    margin-bottom: 8px;
  }
  .nr-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 32px;
    margin-bottom: 24px;
    max-width: 640px;
  }
  .nr-field {
    width: 100%;
    height: 42px;
    padding: 0 18px;
    border: 1px solid var(--line);
    border-radius: 10px;
    background: #fff;
    font: inherit;
    font-size: 14px;
    color: var(--ink);
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  .nr-field:focus, .nr-form textarea:focus {
    outline: none;
    border-color: var(--blue);
    box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.15);
  }
  .nr-group {
    margin-bottom: 24px;
  }

  /* Chips & Priority */
  .chips, .prio {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }
  .chip {
    padding: 0 20px;
    height: 36px;
    border: 0;
    border-radius: 999px;
    background: var(--blue-soft);
    color: var(--blue);
    font: inherit;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
  }
  .chip:hover {
    background: #dbeafe;
  }
  .chip[aria-pressed="true"] {
    background: var(--blue) !important;
    color: #fff !important;
    box-shadow: 0 2px 4px rgba(29, 78, 216, 0.25);
  }
  
  .pbtn {
    width: 135px;
    height: 36px;
    border: 0;
    border-radius: 8px;
    font: inherit;
    font-size: 14px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.15s;
  }
  .pbtn i {
    width: 11px;
    height: 11px;
    border-radius: 50%;
    display: block;
  }
  .pbtn[data-p="High"] { background: #fee2e2; color: var(--red); }
  .pbtn[data-p="High"] i { background: var(--red); }
  .pbtn[data-p="Medium"] { background: #e2e8f0; color: #475569; }
  .pbtn[data-p="Medium"] i { background: var(--amber); }
  .pbtn[data-p="Low"] { background: #e2e8f0; color: #475569; }
  .pbtn[data-p="Low"] i { background: var(--green); }
  .pbtn[aria-pressed="true"] {
    background: var(--blue) !important;
    color: #fff !important;
    box-shadow: 0 2px 4px rgba(29, 78, 216, 0.25);
  }
  .pbtn[aria-pressed="true"] i {
    background: #fff !important;
  }

  .nr-form textarea {
    width: 100%;
    max-width: 640px;
    min-height: 85px;
    padding: 12px 18px;
    border: 1px solid var(--line);
    border-radius: 10px;
    background: #fff;
    font: inherit;
    font-size: 13px;
    color: var(--ink);
    resize: vertical;
    display: block;
    transition: border-color 0.15s;
  }

  /* Upload */
  .upload {
    width: 100%;
    max-width: 640px;
    height: 110px;
    border: 1px dashed #cbd5e1;
    border-radius: 10px;
    background: #fff;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    cursor: pointer;
    color: #94a3b8;
    font-size: 12px;
    text-align: center;
    overflow: hidden;
    transition: border-color 0.15s, background-color 0.15s;
  }
  .upload:hover {
    border-color: var(--blue);
    background: #f8fafc;
  }
  .upload span:first-child {
    font-size: 24px;
  }
  .upload img {
    max-height: 100%;
    max-width: 100%;
    object-fit: contain;
  }
  .upload input {
    display: none;
  }

  .submit {
    margin-top: 14px;
    height: 42px;
    padding: 0 36px;
    border: 0;
    border-radius: 10px;
    background: var(--blue);
    color: #fff;
    font: inherit;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 6px -1px rgba(29, 78, 216, 0.25);
    transition: all 0.15s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }
  .submit:hover {
    background: #1e40af;
    transform: translateY(-1px);
  }
  .submit:active {
    transform: translateY(0);
  }
  .toast {
    margin-top: 14px;
    font-size: 14px;
    font-weight: 600;
    color: #15803d;
  }

  @media (max-width: 760px) {
    .nr-row {
      grid-template-columns: 1fr;
      gap: 16px;
    }
    .pbtn {
      flex: 1;
      min-width: 90px;
    }
  }
</style>

<div class="bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-xs">
  <div class="mb-6">
    <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Service Request Portal</span>
    <h2 class="text-xl font-extrabold text-slate-800 mt-0.5">Submit an IT Incident or Request</h2>
    <p class="text-xs text-slate-500 mt-1">Please provide the details below so our support coordinators can dispatch a technician.</p>
  </div>

  <?php if (!empty($err)): ?>
  <div class="mb-5 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs flex items-center gap-2">
    <i data-lucide="alert-circle" class="w-4 h-4 text-red-500 shrink-0"></i>
    <span><?= e($err) ?></span>
  </div>
  <?php endif; ?>

  <form id="requestForm" method="POST" action="new_request.php" enctype="multipart/form-data" class="nr-form">
    <?= tok() ?>
    <input type="hidden" name="category" id="category-input" value="Printer">
    <input type="hidden" name="category_id" id="category-id-input" value="1">
    <input type="hidden" name="priority" id="priority-input" value="Medium">

    <!-- Row: Branch & Dept -->
    <div class="nr-row">
      <div>
        <label for="branch">Branch Name</label>
        <input class="nr-field bg-slate-50 text-slate-600" id="branch" value="<?= e($info['branch'] ?: 'Colombo Head Office') ?>" readonly>
      </div>
      <div>
        <label for="dept">Department</label>
        <input class="nr-field bg-slate-50 text-slate-600" id="dept" value="<?= e($info['dept'] ?: 'Finance') ?>" readonly>
      </div>
    </div>

    <!-- Category Chips -->
    <div class="nr-group">
      <span class="label">Issue Category</span>
      <div class="chips" id="category-chips">
        <button type="button" class="chip" data-name="Printer" data-id="1" aria-pressed="true">🖨️ Printer</button>
        <button type="button" class="chip" data-name="Hardware" data-id="1" aria-pressed="false">💻 Hardware</button>
        <button type="button" class="chip" data-name="Network" data-id="2" aria-pressed="false">🌐 Network</button>
        <button type="button" class="chip" data-name="Software" data-id="3" aria-pressed="false">📱 Software</button>
        <button type="button" class="chip" data-name="Telephone" data-id="4" aria-pressed="false">📞 Telephone</button>
      </div>
    </div>

    <!-- Priority Selector -->
    <div class="nr-group">
      <span class="label">Priority</span>
      <div class="prio" id="priority-group">
        <button type="button" class="pbtn" data-p="High" aria-pressed="false"><i></i>High</button>
        <button type="button" class="pbtn" data-p="Medium" aria-pressed="true"><i></i>Medium</button>
        <button type="button" class="pbtn" data-p="Low" aria-pressed="false"><i></i>Low</button>
      </div>
    </div>

    <!-- Description -->
    <div class="nr-group">
      <label for="desc">Description</label>
      <textarea name="description" id="desc" rows="3" required placeholder="Describe the issue you are experiencing...">The printer on 3rd floor is not responding. Tried restarting but same issue persists.</textarea>
    </div>

    <!-- File Upload -->
    <div class="nr-group">
      <span class="label">Attach Photo (optional)</span>
      <label class="upload" id="drop">
        <input type="file" name="photo" accept="image/*,.pdf" id="photo">
        <span id="upload-icon">📷</span>
        <span id="upload-label">Click to upload image</span>
      </label>
    </div>

    <!-- Submit Button & Toast Feedback -->
    <div class="flex items-center gap-4">
      <button class="submit" id="submit-btn" type="submit">
        <span id="btn-text">Submit Request</span>
        <i data-lucide="arrow-right" class="w-4 h-4"></i>
      </button>
      <p class="toast hidden" id="toast" role="status">
        <span class="flex items-center gap-1.5"><i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i> Request submitted! Redirecting...</span>
      </p>
    </div>
  </form>
</div>

<script>
  // Category Selection
  const catInput = document.getElementById('category-input');
  const catIdInput = document.getElementById('category-id-input');
  const catChips = document.getElementById('category-chips');

  catChips.addEventListener('click', e => {
    const btn = e.target.closest('button');
    if (!btn) return;
    catChips.querySelectorAll('button').forEach(b => b.setAttribute('aria-pressed', b === btn));
    catInput.value = btn.dataset.name;
    catIdInput.value = btn.dataset.id;
  });

  // Priority Selection
  const prioInput = document.getElementById('priority-input');
  const prioGroup = document.getElementById('priority-group');

  prioGroup.addEventListener('click', e => {
    const btn = e.target.closest('button');
    if (!btn) return;
    prioGroup.querySelectorAll('button').forEach(b => b.setAttribute('aria-pressed', b === btn));
    prioInput.value = btn.dataset.p;
  });

  // Photo Preview
  const photo = document.getElementById('photo');
  const drop = document.getElementById('drop');
  photo.addEventListener('change', () => {
    const f = photo.files[0];
    if (!f) return;
    if (f.type.startsWith('image/')) {
      const img = document.createElement('img');
      img.src = URL.createObjectURL(f);
      img.alt = 'Selected photo';
      drop.querySelectorAll('span,img').forEach(n => n.remove());
      drop.appendChild(img);
    } else {
      document.getElementById('upload-icon').textContent = '📄';
      document.getElementById('upload-label').textContent = f.name;
    }
  });

  // AJAX Submission with Instant Toast Feedback and Redirect
  const form = document.getElementById('requestForm');
  const submitBtn = document.getElementById('submit-btn');
  const btnText = document.getElementById('btn-text');
  const toast = document.getElementById('toast');

  form.addEventListener('submit', async e => {
    if (window.location.protocol === 'file:') return; // let normal or prevent

    e.preventDefault();
    submitBtn.disabled = true;
    btnText.textContent = 'Submitting...';

    const formData = new FormData(form);
    formData.append('ajax', '1');

    try {
      const res = await fetch('new_request.php', {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });
      const data = await res.json();

      if (res.ok && data.success) {
        toast.classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
        btnText.textContent = 'Redirecting...';
        setTimeout(() => {
          window.location.href = data.redirect || 'dashboard.php';
        }, 600);
      } else {
        alert(data.error || 'Failed to submit request.');
        submitBtn.disabled = false;
        btnText.textContent = 'Submit Request';
      }
    } catch (err) {
      console.warn('AJAX submit issue, submitting normally:', err);
      form.submit();
    }
  });
</script>

<?php foot(); ?>
