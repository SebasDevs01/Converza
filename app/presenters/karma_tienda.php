<?php
/**
 * KARMA TIENDA - SISTEMA DE RECOMPENSAS
 * Página donde los usuarios pueden desbloquear y equipar recompensas con Karma
 */

session_start();
require_once(__DIR__.'/../models/config.php');
require_once(__DIR__.'/../models/karma-social-helper.php');

// Verificar sesión
if (!isset($_SESSION['id'])) {
    header('Location: ../view/login.php');
    exit;
}

$karmaHelper = new KarmaSocialHelper($conexion);
$usuario_id = $_SESSION['id'];

// Obtener datos del usuario (karma, nivel, etc)
$karmaData = $karmaHelper->obtenerKarmaUsuario($usuario_id);
$karma = $karmaData['karma_total'];
$nivel = $karmaData['nivel_data']['nivel'] ?? 1;
$nivel_titulo = $karmaData['nivel'];

// Procesar desbloqueo de recompensa con EQUIPADO AUTOMÁTICO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['desbloquear'])) {
    $recompensa_id = (int)$_POST['recompensa_id'];
    
    // Obtener info de la recompensa
    $stmtRecomp = $conexion->prepare("SELECT * FROM karma_recompensas WHERE id = ?");
    $stmtRecomp->execute([$recompensa_id]);
    $recompensa = $stmtRecomp->fetch(PDO::FETCH_ASSOC);
    
    if ($recompensa && $karma >= $recompensa['karma_requerido']) {
        // Verificar que no la tiene ya
        $stmtCheck = $conexion->prepare("SELECT id FROM usuario_recompensas WHERE usuario_id = ? AND recompensa_id = ?");
        $stmtCheck->execute([$usuario_id, $recompensa_id]);
        
        if (!$stmtCheck->fetch()) {
            // 🎯 NUEVA LÓGICA: EQUIPAMIENTO ÚNICO POR CATEGORÍA
            // Solo se puede tener 1 ítem equipado por tipo a la vez
            $auto_equipar = false;
            $tipo = $recompensa['tipo'];
            
            // Para TODOS los tipos: desequipar otros del mismo tipo antes de equipar
            // Esto asegura que solo haya 1 ítem equipado por categoría
            $stmtDesequipar = $conexion->prepare("
                UPDATE usuario_recompensas ur
                JOIN karma_recompensas kr ON ur.recompensa_id = kr.id
                SET ur.equipada = FALSE
                WHERE ur.usuario_id = ? AND kr.tipo = ?
            ");
            $stmtDesequipar->execute([$usuario_id, $tipo]);
            
            // Auto-equipar según el tipo
            if (in_array($tipo, ['marco', 'tema', 'icono', 'color_nombre', 'color', 'sticker'])) {
                // Estos tipos se equipan automáticamente al desbloquear
                $auto_equipar = true;
            } elseif ($tipo == 'insignia') {
                // Insignias: requieren equipamiento manual
                $auto_equipar = false;
            }
            
            // Insertar con equipado automático según tipo
            $stmtInsert = $conexion->prepare("
                INSERT INTO usuario_recompensas (usuario_id, recompensa_id, fecha_desbloqueo, equipada) 
                VALUES (?, ?, NOW(), ?)
            ");
            $stmtInsert->execute([$usuario_id, $recompensa_id, $auto_equipar ? 1 : 0]);
            
            // 💰 DESCONTAR KARMA (registrar como acción negativa)
            $karma_gastado = -abs($recompensa['karma_requerido']); // Asegurar que sea negativo
            $stmt_karma = $conexion->prepare("
                INSERT INTO karma_social 
                (usuario_id, tipo_accion, puntos, referencia_id, referencia_tipo, descripcion, fecha_accion)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $resultado_karma = $stmt_karma->execute([
                $usuario_id,
                'compra_tienda',
                $karma_gastado,
                $recompensa_id,
                'recompensa',
                "Compra en tienda: {$recompensa['nombre']}"
            ]);
            
            // Verificar si hubo error
            if (!$resultado_karma) {
                error_log("Error al descontar karma: " . print_r($stmt_karma->errorInfo(), true));
            }
            
            $mensaje_tipo = '';
            switch($tipo) {
                case 'marco':
                    $mensaje_tipo = '🖼️ Marco aplicado a tu avatar';
                    break;
                case 'tema':
                    $mensaje_tipo = '🎨 Tema aplicado a tu perfil';
                    break;
                case 'icono':
                    $mensaje_tipo = '⭐ Ícono visible junto a tu nombre';
                    break;
                case 'color_nombre':
                case 'color':
                    $mensaje_tipo = '🌈 Color aplicado a tu nombre';
                    break;
                case 'sticker':
                    $mensaje_tipo = '😊 Stickers disponibles en tu perfil';
                    break;
                case 'insignia':
                    $mensaje_tipo = '🏅 Insignia desbloqueada';
                    break;
                default:
                    $mensaje_tipo = '✨ Recompensa lista para usar';
            }
            
            $mensaje_exito = "¡Desbloqueado: {$recompensa['nombre']}! {$mensaje_tipo}" . ($auto_equipar ? ' (Equipado automáticamente)' : '');
            
            // Recargar karma actualizado
            $karmaData = $karmaHelper->obtenerKarmaUsuario($usuario_id);
            $karma = $karmaData['karma_total'];
            
            // Redirigir para mostrar karma actualizado
            $_SESSION['mensaje_exito'] = $mensaje_exito;
            header("Location: karma_tienda.php");
            exit;
        } else {
            $mensaje_error = "Ya tienes esta recompensa desbloqueada";
        }
    } else {
        $mensaje_error = "No tienes suficiente karma para desbloquear esta recompensa";
    }
}

// Procesar equipar/desequipar recompensa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['equipar'])) {
    $recompensa_id = (int)$_POST['recompensa_id'];
    
    // Obtener información de la recompensa y verificar que esté desbloqueada
    $stmtCheck = $conexion->prepare("
        SELECT ur.id, ur.equipada, kr.tipo 
        FROM usuario_recompensas ur
        JOIN karma_recompensas kr ON ur.recompensa_id = kr.id
        WHERE ur.usuario_id = ? AND ur.recompensa_id = ?
    ");
    $stmtCheck->execute([$usuario_id, $recompensa_id]);
    $usuarioRecompensa = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    
    if ($usuarioRecompensa) {
        $nueva_equipada = !$usuarioRecompensa['equipada'];
        
        // 🎯 EQUIPAMIENTO ÚNICO: Si va a equipar, desequipar otros del mismo tipo primero
        if ($nueva_equipada) {
            $tipo = $usuarioRecompensa['tipo'];
            $stmtDesequiparOtros = $conexion->prepare("
                UPDATE usuario_recompensas ur
                JOIN karma_recompensas kr ON ur.recompensa_id = kr.id
                SET ur.equipada = FALSE
                WHERE ur.usuario_id = ? AND kr.tipo = ? AND ur.recompensa_id != ?
            ");
            $stmtDesequiparOtros->execute([$usuario_id, $tipo, $recompensa_id]);
        }
        
        // Actualizar el estado de esta recompensa
        $stmtEquip = $conexion->prepare("UPDATE usuario_recompensas SET equipada = ? WHERE id = ?");
        $stmtEquip->execute([$nueva_equipada, $usuarioRecompensa['id']]);
        $mensaje_exito = $nueva_equipada ? "Recompensa equipada (otras del mismo tipo se desequiparon automáticamente)" : "Recompensa desequipada";
        
        // Refrescar karma después de equipar/desequipar
        $karmaData = $karmaHelper->obtenerKarmaUsuario($usuario_id);
        $karma = $karmaData['karma_total'];
    }
}

// Obtener recompensas desbloqueadas
$stmtUnlocked = $conexion->prepare("
    SELECT recompensa_id, equipada 
    FROM usuario_recompensas 
    WHERE usuario_id = ?
");
$stmtUnlocked->execute([$usuario_id]);
$recompensas_desbloqueadas = [];
while ($row = $stmtUnlocked->fetch(PDO::FETCH_ASSOC)) {
    $recompensas_desbloqueadas[$row['recompensa_id']] = $row['equipada'];
}

// 🎯 Obtener qué tipos tienen ítems equipados (para validación de equipamiento único)
$stmtTiposEquipados = $conexion->prepare("
    SELECT DISTINCT kr.tipo 
    FROM usuario_recompensas ur
    JOIN karma_recompensas kr ON ur.recompensa_id = kr.id
    WHERE ur.usuario_id = ? AND ur.equipada = TRUE
");
$stmtTiposEquipados->execute([$usuario_id]);
$tipos_con_equipado = [];
while ($row = $stmtTiposEquipados->fetch(PDO::FETCH_ASSOC)) {
    $tipos_con_equipado[] = $row['tipo'];
}

// Recuperar mensaje de sesión si existe
if (isset($_SESSION['mensaje_exito'])) {
    $mensaje_exito = $_SESSION['mensaje_exito'];
    unset($_SESSION['mensaje_exito']);
}

// Obtener todas las recompensas agrupadas por tipo
// 🚫 EXCLUIR temas, stickers e íconos (no implementados aún)
$stmtRecompensas = $conexion->prepare("
    SELECT * FROM karma_recompensas 
    WHERE activo = TRUE 
    AND tipo NOT IN ('tema', 'sticker', 'icono')
    ORDER BY tipo, karma_requerido ASC
");
$stmtRecompensas->execute();
$recompensas = $stmtRecompensas->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por tipo
$recompensas_por_tipo = [];
foreach ($recompensas as $r) {
    $recompensas_por_tipo[$r['tipo']][] = $r;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏆 Tienda de Karma - Converza</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/Converza/public/css/karma-recompensas.css?v=2.6">
    
    <?php 
    // 🎨 SISTEMA DE TEMAS GLOBAL - Aplicar tema equipado a la tienda también
    require_once __DIR__ . '/../models/tema-global-aplicar.php';
    ?>
    
    <style>
        /* Estilos base - se adaptan al tema equipado */
        body.tema-default {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .header-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(13, 110, 253, 0.2);
            margin-bottom: 30px;
        }
        .karma-display {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            border-radius: 15px;
            padding: 20px;
            color: white;
            text-align: center;
        }
        .karma-points {
            font-size: 3rem;
            font-weight: 700;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .recompensa-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            height: 100%;
            transition: all 0.3s ease;
            border: 3px solid transparent;
            position: relative;
            overflow: hidden;
        }
        .recompensa-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }
        .recompensa-card.bloqueada {
            opacity: 0.6;
            background: #f8f9fa;
        }
        .recompensa-card.desbloqueada {
            border-color: #28a745;
        }
        .recompensa-card.equipada {
            border-color: #0d6efd;
            background: linear-gradient(135deg, rgba(13,110,253,0.1), rgba(10,88,202,0.1));
        }
        .recompensa-icono {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        .recompensa-karma {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
            margin-top: 10px;
        }
        .badge-equipada {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #0d6efd;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .badge-desbloqueada {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .tipo-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }
        .tipo-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 15px;
        }
        .btn-desbloquear {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            border: none;
            color: white;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .btn-desbloquear:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(102,126,234,0.4);
        }
        .btn-equipar {
            background: #28a745;
            border: none;
            color: white;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 25px;
        }
        .btn-desequipar {
            background: #6c757d;
            border: none;
            color: white;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 25px;
        }
        
        /* 🎨 ESTILOS DE PREVIEWS */
        .marco-preview-container {
            padding: 20px;
        }
        .avatar-preview-img {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            border: 2px solid white;
        }
        .tema-preview-container {
            padding: 10px;
        }
        .tema-preview-box {
            height: 100px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            margin: 10px 0;
            transition: transform 0.3s ease;
        }
        .tema-preview-box:hover {
            transform: scale(1.05);
        }
        .recompensa-card:hover .avatar-karma-container {
            transform: scale(1.1);
        }
        .recompensa-card:hover .tema-preview-box {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header con Karma actual -->
        <div class="header-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-3">
                        <i class="bi bi-trophy-fill text-warning"></i> 
                        Tienda de Recompensas Karma
                    </h1>
                    <p class="text-muted lead mb-3">
                        Desbloquea recompensas exclusivas ganando Karma por tus acciones positivas
                    </p>
                </div>
                <div class="col-md-4">
                    <div class="karma-display">
                        <div class="karma-points"><?php echo $karma; ?></div>
                        <div>Puntos de Karma</div>
                        <div class="mt-2">Nivel <?php echo $nivel; ?></div>
                    </div>
                </div>
            </div>
            
            <?php if (isset($mensaje_exito)): ?>
                <div class="alert alert-success alert-dismissible fade show mt-3 shadow-lg border-0" role="alert" style="background: linear-gradient(135deg, #28a745, #20c997); color: white;">
                    <h5 class="mb-2">
                        <i class="bi bi-stars"></i> ¡Felicidades! 
                    </h5>
                    <p class="mb-2"><?php echo htmlspecialchars($mensaje_exito); ?></p>
                    <small>✨ Ahora puedes equiparla y disfrutar de tu nueva recompensa</small>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($mensaje_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($mensaje_error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recompensas por tipo -->
        <?php 
        $tipo_nombres = [
            'tema' => ['nombre' => 'Temas', 'icono' => '🎨', 'color' => '#0d6efd'],
            'marco' => ['nombre' => 'Marcos de Perfil', 'icono' => '🖼️', 'color' => '#0dcaf0'],
            'insignia' => ['nombre' => 'Insignias', 'icono' => '🏅', 'color' => '#ffc107'],
            'icono' => ['nombre' => 'Íconos Especiales', 'icono' => '⭐', 'color' => '#0d6efd'],
            'color' => ['nombre' => 'Colores de Nombre', 'icono' => '🌈', 'color' => '#198754'],
            'sticker' => ['nombre' => 'Stickers BONUS', 'icono' => '🎁', 'color' => '#dc3545']
        ];
        
        foreach ($recompensas_por_tipo as $tipo => $items): 
            $tipoData = $tipo_nombres[$tipo] ?? ['nombre' => ucfirst($tipo), 'icono' => '📦', 'color' => '#6c757d'];
        ?>
        <div class="tipo-section">
            <div class="d-flex align-items-center mb-4">
                <div class="tipo-icon" style="background: <?php echo $tipoData['color']; ?>;">
                    <?php echo $tipoData['icono']; ?>
                </div>
                <h2 class="mb-0"><?php echo $tipoData['nombre']; ?></h2>
            </div>
            
            <div class="row g-4">
                <?php foreach ($items as $recompensa): 
                    $recompensa_id = $recompensa['id'];
                    $desbloqueada = isset($recompensas_desbloqueadas[$recompensa_id]);
                    $equipada = $desbloqueada && $recompensas_desbloqueadas[$recompensa_id];
                    $puede_desbloquear = $karma >= $recompensa['karma_requerido'];
                    
                    $card_class = 'recompensa-card';
                    if ($equipada) {
                        $card_class .= ' equipada';
                    } elseif ($desbloqueada) {
                        $card_class .= ' desbloqueada';
                    } elseif (!$puede_desbloquear) {
                        $card_class .= ' bloqueada';
                    }
                ?>
                <div class="col-md-4 col-lg-3">
                    <div class="<?php echo $card_class; ?>">
                        <?php if ($equipada): ?>
                            <span class="badge-equipada">✓ Equipada</span>
                        <?php elseif ($desbloqueada): ?>
                            <span class="badge-desbloqueada">✓ Desbloqueada</span>
                        <?php endif; ?>
                        
                        <div class="text-center">
                            <!-- 🎨 PREVIEW VISUAL SUPER MEJORADO -->
                            <?php if ($tipo == 'marco'): ?>
                                <!-- Preview del marco animado -->
                                <div class="marco-preview-container mb-3">
                                    <?php
                                    $marco_class = '';
                                    if (stripos($recompensa['nombre'], 'Dorado') !== false) $marco_class = 'marco-dorado';
                                    elseif (stripos($recompensa['nombre'], 'Diamante') !== false) $marco_class = 'marco-diamante';
                                    elseif (stripos($recompensa['nombre'], 'Fuego') !== false) $marco_class = 'marco-fuego';
                                    elseif (stripos($recompensa['nombre'], 'Arcoíris') !== false || stripos($recompensa['nombre'], 'Arcoiris') !== false) $marco_class = 'marco-arcoiris';
                                    elseif (stripos($recompensa['nombre'], 'Legendario') !== false) $marco_class = 'marco-legendario';
                                    elseif (stripos($recompensa['nombre'], 'Halloween') !== false) $marco_class = 'marco-halloween';
                                    ?>
                                    <div class="avatar-karma-container <?php echo $marco_class; ?>" style="display: inline-block;">
                                        <img src="https://via.placeholder.com/120/CCCCCC/FFFFFF?text=👤" 
                                             class="avatar-karma-img" 
                                             width="120" 
                                             height="120" 
                                             alt="Preview">
                                    </div>
                                </div>
                                
                            <?php elseif ($tipo == 'tema'): ?>
                                <!-- Preview del tema -->
                                <div class="tema-preview-container mb-3">
                                    <?php
                                    $tema_style = '';
                                    if (stripos($recompensa['nombre'], 'Oscuro') !== false) {
                                        $tema_style = 'background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color: #e0e0e0;';
                                    } elseif (stripos($recompensa['nombre'], 'Galaxy') !== false) {
                                        $tema_style = 'background: #0a0e27; color: #fff; border: 2px solid #6464ff;';
                                    } elseif (stripos($recompensa['nombre'], 'Sunset') !== false) {
                                        $tema_style = 'background: linear-gradient(135deg, #ff6b6b 0%, #feca57 50%, #ff9ff3 100%); color: #fff;';
                                    } elseif (stripos($recompensa['nombre'], 'Neon') !== false) {
                                        $tema_style = 'background: #0a0e27; color: #00ffff; border: 2px solid #00ffff; box-shadow: 0 0 15px #00ffff;';
                                    }
                                    ?>
                                    <div class="tema-preview-box" style="<?php echo $tema_style; ?>">
                                        <small>Vista Previa</small>
                                    </div>
                                </div>
                                
                            <?php elseif ($tipo == 'insignia'): ?>
                                <!-- Preview de INSIGNIAS ANIMADAS estilo demo -->
                                <div class="insignia-preview-container mb-3">
                                    <?php
                                    $insignia_html = '';
                                    $insignia_bg = '';
                                    $insignia_emoji = '';
                                    
                                    if (stripos($recompensa['nombre'], 'Novato') !== false) {
                                        $insignia_html = '<span class="insignia-badge" style="background: linear-gradient(135deg, #10b981, #059669); padding: 12px 24px; border-radius: 25px; color: white; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);"><span style="font-size: 1.3rem;">🌱</span> Insignia Novato</span>';
                                    } elseif (stripos($recompensa['nombre'], 'Intermedio') !== false) {
                                        $insignia_html = '<span class="insignia-badge" style="background: linear-gradient(135deg, #3b82f6, #2563eb); padding: 12px 24px; border-radius: 25px; color: white; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4); animation: insignia-intermedio 2s ease-in-out infinite;"><span style="font-size: 1.3rem;">⭐</span> Insignia Intermedio</span>';
                                    } elseif (stripos($recompensa['nombre'], 'Avanzado') !== false) {
                                        $insignia_html = '<span class="insignia-badge" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed); padding: 12px 24px; border-radius: 25px; color: white; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4); animation: insignia-avanzado 2s ease-in-out infinite;"><span style="font-size: 1.3rem;">⚡</span> Insignia Avanzado</span>';
                                    } elseif (stripos($recompensa['nombre'], 'Experto') !== false) {
                                        $insignia_html = '<span class="insignia-badge" style="background: linear-gradient(135deg, #f59e0b, #d97706); padding: 12px 24px; border-radius: 25px; color: white; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4); animation: insignia-experto 2s ease-in-out infinite;"><span style="font-size: 1.3rem;">🔥</span> Insignia Experto</span>';
                                    } elseif (stripos($recompensa['nombre'], 'Maestro') !== false) {
                                        $insignia_html = '<span class="insignia-badge" style="background: linear-gradient(135deg, #ef4444, #dc2626); padding: 12px 24px; border-radius: 25px; color: white; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4); animation: insignia-maestro 2s ease-in-out infinite;"><span style="font-size: 1.3rem;">⭐</span> Insignia Maestro</span>';
                                    } elseif (stripos($recompensa['nombre'], 'Legendario') !== false || stripos($recompensa['nombre'], 'Leyenda') !== false) {
                                        $insignia_html = '<span class="insignia-badge" style="background: linear-gradient(135deg, #fbbf24, #f59e0b); padding: 12px 24px; border-radius: 25px; color: white; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 20px rgba(251, 191, 36, 0.6); animation: insignia-legendaria 2s ease-in-out infinite;"><span style="font-size: 1.3rem;">👑</span> Insignia Legendario</span>';
                                    }
                                    ?>
                                    <div style="padding: 20px;">
                                        <?php echo $insignia_html; ?>
                                    </div>
                                </div>
                                
                            <?php elseif ($tipo == 'icono'): ?>
                                <!-- Preview de ÍCONOS ESPECIALES MÁS GRANDES -->
                                <div class="icono-preview-container mb-3">
                                    <?php
                                    $icono_html = '';
                                    if (stripos($recompensa['nombre'], 'Estrella') !== false) {
                                        $icono_html = '<span class="icono-especial icono-estrella" style="font-size: 3.5rem;">⭐</span>';
                                    } elseif (stripos($recompensa['nombre'], 'Corona') !== false) {
                                        $icono_html = '<span class="icono-especial icono-corona" style="font-size: 3.5rem;">👑</span>';
                                    } elseif (stripos($recompensa['nombre'], 'Fuego') !== false) {
                                        $icono_html = '<span class="icono-especial icono-fuego" style="font-size: 3.5rem;">🔥</span>';
                                    } elseif (stripos($recompensa['nombre'], 'Corazón') !== false) {
                                        $icono_html = '<span class="icono-especial icono-corazon" style="font-size: 3.5rem;">💖</span>';
                                    } elseif (stripos($recompensa['nombre'], 'Rayo') !== false) {
                                        $icono_html = '<span class="icono-especial icono-rayo" style="font-size: 3.5rem;">⚡</span>';
                                    } elseif (stripos($recompensa['nombre'], 'Diamante') !== false) {
                                        $icono_html = '<span class="icono-especial icono-diamante" style="font-size: 3.5rem;">💎</span>';
                                    }
                                    ?>
                                    <div style="padding: 30px 25px; background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1)); border-radius: 18px; text-align: center;">
                                        <div style="margin-bottom: 20px;">
                                            <?php echo $icono_html; ?>
                                        </div>
                                        <div style="font-size: 1.1rem; color: #555; font-weight: 600;">
                                            Tu Nombre
                                        </div>
                                        <div style="font-size: 0.9rem; color: #888; margin-top: 10px; font-style: italic;">✨ Aparece debajo de tu nombre</div>
                                    </div>
                                </div>
                                
                            <?php elseif ($tipo == 'color_nombre' || $tipo == 'color'): ?>
                                <!-- Preview de COLORES con palabra "NOMBRE" -->
                                <div class="color-nombre-preview-container mb-3">
                                    <?php
                                    $color_class = '';
                                    if (stripos($recompensa['nombre'], 'Dorado') !== false && stripos($recompensa['nombre'], 'Nombre Dorado') !== false) {
                                        $color_class = 'nombre-dorado';
                                    } elseif (stripos($recompensa['nombre'], 'Arcoíris') !== false || stripos($recompensa['nombre'], 'Arcoiris') !== false) {
                                        $color_class = 'nombre-arcoiris';
                                    } elseif (stripos($recompensa['nombre'], 'Fuego') !== false) {
                                        $color_class = 'nombre-fuego';
                                    } elseif (stripos($recompensa['nombre'], 'Océano') !== false || stripos($recompensa['nombre'], 'Oceano') !== false) {
                                        $color_class = 'nombre-oceano';
                                    } elseif (stripos($recompensa['nombre'], 'Neon Cyan') !== false) {
                                        $color_class = 'nombre-neon-cyan';
                                    } elseif (stripos($recompensa['nombre'], 'Neon Rosa') !== false) {
                                        $color_class = 'nombre-neon-rosa';
                                    } elseif (stripos($recompensa['nombre'], 'Galaxia') !== false) {
                                        $color_class = 'nombre-galaxia';
                                    } elseif (stripos($recompensa['nombre'], 'Púrpura Real') !== false) {
                                        $color_class = 'nombre-purpura-real';
                                    } elseif (stripos($recompensa['nombre'], 'Rosa Neón') !== false) {
                                        $color_class = 'nombre-rosa-neon';
                                    } elseif (stripos($recompensa['nombre'], 'Esmeralda') !== false) {
                                        $color_class = 'nombre-esmeralda';
                                    } elseif (stripos($recompensa['nombre'], 'Oro Premium') !== false) {
                                        $color_class = 'nombre-oro-premium';
                                    }
                                    ?>
                                    <div style="padding: 20px 15px; background: rgba(0,0,0,0.03); border-radius: 15px; overflow: hidden;">
                                        <div class="nombre-usuario <?php echo $color_class; ?>" style="font-size: clamp(1.5rem, 5vw, 1.8rem); font-weight: 800; letter-spacing: 0.5px; word-break: break-word; line-height: 1.2;">
                                            NOMBRE
                                        </div>
                                        <div style="font-size: 0.85rem; color: #999; margin-top: 12px;">Así se verá tu nombre</div>
                                    </div>
                                </div>
                                
                            <?php elseif ($tipo == 'sticker'): ?>
                                <?php
                                // Detectar si es un PACK de emojis de estado de ánimo o un STICKER individual
                                $es_pack_emojis = (stripos($recompensa['nombre'], 'Básico') !== false || 
                                                   stripos($recompensa['nombre'], 'Premium') !== false || 
                                                   stripos($recompensa['nombre'], 'Elite') !== false);
                                
                                if ($es_pack_emojis):
                                    // PACKS DE EMOJIS DE ESTADO DE ÁNIMO
                                    $stickers_preview = [];
                                    if (stripos($recompensa['nombre'], 'Básico') !== false) {
                                        $stickers_preview = [
                                            ['emoji' => '😊', 'nombre' => 'Feliz', 'clase' => 'sticker-feliz'],
                                            ['emoji' => '😢', 'nombre' => 'Triste', 'clase' => 'sticker-triste'],
                                            ['emoji' => '🤩', 'nombre' => 'Emocionado', 'clase' => 'sticker-emocionado'],
                                        ];
                                    } elseif (stripos($recompensa['nombre'], 'Premium') !== false) {
                                        $stickers_preview = [
                                            ['emoji' => '😌', 'nombre' => 'Relajado', 'clase' => 'sticker-relajado'],
                                            ['emoji' => '💪', 'nombre' => 'Motivado', 'clase' => 'sticker-motivado'],
                                            ['emoji' => '🎨', 'nombre' => 'Creativo', 'clase' => 'sticker-creativo'],
                                        ];
                                    } elseif (stripos($recompensa['nombre'], 'Elite') !== false) {
                                        $stickers_preview = [
                                            ['emoji' => '🤔', 'nombre' => 'Pensativo', 'clase' => 'sticker-pensativo'],
                                            ['emoji' => '⚡', 'nombre' => 'Energético', 'clase' => 'sticker-energetico'],
                                            ['emoji' => '🔥', 'nombre' => 'Legendario', 'clase' => 'sticker-motivado'],
                                        ];
                                    }
                                    ?>
                                    <!-- Preview de PACKS DE EMOJIS DE ESTADO DE ÁNIMO -->
                                    <div class="sticker-preview-container mb-3" style="padding: 45px 30px; background: linear-gradient(135deg, rgba(102, 126, 234, 0.12), rgba(118, 75, 162, 0.12)); border-radius: 25px; box-shadow: inset 0 0 40px rgba(102, 126, 234, 0.15);">
                                        <div style="font-size: 1.5rem; color: #667eea; font-weight: 700; margin-bottom: 30px; text-align: center; letter-spacing: 0.5px;">
                                            <span style="font-size: 2.5rem;">😊</span> Estados de Ánimo
                                        </div>
                                        <div class="stickers-container" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; padding: 0; background: transparent; border: none;">
                                            <?php foreach ($stickers_preview as $st): ?>
                                            <div class="sticker-item <?php echo $st['clase']; ?>" style="font-size: 1.2rem; padding: 25px 30px; min-width: 140px; transition: all 0.3s ease; cursor: pointer; box-shadow: 0 6px 20px rgba(0,0,0,0.15); background: rgba(255,255,255,0.6); border-radius: 18px;">
                                                <span class="sticker-emoji" style="font-size: 4rem; display: block; margin-bottom: 15px; transform: scale(1); transition: transform 0.3s ease;"><?php echo $st['emoji']; ?></span>
                                                <span class="sticker-nombre" style="font-size: 1.1rem; font-weight: 700; color: #667eea;"><?php echo $st['nombre']; ?></span>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div style="font-size: 0.95rem; color: #888; margin-top: 25px; text-align: center; font-style: italic;">
                                            ✨ Aparecen en tu perfil y puedes cambiarlos cuando quieras
                                        </div>
                                    </div>
                                <?php else:
                                    // STICKERS INDIVIDUALES DECORATIVOS (🔥✨🚀🎉🦄)
                                    $sticker_emoji = '🎁';
                                    if (stripos($recompensa['nombre'], 'Fuego') !== false) {
                                        $sticker_emoji = '🔥';
                                    } elseif (stripos($recompensa['nombre'], 'Estrella') !== false) {
                                        $sticker_emoji = '✨';
                                    } elseif (stripos($recompensa['nombre'], 'Cohete') !== false) {
                                        $sticker_emoji = '🚀';
                                    } elseif (stripos($recompensa['nombre'], 'Confeti') !== false) {
                                        $sticker_emoji = '🎉';
                                    } elseif (stripos($recompensa['nombre'], 'Unicornio') !== false) {
                                        $sticker_emoji = '🦄';
                                    }
                                    ?>
                                    <!-- Preview de STICKER INDIVIDUAL DECORATIVO -->
                                    <div class="sticker-preview-container mb-3" style="padding: 40px 30px; background: linear-gradient(135deg, rgba(102, 126, 234, 0.12), rgba(118, 75, 162, 0.12)); border-radius: 25px; box-shadow: inset 0 0 40px rgba(102, 126, 234, 0.15); text-align: center;">
                                        <div style="font-size: 5rem; margin-bottom: 20px; animation: sticker-bounce 2s ease-in-out infinite;">
                                            <?php echo $sticker_emoji; ?>
                                        </div>
                                        <div style="font-size: 1.1rem; color: #667eea; font-weight: 700; margin-bottom: 10px;">
                                            Sticker Decorativo
                                        </div>
                                        <div style="font-size: 0.95rem; color: #888; font-style: italic;">
                                            ✨ Aparece en tu perfil como decoración especial
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                            <?php else: ?>
                                <!-- Icono normal para otros tipos -->
                                <div class="recompensa-icono">
                                    <?php echo $tipoData['icono']; ?>
                                </div>
                            <?php endif; ?>
                            
                            <h5 class="fw-bold"><?php echo htmlspecialchars($recompensa['nombre']); ?></h5>
                            <p class="text-muted small"><?php echo htmlspecialchars($recompensa['descripcion']); ?></p>
                            
                            <div class="recompensa-karma">
                                <i class="bi bi-stars"></i> <?php echo $recompensa['karma_requerido']; ?> Karma
                            </div>
                            
                            <div class="mt-3">
                                <?php if ($desbloqueada): ?>
                                    <?php 
                                    // 🎯 Verificar si hay otro ítem del mismo tipo equipado
                                    $tipo_actual = $recompensa['tipo'];
                                    $hay_otro_equipado = in_array($tipo_actual, $tipos_con_equipado) && !$equipada;
                                    ?>
                                    
                                    <?php if ($equipada): ?>
                                        <!-- Está equipada: mostrar botón de desequipar -->
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="recompensa_id" value="<?php echo $recompensa_id; ?>">
                                            <button type="submit" name="equipar" class="btn btn-desequipar w-100">
                                                Desequipar
                                            </button>
                                        </form>
                                    <?php elseif ($hay_otro_equipado): ?>
                                        <!-- Hay otro ítem del mismo tipo equipado: botón deshabilitado con mensaje -->
                                        <button class="btn btn-secondary w-100" disabled style="opacity: 0.6;">
                                            <i class="bi bi-ban"></i> Ya tienes un <?php 
                                                $nombre_tipo = '';
                                                switch($tipo_actual) {
                                                    case 'marco': $nombre_tipo = 'marco'; break;
                                                    case 'tema': $nombre_tipo = 'tema'; break;
                                                    case 'icono': $nombre_tipo = 'icono'; break;
                                                    case 'color_nombre':
                                                    case 'color': $nombre_tipo = 'color'; break;
                                                    case 'sticker': $nombre_tipo = 'pack de stickers'; break;
                                                    case 'insignia': $nombre_tipo = 'insignia'; break;
                                                }
                                                echo $nombre_tipo;
                                            ?> equipado
                                        </button>
                                        <small class="text-muted d-block mt-2 text-center">
                                            <i class="bi bi-info-circle"></i> Desequipa el otro primero
                                        </small>
                                    <?php else: ?>
                                        <!-- No hay nada equipado: permitir equipar -->
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="recompensa_id" value="<?php echo $recompensa_id; ?>">
                                            <button type="submit" name="equipar" class="btn btn-equipar w-100">
                                                Equipar
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php elseif ($puede_desbloquear): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="recompensa_id" value="<?php echo $recompensa_id; ?>">
                                        <button type="submit" name="desbloquear" class="btn btn-desbloquear w-100" 
                                                onclick="return confirm('¿Deseas desbloquear <?php echo htmlspecialchars($recompensa['nombre']); ?>?\n\n✨ Se aplicará inmediatamente a tu perfil!');">
                                            <i class="bi bi-unlock-fill"></i> Desbloquear
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-secondary w-100" disabled>
                                        <i class="bi bi-lock-fill"></i> Bloqueada
                                    </button>
                                    <small class="text-muted d-block mt-2">
                                        Te faltan <?php echo $recompensa['karma_requerido'] - $karma; ?> puntos
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Botón volver -->
        <div class="text-center mt-4 mb-4">
            <a href="../view/index.php" class="btn btn-light btn-lg shadow">
                <i class="bi bi-arrow-left"></i> Volver al Inicio
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Animaciones de entrada
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.recompensa-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 50);
        });
        
        // 🔄 Actualizar karma cada 3 segundos
        actualizarKarmaTienda();
        setInterval(actualizarKarmaTienda, 3000);
    });
    
    // Función para actualizar el karma mostrado en la tienda
    function actualizarKarmaTienda() {
        fetch('/converza/app/presenters/get_karma.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar el display de puntos
                    const karmaDisplay = document.querySelector('.karma-points');
                    if (karmaDisplay && karmaDisplay.textContent !== String(data.karma)) {
                        // Animación de cambio
                        karmaDisplay.style.transition = 'transform 0.3s, color 0.3s';
                        karmaDisplay.style.transform = 'scale(1.2)';
                        karmaDisplay.style.color = '#0d6efd';
                        
                        setTimeout(() => {
                            karmaDisplay.textContent = data.karma;
                            setTimeout(() => {
                                karmaDisplay.style.transform = 'scale(1)';
                                karmaDisplay.style.color = '#0d6efd';
                            }, 200);
                        }, 150);
                    }
                    
                    // Actualizar nivel
                    const nivelDisplay = document.querySelector('.karma-display .mt-2');
                    if (nivelDisplay) {
                        nivelDisplay.textContent = 'Nivel ' + data.nivel;
                    }
                }
            })
            .catch(error => console.error('Error al actualizar karma:', error));
    }
    </script>
</body>
</html>
