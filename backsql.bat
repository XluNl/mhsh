@echo off
forfiles /p "c:\backsql" /m hzxql_backup_*.sql -d -7 /c "cmd /c del /f @path"
set "Ymd=%date:~0,4%%date:~5,2%%date:~8,2%0%time:~1,1%%time:~3,2%%time:~6,2%"
"C:/phpstudy_pro/Extensions/MySQL5.7.26/bin/mysqldump.exe" --default-character-set=utf8mb4 -uxql -pxql8888 hzxql > C:/backsql/hzxql_backup_%Ymd%.sql
@echo on