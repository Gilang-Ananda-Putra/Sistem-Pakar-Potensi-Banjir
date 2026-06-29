CREATE DATABASE IF NOT EXISTS sistem_pakar_banjir CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistem_pakar_banjir;
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS activity_logs,diagnosis_results,consultation_details,consultations,rule_conditions,rules,symptoms,variables,users,roles,`references`;
SET FOREIGN_KEY_CHECKS=1;
CREATE TABLE roles(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(30) NOT NULL UNIQUE,description VARCHAR(120));
CREATE TABLE users(id INT AUTO_INCREMENT PRIMARY KEY,role_id INT NOT NULL,name VARCHAR(100) NOT NULL,email VARCHAR(120) NOT NULL UNIQUE,password VARCHAR(255) NOT NULL,is_active TINYINT(1) DEFAULT 1,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(role_id) REFERENCES roles(id));
CREATE TABLE variables(id INT AUTO_INCREMENT PRIMARY KEY,code VARCHAR(5) NOT NULL UNIQUE,name VARCHAR(80) NOT NULL,description TEXT);
CREATE TABLE `references`(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(180) NOT NULL,source VARCHAR(180),description TEXT);
CREATE TABLE symptoms(id INT AUTO_INCREMENT PRIMARY KEY,variable_id INT NOT NULL,code VARCHAR(10) NOT NULL UNIQUE,name VARCHAR(120) NOT NULL,category VARCHAR(80) NOT NULL,description TEXT,reference_id INT,FOREIGN KEY(variable_id) REFERENCES variables(id) ON DELETE CASCADE,FOREIGN KEY(reference_id) REFERENCES `references`(id) ON DELETE SET NULL);
CREATE TABLE rules(id INT AUTO_INCREMENT PRIMARY KEY,code VARCHAR(10) NOT NULL UNIQUE,diagnosis ENUM('Rendah','Sedang','Tinggi','Sangat Tinggi') NOT NULL,priority INT NOT NULL,rule_type ENUM('base','modifier') NOT NULL DEFAULT 'base',min_score INT NULL,max_score INT NULL,adjustment INT NOT NULL DEFAULT 0,explanation TEXT,recommendation TEXT,reference_id INT,is_active TINYINT(1) DEFAULT 1,FOREIGN KEY(reference_id) REFERENCES `references`(id) ON DELETE SET NULL);
CREATE TABLE rule_conditions(id INT AUTO_INCREMENT PRIMARY KEY,rule_id INT NOT NULL,symptom_id INT NOT NULL,operator ENUM('AND') DEFAULT 'AND',UNIQUE(rule_id,symptom_id),FOREIGN KEY(rule_id) REFERENCES rules(id) ON DELETE CASCADE,FOREIGN KEY(symptom_id) REFERENCES symptoms(id) ON DELETE CASCADE);
CREATE TABLE consultations(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,location VARCHAR(150),notes TEXT,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(user_id) REFERENCES users(id));
CREATE TABLE consultation_details(id INT AUTO_INCREMENT PRIMARY KEY,consultation_id INT NOT NULL,variable_id INT NOT NULL,symptom_id INT NOT NULL,FOREIGN KEY(consultation_id) REFERENCES consultations(id) ON DELETE CASCADE,FOREIGN KEY(variable_id) REFERENCES variables(id),FOREIGN KEY(symptom_id) REFERENCES symptoms(id));
CREATE TABLE diagnosis_results(id INT AUTO_INCREMENT PRIMARY KEY,consultation_id INT NOT NULL,rule_id INT NULL,diagnosis ENUM('Rendah','Sedang','Tinggi','Sangat Tinggi') NOT NULL,working_memory JSON,active_rules JSON,failed_rules JSON,inference_trace JSON,explanation TEXT,recommendation TEXT,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(consultation_id) REFERENCES consultations(id) ON DELETE CASCADE,FOREIGN KEY(rule_id) REFERENCES rules(id) ON DELETE SET NULL);
CREATE TABLE activity_logs(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT,action VARCHAR(80) NOT NULL,description TEXT,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL);
INSERT INTO roles VALUES(1,'admin','Administrator'),(2,'user','Pengguna');
INSERT INTO users(role_id,name,email,password) VALUES(1,'Administrator','admin@banjir.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.'),(2,'Pengguna','user@banjir.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.');
INSERT INTO `references` VALUES(1,'Laporan Akademik Sistem Pakar Potensi Banjir','Dokumen penelitian','Knowledge base dan rule base sesuai laporan akademik: 7 variabel, 22 gejala, 34 rule, forward chaining.');
INSERT INTO variables(code,name,description) VALUES('CH','Curah Hujan','Intensitas curah hujan'),('DH','Durasi Hujan','Lama hujan'),('KD','Kondisi Drainase','Kemampuan saluran air'),('KW','Ketinggian Wilayah','Elevasi wilayah'),('KS','Kedekatan Sungai','Jarak ke sungai'),('RB','Riwayat Banjir','Histori kejadian banjir'),('PD','Peringatan Dini','Status peringatan dini');
INSERT INTO symptoms(variable_id,code,name,category,description,reference_id) VALUES
(1,'CH1','Curah hujan rendah','Rendah','Hujan ringan',1),(1,'CH2','Curah hujan sedang','Sedang','Hujan sedang',1),(1,'CH3','Curah hujan tinggi','Tinggi','Hujan lebat',1),(1,'CH4','Curah hujan sangat tinggi','Sangat Tinggi','Hujan ekstrem',1),
(2,'DH1','Durasi hujan singkat','Rendah','Durasi hujan singkat',1),(2,'DH2','Durasi hujan sedang','Sedang','Durasi hujan sedang',1),(2,'DH3','Durasi hujan lama','Tinggi','Durasi hujan lama',1),
(3,'KD1','Drainase baik','Rendah','Aliran lancar',1),(3,'KD2','Drainase sedang','Sedang','Sebagian tersumbat',1),(3,'KD3','Drainase buruk','Tinggi','Banyak sumbatan atau tidak lancar',1),
(4,'KW1','Wilayah tinggi','Rendah','Elevasi aman',1),(4,'KW2','Wilayah sedang','Sedang','Elevasi sedang',1),(4,'KW3','Wilayah rendah','Tinggi','Dataran rendah',1),
(5,'KS1','Jauh dari sungai','Rendah','Jarak aman',1),(5,'KS2','Dekat sungai','Sedang','Dekat sungai',1),(5,'KS3','Sangat dekat sungai','Tinggi','Bantaran sungai',1),
(6,'RB1','Tidak pernah banjir','Rendah','Tidak ada histori',1),(6,'RB2','Jarang banjir','Sedang','Histori jarang',1),(6,'RB3','Sering banjir','Tinggi','Histori sering',1),
(7,'PD1','Tidak ada peringatan','Rendah','Status normal',1),(7,'PD2','Waspada','Sedang','Peringatan waspada',1),(7,'PD3','Siaga/Awas','Tinggi','Peringatan siaga atau awas',1);

