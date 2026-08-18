<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
if (is_authenticated()) log_security_event('logout', 'Cierre de sesión manual.');
logout_user(true);
session_start();
flash('success', 'Sesión cerrada correctamente.');
redirect('auth/login.php');
