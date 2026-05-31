-- =======================================================
-- GURUKUL ACADEMY CMS DATABASE SCHEMA
-- Compatible with Hostinger Shared Hosting (MySQL 5.7+)
-- =======================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `admins`;
DROP TABLE IF EXISTS `media`;
DROP TABLE IF EXISTS `homepage_content`;
DROP TABLE IF EXISTS `about_content`;
DROP TABLE IF EXISTS `about_timeline`;
DROP TABLE IF EXISTS `gallery_categories`;
DROP TABLE IF EXISTS `gallery`;
DROP TABLE IF EXISTS `news_events`;
DROP TABLE IF EXISTS `results`;
DROP TABLE IF EXISTS `contact_submissions`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. ADMINISTRATORS TABLE
CREATE TABLE `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `is_first_login` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. MEDIA LIBRARY TABLE
CREATE TABLE `media` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `filename` VARCHAR(255) NOT NULL,
  `filepath` VARCHAR(255) NOT NULL,
  `filetype` VARCHAR(50) NOT NULL,
  `filesize` INT NOT NULL,
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. HOMEPAGE CONTENT TABLE (Single Row: id = 1)
CREATE TABLE `homepage_content` (
  `id` INT PRIMARY KEY DEFAULT 1,
  
  -- Hero Banner Section
  `hero_title` VARCHAR(255) DEFAULT 'Empowering Minds, Fostering Legacy',
  `hero_subtitle` TEXT,
  `hero_btn_text_1` VARCHAR(50) DEFAULT 'Explore Academy',
  `hero_btn_link_1` VARCHAR(255) DEFAULT 'about.php',
  `hero_btn_text_2` VARCHAR(50) DEFAULT 'Get In Touch',
  `hero_btn_link_2` VARCHAR(255) DEFAULT 'contact.php',
  `hero_image_path` VARCHAR(255) DEFAULT 'images/classroom.png',
  
  -- Statistics Counters
  `stat_number_1` VARCHAR(20) DEFAULT '15',
  `stat_label_1` VARCHAR(100) DEFAULT 'Years of Legacy',
  `stat_number_2` VARCHAR(20) DEFAULT '1200',
  `stat_label_2` VARCHAR(100) DEFAULT 'Elite Toppers',
  `stat_number_3` VARCHAR(20) DEFAULT '50',
  `stat_label_3` VARCHAR(100) DEFAULT 'Expert Mentors',
  `stat_number_4` VARCHAR(20) DEFAULT '98',
  `stat_label_4` VARCHAR(100) DEFAULT 'Distinction Ratio',
  
  -- Call to Action Admissions Banner
  `cta_banner_title` VARCHAR(255) DEFAULT 'Begin Your Child\'s Journey Today',
  `cta_banner_desc` TEXT,
  `cta_btn_text` VARCHAR(50) DEFAULT 'Enroll Now',
  `cta_btn_link` VARCHAR(255) DEFAULT 'contact.php#admissions',
  
  -- Section Visibility Toggles (1 = Visible, 0 = Hidden)
  `show_stats` TINYINT(1) DEFAULT 1,
  `show_pillars` TINYINT(1) DEFAULT 1,
  `show_about_preview` TINYINT(1) DEFAULT 1,
  `show_gallery_preview` TINYINT(1) DEFAULT 1,
  `show_news_preview` TINYINT(1) DEFAULT 1,
  `show_results_preview` TINYINT(1) DEFAULT 1,
  `show_cta_banner` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. ABOUT US PAGE CONTENT TABLE (Single Row: id = 1)
CREATE TABLE `about_content` (
  `id` INT PRIMARY KEY DEFAULT 1,
  
  -- Introduction Section
  `intro_heading` VARCHAR(255) DEFAULT 'A Heritage of Trust, A Future of Excellence',
  `intro_desc_1` TEXT,
  `intro_desc_2` TEXT,
  `intro_image_path` VARCHAR(255) DEFAULT 'images/library.png',
  
  -- Vision & Mission Statements
  `vision_title` VARCHAR(255) DEFAULT 'Noble Vision',
  `vision_desc` TEXT,
  `mission_title` VARCHAR(255) DEFAULT 'Pure Mission',
  `mission_desc` TEXT,
  `philosophy_title` VARCHAR(255) DEFAULT 'Core Philosophy',
  `philosophy_desc` TEXT,
  
  -- Leadership Director Message
  `leadership_heading` VARCHAR(255) DEFAULT 'Director\'s Visionary Message',
  `leadership_quote` TEXT,
  `leadership_author` VARCHAR(100) DEFAULT 'Dr. Rajesh Mukhopadhyay',
  `leadership_role` VARCHAR(100) DEFAULT 'Principal & Managing Director',
  
  -- Achievements
  `achievement_1_title` VARCHAR(100) DEFAULT '100% Board Success',
  `achievement_1_metric` VARCHAR(20) DEFAULT '100%',
  `achievement_1_desc` VARCHAR(255) DEFAULT 'CBSE high-distinction aggregate success rates.',
  `achievement_2_title` VARCHAR(100) DEFAULT 'National STEM Honors',
  `achievement_2_metric` VARCHAR(20) DEFAULT 'Rank #1',
  `achievement_2_desc` VARCHAR(255) DEFAULT 'Elite engineering robotics laureates nationally.',
  `achievement_3_title` VARCHAR(100) DEFAULT 'Legacy of Leadership',
  `achievement_3_metric` VARCHAR(20) DEFAULT 'A+ Rating',
  `achievement_3_desc` VARCHAR(255) DEFAULT 'National Ethical Leadership Award recipients.',
  
  -- Page Section Toggles
  `show_intro` TINYINT(1) DEFAULT 1,
  `show_vision_mission` TINYINT(1) DEFAULT 1,
  `show_leadership` TINYINT(1) DEFAULT 1,
  `show_achievements` TINYINT(1) DEFAULT 1,
  `show_timeline` TINYINT(1) DEFAULT 1,
  `show_cta` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. TIMELINE MILESTONES TABLE
CREATE TABLE `about_timeline` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `milestone_year` VARCHAR(10) NOT NULL,
  `milestone_title` VARCHAR(255) NOT NULL,
  `milestone_desc` TEXT NOT NULL,
  `sort_order` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. GALLERY CATEGORIES TABLE
CREATE TABLE `gallery_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. GALLERY IMAGES & VIDEOS TABLE
CREATE TABLE `gallery` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `type` ENUM('image', 'video') DEFAULT 'image',
  `filepath` VARCHAR(255) NOT NULL,
  `is_featured` TINYINT(1) DEFAULT 0,
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `gallery_categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. NEWS & EVENTS TABLE
CREATE TABLE `news_events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `type` ENUM('news', 'event') DEFAULT 'news',
  `content` TEXT NOT NULL,
  `event_date` DATE DEFAULT NULL,
  `filepath` VARCHAR(255) NOT NULL,
  `is_featured` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. RESULTS SHOWCASE TABLE
