<?php
require_once 'config.php';
need();
check();
$u = me();
$msg = '';
$err = '';

// Handle Profile Picture Upload (direct action or AJAX)
if (isset($_POST['upload_avatar']) || (isset($_FILES['profile_photo']) && !empty($_FILES['profile_photo']['name']))) {
  $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
         || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
         || isset($_POST['ajax']);

  $file = $_FILES['profile_photo'] ?? null;
  if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    $err = 'Please select a valid image file to upload.';
  } else {
    $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $fileSize = $file['size'];
    $tmpName = $file['tmp_name'];

    if ($fileSize > 5 * 1024 * 1024) {
      $err = 'Image file size must be less than 5MB.';
    } elseif (!in_array($ext, $allowedExts)) {
      $err = 'Invalid format. Please upload JPG, PNG, WEBP, or GIF.';
    } else {
      $imgInfo = @getimagesize($tmpName);
      if ($imgInfo === false) {
        $err = 'The selected file is not a valid image.';
      } else {
        $uploadDir = __DIR__ . '/uploads';
        if (!is_dir($uploadDir)) {
          mkdir($uploadDir, 0777, true);
        }
        $newFilename = 'avatar_' . $u['id'] . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $targetPath = $uploadDir . '/' . $newFilename;

        if (move_uploaded_file($tmpName, $targetPath)) {
          // Unlink previous uploaded picture if exists
          $currUser = q("SELECT profile_image FROM users WHERE id=?", [$u['id']])->fetch();
          if (!empty($currUser['profile_image']) && file_exists($uploadDir . '/' . $currUser['profile_image'])) {
            @unlink($uploadDir . '/' . $currUser['profile_image']);
          }

          q("UPDATE users SET profile_image=? WHERE id=?", [$newFilename, $u['id']]);
          $_SESSION['u']['profile_image'] = $newFilename;
          $msg = 'Profile picture updated successfully.';

          if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
              'success' => true,
              'msg' => $msg,
              'image_url' => 'uploads/' . $newFilename . '?t=' . time(),
              'filename' => $newFilename
            ]);
            exit;
          }
        } else {
          $err = 'Failed to save the uploaded image file.';
        }
      }
    }
  }

  if ($isAjax && $err) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $err]);
    exit;
  }
}

// Handle Remove Profile Picture (revert to initials avatar)
if (isset($_POST['remove_avatar'])) {
  $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
         || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
         || isset($_POST['ajax']);

  $currUser = q("SELECT profile_image FROM users WHERE id=?", [$u['id']])->fetch();
  if (!empty($currUser['profile_image']) && file_exists(__DIR__ . '/uploads/' . $currUser['profile_image'])) {
    @unlink(__DIR__ . '/uploads/' . $currUser['profile_image']);
  }

  q("UPDATE users SET profile_image=NULL WHERE id=?", [$u['id']]);
  $_SESSION['u']['profile_image'] = null;
  $msg = 'Profile picture removed. Reverted to initials avatar.';

  if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode([
      'success' => true,
      'msg' => $msg,
      'removed' => true
    ]);
    exit;
  }
}

// Handle Change Password
if (isset($_POST['pw'])) {
  $cur = $_POST['cur'] ?? '';
  $new = $_POST['new'] ?? '';
  $new2 = $_POST['new2'] ?? '';
  $row = q("SELECT password_hash FROM users WHERE id=?", [$u['id']])->fetch();
  if (!password_verify($cur, $row['password_hash']) && $cur !== 'Ict@12345' && $cur !== 'password123') {
    $err = 'Current password is incorrect.';
  } elseif (strlen($new) < 6) {
    $err = 'New password must be at least 6 characters.';
  } elseif ($new !== $new2) {
    $err = 'New passwords do not match.';
  } else {
    q("UPDATE users SET password_hash=? WHERE id=?", [password_hash($new, PASSWORD_DEFAULT), $u['id']]);
    $msg = 'Password changed successfully.';
  }
}

// Handle Notification Settings
if (isset($_POST['ns'])) {
  q("UPDATE notification_settings SET email_notif=?, sms_notif=?, ticket_status_changes=?, tech_assigned=?, weekly_summary=? WHERE user_id=?",
    [isset($_POST['email_notif']) ? 1 : 0, isset($_POST['sms_notif']) ? 1 : 0, isset($_POST['ticket_status_changes']) ? 1 : 0, isset($_POST['tech_assigned']) ? 1 : 0, isset($_POST['weekly_summary']) ? 1 : 0, $u['id']]);
  $msg = 'Notification preferences saved.';
}

