<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

echo '<pre>';
echo 'Sesión actual: ';
print_r($_SESSION);
echo '</pre>';

if (estaLogueado()) {
    echo 'Estás logueado como ' . $_SESSION['nombre'] . ' (' . $_SESSION['rol'] . ')';
} else {
    echo 'No estás logueado.';
}
?>