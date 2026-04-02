CREATE TABLE unexca_db.tipos_usuario (
    id_tipo SERIAL PRIMARY KEY,
    nombre_tipo VARCHAR(50) UNIQUE NOT NULL,
	descripcion TEXT
);

CREATE TABLE unexca_db.estatus (
	id_estatus SERIAL PRIMARY KEY,
	nombre_estatus VARCHAR (100) NOT NULL,
	creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE unexca_db.sedes_unexca (
    id_sede SERIAL PRIMARY KEY,
    nombre_sede VARCHAR(100) NOT NULL,
    correo_institucional VARCHAR(100) UNIQUE NOT NULL,
    direccion TEXT
);

CREATE TABLE unexca_db.usuarios (
    id_usuario SERIAL PRIMARY KEY,
    cedula VARCHAR(15) UNIQUE NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    correo_institucional VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    id_tipo INTEGER REFERENCES unexca_db.tipos_usuario(id_tipo),
    activo BOOLEAN DEFAULT FALSE,
    ultimo_login TIMESTAMP,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE unexca_db.pnf (
	id_pnf SERIAL PRIMARY KEY,
	id_sede INTEGER REFERENCES unexca_db.sedes_unexca(id_sede) ON DELETE CASCADE,
	cod_pnf VARCHAR(20) NOT NULL,
	nombre_pnf VARCHAR(100) NOT NULL,
	descripcion TEXT,
	duracion_pnf INTEGER NOT NULL,
	unidad_total_creditos INTEGER NOT NULL,
	creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE unexca_db.trayectos (
	id_trayecto SERIAL PRIMARY KEY,
	id_pnf INTEGER REFERENCES unexca_db.pnf(id_pnf) ON DELETE CASCADE,
	id_periodo INTEGER REFERENCES unexca_db.periodo_academico(id_periodo) ON DELETE CASCADE,
	cod_trayecto VARCHAR(10) NOT NULL,
	creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE unexca_db.unidades_curriculares (
    id_unidad_curricular SERIAL PRIMARY KEY,
    id_pnf INTEGER REFERENCES unexca_db.pnf(id_pnf) ON DELETE CASCADE,
	id_trayecto INTEGER REFERENCES unexca_db.trayectos(id_trayecto) ON DELETE CASCADE,
    codigo VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    unidades_credito INTEGER NOT NULL
);

CREATE TABLE unexca_db.datos_docentes (
    id_docente SERIAL PRIMARY KEY,
    id_usuario INTEGER REFERENCES unexca_db.usuarios(id_usuario) ON DELETE CASCADE,
	id_tipo INTEGER REFERENCES unexca_db.tipos_usuario(id_tipo) ON DELETE CASCADE,
    id_pnf INTEGER REFERENCES unexca_db.pnf(id_pnf) ON DELETE CASCADE,
    nombres VARCHAR(100) NOT NULL,
	apellidos VARCHAR(100) NOT NULL,
	genero VARCHAR(30) NOT NULL,
	fecha_nacimiento DATE NOT NULL,
	correo_personal VARCHAR(150) UNIQUE NOT NULL,
	telefono_personal VARCHAR(20),
	direccion_habitacion TEXT,
	fecha_ingreso DATE NOT NULL,
	creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	ultimo_login TIMESTAMP
);

CREATE TABLE unexca_db.periodo_academico (
    id_periodo SERIAL PRIMARY KEY,
    periodo VARCHAR(10) NOT NULL UNIQUE,
    fecha_inicio DATE NOT NULL,
    fecha_final DATE NOT NULL,
    estado BOOLEAN DEFAULT TRUE,
    CONSTRAINT check_fechas CHECK (fecha_final > fecha_inicio)
);

CREATE TABLE unexca_db.secciones (
    id_seccion SERIAL PRIMARY KEY,
    id_unidad_curricular INTEGER REFERENCES unexca_db.unidades_curriculares(id_unidad_curricular) ON DELETE CASCADE,
    id_docente INTEGER REFERENCES unexca_db.datos_docentes(id_docente) ON DELETE SET NULL,
	id_periodo INTEGER REFERENCES unexca_db.periodo_academico(id_periodo) ON DELETE SET NULL,
    cod_seccion VARCHAR(15) NOT NULL,
    capacidad_max INTEGER DEFAULT 40,
    UNIQUE(id_unidad_curricular, id_periodo, cod_seccion)
);

CREATE TABLE unexca_db.datos_estudiantes (
    id_estudiante SERIAL PRIMARY KEY,
    id_usuario INTEGER REFERENCES unexca_db.usuarios(id_usuario) ON DELETE CASCADE,
	id_estatus INTEGER REFERENCES unexca_db.estatus(id_estatus) ON DELETE CASCADE,
	id_seccion INTEGER REFERENCES unexca_db.secciones(id_seccion) ON DELETE CASCADE,
	id_trayecto INTEGER REFERENCES unexca_db.trayectos(id_trayecto) ON DELETE CASCADE,
	cedula_identidad VARCHAR(15) UNIQUE NOT NULL,
	nombres_estudiante VARCHAR(100) NOT NULL,
	apellidos_estudiante VARCHAR(100) NOT NULL,
	genero VARCHAR(30) NOT NULL,
	fecha_nacimiento DATE NOT NULL,
	correo_personal VARCHAR(150) UNIQUE NOT NULL,
	telefono_personal VARCHAR (20),
	direccion_habitacion TEXT,
    indice_academico DECIMAL(4,2) DEFAULT 0.00,
    fecha_ingreso DATE NOT NULL,
	creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	actualizado_en TIMESTAMP
);

CREATE TABLE unexca_db.expediente_estudiante (
	id_expediente SERIAL PRIMARY KEY,
	id_estudiante INTEGER REFERENCES unexca_db.datos_estudiantes(id_estudiante) ON DELETE CASCADE,
	cod_expediente VARCHAR(50) UNIQUE NOT NULL,
	creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE unexca_db.inscripciones (
    id_inscripcion SERIAL PRIMARY KEY,
    id_estudiante INTEGER REFERENCES unexca_db.datos_estudiantes(id_estudiante) ON DELETE CASCADE,
    id_seccion INTEGER REFERENCES unexca_db.secciones(id_seccion) ON DELETE CASCADE,
    nota_parcial DECIMAL(4,2) DEFAULT 0.00,
    nota_final DECIMAL(4,2) DEFAULT 0.00,
    fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(id_estudiante, id_seccion),
    CONSTRAINT check_rango_notas CHECK (nota_final >= 0 AND nota_final <= 20)
);

CREATE TABLE unexca_db.aranceles (
    id_arancel SERIAL PRIMARY KEY,
    descripcion VARCHAR(100) NOT NULL,
    monto DECIMAL(12,2) NOT NULL,
    activo BOOLEAN DEFAULT TRUE
);

CREATE TABLE unexca_db.estatus_pago (
	id_estatus_pago SERIAL PRIMARY KEY,
	descripcion TEXT,
	creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE unexca_db.pagos (
    id_pago SERIAL PRIMARY KEY,
    id_estudiante INTEGER REFERENCES unexca_db.datos_estudiantes(id_estudiante) ON DELETE CASCADE,
    id_arancel INTEGER REFERENCES unexca_db.aranceles(id_arancel) ON DELETE CASCADE,
	id_estatus INTEGER REFERENCES unexca_db.estatus_pago(id_estatus_pago) ON DELETE CASCADE,
	nombre_banco VARCHAR(50) NOT NULL,
    referencia_bancaria VARCHAR(50) UNIQUE NOT NULL,
    fecha_pago DATE NOT NULL,
);

CREATE VIEW reporte_aprobados AS
SELECT *, CASE WHEN nota_final >= 13 THEN 'Aprobado' ELSE 'Reprobado' END AS estatus
FROM inscripciones_notas;

CREATE TABLE unexca_db.permisos (
    id_permiso SERIAL PRIMARY KEY,
    nombre_permiso VARCHAR(100) UNIQUE NOT NULL,
    descripcion TEXT,
    id_modulos INTEGER REFERENCES unexca_db.modulos(id_modulo) ON DELETE CASCADE
);

CREATE TABLE unexca_db.modulos (
    id_modulo SERIAL PRIMARY KEY,
    nombre_modulo VARCHAR(50) UNIQUE NOT NULL, 
    icono VARCHAR(50), 
    orden INTEGER DEFAULT 0, 
    activo BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE unexca_db.roles_permisos (
    id_tipo_usuario INTEGER REFERENCES unexca_db.tipos_usuario(id_tipo) ON DELETE CASCADE,
    id_permiso INTEGER REFERENCES unexca_db.permisos(id_permiso) ON DELETE CASCADE,
	id_usuario INTEGER REFERENCES unexca_db.usuarios(id_usuario) ON DELETE CASCADE,
    PRIMARY KEY (id_tipo_usuario, id_permiso, id_usuario)
);

CREATE TABLE unexca_db.configuraciones (
    id SERIAL PRIMARY KEY,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    descripcion TEXT,
    id_categoria INTEGER REFERENCES unexca_db.categorias_conf(id_categoria) ON DELETE CASCADE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP
);

CREATE TABLE unexca_db.categorias_conf (
	id_categoria SERIAL PRIMARY KEY,
	nombre_categoria VARCHAR(100) NOT NULL UNIQUE,
	descripcion TEXT,
	creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	actualizado_en TIMESTAMP
);


CREATE TABLE unexca_db.semestre_actual (
	id_semestre_actual SERIAL PRIMARY KEY,
	periodo_academico VARCHAR(15) NOT NULL,
	anio_en_curso VARCHAR(15) NOT NULL,
	creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	actulizado_en TIMESTAMP
);


INSERT INTO unexca_db.usuarios (cedula, nombres, apellidos, correo_institucional, password_hash, id_tipo) VALUES
('27391753', 'Alex Jonfranc', 'Madrid Marin', 'alexmadrid326@gmail.com', 'qwerty2801**', 1)

INSERT INTO unexca_db.estatus (nombre_estatus, descripcion) VALUES
('ACTIVO', 'Estudiante regular del semestre actual'),
('INACTIVO', 'Estudiante inactivo del semestre actual'),
('EGRESADO', 'Estudiante que egreso y tiene tiempo de graduado'),
('SUSPENDIDO', 'Estudiante regular suspendido del semestre actual'),
('GRADUADO', 'Estudiante que estaba en proceso de graduacion');

INSERT INTO unexca_db.modulos (nombre_modulo, icono, orden) VALUES
('Configuración General', 'settings', 1),
('Control de Estudios', 'school', 2),
('Gestión Financiera', 'payments', 3),
('Correspondencia', 'email', 4),
('Seguridad y Accesos', 'shield', 5),
('Mantenimiento', 'storage', 6);

INSERT INTO unexca_db.permisos (nombre_permiso, descripcion, id_modulos) VALUES
-- Módulo 1: Configuración General
('modificar_institucion', 'Permite cambiar el nombre y datos de la UNEXCA', 1),
('cambiar_periodo', 'Permite establecer el período académico activo', 1),
-- Módulo 2: Control de Estudios
('inscribir_alumno', 'Permite formalizar la inscripción de estudiantes', 2),
('editar_notas', 'Permite la carga y modificación de calificaciones', 2),
('generar_constancias', 'Permite emitir documentos de estudios vigentes', 2),
-- Módulo 3: Gestión Financiera
('registrar_pagos', 'Permite el registro de aranceles y mensualidades', 3),
('consultar_solvencia', 'Permite verificar si un alumno está solvente', 3),
-- Módulo 5: Seguridad y Accesos
('crear_usuario', 'Permite registrar nuevos usuarios en el sistema', 5),
('asignar_roles', 'Permite gestionar los permisos de cada tipo de usuario', 5),
('ver_auditoria', 'Permite ver los logs y movimientos de los usuarios', 5),
-- Módulo 6: Mantenimiento
('ejecutar_backup', 'Permite realizar respaldos manuales de la base de datos', 6),
('restaurar_sistema', 'Permite la restauración de puntos de control', 6);

INSERT INTO unexca_db.categorias_conf (nombre_categoria, descripcion) VALUES
('general', 'configuraciones generales del sistema'),
('control de estudios', 'configuracion de gestion academica'),
('financias', 'configuracion de gestion financiera'),
('correo', 'configuraciones del correo de la institucion'),
('seguridad', 'configuracion de seguridad del sistema'),
('backup', 'configuracion para registrar backup del sistema');


INSERT INTO unexca_db.configuraciones (clave, valor, descripcion, id_categoria) VALUES
('nombre_institucion', 'UNEXCA', 'Nombre de la institución', 1),
('periodo_actual', '2024-1', 'Período académico actual', 2),
('moneda', 'Bs', 'Moneda principal del sistema', 1),
('zona_horaria', 'America/Caracas', 'Zona horaria del sistema', 1),
('nota_minima', '12', 'Nota mínima para aprobar', 2),
('nota_excelencia', '20', 'Nota para excelencia académica', 2),
('maximo_creditos', '24', 'Máximo de créditos por semestre', 2),
('monto_matricula', '1000', 'Monto de matrícula', 3),
('dias_vencimiento', '30', 'Días para vencimiento de pagos', 3),
('smtp_host', 'smtp.gmail.com', 'Servidor SMTP', 4),
('smtp_port', '587', 'Puerto SMTP', 4),
('email_from', 'noreply@unexca.edu', 'Email remitente', 4),
('max_intentos_login', '3', 'Máximo de intentos de login', 5),
('tiempo_bloqueo', '30', 'Minutos de bloqueo tras intentos fallidos', 5),
('requerir_cambio_password', '90', 'Días para forzar cambio de contraseña', 5),
('auto_backup', '1', 'Activar backup automático', 6),
('frecuencia_backup', 'daily', 'Frecuencia de backups automáticos', 6),
('mantener_backups', '30', 'Días para mantener backups', 6);

INSERT INTO unexca_db.tipos_usuario (nombre_tipo, descripcion) VALUES
('Administrador', 'Acceso total al sistema y gestión de usuarios.'),
('Administrativo', 'Gestión de procesos de control de estudios y expedientes.'),
('Coordinador', 'Supervisión de PNF, horarios y asignación docente.'),
('Docente', 'Carga de calificaciones y gestión de actividades académicas.'),
('Estudiante', 'Consulta de historial académico e inscripción de unidades curriculares.'),
('Invitado', 'Acceso restringido para procesos de auditoría o preinscripción.');

INSERT INTO unexca_db.sedes_unexca (nombre_sede, correo_institucional, direccion) VALUES 
('La floresta', 'unexca_floresta2026@gmail.com', 'Av. Principal de la Floresta cruce con Av. Francisco de Miranda, Edificio, Caracas 1060, Miranda'),
('Altagracia', 'unexca_altagracia2026@gmail.com', 'Esquina Mijares, Avenida Oeste 3, Altagracia, Caracas 1010, 1010, Distrito Capital'),
('La Urbina', 'unexca_urbina2026@gmail.com', 'Calle 8, Zona Industrial, Edificio Mercurio, Caracas 1073, Distrito Capital'),
('Carayaca, la Guaira', 'unexca_laguaira2026@gmail.com', 'complejo educativo Hueikaipuro, parroquia Carayaca, Municipio Vargas, Estado La Guaira');

INSERT INTO unexca_db.pnf (id_sede, cod_pnf, nombre_pnf, descripcion, duracion_pnf, unidad_total_creditos) 
VALUES 
(1, 'ADM', 'PNF en Administración', 'Programa nacional de formación en el área administrativa.', 4, 180),
(1, 'COP', 'PNF en Contaduría Pública', 'Programa enfocado en la gestión contable y financiera.', 4, 180),
(1, 'DIL', 'PNF en Distribución y Logística', 'Gestión de cadenas de suministro y procesos logísticos.', 4, 180),
(1, 'EDE', 'PNF en Educación Especial', 'Formación para la atención educativa integral.', 4, 180),
(1, 'INF', 'PNF en Ingeniería Informática', 'Desarrollo de software y sistemas de información.', 4, 190),
(1, 'TUR', 'PNF en Turismo', 'Gestión y desarrollo de servicios turísticos sostenibles.', 4, 170);

INSERT INTO unexca_db.usuarios (cedula, nombres, apellidos, correo_institucional, password_hash, id_tipo) VALUES
('27391753', 'Alex Jonfranc', 'Madrid Marin', 'alexmadrid326@gmail.com', 'qwerty2801**', 1)