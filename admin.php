<?php
session_start();
// Validación estricta de seguridad: Solo ADMIN y SUPERADMIN pueden estar aquí
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['rol'], ['ADMIN', 'SUPERADMIN'])) {
    header("Location: index.php");
    exit();
}

$rol = $_SESSION['rol'];
$centro_id = $_SESSION['centro_id'];
$nombre_usuario = $_SESSION['nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - FleeDriver</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gold: '#F5A623', 'gold-hover': '#E0961D',
                        orange: '#E67E22', 
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
        body { background-color: #f8fafc; }
        .swal2-popup { font-family: 'Poppins', sans-serif !important; border-radius: 1.25rem !important; padding: 2rem !important; box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1) !important; border: 1px solid #e2e8f0; }
        .swal2-title { color: #0f172a !important; font-weight: 700 !important; font-size: 1.25rem !important; }
        .swal2-html-container { margin-top: 1.5rem !important; overflow: hidden !important; }
        .tab-content { display: none; animation: fadeIn 0.2s ease-in-out forwards; }
        .tab-content.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
        
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .selectable-card { transition: all 0.2s ease; border: 1px solid #e2e8f0; cursor: pointer; background: #ffffff; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05); }
        .selectable-card:hover { border-color: #cbd5e1; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        .selectable-card.selected { border-color: #0f172a; background-color: #f8fafc; color: #0f172a; box-shadow: 0 0 0 1px #0f172a; }
        .selectable-card.selected-gold { border-color: #F5A623; background-color: #fffbeb; color: #b45309; box-shadow: 0 0 0 1px #F5A623; }
        
        table th { letter-spacing: 0.05em; }

        /* Estilo para el botón de subida de foto de perfil en Wizard */
        .photo-upload-btn { position: relative; width: 80px; height: 80px; border-radius: 50%; background-color: #f1f5f9; border: 2px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; cursor: pointer; overflow: hidden; margin: 0 auto 1rem; transition: all 0.2s ease; }
        .photo-upload-btn:hover { border-color: #F5A623; background-color: #fffbeb; }
        .photo-upload-btn img { width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; display: none; }
    </style>
</head>
<body class="text-slate-700 min-h-screen flex flex-col">

    <!-- Header Profesional -->
    <header class="bg-dark px-6 py-5 shadow-sm border-b-[3px] border-gold relative z-20">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="bg-slate-800 border border-slate-700 p-3 rounded-xl text-gold shadow-inner">
                    <i class="fas fa-layer-group text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Panel de Control</h1>
                    <p class="text-sm font-medium text-slate-400 flex items-center gap-2">
                        <i class="fas <?php echo $rol === 'SUPERADMIN' ? 'fa-globe' : 'fa-building'; ?>"></i>
                        <?php echo $rol === 'SUPERADMIN' ? 'Gestión Global (Super Admin)' : 'Gestión de Centro'; ?>
                    </p>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="bg-slate-800 px-5 py-2.5 rounded-xl font-medium text-white text-sm border border-slate-700 shadow-sm flex items-center gap-3">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($nombre_usuario); ?>&background=F5A623&color=0f172a&font-weight=bold" class="w-7 h-7 rounded-full border border-slate-600">
                    <?php echo htmlspecialchars($nombre_usuario); ?>
                </div>
                <a href="index.php" class="bg-white text-dark px-6 py-2.5 rounded-xl font-bold hover:bg-slate-100 transition-colors shadow-sm flex items-center gap-2 border border-slate-200">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl w-full mx-auto px-4 py-8 flex-1">
        
        <!-- Navegación de Pestañas Sobria -->
        <div class="flex flex-wrap gap-2 mb-8 bg-white p-1.5 rounded-xl shadow-sm border border-slate-200">
            <button onclick="switchTab('tab-flota')" id="btn-tab-flota" class="tab-btn flex-1 py-3 px-6 rounded-lg font-semibold text-sm transition-colors bg-dark text-white flex items-center justify-center gap-2">
                <i class="fas fa-truck-fast"></i> Flota y Operarios
            </button>
            <button onclick="switchTab('tab-usuarios')" id="btn-tab-usuarios" class="tab-btn flex-1 py-3 px-6 rounded-lg font-semibold text-sm text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-colors flex items-center justify-center gap-2">
                <i class="fas fa-id-badge"></i> Usuarios
            </button>
            
            <?php if ($rol === 'SUPERADMIN'): ?>
            <button onclick="switchTab('tab-centros')" id="btn-tab-centros" class="tab-btn flex-1 py-3 px-6 rounded-lg font-semibold text-sm text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-colors flex items-center justify-center gap-2">
                <i class="fas fa-building-flag"></i> Centros
            </button>
            <button onclick="switchTab('tab-config')" id="btn-tab-config" class="tab-btn flex-1 py-3 px-6 rounded-lg font-semibold text-sm text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-colors flex items-center justify-center gap-2">
                <i class="fas fa-sliders"></i> Catálogos
            </button>
            <?php endif; ?>
        </div>

        <!-- ================= PESTAÑA: FLOTA Y OPERARIOS ================= -->
        <div id="tab-flota" class="tab-content active">
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <button onclick="openWizard()" class="bg-gold text-dark px-6 py-3 rounded-xl font-bold shadow-sm hover:bg-gold-hover hover:shadow transition-all flex items-center gap-2">
                    <i class="fas fa-user-plus text-lg"></i> Nuevo Registro
                </button>
                <div class="relative w-full md:w-96">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" id="searchFlota" oninput="filterData('registros')" placeholder="Buscar por nombre, placa..." class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl shadow-sm focus:border-dark focus:ring-1 focus:ring-dark outline-none transition-all font-medium text-slate-700">
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-slate-200 flex flex-col">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse min-w-[900px]">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-500">
                                <th class="p-4 font-bold text-[11px] uppercase tracking-wider text-center">Foto</th>
                                <th class="p-4 font-bold text-[11px] uppercase tracking-wider">Centro</th>
                                <th class="p-4 font-bold text-[11px] uppercase tracking-wider">Clasificación</th>
                                <th class="p-4 font-bold text-[11px] uppercase tracking-wider">Identificación</th>
                                <th class="p-4 font-bold text-[11px] uppercase tracking-wider text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="registrosBody" class="divide-y divide-slate-100"></tbody>
                    </table>
                </div>
                <div id="pagination-registros" class="bg-slate-50 p-4 border-t border-slate-200 flex justify-between items-center text-sm text-slate-600"></div>
            </div>
        </div>

        <!-- ================= PESTAÑA: USUARIOS ================= -->
        <div id="tab-usuarios" class="tab-content">
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <div class="flex items-center gap-3">
                    <div class="bg-slate-100 text-slate-700 p-2.5 rounded-lg border border-slate-200"><i class="fas fa-users-gear text-lg"></i></div>
                    <h2 class="text-xl font-bold text-dark">Gestión de Accesos</h2>
                </div>
                <div class="flex gap-4 w-full md:w-auto">
                    <div class="relative flex-1 md:w-64">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" id="searchUsuarios" oninput="filterData('usuarios')" placeholder="Buscar usuario..." class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl shadow-sm focus:border-dark focus:ring-1 focus:ring-dark outline-none transition-all font-medium text-slate-700">
                    </div>
                    <button onclick="openUsuarioModal()" class="bg-dark text-white px-6 py-3 rounded-xl font-bold hover:bg-dark-hover shadow-sm flex items-center gap-2 transition-colors">
                        <i class="fas fa-user-plus"></i> Crear
                    </button>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-slate-200 flex flex-col">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse min-w-[700px]">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-500">
                                <th class="p-4 font-bold text-[11px] uppercase tracking-wider">Usuario</th>
                                <th class="p-4 font-bold text-[11px] uppercase tracking-wider">Cédula / Login</th>
                                <th class="p-4 font-bold text-[11px] uppercase tracking-wider">Rol</th>
                                <th class="p-4 font-bold text-[11px] uppercase tracking-wider">Centro</th>
                                <th class="p-4 font-bold text-[11px] uppercase tracking-wider text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="usuariosBody" class="divide-y divide-slate-100"></tbody>
                    </table>
                </div>
                <div id="pagination-usuarios" class="bg-slate-50 p-4 border-t border-slate-200 flex justify-between items-center text-sm text-slate-600"></div>
            </div>
        </div>

        <!-- ================= PESTAÑA: CENTROS (SUPERADMIN) ================= -->
        <?php if ($rol === 'SUPERADMIN'): ?>
        <div id="tab-centros" class="tab-content">
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <div class="flex items-center gap-3">
                    <div class="bg-slate-100 text-slate-700 p-2.5 rounded-lg border border-slate-200"><i class="fas fa-building-shield text-lg"></i></div>
                    <h2 class="text-xl font-bold text-dark">Sucursales</h2>
                </div>
                <div class="flex gap-4 w-full md:w-auto">
                    <div class="relative flex-1 md:w-64">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" id="searchCentros" oninput="filterData('centros')" placeholder="Buscar centro..." class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl shadow-sm focus:border-dark focus:ring-1 focus:ring-dark outline-none transition-all font-medium text-slate-700">
                    </div>
                    <button onclick="crearCentro()" class="bg-dark text-white px-6 py-3 rounded-xl font-bold shadow-sm hover:bg-dark-hover flex items-center gap-2 transition-colors">
                        <i class="fas fa-plus"></i> Añadir Centro
                    </button>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-slate-200 flex flex-col">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse min-w-[600px]">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-500">
                                <th class="p-4 font-bold text-[11px] uppercase tracking-wider w-16">ID</th>
                                <th class="p-4 font-bold text-[11px] uppercase tracking-wider">Nombre del Centro</th>
                                <th class="p-4 font-bold text-[11px] uppercase tracking-wider">Dirección Física</th>
                                <th class="p-4 font-bold text-[11px] uppercase tracking-wider text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="centrosBody" class="divide-y divide-slate-100"></tbody>
                    </table>
                </div>
                <div id="pagination-centros" class="bg-slate-50 p-4 border-t border-slate-200 flex justify-between items-center text-sm text-slate-600"></div>
            </div>
        </div>
        
        <!-- ================= PESTAÑA: CONFIG (SUPERADMIN) ================= -->
        <div id="tab-config" class="tab-content">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Cargos -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 flex flex-col overflow-hidden">
                    <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                        <h3 class="text-base font-bold text-dark flex items-center gap-2">
                            <div class="bg-slate-200 text-slate-700 p-2 rounded border border-slate-300"><i class="fas fa-user-tag text-sm"></i></div>
                            Tipos de Cargos
                        </h3>
                        <button onclick="openCargoModal()" class="bg-dark text-white px-3 py-1.5 rounded-lg text-sm font-semibold hover:bg-dark-hover transition-colors" title="Agregar Cargo">
                            <i class="fas fa-plus mr-1"></i> Añadir
                        </button>
                    </div>
                    <div class="max-h-[350px] overflow-y-auto custom-scrollbar flex-1 relative">
                        <table class="w-full text-left border-collapse">
                            <thead class="sticky top-0 bg-white shadow-sm z-10">
                                <tr class="border-b border-slate-200 text-slate-500 text-[10px] uppercase bg-slate-50">
                                    <th class="p-4 font-bold">Módulo</th>
                                    <th class="p-4 font-bold">Cargo</th>
                                    <th class="p-4 font-bold text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="cargosBody" class="divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Documentos -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 flex flex-col overflow-hidden">
                    <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                        <h3 class="text-base font-bold text-dark flex items-center gap-2">
                            <div class="bg-slate-200 text-slate-700 p-2 rounded border border-slate-300"><i class="fas fa-file-signature text-sm"></i></div>
                            Catálogo de Documentos
                        </h3>
                        <button onclick="openDocumentoModal()" class="bg-dark text-white px-3 py-1.5 rounded-lg text-sm font-semibold hover:bg-dark-hover transition-colors" title="Añadir Documento">
                            <i class="fas fa-plus mr-1"></i> Añadir
                        </button>
                    </div>
                    <div class="max-h-[350px] overflow-y-auto custom-scrollbar flex-1 relative">
                        <table class="w-full text-left border-collapse">
                            <thead class="sticky top-0 bg-white shadow-sm z-10">
                                <tr class="border-b border-slate-200 text-slate-500 text-[10px] uppercase bg-slate-50">
                                    <th class="p-4 font-bold">Documento</th>
                                    <th class="p-4 font-bold">Aplica A</th>
                                    <th class="p-4 font-bold text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="documentosBody" class="divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
        <?php endif; ?>

    </main>

    <script>
        const userRol = '<?php echo $rol; ?>';
        const API_URL = 'controllers/api_admin.php';

        // ================= ESTADO DE DATOS EN MEMORIA =================
        const state = {
            registros: { data: [], filtered: [], page: 1, limit: 10, render: renderRowRegistro },
            usuarios:  { data: [], filtered: [], page: 1, limit: 10, render: renderRowUsuario },
            centros:   { data: [], filtered: [], page: 1, limit: 8,  render: renderRowCentro },
            cargos:    { data: [] },
            documentos:{ data: [] }
        };

        const getEmptyState = (icon, title, desc, colspan) => `<tr><td colspan="${colspan}"><div class="flex flex-col items-center justify-center py-10 px-4 text-center"><div class="bg-slate-50 w-16 h-16 rounded-full flex items-center justify-center mb-3 border border-slate-200 shadow-sm"><i class="fas ${icon} text-2xl text-slate-400"></i></div><h4 class="text-base font-bold text-slate-700 mb-1">${title}</h4><p class="text-sm text-slate-500 max-w-xs mx-auto">${desc}</p></div></td></tr>`;

        document.addEventListener('DOMContentLoaded', () => {
            loadRegistros(); loadUsuarios(); loadCargos(); loadTiposDocumento();
            if (userRol === 'SUPERADMIN') loadCentros();
        });

        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('bg-dark', 'text-white');
                btn.classList.add('text-slate-500', 'bg-transparent');
            });
            document.getElementById(tabId).classList.add('active');
            const activeBtn = document.getElementById('btn-' + tabId);
            activeBtn.classList.remove('text-slate-500', 'bg-transparent');
            activeBtn.classList.add('bg-dark', 'text-white');
        }

        // ================= LÓGICA DE PAGINACIÓN =================
        function updateTable(key) {
            const tbody = document.getElementById(key + 'Body');
            const pagContainer = document.getElementById('pagination-' + key);
            const dState = state[key];
            
            const totalItems = dState.filtered.length;
            const totalPages = Math.ceil(totalItems / dState.limit);
            if (dState.page > totalPages && totalPages > 0) dState.page = totalPages;

            const start = (dState.page - 1) * dState.limit;
            const paginatedItems = dState.filtered.slice(start, start + dState.limit);

            if (totalItems > 0) {
                tbody.innerHTML = paginatedItems.map(item => dState.render(item)).join('');
                pagContainer.innerHTML = `
                    <span>Mostrando ${start + 1} - ${Math.min(start + dState.limit, totalItems)} de ${totalItems} registros</span>
                    <div class="flex items-center gap-2">
                        <button onclick="changePage('${key}', -1)" class="px-3 py-1.5 bg-white border border-slate-200 rounded hover:bg-slate-50 disabled:opacity-50 transition-colors" ${dState.page === 1 ? 'disabled' : ''}><i class="fas fa-chevron-left text-xs"></i></button>
                        <span class="font-semibold text-dark px-2 text-xs">Página ${dState.page} de ${totalPages}</span>
                        <button onclick="changePage('${key}', 1)" class="px-3 py-1.5 bg-white border border-slate-200 rounded hover:bg-slate-50 disabled:opacity-50 transition-colors" ${dState.page === totalPages ? 'disabled' : ''}><i class="fas fa-chevron-right text-xs"></i></button>
                    </div>`;
                pagContainer.style.display = 'flex';
            } else {
                tbody.innerHTML = getEmptyState('fa-inbox', 'Sin resultados', 'No se encontraron datos que coincidan.', 6);
                pagContainer.style.display = 'none';
            }
        }
        function changePage(key, dir) { state[key].page += dir; updateTable(key); }
        function filterData(key) {
            const input = document.getElementById('search' + key.charAt(0).toUpperCase() + key.slice(1)).value.toLowerCase();
            state[key].filtered = state[key].data.filter(item => Object.values(item).some(val => String(val).toLowerCase().includes(input)));
            state[key].page = 1; updateTable(key);
        }

       function renderRowRegistro(r) {
            const isOp = r.tipo_registro === 'OPERARIO' || r.tipo_registro === 'CONDUCTOR';
            const foto = r.foto ? `uploads/${r.foto}` : (isOp ? 'https://ui-avatars.com/api/?name='+r.nombre+'&background=f1f5f9&color=475569' : 'https://ui-avatars.com/api/?name=VEH&background=F5A623&color=0f172a');
            const bContratista = 'bg-slate-100 text-slate-700 border border-slate-200';
            const bModulo = r.modulo === 'TRANSPORTE' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200';
            
            return `
                <tr class="hover:bg-slate-50 transition-colors group">
                    <td class="p-4 w-16 text-center">
                        <div onclick="changeFoto(${r.id}, '${r.foto||''}')" class="relative group cursor-pointer w-10 h-10 mx-auto rounded-lg overflow-hidden border border-slate-200 shadow-sm" title="Actualizar Fotografía">
                            <img src="${foto}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                            <div class="absolute inset-0 bg-black/50 hidden group-hover:flex items-center justify-center transition-all">
                                <i class="fas fa-camera text-white text-xs"></i>
                            </div>
                        </div>
                    </td>
                    <td class="p-4 font-semibold text-slate-700 text-sm">${r.nombre_centro || 'Global'}</td>
                    <td class="p-4"><div class="flex flex-col gap-1.5 items-start"><span class="px-2 py-0.5 rounded text-[10px] font-bold ${bContratista}">${r.tipo_contratista}</span><span class="px-2 py-0.5 rounded text-[10px] font-bold ${bModulo}">${r.modulo}</span></div></td>
                    <td class="p-4"><div class="font-bold text-dark text-sm">${r.tipo_registro}</div><div class="text-xs text-slate-500">${r.cargo || 'N/A'}</div></td>
                    <td class="p-4"><div class="font-bold text-dark text-sm">${isOp ? r.nombre : r.placa}</div><div class="text-xs font-medium text-slate-500 mt-0.5"><i class="fas fa-id-card mr-1 text-slate-400"></i> ${isOp ? r.cedula : '-'}</div></td>
                    <td class="p-4 text-right">
                        <div class="flex gap-2 justify-end">
                            <button class="text-blue-600 hover:text-blue-800 p-1.5 rounded transition-colors" title="Gestionar Documentos" onclick="openGestorDocumentos(${r.id}, '${r.tipo_registro}', '${r.cargo}', '${r.modulo}')"><i class="fas fa-folder-open text-lg"></i></button>
                            <button class="text-slate-400 hover:text-dark p-1.5 rounded transition-colors" title="Editar Info Básica" onclick="editRegistro(${r.id})"><i class="fas fa-pen text-lg"></i></button>
                            <button class="text-slate-400 hover:text-red-600 p-1.5 rounded transition-colors" title="Eliminar Registro" onclick="deleteItem('deleteRegistro', ${r.id}, loadRegistros)"><i class="fas fa-trash-can text-lg"></i></button>
                        </div>
                    </td>
                </tr>`;
        }

        function renderRowUsuario(u) {
            const bRol = u.rol === 'SUPERADMIN' ? 'bg-slate-800 text-white' : (u.rol==='ADMIN' ? 'bg-slate-200 text-slate-700' : 'bg-slate-100 text-slate-600');
            return `
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="p-4"><div class="flex items-center gap-3"><img src="https://ui-avatars.com/api/?name=${u.nombre}&background=e2e8f0&color=475569" class="w-8 h-8 rounded-full border border-slate-200"><span class="font-bold text-dark text-sm">${u.nombre}</span></div></td>
                    <td class="p-4 font-medium text-slate-600 font-mono text-sm">${u.cedula}</td>
                    <td class="p-4"><span class="px-2 py-1 rounded text-[10px] font-bold ${bRol}">${u.rol}</span></td>
                    <td class="p-4 text-sm font-medium text-slate-600">${u.centro || '<span class="text-slate-400 italic">Acceso Global</span>'}</td>
                    <td class="p-4 text-right">
                        <div class="flex gap-2 justify-end">
                            <button class="text-blue-500 hover:text-blue-700 p-1.5 rounded transition-colors" title="Editar" onclick="editUsuario(${u.id}, '${u.nombre}', '${u.cedula}', '${u.rol}')"><i class="fas fa-pen"></i></button>
                            <button class="text-slate-400 hover:text-red-600 p-1.5 rounded transition-colors" title="Eliminar" onclick="deleteItem('deleteUsuario', ${u.id}, loadUsuarios)"><i class="fas fa-trash-can"></i></button>
                        </div>
                    </td>
                </tr>`;
        }

        function renderRowCentro(c) {
            return `
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="p-4 font-semibold text-slate-400 text-xs">#${c.id}</td>
                    <td class="p-4 font-bold text-dark text-sm"><i class="fas fa-map-marker-alt text-slate-400 mr-2"></i> ${c.nombre}</td>
                    <td class="p-4 text-sm text-slate-500">${c.direccion || '<em class="opacity-50">Sin dirección</em>'}</td>
                    <td class="p-4 text-right">
                        <div class="flex gap-2 justify-end">
                            <button class="text-blue-500 hover:text-blue-700 p-1.5 rounded transition-colors" title="Editar" onclick="editCentro(${c.id}, '${c.nombre}', '${c.direccion}')"><i class="fas fa-pen"></i></button>
                            <button class="text-slate-400 hover:text-red-600 p-1.5 rounded transition-colors" title="Eliminar" onclick="deleteItem('deleteCentro', ${c.id}, loadCentros)"><i class="fas fa-trash-can"></i></button>
                        </div>
                    </td>
                </tr>`;
        }

        // ================= FETCHS BASE =================
        function loadRegistros() { fetch(`${API_URL}?action=getRegistros`).then(r=>r.json()).then(d=>{if(d.success){state.registros.data=d.data;state.registros.filtered=d.data;updateTable('registros');}}); }
        function loadUsuarios() { fetch(`${API_URL}?action=getUsuarios`).then(r=>r.json()).then(d=>{if(d.success){state.usuarios.data=d.data;state.usuarios.filtered=d.data;updateTable('usuarios');}}); }
        function loadCentros() { fetch(`${API_URL}?action=getCentros`).then(r=>r.json()).then(d=>{if(d.success){state.centros.data=d.data;state.centros.filtered=d.data;updateTable('centros');}}); }

        function loadCargos() {
            fetch(`${API_URL}?action=getCargos`).then(res => res.json()).then(data => {
                if (data.success) {
                    state.cargos.data = data.data;
                    const tbody = document.getElementById('cargosBody');
                    if (data.data.length > 0) {
                        tbody.innerHTML = data.data.map(c => {
                            const bMod = c.modulo === 'TRANSPORTE' ? 'text-blue-700 bg-blue-50 border-blue-200' : 'text-emerald-700 bg-emerald-50 border-emerald-200';
                            return `<tr class="hover:bg-slate-50 transition-colors"><td class="p-4"><span class="px-2 py-0.5 rounded text-[10px] font-bold border ${bMod}">${c.modulo}</span></td><td class="p-4 font-semibold text-sm text-slate-700">${c.nombre}</td><td class="p-4 text-right"><button class="text-slate-400 hover:text-red-600 p-1 rounded transition-colors" onclick="deleteItem('deleteCargo', ${c.id}, loadCargos)"><i class="fas fa-trash-can"></i></button></td></tr>`;
                        }).join('');
                    } else { tbody.innerHTML = getEmptyState('fa-toolbox', 'Catálogo Vacío', 'Agrega los cargos disponibles.', 3); }
                }
            });
        }

        function loadTiposDocumento() {
            fetch(`${API_URL}?action=getTiposDocumento`).then(res => res.json()).then(data => {
                if (data.success) {
                    state.documentos.data = data.data;
                    const tbody = document.getElementById('documentosBody');
                    if (data.data.length > 0) {
                        tbody.innerHTML = data.data.map(d => {
                            const req = d.es_obligatorio == 1 ? '<i class="fas fa-asterisk text-[10px] text-red-500" title="Obligatorio"></i>' : '';
                            return `<tr class="hover:bg-slate-50 transition-colors">
                                <td class="p-4 font-semibold text-sm text-slate-700">
                                    <i class="fas fa-file-alt text-slate-400 mr-1.5"></i> ${d.nombre} ${req}
                                    <div class="text-[10px] text-slate-400 mt-1 font-medium">Módulo: <span class="font-bold">${d.modulo || 'AMBOS'}</span></div>
                                </td>
                                <td class="p-4 text-[11px] font-bold text-slate-500">${d.aplica_a}</td>
                                <td class="p-4 text-right">
                                    <div class="flex gap-2 justify-end">
                                        <button class="text-blue-500 hover:text-blue-700 p-1.5 rounded transition-colors" title="Editar" onclick="editDocumentoModal(${d.id})"><i class="fas fa-pen"></i></button>
                                        <button class="text-slate-400 hover:text-red-600 p-1 rounded transition-colors" onclick="deleteItem('deleteTipoDocumento', ${d.id}, loadTiposDocumento)"><i class="fas fa-trash-can"></i></button>
                                    </div>
                                </td>
                            </tr>`;
                        }).join('');
                    } else { tbody.innerHTML = getEmptyState('fa-folder-blank', 'Sin Documentos', 'Configura los papeles requeridos.', 3); }
                }
            });
        }

        // ================= ACTUALIZAR SOLO FOTO =================
        function changeFoto(id, currentFoto) {
            let imgHtml = currentFoto ? `<img id="previewModalFoto" src="uploads/${currentFoto}" class="w-32 h-32 rounded-xl object-cover mx-auto mb-4 shadow-sm border border-slate-200">` : `<div id="previewModalFotoPlaceholder" class="w-32 h-32 rounded-xl mx-auto mb-4 bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 text-4xl shadow-sm"><i class="fas fa-camera"></i></div><img id="previewModalFoto" src="" class="w-32 h-32 rounded-xl object-cover mx-auto mb-4 shadow-sm border border-slate-200" style="display:none;">`;

            Swal.fire({
                title: 'Actualizar Fotografía',
                html: `
                    <div class="text-center mt-4">
                        ${imgHtml}
                        <input type="file" id="newFotoInput" accept="image/*" class="hidden">
                        <button onclick="document.getElementById('newFotoInput').click()" class="bg-blue-50 text-blue-700 px-4 py-2 rounded-lg font-bold border border-blue-200 hover:bg-blue-100 transition-colors w-full mb-2"><i class="fas fa-upload mr-2"></i> Seleccionar Nueva Foto</button>
                    </div>
                `,
                showCancelButton: true,
                showDenyButton: currentFoto ? true : false,
                confirmButtonText: 'Guardar Foto',
                cancelButtonText: 'Cancelar',
                denyButtonText: 'Eliminar Actual',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'px-4 py-2 bg-dark text-white rounded-lg font-semibold hover:bg-dark-hover ml-2',
                    cancelButton: 'px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg font-semibold hover:bg-slate-50',
                    denyButton: 'px-4 py-2 bg-red-50 text-red-600 border border-red-200 rounded-lg font-semibold hover:bg-red-100 ml-2 mr-auto'
                },
                didOpen: () => {
                    document.getElementById('newFotoInput').addEventListener('change', function(e) {
                        if (e.target.files && e.target.files[0]) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const preview = document.getElementById('previewModalFoto');
                                preview.src = e.target.result;
                                preview.style.display = 'block';
                                const placeholder = document.getElementById('previewModalFotoPlaceholder');
                                if(placeholder) placeholder.style.display = 'none';
                            }
                            reader.readAsDataURL(e.target.files[0]);
                        }
                    });
                },
                preConfirm: () => {
                    const file = document.getElementById('newFotoInput').files[0];
                    if (!file) return false; 
                    const fd = new FormData();
                    fd.append('action', 'updateFoto');
                    fd.append('id', id);
                    fd.append('foto', file);
                    return fd;
                }
            }).then(res => {
                if (res.isConfirmed && res.value) {
                    Swal.fire({ title: 'Subiendo...', didOpen: () => Swal.showLoading() });
                    fetch(API_URL, { method: 'POST', body: res.value }).then(r=>r.json()).then(data => {
                        if(data.success) { Swal.fire({title: 'Éxito', icon: 'success', timer: 1000, showConfirmButton: false}); loadRegistros(); }
                        else Swal.fire('Error', data.message, 'error');
                    });
                } else if (res.isDenied) {
                    Swal.fire({
                        title: '¿Eliminar foto?', text: "La foto se borrará permanentemente", icon: 'warning',
                        showCancelButton: true, confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar', buttonsStyling: false,
                        customClass: { confirmButton: 'px-4 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 ml-2', cancelButton: 'px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg font-semibold hover:bg-slate-50' }
                    }).then(r => {
                        if(r.isConfirmed) {
                            const fd = new FormData();
                            fd.append('action', 'updateFoto');
                            fd.append('id', id);
                            fd.append('remove_foto', '1');
                            fetch(API_URL, { method: 'POST', body: fd }).then(r=>r.json()).then(data => {
                                if(data.success) { Swal.fire({title: 'Eliminada', icon: 'success', timer: 1000, showConfirmButton: false}); loadRegistros(); }
                            });
                        }
                    });
                }
            });
        }

        // ================= WIZARD DE REGISTRO RÁPIDO =================
        let wizardData = { step: 1, contratista: '', tipo: '', modulo: '', nombre: '', cedula: '', cargoNombre: '', placa: '', centro_id: null, fotoFile: null };

        function openWizard() {
            wizardData = { step: 1, contratista: '', tipo: '', modulo: '', nombre: '', cedula: '', cargoNombre: '', placa: '', centro_id: null, fotoFile: null };
            showStepModulo();
        }

        function showStepModulo() {
            Swal.fire({
                title: 'Área Operacional',
                html: `
                    <p class="text-sm text-slate-500 mb-5">Seleccione el área al que pertenece el registro</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div onclick="setModulo('TRANSPORTE', 'UC')" class="selectable-card p-5 rounded-xl flex flex-col items-center justify-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-slate-100 text-dark border border-slate-200 flex items-center justify-center text-xl"><i class="fas fa-route"></i></div>
                            <div class="text-center">
                                <h3 class="font-bold text-dark text-base">Transporte</h3>
                                <span class="text-[11px] font-medium text-slate-500">Rutas / Externo</span>
                            </div>
                        </div>
                        <div onclick="setModulo('ALMACEN', 'OL')" class="selectable-card p-5 rounded-xl flex flex-col items-center justify-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-slate-100 text-dark border border-slate-200 flex items-center justify-center text-xl"><i class="fas fa-boxes-stacked"></i></div>
                            <div class="text-center">
                                <h3 class="font-bold text-dark text-base">Almacén</h3>
                                <span class="text-[11px] font-medium text-slate-500">Interno</span>
                            </div>
                        </div>
                    </div>
                `,
                showConfirmButton: false, showCancelButton: true, cancelButtonText: 'Cancelar', width: '500px',
                buttonsStyling: false, customClass: { cancelButton: 'px-5 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-lg font-semibold hover:bg-slate-50 mt-4' }
            });
        }
        function setModulo(mod, cont) { wizardData.modulo = mod; wizardData.contratista = cont; showStepTipo(); }

        function showStepTipo() {
            Swal.fire({
                title: 'Tipo de Registro',
                html: `
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div onclick="setTipo('OPERARIO')" class="selectable-card p-5 rounded-xl flex flex-col items-center justify-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-slate-100 text-dark border border-slate-200 flex items-center justify-center text-xl"><i class="fas fa-user-tie"></i></div>
                            <h3 class="font-bold text-dark text-base">Operario</h3>
                        </div>
                        <div onclick="setTipo('VEHICULO')" class="selectable-card p-5 rounded-xl flex flex-col items-center justify-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-slate-100 text-dark border border-slate-200 flex items-center justify-center text-xl"><i class="fas fa-truck"></i></div>
                            <h3 class="font-bold text-dark text-base">Vehículo</h3>
                        </div>
                    </div>
                `,
                showConfirmButton: false, showCancelButton: true, cancelButtonText: 'Atrás', width: '500px',
                buttonsStyling: false, customClass: { cancelButton: 'px-5 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-lg font-semibold hover:bg-slate-50 mt-4' }
            }).then((res) => { if (res.dismiss === Swal.DismissReason.cancel) showStepModulo(); });
        }
        function setTipo(tipo) { 
            wizardData.tipo = tipo; 
            if (userRol === 'SUPERADMIN') showStepCentro(); else showStepForm(); 
        }

        function showStepCentro() {
            const centrosHtml = state.centros.data.map(c => `
                <div onclick="setCentro(${c.id})" class="selectable-card p-4 rounded-xl flex items-center gap-4 text-left">
                    <div class="bg-slate-100 text-slate-500 border border-slate-200 w-10 h-10 rounded-lg flex items-center justify-center text-lg shrink-0"><i class="fas fa-map-marker-alt"></i></div>
                    <div><h4 class="font-bold text-dark text-sm">${c.nombre}</h4></div>
                </div>
            `).join('');

            Swal.fire({
                title: 'Asignar a Sucursal',
                html: `<div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4 max-h-[300px] overflow-y-auto custom-scrollbar p-1">${centrosHtml}</div>`,
                showConfirmButton: false, showCancelButton: true, cancelButtonText: 'Atrás', width: '600px',
                buttonsStyling: false, customClass: { cancelButton: 'px-5 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-lg font-semibold hover:bg-slate-50 mt-4' }
            }).then((res) => { if (res.dismiss === Swal.DismissReason.cancel) showStepTipo(); });
        }
        function setCentro(id) { wizardData.centro_id = id; showStepForm(); }

        function showStepForm() {
            const isOp = wizardData.tipo === 'OPERARIO';
            
            let cargoHtml = '';
            if (isOp) {
                const cargosFiltrados = state.cargos.data.filter(c => c.modulo === wizardData.modulo);
                const cargoCards = cargosFiltrados.map(c => `
                    <div onclick="selectCargo('${c.nombre}')" class="selectable-card p-3 rounded-lg text-left flex items-center gap-3 mb-2">
                        <i class="fas fa-circle-dot text-[10px] text-slate-400"></i> <span class="font-bold text-sm text-slate-700">${c.nombre}</span>
                    </div>`).join('');
                    
                cargoHtml = `
                    <div id="formSection" class="text-sm">
                        <!-- Selector de Foto Circular -->
                        <div class="photo-upload-btn" onclick="document.getElementById('wizardFoto').click()">
                            <i class="fas fa-camera text-slate-400 text-xl" id="fotoIcon"></i>
                            <img id="fotoPreviewImg" src="" alt="Preview">
                        </div>
                        <input type="file" id="wizardFoto" accept="image/*" class="hidden" onchange="previewWizardFoto(this)">

                        <div class="text-left bg-slate-50 p-3 rounded-lg mb-4 text-xs text-slate-600 border border-slate-200 flex gap-2"><i class="fas fa-info-circle mt-0.5"></i> Se creará usuario de acceso automáticamente.</div>
                        <div class="space-y-3">
                            <input id="nombre" type="text" placeholder="NOMBRE COMPLETO" class="w-full p-3 border border-slate-300 rounded-lg uppercase font-medium focus:border-dark focus:ring-1 focus:ring-dark outline-none text-sm" value="${wizardData.nombre}">
                            <input id="cedula" type="text" placeholder="Cédula (Usuario Login)" class="w-full p-3 border border-slate-300 rounded-lg font-medium focus:border-dark focus:ring-1 focus:ring-dark outline-none text-sm" oninput="this.value = this.value.replace(/[^0-9]/g, '')" value="${wizardData.cedula}">
                            <input id="passwordOp" type="password" placeholder="Crear Contraseña" class="w-full p-3 border border-slate-300 rounded-lg font-medium focus:border-dark focus:ring-1 focus:ring-dark outline-none text-sm">
                            
                            <button type="button" id="btnCargoSelector" onclick="toggleCargoView()" class="w-full p-3 border border-slate-300 rounded-lg font-medium text-slate-600 hover:border-dark hover:bg-slate-50 transition-colors flex justify-between items-center bg-white text-left text-sm">
                                <span id="cargoLabel">${wizardData.cargoNombre ? '<i class="fas fa-check text-green-600 mr-1"></i> ' + wizardData.cargoNombre : 'Seleccionar Cargo...'}</span>
                                <i class="fas fa-chevron-right text-xs"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div id="cargoSelectionSection" style="display:none;" class="text-left">
                        <div class="flex items-center gap-2 mb-4">
                            <button type="button" onclick="toggleCargoView()" class="p-1.5 bg-slate-100 hover:bg-slate-200 rounded border border-slate-200 text-slate-600"><i class="fas fa-arrow-left text-sm"></i></button>
                            <h4 class="font-bold text-dark text-sm">Elegir Cargo</h4>
                        </div>
                        <div class="max-h-[200px] overflow-y-auto custom-scrollbar pr-2">${cargoCards || '<p class="text-sm text-slate-500 py-2">No hay cargos configurados.</p>'}</div>
                    </div>`;
            } else {
                cargoHtml = `
                <div id="formSection">
                    <!-- Selector de Foto Circular -->
                    <div class="photo-upload-btn" onclick="document.getElementById('wizardFoto').click()">
                        <i class="fas fa-camera text-slate-400 text-xl" id="fotoIcon"></i>
                        <img id="fotoPreviewImg" src="" alt="Preview">
                    </div>
                    <input type="file" id="wizardFoto" accept="image/*" class="hidden" onchange="previewWizardFoto(this)">
                    <input id="placa" type="text" placeholder="PLACA DEL VEHÍCULO" class="w-full p-3 border border-slate-300 rounded-lg uppercase font-bold text-center tracking-widest focus:border-dark focus:ring-1 focus:ring-dark outline-none" value="${wizardData.placa}">
                </div>`;
            }

            Swal.fire({
                title: `Datos de ${wizardData.tipo}`, html: cargoHtml,
                confirmButtonText: 'Crear Registro', showCancelButton: true, cancelButtonText: 'Atrás', width: '450px',
                buttonsStyling: false,
                customClass: { 
                    confirmButton: 'px-5 py-2.5 bg-dark text-white rounded-lg font-semibold hover:bg-dark-hover ml-2',
                    cancelButton: 'px-5 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-lg font-semibold hover:bg-slate-50'
                },
                didOpen: () => {
                    // Mantener preview si ya había seleccionado foto antes de retroceder
                    if (wizardData.fotoFile) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            document.getElementById('fotoPreviewImg').src = e.target.result;
                            document.getElementById('fotoPreviewImg').style.display = 'block';
                            document.getElementById('fotoIcon').style.display = 'none';
                        }
                        reader.readAsDataURL(wizardData.fotoFile);
                    }

                    window.previewWizardFoto = (input) => {
                        if (input.files && input.files[0]) {
                            wizardData.fotoFile = input.files[0];
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                document.getElementById('fotoPreviewImg').src = e.target.result;
                                document.getElementById('fotoPreviewImg').style.display = 'block';
                                document.getElementById('fotoIcon').style.display = 'none';
                            }
                            reader.readAsDataURL(input.files[0]);
                        }
                    };

                    window.toggleCargoView = () => {
                        const fs = document.getElementById('formSection'), cs = document.getElementById('cargoSelectionSection');
                        if (fs.style.display === 'none') {
                            fs.style.display = 'block'; cs.style.display = 'none'; Swal.getConfirmButton().style.display = 'inline-flex';
                        } else {
                            wizardData.nombre = document.getElementById('nombre').value;
                            wizardData.cedula = document.getElementById('cedula').value;
                            fs.style.display = 'none'; cs.style.display = 'block'; Swal.getConfirmButton().style.display = 'none';
                        }
                    };
                    window.selectCargo = (nombre) => {
                        wizardData.cargoNombre = nombre;
                        document.getElementById('cargoLabel').innerHTML = '<i class="fas fa-check text-green-600 mr-1"></i> ' + nombre;
                        document.getElementById('btnCargoSelector').className = 'w-full p-3 border border-dark rounded-lg font-bold text-dark bg-slate-50 flex justify-between items-center transition-colors text-left text-sm shadow-sm';
                        toggleCargoView();
                    };
                },
                preConfirm: () => {
                    if (document.getElementById('formSection').style.display === 'none') return false; 
                    
                    if (isOp) {
                        wizardData.nombre = document.getElementById('nombre').value.toUpperCase().trim();
                        wizardData.cedula = document.getElementById('cedula').value.trim();
                        wizardData.password = document.getElementById('passwordOp').value;
                        if (!wizardData.nombre || !wizardData.cedula || !wizardData.password || !wizardData.cargoNombre) {
                            Swal.showValidationMessage('Complete campos y seleccione un cargo'); return false;
                        }
                    } else {
                        wizardData.placa = document.getElementById('placa').value.toUpperCase().trim();
                        if (!wizardData.placa) { Swal.showValidationMessage('Ingrese la placa'); return false; }
                    }
                    return true;
                }
            }).then((res) => {
                if (res.isConfirmed) saveRegistroSolo();
                else if (res.dismiss === Swal.DismissReason.cancel) {
                    if (userRol === 'SUPERADMIN') showStepCentro(); else showStepTipo();
                }
            });
        }

      function saveRegistroSolo() {
            Swal.fire({ title: 'Guardando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            const formData = new FormData();
            formData.append('action', 'saveRegistro');
            formData.append('contratista', wizardData.contratista);
            formData.append('tipo', wizardData.tipo);
            formData.append('modulo', wizardData.modulo);
            formData.append('nombre', wizardData.nombre);
            formData.append('cedula', wizardData.cedula);
            formData.append('pass', wizardData.password); 
            formData.append('cargo', wizardData.cargoNombre);
            formData.append('placa', wizardData.placa);
            if (wizardData.centro_id) formData.append('centro_id', wizardData.centro_id);
            if (wizardData.fotoFile) formData.append('foto', wizardData.fotoFile); 

            formData.append('documentos', '[]'); 
            
            fetch(API_URL, { method: 'POST', body: formData }).then(res => res.json()).then(data => {
                if (data.success) { 
                    loadRegistros(); loadUsuarios(); 
                    Swal.fire({ 
                        icon: 'success', title: 'Registro Creado', text: '¿Deseas subir sus documentos ahora?',
                        showCancelButton: true, confirmButtonText: 'Sí, gestionar', cancelButtonText: 'Más tarde',
                        buttonsStyling: false, customClass: { confirmButton: 'px-4 py-2 bg-dark text-white rounded-lg font-semibold ml-2', cancelButton: 'px-4 py-2 bg-slate-200 text-slate-700 rounded-lg font-semibold' }
                    }).then(res => {
                        if (res.isConfirmed) openGestorDocumentos(data.id, wizardData.tipo, wizardData.cargoNombre, wizardData.modulo);
                    });
                } else { Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#0f172a' }); }
            });
        }

       function openGestorDocumentos(registroId, tipoRegistro, cargoNombre, moduloRegistro) {
            Swal.fire({ title: 'Cargando Gestor...', didOpen: () => Swal.showLoading() });

            fetch(`${API_URL}?action=getDocumentos&id=${registroId}`).then(r=>r.json()).then(docRes => {
                if(!docRes.success) return Swal.fire('Error', 'No se pudieron cargar los documentos', 'error');
                
                const docsSubidos = docRes.data;

                const cargoObj = state.cargos.data.find(c => c.nombre === cargoNombre);
                const cargoIdStr = cargoObj ? cargoObj.id.toString() : '';

                let catalogoAplicable = state.documentos.data.filter(d => {
                    // 1. Validar que el documento pertenezca al Módulo del Registro (o AMBOS)
                    const docModulo = d.modulo || 'AMBOS';
                    if (docModulo !== 'AMBOS' && docModulo !== moduloRegistro) return false;

                    // 2. Validar que aplique al Tipo de Registro (Vehículo u Operario)
                    if (tipoRegistro === 'VEHICULO') return d.aplica_a === 'VEHICULO' || d.aplica_a === 'AMBOS';
                    if (d.aplica_a === 'VEHICULO') return false;
                    
                    // 3. Validar Cargos
                    if (d.condicion_cargos === 'TODOS') return true;
                    let rel = []; try { rel = JSON.parse(d.cargos_relacionados || '[]'); } catch(e){}
                    if (d.condicion_cargos === 'SOLO') return rel.includes(cargoIdStr);
                    if (d.condicion_cargos === 'EXCEPTO') return !rel.includes(cargoIdStr);
                    return true;
                });

                let tarjetasHtml = catalogoAplicable.map(docCatalogo => {
                    const docSubido = docsSubidos.find(ds => ds.tipo_documento === docCatalogo.nombre);
                    const isReq = docCatalogo.es_obligatorio == 1;

                    if (docSubido) {
                        return `
                        <div class="bg-white border-2 border-green-500 rounded-xl p-4 shadow-sm text-left flex justify-between items-center mb-3">
                            <div>
                                <h4 class="font-bold text-dark text-sm"><i class="fas fa-check-circle text-green-500 mr-1"></i> ${docCatalogo.nombre}</h4>
                                <p class="text-xs text-slate-500 mt-1">Exp: ${docSubido.fecha_expedicion || 'N/A'} | Venc: ${docSubido.fecha_vencimiento || 'N/A'}</p>
                            </div>
                            <button onclick="openEditorUnico(${registroId}, ${docCatalogo.id}, '${docCatalogo.nombre}', ${docSubido.id}, '${docSubido.fecha_expedicion||''}', '${docSubido.fecha_vencimiento||''}', '${docSubido.archivo_tipo}', '${docSubido.archivo_valor}')" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition-colors">
                                Editar
                            </button>
                        </div>`;
                    } else {
                        return `
                        <div class="bg-white border-2 border-slate-200 border-dashed rounded-xl p-4 shadow-sm text-left flex justify-between items-center mb-3">
                            <div>
                                <h4 class="font-bold text-slate-500 text-sm"><i class="fas fa-circle-exclamation text-orange mr-1"></i> ${docCatalogo.nombre} ${isReq ? '<span class="text-red-500">*</span>' : ''}</h4>
                                <p class="text-xs text-slate-400 mt-1">Documento pendiente por subir</p>
                            </div>
                            <button onclick="openEditorUnico(${registroId}, ${docCatalogo.id}, '${docCatalogo.nombre}')" class="px-3 py-1.5 bg-dark hover:bg-dark-hover text-white rounded-lg text-xs font-bold transition-colors">
                                Agregar
                            </button>
                        </div>`;
                    }
                }).join('');

                if (catalogoAplicable.length === 0) tarjetasHtml = '<p class="text-slate-500 text-sm py-4">No hay documentos requeridos para este perfil.</p>';

                Swal.fire({
                    title: 'Gestor de Documentos',
                    html: `<div class="max-h-[60vh] overflow-y-auto custom-scrollbar pr-2 mt-4">${tarjetasHtml}</div>`,
                    width: '600px', showConfirmButton: false, showCloseButton: true
                });
            });
        }

        function openEditorUnico(registroId, tipoDocId, tipoDocNombre, docId = null, exp = '', venc = '', archTipo = 'PDF', archValor = '') {
            const isEdit = docId !== null;
            
            Swal.fire({
                title: isEdit ? 'Editar Documento' : 'Subir Documento',
                html: `
                    <div class="text-left space-y-4 mt-4">
                        <div class="bg-slate-50 p-3 rounded-lg border border-slate-200">
                            <span class="font-bold text-sm text-dark block"><i class="fas fa-file-alt text-slate-400 mr-2"></i> ${tipoDocNombre}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] text-slate-500 uppercase font-bold block mb-1">Expedición</label>
                                <div class="flex gap-2">
                                    <input type="date" id="doc_exp" value="${exp}" class="w-full p-2.5 text-xs border border-slate-300 rounded-lg outline-none focus:border-dark bg-white" ${exp==='' && isEdit ? 'disabled' : ''}>
                                    <button type="button" onclick="toggleDateNA('doc_exp', this)" class="p-2 ${exp==='' && isEdit ? 'bg-dark text-white border-dark' : 'bg-slate-100 text-slate-500 border-slate-200'} border rounded-lg text-xs font-bold transition-colors" title="No Aplica">N/A</button>
                                </div>
                            </div>
                            <div>
                                <label class="text-[10px] text-slate-500 uppercase font-bold block mb-1">Vencimiento</label>
                                <div class="flex gap-2">
                                    <input type="date" id="doc_venc" value="${venc}" class="w-full p-2.5 text-xs border border-slate-300 rounded-lg outline-none focus:border-dark bg-white" ${venc==='' && isEdit ? 'disabled' : ''}>
                                    <button type="button" onclick="toggleDateNA('doc_venc', this)" class="p-2 ${venc==='' && isEdit ? 'bg-dark text-white border-dark' : 'bg-slate-100 text-slate-500 border-slate-200'} border rounded-lg text-xs font-bold transition-colors" title="No Aplica">N/A</button>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="text-[10px] text-slate-500 uppercase font-bold block mb-2">Formato de Archivo</label>
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div onclick="setDocFormat('PDF', this)" class="fmt-card selectable-card ${archTipo==='PDF'?'selected':''} p-3 rounded-lg text-center flex items-center justify-center gap-2"><i class="fas fa-file-pdf text-rose-500 text-lg"></i><span class="font-bold text-sm">PDF</span></div>
                                <div onclick="setDocFormat('LINK', this)" class="fmt-card selectable-card ${archTipo==='LINK'?'selected':''} p-3 rounded-lg text-center flex items-center justify-center gap-2"><i class="fas fa-link text-blue-500 text-lg"></i><span class="font-bold text-sm">Enlace</span></div>
                            </div>
                            <input type="hidden" id="doc_arch_tipo" value="${archTipo}">
                            
                            <div id="doc_file_cont" style="display:${archTipo==='PDF'?'block':'none'};" class="bg-white p-3 rounded-lg border border-slate-200 shadow-sm">
                                ${isEdit && archTipo==='PDF' ? `<p class="text-[10px] text-green-600 font-bold mb-2">Ya hay un archivo subido. Sube uno nuevo solo si deseas reemplazarlo.</p>` : ''}
                                <input type="file" id="doc_file" accept=".pdf" class="w-full text-xs file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                            </div>
                            <div id="doc_link_cont" style="display:${archTipo==='LINK'?'block':'none'};" class="bg-white p-3 rounded-lg border border-slate-200 shadow-sm">
                                <input type="url" id="doc_link" value="${archTipo==='LINK'?archValor:''}" placeholder="https://ejemplo.com/documento.pdf" class="w-full p-2 text-sm border-none outline-none bg-transparent">
                            </div>
                        </div>
                    </div>`,
                showCancelButton: true, confirmButtonText: 'Guardar Documento', cancelButtonText: 'Cancelar', width: '500px',
                buttonsStyling: false,
                customClass: { confirmButton: 'px-5 py-2.5 bg-dark text-white rounded-lg font-semibold hover:bg-dark-hover ml-2', cancelButton: 'px-5 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-lg font-semibold hover:bg-slate-50' },
                didOpen: () => {
                    window.toggleDateNA = (inputId, btn) => {
                        const inp = document.getElementById(inputId);
                        if (inp.disabled) {
                            inp.disabled = false;
                            btn.classList.remove('bg-dark', 'text-white', 'border-dark');
                            btn.classList.add('bg-slate-100', 'text-slate-500', 'border-slate-200');
                        } else {
                            inp.disabled = true; inp.value = '';
                            btn.classList.remove('bg-slate-100', 'text-slate-500', 'border-slate-200');
                            btn.classList.add('bg-dark', 'text-white', 'border-dark');
                        }
                    };
                    window.setDocFormat = (val, el) => {
                        document.querySelectorAll('.fmt-card').forEach(e => e.classList.remove('selected'));
                        el.classList.add('selected');
                        document.getElementById('doc_arch_tipo').value = val;
                        document.getElementById('doc_file_cont').style.display = val === 'PDF' ? 'block' : 'none';
                        document.getElementById('doc_link_cont').style.display = val === 'LINK' ? 'block' : 'none';
                    };
                },
                // (Código intermedio de openEditorUnico...)
                preConfirm: () => {
                    const tipoFormat = document.getElementById('doc_arch_tipo').value;
                    const file = document.getElementById('doc_file').files[0];
                    const link = document.getElementById('doc_link').value.trim();
                    
                    if (tipoFormat === 'PDF' && !file && !isEdit) { Swal.showValidationMessage('Sube el archivo PDF'); return false; }
                    if (tipoFormat === 'LINK' && !link) { Swal.showValidationMessage('Ingresa el enlace'); return false; }

                    const fd = new FormData();
                    fd.append('action', 'saveSingleDoc'); 
                    fd.append('registro_id', registroId);
                    if (isEdit) fd.append('doc_id', docId);
                    fd.append('tipo_documento', tipoDocNombre);
                    fd.append('fecha_expedicion', document.getElementById('doc_exp').disabled ? '' : document.getElementById('doc_exp').value);
                    fd.append('fecha_vencimiento', document.getElementById('doc_venc').disabled ? '' : document.getElementById('doc_venc').value);
                    fd.append('archivo_tipo', tipoFormat);
                    
                    if (tipoFormat === 'PDF') { if(file) fd.append('archivo_pdf', file); } 
                    else { fd.append('archivo_valor', link); }
                    return fd;
                }
            }).then(res => {
                if (res.isConfirmed) {
                    Swal.fire({ title: 'Guardando...', didOpen: () => Swal.showLoading() });
                    fetch(API_URL, { method: 'POST', body: res.value }).then(r=>r.json()).then(data => {
                        if(data.success) {
                            Swal.fire({title: 'Éxito', icon: 'success', timer: 1000, showConfirmButton: false});
                            // Re-abrimos el modal de documentos para ver el cambio, incluyendo el Módulo
                            fetch(`${API_URL}?action=getRegistro&id=${registroId}`).then(rr=>rr.json()).then(regData => {
                                if(regData.success) openGestorDocumentos(registroId, regData.data.tipo_registro, regData.data.cargo, regData.data.modulo);
                            });
                        } else { Swal.fire('Error', data.message, 'error'); }
                    });
                }
            });
        }

        // ================= MODALES DE CATÁLOGO =================
        function openDocumentoModal(id = null) {
            const isEdit = id !== null;
            let docData = { nombre: '', aplica_a: 'CONDUCTOR', modulo: 'AMBOS', condicion_cargos: 'TODOS', cargos_relacionados: '[]', es_obligatorio: 1 };
            
            if (isEdit) {
                const docFound = state.documentos.data.find(d => d.id === id);
                if(docFound) docData = docFound;
            }

            let relArray = [];
            try { relArray = JSON.parse(docData.cargos_relacionados || '[]'); } catch(e){}

            let cargosHtml = state.cargos.data.length > 0 ? state.cargos.data.map(c => {
                const isChecked = relArray.includes(c.id.toString()) ? 'checked' : '';
                return `
                <label class="flex items-center gap-3 p-2.5 bg-white border border-slate-200 hover:border-dark rounded-lg cursor-pointer transition-colors shadow-sm mb-2">
                    <input type="checkbox" value="${c.id}" class="doc-cargo-cb w-4 h-4 accent-dark" ${isChecked}> 
                    <span class="font-semibold text-sm text-slate-700 flex-1">${c.nombre}</span>
                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded border border-slate-200 bg-slate-50 text-slate-500">${c.modulo}</span>
                </label>`;
            }).join('') : '<p class="text-sm text-slate-400 p-2 text-center">No hay cargos registrados.</p>';

            Swal.fire({
                title: isEdit ? 'Editar Documento' : 'Nuevo Documento', width: '600px',
                html: `
                    <div class="mt-4 text-left space-y-4">
                        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                            <label class="text-[11px] font-bold text-slate-500 block mb-1">Nombre del Documento</label>
                            <input id="docNom" type="text" value="${docData.nombre}" placeholder="Ej: Licencia de Conducción" class="w-full p-2.5 border border-slate-300 rounded-lg font-semibold text-dark uppercase focus:border-dark outline-none text-sm">
                        </div>

                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 transition-all shadow-sm">
                            <label class="text-[11px] font-bold text-slate-500 mb-2 block">Módulo del Documento</label>
                            <div class="grid grid-cols-3 gap-2 mb-3">
                                <div onclick="setDocModulo('AMBOS', this)" class="doc-mod selectable-card ${docData.modulo==='AMBOS' || !docData.modulo ?'selected':''} p-2 rounded text-center text-xs justify-center font-bold">Ambos</div>
                                <div onclick="setDocModulo('TRANSPORTE', this)" class="doc-mod selectable-card ${docData.modulo==='TRANSPORTE'?'selected':''} p-2 rounded text-center text-xs justify-center font-bold">Transporte</div>
                                <div onclick="setDocModulo('ALMACEN', this)" class="doc-mod selectable-card ${docData.modulo==='ALMACEN'?'selected':''} p-2 rounded text-center text-xs justify-center font-bold">Almacén</div>
                            </div>
                            <input type="hidden" id="docModulo" value="${docData.modulo || 'AMBOS'}">
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <div onclick="setDocTarget('CONDUCTOR', this)" class="doc-target selectable-card ${docData.aplica_a==='CONDUCTOR'?'selected-gold':''} p-4 rounded-xl text-center"><i class="fas fa-users mb-1.5 text-lg"></i><br><span class="font-semibold text-sm">Operarios</span></div>
                            <div onclick="setDocTarget('VEHICULO', this)" class="doc-target selectable-card ${docData.aplica_a==='VEHICULO'?'selected-gold':''} p-4 rounded-xl text-center"><i class="fas fa-truck mb-1.5 text-lg"></i><br><span class="font-semibold text-sm">Vehículos</span></div>
                            <div onclick="setDocTarget('AMBOS', this)" class="doc-target selectable-card ${docData.aplica_a==='AMBOS'?'selected-gold':''} p-3 rounded-xl text-center col-span-2"><i class="fas fa-link mb-1 text-sm"></i> <span class="font-semibold text-sm">General (Ambos)</span></div>
                        </div>
                        <input type="hidden" id="docAplica" value="${docData.aplica_a}">

                        <div id="cargosFilterSection" style="display:${docData.aplica_a==='VEHICULO'?'none':'block'}" class="bg-slate-50 p-4 rounded-xl border border-slate-200 transition-all shadow-sm">
                            <label class="text-[11px] font-bold text-slate-500 mb-2 block">Regla de Aplicación</label>
                            <div class="flex gap-2 mb-3">
                                <button type="button" onclick="setDocCondicion('TODOS', this)" class="doc-cond flex-1 p-2 rounded ${docData.condicion_cargos==='TODOS'?'bg-dark text-white border-dark':'bg-white text-slate-600 border-slate-300'} font-semibold text-[11px] transition-colors border">Todos</button>
                                <button type="button" onclick="setDocCondicion('SOLO', this)" class="doc-cond flex-1 p-2 rounded ${docData.condicion_cargos==='SOLO'?'bg-dark text-white border-dark':'bg-white text-slate-600 border-slate-300'} font-semibold text-[11px] transition-colors border">Solo Aplica A</button>
                                <button type="button" onclick="setDocCondicion('EXCEPTO', this)" class="doc-cond flex-1 p-2 rounded ${docData.condicion_cargos==='EXCEPTO'?'bg-dark text-white border-dark':'bg-white text-slate-600 border-slate-300'} font-semibold text-[11px] transition-colors border">No Aplica A</button>
                            </div>
                            <input type="hidden" id="docCondicionVal" value="${docData.condicion_cargos}">
                            
                            <div id="cargosList" style="display:${docData.condicion_cargos==='TODOS'?'none':'block'};" class="max-h-[150px] overflow-y-auto custom-scrollbar p-1">
                                ${cargosHtml}
                            </div>
                        </div>
                        
                        <label class="flex items-center gap-3 p-3 bg-white border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 transition-colors shadow-sm">
                            <input type="checkbox" id="docReq" class="w-4 h-4 accent-dark" ${docData.es_obligatorio == 1 ? 'checked' : ''}> 
                            <span class="font-semibold text-sm text-dark">Documento Obligatorio</span>
                        </label>
                    </div>`,
                showCancelButton: true, confirmButtonText: isEdit ? 'Actualizar' : 'Guardar', cancelButtonText: 'Cancelar',
                buttonsStyling: false,
                customClass: { confirmButton: 'px-5 py-2.5 bg-dark text-white rounded-lg font-semibold hover:bg-dark-hover ml-2', cancelButton: 'px-5 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-lg font-semibold hover:bg-slate-50' },
                didOpen: () => {
                    window.setDocModulo = (val, el) => {
                        document.querySelectorAll('.doc-mod').forEach(e => e.classList.remove('selected'));
                        el.classList.add('selected');
                        document.getElementById('docModulo').value = val;
                    };
                    window.setDocTarget = (val, el) => {
                        document.querySelectorAll('.doc-target').forEach(e => e.classList.remove('selected-gold'));
                        el.classList.add('selected-gold');
                        document.getElementById('docAplica').value = val;
                        document.getElementById('cargosFilterSection').style.display = val === 'VEHICULO' ? 'none' : 'block';
                    };
                    window.setDocCondicion = (val, el) => {
                        document.querySelectorAll('.doc-cond').forEach(e => { e.classList.remove('bg-dark', 'text-white', 'border-dark'); e.classList.add('bg-white', 'text-slate-600', 'border-slate-300'); });
                        el.classList.remove('bg-white', 'text-slate-600', 'border-slate-300'); el.classList.add('bg-dark', 'text-white', 'border-dark');
                        document.getElementById('docCondicionVal').value = val;
                        document.getElementById('cargosList').style.display = val === 'TODOS' ? 'none' : 'block';
                    };
                },
                preConfirm: () => {
                    const nombre = document.getElementById('docNom').value.trim();
                    if (!nombre) { Swal.showValidationMessage('El nombre es obligatorio'); return false; }
                    const checks = document.querySelectorAll('.doc-cargo-cb:checked');
                    let cargosIds = Array.from(checks).map(cb => cb.value);
                    
                    let dataToSave = { 
                        action: isEdit ? 'updateTipoDocumento' : 'saveTipoDocumento', 
                        nombre: nombre.toUpperCase(), 
                        modulo: document.getElementById('docModulo').value,
                        aplica_a: document.getElementById('docAplica').value, 
                        es_obligatorio: document.getElementById('docReq').checked ? 1 : 0,
                        condicion_cargos: document.getElementById('docAplica').value === 'VEHICULO' ? 'TODOS' : document.getElementById('docCondicionVal').value,
                        cargos_relacionados: JSON.stringify(cargosIds)
                    };
                    if (isEdit) dataToSave.id = id;
                    return dataToSave;
                }
            }).then(handleFormSubmit(loadTiposDocumento));
        }

        function editDocumentoModal(id) {
            openDocumentoModal(id);
        }

        // ================= CREACIÓN DE USUARIO, CENTRO Y CARGO =================
        function openUsuarioModal(id = null, nombre = '', cedula = '', rol = 'ADMIN') {
            const isEdit = id !== null;
            const isSuperAdmin = userRol === 'SUPERADMIN';
            
            // Determinar centro para el select si es superadmin (para que en edición traiga el seleccionado)
            let centroSelectHTML = '';
            if (isSuperAdmin) {
                let options = state.centros.data.map(c => {
                    let selected = '';
                    if (isEdit) {
                        const userFound = state.usuarios.data.find(u => u.id === id);
                        if(userFound && userFound.centro === c.nombre) selected = 'selected';
                    }
                    return `<option value="${c.id}" ${selected}>${c.nombre}</option>`;
                }).join('');
                centroSelectHTML = `<select id="nuCentro" class="w-full p-2.5 border border-slate-300 rounded-lg text-sm font-medium focus:border-dark outline-none bg-white mt-3"><option value="">Acceso Global (Sin Centro)</option>${options}</select>`;
            }

            Swal.fire({
                title: isEdit ? 'Editar Usuario' : 'Nuevo Usuario',
                html: `
                    <div class="space-y-3 mt-4 text-left">
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div onclick="document.getElementById('nuRol').value='ADMIN'; document.querySelectorAll('.rol-card').forEach(e=>e.classList.remove('selected')); this.classList.add('selected');" class="rol-card selectable-card ${rol==='ADMIN'?'selected':''} p-3 rounded-lg text-center"><i class="fas fa-user-shield text-lg mb-1 text-slate-600"></i><br><span class="font-semibold text-xs text-slate-700">Administrador</span></div>
                            ${isSuperAdmin ? `<div onclick="document.getElementById('nuRol').value='SUPERADMIN'; document.querySelectorAll('.rol-card').forEach(e=>e.classList.remove('selected')); this.classList.add('selected');" class="rol-card selectable-card ${rol==='SUPERADMIN'?'selected':''} p-3 rounded-lg text-center"><i class="fas fa-globe text-lg mb-1 text-slate-600"></i><br><span class="font-semibold text-xs text-slate-700">SuperAdmin</span></div>` : ''}
                        </div>
                        <input type="hidden" id="nuRol" value="${rol}">
                        <input id="nuNom" type="text" value="${nombre}" placeholder="Nombre Completo" class="w-full p-2.5 border border-slate-300 rounded-lg text-sm font-medium focus:border-dark outline-none">
                        <input id="nuCed" type="text" value="${cedula}" placeholder="Cédula / Login" class="w-full p-2.5 border border-slate-300 rounded-lg text-sm font-medium focus:border-dark outline-none" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                        <input id="nuPass" type="password" placeholder="${isEdit ? 'Nueva Contraseña (Opcional)' : 'Contraseña Inicial'}" class="w-full p-2.5 border border-slate-300 rounded-lg text-sm font-medium focus:border-dark outline-none">
                        ${centroSelectHTML}
                    </div>`,
                showCancelButton: true, confirmButtonText: isEdit ? 'Actualizar' : 'Guardar',
                buttonsStyling: false, width: '400px',
                customClass: { confirmButton: 'px-5 py-2.5 bg-dark text-white rounded-lg font-semibold hover:bg-dark-hover ml-2', cancelButton: 'px-5 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-lg font-semibold hover:bg-slate-50' },
                preConfirm: () => {
                    const params = { action: isEdit ? 'updateUsuario' : 'saveUsuario', nombre: document.getElementById('nuNom').value, cedula: document.getElementById('nuCed').value, rol: document.getElementById('nuRol').value };
                    if(isEdit) params.id = id;
                    
                    const passInput = document.getElementById('nuPass').value;
                    if(passInput) params.pass = passInput; // Si escribe contraseña se envía, si no (en update), se ignora en el backend

                    if(isSuperAdmin) params.centro_id = document.getElementById('nuCentro').value;
                    return params;
                }
            }).then(handleFormSubmit(loadUsuarios));
        }

        function editUsuario(id, nombre, cedula, rol) { openUsuarioModal(id, nombre, cedula, rol); }

        function crearCentro(id = null, nombre = '', direccion = '') {
            const isEdit = id !== null;
            Swal.fire({
                title: isEdit ? 'Editar Centro' : 'Nuevo Centro',
                html: `
                    <div class="space-y-3 mt-4 text-left">
                        <input id="ncNom" type="text" value="${nombre}" placeholder="Nombre de la Sucursal" class="w-full p-3 border border-slate-300 rounded-lg focus:border-dark font-medium uppercase text-sm outline-none">
                        <input id="ncDir" type="text" value="${direccion}" placeholder="Dirección Física (Opcional)" class="w-full p-3 border border-slate-300 rounded-lg focus:border-dark font-medium text-sm outline-none">
                    </div>`,
                showCancelButton: true, confirmButtonText: 'Guardar',
                buttonsStyling: false, width: '400px',
                customClass: { confirmButton: 'px-5 py-2.5 bg-dark text-white rounded-lg font-semibold hover:bg-dark-hover ml-2', cancelButton: 'px-5 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-lg font-semibold hover:bg-slate-50' },
                preConfirm: () => ({ action: isEdit ? 'updateCentro' : 'saveCentro', id: id, nombre: document.getElementById('ncNom').value, direccion: document.getElementById('ncDir').value })
            }).then(handleFormSubmit(loadCentros));
        }
        function editCentro(id, nombre, direccion) { crearCentro(id, nombre, direccion); }

        function openCargoModal() {
            Swal.fire({
                title: 'Nuevo Cargo',
                html: `
                    <div class="mt-4 text-left">
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div onclick="document.getElementById('cgMod').value='TRANSPORTE'; document.querySelectorAll('.cg-card').forEach(e=>e.classList.remove('selected')); this.classList.add('selected');" class="cg-card selectable-card selected p-3 rounded-lg text-center"><i class="fas fa-truck text-lg mb-1 text-slate-500"></i><br><span class="font-semibold text-xs text-slate-700">Transporte</span></div>
                            <div onclick="document.getElementById('cgMod').value='ALMACEN'; document.querySelectorAll('.cg-card').forEach(e=>e.classList.remove('selected')); this.classList.add('selected');" class="cg-card selectable-card p-3 rounded-lg text-center"><i class="fas fa-boxes-stacked text-lg mb-1 text-slate-500"></i><br><span class="font-semibold text-xs text-slate-700">Almacén</span></div>
                        </div>
                        <input type="hidden" id="cgMod" value="TRANSPORTE">
                        <input id="cgNom" type="text" placeholder="Nombre del Cargo" class="w-full p-3 border border-slate-300 rounded-lg focus:border-dark font-medium text-sm outline-none">
                    </div>`,
                showCancelButton: true, confirmButtonText: 'Guardar',
                buttonsStyling: false, width: '400px',
                customClass: { confirmButton: 'px-5 py-2.5 bg-dark text-white rounded-lg font-semibold hover:bg-dark-hover ml-2', cancelButton: 'px-5 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-lg font-semibold hover:bg-slate-50' },
                preConfirm: () => ({ action: 'saveCargo', modulo: document.getElementById('cgMod').value, nombre: document.getElementById('cgNom').value })
            }).then(handleFormSubmit(loadCargos));
        }

        // ================= HANDLERS GLOBALES =================
        const handleFormSubmit = (reloadFunction) => (result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                for (const key in result.value) { if(result.value[key] !== null) fd.append(key, result.value[key]); }
                fetch(API_URL, { method: 'POST', body: fd }).then(r => r.json()).then(data => {
                    if (data.success) { Swal.fire({title: 'Guardado', text: data.message, icon: 'success', timer: 1500, showConfirmButton: false}); reloadFunction(); }
                    else Swal.fire({title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#0f172a'});
                });
            }
        };

        // Modal para actualizar el registro en sí (Pestaña 1)
        function editRegistro(id) {
            fetch(`${API_URL}?action=getRegistro&id=${id}`).then(r => r.json()).then(res => {
                if(!res.success) return Swal.fire('Error', 'No se pudo cargar el registro', 'error');
                const d = res.data;
                const isOp = d.tipo_registro === 'OPERARIO' || d.tipo_registro === 'CONDUCTOR';

                let contentHtml = '';
                if(isOp) {
                    contentHtml = `
                        <input id="edit_nombre" type="text" value="${d.nombre}" class="w-full p-3 mb-3 border border-slate-300 rounded-lg uppercase font-medium focus:border-dark outline-none text-sm">
                        <input id="edit_cedula" type="text" value="${d.cedula}" class="w-full p-3 mb-3 border border-slate-300 rounded-lg font-medium focus:border-dark outline-none text-sm" readonly title="La cédula no se puede cambiar aquí">
                        <input id="edit_cargo" type="text" value="${d.cargo}" class="w-full p-3 border border-slate-300 rounded-lg font-medium focus:border-dark outline-none text-sm" readonly title="El cargo no se puede cambiar tras crearlo">
                    `;
                } else {
                    contentHtml = `<input id="edit_placa" type="text" value="${d.placa}" class="w-full p-3 border border-slate-300 rounded-lg uppercase font-bold text-center tracking-widest focus:border-dark outline-none">`;
                }

                Swal.fire({
                    title: 'Editar ' + d.tipo_registro,
                    html: `
                        <div class="mb-4 text-left p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-500 font-bold">
                            <i class="fas fa-info-circle mr-1"></i> Para cambiar la foto de perfil, dale clic a la foto en la tabla de registros.
                        </div>
                        ${contentHtml}
                    `,
                    showCancelButton: true, confirmButtonText: 'Actualizar', cancelButtonText: 'Cancelar',
                    buttonsStyling: false, customClass: { confirmButton: 'px-5 py-2.5 bg-dark text-white rounded-lg font-semibold hover:bg-dark-hover ml-2', cancelButton: 'px-5 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-lg font-semibold hover:bg-slate-50' },
                    preConfirm: () => {
                        const fd = new FormData();
                        fd.append('action', 'updateRegistro');
                        fd.append('id', id);
                        fd.append('tipo', d.tipo_registro);
                        fd.append('modulo', d.modulo);
                        fd.append('contratista', d.tipo_contratista);
                        if(isOp) {
                            fd.append('nombre', document.getElementById('edit_nombre').value.toUpperCase());
                            fd.append('cedula', document.getElementById('edit_cedula').value);
                            fd.append('cargo', document.getElementById('edit_cargo').value);
                        } else {
                            fd.append('placa', document.getElementById('edit_placa').value.toUpperCase());
                        }
                        return fd;
                    }
                }).then(res => {
                    if(res.isConfirmed) {
                        Swal.fire({ title: 'Actualizando...', didOpen: () => Swal.showLoading() });
                        fetch(API_URL, { method: 'POST', body: res.value }).then(r=>r.json()).then(data => {
                            if(data.success) { Swal.fire({title: 'Éxito', text: 'Registro actualizado', icon: 'success', timer: 1500, showConfirmButton: false}); loadRegistros(); loadUsuarios(); }
                            else Swal.fire('Error', data.message, 'error');
                        });
                    }
                });
            });
        }

        // Eliminación unificada (que ahora borrará en cascada desde backend)
        function deleteItem(action, id, reloadFunction) {
            Swal.fire({ 
                title: '¿Estás seguro?', text: "Esta acción es irreversible y eliminará todos los datos vinculados.", icon: 'warning', 
                showCancelButton: true, confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar',
                buttonsStyling: false,
                customClass: { confirmButton: 'px-5 py-2.5 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 ml-2', cancelButton: 'px-5 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-lg font-semibold hover:bg-slate-50' }
            })
            .then(r => {
                if (r.isConfirmed) {
                    fetch(`${API_URL}?action=${action}&id=${id}`).then(res => res.json()).then(data => {
                        if (data.success) { Swal.fire({title:'Eliminado', icon:'success', timer: 1000, showConfirmButton:false}); reloadFunction(); loadRegistros(); loadUsuarios(); }
                        else Swal.fire({title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#0f172a'});
                    });
                }
            });
        }
    </script>
</body>
</html>