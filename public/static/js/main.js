$(function(){
    $('#short').click(function(){
        short();
    });

    $('#urlbox').keyup(function(e) {
        if (e.keyCode == 13) {
            short();
        }
    })

    $('#imagebox').change(function () {
        var file = $(this).prop('files')[0],
            data = new FormData();

        if (file.size < 30e6) {  
            data.append('cppt_file', file)
            $('#urlbox').val('Uploading file...');
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
                    } else {
                        alert(data.message);
                    }
                }
            });
        } else {
            alert('File too large, 30Mb Max!');
        }
    })

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
})