DROP PROCEDURE IF EXISTS `statuswolf`.`foo`;

DELIMITER //

CREATE PROCEDURE foo()
READS SQL DATA
BEGIN

	DECLARE done INT(1) DEFAULT FALSE;
	DECLARE my_id VARCHAR(32);
	DECLARE cur1 CURSOR FOR SELECT id FROM saved_dashboards;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

	DROP TABLE IF EXISTS dashboard_rank;
	CREATE TABLE dashboard_rank (
		`id` VARCHAR(32),
		`count` INT(15) DEFAULT '0',
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

	OPEN cur1;

	read_loop: LOOP

		FETCH cur1 into my_id;
		IF done THEN
			LEAVE read_loop;
		END IF;
		INSERT INTO dashboard_rank VALUES(my_id,'');

	END LOOP read_loop;

	CLOSE cur1;

END//

DELIMITER ;

CALL foo();

DROP PROCEDURE IF EXISTS `statuswolf`.`foo`;
