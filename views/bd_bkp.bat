PATH C:\\xampp\\mysql\\bin\\
setlocal enableextensions
mysqldump --databases --host=localhost --user=tickets_root --password=Sevens1718* tickets > "C:\\xampp\\htdocs\\ticketsystem\\database\\BKP\\Tickets_db_BKP.sql"