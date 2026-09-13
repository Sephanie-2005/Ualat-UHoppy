<?php
    session_start();
    session_unset();
    session_destroy();
    echo "<script>window.location.href = '../default-browser/index.php';</script>";
    exit();
?>
