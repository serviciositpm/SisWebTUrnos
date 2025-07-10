<?php
// Iniciar sesión al principio para manejar posibles errores
session_start();
$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Sistema Gestión de Turnos</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--===============================================================================================-->
    <link rel="icon" type="image/png" href="../../assets/images/icons/favicon.ico" />
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="../../assets/vendor/bootstrap/css/bootstrap.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="../../assets/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="../../assets/vendor/animate/animate.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="../../assets/vendor/css-hamburgers/hamburgers.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="../../assets/vendor/select2/select2.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="../../assets/css/util.css">
    <link rel="stylesheet" type="text/css" href="../../assets/css/main.css">
    <!--===============================================================================================-->
</head>

<body>
    <div class="limiter">
        <div class="container-login100">
            <div class="wrap-login100">
                <div class="login100-pic js-tilt" data-tilt>
                    <img src="../../assets/images/img-01.png" alt="IMG">
                </div>

                <form class="login100-form validate-form" id="loginForm" method="POST">
                    <span class="login100-form-title">
                        Ingreso al Sistema
                    </span>

                    <div id="loginError" class="alert alert-danger" style="display: none;"></div>

                    <div class="wrap-input100 validate-input" data-validate="Usuario es Requerido: PAPELLIDOP">
                        <input class="input100" type="text" name="username" placeholder="Usuario" required>
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-envelope" aria-hidden="true"></i>
                        </span>
                    </div>

                    <div class="wrap-input100 validate-input" data-validate="Password is required">
                        <input class="input100" type="password" name="pass" placeholder="***********" required>
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-lock" aria-hidden="true"></i>
                        </span>
                    </div>

                    <div class="container-login100-form-btn">
                        <button type="submit" class="login100-form-btn" id="loginButton">
                            Ingresar
                        </button>
                    </div>
                    <div class="text-center p-t-12">
                        <a href="#" class="txt2" id="changePasswordLink">Cambiar Contraseña</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog"
        aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 15px; overflow: hidden; border: none;">
                <div class="modal-header"
                    style="background: linear-gradient(135deg, #57b846 0%, #3d8b2e 100%); border-bottom: none; padding: 20px 30px;">
                    <h5 class="modal-title text-white" id="changePasswordModalLabel"
                        style="font-family: 'Poppins-Bold'; font-size: 1.5rem;">
                        <i class="fa fa-key mr-2"></i>Cambiar Contraseña
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"
                        style="opacity: 0.8;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <form id="changePasswordForm">
                        <div class="form-group mb-4">
                            <label for="username" class="txt2" style="font-size: 14px; color: #666; font-weight: 500;">
                                <i class="fa fa-user mr-1"></i>Usuario
                            </label>
                            <div class="wrap-input100 validate-input">
                                <input class="input100" type="text" id="username" placeholder="Ingrese su usuario"
                                    required>
                                <span class="focus-input100"></span>
                                <span class="symbol-input100">
                                    <i class="fa fa-user" aria-hidden="true"></i>
                                </span>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="currentPassword" class="txt2"
                                style="font-size: 14px; color: #666; font-weight: 500;">
                                <i class="fa fa-lock mr-1"></i>Contraseña actual
                            </label>
                            <div class="wrap-input100 validate-input">
                                <input class="input100" type="password" id="currentPassword"
                                    placeholder="Ingrese su contraseña actual" required>
                                <span class="focus-input100"></span>
                                <span class="symbol-input100">
                                    <i class="fa fa-lock" aria-hidden="true"></i>
                                </span>
                                <span class="toggle-password" data-target="#currentPassword"
                                    style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                </span>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="newPassword" class="txt2"
                                style="font-size: 14px; color: #666; font-weight: 500;">
                                <i class="fa fa-key mr-1"></i>Nueva contraseña
                            </label>
                            <div class="wrap-input100 validate-input">
                                <input class="input100" type="password" id="newPassword"
                                    placeholder="Ingrese nueva contraseña" required>
                                <span class="focus-input100"></span>
                                <span class="symbol-input100">
                                    <i class="fa fa-key" aria-hidden="true"></i>
                                </span>
                                <span class="toggle-password" data-target="#newPassword"
                                    style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                </span>
                            </div>
                            <div class="password-feedback mt-2 ml-3">
                                <div class="progress" style="height: 5px; border-radius: 3px; background: #e6e6e6;">
                                    <div id="passwordStrength" class="progress-bar" role="progressbar"
                                        style="transition: all 0.3s ease;"></div>
                                </div>
                                <small class="text-muted d-block mt-2" style="font-size: 12px;">
                                    <span id="passwordTips">La contraseña debe tener al menos 7 caracteres</span>
                                </small>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="confirmPassword" class="txt2"
                                style="font-size: 14px; color: #666; font-weight: 500;">
                                <i class="fa fa-check-circle mr-1"></i>Confirmar nueva contraseña
                            </label>
                            <div class="wrap-input100 validate-input">
                                <input class="input100" type="password" id="confirmPassword"
                                    placeholder="Confirme nueva contraseña" required>
                                <span class="focus-input100"></span>
                                <span class="symbol-input100">
                                    <i class="fa fa-check-circle" aria-hidden="true"></i>
                                </span>
                                <span class="toggle-password" data-target="#confirmPassword"
                                    style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                </span>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4" style="background: transparent;">
                    <button type="button" class="btn btn-secondary btn-lg" data-dismiss="modal" style="
                    font-family: 'Poppins-Medium';
                    border-radius: 25px;
                    padding: 0 30px;
                    height: 45px;
                    background: #e6e6e6;
                    color: #666;
                    border: none;
                    transition: all 0.4s;
                ">
                        <i class="fa fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-primary btn-lg" id="submitChangePassword" style="
                    font-family: 'Poppins-Medium';
                    border-radius: 25px;
                    padding: 0 30px;
                    height: 45px;
                    background: #57b846;
                    border: none;
                    transition: all 0.4s;
                ">
                        <i class="fa fa-save mr-1"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>


    <script src="../../assets/vendor/jquery/jquery-3.2.1.min.js"></script>
    <script src="../../assets/vendor/bootstrap/js/popper.js"></script>
    <script src="../../assets/vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="../../assets/vendor/select2/select2.min.js"></script>
    <script src="../../assets/vendor/tilt/tilt.jquery.min.js"></script>
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $('.js-tilt').tilt({
            scale: 1.1
        })
    </script>
    <script src="../../assets/js/main.js"></script>
    <script>
        $(document).ready(function () {
            // Mostrar/ocultar contraseña
            $(document).on('click', '.toggle-password', function () {
                const target = $(this).data('target');
                const input = $(target);
                const icon = $(this).find('i');

                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            // Mostrar modal
            $('#changePasswordLink').click(function (e) {
                e.preventDefault();
                $('#changePasswordModal').modal('show');
            });

            // Validar fortaleza de la contraseña
            $('#newPassword').on('keyup', function () {
                const password = $(this).val();
                const strengthBar = $('#passwordStrength');
                const tips = $('#passwordTips');

                if (password.length === 0) {
                    strengthBar.css({
                        'width': '0%',
                        'background-color': 'transparent'
                    });
                    tips.text('La contraseña debe tener exactamente 7 caracteres').css('color', '#666');
                    return;
                }

                // Medir fortaleza
                let strength = 0;

                // Longitud exacta de 7 caracteres
                if (password.length === 7) strength += 2;

                // Caracteres especiales
                if (password.match(/[!@#$%^&*(),.?":{}|<>]/)) strength += 1;

                // Números
                if (password.match(/\d/)) strength += 1;

                // Mayúsculas y minúsculas
                if (password.match(/[A-Z]/) && password.match(/[a-z]/)) strength += 1;

                // Actualizar barra y mensajes
                if (password.length < 7) {
                    strengthBar.css({
                        'width': `${(password.length / 7) * 33}%`,
                        'background-color': '#ff4757'
                    });
                    tips.text(`Muy corta (${password.length}/7 caracteres)`).css('color', '#ff4757');
                } else if (password.length > 7) {
                    strengthBar.css({
                        'width': '100%',
                        'background-color': '#ff4757'
                    });
                    tips.text('Demasiado larga (máximo 7 caracteres)').css('color', '#ff4757');
                } else if (strength < 2) {
                    strengthBar.css({
                        'width': '50%',
                        'background-color': '#ffa502'
                    });
                    tips.text('Básica - solo longitud correcta').css('color', '#ffa502');
                } else if (strength < 4) {
                    strengthBar.css({
                        'width': '75%',
                        'background-color': '#2ed573'
                    });
                    tips.text('Fuerte - buena contraseña').css('color', '#2ed573');
                } else {
                    strengthBar.css({
                        'width': '100%',
                        'background-color': '#57b846'
                    });
                    tips.text('Excelente - cumple todos los requisitos').css('color', '#57b846');
                }
            });
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                const form = $(this);
                const button = $('#loginButton');
                const errorDiv = $('#loginError');
                
                // Mostrar estado de carga
                button.html('<i class="fa fa-spinner fa-spin"></i> Procesando...');
                button.prop('disabled', true);
                errorDiv.hide();
                
                // Enviar datos via AJAX
                $.ajax({
                    url: '../../controllers/AuthController.php?action=login',
                    type: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Redireccionar después de login exitoso
                            window.location.href = response.redirectUrl || '../../views/home.php';
                        } else {
                            // Mostrar error
                            errorDiv.text(response.message || 'Error desconocido').show();
                            button.html('Ingresar');
                            button.prop('disabled', false);
                            
                            // Efecto de shake para indicar error
                            form.addClass('animate__animated animate__shakeX');
                            setTimeout(() => {
                                form.removeClass('animate__animated animate__shakeX');
                            }, 1000);
                        }
                    },
                    error: function(xhr, status, error) {
                        errorDiv.text('Error de conexión con el servidor').show();
                        button.html('Ingresar');
                        button.prop('disabled', false);
                    }
                });
            });

            // Manejar el envío del formulario con SweetAlert
            $('#submitChangePassword').click(function () {
                const username = $('#username').val();
                const currentPassword = $('#currentPassword').val();
                const newPassword = $('#newPassword').val();
                const confirmPassword = $('#confirmPassword').val();

                // Validaciones básicas
                if (!username) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'El usuario es requerido',
                        confirmButtonColor: '#57b846',
                    });
                    return;
                }

                if (!currentPassword || !newPassword || !confirmPassword) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Todos los campos son requeridos',
                        confirmButtonColor: '#57b846',
                    });
                    return;
                }

                if (newPassword !== confirmPassword) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Las contraseñas no coinciden',
                        confirmButtonColor: '#57b846',
                    });
                    return;
                }

                if (newPassword.length < 7) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'La contraseña debe tener al menos 7 caracteres',
                        confirmButtonColor: '#57b846',
                    });
                    return;
                }

                // Mostrar loader
                Swal.fire({
                    title: 'Procesando',
                    html: 'Actualizando contraseña...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Enviar datos al servidor
                $.ajax({
                    url: '../../controllers/AuthController.php?action=changePassword',
                    type: 'POST',
                    data: {
                        username: username,
                        current_password: currentPassword,
                        new_password: newPassword,
                        confirm_password: confirmPassword
                    },
                    success: function (response) {
                        Swal.close();
                        try {
                            const data = JSON.parse(response);
                            if (data.success) {
                                // Mostrar SweetAlert de éxito con animación
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Éxito!',
                                    text: data.message || 'Contraseña actualizada correctamente',
                                    showConfirmButton: true,
                                    confirmButtonColor: '#4e73df',
                                    timer: 3000,
                                    timerProgressBar: true,
                                    willClose: () => {
                                        $('#changePasswordModal').modal('hide');
                                        $('#changePasswordForm')[0].reset();
                                        $('#passwordStrength').css('width', '0%').removeClass('bg-success');
                                    }
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.message || 'Error al cambiar la contraseña',
                                    confirmButtonColor: '#4e73df',
                                });
                            }
                        } catch (e) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error al procesar la respuesta del servidor',
                                confirmButtonColor: '#4e73df',
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de conexión',
                            text: 'No se pudo conectar con el servidor',
                            confirmButtonColor: '#4e73df',
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>