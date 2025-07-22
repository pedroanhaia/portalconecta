/**
 * Sistema de Comentários e Timeline para Pedidos
 * @version 2.0
 */

$(document).ready(function() {
    
    // Função para obter classe do badge por status
    function getStatusBadgeClass(status) {
        if (window.statusBadgeClasses && window.statusBadgeClasses[status]) {
            return window.statusBadgeClasses[status];
        }
        
        // Fallback para as constantes diretas
        switch(parseInt(status)) {
            case 1: return 'primary';    // Recebido
            case 2: return 'warning';    // Em Projeto
            case 3: return 'info';       // Em Fabricação
            case 4: return 'orange';     // Em Transporte
            case 5: return 'success';    // Entregue
            default: return 'secondary';
        }
    }
    
    // Função para obter texto do status
    function getStatusText(status) {
        // Verificar se window.statusTranslations existe e tem o status
        if (window.statusTranslations && window.statusTranslations[status]) {
            return window.statusTranslations[status];
        }
        
        // Fallback para textos padrão
        const statusTexts = {
            1: 'Recebido',
            2: 'Em Projeto',
            3: 'Em Fabricação',
            4: 'Em Transporte',
            5: 'Entregue'
        };
        
        return statusTexts[parseInt(status)] || 'Status ' + status;
    }
    
    // Carregar histórico do pedido
    function loadOrderHistory() {
        if (typeof window.comentarios !== 'undefined' && Array.isArray(window.comentarios) && window.comentarios.length > 0) {
            let historyHtml = '';
            
            window.comentarios.forEach(function(item) {
                // Verificar se o item tem as propriedades necessárias
                if (!item || typeof item !== 'object') {
                    console.warn('Item inválido no histórico:', item);
                    return;
                }
                
                const status = item.status || 0;
                const statusText = getStatusText(status);
                const badgeClass = getStatusBadgeClass(status);
                const dataFormatada = item.data_formatada || item.data || 'Data não disponível';
                
                // Verificar se há mensagens/comentários para este status
                const mensagens = item.mensagens || [];
                const temComentarios = Array.isArray(mensagens) && mensagens.length > 0;
                
                // Construir o HTML no formato original: Status - Data - Botão
                historyHtml += `
                    <div class="history-item d-flex align-items-center justify-content-between">
                        <div class="history-status">
                            <span class="badge badge-${badgeClass}">${statusText}</span>
                        </div>
                        <div class="history-date">
                            <small class="text-muted">${dataFormatada}</small>
                        </div>
                        <div class="history-actions">
                            ${temComentarios ? 
                                `<button class="btn btn-sm btn-primary view-comment" 
                                         data-messages='${JSON.stringify(mensagens)}' 
                                         data-status="${statusText}" 
                                         data-date="${dataFormatada}"
                                         data-status-id="${status}">
                                    <i class="fas fa-comment"></i>
                                    <span class="">${mensagens.length}</span>
                                </button>` 
                                : 
                                '<span class="text-muted">-</span>'
                            }
                        </div>
                    </div>
                `;
            });
            
            $('.history').html(historyHtml);
        } else {
            console.log('Nenhum histórico encontrado ou dados inválidos');
            $('.history').html('<p class="text-muted">Nenhum histórico disponível.</p>');
        }
    }
    
    // Função para escapar HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Visualizar comentários do histórico
    $(document).on('click', '.view-comment', function() {
        const messagesData = $(this).data('messages');
        const status = $(this).data('status');
        const date = $(this).data('date');
        
        let commentsHtml = '';
        
        if (Array.isArray(messagesData) && messagesData.length > 0) {
            messagesData.forEach(function(message, index) {
                const autor = message.autor || message.usuario || 'Usuário não identificado';
                const comentario = message.comentario || message.mensagem || message.text || '';
                const dataMsg = message.data_formatada || message.data || date;
                
                commentsHtml += `
                    <div class="comment-detail ${index > 0 ? 'mt-3 pt-3 border-top' : ''}">
                        <div class="comment-meta mb-2">
                            <strong>${escapeHtml(autor)}</strong>
                            <small class="text-muted ml-2">${escapeHtml(dataMsg)}</small>
                        </div>
                        <div class="comment-text">
                            ${escapeHtml(comentario)}
                        </div>
                    </div>
                `;
            });
        } else {
            commentsHtml = '<p class="text-muted">Nenhum comentário disponível.</p>';
        }
        
        const modalContent = `
            <div class="modal-header">
                <h5 class="modal-title">Comentários - ${status}</h5>
                <button type="button" class="close" onclick="$('#modalComent').hide()">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>Status:</strong> ${status}</p>
                <p><strong>Data da Movimentação:</strong> ${date}</p>
                <hr>
                <div class="comments-container">
                    ${commentsHtml}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="$('#modalComent').hide()">Fechar</button>
            </div>
        `;
        
        $('#comentariosModal').html(modalContent);
        $('#modalComent').show();
    });
    
    // Função para atualizar o histórico com novo comentário
    function updateHistoryWithNewComment(statusId, newComment) {
        // Encontrar o item no histórico que corresponde ao status
        if (typeof window.comentarios !== 'undefined' && Array.isArray(window.comentarios)) {
            const historyItem = window.comentarios.find(item => item.status == statusId);
            
            if (historyItem) {
                // Inicializar mensagens se não existir
                if (!historyItem.mensagens) {
                    historyItem.mensagens = [];
                }
                
                // Adicionar nova mensagem
                historyItem.mensagens.push({
                    autor: newComment.autor,
                    comentario: newComment.comentario,
                    data_formatada: newComment.data,
                    data: newComment.data
                });
                
                // Recarregar o histórico
                loadOrderHistory();
            }
        }
    }
    
    // Manipular cliques nos status da timeline (apenas para edit)
    $(document).on('click', '.clickable-status', function() {
        if (typeof window.currentOrderId === 'undefined') {
            return; // Não é a página de edição
        }
        
        const newStatus = $(this).data('status');
        const statusText = $(this).data('text');
        
        // Verificar se pode alterar para este status
        if (newStatus <= window.currentStatus) {
            alert('Não é possível retroceder o status do pedido.');
            return;
        }
        
        // Abrir modal de confirmação
        $('#modalStatusText').text(`Alterar status para: ${statusText}`);
        $('#modalStatusComentario').val('');
        $('#modalStatus').data('new-status', newStatus).show();
    });
    
    // Confirmar mudança de status
    $(document).on('click', '#confirmarStatusBtn', function() {
        const newStatus = $('#modalStatus').data('new-status');
        const comentario = $('#modalStatusComentario').val();
        
        $.ajax({
            url: window.changeStatusUrl,
            method: 'POST',
            data: {
                order_id: window.currentOrderId,
                new_status: newStatus,
                comment: comentario
            },
            success: function(response) {
                if (response.success) {
                    location.reload(); // Recarregar para mostrar as mudanças
                } else {
                    alert('Erro: ' + (response.message || 'Erro desconhecido'));
                }
            },
            error: function() {
                alert('Erro ao conectar com o servidor.');
            }
        });
        
        $('#modalStatus').hide();
    });
    
    // Fechar modal ao clicar fora
    $(document).on('click', function(e) {
        if ($(e.target).is('#modalStatus, #modalComent')) {
            $(e.target).hide();
        }
    });
    
    // Envio do formulário de comentário
    $('#commentForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const currentStatusId = $('input[name="status"]').val();
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            success: function(response) {
                console.log(response);

                if (response.success) {
                    // Limpar formulário
                    $('#commentForm textarea').val('');
                    
                    // Adicionar comentário à lista da direita
                    const statusText = getStatusText(response.data.status);
                    const badgeClass = getStatusBadgeClass(response.data.status);
                    
                    const newComment = `
                        <div class="comment-item">
                            <div class="comment-header">
                                <strong>${response.data.user_name}</strong>
                                <small class="text-muted">${response.data.data}</small>
                            </div>
                            <div class="comment-text">
                                ${response.data.comentario}
                            </div>
                            <div class="comment-status">
                                <small class="badge badge-${badgeClass}">
                                    Status: ${statusText}
                                </small>
                            </div>
                        </div>
                    `;
                    
                    $('#commentsList').prepend(newComment);
                    
                    // Remover mensagem de "nenhum comentário"
                    $('#commentsList .text-muted').remove();
                    
                    // Atualizar o histórico com o novo comentário
                    updateHistoryWithNewComment(currentStatusId, {
                        autor: response.data.autor,
                        comentario: response.data.comentario,
                        data: response.data.data
                    });
                    
                } else {
                    alert('Erro ao adicionar comentário: ' + (response.message || 'Erro desconhecido'));
                }
            },
            error: function() {
                alert('Erro ao conectar com o servidor.');
            }
        });
    });
    
    // Carregar histórico quando a página carrega
    loadOrderHistory();
});