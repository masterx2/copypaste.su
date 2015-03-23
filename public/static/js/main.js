$(function(){
    $('#short').click(function(){
        var url = $('#urlbox').val();
        if (url) {
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
    });

    $('.last_click').each(function(i,v){
        $(v).text(moment(parseInt($(v).text())*1e3).fromNow());
    });
})