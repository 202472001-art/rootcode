<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_role('administrador');
redirect('admin/dashboard.php');