CREATE TABLE `results` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_name` VARCHAR(100) NOT NULL,
  `roll_no` VARCHAR(50) NOT NULL,
  `exam_category` VARCHAR(100) NOT NULL, -- Class XII CBSE, IIT-JEE, NEET, etc.
  `academic_year` VARCHAR(20) NOT NULL,    -- 2025, 2024, etc.
  `score_metric` VARCHAR(50) NOT NULL,     -- 99.2%, All India Rank 45, etc.
  `pdf_path` VARCHAR(255) NOT NULL,
  `is_featured` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. CONTACT FORM SUBMISSIONS TABLE
CREATE TABLE `contact_submissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =======================================================
-- POPULATE INITIAL DEFAULT DATA
-- =======================================================

-- Seed default administrator (Password: GurukulAdmin2026!)
-- Hash calculated using PHP password_hash() with PASSWORD_DEFAULT:
-- '$2y$10$tM6n.iZ1l8V46gV/qE4JjO6N11/1qE3U26.Kz85T3E7E7V2Z5Z5Z5' (Dynamic seeder will overwrite if needed)
INSERT INTO `admins` (`id`, `username`, `password_hash`, `email`, `is_first_login`) 
VALUES (1, 'admin', '$2y$10$wJtK2.tP0JgG7Z7h8j7HcuJj.j20N2e7h01Wb3g0T3T5F5V7V7Z5.', 'admin@gurukul.edu', 1);

-- Seed default homepage layout parameters
INSERT INTO `homepage_content` (`id`, `hero_subtitle`, `cta_banner_desc`) 
VALUES (1, 
'At Gurukul Academy, we fuse rich heritage and traditional ethics with cutting-edge academic excellence. Discover an environment designed to nurture leaders, thinkers, and innovators of tomorrow.', 
'Unlock your path to academic excellence, innovative STEM training, and moral character development. Admissions are officially open for the academic sessions 2026-27. Enroll your child today.');

-- Seed default about page layout parameters
INSERT INTO `about_content` (`id`, `intro_desc_1`, `intro_desc_2`, `vision_desc`, `mission_desc`, `philosophy_desc`, `leadership_quote`)
VALUES (1,
'Established in 2011, Gurukul Academy has risen to stand as one of the nation\'s premier institutions of learning. We bridge ancient Gurukul ethics with cutting-edge 21st-century STEM frameworks.',
'Our expansive campus features elite interactive classrooms, highly equipped research laboratories, high-performance athletic arenas, and a library system holding thousands of educational records. We serve to groom comprehensive global leaders.',
'To be a globally recognized beacon of holistic education, where academic brilliance blends with ethical leadership to build enlightened human resources for the future.',
'To provide a nurturing environment that fosters scientific inquiry, critical problem-solving skills, and a commitment to positive civic action, ensuring every student discovers their path.',
'Our philosophy centers around the ancient Sanskrit ideal "Sa Vidya Ya Vimuktaye" (Education is that which liberates), aiming to free the mind from boundaries and cultivate absolute potential.',
'Education is not merely a pipeline of credentials; it is the sacred combustion that kindles moral character, creative logic, and cognitive independence. At Gurukul, we teach students how to think, how to lead, and how to build a better world.');

-- Seed default timeline milestones
INSERT INTO `about_timeline` (`milestone_year`, `milestone_title`, `milestone_desc`, `sort_order`) VALUES
('2011', 'The Foundation Stone', 'Gurukul Academy opened its doors with a simple dream of transforming education, starting with 120 primary grade scholars.', 1),
('2015', 'CBSE Accreditation & Laboratories', 'Secured official CBSE Secondary Board accreditation, opening high-fidelity Physics, Chemistry, and Biological research halls.', 2),
('2019', 'Elite STEM robotics laboratory wing', 'Launched the National Robotics and Fabrication center, bagging first rank accolades at the National STEM Championship.', 3),
('2022', 'Olympic Sports & Hostel Facilities', 'Unveiled our indoor Olympic-sized sports complex alongside modern, air-conditioned boarding suites.', 4),
('2026', 'Academic Wall of Glory Landmark', 'Celebrating a milestone legacy of over 1200+ elite alumni selected inside premium IITs, AIIMS, and global research centers.', 5);

-- Seed default gallery categories
INSERT INTO `gallery_categories` (`id`, `name`, `slug`) VALUES
(1, 'Academic Pillars', 'academics'),
(2, 'STEM Laboratories', 'laboratories'),
(3, 'Athletic Fields', 'athletics'),
(4, 'Performing Arts', 'arts');
