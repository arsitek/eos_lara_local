/*
 Navicat Premium Data Transfer

 Source Server         : SIREKAT
 Source Server Type    : MySQL
 Source Server Version : 80046 (8.0.46-0ubuntu0.24.04.3)
 Source Host           : 127.0.0.1:3306
 Source Schema         : sirekat

 Target Server Type    : MySQL
 Target Server Version : 80046 (8.0.46-0ubuntu0.24.04.3)
 File Encoding         : 65001

 Date: 21/08/2026 12:33:25
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for tb_sasaran
-- ----------------------------
DROP TABLE IF EXISTS `tb_sasaran`;
CREATE TABLE `tb_sasaran`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `kode_ss` varchar(25) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `sasaran_program` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `tahun` int NOT NULL,
  `created_at` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `kode_ss_tahun_unique` (`kode_ss`, `tahun`) USING BTREE,
  INDEX `kode_ss`(`kode_ss` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 29 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tb_sasaran
-- ----------------------------
INSERT INTO `tb_sasaran` VALUES (1, 'SKL', 'Meningkatnya kualitas lulusan pendidikan tinggi', 2024, '2026-01-09 10:38:39', NULL);
INSERT INTO `tb_sasaran` VALUES (2, 'SKD', 'Meningkatnya kualitas dosen pendidikan tinggi', 2024, '2026-01-09 10:38:39', NULL);
INSERT INTO `tb_sasaran` VALUES (3, 'SKK', 'Meningkatnya kualitas kurikulum dan pembelajaran', 2024, '2026-01-09 10:38:39', NULL);
INSERT INTO `tb_sasaran` VALUES (4, 'SKT', 'Meningkatnya tata kelola Perguruan Tinggi Negeri', 2024, '2026-01-09 10:38:39', NULL);
INSERT INTO `tb_sasaran` VALUES (5, 'SKL', 'Meningkatnya kualitas lulusan pendidikan tinggi', 2025, '2026-01-09 10:38:39', NULL);
INSERT INTO `tb_sasaran` VALUES (6, 'SKD', 'Meningkatnya kualitas dosen pendidikan tinggi', 2025, '2026-01-09 10:38:39', NULL);
INSERT INTO `tb_sasaran` VALUES (7, 'SKK', 'Meningkatnya kualitas kurikulum dan pembelajaran', 2025, '2026-01-09 10:38:39', NULL);
INSERT INTO `tb_sasaran` VALUES (8, 'SKT', 'Meningkatnya tata kelola Perguruan Tinggi Negeri', 2025, '2026-01-09 10:38:39', NULL);
INSERT INTO `tb_sasaran` VALUES (21, 'S.01', 'Talenta', 2026, '2026-01-13 02:41:25', '2026-01-13 02:41:25');
INSERT INTO `tb_sasaran` VALUES (22, 'S.02', 'Inovasi', 2026, '2026-01-13 02:51:52', '2026-01-13 02:51:52');
INSERT INTO `tb_sasaran` VALUES (23, 'S.03', 'Kontribusi/dedikasi pada masyarakat', 2026, '2026-01-13 02:54:11', '2026-01-13 02:54:11');
INSERT INTO `tb_sasaran` VALUES (24, 'S.04', 'Tata kelola berintegritas', 2026, '2026-05-07 20:24:27', NULL);
INSERT INTO `tb_sasaran` VALUES (25, 'S.01', 'Talenta', 2027, '2026-07-24 09:35:58', '2026-07-24 09:35:58');
INSERT INTO `tb_sasaran` VALUES (26, 'S.02', 'Inovasi', 2027, '2026-07-24 09:36:44', '2026-07-24 09:36:44');
INSERT INTO `tb_sasaran` VALUES (27, 'S.03', 'Kontribusi/dedikasi pada masyarakat', 2027, '2026-07-24 09:36:52', '2026-07-24 09:36:52');
INSERT INTO `tb_sasaran` VALUES (28, 'S.04', 'Tata kelola berintegritas', 2027, '2026-07-24 09:36:57', '2026-07-24 09:36:57');

SET FOREIGN_KEY_CHECKS = 1;
