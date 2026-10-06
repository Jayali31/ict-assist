<?php
require 'config.php';
$target = $_GET['role'] ?? 'employee';
$users = [
  'employee' => 'a.m.emandi@ictassist.com',
  'coordinator' => 'coordinator@ictassist.com',
  'staff' => 'ratnasiri@ictassist.com'
];
if (isset($users[$target])) {
  $u = q("SELECT * FROM users WHERE email=? AND is_active=1", [$users[$target]])->fetch();
  if ($u) {
    session_regenerate_id(true);
    $_SESSION['u'] = [
      'id' => $u['id'],
      'name' => $u['name'],
      'role' => $u['role'],
      'email' => $u['email']
    ];
  }
}
$from = $_GET['from'] ?? 'dashboard.php';
header("Location: $from");
exit;
