-- Insert into PRODUCT
INSERT INTO PRODUCT (PID, PType, PPrice, PName, Description, PQuantity)
VALUES 
(1001, 'Laptop', 999.99, 'Dell XPS 13', 'Ultra portable with Intel i7', 15),
(1002, 'Laptop', 849.99, 'HP Envy', 'Lightweight, long battery', 10),
(1003, 'Printer', 199.99, 'Canon PIXMA', 'Inkjet Color Printer', 20),
(1004, 'Printer', 299.99, 'HP LaserJet', 'Laser Monochrome Printer', 12),
(1005, 'Computer', 749.50, 'Lenovo ThinkCentre', 'Desktop with Intel i5', 8);

-- Insert into LAPTOP
INSERT INTO LAPTOP (PID, CPUType, Weight, BType)
VALUES 
(1001, 'Intel i7', 1.2, 'Li-ion'),
(1002, 'Intel i5', 1.4, 'Li-poly');

-- Insert into PRINTER
INSERT INTO PRINTER (PID, PrinterType, Resolution)
VALUES
(1003, 'Inkjet', '4800x1200'),
(1004, 'Laser', '1200x1200');

-- Insert into COMPUTER
INSERT INTO COMPUTER (PID, CPUType)
VALUES
(1005, 'Intel i5');