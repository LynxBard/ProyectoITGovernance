<?php
session_start();

/*
  index.php - CRUD de archivos (base TI, tabla archivos)
  Versión: vista con previsualización en modal + botón "Pantalla completa"
*/

/* ======= CONFIG ======= */
$db_host = getenv('DB_HOST') ?: "localhost";
$db_user = getenv('DB_USER') ?: "root";
$db_pass = getenv('DB_PASS') ?: "";
$db_name = getenv('DB_NAME') ?: "Camaras"; // O "ti", según corresponda
$upload_dir = __DIR__ . '/uploads';
/* ====================== */

/* Crear carpeta uploads si no existe */
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

/* Conexión (Modificada para Azure con SSL) */
$con = mysqli_init();
// Azure en Linux guarda los certificados CA aquí:
mysqli_ssl_set($con, NULL, NULL, "/etc/ssl/certs/ca-certificates.crt", NULL, NULL);

// Conectamos usando real_connect con la bandera SSL
if (!mysqli_real_connect($con, $db_host, $db_user, $db_pass, NULL, 3306, NULL, MYSQLI_CLIENT_SSL)) {
    die('Error conexión MySQL: ' . mysqli_connect_error());
}

/* Crear DB si no existe y seleccionar */
if (!mysqli_select_db($con, $db_name)) {
    $createdb_sql = "CREATE DATABASE IF NOT EXISTS " . mysqli_real_escape_string($con, $db_name) . " CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
    if (!mysqli_query($con, $createdb_sql)) {
        die("No se pudo crear la base de datos: " . mysqli_error($con));
    }
    mysqli_select_db($con, $db_name);
}

