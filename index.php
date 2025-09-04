<?php
session_start();
require_once 'lib/config.php'; // Conexión PDO en $conexion
require_once 'lib/socialnetwork-lib.php';

// ✅ Verificación de sesión
if (!isset($_SESSION['id']) || !isset($_SESSION['usuario']) || !isset($_SESSION['avatar'])) {
    header("Location: login.php");
    exit();
}

$mensaje = "";

// ✅ Procesamiento de publicación antes del HTML
if (isset($_POST['publicar'])) {
    $publicacion = trim($_POST['publicacion']);
    $usuario_id  = $_SESSION['id'];
    $imagen_id   = null;
    $album_id    = null;
/*     var_dump($publicacion, $usuario_id); // Debugging
    die(); */

    // Evitar publicaciones vacías
    if ($publicacion !== "") {
        // Verificar o crear álbum "Publicaciones"
        $stmtAlb = $conexion->prepare("SELECT id_alb FROM albumes WHERE usuario = :usuario AND nombre = 'Publicaciones'");
        $stmtAlb->bindParam(':usuario', $usuario_id, PDO::PARAM_INT);
        $stmtAlb->execute();
        $resAlb = $stmtAlb->fetch(PDO::FETCH_ASSOC);

        if ($resAlb) {
            $album_id = $resAlb['id_alb'];
        } else {
            $stmtCreateAlb = $conexion->prepare("INSERT INTO albumes (usuario, fecha, nombre) VALUES (:usuario, NOW(), 'Publicaciones')");
            $stmtCreateAlb->bindParam(':usuario', $usuario_id, PDO::PARAM_INT);
            $stmtCreateAlb->execute();
            $album_id = $conexion->lastInsertId();
        }

        // Subir imagen si existe
        if (!empty($_FILES['foto']['tmp_name'])) {
            $fileTmp = $_FILES['foto']['tmp_name'];
            $mime = mime_content_type($fileTmp);
            $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];

            if (isset($permitidos[$mime])) {
                $ext = $permitidos[$mime];
                $nombreArchivo = uniqid() . "." . $ext;
                $destino = "publicaciones/" . $nombreArchivo;

                if (move_uploaded_file($fileTmp, $destino)) {
                    $stmtFoto = $conexion->prepare("INSERT INTO fotos (usuario, fecha, ruta, album) VALUES (:usuario, NOW(), :ruta, :album)");
                    $stmtFoto->bindParam(':usuario', $usuario_id, PDO::PARAM_INT);
                    $stmtFoto->bindParam(':ruta', $nombreArchivo, PDO::PARAM_STR);
                    $stmtFoto->bindParam(':album', $album_id, PDO::PARAM_INT);
                    $stmtFoto->execute();
                    $imagen_id = $conexion->lastInsertId();
                }
            }
        }

        // Insertar publicación
        $stmtPub = $conexion->prepare("INSERT INTO publicaciones (usuario, fecha, contenido, imagen, album, comentarios) VALUES (:usuario, :fecha, :contenido, :imagen, :album, 1)");
        $fecha = date("Y-m-d H:i:s");
        $stmtPub->bindParam(':usuario', $usuario_id, PDO::PARAM_INT);
        $stmtPub->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmtPub->bindParam(':contenido', $publicacion, PDO::PARAM_STR);
        $stmtPub->bindParam(':imagen', $imagen_id, PDO::PARAM_INT);
        $stmtPub->bindParam(':album', $album_id, PDO::PARAM_INT);

        if ($stmtPub->execute()) {
            header("Location: index.php");
            exit();
        } else {
            $mensaje = "❌ Error al publicar.";
        }
    } else {
        $mensaje = "⚠️ La publicación no puede estar vacía.";
    }
}
?>
<!DOCTYPE html>
<html class="no-js">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>REDSOCIAL</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
  <link rel="stylesheet" type="text/css" href="css/component.css" />
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
  <script src="js/jquery.jscroll.js"></script>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<!-- <style>
      /* RS Unique — Overrides para tu página de publicaciones
      Objetivo: aspecto único sin tocar clases/IDs de AdminLTE v2
      Uso: guarda como css/rs-unique.css e inclúyelo DESPUÉS de AdminLTE y skins
    */

    /* =========================
      0) Tokens de diseño
      ========================= */
    :root{
      --rs-brand: #8b5cf6;      /* morado vibrante */
      --rs-brand-600:#7c3aed;
      --rs-accent:#22d3ee;      /* cian */
      --rs-success:#22c55e;
      --rs-warning:#f59e0b;
      --rs-danger:#ef4444;

      --rs-text:#e6e7eb;        /* texto */
      --rs-muted:#9aa6b2;

      --rs-bg:#101726;          /* fondo app */
      --rs-paper:#0f1625;       /* tarjetas */
      --rs-elev:#0d1422;        /* navbar/sidebar */

      --rs-border:rgba(255,255,255,.08);
      --rs-shadow:0 16px 40px rgba(0,0,0,.45);
      --rs-radius:16px;
    }

    /* =========================
      1) Base
      ========================= */
    body{background:var(--rs-bg);color:var(--rs-text);}
    a{color:var(--rs-accent);} a:hover{color:#67e8f9;}
    *{text-rendering:optimizeLegibility;-webkit-font-smoothing:antialiased;}

    /* Header / Navbar / Logo */
    .main-header .navbar{background:var(--rs-elev);border:1px solid var(--rs-border);box-shadow:var(--rs-shadow);} 
    .main-header .logo{background:linear-gradient(135deg,var(--rs-brand),var(--rs-brand-600));color:#fff !important;box-shadow:var(--rs-shadow);} 
    .main-header .navbar .nav>li>a{color:var(--rs-muted);} .main-header .navbar .nav>li>a:hover{color:#fff;}

    /* Sidebar */
    .skin-blue .main-sidebar,.skin-blue .left-side{background:var(--rs-elev);box-shadow:var(--rs-shadow);} 
    .skin-blue .sidebar-menu>li>a{color:var(--rs-muted);border-radius:12px;margin:4px 10px;}
    .skin-blue .sidebar-menu>li:hover>a,.skin-blue .sidebar-menu>li.active>a{color:#fff;background:rgba(255,255,255,.06);border-left-color:var(--rs-accent);} 

    /* Content background con acento sutil */
    .content-wrapper,.right-side{background:radial-gradient(1200px 500px at -10% -10%, rgba(139,92,246,.12), transparent 60%), var(--rs-bg);} 
    .content-header>h1{color:#fff}
    .content-header>.breadcrumb{border:1px dashed var(--rs-border);border-radius:12px;}

    /* =========================
      2) Caja de publicación (composer)
      ========================= */
    .box.direct-chat{border:1px solid var(--rs-border);border-top:3px solid var(--rs-brand);background:var(--rs-paper);border-radius:var(--rs-radius);box-shadow:var(--rs-shadow);} 
    .box.direct-chat .box-header{padding:16px;border-bottom:1px solid var(--rs-border);} 
    .box.direct-chat .box-title{font-weight:600;letter-spacing:.3px}
    .box.direct-chat .box-footer{background:transparent;border-top:1px solid var(--rs-border);} 

    /* Textarea moderno */
    #form-publicar .form-control{background:#101a33;color:var(--rs-text);border:1px solid var(--rs-border);border-radius:12px;transition:border-color .2s, box-shadow .2s;}
    #form-publicar .form-control:focus{border-color:var(--rs-brand);box-shadow:0 0 0 3px rgba(139,92,246,.25);} 

    /* Botón publicar */
    #publicar-btn.btn{border-radius:12px;background:var(--rs-brand);border-color:var(--rs-brand-600);} 
    #publicar-btn.btn:hover{background:var(--rs-brand-600);}

    /* Input file personalizado (basado en .inputfile input + label) */
    .inputfile{width:0.1px;height:0.1px;opacity:0;overflow:hidden;position:absolute;z-index:-1;}
    .inputfile + label{display:inline-flex;align-items:center;gap:8px;padding:10px 12px;border:1px dashed var(--rs-border);border-radius:12px;background:rgba(255,255,255,.03);color:var(--rs-muted);cursor:pointer;transition:all .2s;}
    .inputfile + label svg{fill:var(--rs-accent);} 
    .inputfile + label:hover{border-color:var(--rs-accent);color:#fff;background:rgba(34,211,238,.08);} 
    .inputfile + label span{font-weight:600;}

    /* Vista previa (si la agregas via JS, usa .rs-preview) */
    .rs-preview{margin-top:10px;display:flex;gap:10px;flex-wrap:wrap}
    .rs-preview img{width:120px;height:120px;object-fit:cover;border-radius:12px;border:1px solid var(--rs-border);box-shadow:var(--rs-shadow);} 

    /* =========================
      3) Lista de publicaciones (scroll)
      ========================= */
    .scroll{scrollbar-width:thin;}
    .scroll .box{background:var(--rs-paper);border:1px solid var(--rs-border);border-radius:14px;box-shadow:var(--rs-shadow);} 
    .scroll .box-header.with-border{border-bottom:1px solid var(--rs-border);} 

    /* Loader de jscroll */
    .jscroll-loading{display:flex;justify-content:center;align-items:center;padding:16px;opacity:.7}

    /* =========================
      4) Solicitudes de amistad (columna derecha)
      ========================= */
    .products-list>.item{background:var(--rs-paper);border:1px solid var(--rs-border);border-radius:14px;margin-bottom:12px;transition:transform .15s ease, box-shadow .15s ease;}
    .products-list>.item:hover{transform:translateY(-2px);} 
    .products-list .product-img img{width:46px;height:46px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.12);} 
    .products-list .product-info{margin-left:62px;color:var(--rs-text);} 
    .products-list .product-description{color:var(--rs-muted);} 
    .products-list .label{border-radius:999px;padding:.35em .6em;font-weight:700;}
    .products-list .label-success{background:var(--rs-success);} 
    .products-list .label-danger{background:var(--rs-danger);} 

    /* =========================
      5) Últimos registrados
      ========================= */
    .users-list>li{background:var(--rs-paper);border:1px solid var(--rs-border);border-radius:14px;margin:8px;transition:transform .15s ease, box-shadow .15s ease;}
    .users-list>li:hover{transform:translateY(-3px);} 
    .users-list>li img{width:100%;height:120px;object-fit:cover;border-bottom:1px solid var(--rs-border);border-top-left-radius:14px;border-top-right-radius:14px;} 
    .users-list .users-list-name{color:#fff;font-weight:600;margin-top:6px;}
    .users-list .users-list-date{color:var(--rs-muted);} 

    /* =========================
      6) Componentes globales retocados
      ========================= */
    .box{background:var(--rs-paper);border:1px solid var(--rs-border);border-top:3px solid var(--rs-brand);border-radius:var(--rs-radius);box-shadow:var(--rs-shadow);} 
    .box.box-primary{border-top-color:var(--rs-brand);} 
    .box.box-danger{border-top-color:var(--rs-danger);} 
    .box.box-success{border-top-color:var(--rs-success);} 
    .box.box-warning{border-top-color:var(--rs-warning);} 

    .table>thead>tr>th{background:rgba(255,255,255,.02);color:var(--rs-muted);border-bottom:1px solid var(--rs-border);} 
    .table>tbody>tr>td{border-top:1px solid var(--rs-border);} 

    .btn{border-radius:12px;border:1px solid transparent;}
    .btn-default{background:transparent;border-color:var(--rs-border);color:var(--rs-text);} 
    .btn-default:hover{background:rgba(255,255,255,.06);} 
    .btn-primary{background:var(--rs-brand);border-color:var(--rs-brand-600);} 
    .btn-warning{background:var(--rs-warning);border-color:#d97706;color:#111;} 
    .btn-danger{background:var(--rs-danger);border-color:#dc2626;} 

    .alert{border-radius:12px;border:1px solid var(--rs-border);} 
    .alert-warning{background:linear-gradient(90deg,#f59e0b,#fbbf24);color:#111;border-color:transparent;} 

    /* SweetAlert2 theme sutil */
    .swal2-popup{background:var(--rs-paper)!important;color:var(--rs-text)!important;border:1px solid var(--rs-border)!important;border-radius:16px!important;box-shadow:var(--rs-shadow)!important;}
    .swal2-styled.swal2-confirm{background:var(--rs-brand)!important;border-radius:12px!important;}
    .swal2-styled.swal2-cancel{background:transparent!important;color:var(--rs-text)!important;border:1px solid var(--rs-border)!important;border-radius:12px!important;}

    /* =========================
      7) Accesibilidad / foco
      ========================= */
    :focus{outline:none;} 
    :focus-visible{outline:3px solid rgba(139,92,246,.55);outline-offset:2px;border-radius:8px;}

    /* =========================
      8) Pequeños detalles
      ========================= */
    .box .box-tools .btn{color:var(--rs-muted);} .box .box-tools .btn:hover{color:#fff;background:rgba(255,255,255,.06);} 
    /* Mejor scrollbar */
    ::-webkit-scrollbar{width:10px;height:10px} 
    ::-webkit-scrollbar-track{background:transparent}
    ::-webkit-scrollbar-thumb{background:rgba(255,255,255,.12);border-radius:999px;border:2px solid transparent;background-clip:padding-box}
    ::-webkit-scrollbar-thumb:hover{background:rgba(255,255,255,.2)}

    /* =========================
      HOTFIX PACK v1 (basado en screenshot)
      Ajusta contraste, tamaños y espaciados sin tocar clases/IDs
      ========================= */

    /* 1) Header/Logo más limpio y consistente */
    .main-header .logo{font-weight:700;letter-spacing:.6px;text-transform:uppercase;padding:0 18px;border-right:1px solid var(--rs-border);} 
    .skin-blue .main-header .logo{background:linear-gradient(90deg,var(--rs-brand),var(--rs-brand-600))!important;}
    .skin-blue .main-header .navbar{min-height:56px;border-bottom:1px solid var(--rs-border);} 

    /* 2) Contenido con ancho máximo legible */
    .content{max-width:1200px;} 
    @media (min-width:1400px){.content{max-width:1280px;}}

    /* 3) Mejor contraste de tipografías en sidebar */
    .skin-blue .sidebar-menu>li.header{color:#b9c3cf;opacity:.9;}
    .skin-blue .sidebar-menu>li>a{color:#c1c9d6;} 
    .skin-blue .sidebar-menu>li>a .fa{opacity:.9;} 

    /* 4) Buscador (input largo) con estilo pill */
    .content .input-group>.form-control{height:44px;border-radius:999px;background:#0c1426;border:1px solid var(--rs-border);box-shadow:inset 0 1px 0 rgba(255,255,255,.03);} 
    .content .input-group .input-group-btn>.btn,
    .content .input-group-addon{border-radius:999px;background:transparent;border:1px solid var(--rs-border);} 

    /* 5) Imágenes grandes dentro del feed: bordes y ajuste */
    .content img{max-width:100%;height:auto;border-radius:14px;border:1px solid var(--rs-border);} 

    /* 6) Notificaciones/labels más visibles sobre fondo oscuro */
    .label{border-radius:999px;padding:.35em .65em;font-weight:700;}
    .label-primary{background:var(--rs-brand);} 
    .label-success{background:var(--rs-success);} 
    .label-danger{background:var(--rs-danger);} 

    /* 7) Separación vertical entre boxes para que "respire" */
    .box{margin-bottom:24px;background:var(--rs-paper);backdrop-filter:saturate(120%) blur(2px);} 
    .box .box-header .box-title{color:#e9eaf0;}

    /* 8) Perfil/Avatar en listas a tamaño consistente */
    .users-list>li img{height:140px;} 
    .products-list .product-img img{width:48px;height:48px;}

    /* 9) Botones compactos y con alto mínimo */
    .btn{min-height:40px;padding:.55rem 1rem;}

    /* 10) Bordes delicados en bloques principales */
    .content-wrapper, .main-header .navbar, .main-sidebar{border-color:var(--rs-border);} 


</style> -->
<?php echo Headerb(); ?>
<?php echo Side(); ?>

<div class="content-wrapper">
  <section class="content">
    <div class="row">
      <div class="col-md-8">
        <!-- Caja publicación -->
        <div class="box box-primary direct-chat direct-chat-warning">
          <div class="box-header with-border">
            <h3 class="box-title">¿Qué estás pensando?</h3>
            <button type="button" class="btn btn-box-tool" data-widget="collapse">
              <i class="fa fa-minus"></i>
            </button>
          </div>
          <div class="box-footer">

            <?php if ($mensaje): ?>
              <div class="alert alert-warning"><?= $mensaje ?></div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data" id="form-publicar">
              <div class="input-group">
              <textarea name="publicacion" placeholder="¿Qué estás pensando?"
                  class="form-control" rows="3" required></textarea>
              <br>
              <input type="file" name="foto" id="file-1"
                 class="inputfile inputfile-1"
                 accept="image/jpeg,image/png,image/gif"/>
              <label for="file-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="17" viewBox="0 0 20 17">
                <path d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9z"/>
                </svg> 
                <span>Sube una foto</span>
              </label>
              <br>
              <button type="button" name="publicar" class="btn btn-primary btn-flat" id="publicar-btn">Publicar</button>
              </div>
            </form>
          </div>
        </div>
        <!-- Scroll publicaciones -->
        <div class="scroll">
          <?php require_once 'publicaciones.php'; ?>
        </div>
        <script>
        $(document).ready(function() {
            $('.scroll').jscroll({
            loadingHtml: '<img src="public/images/invisible.png" alt="Loading" />'
          });
        });
        </script>
      </div>

      <!-- Columna derecha -->
      <div class="col-md-4">
        <!-- Solicitudes de amistad -->
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">Solicitudes de amistad</h3>
          </div>
          <div class="box-body">
            <ul class="products-list product-list-in-box">
              <?php
              $stmtAmigos = $conexion->prepare("SELECT * FROM amigos WHERE para = :usuario_id AND estado = 0 ORDER BY id_ami DESC LIMIT 4");
              $stmtAmigos->bindParam(':usuario_id', $_SESSION['id'], PDO::PARAM_INT);
              $stmtAmigos->execute();
              $resAmigos = $stmtAmigos->fetchAll(PDO::FETCH_ASSOC);

              foreach ($resAmigos as $am) {
                  $stmtUse = $conexion->prepare("SELECT * FROM usuarios WHERE id_use = :id_use");
                  $stmtUse->bindParam(':id_use', $am['de'], PDO::PARAM_INT);
                  $stmtUse->execute();
                  $us = $stmtUse->fetch(PDO::FETCH_ASSOC);
              ?>
              <li class="item">
                <div class="product-img">
                  <img src="public/avatars/<?php echo htmlspecialchars($us['avatar']); ?>" alt="Avatar">
                </div>
                <div class="product-info">
                  <?php echo htmlspecialchars($us['usuario']); ?>
                  <a href="solicitud.php?action=aceptar&id=<?php echo (int)$am['id_ami']; ?>">
                    <span class="label label-success pull-right">Aceptar</span>
                  </a><br>
                  <a href="solicitud.php?action=rechazar&id=<?php echo (int)$am['id_ami']; ?>">
                    <span class="label label-danger pull-right">Rechazar</span>
                  </a>
                  <span class="product-description">
                    <?php echo htmlspecialchars($us['sexo']); ?>
                  </span>
                </div>
              </li>
              <?php } ?>
            </ul>
          </div>
        </div>
      </div>

      <!-- Últimos registrados -->
      <div class="col-md-4">
        <div class="box box-danger">
          <div class="box-header with-border">
            <h3 class="box-title">Últimos registrados</h3>
          </div>
          <div class="box-body no-padding">
            <ul class="users-list clearfix">
              <?php
              $stmtReg = $conexion->prepare("SELECT id_use, avatar, usuario, fecha_reg FROM usuarios ORDER BY id_use DESC LIMIT 8");
              $stmtReg->execute();
              $resReg = $stmtReg->fetchAll(PDO::FETCH_ASSOC);
              foreach ($resReg as $reg) {
              ?>
                <li>
                  <img src="public  /avatars/<?php echo htmlspecialchars($reg['avatar']); ?>" alt="User Image" width="100" height="200">
                  <a class="users-list-name" href="perfil.php?id=<?php echo (int)$reg['id_use']; ?>"><?php echo htmlspecialchars($reg['usuario']); ?></a>
                  <span class="users-list-date"><?php echo htmlspecialchars($reg['fecha_reg']); ?></span>
                </li>
              <?php } ?>
            </ul>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<div class="control-sidebar-bg"></div>
</div>

<script src="bootstrap/js/bootstrap.min.js"></script>
<script src="plugins/fastclick/fastclick.js"></script>
<script src="dist/js/app.min.js"></script>
<script src="plugins/sparkline/jquery.sparkline.min.js"></script>
<script src="plugins/slimScroll/jquery.slimscroll.min.js"></script>
<script src="js/custom-file-input.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
              document.getElementById('publicar-btn').addEventListener('click', function(event) {
              Swal.fire({
                title: '¿Estás seguro?',
                text: 'Estás a punto de publicar esto. ¿Quieres continuar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, publicar',
                cancelButtonText: 'Cancelar',
              }).then((result) => {
                if (result.isConfirmed) {
                // Create a hidden submit button and click it
                const submitBtn = document.createElement('input');
                submitBtn.type = 'submit';
                submitBtn.name = 'publicar';
                submitBtn.style.display = 'none';
                document.getElementById('form-publicar').appendChild(submitBtn);
                submitBtn.click();
                }
              });
              });
</script>
</body>
</html>
