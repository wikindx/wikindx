-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Move valide sessions (1 day or 1 hour maxlifetime) from the old table to the new session table
-- 

INSERT INTO session (
    sessionId,
    sessionUserId,
    sessionLastAccessTimestamp,
    sessionData
)
    SELECT
        sessionId,
        sessionUserId,
        sessionLastAccessTimestamp,
        sessionData
    FROM session_647
    WHERE DATE_ADD(
        sessionLastAccessTimestamp,
        INTERVAL (
            CASE
                WHEN sessionUserId = 0 THEN 3600
                ELSE 68400
            END
        ) SECOND
    ) >= CURRENT_TIMESTAMP();
