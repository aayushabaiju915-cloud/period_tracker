<?php
// ============================================================
// pages/logout.php — Destroy Session and Redirect
// ============================================================
require_once '../config/auth.php';
logout(); // defined in auth.php — destroys session & redirects
