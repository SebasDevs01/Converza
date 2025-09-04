<?php
session_start();
include 'lib/config.php';
include 'lib/socialnetwork-lib.php';

// Evitar mostrar errores de advertencia
error_reporting(E_ALL & ~E_NOTICE);

// Redirigir si no está logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtener el ID del usuario
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // seguridad

    if ($_SESSION['id'] != $id) {
        header("Location: login.php");
        exit();
    }

    // Traer datos del usuario
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id_use = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $use = $stmt->fetch(PDO::FETCH_ASSOC);

    // Actualizar datos
    if (isset($_POST['actualizar'])) {
        $nombre = trim($_POST['nombre']);
        $usuario = trim($_POST['usuario']);
        $email = trim($_POST['email']);
        $sexo = $_POST['sexo'];
        $nacimiento = !empty($_POST['nacimiento']) ? $_POST['nacimiento'] : $use['nacimiento'];

        // Comprobar usuario único
        $stmtCheck = $conexion->prepare("SELECT id_use FROM usuarios WHERE usuario = :usuario AND id_use != :id");
        $stmtCheck->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $stmtCheck->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$resCheck) {
            // Manejar avatar
            if (!empty($_FILES['avatar']['tmp_name'])) {
                $fileTmp = $_FILES['avatar']['tmp_name'];
                $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $name = $id . '.' . $ext;
                $destino = 'avatars/' . $name;
                move_uploaded_file($fileTmp, $destino);
            } else {
                $name = $use['avatar'];
            }

            // Actualizar datos en la BD
            $stmtUpdate = $conexion->prepare("UPDATE usuarios SET nombre = :nombre, usuario = :usuario, email = :email, sexo = :sexo, nacimiento = :nacimiento, avatar = :avatar WHERE id_use = :id");
            $stmtUpdate->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':usuario', $usuario, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':email', $email, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':sexo', $sexo, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':nacimiento', $nacimiento, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':avatar', $name, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmtUpdate->execute()) {
                $_SESSION['usuario'] = $usuario;
                $_SESSION['avatar'] = $name;
                header("Location: editarperfil.php?id=$id");
                exit();
            } else {
                $error = "Error al actualizar tus datos.";
            }
        } else {
            $error = "El nombre de usuario ya está en uso, escoja otro.";
        }
    }
} else {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Editar mi perfil</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

<?php echo Headerb(); ?>
<?php echo Side(); ?>

<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border"><h3 class="box-title">Editar mi perfil</h3></div>
                    <form method="post" enctype="multipart/form-data">
                        <div class="box-body">
                            <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                            <div class="form-group">
                                <label>Nombre completo</label>
                                <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($use['nombre']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Usuario</label>
                                <input type="text" name="usuario" class="form-control" value="<?php echo htmlspecialchars($use['usuario']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="text" name="email" class="form-control" value="<?php echo htmlspecialchars($use['email']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Cambiar avatar</label>
                                <input type="file" name="avatar">
                            </div>
                            <div class="form-group">
                                <label>Sexo</label><br>
                                <input type="radio" name="sexo" value="H" <?php if ($use['sexo'] == 'H') echo 'checked'; ?>> Hombre
                                <input type="radio" name="sexo" value="M" <?php if ($use['sexo'] == 'M') echo 'checked'; ?>> Mujer
                            </div>
                            <div class="form-group">
                                <label>Fecha de nacimiento</label>
                                <input type="date" name="nacimiento" class="form-control" value="<?php echo $use['nacimiento']; ?>">
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" name="actualizar" class="btn btn-primary">Actualizar datos</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

</div>
<script src="bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
