CREATE TABLE unexca_db.tipos_usuario (
    id_tipo SERIAL PRIMARY KEY,
    nombre_tipo VARCHAR(50) UNIQUE NOT NULL,
	descripcion TEXT
);

CREATE TABLE unexca_db.estatus (
	id_estatus SERIAL PRIMARY KEY,
	nombre_estatus VARCHAR (100) NOT NULL,
	descripcion TEXT,
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
	cod_trayecto VARCHAR(10) NOT NULL,
	descripcion TEXT,
	creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE unexca_db.datos_docentes (
    id_docente SERIAL PRIMARY KEY,
	id_tipo INTEGER REFERENCES unexca_db.tipos_usuario(id_tipo) ON DELETE CASCADE,
	id_estatus INTEGER REFERENCES unexca_db.estatus(id_estatus) ON DELETE CASCADE DEFAULT 2,
	cedula_docente INT UNIQUE NOT NULL,
    nombres VARCHAR(100) NOT NULL,
	apellidos VARCHAR(100) NOT NULL,
	genero VARCHAR(30) NOT NULL,
	fecha_nacimiento DATE NOT NULL,
	correo_personal VARCHAR(150) UNIQUE NOT NULL,
	telefono_personal VARCHAR(20),
	direccion_habitacion TEXT,
	fecha_ingreso DATE NOT NULL,
	creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	actualizado_en TIMESTAMP
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
    id_horario INTEGER REFERENCES unexca_db.horarios(id_horario) ON DELETE CASCADE,
    cod_seccion VARCHAR(15) NOT NULL,
    capacidad_max INTEGER DEFAULT 40,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP,
    CONSTRAINT check_capacidad_max CHECK (capacidad_max >= 0 AND capacidad_max <= 40)
);

CREATE TABLE unexca_db.horarios (
	id_horario SERIAL PRIMARY KEY,
	id_asignatura INTEGER REFERENCES unexca_db.asignatura(id_asignatura) ON DELETE CASCADE,
	id_docente INTEGER REFERENCES unexca_db.datos_docentes(id_docente) ON DELETE CASCADE,
	id_aula INTEGER REFERENCES unexca_db.aulas(id_aula) ON DELETE CASCADE,
	id_turno INTEGER REFERENCES unexca_db.turnos(id_turno) ON DELETE CASCADE,
	id_trayecto INTEGER REFERENCES unexca_db.trayectos(id_trayecto) ON DELETE CASCADE,
	hora_inicio TIME NOT NULL,
	hora_fin TIME NOT NULL,
	creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	actualizado_en TIMESTAMP
);

CREATE TABLE unexca_db.asignatura (
    id_asignatura SERIAL PRIMARY KEY,
    id_pnf INTEGER REFERENCES unexca_db.pnf(id_pnf) ON DELETE CASCADE,
	id_trayecto INTEGER REFERENCES unexca_db.trayectos(id_trayecto) ON DELETE CASCADE,
    codigo VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    unidades_credito INTEGER NOT NULL,
	creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	actualizado_en TIMESTAMP
);
SELECT * FROM unexca_db.pnf;
SELECT * FROM unexca_db.trayectos;

CREATE TABLE unexca_db.turnos (
	id_turno SERIAL PRIMARY KEY,
	turno VARCHAR(50) NOT NULL,
	descripcion TEXT,
	creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE unexca_db.aulas (
	id_aula SERIAL PRIMARY KEY,
	piso VARCHAR(15) NOT NULL,
	nro_aula VARCHAR(15) NOT NULL,
	nombre_aula VARCHAR(20) NOT NULL,
	creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE unexca_db.datos_estudiantes (
    id_estudiante SERIAL PRIMARY KEY,
	id_tipo INTEGER REFERENCES unexca_db.tipos_usuario(id_tipo) ON DELETE CASCADE,
	id_estatus INTEGER REFERENCES unexca_db.estatus(id_estatus) ON DELETE CASCADE DEFAULT 2,
	cedula_identidad INT UNIQUE NOT NULL,
	nombres_estudiante VARCHAR(100) NOT NULL,
	apellidos_estudiante VARCHAR(100) NOT NULL,
	genero VARCHAR(30) NOT NULL,
	fecha_nacimiento DATE NOT NULL,
	correo_personal VARCHAR(150) UNIQUE NOT NULL,
	telefono_personal VARCHAR (20),
	direccion_habitacion TEXT,
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

SELECT * FROM unexca_db.pnf;

INSERT INTO unexca_db.trayectos (cod_trayecto, descripcion) VALUES
('1-1', 'Trayecto 1, semetre 1 TSU'),
('1-2', 'Trayecto 1, semetre 2 TSU'),
('2-1', 'Trayecto 2, semetre 1 TSU'),
('2-2', 'Trayecto 2, semetre 2 TSU'),
('3-1', 'Trayecto 3, semetre 1'),
('3-2', 'Trayecto 3, semetre 2'),
('4-1', 'Trayecto 4, semetre 1'),
('4-2', 'Trayecto 4, semetre 2');

INSERT INTO unexca_db.usuarios (cedula, nombres, apellidos, correo_institucional, password_hash, id_tipo) VALUES
('27391753', 'Alex Jonfranc', 'Madrid Marin', 'alexmadrid326@gmail.com', 'qwerty2801**', 1);

INSERT INTO unexca_db.turnos (turno, descripcion) VALUES
('Mañana', 'Horario matutino destinado a las primeras cohortes del día (07:00 - 12:00)'),
('Tarde', 'Horario vespertino para clases intermedias y talleres (13:00 - 18:00)'),
('Noche', 'Horario nocturno para programas de formación profesional y postgrado (18:00 - 22:00)');

INSERT INTO unexca_db.aulas (piso, nro_aula, nombre_aula) VALUES
-- Piso 1
('Piso 1', '101', 'Aula 101'), ('Piso 1', '102', 'Aula 102'), ('Piso 1', '103', 'Aula 103'), ('Piso 1', '104', 'Aula 104'), ('Piso 1', '105', 'Aula 105'),
('Piso 1', '106', 'Aula 106'), ('Piso 1', '107', 'Aula 107'), ('Piso 1', '108', 'Aula 108'), ('Piso 1', '109', 'Aula 109'), ('Piso 1', '110', 'Aula 110'),
-- Piso 2
('Piso 2', '201', 'Aula 201'), ('Piso 2', '202', 'Aula 202'), ('Piso 2', '203', 'Aula 203'), ('Piso 2', '204', 'Aula 204'), ('Piso 2', '205', 'Aula 205'),
('Piso 2', '206', 'Aula 206'), ('Piso 2', '207', 'Aula 207'), ('Piso 2', '208', 'Aula 208'), ('Piso 2', '209', 'Aula 209'), ('Piso 2', '210', 'Aula 210'),
-- Piso 3
('Piso 3', '301', 'Aula 301'), ('Piso 3', '302', 'Aula 302'), ('Piso 3', '303', 'Aula 303'), ('Piso 3', '304', 'Aula 304'), ('Piso 3', '305', 'Aula 305'),
('Piso 3', '306', 'Aula 306'), ('Piso 3', '307', 'Aula 307'), ('Piso 3', '308', 'Aula 308'), ('Piso 3', '309', 'Aula 309'), ('Piso 3', '310', 'Aula 310'),
-- Piso 4
('Piso 4', '401', 'Aula 401'), ('Piso 4', '402', 'Aula 402'), ('Piso 4', '403', 'Aula 403'), ('Piso 4', '404', 'Aula 404'), ('Piso 4', '405', 'Aula 405'),
('Piso 4', '406', 'Aula 406'), ('Piso 4', '407', 'Aula 407'), ('Piso 4', '408', 'Aula 408'), ('Piso 4', '409', 'Aula 409'), ('Piso 4', '410', 'Aula 410'),
-- Piso 5
('Piso 5', '501', 'Aula 501'), ('Piso 5', '502', 'Aula 502'), ('Piso 5', '503', 'Aula 503'), ('Piso 5', '504', 'Aula 504'), ('Piso 5', '505', 'Aula 505'),
('Piso 5', '506', 'Aula 506'), ('Piso 5', '507', 'Aula 507'), ('Piso 5', '508', 'Aula 508'), ('Piso 5', '509', 'Aula 509'), ('Piso 5', '510', 'Aula 510'),
-- Piso 6
('Piso 6', '601', 'Aula 601'), ('Piso 6', '602', 'Aula 602'), ('Piso 6', '603', 'Aula 603'), ('Piso 6', '604', 'Aula 604'), ('Piso 6', '605', 'Aula 605'),
('Piso 6', '606', 'Aula 606'), ('Piso 6', '607', 'Aula 607'), ('Piso 6', '608', 'Aula 608'), ('Piso 6', '609', 'Aula 609'), ('Piso 6', '610', 'Aula 610');

