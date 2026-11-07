# Bartolay Mart - Inventory & User Management System

> **Instructor Test Credentials to Approve an account:**
> - **Username:** `root`
> - **Password:** `root123`

A secure web-based inventory management and administrative control system built with PHP, MySQL, and custom vanilla CSS.

---

## 🔑 Role & Access Level Matrix

| Role Level | Dashboard Access | Manage Products | View Users | Remove Users | Approve/Reject Registrations |
| :--- | :---: | :---: | :---: | :---: | :---: |
| **`root`** | Super Admin | Yes | Yes | All non-root users | Yes |
| **`manager`** | Operational | Yes | Yes | `staff` users only | No |
| **`staff`** | Basic | Yes | View only | No | No |

---

## 🚀 Key Features

* **Dashboard Overview**: Live summary metric cards displaying total product count, aggregated inventory valuation in Philippine Pesos (`₱`), total registered system users, and real-time pending registration approvals.
* **Product Inventory Management**: Full inventory workflow (Add, Edit, Delete) with price formatting (`₱`), quantity tracking, and user authorship mapping (`created_by`).
* **Registration Approval Workflow**: Admin verification system where self-registered accounts remain in a `pending` state until the `root` account reviews and assigns operational roles (`staff` or `manager`).
* **Role-Based Deletion Protection**: Built-in permission checks preventing non-root users from deleting protected accounts or pending users.
* **Unified Custom Modal Dialogues**: Replaces browser-native popups (`alert`/`confirm`) with custom HTML/CSS overlay modals across all actions (deletion, approvals, rejections, and logouts).

---

## 🛠️ Tech Stack & Dependencies

* **Language**: PHP 8.x (MySQLi prepared statements)
* **Database**: MySQL / MariaDB (InnoDB engine, foreign keys)
* **Styling & UI**: Custom CSS3, Lucide Icons (`unpkg.com/lucide@latest`)
* **Environment**: Windows 11 / XAMPP (Apache)

---

## 📁 Repository File Structure

```text
bartolay-mart/
├── approvals.php   # Root panel for approving/rejecting user registrations
├── config.php      # Database configuration & session initialization
├── dashboard.php   # Central metrics overview & user role dashboard
├── index.php       # Authentication entry point (Sign-in handler)
├── logout.php      # Secure session destruction endpoint
├── products.php    # Product catalog listing and CRUD operations
├── register.php    # Public account registration interface
├── schema.sql      # Database structure & root account seed script
├── style.css       # Unified design system and modal layout styles
└── users.php       # Administrative user oversight and role management
```

---

## 📌 Setup & Installation Instructions

1. **Clone or copy repository** into your XAMPP `htdocs` directory:
   ```bash
   git clone https://github.com/reinfection/bartolay-mart.git
   ```

2. **Start Local Server Services**:
   * Open the **XAMPP Control Panel**.
   * Start both **Apache** and **MySQL** modules.

3. **Import Database Schema**:
   * Open **phpMyAdmin** in your browser (`http://localhost/phpmyadmin`).
   * Navigate to the **SQL** tab.
   * Paste the contents of `schema.sql` (or import the file directly) and click **Go** to create the `bartolay_mart` database, tables, and default seed root user.

4. **Verify Database Configuration**:
   * Open `config.php` and verify your local MySQL credentials:
     ```php
     define('DB_SERVER', 'localhost');
     define('DB_USERNAME', 'root');
     define('DB_PASSWORD', '');
     define('DB_NAME', 'bartolay_mart');
     ```

5. **Launch Application**:
   * Open your web browser and navigate to:
     ```text
     http://localhost/linuxlocalhost
     ```
   * Log in using the **root** test credentials listed above.

6. **Test User Registration & Admin Approval Workflow**:
   - Open your browser and navigate to `http://localhost/linuxlocalhost` and click "Create Account".
   - Register a new test account (e.g., username: `example`, password: `anypassuwant`).
   - Log out if logged in, then sign in with the admin credentials (`root` / `root123`).
   - Access **Registration Approvals** from the sidebar, select a role (`Staff` or `Manager`), and click **Approve**.
   - Log out as `root` and sign back in using your newly registered account to test user-level access.
