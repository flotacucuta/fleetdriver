<?php
session_start();
error_reporting(0); // Evita que Warnings de PHP se impriman en HTML y rompan el JSON
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

// Validación base: El usuario debe estar logueado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No logueado']);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$rol_actual = $_SESSION['rol'] ?? 'EMPLEADO';

// Si es un empleado (operario), SOLO le permitimos usar las rutas de lectura para pintar sus tarjetas
if (in_array($rol_actual, ['EMPLEADO', 'OPERARIO']) && !in_array($action, ['getRegistros', 'getDocumentos'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado para modificar datos']);
    exit();
}

require_once '../include/config.php';

try {
    $mi_rol = $_SESSION['rol'];
    $mi_centro = $_SESSION['centro_id'];

    switch ($action) {

        case 'getRegistros': getRegistros($mi_rol, $mi_centro); break;
        case 'getRegistro': getRegistro($mi_rol, $mi_centro); break;
        case 'saveRegistro': saveRegistro($mi_rol, $mi_centro); break;
        case 'updateRegistro': updateRegistro($mi_rol, $mi_centro); break;
        case 'updateFoto': updateFoto(); break;
        case 'deleteRegistro': deleteRegistro(); break;
        case 'getDocumentos': getDocumentos(); break;
        case 'deleteDocumento': deleteDocumento(); break;
        
        // --- ACCIONES PARA CONFIGURACIÓN Y USUARIOS ---
        case 'saveUsuario': saveUsuario($mi_rol, $mi_centro); break;
        case 'saveCentro': saveCentro($mi_rol); break;
        case 'saveCargo': saveCargo($mi_rol); break;
        case 'saveTipoDocumento': saveTipoDocumento($mi_rol); break;
        
        case 'getUsuarios': getUsuarios($mi_rol, $mi_centro); break;
        case 'getCentros': getCentros(); break;
        case 'getCargos': getCargos(); break;
        case 'getTiposDocumento': getTiposDocumento(); break;
        
        // Actualizaciones
        case 'updateUsuario': updateUsuario($mi_rol); break;
        case 'updateCentro': updateCentro(); break;
        case 'updateTipoDocumento': updateTipoDocumento($mi_rol); break;
        
        // Eliminaciones (Borrado en Cascada)
        case 'deleteUsuario': deleteUsuario(); break;
        case 'deleteCentro': deleteGeneric('centros_distribucion'); break;
        case 'deleteCargo': deleteGeneric('cargos'); break;
        case 'deleteTipoDocumento': deleteGeneric('tipos_documento'); break;
        case 'getInspecciones': getInspecciones(); break;
        case 'saveInspeccion': saveInspeccion(); break;
        // Edición de un solo documento a la vez
        case 'saveSingleDoc': saveSingleDoc(); break;
        
        default: echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Error crítico en servidor: ' . $e->getMessage()]);
}

function getRegistros($rol, $centro_id) {
    $conn = getConnection();
    $sql = "SELECT r.*, c.nombre AS nombre_centro 
            FROM registros r 
            LEFT JOIN centros_distribucion c ON r.centro_id = c.id ";
    
    if ($rol === 'ADMIN') {
        $sql .= " WHERE r.centro_id = ? ORDER BY r.fecha_registro DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $centro_id);
    } else {
        $sql .= " ORDER BY r.fecha_registro DESC";
        $stmt = $conn->prepare($sql);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $registros = [];
    while ($row = $result->fetch_assoc()) {
        $registros[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => true, 'data' => $registros]);
}

function getRegistro($rol, $centro_id) {
    $conn = getConnection();
    $id = intval($_GET['id'] ?? 0);
    
    $sql = "SELECT * FROM registros WHERE id = ?";
    if ($rol === 'ADMIN') {
        $sql .= " AND centro_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id, $centro_id);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => true, 'data' => $result->fetch_assoc()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registro no encontrado o sin permisos']);
    }
    
    $stmt->close();
    $conn->close();
}

// --- FUNCIONES DE INSPECCIONES SEMANALES ---
function getInspecciones() {
    $conn = getConnection();
    $modulo = $_GET['modulo'] ?? '';
    $tipo = $_GET['tipo'] ?? '';
    
    $stmt = $conn->prepare("SELECT identificador, semana, estado FROM inspecciones WHERE modulo = ? AND tipo = ?");
    $stmt->bind_param("ss", $modulo, $tipo);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = [];
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $data]);
    $stmt->close();
    $conn->close();
}

function saveInspeccion() {
    $conn = getConnection();
    
    // Obtener datos crudos enviados por Fetch API (JSON)
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        $modulo = $_POST['modulo'] ?? '';
        $tipo = $_POST['tipo'] ?? '';
        $identificador = $_POST['identificador'] ?? '';
        $semana = intval($_POST['semana'] ?? 0);
        $estado = $_POST['estado'] ?? 'empty';
    } else {
        $modulo = $data['modulo'] ?? '';
        $tipo = $data['tipo'] ?? '';
        $identificador = $data['identificador'] ?? '';
        $semana = intval($data['semana'] ?? 0);
        $estado = $data['estado'] ?? 'empty';
    }

    if (empty($modulo) || empty($tipo) || empty($identificador) || $semana <= 0) {
        echo json_encode(['success' => false, 'message' => 'Faltan parámetros']);
        return;
    }

    // Insertar o actualizar (Upsert)
    $stmt = $conn->prepare("INSERT INTO inspecciones (modulo, tipo, identificador, semana, estado) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE estado = VALUES(estado)");
    $stmt->bind_param("sssis", $modulo, $tipo, $identificador, $semana, $estado);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar']);
    }
    
    $stmt->close();
    $conn->close();
}

