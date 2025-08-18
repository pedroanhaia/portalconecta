<?php
declare(strict_types=1);

namespace App\Controller;

class FilesController extends AppController
{
	private $categories = [
		'pedido' => '0 - PEDIDO',
		'desenhos' => '1 - DESENHOS',
		'art' => '2 - CRONOGRAMA',
		'databook' => '3 - DATABOOK',
		'status' => '4 - STATUS SEMANAL',
		'testes' => '5 - TESTES'
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
		
		$ajaxMethods = ['checkCategory', 'listCategory', 'thumbnail'];
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
		$baseDir = rtrim($this->Config->get(1)->path, '\\/') . DS;

		// Buscar pasta que contenha o número do pedido após o caractere '-'
		$pedidoNumero = str_pad($order->number, 3, '0', STR_PAD_LEFT);
		$caminhoPedido = null;
		$pastasEncontradas = [];
		$pastasCorrespondentes = [];

		// Verificar se o diretório base existe
		if (!is_dir($baseDir)) {
			return $this->response->withType('application/json')
				->withStringBody(json_encode(['success' => false, 'message' => 'Diretório base não encontrado: ' . $baseDir]));
		}

		// Listar todas as pastas no diretório base com tratamento melhorado
		try {
			$iterator = new \DirectoryIterator($baseDir);
			
			foreach ($iterator as $item) {
				if ($item->isDot() || !$item->isDir()) {
					continue;
				}
				
				$nomePasta = $item->getFilename();
				$caminhoCompleto = $item->getPathname();
				$pastasEncontradas[] = $nomePasta; // Para debug
				
				// Verificar se a pasta contém '-' seguido do número do pedido
				if (strpos($nomePasta, '-') !== false) {
					$partes = explode('-', $nomePasta);
					
					// Pegar a última parte após o último '-' e limpar espaços
					$ultimaParte = trim(end($partes));
					
					// Comparar números (removendo zeros à esquerda)
					$numeroEncontrado = ltrim($ultimaParte, '0');
					$numeroPedidoBusca = ltrim($pedidoNumero, '0');
					
					if ($numeroEncontrado == $numeroPedidoBusca) {
						$pastasCorrespondentes[] = [
							'nome' => $nomePasta,
							'caminho' => $caminhoCompleto
						];
					}
				}
			}
			
			// Se encontrou pastas correspondentes, ordenar alfabeticamente e pegar a última
			if (!empty($pastasCorrespondentes)) {
				// Ordenar por nome da pasta alfabeticamente
				usort($pastasCorrespondentes, function($a, $b) {
					return strcmp($a['nome'], $b['nome']);
				});
				
				// Pegar a última pasta (última em ordem alfabética)
				$ultimaPasta = end($pastasCorrespondentes);
				$caminhoPedido = $ultimaPasta['caminho'] . DS;
			}
			
		} catch (\Exception $e) {
			return $this->response->withType('application/json')
				->withStringBody(json_encode(['success' => false, 'message' => 'Erro ao acessar diretório: ' . $e->getMessage()]));
		}

		if (!$caminhoPedido || !is_dir($caminhoPedido)) {
			// Incluir informações de debug na resposta
			$debugInfo = [
				'baseDir' => $baseDir,
				'pedidoNumero' => $pedidoNumero,
				'pastasEncontradas' => array_slice($pastasEncontradas, 0, 10), // Primeiras 10 pastas para não sobrecarregar
				'pastasCorrespondentes' => $pastasCorrespondentes,
				'totalPastas' => count($pastasEncontradas)
			];
			
			return $this->response->withType('application/json')
				->withStringBody(json_encode([
					'success' => false, 
					'message' => 'Pasta do pedido não encontrada para o pedido #' . $order->number,
					'debug' => $debugInfo
				]));
		}

		// Função recursiva para vasculhar arquivos
		// Verificar se existem subpastas dentro da pasta do pedido (pastas de itens)
		$subpastas = [];
		$caminhoFinal = $caminhoPedido;
		
		try {
			$iterator = new \DirectoryIterator($caminhoPedido);
			
			foreach ($iterator as $item) {
				if ($item->isDot() || !$item->isDir()) {
					continue;
				}
				
				$nomePasta = $item->getFilename();
				
				// Verificar se a pasta contém '-' seguido do código do item
				if (strpos($nomePasta, '-') !== false && !empty($order->coditem)) {
					$partes = explode('-', $nomePasta);
					
					// Pegar a última parte após o último '-' e limpar espaços
					$ultimaParte = trim(end($partes));
					
					// Comparar com o código do item
					if (strcasecmp($ultimaParte, trim($order->coditem)) === 0) {
						$subpastas[] = [
							'nome' => $nomePasta,
							'caminho' => $item->getPathname()
						];
					}
				}
			}
			
			// Se encontrou pastas do item, ordenar alfabeticamente e pegar a última
			if (!empty($subpastas)) {
				usort($subpastas, function($a, $b) {
					return strcmp($a['nome'], $b['nome']);
				});
				
				// Usar a última pasta do item (última em ordem alfabética)
				$ultimaSubpasta = end($subpastas);
				$caminhoFinal = $ultimaSubpasta['caminho'] . DS;
			}
			
		} catch (\Exception $e) {
			// Se houver erro ao listar subpastas, usar a pasta principal
			$caminhoFinal = $caminhoPedido;
		}
		
		$arquivos = $this->listarArquivos($caminhoFinal);

		// Usar transação para evitar duplicados
		$connection = $this->Files->getConnection();
		
		try {
			$connection->begin();
			
			// Exclui os existentes do pedido
			$this->Files->deleteAll(['id_order' => $order->id]);

			// Preparar dados para inserção em lote
			$arquivosParaInserir = [];
			$arquivosUnicos = []; // Para controle de duplicatas
			
			// Salvar os arquivos no banco de dados
			foreach ($arquivos as $arquivo) {
				$name = basename($arquivo);
				$type = pathinfo($arquivo, PATHINFO_EXTENSION);
				
				// Criar chave única baseada em nome e extensão
				$chaveUnica = strtolower($name . '.' . $type);
				
				// Verificar se já foi processado (evitar duplicatas)
				if (isset($arquivosUnicos[$chaveUnica])) {
					continue;
				}
				
				// Marcar como processado
				$arquivosUnicos[$chaveUnica] = true;
				
				// Determinar categoria baseada no caminho da pasta
				$category = $this->determineCategoryFromPath($arquivo, $caminhoFinal);

				$arquivosParaInserir[] = [
					'name' => $name,
					'path' => $arquivo, // Caminho completo
					'status' => 1,
					'id_order' => $id,
					'type' => $type,
					'category' => $category,
					'created' => date('Y-m-d H:i:s'),
					'modified' => date('Y-m-d H:i:s')
				];
			}
			
			// Inserir todos os arquivos de uma vez
			if (!empty($arquivosParaInserir)) {
				$entities = $this->Files->newEntities($arquivosParaInserir);
				$result = $this->Files->saveMany($entities);
				
				if (!$result) {
					throw new \Exception('Erro ao salvar arquivos no banco de dados');
				}
			}
			
			$connection->commit();
			
		} catch (\Exception $e) {
			$connection->rollback();
			return $this->response->withType('application/json')
				->withStringBody(json_encode(['success' => false, 'message' => 'Erro ao salvar arquivos: ' . $e->getMessage()]));
		}

		return $this->response->withType('application/json')
			->withStringBody(json_encode([
				'success' => true, 
				'message' => 'Arquivos mapeados com sucesso.',
				'debug' => [
					'pastasCorrespondentes' => count($pastasCorrespondentes),
					'pastaSelecionada' => basename($caminhoPedido),
					'coditemPedido' => $order->coditem ?? 'Não informado',
					'subpastasEncontradas' => count($subpastas),
					'caminhoFinal' => basename($caminhoFinal),
					'totalArquivosEncontrados' => count($arquivos),
					'arquivosUnicos' => count($arquivosParaInserir),
					'duplicatasIgnoradas' => count($arquivos) - count($arquivosParaInserir)
				]
			]));
	}

