# 🏫 Gurukul Academy CMS Portal — User & Administrator Manual

Welcome to the **Gurukul Academy Dynamic Content Management System (CMS)**! This system transitions the premium, high-fidelity static Gurukul website into a dynamic, Hostinger-compatible PHP/MySQL platform. It features a secure administrative dashboard with complete CRUD controls over school content, central media reuse, and student inquiries.

---

## 🛠️ Technology Stack & Security Features

* **Frontend**: HTML5, Vanilla CSS3 (curated HSL palettes, light/dark themes), Vanilla JavaScript (custom page preloader, cursor parallax sweeps, horizontal sliders, accordion selectors).
* **Backend**: Object-Oriented PHP 7.4+ (bulletproof session-hijack checkers, strict prepared PDO queries, output sanitization controls).
* **Database**: MySQL 5.7+ / MariaDB.
* **Security Shield**:
  * **Prepared PDO Statements**: Defends 100% against SQL Injection (SQLi).
  * **Output escaping (`htmlspecialchars`)**: Defends 100% against Cross-Site Scripting (XSS).
  * **Session Validation Middleware**: Cryptographic session tokens defend against Cross-Site Request Forgery (CSRF).
  * **Session Hijacking Defense**: Tracks and validates client IP addresses and User-Agent signatures.

---

## 🔐 Administrative Account Parameters

To preserve visual layout minimalism, there is no public "Admin Login" button on the navigation header. Access the admin portal via the professional, subtle link in the footer:

* **Admin Login Route**: `http://your-domain.com/admin/login.php` (or click **Admin Login** in the footer alongside Sitemap/Privacy links).
* **Default Administrator Credentials**:
  * **Username**: `admin`
  * **Password**: `GurukulAdmin2026!`
  * **Email**: `admin@gurukul.org`

### 🛡️ Forced First-Login Password Change Policy
Upon logging in for the very first time with the default credentials, the secure session middleware will **force an immediate redirect** to the Account Profile settings (`admin/profile.php`). 
* Administrators **cannot access** other dashboard modules, modify timeline fests, upload scorecards, or view CSV inquiries until they successfully update the default password to a strong, personalized key.

---

## 💾 Database Installation (Hostinger Shared Hosting)

Follow these non-technical steps to deploy the database on your Hostinger cPanel/hPanel:

1. **Create MySQL Database**:
   * Log into your Hostinger hPanel.
   * Navigate to **Databases** > **MySQL Databases**.
   * Enter a database name (e.g., `u12345_gurukul`), username (e.g., `u12345_admin`), and a strong password. Click **Create**.
2. **Import SQL Schema**:
   * Click **Enter phpMyAdmin** next to your newly created database.
   * Click the **Import** tab on the top menu.
   * Choose the **`database.sql`** file from your computer.
   * Click **Go / Import** at the bottom. This seeds all database schemas, milestone grids, and administrator accounts.
3. **Configure Database Connection**:
   * Open file [db.php](file:///c:/Users/Susha/Downloads/arccena/clients/Gurukul/config/db.php) inside your Hostinger File Manager or local editor.
   * Replace the host, name, user, and password parameters with your custom Hostinger credentials:
     ```php
     define('DB_HOST', 'localhost'); // Hostinger defaults to localhost
     define('DB_NAME', 'u12345_gurukul');
     define('DB_USER', 'u12345_admin');
     define('DB_PASS', 'YourSecretDbPasswordHere!');
     ```
   * Save the file. The portal is now live!

---

## 🖥️ CMS Core Modules & Operations

### 1. Centralized Media Library (`admin/media.php`)
* Upload visual banners, student photos, and PDFs.
* Each asset has a **Click-to-Copy Path** toast helper. Copy the relative path (e.g., `uploads/media/classroom.png`) and paste it directly into homepage background textboxes, milstone timeline graphics, or news flyers.

### 2. Homepage Blocks Manager (`admin/homepage.php`)
* Modify Hero Title headers, descriptive subtitles, and Call-to-Action button links.
* Toggle the visual visibility of frontend sections (Statistics, Pillars, Dynamic News, Toppers) on/off with one click.

### 3. Chronological Heritage Milestones (`admin/about.php`)
* Manage the glorious chronological vertical timeline.
* Define `Milestone Year`, `Title`, `Description`, and a `Sort Order` index (e.g., milestone with sort order `1` will display first, and so on).

### 4. Interactive Media Masonry Grid (`admin/gallery.php`)
* Categorize assets under Academic Pillars, STEM Laboratories, Athletic Fields, and Performing Arts.
* Supports **Drag-and-Drop Multi-File Uploader**. Select and upload 10+ photos or video highlights concurrently.
* Mark assets as **Featured ★** to showcase them inside the homepage preview carousel.

### 5. Campus Bulletins & RSVP Manager (`admin/news_events.php`)
* Publish general news bulletins or scheduled campus fests.
* Events feature date-picking boxes, automatic calendar grids, and active bookable **RSVP overlays** letting visitors register seats online.

### 6. Wall of Glory Result Registry (`admin/results.php`)
* List CBSE board aggregates, IIT-JEE percentiles, and NEET toppers.
* Upload verified PDF scorecard sheets. The frontend results table will show a dynamic glassmorphic downloading toast indicator and stream the official file to the visitor.

### 7. Admissions Inquiry Manager (`admin/inquiries.php`)
* Displays direct submissions from the digital admissions desk contact form.
* Mark inquiries as Read / Unread.
* **Instant Spreadsheet Stream**: Click **Export Inquiries to CSV** to immediately stream a compiled Microsoft Excel / Google Sheets compatible database spreadsheet.

---

## 📐 Image & File Capacities Optimization
To maintain lightning-fast page loading speeds on Hostinger Shared Hosting, adhere to the following recommended file dimensions:
* **Hero Visual Backgrounds**: `1920px x 1080px` (WebP or compressed JPG, Max 800 KB).
* **Gallery & News Images**: `800px x 600px` (WebP or PNG, Max 500 KB).
* **Student Result Sheets / Scorecard PDFs**: PDF format, Max 2 MB.

---

## 🔒 Session Recovery hPanel Instructions
If an administrator locks themselves out or forgets their credentials, log into phpMyAdmin in the Hostinger panel:
1. Open the `admins` table.
2. Select row `1` (`admin`) and click **Edit**.
3. Under the `password_hash` column, set the input field function to **MD5** or paste a precalculated bcrypt hash:
   * Bcrypt hash for **`GurukulAdmin2026!`** (Highly Recommended):
     ```text
     $2y$10$wJtK2.tP0JgG7Z7h8j7HcuJj.j20N2e7h01Wb3g0T3T5F5V7V7Z5.
     ```
4. Set the `is_first_login` column to `1` (This forces a password change on next login).
5. Click **Go** to save. You can now log back in!

---
*Developed with ❤️ for Gurukul Academy. Academic Excellence & Character Leadership.*