function saveRegistro($rol, $mi_centro) {
    $conn = getConnection();
    
    $centro_final = ($rol === 'SUPERADMIN' && !empty($_POST['centro_id'])) ? intval($_POST['centro_id']) : $mi_centro;
    $centro_final = empty($centro_final) ? null : $centro_final;

    $contratista = $_POST['contratista'] ?? '';
    $tipo = $_POST['tipo'] ?? ''; 
    $modulo = !empty($_POST['modulo']) ? strtoupper(trim($_POST['modulo'])) : 'TRANSPORTE';
    $nombre = !empty($_POST['nombre']) ? strtoupper(trim($_POST['nombre'])) : null;
    $cedula = !empty($_POST['cedula']) ? trim($_POST['cedula']) : null;
    $cargo = !empty($_POST['cargo']) ? trim($_POST['cargo']) : null;
    $placa = !empty($_POST['placa']) ? strtoupper(trim($_POST['placa'])) : null;
    $pass = !empty($_POST['pass']) ? $_POST['pass'] : ($cedula ?? '123456');
    
    $usuario_id = null;

    if ($tipo === 'OPERARIO' || $tipo === 'CONDUCTOR') {
        if (empty($cedula)) {
            echo json_encode(['success' => false, 'message' => 'La cédula es obligatoria']);
            return;
        }

        $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE cedula = ?");
        $stmt_check->bind_param("s", $cedula);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'La cédula ya está registrada como usuario.']);
            $stmt_check->close();
            $conn->close();
            return;
        }
        $stmt_check->close();

        $pass_hash = password_hash($pass, PASSWORD_BCRYPT);
        $rol_user = 'EMPLEADO';
        $estado = 1;
        
        $stmt_user = $conn->prepare("INSERT INTO usuarios (centro_id, nombre, cedula, password, rol, estado) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_user->bind_param("issssi", $centro_final, $nombre, $cedula, $pass_hash, $rol_user, $estado);
        if (!$stmt_user->execute()) {
            echo json_encode(['success' => false, 'message' => 'Error creando usuario: ' . $stmt_user->error]);
            return;
        }
        $usuario_id = $stmt_user->insert_id;
        $stmt_user->close();
    }

    $fotoNombre = procesarFoto();

    $sql = "INSERT INTO registros (centro_id, usuario_id, tipo_contratista, tipo_registro, modulo, cargo, nombre, cedula, placa, foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissssssss", $centro_final, $usuario_id, $contratista, $tipo, $modulo, $cargo, $nombre, $cedula, $placa, $fotoNombre);
    
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Error al guardar el registro: ' . $stmt->error]);
        return;
    }
    
    $registroId = $stmt->insert_id;
    $stmt->close();

    procesarDocumentos($conn, $registroId);
    
    $conn->close();
    echo json_encode(['success' => true, 'message' => 'Registro guardado exitosamente', 'id' => $registroId]);
}

