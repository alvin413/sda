-- SCRIPT: schema_gestion_alarmas.sql
-- Crea la base de datos y tablas para "gestion_alarmas"
DROP DATABASE IF EXISTS `gestion_alarmas`;

CREATE DATABASE IF NOT EXISTS `gestion_alarmas`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;
USE `gestion_alarmas`;

-- ===================================================================
-- Tablas de catálogos
-- ===================================================================
CREATE TABLE IF NOT EXISTS ciudades (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL UNIQUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS servicios_rondines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL UNIQUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bunkers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  ciudad_id INT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE (nombre, ciudad_id),
  CONSTRAINT fk_bunker_ciudad FOREIGN KEY (ciudad_id) REFERENCES ciudades(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS jaulas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  bunker_id INT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE (nombre, bunker_id),
  CONSTRAINT fk_jaula_bunker FOREIGN KEY (bunker_id) REFERENCES bunkers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS racks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  jaula_id INT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE (nombre, jaula_id),
  CONSTRAINT fk_rack_jaula FOREIGN KEY (jaula_id) REFERENCES jaulas(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS clientes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(200) NOT NULL UNIQUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS marcas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL UNIQUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS modelos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(200) NOT NULL,
  marca_id INT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE (marca_id, nombre),
  CONSTRAINT fk_modelo_marca FOREIGN KEY (marca_id) REFERENCES marcas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===================================================================
-- Tabla principal: servidores
-- ===================================================================
CREATE TABLE IF NOT EXISTS servidores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ciudad_id INT NOT NULL,
  bunker_id INT NOT NULL,
  jaula_id INT NOT NULL,
  rack_id INT NULL,
  unidad_rack VARCHAR(50) NULL,
  cliente_id INT NOT NULL,
  hostname VARCHAR(255) NOT NULL,
  marca_id INT NOT NULL,
  modelo_id INT NOT NULL,
  no_serie VARCHAR(200) NOT NULL,
  cpu VARCHAR(200) NULL,
  ip_ilo VARCHAR(15) NULL,
  ilo_user VARCHAR(150) NULL,
  ilo_password TEXT NULL,
  ci VARCHAR(100) NULL,
  rfc_alta VARCHAR(100) NOT NULL,
  rfc_baja VARCHAR(100) NULL,
  fecha_garantia DATE NULL,
  estado ENUM('activo', 'baja') NOT NULL DEFAULT 'activo',
  fecha_cambio_estado TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  disabled_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE (no_serie),
  INDEX idx_hostname (hostname),
  CONSTRAINT chk_ip_ilo_ipv4 CHECK (
    ip_ilo IS NULL OR
    ip_ilo REGEXP '^((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])\\.){3}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])$'
  ),
  -- Llaves foráneas
  CONSTRAINT fk_servidores_ciudad FOREIGN KEY (ciudad_id) REFERENCES ciudades(id),
  CONSTRAINT fk_servidores_bunker FOREIGN KEY (bunker_id) REFERENCES bunkers(id),
  CONSTRAINT fk_servidores_jaula FOREIGN KEY (jaula_id) REFERENCES jaulas(id),
  CONSTRAINT fk_servidores_rack FOREIGN KEY (rack_id) REFERENCES racks(id),
  CONSTRAINT fk_servidores_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  CONSTRAINT fk_servidores_marca FOREIGN KEY (marca_id) REFERENCES marcas(id),
  CONSTRAINT fk_servidores_modelo FOREIGN KEY (modelo_id) REFERENCES modelos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===================================================================
-- Tabla users
-- ===================================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE rondines_racks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ciudad_id INT NOT NULL,
    bunker_id INT NOT NULL,
    jaula_id INT NOT NULL,
    rack_id INT NOT NULL,
    llave INT NOT NULL,
    cliente_id INT NOT NULL,
    rfc_solicitante VARCHAR(20) NOT NULL,
    rfc_baja VARCHAR(20) NULL,
    lider_proyecto VARCHAR(100) NOT NULL,
    fecha_alta DATE NOT NULL COMMENT 'Fecha inicio servicio rondín',
    fecha_baja DATE NULL COMMENT 'Fecha fin servicio rondín',
    usuario_registro INT NOT NULL,
    observaciones TEXT NULL,
    servicios_contratados TEXT NOT NULL,
    usuario_baja INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ciudad_id) REFERENCES ciudades(id),
    FOREIGN KEY (bunker_id) REFERENCES bunkers(id),
    FOREIGN KEY (jaula_id) REFERENCES jaulas(id),
    FOREIGN KEY (rack_id) REFERENCES racks(id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (usuario_registro) REFERENCES usuarios(id),
    FOREIGN KEY (usuario_baja) REFERENCES usuarios(id)
);

-- Tabla para tipos de alarma
CREATE TABLE tipos_alarma (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla para estados de alarma
CREATE TABLE estados_alarma (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO servicios_rondines (nombre) VALUES ('Manos Remotas'),('Administrado'),('Arrendado'),('Rondines'),('Acceso a Proveedores'),('Levantar Casos');

INSERT INTO estados_alarma (nombre) VALUES ('Reportada'),('Resuelta');

INSERT INTO ciudades (id, nombre)VALUES ('1','SITIO CLIENTE');
INSERT INTO bunkers (id,nombre, ciudad_id) VALUES ('1','SITIO CLIENTE','1');
INSERT INTO jaulas (id,nombre, bunker_id) VALUES ('1','SITIO CLIENTE','1');
INSERT INTO racks (id,nombre, jaula_id) VALUES ('1','SITIO CLIENTE','1');



-- Tabla principal de alarmas
CREATE TABLE alarmas_servidores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    servidor_id INT NULL,
    no_serie VARCHAR(50) NULL,
    ciudad_id INT NULL,
    bunker_id INT NULL,
    jaula_id INT NULL,
    rack_id INT NULL,
    cliente_id INT(11) DEFAULT NULL,
    marca_id INT(11) DEFAULT NULL,
    modelo_id INT(11) DEFAULT NULL,
    ubicacion_manual VARCHAR(255) NULL,
    tipo_alarma_id INT NOT NULL,
    estado_alarma_id INT NOT NULL,
    fecha_deteccion DATETIME NOT NULL,
    fecha_resolucion DATETIME NULL,
    descripcion TEXT NOT NULL,
    caso VARCHAR(50) NULL,
    usuario_registro INT NOT NULL,
    resolucion TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (servidor_id) REFERENCES servidores(id),
    FOREIGN KEY (ciudad_id) REFERENCES ciudades(id),
    FOREIGN KEY (bunker_id) REFERENCES bunkers(id),
    FOREIGN KEY (jaula_id) REFERENCES jaulas(id),
    FOREIGN KEY (rack_id) REFERENCES racks(id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (marca_id) REFERENCES marcas(id),
    FOREIGN KEY (modelo_id) REFERENCES modelos(id),
    FOREIGN KEY (tipo_alarma_id) REFERENCES tipos_alarma(id),
    FOREIGN KEY (estado_alarma_id) REFERENCES estados_alarma(id),
    FOREIGN KEY (usuario_registro) REFERENCES usuarios(id)
);

