<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_role('cliente');
redirect('cliente/dashboard.php');
