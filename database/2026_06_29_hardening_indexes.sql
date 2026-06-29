-- Migration hardening untuk instalasi yang sudah berjalan.
-- Aman dijalankan setelah database/sistem_pakar_banjir.sql diimpor.

ALTER TABLE consultations ADD INDEX idx_consultations_user_created (user_id, created_at);
ALTER TABLE diagnosis_results ADD INDEX idx_diagnosis_results_diagnosis (diagnosis);
ALTER TABLE consultation_details ADD UNIQUE KEY uq_consultation_variable (consultation_id, variable_id);
ALTER TABLE rules ADD INDEX idx_rules_active_priority (is_active, priority, id);
ALTER TABLE symptoms ADD INDEX idx_symptoms_variable (variable_id, id);
ALTER TABLE activity_logs ADD INDEX idx_activity_logs_user_created (user_id, created_at);