	private function determineCategoryFromPath($filePath, $basePath)
	{
		// Remove o caminho base para analisar apenas a estrutura interna
		$relativePath = str_replace($basePath, '', $filePath);
		$relativePath = str_replace('\\', '/', $relativePath); // Normalizar separadores
		
		// Mapear pastas para categorias
		$folderMapping = [
			'pedido' => '0 - PEDIDO',
			'desenhos' => '1 - DESENHOS',
			'cronograma' => '2 - CRONOGRAMA',
			'databook' => '3 - DATABOOK',
			'status' => '4 - STATUS SEMANAL',
			'testes' => '5 - TESTES'
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

	/**
	 * Verifica se um arquivo deve ser ignorado
	 */
	private function deveIgnorarArquivo($filename)
	{
		// Ignorar arquivos ocultos (começam com .)
		if (strpos($filename, '.') === 0) {
			return true;
		}
		
		// Aceitar apenas arquivos PDF, JPG e JPEG
		$extensoesPermitidas = ['pdf', 'jpg', 'jpeg', 'dwg', 'x_t'];
		$extensao = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		
		// Se a extensão não estiver na lista de permitidas, ignorar o arquivo
		if (!in_array($extensao, $extensoesPermitidas)) {
			return true;
		}
		
		return false;
	}

	private function listarArquivos($dir)
	{
		$resultados = [];

		if (!is_dir($dir)) {
			return $resultados;
		}

		try {
			// Listar arquivos da pasta principal
			$iterator = new \DirectoryIterator($dir);
			foreach ($iterator as $item) {
				if ($item->isDot()) {
					continue;
				}
				
				if ($item->isFile()) {
					$filename = $item->getFilename();
					
					// Usar função auxiliar para verificar se deve ignorar
					if ($this->deveIgnorarArquivo($filename)) {
						continue;
					}
					
					$resultados[] = $item->getPathname();
				}
			}
			
			// Listar arquivos das pastas de primeiro nível (categorias)
			$iterator = new \DirectoryIterator($dir);
			foreach ($iterator as $item) {
				if ($item->isDot() || !$item->isDir()) {
					continue;
				}
				
				// Entrar apenas no primeiro nível de subpastas (categorias)
				$subIterator = new \DirectoryIterator($item->getPathname());
				foreach ($subIterator as $subItem) {
					if ($subItem->isDot() || !$subItem->isFile()) {
						continue;
					}
					
					$filename = $subItem->getFilename();
					
					// Usar função auxiliar para verificar se deve ignorar
					if ($this->deveIgnorarArquivo($filename)) {
						continue;
					}
					
					$resultados[] = $subItem->getPathname();
				}
			}
			
		} catch (\Exception $e) {
			error_log("Erro ao listar arquivos: " . $e->getMessage());
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
			// Listar arquivos da pasta principal
			$iterator = new \DirectoryIterator($dir);
			foreach ($iterator as $item) {
				if ($item->isDot()) {
					continue;
				}
				
				// Verificar timeout a cada 50 arquivos
				if (count($resultados) % 50 === 0 && (time() - $startTime) > $maxTime) {
					error_log("Timeout detectado durante listagem de arquivos após " . count($resultados) . " arquivos");
					break;
				}
				
				if ($item->isFile()) {
					$filename = $item->getFilename();
					
					// Usar função auxiliar para verificar se deve ignorar
					if ($this->deveIgnorarArquivo($filename)) {
						continue;
					}
					
					$resultados[] = $item->getPathname();
				}
			}
			
			// Verificar timeout antes de continuar
			if ((time() - $startTime) > $maxTime) {
				return $resultados;
			}
			
			// Listar arquivos das pastas de primeiro nível (categorias)
			$iterator = new \DirectoryIterator($dir);
			foreach ($iterator as $item) {
				if ($item->isDot() || !$item->isDir()) {
					continue;
				}
				
				// Verificar timeout antes de entrar na subpasta
				if ((time() - $startTime) > $maxTime) {
					break;
				}
				
				// Entrar apenas no primeiro nível de subpastas (categorias)
				$subIterator = new \DirectoryIterator($item->getPathname());
				foreach ($subIterator as $subItem) {
					if ($subItem->isDot() || !$subItem->isFile()) {
						continue;
					}
					
					// Verificar timeout a cada 50 arquivos
					if (count($resultados) % 50 === 0 && (time() - $startTime) > $maxTime) {
						error_log("Timeout detectado durante listagem de arquivos após " . count($resultados) . " arquivos");
						break 2; // Sair dos dois loops
					}
					
					$filename = $subItem->getFilename();
					
					// Usar função auxiliar para verificar se deve ignorar
					if ($this->deveIgnorarArquivo($filename)) {
						continue;
					}
					
					$resultados[] = $subItem->getPathname();
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
		$baseDir = rtrim($this->Config->get(1)->path, '\\/') . DS;

		if (!is_dir($baseDir)) {
			set_time_limit(intval($originalTimeLimit));
			return ['found' => false, 'message' => 'Diretório base não encontrado'];
		}

		// Buscar pasta que contenha o número do pedido após o caractere '-'
		$pedidoNumero = str_pad($order->number, 3, '0', STR_PAD_LEFT);
		$caminhoPedido = null;
		$pastasEncontradas = [];
		$pastasCorrespondentes = [];

		// Listar todas as pastas no diretório base com tratamento melhorado
		try {
			$iterator = new \DirectoryIterator($baseDir);
			
			foreach ($iterator as $item) {
				if ($item->isDot() || !$item->isDir()) {
					continue;
				}
				
				$nomePasta = $item->getFilename();
				$caminhoCompleto = $item->getPathname();
				$pastasEncontradas[] = $nomePasta; // Para debug
				
				// Verificar se a pasta contém '-' seguido do número do pedido
				if (strpos($nomePasta, '-') !== false) {
					$partes = explode('-', $nomePasta);
					
					// Pegar a última parte após o último '-' e limpar espaços
					$ultimaParte = trim(end($partes));
					
					// Comparar números (removendo zeros à esquerda)
					$numeroEncontrado = ltrim($ultimaParte, '0');
					$numeroPedidoBusca = ltrim($pedidoNumero, '0');
					
					if ($numeroEncontrado == $numeroPedidoBusca) {
						$pastasCorrespondentes[] = [
							'nome' => $nomePasta,
							'caminho' => $caminhoCompleto
						];
					}
				}
			}
			
			// Se encontrou pastas correspondentes, ordenar alfabeticamente e pegar a última
			if (!empty($pastasCorrespondentes)) {
				// Ordenar por nome da pasta alfabeticamente
				usort($pastasCorrespondentes, function($a, $b) {
					return strcmp($a['nome'], $b['nome']);
				});
				
				// Pegar a última pasta (última em ordem alfabética)
				$ultimaPasta = end($pastasCorrespondentes);
				$caminhoPedido = $ultimaPasta['caminho'] . DS;
			}
			
		} catch (\Exception $e) {
			set_time_limit(intval($originalTimeLimit));
			return ['found' => false, 'message' => 'Erro ao acessar diretório: ' . $e->getMessage()];
		}

		if (!$caminhoPedido || !is_dir($caminhoPedido)) {
			set_time_limit(intval($originalTimeLimit));
			return ['found' => false, 'message' => 'Pasta do pedido não encontrada no diretório'];
		}

		// Verificar se existem subpastas dentro da pasta do pedido (pastas de itens)
		$subpastas = [];
		$caminhoFinal = $caminhoPedido;
		
		try {
			$iterator = new \DirectoryIterator($caminhoPedido);
			
			foreach ($iterator as $item) {
				if ($item->isDot() || !$item->isDir()) {
					continue;
				}
				
				$nomePasta = $item->getFilename();
				
				// Verificar se a pasta contém '-' seguido do código do item
				if (strpos($nomePasta, '-') !== false && !empty($order->coditem)) {
					$partes = explode('-', $nomePasta);
					
					// Pegar a última parte após o último '-' e limpar espaços
					$ultimaParte = trim(end($partes));
					
					// Comparar com o código do item
					if (strcasecmp($ultimaParte, trim($order->coditem)) === 0) {
						$subpastas[] = [
							'nome' => $nomePasta,
							'caminho' => $item->getPathname()
						];
					}
				}
			}
			
			// Se encontrou pastas do item, ordenar alfabeticamente e pegar a última
			if (!empty($subpastas)) {
				usort($subpastas, function($a, $b) {
					return strcmp($a['nome'], $b['nome']);
				});
				
				// Usar a última pasta do item (última em ordem alfabética)
				$ultimaSubpasta = end($subpastas);
				$caminhoFinal = $ultimaSubpasta['caminho'] . DS;
			}
			
		} catch (\Exception $e) {
			// Se houver erro ao listar subpastas, usar a pasta principal
			$caminhoFinal = $caminhoPedido;
		}

		// Executar o scan da pasta encontrada
		$startTime = time();
		$maxScanTime = 240; // 4 minutos máximo para o scan
		
		$arquivos = $this->listarArquivosComTimeout($caminhoFinal, $startTime, $maxScanTime);

		if (empty($arquivos)) {
			set_time_limit(intval($originalTimeLimit));
			return ['found' => false, 'message' => 'Nenhum arquivo encontrado na pasta do pedido'];
		}

		// Verificar se ainda temos tempo
		if ((time() - $startTime) > $maxScanTime) {
			set_time_limit(intval($originalTimeLimit));
			return ['found' => false, 'message' => 'Timeout: Muitos arquivos para processar. Tente novamente.'];
		}

		// Usar transação para evitar duplicados
		$connection = $this->Files->getConnection();
		
		try {
			$connection->begin();
			
			// Remover registros existentes do pedido de forma otimizada
			$this->Files->deleteAll(['id_order' => $orderId]);

			// Preparar dados para inserção em lote
			$arquivosParaInserir = [];
			$arquivosSalvos = 0;
			$arquivosUnicos = []; // Para controle de duplicatas

			foreach ($arquivos as $arquivo) {
				$name = basename($arquivo);
				$type = pathinfo($arquivo, PATHINFO_EXTENSION);
				
				// Criar chave única baseada em nome e extensão
				$chaveUnica = strtolower($name . '.' . $type);
				
				// Verificar se já foi processado (evitar duplicatas)
				if (isset($arquivosUnicos[$chaveUnica])) {
					continue;
				}
				
				// Marcar como processado
				$arquivosUnicos[$chaveUnica] = true;
				
				// Determinar categoria baseada no caminho da pasta
				$category = $this->determineCategoryFromPath($arquivo, $caminhoFinal);

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
			}

			// Inserir todos os arquivos de uma vez
			if (!empty($arquivosParaInserir)) {
				$entities = $this->Files->newEntities($arquivosParaInserir);
				$result = $this->Files->saveMany($entities);
				
				if ($result) {
					$arquivosSalvos = count($arquivosParaInserir);
					$connection->commit();
				} else {
					throw new \Exception('Erro ao salvar arquivos no banco de dados');
				}
			} else {
				$connection->commit();
			}
			
		} catch (\Exception $e) {
			$connection->rollback();
			set_time_limit(intval($originalTimeLimit));
			return ['found' => false, 'message' => 'Erro ao salvar arquivos: ' . $e->getMessage()];
		}

		// Restaurar limite de tempo original
		set_time_limit(intval($originalTimeLimit));

		$totalArquivos = count($arquivos);
		$duplicatasIgnoradas = $totalArquivos - $arquivosSalvos;

		return [
			'found' => true, 
			'message' => "Busca ativa realizada! {$arquivosSalvos} arquivos únicos indexados" . 
						($duplicatasIgnoradas > 0 ? " ({$duplicatasIgnoradas} duplicatas ignoradas)" : "") . "."
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

	public function thumbnail()
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

			// Verificar se é uma imagem
			$imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
			$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
			
			if (!in_array($extension, $imageExtensions)) {
				throw new \Cake\Http\Exception\BadRequestException('Arquivo não é uma imagem');
			}

			// Gerar miniatura
			$thumbnailPath = $this->generateThumbnail($file->path, $extension);

			if (!$thumbnailPath || !file_exists($thumbnailPath)) {
				throw new \Cake\Http\Exception\InternalErrorException('Erro ao gerar miniatura');
			}

			return $this->response->withFile($thumbnailPath, [
				'download' => false,
				'name' => 'thumb_' . $filename
			])->withType(mime_content_type($thumbnailPath));

		} catch (\Exception $e) {
			throw new \Cake\Http\Exception\InternalErrorException('Erro ao gerar miniatura: ' . $e->getMessage());
		}
	}

	private function generateThumbnail($imagePath, $extension)
	{
		$thumbnailDir = TMP . 'thumbnails' . DS;
		if (!is_dir($thumbnailDir)) {
			mkdir($thumbnailDir, 0755, true);
		}

		$thumbnailFilename = md5($imagePath . filemtime($imagePath)) . '.jpg';
		$thumbnailPath = $thumbnailDir . $thumbnailFilename;

		// Se a miniatura já existe e é mais recente que o arquivo original, retornar
		if (file_exists($thumbnailPath) && filemtime($thumbnailPath) >= filemtime($imagePath)) {
			return $thumbnailPath;
		}

		// Verificar se GD está disponível
		if (!extension_loaded('gd')) {
			// Se GD não estiver disponível, retornar o arquivo original para imagens pequenas
			if (filesize($imagePath) < 1024 * 1024) { // 1MB
				return $imagePath;
			}
			throw new \Exception('GD extension não está disponível');
		}

		try {
			// Carregar imagem baseada na extensão
			switch ($extension) {
				case 'jpg':
				case 'jpeg':
					$image = imagecreatefromjpeg($imagePath);
					break;
				case 'png':
					$image = imagecreatefrompng($imagePath);
					break;
				case 'gif':
					$image = imagecreatefromgif($imagePath);
					break;
				case 'bmp':
					$image = imagecreatefrombmp($imagePath);
					break;
				case 'webp':
					$image = imagecreatefromwebp($imagePath);
					break;
				default:
					throw new \Exception('Formato de imagem não suportado');
			}

			if (!$image) {
				throw new \Exception('Erro ao carregar imagem');
			}

			// Obter dimensões originais
			$originalWidth = imagesx($image);
			$originalHeight = imagesy($image);

			// Calcular novas dimensões (máximo 200x150)
			$maxWidth = 200;
			$maxHeight = 150;
			
			$ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
			$newWidth = intval($originalWidth * $ratio);
			$newHeight = intval($originalHeight * $ratio);

			// Criar nova imagem
			$thumbnail = imagecreatetruecolor($newWidth, $newHeight);
			
			// Preservar transparência para PNG e GIF
			if ($extension === 'png' || $extension === 'gif') {
				imagealphablending($thumbnail, false);
				imagesavealpha($thumbnail, true);
				$transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
				imagefilledrectangle($thumbnail, 0, 0, $newWidth, $newHeight, $transparent);
			}

			// Redimensionar
			imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

			// Salvar miniatura como JPEG
			imagejpeg($thumbnail, $thumbnailPath, 85);

			// Limpar memória
			imagedestroy($image);
			imagedestroy($thumbnail);

			return $thumbnailPath;

		} catch (\Exception $e) {
			// Em caso de erro, retornar o arquivo original se for pequeno
			if (filesize($imagePath) < 512 * 1024) { // 512KB
				return $imagePath;
			}
			throw $e;
		}
	}
}