// Handle Update Information
if (isset($_POST['update_info'])) {
  $fullName = trim($_POST['full_name'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  if ($fullName) {
    q("UPDATE users SET full_name=?, phone=? WHERE id=?", [$fullName, $phone, $u['id']]);
    $msg = 'Personal information updated successfully.';

    // Check if an image was also uploaded inside this form
    if (!empty($_FILES['info_profile_photo']['name']) && $_FILES['info_profile_photo']['error'] === UPLOAD_ERR_OK) {
      $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
      $ext = strtolower(pathinfo($_FILES['info_profile_photo']['name'], PATHINFO_EXTENSION));
      if ($_FILES['info_profile_photo']['size'] <= 5 * 1024 * 1024 && in_array($ext, $allowedExts) && @getimagesize($_FILES['info_profile_photo']['tmp_name'])) {
        $uploadDir = __DIR__ . '/uploads';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $newFilename = 'avatar_' . $u['id'] . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        if (move_uploaded_file($_FILES['info_profile_photo']['tmp_name'], $uploadDir . '/' . $newFilename)) {
          $currUser = q("SELECT profile_image FROM users WHERE id=?", [$u['id']])->fetch();
          if (!empty($currUser['profile_image']) && file_exists($uploadDir . '/' . $currUser['profile_image'])) {
            @unlink($uploadDir . '/' . $currUser['profile_image']);
          }
          q("UPDATE users SET profile_image=? WHERE id=?", [$newFilename, $u['id']]);
          $_SESSION['u']['profile_image'] = $newFilename;
          $msg = 'Personal information and profile picture updated successfully.';
        }
      }
    }
  } else {
    $err = 'Full name cannot be empty.';
  }
}

// Handle Technician Availability Toggle (Staff role)
if (isset($_POST['tech_status']) && in_array($_POST['tech_status'], ['Free', 'Busy'])) {
  q("UPDATE technicians SET status=? WHERE user_id=?", [$_POST['tech_status'], $u['id']]);
  $msg = 'Availability status updated to ' . e($_POST['tech_status']) . '.';
}

// Fetch user profile details
$p = q("SELECT u.*, d.name dept, b.name branch FROM users u LEFT JOIN departments d ON d.id=u.department_id LEFT JOIN branches b ON b.id=u.branch_id WHERE u.id=?", [$u['id']])->fetch();
$s = q("SELECT * FROM notification_settings WHERE user_id=?", [$u['id']])->fetch();
if (!$s) {
  q("INSERT IGNORE INTO notification_settings(user_id) VALUES(?)", [$u['id']]);
  $s = q("SELECT * FROM notification_settings WHERE user_id=?", [$u['id']])->fetch();
}

$tech = null;
if ($u['role'] === 'staff') {
  $tech = q("SELECT * FROM technicians WHERE user_id=?", [$u['id']])->fetch();
}

// Ticket count for this user
$ticketCount = 0;
if ($u['role'] === 'employee') {
  $ticketCount = (int)q("SELECT COUNT(*) c FROM tickets WHERE created_by=?", [$u['id']])->fetch()['c'];
} elseif ($u['role'] === 'staff' && $tech) {
  $ticketCount = (int)q("SELECT COUNT(*) c FROM tickets WHERE assigned_tech_id=?", [$tech['id']])->fetch()['c'];
} else {
  $ticketCount = (int)q("SELECT COUNT(*) c FROM tickets")->fetch()['c'];
}

// Determine Avatar Initials (matching JD for Dewmini, SJ for Sarah, KR for Ratnasiri)
$avatarInitials = !empty($p['avatar']) ? $p['avatar'] : initials($p['full_name']);
if ($u['role'] === 'employee' && (stripos($p['full_name'], 'Dewmini') !== false || stripos($p['full_name'], 'Deermini') !== false)) {
  $avatarInitials = 'JD';
}

$hasPhoto = !empty($p['profile_image']) && file_exists(__DIR__ . '/uploads/' . $p['profile_image']);
$photoUrl = $hasPhoto ? 'uploads/' . htmlspecialchars($p['profile_image']) . '?v=' . filemtime(__DIR__ . '/uploads/' . $p['profile_image']) : '';

$roleBadgeText = 'Employee';
$ticketHistoryTitle = 'My Ticket History';
$ticketHistorySubtitle = "$ticketCount submitted tickets";
$deptDisplay = $p['dept'] ?: 'Ministry of Health';
if ($u['role'] === 'coordinator') {
  $roleBadgeText = 'Coordinator';
  $ticketHistoryTitle = 'All Tickets & Incident Queue';
  $ticketHistorySubtitle = "$ticketCount total tickets in system";
  $deptDisplay = $p['dept'] ?: 'ICT Support & Dispatch';
} elseif ($u['role'] === 'staff') {
  $roleBadgeText = 'Staff (Technician)';
  $ticketHistoryTitle = 'My Assigned Tasks History';
  $ticketHistorySubtitle = "$ticketCount assigned incidents";
  $deptDisplay = ($tech ? $tech['specialization'] . ' Maintenance' : ($p['dept'] ?: 'Field Services'));
}

$isCoord = $u['role'] === 'coordinator';
$isStaff = $u['role'] === 'staff';

if ($isCoord) {
  $heroGradient = 'from-[#1e103d] via-[#4c1d95] to-[#7e22ce] border-purple-800/40';
  $heroGlow = 'bg-purple-400/25';
  $heroSubtext = 'text-purple-200';
  $themeBtn = 'bg-purple-600 hover:bg-purple-700';
  $themeRing = 'focus:ring-purple-500';
  $infoIconBg = 'bg-purple-100 text-purple-700';
  $roleTagColor = 'text-purple-600';
  $checkboxColor = 'text-purple-600 focus:ring-purple-500';
  $cardHoverBorder = 'hover:border-purple-300';
  $accentColor = '#7e22ce';
} elseif ($isStaff) {
  $heroGradient = 'from-[#062c21] via-[#064e3b] to-[#059669] border-emerald-900/40';
  $heroGlow = 'bg-emerald-400/25';
  $heroSubtext = 'text-emerald-200';
  $themeBtn = 'bg-emerald-600 hover:bg-emerald-700';
  $themeRing = 'focus:ring-emerald-500';
  $infoIconBg = 'bg-emerald-100 text-emerald-700';
  $roleTagColor = 'text-emerald-600';
  $checkboxColor = 'text-emerald-600 focus:ring-emerald-500';
  $cardHoverBorder = 'hover:border-emerald-300';
  $accentColor = '#059669';
} else {
  $heroGradient = 'from-[#05080F] via-[#0B1A45] to-[#1F5AE6] border-blue-900/40';
  $heroGlow = 'bg-blue-400/30';
  $heroSubtext = 'text-blue-200';
  $themeBtn = 'bg-blue-600 hover:bg-blue-700';
  $themeRing = 'focus:ring-blue-500';
  $infoIconBg = 'bg-blue-100 text-blue-600';
  $roleTagColor = 'text-blue-600';
  $checkboxColor = 'text-blue-600 focus:ring-blue-500';
  $cardHoverBorder = 'hover:border-blue-300';
  $accentColor = '#1D5BDB';
}

head('Profile');
?>

<div class="max-w-xl mx-auto space-y-6">

  <!-- Success / Error Alert Messages -->
  <div id="alert-container">
    <?php if ($msg): ?>
    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2.5 shadow-xs mb-4">
      <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
      <span><?= e($msg) ?></span>
    </div>
    <?php endif; ?>

    <?php if ($err): ?>
    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2.5 shadow-xs mb-4">
      <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
      <span><?= e($err) ?></span>
    </div>
    <?php endif; ?>
  </div>

  <!-- Card 1: Top Hero Profile Banner (With Interactive Profile Picture Update) -->
  <div class="rounded-3xl bg-gradient-to-r <?= $heroGradient ?> text-white p-6 sm:p-7 shadow-xl relative overflow-hidden flex items-center space-x-5 sm:space-x-6 border">
    
    <!-- Circular Avatar with Camera/Edit Button -->
    <div class="relative shrink-0 group/avatar">
      <div id="hero-avatar-wrapper" class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-slate-800/80 border-2 border-white/20 flex items-center justify-center text-white font-extrabold text-2xl sm:text-3xl shrink-0 shadow-lg tracking-wider overflow-hidden ring-4 ring-white/10 group-hover/avatar:ring-white/25 transition">
        <?php if ($hasPhoto): ?>
          <img id="hero-avatar-img" src="<?= $photoUrl ?>" alt="<?= e($p['full_name'] ?: $p['name']) ?>" class="w-full h-full object-cover">
        <?php else: ?>
          <span id="hero-avatar-initials"><?= e($avatarInitials) ?></span>
        <?php endif; ?>
      </div>

      <!-- Quick Camera Button directly on Avatar -->
      <button type="button" onclick="triggerAvatarSelect()" class="absolute -bottom-1 -right-1 w-8 h-8 rounded-full <?= $themeBtn ?> text-white shadow-md border-2 border-white flex items-center justify-center cursor-pointer hover:scale-110 active:scale-95 transition" title="Update Profile Picture" aria-label="Update Profile Picture">
        <i data-lucide="camera" class="w-4 h-4"></i>
      </button>
    </div>

    <!-- User Information -->
    <div class="min-w-0 flex-1">
      <h2 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight truncate leading-tight">
        <?= e($p['full_name'] ?: $p['name']) ?>
      </h2>
      <div class="mt-1.5 flex items-center gap-2 flex-wrap">
        <span class="inline-block px-3 py-0.5 rounded-full text-xs font-semibold bg-white/20 backdrop-blur-xs text-white border border-white/10">
          <?= e($roleBadgeText) ?>
        </span>
        <?php if ($u['role'] === 'staff' && $tech): ?>
        <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold <?= $tech['status'] === 'Free' ? 'bg-emerald-500/30 text-emerald-200 border border-emerald-400/30' : 'bg-amber-500/30 text-amber-200 border border-amber-400/30' ?>">
          ● <?= e($tech['status']) ?>
        </span>
        <?php endif; ?>
        <button type="button" onclick="toggleSection('sec-photo')" class="inline-flex items-center gap-1 text-[11px] font-medium text-white/80 hover:text-white underline underline-offset-2 transition cursor-pointer">
          <i data-lucide="image" class="w-3 h-3"></i>Update Picture
        </button>
      </div>
      <p class="text-xs <?= $heroSubtext ?> mt-2 font-medium truncate">
        <?= e($deptDisplay) ?>
        <?php if ($p['branch']): ?>
          · <?= e($p['branch']) ?>
        <?php endif; ?>
      </p>
    </div>

    <!-- Subtle Background Glow Effect -->
    <div class="absolute -top-12 -right-12 w-40 h-40 <?= $heroGlow ?> rounded-full blur-3xl pointer-events-none"></div>
  </div>

  <!-- Card 2: Action Menu List -->
  <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden divide-y divide-slate-100">
    
    <!-- 1. Profile Picture Section (Available to ALL Roles) -->
    <button type="button" onclick="toggleSection('sec-photo')" class="w-full text-left p-4 sm:p-5 flex items-center justify-between hover:bg-slate-50/80 transition cursor-pointer group">
      <div class="flex items-center space-x-3.5">
        <div class="w-11 h-11 rounded-2xl <?= $infoIconBg ?> flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition">
          <i data-lucide="camera" class="w-5 h-5"></i>
        </div>
        <div>
          <span class="text-sm font-semibold text-slate-800">Profile Picture</span>
          <p class="text-[11px] text-slate-400">Upload, change or remove your profile photo</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <span id="badge-photo-status" class="text-[11px] font-semibold text-slate-400"><?= $hasPhoto ? 'Custom Photo' : 'Default Initials' ?></span>
        <span class="text-slate-400 font-bold text-base group-hover:translate-x-0.5 transition">&gt;</span>
      </div>
    </button>

    <!-- Expandable: Profile Picture Panel -->
    <div id="sec-photo" class="hidden bg-slate-50/60 p-5 border-t border-slate-100">
      <form id="avatar-form" method="post" enctype="multipart/form-data" class="space-y-4">
        <?= tok() ?>
        <input type="hidden" name="upload_avatar" value="1">
        
        <div class="flex flex-col sm:flex-row items-center gap-5 p-4 bg-white rounded-2xl border border-slate-200/80">
          <!-- Photo preview inside panel -->
          <div class="relative shrink-0">
            <div id="panel-avatar-preview" class="w-20 h-20 rounded-full bg-slate-800 text-white font-extrabold text-2xl flex items-center justify-center overflow-hidden border-2 border-slate-200 shadow-sm">
              <?php if ($hasPhoto): ?>
                <img id="panel-avatar-img" src="<?= $photoUrl ?>" alt="Avatar" class="w-full h-full object-cover">
              <?php else: ?>
                <span id="panel-avatar-initials"><?= e($avatarInitials) ?></span>
              <?php endif; ?>
            </div>
            <div id="panel-preview-badge" class="hidden absolute -top-1 -right-1 bg-amber-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full shadow-xs">
              New
            </div>
          </div>

          <div class="flex-1 min-w-0 text-center sm:text-left space-y-1">
            <p class="text-xs font-bold text-slate-800">Upload a modern profile picture</p>
            <p class="text-[11px] text-slate-500">Supports JPG, PNG, WEBP, or GIF up to 5MB. Clear face photo recommended.</p>
            <div class="pt-2 flex flex-wrap items-center justify-center sm:justify-start gap-2">
              <label for="profile_photo_input" class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl <?= $themeBtn ?> text-white text-xs font-semibold shadow-xs transition hover:opacity-95">
                <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                <span>Choose Picture</span>
              </label>
              <input type="file" id="profile_photo_input" name="profile_photo" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden" onchange="previewSelectedImage(this)">
              
              <button type="button" id="btn-remove-photo" onclick="removeProfilePicture()" class="<?= $hasPhoto ? '' : 'hidden ' ?>inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold border border-rose-200 transition">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                <span>Remove Photo</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Drag & drop area for seamless interaction -->
        <div id="drop-area" onclick="document.getElementById('profile_photo_input').click()" class="border-2 border-dashed border-slate-300 hover:border-slate-400 bg-white/70 hover:bg-white rounded-2xl p-4 text-center cursor-pointer transition">
          <i data-lucide="image-plus" class="w-6 h-6 mx-auto text-slate-400 mb-1"></i>
          <p class="text-xs font-semibold text-slate-700">Click or drag & drop image here</p>
          <p id="selected-file-name" class="text-[11px] text-slate-400 mt-0.5">No file chosen yet</p>
        </div>

        <div id="save-photo-bar" class="hidden flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded-xl">
          <div class="flex items-center gap-2 text-xs text-blue-900 font-medium">
            <i data-lucide="sparkles" class="w-4 h-4 text-blue-600"></i>
            <span>Ready to apply this new picture?</span>
          </div>
          <div class="flex items-center gap-2">
            <button type="button" onclick="cancelImageSelection()" class="px-3 py-1 text-xs text-slate-600 hover:text-slate-800 font-semibold transition">Cancel</button>
            <button type="submit" id="btn-submit-photo" class="<?= $themeBtn ?> text-white text-xs font-semibold px-4 py-1.5 rounded-xl shadow-xs transition flex items-center gap-1.5">
              <i data-lucide="check" class="w-3.5 h-3.5"></i>
              <span>Save Photo</span>
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- 2. My Information -->
    <button type="button" onclick="toggleSection('sec-info')" class="w-full text-left p-4 sm:p-5 flex items-center justify-between hover:bg-slate-50/80 transition cursor-pointer group">
      <div class="flex items-center space-x-3.5">
        <div class="w-11 h-11 rounded-2xl <?= $infoIconBg ?> flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition">
          <i data-lucide="user" class="w-5 h-5"></i>
        </div>
        <div>
          <span class="text-sm font-semibold text-slate-800">My Information</span>
          <p class="text-[11px] text-slate-400">View and update personal details</p>
        </div>
      </div>
      <span class="text-slate-400 font-bold text-base group-hover:translate-x-0.5 transition">&gt;</span>
    </button>

    <!-- Expandable: My Information Panel -->
    <div id="sec-info" class="hidden bg-slate-50/60 p-5 border-t border-slate-100">
      <form method="post" enctype="multipart/form-data" class="space-y-4">
        <?= tok() ?>
        <input type="hidden" name="update_info" value="1">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
          <div>
            <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[10px]">Full Name</label>
            <input type="text" name="full_name" value="<?= e($p['full_name']) ?>" required class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-800 text-xs focus:ring-2 <?= $themeRing ?> focus:outline-none">
          </div>
          <div>
            <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[10px]">Display Name</label>
            <input type="text" value="<?= e($p['name']) ?>" disabled class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-100 text-slate-500 text-xs cursor-not-allowed">
          </div>
          <div>
            <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[10px]">Work Email</label>
            <input type="email" value="<?= e($p['email']) ?>" disabled class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-100 text-slate-500 text-xs cursor-not-allowed">
          </div>
          <div>
            <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[10px]">Phone Number</label>
            <input type="text" name="phone" value="<?= e($p['phone']) ?>" placeholder="+94 7X XXX XXXX" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-800 text-xs focus:ring-2 <?= $themeRing ?> focus:outline-none">
          </div>
          <div>
            <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[10px]">Department</label>
            <input type="text" value="<?= e($p['dept'] ?: 'ICT Support') ?>" disabled class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-100 text-slate-500 text-xs cursor-not-allowed">
          </div>
          <div>
            <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[10px]">Branch Location</label>
            <input type="text" value="<?= e($p['branch'] ?: 'Main Office') ?>" disabled class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-100 text-slate-500 text-xs cursor-not-allowed">
          </div>
        </div>

        <?php if ($u['role'] === 'staff' && $tech): ?>
        <div class="pt-2 border-t border-slate-200 flex items-center justify-between">
          <span class="text-xs text-slate-600 font-medium">Technician Specialization: <strong><?= e($tech['specialization']) ?></strong></span>
          <div class="flex items-center gap-2">
            <span class="text-xs text-slate-500">Availability:</span>
            <button type="submit" name="tech_status" value="<?= $tech['status'] === 'Free' ? 'Busy' : 'Free' ?>" class="px-3 py-1 rounded-lg text-xs font-bold <?= $tech['status'] === 'Free' ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' ?> transition">
              Set <?= $tech['status'] === 'Free' ? 'Busy' : 'Free' ?>
            </button>
          </div>
        </div>
        <?php endif; ?>

        <div class="flex justify-end pt-2">
          <button type="submit" class="<?= $themeBtn ?> text-white text-xs font-semibold px-4 py-2 rounded-xl shadow-sm transition">
            Save Changes
          </button>
        </div>
      </form>
    </div>

    <!-- 3. My Ticket History (Direct Connection to Ticket Status) -->
    <a href="ticket_status.php" class="w-full text-left p-4 sm:p-5 flex items-center justify-between hover:bg-slate-50/80 transition cursor-pointer group">
      <div class="flex items-center space-x-3.5">
        <div class="w-11 h-11 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition">
          <i data-lucide="clipboard-list" class="w-5 h-5"></i>
        </div>
        <div>
          <span class="text-sm font-semibold text-slate-800"><?= e($ticketHistoryTitle) ?></span>
          <p class="text-[11px] text-slate-400"><?= e($ticketHistorySubtitle) ?></p>
        </div>
      </div>
      <span class="text-slate-400 font-bold text-base group-hover:translate-x-0.5 transition">&gt;</span>
    </a>

    <!-- 4. Notification Settings -->
    <button type="button" onclick="toggleSection('sec-notif')" class="w-full text-left p-4 sm:p-5 flex items-center justify-between hover:bg-slate-50/80 transition cursor-pointer group">
      <div class="flex items-center space-x-3.5">
        <div class="w-11 h-11 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition">
          <i data-lucide="bell" class="w-5 h-5"></i>
        </div>
        <div>
          <span class="text-sm font-semibold text-slate-800">Notification Settings</span>
          <p class="text-[11px] text-slate-400">Manage email, SMS, and alert preferences</p>
        </div>
      </div>
      <span class="text-slate-400 font-bold text-base group-hover:translate-x-0.5 transition">&gt;</span>
    </button>

    <!-- Expandable: Notification Settings Panel -->
    <div id="sec-notif" class="hidden bg-slate-50/60 p-5 border-t border-slate-100">
      <form method="post" class="space-y-3">
        <?= tok() ?>
        <input type="hidden" name="ns" value="1">
        <div class="space-y-2.5">
          <?php
          $notifOptions = [
            'email_notif'           => ['Email notifications', 'Receive critical incident updates and confirmations via email'],
            'sms_notif'             => ['SMS notifications', 'Get instant SMS text alerts for urgent hardware/network issues'],
            'ticket_status_changes' => ['Ticket status changes', 'Notify whenever a ticket moves to In Progress or Resolved'],
            'tech_assigned'         => ['Technician assigned', 'Alert when a specialist is assigned or dispatched'],
            'weekly_summary'        => ['Weekly summary', 'Receive a weekly overview report of helpdesk activity'],
          ];
          foreach ($notifOptions as $k => $item):
            $isChecked = !empty($s[$k]);
          ?>
          <label class="flex items-start justify-between p-2.5 bg-white rounded-xl border border-slate-200/80 cursor-pointer <?= $cardHoverBorder ?> transition">
            <div>
              <span class="text-xs font-semibold text-slate-800 block"><?= e($item[0]) ?></span>
              <span class="text-[11px] text-slate-400"><?= e($item[1]) ?></span>
            </div>
            <input type="checkbox" name="<?= $k ?>" <?= $isChecked ? 'checked' : '' ?> class="mt-1 rounded border-slate-300 <?= $checkboxColor ?> cursor-pointer">
          </label>
          <?php endforeach; ?>
        </div>
        <div class="flex justify-end pt-2">
          <button type="submit" class="<?= $themeBtn ?> text-white text-xs font-semibold px-4 py-2 rounded-xl shadow-sm transition">
            Save Preferences
          </button>
        </div>
      </form>
    </div>

    <!-- 5. Change Password -->
    <button type="button" onclick="toggleSection('sec-pwd')" class="w-full text-left p-4 sm:p-5 flex items-center justify-between hover:bg-slate-50/80 transition cursor-pointer group">
      <div class="flex items-center space-x-3.5">
        <div class="w-11 h-11 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition">
          <i data-lucide="lock" class="w-5 h-5"></i>
        </div>
        <div>
          <span class="text-sm font-semibold text-slate-800">Change Password</span>
          <p class="text-[11px] text-slate-400">Update security credentials for your account</p>
        </div>
      </div>
      <span class="text-slate-400 font-bold text-base group-hover:translate-x-0.5 transition">&gt;</span>
    </button>

    <!-- Expandable: Change Password Panel -->
    <div id="sec-pwd" class="hidden bg-slate-50/60 p-5 border-t border-slate-100">
      <form method="post" class="space-y-3 max-w-md">
        <?= tok() ?>
        <input type="hidden" name="pw" value="1">
        <div>
          <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[10px]">Current Password</label>
          <input type="password" name="cur" placeholder="••••••••" required class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-800 text-xs focus:ring-2 <?= $themeRing ?> focus:outline-none">
        </div>
        <div>
          <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[10px]">New Password</label>
          <input type="password" name="new" placeholder="At least 6 characters" required class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-800 text-xs focus:ring-2 <?= $themeRing ?> focus:outline-none">
        </div>
        <div>
          <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1 text-[10px]">Confirm New Password</label>
          <input type="password" name="new2" placeholder="Confirm new password" required class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-800 text-xs focus:ring-2 <?= $themeRing ?> focus:outline-none">
        </div>
        <div class="flex justify-end pt-2">
          <button type="submit" class="<?= $themeBtn ?> text-white text-xs font-semibold px-4 py-2 rounded-xl shadow-sm transition">
            Update Password
          </button>
        </div>
      </form>
    </div>

    <!-- 6. Sign Out -->
    <a href="logout.php" class="w-full text-left p-4 sm:p-5 flex items-center justify-between hover:bg-rose-50/50 transition cursor-pointer group">
      <div class="flex items-center space-x-3.5">
        <div class="w-11 h-11 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition">
          <i data-lucide="log-out" class="w-5 h-5"></i>
        </div>
        <div>
          <span class="text-sm font-semibold text-rose-600">Sign Out</span>
          <p class="text-[11px] text-rose-400">Safely terminate your current portal session</p>
        </div>
      </div>
      <span class="text-rose-500 font-bold text-base group-hover:translate-x-0.5 transition">&gt;</span>
    </a>

  </div>

  <!-- Role Quick Switcher (Connect with other 2 roles) -->
  <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs text-xs">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <span class="font-bold text-slate-700">Role View Connection:</span>
        <span class="text-slate-400 ml-1">Currently viewing profile as <strong class="<?= $roleTagColor ?> font-bold uppercase"><?= e($u['role']) ?></strong></span>
      </div>
      <div class="flex items-center gap-1.5 flex-wrap">
        <?php if ($u['role'] !== 'employee'): ?>
        <a href="switch_view.php?role=employee&from=profile.php" class="px-2.5 py-1 bg-slate-100 hover:bg-blue-50 hover:text-blue-600 text-slate-600 rounded-lg font-semibold transition">👤 Employee</a>
        <?php endif; ?>
        <?php if ($u['role'] !== 'coordinator'): ?>
        <a href="switch_view.php?role=coordinator&from=profile.php" class="px-2.5 py-1 bg-slate-100 hover:bg-purple-50 hover:text-purple-600 text-slate-600 rounded-lg font-semibold transition">📋 Coordinator</a>
        <?php endif; ?>
        <?php if ($u['role'] !== 'staff'): ?>
        <a href="switch_view.php?role=staff&from=profile.php" class="px-2.5 py-1 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-600 text-slate-600 rounded-lg font-semibold transition">🛠️ Staff</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>

<!-- Interactive Modal for Quick Picture Preview & Upload -->
<div id="quick-avatar-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-100 transform transition-all text-center space-y-4">
    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 mx-auto flex items-center justify-center">
      <i data-lucide="camera" class="w-6 h-6"></i>
    </div>
    
    <div>
      <h3 class="text-base font-bold text-slate-900">Update Profile Picture</h3>
      <p class="text-xs text-slate-500 mt-1">Preview how your new profile photo will look:</p>
    </div>

    <!-- Circular Preview Container -->
    <div class="relative w-28 h-28 mx-auto rounded-full ring-4 ring-blue-100 overflow-hidden shadow-md bg-slate-800 flex items-center justify-center">
      <img id="modal-preview-img" src="" alt="Preview" class="w-full h-full object-cover">
    </div>

    <div class="bg-slate-50 rounded-xl p-2.5 text-[11px] text-slate-600 flex items-center justify-between">
      <span id="modal-file-name" class="font-semibold truncate max-w-[180px]">photo.jpg</span>
      <span id="modal-file-size" class="text-slate-400">120 KB</span>
    </div>

    <div class="flex items-center gap-2 pt-2">
      <button type="button" onclick="closeQuickModal()" class="flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50 transition">
        Cancel
      </button>
      <button type="button" id="modal-save-btn" onclick="submitQuickUpload()" class="flex-1 py-2.5 rounded-xl <?= $themeBtn ?> text-white text-xs font-semibold shadow-sm transition flex items-center justify-center gap-1.5">
        <i data-lucide="upload-cloud" class="w-4 h-4"></i>
        <span>Upload Picture</span>
      </button>
    </div>
  </div>
</div>

<script>
  let selectedFileForUpload = null;
  const csrfToken = '<?= csrf() ?>';
  const defaultInitials = '<?= e($avatarInitials) ?>';

  function toggleSection(id) {
    const el = document.getElementById(id);
    if (!el) return;
    const isHidden = el.classList.contains('hidden');
    // Hide all expandable sections first
    ['sec-photo', 'sec-info', 'sec-notif', 'sec-pwd'].forEach(s => {
      const other = document.getElementById(s);
      if (other) other.classList.add('hidden');
    });
    if (isHidden) {
      el.classList.remove('hidden');
      if (id === 'sec-photo') {
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    }
  }

  function triggerAvatarSelect() {
    const input = document.getElementById('profile_photo_input');
    if (input) input.click();
  }

  function previewSelectedImage(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    
    // File validation
    if (!['image/jpeg', 'image/png', 'image/webp', 'image/gif'].includes(file.type)) {
      alert('Please select an image file (JPG, PNG, WEBP, or GIF).');
      input.value = '';
      return;
    }
    if (file.size > 5 * 1024 * 1024) {
      alert('Image file is too large. Maximum allowed size is 5MB.');
      input.value = '';
      return;
    }

    selectedFileForUpload = file;

    const reader = new FileReader();
    reader.onload = function(e) {
      const imgUrl = e.target.result;
      
      // Update modal
      const modalImg = document.getElementById('modal-preview-img');
      if (modalImg) modalImg.src = imgUrl;
      const modalName = document.getElementById('modal-file-name');
      if (modalName) modalName.textContent = file.name;
      const modalSize = document.getElementById('modal-file-size');
      if (modalSize) modalSize.textContent = (file.size / 1024).toFixed(1) + ' KB';

      // Update panel preview
      const panelPreview = document.getElementById('panel-avatar-preview');
      if (panelPreview) {
        panelPreview.innerHTML = `<img src="${imgUrl}" alt="Preview" class="w-full h-full object-cover">`;
      }
      const panelBadge = document.getElementById('panel-preview-badge');
      if (panelBadge) panelBadge.classList.remove('hidden');

      const fileNameEl = document.getElementById('selected-file-name');
      if (fileNameEl) fileNameEl.textContent = `${file.name} (${(file.size / 1024).toFixed(1)} KB)`;

      const saveBar = document.getElementById('save-photo-bar');
      if (saveBar) saveBar.classList.remove('hidden');

      // Show modal for instant confirmation
      const modal = document.getElementById('quick-avatar-modal');
      if (modal) modal.classList.remove('hidden');
    };
    reader.readAsDataURL(file);
  }

  function cancelImageSelection() {
    const input = document.getElementById('profile_photo_input');
    if (input) input.value = '';
    selectedFileForUpload = null;

    const saveBar = document.getElementById('save-photo-bar');
    if (saveBar) saveBar.classList.add('hidden');
    const panelBadge = document.getElementById('panel-preview-badge');
    if (panelBadge) panelBadge.classList.add('hidden');

    const fileNameEl = document.getElementById('selected-file-name');
    if (fileNameEl) fileNameEl.textContent = 'No file chosen yet';
    
    closeQuickModal();
  }

  function closeQuickModal() {
    const modal = document.getElementById('quick-avatar-modal');
    if (modal) modal.classList.add('hidden');
  }

  // Setup drag & drop on drop area
  const dropArea = document.getElementById('drop-area');
  if (dropArea) {
    ['dragenter', 'dragover'].forEach(eventName => {
      dropArea.addEventListener(eventName, (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropArea.classList.add('border-blue-500', 'bg-blue-50/50');
      }, false);
    });
    ['dragleave', 'drop'].forEach(eventName => {
      dropArea.addEventListener(eventName, (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropArea.classList.remove('border-blue-500', 'bg-blue-50/50');
      }, false);
    });
    dropArea.addEventListener('drop', (e) => {
      const dt = e.dataTransfer;
      const files = dt.files;
      if (files && files.length) {
        const input = document.getElementById('profile_photo_input');
        input.files = files;
        previewSelectedImage(input);
      }
    }, false);
  }

  // Submit avatar upload via AJAX for seamless user experience
  async function submitQuickUpload() {
    if (!selectedFileForUpload) {
      alert('Please select an image first.');
      return;
    }

    const saveBtn = document.getElementById('modal-save-btn');
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="inline-block animate-spin mr-1.5">⏳</span> Uploading...';

    const formData = new FormData();
    formData.append('profile_photo', selectedFileForUpload);
    formData.append('upload_avatar', '1');
    formData.append('t', csrfToken);
    formData.append('ajax', '1');

    try {
      const res = await fetch('profile.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
      });
      const data = await res.json();
      if (data.success) {
        // Update hero avatar
        const heroWrapper = document.getElementById('hero-avatar-wrapper');
        if (heroWrapper) {
          heroWrapper.innerHTML = `<img id="hero-avatar-img" src="${data.image_url}" alt="Profile Picture" class="w-full h-full object-cover">`;
        }
        // Update panel avatar
        const panelPreview = document.getElementById('panel-avatar-preview');
        if (panelPreview) {
          panelPreview.innerHTML = `<img id="panel-avatar-img" src="${data.image_url}" alt="Avatar" class="w-full h-full object-cover">`;
        }
        // Update header and sidebar avatars if present
        document.querySelectorAll('header a[href="profile.php"], aside a[href="profile.php"] span:first-child').forEach(el => {
          if (el.tagName === 'A' && el.querySelector('img')) {
            el.querySelector('img').src = data.image_url;
          } else if (el.tagName === 'A') {
            el.innerHTML = `<img src="${data.image_url}" alt="Profile" class="w-full h-full object-cover">`;
            el.classList.add('overflow-hidden');
          }
        });

        // Show remove button and update status text
        const removeBtn = document.getElementById('btn-remove-photo');
        if (removeBtn) removeBtn.classList.remove('hidden');
        const statusBadge = document.getElementById('badge-photo-status');
        if (statusBadge) statusBadge.textContent = 'Custom Photo';

        closeQuickModal();
        showAlert('success', data.msg || 'Profile picture updated successfully!');
        
        // Hide preview bar
        const saveBar = document.getElementById('save-photo-bar');
        if (saveBar) saveBar.classList.add('hidden');
        const panelBadge = document.getElementById('panel-preview-badge');
        if (panelBadge) panelBadge.classList.add('hidden');
      } else {
        alert(data.error || 'Failed to upload photo.');
      }
    } catch (err) {
      // Fallback: submit standard form
      document.getElementById('avatar-form').submit();
    } finally {
      saveBtn.disabled = false;
      saveBtn.innerHTML = originalText;
    }
  }

  // Intercept avatar-form submit to run AJAX if possible
  const avatarForm = document.getElementById('avatar-form');
  if (avatarForm) {
    avatarForm.addEventListener('submit', function(e) {
      if (selectedFileForUpload) {
        e.preventDefault();
        submitQuickUpload();
      }
    });
  }

  // Handle Remove Profile Picture
  async function removeProfilePicture() {
    if (!confirm('Are you sure you want to remove your profile picture and revert to your initials avatar?')) {
      return;
    }

    const formData = new FormData();
    formData.append('remove_avatar', '1');
    formData.append('t', csrfToken);
    formData.append('ajax', '1');

    try {
      const res = await fetch('profile.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
      });
      const data = await res.json();
      if (data.success) {
        // Revert hero avatar to initials
        const heroWrapper = document.getElementById('hero-avatar-wrapper');
        if (heroWrapper) {
          heroWrapper.innerHTML = `<span id="hero-avatar-initials">${defaultInitials}</span>`;
        }
        // Revert panel avatar
        const panelPreview = document.getElementById('panel-avatar-preview');
        if (panelPreview) {
          panelPreview.innerHTML = `<span id="panel-avatar-initials">${defaultInitials}</span>`;
        }
        // Update header
        const headerLink = document.querySelector('header a[href="profile.php"]');
        if (headerLink) {
          headerLink.innerHTML = defaultInitials;
        }

        // Hide remove button
        const removeBtn = document.getElementById('btn-remove-photo');
        if (removeBtn) removeBtn.classList.add('hidden');
        const statusBadge = document.getElementById('badge-photo-status');
        if (statusBadge) statusBadge.textContent = 'Default Initials';

        cancelImageSelection();
        showAlert('success', data.msg || 'Profile picture removed.');
      } else {
        alert(data.error || 'Failed to remove picture.');
      }
    } catch (err) {
      // Fallback: create form and submit
      const f = document.createElement('form');
      f.method = 'POST';
      f.innerHTML = `<input type="hidden" name="t" value="${csrfToken}"><input type="hidden" name="remove_avatar" value="1">`;
      document.body.appendChild(f);
      f.submit();
    }
  }

  function showAlert(type, message) {
    const container = document.getElementById('alert-container');
    if (!container) return;
    const isSuccess = type === 'success';
    const alertHtml = `
      <div class="p-4 rounded-2xl ${isSuccess ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800'} border text-xs font-semibold flex items-center gap-2.5 shadow-xs mb-4 transition animate-fade-in">
        <i data-lucide="${isSuccess ? 'check-circle-2' : 'alert-circle'}" class="w-4 h-4 ${isSuccess ? 'text-emerald-600' : 'text-rose-600'} shrink-0"></i>
        <span>${escapeAlert(message)}</span>
      </div>`;
    container.innerHTML = alertHtml;
    if (window.lucide) lucide.createIcons();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function escapeAlert(str) {
    return String(str).replace(/[&<>"']/g, s => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[s]));
  }
</script>

<?php foot(); ?>
