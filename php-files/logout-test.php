<?php
    session_start();
    session_unset();
    session_destroy();
    header("Location: ./default-browser/index.php");
    exit();
?>
