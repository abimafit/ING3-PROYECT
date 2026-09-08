document.addEventListener('DOMContentLoaded', function () {
    // Validación login
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            const email = document.querySelector('input[name="email"]').value.trim();
            const pass = document.querySelector('input[name="password"]').value.trim();
            if (!email || !pass) {
                e.preventDefault();
                alert('Completa todos los campos');
            } else if (!email.includes('@')) {
                e.preventDefault();
                alert('Correo inválido');
            }
        });
    }

    // Validación registro
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            console.log('Validando registro...');
            const pass = document.getElementById('password');
            const confirmPass = document.getElementById('confirm_password');

            // Si no existen los campos, mostrar error en consola
            if (!pass || !confirmPass) {
                console.error('No se encontraron los campos password o confirm_password');
                return;
            }

            const passValue = pass.value.trim();
            const confirmValue = confirmPass.value.trim();
            const rol = document.querySelector('select[name="rol"]').value;
            const ciudad = document.getElementById('ciudad').value.trim();

            console.log('pass:', passValue, 'confirm:', confirmValue);

            // Validar longitud
            if (passValue.length < 4) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 4 caracteres');
                return;
            }

            function validarNombre(nombre) {
                // Permitir letras (incl. acentos, ñ), espacios, guiones, apóstrofes y puntos
                // Bloquear < > & = " ' ` (excepto apóstrofe simple que se usa en nombres)
                const regexPeligrosos = /[<>]/;  // Solo los más riesgosos
                return !regexPeligrosos.test(nombre);
            }

            // Validar coincidencia
            if (passValue !== confirmValue) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                return;
            }

            // Validar ciudad para compañía
            if (rol === 'compania' && ciudad === '') {
                e.preventDefault();
                alert('Debes ingresar la ciudad de la compañía');
                return;
            }
        });
    }
});