/* Crear tabla archivos si no existe (columnas que solicitaste) */
$create_table_sql = "
CREATE TABLE IF NOT EXISTS archivos (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre_archivo VARCHAR(255) NOT NULL,
  tipo_mime VARCHAR(100) NOT NULL,
  tamano_bytes BIGINT UNSIGNED NOT NULL,
  num_version INT UNSIGNED NOT NULL DEFAULT 1,
  fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
if (!mysqli_query($con, $create_table_sql)) {
    die("Error creando tabla: " . mysqli_error($con));
}

/* --------------------------
   HANDLERS: Upload / Delete
   -------------------------- */

/* Helper: sanitize filename for storage (keeps extension) */
function sanitize_filename($name) {
    $base = basename($name);
    $base = preg_replace('/[^\p{L}\p{N}\.\-_ ]+/u', '_', $base);
    $base = str_replace(' ', '_', $base);
    $parts = explode('.', $base);
    if (count($parts) > 2) {
        $ext = array_pop($parts);
        $base = preg_replace('/\.+/', '_', implode('_', $parts)) . '.' . $ext;
    }
    return $base;
}

/* Acción: SUBIR ARCHIVO */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['flash'] = "Error al subir archivo.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    $originalName = $_FILES['archivo']['name'];
    $safeName = sanitize_filename($originalName);
    $mime = $_FILES['archivo']['type'] ?: mime_content_type($_FILES['archivo']['tmp_name']);
    $size = (int)$_FILES['archivo']['size'];

    // calcular num_version: obtener max(num_version) para el mismo nombre de archivo (exact match)
    $stmt = mysqli_prepare($con, "SELECT IFNULL(MAX(num_version), 0) AS mv FROM archivos WHERE nombre_archivo = ?");
    mysqli_stmt_bind_param($stmt, "s", $originalName);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    $nextVersion = ((int)$row['mv']) + 1;
    mysqli_stmt_close($stmt);

    // Insert metadata primero
    $stmt = mysqli_prepare($con, "INSERT INTO archivos (nombre_archivo, tipo_mime, tamano_bytes, num_version) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssii", $originalName, $mime, $size, $nextVersion);
    $ok = mysqli_stmt_execute($stmt);
    if (!$ok) {
        $_SESSION['flash'] = "Error al guardar metadata: " . mysqli_error($con);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    $insert_id = mysqli_insert_id($con);
    mysqli_stmt_close($stmt);

    // Mover archivo subido a uploads con nombre: {id}_{safeName}
    $storedName = $insert_id . '_' . $safeName;
    $destPath = $upload_dir . '/' . $storedName;
    if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $destPath)) {
        // intentar borrar registro si falla el move
        mysqli_query($con, "DELETE FROM archivos WHERE id = " . (int)$insert_id);
        $_SESSION['flash'] = "Error moviendo archivo al directorio uploads.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Éxito
    $_SESSION['flash'] = "Archivo subido correctamente (ID: $insert_id, Versión: $nextVersion).";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

/* Acción: ELIMINAR (POST) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id <= 0) {
        $_SESSION['flash'] = "ID inválido para eliminación.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // obtener nombre para eliminar archivo físico
    $stmt = mysqli_prepare($con, "SELECT nombre_archivo FROM archivos WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if (!$row) {
        $_SESSION['flash'] = "Registro no encontrado.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    $originalName = $row['nombre_archivo'];
    $safeName = sanitize_filename($originalName);
    $storedNamePattern = $id . '_' . $safeName;
    $filePath = $upload_dir . '/' . $storedNamePattern;
    // eliminar archivo si existe
    if (file_exists($filePath)) {
        @unlink($filePath);
    }

    // eliminar registro DB
    $stmt = mysqli_prepare($con, "DELETE FROM archivos WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $_SESSION['flash'] = "Archivo eliminado (ID: $id).";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

/* GET actions (antes de emitir HTML): view, download, get (json) */
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        header("HTTP/1.1 400 Bad Request");
        echo "ID inválido.";
        exit;
    }

    // obtener metadata
    $stmt = mysqli_prepare($con, "SELECT nombre_archivo, tipo_mime FROM archivos WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if (!$row) {
        header("HTTP/1.1 404 Not Found");
        echo "Registro no encontrado.";
        exit;
    }

    $originalName = $row['nombre_archivo'];
    $safeName = sanitize_filename($originalName);
    $storedName = $id . '_' . $safeName;
    $filePath = $upload_dir . '/' . $storedName;
    $mime = $row['tipo_mime'] ?: mime_content_type($filePath);

    if ($action === 'get') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'id' => $id,
            'nombre_archivo' => $row['nombre_archivo'],
            'tipo_mime' => $row['tipo_mime'],
            'tamano_bytes' => filesize($filePath) ?: 0
        ]);
        exit;
    }

    if (!file_exists($filePath)) {
        header("HTTP/1.1 404 Not Found");
        echo "Archivo físico no encontrado.";
        exit;
    }

    if ($action === 'view') {
        // mostrar inline cuando sea posible (permite embebido en iframe/img/video)
        // NO forzamos Content-Disposition attachment aquí; usamos inline
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($filePath));
        header('Content-Disposition: inline; filename="' . basename($originalName) . '"');
        // evitar que navegadores hagan sniffing equivocado
        header('X-Content-Type-Options: nosniff');
        readfile($filePath);
        exit;
    }

    if ($action === 'download') {
        // Forzar descarga
        header('Content-Type: application/octet-stream');
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename="' . basename($originalName) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    // unknown action
    header("HTTP/1.1 400 Bad Request");
    echo "Acción desconocida.";
    exit;
}

/* ========== FIN HANDLERS - Ahora preparar datos para mostrar la tabla ========== */

/* Obtener todos los archivos */
$stmt = mysqli_prepare($con, "SELECT id, nombre_archivo, tipo_mime, tamano_bytes, num_version FROM archivos ORDER BY id DESC");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$archivos = [];
while ($row = mysqli_fetch_assoc($result)) {
    $archivos[] = $row;
}
mysqli_stmt_close($stmt);

