/* ---- Evento 15m -- -- */
DROP EVENT IF EXISTS ev_cleanup_15m;

DELIMITER $$

CREATE EVENT ev_cleanup_15m ON SCHEDULE EVERY 15 MINUTE

DO
BEGIN

    DECLARE now_time INT UNSIGNED;
    SET now_time = UNIX_TIMESTAMP();

    DELETE FROM u_sessions WHERE session_last_activity < now_time - 1800;
    DELETE FROM w_activate WHERE used = 1 OR expire_at < now_time;
    DELETE FROM w_contacts WHERE time < now_time - 604800;

END$$

DELIMITER ;

/* ---- Evento daily -- -- */
DROP EVENT IF EXISTS ev_cleanup_daily;

DELIMITER $$

CREATE EVENT ev_cleanup_daily ON SCHEDULE EVERY 1 DAY

DO
BEGIN

    DECLARE now_time INT UNSIGNED;
    SET now_time = UNIX_TIMESTAMP();

    DELETE FROM u_login_attempts WHERE created_at < now_time - 7776000;
    DELETE FROM u_nicks WHERE estado = 'accepted';
    DELETE FROM w_blacklist WHERE type = 99;

END$$

DELIMITER ;
