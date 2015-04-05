$(function(){
    $('#short').click(function(){
        short();
    });

    $('#urlbox').keyup(function(e) {
        if (e.keyCode == 13) {
            short();
        }
    })

    $('#filebox').change(function () {
        var file = $(this).prop('files')[0];
        uploadFile(file);
    })

    function uploadFile(file) {
        var data = new FormData();

        if (file.size < 30e6) {  
            data.append('cppt_file', file)
            $('#drop-area').text('Uploading file...');
            $.ajax({
                url: document.location.origin + "/api/uploadFile",
                type: 'POST',
                processData: false,
                contentType: false,
                dataType: 'json',
                data: data,
                success: function (data) {
                    if (data.success) {
                        $('#urlbox').val('cppt.su/'+data.url);
                        $('#drop-area').text('Link to your file ready!');
                        setTimeout(function(){
                            $('#drop-area').text('Drop file or click here for upload');
                        }, 1000)
                    } else {
                        alert(data.message);
                    }

                    $('#drop-area').css({
                        'border-color': '#EA5A1D',
                        background: 'none',
                        color: '#EA5A1D'
                    });
                }
            });
        } else {
            alert('File too large, 30Mb Max!');
        }
    }

    function short() {
        var url = $('#urlbox').val();
        if (url && url.indexOf('cppt.su') == -1) {
            $.ajax({
                url: document.location.origin + "/api/short",
                cache: false,
                method: "post",
                data: { url : url },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        $('#urlbox').val('cppt.su/'+data.url);
                    } else {
                        alert(data.message);
                    }
                },
                error: function(data) {}
            })
        } else {
            alert('Nothing to short!');
        }
    }

    $('.last_click').each(function(i,v){
        $(v).text(moment(parseInt($(v).text())*1e3).fromNow());
    });

    // Click Drop-Area to append file
    $('#drop-area').click(function(){
        $('#filebox').trigger('click');
    })

    // File Drop-Area
    $('#drop-area').on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
    });

    $('#drop-area').on('dragenter', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).css({
            'border-color': '#740303',
            background: '#C35547',
            color: 'white'
        });
    });

    $('#drop-area').on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).css({
            'border-color': '#EA5A1D',
            background: 'none',
            color: '#EA5A1D'
        });
    });


    $('#drop-area').on('drop', function (e) {
        if(e.originalEvent.dataTransfer){
            if(e.originalEvent.dataTransfer.files.length) {
                e.preventDefault();
                e.stopPropagation();
                uploadFile(e.originalEvent.dataTransfer.files[0]);
            }   
        }
    });
});