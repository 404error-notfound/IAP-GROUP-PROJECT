# Go Puppy Go 🐶

Go Puppy Go is a web-based platform where users can browse different dog breeds and adopt puppies directly from registered owners. The system allows users to sign up, verify their emails, log in, and access a directory of puppies available for adoption. Owners can list puppies with mandatory adoption fees, and users can view breed information stored in the system.

---

## 📌 Project Overview

* **Tech Stack:** PHP, MariaDB, HTML/CSS/JS
* **Purpose:** Create an adoption platform connecting owners and adopters.
* **Target Completion:** Mid-November 2025

---

## ⚙️ Functional Requirements

1. **User Management**

   * User registration with email verification.
   * Secure login/logout.
   * Profile management.

2. **Puppy Listings**

   * Owners can add puppies with details (name, age, breed, description, adoption fee).
   * Adoption fee is mandatory.
   * Upload and store puppy images.

3. **Breed Information**

   * Admin can manually upload breed info to the breeds table.
   * Users can browse breed details.

4. **Adoption Process**

   * Users can browse puppies and express interest in adoption.
   * Simple log of adoption interactions stored.

5. **Admin Management**

   * Admin dashboard to manage users, breeds, and adoption logs.

---

## 📊 Non-Functional Requirements

1. **Performance** – Pages should load in under 3 seconds.
2. **Security** – Password hashing, input validation, and secure session handling.
3. **Scalability** – Database structured for growth in users and puppy listings.
4. **Usability** – Simple, mobile-friendly UI.
5. **Reliability** – System uptime of at least 95%.

---

## 🗂️ Project Modules

1. **Authentication Module** – Registration, login, email verification.
2. **User Dashboard Module** – Manage profile, view puppies, adoption history.
3. **Puppy Listings Module** – Add/manage puppy details, mandatory adoption fees.
4. **Breed Information Module** – Upload and display breed info.
5. **Adoption Module** – Browse and request adoption, log interactions.
6. **Admin Module** – Manage users, breeds, and adoption logs.

---

## 📅 Timeline & Milestones

**Week 1-2 (Sept 21 - Oct 4):**

* Set up GitHub repo & file structure.
* Configure PHP & MariaDB environment.
* Design database schema.

**Week 3 (Oct 5 - Oct 11):**

* Implement authentication (sign up, email verification, login).
* Build basic UI templates.

**Week 4 (Oct 12 - Oct 18):**

* Puppy Listings module.
* Breed information module.

**Week 5 (Oct 19 - Oct 25):**

* Adoption module (logs, interest forms).
* Admin dashboard basics.

**Week 6 (Oct 26 - Nov 1):**

* Testing functional requirements.
* Fix bugs and refine UI.

**Week 7 (Nov 2 - Nov 8):**

* Finalize non-functional requirements (performance, security).
* Prepare documentation.

**Week 8 (Nov 9 - Nov 15):**

* Project review, polish, and final submission.

---

## 🚀 How to Run the Project

1. Clone the repo:

   ```bash
   git clone https://github.com/yourusername/go-puppy-go.git
   ```
2. Import the SQL database schema from `/database/schema.sql`.
3. Configure database connection in `/config/db.php`.
4. Start your PHP server:

   ```bash
   php -S localhost
   ```
5. Access the app at `http://localhost`.

---

## ✨ Future Improvements

* Advanced search & filters (age, breed, location).
* Payment gateway integration for adoption fees.
* Notifications (email/SMS) for adoption updates.
* Chat feature between owners and adopters.

---

## 👥 Team

* **Angela Omondi** 
* **Eliud Obosi** 
* **Wilson Mutua** 

---


