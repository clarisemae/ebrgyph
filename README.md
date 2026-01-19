# eBrgyPH

**eBrgyPH** is a modern, full-stack barangay management system designed to streamline administrative processes and enhance community engagement. Originally developed using PHP, this project has been rebuilt to provide a more responsive and scalable experience for both administrators and residents.

## 🚀 Overview

The system is divided into two primary platforms:
* **Admin Portal (Web):** A centralized dashboard for barangay officials to manage records, requests, and announcements.
* **Resident App (Mobile):** A mobile application for citizens to access services, request documents, and receive updates directly from their local government.

## 🛠 Tech Stack

### Frontend
* **Admin Dashboard:** React.js (Web)
* **Resident App:** React Native with Expo (Mobile)

### Backend
* **Runtime:** Node.js
* **Database:** MySQL

## ✨ Key Features

* **Document Requests:** Streamlined processing for Barangay Clearance, Indigency, and Residency certificates.
* **Blotter Management:** Secure digital recording and tracking of incidents.
* **Community Announcements:** Real-time updates and news posted by officials.
* **Resident Profiling:** Organized database of household and resident information.
* **User Authentication:** Secure login for both administrators and residents.

## 📂 Project Structure

```text
ebrgyph/
* **`admin/`**: Contains the core logic for the administrative dashboard and official functions.
* **`user/`**: Frontend components and logic for resident-facing features.
* **`a.database/`**: SQL scripts and database schema files for system setup.
* **`composer.json`**: Manages the project dependencies and PHP packages.
* **`.env`**: Configuration file for environment variables and database connections.
