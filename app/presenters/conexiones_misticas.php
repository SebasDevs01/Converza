<?php
session_start();
require_once(__DIR__ . '/../models/config.php');
require_once(__DIR__ . '/../models/conexiones-misticas-helper.php');
require_once(__DIR__ . '/../models/intereses-helper.php');

if (!isset($_SESSION['id'])) {
    header("Location: ../view/login.php");
    exit();
}

$motor = new ConexionesMisticas($conexion);
$interesesHelper = new InteresesHelper($conexion);

// 🚀 GENERACIÓN AUTOMÁTICA DE CONEXIONES
// Si el usuario no tiene conexiones o han pasado más de 6 horas, se generan automáticamente
$motor->generarConexionesAutomaticas($_SESSION['id']);

// Obtener conexiones y mejorarlas con intereses
$conexiones = $motor->obtenerConexionesUsuario($_SESSION['id'], 50);
$conexiones = $interesesHelper->mejorarConexionesMisticas($_SESSION['id'], $conexiones);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conexiones Místicas - Converza</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        
        .container-main {
            max-width: 900px;
        }
        
        .header-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .header-card h1 {
            font-size: 2rem;
            color: #0d6efd;
            margin-bottom: 10px;
        }
        
        .header-card h1 i {
            color: #0d6efd;
        }
        
        .btn-back {
            position: absolute;
            top: 20px;
            left: 20px;
        }
        
        .conexion-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.2s ease;
            cursor: pointer;
            border-left: 4px solid #0d6efd;
        }
        
        .conexion-card:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }
        
        .conexion-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 12px;
        }
        
        .conexion-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #0d6efd;
        }
        
        .conexion-info {
            flex-grow: 1;
        }
        
        .conexion-username {
            font-size: 1.1rem;
            font-weight: 600;
            color: #212529;
        }
        
        .conexion-tipo {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .conexion-badge {
            background-color: #0d6efd;
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .conexion-descripcion {
            font-size: 0.95rem;
            line-height: 1.5;
            color: #495057;
            background: #f8f9fa;
            padding: 12px 16px;
            border-radius: 8px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }
    </style>
</head>
<body>
    <div class="container container-main py-4">
        <a href="../view/index.php" class="btn btn-primary btn-back mb-3">
            <i class="bi bi-arrow-left"></i> Volver al feed
        </a>
        
        <div class="header-card">
            <h1><i class="bi bi-stars"></i> Conexiones Místicas</h1>
            <p class="text-muted mb-0">Descubre coincidencias curiosas con otros usuarios</p>
        </div>
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container container-main">
        <div class="header-card">
            <h1>🔮 Conexiones Místicas</h1>
            <p class="text-muted mb-3">Descubre patrones y coincidencias curiosas con otros usuarios</p>
            <a href="../view/index.php" class="back-btn">
                <i class="bi bi-arrow-left"></i>
                Volver al inicio
            </a>
        </div>
        
        <?php if (empty($conexiones)): ?>
            <div class="empty-state">
                <i class="bi bi-stars"></i>
                <h3>Aún no hay conexiones místicas</h3>
                <p class="text-muted">Interactúa más con otros usuarios para descubrir coincidencias curiosas</p>
                <a href="../view/index.php" class="btn btn-primary mt-3">
                    <i class="bi bi-house-door"></i> Ir al feed
                </a>
            </div>
        <?php else: ?>
            <?php 
            $tipos = [
                'gustos_compartidos' => ['💖', 'Gustos Compartidos'],
                'intereses_comunes' => ['💬', 'Intereses Comunes'],
                'amigos_de_amigos' => ['👥', 'Amigos de Amigos'],
                'horarios_coincidentes' => ['🕐', 'Horarios Coincidentes']
            ];
            
            foreach ($conexiones as $conexion): 
                $avatarPath = $conexion['otro_avatar'] 
                    ? "/Converza/public/avatars/{$conexion['otro_avatar']}" 
                    : "/Converza/public/avatars/defect.jpg";
                
                $tipoInfo = $tipos[$conexion['tipo_conexion']] ?? ['✨', 'Conexión Especial'];
            ?>
            
            <div class="conexion-card" onclick="location.href='perfil.php?id=<?php echo $conexion['otro_id']; ?>'">
                <div class="conexion-header">
                    <img src="<?php echo $avatarPath; ?>" alt="Avatar" class="conexion-avatar">
                    <div class="conexion-info">
                        <div class="conexion-username"><?php echo htmlspecialchars($conexion['otro_usuario']); ?></div>
                        <div class="conexion-tipo"><?php echo $tipoInfo[0]; ?> <?php echo $tipoInfo[1]; ?></div>
                    </div>
                    <div class="conexion-badge" title="Score combinado: Sistema Místico (50%) + Predicciones (50%)">
                        <?php echo $conexion['puntuacion']; ?>%
                    </div>
                </div>
                <div class="conexion-descripcion">
                    <?php echo htmlspecialchars($conexion['descripcion']); ?>
                </div>
                
                <!-- Desglose de scores -->
                <div class="scores-desglose mt-3 p-3" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 10px;">
                    <small class="text-muted d-block mb-2">
                        <i class="bi bi-calculator"></i> Desglose de compatibilidad:
                    </small>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="score-item">
                                <small class="text-muted d-block">
                                    <i class="bi bi-diagram-3"></i> Sistema Místico
                                </small>
                                <strong class="text-primary"><?php echo $conexion['puntuacion_original'] ?? 0; ?>%</strong>
                                <small class="text-muted d-block" style="font-size: 0.75rem;">
                                    Amigos, reacciones, actividad
                                </small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="score-item">
                                <small class="text-muted d-block">
                                    <i class="bi bi-heart"></i> Predicciones
                                </small>
                                <strong class="text-success"><?php echo $conexion['compatibilidad_intereses'] ?? 0; ?>%</strong>
                                <small class="text-muted d-block" style="font-size: 0.75rem;">
                                    Gustos e intereses
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($conexion['intereses_comunes'])): ?>
                    <div class="intereses-comunes mt-3">
                        <small class="text-muted d-block mb-2">
                            <i class="bi bi-star-fill text-warning"></i> Intereses en común:
                        </small>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php foreach ($conexion['intereses_comunes'] as $interes): ?>
                                <span class="badge bg-primary" style="font-size: 0.85rem;">
                                    <?php echo $interes['emoji']; ?> <?php echo $interes['nombre']; ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
