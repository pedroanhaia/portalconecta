<?php
/**
 * Script para gerar dados de teste que simulam um sistema em produção
 * 
 * Este script cria:
 * - 20 clientes com dados realistas
 * - 200 pedidos com diferentes status e datas
 * - Histórico de alterações de status para cada pedido
 * - Comentários associados às mudanças de status
 * - 3 usuários (1 admin e 2 clientes)
 * 
 * Para executar: php bin/seed_test_data.php
 */

// Bootstrap CakePHP
require dirname(__DIR__) . '/config/bootstrap.php';

use Cake\Datasource\ConnectionManager;

// Conectar ao banco
try {
    $connection = ConnectionManager::get('default');
    echo "Conectado ao banco de dados com sucesso!\n";
} catch (Exception $e) {
    echo "Erro ao conectar ao banco: " . $e->getMessage() . "\n";
    exit(1);
}

// Carregar constantes
require_once dirname(__DIR__) . '/vendor/KanbadaPackages/Constants.php';

class TestDataSeeder
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
        echo "\n=== INICIANDO GERAÇÃO DE DADOS DE TESTE ===\n\n";
        
        // $this->clearExistingData(); // Comentado para manter dados existentes
        echo "Mantendo dados existentes e adicionando novos registros...\n\n";
        
        $this->seedUsers();
        $this->seedCustomers();
        $this->seedOrders();
        
        echo "\n=== DADOS DE TESTE GERADOS COM SUCESSO! ===\n";
        $this->showSummary();
    }

    private function clearExistingData()
    {
        echo "Limpando dados existentes...\n";
        
        // Desabilitar verificações de chave estrangeira temporariamente
        $this->connection->execute('SET FOREIGN_KEY_CHECKS = 0');
        
        $this->connection->execute('TRUNCATE TABLE comments');
        $this->connection->execute('TRUNCATE TABLE orders_log');
        $this->connection->execute('TRUNCATE TABLE orders');
        $this->connection->execute('TRUNCATE TABLE customers');
        $this->connection->execute('DELETE FROM users WHERE id > 1'); // Manter usuário admin original se existir
        
        // Reabilitar verificações de chave estrangeira
        $this->connection->execute('SET FOREIGN_KEY_CHECKS = 1');
        
        echo "Dados limpos com sucesso!\n\n";
    }

    private function seedUsers()
    {
        echo "Criando usuários...\n";
        
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
                'type' => C_UsersTypeAdmin,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ];
            
            $this->connection->insert('users', $adminData);
            $adminId = $this->connection->lastInsertId();
            $this->userIds[] = $adminId;
            echo "Admin criado com ID: $adminId\n";
        } else {
            $this->userIds[] = $existingAdmin['id'];
            echo "Admin já existe, usando ID: {$existingAdmin['id']}\n";
        }
        
        // Usuários clientes (verificar se já existem)
        $clientUsers = [
            [
                'name' => 'João Silva Santos',
                'email' => 'joao.silva@empresa1.com',
                'password' => password_hash('cliente123', PASSWORD_DEFAULT),
                'admin' => 0,
                'type' => C_UsersTypeCliente
            ],
            [
                'name' => 'Maria Oliveira Costa',
                'email' => 'maria.costa@empresa2.com', 
                'password' => password_hash('cliente123', PASSWORD_DEFAULT),
                'admin' => 0,
                'type' => C_UsersTypeCliente
            ]
        ];
        
        foreach ($clientUsers as $userData) {
            // Verificar se o usuário já existe
            $existingUser = $this->connection->execute('SELECT id FROM users WHERE email = ? LIMIT 1', [$userData['email']])->fetch();
            
            if (!$existingUser) {
                $userData['created'] = date('Y-m-d H:i:s');
                $userData['modified'] = date('Y-m-d H:i:s');
                $userData['id_customer'] = null; // Será atualizado depois
                
                $this->connection->insert('users', $userData);
                $userId = $this->connection->lastInsertId();
                $this->userIds[] = $userId;
                echo "Cliente criado: {$userData['name']} (ID: $userId)\n";
            } else {
                $this->userIds[] = $existingUser['id'];
                echo "Cliente já existe: {$userData['name']} (ID: {$existingUser['id']})\n";
            }
        }
        
        echo "Total de usuários disponíveis: " . count($this->userIds) . "\n";
    }

    private function seedCustomers()
    {
        echo "Criando clientes...\n";
        
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

        $clientUserIndex = 1; // Começar do segundo usuário (primeiro é admin)
        
        foreach ($companies as $i => $company) {
            // Verificar se o cliente já existe pelo CNPJ
            $existingCustomer = $this->connection->execute('SELECT id FROM customers WHERE cnpj = ? LIMIT 1', [$company['cnpj']])->fetch();
            
            if (!$existingCustomer) {
                $this->connection->insert('customers', $company);
                $customerId = $this->connection->lastInsertId();
                echo "Cliente criado: {$company['name']} (ID: $customerId)\n";
            } else {
                $customerId = $existingCustomer['id'];
                echo "Cliente já existe: {$company['name']} (ID: $customerId)\n";
            }
            
            $this->customerIds[] = $customerId;
            
            // Associar usuários clientes aos primeiros 2 clientes (apenas se não estiverem já associados)
            if ($i < 2 && isset($this->userIds[$clientUserIndex])) {
                $userId = $this->userIds[$clientUserIndex];
                
                // Verificar se o usuário já está associado a algum cliente
                $userCustomer = $this->connection->execute('SELECT id_customer FROM users WHERE id = ?', [$userId])->fetch();
                
                if (!$userCustomer['id_customer']) {
                    $this->connection->update('users', 
                        ['id_customer' => $customerId], 
                        ['id' => $userId]
                    );
                    echo "Usuário ID $userId associado ao cliente ID $customerId\n";
                } else {
                    echo "Usuário ID $userId já associado ao cliente ID {$userCustomer['id_customer']}\n";
                }
                
                $clientUserIndex++;
            }
        }
        
        // Buscar todos os clientes existentes para usar nos pedidos
        $allCustomers = $this->connection->execute('SELECT id FROM customers')->fetchAll();
        $this->customerIds = array_column($allCustomers, 'id');
        
        echo "Total de clientes disponíveis: " . count($this->customerIds) . "\n";
    }

    private function seedOrders()
    {
        echo "Criando pedidos e histórico...\n";
        
        // Buscar o maior número de pedido existente
        $maxOrderNumber = $this->connection->execute('SELECT MAX(number) as max_number FROM orders')->fetch();
        $orderNumber = $maxOrderNumber['max_number'] ? $maxOrderNumber['max_number'] + 1 : 2025001;
        
        echo "Iniciando numeração de pedidos a partir de: $orderNumber\n";
        
        $totalOrders = 50; // Reduzido para não sobrecarregar
        
        // Buscar todos os usuários disponíveis para usar nos logs
        $allUsers = $this->connection->execute('SELECT id FROM users')->fetchAll();
        $allUserIds = array_column($allUsers, 'id');
        
        if (empty($allUserIds)) {
            echo "ERRO: Nenhum usuário encontrado para criar os logs!\n";
            return;
        }
        
        for ($i = 0; $i < $totalOrders; $i++) {
            $customerId = $this->customerIds[array_rand($this->customerIds)];
            
            // Data de criação do pedido (últimos 6 meses)
            $createdDate = $this->randomDateInRange('-6 months', 'now');
            
            // Valor do pedido (R$ 5.000 a R$ 500.000)
            $value = rand(5000, 500000);
            
            // Status inicial sempre é "Recebido"
            $initialStatus = C_PedidosStatusRecebido;
            
            // Data de previsão de entrega (15 a 60 dias após criação)
            $estimateToReady = date('Y-m-d', strtotime($createdDate . ' +' . rand(15, 60) . ' days'));
            
            $orderData = [
                'number' => $orderNumber++,
                'id_customer' => $customerId,
                'value' => $value,
                'status' => $initialStatus,
                'type' => 1,
                'role' => 1,
                'estimatetoready' => $estimateToReady,
                'created' => $createdDate,
                'modified' => $createdDate
            ];
            
            $this->connection->insert('orders', $orderData);
            $orderId = $this->connection->lastInsertId();
            $this->orderIds[] = $orderId;
            
            // Criar histórico de status para este pedido usando usuários disponíveis
            $this->createOrderHistory($orderId, $createdDate, $value, $allUserIds);
            
            // Progress indicator
            if (($i + 1) % 10 == 0) {
                echo "Processados " . ($i + 1) . " pedidos...\n";
            }
        }
        
        echo "Novos pedidos criados: " . count($this->orderIds) . "\n";
    }

    private function createOrderHistory($orderId, $orderCreatedDate, $orderValue, $allUserIds)
    {
        $userId = $allUserIds[0]; // Usar o primeiro usuário disponível
        $currentStatus = C_PedidosStatusRecebido;
        $currentDate = $orderCreatedDate;
        
        // Criar log inicial (pedido recebido)
        $this->createStatusLog($orderId, $userId, null, $currentStatus, $currentDate, 
            "Pedido recebido no sistema. Valor: R$ " . number_format($orderValue, 2, ',', '.'));
        
        // Definir probabilidade de progressão do status
        $progressionChance = rand(1, 100);
        
        if ($progressionChance > 20) { // 80% chance de ir para "Em Projeto"
            $currentDate = $this->addRandomDays($currentDate, 1, 5);
            $currentStatus = C_PedidosStatusEmProjeto;
            
            $this->createStatusLog($orderId, $userId, C_PedidosStatusRecebido, $currentStatus, $currentDate,
                "Projeto iniciado. Análise técnica em andamento.");
            
            // Atualizar status do pedido
            $this->connection->update('orders', ['status' => $currentStatus, 'modified' => $currentDate], ['id' => $orderId]);
        }
        
        if ($progressionChance > 40) { // 60% chance de ir para "Em Fabricação"
            $currentDate = $this->addRandomDays($currentDate, 3, 10);
            $currentStatus = C_PedidosStatusEmFabricação;
            
            $this->createStatusLog($orderId, $userId, C_PedidosStatusEmProjeto, $currentStatus, $currentDate,
                "Projeto aprovado. Iniciando processo de fabricação.");
            
            $this->connection->update('orders', ['status' => $currentStatus, 'modified' => $currentDate], ['id' => $orderId]);
        }
        
        if ($progressionChance > 60) { // 40% chance de ir para "Em Transporte"
            $currentDate = $this->addRandomDays($currentDate, 7, 21);
            $currentStatus = C_PedidosStatusEmTransporte;
            
            $this->createStatusLog($orderId, $userId, C_PedidosStatusEmFabricação, $currentStatus, $currentDate,
                "Fabricação concluída. Produto em transporte para entrega.");
            
            $this->connection->update('orders', ['status' => $currentStatus, 'modified' => $currentDate], ['id' => $orderId]);
        }
        
        if ($progressionChance > 80) { // 20% chance de ser entregue
            $currentDate = $this->addRandomDays($currentDate, 1, 3);
            $currentStatus = C_PedidosStatusEntregue;
            
            $this->createStatusLog($orderId, $userId, C_PedidosStatusEmTransporte, $currentStatus, $currentDate,
                "Produto entregue com sucesso. Pedido finalizado.");
            
            $this->connection->update('orders', ['status' => $currentStatus, 'modified' => $currentDate], ['id' => $orderId]);
        }
        
        // Adicionar alguns comentários gerais aleatórios
        $this->addRandomComments($orderId, $orderCreatedDate, $allUserIds);
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
        
        // Criar comentário associado ao log
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

    private function addRandomComments($orderId, $baseDate, $allUserIds)
    {
        $commentChance = rand(1, 100);
        
        if ($commentChance > 70) { // 30% chance de ter comentários gerais
            $numComments = rand(1, 2); // Reduzido para não sobrecarregar
            
            $generalComments = [
                "Cliente solicitou informações adicionais sobre o projeto.",
                "Verificação de especificações técnicas realizada.",
                "Cliente confirmou aprovação do orçamento.",
                "Ajustes solicitados pelo cliente foram implementados.",
                "Reunião de alinhamento realizada com sucesso.",
                "Documentação técnica enviada para análise.",
                "Cliente solicitou alteração no prazo de entrega.",
                "Confirmação de disponibilidade de material.",
                "Análise de viabilidade técnica concluída.",
                "Cliente aprovou protótipo apresentado."
            ];
            
            for ($i = 0; $i < $numComments; $i++) {
                $commentDate = $this->addRandomDays($baseDate, rand(1, 30), rand(35, 60));
                $message = $generalComments[array_rand($generalComments)];
                $userId = $allUserIds[array_rand($allUserIds)]; // Usar usuário aleatório
                
                $commentData = [
                    'id_order' => $orderId,
                    'id_user' => $userId,
                    'message' => $message,
                    'refcomment' => null, // Comentário geral
                    'created' => $commentDate,
                    'modified' => $commentDate
                ];
                
                $this->connection->insert('comments', $commentData);
            }
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
        echo "\n--- RESUMO DOS DADOS GERADOS ---\n";
        echo "Usuários: " . count($this->userIds) . "\n";
        echo "Clientes: " . count($this->customerIds) . "\n";
        echo "Pedidos: " . count($this->orderIds) . "\n";
        
        // Contar logs e comentários
        $logsCount = $this->connection->execute('SELECT COUNT(*) as count FROM orders_log')->fetch()['count'];
        $commentsCount = $this->connection->execute('SELECT COUNT(*) as count FROM comments')->fetch()['count'];
        
        echo "Logs de status: " . $logsCount . "\n";
        echo "Comentários: " . $commentsCount . "\n";
        
        // Mostrar distribuição por status
        echo "\n--- DISTRIBUIÇÃO POR STATUS ---\n";
        $statusCounts = $this->connection->execute('
            SELECT status, COUNT(*) as count 
            FROM orders 
            GROUP BY status 
            ORDER BY status
        ')->fetchAll();
        
        foreach ($statusCounts as $statusCount) {
            $statusName = $this->getStatusName($statusCount['status']);
            echo "Status {$statusCount['status']} ({$statusName}): {$statusCount['count']} pedidos\n";
        }
        
        echo "\n--- CREDENCIAIS DE ACESSO ---\n";
        echo "Admin: admin@cglconecta.com / admin123\n";
        echo "Cliente 1: joao.silva@empresa1.com / cliente123\n";
        echo "Cliente 2: maria.costa@empresa2.com / cliente123\n";
        
        echo "\nDados gerados com sucesso! O sistema agora possui dados realistas para testes.\n";
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

// Executar o seeder
try {
    $seeder = new TestDataSeeder($connection);
    $seeder->run();
} catch (Exception $e) {
    echo "Erro durante a execução: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
