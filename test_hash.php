<?php
$hash = '$2y$10$DaKEv1zKZb0Zk2zWq1eXLe6/2b7a6qyslR/7Zs3o5g6cE1zYJq1yG';
var_dump(password_verify("admin123", $hash));