<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Files Controller
 *
 * @method \App\Model\Entity\File[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class FilesController extends AppController
{
    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        
        // Configurar respostas JSON para métodos AJAX
        if ($this->request->is('ajax')) {
            $this->viewBuilder()->setClassName('Json');
        }
    }

    /**
     * Before filter callback.
     *
     * @param \Cake\Event\EventInterface $event The beforeFilter event.
     * @return \Cake\Http\Response|null
     */
    public function beforeFilter(\Cake\Event\EventInterface $event): ?\Cake\Http\Response
    {
        parent::beforeFilter($event);
        
        // Verificar autenticação para métodos de arquivo
        $ajaxMethods = ['checkCategory', 'listCategory'];
        $currentAction = $this->request->getParam('action');
        
        if (in_array($currentAction, $ajaxMethods)) {
            $user = $this->Authentication->getIdentity();
            if (!$user) {
                if ($this->request->is('ajax')) {
                    $this->viewBuilder()->setClassName('Json');
                    $this->set([
                        'success' => false,
                        'message' => 'Usuário não autenticado',
                        '_serialize' => ['success', 'message']
                    ]);
                    
                    // Retornar resposta JSON em vez de redirecionar
                    return $this->response
                        ->withType('application/json')
                        ->withStatus(401)
                        ->withStringBody(json_encode([
                            'success' => false,
                            'message' => 'Usuário não autenticado'
                        ]));
                }
            }
        }
        
        return null;
    }
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $files = $this->paginate($this->Files);

        $this->set(compact('files'));
    }

    /**
     * View method
     *
     * @param string|null $id File id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $file = $this->Files->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('file'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $file = $this->Files->newEmptyEntity();
        if ($this->request->is('post')) {
            $file = $this->Files->patchEntity($file, $this->request->getData());
            if ($this->Files->save($file)) {
                $this->Flash->success(__('The file has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The file could not be saved. Please, try again.'));
        }
        $this->set(compact('file'));
    }

    /**
     * Edit method
     *
     * @param string|null $id File id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $file = $this->Files->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $file = $this->Files->patchEntity($file, $this->request->getData());
            if ($this->Files->save($file)) {
                $this->Flash->success(__('The file has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The file could not be saved. Please, try again.'));
        }
        $this->set(compact('file'));
    }

    /**
     * Delete method
     *
     * @param string|null $id File id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $file = $this->Files->get($id);
        if ($this->Files->delete($file)) {
            $this->Flash->success(__('The file has been deleted.'));
        } else {
            $this->Flash->error(__('The file could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Método de teste para verificar se o controller está funcionando
     *
     * @return \Cake\Http\Response|null|void JSON response
     */
    public function test()
    {
        $this->viewBuilder()->setClassName('Json');
        
        $this->set([
            'success' => true,
            'message' => 'FilesController funcionando',
            'timestamp' => date('Y-m-d H:i:s'),
            '_serialize' => ['success', 'message', 'timestamp']
        ]);
    }

    /**
     * Verifica arquivos disponíveis em uma categoria específica
     *
     * @return \Cake\Http\Response|null|void JSON response
     */
    public function checkCategory()
    {
        // Força configuração JSON primeiro
        $this->viewBuilder()->setClassName('Json');
        $this->response = $this->response->withType('application/json');
        
        $this->request->allowMethod(['post']);
        
        if (!$this->request->is('ajax')) {
            $this->set([
                'success' => false,
                'message' => 'Requisição deve ser AJAX',
                '_serialize' => ['success', 'message']
            ]);
            return;
        }

        $orderId = $this->request->getData('order_id');
        $category = $this->request->getData('category');
        $folder = $this->request->getData('folder');

        if (!$orderId || !$category || !$folder) {
            $this->set([
                'success' => false,
                'message' => 'Parâmetros inválidos: order_id, category e folder são obrigatórios',
                '_serialize' => ['success', 'message']
            ]);
            return;
        }

        try {
            // Obter caminho base das configurações
            $basePath = \Cake\Core\Configure::read('Files.basePath') ?? ROOT . DS . 'files';
            $categoryPath = $basePath . DS . $folder . '-' . $orderId;

            if (!is_dir($categoryPath)) {
                $this->set([
                    'success' => true,
                    'data' => [
                        'count' => 0,
                        'totalSize' => 0
                    ],
                    '_serialize' => ['success', 'data']
                ]);
                return;
            }

            $files = scandir($categoryPath);
            $fileCount = 0;
            $totalSize = 0;

            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && is_file($categoryPath . DS . $file)) {
                    $fileCount++;
                    $totalSize += filesize($categoryPath . DS . $file);
                }
            }

            $this->set([
                'success' => true,
                'data' => [
                    'count' => $fileCount,
                    'totalSize' => $totalSize
                ],
                '_serialize' => ['success', 'data']
            ]);

        } catch (\Exception $e) {
            $this->set([
                'success' => false,
                'message' => 'Erro ao verificar categoria: ' . $e->getMessage(),
                '_serialize' => ['success', 'message']
            ]);
        }
    }

    /**
     * Lista arquivos de uma categoria específica
     *
     * @return \Cake\Http\Response|null|void JSON response
     */
    public function listCategory()
    {
        // Força configuração JSON primeiro
        $this->viewBuilder()->setClassName('Json');
        $this->response = $this->response->withType('application/json');
        
        $this->request->allowMethod(['post']);
        
        if (!$this->request->is('ajax')) {
            $this->set([
                'success' => false,
                'message' => 'Requisição deve ser AJAX',
                '_serialize' => ['success', 'message']
            ]);
            return;
        }

        $orderId = $this->request->getData('order_id');
        $category = $this->request->getData('category');
        $folder = $this->request->getData('folder');

        if (!$orderId || !$category || !$folder) {
            $this->set([
                'success' => false,
                'message' => 'Parâmetros inválidos: order_id, category e folder são obrigatórios',
                '_serialize' => ['success', 'message']
            ]);
            return;
        }

        try {
            // Obter caminho base das configurações
            $basePath = \Cake\Core\Configure::read('Files.basePath') ?? ROOT . DS . 'files';
            $categoryPath = $basePath . DS . $folder . '-' . $orderId;

            if (!is_dir($categoryPath)) {
                $this->set([
                    'success' => true,
                    'data' => ['files' => []],
                    '_serialize' => ['success', 'data']
                ]);
                return;
            }

            $files = [];
            $fileList = scandir($categoryPath);

            foreach ($fileList as $file) {
                if ($file !== '.' && $file !== '..' && is_file($categoryPath . DS . $file)) {
                    $filePath = $categoryPath . DS . $file;
                    $files[] = [
                        'name' => $file,
                        'size' => filesize($filePath),
                        'modified' => date('d/m/Y H:i', filemtime($filePath)),
                        'type' => mime_content_type($filePath)
                    ];
                }
            }

            // Ordenar por nome
            usort($files, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            $this->set([
                'success' => true,
                'data' => ['files' => $files],
                '_serialize' => ['success', 'data']
            ]);

        } catch (\Exception $e) {
            $this->set([
                'success' => false,
                'message' => 'Erro ao listar arquivos: ' . $e->getMessage(),
                '_serialize' => ['success', 'message']
            ]);
        }
    }

    /**
     * Download de um arquivo específico
     *
     * @return \Cake\Http\Response
     */
    public function downloadFile()
    {
        $orderId = $this->request->getQuery('order_id');
        $category = $this->request->getQuery('category');
        $folder = $this->request->getQuery('folder');
        $filename = $this->request->getQuery('filename');

        if (!$orderId || !$category || !$folder || !$filename) {
            throw new \Cake\Http\Exception\BadRequestException('Parâmetros inválidos');
        }

        // Verificar permissões de acesso ao pedido
        // Aqui você pode adicionar lógica adicional de segurança

        try {
            $basePath = \Cake\Core\Configure::read('Files.basePath') ?? ROOT . DS . 'files';
            $filePath = $basePath . DS . $folder . '-' . $orderId . DS . $filename;

            if (!file_exists($filePath) || !is_file($filePath)) {
                throw new \Cake\Http\Exception\NotFoundException('Arquivo não encontrado');
            }

            // Validar que o arquivo está dentro do diretório permitido (segurança)
            $realBasePath = realpath($basePath);
            $realFilePath = realpath($filePath);
            
            if (strpos($realFilePath, $realBasePath) !== 0) {
                throw new \Cake\Http\Exception\ForbiddenException('Acesso negado');
            }

            $response = $this->response->withFile($filePath, [
                'download' => true,
                'name' => $filename
            ]);

            return $response;

        } catch (\Exception $e) {
            throw new \Cake\Http\Exception\InternalErrorException('Erro ao baixar arquivo: ' . $e->getMessage());
        }
    }

    /**
     * Download de todos os arquivos de uma categoria (ZIP)
     *
     * @return \Cake\Http\Response
     */
    public function downloadCategory()
    {
        $orderId = $this->request->getQuery('order_id');
        $category = $this->request->getQuery('category');
        $folder = $this->request->getQuery('folder');

        if (!$orderId || !$category || !$folder) {
            throw new \Cake\Http\Exception\BadRequestException('Parâmetros inválidos');
        }

        try {
            $basePath = \Cake\Core\Configure::read('Files.basePath') ?? ROOT . DS . 'files';
            $categoryPath = $basePath . DS . $folder . '-' . $orderId;

            if (!is_dir($categoryPath)) {
                throw new \Cake\Http\Exception\NotFoundException('Categoria não encontrada');
            }

            // Criar arquivo ZIP temporário
            $tempPath = TMP . 'downloads' . DS;
            if (!is_dir($tempPath)) {
                mkdir($tempPath, 0755, true);
            }

            $zipFilename = $folder . '-' . $orderId . '.zip';
            $zipPath = $tempPath . $zipFilename;

            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE) !== TRUE) {
                throw new \Exception('Não foi possível criar o arquivo ZIP');
            }

            $files = scandir($categoryPath);
            $fileCount = 0;

            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && is_file($categoryPath . DS . $file)) {
                    $zip->addFile($categoryPath . DS . $file, $file);
                    $fileCount++;
                }
            }

            $zip->close();

            if ($fileCount === 0) {
                unlink($zipPath);
                throw new \Cake\Http\Exception\NotFoundException('Nenhum arquivo encontrado na categoria');
            }

            $response = $this->response->withFile($zipPath, [
                'download' => true,
                'name' => $zipFilename
            ]);

            // Agendar remoção do arquivo temporário
            register_shutdown_function(function() use ($zipPath) {
                if (file_exists($zipPath)) {
                    unlink($zipPath);
                }
            });

            return $response;

        } catch (\Exception $e) {
            throw new \Cake\Http\Exception\InternalErrorException('Erro ao criar arquivo ZIP: ' . $e->getMessage());
        }
    }

    /**
     * Visualizar um arquivo específico
     *
     * @return \Cake\Http\Response
     */
    public function viewFile()
    {
        $orderId = $this->request->getQuery('order_id');
        $category = $this->request->getQuery('category');
        $folder = $this->request->getQuery('folder');
        $filename = $this->request->getQuery('filename');

        if (!$orderId || !$category || !$folder || !$filename) {
            throw new \Cake\Http\Exception\BadRequestException('Parâmetros inválidos');
        }

        try {
            $basePath = \Cake\Core\Configure::read('Files.basePath') ?? ROOT . DS . 'files';
            $filePath = $basePath . DS . $folder . '-' . $orderId . DS . $filename;

            if (!file_exists($filePath) || !is_file($filePath)) {
                throw new \Cake\Http\Exception\NotFoundException('Arquivo não encontrado');
            }

            // Validar que o arquivo está dentro do diretório permitido (segurança)
            $realBasePath = realpath($basePath);
            $realFilePath = realpath($filePath);
            
            if (strpos($realFilePath, $realBasePath) !== 0) {
                throw new \Cake\Http\Exception\ForbiddenException('Acesso negado');
            }

            $mimeType = mime_content_type($filePath);
            
            $response = $this->response->withFile($filePath, [
                'download' => false,
                'name' => $filename
            ])->withType($mimeType);

            return $response;

        } catch (\Exception $e) {
            throw new \Cake\Http\Exception\InternalErrorException('Erro ao visualizar arquivo: ' . $e->getMessage());
        }
    }
}
