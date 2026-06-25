CREATE DATABASE IF NOT EXISTS academic_platform;
USE academic_platform;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'faculty', 'admin') DEFAULT 'student',
    department VARCHAR(100),
    profile_pic VARCHAR(255) DEFAULT 'default.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories Table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(50) DEFAULT 'fa-folder',
    description TEXT
);

-- Resources Table
CREATE TABLE resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    category_id INT,
    uploader_id INT NOT NULL,
    subject VARCHAR(100),
    semester VARCHAR(20),
    tags VARCHAR(255),
    download_count INT DEFAULT 0,
    view_count INT DEFAULT 0,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (uploader_id) REFERENCES users(id)
);

-- Ratings Table
CREATE TABLE ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resource_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT CHECK (rating BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_rating (resource_id, user_id),
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Comments Table
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resource_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bookmarks Table
CREATE TABLE bookmarks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    resource_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_bookmark (user_id, resource_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE
);

-- Notifications Table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255),
    is_read TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default categories
INSERT INTO categories (name, icon, description) VALUES
('Notes', 'fa-sticky-note', 'Lecture notes and handwritten materials'),
('Previous Papers', 'fa-file-alt', 'Previous year question papers'),
('Textbooks', 'fa-book', 'E-books and textbook PDFs'),
('Lab Manuals', 'fa-flask', 'Practical and lab experiment guides'),
('Presentations', 'fa-chalkboard', 'Slides and presentation files'),
('Assignments', 'fa-tasks', 'Assignment questions and solutions'),
('Research Papers', 'fa-scroll', 'Academic research and journals'),
('Videos', 'fa-video', 'Educational video links and tutorials');

-- Insert default admin user (password: Admin@123)
INSERT INTO users (full_name, email, password, role, department) VALUES
('Admin', 'admin@academic.com', '$2y$10$XqHbgHJJsXWggbGNs6d3AuKoZtxyRanxZxcKo9O7ermsEw1ltA5CK', 'admin', 'Administration');
