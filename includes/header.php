<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="<?php echo $_SERVER['PHP_SELF']; ?>">
            <i class="fas fa-bell me-2"></i>Gestión Alarmas
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/alarmas/dashboard/"><i class="fas fa-home me-1"></i> Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/alarmas/dashboard/listado_servidores.php">
                        <i class="fas fa-server me-2"></i>Listado Servidores
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/alarmas/dashboard/listado_alarmas.php">
                        <i class="fas fa-bell me-2"></i>Listado de Alarmas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/alarmas/dashboard/listado_rondines.php">
                        <i class="fas fa-clipboard-check me-2"></i>Rondines
                    </a>
                </li>
            </ul>

            <!-- Barra de búsqueda -->
            <div class="d-flex align-items-center me-3">
                <div class="search-container position-relative">
                    <form id="search-form" class="d-flex">
                        <input type="text" id="header-search" class="form-control form-control-sm" 
                               placeholder="Buscar por nº serie..." aria-label="Buscar servidor">
                        <button class="btn btn-outline-light btn-sm ms-2" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                    <div id="search-results" class="search-results dropdown-menu"></div>
                </div>
            </div>

            <div class="d-flex align-items-center dropdown">
				<a class="nav-link dropdown-toggle d-flex align-items-center text-light" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle me-2"></i><?= htmlspecialchars( explode("@", $_SESSION['username'])[0]);?>
                </a>
				<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
					<li>
						<a class="dropdown-item" href="/alarmas/auth/modificar_password.php">
							<i class="fas fa-key me-2"></i>Modificar contraseña
						</a>
					</li>
					<li><hr class="dropdown-divider"></li>
					<li>
						<a class="dropdown-item" href="/alarmas/auth/reset_password.php">
							<i class="fas fa-key me-2"></i>Reestablecer contraseñas
						</a>
					</li>
					<li><hr class="dropdown-divider"></li>
					<li>
						<a class="dropdown-item" href="/alarmas/auth/logout.php">
							<i class="fas fa-sign-out-alt me-2"></i>Salir
						</a>
					</li>
				</ul>
			</div>
            <div id="header-alerts" class="position-absolute top-0 end-0 p-3" style="z-index: 1050;"></div>
        </div>
    </div>
</nav>

<style>
.search-container {
    width: 300px;
}

.search-results {
    width: 100%;
    max-height: 300px;
    overflow-y: auto;
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 1000;
}

.search-result-item {
    padding: 10px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    display: flex;
    align-items: center;
}

.search-result-item:hover {
    background-color: #f8f9fa;
}

.search-result-item i {
    margin-right: 10px;
    color: #6c757d;
}

.no-results {
    padding: 10px;
    color: #6c757d;
    font-style: italic;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('header-search');
    const searchForm = document.getElementById('search-form');
    const searchResults = document.getElementById('search-results');

    // Función para buscar servidores por número de serie completo
    function searchServidorFull(query) {
        if (!query) {
            searchResults.style.display = 'none';
            return;
        }

        fetch(`/alarmas/api/buscar_servidor.php?no_serie=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                displayResult(data, query);
            })
            .catch(error => {
                console.error('No se localiza servidor:', error);
                searchResults.innerHTML = '<div class="no-results">No se localiza servidor</div>';
                searchResults.style.display = 'block';
            });
    }

    // Mostrar resultado
    function displayResult(data, query) {
    searchResults.innerHTML = '';

    if (data.success && data.data && data.data.length > 0) {
        data.data.forEach(item => {
            const resultItem = document.createElement('div');
            resultItem.className = 'search-result-item';
            resultItem.innerHTML = `
                <i class="fas fa-server"></i>
                <div>
                    <div><strong>${item.no_serie}</strong></div>
                </div>
            `;
            resultItem.addEventListener('click', function() {
                window.location.href = `/alarmas/dashboard/nueva_alarma.php?no_serie=${encodeURIComponent(item.no_serie)}`;
            });
            searchResults.appendChild(resultItem);
        });
    } else {
        searchResults.innerHTML = `
            <div class="search-result-item" id="create-new-alarm">
                <i class="fas fa-plus-circle"></i>
                <div>
                    <div>Crear alarma para: <strong>${query}</strong></div>
                    <small class="text-muted">No se encontró el servidor</small>
                </div>
            </div>
        `;

        document.getElementById('create-new-alarm').addEventListener('click', function() {
            window.location.href = `/alarmas/dashboard/nueva_alarma.php?no_serie=${encodeURIComponent(query)}`;
        });
    }

    searchResults.style.display = 'block';
}


    // Evento al escribir en el input
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        if (query) {
            searchServidorFull(query);
        } else {
            searchResults.style.display = 'none';
        }
    });

    // Evento submit del formulario
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const query = searchInput.value.trim();
        if (query) {
            window.location.href = `/alarmas/dashboard/nueva_alarma.php?no_serie=${encodeURIComponent(query)}`;
        }
    });

    // Ocultar resultados al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!searchForm.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });

    searchResults.addEventListener('click', function(e) {
        e.stopPropagation();
    });
});

</script>