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

CREATE TABLE unexca_db.trayectos (
    id_trayecto SERIAL PRIMARY KEY,
    cod_trayecto VARCHAR(10) NOT NULL,
    descripcion TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
    cod_seccion VARCHAR(15) NOT NULL,
    capacidad_max INTEGER DEFAULT 40,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP,
    CONSTRAINT check_capacidad_max CHECK (capacidad_max >= 0 AND capacidad_max <= 40)
);

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

CREATE TABLE unexca_db.requisitos (
    id_requisito SERIAL PRIMARY KEY,
    nombre_requisito VARCHAR(100) NOT NULL,
    categoria_estudiante VARCHAR(50),
    descripcion TEXT,
    es_obligatorio BOOLEAN DEFAULT TRUE
);

CREATE TABLE unexca_db.modulos (
    id_modulo SERIAL PRIMARY KEY,
    nombre_modulo VARCHAR(50) UNIQUE NOT NULL, 
    icono VARCHAR(50), 
    orden INTEGER DEFAULT 0, 
    activo BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE unexca_db.categorias_conf (
    id_categoria SERIAL PRIMARY KEY,
    nombre_categoria VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP
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

CREATE TABLE unexca_db.permisos (
    id_permiso SERIAL PRIMARY KEY,
    nombre_permiso VARCHAR(100) UNIQUE NOT NULL,
    descripcion TEXT,
    id_modulos INTEGER REFERENCES unexca_db.modulos(id_modulo) ON DELETE CASCADE
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

CREATE TABLE unexca_db.aranceles (
    id_arancel SERIAL PRIMARY KEY,
    id_estatus INTEGER REFERENCES unexca_db.estatus(id_estatus) ON DELETE CASCADE,
    descripcion VARCHAR(100) NOT NULL,
    monto DECIMAL(12,2) NOT NULL
);

CREATE TABLE unexca_db.usuarios (
    id_usuario SERIAL PRIMARY KEY,
    id_persona INTEGER UNIQUE REFERENCES unexca_db.datos_personas(id_persona) ON DELETE CASCADE,
    cedula VARCHAR(15) UNIQUE NOT NULL, -- Se mantiene para login, pero debe coincidir con datos_personas
    correo_institucional VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    id_tipo INTEGER REFERENCES unexca_db.tipos_usuario(id_tipo) ON DELETE CASCADE,
    id_estatus INTEGER REFERENCES unexca_db.estatus(id_estatus) ON DELETE CASCADE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	ultimo_login TIMESTAMP
);

select * from unexca_db.datos_personas;

CREATE TABLE unexca_db.datos_personas (
    id_persona SERIAL PRIMARY KEY,
    id_estatus INTEGER REFERENCES unexca_db.estatus(id_estatus) ON DELETE CASCADE,
    cedula_identidad INT UNIQUE NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    genero VARCHAR(30) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    correo_personal VARCHAR(150) UNIQUE NOT NULL,
    telefono_personal VARCHAR (20),
    direccion_habitacion TEXT,
    fecha_ingreso DATE NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP
);

CREATE TABLE unexca_db.roles_permisos (
    id_tipo_usuario INTEGER REFERENCES unexca_db.tipos_usuario(id_tipo) ON DELETE CASCADE,
    id_permiso INTEGER REFERENCES unexca_db.permisos(id_permiso) ON DELETE CASCADE,
    id_usuario INTEGER REFERENCES unexca_db.usuarios(id_usuario) ON DELETE CASCADE,
    PRIMARY KEY (id_tipo_usuario, id_permiso, id_usuario)
);

CREATE TABLE unexca_db.datos_docentes (
    id_docente SERIAL PRIMARY KEY,
    id_persona INTEGER UNIQUE REFERENCES unexca_db.datos_personas(id_persona) ON DELETE CASCADE,
    id_pnf INTEGER REFERENCES unexca_db.pnf(id_pnf) ON DELETE CASCADE,
    id_sede INTEGER REFERENCES unexca_db.sedes_unexca(id_sede) ON DELETE CASCADE,
    fecha_ingreso DATE NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP
);

CREATE TABLE unexca_db.datos_estudiantes (
    id_estudiante SERIAL PRIMARY KEY,
    id_persona INTEGER UNIQUE REFERENCES unexca_db.datos_personas(id_persona) ON DELETE CASCADE,
    id_trayecto INTEGER REFERENCES unexca_db.trayectos(id_trayecto) ON DELETE CASCADE,
    id_pnf INTEGER REFERENCES unexca_db.pnf(id_pnf) ON DELETE CASCADE,
    id_sede INTEGER REFERENCES unexca_db.sedes_unexca(id_sede) ON DELETE CASCADE,
    fecha_ingreso DATE NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP
);

CREATE TABLE unexca_db.expediente_estudiante (
    id_expediente SERIAL PRIMARY KEY,
    id_persona INTEGER REFERENCES unexca_db.datos_personas(id_persona) ON DELETE CASCADE,
    cod_expediente VARCHAR(50) UNIQUE NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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

CREATE TABLE unexca_db.horarios (
    id_horario SERIAL PRIMARY KEY,
    id_asignatura INTEGER REFERENCES unexca_db.asignatura(id_asignatura) ON DELETE CASCADE,
    id_seccion INTEGER REFERENCES unexca_db.secciones(id_seccion) ON DELETE CASCADE,
    id_docente INTEGER REFERENCES unexca_db.datos_docentes(id_docente) ON DELETE CASCADE,
    id_aula INTEGER REFERENCES unexca_db.aulas(id_aula) ON DELETE CASCADE,
    id_turno INTEGER REFERENCES unexca_db.turnos(id_turno) ON DELETE CASCADE,
    id_trayecto INTEGER REFERENCES unexca_db.trayectos(id_trayecto) ON DELETE CASCADE,
    cod_horario VARCHAR(20) NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Corregido: Referencias ajustadas a nombres de tablas reales
CREATE TABLE unexca_db.inscripcion_nue_ingreso (
    id_inscripcion SERIAL PRIMARY KEY,
    id_estudiante INTEGER REFERENCES unexca_db.datos_personas(id_persona) ON DELETE CASCADE,
    id_periodo INTEGER REFERENCES unexca_db.periodo_academico(id_periodo) ON DELETE CASCADE,
    id_seccion INTEGER REFERENCES unexca_db.secciones(id_seccion) ON DELETE CASCADE,
    id_pnf INTEGER REFERENCES unexca_db.pnf(id_pnf) ON DELETE CASCADE,
    id_sede INTEGER REFERENCES unexca_db.sedes_unexca(id_sede) ON DELETE CASCADE,
    id_trayecto INTEGER REFERENCES unexca_db.trayectos(id_trayecto) ON DELETE CASCADE,
    id_estatus_inscripcion INTEGER REFERENCES unexca_db.estatus(id_estatus) ON DELETE CASCADE,
    fecha_formalizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unico_estudiante_periodo UNIQUE(id_estudiante, id_periodo)
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

CREATE TABLE unexca_db.pagos (
    id_pago SERIAL PRIMARY KEY,
    id_arancel INTEGER REFERENCES unexca_db.aranceles(id_arancel) ON DELETE CASCADE,
    id_estatus INTEGER REFERENCES unexca_db.estatus(id_estatus) ON DELETE CASCADE,
    id_persona INTEGER REFERENCES unexca_db.datos_personas(id_persona) ON DELETE CASCADE,
    nombre_banco VARCHAR(50) NOT NULL,
    referencia_bancaria varchar(100) UNIQUE NOT NULL,
    fecha_pago DATE NOT NULL
);

CREATE TABLE unexca_db.estudiante_requisitos (
    id SERIAL PRIMARY KEY,
    id_estudiante INTEGER REFERENCES unexca_db.datos_estudiantes(id_estudiante) ON DELETE CASCADE,
    id_requisito INTEGER REFERENCES unexca_db.requisitos(id_requisito) ON DELETE CASCADE,
    id_estatus INTEGER REFERENCES unexca_db.estatus(id_estatus) ON DELETE CASCADE, 
    url_archivo TEXT,
    fecha_entrega TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    observaciones TEXT,
    actualizado_en TIMESTAMP
);

CREATE TABLE unexca_db.semestre_actual (
    id_semestre_actual SERIAL PRIMARY KEY,
    periodo_academico VARCHAR(15) NOT NULL,
    anio_en_curso VARCHAR(15) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actulizado_en TIMESTAMP
);


select * from unexca_db.usuarios;

SELECT * FROM unexca_db.secciones order by id_seccion asc;
select * from unexca_db.horarios;

INSERT INTO unexca_db.tipos_usuario (nombre_tipo, descripcion) VALUES
('Administrador', 'Superusuario con permisos totales sobre la plataforma y base de datos.'),
('Estudiante', 'Usuario regular inscrito en programas académicos, consulta notas y horarios.'),
('Docente', 'Personal académico encargado de impartir materias y cargar calificaciones.'),
('Control de Estudios', 'Personal administrativo que gestiona expedientes académicos y certificaciones.'),
('Finanzas', 'Personal encargado de la recepción de pagos y validación de solvencias.');


INSERT INTO unexca_db.pnf (id_sede, cod_pnf, nombre_pnf, descripcion, duracion_pnf, unidad_total_creditos) VALUES
(1, 'PNF-ADM', 'PNF en Administración', 'Formación en gestión y dirección de organizaciones.', 4, 180),
(1, 'PNF-CP', 'PNF en Contaduría Pública', 'Formación en sistemas de información contable y financiera.', 4, 185),
(2, 'PNF-DL', 'PNF en Distribución y Logística', 'Gestión de cadenas de suministro y procesos logísticos.', 4, 175),
(3, 'PNF-EE', 'PNF en Educación Especial | Lenguaje de señas', 'Especialización en atención a la diversidad y comunicación inclusiva.', 4, 170),
(1, 'PNF-INF', 'PNF en Ingeniería Informática', 'Desarrollo de software, redes y soluciones tecnológicas.', 4, 190),
(4, 'PNF-TUR', 'PNF en Turismo', 'Gestión de servicios turísticos y desarrollo sustentable.', 4, 165);

INSERT INTO unexca_db.trayectos (cod_trayecto, descripcion) VALUES
('T-I', 'Trayecto Inicial, cursos introductorios'),
('1-1', 'Trayecto 1, semetre 1'),
('1-2', 'Trayecto 1, semetre 2 '),
('2-1', 'Trayecto 2, semetre 1'),
('2-2', 'Trayecto 2, semetre 2'),
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

INSERT INTO unexca_db.horarios 
(id_asignatura, id_seccion, id_docente, id_aula, id_turno, id_trayecto, cod_horario, hora_inicio, hora_fin) 
VALUES
-- Actividades Acreditables
(1, 1, 1, 1, 1, 1, 'AAC6012-M1', '07:30:00', '09:00:00'),

-- Algorítmica y Programación
(2, 1, 2, 5, 1, 1, 'APT150151-M1', '09:00:00', '11:15:00'),

-- Arquitectura del Computador
(3, 1, 3, 8, 1, 1, 'ACT150151-M1', '11:15:00', '12:45:00'),

-- Formación Crítica I
(4, 1, 4, 2, 2, 1, 'FCS120141-T1', '13:30:00', '15:00:00'),

-- Inglés I
(5, 1, 5, 3, 2, 1, 'IDC60121-T1', '15:00:00', '16:30:00'),

-- Matemática I
(6, 1, 6, 5, 1, 1, 'MAC90131-M1', '07:30:00', '09:45:00'),

-- Proyecto Socio Tecnológico I
(7, 1, 7, 10, 1, 1, 'PTP270191-M1', '09:45:00', '12:45:00');


INSERT INTO unexca_db.datos_estudiantes (
    id_tipo, 
    id_estatus, 
    cedula_identidad, 
    nombres_estudiante, 
    apellidos_estudiante, 
    genero, 
    fecha_nacimiento, 
    correo_personal, 
    telefono_personal, 
    direccion_habitacion, 
    fecha_ingreso
)
SELECT 
    5, -- id_tipo fijo para estudiantes
    2, -- estatus por defecto
    floor(random() * (35000000 - 20000000 + 1) + 20000000)::int, -- Cédulas entre 20M y 35M
    (ARRAY['Aarón', 'Beatriz', 'Carlos', 'Diana', 'Eduardo', 'Fabiola', 'Gabriel', 'Héctor', 'Irene', 'José'])[floor(random() * 10 + 1)],
    (ARRAY['Martínez', 'Rodríguez', 'Pérez', 'Hernández', 'García', 'López', 'Sánchez', 'González', 'Ramírez', 'Torres'])[floor(random() * 10 + 1)],
    (ARRAY['Masculino', 'Femenino'])[floor(random() * 2 + 1)],
    CURRENT_DATE - (floor(random() * (25 - 17 + 1) + 17) || ' years')::interval, -- Edad entre 17 y 25 años
    'user' || i || floor(random() * 1000) || '@example.com',
    '0414-' || floor(random() * (9999999 - 1000000 + 1) + 1000000)::int,
    'Caracas, Municipio Libertador, Sector ' || floor(random() * 100 + 1),
    '2024-01-15'::date + (floor(random() * 365) || ' days')::interval -- Fechas de ingreso en el último año
FROM generate_series(1, 50) s(i);

INSERT INTO unexca_db.estatus (nombre_estatus, descripcion) VALUES
-- Estados de Usuario/Estudiante
('Activo', 'El estudiante o usuario se encuentra con todos sus privilegios vigentes.'),
('Inactivo', 'El usuario no tiene acceso al sistema, posiblemente por retiro voluntario.'),
('Graduado', 'El estudiante ha completado satisfactoriamente toda su carga académica.'),
('Suspendido', 'Acceso restringido por motivos disciplinarios o administrativos.'),

-- Estados de Materias/Cursos
('Cursando', 'La asignatura se encuentra actualmente en desarrollo.'),
('Retirada', 'La asignatura fue desincorporada por el estudiante dentro de los lapsos permitidos.'),
('Reprobada', 'El estudiante no alcanzó la nota mínima aprobatoria.'),
('Convalidada', 'La asignatura fue aprobada mediante proceso de equivalencia o acreditación.'), 

-- Estados de Pagos y Aranceles
('Pago Pendiente', 'El arancel ha sido generado pero aún no se ha registrado ningún comprobante.'),
('Pago Reportado', 'El estudiante cargó el soporte de pago y espera validación administrativa.'),
('Pago Conciliado', 'El pago ha sido verificado en la cuenta bancaria de la institución.'),
('Pago Rechazado', 'El soporte de pago es inválido, ilegible o el monto es incorrecto.'),
('Exonerado', 'El estudiante cuenta con un beneficio o beca que cubre el costo del arancel.'),
('Reembolsado', 'El monto del arancel fue devuelto al estudiante por anulación de proceso.'),

-- Estados de Requisitos y Documentos de Procesos Académicos (Inscripciones/Solicitudes)
('Entregado', 'El estudiante ha cumplido con la entrega de requisitos o documentos solicitados.'),
('Pendiente', 'La solicitud ha sido enviada y espera revisión por parte de control de estudios.'),
('En Revisión', 'El proceso está siendo validado por el personal administrativo.'),
('Rechazado', 'La solicitud no cumple con los requisitos necesarios.');

-- Estados de Inscripciones y Trayectos
('Inscrito', 'El estudiante se encuentra actualmente inscrito en la sección.'),
('Retirado', 'El estudiante se retiró de la sección dentro del período permitido.'),
('Aprobado', 'El estudiante aprobó la sección con una nota final igual o superior a 10.00.'),
('Reprobado', 'El estudiante no alcanzó la nota mínima aprobatoria en la sección.'),
('Convalidado', 'La sección fue aprobada mediante proceso de equivalencia o acreditación.'),
('Preinscrito', 'El aspirante ha completado el proceso de preinscripción pero aún no ha formalizado su inscripción.'),
('Regular', 'El estudiante está inscrito pero no ha cumplido con los requisitos académicos para avanzar al siguiente trayecto.'),
('Nuevo Ingreso', 'El estudiante es un nuevo ingreso que aún no ha formalizado su inscripción.'),
('Egresado', 'El estudiante ha completado satisfactoriamente toda su carga académica y ha egresado de la institución.');


INSERT INTO unexca_db.aranceles (id_estatus, descripcion, monto) VALUES
-- Aranceles de Inscripción y Carnetización
(1, 'Inscripción de Nuevo Ingreso - Pregrado', 150.00),
(1, 'Reinscripción Semestral / Trayecto', 100.00),
(1, 'Emisión de Carnet Estudiantil', 50.00),

-- Aranceles de Documentación Académica
(1, 'Constancia de Estudios', 30.00),
(1, 'Certificación de Calificaciones (Notas)', 80.00),
(1, 'Pensum y Programa de Estudio', 120.00),
(1, 'Carga Horaria / Horas Lectivas', 40.00),

-- Aranceles de Grado y Egreso
(1, 'Derecho a Acto de Grado', 500.00),
(1, 'Paquete de Título y Medalla', 350.00),
(1, 'Certificación de Título (Fondo Negro)', 60.00),

-- Otros Trámites
(1, 'Examen de Suficiencia / Reparación', 45.00),
(1, 'Equivalencia de Estudios (Por materia)', 25.00),
(1, 'Solicitud de Cambio de Carrera', 70.00);

INSERT INTO unexca_db.requisitos (nombre_requisito, categoria_estudiante, descripcion, es_obligatorio)
VALUES 
('Título de Bachiller', 'Nuevo Ingreso', 'Copia fondo negro y original a la vista del título de educación media general.', TRUE),
('Notas Certificadas', 'Nuevo Ingreso', 'Certificación de calificaciones de 1ro a 5to año debidamente firmadas y selladas.', TRUE),
('Partida de Nacimiento', 'Nuevo Ingreso', 'Copia legible de la partida de nacimiento (algunas instituciones piden que sea original/actualizada).', TRUE),
('Cédula de Identidad', 'Nuevo Ingreso', 'Fotocopia ampliada de la cédula de identidad vigente.', TRUE),
('Certificado de Participación OPSU', 'Nuevo Ingreso', 'Comprobante de registro en el Sistema Nacional de Ingreso a la Educación Universitaria.', TRUE),
('Fotos tipo Carnet', 'Nuevo Ingreso', 'Dos (02) fotos recientes de frente, tamaño carnet.', FALSE),
('Constancia de Residencia', 'Nuevo Ingreso', 'Documento que valide la dirección de domicilio del aspirante.', FALSE);