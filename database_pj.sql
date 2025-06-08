-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025-06-08 09:48:34
-- 伺服器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `database_pj`
--

-- --------------------------------------------------------

--
-- 資料表結構 `admin`
--

CREATE TABLE `admin` (
  `AdminID` int(10) NOT NULL,
  `AdminName` varchar(10) NOT NULL,
  `Phone` int(20) NOT NULL,
  `Email` varchar(20) NOT NULL,
  `Password` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `admin`
--

INSERT INTO `admin` (`AdminID`, `AdminName`, `Phone`, `Email`, `Password`) VALUES
(1, 'Alice', 912345678, 'alice@example.com', 123),
(2, 'Bob', 923456789, 'bob@example.com', 23456),
(3, 'Charlie', 934567890, 'charlie@example.com', 34567),
(4, 'David', 945678901, 'david@example.com', 45678),
(5, 'Eve', 956789012, 'eve@example.com', 56789),
(6, 'Frank', 967890123, 'frank@example.com', 67890),
(7, 'Grace', 978901234, 'grace@example.com', 78901),
(8, 'Hank', 989012345, 'hank@example.com', 89012),
(9, 'Ivy', 990123456, 'ivy@example.com', 90123),
(10, 'Jack', 991234567, 'jack@example.com', 12345);

-- --------------------------------------------------------

--
-- 資料表結構 `announcement`
--

CREATE TABLE `announcement` (
  `AnnouncementID` int(11) NOT NULL,
  `Title` varchar(200) NOT NULL,
  `Content` text NOT NULL,
  `PublishDate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `announcement`
--

INSERT INTO `announcement` (`AnnouncementID`, `Title`, `Content`, `PublishDate`) VALUES
(10, 'test1', 'test123123', '2025-01-06 07:42:21'),
(11, 'testttttttt', '6\r\n', '2025-01-06 08:30:03'),
(13, 'TEST', 'TESTTT', '2025-01-06 13:42:22'),
(14, 'TEAST', '6566', '2025-01-06 16:22:21');

-- --------------------------------------------------------

--
-- 資料表結構 `attachment`
--

CREATE TABLE `attachment` (
  `AttachmentID` int(11) NOT NULL,
  `AnnouncementID` int(11) NOT NULL,
  `AttachmentName` varchar(100) NOT NULL,
  `AttachmentLink` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `attachment`
--

INSERT INTO `attachment` (`AttachmentID`, `AnnouncementID`, `AttachmentName`, `AttachmentLink`) VALUES
(7, 13, 'TEST', 'uploads/attachments/1736142448_11_25作業.pptx'),
(8, 14, 'TEST123', 'uploads/attachments/1736151792_extra_teacher_data.sql');

-- --------------------------------------------------------

--
-- 資料表結構 `carousel`
--

CREATE TABLE `carousel` (
  `CarouselID` int(11) NOT NULL,
  `ImagePath` varchar(255) NOT NULL,
  `Caption` varchar(255) DEFAULT NULL,
  `PublishDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `carousel`
--

INSERT INTO `carousel` (`CarouselID`, `ImagePath`, `Caption`, `PublishDate`) VALUES
(3, 'uploads/carousel/1736123551_photo1.jpg', '行政大樓', '2025-01-06 08:32:31'),
(4, 'uploads/carousel/1736124362_photo5.jpg', '合照', '2025-01-06 08:46:02'),
(5, 'uploads/carousel/1736123622_photo3.jpg', '歐式廣場', '2025-01-06 08:33:42'),
(6, 'uploads/carousel/1736151925_photo2.jpg', '校長', '2025-01-06 16:25:25'),
(7, 'uploads/carousel/1736124788_photo6.jpg', 'nukcsic', '2025-01-06 08:53:08');

-- --------------------------------------------------------

--
-- 資料表結構 `criteria`
--

CREATE TABLE `criteria` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `criteria`
--

INSERT INTO `criteria` (`id`, `name`) VALUES
(1, '創意'),
(2, '技術實現'),
(3, '可行性'),
(4, '簡報表現');

-- --------------------------------------------------------

--
-- 資料表結構 `judge`
--

CREATE TABLE `judge` (
  `JudgeID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Title` varchar(50) NOT NULL,
  `Phone` varchar(15) NOT NULL,
  `Email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `judge`
--

INSERT INTO `judge` (`JudgeID`, `Name`, `Title`, `Phone`, `Email`) VALUES
(1, '廖冠丞丞丞', '總統', '123', '123@gmail.com'),
(3, '張大文', '教授', '0912345678', 'zhang@example.com'),
(4, '李小美', '副教授', '0922345678', 'li@example.com'),
(5, '王中和', '博士', '0933345678', 'wang@example.com'),
(6, '陳麗華', '助理教授', '0944345678', 'chen@example.com'),
(7, '林俊傑', '博士', '0955345678', 'lin@example.com'),
(8, '高志強', '教授', '0966345678', 'gao@example.com'),
(9, '黃怡君', '助理教授', '0977345678', 'huang@example.com'),
(10, '吳天明', '副教授', '0988345678', 'wu@example.com'),
(11, '許志豪', '教授', '0999345678', 'hsu@example.com'),
(12, '鄭佩真', '博士', '0910445678', 'cheng@example.com');

-- --------------------------------------------------------

--
-- 資料表結構 `past_projects`
--

CREATE TABLE `past_projects` (
  `PastTeamID` int(11) NOT NULL,
  `TeamName` varchar(100) NOT NULL,
  `ProjectName` varchar(100) NOT NULL,
  `CompetitionCategory` varchar(50) DEFAULT NULL,
  `PostYear` year(4) NOT NULL,
  `DescriptionLink` varchar(255) DEFAULT NULL,
  `PosterLink` varchar(255) DEFAULT NULL,
  `VideoLink` varchar(255) DEFAULT NULL,
  `CodeLink` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `past_projects`
--

INSERT INTO `past_projects` (`PastTeamID`, `TeamName`, `ProjectName`, `CompetitionCategory`, `PostYear`, `DescriptionLink`, `PosterLink`, `VideoLink`, `CodeLink`) VALUES
(31, 'AlphaTeam', 'AlphaProject', '創意發想組', '2020', 'https://example.com/desc/alpha', 'https://example.com/poster/alpha', 'https://example.com/video/alpha', 'https://example.com/code/alpha'),
(32, 'BetaTeam', 'BetaProject', '創業實作組', '2021', 'https://example.com/desc/beta', 'https://example.com/poster/beta', 'https://example.com/video/beta', 'https://example.com/code/beta'),
(33, 'GammaTeam', 'GammaProject', '創意發想組', '2022', 'https://example.com/desc/gamma', 'https://example.com/poster/gamma', 'https://example.com/video/gamma', 'https://example.com/code/gamma'),
(34, 'DeltaTeam', 'DeltaProject', '創業實作組', '2023', 'https://example.com/desc/delta', 'https://example.com/poster/delta', 'https://example.com/video/delta', 'https://example.com/code/delta'),
(35, 'EpsilonTeam', 'EpsilonProject', '創意發想組', '2024', 'https://example.com/desc/epsilon', 'https://example.com/poster/epsilon', 'https://example.com/video/epsilon', 'https://example.com/code/epsilon'),
(36, 'ZetaTeam', 'ZetaProject', '創業實作組', '2019', 'https://example.com/desc/zeta', 'https://example.com/poster/zeta', 'https://example.com/video/zeta', 'https://example.com/code/zeta'),
(37, 'EtaTeam', 'EtaProject', '創意發想組', '2025', 'https://example.com/desc/eta', 'https://example.com/poster/eta', 'https://example.com/video/eta', 'https://example.com/code/eta'),
(38, 'ThetaTeam', 'ThetaProject', '創業實作組', '2020', 'https://example.com/desc/theta', 'https://example.com/poster/theta', 'https://example.com/video/theta', 'https://example.com/code/theta'),
(39, 'IotaTeam', 'IotaProject', '創意發想組', '2021', 'https://example.com/desc/iota', 'https://example.com/poster/iota', 'https://example.com/video/iota', 'https://example.com/code/iota'),
(40, 'KappaTeam', 'KappaProject', '創業實作組', '2022', 'https://example.com/desc/kappa', 'https://example.com/poster/kappa', 'https://example.com/video/kappa', 'https://example.com/code/kappa');

-- --------------------------------------------------------

--
-- 資料表結構 `score`
--

CREATE TABLE `score` (
  `ScoreID` int(11) NOT NULL,
  `JudgeID` int(11) NOT NULL,
  `TeamID` int(11) NOT NULL,
  `ScoreValue` int(11) NOT NULL,
  `Comment` text NOT NULL,
  `Timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `score`
--

INSERT INTO `score` (`ScoreID`, `JudgeID`, `TeamID`, `ScoreValue`, `Comment`, `Timestamp`) VALUES
(11, 3, 5, 60, '123', '2025-01-06 07:07:45'),
(12, 4, 5, 60, '123', '2025-01-06 07:08:15'),
(13, 3, 6, 26, '6', '2025-01-06 09:40:17'),
(14, 3, 9, 99, '4545', '2025-01-06 12:17:02'),
(15, 3, 10, 77, '7', '2025-01-06 12:17:10'),
(16, 3, 12, 88, '8', '2025-01-06 12:17:16'),
(17, 3, 18, 99, '9', '2025-01-06 12:17:26'),
(18, 3, 21, 66, '6', '2025-01-06 12:17:31'),
(19, 3, 8, 12, '11', '2025-01-06 12:18:10'),
(20, 4, 6, 100, '1000', '2025-01-06 12:19:51'),
(21, 4, 8, 100, '100', '2025-01-06 12:21:04'),
(22, 5, 8, 68, '60', '2025-01-06 12:23:08'),
(23, 1, 5, 90, 'good', '2025-01-06 16:18:57'),
(25, 3, 22, 90, '666', '2025-01-06 16:21:01'),
(28, 1, 22, 21, '21', '2025-06-07 16:59:38');

-- --------------------------------------------------------

--
-- 資料表結構 `submission`
--

CREATE TABLE `submission` (
  `SubmissionID` int(11) NOT NULL,
  `TeamID` int(11) NOT NULL,
  `Description` varchar(500) NOT NULL,
  `PosterLink` varchar(255) NOT NULL,
  `VideoLink` varchar(255) NOT NULL,
  `CodeLink` varchar(255) NOT NULL,
  `UploadDate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `submission`
--

INSERT INTO `submission` (`SubmissionID`, `TeamID`, `Description`, `PosterLink`, `VideoLink`, `CodeLink`, `UploadDate`) VALUES
(3, 22, '我們超棒', 'https://www.nuk.edu.tw/', '', 'https://www.nuk.edu.tw/', '2025-01-06 05:51:38'),
(4, 8, 'test', 'https://www.nuk.edu.tw/', 'https://www.nuk.edu.tw/', 'https://www.nuk.edu.tw/', '2025-01-06 05:59:34');

-- --------------------------------------------------------

--
-- 資料表結構 `teacher`
--

CREATE TABLE `teacher` (
  `TeacherID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Phone` varchar(15) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `teacher`
--

INSERT INTO `teacher` (`TeacherID`, `Name`, `Phone`, `Email`, `Password`) VALUES
(1, '廖冠丞', '0921000000', 'LIAO@gmail.com', '123'),
(3, '張偉', '0912345678', 't1234@university.edu', 'Jo8Ie9Gl'),
(4, '王芳', '0912345679', 't1235@university.edu', 'Ik0Zu0Xj'),
(5, '李娜', '0912345680', 't1236@university.edu', 'Ci3Pc6Ev'),
(6, '陳靜', '0912345681', 't1237@university.edu', 'Qr4Bd4Tm'),
(7, '楊秀英', '0912345682', 't1238@university.edu', 'Ek5Oc8Xb'),
(8, '張敏', '09916694140', 'zhang敏@university.edu', 'On9Gh7By'),
(9, '張秀英', '09924409035', 'zhang秀英@university.edu', 'Pb5Pg4Sc'),
(10, '王麗', '09529282254', 'wang麗@university.edu', 'Kq9Vj2Iw'),
(11, '吳偉', '09985905075', '吳偉@university.edu', 'Jd5Lo5Wt'),
(12, '楊靜', '09522954904', '楊靜@university.edu', 'Cj4Gv3Mh'),
(13, '吳靜', '09108796186', '吳靜@university.edu', 'Za2Aj8Cy'),
(14, '劉洋', '09583071661', '劉洋@university.edu', 'Mo3Aa1Ol'),
(15, '陳娜', '09844486484', '陳娜@university.edu', 'Ni1Mc0Dj'),
(16, '陳靜', '09665480920', '陳靜@university.edu', 'Nk4Df6Pc'),
(17, '吳娜', '09684937218', '吳娜@university.edu', 'Qw4Qw3Ge'),
(18, '陳磊', '09864814088', '陳磊@university.edu', 'Eg7Av0Xj'),
(19, '劉偉', '09111606235', '劉偉@university.edu', 'Ck7Md2Wo'),
(20, '楊磊', '09556864952', '楊磊@university.edu', 'Eh8Jf0Rc'),
(21, '吳洋', '09658886453', '吳洋@university.edu', 'Nh9Ub0Vd'),
(22, '劉磊', '09544218674', '劉磊@university.edu', 'Ce5Fj1Xa'),
(23, '楊秀英', '09473408585', '楊秀英@university.edu', 'Gg5Zf1Yj'),
(24, '周娜', '09931839993', '周娜@university.edu', 'Zx4Se6Uz'),
(25, '周磊', '09141526351', '周磊@university.edu', 'Pa2Jy6Ml'),
(26, '趙娜', '09101520320', '趙娜@university.edu', 'Rc3Si6Bj'),
(27, '李秀英', '09103220222', 'li秀英@university.edu', 'Tr1Ne3Lb'),
(28, '陳敏', '09492671494', '陳敏@university.edu', 'Aw2Mw7Jm'),
(29, '王靜', '09548819403', 'wang靜@university.edu', 'Gx7Cd3Lc'),
(30, '趙芳', '09570027164', '趙芳@university.edu', 'Gy8Pi8Lq'),
(31, '李磊', '09959838714', 'li磊@university.edu', 'Ss3Rg3Wk'),
(32, '周麗', '09309455565', '周麗@university.edu', 'Im3Mh9Za'),
(33, '張洋', '09516659506', 'zhang洋@university.edu', 'Bh2Pb5Qk'),
(34, '王偉', '09100567697', 'wang偉@university.edu', 'Eo3Dq7Zo'),
(35, '周靜', '09317878088', '周靜@university.edu', 'Wp4Fy9Zc'),
(36, '王敏', '09824408178', 'wang敏@university.edu', 'Ng7Xg5Ct'),
(37, '周偉', '09553053541', '周偉@university.edu', 'Lz5Rw2Ua'),
(38, '楊強', '09206788205', '楊強@university.edu', 'Vd1Gy8Mu'),
(39, '趙靜', '09257537839', '趙靜@university.edu', 'Pn7Kq9Xs'),
(40, '陳偉', '09310176018', '陳偉@university.edu', 'Vy3Uy3Uy'),
(41, '劉強', '09223874908', '劉強@university.edu', 'Jy6Qc5Ph'),
(42, '李麗', '09981168098', 'li麗@university.edu', 'Ox8Tb0Wh'),
(43, '黃娜', '09554415675', '黃娜@university.edu', 'Yv2Wp3Bc'),
(44, '陳洋', '09477001795', '陳洋@university.edu', 'Jn4Vr9Vd'),
(45, '吳磊', '09622840633', '吳磊@university.edu', 'Ic6Uc0Wg'),
(46, '張芳', '09453497201', 'zhang芳@university.edu', 'Rr3Nr8Df'),
(47, '陳強', '09598387595', '陳強@university.edu', 'Qm5Hv2Sx'),
(48, '測試老師', '0912345678', 'test_teacher@university.edu', 'Iy7Sn4Nk');

-- --------------------------------------------------------

--
-- 資料表結構 `team`
--

CREATE TABLE `team` (
  `TeamID` int(11) NOT NULL,
  `competition_category` varchar(10) NOT NULL,
  `TeamName` varchar(100) NOT NULL,
  `RegistrationDate` date NOT NULL,
  `Rank` int(11) DEFAULT NULL,
  `TeacherID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `team`
--

INSERT INTO `team` (`TeamID`, `competition_category`, `TeamName`, `RegistrationDate`, `Rank`, `TeacherID`) VALUES
(5, '創意發想組', '好棒棒', '2025-01-06', 5, 17),
(6, '創業實作組', '21332', '2025-01-01', 2, 14),
(8, '創業實作組', 'test1', '2024-12-19', 3, 38),
(9, '創意發想組', '444', '2025-01-02', 1, 10),
(10, '創意發想組', '333', '2025-01-02', 4, 10),
(12, '創意發想組', '565', '2025-01-02', 3, 10),
(18, '創意發想組', '123', '2025-01-02', 2, 10),
(21, '創業實作組', '666', '2025-01-01', 1, 16),
(22, '創業實作組', '廖777', '2025-01-06', 4, 1),
(24, '創意發想組', '測試demo', '2025-01-06', 7, 1),
(25, '創意發想組', '測試123', '2025-06-30', 6, 1);

-- --------------------------------------------------------

--
-- 資料表結構 `teammember`
--

CREATE TABLE `teammember` (
  `MemberID` int(11) NOT NULL,
  `TeamID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `StudentID` varchar(50) NOT NULL,
  `Gender` enum('Male','Female','Other') NOT NULL,
  `Phone` varchar(15) NOT NULL,
  `Email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `teammember`
--

INSERT INTO `teammember` (`MemberID`, `TeamID`, `Name`, `StudentID`, `Gender`, `Phone`, `Email`) VALUES
(7, 6, '232', '45', '', '092134', 'L234243@gmail.com'),
(8, 6, '陳3', '4353222', '', '2131245623', 'dja12s@gmail.com'),
(10, 8, '廖0123', '452', 'Male', '092134', 'L234243@gmail.com'),
(11, 8, '陳3', '213213', 'Female', '123213', '423@gmail.com'),
(12, 9, '廖000', 'a123', 'Male', '092134', 'L234243@gmail.com'),
(28, 21, '廖0123', '666', 'Male', '092134', 'L234243@gmail.com'),
(29, 21, '陳123', '6666', 'Male', '21312456', '423@gmail.com'),
(30, 21, '鄒666', '66666', 'Male', '245346', '757@fhrjuh'),
(32, 22, 'liao777', '1', 'Male', '123', 'liao777@gmail.com'),
(33, 22, 'liao666', 'liao666', 'Male', '123', 'liao666@gmail.com'),
(36, 24, '廖000', '99999', 'Male', '88888', 'L234243@gmail.com'),
(37, 24, '陳123', '000000', 'Male', '123213', 'dja12s@gmail.com'),
(38, 25, '1', '1123', 'Male', '1', '1@gmail'),
(39, 25, '1', '13', 'Male', '1', '1@gmail');

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`AdminID`);

--
-- 資料表索引 `announcement`
--
ALTER TABLE `announcement`
  ADD PRIMARY KEY (`AnnouncementID`);

--
-- 資料表索引 `attachment`
--
ALTER TABLE `attachment`
  ADD PRIMARY KEY (`AttachmentID`),
  ADD KEY `AnnouncementID` (`AnnouncementID`);

--
-- 資料表索引 `carousel`
--
ALTER TABLE `carousel`
  ADD PRIMARY KEY (`CarouselID`);

--
-- 資料表索引 `criteria`
--
ALTER TABLE `criteria`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `judge`
--
ALTER TABLE `judge`
  ADD PRIMARY KEY (`JudgeID`);

--
-- 資料表索引 `past_projects`
--
ALTER TABLE `past_projects`
  ADD PRIMARY KEY (`PastTeamID`);

--
-- 資料表索引 `score`
--
ALTER TABLE `score`
  ADD PRIMARY KEY (`ScoreID`),
  ADD KEY `JudgeID` (`JudgeID`),
  ADD KEY `TeamID` (`TeamID`);

--
-- 資料表索引 `submission`
--
ALTER TABLE `submission`
  ADD PRIMARY KEY (`SubmissionID`),
  ADD UNIQUE KEY `TeamID` (`TeamID`);

--
-- 資料表索引 `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`TeacherID`);

--
-- 資料表索引 `team`
--
ALTER TABLE `team`
  ADD PRIMARY KEY (`TeamID`),
  ADD KEY `TeacherID` (`TeacherID`);

--
-- 資料表索引 `teammember`
--
ALTER TABLE `teammember`
  ADD PRIMARY KEY (`MemberID`),
  ADD UNIQUE KEY `StudentID` (`StudentID`),
  ADD KEY `TeamID` (`TeamID`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `announcement`
--
ALTER TABLE `announcement`
  MODIFY `AnnouncementID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `attachment`
--
ALTER TABLE `attachment`
  MODIFY `AttachmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `carousel`
--
ALTER TABLE `carousel`
  MODIFY `CarouselID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `criteria`
--
ALTER TABLE `criteria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `judge`
--
ALTER TABLE `judge`
  MODIFY `JudgeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `past_projects`
--
ALTER TABLE `past_projects`
  MODIFY `PastTeamID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `score`
--
ALTER TABLE `score`
  MODIFY `ScoreID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `submission`
--
ALTER TABLE `submission`
  MODIFY `SubmissionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `teacher`
--
ALTER TABLE `teacher`
  MODIFY `TeacherID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `team`
--
ALTER TABLE `team`
  MODIFY `TeamID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `teammember`
--
ALTER TABLE `teammember`
  MODIFY `MemberID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `attachment`
--
ALTER TABLE `attachment`
  ADD CONSTRAINT `attachment_ibfk_1` FOREIGN KEY (`AnnouncementID`) REFERENCES `announcement` (`AnnouncementID`) ON DELETE CASCADE;

--
-- 資料表的限制式 `score`
--
ALTER TABLE `score`
  ADD CONSTRAINT `score_ibfk_1` FOREIGN KEY (`JudgeID`) REFERENCES `judge` (`JudgeID`) ON DELETE CASCADE,
  ADD CONSTRAINT `score_ibfk_2` FOREIGN KEY (`TeamID`) REFERENCES `team` (`TeamID`) ON DELETE CASCADE;

--
-- 資料表的限制式 `submission`
--
ALTER TABLE `submission`
  ADD CONSTRAINT `submission_ibfk_1` FOREIGN KEY (`TeamID`) REFERENCES `team` (`TeamID`) ON DELETE CASCADE;

--
-- 資料表的限制式 `team`
--
ALTER TABLE `team`
  ADD CONSTRAINT `team_ibfk_1` FOREIGN KEY (`TeacherID`) REFERENCES `teacher` (`TeacherID`) ON DELETE CASCADE;

--
-- 資料表的限制式 `teammember`
--
ALTER TABLE `teammember`
  ADD CONSTRAINT `teammember_ibfk_1` FOREIGN KEY (`TeamID`) REFERENCES `team` (`TeamID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