function updateRegistro($rol, $mi_centro) {
    $conn = getConnection();
    
    $id = intval($_POST['id'] ?? 0);
    $contratista = $_POST['contratista'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $modulo = !empty($_POST['modulo']) ? strtoupper(trim($_POST['modulo'])) : 'TRANSPORTE';
    $nombre = !empty($_POST['nombre']) ? strtoupper(trim($_POST['nombre'])) : null;
    $cedula = !empty($_POST['cedula']) ? trim($_POST['cedula']) : null;
    $cargo = !empty($_POST['cargo']) ? trim($_POST['cargo']) : null;
    $placa = !empty($_POST['placa']) ? strtoupper(trim($_POST['placa'])) : null;

    $stmt_current = $conn->prepare("SELECT usuario_id FROM registros WHERE id = ?");
    $stmt_current->bind_param("i", $id);
    $stmt_current->execute();
    $res_current = $stmt_current->get_result();
    $current = $res_current->fetch_assoc();
    $stmt_current->close();

    $sql = "UPDATE registros SET tipo_contratista=?, tipo_registro=?, modulo=?, cargo=?, nombre=?, cedula=?, placa=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $contratista, $tipo, $modulo, $cargo, $nombre, $cedula, $placa, $id);
    
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el registro']);
        return;
    }
    $stmt->close();

    // Sincronizar el nombre y cédula en la tabla de usuarios
    if ($current && $current['usuario_id']) {
        $stmt_user = $conn->prepare("UPDATE usuarios SET nombre=?, cedula=? WHERE id=?");
        $stmt_user->bind_param("ssi", $nombre, $cedula, $current['usuario_id']);
        $stmt_user->execute();
        $stmt_user->close();
    }

    procesarDocumentos($conn, $id, true);
    
    $conn->close();
    echo json_encode(['success' => true, 'message' => 'Registro actualizado exitosamente']);
}

function procesarFoto() {
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $uploadDir = '../uploads/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $fotoNombre = uniqid() . '_' . time() . '.' . $extension;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $fotoNombre)) {
            return $fotoNombre;
        }
    }
    return null;
}

function procesarDocumentos($conn, $registroId, $isUpdate = false) {
    if (!isset($_POST['documentos'])) return;
    $documentos = json_decode($_POST['documentos'], true);
    if (!$documentos || !is_array($documentos)) return;

    $uploadDocDir = '../uploads/documentos/';
    if (!file_exists($uploadDocDir)) mkdir($uploadDocDir, 0777, true);

    foreach ($documentos as $doc) {
        $tipoDoc = strtoupper(trim($doc['tipo']));
        $fechaExp = !empty($doc['fecha_expedicion']) ? $doc['fecha_expedicion'] : null;
        $fechaVenc = !empty($doc['fecha_vencimiento']) ? $doc['fecha_vencimiento'] : null;
        $archivoTipo = $doc['archivo_tipo'] ?? 'PDF';
        $archivoValor = null;

        if ($isUpdate && isset($doc['id'])) {
            $docId = intval($doc['id']);
            $stmtOld = $conn->prepare("SELECT archivo_valor, archivo_tipo FROM documentos WHERE id = ?");
            $stmtOld->bind_param("i", $docId);
            $stmtOld->execute();
            $oldData = $stmtOld->get_result()->fetch_assoc();
            $stmtOld->close();

            $archivoValor = $oldData ? $oldData['archivo_valor'] : null;
            
            if ($archivoTipo === 'PDF' && isset($doc['archivo_index'])) {
                $fileKey = "documento_pdf_" . $doc['archivo_index'];
                if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] == 0) {
                    $ext = pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION);
                    $nuevoNombre = uniqid() . '_edit_' . time() . '.' . $ext;
                    if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $uploadDocDir . $nuevoNombre)) {
                        if ($oldData && $oldData['archivo_tipo'] === 'PDF' && !empty($oldData['archivo_valor']) && is_file($uploadDocDir . $oldData['archivo_valor'])) {
                            unlink($uploadDocDir . $oldData['archivo_valor']);
                        }
                        $archivoValor = $nuevoNombre;
                    }
                }
            } else if ($archivoTipo === 'LINK' && $oldData) {
                if ($oldData['archivo_tipo'] === 'PDF' && !empty($oldData['archivo_valor']) && is_file($uploadDocDir . $oldData['archivo_valor'])) {
                    unlink($uploadDocDir . $oldData['archivo_valor']);
                }
                $archivoValor = $doc['archivo_valor'] ?? '';
            }

            $stmtUpd = $conn->prepare("UPDATE documentos SET tipo_documento=?, fecha_expedicion=?, fecha_vencimiento=?, archivo_tipo=?, archivo_valor=? WHERE id=?");
            $stmtUpd->bind_param("sssssi", $tipoDoc, $fechaExp, $fechaVenc, $archivoTipo, $archivoValor, $docId);
            $stmtUpd->execute();
            $stmtUpd->close();
            continue;
        }

        if ($archivoTipo === 'PDF' && isset($doc['archivo_index'])) {
            $fileKey = "documento_pdf_" . $doc['archivo_index'];
            if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] == 0) {
                $ext = pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION);
                $archivoValor = uniqid() . '_' . time() . '.' . $ext;
                if (!move_uploaded_file($_FILES[$fileKey]['tmp_name'], $uploadDocDir . $archivoValor)) continue;
            }
        } else if ($archivoTipo === 'LINK') {
            $archivoValor = $doc['archivo_valor'] ?? '';
        }

        if ($archivoValor) {
            $stmtIns = $conn->prepare("INSERT INTO documentos (registro_id, tipo_documento, fecha_expedicion, fecha_vencimiento, archivo_tipo, archivo_valor) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtIns->bind_param("isssss", $registroId, $tipoDoc, $fechaExp, $fechaVenc, $archivoTipo, $archivoValor);
            $stmtIns->execute();
            $stmtIns->close();
        }
    }
}