/* Mensaje flash (si existe) */
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de archivos - TI</title>

    <!-- Tus estilos generales (mantenidos tal cual) -->
    <link rel="stylesheet" href="adminstyle.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/dce333249f.js" crossorigin="anonymous"></script>

    <!-- Estilos personalizados integrados (no cambié diseño) -->
    <style>
    /* (Se mantiene igual que tu template original; copié exactamente los estilos) */
    main.table {
        height: 95vh !important;
        max-height: 95vh !important;
        padding-top: 1rem;
    }
    .table__header {
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
        align-items: center !important;
        gap: 10px !important;
        height: auto !important;
        min-height: 120px !important;
        padding: 0.75rem 1rem !important;
        position: relative;
        z-index: 4;
    }
    .table__header h1 {
        margin: 0 !important;
        text-align: center !important;
        width: 100% !important;
        color: #ffffff !important;
        font-size: 2rem;
        font-weight: 600;
    }
    .header-controls {
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        gap: 15px !important;
        width: auto !important;
    }
    .table__header .input-group {
        width: 400px !important;
        background-color: rgba(255, 255, 255, 0.9) !important;
        border: 1px solid rgba(255, 255, 255, 0.95) !important;
        border-radius: 25px !important;
        transition: width 0.3s ease, background-color 0.2s ease, border-color 0.2s ease !important;
        margin: 0 !important;
        flex-shrink: 0 !important;
        padding: 0.25rem 0.75rem !important;
    }
    .table__header .input-group:hover {
        width: 500px !important;
        background-color: rgba(255, 255, 255, 1) !important;
        border-color: rgba(255, 255, 255, 1) !important;
    }
    .table__header .input-group input {
        background-color: transparent !important;
        color: #333 !important;
        border: none !important;
        box-shadow: none !important;
    }
    .table__header .input-group input:focus {
        outline: none !important;
        border: none !important;
        box-shadow: none !important;
    }
    .table__header .input-group input::placeholder {
        color: rgba(0, 0, 0, 0.4) !important;
    }
    .btn-regresar {
        background-color: #6B7280 !important;
        color: white !important;
        border: none !important;
        padding: 10px 20px !important;
        border-radius: 8px !important;
        cursor: pointer !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        transition: background-color 0.3s ease !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2) !important;
        white-space: nowrap !important;
        flex-shrink: 0 !important;
    }
    .btn-regresar:hover {
        background-color: #4B5563 !important;
    }
    .btn-nuevo {
        background-color: #10B981 !important;
        color: white !important;
        border: none !important;
        padding: 10px 20px !important;
        border-radius: 8px !important;
        cursor: pointer !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        transition: background-color 0.3s ease !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2) !important;
        white-space: nowrap !important;
        flex-shrink: 0 !important;
    }
    .btn-nuevo:hover {
        background-color: #059669 !important;
    }
    .table__body {
        position: relative;
        max-height: calc(85% - 1.6rem) !important;
        margin-top: 5px !important;
        z-index: 2;
        padding: 0;
        transition: margin-top 0.18s ease;
        background-color: #F9FAFB !important;
    }
    .table-borderless > :not(caption) > * > * {
        border: none !important;
        border-bottom: 1px solid #D1D5DB !important;
    }
    .table-borderless thead th {
        border-bottom: 2px solid #D1D5DB !important;
    }
    .table__body table {
        width: 100%;
        border-collapse: collapse;
        background: #F9FAFB;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 6px 18px rgba(0,0,0,0.12);
        margin: 0;
        border: none !important;
    }
    .table__body table tbody tr td,
    .table__body table thead tr th {
        border-left: none !important;
        border-right: none !important;
        border-top: none !important;
    }
    thead th {
        background-color: #1F2937 !important;
        color: #FFFFFF !important;
        font-weight: 700 !important;
        text-align: center !important;
        padding: 1rem !important;
        border-bottom: 2px solid #D1D5DB !important;
        border-left: none !important;
        border-right: none !important;
        border-top: none !important;
    }
    thead th:last-child,
    tbody td:last-child {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
    tbody tr {
        background-color: #F9FAFB !important;
    }
    tbody tr:not(.grupo-separador):hover {
        background-color: #E5E7EB !important;
    }
    tbody tr td {
        vertical-align: middle;
        font-size: 14px;
        text-align: center;
        color: #111827 !important;
        border-bottom: 1px solid #D1D5DB !important;
        border-left: none !important;
        border-right: none !important;
        border-top: none !important;
        padding: 1rem;
        background-color: inherit !important;
    }
    tbody tr:not(.grupo-separador) td {
        background-color: #F9FAFB !important;
    }
    tbody tr:not(.grupo-separador):hover td {
        background-color: #E5E7EB !important;
    }
    .grupo-separador {
        font-weight: 600;
    }
    .grupo-separador td {
        padding: 0.75rem 1rem !important;
        text-align: left !important;
        border-bottom: 2px solid #D1D5DB !important;
        background-color: #1F2937 !important;
        color: #FFFFFF !important;
    }
    tbody tr:not(.grupo-separador) td:nth-child(5) {
        color: #2563EB !important;
        font-weight: 500;
    }
    tbody tr:not(.grupo-separador) td:nth-child(7) {
        color: #059669 !important;
        font-weight: 500;
    }
    .action-icons {
        display: flex;
        gap: 8px;
        justify-content: center;
        align-items: center;
        background-color: transparent !important;
    }
    .action-icons a, .action-icons button {
        background: none !important;
        background-color: transparent !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
        cursor: pointer;
        font-size: 18px;
        transition: transform 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .action-icons a:hover, .action-icons button:hover {
        transform: scale(1.15);
        background: none !important;
        background-color: transparent !important;
    }
    .action-icons .fa-eye { color: #2563EB; }
    .action-icons .fa-pen-to-square { color: #F59E0B; }
    .action-icons .fa-trash { color: #F59E0B; }
    .action-icons .fa-right-from-bracket { color: #6B7280; }
    @media (max-width: 900px) {
        .table__header .input-group { width: 220px !important; }
        .table__header .input-group:hover { width: 280px !important; }
        .table__body { margin-top: 5px !important; padding: 0 0.75rem; }
        .table__header { min-height: 100px !important; gap: 8px !important; }
        .header-controls { flex-wrap: wrap; gap: 10px !important; }
    }

    /* Estilos para el preview dentro del modal */
    .preview-container {
        width: 100%;
        height: 60vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #111827;
        color: #fff;
    }
    .preview-container iframe,
    .preview-container img,
    .preview-container video {
        max-width: 100%;
        max-height: 100%;
        width: 100%;
        height: 100%;
        border: none;
        background: #fff;
    }
    </style>
</head>
<body>
    <main class="table" id="customers_table">
        <section class="table__header">
            <h1>Administración de archivos</h1>

            <div class="header-controls">
                <!-- Botón Regresar -->
                <button class="btn-regresar" onclick="window.location.href='../index.php'">
                    <i class="fa-solid fa-arrow-left"></i>
                    Regresar
                </button>

                <!-- Barra de búsqueda -->
                <div class="input-group">
                    <input type="search" class="form-control" placeholder="Buscar" id="searchInput">
                </div>

                <!-- Botón Nuevo Archivo -->
                <button class="btn-nuevo" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fa-solid fa-file-circle-plus"></i>
                    Nuevo Archivo
                </button>
            </div>
        </section>

        <section class="table__body">
            <table class="table table-borderless">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 5%;">ID</th>
                        <th style="width: 30%;">Nombre del archivo</th>
                        <th style="width: 20%;">Tipo MIME</th>
                        <th style="width: 15%;">Tamaño</th>
                        <th style="width: 10%;">Versión</th>
                        <th style="text-align: center; width: 20%;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($flash): ?>
                        <tr class="grupo-separador"><td colspan="6"><?= htmlspecialchars($flash) ?></td></tr>
                    <?php endif; ?>

                    <?php if (empty($archivos)): ?>
                        <tr><td colspan="6">No hay archivos aún.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($archivos as $archivo): ?>
                        <tr>
                            <td style="text-align: center;"><?= htmlspecialchars($archivo['id']) ?></td>

                            <td style="text-align: left;"><?= htmlspecialchars($archivo['nombre_archivo']) ?></td>

                            <td><?= htmlspecialchars($archivo['tipo_mime']) ?></td>

                            <td>
                                <?php
                                    $b = (int)$archivo['tamano_bytes'];
                                    if ($b >= 1024*1024) {
                                        echo number_format($b / (1024*1024), 2) . ' MB';
                                    } elseif ($b >= 1024) {
                                        echo number_format($b / 1024, 2) . ' KB';
                                    } else {
                                        echo $b . ' B';
                                    }
                                ?>
                            </td>

                            <td><?= htmlspecialchars($archivo['num_version']) ?></td>

                            <td style="vertical-align: middle; padding: 1rem;">
                                <div class="action-icons">
                                    <!-- VER (abrirá modal con preview) -->
                                    <a href="#" class="view-link" data-id="<?= $archivo['id'] ?>" title="Ver">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>

                                    <!-- DESCARGAR -->
                                    <a href="?action=download&id=<?= $archivo['id'] ?>" title="Descargar">
                                        <i class="fa-solid fa-download"></i>
                                    </a>

                                    <!-- ELIMINAR (abre modal) -->
                                    <a href="#" class="delete-btn" data-id="<?= $archivo['id'] ?>" data-name="<?= htmlspecialchars($archivo['nombre_archivo']) ?>" data-bs-toggle="modal" data-bs-target="#deleteUserModal" title="Eliminar">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <!-- Modal para Agregar Archivo -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Nuevo Archivo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="addUserForm" action="<?= $_SERVER['PHP_SELF'] ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="upload">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="archivo" class="form-label">Seleccionar archivo</label>
                            <input type="file" class="form-control" id="archivo" name="archivo" required>
                        </div>
                        <p class="small text-muted">Se calculará automáticamente la versión (si ya existe el mismo nombre, aumentará la versión).</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Subir Archivo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Ver / Previsualizar Archivo -->
    <div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl"> <!-- más ancho para preview -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Previsualización</h5>
                    <div class="ms-auto d-flex gap-2">
                        <button id="fullscreenBtn" type="button" class="btn btn-sm btn-outline-secondary" title="Pantalla completa">
                            <i class="fa-solid fa-expand"></i> Pantalla completa
                        </button>
                        <a id="openInNewTab" href="#" target="_blank" class="btn btn-sm btn-outline-primary" title="Abrir en nueva pestaña">
                            <i class="fa-solid fa-up-right-from-square"></i> Abrir
                        </a>
                        <a id="downloadLink" href="#" class="btn btn-sm btn-outline-success" title="Descargar">
                            <i class="fa-solid fa-download"></i> Descargar
                        </a>
                        <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <div id="previewWrapper" class="preview-container" role="region" aria-label="Previsualización del archivo">
                        <!-- Aquí se inyecta la previsualización: iframe / img / video -->
                        <div id="previewPlaceholder" style="color:#fff; text-align:center; padding:1rem;">
                            Cargando previsualización...
                        </div>
                    </div>

                    <div id="previewMeta" class="mt-3">
                        <!-- Metadatos (llenados dinámicamente) -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Eliminar Archivo -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteConfirmationText">¿Está seguro que desea eliminar este archivo?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="deleteUserForm" action="<?= $_SERVER['PHP_SELF'] ?>" method="POST" style="display: inline;">
                        <input type="hidden" id="deleteUserId" name="id">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts (mantengo jQuery + bootstrap como en tu plantilla) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // Búsqueda simple
    document.getElementById('searchInput').addEventListener('input', function() {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');

        rows.forEach(row => {
            if (row.classList && row.classList.contains('grupo-separador')) return;
            const rowText = row.textContent.toLowerCase();
            row.style.display = rowText.includes(searchValue) ? '' : 'none';
        });
    });

    // Delete modal wiring
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const fileId = this.getAttribute('data-id');
            const fileName = this.getAttribute('data-name');
            document.getElementById('deleteUserId').value = fileId;
            document.getElementById('deleteConfirmationText').textContent =
                `¿Está seguro que desea eliminar el archivo "${fileName}" (ID: ${fileId})?`;
        });
    });

    // PREVIEW (Ver) - click en icono de ojo
    document.querySelectorAll('.view-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            if (!id) return;
            // pedir metadata
            fetch(`?action=get&id=${id}`)
                .then(r => {
                    if (!r.ok) throw new Error('Error al obtener metadata');
                    return r.json();
                })
                .then(data => {
                    openPreviewModal(data);
                })
                .catch(err => {
                    alert('No se pudo cargar la previsualización.');
                    console.error(err);
                });
        });
    });

    function openPreviewModal(meta) {
        const modalEl = document.getElementById('viewUserModal');
        const previewWrapper = document.getElementById('previewWrapper');
        const previewMeta = document.getElementById('previewMeta');
        const openInNewTab = document.getElementById('openInNewTab');
        const downloadLink = document.getElementById('downloadLink');
        const fullscreenBtn = document.getElementById('fullscreenBtn');

        // limpiar contenido previo
        previewWrapper.innerHTML = '<div id="previewPlaceholder" style="color:#fff; text-align:center; padding:1rem;">Cargando previsualización...</div>';
        previewMeta.innerHTML = '';
        openInNewTab.href = `?action=view&id=${encodeURIComponent(meta.id)}`;
        downloadLink.href = `?action=download&id=${encodeURIComponent(meta.id)}`;

        // construir preview según tipo MIME
        const mime = (meta.tipo_mime || '').toLowerCase();

        // función para crear iframe/img/video elements
        function makeIframe(src) {
            const iframe = document.createElement('iframe');
            iframe.src = src;
            iframe.setAttribute('allowfullscreen', '');
            iframe.style.border = 'none';
            iframe.style.width = '100%';
            iframe.style.height = '100%';
            return iframe;
        }
        function makeImg(src) {
            const img = document.createElement('img');
            img.src = src;
            img.alt = meta.nombre_archivo;
            img.style.objectFit = 'contain';
            img.style.display = 'block';
            img.style.margin = '0 auto';
            return img;
        }
        function makeVideo(src, type) {
            const video = document.createElement('video');
            video.controls = true;
            video.src = src;
            video.style.maxWidth = '100%';
            video.style.maxHeight = '100%';
            return video;
        }

        const src = `?action=view&id=${encodeURIComponent(meta.id)}`;

        // Decide qué elemento usar
        let previewElement;
        if (mime.startsWith('image/')) {
            previewElement = makeImg(src);
        } else if (mime === 'application/pdf') {
            previewElement = makeIframe(src);
        } else if (mime.startsWith('text/') || mime === 'application/json' || mime === 'application/javascript') {
            // mostrar en iframe (text/plain), navegador hará render
            previewElement = makeIframe(src);
        } else if (mime.startsWith('video/')) {
            previewElement = makeVideo(src, mime);
        } else if (mime.startsWith('audio/')) {
            const audio = document.createElement('audio');
            audio.controls = true;
            audio.src = src;
            previewElement = audio;
        } else {
            // tipo desconocido -> mostrar iframe con mensaje o un link para abrir en nueva pestaña
            const wrapper = document.createElement('div');
            wrapper.style.padding = '1rem';
            wrapper.style.color = '#fff';
            wrapper.innerHTML = `<p>Previsualización no disponible para este tipo de archivo (${escapeHtml(mime)}).</p>
                                 <p><a href="${src}" target="_blank" class="btn btn-sm btn-light">Abrir en nueva pestaña</a></p>`;
            previewElement = wrapper;
        }

        // insertar
        previewWrapper.innerHTML = ''; // limpiar placeholder
        previewWrapper.appendChild(previewElement);

        // llenar metadatos abajo
        const metaHtml = `
            <div class="row">
                <div class="col-md-4 fw-bold">ID:</div><div class="col-md-8">${escapeHtml(meta.id)}</div>
            </div>
            <div class="row">
                <div class="col-md-4 fw-bold">Nombre:</div><div class="col-md-8">${escapeHtml(meta.nombre_archivo)}</div>
            </div>
            <div class="row">
                <div class="col-md-4 fw-bold">Tipo MIME:</div><div class="col-md-8">${escapeHtml(meta.tipo_mime)}</div>
            </div>
        `;
        previewMeta.innerHTML = metaHtml;

        // Fullscreen button behavior
        fullscreenBtn.onclick = function() {
            // request fullscreen on the element that contains the preview
            // prefer the actual media element if possible for better fullscreen experience
            let target = previewElement;
            // for iframe wrapper or generic elements, request on previewWrapper
            if (previewElement.tagName.toLowerCase() === 'iframe' || previewElement.tagName.toLowerCase() === 'div') {
                target = previewWrapper;
            }
            if (target.requestFullscreen) {
                target.requestFullscreen();
            } else if (target.webkitRequestFullscreen) { /* Safari */
                target.webkitRequestFullscreen();
            } else if (target.mozRequestFullScreen) { /* Firefox */
                target.mozRequestFullScreen();
            } else if (target.msRequestFullscreen) { /* IE/Edge */
                target.msRequestFullscreen();
            } else {
                alert('Pantalla completa no está soportada en este navegador.');
            }
        };

        // show modal
        var myModal = new bootstrap.Modal(document.getElementById('viewUserModal'));
        myModal.show();
    }

    // pequeña función de escape para seguridad al inyectar texto
    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }
    </script>
</body>
</html>
