@echo off
echo ================================================
echo    GERADOR DE DADOS DE TESTE - CGL CONECTA
echo ================================================
echo.
echo Este script ira gerar dados de teste para seu sistema:
echo - Usuarios (admin e clientes)  
echo - Clientes empresariais
echo - Pedidos com historico completo
echo - Comentarios e logs de status
echo.
echo IMPORTANTE: Os dados existentes serao PRESERVADOS
echo.
pause
echo.
echo Iniciando geracao...
echo.

cd /d "c:\Users\UCS\source\repos\cglconecta"
php bin/seed_test_data.php

echo.
echo ================================================
echo           PROCESSO CONCLUIDO!
echo ================================================
echo.
echo Para testar o sistema:
echo 1. Acesse: http://localhost/cglconecta/
echo 2. Use as credenciais:
echo    - Admin: admin@cglconecta.com / admin123
echo    - Cliente: joao.silva@empresa1.com / cliente123
echo.
pause
