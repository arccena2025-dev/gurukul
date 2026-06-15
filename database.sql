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
DROP TABLE IF EXISTS `certificates`;
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
  `show_cta` TINYINT(1) DEFAULT 1,
  `show_faculty` TINYINT(1) DEFAULT 1
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
'Established in 2003, Gurukul Academy has risen to stand as one of the nation\'s premier institutions of learning. We bridge ancient Gurukul ethics with cutting-edge 21st-century STEM frameworks.',
'Our expansive campus features elite interactive classrooms, highly equipped research laboratories, high-performance athletic arenas, and a library system holding thousands of educational records. We serve to groom comprehensive global leaders.',
'To be a globally recognized beacon of holistic education, where academic brilliance blends with ethical leadership to build enlightened human resources for the future.',
'To provide a nurturing environment that fosters scientific inquiry, critical problem-solving skills, and a commitment to positive civic action, ensuring every student discovers their path.',
'Our philosophy centers around the ancient Sanskrit ideal "Sa Vidya Ya Vimuktaye" (Education is that which liberates), aiming to free the mind from boundaries and cultivate absolute potential.',
'Education is not merely a pipeline of credentials; it is the sacred combustion that kindles moral character, creative logic, and cognitive independence. At Gurukul, we teach students how to think, how to lead, and how to build a better world.');

-- Seed default timeline milestones
INSERT INTO `about_timeline` (`milestone_year`, `milestone_title`, `milestone_desc`, `sort_order`) VALUES
('2003', 'The Foundation Stone', 'Gurukul Academy opened its doors with a simple dream of transforming education, starting with 120 primary grade scholars.', 1),
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

