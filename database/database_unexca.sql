-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    apellido VARCHAR(255) NOT NULL,
    correo VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('estudiante', 'docente', 'administrador') NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de estudiantes
CREATE TABLE estudiantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    matricula VARCHAR(50) NOT NULL UNIQUE,
    fecha_nacimiento DATE,
    direccion TEXT,
    telefono VARCHAR(15),
    fecha_ingreso DATE,
    estado ENUM('activo', 'graduado', 'egresado', 'inactivo') DEFAULT 'activo',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);

-- Tabla de cursos
CREATE TABLE cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    creditos INT NOT NULL,
    anio YEAR NOT NULL,
    semestre ENUM('1er semestre', '2do semestre') NOT NULL
);

-- Tabla de inscripciones
CREATE TABLE inscripciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante INT,
    id_curso INT,
    fecha_inscripcion DATE,
    estado ENUM('inscrito', 'aprobado', 'reprobado', 'en curso') DEFAULT 'inscrito',
    FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id),
    FOREIGN KEY (id_curso) REFERENCES cursos(id)
);

-- Tabla de notas
CREATE TABLE notas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante INT,
    id_curso INT,
    nota_parcial DECIMAL(5,2),
    nota_final DECIMAL(5,2),
    nota_total DECIMAL(5,2),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('aprobado', 'reprobado', 'pendiente') DEFAULT 'pendiente',
    FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id),
    FOREIGN KEY (id_curso) REFERENCES cursos(id)
);

-- Tabla de actas definitivas
CREATE TABLE actas_definitivas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante INT,
    id_curso INT,
    nota_final DECIMAL(5,2),
    fecha_emision DATE,
    FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id),
    FOREIGN KEY (id_curso) REFERENCES cursos(id)
);

-- Tabla de aranceles
CREATE TABLE aranceles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante INT,
    monto DECIMAL(10,2) NOT NULL,
    estado ENUM('pendiente', 'pagado', 'exonerado') DEFAULT 'pendiente',
    fecha_pago DATE,
    FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id)
);

-- Tabla de egresados
CREATE TABLE egresados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante INT,
    fecha_egreso DATE,
    indice_academico DECIMAL(5,2),
    requisitos_completos BOOLEAN DEFAULT FALSE,
    titulacion ENUM('pendiente', 'titulado', 'no titulado') DEFAULT 'pendiente',
    FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id)
);

-- Tabla de solvencias
CREATE TABLE solvencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante INT,
    tipo ENUM('administrativa', 'academica', 'financiera') NOT NULL,
    estado ENUM('pendiente', 'solventado') DEFAULT 'pendiente',
    fecha_solvencia DATE,
    FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id)
);