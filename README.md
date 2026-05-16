# RESC-QR: Rapid Emergency Status Checking via Quick Response

> A QR code-based student emergency monitoring system for the University of Southeastern Philippines (USeP) — designed to replace manual headcount processes during earthquake emergencies with real-time, digital student accountability.

---

## Table of Contents

- [Overview](#overview)
- [Problem Statement](#problem-statement)
- [System Objectives](#system-objectives)
- [Key Features](#key-features)
- [User Roles](#user-roles)
- [System Architecture (MVC)](#system-architecture-mvc)
- [MVC Directory Structure](#mvc-directory-structure)
- [Database Design](#database-design)
- [Technology Stack](#technology-stack)
- [System Workflow](#system-workflow)
- [Hardware & Software Requirements](#hardware--software-requirements)
- [Installation & Setup](#installation--setup)
- [Security Features](#security-features)
- [Testing Plan](#testing-plan)

---

## Overview

**RESC-QR** (Rapid Emergency Status Checking via Quick Response) is a web and mobile-based system that uses QR codes to monitor and record student attendance during emergency situations, particularly earthquakes. The system enables class mayors to scan student QR codes at evacuation areas, automatically updating each student's status as **Safe**, **Missing**, or **Not in Class** in real time.

The system integrates:
- **QR-based scanning** for rapid student identification
- **Real-time status dashboard** for administrators and emergency responders
- **Offline capability** with automatic data synchronization
- **Emergency hotline access** for immediate communication
- **Automated report generation** for institutional documentation

---

## Problem Statement

At the University of Southeastern Philippines (USeP), student attendance and accountability during earthquake emergencies are handled through **manual methods** — verbal headcounts, paper-based class lists, and sequential hand-off reports between class mayors, emergency responders, and monitoring officers.

### PIECES Analysis Summary

| Component    | Problem                                             | Proposed Solution                                    |
|:-------------|:----------------------------------------------------|:-----------------------------------------------------|
| Performance  | Slow and delayed student accounting                 | QR code scanning enables real-time status updates    |
| Information  | Inaccurate/incomplete student status                | Centralized database updates status instantly        |
| Economics    | Inefficient use of time and manpower                | Automated scanning reduces manual effort             |
| Control      | No structured tracking of student movement          | QR-based tracking enforced per student               |
| Efficiency   | Time-consuming paper-based verification             | Fast QR scanning improves verification speed         |
| Service      | Poor coordination during emergencies                | Real-time mobile and web monitoring system           |

---

## System Objectives

### General Objective
- Develop an attendance tracking system that enables class mayors to efficiently monitor and account for students after earthquake incidents.

### Specific Objectives
- Design a system that records daily attendance of students per classroom
- Allow class mayors to update student states during emergencies (Safe / Missing)
- Provide one-click access to Philippine emergency hotlines (e.g., 911, 117)
- Implement attendance conversion: "Present" → requires confirmation, "Absent" → auto-marked "Not in Class", "No response" → flagged as "Missing"
- Design a user-friendly interface usable under high-pressure emergency conditions
- Implement QR scanning with visual list of previously scanned students with profile images
- Integrate communication module for calling missing students or emergency contacts

---

## Key Features

| Feature                              | Description                                                                 |
|:-------------------------------------|:----------------------------------------------------------------------------|
| 🔲 QR Code Generation               | Unique QR codes generated for each registered student                       |
| 📱 QR Code Scanning                 | Class mayors scan student QR codes via mobile camera                        |
| 📊 Real-Time Status Dashboard       | Live display of Safe, Missing, and Unscanned students                       |
| 🔴 Auto-Flag Missing Students       | Unscanned students automatically flagged as "Missing" after timeout         |
| 📋 Emergency Report Generation      | Printable reports with timestamps and status summaries                      |
| 📞 Emergency Hotline Access         | One-tap dialing to pre-configured emergency numbers                         |
| 📶 Offline Mode                     | QR scans stored locally and synced when connection is restored              |
| 🔐 Role-Based Access Control        | Separate interfaces and permissions for Admin, Class Mayor, and Student     |
| 📝 QR Scan Logs                     | Full audit trail of all scanning activities                                 |
| 🔔 Real-Time Notifications          | Emergency alerts sent to students and responders                            |

---

## User Roles

### 1. Administrator (Web Dashboard)
- Register and manage student profiles
- Generate and assign unique QR codes
- Monitor emergency status dashboard in real time
- Generate and export emergency reports
- View QR scan logs and audit trail
- Manage class and section records

### 2. Class Mayor (Mobile App)
- Scan student QR codes during evacuation
- View real-time list of Safe and Missing students
- Contact emergency hotlines with one tap
- Call missing students or their emergency contacts
- Operate in offline mode during connectivity issues
- Submit scan session data for synchronization

### 3. Student (Mobile App)
- View personal QR code for scanning
- View own emergency status
- Receive emergency notifications
- Update personal contact information

---

## System Architecture (MVC)

The system follows the **Model-View-Controller (MVC)** architectural pattern to ensure clean separation of concerns, making the codebase easier to **maintain**, **scale**, and **test**, while allowing developers to work on different parts of the application simultaneously.

```
┌─────────────────────────────────────────────────────────────────┐
│                         CLIENT LAYER                            │
│  ┌─────────────────────┐    ┌────────────────────────────────┐  │
│  │   Web Application   │    │      Mobile Application        │  │
│  │  (Admin Dashboard)  │    │  (Class Mayor / Student App)   │  │
│  │  HTML, CSS, JS      │    │  HTML, CSS, JS (Responsive)    │  │
│  └────────┬────────────┘    └──────────────┬─────────────────┘  │
│           │                                │                    │
├───────────┼────────────────────────────────┼────────────────────┤
│           │         CONTROLLER LAYER       │                    │
│           └───────────────┬────────────────┘                    │
│                           ▼                                     │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │              PHP Controllers (Router)                    │    │
│  │  AuthController · StudentController · QRController      │    │
│  │  ScanController · EventController · ReportController    │    │
│  │  DashboardController · AttendanceController             │    │
│  └────────────────────────┬────────────────────────────────┘    │
│                           │                                     │
├───────────────────────────┼─────────────────────────────────────┤
│                           │         MODEL LAYER                 │
│                           ▼                                     │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │                 PHP Models (Business Logic)              │    │
│  │  Student · Class · ClassMayor · Admin · EmergencyEvent  │    │
│  │  QRScanLog · StudentStatus · Attendance                 │    │
│  │  OfflineScanBuffer · EmergencyContact · IncidentReport  │    │
│  └────────────────────────┬────────────────────────────────┘    │
│                           │                                     │
├───────────────────────────┼─────────────────────────────────────┤
│                           │       DATABASE LAYER                │
│                           ▼                                     │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │                    MySQL Database                        │    │
│  │  Tables · Views · Stored Procedures · Triggers          │    │
│  └─────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────┘
```

### Why MVC?

| Benefit                | Description                                                             |
|:-----------------------|:------------------------------------------------------------------------|
| **Separation of Concerns** | Business logic (Model), presentation (View), and routing (Controller) are isolated |
| **Maintainability**    | Changes to the UI don't affect the database layer and vice versa        |
| **Scalability**        | New modules (e.g., SMS alerts) can be added without rewriting existing code |
| **Testability**        | Models and controllers can be unit-tested independently                 |
| **Team Collaboration** | Frontend and backend developers can work in parallel                    |
| **Reusability**        | Models can serve both web and mobile/API interfaces                     |

---

## MVC Directory Structure

```
RESC-QR/
│
├── index.php                        # Application entry point & front controller
├── .htaccess                        # URL rewriting rules (clean URLs)
│
├── config/
│   ├── database.php                 # Database connection configuration
│   ├── app.php                      # Application-wide settings
│   └── routes.php                   # Route definitions
│
├── app/
│   ├── controllers/                 # CONTROLLERS — Handle requests & responses
│   │   ├── AuthController.php       # Login, logout, session management
│   │   ├── DashboardController.php  # Admin & mayor dashboard logic
│   │   ├── StudentController.php    # CRUD operations for student records
│   │   ├── QRController.php         # QR code generation & management
│   │   ├── ScanController.php       # QR scan processing & validation
│   │   ├── AttendanceController.php # Daily attendance tracking
│   │   ├── EventController.php      # Emergency event creation & management
│   │   ├── ReportController.php     # Report generation & export
│   │   └── ContactController.php    # Emergency contact management
│   │
│   ├── models/                      # MODELS — Business logic & database interaction
│   │   ├── Student.php              # Student data operations
│   │   ├── ClassModel.php           # Class/section data operations
│   │   ├── ClassMayor.php           # Class mayor data operations
│   │   ├── Admin.php                # Administrator data operations
│   │   ├── QRScanLog.php            # QR scan log operations
│   │   ├── StudentStatus.php        # Emergency status operations
│   │   ├── Attendance.php           # Attendance record operations
│   │   ├── EmergencyEvent.php       # Emergency event operations
│   │   ├── EmergencyContact.php     # Emergency contact operations
│   │   ├── OfflineScanBuffer.php    # Offline scan sync operations
│   │   └── IncidentReport.php       # Incident report operations
│   │
│   ├── views/                       # VIEWS — Presentation layer (HTML/CSS/JS)
│   │   ├── layouts/
│   │   │   ├── header.php           # Common page header & navigation
│   │   │   ├── footer.php           # Common page footer
│   │   │   └── sidebar.php          # Sidebar navigation
│   │   │
│   │   ├── auth/
│   │   │   ├── login.php            # Login page
│   │   │   └── forgot_password.php  # Password recovery page
│   │   │
│   │   ├── admin/
│   │   │   ├── dashboard.php        # Admin emergency status dashboard
│   │   │   ├── students/
│   │   │   │   ├── index.php        # Student list with search/filter
│   │   │   │   ├── create.php       # Register new student form
│   │   │   │   └── edit.php         # Edit student profile
│   │   │   ├── qr_codes.php         # QR code generation & management
│   │   │   ├── reports.php          # Emergency reports view
│   │   │   ├── scan_logs.php        # QR scan audit log
│   │   │   └── events.php           # Emergency event management
│   │   │
│   │   ├── mayor/
│   │   │   ├── dashboard.php        # Class mayor overview
│   │   │   ├── scanner.php          # QR code scanning interface
│   │   │   ├── student_list.php     # Scanned/missing students list
│   │   │   └── hotlines.php         # Emergency hotline quick-dial
│   │   │
│   │   └── student/
│   │       ├── dashboard.php        # Student status view
│   │       ├── qr_code.php          # Personal QR code display
│   │       └── profile.php          # Profile & contact management
│   │
│   └── helpers/                     # Utility functions
│       ├── session_helper.php       # Session management utilities
│       ├── url_helper.php           # URL generation utilities
│       └── qr_helper.php           # QR code generation utilities
│
├── core/                            # MVC core framework
│   ├── App.php                      # Main application router
│   ├── Controller.php               # Base controller class
│   ├── Model.php                    # Base model class (DB abstraction)
│   └── Database.php                 # PDO database connection handler
│
├── public/                          # Publicly accessible assets
│   ├── css/
│   │   ├── style.css                # Global styles
│   │   ├── dashboard.css            # Dashboard-specific styles
│   │   └── scanner.css              # Scanner interface styles
│   ├── js/
│   │   ├── app.js                   # Core application JavaScript
│   │   ├── scanner.js               # QR scanning logic
│   │   ├── dashboard.js             # Real-time dashboard updates
│   │   └── offline.js               # Offline mode & sync logic
│   └── img/
│       ├── logo.png                 # Application logo
│       └── profiles/                # Student profile images
│
├── storage/
│   ├── qr_codes/                    # Generated QR code images
│   ├── reports/                     # Generated PDF reports
│   └── logs/                        # Application logs
│
└── database/
    ├── resc_qr.sql                  # Full database schema
    ├── seed.sql                     # Sample data for testing
    └── migrations/                  # Database migration files
```

---

## Database Design

### Entity Relationship Diagram (Tables)

The database follows **Third Normal Form (3NF)** to eliminate redundancy and ensure data integrity.

| Table                  | Description                                        | Key Columns                                                         |
|:-----------------------|:---------------------------------------------------|:--------------------------------------------------------------------|
| `STUDENT`              | Student profiles and QR code values                | `student_id` (PK), `class_id` (FK), `first_name`, `last_name`, `course`, `year_level`, `qr_code_value`, `profile_status` |
| `CLASS`                | Class/section information                          | `class_id` (PK), `section_name`, `program`, `year_level`            |
| `CLASS_MAYOR`          | Class mayor accounts                               | `mayor_id` (PK), `class_id` (FK), `name`, `email` (UQ), `password_hash` |
| `ADMIN`                | Administrator accounts                             | `admin_id` (PK), `name`, `email` (UQ), `password_hash`, `role`      |
| `EMERGENCY_EVENT`      | Emergency event instances                          | `event_id` (PK), `event_type`, `event_datetime`, `description`, `created_by` (FK) |
| `QR_SCAN_LOG`          | Log of all QR scan activities                      | `scan_id` (PK), `student_id` (FK), `event_id` (FK), `scanned_by` (FK), `scan_time`, `scan_result` |
| `STUDENT_STATUS`       | Student safety status per event                    | `status_id` (PK), `student_id` (FK), `event_id` (FK), `status`, `updated_at` |
| `ATTENDANCE`           | Daily classroom attendance records                 | `attendance_id` (PK), `student_id` (FK), `date`, `status`           |
| `EMERGENCY_CONTACT`    | Student emergency contact information              | `contact_id` (PK), `student_id` (FK), `contact_name`, `relationship`, `phone_number` |
| `OFFLINE_SCAN_BUFFER`  | Temporarily stored offline scans                   | `buffer_id` (PK), `student_id` (FK), `event_id` (FK), `scanned_by` (FK), `scan_time`, `synced_at` |
| `INCIDENT_REPORT`      | Generated emergency reports                        | `report_id` (PK), `event_id` (FK), `generated_by` (FK), `report_time`, `summary_text` |

### Database Views
- `vw_student_profile` — Combined student + class information
- `vw_event_status_summary` — Aggregated Safe/Missing/Not-in-class counts per event
- `vw_missing_students` — List of missing students per event
- `vw_scan_audit` — Detailed scan log with student and mayor names

### Stored Procedures
- `sp_register_student()` — Register new student with validation
- `sp_log_qr_scan()` — Log a QR scan and auto-update status to "Safe" if valid
- `sp_sync_offline_scans()` — Bulk sync buffered offline scans to main tables
- `sp_generate_incident_report()` — Create timestamped incident report

### Triggers
- `trg_before_scan_insert` — Validates student_id exists before accepting a scan
- `trg_after_scan_insert` — Auto-updates `STUDENT_STATUS` to "Safe" on valid scan
- `trg_after_offline_sync` — Fires after offline buffer sync completion

---

## Technology Stack

| Layer       | Technology                                              |
|:------------|:--------------------------------------------------------|
| Frontend    | HTML5, CSS3, JavaScript                                 |
| Backend     | PHP (MVC Architecture)                                  |
| Database    | MySQL                                                   |
| Server      | Laragon / XAMPP (Apache + MySQL)                        |
| QR Library  | PHP QR Code library (e.g., `endroid/qr-code`)           |
| JS Scanner  | `html5-qrcode` or `instascan` for camera-based scanning |
| IDE         | Visual Studio Code                                      |

---

## System Workflow

```
┌──────────────────────────────────────────────────────────────────────────┐
│                        RESC-QR Emergency Workflow                        │
├──────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  ① PREPARATION PHASE (Admin)                                            │
│     Register Students → Generate QR Codes → Assign to Classes           │
│                                                                          │
│  ② DAILY OPERATION                                                      │
│     Class Mayor records daily attendance (Present / Absent)              │
│                                                                          │
│  ③ EARTHQUAKE OCCURS                                                    │
│     └─→ Students evacuate to designated assembly areas                  │
│                                                                          │
│  ④ EMERGENCY SCANNING (Class Mayor - Mobile App)                        │
│     └─→ Open RESC-QR App                                               │
│     └─→ Scan student QR codes at evacuation area                        │
│     └─→ System updates status to "SAFE" with timestamp                  │
│     └─→ Offline? Data stored locally, synced when online                │
│                                                                          │
│  ⑤ AUTO-DETECTION (System)                                              │
│     └─→ After scanning window (e.g., 10 minutes):                       │
│         • Unscanned "Present" students → flagged as "MISSING"           │
│         • "Absent" students → marked as "Not in Class"                  │
│                                                                          │
│  ⑥ REAL-TIME DASHBOARD (Admin)                                          │
│     └─→ Dashboard displays: Safe ✅ | Missing ❌ | Not in Class ⚪       │
│     └─→ Missing student alerts sent to emergency responders             │
│                                                                          │
│  ⑦ EMERGENCY RESPONSE                                                   │
│     └─→ Mayor can call missing student / emergency contact / hotlines   │
│     └─→ Emergency responders focus search on flagged students           │
│                                                                          │
│  ⑧ REPORT GENERATION                                                    │
│     └─→ System generates incident report with:                          │
│         • List of students marked SAFE (with scan timestamps)           │
│         • List of students marked MISSING                               │
│         • Students marked ABSENT (excluded from missing list)           │
│                                                                          │
└──────────────────────────────────────────────────────────────────────────┘
```

---

## Hardware & Software Requirements

### Hardware

| User Type             | Requirements                                                |
|:----------------------|:------------------------------------------------------------|
| Students              | Smartphone (Android/iOS) with camera, minimum 2GB RAM       |
| Class Mayor           | Smartphone with high-quality camera, minimum 4GB RAM        |
| Administrator         | Desktop/Laptop, Intel i3+, 4GB RAM (8GB recommended)        |
| Server (Local)        | Server machine or cloud, minimum 8GB RAM, 256GB+ storage    |

### Software

| Component             | Requirements                                                |
|:----------------------|:------------------------------------------------------------|
| Web Browser           | Google Chrome, Microsoft Edge                               |
| Frontend              | HTML, CSS, JavaScript                                       |
| Backend               | PHP                                                         |
| Database              | MySQL via Laragon or XAMPP                                   |
| IDE                   | Visual Studio Code or similar                               |
| Network               | Wired or wireless campus network infrastructure             |

---

## Installation & Setup

### Prerequisites
- **Laragon** or **XAMPP** installed (Apache + MySQL + PHP)
- **PHP 7.4+** with PDO extension enabled
- **MySQL 5.7+**

### Steps

```bash
# 1. Clone the repository
git clone https://github.com/your-repo/RESC-QR.git

# 2. Place in your web server directory
#    Laragon: C:\laragon\www\RESC-QR
#    XAMPP:   C:\xampp\htdocs\RESC-QR

# 3. Create the database
#    Open phpMyAdmin → Create database "resc_qr"

# 4. Import the database schema
#    Import database/resc_qr.sql

# 5. Configure database connection
#    Edit config/database.php with your credentials

# 6. Access the application
#    Open browser → http://localhost/RESC-QR
```

---


### Security Summary

```
┌─────────────────────────────────────────────────────────────────┐
│                   RESC-QR Security Layers                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  🔒 Layer 1: INPUT VALIDATION                                  │
│     Sanitize all inputs · Validate types & formats              │
│     Reject malformed data before processing                     │
│                                                                 │
│  🛡️ Layer 2: SQL INJECTION PREVENTION                           │
│     PDO Prepared Statements · Parameterized queries             │
│     No raw SQL concatenation anywhere                           │
│                                                                 │
│  🔑 Layer 3: AUTHENTICATION & PASSWORDS                        │
│     Bcrypt hashing · Password policies · Session management     │
│     Rate limiting · Generic error messages                      │
│                                                                 │
│  🚫 Layer 4: AUTHORIZATION (RBAC)                               │
│     Role-based middleware · Route protection                    │
│     Admin / Mayor / Student permission levels                   │
│                                                                 │
│  🧹 Layer 5: OUTPUT ENCODING                                   │
│     XSS prevention · htmlspecialchars() on all output           │
│     CSRF tokens on all forms                                    │
│                                                                 │
│  📦 Layer 6: DATA PROTECTION                                   │
│     Encrypted sensitive data · Secure cookies                   │
│     Credentials outside web root · .gitignore secrets           │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## Testing Plan

The system will undergo three testing phases:

| Phase            | Description                                                                      |
|:-----------------|:---------------------------------------------------------------------------------|
| **Unit Testing** | Test individual functions and components (login, QR scan, status update, etc.)    |
| **Alpha Testing**| Internal testing by developers under controlled, realistic conditions             |
| **Beta Testing** | Testing with selected students, class mayors, and faculty in drill simulations    |

### Key Test Cases

| ID     | Test Case                        | Expected Result                                  |
|:-------|:---------------------------------|:-------------------------------------------------|
| TC1.1  | Admin Login                      | Dashboard opens with correct role access          |
| TC1.2  | Register Student                 | Student record saved, QR code generated           |
| TC2.1  | Mayor scans QR code              | Student status updated to "Safe"                  |
| TC2.2  | Missing student auto-detection   | Unscanned students flagged as "Missing"           |
| TC3.1  | Offline QR scan                  | Scan stored locally, synced after reconnection    |
| TC4.1  | Mobile responsiveness            | Interface adjusts properly on all device sizes    |
| TC4.2  | Unauthorized access restriction  | Invalid credentials denied access                 |
| TC4.3  | Real-time dashboard updates      | Dashboard reflects scans instantly                |

---

## License

This project is developed as an academic capstone project for the **University of Southeastern Philippines (USeP) — Tagum Unit**.

---

> **RESC-QR** — *Because every second counts when lives are at stake.* 🚨