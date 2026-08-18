-- Time Saver - Organización de trabajo
-- Base de datos y tablas

CREATE DATABASE IF NOT EXISTS timesaver_tasks CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE timesaver_tasks;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    debe_cambiar_password TINYINT(1) NOT NULL DEFAULT 0,
    rol ENUM('admin', 'vendedor') NOT NULL DEFAULT 'vendedor',
    recibir_emails TINYINT(1) NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE proyectos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(180) NOT NULL,
    cliente VARCHAR(180) NOT NULL,
    descripcion TEXT,
    estado ENUM('activo', 'pausado', 'cerrado') NOT NULL DEFAULT 'activo',
    creado_por INT NOT NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE proyecto_miembros (
    proyecto_id INT NOT NULL,
    usuario_id INT NOT NULL,
    PRIMARY KEY (proyecto_id, usuario_id),
    FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE tareas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proyecto_id INT NULL,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    prioridad ENUM('alta', 'media', 'baja') NOT NULL DEFAULT 'media',
    fecha_inicio DATE NULL,
    fecha_limite DATE NULL,
    estado ENUM('pendiente', 'en_curso', 'hecha') NOT NULL DEFAULT 'pendiente',
    asignado_a INT NULL,
    creado_por INT NOT NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    notificado_proximo TINYINT(1) NOT NULL DEFAULT 0,
    notificado_vencido TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE,
    FOREIGN KEY (asignado_a) REFERENCES usuarios(id),
    FOREIGN KEY (creado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE tarea_comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tarea_id INT NOT NULL,
    usuario_id INT NOT NULL,
    mensaje TEXT NOT NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tarea_id) REFERENCES tareas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE decisiones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proyecto_id INT NULL,
    titulo VARCHAR(200) NOT NULL,
    bajada TEXT NOT NULL,
    fecha_limite DATE NOT NULL,
    estado ENUM('abierta', 'resuelta', 'vencida') NOT NULL DEFAULT 'abierta',
    resolucion TEXT NULL,
    resuelto_por INT NULL,
    fecha_resolucion DATETIME NULL,
    creado_por INT NOT NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    notificado_proximo TINYINT(1) NOT NULL DEFAULT 0,
    notificado_vencido TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE,
    FOREIGN KEY (resuelto_por) REFERENCES usuarios(id),
    FOREIGN KEY (creado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE decision_participantes (
    decision_id INT NOT NULL,
    usuario_id INT NOT NULL,
    PRIMARY KEY (decision_id, usuario_id),
    FOREIGN KEY (decision_id) REFERENCES decisiones(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE decision_mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    decision_id INT NOT NULL,
    usuario_id INT NOT NULL,
    mensaje TEXT NOT NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (decision_id) REFERENCES decisiones(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE tarea_acceso (
    tarea_id INT NOT NULL,
    usuario_id INT NOT NULL,
    PRIMARY KEY (tarea_id, usuario_id),
    FOREIGN KEY (tarea_id) REFERENCES tareas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reuniones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    creado_por INT NOT NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE reunion_participantes (
    reunion_id INT NOT NULL,
    usuario_id INT NOT NULL,
    PRIMARY KEY (reunion_id, usuario_id),
    FOREIGN KEY (reunion_id) REFERENCES reuniones(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE adjuntos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_padre ENUM('tarea', 'decision') NOT NULL,
    padre_id INT NOT NULL,
    url VARCHAR(500) NOT NULL,
    descripcion VARCHAR(200) NULL,
    creado_por INT NOT NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo VARCHAR(40) NOT NULL,
    mensaje VARCHAR(300) NOT NULL,
    link VARCHAR(300) NULL,
    leida TINYINT(1) NOT NULL DEFAULT 0,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE actividad_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_entidad ENUM('tarea', 'decision') NOT NULL,
    entidad_id INT NOT NULL,
    usuario_id INT NOT NULL,
    accion VARCHAR(60) NOT NULL,
    detalle VARCHAR(500) NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE INDEX idx_tareas_proyecto ON tareas(proyecto_id);
CREATE INDEX idx_tareas_asignado ON tareas(asignado_a);
CREATE INDEX idx_tareas_limite ON tareas(fecha_limite);
CREATE INDEX idx_decisiones_proyecto ON decisiones(proyecto_id);
CREATE INDEX idx_decisiones_limite ON decisiones(fecha_limite);
CREATE INDEX idx_reuniones_fecha ON reuniones(fecha);
CREATE INDEX idx_actividad_entidad ON actividad_log(tipo_entidad, entidad_id);
CREATE INDEX idx_notificaciones_usuario ON notificaciones(usuario_id, leida);

-- Usuarios iniciales (password: timesaver123 -- se fuerza el cambio en el primer login)
INSERT INTO usuarios (nombre, email, password_hash, debe_cambiar_password, rol) VALUES
('Alejandro Parra', 'ale.dani.parra@gmail.com', '$2y$10$UcrCmJ7ioLfIWgy4AXoIJeZww3RmTukEk9175TqsBrGA5PIcdWFHK', 1, 'admin'),
('Gonzalo Volpe Gomez', 'gonzalomartinvg@gmail.com', '$2y$10$UcrCmJ7ioLfIWgy4AXoIJeZww3RmTukEk9175TqsBrGA5PIcdWFHK', 1, 'admin'),
('Nahuel Fusco', 'nahuel9707@gmail.com', '$2y$10$UcrCmJ7ioLfIWgy4AXoIJeZww3RmTukEk9175TqsBrGA5PIcdWFHK', 1, 'vendedor');
