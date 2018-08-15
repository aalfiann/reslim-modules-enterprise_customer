SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for customer_company
-- ----------------------------
DROP TABLE IF EXISTS `customer_company`;
CREATE TABLE `customer_company` (
  `BranchID` varchar(10) NOT NULL,
  `CompanyID` varchar(20) NOT NULL,
  `Company_name` varchar(50) NOT NULL,
  `Company_name_alias` varchar(50) DEFAULT NULL,
  `Address` varchar(255) NOT NULL,
  `Phone` varchar(15) NOT NULL,
  `Fax` varchar(15) DEFAULT NULL,
  `Email` varchar(50) DEFAULT NULL,
  `PIC` varchar(50) NOT NULL,
  `TIN` varchar(50) DEFAULT NULL,
  `Discount` decimal(7,2) NOT NULL,
  `Tax` decimal(7,2) NOT NULL,
  `Admin_cost` decimal(10,2) NOT NULL,
  `IndustryID` int(11) NOT NULL,
  `SalesID` varchar(20) DEFAULT NULL,
  `StatusID` int(11) NOT NULL,
  `Created_at` datetime NOT NULL,
  `Created_by` varchar(50) NOT NULL,
  `Updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `Updated_by` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`CompanyID`),
  KEY `BranchID` (`BranchID`),
  KEY `IndustryID` (`IndustryID`),
  KEY `StatusID` (`StatusID`),
  KEY `SalesID` (`SalesID`),
  KEY `Name` (`CompanyID`,`Company_name`,`Company_name_alias`) USING BTREE,
  KEY `Created_by` (`Created_by`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for customer_mas_industry
-- ----------------------------
DROP TABLE IF EXISTS `customer_mas_industry`;
CREATE TABLE `customer_mas_industry` (
  `IndustryID` int(11) NOT NULL AUTO_INCREMENT,
  `Industry` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`IndustryID`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of customer_mas_industry
-- ----------------------------
INSERT INTO `customer_mas_industry` VALUES ('1', 'Others');
INSERT INTO `customer_mas_industry` VALUES ('2', 'Banking');
INSERT INTO `customer_mas_industry` VALUES ('3', 'Insurance');
INSERT INTO `customer_mas_industry` VALUES ('4', 'Logistics');
INSERT INTO `customer_mas_industry` VALUES ('5', 'Entertainment');
INSERT INTO `customer_mas_industry` VALUES ('6', 'Hospital / Health Care');
INSERT INTO `customer_mas_industry` VALUES ('7', 'Advertising');
INSERT INTO `customer_mas_industry` VALUES ('8', 'Automotive');
INSERT INTO `customer_mas_industry` VALUES ('9', 'Publishing');
INSERT INTO `customer_mas_industry` VALUES ('10', 'Outsourcing');
INSERT INTO `customer_mas_industry` VALUES ('11', 'Transportation');
INSERT INTO `customer_mas_industry` VALUES ('12', 'Retail');
INSERT INTO `customer_mas_industry` VALUES ('13', 'Goverment');
INSERT INTO `customer_mas_industry` VALUES ('14', 'Manufacturing');
INSERT INTO `customer_mas_industry` VALUES ('15', 'Chemical / Pharmacy');
INSERT INTO `customer_mas_industry` VALUES ('16', 'Food and Beverages');
INSERT INTO `customer_mas_industry` VALUES ('17', 'Financial Institutions');
INSERT INTO `customer_mas_industry` VALUES ('18', 'General Trading');
INSERT INTO `customer_mas_industry` VALUES ('19', 'Consultant');
INSERT INTO `customer_mas_industry` VALUES ('20', 'Electronic');
INSERT INTO `customer_mas_industry` VALUES ('21', 'Aviation / Airlines');
INSERT INTO `customer_mas_industry` VALUES ('22', 'Business Supplies');
INSERT INTO `customer_mas_industry` VALUES ('23', 'Package / Courier / Delivery');
INSERT INTO `customer_mas_industry` VALUES ('24', 'Political Organization');
INSERT INTO `customer_mas_industry` VALUES ('25', 'Venture Capital');

-- ----------------------------
-- Table structure for customer_member
-- ----------------------------
DROP TABLE IF EXISTS `customer_member`;
CREATE TABLE `customer_member` (
  `BranchID` varchar(10) NOT NULL,
  `MemberID` varchar(20) NOT NULL,
  `Member_name` varchar(50) NOT NULL,
  `Member_name_alias` varchar(50) DEFAULT NULL,
  `Address` varchar(255) NOT NULL,
  `Phone` varchar(15) NOT NULL,
  `Fax` varchar(15) DEFAULT NULL,
  `Email` varchar(50) DEFAULT NULL,
  `Discount` decimal(7,2) NOT NULL,
  `Admin_cost` decimal(10,2) NOT NULL,
  `StatusID` int(11) NOT NULL,
  `Created_at` datetime NOT NULL,
  `Created_by` varchar(50) NOT NULL,
  `Updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `Updated_by` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`MemberID`),
  KEY `BranchID` (`BranchID`),
  KEY `Name` (`MemberID`,`Member_name`,`Member_name_alias`),
  KEY `StatusID` (`StatusID`),
  KEY `Phone` (`Phone`),
  KEY `Created_by` (`Created_by`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

SET FOREIGN_KEY_CHECKS=1;