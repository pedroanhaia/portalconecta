/**
 * Sistema de Gerenciamento de Arquivos por Categoria
 * Para o sistema de pedidos CakePHP
 * 
 * @version 2.0
 * @requires jQuery 3.7.1+
 */

var OrderFilesManager = {
    
    // Configurações
    config: {
        orderBaseUrl: 'http://localhost/',
        orderId: null,
        categoryLabels: {
            'dadosgerais': 'Dados Gerais',
            'desenhos': 'Desenhos',
            'art': 'ART',
            'eps': 'EPS',
            'rqs': 'RQS',
            'certmat': 'Certificados dos Materiais',
            'certcons': 'Certificados dos Consumíveis',
            'certcalib': 'Certificados de Calibração',
            'relinsp': 'Relatórios de Inspeção',
            'montdatabook': 'Montagem do Databook',
            'testes': 'Testes',
            'outros': 'Outros'
        },
        fileIcons: {
            'pdf': 'pdf',
            'doc': 'word',
            'docx': 'word',
            'xls': 'excel',
            'xlsx': 'excel',
            'jpg': 'image',
            'jpeg': 'image',
            'png': 'image',
            'gif': 'image',
            'bmp': 'image',
            'svg': 'image',
            'zip': 'archive',
            'rar': 'archive',
            '7z': 'archive',
            'txt': 'alt',
            'csv': 'alt',
            'log': 'alt',
            'dwg': 'alt',
            'dxf': 'alt'
        },
        // Extensões que podem ser visualizadas no navegador
        viewableExtensions: ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'txt', 'html', 'htm', 'xml']
    },

    // Inicializar o sistema
    init: function(baseUrl, orderData) {
        this.config.orderBaseUrl = baseUrl.endsWith('/') ? baseUrl : baseUrl + '/';
        this.config.orderId = orderData.order_id;
        this.config.customerName = orderData.customer_name;
        this.config.orderFolder = orderData.order_folder;

        console.log('OrderFilesManager inicializado:', {
            baseUrl: this.config.orderBaseUrl,
            orderId: this.config.orderId,
            customer: this.config.customerName,
            orderFolder: this.config.orderFolder
        });

        this.bindEvents();
        this.checkAllCategories();
    },

    // Vincular eventos
    bindEvents: function() {
        var self = this;
        
        // Evento para navegar em uma categoria
        $(document).on('click', '.browse-files', function() {
            const category = $(this).data('category');
            const folder = $(this).data('folder');
            self.loadCategoryFiles(category, folder);
        });
        
        // Evento para baixar todos os arquivos de uma categoria
        $(document).on('click', '.download-all', function() {
            const category = $(this).data('category');
            const folder = $(this).data('folder');
            self.downloadCategoryFiles(category, folder);
        });
        
        // Evento para voltar às categorias
        $(document).on('click', '#backToCategories', function() {
            self.showCategoriesList();
        });
        
        // Eventos para arquivos individuais (delegados)
        $(document).on('click', '.download-file', function() {
            const category = $(this).data('category');
            const folder = $(this).data('folder');
            const filename = $(this).data('filename');
            self.downloadSingleFile(filename);
        });
        
        $(document).on('click', '.view-file', function() {
            const category = $(this).data('category');
            const folder = $(this).data('folder');
            const filename = $(this).data('filename');
            self.viewSingleFile(filename);
        });
    },

    // Verificar arquivos disponíveis em todas as categorias
    checkAllCategories: function() {
        var self = this;
        
        $('.file-category-card').each(function() {
            const card = $(this);
            const category = card.data('category');
            const folder = card.data('folder');
            
            self.checkCategoryFiles(card, category, folder);
        });
    },

    // Verificar arquivos de uma categoria específica
    checkCategoryFiles: function(card, category, folder) {
        var self = this;
        const countElement = card.find('.file-count');

        countElement.removeClass().addClass('badge badge-primary');
        countElement.html('<i class="fas fa-spinner fa-spin"></i> Verificando...');

        const url = this.config.orderBaseUrl + 'Files/checkCategory';

        $.ajax({
            url: url,
            method: 'POST',
            timeout: 300000, // 5 minutos de timeout para AJAX
            data: {
                order_id: this.config.orderId,
                customer_name: this.config.customerName,
                order_folder: this.config.orderFolder,
                category: category,
                folder: folder,
                _csrfToken: $('meta[name="csrfToken"]').attr('content') || $('input[name="_csrfToken"]').val()
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            beforeSend: function() {
                // Se for a primeira verificação, mostrar aviso sobre possível demora
                if (countElement.text().includes('Verificando...')) {
                    setTimeout(function() {
                        if (countElement.text().includes('Verificando...')) {
                            countElement.html('<i class="fas fa-spinner fa-spin"></i> Buscando arquivos... Isso pode demorar alguns minutos.');
                        }
                    }, 5000); // Após 5 segundos, mostrar mensagem de demora
                }
            },
            success: function(response) {
                if (response.success) {
                    const count = response.data.count || 0;
                    const size = response.data.totalSize || 0;

                    if (count > 0) {
                        countElement.removeClass().addClass('badge badge-success');
                        let message = `<i class="fas fa-file"></i> ${count} arquivo(s) - ${self.formatBytes(size)}`;
                        
                        // Se foi realizada uma busca ativa, mostrar mensagem adicional
                        if (response.data.scanPerformed) {
                            message += `<br><small class="text-info"><i class="fas fa-search"></i> ${response.data.message}</small>`;
                        }
                        
                        countElement.html(message);
                        card.find('.browse-files, .download-all').prop('disabled', false);
                    } else {
                        countElement.removeClass().addClass('badge badge-secondary');
                        countElement.html('<i class="fas fa-folder"></i> Vazio');
                        card.find('.browse-files, .download-all').prop('disabled', true);
                    }
                } else {
                    countElement.removeClass().addClass('badge badge-warning');
                    countElement.html('<i class="fas fa-exclamation-triangle"></i> ' + (response.message || 'Erro'));
                    card.find('.browse-files, .download-all').prop('disabled', true);
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro ao verificar categoria:', category, error);
                
                if (status === 'timeout') {
                    countElement.removeClass().addClass('badge badge-warning');
                    countElement.html('<i class="fas fa-clock"></i> Timeout - Tente novamente');
                } else {
                    countElement.removeClass().addClass('badge badge-danger');
                    countElement.html('<i class="fas fa-times"></i> Indisponível');
                }
                
                card.find('.browse-files, .download-all').prop('disabled', true);
            }
        });
    },

    // Carregar arquivos de uma categoria específica
    loadCategoryFiles: function(category, folder) {
        var self = this;

        const categoryTitle = this.config.categoryLabels[category] || category;
        $('#selectedCategoryTitle').text(`Arquivos de ${categoryTitle}`);
        $('#categoryFilesTableBody').html('<tr><td colspan="4" class="text-center"><i class="fas fa-spinner fa-spin"></i> Carregando arquivos...</td></tr>');

        $('.files-list').hide();
        $('#selectedCategoryFiles').show();

        const url = this.config.orderBaseUrl + 'Files/listCategory';

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                order_id: this.config.orderId,
                customer_name: this.config.customerName,
                order_folder: this.config.orderFolder,
                category: category,
                folder: folder,
                _csrfToken: $('meta[name="csrfToken"]').attr('content') || $('input[name="_csrfToken"]').val()
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            success: function(response) {
                console.log(response)
                if (response.success && response.data.files) {
                    self.renderFilesList(response.data.files, category, folder);
                } else {
                    $('#categoryFilesTableBody').html(
                        '<tr><td colspan="4" class="text-center text-danger">' +
                        (response.message || 'Erro ao carregar arquivos.') +
                        '</td></tr>'
                    );
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro ao carregar arquivos da categoria:', category, error);
                $('#categoryFilesTableBody').html('<tr><td colspan="4" class="text-center text-danger">Erro de conexão.</td></tr>');
            }
        });
    },

    // Verificar se arquivo pode ser visualizado no navegador
    isViewableFile: function(filename) {
        const extension = filename.split('.').pop().toLowerCase();
        return this.config.viewableExtensions.includes(extension);
    },

    // Renderizar lista de arquivos
    renderFilesList: function(files, category, folder) {
        var self = this;
        let tableHtml = '';
        
        if (files.length > 0) {
            files.forEach(function(file) {
                const iconClass = self.getFileIcon(file.name);
                const fileSize = self.formatBytes(file.size || 0);
                const fileDate = file.modified || '-';
                const isViewable = self.isViewableFile(file.name);
                
                // Determinar quais botões mostrar
                let actionButtons = '';
                
                if (isViewable) {
                    // Arquivo pode ser visualizado - mostrar ambos os botões
                    actionButtons = `
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-outline-info view-file" 
                                    data-category="${category}" 
                                    data-folder="${folder}" 
                                    data-filename="${file.name}"
                                    title="Visualizar arquivo">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-outline-primary download-file" 
                                    data-category="${category}" 
                                    data-folder="${folder}" 
                                    data-filename="${file.name}"
                                    title="Baixar arquivo">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    `;
                } else {
                    // Arquivo não pode ser visualizado - apenas download
                    actionButtons = `
                        <button class="btn btn-outline-primary download-file" 
                                data-category="${category}" 
                                data-folder="${folder}" 
                                data-filename="${file.name}"
                                title="Baixar arquivo">
                            <i class="fas fa-download"></i> Download
                        </button>
                    `;
                }
                
                tableHtml += `
                    <tr>
                        <td>
                            <i class="fas fa-file-${iconClass} text-primary"></i>
                            <span class="ml-2">${file.name}</span>
                        </td>
                        <td><small class="text-muted">${fileDate}</small></td>
                        <td><small class="text-muted">${fileSize}</small></td>
                        <td>
                            ${actionButtons}
                        </td>
                    </tr>
                `;
            });
        } else {
            tableHtml = '<tr><td colspan="4" class="text-center text-muted py-4">Nenhum arquivo encontrado nesta categoria.</td></tr>';
        }
        
        $('#categoryFilesTableBody').html(tableHtml);
    },

    // Mostrar lista de categorias
    showCategoriesList: function() {
        $('.files-list').show();
        $('#selectedCategoryFiles').hide();
    },

    downloadCategoryFiles: function(category, folder) {
        const url = this.config.orderBaseUrl + 'Files/downloadCategory?' + 
            this.buildQueryString({
                order_id: this.config.orderId,
                category: category
            });

        this.downloadFile(url);
    },

    // Baixar um arquivo específico
    downloadSingleFile: function(filename) {
        const url = this.config.orderBaseUrl + 'Files/downloadFile?' + 
            this.buildQueryString({
                order_id: this.config.orderId,
                filename: filename
            });

        this.downloadFile(url);
    },

    // Visualizar um arquivo específico
    viewSingleFile: function(filename) {
        const url = this.config.orderBaseUrl + 'Files/viewFile?' + 
            this.buildQueryString({
                order_id: this.config.orderId,
                filename: filename
            });

        window.open(url, '_blank');
    },

    // Executar download
    downloadFile: function(url) {
        // Criar elemento temporário para download
        const link = document.createElement('a');
        link.href = url;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    },

    // Construir query string
    buildQueryString: function(params) {
        return Object.keys(params)
            .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(params[key]))
            .join('&');
    },

    // Obter ícone do arquivo baseado na extensão
    getFileIcon: function(filename) {
        const extension = filename.split('.').pop().toLowerCase();
        return this.config.fileIcons[extension] || 'alt';
    },

    // Formatar bytes para formato legível
    formatBytes: function(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },

    // Atualizar contadores de categoria
    refreshCategoryCounters: function() {
        this.checkAllCategories();
    },

    // Mostrar notificação de erro
    showError: function(message) {
        console.error('OrderFilesManager Error:', message);
        
        // Se houver um sistema de notificação disponível, usar aqui
        if (typeof toastr !== 'undefined') {
            toastr.error(message);
        } else if (typeof alert !== 'undefined') {
            alert('Erro: ' + message);
        }
    }
};

// Exportar para uso global
window.OrderFilesManager = OrderFilesManager;
