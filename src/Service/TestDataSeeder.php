<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Datasource\ConnectionManager;

class TestDataSeeder
{
    private $connection;
    private $customerIds = [];
    private $userIds = [];
    private $orderIds = [];

    public function __construct()
    {
        $this->connection = ConnectionManager::get('default');
    }

    public function generateTestData(bool $preserveData = true, int $totalOrders = 50): array
    {
        try {
            if (!$preserveData) {
                $this->clearExistingData();
            }

            $this->seedUsers($preserveData);
            
            // Verificar se usuários foram criados
            if (empty($this->userIds)) {
                throw new \Exception('Falha ao criar usuários - nenhum usuário disponível para continuar!');
            }
            
            $this->seedCustomers($preserveData);
            $this->seedOrders($totalOrders);

            return [
                'success' => true,
                'message' => 'Dados gerados com sucesso!',
                'stats' => $this->getStats()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage(),
                'stats' => []
            ];
        }
    }

    private function clearExistingData(): void
    {
        $this->connection->execute('SET FOREIGN_KEY_CHECKS = 0');
        $this->connection->execute('TRUNCATE TABLE comments');
        $this->connection->execute('TRUNCATE TABLE orders_log');
        $this->connection->execute('TRUNCATE TABLE orders');
        $this->connection->execute('TRUNCATE TABLE customers');
        $this->connection->execute('DELETE FROM users WHERE id > 1');
        $this->connection->execute('SET FOREIGN_KEY_CHECKS = 1');
    }

    private function seedUsers(bool $preserveData): void
    {
        // Verificar se já existe admin
        $existingAdmin = $this->connection->execute('SELECT id FROM users WHERE email = ? LIMIT 1', ['admin@cglconecta.com'])->fetch();

        if (!$existingAdmin || !isset($existingAdmin['id'])) {
            $adminData = [
                'name' => 'Administrador Sistema',
                'email' => 'admin@cglconecta.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'admin' => 1,
                'id_customer' => null,
                'type' => 2, // C_UsersTypeAdmin
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ];

            $statement = $this->connection->insert('users', $adminData);
            $this->userIds[] = $statement->lastInsertId();
        } else {
            $this->userIds[] = $existingAdmin['id'];
        }

        // Usuários clientes
        $clientUsers = [
            [
                'name' => 'João Silva Santos',
                'email' => 'joao.silva@empresa1.com',
                'password' => password_hash('cliente123', PASSWORD_DEFAULT),
                'admin' => 0,
                'type' => 1 // C_UsersTypeCliente
            ],
            [
                'name' => 'Maria Oliveira Costa',
                'email' => 'maria.costa@empresa2.com',
                'password' => password_hash('cliente123', PASSWORD_DEFAULT),
                'admin' => 0,
                'type' => 1 // C_UsersTypeCliente
            ]
        ];

        foreach ($clientUsers as $userData) {
            if ($preserveData) {
                $existingUser = $this->connection->execute('SELECT id FROM users WHERE email = ? LIMIT 1', [$userData['email']])->fetch();
                if ($existingUser && isset($existingUser['id'])) {
                    $this->userIds[] = $existingUser['id'];
                    continue;
                }
            }

            $userData['created'] = date('Y-m-d H:i:s');
            $userData['modified'] = date('Y-m-d H:i:s');
            $userData['id_customer'] = null;

            $statement = $this->connection->insert('users', $userData);
            $this->userIds[] = $statement->lastInsertId();
        }
    }

    private function seedCustomers(bool $preserveData): void
    {
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

        $clientUserIndex = 1;

        foreach ($companies as $i => $company) {
            if ($preserveData) {
                $existingCustomer = $this->connection->execute('SELECT id FROM customers WHERE cnpj = ? LIMIT 1', [$company['cnpj']])->fetch();
                if ($existingCustomer && isset($existingCustomer['id'])) {
                    $this->customerIds[] = $existingCustomer['id'];
                    continue;
                }
            }

            $statement = $this->connection->insert('customers', $company);
            $customerId = $statement->lastInsertId();
            $this->customerIds[] = $customerId;

            // Associar usuários aos primeiros 2 clientes
            if ($i < 2 && isset($this->userIds[$clientUserIndex])) {
                $userId = $this->userIds[$clientUserIndex];
                
                $userCustomer = $this->connection->execute('SELECT id_customer FROM users WHERE id = ?', [$userId])->fetch();
                if ($userCustomer && isset($userCustomer['id_customer']) && !$userCustomer['id_customer']) {
                    $this->connection->update('users', 
                        ['id_customer' => $customerId], 
                        ['id' => $userId]
                    );
                }
                $clientUserIndex++;
            }
        }

        // Buscar todos os clientes existentes
        if ($preserveData) {
            $allCustomers = $this->connection->execute('SELECT id FROM customers')->fetchAll('assoc');
            $this->customerIds = array_merge($this->customerIds, array_column($allCustomers, 'id'));
            $this->customerIds = array_unique($this->customerIds); // Remove duplicatas
        }
    }

