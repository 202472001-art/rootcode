<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_role('administrador');
redirect('admin/dashboard.php');
