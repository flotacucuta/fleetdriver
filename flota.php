<?php
session_start();
require_once 'include/config.php'; 

// Identificar al usuario actual
$user_rol = $_SESSION['rol'] ?? 'EMPLEADO';
$user_cedula = $_SESSION['cedula'] ?? ''; 
$user_id = $_SESSION['usuario_id'] ?? 0;

$modulo = isset($_GET['modulo']) ? strtoupper($_GET['modulo']) : 'TRANSPORTE';
$tipo_raw = isset($_GET['tipo']) ? $_GET['tipo'] : 'OPERARIO';

$tipo_parts = explode('/', $tipo_raw);
$tipo = $tipo_parts[0];
$filtro_id = isset($tipo_parts[1]) ? $tipo_parts[1] : ''; 

// Validar que el módulo y el tipo sean correctos
if (!in_array($modulo, ['TRANSPORTE', 'ALMACEN']) || !in_array($tipo, ['CONDUCTOR', 'VEHICULO', 'OPERARIO'])) {
    header('Location: index.php');
    exit;
}

// Estilos dinámicos según el módulo
$iconoModulo = $modulo === 'TRANSPORTE' ? '<i class="fas fa-truck-fast"></i>' : '<i class="fas fa-boxes-stacked"></i>';
$iconoTipo = $tipo === 'VEHICULO' ? '<i class="fas fa-truck"></i>' : '<i class="fas fa-user-tie"></i>';
$colorAcento = $modulo === 'TRANSPORTE' ? '#F5A623' : '#3b82f6';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $modulo; ?> - <?php echo $tipo === 'VEHICULO' ? 'Vehículos' : 'Operarios'; ?> - FleeDriver</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gold: '#F5A623', 'gold-hover': '#E0961D',
                        blue: '#3b82f6',
                        dark: '#0f172a', 'dark-hover': '#1e293b',
                        surface: '#ffffff',
                        background: '#f8fafc'
                    },
                    fontFamily: { sans: ['Poppins', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f1f5f9; min-height: 100vh; }
        
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .id-card { 
            background: #ffffff; 
            border-radius: 16px; 
            overflow: hidden; 
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            border: 1px solid #e2e8f0; 
            cursor: pointer; 
            position: relative; 
            height: 290px; 
            display: flex; 
            flex-direction: column; 
            width: 100%; 
        }
        .id-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: <?php echo $colorAcento; ?>; z-index: 1; }                
        .id-card:hover { transform: translateY(-4px); box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1); border-color: <?php echo $colorAcento; ?>; }
        
        .card-header { background: #0f172a; padding: 18px 12px; text-align: center; font-weight: 800; color: #ffffff; font-size: 0.75rem; letter-spacing: 1px; text-transform: uppercase; }
        .card-body { padding: 12px; flex: 1; display: flex; gap: 12px; position: relative; z-index: 1; }
        
        .card-photo-section { flex-shrink: 0; position: relative; width: 85px; }
        .card-photo { width: 85px; height: 85px; border-radius: 12px; object-fit: cover; border: 2px solid <?php echo $colorAcento; ?>; background: #f8fafc; }
        .no-photo { width: 85px; height: 85px; background: #f1f5f9; border: 2px dashed #cbd5e1; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 2rem; }
        .photo-badge { position: absolute; bottom: -8px; right: -8px; width: 28px; height: 28px; background: <?php echo $colorAcento; ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 0.8rem; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        
        .card-right-section { flex: 1; display: flex; flex-direction: column; gap: 8px; min-width: 0; }
        .card-name { font-size: 0.9rem; font-weight: 800; color: #0f172a; line-height: 1.2; text-transform: uppercase; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
        .card-id { font-size: 0.75rem; color: #475569; display: flex; align-items: center; gap: 6px; font-weight: 700; background: #f8fafc; padding: 6px 8px; border-radius: 8px; border: 1px solid #e2e8f0; }
        
        .card-documents { flex: 1; display: flex; flex-direction: column; gap: 6px; }
        
        .btn-card-action { position: absolute; top: 8px; background: rgba(255, 255, 255, 0.9); color: #0f172a; border: 1px solid #e2e8f0; width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; cursor: pointer; z-index: 20; transition: all 0.2s; }
        .btn-card-action:hover { background: <?php echo $colorAcento; ?>; color: white; border-color: <?php echo$colorAcento; ?>; transform: scale(1.1); }
        .btn-link { right: 8px; }
        
        .estado-text { font-size: 0.6rem; font-weight: 800; padding: 2px 6px; border-radius: 4px; }
        .text-vigente { background: #d1fae5; color: #059669; }
        .text-proximo { background: #fef3c7; color: #d97706; }
        .text-vencido { background: #fee2e2; color: #dc2626; }
        .text-na { background: #f1f5f9; color: #64748b; }

        .swal2-popup { font-family: 'Poppins', sans-serif !important; border-radius: 1.25rem !important; padding: 2rem !important; box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1) !important; border: 1px solid #e2e8f0; }
        .swal2-title { color: #0f172a !important; font-weight: 700 !important; font-size: 1.25rem !important; }
        .swal2-html-container { margin-top: 1.5rem !important; overflow: hidden !important; }
        .fade-in { animation: fadeInUp 0.4s ease-out backwards; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

        /* Estilos de botones de filtro */
        .filter-btn { display: flex; justify-content: space-between; items-center; width: 100%; padding: 10px 16px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.875rem; font-weight: 700; color: #0f172a; outline: none; cursor: pointer; transition: all 0.2s ease; }
        .filter-btn:hover { background-color: #f1f5f9; border-color: <?php echo $colorAcento; ?>; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .filter-btn span.val { color: <?php echo$colorAcento; ?>; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 150px; text-align: right; }
        
        .selectable-card { transition: all 0.2s ease; border: 1px solid #e2e8f0; cursor: pointer; background: #ffffff; padding: 12px; border-radius: 12px; font-weight: 600; font-size: 0.9rem; color: #334155; text-align: left; display: flex; align-items: center; gap: 10px; }
        .selectable-card:hover { border-color: <?php echo $colorAcento; ?>; background-color: #f8fafc; transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        .selectable-card i { color: <?php echo $colorAcento; ?>; font-size: 1.1rem; }
    </style>
</head>
<body>

    <header class="bg-dark px-6 py-5 shadow-sm border-b-[3px] relative z-20" style="border-color: <?php echo $colorAcento; ?>;">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="bg-slate-800 border border-slate-700 p-3 rounded-xl shadow-inner text-2xl" style="color: <?php echo $colorAcento; ?>;">
                    <?php echo $iconoModulo; ?>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">MÓDULO <?php echo $modulo; ?> - <?php echo$tipo === 'VEHICULO' ? 'VEHÍCULOS' : 'OPERARIOS'; ?></h1>
                    <p class="text-sm font-medium text-slate-400">Control Documental y Estado Operativo</p>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <a href="index.php" class="bg-white text-dark px-6 py-2.5 rounded-xl font-bold hover:bg-slate-100 transition-colors shadow-sm flex items-center gap-2 border border-slate-200">
                    <i class="fas fa-arrow-left"></i> Volver al Panel
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl w-full mx-auto px-4 py-8 flex-1 flex flex-col">
        
        <?php if ($user_rol === 'EMPLEADO' ||$user_rol === 'OPERARIO'): ?>
            <div class="mb-6 bg-blue-50 border border-blue-100 text-blue-700 p-4 rounded-xl font-bold flex items-center gap-3 shadow-sm">
                <div class="bg-blue-100 w-8 h-8 rounded-full flex items-center justify-center shrink-0">
                    <i class="fas fa-shield-alt text-blue-600"></i> 
                </div>
                Vista protegida: Mostrando únicamente tu perfil e información asignada al módulo de <?php echo ucfirst(strtolower($modulo)); ?>.
            </div>
        <?php else: ?>
            <?php if (!$filtro_id): ?>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-sm font-bold text-slate-500 mb-1">
                        <i class="fas fa-filter" style="color: <?php echo $colorAcento; ?>;"></i> Filtros del Módulo
                    </div>
                    <?php if ($user_rol === 'ADMIN'): ?>
                        <span class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-lg text-xs font-bold border border-emerald-100"><i class="fas fa-building mr-1"></i> Mostrando sede actual</span>
                    <?php endif; ?>
                    <button onclick="clearFilters()" class="text-xs font-bold text-red-500 hover:text-red-700 transition-colors"><i class="fas fa-times-circle"></i> Limpiar Filtros</button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 <?php echo $user_rol === 'SUPERADMIN' ? 'lg:grid-cols-5' : 'lg:grid-cols-4'; ?> gap-3">
                    
                    <?php if ($user_rol === 'SUPERADMIN'): ?>
                    <button onclick="openModalFilter('Centro')" class="filter-btn">
                        <div class="flex items-center gap-2"><i class="fas fa-building text-slate-400"></i> Centro</div>
                        <span id="labelCentro" class="val">Todos</span>
                    </button>
                    <?php endif; ?>

                    <button onclick="openModalFilter('Cargo')" class="filter-btn">
                        <div class="flex items-center gap-2"><i class="fas fa-user-tag text-slate-400"></i> Cargo</div>
                        <span id="labelCargo" class="val">Todos</span>
                    </button>

                    <button onclick="openModalFilter('Documento')" class="filter-btn">
                        <div class="flex items-center gap-2"><i class="fas fa-file-alt text-slate-400"></i> Documento</div>
                        <span id="labelDocumento" class="val">Todos</span>
                    </button>

                    <button onclick="openModalFilter('Orden')" class="filter-btn">
                        <div class="flex items-center gap-2"><i class="fas fa-sort text-slate-400"></i> Orden</div>
                        <span id="labelOrden" class="val">Por Defecto</span>
                    </button>
                    
                    <!-- Buscador -->
                    <div class="relative w-full">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" id="searchInput" oninput="filterCards()" placeholder="Buscar..." class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none font-bold text-sm text-slate-700">
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="mb-6">
                <a href="flota.php?modulo=<?php echo $modulo; ?>&tipo=<?php echo$tipo; ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg font-bold text-sm transition-colors">
                    <i class="fas fa-times-circle"></i> Quitar filtro y ver todos
                </a>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Grid de Tarjetas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="cardsGrid">
            <div class="col-span-full text-center py-12">
                <i class="fas fa-circle-notch fa-spin text-4xl mb-3" style="color: <?php echo $colorAcento; ?>;"></i>
                <p class="font-bold text-slate-500">Cargando base de datos...</p>
            </div>
        </div>

        <div id="paginationControls" class="mt-8 flex justify-center items-center gap-4 hidden">
            <button onclick="changePage(-1)" id="btnPrev" class="px-4 py-2 bg-white border border-slate-200 rounded-lg font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-50 transition-colors shadow-sm"><i class="fas fa-chevron-left mr-2"></i> Anterior</button>
            <span id="pageInfo" class="font-bold text-dark px-4 py-2 bg-slate-200 rounded-lg text-sm"></span>
            <button onclick="changePage(1)" id="btnNext" class="px-4 py-2 bg-white border border-slate-200 rounded-lg font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-50 transition-colors shadow-sm">Siguiente <i class="fas fa-chevron-right ml-2"></i></button>
        </div>
    </main>

    <script>
        const currentModulo = '<?php echo $modulo; ?>';
        const tipo = '<?php echo $tipo; ?>';
        const filtroId = '<?php echo $filtro_id; ?>'; 
        const userRole = '<?php echo $user_rol; ?>';
        const userCedula = '<?php echo $user_cedula; ?>';
        const userId = '<?php echo $user_id; ?>'; 
        const API_URL = 'controllers/api_admin.php';

        let allData = [];
        let filteredData = [];
        let currentPage = 1;
        const itemsPerPage = 9;

        // Estado de los filtros
        let activeFilters = { centro: '', cargo: '', documento: '', orden: '' };
        let catalogos = { centros: [], cargos: [], documentos: [] };

        function copiarEnlaceTarjeta(event, id) {
            event.stopPropagation();
            const url = `${window.location.origin}${window.location.pathname}?modulo=${currentModulo}&tipo=${tipo}/${id}`;
            navigator.clipboard.writeText(url).then(() => {
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Enlace copiado', showConfirmButton: false, timer: 1500 });
            });
        }

        async function loadFlota() {
            if (userRole === 'SUPERADMIN') {
                fetch(`${API_URL}?action=getCentros`).then(res => res.json()).then(data => { if(data.success) catalogos.centros = data.data; });
            }

            try {
                const res = await fetch(`${API_URL}?action=getRegistros`);
                const data = await res.json();
                
                if (data.success) {
                    let datosAVisualizar = [];

                    if (userRole === 'EMPLEADO' || userRole === 'OPERARIO') {
                        datosAVisualizar = data.data.filter(item => {
                            const matchUsuario = (String(item.usuario_id) === String(userId) || (userCedula !== '' && String(item.cedula).trim() === String(userCedula).trim()));
                            const matchModulo = (item.modulo ? String(item.modulo).trim().toUpperCase() : 'TRANSPORTE') === currentModulo;
                            return matchUsuario && matchModulo;
                        });
                    } else {
                        datosAVisualizar = data.data.filter(item => {
                            const itemModulo = item.modulo ? String(item.modulo).trim().toUpperCase() : 'TRANSPORTE';
                            const itemTipo = item.tipo_registro ? String(item.tipo_registro).trim().toUpperCase() : '';
                            const matchModulo = (itemModulo === currentModulo);
                            const matchTipo = (tipo === 'VEHICULO') ? (itemTipo === 'VEHICULO') : (['CONDUCTOR', 'OPERARIO'].includes(itemTipo));
                            return matchModulo && matchTipo;
                        });
                        
                        if (filtroId !== '') {
                            datosAVisualizar = datosAVisualizar.filter(item => {
                                const idAComparar = (item.tipo_registro === 'OPERARIO' || item.tipo_registro === 'CONDUCTOR') ? item.cedula : item.placa;
                                return String(idAComparar).trim() === String(filtroId).trim();
                            });
                        }
                    }

                    if (datosAVisualizar.length === 0 && (userRole === 'EMPLEADO' || userRole === 'OPERARIO')) {
                        document.getElementById('cardsGrid').innerHTML = `<div class="col-span-full text-center py-12"><div class="bg-blue-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 border border-blue-100"><i class="fas fa-user-shield text-3xl text-blue-400"></i></div><h4 class="text-lg font-bold text-slate-700">Perfil no vinculado</h4><p class="text-slate-500 max-w-md mx-auto mt-2">Tu usuario existe, pero no estás asignado al módulo de ${currentModulo}.</p></div>`;
                        return;
                    }

                    document.getElementById('cardsGrid').innerHTML = `<div class="col-span-full text-center py-12"><i class="fas fa-folder-open fa-spin text-4xl mb-3" style="color: <?php echo $colorAcento; ?>;"></i><p class="font-bold text-slate-500">Analizando documentos... (esto puede tardar unos segundos)</p></div>`;

                    for (let item of datosAVisualizar) {
                        try {
                            const docRes = await fetch(`${API_URL}?action=getDocumentos&id=${item.id}`);
                            const docData = await docRes.json();
                            item.documentos = (docData.success && docData.data) ? docData.data : [];
                            
                            item.min_dias = Infinity;
                            item.max_dias = -Infinity;
                            
                            item.documentos.forEach(doc => {
                                if (doc.fecha_vencimiento) {
                                    const diff = Math.ceil((new Date(doc.fecha_vencimiento).setHours(0,0,0,0) - new Date().setHours(0,0,0,0)) / (1000*60*60*24));
                                    if(diff < item.min_dias) item.min_dias = diff;
                                    if(diff > item.max_dias) item.max_dias = diff;
                                }
                            });
                        } catch (e) {
                            item.documentos = [];
                            item.min_dias = Infinity;
                            item.max_dias = -Infinity;
                        }
                        
                        item.moduloNormalizado = item.modulo ? String(item.modulo).trim().toUpperCase() : 'TRANSPORTE';
                        item.cargoNormalizado = item.cargo ? String(item.cargo).trim().toUpperCase() : '';
                    }
                    
                    allData = datosAVisualizar; 
                    extractCatalogos();
                    filterCards();
                } else {
                    document.getElementById('cardsGrid').innerHTML = `<div class="col-span-full text-center py-12"><div class="bg-red-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3"><i class="fas fa-exclamation-triangle text-2xl text-red-500"></i></div><h4 class="text-lg font-bold text-slate-700">Error</h4><p class="text-sm text-slate-500">${data.message}</p></div>`;
                }
            } catch (err) {
                document.getElementById('cardsGrid').innerHTML = `<div class="col-span-full text-center py-12"><div class="bg-red-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3"><i class="fas fa-wifi text-2xl text-red-500"></i></div><h4 class="text-lg font-bold text-slate-700">Error de conexión</h4></div>`;
            }
        }

        function extractCatalogos() {
            const cargoSet = new Set();
            const docSet = new Set();
            allData.forEach(item => {
                if (item.cargoNormalizado) cargoSet.add(item.cargoNormalizado);
                item.documentos.forEach(doc => docSet.add(doc.tipo_documento));
            });
            catalogos.cargos = [...cargoSet].sort();
            catalogos.documentos = [...docSet].sort();
        }

        // ================= WIZARD DE FILTROS =================
        function openModalFilter(filterType, autoClose = false) {
            let htmlContent = '';
            let title = '';
            
            if (filterType === 'Centro') {
                title = 'Seleccionar Centro';
                htmlContent += `<div class="grid grid-cols-1 gap-2 mt-4 max-h-[300px] overflow-y-auto custom-scrollbar p-1">`;
                htmlContent += `<div onclick="applyFilter('centro', '', 'Todos', 'Cargo')" class="selectable-card"><i class="fas fa-globe"></i> Todos los Centros</div>`;
                catalogos.centros.forEach(c => {
                    htmlContent += `<div onclick="applyFilter('centro', '${c.id}', '${c.nombre}', 'Cargo')" class="selectable-card"><i class="fas fa-building"></i> ${c.nombre}</div>`;
                });
                htmlContent += `</div>`;
            } 
            else if (filterType === 'Cargo') {
                title = 'Seleccionar Cargo';
                htmlContent += `<div class="grid grid-cols-1 gap-2 mt-4 max-h-[300px] overflow-y-auto custom-scrollbar p-1">`;
                htmlContent += `<div onclick="applyFilter('cargo', '', 'Todos', 'Documento')" class="selectable-card"><i class="fas fa-users"></i> Todos los Cargos</div>`;
                catalogos.cargos.forEach(c => {
                    htmlContent += `<div onclick="applyFilter('cargo', '${c}', '${c}', 'Documento')" class="selectable-card"><i class="fas fa-user-tag"></i> ${c}</div>`;
                });
                htmlContent += `</div>`;
            }
            else if (filterType === 'Documento') {
                title = 'Seleccionar Documento';
                htmlContent += `<div class="grid grid-cols-1 gap-2 mt-4 max-h-[300px] overflow-y-auto custom-scrollbar p-1">`;
                htmlContent += `<div onclick="applyFilter('documento', '', 'Todos', 'Orden')" class="selectable-card"><i class="fas fa-folder"></i> Todos los Documentos</div>`;
                catalogos.documentos.forEach(d => {
                    htmlContent += `<div onclick="applyFilter('documento', '${d}', '${d}', 'Orden')" class="selectable-card"><i class="fas fa-file-alt"></i> ${d}</div>`;
                });
                htmlContent += `</div>`;
            }
            else if (filterType === 'Orden') {
                title = 'Seleccionar Orden';
                htmlContent += `<div class="grid grid-cols-1 gap-2 mt-4 max-h-[300px] overflow-y-auto custom-scrollbar p-1">`;
                htmlContent += `<div onclick="applyFilter('orden', '', 'Por Defecto', null)" class="selectable-card"><i class="fas fa-sort"></i> Orden por defecto</div>`;
                htmlContent += `<div onclick="applyFilter('orden', 'asc', 'Más próximos a vencer', null)" class="selectable-card"><i class="fas fa-arrow-down-short-wide text-orange-500"></i> Más próximos a vencer</div>`;
                htmlContent += `<div onclick="applyFilter('orden', 'desc', 'Más lejos a vencer', null)" class="selectable-card"><i class="fas fa-arrow-up-wide-short text-green-500"></i> Más lejos a vencer</div>`;
                htmlContent += `</div>`;
            }

            Swal.fire({
                title: title,
                html: htmlContent,
                showConfirmButton: false, showCloseButton: true, width: '450px'
            });
        }

        window.applyFilter = (key, value, label, nextFilter) => {
            activeFilters[key] = value;
            document.getElementById('label' + key.charAt(0).toUpperCase() + key.slice(1)).innerText = label;
            filterCards();
            
            if (nextFilter) {
                setTimeout(() => { openModalFilter(nextFilter); }, 300);
            } else {
                Swal.close();
            }
        };

        window.clearFilters = () => {
            activeFilters = { centro: '', cargo: '', documento: '', orden: '' };
            if(document.getElementById('labelCentro')) document.getElementById('labelCentro').innerText = 'Todos';
            document.getElementById('labelCargo').innerText = 'Todos';
            document.getElementById('labelDocumento').innerText = 'Todos';
            document.getElementById('labelOrden').innerText = 'Por Defecto';
            document.getElementById('searchInput').value = '';
            filterCards();
        }

        function filterCards() {
            if (userRole === 'EMPLEADO' || userRole === 'OPERARIO') {
                filteredData = [...allData]; 
            } else {
                const searchEl = document.getElementById('searchInput');
                const term = searchEl ? searchEl.value.toLowerCase().trim() : '';
                
                filteredData = allData.filter(item => {
                    const matchTerm = term === '' || 
                                      (item.nombre && String(item.nombre).toLowerCase().includes(term)) || 
                                      (item.placa && String(item.placa).toLowerCase().includes(term)) || 
                                      (item.cedula && String(item.cedula).toLowerCase().includes(term));
                    
                    const matchCargo = !activeFilters.cargo || item.cargoNormalizado === activeFilters.cargo;
                    const matchCentro = !activeFilters.centro || item.centro_id == activeFilters.centro;
                    const matchDoc = !activeFilters.documento || item.documentos.some(d => d.tipo_documento === activeFilters.documento);
                    
                    return matchTerm && matchCargo && matchDoc && matchCentro;
                });
                
                if (activeFilters.orden) {
                    filteredData.sort((a, b) => {
                        let valA = Infinity;
                        let valB = Infinity;
                        
                        if (activeFilters.documento) {
                            const dA = a.documentos.find(d => d.tipo_documento === activeFilters.documento);
                            const dB = b.documentos.find(d => d.tipo_documento === activeFilters.documento);
                            if (dA && dA.fecha_vencimiento) valA = getDays(dA.fecha_vencimiento);
                            if (dB && dB.fecha_vencimiento) valB = getDays(dB.fecha_vencimiento);
                        } else {
                            valA = activeFilters.orden === 'asc' ? a.min_dias : a.max_dias;
                            valB = activeFilters.orden === 'asc' ? b.min_dias : b.max_dias;
                        }
                        
                        return activeFilters.orden === 'asc' ? valA - valB : valB - valA;
                    });
                }
            }

            currentPage = 1;
            renderGrid();
        }

        function getDays(dateStr) {
            if (!dateStr) return Infinity;
            return Math.ceil((new Date(dateStr).setHours(0,0,0,0) - new Date().setHours(0,0,0,0)) / (1000*60*60*24));
        }

        function changePage(dir) {
            currentPage += dir;
            renderGrid();
        }

        function renderGrid() {
            const grid = document.getElementById('cardsGrid');
            const pag = document.getElementById('paginationControls');
            
            if (filteredData.length === 0) {
                grid.innerHTML = `<div class="col-span-full text-center py-12 bg-white rounded-2xl border border-slate-200 shadow-sm"><div class="bg-slate-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100"><i class="fas fa-search text-3xl text-slate-400"></i></div><h4 class="text-lg font-bold text-slate-700">No se encontraron resultados</h4></div>`;
                if(pag) pag.classList.add('hidden');
                return;
            }

            const totalPages = Math.ceil(filteredData.length / itemsPerPage);
            const start = (currentPage - 1) * itemsPerPage;
            const paginatedData = filteredData.slice(start, start + itemsPerPage);

            grid.innerHTML = paginatedData.map((item, index) => createCard(item, index)).join('');
            
            loadDocsForCardsLocally(paginatedData);

            if (totalPages > 1 && pag) {
                pag.classList.remove('hidden');
                document.getElementById('pageInfo').innerText = `Página ${currentPage} de ${totalPages}`;
                document.getElementById('btnPrev').disabled = currentPage === 1;
                document.getElementById('btnNext').disabled = currentPage === totalPages;
            } else if(pag) {
                pag.classList.add('hidden');
            }
        }

        function createCard(item, index) {
            const isOp = item.tipo_registro === 'OPERARIO' || item.tipo_registro === 'CONDUCTOR';
            const fotoSrc = item.foto ? `uploads/${item.foto}` : 'placeholder';
            const displayName = isOp ? item.nombre : item.placa;
            const displayId = isOp ? item.cedula : item.placa;
            const idLabel = isOp ? 'Cédula' : 'Placa';
            const idIcon = isOp ? 'id-card' : 'clipboard';
            const badgeIcon = isOp ? '<i class="fas fa-user-tie"></i>' : '<?php echo $iconoTipo; ?>';
            
            return `
                <div class="id-card fade-in" style="animation-delay: ${index * 0.05}s;" onclick="viewAllDocumentos(${item.id}, '${displayName}')">
                    <button class="btn-card-action btn-link" onclick="copiarEnlaceTarjeta(event, '${displayId}')" title="Copiar enlace"><i class="fas fa-link"></i></button>
                    
                    <div class="card-header flex justify-between items-center" style="background: #0f172a;">
                        <span><i class="fas ${isOp ? 'fa-user' : 'fa-truck'} mr-1 opacity-50"></i> ${item.cargo || item.tipo_registro}</span>
                        <span class="bg-white/20 px-2 py-0.5 rounded text-[10px]">${item.moduloNormalizado || 'N/A'}</span>
                    </div>
                    <div class="card-body">
                        <div class="card-photo-section">
                            ${fotoSrc === 'placeholder' ? 
                                `<div class="no-photo"><i class="fas fa-${isOp ? 'user' : 'truck'}"></i></div>` :
                                `<img src="${fotoSrc}" alt="Foto" class="card-photo" onerror="this.style.display='none'; this.parentElement.innerHTML='<div class=\\'no-photo\\'><i class=\\'fas fa-${isOp ? 'user' : 'truck'}\\'></i></div>';">`
                            }
                            <div class="photo-badge" style="background: <?php echo $colorAcento; ?>;">${badgeIcon}</div>
                        </div>
                        <div class="card-right-section">
                            <div class="card-info-top">
                                <div class="card-name" title="${displayName}">${displayName}</div>
                                <div class="card-id"><i class="fas fa-${idIcon} text-slate-400"></i> <span><strong>${idLabel}:</strong> ${displayId}</span></div>
                            </div>
                            
                            <div class="card-documents mt-3 flex flex-col">
                                <div class="text-[10px] font-bold text-slate-400 mb-2 flex items-center justify-between">
                                    <span><i class="fas fa-folder-open mr-1"></i> Estado Documentos</span>
                                </div>
                                <div id="card-docs-${item.id}" class="flex flex-wrap gap-1.5 overflow-y-auto custom-scrollbar max-h-[110px] pr-1"></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function loadDocsForCardsLocally(items) {
            items.forEach(item => {
                const container = document.getElementById(`card-docs-${item.id}`);
                if (!container) return;
                
                if (item.documentos && item.documentos.length > 0) {
                    let badgesHtml = '';
                    item.documentos.forEach(doc => {
                        const estado = getEstadoDocumento(doc.fecha_vencimiento);
                        let bg = 'bg-slate-200 text-slate-600'; 
                        
                        if (estado.tipo === 'vencido') bg = 'bg-red-500 text-white';
                        else if (estado.tipo === 'proximo') bg = 'bg-yellow-400 text-slate-800';
                        else if (estado.tipo === 'vigente') bg = 'bg-green-500 text-white';

                        badgesHtml += `<div class="${bg} px-2 py-1 rounded text-[9px] font-bold whitespace-normal leading-tight shadow-sm border border-black/5" title="${doc.tipo_documento} - ${estado.texto}">${doc.tipo_documento}</div>`;
                    });
                    container.innerHTML = badgesHtml;
                } else {
                    container.innerHTML = `<span class="text-[10px] font-bold text-slate-400 italic">Sin documentos cargados</span>`;
                }
            });
        }
        
        function getEstadoDocumento(fechaVencimiento) {
            if (!fechaVencimiento) return { tipo: 'na', texto: 'N/A', color: '#94a3b8' };
            const diffDays = getDays(fechaVencimiento);
            
            if (diffDays < 0) return { tipo: 'vencido', texto: 'VENCIDO', color: '#ef4444' };
            if (diffDays <= 30) return { tipo: 'proximo', texto: `${diffDays}D`, color: '#f59e0b' };
            return { tipo: 'vigente', texto: 'AL DÍA', color: '#10b981' };
        }

        function viewAllDocumentos(id, nombreEntidad) {
            const item = allData.find(i => i.id == id);
            
            let html = '<div style="max-height: 500px; overflow-y: auto;" class="custom-scrollbar pr-2 mt-4 space-y-3">';
            
            if (!item || !item.documentos || item.documentos.length === 0) {
                html += '<div class="text-center py-10"><div class="bg-slate-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3"><i class="fas fa-folder-open text-2xl text-slate-400"></i></div><p class="text-slate-500 font-bold">No hay documentos registrados</p></div>';
            } else {
                const docsOrdenados = [...item.documentos].sort((a, b) => {
                    const estA = getEstadoDocumento(a.fecha_vencimiento);
                    const estB = getEstadoDocumento(b.fecha_vencimiento);
                    const prio = {'vencido': 3, 'proximo': 2, 'vigente': 1, 'na': 0};
                    return prio[estB.tipo] - prio[estA.tipo];
                });

                docsOrdenados.forEach(doc => {
                    const estado = getEstadoDocumento(doc.fecha_vencimiento);
                    html += `
                        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm text-left flex flex-col gap-3 relative overflow-hidden">
                            <div class="absolute left-0 top-0 bottom-0 w-1.5 ${estado.tipo === 'vencido' ? 'bg-red-500' : (estado.tipo === 'proximo' ? 'bg-orange-500' : (estado.tipo === 'vigente' ? 'bg-emerald-500' : 'bg-slate-300'))}"></div>
                            
                            <div class="flex justify-between items-start pl-2">
                                <div class="flex-1">
                                    <h4 class="font-bold text-dark text-sm leading-tight pr-2">${doc.tipo_documento}</h4>
                                    <div class="flex gap-4 mt-2">
                                        <div class="text-[10px] text-slate-500"><i class="fas fa-calendar-plus text-slate-400"></i> Exp: ${doc.fecha_expedicion || '<span class="italic">N/A</span>'}</div>
                                        <div class="text-[10px] text-slate-500"><i class="fas fa-calendar-times text-slate-400"></i> Venc: <span class="font-bold text-slate-700">${doc.fecha_vencimiento || '<span class="italic">N/A</span>'}</span></div>
                                    </div>
                                </div>
                                <div class="estado-text text-${estado.tipo} text-center shrink-0">
                                    ${estado.texto}
                                </div>
                            </div>
                            
                            <div class="pl-2 pt-2 border-t border-slate-100 flex justify-end">
                                <a href="${doc.archivo_tipo === 'PDF' ? 'uploads/documentos/' + doc.archivo_valor : doc.archivo_valor}" target="_blank" onclick="event.stopPropagation()" class="bg-dark hover:bg-dark-hover text-white px-4 py-1.5 rounded-lg text-xs font-bold transition-colors flex items-center gap-2">
                                    <i class="fas fa-${doc.archivo_tipo === 'PDF' ? 'file-pdf' : 'external-link-alt'}"></i> Ver Archivo
                                </a>
                            </div>
                        </div>
                    `;
                });
            }
            html += '</div>';

            Swal.fire({
                title: `<div class="text-left"><span class="text-sm font-semibold text-slate-400 block mb-1">Documentos de</span><span class="text-xl text-dark"><i class="fas fa-folder-open text-gold mr-2"></i>${nombreEntidad}</span></div>`,
                html: html,
                width: '600px',
                showConfirmButton: true,
                confirmButtonText: '<i class="fas fa-times mr-2"></i> Cerrar',
                buttonsStyling: false,
                customClass: { confirmButton: 'w-full mt-4 px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition-colors' }
            });
        }

        loadFlota();
    </script>
</body>
</html>