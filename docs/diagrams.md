# Dokumentasi Desain

## ERD
```mermaid
erDiagram
roles ||--o{ users : has
variables ||--o{ symptoms : contains
references ||--o{ symptoms : cites
references ||--o{ rules : cites
rules ||--o{ rule_conditions : has
symptoms ||--o{ rule_conditions : used_by
users ||--o{ consultations : performs
consultations ||--o{ consultation_details : has
variables ||--o{ consultation_details : selected
symptoms ||--o{ consultation_details : selected
consultations ||--|| diagnosis_results : produces
rules ||--o{ diagnosis_results : fires
users ||--o{ activity_logs : records
```

## UML Use Case
```mermaid
flowchart LR
Admin((Admin)) --> Login
User((User)) --> Login
Admin --> CRUDVariabel
Admin --> CRUDKnowledgeBase
Admin --> CRUDRuleBase
Admin --> CRUDPengguna
Admin --> Statistik
Admin --> Riwayat
User --> Konsultasi
User --> Hasil
User --> RiwayatSendiri
```

## Activity Diagram Konsultasi
```mermaid
flowchart TD
A[Login] --> B[Buka Form Konsultasi]
B --> C[Pilih 7 Gejala]
C --> D[Simpan Konsultasi]
D --> E[Jalankan Forward Chaining]
E --> F[Simpan Diagnosis]
F --> G[Tampilkan Hasil]
```

## Sequence Diagram
```mermaid
sequenceDiagram
actor U as User
participant F as Form Konsultasi
participant I as Mesin Inferensi
participant DB as MySQL
U->>F: input gejala
F->>DB: simpan konsultasi
F->>I: kirim working memory
I->>DB: ambil rules dan conditions
I-->>F: rule aktif/gagal dan diagnosis
F->>DB: simpan hasil
F-->>U: tampilkan hasil
```

## Class Diagram
```mermaid
classDiagram
class User{+id +role_id +email}
class Variable{+code +name}
class Symptom{+code +category}
class Rule{+code +diagnosis +priority}
class RuleCondition{+rule_id +symptom_id}
class Consultation{+user_id +location}
class DiagnosisResult{+diagnosis +trace}
Variable "1" --> "*" Symptom
Rule "1" --> "*" RuleCondition
Consultation "1" --> "1" DiagnosisResult
```

## Flowchart Forward Chaining
```mermaid
flowchart TD
A[Input Gejala] --> B[Working Memory]
B --> C[Ambil Rule]
C --> D{Semua kondisi rule ada di WM?}
D -- Ya --> E[Fire Rule dan catat aktif]
D -- Tidak --> F[Catat rule gagal]
E --> G{Rule berikutnya?}
F --> G
G -- Ya --> C
G -- Tidak --> H[Pilih rule aktif prioritas teratas]
H --> I[Kesimpulan Potensi]
```

## Flow Aplikasi
Landing Page → Login → Dashboard/Admin atau Konsultasi/User → Mesin Inferensi → Hasil Diagnosa → Laporan/Export.
