<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: /alarmas/auth/login.php");
    exit;
}

require_once __DIR__ . '/../config/security.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar servidores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --primary-dark: #3a56c0;
            --secondary-color: #f8f9fc;
            --accent-color: #36b9cc;
            --text-primary: #2e384d;
            --text-secondary: #8798ad;
            --border-color: #e3e8f0;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --card-shadow: 0 0.35rem 1.5rem rgba(58, 59, 69, 0.15);
            --transition: all 0.3s ease;
        }
        
        body {
            background-color: var(--secondary-color);
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-primary);
            line-height: 1.6;
        }
        
        .card {
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow);
            border: none;
            transition: var(--transition);
        }
        
        .card:hover {
            box-shadow: 0 0.5rem 2rem rgba(58, 59, 69, 0.2);
        }
        
        .card-header {
            background: linear-gradient(120deg, var(--primary-color), var(--primary-dark));
            color: white;
            border-radius: 0.75rem 0.75rem 0 0 !important;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .btn-new {
            background: white;
            color: var(--primary-color);
            border: none;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .btn-new:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            background: var(--primary-color);
            color: white;
        }
        
        .upload-area {
            border: 2px dashed var(--border-color);
            border-radius: 0.75rem;
            padding: 2rem;
            text-align: center;
            transition: var(--transition);
            background-color: white;
        }
        
        .upload-area:hover, .upload-area.dragover {
            border-color: var(--primary-color);
            background-color: rgba(78, 115, 223, 0.05);
        }
        
        .upload-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .template-download {
            color: var(--primary-color);
            text-decoration: none;
            transition: var(--transition);
        }
        
        .template-download:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .progress {
            height: 0.5rem;
            border-radius: 0.25rem;
        }
        
        .results-container {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .result-item {
            padding: 0.75rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .result-item:last-child {
            border-bottom: none;
        }
        
        .success-item {
            background-color: rgba(28, 200, 138, 0.1);
            border-left: 3px solid var(--success-color);
        }
        
        .error-item {
            background-color: rgba(231, 74, 59, 0.1);
            border-left: 3px solid var(--danger-color);
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid py-4 fade-in">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center py-3">
            <h4 class="mb-0"><i class="fas fa-file-import me-2"></i>Importar servidores</h4>
            <div class="d-flex align-items-center">
                <a href="/alarmas/dashboard/listado_servidores.php" class="btn btn-new me-2">
                    <i class="fas fa-list me-2"></i>Ver Listado
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="upload-area mb-4" id="uploadArea">
                        <div class="upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <h5>Arrastra y suelta tu archivo Excel aquí</h5>
                        <p class="text-muted">O haz clic para seleccionar un archivo (.xlsx)</p>
                        <input type="file" id="excelFile" accept=".xlsx" class="d-none">
                        <button class="btn btn-primary mt-2" onclick="document.getElementById('excelFile').click()">
                            <i class="fas fa-folder-open me-2"></i>Seleccionar Archivo
                        </button>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Instrucciones:</h6>
                        <ul class="mb-0">
                            <li>Descarga la plantilla Excel para asegurar el formato correcto</li>
                            <li>El archivo debe tener encabezados en la primera fila</li>
                            <li>Los campos en <strong>negrita</strong> son obligatorios</li>
                            <li>Ingrese los <strong>nombres</strong> de ciudad, bunker, jaula, cliente, marca y modelo (no los IDs)</li>
                            <li>El formato de fecha debe ser YYYY-MM-DD</li>
                        </ul>
                    </div>
                    
                    <div class="text-center mb-4">
                        <a href="/alarmas/api/template_servidores.php" class="template-download">
                            <i class="fas fa-download me-2"></i>Descargar Plantilla Excel
                        </a>
                    </div>
                    
                    <div id="uploadProgress" class="d-none">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Procesando archivo...</span>
                            <span id="progressPercent">0%</span>
                        </div>
                        <div class="progress mb-4">
                            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                    
                    <div id="results" class="d-none">
                        <h5 class="mb-3">Resultados de la Importación</h5>
                        <div class="results-container mb-4" id="resultsContainer"></div>
                        <div class="d-flex justify-content-between">
                            <span id="successCount" class="text-success"></span>
                            <span id="errorCount" class="text-danger"></span>
                        </div>
                        <div class="text-center mt-4">
                            <button id="btnNewImport" class="btn btn-primary me-2">
                                <i class="fas fa-plus me-2"></i>Nueva Importación
                            </button>
                            <a href="/alarmas/dashboard/listado_servidores.php" class="btn btn-success">
                                <i class="fas fa-list me-2"></i>Ver Servidores Importados
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    const uploadArea = $('#uploadArea');
    const fileInput = $('#excelFile');
    const uploadProgress = $('#uploadProgress');
    const progressBar = $('#progressBar');
    const progressPercent = $('#progressPercent');
    const results = $('#results');
    const resultsContainer = $('#resultsContainer');
    const successCount = $('#successCount');
    const errorCount = $('#errorCount');
    const btnNewImport = $('#btnNewImport');
    
    // Drag and drop functionality
    uploadArea.on('dragover', function(e) {
        e.preventDefault();
        uploadArea.addClass('dragover');
    });
    
    uploadArea.on('dragleave', function() {
        uploadArea.removeClass('dragover');
    });
    
    uploadArea.on('drop', function(e) {
        e.preventDefault();
        uploadArea.removeClass('dragover');
        const files = e.originalEvent.dataTransfer.files;
        if (files.length) {
            handleFile(files[0]);
        }
    });
    
    fileInput.on('change', function() {
        if (this.files.length) {
            handleFile(this.files[0]);
        }
    });
    
    // En la función handleFile, mejora el manejo de errores:
function handleFile(file) {
    // Check file type
    const validExtensions = ['.xlsx'];
    const fileExtension = file.name.substring(file.name.lastIndexOf('.')).toLowerCase();

    if (!validExtensions.includes(fileExtension)) {
        alert('Por favor, selecciona un archivo Excel válido (.xlsx)');
        return;
    }
    
    // Prepare and upload file
    const formData = new FormData();
    formData.append('file', file);
    
    uploadProgress.removeClass('d-none');
    results.addClass('d-none');
    
    $.ajax({
        url: '/alarmas/api/import_servidores.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhr: function() {
            const xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    progressBar.css('width', percent + '%');
                    progressPercent.text(percent + '%');
                }
            });
            return xhr;
        },
        success: function(response) {
            try {
                // Verificar si la respuesta es JSON válido
                if (typeof response === 'string') {
                    response = JSON.parse(response);
                }
                displayResults(response);
            } catch (e) {
                console.error('Error parsing response:', response);
                showError('Error al procesar la respuesta del servidor. Detalles en consola.');
            }
        },
        error: function(xhr, status, error) {
            let errorMsg = 'Error al subir el archivo';
            
            try {
                // Intentar parsear error como JSON
                const errorData = JSON.parse(xhr.responseText);
                errorMsg = errorData.message || errorMsg;
            } catch (e) {
                // Si no es JSON, mostrar el error crudo
                if (xhr.responseText) {
                    errorMsg = 'Error del servidor: ' + xhr.responseText.substring(0, 200);
                } else {
                    errorMsg = 'Error de conexión: ' + error;
                }
            }
            
            showError(errorMsg);
        }
    });
}
    
    function displayResults(data) {
    uploadProgress.addClass('d-none');
    results.removeClass('d-none');
    
    resultsContainer.empty();
    
    // Mostrar registros insertados correctamente
    if (data.success && data.inserted > 0) {
        successCount.html(`<i class="fas fa-check-circle me-1"></i> ${data.message}`);
    } else {
        successCount.html('');
    }
    
    // Mostrar errores
    if (data.errors && data.errors.length > 0) {
        errorCount.html(`<i class="fas fa-exclamation-circle me-1"></i> ${data.errors.length} errores encontrados`);
        
        data.errors.forEach((error, index) => {
            const errorItem = $('<div class="result-item error-item"></div>');
            errorItem.html(`<strong>Fila ${error.row}:</strong> ${error.message} ${error.no_serie ? `(No. Serie: ${error.no_serie})` : ''}`);
            resultsContainer.append(errorItem);
        });
    } else {
        errorCount.html('');
    }
    
    // Mostrar filas omitidas (skipped)
    if (data.skipped && data.skipped.length > 0) {
        data.skipped.forEach((skippedRow, index) => {
            const skippedItem = $('<div class="result-item skipped-item" style="background-color: rgba(246, 194, 62, 0.1); border-left: 3px solid var(--warning-color);"></div>');
            
            // Crear mensaje amigable con columnas faltantes
            let missingMsg = Object.entries(skippedRow.missing_columns)
                                   .map(([col, msg]) => msg)
                                   .join(', ');
            
            skippedItem.html(`<strong>Fila ${skippedRow.row}:</strong> ${missingMsg} ${skippedRow.no_serie ? `(No. Serie: ${skippedRow.no_serie})` : ''}`);
            resultsContainer.append(skippedItem);
        });
    }
    
    // Scroll to results
    $('html, body').animate({
        scrollTop: results.offset().top - 20
    }, 500);
}


    
    function showError(message) {
        uploadProgress.addClass('d-none');
        results.removeClass('d-none');
        
        resultsContainer.empty();
        resultsContainer.append(`<div class="alert alert-danger">${message}</div>`);
        successCount.html('');
        errorCount.html('<i class="fas fa-exclamation-circle me-1"></i> Error en la importación');
    }
    
    btnNewImport.on('click', function() {
        fileInput.val('');
        results.addClass('d-none');
        uploadProgress.addClass('d-none');
    });
});
</script>
</body>
</html>