<?php
// Paneles emergentes para la navbar (offcanvas Bootstrap)
?>
<!-- Offcanvas: Buscar usuarios -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasSearch" aria-labelledby="offcanvasSearchLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasSearchLabel"><i class="bi bi-search"></i> Buscar usuarios</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
  </div>
  <div class="offcanvas-body">
    <input type="text" id="buscador-usuarios" class="form-control mb-2" placeholder="Buscar usuario...">
    <div id="resultados-busqueda"></div>
  </div>
</div>
<!-- Offcanvas: Solicitudes de amistad -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasSolicitudes" aria-labelledby="offcanvasSolicitudesLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasSolicitudesLabel"><i class="bi bi-person-plus"></i> Solicitudes de amistad</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
  </div>
  <div class="offcanvas-body">
    <?php
    $stmtAmigos = $conexion->prepare("SELECT * FROM amigos WHERE para = :usuario_id AND estado = 0 ORDER BY id_ami DESC LIMIT 4");
    $stmtAmigos->bindParam(':usuario_id', $_SESSION['id'], PDO::PARAM_INT);
    $stmtAmigos->execute();
    $resAmigos = $stmtAmigos->fetchAll(PDO::FETCH_ASSOC);
    if ($resAmigos):
        foreach ($resAmigos as $am):
            $stmtUse = $conexion->prepare("SELECT * FROM usuarios WHERE id_use = :id_use");
            $stmtUse->bindParam(':id_use', $am['de'], PDO::PARAM_INT);
            $stmtUse->execute();
            $us = $stmtUse->fetch(PDO::FETCH_ASSOC);
            $avatarU = htmlspecialchars($us['avatar']);
            $avatarUPath = realpath(__DIR__.'/../../public/avatars/'.$avatarU);
            $avatarUWeb = '/TrabajoRedSocial/public/avatars/'.$avatarU;
            if ($avatarU && $avatarU !== 'default_avatar.svg' && $avatarUPath && file_exists($avatarUPath)) {
                $imgU = '<img src="'.$avatarUWeb.'" class="rounded-circle me-2" width="40" height="40" alt="Avatar" loading="lazy">';
            } else {
                $imgU = '<img src="/TrabajoRedSocial/public/avatars/defect.jpg" class="rounded-circle me-2" width="40" height="40" alt="Avatar por defecto" loading="lazy">';
            }
    ?>
    <div class="d-flex align-items-center mb-2">
        <?php echo $imgU; ?>
        <a class="me-auto fw-bold text-decoration-none" href="../presenters/perfil.php?id=<?php echo (int)$us['id_use']; ?>"><?php echo htmlspecialchars($us['usuario']); ?></a>
        <?php echo $am['de']; ?>
        <a href="../presenters/solicitud.php?action=aceptar&id=<?php echo (int)$am['de']; ?>" class="btn btn-success btn-sm me-1"><i class="bi bi-check"></i></a>
        <a href="../presenters/solicitud.php?action=rechazar&id=<?php echo (int)$am['de']; ?>" class="btn btn-danger btn-sm"><i class="bi bi-x"></i></a>
    </div>
    <?php
        endforeach;
    else:
        echo '<div class="text-muted">No tienes solicitudes pendientes.</div>';
    endif;
    ?>
  </div>
</div>
<!-- Offcanvas: Nuevos usuarios -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNuevos" aria-labelledby="offcanvasNuevosLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasNuevosLabel"><i class="bi bi-people"></i> Nuevos usuarios</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
  </div>
  <div class="offcanvas-body">
    <div class="row g-2">
      <?php
      $stmtReg = $conexion->prepare("SELECT id_use, avatar, usuario, fecha_reg FROM usuarios WHERE id_use != :current_user ORDER BY id_use DESC LIMIT 6");
      $stmtReg->bindParam(':current_user', $_SESSION['id'], PDO::PARAM_INT);
      $stmtReg->execute();
      $resReg = $stmtReg->fetchAll(PDO::FETCH_ASSOC);
      foreach ($resReg as $reg):
          $avatarR = htmlspecialchars($reg['avatar']);
          $avatarRPath = realpath(__DIR__.'/../../public/avatars/'.$avatarR);
          $avatarRWeb = '/TrabajoRedSocial/public/avatars/'.$avatarR;
          if ($avatarR && $avatarR !== 'default_avatar.svg' && $avatarRPath && file_exists($avatarRPath)) {
              $imgR = '<img src="'.$avatarRWeb.'" class="card-img-top rounded-top" style="height:80px;object-fit:cover;" loading="lazy" title="Avatar de usuario">';
          } else {
              $imgR = '<img src="/TrabajoRedSocial/public/avatars/defect.jpg" class="card-img-top rounded-top" style="height:80px;object-fit:cover;" width="100%" height="80" loading="lazy" title="Avatar por defecto">';
          }
      ?>
      <div class="col-6">
        <div class="card h-100 text-center border-0 bg-light">
          <?php echo $imgR; ?>
          <div class="card-body p-2">
            <a class="fw-bold text-decoration-none" href="../presenters/perfil.php?id=<?php echo (int)$reg['id_use']; ?>"><?php echo htmlspecialchars($reg['usuario']); ?></a>
            <div class="text-muted small"><?php echo htmlspecialchars($reg['fecha_reg']); ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