    private function seedOrders(int $totalOrders): void
    {
        // Buscar o maior número de pedido existente
        $maxOrderNumber = $this->connection->execute('SELECT MAX(number) as max_number FROM orders')->fetch();
        $orderNumber = ($maxOrderNumber && isset($maxOrderNumber['max_number']) && $maxOrderNumber['max_number']) 
            ? $maxOrderNumber['max_number'] + 1 
            : 2025001;

        // Buscar todos os usuários disponíveis
        if (empty($this->userIds)) {
            throw new \Exception('Nenhum usuário foi criado/encontrado para criar os logs!');
        }

        // Usar os usuários que já temos na classe
        $allUserIds = $this->userIds;

        for ($i = 0; $i < $totalOrders; $i++) {
            $customerId = $this->customerIds[array_rand($this->customerIds)];
            
            // Buscar o nome do cliente
            $customerData = $this->connection->execute('SELECT name FROM customers WHERE id = ?', [$customerId])->fetch();
            $customerName = $customerData && isset($customerData['name']) ? $customerData['name'] : 'CLIENTE DESCONHECIDO';
            
            $createdDate = $this->randomDateInRange('-6 months', 'now');
            $value = rand(5000, 500000);
            $estimateToReady = date('Y-m-d', strtotime($createdDate . ' +' . rand(15, 60) . ' days'));

            $orderData = [
                'number' => $orderNumber++,
                'id_customer' => $customerId,
                'value' => $value,
                'status' => 1, // Recebido
                'type' => 1,
                'role' => 1,
                'estimatetoready' => $estimateToReady,
                'created' => $createdDate,
                'modified' => $createdDate
            ];

            $statement = $this->connection->insert('orders', $orderData);
            $orderId = $statement->lastInsertId();
            $this->orderIds[] = $orderId;

            // Criar pasta de arquivos para o pedido com nome do cliente
            $this->createOrderDirectoryWithCustomer($orderNumber - 1, $customerName, $orderId);

            $this->createOrderHistory($orderId, $createdDate, $value, $allUserIds);
        }
    }

    private function createOrderHistory( $orderId, string $orderCreatedDate, int $orderValue, array $allUserIds): void
    {
        $userId = $allUserIds[0];
        $currentStatus = 1; // Recebido
        $currentDate = $orderCreatedDate;

        // Log inicial
        $this->createStatusLog($orderId, $userId, 0, $currentStatus, $currentDate, 
            "Pedido recebido no sistema. Valor: R$ " . number_format($orderValue, 2, ',', '.'));

        $progressionChance = rand(1, 100);

        // Evolução do status
        if ($progressionChance > 20) { // 80% chance de ir para "Em Projeto"
            $currentDate = $this->addRandomDays($currentDate, 1, 5);
            $currentStatus = 2; // Em Projeto
            $this->createStatusLog($orderId, $userId, 1, $currentStatus, $currentDate,
                "Projeto iniciado. Análise técnica em andamento.");
            $this->connection->update('orders', ['status' => $currentStatus, 'modified' => $currentDate], ['id' => $orderId]);
        }

        if ($progressionChance > 40) { // 60% chance de ir para "Em Fabricação"
            $currentDate = $this->addRandomDays($currentDate, 3, 10);
            $currentStatus = 3; // Em Fabricação
            $this->createStatusLog($orderId, $userId, 2, $currentStatus, $currentDate,
                "Projeto aprovado. Iniciando processo de fabricação.");
            $this->connection->update('orders', ['status' => $currentStatus, 'modified' => $currentDate], ['id' => $orderId]);
        }

        if ($progressionChance > 60) { // 40% chance de ir para "Em Transporte"
            $currentDate = $this->addRandomDays($currentDate, 7, 21);
            $currentStatus = 4; // Em Transporte
            $this->createStatusLog($orderId, $userId, 3, $currentStatus, $currentDate,
                "Fabricação concluída. Produto em transporte para entrega.");
            $this->connection->update('orders', ['status' => $currentStatus, 'modified' => $currentDate], ['id' => $orderId]);
        }

        if ($progressionChance > 80) { // 20% chance de ser entregue
            $currentDate = $this->addRandomDays($currentDate, 1, 3);
            $currentStatus = 5; // Entregue
            $this->createStatusLog($orderId, $userId, 4, $currentStatus, $currentDate,
                "Produto entregue com sucesso. Pedido finalizado.");
            $this->connection->update('orders', ['status' => $currentStatus, 'modified' => $currentDate], ['id' => $orderId]);
        }

        // Comentários gerais aleatórios
        if (rand(1, 100) > 70) {
            $this->addRandomComments($orderId, $orderCreatedDate, $allUserIds);
        }
    }

    private function createStatusLog($orderId, $userId, ?int $oldStatus, int $newStatus, string $date, string $message): void
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

        $statement = $this->connection->insert('orders_log', $logData);
        $logId = $statement->lastInsertId();

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

