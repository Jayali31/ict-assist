ICT ASSIST - XAMPP SETUP
1. Copy this folder to C:\xampp\htdocs\ict-assist
2. Start Apache + MySQL in the XAMPP Control Panel
3. For a new installation, open http://localhost/phpmyadmin -> Import -> choose database/ict_assist.sql -> Import. This recreates the database, so do not use it to update a database that contains data you need to keep.
4. For an existing installation, select the ict_assist database in phpMyAdmin -> Import -> choose database\migrations\2026_10_15_profile_images.sql -> Import. This adds the profile-image column and refreshes the affected views without deleting existing data. It is safe to run again.
5. Open http://localhost/ict-assist/
Demo password for all users: Ict@12345
 employee:    a.m.emandi@ictassist.com
 coordinator: coordinator@ictassist.com
 technician:  ratnasiri@ictassist.com
If your MySQL root has a password, edit config.php.
