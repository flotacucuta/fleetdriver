<?php
session_start();
// Validación estricta de seguridad: Solo ADMIN y SUPERADMIN pueden estar aquí
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['rol'], ['ADMIN', 'SUPERADMIN'])) {
    header("Location: index.php");
    exit();
}
$modulo = isset($_GET['modulo']) ? strtoupper($_GET['modulo']) : 'TRANSPORTE';$tipo = isset($_GET['tipo']) ? strtoupper($_GET['tipo']) : 'CONDUCTOR';

// Validar que el módulo y el tipo sean correctos
if (!in_array($modulo, ['TRANSPORTE', 'ALMACEN']) || !in_array($tipo, ['CONDUCTOR', 'VEHICULO', 'OPERARIO'])) {
    header('Location: index.php');
    exit;
}
$iconoModulo =$modulo === 'TRANSPORTE' ? '<i class="fas fa-truck-fast"></i>' : '<i class="fas fa-boxes-stacked"></i>';
$colorAcento =$modulo === 'TRANSPORTE' ? '#F5A623' : '#3b82f6'; 

// Si entra al módulo Almacén con un tipo "Conductor", lo pasamos a "Operario"
if ($modulo === 'ALMACEN' && $tipo === 'CONDUCTOR') {$tipo = 'OPERARIO';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspecciones - <?php echo $modulo; ?> - FleeDriver</title>
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
        
        .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        .inspection-table { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 1200px; }
        .inspection-table th, .inspection-table td { border-bottom: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; padding: 12px 10px; text-align: center; white-space: nowrap; position: relative; }
        .inspection-table thead th { background: #1e293b; color: #fff; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; position: sticky; top: 0; z-index: 10; }
        
        .sticky-col { position: sticky; left: 0; background: #f8fafc !important; z-index: 11 !important; border-right: 2px solid #cbd5e1 !important; text-align: left !important; box-shadow: 5px 0 15px rgba(0,0,0,0.05); }
        .inspection-table thead .sticky-col { background: #0f172a !important; color: <?php echo $colorAcento; ?>; z-index: 12 !important; border-right: 2px solid #334155 !important; }
        
        .entity-name { font-weight: 800; color: #1e293b; font-size: 0.9rem; display: flex; align-items: center; gap: 10px; }
        .entity-id { font-size: 0.75rem; color: #64748b; font-weight: 600; margin-top: 2px; }
        
        .cell-interactive { cursor: pointer; transition: all 0.2s ease; user-select: none; font-size: 1.2rem; }
        .cell-interactive:hover { background: #f1f5f9; box-shadow: inset 0 0 0 2px <?php echo $colorAcento; ?>; }
        
        .state-check { background-color: #d1fae5 !important; color: #10b981; }
        .state-cross { background-color: #fecaca !important; color: #ef4444; }
        
        .warning-week { 
            background-color: #ffedd5;
            box-shadow: inset 0 0 0 2px #f97316;
        }
        .warning-week::after {
            content: '⚠️';
            position: absolute;
            top: 2px;
            right: 2px;
            font-size: 0.6rem;
        }

        .inspection-table tbody tr:hover td:not(.state-check):not(.state-cross):not(.warning-week) { background-color: #f8fafc; }
        .inspection-table tbody tr:hover .sticky-col { background-color: #f1f5f9 !important; }

        @keyframes popIn { 0% { transform: scale(0.5); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
        .icon-pop { animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
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
                    <h1 class="text-2xl font-bold text-white tracking-tight">MÓDULO <?php echo $modulo; ?> - INSPECCIONES</h1>
                    <p class="text-sm font-medium text-slate-400">Control Semanal</p>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <a href="index.php" class="bg-white text-dark px-6 py-2.5 rounded-xl font-bold hover:bg-slate-100 transition-colors shadow-sm flex items-center gap-2 border border-slate-200">
                    <i class="fas fa-arrow-left"></i> Volver al Panel
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-full mx-auto px-6 py-8 flex flex-col">
        
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="bg-slate-100 p-2.5 rounded-lg text-slate-500"><i class="fas fa-filter"></i></div>
                <h2 class="font-bold text-dark text-lg">Tabla de Inspecciones</h2>
            </div>
            
            <div class="flex items-center gap-3">
                <span class="font-bold text-slate-500 text-sm">Mostrar:</span>
                <select id="tipoSelect" class="bg-slate-50 border-2 border-slate-200 text-dark text-sm rounded-xl focus:border-[<?php echo $colorAcento; ?>] block p-2.5 font-bold outline-none cursor-pointer">
                    <?php if ($modulo === 'TRANSPORTE'): ?>
                        <option value="CONDUCTOR" <?php echo $tipo === 'CONDUCTOR' ? 'selected' : ''; ?>>👤 Conductores</option>
                    <?php else: ?>
                        <option value="OPERARIO" <?php echo $tipo === 'OPERARIO' ? 'selected' : ''; ?>>👤 Operarios</option>
                    <?php endif; ?>
                    <option value="VEHICULO" <?php echo $tipo === 'VEHICULO' ? 'selected' : ''; ?>>🚚 Vehículos</option>
                </select>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden relative">
            <div class="overflow-x-auto custom-scrollbar max-h-[70vh]">
                <table class="inspection-table" id="mainTable" style="display: none;">
                    <thead>
                        <tr id="tableHeader">
                            <th class="sticky-col"><?php echo in_array($tipo, ['CONDUCTOR', 'OPERARIO']) ? 'Personal' : 'Vehículo'; ?></th>
                        </tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                </table>
                
                <div id="loader" class="text-center py-20 text-slate-500">
                    <i class="fas fa-circle-notch fa-spin text-4xl mb-3" style="color: <?php echo $colorAcento; ?>;"></i>
                    <p class="font-bold">Cargando inspecciones...</p>
                </div>
            </div>
        </div>
    </main>

    <script>
        const modulo = '<?php echo $modulo; ?>';
        const tipo = '<?php echo $tipo; ?>';
        const API_URL = 'controllers/api_admin.php';
        
        const numSemanas = 52; 
        const anioActual = new Date().getFullYear();

        document.getElementById('tipoSelect').addEventListener('change', function() {
            window.location.href = `inspeccion.php?modulo=${modulo}&tipo=${this.value}`;
        });

        function generarEncabezadosSemanas() {
            const headerRow = document.getElementById('tableHeader');
            for (let i = 1; i <= numSemanas; i++) {
                const th = document.createElement('th');
                th.innerText = `S${i}`;
                th.title = `Semana ${i}`;
                headerRow.appendChild(th);
            }
        }

        // Obtener el número de semana ISO de una fecha
        function getISOWeek(dateString) {
            if (!dateString) return null;
            const parts = dateString.split('-');
            if (parts.length !== 3) return null;
            
            const year = parseInt(parts[0]);
            if (year !== anioActual) return null; 

            const d = new Date(year, parseInt(parts[1]) - 1, parseInt(parts[2]));
            const dN = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
            dN.setUTCDate(dN.getUTCDate() + 4 - (dN.getUTCDay() || 7));
            const yearStart = new Date(Date.UTC(dN.getUTCFullYear(), 0, 1));
            return Math.ceil((((dN - yearStart) / 86400000) + 1) / 7);
        }

        function aplicarEstadoVisual(cell, state) {
            cell.setAttribute('data-state', state);
            cell.classList.remove('state-check', 'state-cross');
            
            if (state === 'check') {
                cell.classList.add('state-check');
                cell.innerHTML = '<i class="fas fa-check icon-pop"></i>';
            } else if (state === 'cross') {
                cell.classList.add('state-cross');
                cell.innerHTML = '<i class="fas fa-times icon-pop"></i>';
            } else {
                cell.innerHTML = '';
            }
        }

        function toggleCell(cell) {
            const currentState = cell.getAttribute('data-state') || 'empty';
            let newState = 'empty';
            
            if (currentState === 'empty') newState = 'check';
            else if (currentState === 'check') newState = 'cross';
            else newState = 'empty';

            aplicarEstadoVisual(cell, newState);
            
            // Guardar en la base de datos
            const id = cell.getAttribute('data-id');
            const semana = cell.getAttribute('data-semana');

            const fd = new FormData();
            fd.append('action', 'saveInspeccion');
            fd.append('modulo', modulo);
            fd.append('tipo', tipo);
            fd.append('identificador', id);
            fd.append('semana', semana);
            fd.append('estado', newState);

            fetch(API_URL, { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                if(!data.success) {
                    Swal.fire({toast: true, position: 'top-end', icon: 'error', title: 'No se guardó el cambio', showConfirmButton: false, timer: 3000});
                }
            })
            .catch(error => console.error("Error guardando:", error));
        }

        async function loadDatos() {
            try {
                // Hacemos peticiones para traer registros e inspecciones guardadas
                const resFlota = await fetch(`${API_URL}?action=getRegistros`);
                const dataFlota = await resFlota.json();
                
                const resInspecciones = await fetch(`${API_URL}?action=getInspecciones&modulo=${modulo}&tipo=${tipo}`);
                const dataInspecciones = await resInspecciones.json();

                if (dataFlota.success) {
                    // Filtrar los datos locales para que coincidan con la vista actual
                    const registrosVisibles = dataFlota.data.filter(item => {
                        const itemModulo = item.modulo ? String(item.modulo).trim().toUpperCase() : 'TRANSPORTE';
                        const itemTipo = item.tipo_registro ? String(item.tipo_registro).trim().toUpperCase() : '';
                        
                        const matchModulo = (itemModulo === modulo);
                        const matchTipo = (tipo === 'VEHICULO') ? (itemTipo === 'VEHICULO') : (['CONDUCTOR', 'OPERARIO'].includes(itemTipo));
                        
                        return matchModulo && matchTipo;
                    });

                    document.getElementById('loader').style.display = 'none';
                    const table = document.getElementById('mainTable');
                    const tbody = document.getElementById('tableBody');

                    if (registrosVisibles.length > 0) {
                        table.style.display = 'table';
                        
                        // Mapa de inspecciones
                        const estadosGuardados = {};
                        if (dataInspecciones.success && dataInspecciones.data) {
                            dataInspecciones.data.forEach(ins => {
                                estadosGuardados[`${ins.identificador}_${ins.semana}`] = ins.estado;
                            });
                        }

                        // Ordenar
                        const sortedData = registrosVisibles.sort((a, b) => {
                            const nameA = (['CONDUCTOR', 'OPERARIO'].includes(tipo) ? a.nombre : a.placa) || '';
                            const nameB = (['CONDUCTOR', 'OPERARIO'].includes(tipo) ? b.nombre : b.placa) || '';
                            return nameA.localeCompare(nameB);
                        });

                        // Construir Filas (incluyendo cálculo de documentos para Alerta de Vencimiento)
                        for(let item of sortedData) {
                            const tr = document.createElement('tr');
                            
                            // 1. Petición de documentos para identificar semanas de vencimiento
                            const docRes = await fetch(`${API_URL}?action=getDocumentos&id=${item.id}`);
                            const docData = await docRes.json();
                            const semanasVencimiento = new Set();
                            if (docData.success && docData.data.length > 0) {
                                docData.data.forEach(doc => {
                                    const numSemana = getISOWeek(doc.fecha_vencimiento);
                                    if (numSemana) semanasVencimiento.add(numSemana);
                                });
                            }

                            // 2. Columna Estática
                            const tdFija = document.createElement('td');
                            tdFija.className = 'sticky-col';
                            
                            const isOp = ['CONDUCTOR', 'OPERARIO'].includes(tipo);
                            const principalTexto = isOp ? item.nombre : item.placa;
                            const secundarioTexto = isOp ? `ID: ${item.cedula}` : `Clasificación: ${item.cargo}`;
                            const iconClass = isOp ? 'fa-user-tie' : 'fa-truck';
                            const identifier = isOp ? item.cedula : item.placa;
                            
                            tdFija.innerHTML = `
                                <div class="entity-name">
                                    <i class="fas ${iconClass}" style="color: <?php echo $colorAcento; ?>;"></i>
                                    ${principalTexto}
                                </div>
                                ${secundarioTexto ? `<div class="entity-id">${secundarioTexto}</div>` : ''}
                            `;
                            tr.appendChild(tdFija);

                            // 3. Generar las 52 semanas
                            for (let i = 1; i <= numSemanas; i++) {
                                const td = document.createElement('td');
                                td.className = 'cell-interactive';
                                td.setAttribute('data-id', identifier);
                                td.setAttribute('data-semana', i);
                                
                                if (semanasVencimiento.has(i)) {
                                    td.classList.add('warning-week');
                                    td.title = "Un documento vence esta semana";
                                }
                                
                                const estadoPrevio = estadosGuardados[`${identifier}_${i}`] || 'empty';
                                aplicarEstadoVisual(td, estadoPrevio);
                                
                                td.addEventListener('click', function() { toggleCell(this); });
                                tr.appendChild(td);
                            }
                            
                            tbody.appendChild(tr);
                        }
                    } else {
                        document.getElementById('loader').style.display = 'block';
                        document.getElementById('loader').innerHTML = `<i class="fas fa-inbox text-4xl mb-3 text-slate-300"></i><br><span class="text-slate-500">No hay registros asignados a este módulo.</span>`;
                    }
                }
            } catch (error) {
                console.error(error);
                document.getElementById('loader').innerHTML = `<i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-3"></i><br>Error cargando la información.`;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            generarEncabezadosSemanas();
            loadDatos();
        });
    </script>
</body>
</html>