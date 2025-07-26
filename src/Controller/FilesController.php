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
		$this->loadModel('FilesLog');

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

	/**
	 * Lista arquivos com controle de timeout
	 */
	private function listarArquivosComTimeout($dir, $startTime, $maxTime)
	{
		$resultados = [];

		if (!is_dir($dir)) {
			return $resultados;
		}

		try {
			$it = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
			);

			foreach ($it as $file) {
				// Verificar timeout a cada 100 arquivos
				if (count($resultados) % 100 === 0 && (time() - $startTime) > $maxTime) {
					error_log("Timeout detectado durante listagem de arquivos após " . count($resultados) . " arquivos");
					break;
				}

				if ($file->isFile()) {
					$resultados[] = $file->getPathname();
				}
			}
		} catch (\Exception $e) {
			error_log("Erro durante listagem de arquivos: " . $e->getMessage());
		}

		return $resultados;
	}

	/**
	 * Busca ativa no diretório físico quando não encontra arquivos no banco
	 */
	private function activeScanForOrder($orderId)
	{
		try {
			// Aumentar limite de tempo temporariamente para este processo
			$originalTimeLimit = ini_get('max_execution_time');
			set_time_limit(300); // 5 minutos para o scan

			// Buscar dados do pedido
			$order = $this->Orders->findById($orderId)
				->contain(['Customers' => ['fields' => ['name']]])
				->first();

			if (!$order) {
				set_time_limit(intval($originalTimeLimit));
				return ['found' => false, 'message' => 'Pedido não encontrado'];
			}

			// Caminho base dos arquivos no servidor
			$baseDir = $this->Config->get(1)->path . DS;

			if (!is_dir($baseDir)) {
				set_time_limit(intval($originalTimeLimit));
				return ['found' => false, 'message' => 'Diretório base não encontrado'];
			}

			// Buscar pasta que contenha o número do pedido
			$pedidoNumero = str_pad($order->number, 3, '0', STR_PAD_LEFT);
			$pastEncontrada = null;

			// Listar todas as pastas no diretório base
			$pastas = glob($baseDir . '*', GLOB_ONLYDIR);
			
			foreach ($pastas as $pasta) {
				$nomePasta = basename($pasta);
				
				// Verificar se a pasta segue o padrão "nome cliente - numero pedido"
				$partes = explode(' - ', $nomePasta);
				if (count($partes) >= 2) {
					$ultimaParte = end($partes);
					// Remover zeros à esquerda para comparação
					$numeroEncontrado = ltrim($ultimaParte, '0');
					$numeroPedido = ltrim($pedidoNumero, '0');
					
					if ($numeroEncontrado == $numeroPedido) {
						$pastEncontrada = $pasta;
						break;
					}
				}
			}

			if (!$pastEncontrada) {
				set_time_limit(intval($originalTimeLimit));
				return ['found' => false, 'message' => 'Pasta do pedido não encontrada no diretório'];
			}

			// Executar o scan da pasta encontrada
			$caminhoPedido = $pastEncontrada . DS;
			$startTime = time();
			$maxScanTime = 240; // 4 minutos máximo para o scan
			
			$arquivos = $this->listarArquivosComTimeout($caminhoPedido, $startTime, $maxScanTime);

			if (empty($arquivos)) {
				set_time_limit(intval($originalTimeLimit));
				return ['found' => false, 'message' => 'Nenhum arquivo encontrado na pasta do pedido'];
			}

			// Verificar se ainda temos tempo
			if ((time() - $startTime) > $maxScanTime) {
				set_time_limit(intval($originalTimeLimit));
				return ['found' => false, 'message' => 'Timeout: Muitos arquivos para processar. Tente novamente.'];
			}

			// Remover registros existentes do pedido de forma otimizada
			$this->Files->deleteAll(['id_order' => $orderId]);

			// Preparar dados para inserção em lote
			$arquivosParaInserir = [];
			$batchSize = 50; // Processar 50 arquivos por vez
			$arquivosSalvos = 0;

			foreach ($arquivos as $indice => $arquivo) {
				$name = basename($arquivo);
				$type = pathinfo($arquivo, PATHINFO_EXTENSION);
				
				// Determinar categoria baseada no caminho da pasta
				$category = $this->determineCategoryFromPath($arquivo, $caminhoPedido);

				$arquivosParaInserir[] = [
					'name' => $name,
					'path' => $arquivo, // Caminho completo
					'status' => 1,
					'id_order' => $orderId,
					'type' => $type,
					'category' => $category,
					'created' => date('Y-m-d H:i:s'),
					'modified' => date('Y-m-d H:i:s')
				];

				// Inserir em lotes para evitar timeout
				if (count($arquivosParaInserir) >= $batchSize || $indice === count($arquivos) - 1) {
					try {
						$entities = $this->Files->newEntities($arquivosParaInserir);
						$result = $this->Files->saveMany($entities);
						
						if ($result) {
							$arquivosSalvos += count($arquivosParaInserir);
						}
					} catch (\Exception $e) {
						// Log do erro mas continue o processo
						error_log("Erro ao salvar lote de arquivos: " . $e->getMessage());
					}
					
					$arquivosParaInserir = []; // Limpar array para próximo lote
				}
			}

			// Restaurar limite de tempo original
			set_time_limit(intval($originalTimeLimit));

			return [
				'found' => true, 
				'message' => "Busca ativa realizada! {$arquivosSalvos} arquivos encontrados e indexados."
			];

		} catch (\Exception $e) {
			// Restaurar limite de tempo em caso de erro
			if (isset($originalTimeLimit)) {
				set_time_limit(intval($originalTimeLimit));
			}
			
			return ['found' => false, 'message' => 'Erro na busca ativa: ' . $e->getMessage()];
		}
	}

	public function checkCategory()
	{
		$this->viewBuilder()->setClassName('Json');
		$this->response = $this->response->withType('application/json');
		
		// if (!$this->request->is('ajax')) {
		// 	return $this->response->withType('application/json')
		// 		->withStringBody(json_encode(['success' => false, 'message' => 'Requisição não suportada.']));
		// }

		$orderId = $this->request->getData('order_id');
		$category = $this->request->getData('category');

		$orderId = 139;
		$category = 'art';

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

			// Se não encontrou arquivos no banco, tentar busca ativa no diretório
			if ($fileCount == 0) {
				$scanResult = $this->activeScanForOrder($orderId);
				if ($scanResult['found']) {
					// Recarregar arquivos após a busca ativa
					$files = $this->Files->find()
						->where(['id_order' => $orderId, 'category' => $category])
						->all();

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
							'totalSize' => $totalSize,
							'scanPerformed' => true,
							'message' => $scanResult['message']
						],
						'_serialize' => ['success', 'data']
					]);
					return;
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

			if(!$this->Authentication->getIdentity()->admin) {
				$userName = $this->Authentication->getIdentity()->name ?? 'Usuário Desconhecido';

				$fileLog = $this->FilesLog->newEntity([
					'id_file' => $file->id,
					'id_order' => $orderId,
					'message' => "Usuário {$userName} fez download do arquivo: {$filename}",
				]);

				$this->FilesLog->save($fileLog);
			}


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
		$userName = $this->Authentication->getIdentity()->name ?? 'Usuário Desconhecido';

		foreach ($files as $file) {
			$filename = $file->name;
			$path = $file->path;

			if (!file_exists($path) || !is_file($path)) {
				continue;
			}
			
			$filesZip[] = ['real' => $path, 'name' => $filename];

			if(!$this->Authentication->getIdentity()->admin) {
				$fileLog = $this->FilesLog->newEntity([
					'id_file' => $file->id,
					'id_order' => $orderId,
					'message' => "Usuário {$userName} fez download do arquivo: {$file->name}, fazendo download de todos os arquivos da pasta.",
				]);
				$this->FilesLog->save($fileLog);
			}
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

			if(!$this->Authentication->getIdentity()->admin) {
				$userName = $this->Authentication->getIdentity()->name ?? 'Usuário Desconhecido';

				$fileLog = $this->FilesLog->newEntity([
					'id_file' => $file->id,
					'id_order' => $orderId,
					'message' => "Usuário {$userName} visualizou o arquivo: {$filename}",
				]);

				$this->FilesLog->save($fileLog);
			}


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