function updateFoto() {
    $conn = getConnection();
    $id = intval($_POST['id'] ?? 0);
    
    $stmt = $conn->prepare("SELECT foto FROM registros WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (isset($_POST['remove_foto']) && $_POST['remove_foto'] == '1') {
        if ($row && !empty($row['foto']) && is_file('../uploads/' . $row['foto'])) {
            unlink('../uploads/' . $row['foto']);
        }
        $conn->query("UPDATE registros SET foto = NULL WHERE id = $id");
        echo json_encode(['success' => true]);
        return;
    }
    
    $fotoNombre = procesarFoto();
    if ($fotoNombre) {
        if ($row && !empty($row['foto']) && is_file('../uploads/' . $row['foto'])) {
            unlink('../uploads/' . $row['foto']);
        }
        $stmtUpdate = $conn->prepare("UPDATE registros SET foto = ? WHERE id = ?");
        $stmtUpdate->bind_param("si", $fotoNombre, $id);
        $stmtUpdate->execute();
        echo json_encode(['success' => true]);
    }
}

// Borra la ficha operativa y también el usuario si lo tiene vinculado
function deleteRegistro() {
    $conn = getConnection();
    $id = intval($_GET['id'] ?? 0);
    
    $stmt = $conn->prepare("SELECT foto, usuario_id FROM registros WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($row && !empty($row['foto']) && is_file('../uploads/' . $row['foto'])) {
        unlink('../uploads/' . $row['foto']);
    }
    
    $stmtDocs = $conn->prepare("SELECT archivo_valor FROM documentos WHERE registro_id = ? AND archivo_tipo = 'PDF'");
    $stmtDocs->bind_param("i", $id);
    $stmtDocs->execute();
    $resDocs = $stmtDocs->get_result();
    while ($doc = $resDocs->fetch_assoc()) {
        if (!empty($doc['archivo_valor']) && is_file('../uploads/documentos/' . $doc['archivo_valor'])) {
            unlink('../uploads/documentos/' . $doc['archivo_valor']);
        }
    }
    $stmtDocs->close();
    
    $conn->query("DELETE FROM registros WHERE id = $id");
    
    if ($row && $row['usuario_id']) {
        $conn->query("DELETE FROM usuarios WHERE id = " . $row['usuario_id']);
    }
    
    echo json_encode(['success' => true]);
}

function getDocumentos() {
    $conn = getConnection();
    $id = intval($_GET['id'] ?? 0);
    $stmt = $conn->prepare("SELECT * FROM documentos WHERE registro_id = ? ORDER BY fecha_vencimiento ASC");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $docs = [];
    while ($row = $res->fetch_assoc()) $docs[] = $row;
    echo json_encode(['success' => true, 'data' => $docs]);
}

