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

    // Notify coordinators
    foreach (q("SELECT id FROM users WHERE role='coordinator'")->fetchAll() as $c) {
      notify($c['id'], $id, "New ticket #TX-$id", "$title ($pri priority)", $pri === 'High' ? 'warning' : 'info');
    }

    // Notify staff (technicians) like the other roles
    foreach (q("SELECT id FROM users WHERE role='staff'")->fetchAll() as $s) {
      notify($s['id'], $id, "New ticket #TX-$id", "$title ($pri priority)", $pri === 'High' ? 'warning' : 'info');
    }

    // Notify submitting employee
    notify($u['id'], $id, "Ticket #TX-$id submitted", "Your request '$title' was received by ICT support.", 'info');

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
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  .request-page { font-family: 'Plus Jakarta Sans', sans-serif; }
  .font-head { font-family: 'Bricolage Grotesque', sans-serif; }
  .tile input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
  .tile > div { transition: all .18s ease; }
  .tile input:focus-visible + div { outline: 3px solid #93c5fd; outline-offset: 2px; }
  .cat input:checked + div { background: #2563eb; color: #fff; border-color: #2563eb; transform: translateY(-3px); box-shadow: 0 12px 24px -10px rgba(37,99,235,.7); }
  .cat input:checked + div .ico { background: rgba(255,255,255,.18); color: #fff; }
  .pri-high input:checked + div { background: #fee2e2; border-color: #ef4444; color: #b91c1c; }
  .pri-medium input:checked + div { background: #fef3c7; border-color: #f59e0b; color: #92400e; }
  .pri-low input:checked + div { background: #dcfce7; border-color: #22c55e; color: #166534; }
  .field:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37,99,235,.15); }
  .drop.over { border-color: #2563eb; background: #eff6ff; }
  @media (prefers-reduced-motion: reduce) { .request-page *, .request-page *::before, .request-page *::after { transition: none !important; } }
</style>

<div class="request-page max-w-3xl mx-auto space-y-4">
  <?php if (!empty($err)): ?>
  <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-2" role="alert">
    <i data-lucide="alert-circle" class="w-4 h-4 text-red-500 shrink-0"></i>
    <span><?= e($err) ?></span>
  </div>
  <?php endif; ?>

  <form id="requestForm" action="new_request.php" method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 md:p-8 space-y-8">
    <?= tok() ?>
    <input type="hidden" name="category" id="category-input" value="Printer">
    <input type="hidden" name="category_id" id="category-id-input" value="1">
    <input type="hidden" name="priority" id="priority-input" value="Medium">

    <section>
      <h3 class="font-head font-bold text-lg mb-4">Where are you?</h3>
      <div class="grid sm:grid-cols-2 gap-4">
        <label for="branch" class="block text-sm font-semibold">Branch name
          <span class="relative mt-1.5 block"><i data-lucide="building-2" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
          <input id="branch" readonly value="<?= e($info['branch'] ?: 'Colombo Main Office') ?>" class="field w-full border border-slate-300 rounded-xl pl-10 pr-3 py-3 font-medium bg-slate-50 text-slate-600"></span>
        </label>
        <label for="dept" class="block text-sm font-semibold">Department
          <span class="relative mt-1.5 block"><i data-lucide="briefcase" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
          <input id="dept" readonly value="<?= e($info['dept'] ?: 'Accounts & Finance') ?>" class="field w-full border border-slate-300 rounded-xl pl-10 pr-3 py-3 font-medium bg-slate-50 text-slate-600"></span>
        </label>
      </div>
    </section>

    <fieldset>
      <legend class="font-head font-bold text-lg mb-4">What kind of problem is it?</legend>
      <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <label class="tile cat relative"><input type="radio" name="category_choice" value="Printer" data-id="1" checked><div class="border border-slate-200 bg-slate-50 rounded-2xl py-4 flex flex-col items-center gap-2 text-sm font-semibold"><span class="ico w-10 h-10 rounded-xl bg-white text-blue-600 grid place-items-center"><i data-lucide="printer" class="w-5 h-5"></i></span>Printer</div></label>
        <label class="tile cat relative"><input type="radio" name="category_choice" value="Hardware" data-id="1"><div class="border border-slate-200 bg-slate-50 rounded-2xl py-4 flex flex-col items-center gap-2 text-sm font-semibold"><span class="ico w-10 h-10 rounded-xl bg-white text-blue-600 grid place-items-center"><i data-lucide="cpu" class="w-5 h-5"></i></span>Hardware</div></label>
        <label class="tile cat relative"><input type="radio" name="category_choice" value="Network" data-id="2"><div class="border border-slate-200 bg-slate-50 rounded-2xl py-4 flex flex-col items-center gap-2 text-sm font-semibold"><span class="ico w-10 h-10 rounded-xl bg-white text-blue-600 grid place-items-center"><i data-lucide="wifi" class="w-5 h-5"></i></span>Network</div></label>
        <label class="tile cat relative"><input type="radio" name="category_choice" value="Software" data-id="3"><div class="border border-slate-200 bg-slate-50 rounded-2xl py-4 flex flex-col items-center gap-2 text-sm font-semibold"><span class="ico w-10 h-10 rounded-xl bg-white text-blue-600 grid place-items-center"><i data-lucide="app-window" class="w-5 h-5"></i></span>Software</div></label>
        <label class="tile cat relative"><input type="radio" name="category_choice" value="Telephone" data-id="4"><div class="border border-slate-200 bg-slate-50 rounded-2xl py-4 flex flex-col items-center gap-2 text-sm font-semibold"><span class="ico w-10 h-10 rounded-xl bg-white text-blue-600 grid place-items-center"><i data-lucide="phone" class="w-5 h-5"></i></span>Telephone</div></label>
      </div>
    </fieldset>

    <fieldset>
      <legend class="font-head font-bold text-lg mb-1">How urgent is it?</legend>
      <p id="priHint" class="text-sm text-slate-500 mb-4">Medium: we aim to respond within 4 hours.</p>
      <div class="grid grid-cols-3 gap-3">
        <label class="tile pri-high relative"><input type="radio" name="priority_choice" value="High"><div class="border-2 border-slate-200 bg-slate-50 rounded-2xl py-3.5 text-center font-bold text-sm text-slate-500"><span class="inline-block w-2.5 h-2.5 rounded-full bg-red-500 mr-1.5"></span>High</div></label>
        <label class="tile pri-medium relative"><input type="radio" name="priority_choice" value="Medium" checked><div class="border-2 border-slate-200 bg-slate-50 rounded-2xl py-3.5 text-center font-bold text-sm text-slate-500"><span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-500 mr-1.5"></span>Medium</div></label>
        <label class="tile pri-low relative"><input type="radio" name="priority_choice" value="Low"><div class="border-2 border-slate-200 bg-slate-50 rounded-2xl py-3.5 text-center font-bold text-sm text-slate-500"><span class="inline-block w-2.5 h-2.5 rounded-full bg-green-500 mr-1.5"></span>Low</div></label>
      </div>
    </fieldset>

    <section>
      <div class="flex items-end justify-between mb-4"><label for="desc" class="font-head font-bold text-lg">Describe what happened</label><span id="count" class="text-xs text-slate-400">0 / 500</span></div>
      <textarea id="desc" name="description" rows="4" maxlength="500" required placeholder="What stopped working, and what have you already tried?" class="field w-full border border-slate-300 rounded-xl px-4 py-3 text-sm resize-y">The printer on 3rd floor is not responding. Tried restarting but same issue persists.</textarea>
    </section>

    <section>
      <h3 class="font-head font-bold text-lg mb-1">Add a photo <span class="text-sm font-medium text-slate-400">(optional)</span></h3>
      <p class="text-sm text-slate-500 mb-4">An error message or a photo of the device helps the technician arrive prepared.</p>
      <label id="drop" class="drop block border-2 border-dashed border-slate-300 rounded-2xl p-6 text-center cursor-pointer hover:border-blue-600 hover:bg-blue-50/50 transition">
        <input id="photo" name="photo" type="file" accept="image/*" class="sr-only">
        <span id="dropEmpty"><span class="mx-auto w-12 h-12 rounded-full bg-blue-50 text-blue-600 grid place-items-center mb-2"><i data-lucide="image-plus" class="w-6 h-6"></i></span>
          <span class="block text-sm font-semibold">Drop an image here or <span class="text-blue-600 underline">browse</span></span><span class="block text-xs text-slate-400 mt-1">PNG or JPG</span></span>
        <span id="dropFull" class="hidden items-center gap-4 text-left"><img id="thumb" alt="Selected photo preview" class="w-20 h-20 rounded-xl object-cover"><span class="min-w-0"><span id="fname" class="block text-sm font-semibold truncate"></span><button type="button" id="remove" class="text-xs text-red-600 font-semibold mt-1">Remove photo</button></span></span>
      </label>
    </section>

    <div class="flex flex-wrap items-center gap-4">
      <button type="submit" id="submit-btn" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold px-8 py-3.5 rounded-xl inline-flex items-center justify-center gap-2 shadow-lg shadow-blue-600/30 transition"><span id="btn-text">Send request</span><i data-lucide="send" class="w-4 h-4"></i></button>
      <p class="toast hidden text-sm font-semibold text-green-700" id="toast" role="status">Request submitted! Redirecting...</p>
    </div>
  </form>

  <div class="bg-white rounded-2xl border border-slate-200 p-4 text-sm text-slate-600 flex gap-3"><i data-lucide="lightbulb" class="w-5 h-5 text-amber-500 shrink-0"></i><p>Include the floor, room and device name so the technician can find it quickly.</p></div>
</div>

<script>
  const categoryInput = document.getElementById('category-input');
  const categoryIdInput = document.getElementById('category-id-input');
  document.querySelectorAll('[name="category_choice"]').forEach(input => {
    input.addEventListener('change', () => {
      categoryInput.value = input.value;
      categoryIdInput.value = input.dataset.id;
    });
  });

  const priorityInput = document.getElementById('priority-input');
  const priorityHints = {
    High: 'Respond within 1 hour. Use for anything stopping work for a whole team.',
    Medium: 'Respond within 4 hours.',
    Low: 'Respond within 2 working days.'
  };
  const updateFormPreview = () => {
    const priority = document.querySelector('[name="priority_choice"]:checked').value;
    const hint = priorityHints[priority];
    document.getElementById('priHint').textContent = priority + ': ' + hint.charAt(0).toLowerCase() + hint.slice(1).replace(/^respond/, 'we aim to respond');
    priorityInput.value = priority;
    document.getElementById('count').textContent = document.getElementById('desc').value.length + ' / 500';
  };
  document.querySelectorAll('[name="priority_choice"]').forEach(input => input.addEventListener('change', updateFormPreview));
  document.getElementById('desc').addEventListener('input', updateFormPreview);
  updateFormPreview();

  const photo = document.getElementById('photo');
  const drop = document.getElementById('drop');
  let previewUrl = null;
  const showFile = file => {
    if (!file) return;
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = URL.createObjectURL(file);
    document.getElementById('thumb').src = previewUrl;
    document.getElementById('fname').textContent = file.name;
    document.getElementById('dropEmpty').classList.add('hidden');
    document.getElementById('dropFull').classList.remove('hidden');
    document.getElementById('dropFull').classList.add('flex');
  };
  const clearFile = () => {
    photo.value = '';
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = null;
    document.getElementById('thumb').removeAttribute('src');
    document.getElementById('dropFull').classList.add('hidden');
    document.getElementById('dropFull').classList.remove('flex');
    document.getElementById('dropEmpty').classList.remove('hidden');
  };

  photo.addEventListener('change', () => showFile(photo.files[0]));
  ['dragenter', 'dragover'].forEach(eventName => drop.addEventListener(eventName, event => {
    event.preventDefault();
    drop.classList.add('over');
  }));
  ['dragleave', 'drop'].forEach(eventName => drop.addEventListener(eventName, event => {
    event.preventDefault();
    drop.classList.remove('over');
  }));
  drop.addEventListener('drop', event => {
    const file = event.dataTransfer.files[0];
    if (!file) return;
    const transfer = new DataTransfer();
    transfer.items.add(file);
    photo.files = transfer.files;
    showFile(file);
  });
  document.getElementById('remove').addEventListener('click', event => {
    event.preventDefault();
    event.stopPropagation();
    clearFile();
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
        btnText.textContent = 'Send request';
      }
    } catch (err) {
      console.warn('AJAX submit issue, submitting normally:', err);
      form.submit();
    }
  });
</script>

<?php foot(); ?>
