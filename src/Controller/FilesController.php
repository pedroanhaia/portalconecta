<?php
declare(strict_types=1);

namespace App\Controller;

class FilesController extends AppController
{
	private $categories = [
		'dadosgerais' => '0 - DADOS GERAIS',
		'desenhos' => '1 - DESENHOS',
		'art' => '2 - ART',
		'eps' => '3 - EPS',
		'rqs' => '4 - RQS',
		'certmat' => '5 - CERTIFICADOS DOS MATERIAIS',
		'certcons' => '6 - CERTIFICADOS DOS CONSUMÍVEIS',
		'certcalib' => '7 - CERTIFICADOS DE CALIBRAÇÃO',
		'relinsp' => '8 - RELATORIOS DE INSPEÇÃO',
		// 'montdatabook' => 'MONTAGEM DO DATABOOK',
		'testes' => 'TESTES',
		'outros' => 'OUTROS'
	];

	public function initialize(): void
	{
		parent::initialize();
		
		if ($this->request->is('ajax')) {
			$this->viewBuilder()->setClassName('Json');
		}
	}

	public function beforeFilter(\Cake\Event\EventInterface $event): ?\Cake\Http\Response
	{
		parent::beforeFilter($event);
		
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
		
		$this->loadModel('Config');
		$this->loadModel('Orders');

		return null;
	}

	public function scanAndMap($id = null)
	{
		$this->autoRender = false;

		if (!$this->request->is('ajax')) {
			return $this->response->withType('application/json')
				->withStringBody(json_encode(['success' => false, 'message' => 'Requisição não suportada.']));
		}

		$order = $this->Orders->findById($id)
			->contain([
				'Customers' => ['fields' => ['name']],
			])
		->first();

		if (!$order) {
			return $this->response->withType('application/json')
				->withStringBody(json_encode(['success' => false, 'message' => 'Pedido não encontrado.']));
		}

		// Caminho base dos arquivos no servidor
		$baseDir = $this->Config->get(1)->path . DS;

		// Nova estrutura: Nome do cliente - número do pedido
		$clienteNome = preg_replace('/[^a-zA-Z0-9_\s]/', '_', $order->customer->name);
		$pedidoNumero = str_pad($order->number, 3, '0', STR_PAD_LEFT);
		$pastaPedido = "{$clienteNome} - {$pedidoNumero}";
		$caminhoPedido = $baseDir . $pastaPedido . DS;

		if (!is_dir($caminhoPedido)) {
			return $this->response->withType('application/json')
				->withStringBody(json_encode(['success' => false, 'message' => 'Pasta do pedido não encontrada: ' . $pastaPedido]));
		}

		// Função recursiva para vasculhar arquivos
		$arquivos = $this->listarArquivos($caminhoPedido);

		// Exclui os existentes
		foreach($this->Files->findByIdOrder($order->id)->toArray() as $reg) {
			$this->Files->delete($reg);
		}

		// Salvar os arquivos no banco de dados
		foreach ($arquivos as $arquivo) {
			$relativePath = str_replace($baseDir, '', $arquivo);
			$name = basename($arquivo);
			$type = pathinfo($arquivo, PATHINFO_EXTENSION);
			
			// Determinar categoria baseada no caminho da pasta
			$category = $this->determineCategoryFromPath($arquivo, $caminhoPedido);

			$fileEntity = $this->Files->newEntity([
				'name' => $name,
				'path' => $arquivo, // Caminho completo
				'status' => 1,
				'id_order' => $id,
				'type' => $type,
				'category' => $category
			]);

			$this->Files->save($fileEntity);
		}

		return $this->response->withType('application/json')
			->withStringBody(json_encode(['success' => true, 'message' => 'Arquivos mapeados com sucesso.']));
	}

	private function determineCategoryFromPath($filePath, $basePath)
	{
		// Remove o caminho base para analisar apenas a estrutura interna
		$relativePath = str_replace($basePath, '', $filePath);
		$relativePath = str_replace('\\', '/', $relativePath); // Normalizar separadores
		
		// Mapear pastas para categorias
		$folderMapping = [
			'0 - DADOS GERAIS' => 'dadosgerais',
			'1 - DESENHOS' => 'desenhos',
			'2 - ART' => 'art',
			'3 - EPS' => 'eps',
			'4 - RQS' => 'rqs',
			'5 - CERTIFICADOS DOS MATERIAIS' => 'certmat',
			'6 - CERTIFICADOS DOS CONSUMÍVEIS' => 'certcons',
			'7 - CERTIFICADOS DE CALIBRAÇÃO' => 'certcalib',
			'8 - RELATORIOS DE INSPEÇÃO' => 'relinsp',
			// 'MONTAGEM DO DATABOOK' => 'montdatabook',
			'TESTES' => 'testes'
		];

		// Verificar se o arquivo está dentro de alguma pasta conhecida
		foreach ($folderMapping as $folderName => $categoryKey) {
			if (stripos($relativePath, $folderName) !== false) {
				return $categoryKey;
			}
		}

		// Se não encontrou em nenhuma pasta conhecida, categorizar como "outros"
		return 'outros';
	}

	private function listarArquivos($dir)
	{
		$resultados = [];

		if (!is_dir($dir)) {
			return $resultados;
		}

		$it = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
		);

		foreach ($it as $file) {
			if ($file->isFile()) {
				$resultados[] = $file->getPathname();
			}
		}

