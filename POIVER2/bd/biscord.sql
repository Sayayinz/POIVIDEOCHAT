CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
    correo VARCHAR(100) NOT NULL UNIQUE,
    contra VARCHAR(255) NOT NULL,
    avatar_url VARCHAR(255) DEFAULT 'avatar_predeterminado.png',
    estado ENUM('conectado', 'desconectado', 'ausente', 'ocupado') DEFAULT 'desconectado',
    ultima_conexion TIMESTAMP,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    puntos_recompensa INT DEFAULT 0  -- Para el sistema de recompensas
);

CREATE TABLE grupos (
    id_grupo INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    id_creador INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_creador) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

CREATE TABLE miembros_grupo (
    id_grupo INT NOT NULL,
    id_usuario INT NOT NULL,
    fecha_union TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_grupo, id_usuario),
    FOREIGN KEY (id_grupo) REFERENCES grupos(id_grupo) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

CREATE TABLE chats_privados (
    id_chat_privado INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario1 INT NOT NULL,
    id_usuario2 INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario1) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario2) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

CREATE TABLE mensajes (
    id_mensaje INT AUTO_INCREMENT PRIMARY KEY,
    id_remitente INT NOT NULL,
    id_grupo INT,  -- NULL si es chat privado
    id_chat_privado INT,  -- NULL si es grupal
    contenido TEXT,
    url_adjunto VARCHAR(255),
    tipo_adjunto ENUM('imagen', 'video', 'audio', 'archivo', 'ubicacion') NULL,
    esta_cifrado BOOLEAN DEFAULT FALSE,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_remitente) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_grupo) REFERENCES grupos(id_grupo) ON DELETE CASCADE,
    FOREIGN KEY (id_chat_privado) REFERENCES chats_privados(id_chat_privado) ON DELETE CASCADE,
    CHECK (
        (id_grupo IS NOT NULL AND id_chat_privado IS NULL) OR
        (id_grupo IS NULL AND id_chat_privado IS NOT NULL)
    )
);

CREATE TABLE videollamadas (
    id_videollamada INT AUTO_INCREMENT PRIMARY KEY,
    id_chat_privado INT NOT NULL,
    id_iniciador INT NOT NULL,
    hora_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    hora_fin TIMESTAMP NULL,
    FOREIGN KEY (id_chat_privado) REFERENCES chats_privados(id_chat_privado) ON DELETE CASCADE,
    FOREIGN KEY (id_iniciador) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

CREATE TABLE tareas (
    id_tarea INT AUTO_INCREMENT PRIMARY KEY,
    id_grupo INT NOT NULL,
    id_creador INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    id_asignado INT NOT NULL,  -- Usuario asignado
    esta_completada BOOLEAN DEFAULT FALSE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_completado TIMESTAMP NULL,
    FOREIGN KEY (id_grupo) REFERENCES grupos(id_grupo) ON DELETE CASCADE,
    FOREIGN KEY (id_creador) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_asignado) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

CREATE TABLE recompensas (
    id_recompensa INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,  -- Ej: "Chat Activo", "Líder de Grupo"
    descripcion TEXT,
    icono_url VARCHAR(255),
    fecha_otorgado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

CREATE TABLE notificaciones (
    id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    tipo ENUM('mensaje', 'tarea', 'recompensa', 'videollamada') NOT NULL,
    id_origen INT NOT NULL,  -- ID del mensaje/tarea/recompensa
    fue_leida BOOLEAN DEFAULT FALSE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

CREATE TABLE correos_enviados (
    id_correo INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    asunto VARCHAR(255) NOT NULL,
    contenido TEXT NOT NULL,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);