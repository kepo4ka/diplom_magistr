SET table=coronovirus

echo %table%

@ECHO OFF
echo Droping...
echo Y | mysqladmin -h localhost -u root drop %table%
echo Creating...
echo Y | mysqladmin -h localhost -u root create %table%
echo Importing...
mysql -u root %table% < %table%.sql
echo Complete!
pause