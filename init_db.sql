CREATE DATABASE IF NOT EXISTS facilita CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE facilita;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    role ENUM('client', 'provider') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Provider profiles
CREATE TABLE IF NOT EXISTS provider_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    specialty VARCHAR(255) NOT NULL,
    team_size ENUM('Individual', 'Equipe') NOT NULL DEFAULT 'Individual',
    bio TEXT,
    hourly_rate DECIMAL(10, 2),
    location_name VARCHAR(255),
    lat DECIMAL(10, 6) DEFAULT 0.000000,
    lng DECIMAL(10, 6) DEFAULT 0.000000,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Services
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    provider_id INT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    budget DECIMAL(10, 2),
    status ENUM('Aberta', 'Em negociação', 'Aguardando início', 'Em realização', 'Aguardando pagamento', 'Finalizado') DEFAULT 'Aberta',
    location_name VARCHAR(255),
    lat DECIMAL(10, 6) DEFAULT 0.000000,
    lng DECIMAL(10, 6) DEFAULT 0.000000,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Reviews
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    provider_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Clear old data if exists
DELETE FROM reviews;
DELETE FROM services;
DELETE FROM provider_profiles;
DELETE FROM users;

-- Seed Data: Users
-- Passwords are '123456' hashed with password_hash('123456', PASSWORD_DEFAULT)
INSERT INTO users (id, nome, email, senha, role) VALUES
(1, 'João Cliente', 'joao@cliente.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client'),
(2, 'Maria Silva', 'maria@provedor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'provider'),
(3, 'Carlos Souza', 'carlos@provedor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'provider'),
(4, 'Ana Tech', 'ana@cliente.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client'),
(5, 'José Eletricista', 'jose@provedor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'provider');

-- Seed Data: Provider Profiles
-- Mock locations using general coords around generic center Lat -23.55, Lng -46.63 (Sao Paulo)
INSERT INTO provider_profiles (id, user_id, specialty, team_size, bio, hourly_rate, location_name, lat, lng) VALUES
(1, 2, 'Design Gráfico', 'Individual', 'Especialista em identidades visuais e UI/UX.', 80.00, 'Centro, SP', -23.550520, -46.633308),
(2, 3, 'Desenvolvimento Web', 'Equipe', 'Criamos sites e apps sob medida. Equipe de 3 desenvolvedores.', 150.00, 'Pinheiros, SP', -23.561685, -46.697071),
(3, 5, 'Eletricista Residencial', 'Individual', 'Mais de 10 anos de experiência com instalações elétricas.', 60.00, 'Tatuapé, SP', -23.540166, -46.575971);

-- Seed Data: Services
INSERT INTO services (id, client_id, provider_id, title, description, budget, status, location_name, lat, lng) VALUES
(1, 1, 5, 'Conserto do quadro de luz', 'Preciso de um eletricista para revisar o quadro geral de disjuntores.', 150.00, 'Finalizado', 'Vila Maria, SP', -23.509740, -46.589886),
(2, 4, 3, 'Desenvolver E-commerce', 'Criação de e-commerce com integração de pagamentos.', 3000.00, 'Em realização', 'Paraíso, SP', -23.575306, -46.643034),
(3, 1, NULL, 'Criação de Logo Nova', 'Busco designer para reformular logo da minha marca de roupas.', 400.00, 'Aberta', 'Vila Maria, SP', -23.509740, -46.589886),
(4, 4, NULL, 'Manutenção no Servidor', 'Preciso de manutenção num servidor Linux.', 500.00, 'Aberta', 'Paraíso, SP', -23.575306, -46.643034);

-- Seed Data: Reviews
INSERT INTO reviews (id, service_id, reviewer_id, provider_id, rating, comment) VALUES
(1, 1, 1, 5, 5, 'Serviço excelente e muito rápido. Resolveu meu problema na mesma hora.');
