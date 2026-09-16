<?php
function avisoForeground($tipo, $titulo, $mensaje, $botonTexto = 'Cerrar', $botonUrl = '') {
    $tipo = in_array($tipo, ['success', 'warning', 'error']) ? $tipo : 'error';
    $iconos = [
        'success' => '<svg viewBox="0 0 24 24" width="44" height="44" fill="none" stroke="#16A34A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>',
        'error' => '<svg viewBox="0 0 24 24" width="44" height="44" fill="none" stroke="#DC2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>',
        'warning' => '<svg viewBox="0 0 24 24" width="44" height="44" fill="none" stroke="#D97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>'
    ];
    if ($botonUrl !== '') {
        $accion = '<a href="' . htmlspecialchars($botonUrl) . '" class="btn btn-primary">' . htmlspecialchars($botonTexto) . '</a>';
    } else {
        $accion = '<button type="button" class="btn btn-primary" onclick="window.history.back();">' . htmlspecialchars($botonTexto) . '</button>';
    }
    echo '<div class="rw-modal">'
        . '<div class="rw-modal__overlay"></div>'
        . '<div class="rw-modal__dialog rw-modal__dialog--' . $tipo . '">'
        . '<div class="rw-modal__icon">' . $iconos[$tipo] . '</div>'
        . '<h3 class="rw-modal__title">' . htmlspecialchars($titulo) . '</h3>'
        . '<p class="rw-modal__message">' . htmlspecialchars($mensaje) . '</p>'
        . '<div class="rw-modal__actions">' . $accion . '</div>'
        . '</div>'
        . '</div>';
}