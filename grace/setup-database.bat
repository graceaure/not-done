@echo off
echo ========================================
echo Grace Dashboard - Database Setup
echo ========================================
echo.
echo This will create the database and import the schema.
echo Make sure MySQL is running in XAMPP!
echo.
pause

cd /d C:\xampp\mysql\bin

echo Creating database...
mysql -u root -e "CREATE DATABASE IF NOT EXISTS demo CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"

echo Importing schema...
mysql -u root demo < "C:\xampp\htdocs\grace\database.sql"

echo.
echo ========================================
echo Database setup complete!
echo ========================================
echo.
echo You can now access the application at:
echo http://localhost/grace/
echo.
echo Default login:
echo Username: admin
echo Password: admin123
echo.
pause