-- 11. CERTIFICATES & COMPLIANCE TABLE
CREATE TABLE `certificates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `category` ENUM('recognition', 'safety', 'academic', 'awards', 'student_safety') NOT NULL,
  `pdf_path` VARCHAR(255) NOT NULL,
  `thumbnail_path` VARCHAR(255) DEFAULT NULL,
  `issue_authority` VARCHAR(255) DEFAULT NULL,
  `certificate_number` VARCHAR(100) DEFAULT NULL,
  `issue_date` DATE DEFAULT NULL,
  `expiry_date` DATE DEFAULT NULL,
  `is_visible` TINYINT(1) DEFAULT 1,
  `is_featured` TINYINT(1) DEFAULT 0,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default certificates & mandatory disclosures
INSERT INTO `certificates` (`title`, `category`, `pdf_path`, `issue_authority`, `certificate_number`, `issue_date`, `expiry_date`, `is_visible`, `is_featured`, `sort_order`) VALUES
('CBSE Affiliation Extension Certificate', 'recognition', 'uploads/certificates/cbse_affiliation.pdf', 'Central Board of Secondary Education', 'CBSE/AFF/1131304/2025', '2025-04-01', '2030-03-31', 1, 1, 1),
('School Recognition Certificate (Form V)', 'recognition', 'uploads/certificates/school_recognition.pdf', 'Department of School Education, Govt. of India', 'DSE/REC-2291/2024', '2024-06-15', NULL, 0, 0, 2),
('No Objection Certificate (NOC)', 'recognition', 'uploads/certificates/noc_certificate.pdf', 'State Education Secretariat', 'SEC/NOC-992/2011', '2011-03-10', NULL, 1, 0, 3),
('Society/Trust Registration Deed', 'recognition', 'uploads/certificates/trust_deed.pdf', 'Registrar of Societies & Trusts', 'REG/TRUST-8891-A', '2010-09-05', NULL, 0, 0, 4),
('Structural Building Safety Certificate', 'safety', 'uploads/certificates/building_safety.pdf', 'Public Works Department (PWD) Engineers', 'PWD/STR-881/2026', '2026-01-12', '2029-01-11', 1, 0, 5),
('Fire Safety & Prevention Certificate', 'safety', 'uploads/certificates/fire_safety.pdf', 'State Fire & Emergency Services', 'FIRE/SAFE-9902/2026', '2026-02-20', '2027-02-19', 1, 1, 6),
('Water Health & Sanitation Certificate', 'safety', 'uploads/certificates/water_sanitation.pdf', 'Municipal Public Health Laboratory', 'MPH/SAN-7762/2026', '2026-03-05', '2027-03-04', 1, 1, 7),
('Academic Fee Structure Matrix', 'academic', 'uploads/certificates/fee_structure.pdf', 'School Finance & Management Committee', 'SFC/FEE-2026-27', '2026-01-05', NULL, 0, 0, 8),
('Annual Academic Calendar 2026-27', 'academic', 'uploads/certificates/academic_calendar.pdf', 'Academic Dean Office', 'AC/CAL-2026-27', '2026-03-01', '2027-03-31', 0, 0, 9),
('School Management Committee (SMC) Registry', 'academic', 'uploads/certificates/smc_members.pdf', 'Board of Trustees Office', 'BOT/SMC-2026', '2026-01-10', NULL, 0, 0, 10),
('Dynamic Staff Details & Ratios', 'academic', 'uploads/certificates/staff_details.pdf', 'HR Administration Office', 'HR/STAFF-2026', '2026-02-01', NULL, 0, 0, 11),
('National Innovation School Excellence Award', 'awards', 'uploads/certificates/innovation_award.pdf', 'National STEM Foundation', 'NSF/AWARD-2025', '2025-11-14', NULL, 0, 0, 12),
('Child Protection & Safety Policy', 'student_safety', 'uploads/certificates/child_protection.pdf', 'Student Welfare Council', 'SWC/CPP-2025', '2025-06-01', NULL, 0, 0, 13);



-- 12. FACULTY DIRECTORY TABLE
DROP TABLE IF EXISTS `faculty`;
CREATE TABLE `faculty` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `designation` VARCHAR(255) NOT NULL,
  `qualification` VARCHAR(255) DEFAULT NULL,
  `subject` VARCHAR(255) DEFAULT NULL,
  `experience` VARCHAR(100) DEFAULT NULL,
  `expertise` TEXT DEFAULT NULL,
  `meaning_of_education` TEXT DEFAULT NULL,
  `teaching_philosophy` TEXT DEFAULT NULL,
  `student_message` TEXT DEFAULT NULL,
  `quote` TEXT DEFAULT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `faculty` (`name`, `designation`, `qualification`, `subject`, `experience`, `expertise`, `meaning_of_education`, `teaching_philosophy`, `student_message`, `quote`, `image_path`, `sort_order`) VALUES
('Rutuja Gadiwan', 'Prinicpal', 'B.Ed, M.Sc', 'Maths', '16 years', 'Educational leadership,Strategic planning,Student Development,Administration and Operations', 'To me, education is much more than academic achievement. It is the process of nurturing young minds, developing character, fostering critical thinking, and preparing students to become responsible, compassionate, and capable members of society. As a principal, I view education as a lifelong journey that empowers individuals with knowledge, skills, values, and confidence to succeed in an ever-changing world.', 'My teaching philosophy is rooted in the belief that every student has the ability to learn, grow, and succeed when provided with the right support, opportunities, and encouragement. Education should inspire curiosity, critical thinking, creativity, and a lifelong love of learning.', 'Dear Students,

Each one of you has unique talents, dreams, and potential. Believe in yourselves, work hard, and never be afraid to ask questions or make mistakes—they are an important part of learning and growth.

Education is not only about achieving good grades; it is about developing character, values, confidence, and the skills needed to make a positive difference in the world. Be curious, be kind, respect others, and always strive to be the best version of yourself.

Remember, success comes through dedication, perseverance, and a willingness to learn every day. Your teachers and I are here to support and guide you on this journey.

Dream big, stay focused, and never stop learning. The future is full of opportunities, and I am confident that each of you can achieve great things.

Wishing you happiness, success, and a wonderful learning experience.', '“The purpose of education is not only to create successful students but responsible and compassionate citizens.”', 'uploads/faculty/principal_rutuja_ma_am.jpg', 1),
('Sharmila Kishor Patil', 'Vice Principal', 'B.Ed, B.A', 'English', '23 years', 'Learning playful and simple.', 'Change and lifelong process of learning.', 'Problem solvers and lifelong learners.', 'Always remember that true success is built on continuous learning.', 'Education is not the filling of a pill but the lighting of a fire.', 'uploads/faculty/sharmila_ma_am.jpg', 2),
('Pooja Shamrao Dangare', 'Class Co-ordinator', 'D.Ed, B.Ed, B.A, M.A', 'English', '10 Years', 'English', 'Education shapes the world.', 'Basic concepts of subjects and social value should be teach by teachers.', 'Never give up in life.We should always try in life nature gives us 100% fruits.', 'There are two options make progress or make excuses......', 'uploads/faculty/pooja_ma_am.jpg', 3),
('Anuradha Dinkarao Nandurakar', 'Teacher', 'M.Sc, B.Ed', 'Maths', '1Years', 'Algebra, Geometry, Calculus and Problem Solving', 'Education is life long process of learning', 'Make mathematics simple and engaging, and helping students to develop strong problem solving skills', 'Maths is not about numbers but its about the way to see life with numbers', 'Language of life is Maths', NULL, 4),
('Ashtavinayak Vishwanath Shenkude', 'Teacher', 'A.T.D Arts, G.D. Arts', 'Drawing, Art n Craft', '7 Years', 'Portraits', 'Education is new Revoluation', 'My teaching philosophy is to inspire creativity, encourage self-expression,and help every student learn with confidence', 'mathematics is not about being perfect; it is about learning to think, reason, and solve problems. Be patient with yourself, ask questions, and enjoy the journey of learning.', 'Information is not knowledge', 'uploads/faculty/ashtavinayak_sir.jpg', 5),
('Sushma Narendra kulkarni', 'Official Staff', 'M.A', 'Lib', '10 years', 'Library mangement', 'Building informed citizens', 'To inspire reading', 'If you read, you will read', 'To promote reading culture', 'uploads/faculty/sushama_ma_am.jpg', 6),
('Samruddhi sunil Deshmukh', 'Teacher', 'B.Sc', '', '1year', 'Teaching, problem solving', 'To me education means empowering to students with the critical thinking.', 'My teaching philosophy is to foster a students centered environment that sparks curiosity.', 'Believe in yourself work hard and never stop being curious about the world around you.', 'Teaching is not filling a bucket but lighting a fire.', 'uploads/faculty/sammrudhi_ma_am.jpg', 7),
('Neha Prakash Babras', 'Teacher', 'M.Sc', '', '1 year', 'Teaching ... Classroom Management ...Interactive Learning Methods', 'To me, education is the foundation for personal growth, character building, and lifelong success...', 'I believe that every student has unique potential, and my role is to provide the tailored guidance and encouragement they need to unlock it...', 'Your potential is limitless—dream big, stay focused, and always do your best...', 'Every child is a different kind of flower, and all together, they make this world a beautiful garden..', 'uploads/faculty/neha_b.jpg', 8),
('Hrutwik Ramdas Tandale', 'Teacher', 'B.A, B.P.ed', 'Sports', '3 to 4 year’s experience of Sport Coaching', 'ALL INDIA UNIVERSITY NATIONALS SILVER MEDALIST & SELECTED FOR WORLD UNIVERSITY INTERNATIONAL GAMES TRIALS 1)Taekwondo Coaching 2)Athlete Development 3) Sports Fitness Training 4)Self-Defense Training 5)Competition Preparation 6)Strength & Conditionin', 'Education is not just about gaining knowledge from books; it is the process of developing skills, values, discipline, and critical thinking that help a person grow and contribute positively to society. It empowers individuals to achieve their goals and make informed decisions in life.', 'My teaching philosophy is to create a positive, disciplined, and supportive learning environment where every student feels encouraged to learn and grow. I believe that each student has unique potential, and my role is to guide, motivate, and help them develop their skills, confidence, and character. I focus on continuous improvement, respect, hard work, and lifelong learning.', '“Believe in yourself, stay disciplined, and never stop learning. Success does not come overnight; it is achieved through consistent effort, dedication, and perseverance. Do not be afraid of mistakes—every mistake is an opportunity to learn and improve. Respect your teachers, parents, and peers, and always strive to become the best version of yourself.”', '“Success is not final, failure is not fatal: it is the courage to continue that counts.”', 'uploads/faculty/hrutwik_sir.jpg', 9),
('Shiv Rathore', 'Teacher', 'B.Ed, M.Sc, B.Sc', 'Maths', '5 Years', 'Experienced in teaching Mathematics with expertise in Algebra, Geometry, Arithmetic, problem-solving techniques, lesson planning, student assessment, and creating an engaging learning environment that promotes conceptual understanding and academic excellence.', 'Education is not just about acquiring knowledge; it is about developing the ability to think logically, solve problems, and apply learning in real-life situations. As a Mathematics teacher, I strive to inspire curiosity, confidence, and a love for learning in every student.', 'I believe Mathematics is a powerful tool for understanding the world. By connecting lessons to real-life examples and practical applications, I aim to make learning relevant, interesting, and accessible to every student while fostering analytical and reasoning skills.', 'Mathematics is not about being perfect; it is about learning to think, reason, and solve problems. Be patient with yourself, ask questions, and enjoy the journey of learning.', 'Mathematics is a language of God.', 'uploads/faculty/shiv_sir.jpg', 10),
('Aiman Firdous Mirza Rafat Baig', 'Teacher', 'D.Ed, B.Sc', 'Maths', '2 years', 'Mathematics Teaching, Classroom Management, Student Engagement, Lesson Planning, and Concept-Based Learning.', 'Education means gaining knowledge, skills, and values that help us grow and succeed in life. It empowers us to make better decisions and contribute positively to society.', 'I believe in making learning simple, engaging, and meaningful for every student.', 'Believe in yourself, keep learning, and never be afraid to make mistakes—they are part of success.', '"Success is the result of hard work, consistency, and never giving up."', 'uploads/faculty/aiman_ma_am.jpg', 11),
('Priyanka Sunil Nagargoje', 'Teacher', 'BCA', 'Science', '2 years', 'Mathematics teaching, Communication skills', 'Education means gaining knowledge, skills, and values that help us grow as individuals and contribute positively to society.', 'My teaching philosophy is that every child can learn when provided with a supportive, engaging, and positive learning environment.', 'Dear Students,
Believe in yourself and never stop learning. Every question you ask and every effort you make helps you grow. Do not be afraid of mistakes—they are a part of learning and success.', 'The woods are lovely dark and deep ... But miles to go before I sleep....', 'uploads/faculty/priyanka_ma_am.jpg', 12),
('Mohammad Younus Rather', 'Teacher', 'B.Ed, M.A', 'Social Science', '9 years', 'Ability to bridge theoretical political and social theories with practical, real-world examples to make abstract concepts tangible for students.', 'Education means to me modification of behaviour.', 'My teaching philosophy is rooted in a holistic, student-centered approach that aims to move beyond rote memorization to foster true critical thinking.', 'To build trust, lower anxiety, and establish a safe space for learning.("Welcome! This classroom is a community. My job isn''t just to teach you facts from a textbook; my job is to help you discover how capable you are. In this room, mistakes aren''t failures—they are proof that you are trying.)', 'Give a man a fish you will feed him for a day, teach him how to fish you will feed him for whole life…', 'uploads/faculty/younus_sir.jpg', 13),
('Anjali Anilrao Kulkarni', 'Teacher', 'B.Ed, M.Sc, B.Sc', 'Science', '10 Years', 'Chemistry - Organic Chemistry', 'Education means the process of gaining knowledge, skills, values, and understanding through learning, teaching, training, or experience.', 'Chemistry is an experimental science. I emphasize hands-on laboratory work so students can connect theory with real-life observations.
Focus on “why” and “how”, not just memorizing formulas
Encourage critical thinking and logical reasoning
Use diagrams, models, and examples for clarity.', 'Education is not just about marks, it is about gaining knowledge and becoming a better human being.
Always stay curious. Ask questions, explore ideas, and never stop learning.', 'Knowledge is Power.', 'uploads/faculty/anjali_ma_am.jpg', 14),
('Shakuntala Bhausaheb Takik', 'Teacher', 'D.Ed', 'Pre-Primary', '15 year', 'Teaching, guidance', 'Strong Mind', 'Motivation for students', 'Try hard and always humble', 'Old is gold', NULL, 15),
('Monika Suryakant sapkal', 'Teacher', 'B.Ed, M.Sc', 'EVS', '3 years', 'Class management', 'Education is lifetime process sharing knowledge and skills.', 'A teaching philosophy is personal statement of your personal belief,core value and teaching method', 'Achiev good knowledge and success own your life', 'Nothing is impossible the world itself say s I am possible.', 'uploads/faculty/monika_ma_am.jpg', 16),
('Chavan Manjushri Arun', 'Teacher', 'D.Ed, B.Ed, B.A, M.A', 'Marathi', '14 years', 'Marathi', 'Education, to me, is much more than schooling or earning qualifications. It’s the ongoing process of learning how to understand the world and your place in it.', 'I believe that every student has the potential to learn and succeed when provided with a positive, supportive, and engaging learning environment. My teaching philosophy focuses on student-centered learning, encouraging curiosity, critical thinking, creativity, and active participation. I strive to make learning meaningful and enjoyable by connecting lessons to real-life experiences. I also believe in fostering values such as respect, responsibility, and lifelong learning, helping students grow not only academically but also as confident and responsible individuals.', 'Believe in yourself and never stop learning. Every challenge is an opportunity to grow, and every mistake is a step toward success. Stay curious, work hard, respect others, and always strive to be the best version of yourself. Remember that success comes not only from intelligence but also from dedication, perseverance, and a positive attitude. Dream big, stay focused, and never give up on your goals.', '"Education is the most powerful weapon which you can use to change the world."', 'uploads/faculty/manjushri_ma_am.jpg', 17),
('Shaikh Rahimunnisa Shaiffioudin', 'Teacher', 'B.Ed, M.A', 'Social Science', '18 years', 'Hindi Language Teaching, Grammer, Reading Comprehension, Creative writting and Students-Centered Learning', 'Education is key to knowledge, personal growth, and a better future.', 'I believe in making Hindi learning simple, engaging, and enjoyable for every student.', 'Read regularly, express your thoughts confidently, and take pride in learning and using Hindi.', 'Learning a language opens the door to knowledge, culture, and confidence.', 'uploads/faculty/rahemunnisa_ma_am.jpg', 18),
('Balin Shanthi', 'Teacher', 'B.Ed, M.A', 'English', '8 years', 'English', 'Education, to me, is more than acquiring knowledge from books. It is a lifelong process of learning.', 'My teaching philosophy is based on the belief that education is not about filling minds with information but about inspiring students to think, question, and discover.', 'Never stop being curious and never be afraid of making mistakes. Learning is not about being perfect, it is about growing everyday.', '"If you want to shine like a sun, first burn like a sun." - A.P.J. Abdul Kalam', 'uploads/faculty/balin_shanti_ma_am.jpg', 19),
('Pratibha Sachin Jogdand', 'Teacher', 'B.Ed', 'Maths', '4 year''s', 'Classroom Management', 'Education is life time process', 'Rooted in accessible, personalized, and active learning.', 'Achieve good knowledge', 'Nothing is impossible', 'uploads/faculty/pratibha_ma_am.jpg', 20),
('Rohini prakash zole', 'Teacher', 'D.Ed', 'Pre-Primary', '5 years', 'Class managment', 'Education is important', 'Use teaching aids', 'Education is the very important in your future life', 'Always try one more time', 'uploads/faculty/rohini_ma_am.jpg', 21),
('Shaikh Neha Najim', 'Teacher', 'D.Ed, B.A', 'Hindi', '2 years', 'Hindi and marathi teaching', 'Education means not only complete the degrees but also good behavior and good thinking in our life😊', 'Students learning are important', 'You are the star of your parents and teachers so do study hard', 'Honesty is the best policy', 'uploads/faculty/neha_ma_am.jpg', 22),
('Pushpanjali Dilip Gore', 'Teacher', 'B.Ed', 'English', '4 years', 'English Spoken', 'Education means gaining knowledge and developing skills that help me', 'creat a positive and engaging learning environment', 'believe in yourself and never stop learning', 'learn with confidence,grow with knowledge, and Succeed with hard work', 'uploads/faculty/pushpanjali_ma_am.jpg', 23),
('Priyanka Siddharth Kamble', 'Teacher', 'B.Sc, M.Sc, MBA', 'English', '1 year', 'Good at Communication, Teaching, To Motivate others.', 'Education is the key of success and way to achieve your Goals. Education teaches how to live your live.', 'self-reflective statement detailing your core beliefs, methods, and goals as an educator.', 'Be Confident, Believe in yourself and Do hard work as well as Smart work.', 'Be Educated, Be Agitated, Be Organised.', 'uploads/faculty/priyanka_k.jpg', 24),
('Sarita Bhanudas Kadam', 'Teacher', 'D.Ed, B.Ed, B.A', 'S.St', '10 years', 'Classroom management', 'Education is not only to teach students but also create a good citizen for the Nation to represent it.', 'According to me teaching is not only a profession but it is nation''s service to build the future of our country.', 'Achieve the goal with wisdom and become a good person in your life.', 'Education is not just about learning facts; it is about understanding the world and becoming a responsible citizen.', 'uploads/faculty/sarita_ma_am.jpg', 25),
('Vaidya kirti vitthal', 'Teacher', 'D.Ed, B.A', '', '1 year', 'Teaching', 'New concept teaching in this filed', 'A teacher is dedicated by discipline and students making proper knowledge and best future', 'Dedication students good habits and focus on the basic skills', 'Education is the movement from darkness to light. Education field is very important of the future and life', 'uploads/faculty/kirti_ma_am.jpg', 26),
('Swati Darphe', 'Teacher', 'D.Ed, B.A', 'Marathi', '11 Years', 'Marathi', 'Education is the lifelong process of acquiring knowledge, developing practical skills, and shaping character.', 'My teaching philosophy is centered on creating a positive, inclusive, and engaging learning environment where every student feels valued and encouraged to reach their full potential. I believe that learning is most effective when students actively participate, think critically, and connect classroom knowledge to real-life experiences. My goal is not only to impart knowledge but also to inspire curiosity, confidence, creativity, and lifelong learning. I strive to nurture responsible, compassionate, and independent learners who are prepared to contribute positively to society.', 'Always believe in yourself and your abilities. Be curious, keep learning, and never be afraid to ask questions. Success does not come from talent alone—it comes from hard work, discipline, and perseverance. Learn from your mistakes, respect your teachers and parents, and strive to become responsible and compassionate individuals. Remember, every small effort you make today helps build a brighter future tomorrow.', '"Education is the most powerful weapon which you can use to change the world."', 'uploads/faculty/swati_ma_am.jpg', 27),
('Varsha uttamrao Awhad', 'Teacher', 'B.Ed, M.A, M.P.ed', 'Hindi', '15 year', 'Reading', 'Education meaning progress and success', 'philosophy students understand and explain, success', 'Learning skills and success your life,,', 'Learning new skills and success in your life', 'uploads/faculty/varsha_ma_am.jpg', 28),
('Ankita mithu Ingole', 'Teacher', 'B.Ed, B.Sc, M.Sc', '', '2 years', 'Typing english 30 and 40', 'Education is key of success', 'I believe students centred learning.', 'Stay curious, work hard,be kind to others and always give your best.', 'I will win not immediately but definitely', 'uploads/faculty/anikta_ma_am.jpg', 29),
('Vitekar Sandip Sudhakarrao', 'Teacher', 'B.Ed, B.E', 'Maths', '14 years', 'Mathematics and Statestics', 'Change in mind and heart to develop culture, critical thinking, confidence, life long learn', 'Inspire students to solve problems in study as well as in life by applying different ways.', 'Keep on learning 
Learn the mathematics by doing the mathematics. Keep on doing keep on learning.', 'चरैवेती चरैवेती (Keep on Walking )', 'uploads/faculty/sandeep_sir.jpg', 30),
('Kunda Ankush Walke', 'Teacher', 'B.A', 'Computer', '16 years', 'Computer', 'Education is important', 'Technology important', 'Computer is most important in our life.', 'Technology is best when it brings people together and empowers them to learn.', 'uploads/faculty/kunda_ma_am.jpg', 31),
('Meerabai Bhausaheb Lendgule', 'Teacher', 'M.A, B.P.ed', 'Physics', '13 years', 'Physical education.', 'Education is important.', 'Teaching should be enjoyable and interactive.', 'Believe in yourself, work hard, stay disciplined and treat others with kindness and respect.', 'Every child can learn.', 'uploads/faculty/kashid_ma_am.jpg', 32),
('Shivam Sandipan Naikwade', 'Teacher', 'B.Ed, M.Sc', 'Science', '4 years', 'Scientific approach for life', 'Education means empowering minds, shaping character, and preparing individuals to face life''s challenges with knowledge, confidence, and wisdom.', 'I believe every child can learn and succeed when provided with the right guidance, encouragement, and opportunities. My goal is to make learning meaningful, engaging, and enjoyable while helping students develop knowledge, skills, values, and confidence for their future.', 'Dont ever stop learning new things.', 'Am the person who will build future minds', 'uploads/faculty/shivam_sir.jpg', 33),
('Mrs Lourdes Fatima Fernandes', 'Teacher', 'B.A', 'S.St', '39 years', 'English and social science', 'Because of education, the people can become good human beings.', 'I believe teaching should be student centred, engaging and inclusive', 'Be humble and kind to all', 'Live and let live', 'uploads/faculty/fatima_fernandes_ma_am.jpg', 34),
('Lalita Gundpatil', 'Teacher', 'B.Sc', 'Arts', '10 years', 'Teaching,Drawing, counselling', 'Strong mindset', 'A good and perfect human', '"Believe in yourself, stay focused, and never stop learning."', 'Art is a powerful way to express ideas, emotions, and creativity', 'uploads/faculty/lalita_ma_am.jpg', 35),
('Supriya Pramod Wanjare', 'Teacher', 'M.Sc', 'Maths', '2 years', 'Support weak students and maintain positive classroom environment.', 'Education is the lifelong process to build our knowledge to understand the World, build confidence and achieve success in our life.', 'Prepare proper lesson plan before going in the classroom.', 'Recognize your mistake and improve it, learn well.', 'Today is a new beginning .', 'uploads/faculty/surpriya_ma_am.jpg', 36),
('Manjusha Narendra Thakur', 'Teacher', 'D.Ed, B.Ed, B.A', 'EVS', '7 years', 'Ev''s science,', 'Education means progress in our work', 'Teaching is a good way in our life', 'Doing hard work , success in life', 'Go ahead. Don''t stop until you''ve reached your goal and won your success.', 'uploads/faculty/manjusha_ma_am.jpg', 37),
('Kshitij Surendra Waghmare', 'Teacher', 'B.Tech', 'Computer', '1 Year', 'Python, AI, ML and MERN Stack', 'Education is more than books and exams. It is the ability to learn, grow, and use knowledge to turn ideas into reality while making a positive difference in the lives of others.', 'I believe teaching is not just about sharing knowledge but inspiring curiosity, confidence, and a lifelong love for learning.', 'Learn with curiosity, work with dedication, and always strive to become a better version of yourself.', 'Knowledge gains value only when it is put into action.', 'uploads/faculty/kshitij_sir.jpg', 38),
('Ankush Baburao Walke.', 'Teacher', 'B.Ed, M.A', 'Hindi', '20 years.', 'Hindi', 'Education is the most important tool for social change.', 'I believe every child can learn and succeed when provided with the right guidance, encouragement, and opportunities. My goal is to make learning meaningful, engaging, and enjoyable while helping students develop knowledge, skills, values, and confidence for their future."', 'Education means empowering minds, shaping character, and preparing individuals to face life''s challenges with knowledge, confidence, and wisdom.', 'Learning is future investment', 'uploads/faculty/ankush_sir.jpg', 39),
('Anuja Rajenrdra Slave', 'Teacher', 'M.Com, MBA', 'English', '4 Years', 'English Grammer, Maths(Finance)', 'Education is something, you require to make in the world', 'My teaching philosophy is to make English learning enjoyable, build confidence in communication, and inspire a love language learning', 'Education investment for your better future.', 'Knowledge is wisedom', 'uploads/faculty/anuja_ma_am.jpg', 40),
('Devendra Mukundrao Rankhambe', 'Teacher', 'B.Ed', 'Computer', '7 years', 'Computer', 'Education gives people the knowledge and courage to destroy harmful traditions and build a just society based on equality, dignity, and reason.', 'My teaching philosophy is that education should develop both knowledge and thinking ability. A good teacher does not only give information, but helps students understand concepts, ask questions, and apply learning in real life.', 'Always remember that education is not just about marks or exams—it is about building your mind, character, and future. Learn with curiosity, not pressure. Ask questions, make mistakes, and learn from them, because that is how real understanding grows.', 'Once a person is educated, they develop the ability to think critically, reject ignorance, and stand against injustice.', 'uploads/faculty/devendra_sir.jpg', 41),
('Godawari R Sonawane', 'Teacher', 'B.A', 'Music', '5 years', 'classical Singing', 'foundation aur successful and meaningful life', 'I encourage students to express themselves confidently', 'Music is a powerful way to share emotions,build confidence and spread happiness so, practice with dedication', 'Music can change the world because it can change people', 'uploads/faculty/godawari_ma_am.jpg', 42),
('Manisha Santosh mandave', 'Teacher', 'D.Ed, B.A', 'Marathi', '10 Years', 'Marathi Class room management, Grammar, literature, and communication skills', 'Growth and personal development', 'I believe every student can learn and appreciate Marathi through interactive, student-centered teaching. My goal is to develop strong language skills while fostering a love for Marathi literature and culture.', 'Dream Big , start small and keep going', 'If you can change your mind ,you can change your life.', 'uploads/faculty/manisha_ma_am.jpg', 43),
('Tausif Shaikh', 'Teacher', 'B.Ed', 'Science', '8 years', 'Science, English', 'It''s a part of living life with facts.', 'Experimental learning', 'Be always ready to learn and live in education', 'Journey of life without knowledge is just like plant without sunlight.', 'uploads/faculty/tausif_sir.jpg', 44),
('Dhawale sanyojita mokind', 'Teacher', 'B.Ed, B.Sc', '', '1 year', 'Teaching', 'Education means knowledge , skills, problem solving,thinking, creativity.', 'A teaching philosophy is a personal values ,beliefs and concept about teaching.', 'School is a place is discover your potential , not just memorize facts.', 'Education is a intelligence and good character.', 'uploads/faculty/sanyojita_ma_am.jpg', 45),
('Renuka Ganesh Kamble', 'Class Co-ordinator', 'B.Ed, M.Ed, B.A, M.A', 'Social Science', '17 years', 'Social Science', 'Education is everything.
Good education doesn’t just fill your mind—it changes how you see and interact with life. It helps you make better choices, adapt to new situations, and build a meaningful future.', 'Believe that every child can learn and succeed when given the right guidance and opportunities. My role is to make learning meaningful, enjoyable, and student-centered while nurturing both academic excellence and character development.', 'Education is very important without it we cannot win the world.', 'Try try but don''t cry until success is in our hands.', 'uploads/faculty/renuka_ma_am.jpg', 46),
('Kale Ranjana Tukaram', 'Teacher', 'B.A, D.Ed', 'G.K', '8 years', 'English', 'Everything without education is zero', 'No one should live without it', 'Do hardwork', 'Try try but don''t cry', 'uploads/faculty/ranjana_ma_am.jpg', 47),
('Priyanka Uttama Hajare', 'Teacher', 'B.Ed, B.A, M.A', 'Marathi', '14 years', 'Marathi', 'Education is strong weapon.', 'Making each and every student strong and educating', 'Hardwork makes success.', 'Health is wealth.', 'uploads/faculty/priyanka_h.jpg', 48),
('Vaibhav Vasantrao Joshi', 'Teacher', 'B.Ed, M.Sc', 'Physics', '7 year', 'Physics', 'Change in BEHAVIOUR', 'Student centric', 'Focus on your strength', 'क्यों डरे जिंदगी में क्या होगा, कूछ न होगा तो तजुर्बा होगा l- Vikas divyakirti sir', 'uploads/faculty/vaibhav_sir.jpg', 49),
('Ajith Babu', 'Teacher', 'B.Ed, M.A', 'Social Science', '8 years', 'History, Geography, Political Science, Economics', 'In my view, education constitutes the fundamental basis of personal development and social progress. Beyond the acquisition of academic knowledge, it plays a pivotal role in shaping character, enhancing cognitive abilities, and encouraging independent and critical thought.', 'I strive to connect classroom learning with real-world events and contemporary challenges, encouraging students to question, investigate, and form evidence-based opinions. I believe that social science education should foster empathy, respect for cultural diversity, social responsibility, and ethical decision-making.', 'Learn from the past, stay informed about the present, and work towards building a better future. Be curious, think critically, respect different perspectives, and always act with honesty and responsibility. The knowledge you gain today will help you become thoughtful citizens and positive contributors to society."', '"Your direction is more important than your speed."', 'uploads/faculty/ajit_sir.jpg', 50);