		return $resultados;
	}

	public function checkCategory()
	{
		$this->viewBuilder()->setClassName('Json');
		$this->response = $this->response->withType('application/json');
		
		if (!$this->request->is('ajax')) {
			return $this->response->withType('application/json')
				->withStringBody(json_encode(['success' => false, 'message' => 'Requisição não suportada.']));
		}

		$orderId = $this->request->getData('order_id');
		$category = $this->request->getData('category');

		if (!$orderId || !$category) {
			$this->set([
				'success' => false,
				'message' => 'Parâmetros inválidos: order_id e category são obrigatórios',
				'_serialize' => ['success', 'message']
			]);
			return;
		}

		try {
			$files = $this->Files->find()
				->where(['id_order' => $orderId, 'category' => $category])
				->all();

			$fileCount = 0;
			$totalSize = 0;

			foreach ($files as $file) {
				$realPath = $file->path;
				if (is_file($realPath)) {
					$fileCount++;
					$totalSize += filesize($realPath);
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
				'message' => 'Erro: ' . $e->getMessage(),
				'_serialize' => ['success', 'message']
			]);
		}
	}

	public function listCategory()
	{
		$this->viewBuilder()->setClassName('Json');
		$this->response = $this->response->withType('application/json');

		if (!$this->request->is('ajax')) {
			return $this->response->withType('application/json')
				->withStringBody(json_encode(['success' => false, 'message' => 'Requisição não suportada.']));
		}

		$orderId = $this->request->getData('order_id');
		$category = $this->request->getData('category');

		if (!$orderId || !$category) {
			$this->set([
				'success' => false,
				'message' => 'Parâmetros inválidos: order_id e category são obrigatórios',
				'_serialize' => ['success', 'message']
			]);
			return;
		}

		try {
			$files = $this->Files->find()
				->where(['id_order' => $orderId, 'category' => $category])
				->all();

			$result = [];

			foreach ($files as $file) {
				$fullPath = $file->path;
				$result[] = [
					'name' => $file->name,
					'size' => (is_file($fullPath) ? filesize($fullPath) : 0),
					'modified' => (is_file($fullPath) ? date('d/m/Y H:i', filemtime($fullPath)) : '-'),
					'type' => (is_file($fullPath) ? mime_content_type($fullPath) : ($file->type ?? 'application/octet-stream'))
				];
			}

			$this->set([
				'success' => true,
				'data' => ['files' => $result],
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

	public function downloadFile()
	{
		$orderId = $this->request->getQuery('order_id');
		$filename = $this->request->getQuery('filename');

		if (!$orderId || !$filename) {
			throw new \Cake\Http\Exception\BadRequestException('Parâmetros inválidos');
		}

		try {
			$file = $this->Files->find()
				->where(['id_order' => $orderId, 'name' => $filename])
				->first();

			if (!$file || !file_exists($file->path) || !is_file($file->path)) {
				throw new \Cake\Http\Exception\NotFoundException('Arquivo não encontrado');
			}

			return $this->response->withFile($file->path, [
				'download' => true,
				'name' => $filename
			])->withType(mime_content_type($file->path));

		} catch (\Exception $e) {
			throw new \Cake\Http\Exception\InternalErrorException('Erro ao baixar arquivo: ' . $e->getMessage());
		}
	}

	public function downloadCategory()
	{
		$orderId = $this->request->getQuery('order_id');
		$category = $this->request->getQuery('category');

		if (!$orderId || !$category) {
			throw new \Cake\Http\Exception\BadRequestException('Parâmetros inválidos');
		}

		$files = $this->Files->find()
			->where(['id_order' => $orderId, 'category' => $category])
			->all();

		$filesZip = [];

		foreach ($files as $file) {
			$filename = $file->name;
			$path = $file->path;

			if (!file_exists($path) || !is_file($path)) {
				continue;
			}
			
			$filesZip[] = ['real' => $path, 'name' => $filename];
		}

		if (empty($filesZip)) {
			throw new \Cake\Http\Exception\NotFoundException('Nenhum arquivo encontrado na categoria.');
		}

		// Criar diretório temporário para ZIP
		$tempPath = TMP . 'downloads' . DS;
		if (!is_dir($tempPath)) {
			mkdir($tempPath, 0755, true);
		}

		$categoryName = $this->categories[$category] ?? $category;
		$zipFilename = 'Arquivos_' . $categoryName . '.zip';
		$zipPath = $tempPath . $zipFilename;

		$zip = new \ZipArchive();
		if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
			throw new \Exception('Erro ao criar o arquivo ZIP');
		}

		foreach ($filesZip as $f) {
			$zip->addFile($f['real'], $f['name']);
		}

		$zip->close();

		// Excluir após envio
		register_shutdown_function(function () use ($zipPath) {
			if (file_exists($zipPath)) {
				unlink($zipPath);
			}
		});

		return $this->response->withFile($zipPath, [
			'download' => true,
			'name' => $zipFilename
		]);
	}

	public function viewFile()
	{
		$orderId = $this->request->getQuery('order_id');
		$filename = $this->request->getQuery('filename');

		if (!$orderId || !$filename) {
			throw new \Cake\Http\Exception\BadRequestException('Parâmetros inválidos');
		}

		try {
			$file = $this->Files->find()
				->where(['id_order' => $orderId, 'name' => $filename])
				->first();

			if (!$file || !file_exists($file->path) || !is_file($file->path)) {
				throw new \Cake\Http\Exception\NotFoundException('Arquivo não encontrado');
			}

			return $this->response->withFile($file->path, [
				'download' => false,
				'name' => $filename
			])->withType(mime_content_type($file->path));

		} catch (\Exception $e) {
			throw new \Cake\Http\Exception\InternalErrorException('Erro ao visualizar arquivo: ' . $e->getMessage());
		}
	}
}