function deleteDocumento() {
    $conn = getConnection();
    $id = intval($_GET['id'] ?? 0);
    $stmt = $conn->prepare("SELECT archivo_valor, archivo_tipo FROM documentos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $doc = $stmt->get_result()->fetch_assoc();
    
    if ($doc && $doc['archivo_tipo'] === 'PDF' && !empty($doc['archivo_valor']) && is_file('../uploads/documentos/' . $doc['archivo_valor'])) {
        unlink('../uploads/documentos/' . $doc['archivo_valor']);
    }
    $conn->query("DELETE FROM documentos WHERE id = $id");
    echo json_encode(['success' => true]);
}

function saveSingleDoc() {
    $conn = getConnection();
    
    $registro_id = intval($_POST['registro_id'] ?? 0);
    $doc_id = intval($_POST['doc_id'] ?? 0);
    $tipo_documento = $_POST['tipo_documento'] ?? '';
    $fecha_exp = !empty($_POST['fecha_expedicion']) ? $_POST['fecha_expedicion'] : null;
    $fecha_venc = !empty($_POST['fecha_vencimiento']) ? $_POST['fecha_vencimiento'] : null;
    $archivo_tipo = $_POST['archivo_tipo'] ?? 'PDF';
    
    $archivo_valor = null;
    $uploadDocDir = '../uploads/documentos/';
    
    if (!file_exists($uploadDocDir)) mkdir($uploadDocDir, 0777, true);

    if ($doc_id > 0) {
        $stmtOld = $conn->prepare("SELECT archivo_valor, archivo_tipo FROM documentos WHERE id = ?");
        $stmtOld->bind_param("i", $doc_id);
        $stmtOld->execute();
        $oldData = $stmtOld->get_result()->fetch_assoc();
        $stmtOld->close();

        $archivo_valor = $oldData ? $oldData['archivo_valor'] : null; 
        
        if ($archivo_tipo === 'PDF' && isset($_FILES['archivo_pdf']) && $_FILES['archivo_pdf']['error'] == 0) {
            $ext = pathinfo($_FILES['archivo_pdf']['name'], PATHINFO_EXTENSION);
            $nuevoNombre = uniqid() . '_edit_' . time() . '.' . $ext;
            
            if (move_uploaded_file($_FILES['archivo_pdf']['tmp_name'], $uploadDocDir . $nuevoNombre)) {
                if ($oldData && $oldData['archivo_tipo'] === 'PDF' && !empty($oldData['archivo_valor']) && is_file($uploadDocDir . $oldData['archivo_valor'])) {
                    unlink($uploadDocDir . $oldData['archivo_valor']); 
                }
                $archivo_valor = $nuevoNombre;
            }
        } else if ($archivo_tipo === 'LINK') {
            if ($oldData && $oldData['archivo_tipo'] === 'PDF' && !empty($oldData['archivo_valor']) && is_file($uploadDocDir . $oldData['archivo_valor'])) {
                unlink($uploadDocDir . $oldData['archivo_valor']);
            }
            $archivo_valor = $_POST['archivo_valor'] ?? '';
        }

        $stmtUpd = $conn->prepare("UPDATE documentos SET tipo_documento=?, fecha_expedicion=?, fecha_vencimiento=?, archivo_tipo=?, archivo_valor=? WHERE id=?");
        $stmtUpd->bind_param("sssssi", $tipo_documento, $fecha_exp, $fecha_venc, $archivo_tipo, $archivo_valor, $doc_id);
        
        if ($stmtUpd->execute()) {
            echo json_encode(['success' => true, 'message' => 'Documento actualizado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error BD: ' . $stmtUpd->error]);
        }
        $stmtUpd->close();
        
    } else {
        if ($archivo_tipo === 'PDF') {
            if (isset($_FILES['archivo_pdf']) && $_FILES['archivo_pdf']['error'] == 0) {
                $ext = pathinfo($_FILES['archivo_pdf']['name'], PATHINFO_EXTENSION);
                $archivo_valor = uniqid() . '_' . time() . '.' . $ext;
                if (!move_uploaded_file($_FILES['archivo_pdf']['tmp_name'], $uploadDocDir . $archivo_valor)) {
                    $archivo_valor = null;
                }
            }
        } else {
            $archivo_valor = $_POST['archivo_valor'] ?? '';
        }

        if ($archivo_valor) {
            $stmtIns = $conn->prepare("INSERT INTO documentos (registro_id, tipo_documento, fecha_expedicion, fecha_vencimiento, archivo_tipo, archivo_valor) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtIns->bind_param("isssss", $registro_id, $tipo_documento, $fecha_exp, $fecha_venc, $archivo_tipo, $archivo_valor);
            
            if ($stmtIns->execute()) {
                echo json_encode(['success' => true, 'message' => 'Documento subido exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error BD: ' . $stmtIns->error]);
            }
            $stmtIns->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Faltó subir el archivo PDF o enlace']);
        }
    }
    
    $conn->close();
}

function saveUsuario($mi_rol, $mi_centro) {
    $conn = getConnection();
    $nombre = strtoupper(trim($_POST['nombre'] ?? ''));
    $cedula = trim($_POST['cedula'] ?? '');
    $pass = !empty($_POST['pass']) ? $_POST['pass'] : $cedula; 
    $rol_nuevo = $_POST['rol'] ?? 'ADMIN';
    
    $centro_final = ($mi_rol === 'SUPERADMIN' && !empty($_POST['centro_id'])) ? intval($_POST['centro_id']) : $mi_centro;
    $centro_final = empty($centro_final) ? null : $centro_final;

    if ($mi_rol !== 'SUPERADMIN' && $rol_nuevo === 'SUPERADMIN') {
        echo json_encode(['success' => false, 'message' => 'No tienes permisos para crear Super Administradores']);
        return;
    }

    $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE cedula = ?");
    $stmt_check->bind_param("s", $cedula);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Esta cédula ya tiene un usuario registrado.']);
        $stmt_check->close();
        return;
    }
    $stmt_check->close();

    $pass_hash = password_hash($pass, PASSWORD_BCRYPT);
    $estado = 1;
    
    $stmt = $conn->prepare("INSERT INTO usuarios (centro_id, nombre, cedula, password, rol, estado) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssi", $centro_final, $nombre, $cedula, $pass_hash, $rol_nuevo, $estado);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Usuario creado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear usuario']);
    }
    $stmt->close();
    $conn->close();
}