    private function addRandomComments($orderId, string $baseDate, array $allUserIds): void
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
            $userId = $allUserIds[array_rand($allUserIds)];

            $commentData = [
                'id_order' => $orderId,
                'id_user' => $userId,
                'message' => $message,
                'refcomment' => null,
                'created' => $commentDate,
                'modified' => $commentDate
            ];

            $this->connection->insert('comments', $commentData);
        }
    }

    private function randomDateInRange(string $start, string $end): string
    {
        $startTime = strtotime($start);
        $endTime = strtotime($end);
        $randomTime = rand($startTime, $endTime);
        return date('Y-m-d H:i:s', $randomTime);
    }

    private function addRandomDays(string $date, int $minDays, int $maxDays): string
    {
        $days = rand($minDays, $maxDays);
        return date('Y-m-d H:i:s', strtotime($date . " +{$days} days"));
    }

    private function getStats(): array
    {
        return [
            'users_created' => count($this->userIds),
            'customers_created' => count($this->customerIds),
            'orders_created' => count($this->orderIds),
            'directories_created' => count($this->orderIds) // Uma pasta por pedido
        ];
    }

    public function createMissingOrderDirectories(): array
    {
        try {
            $orders = $this->connection->execute('
                SELECT o.id, o.number, COALESCE(c.name, "CLIENTE DESCONHECIDO") as customer_name 
                FROM orders o 
                LEFT JOIN customers c ON o.id_customer = c.id 
                ORDER BY o.number
            ')->fetchAll('assoc');
            $createdCount = 0;
            $skippedCount = 0;
            $errorCount = 0;

            foreach ($orders as $order) {
                $orderNumber = $order['number'];
                $customerName = $order['customer_name'];
                
                // Se não encontrou cliente, tentar buscar novamente ou usar um nome padrão com o número do pedido
                if ($customerName === 'CLIENTE DESCONHECIDO') {
                    $customerName = "PEDIDO {$orderNumber}";
                }
                
                // Limpar caracteres especiais do nome do cliente que podem causar problemas no nome da pasta
                $customerName = preg_replace('/[<>:"/\\|?*]/', '', $customerName);
                $customerName = trim($customerName);
                
                $targetDir = "C:\\cglarquivos\\{$customerName} - {$orderNumber}";

                // Verificar se a pasta já existe
                if (is_dir($targetDir)) {
                    $skippedCount++;
                    continue;
                }

                try {
                    $this->createOrderDirectoryWithCustomer($orderNumber, $customerName, $order['id']);
                    $createdCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    error_log("Erro ao criar pasta para pedido {$orderNumber}: " . $e->getMessage());
                }
            }

            return [
                'success' => true,
                'message' => "Processo concluído!",
                'stats' => [
                    'total_orders' => count($orders),
                    'directories_created' => $createdCount,
                    'directories_skipped' => $skippedCount,
                    'errors' => $errorCount
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage(),
                'stats' => []
            ];
        }
    }

    private function createOrderDirectoryWithCustomer(int $orderNumber, string $customerName, $orderId): void
    {
        try {
            $sourceDir = 'C:\cglarquivos\EMPRESA TESTE - 006';
            $targetDir = "C:\cglarquivos\\{$customerName} - {$orderNumber}";

            // Verificar se a pasta de origem existe
            if (!is_dir($sourceDir)) {
                // Se não existir, criar uma pasta básica
                if (!is_dir('C:\cglarquivos')) {
                    mkdir('C:\cglarquivos', 0755, true);
                }
                mkdir($targetDir, 0755, true);
                return;
            }

            // Copiar recursivamente a pasta de origem para a de destino
            $this->copyDirectory($sourceDir, $targetDir);

        } catch (\Exception $e) {
            // Re-lançar a exceção para que seja capturada no método chamador
            throw new \Exception("Erro ao criar pasta para pedido {$orderNumber}: " . $e->getMessage());
        }
    }

    private function createOrderDirectory(int $orderNumber, int $orderId): void
    {
        try {
            $sourceDir = 'C:\cglarquivos\EMPRESA TESTE - 006';
            $targetDir = "C:\cglarquivos\EMPRESA TESTE - {$orderNumber}";

            // Verificar se a pasta de origem existe
            if (!is_dir($sourceDir)) {
                // Se não existir, criar uma pasta básica
                if (!is_dir('C:\cglarquivos')) {
                    mkdir('C:\cglarquivos', 0755, true);
                }
                mkdir($targetDir, 0755, true);
                return;
            }

            // Copiar recursivamente a pasta de origem para a de destino
            $this->copyDirectory($sourceDir, $targetDir);

        } catch (\Exception $e) {
            // Log do erro mas não interrompe a criação dos dados
            error_log("Erro ao criar pasta para pedido {$orderNumber}: " . $e->getMessage());
        }
    }

    private function copyDirectory(string $source, string $destination): void
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $targetPath = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            
            if ($item->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
            } else {
                copy($item->getPathname(), $targetPath);
            }
        }
    }
}
