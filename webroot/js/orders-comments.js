/**
 * JavaScript para Sistema de Comentários e Timeline dos Pedidos
 * Compatível com templates: Orders/view.php e Orders/edit.php
 */
$(document).ready(function () {
// Objeto principal para gerenciar comentários
window.OrderComments = {
    
    /**
     * Inicializa o sistema de comentários
     */
    init: function() {
        this.bindCommentForm();
        this.bindModalEvents();
        this.bindCommentButtons();
    },

    /**
     * Vincula eventos do formulário de comentários AJAX
     */
    bindCommentForm: function() {
        $('#commentForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const comment = $('textarea[name="comment"]').val().trim();
            
            if (!comment) {
                alert('Por favor, digite um comentário.');
                return;
            }
            
            // Desabilita o botão durante o envio
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');
            
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        // Limpa o formulário
                        $('textarea[name="comment"]').val('');
                        
                        // Adiciona o novo comentário à lista
                        const newComment = `
                            <div class="comment-item">
                                <div class="comment-header">
                                    <strong>${response.data.user_name || 'Você'}</strong>
                                    <small class="text-muted">Agora</small>
                                </div>
                                <div class="comment-text">
                                    ${response.data.comment}
                                </div>
                                <div class="comment-status">
                                    <small class="badge badge-info">Status: ${response.data.status_text || 'Atual'}</small>
                                </div>
                            </div>
                        `;
                        
                        // Remove a mensagem "Nenhum comentário" se existir
                        $('#commentsList .text-muted').remove();
                        
                        // Adiciona o novo comentário no topo (mais recente primeiro)
                        $('#commentsList').prepend(newComment);
                        
                        // Mostra mensagem de sucesso
                        OrderComments.showMessage('Comentário adicionado com sucesso!', 'success');
                    } else {
                        OrderComments.showMessage('Erro ao adicionar comentário: ' + (response.message || 'Erro desconhecido'), 'error');
                    }
                },
                error: function(xhr) {
                    console.error('Erro:', xhr);
                    OrderComments.showMessage('Erro ao enviar comentário. Tente novamente.', 'error');
                },
                complete: function() {
                    // Reabilita o botão
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });
    },

    /**
     * Vincula eventos dos modais
     */
    bindModalEvents: function() {
        // Fecha modais
        $('.close').click(function () {
            $('.modal').hide();
        });

        $(window).on('click', function (event) {
            if ($(event.target).hasClass('modal')) {
                $('.modal').hide();
            }
        });
    },

    /**
     * Vincula eventos dos botões de comentário
     */
    bindCommentButtons: function() {
        $(document).on('click', '.btn-comment', function () {
            const logId = $(this).data('log-id'); // ID do registro orders_log
            const statusCode = $(this).data('status'); // Código do status
            
            // Busca os comentários relacionados a este log específico
            const log = window.comentarios.find(l => l.id == logId);

            let html = `<h4>Comentários</h4><ul>`;
            if (log && log.mensagens && log.mensagens.length > 0) {
                log.mensagens.forEach(m => {
                    html += `<li><strong>${m.autor}:</strong> ${m.comentario}</li>`;
                });
            } else {
                html += '<li>Nenhum comentário para este status.</li>';
            }
            html += '</ul>';
            
            $('#comentariosModal').html(html);
            $('#modalComent').show();
        });
    },

    /**
     * Mostra mensagens de feedback para o usuário
     */
    showMessage: function(message, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show mt-2" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;
        
        // Remove alertas anteriores
        $('.add-comment-form .alert').remove();
        
        // Adiciona novo alerta
        $('.add-comment-form').prepend(alertHtml);
        
        // Remove automaticamente após 5 segundos
        setTimeout(function() {
            $('.add-comment-form .alert').fadeOut();
        }, 5000);
    },

    /**
     * Renderiza o histórico de pedidos
     */
    renderHistorico: function(logs) {
        let historicoHtml = '<h4>Histórico:</h4>';

        if (logs && logs.length > 0) {
            logs.forEach(entry => {
                historicoHtml += `<p>`;
                historicoHtml += `<span class="text-content"><strong>${OrderComments.getStatusText(entry.status)}:</strong> ${entry.data}</span>`;

                // Adiciona botão de comentário se houver mensagens
                if (entry.mensagens && entry.mensagens.length > 0) {
                    historicoHtml += `<button class="btn-comment" data-log-id="${entry.id}" data-status="${entry.status}">💬</button>`;
                }

                historicoHtml += `</p>`;
            });
        } else {
            historicoHtml += '<p>Nenhum histórico para exibir.</p>';
        }

        $('.history').html(historicoHtml);
    },

    /**
     * Traduz códigos de status para texto legível
     */
    getStatusText: function(status) {
        // Esta função será sobrescrita pelas templates com os valores específicos
        // ou pode receber os valores via configuração
        const statusMap = window.statusTranslations || {};
        return statusMap[status] || 'Status ' + status;
    }
};

// Objeto para gerenciar alterações de status (específico para edit.php)
window.OrderStatusManager = {
    
    statusSelecionado: null,

    /**
     * Inicializa o gerenciador de status
     */
    init: function() {
        this.bindStatusClick();
        this.bindConfirmButton();
    },

    /**
     * Vincula clique nos ícones de status
     */
    bindStatusClick: function() {
        $('.clickable-status').on('click', function () {
            OrderStatusManager.statusSelecionado = $(this).data('status');
            const texto = $(this).data('text');
            $('#modalStatusText').text(`Confirmar alteração do pedido para o status "${texto}"?`);
            $('#modalStatusComentario').val('');
            $('#modalStatus').show();
        });
    },

    /**
     * Vincula botão de confirmação de mudança de status
     */
    bindConfirmButton: function() {
        $('#confirmarStatusBtn').on('click', function () {
            const comentario = $('#modalStatusComentario').val();
            const orderId = window.currentOrderId; // Deve ser definido na template
            const changeStatusUrl = window.changeStatusUrl; // Deve ser definido na template
            
            $.ajax({
                type: 'POST',
                url: changeStatusUrl,
                data: {
                    pedido_id: orderId,
                    status: OrderStatusManager.statusSelecionado,
                    comentario: comentario
                },
                success: function (res) {
                    if (!res.success) {
                        alert('Erro: ' + res.message);
                        return;
                    }

                    // Atualiza o logo e classes de status
                    $('.logo-holder').html('');
                    $(`.status-step[data-key='${OrderStatusManager.statusSelecionado}'] .logo-holder`).html(window.statusLogoHtml);
                    $('.status-step').removeClass('active').removeClass('complete');
                    $('.status-step').each(function () {
                        const stepKey = parseInt($(this).attr('data-key'));
                        if (stepKey < OrderStatusManager.statusSelecionado) $(this).addClass('complete');
                        if (stepKey === OrderStatusManager.statusSelecionado) $(this).addClass('active');
                    });

                    OrderComments.renderHistorico(res.new_log);
                    window.comentarios = res.new_log; // Atualiza a variável global

                    $('#modalStatus').hide();
                },
                error: function () {
                    alert('Erro ao alterar status.');
                }
            });
        });
    }
};

// Inicialização automática quando o documento estiver pronto
$(document).ready(function() {
    // Inicializa sempre o sistema de comentários
    OrderComments.init();
    
    // Inicializa o gerenciador de status apenas se existir na página
    if ($('.clickable-status').length > 0) {
        OrderStatusManager.init();
    }
    
    // Renderiza histórico inicial se existir
    if (window.comentarios) {
        OrderComments.renderHistorico(window.comentarios);
    }
});
});