function saveCentro($mi_rol) {
    if ($mi_rol !== 'SUPERADMIN') {
        echo json_encode(['success' => false, 'message' => 'Acceso denegado']); return;
    }
    $conn = getConnection();
    $nombre = strtoupper(trim($_POST['nombre'] ?? ''));
    $direccion = trim($_POST['direccion'] ?? '');
    
    $stmt = $conn->prepare("INSERT INTO centros_distribucion (nombre, direccion, estado) VALUES (?, ?, 1)");
    $stmt->bind_param("ss", $nombre, $direccion);
    
    if ($stmt->execute()) echo json_encode(['success' => true, 'message' => 'Centro de distribución creado']);
    else echo json_encode(['success' => false, 'message' => 'Error al crear centro']);
    $stmt->close(); $conn->close();
}

function saveCargo($mi_rol) {
    if ($mi_rol !== 'SUPERADMIN') {
        echo json_encode(['success' => false, 'message' => 'Acceso denegado']); return;
    }
    $conn = getConnection();
    $modulo = trim($_POST['modulo'] ?? '');
    $nombre = strtoupper(trim($_POST['nombre'] ?? ''));
    
    $stmt = $conn->prepare("INSERT INTO cargos (modulo, nombre) VALUES (?, ?)");
    $stmt->bind_param("ss", $modulo, $nombre);
    
    if ($stmt->execute()) echo json_encode(['success' => true, 'message' => 'Cargo creado exitosamente']);
    else echo json_encode(['success' => false, 'message' => 'Error al crear cargo']);
    $stmt->close(); $conn->close();
}

function saveTipoDocumento($mi_rol) {
    if ($mi_rol !== 'SUPERADMIN') {
        echo json_encode(['success' => false, 'message' => 'Acceso denegado']); return;
    }
    $conn = getConnection();
    $nombre = strtoupper(trim($_POST['nombre'] ?? ''));
    $aplica_a = trim($_POST['aplica_a'] ?? '');
    $modulo = trim($_POST['modulo'] ?? 'AMBOS'); // Nuevo campo
    $es_obligatorio = intval($_POST['es_obligatorio'] ?? 1);
    
    $condicion_cargos = $_POST['condicion_cargos'] ?? 'TODOS';
    $cargos_relacionados = $_POST['cargos_relacionados'] ?? '[]'; 
    
    $stmt = $conn->prepare("INSERT INTO tipos_documento (nombre, aplica_a, modulo, es_obligatorio, condicion_cargos, cargos_relacionados) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiss", $nombre, $aplica_a, $modulo, $es_obligatorio, $condicion_cargos, $cargos_relacionados);
    
    if ($stmt->execute()) echo json_encode(['success' => true, 'message' => 'Tipo de documento guardado']);
    else echo json_encode(['success' => false, 'message' => 'Error BD: ' . $stmt->error]);
    $stmt->close(); $conn->close();
}

