<?php
/**
 * includes/footer.php
 * Cierre de estructura HTML y scripts
 */

// Determinar si estamos en pages/ o en la raíz
$base_path = (strpos($_SERVER['PHP_SELF'], '/pages/') !== false) ? '../' : '';
?>
</main>

<script src="<?php echo $base_path; ?>assets/js/app.js"></script>
</body>
</html>
