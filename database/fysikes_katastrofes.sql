
DROP DATABASE FYSIKES_KATASTROFES;
CREATE DATABASE IF NOT EXISTS FYSIKES_KATASTROFES;
USE FYSIKES_KATASTROFES;

-- Πίνακας Διαχειριστή
CREATE TABLE  Admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL
);
-- Πίνακας Διασώστη
CREATE TABLE Rescuers (
    rescuer_id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT(4),
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Πίνακας Πολίτη
CREATE TABLE Citizens (
    citizen_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    latitude DECIMAL(9,6) NOT NULL,
    longitude DECIMAL(9,6) NOT NULL
);


-- Πίνακας Κατηγοριών Ειδών
CREATE TABLE Categories (
    category_id INT PRIMARY KEY auto_increment,
    category_name VARCHAR(255)
);


CREATE TABLE Products (
    product_id INT PRIMARY KEY auto_increment,
    name VARCHAR(255),
    category_id INT,
    quantity INT,
    FOREIGN KEY(category_id) REFERENCES Categories(category_id)
);


-- Πίνακας Αιτημάτων Πολίτη
CREATE TABLE CitizenRequests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    citizen_id INT,
    product_id INT,
    quantity INT(9),
    number_of_people INT(8),
    status VARCHAR(50) NOT NULL DEFAULT 'new',
    date_submitted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_accepted DATETIME,
    date_completed DATETIME,
    rescuer_id INT,
    FOREIGN KEY (citizen_id) REFERENCES Citizens(citizen_id),
    FOREIGN KEY(rescuer_id) REFERENCES Rescuers(rescuer_id)
);

-- Πίνακας Προσφορών Πολίτη
CREATE TABLE CitizenOffers (
    offer_id INT AUTO_INCREMENT PRIMARY KEY,
    citizen_id INT,
    announcement_id INT,
    product_id INT,
    quantity INT,
    status VARCHAR(50) NOT NULL DEFAULT 'new',
    date_submitted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_accepted DATETIME,
    date_completed DATETIME,
    rescuer_id INT,
     FOREIGN KEY (citizen_id) REFERENCES Citizens(citizen_id),
     FOREIGN KEY (rescuer_id) REFERENCES Rescuers(rescuer_id)
    );

