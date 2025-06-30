<?php
$db = new PDO('pgsql:host=localhost;dbname=private_space', 'postgres', 'password');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
