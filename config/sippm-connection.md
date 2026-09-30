# SIPPM database connection

SIMPM reads finalized history from the separate SIPPM database. Configure the `SIPPM_DB_*` variables in `.env` and keep the SIPPM database user read-only when possible.

The application connection name is `sippm` and the history model uses the SIPPM `laporans` table.
