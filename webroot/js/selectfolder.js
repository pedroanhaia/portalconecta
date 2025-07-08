$(document).ready(function () {
    $('#selectfolder').click(function (e) {
        e.preventDefault();
        $('#inputfolder').click();
    });
    $('#inputfolder').change(function () {
        var files = $(this)[0].files;
        var filesPNG = [];
    
        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            if (file.isDirectory) {
                //percorrerSubpastas(file);
            } else if (file.name.toLowerCase().endsWith('.png') || file.name.toLowerCase().endsWith('.gif')) {
                filesPNG.push(file);
            }
        }
        if (filesPNG.length > 0) {
            console.log('Arquivos PNG selecionados:', filesPNG);
            sendImgsAPI(filesPNG,1);
        } else {
            console.log('Nenhum arquivo PNG selecionado.');
        }
    });
    
    
    var imgsproc = 0;
    var iQtdReq = 0;
    var iQtdReqRet = 0;
    var totalUploads = 0;
    var totalImgs = 0;
    var arrqlength = 0;

    function sendImgsAPI(files, tipo) {
        var batchSize = 5;
        arrqlength = files.length;
        var totalBatches = Math.ceil(files.length / batchSize);
        if (tipo == 1) {
            var psurlapi = 'https://test.pedroanhaia.com/arts/serviceartsapi';
        }
        preLoadTurn(1, `Realizando requisição de upload de ${arrqlength} arquivos...`);

        for (var i = 0; i < totalBatches; i++) {
            var batchFiles = files.slice(i * batchSize, (i + 1) * batchSize);
            var imgsBase64 = [];
            var imgsNames = [];

            batchFiles.forEach(function (file) {
                //console.log("Iniciado envio do arquivo " + arquivo.name);
                var reader = new FileReader();
                reader.onload = function (e) {
                    var imgBase64 = e.target.result.split(',')[1];
                    imgsBase64.push(imgBase64);
                    imgsNames.push(file.name);
                    imgsproc = imgsproc +1;
                    // Esta chamada da função deve estar dentro do evento onload
                    // para garantir que os dados do arquivo estejam prontos antes de enviar
                    // os dados para a API
                    if (imgsBase64.length === batchSize) {
                        iQtdReq = iQtdReq+1;
                        sendimgsAPIBatch(imgsBase64, imgsNames, psurlapi);
                        imgsBase64 = [];
                        imgsNames = [];
                    } else if (imgsproc == arrqlength) {
                        iQtdReq = iQtdReq+1;
                        sendimgsAPIBatch(imgssBase64, imgsNames, psurlapi);
                        imgsBase64 = [];
                        imgsNames = [];
                    }
                };
                reader.readAsDataURL(file);
            });
        }
    }

    // Função para enviar lotes de imagens para a API
    function sendimgsAPIBatch(imgsBase64, imgsNames, psurlapifinal) {
        var dados = {
            sPasse: "capcop",
            imagens: imgsBase64,
            imagensnames: imgsNames,
        };

        $.ajax({
            type: 'POST',
            url: psurlapifinal,
            contentType: 'application/json',
            data: JSON.stringify(dados),
            success: function (response) {
                var jsonString = JSON.stringify(response, null, 2);
                //console.log(`Lote enviado com sucesso, mensagem: ${response.statusCode} - ${response.responseText}`);
                totalUploads += response.artesSalvas.length || 0;
                totalImgs += response.artesExistentes.length || 0;
                sTextoRetorno = response.mensagem + jsonString;
                iQtdReqRet = iQtdReqRet+1;
                var percDone = Math.round(((totalUploads+totalImgs) / arrqlength) * 100);
                preLoadTurn(1, `Processando. ${arrqlength} / ${(totalUploads+totalImgs)}... ${percDone}% concluído.`);
                if (iQtdReqRet == iQtdReq) {
                    alert(`Processo concluído. ${totalUploads} imagens salves e ${totalImgs} já existentes.`, response.status, 'success');
                    preLoadTurn(0);
                    window.location.reload(true);
                }
            },
            error: function (error) {
                console.error(`Erro ao enviar lote: ${error.statusCode} - ${error.responseText}`);
                //alert('Erro na chamada AJAX:', 'erro', 'error');
                iQtdReqRet = iQtdReqRet +1;
                var percDone = Math.round(((totalUploads+totalImgs) / arrqlength) * 100);
                preLoadTurn(1, `Processando. ${arrqlength} / ${(totalUploads+totalImgs)}... ${percDone}% concluído.`);
                if (iQtdReqRet == iQtdReq) {
                    //alert(jsonString, response.status, 'success');
                    alert(`Processo concluído. ${totalUploads} imagens salves e ${totalImgs} já existentes.`, response.status, 'success');
                    preLoadTurn(0);
                    window.location.reload(true);
                }
            },
        });
    }
});