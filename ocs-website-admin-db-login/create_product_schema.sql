-- Drop existing tables if any (respecting foreign key constraints)
DROP TABLE IF EXISTS PRINTER;
DROP TABLE IF EXISTS LAPTOP;
DROP TABLE IF EXISTS COMPUTER;
DROP TABLE IF EXISTS PRODUCT;

-- Create PRODUCT table
CREATE TABLE PRODUCT (
  PID INT PRIMARY KEY,
  PType VARCHAR(50),
  PPrice DECIMAL(10,2),
  PName VARCHAR(100),
  Description TEXT,
  PQuantity INT
);

-- Type-specific tables (subclasses)

-- COMPUTER table
CREATE TABLE COMPUTER (
  PID INT PRIMARY KEY,
  CPUType VARCHAR(50),
  FOREIGN KEY (PID) REFERENCES PRODUCT(PID)
);

-- LAPTOP table
CREATE TABLE LAPTOP (
  PID INT PRIMARY KEY,
  CPUType VARCHAR(50),
  Weight DECIMAL(5,2),
  BType VARCHAR(50),
  FOREIGN KEY (PID) REFERENCES PRODUCT(PID)
);

-- PRINTER table
CREATE TABLE PRINTER (
  PID INT PRIMARY KEY,
  PrinterType VARCHAR(50),
  Resolution VARCHAR(50),
  FOREIGN KEY (PID) REFERENCES PRODUCT(PID)
);