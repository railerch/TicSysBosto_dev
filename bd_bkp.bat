PATH C:\\xampp\\mysql\\bin\\
setlocal enableextensions
mysqldump --databases --host=localhost --user=TicSys --password=TicSys_2040 TicSys > "C:\\xampp\\htdocs\\TicSysBosto\\database\\BKP\\Tickets_db_BKP.sql"