function updateTipoDocumento($mi_rol) {
    if ($mi_rol !== 'SUPERADMIN') {
        echo json_encode(['success' => false, 'message' => 'Acceso denegado']); return;
    }
    $conn = getConnection();
    $id = intval($_POST['id'] ?? 0);
    $nombre = strtoupper(trim($_POST['nombre'] ?? ''));
    $aplica_a = trim($_POST['aplica_a'] ?? '');
    $modulo = trim($_POST['modulo'] ?? 'AMBOS'); // Nuevo campo
    $es_obligatorio = intval($_POST['es_obligatorio'] ?? 1);
    
    $condicion_cargos = $_POST['condicion_cargos'] ?? 'TODOS';
    $cargos_relacionados = $_POST['cargos_relacionados'] ?? '[]'; 
    
    $stmt = $conn->prepare("UPDATE tipos_documento SET nombre=?, aplica_a=?, modulo=?, es_obligatorio=?, condicion_cargos=?, cargos_relacionados=? WHERE id=?");
    $stmt->bind_param("sssissi", $nombre, $aplica_a, $modulo, $es_obligatorio, $condicion_cargos, $cargos_relacionados, $id);
    
    if ($stmt->execute()) echo json_encode(['success' => true, 'message' => 'Tipo de documento actualizado correctamente']);
    else echo json_encode(['success' => false, 'message' => 'Error BD: ' . $stmt->error]);
    $stmt->close(); $conn->close();
}

function getUsuarios($rol, $centro_id) {
    $conn = getConnection();
    $sql = "SELECT u.id, u.nombre, u.cedula, u.rol, c.nombre as centro 
            FROM usuarios u 
            LEFT JOIN centros_distribucion c ON u.centro_id = c.id";
    if ($rol === 'ADMIN') {
        $sql .= " WHERE u.centro_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $centro_id);
    } else {
        $stmt = $conn->prepare($sql);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $data = [];
    while ($row = $res->fetch_assoc()) $data[] = $row;
    echo json_encode(['success' => true, 'data' => $data]);
}

function getCentros() {
    $conn = getConnection();
    $res = $conn->query("SELECT * FROM centros_distribucion");
    $data = [];
    while ($row = $res->fetch_assoc()) $data[] = $row;
    echo json_encode(['success' => true, 'data' => $data]);
}

function getCargos() {
    $conn = getConnection();
    $res = $conn->query("SELECT * FROM cargos");
    $data = [];
    while ($row = $res->fetch_assoc()) $data[] = $row;
    echo json_encode(['success' => true, 'data' => $data]);
}

function getTiposDocumento() {
    $conn = getConnection();
    $res = $conn->query("SELECT * FROM tipos_documento");
    $data = [];
    while ($row = $res->fetch_assoc()) $data[] = $row;
    echo json_encode(['success' => true, 'data' => $data]);
}

function updateUsuario($mi_rol) {
    $conn = getConnection();
    $id = intval($_POST['id'] ?? 0);
    $nombre = strtoupper(trim($_POST['nombre'] ?? ''));
    $cedula = trim($_POST['cedula'] ?? '');
    $rol = $_POST['rol'] ?? 'ADMIN';
    $pass = $_POST['pass'] ?? '';
    
    $centro_id = ($mi_rol === 'SUPERADMIN' && !empty($_POST['centro_id'])) ? intval($_POST['centro_id']) : null;

    $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE cedula = ? AND id != ?");
    $stmt_check->bind_param("si", $cedula, $id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'La cédula ya está en uso por otro usuario.']);
        return;
    }
    $stmt_check->close();

    // Construir query dinámicamente si envía contraseña nueva o centro
    $query = "UPDATE usuarios SET nombre = ?, cedula = ?, rol = ?";
    $types = "sss";
    $params = [$nombre, $cedula, $rol];

    if (!empty($pass)) {
        $query .= ", password = ?";
        $types .= "s";
        $params[] = password_hash($pass, PASSWORD_BCRYPT);
    }

    if ($mi_rol === 'SUPERADMIN') {
        if ($centro_id !== null) {
            $query .= ", centro_id = ?";
            $types .= "i";
            $params[] = $centro_id;
        } else {
            $query .= ", centro_id = NULL";
        }
    }

    $query .= " WHERE id = ?";
    $types .= "i";
    $params[] = $id;

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        // Sincronizar el registro operativo asociado a este usuario (por si cambia de nombre o cédula)
        $stmt_reg = $conn->prepare("UPDATE registros SET nombre = ?, cedula = ? WHERE usuario_id = ?");
        $stmt_reg->bind_param("ssi", $nombre, $cedula, $id);
        $stmt_reg->execute();
        $stmt_reg->close();

        echo json_encode(['success' => true, 'message' => 'Usuario actualizado exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error BD: ' . $stmt->error]);
    }
    
    $stmt->close(); $conn->close();
}

