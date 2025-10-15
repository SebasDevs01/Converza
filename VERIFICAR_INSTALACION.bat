@echo off
echo ==========================================
echo   VERIFICACION SISTEMA CONVERZA
echo   Sistema de Karma y Asistente
echo ==========================================
echo.

echo [1/5] Verificando archivo widget PHP...
if exist "app\microservices\converza-assistant\widget\assistant-widget.php" (
    echo [OK] Widget PHP existe
) else (
    echo [ERROR] Widget PHP no encontrado
    pause
    exit /b 1
)
echo.

echo [2/5] Verificando archivo widget HTML...
if exist "app\microservices\converza-assistant\widget\assistant-widget.html" (
    echo [OK] Widget HTML existe
) else (
    echo [ERROR] Widget HTML no encontrado
    pause
    exit /b 1
)
echo.

echo [3/5] Verificando archivos modificados...
if exist "app\view\index.php" (
    echo [OK] index.php existe
) else (
    echo [ERROR] index.php no encontrado
)

if exist "app\presenters\perfil.php" (
    echo [OK] perfil.php existe
) else (
    echo [ERROR] perfil.php no encontrado
)

if exist "app\presenters\albumes.php" (
    echo [OK] albumes.php existe
) else (
    echo [ERROR] albumes.php no encontrado
)

if exist "app\presenters\save_reaction.php" (
    echo [OK] save_reaction.php existe
) else (
    echo [ERROR] save_reaction.php no encontrado
)
echo.

echo [4/5] Verificando sistema de karma...
if exist "app\models\karma-social-helper.php" (
    echo [OK] karma-social-helper.php existe
) else (
    echo [ERROR] karma-social-helper.php no encontrado
)

if exist "app\models\karma-social-triggers.php" (
    echo [OK] karma-social-triggers.php existe
) else (
    echo [ERROR] karma-social-triggers.php no encontrado
)
echo.

echo [5/5] Verificando documentacion...
if exist "CORRECCIONES_KARMA_ASISTENTE.md" (
    echo [OK] Documentacion de correcciones existe
) else (
    echo [WARN] Documentacion de correcciones no encontrada
)

if exist "RESUMEN_CORRECCIONES_APLICADAS.md" (
    echo [OK] Resumen ejecutivo existe
) else (
    echo [WARN] Resumen ejecutivo no encontrado
)
echo.

echo ==========================================
echo   VERIFICACION COMPLETADA
echo ==========================================
echo.
echo Recomendaciones:
echo 1. Reiniciar Apache en XAMPP
echo 2. Limpiar cache del navegador (Ctrl+Shift+Delete)
echo 3. Abrir http://localhost/converza
echo 4. Verificar que el widget aparece (boton flotante abajo derecha)
echo 5. Probar reacciones con 2 usuarios diferentes
echo.
pause
