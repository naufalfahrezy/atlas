# ATLAS

Aplikasi Terpadu Logistik, Aset, dan Sistem Presensi.

## Architecture

Browser
-> CodeIgniter Web
-> CodeIgniter API Gateway
-> Laravel Microservices
-> Supabase PostgreSQL / Storage

RabbitMQ digunakan khusus untuk notifikasi asinkron.

## Development Ports

8080  Aplikasi Web ATLAS
8081  API Gateway

8101  Auth & User Service
8102  Asset Service
8103  Borrowing Service
8104  Approval Service
8105  QR Handover Service
8106  Procurement Budget Service
8107  Practicum Schedule Service
8108  Attendance & BAP Service
8109  Honorarium Service
8110  Reporting Service
8111  Notification Service

5672  RabbitMQ AMQP
15672 RabbitMQ Management UI

## Database

Schema database dikelola menggunakan SQL scripts pada:

database/sql/

Laravel dan CodeIgniter migration tidak digunakan sebagai sumber perubahan schema.