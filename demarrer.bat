@echo off
title PokéGuess - Serveur local

:: Cherche PHP dans les emplacements courants
set PHP_EXE=
if exist "C:\php\php.exe"           set PHP_EXE=C:\php\php.exe
if exist "C:\xampp\php\php.exe"     set PHP_EXE=C:\xampp\php\php.exe
if exist "C:\laragon\bin\php\php.exe" set PHP_EXE=C:\laragon\bin\php\php.exe

:: Si toujours pas trouvé, cherche dans le PATH
if "%PHP_EXE%"=="" where php >nul 2>&1 && set PHP_EXE=php

if "%PHP_EXE%"=="" (
    echo.
    echo  PHP introuvable sur ton PC.
    echo.
    echo  Telecharge le zip PHP sur : https://windows.php.net/download/
    echo  Extrais-le dans C:\php
    echo  Puis relance ce fichier.
    echo.
    pause
    exit /b 1
)

echo.
echo  Serveur demarre sur http://localhost:8000
echo  Ferme cette fenetre pour arreter le serveur.
echo.

:: Ouvre le navigateur apres 1 seconde
start /b cmd /c "timeout /t 1 >nul && start http://localhost:8000?uc=game"

:: Lance le serveur PHP
"%PHP_EXE%" -S localhost:8000 -t public/