INSERT INTO rules(code,diagnosis,priority,rule_type,min_score,max_score,adjustment,explanation,recommendation,reference_id) VALUES
('R01','Rendah',1,'base',3,3,0,'Rule dasar kombinasi utama CH × DH × KD dengan skor inti 3.','Ikuti rekomendasi sesuai tingkat potensi banjir.',1),
('R02','Rendah',2,'base',3,3,0,'Rule dasar kombinasi utama CH × DH × KD dengan skor inti 3.','Ikuti rekomendasi sesuai tingkat potensi banjir.',1),
('R03','Rendah',3,'base',3,3,0,'Rule dasar kombinasi utama CH × DH × KD dengan skor inti 3.','Ikuti rekomendasi sesuai tingkat potensi banjir.',1),
('R04','Rendah',4,'base',4,4,0,'Rule dasar kombinasi utama CH × DH × KD dengan skor inti 4.','Ikuti rekomendasi sesuai tingkat potensi banjir.',1),
('R05','Rendah',5,'base',4,4,0,'Rule dasar kombinasi utama CH × DH × KD dengan skor inti 4.','Ikuti rekomendasi sesuai tingkat potensi banjir.',1),
('R06','Rendah',6,'base',4,4,0,'Rule dasar kombinasi utama CH × DH × KD dengan skor inti 4.','Ikuti rekomendasi sesuai tingkat potensi banjir.',1),
('R07','Sedang',7,'base',5,5,0,'Rule dasar kombinasi utama CH × DH × KD dengan skor inti 5.','Ikuti rekomendasi sesuai tingkat potensi banjir.',1),
('R08','Sedang',8,'base',5,5,0,'Rule dasar kombinasi utama CH × DH × KD dengan skor inti 5.','Ikuti rekomendasi sesuai tingkat potensi banjir.',1),
('R09','Sedang',9,'base',5,5,0,'Rule dasar kombinasi utama CH × DH × KD dengan skor inti 5.','Ikuti rekomendasi sesuai tingkat potensi banjir.',1),
('R10','Sedang',10,'base',6,6,0,'Rule dasar kombinasi utama CH × DH × KD dengan skor inti 6.','Ikuti rekomendasi sesuai tingkat potensi banjir.',1),
('R11','Sedang',11,'base',6,6,0,'Rule dasar kombinasi utama CH × DH × KD dengan skor inti 6.','Ikuti rekomendasi sesuai tingkat potensi banjir.',1),
('R12','Sedang',12,'base',6,6,0,'Rule dasar kombinasi utama CH × DH × KD dengan skor inti 6.','Ikuti rekomendasi sesuai tingkat potensi banjir.',1),
('R13','Sedang',13,'base',7,7,0,'Rule dasar kombinasi utama CH × DH × KD dengan skor inti 7.','Ikuti rekomendasi sesuai tingkat potensi banjir.',1),
('R14','Sedang',14,'base',7,7,0,'Rule dasar kombinasi utama CH × DH × KD dengan skor inti 7.','Ikuti rekomendasi sesuai tingkat potensi banjir.',1),
('R15','Sedang',15,'base',7,7,0,'Rule dasar kombinasi utama CH × DH × KD dengan skor inti 7.','Ikuti rekomendasi sesuai tingkat potensi banjir.',1),
('R16','Tinggi',16,'base',8,8,0,'Rule dasar kombinasi utama CH × DH × KD dengan skor inti 8.','Ikuti rekomendasi sesuai tingkat potensi banjir.',1),
('R17','Tinggi',17,'base',8,8,0,'Rule dasar kombinasi utama CH × DH × KD dengan skor inti 8.','Ikuti rekomendasi sesuai tingkat potensi banjir.',1),
('R18','Tinggi',18,'base',8,8,0,'Rule dasar kombinasi utama CH × DH × KD dengan skor inti 8.','Ikuti rekomendasi sesuai tingkat potensi banjir.',1),
('R19','Tinggi',19,'base',9,9,0,'Rule dasar kombinasi utama CH × DH × KD dengan skor inti 9.','Ikuti rekomendasi sesuai tingkat potensi banjir.',1),
('R20','Tinggi',20,'base',9,9,0,'Rule dasar kombinasi utama CH × DH × KD dengan skor inti 9.','Ikuti rekomendasi sesuai tingkat potensi banjir.',1),
('R21','Tinggi',21,'base',9,9,0,'Rule dasar kombinasi utama CH × DH × KD dengan skor inti 9.','Ikuti rekomendasi sesuai tingkat potensi banjir.',1),
('R22','Sangat Tinggi',22,'base',10,10,0,'Rule dasar kombinasi utama CH × DH × KD dengan skor inti 10.','Ikuti rekomendasi sesuai tingkat potensi banjir.',1),
('R23','Sangat Tinggi',23,'base',10,10,0,'Rule dasar kombinasi utama CH × DH × KD dengan skor inti 10.','Ikuti rekomendasi sesuai tingkat potensi banjir.',1),
('R24','Sangat Tinggi',24,'base',10,10,0,'Rule dasar kombinasi utama CH × DH × KD dengan skor inti 10.','Ikuti rekomendasi sesuai tingkat potensi banjir.',1),
('R25','Sangat Tinggi',25,'base',11,12,0,'Rule dasar kombinasi utama CH × DH × KD dengan skor inti 11-12.','Ikuti rekomendasi sesuai tingkat potensi banjir.',1),
('R26','Sangat Tinggi',26,'modifier',NULL,NULL,1,'Rule modifikasi menaikkan level berdasarkan faktor KS3.','Terapkan mitigasi sesuai hasil akhir.',1),
('R27','Sangat Tinggi',27,'modifier',NULL,NULL,1,'Rule modifikasi menaikkan level berdasarkan faktor RB3.','Terapkan mitigasi sesuai hasil akhir.',1),
('R28','Sangat Tinggi',28,'modifier',NULL,NULL,1,'Rule modifikasi menaikkan level berdasarkan faktor PD3.','Terapkan mitigasi sesuai hasil akhir.',1),
('R29','Sangat Tinggi',29,'modifier',NULL,NULL,2,'Rule modifikasi menaikkan level berdasarkan faktor PD3.','Terapkan mitigasi sesuai hasil akhir.',1),
('R30','Sangat Tinggi',30,'modifier',NULL,NULL,1,'Rule modifikasi menaikkan level berdasarkan faktor KW3.','Terapkan mitigasi sesuai hasil akhir.',1),
('R31','Rendah',31,'modifier',NULL,NULL,-1,'Rule modifikasi menurunkan level berdasarkan faktor KW1.','Terapkan mitigasi sesuai hasil akhir.',1),
('R32','Rendah',32,'modifier',NULL,NULL,-1,'Rule modifikasi menurunkan level berdasarkan faktor KS1,RB1.','Terapkan mitigasi sesuai hasil akhir.',1),
('R33','Rendah',33,'modifier',NULL,NULL,-1,'Rule modifikasi menurunkan level berdasarkan faktor PD1,RB1.','Terapkan mitigasi sesuai hasil akhir.',1),
('R34','Sangat Tinggi',34,'modifier',NULL,NULL,1,'Rule modifikasi menaikkan level berdasarkan faktor KW3,KS3.','Terapkan mitigasi sesuai hasil akhir.',1);
INSERT INTO rule_conditions(rule_id,symptom_id) SELECT r.id,s.id FROM rules r CROSS JOIN symptoms s WHERE r.code='R26' AND s.code='KS3';
INSERT INTO rule_conditions(rule_id,symptom_id) SELECT r.id,s.id FROM rules r CROSS JOIN symptoms s WHERE r.code='R27' AND s.code='RB3';
INSERT INTO rule_conditions(rule_id,symptom_id) SELECT r.id,s.id FROM rules r CROSS JOIN symptoms s WHERE r.code='R28' AND s.code='PD3';
INSERT INTO rule_conditions(rule_id,symptom_id) SELECT r.id,s.id FROM rules r CROSS JOIN symptoms s WHERE r.code='R29' AND s.code='PD3';
INSERT INTO rule_conditions(rule_id,symptom_id) SELECT r.id,s.id FROM rules r CROSS JOIN symptoms s WHERE r.code='R30' AND s.code='KW3';
INSERT INTO rule_conditions(rule_id,symptom_id) SELECT r.id,s.id FROM rules r CROSS JOIN symptoms s WHERE r.code='R31' AND s.code='KW1';
INSERT INTO rule_conditions(rule_id,symptom_id) SELECT r.id,s.id FROM rules r CROSS JOIN symptoms s WHERE r.code='R32' AND s.code='KS1';
INSERT INTO rule_conditions(rule_id,symptom_id) SELECT r.id,s.id FROM rules r CROSS JOIN symptoms s WHERE r.code='R32' AND s.code='RB1';
INSERT INTO rule_conditions(rule_id,symptom_id) SELECT r.id,s.id FROM rules r CROSS JOIN symptoms s WHERE r.code='R33' AND s.code='PD1';
INSERT INTO rule_conditions(rule_id,symptom_id) SELECT r.id,s.id FROM rules r CROSS JOIN symptoms s WHERE r.code='R33' AND s.code='RB1';
INSERT INTO rule_conditions(rule_id,symptom_id) SELECT r.id,s.id FROM rules r CROSS JOIN symptoms s WHERE r.code='R34' AND s.code='KW3';
INSERT INTO rule_conditions(rule_id,symptom_id) SELECT r.id,s.id FROM rules r CROSS JOIN symptoms s WHERE r.code='R34' AND s.code='KS3';
