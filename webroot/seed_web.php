<?php
/**
 * Script web para gerar dados de teste - Execute via browser
 * URL: http://localhost/cglconecta/webroot/seed_web.php
 */

// Configuração de erro
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Definir separador de diretório se não existir
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

// Definir o path para o CakePHP
define('ROOT', dirname(__DIR__));
define('APP_DIR', 'src');
define('WEBROOT_DIR', 'webroot');
define('WWW_ROOT', __DIR__ . DS);
define('CONFIG', ROOT . DS . 'config' . DS);

// Carregar o bootstrap do CakePHP
if (file_exists(ROOT . '/config/bootstrap.php')) {
    require ROOT . '/config/bootstrap.php';
} else {
    die('Erro: Não foi possível carregar o bootstrap do CakePHP');
}

use Cake\Datasource\ConnectionManager;

// Carregar constantes se existir
if (file_exists(ROOT . '/vendor/KanbadaPackages/Constants.php')) {
    require_once ROOT . '/vendor/KanbadaPackages/Constants.php';
} else {
    die('Erro: Arquivo de constantes não encontrado');
}

// HTML Header
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gerador de Dados de Teste - CGL Conecta</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .info { color: #17a2b8; }
        .warning { color: #ffc107; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
        .button { 
            background: #007bff; color: white; padding: 10px 20px; 
            border: none; border-radius: 4px; cursor: pointer; 
            text-decoration: none; display: inline-block; margin: 10px 5px 0 0;
        }
        .button:hover { background: #0056b3; }
        .status-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .status-card { background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #007bff; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Gerador de Dados de Teste - CGL Conecta</h1>
        
        <?php
        if (isset($_GET['action']) && $_GET['action'] === 'generate') {
            generateTestData();
        } else {
            showMenu();
        }
        ?>
    </div>
</body>
</html>

<?php

function showMenu() {
    echo '<div class="info">';
    echo '<h2>📋 Este script irá gerar dados de teste para simular um sistema em produção</h2>';
    echo '<p><strong>O que será criado:</strong></p>';
    echo '<ul>';
    echo '<li>✅ <strong>20 clientes</strong> com dados realistas de empresas</li>';
    echo '<li>✅ <strong>200 pedidos</strong> com diferentes status e valores</li>';
    echo '<li>✅ <strong>3 usuários</strong> (1 admin + 2 clientes)</li>';
    echo '<li>✅ <strong>Histórico completo</strong> de alterações de status</li>';
    echo '<li>✅ <strong>Comentários</strong> associados às mudanças</li>';
    echo '<li>✅ <strong>Datas de entrega</strong> com cronograma realista</li>';
    echo '</ul>';
    echo '</div>';
    
    echo '<div class="warning">';
    echo '<h3>⚠️ ATENÇÃO:</h3>';
    echo '<p>Este script irá <strong>LIMPAR</strong> todos os dados existentes das tabelas:</p>';
    echo '<p><code>customers, orders, orders_log, comments</code></p>';
    echo '<p>Certifique-se de fazer backup se necessário!</p>';
    echo '</div>';
    
    echo '<a href="?action=generate" class="button">🎯 Gerar Dados de Teste</a>';
    echo '<a href="../" class="button" style="background: #6c757d;">🏠 Voltar ao Sistema</a>';
}

function generateTestData() {
    try {
        // Tentar conectar ao banco
        $connection = ConnectionManager::get('default');
        echo '<div class="success"><h2>✅ Conectado ao banco de dados!</h2></div>';
        
        // Verificar se as constantes estão definidas
        if (!defined('C_PedidosStatusRecebido')) {
            echo '<div class="error"><h3>❌ Erro: Constantes não carregadas!</h3></div>';
            echo '<p>Definindo constantes manualmente...</p>';
            
            // Definir constantes básicas se não estiverem carregadas
            define('C_UsersTypeAdmin', 2);
            define('C_UsersTypeCliente', 1);
            define('C_PedidosStatusRecebido', 1);
            define('C_PedidosStatusEmProjeto', 2);
            define('C_PedidosStatusEmFabricação', 3);
            define('C_PedidosStatusEmTransporte', 4);
            define('C_PedidosStatusEntregue', 5);
        }
        
        $seeder = new WebTestDataSeeder($connection);
        $seeder->run();
        
    } catch (Exception $e) {
        echo '<div class="error">';
        echo '<h2>❌ Erro durante a execução:</h2>';
        echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
        echo '<p><strong>Stack trace:</strong></p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        echo '</div>';
    }
}

class WebTestDataSeeder
{
    private $connection;
    private $customerIds = [];
    private $userIds = [];
    private $orderIds = [];

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function run()
    {
        echo '<div class="info"><h3>🔄 Iniciando geração de dados...</h3></div>';
        
        $this->clearExistingData();
        $this->seedUsers();
        $this->seedCustomers();
        $this->seedOrders();
        
        echo '<div class="success"><h2>🎉 Dados gerados com sucesso!</h2></div>';
        $this->showSummary();
    }

    private function clearExistingData()
    {
        echo '<p>🧹 Limpando dados existentes...</p>';
        
        $this->connection->execute('SET FOREIGN_KEY_CHECKS = 0');
        $this->connection->execute('TRUNCATE TABLE comments');
        $this->connection->execute('TRUNCATE TABLE orders_log');
        $this->connection->execute('TRUNCATE TABLE orders');
        $this->connection->execute('TRUNCATE TABLE customers');
        $this->connection->execute('DELETE FROM users WHERE id > 1');
        $this->connection->execute('SET FOREIGN_KEY_CHECKS = 1');
        
        echo '<div class="success">✅ Dados limpos!</div>';
    }

    private function seedUsers()
    {
        echo '<p>📝 Criando usuários...</p>';
        
        // Verificar se já existe admin
        $existingAdmin = $this->connection->execute('SELECT id FROM users WHERE email = ? LIMIT 1', ['admin@cglconecta.com'])->fetch();
        
        if (!$existingAdmin) {
            // Usuário administrador
            $adminData = [
                'name' => 'Administrador Sistema',
                'email' => 'admin@cglconecta.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'admin' => 1,
                'id_customer' => null,
                'type' => defined('C_UsersTypeAdmin') ? C_UsersTypeAdmin : 2,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ];
            
            $this->connection->insert('users', $adminData);
            $this->userIds[] = $this->connection->lastInsertId();
            echo '<div class="success">✅ Admin criado</div>';
        } else {
            $this->userIds[] = $existingAdmin['id'];
            echo '<div class="info">ℹ️ Admin já existe</div>';
        }
        
        // Clientes
        $clientUsers = [
            [
                'name' => 'João Silva Santos',
                'email' => 'joao.silva@empresa1.com',
                'password' => password_hash('cliente123', PASSWORD_DEFAULT),
                'admin' => 0,
                'type' => defined('C_UsersTypeCliente') ? C_UsersTypeCliente : 1
            ],
            [
                'name' => 'Maria Oliveira Costa',
                'email' => 'maria.costa@empresa2.com', 
                'password' => password_hash('cliente123', PASSWORD_DEFAULT),
                'admin' => 0,
                'type' => defined('C_UsersTypeCliente') ? C_UsersTypeCliente : 1
            ]
        ];
        
        foreach ($clientUsers as $userData) {
            $existingUser = $this->connection->execute('SELECT id FROM users WHERE email = ? LIMIT 1', [$userData['email']])->fetch();
            
            if (!$existingUser) {
                $userData['created'] = date('Y-m-d H:i:s');
                $userData['modified'] = date('Y-m-d H:i:s');
                $userData['id_customer'] = null;
                
                $this->connection->insert('users', $userData);
                $this->userIds[] = $this->connection->lastInsertId();
            } else {
                $this->userIds[] = $existingUser['id'];
            }
        }
        
        echo '<div class="success">✅ ' . count($this->userIds) . ' usuários processados</div>';
    }

    private function seedCustomers()
    {
        echo '<p>🏢 Criando clientes...</p>';
        
        $companies = [
            ['name' => 'Construtora Alpha Ltda', 'cnpj' => '12.345.678/0001-90', 'address' => 'Rua das Flores, 123 - Centro - São Paulo/SP'],
            ['name' => 'Indústria Beta S.A.', 'cnpj' => '23.456.789/0001-01', 'address' => 'Av. Industrial, 456 - Distrito Industrial - Campinas/SP'],
            ['name' => 'Comercial Gama Eireli', 'cnpj' => '34.567.890/0001-12', 'address' => 'Rua do Comércio, 789 - Vila Comercial - Santos/SP'],
            ['name' => 'Metalúrgica Delta Ltda', 'cnpj' => '45.678.901/0001-23', 'address' => 'Estrada da Metalurgia, 1000 - Zona Industrial - São Bernardo/SP'],
            ['name' => 'Transportadora Epsilon', 'cnpj' => '56.789.012/0001-34', 'address' => 'Rod. dos Bandeirantes, Km 25 - Jundiaí/SP'],
            ['name' => 'Construtora Zeta S.A.', 'cnpj' => '67.890.123/0001-45', 'address' => 'Av. Paulista, 1500 - Bela Vista - São Paulo/SP'],
            ['name' => 'Fábrica Eta Industrial', 'cnpj' => '78.901.234/0001-56', 'address' => 'Rua da Fábrica, 200 - Industrial - Guarulhos/SP'],
            ['name' => 'Empresa Theta Ltda', 'cnpj' => '89.012.345/0001-67', 'address' => 'Av. do Estado, 800 - Centro - Osasco/SP'],
            ['name' => 'Inovação Iota Tech', 'cnpj' => '90.123.456/0001-78', 'address' => 'Rua da Tecnologia, 50 - Tech Park - Campinas/SP'],
            ['name' => 'Soluções Kappa Eng.', 'cnpj' => '01.234.567/0001-89', 'address' => 'Av. das Engenharias, 300 - Vila Madalena - São Paulo/SP'],
            ['name' => 'Lambda Construções', 'cnpj' => '11.345.678/0001-90', 'address' => 'Rua dos Construtores, 150 - Centro - Ribeirão Preto/SP'],
            ['name' => 'Mu Serviços Gerais', 'cnpj' => '12.456.789/0001-01', 'address' => 'Av. Central, 600 - Centro - Sorocaba/SP'],
            ['name' => 'Nu Tecnologia S.A.', 'cnpj' => '13.567.890/0001-12', 'address' => 'Complexo Tecnológico, 100 - Alphaville - Barueri/SP'],
            ['name' => 'Xi Metalurgia Ltda', 'cnpj' => '14.678.901/0001-23', 'address' => 'Distrito Industrial, 500 - Taubaté/SP'],
            ['name' => 'Omicron Solutions', 'cnpj' => '15.789.012/0001-34', 'address' => 'Business Center, 250 - Vila Olímpia - São Paulo/SP'],
            ['name' => 'Pi Engenharia Civil', 'cnpj' => '16.890.123/0001-45', 'address' => 'Rua dos Engenheiros, 75 - Centro - Piracicaba/SP'],
            ['name' => 'Rho Infraestrutura', 'cnpj' => '17.901.234/0001-56', 'address' => 'Av. da Infraestrutura, 400 - Industrial - São José dos Campos/SP'],
            ['name' => 'Sigma Projetos Ltda', 'cnpj' => '18.012.345/0001-67', 'address' => 'Rua dos Projetos, 180 - Centro - Araraquara/SP'],
            ['name' => 'Tau Desenvolvimento', 'cnpj' => '19.123.456/0001-78', 'address' => 'Av. do Desenvolvimento, 320 - Jardins - São Paulo/SP'],
            ['name' => 'Upsilon Inovações', 'cnpj' => '20.234.567/0001-89', 'address' => 'Centro de Inovação, 90 - Tecnópolis - Campinas/SP']
        ];

        foreach ($companies as $i => $company) {
            $this->connection->insert('customers', $company);
            $customerId = $this->connection->lastInsertId();
            $this->customerIds[] = $customerId;
            
            // Associar usuários aos primeiros 2 clientes
            if ($i < 2 && isset($this->userIds[$i + 1])) {
                $this->connection->update('users', 
                    ['id_customer' => $customerId], 
                    ['id' => $this->userIds[$i + 1]]
                );
            }
        }
        
        echo '<div class="success">✅ ' . count($this->customerIds) . ' clientes criados</div>';
    }

    private function seedOrders()
    {
        echo '<p>📦 Criando pedidos e histórico...</p>';
        
        $orderNumber = 2025001;
        $totalOrders = 200;
        
        for ($i = 0; $i < $totalOrders; $i++) {
            $customerId = $this->customerIds[array_rand($this->customerIds)];
            $createdDate = $this->randomDateInRange('-6 months', 'now');
            $value = rand(5000, 500000);
            $estimateToReady = date('Y-m-d', strtotime($createdDate . ' +' . rand(15, 60) . ' days'));
            
            $orderData = [
                'number' => $orderNumber++,
                'id_customer' => $customerId,
                'value' => $value,
                'status' => defined('C_PedidosStatusRecebido') ? C_PedidosStatusRecebido : 1,
                'type' => 1,
                'role' => 1,
                'estimatetoready' => $estimateToReady,
                'created' => $createdDate,
                'modified' => $createdDate
            ];
            
            $this->connection->insert('orders', $orderData);
            $orderId = $this->connection->lastInsertId();
            $this->orderIds[] = $orderId;
            
            $this->createOrderHistory($orderId, $createdDate, $value);
            
            // Progress indicator
            if (($i + 1) % 50 == 0) {
                echo '<div class="info">📈 Processados ' . ($i + 1) . ' pedidos...</div>';
            }
        }
        
        echo '<div class="success">✅ ' . count($this->orderIds) . ' pedidos criados</div>';
    }

    private function createOrderHistory($orderId, $orderCreatedDate, $orderValue)
    {
        $userId = $this->userIds[0];
        $currentStatus = C_PedidosStatusRecebido;
        $currentDate = $orderCreatedDate;
        
        // Log inicial
        $this->createStatusLog($orderId, $userId, null, $currentStatus, $currentDate, 
            "Pedido recebido no sistema. Valor: R$ " . number_format($orderValue, 2, ',', '.'));
        
        $progressionChance = rand(1, 100);
        
        // Evolução do status com probabilidades
        if ($progressionChance > 20) {
            $currentDate = $this->addRandomDays($currentDate, 1, 5);
            $currentStatus = C_PedidosStatusEmProjeto;
            $this->createStatusLog($orderId, $userId, C_PedidosStatusRecebido, $currentStatus, $currentDate,
                "Projeto iniciado. Análise técnica em andamento.");
            $this->connection->update('orders', ['status' => $currentStatus, 'modified' => $currentDate], ['id' => $orderId]);
        }
        
        if ($progressionChance > 40) {
            $currentDate = $this->addRandomDays($currentDate, 3, 10);
            $currentStatus = C_PedidosStatusEmFabricação;
            $this->createStatusLog($orderId, $userId, C_PedidosStatusEmProjeto, $currentStatus, $currentDate,
                "Projeto aprovado. Iniciando processo de fabricação.");
            $this->connection->update('orders', ['status' => $currentStatus, 'modified' => $currentDate], ['id' => $orderId]);
        }
        
        if ($progressionChance > 60) {
            $currentDate = $this->addRandomDays($currentDate, 7, 21);
            $currentStatus = C_PedidosStatusEmTransporte;
            $this->createStatusLog($orderId, $userId, C_PedidosStatusEmFabricação, $currentStatus, $currentDate,
                "Fabricação concluída. Produto em transporte para entrega.");
            $this->connection->update('orders', ['status' => $currentStatus, 'modified' => $currentDate], ['id' => $orderId]);
        }
        
        if ($progressionChance > 80) {
            $currentDate = $this->addRandomDays($currentDate, 1, 3);
            $currentStatus = C_PedidosStatusEntregue;
            $this->createStatusLog($orderId, $userId, C_PedidosStatusEmTransporte, $currentStatus, $currentDate,
                "Produto entregue com sucesso. Pedido finalizado.");
            $this->connection->update('orders', ['status' => $currentStatus, 'modified' => $currentDate], ['id' => $orderId]);
        }
        
        // Comentários gerais aleatórios
        if (rand(1, 100) > 70) {
            $this->addRandomComments($orderId, $orderCreatedDate);
        }
    }

    private function createStatusLog($orderId, $userId, $oldStatus, $newStatus, $date, $message)
    {
        $logData = [
            'id_order' => $orderId,
            'id_user' => $userId,
            'message' => $message,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'created' => $date,
            'modified' => $date
        ];
        
        $this->connection->insert('orders_log', $logData);
        $logId = $this->connection->lastInsertId();
        
        $commentData = [
            'id_order' => $orderId,
            'id_user' => $userId,
            'message' => $message,
            'refcomment' => $logId,
            'created' => $date,
            'modified' => $date
        ];
        
        $this->connection->insert('comments', $commentData);
    }

    private function addRandomComments($orderId, $baseDate)
    {
        $comments = [
            "Cliente solicitou informações adicionais sobre o projeto.",
            "Verificação de especificações técnicas realizada.",
            "Cliente confirmou aprovação do orçamento.",
            "Ajustes solicitados pelo cliente foram implementados.",
            "Reunião de alinhamento realizada com sucesso.",
            "Documentação técnica enviada para análise."
        ];
        
        $numComments = rand(1, 2);
        for ($i = 0; $i < $numComments; $i++) {
            $commentDate = $this->addRandomDays($baseDate, rand(1, 30), rand(35, 60));
            $message = $comments[array_rand($comments)];
            
            $commentData = [
                'id_order' => $orderId,
                'id_user' => $this->userIds[0],
                'message' => $message,
                'refcomment' => null,
                'created' => $commentDate,
                'modified' => $commentDate
            ];
            
            $this->connection->insert('comments', $commentData);
        }
    }

    private function randomDateInRange($start, $end)
    {
        $startTime = strtotime($start);
        $endTime = strtotime($end);
        $randomTime = rand($startTime, $endTime);
        return date('Y-m-d H:i:s', $randomTime);
    }

    private function addRandomDays($date, $minDays, $maxDays)
    {
        $days = rand($minDays, $maxDays);
        return date('Y-m-d H:i:s', strtotime($date . " +{$days} days"));
    }

    private function showSummary()
    {
        echo '<h3>📊 Resumo dos Dados Gerados</h3>';
        
        $stats = [
            'Usuários' => count($this->userIds),
            'Clientes' => count($this->customerIds),
            'Pedidos' => count($this->orderIds)
        ];
        
        $logsCount = $this->connection->execute('SELECT COUNT(*) as count FROM orders_log')->fetch()['count'];
        $commentsCount = $this->connection->execute('SELECT COUNT(*) as count FROM comments')->fetch()['count'];
        
        $stats['Logs de Status'] = $logsCount;
        $stats['Comentários'] = $commentsCount;
        
        echo '<div class="status-grid">';
        foreach ($stats as $label => $count) {
            echo "<div class='status-card'><strong>{$label}:</strong> {$count}</div>";
        }
        echo '</div>';
        
        echo '<h4>📈 Distribuição por Status</h4>';
        $statusCounts = $this->connection->execute('
            SELECT status, COUNT(*) as count 
            FROM orders 
            GROUP BY status 
            ORDER BY status
        ')->fetchAll();
        
        echo '<div class="status-grid">';
        foreach ($statusCounts as $statusCount) {
            $statusName = $this->getStatusName($statusCount['status']);
            echo "<div class='status-card'>";
            echo "<strong>{$statusName}:</strong> {$statusCount['count']} pedidos";
            echo "</div>";
        }
        echo '</div>';
        
        echo '<div class="info">';
        echo '<h4>🔑 Credenciais de Acesso</h4>';
        echo '<ul>';
        echo '<li><strong>Admin:</strong> admin@cglconecta.com / admin123</li>';
        echo '<li><strong>Cliente 1:</strong> joao.silva@empresa1.com / cliente123</li>';
        echo '<li><strong>Cliente 2:</strong> maria.costa@empresa2.com / cliente123</li>';
        echo '</ul>';
        echo '</div>';
        
        echo '<a href="../" class="button">🏠 Ir para o Sistema</a>';
        echo '<a href="?" class="button" style="background: #6c757d;">🔄 Voltar</a>';
    }

    private function getStatusName($status)
    {
        $statusNames = [
            1 => 'Recebido',
            2 => 'Em Projeto', 
            3 => 'Em Fabricação',
            4 => 'Em Transporte',
            5 => 'Entregue'
        ];
        return $statusNames[$status] ?? 'Desconhecido';
    }
}
?>