CREATE TABLE Announcements (
    announcement_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE AnnouncementProducts (
    announcement_id INT,
    product_id INT,
    PRIMARY KEY (announcement_id, product_id),
    FOREIGN KEY (announcement_id) REFERENCES Announcements(announcement_id),
    FOREIGN KEY (product_id) REFERENCES Products(product_id)
);

-- Πίνακας Χάρτη
CREATE TABLE Map (
    map_id INT AUTO_INCREMENT PRIMARY KEY,
    base_latitude DECIMAL(9,6) NOT NULL,
    base_longitude DECIMAL(9,6) NOT NULL
           
);



-- Πίνακας για την κατάσταση των οχημάτων των διασωστών
CREATE TABLE Vehicles (
    VehicleID INT AUTO_INCREMENT PRIMARY KEY ,
    VehicleName VARCHAR(255) NOT NULL,
    vehicle_latitude DECIMAL(9,6) NOT NULL,            
    vehicle_longitude DECIMAL(9,6) NOT NULL, 
    rescuer_id INT,
    map_id INT,
    Status VARCHAR(50) DEFAULT 'Available',-- Κατάσταση οχήματος (Available, In Use)
    FOREIGN KEY (rescuer_id) REFERENCES Rescuers(rescuer_id),
    FOREIGN KEY(map_id) REFERENCES Map(map_id)
    );
    
CREATE TABLE RescuerInventory (
    inventory_id INT AUTO_INCREMENT PRIMARY KEY,
    rescuer_id INT,
    product_id INT,
    quantity INT,
    vehicle_id INT,
    UNIQUE KEY unique_rescuer_product (rescuer_id, product_id),
    FOREIGN KEY (rescuer_id) REFERENCES Rescuers(rescuer_id),
    FOREIGN KEY (product_id) REFERENCES Products(product_id),
    FOREIGN KEY (vehicle_id) REFERENCES Vehicles(VehicleID)
);

INSERT INTO Admins(username, password) VALUES("kaplanis","kaplanis");

INSERT INTO `Rescuers` (`rescuer_id`, `task_id`, `username`, `password`) VALUES 
(NULL, NULL, 'd21', '1234'), 
(NULL, NULL, 'd22', '1234'), 
(NULL, NULL, 'diaswstis3', '$2y$10$p9QLk7Cs/eTTbYaGWqxlO.RymWzMHDVomWkTXyhENpN6NffAX9FzC');

INSERT INTO `Citizens` (`citizen_id`, `username`, `password`, `full_name`, `phone_number`, `latitude`, `longitude`) VALUES 
(NULL, 'politis1', '1234', 'ΚΩΣΤΑΣ', '6904445560', '38.247719', '21.738038'), 
(NULL, 'politis2', '1234', 'ΑΝΝΑ', '6579854677', '38.245545', '21.730850'), 
(NULL, 'politis3', '1234', 'ΜΑΡΙΑ', '6947002976', '38.242848', '21.734090'), 
(NULL, 'politis4', '1234', 'ΑΓΓΕΛΟΣ', '6932694555', '38.244618', '21.741750'), 
(NULL, 'politis5', '1234', 'ΓΙΑΝΝΗΣ', '6946577930', '38.242900', '21.740291');

INSERT INTO `Map` (`map_id`, `base_latitude`, `base_longitude`) VALUES (NULL, '38.249572', '21.749505');

INSERT INTO `Vehicles` (`VehicleID`, `VehicleName`, `vehicle_latitude`, `vehicle_longitude`, `rescuer_id`, `map_id`, `Status`) VALUES 
(NULL, 'TOYOTA',  '38.241955', '21.734733', '1', '1', 'Available'), 
(NULL, 'NISSAN',  '38.242400', '21.734330', '2', '1', 'Available'), 
(NULL, 'DATSUN',  '38.242401', '21.734321', '3', '1', 'Available');

DELIMITER //
CREATE TRIGGER prevent_duplicate_categories
BEFORE INSERT ON Categories
FOR EACH ROW
BEGIN
    DECLARE category_count INT;

    -- Έλεγχος αν υπάρχει ήδη η κατηγορία
    SELECT COUNT(*) INTO category_count
    FROM Categories
    WHERE category_name = NEW.category_name;

    -- Αν υπάρχει, τότε ακύρωση της εισαγωγής
    IF category_count > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Αδυναμία εισαγωγής. Η κατηγορία υπάρχει ήδη.';
    END IF;
END;
//
DELIMITER ;

DELIMITER //
CREATE TRIGGER prevent_duplicate_products
BEFORE INSERT ON products
FOR EACH ROW
BEGIN
    DECLARE product_count INT;

    -- Έλεγχος αν υπάρχει ήδη η κατηγορία
    SELECT COUNT(*) INTO product_count
    FROM products
    WHERE name = NEW.name;

    -- Αν υπάρχει, τότε ακύρωση της εισαγωγής
    IF product_count > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Αδυναμία εισαγωγής. Το προϊόν υπάρχει ήδη.';
    END IF;
END;
//
DELIMITER ;
DELIMITER //

CREATE TRIGGER before_rescuer_inventory_insert
BEFORE INSERT ON RescuerInventory
FOR EACH ROW
BEGIN
    -- Check if there is an existing row with the same rescuer_id and product_id
    DECLARE existing_inventory_id INT;
    DECLARE existing_quantity INT;

    SELECT inventory_id, quantity INTO existing_inventory_id, existing_quantity
    FROM RescuerInventory
    WHERE rescuer_id = NEW.rescuer_id AND product_id = NEW.product_id
    LIMIT 1;

    -- If an existing row is found, update the quantity value and keep the same inventory_id
    IF existing_inventory_id IS NOT NULL THEN
        SET NEW.quantity = existing_quantity + NEW.quantity;
        SET NEW.inventory_id = existing_inventory_id;
    ELSE
        -- If no existing row is found, find the next available inventory_id
        SELECT COALESCE(MAX(inventory_id) + 1, 1) INTO existing_inventory_id
        FROM RescuerInventory;

        SET NEW.inventory_id = existing_inventory_id;
    END IF;
END;
//

DELIMITER ;