// Borra el usuario, y si es empleado también busca su ficha y borra su foto y documentos (Borrado en Cascada)
function deleteUsuario() {
    $conn = getConnection();
    $id = intval($_GET['id'] ?? 0);
    
    if ($id <= 0) { 
        echo json_encode(['success' => false, 'message' => 'ID inválido']); 
        return; 
    }

    // 1. Buscar la ficha operativa atada a este usuario
    $stmt = $conn->prepare("SELECT id, foto FROM registros WHERE usuario_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        $reg_id = $row['id'];
        
        // 2. Borrar Foto de disco duro
        if (!empty($row['foto']) && is_file('../uploads/' . $row['foto'])) {
            unlink('../uploads/' . $row['foto']);
        }
        
        // 3. Borrar Documentos PDF del disco duro
        $stmtDocs = $conn->prepare("SELECT archivo_valor FROM documentos WHERE registro_id = ? AND archivo_tipo = 'PDF'");
        $stmtDocs->bind_param("i", $reg_id);
        $stmtDocs->execute();
        $resDocs = $stmtDocs->get_result();
        while ($doc = $resDocs->fetch_assoc()) {
            if (!empty($doc['archivo_valor']) && is_file('../uploads/documentos/' . $doc['archivo_valor'])) {
                unlink('../uploads/documentos/' . $doc['archivo_valor']);
            }
        }
        $stmtDocs->close();
        
        // 4. Eliminar el registro operativo
        $conn->query("DELETE FROM registros WHERE id = $reg_id");
    }
    $stmt->close();
    
    // 5. Finalmente eliminar al usuario de acceso
    $stmtU = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmtU->bind_param("i", $id);
    
    if ($stmtU->execute()) {
        echo json_encode(['success' => true, 'message' => 'Usuario y registros vinculados eliminados.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error BD: ' . $stmtU->error]);
    }
    
    $stmtU->close(); 
    $conn->close();
}

function updateCentro() {
    $conn = getConnection();
    $id = intval($_POST['id'] ?? 0);
    $nombre = strtoupper(trim($_POST['nombre'] ?? ''));
    $direccion = trim($_POST['direccion'] ?? '');

    $sql = "UPDATE centros_distribucion SET nombre = ?, direccion = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $nombre, $direccion, $id);
    
    if ($stmt->execute()) echo json_encode(['success' => true, 'message' => 'Centro de distribución actualizado.']);
    else echo json_encode(['success' => false, 'message' => 'Error BD: ' . $stmt->error]);
    $stmt->close(); $conn->close();
}



function deleteGeneric($tabla) {
    $conn = getConnection();
    $id = intval($_GET['id'] ?? 0);
    
    if ($id <= 0) { echo json_encode(['success' => false, 'message' => 'ID inválido']); return; }

    $tablasPermitidas = ['centros_distribucion', 'cargos', 'tipos_documento'];
    if (!in_array($tabla, $tablasPermitidas)) {
        echo json_encode(['success' => false, 'message' => 'Tabla no autorizada.']); return;
    }

    $sql = "DELETE FROM $tabla WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Registro eliminado correctamente.']);
    } else {
        if ($conn->errno == 1451) echo json_encode(['success' => false, 'message' => 'No se puede eliminar porque este registro está siendo usado.']);
        else echo json_encode(['success' => false, 'message' => 'Error BD: ' . $stmt->error]);
    }
    $stmt->close(); $conn->close();
}
?>