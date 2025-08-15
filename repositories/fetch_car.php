<?php
//connect to database
require_once("../repositories/db_connect.php"); 
/*
DELIMITER //

CREATE PROCEDURE Get_Thumbnail()
BEGIN
    DECLARE rand_car_id INT;

    SELECT c_id INTO rand_car_id
    FROM cars
    ORDER BY RAND()
    LIMIT 1;

    SELECT 
        c.c_id,
        c.c_name,
        ci.img_url
    FROM cars c
    JOIN car_images ci ON c.c_id = ci.car_id
    WHERE c.c_id = rand_car_id
      AND ci.img_id = (
          SELECT MIN(img_id)
          FROM car_images
          WHERE car_id = rand_car_id
      );
END //

DELIMITER ;

*/

/*
DELIMITER //

CREATE PROCEDURE Get_Four_Other_Cars(IN excluded_car_id INT)
BEGIN
    SELECT 
        c.c_id,
        c.c_name,
        ci.img_url
    FROM cars c
    JOIN car_images ci ON c.c_id = ci.car_id
    WHERE c.c_id != excluded_car_id
      AND ci.img_id = (
          SELECT MIN(img_id)
          FROM car_images
          WHERE car_id = c.c_id
      )
    ORDER BY RAND()
    LIMIT 4;
END //

DELIMITER ;

*/

/*
DELIMITER //

CREATE PROCEDURE GetCarDetailsWithImages(IN car_id INT)
BEGIN
    -- Get car details
    SELECT 
        c.c_id,
        c.c_name,
        c.c_type,
        c.c_model,
        c.c_brand,
        c.c_engine,
        c.c_release_date,
        c.c_country,
        c.c_features,
        c.c_interesting_facts,
        c.c_description
    FROM 
        cars c
    WHERE 
        c.c_id = car_id;
    
    -- Get all images for this car
    SELECT 
        ci.img_id,
        ci.img_url
    FROM 
        car_images ci
    WHERE 
        ci.car_id = car_id;
END //

DELIMITER ;
*/ 

/*
DELIMITER $$

CREATE PROCEDURE GetCarReviews(IN carId INT)
BEGIN
    SELECT 
        u.u_name,
        u.u_type,
        r.r_star,
        r.thumbs_ups,
        r.thumbs_downs,
        r.r_topic,
        r.r_description
    FROM reviews r
    INNER JOIN users u ON r.u_id = u.u_id
    WHERE r.c_id = carId;
END $$

DELIMITER ;
*/ 
/*DELIMITER //
CREATE PROCEDURE search_cars(
    IN p_brand VARCHAR(50),
    IN p_engine VARCHAR(100),
    IN p_year_start INT,
    IN p_year_end INT,
    IN p_country VARCHAR(50),
    IN p_keyword VARCHAR(100)
)
BEGIN
    SELECT * 
    FROM cars
    WHERE (p_brand IS NULL OR p_brand = '' OR c_brand = p_brand)
      AND (p_engine IS NULL OR p_engine = '' OR c_engine LIKE CONCAT('%', p_engine, '%'))
      AND (p_year_start IS NULL OR p_year_end IS NULL OR YEAR(c_release_date) BETWEEN p_year_start AND p_year_end)
      AND (p_country IS NULL OR p_country = '' OR c_country = p_country)
      AND (
            p_keyword IS NULL OR p_keyword = '' OR
            c_name LIKE CONCAT('%', p_keyword, '%') OR
            c_model LIKE CONCAT('%', p_keyword, '%') OR
            c_features LIKE CONCAT('%', p_keyword, '%') OR
            c_interesting_facts LIKE CONCAT('%', p_keyword, '%') OR
            c_description LIKE CONCAT('%', p_keyword, '%')
          );
END //
DELIMITER ;
*/
?>