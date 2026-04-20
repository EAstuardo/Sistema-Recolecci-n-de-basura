USE recolectora_db;

CREATE TABLE colonias (
  id_colonia INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  descripcion VARCHAR(255),
  tarifa_mensual DECIMAL(10,2) NOT NULL,
  activo BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE usuarios (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  rol VARCHAR(20) NOT NULL,
  activo BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE clientes (
  id_cliente INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  apellido VARCHAR(100) NOT NULL,
  telefono VARCHAR(20),
  direccion VARCHAR(255),
  id_colonia INT NOT NULL,
  activo BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_colonia) REFERENCES colonias(id_colonia)
);

CREATE TABLE pagos (
  id_pago INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT NOT NULL,
  id_usuario INT NOT NULL,
  monto DECIMAL(10,2) NOT NULL,
  fecha_pago DATE NOT NULL,
  mes_pagado VARCHAR(20) NOT NULL,
  metodo_pago VARCHAR(20) DEFAULT 'EFECTIVO',
  observaciones VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

CREATE TABLE recibos (
  id_recibo INT AUTO_INCREMENT PRIMARY KEY,
  id_pago INT NOT NULL UNIQUE,
  numero_recibo VARCHAR(50) NOT NULL UNIQUE,
  fecha_emision TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_pago) REFERENCES pagos(id_pago)
);
USE recolectora_db;

CREATE TABLE camiones (
  id_camion INT AUTO_INCREMENT PRIMARY KEY,
  numero_placa VARCHAR(20) NOT NULL UNIQUE,
  marca VARCHAR(100) NOT NULL,
  modelo VARCHAR(100) NOT NULL,
  anio INT NOT NULL,
  capacidad_kg DECIMAL(10,2),
  estado VARCHAR(20) DEFAULT 'ACTIVO',
  id_colonia INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_colonia) REFERENCES colonias(id_colonia)
);

CREATE TABLE combustible (
  id_combustible INT AUTO_INCREMENT PRIMARY KEY,
  id_camion INT NOT NULL,
  fecha DATE NOT NULL,
  litros DECIMAL(10,2) NOT NULL,
  costo_total DECIMAL(10,2) NOT NULL,
  tipo_combustible VARCHAR(50) DEFAULT 'DIESEL',
  id_usuario INT NOT NULL,
  observaciones VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_camion) REFERENCES camiones(id_camion),
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);