function onSignIn(googleUser) {
// Useful data for your client-side scripts:
var profile = googleUser.getBasicProfile();
console.log("ID: " + profile.getId()); // Don't send this directly to your server!
console.log("Name: " + profile.getName());
console.log("Image URL: " + profile.getImageUrl());
console.log("Email: " + profile.getEmail());

// The ID token you need to pass to your backend:
var id_token = googleUser.getAuthResponse().id_token;
console.log("ID Token: " + id_token);
};


$(function(){

    setInterval(function(){
      var opacity = Math.random();
      setTimeout(function(){
        $('.beta').css('opacity', opacity > 0.7 ? 1 : opacity);
      }, opacity > 0.9 ? Math.floor(Math.random()*2)*500 : Math.floor(Math.random()*100)*50000)
    }, Math.floor(Math.random()*2)*50)

    $('.file-stat, .upload-progress').hide();

    $('#short').click(function(){
        short();
    });

    $('#urlbox').keyup(function(e) {
        if (e.keyCode == 13) {
            short();
        }
    })

    $('.file-stat-show').click(function(){
        $(this).toggleClass('open');
        $(this).parent().parent().find('.file-stat').slideToggle();
    })

    $('#filebox').change(function () {
        var files = $(this).prop('files');
        uploadFiles(files);
    })

    var qrgen = function(url) {
        $('.qr-code').remove();
    return $('<li>').addClass('qr-code')
        .append($('<img>').attr('src', 'http://api.qrserver.com/v1/create-qr-code/?data='+url+'&margin=10&bgcolor=00D881&color=141B18&size=150x150'))
    }

    function uploadFiles(data) {
        var form = new FormData(),
            total_size = 0;

        form.append('secure', $('#secure').prop('checked'));

        if (data.length > 100) {
            alert('Too many files, 100 Max!');
            return;
        }

        for (var _i = 0; _i < data.length; _i++) {
            form.append('cppt_file[]', data[_i]);
            total_size += data[_i].size;
        }

        if (total_size > 512e6) {
            alert('File(s) too large, 500Mb Max!');
            return;
        }

        if ($('#zip').prop('checked')) {
            form.append('zip', 1);
        };

        $('.drop-message').hide();
        $('.upload-progress').show();
        $('.upload-bar').css('width', '0%');        

        $.ajax({
            url: document.location.origin + "/api/uploadFile",
            type: 'POST',
            processData: false,
            contentType: false,
            dataType: 'json',
            xhr: function () {
                var xhr = new window.XMLHttpRequest();
                //Download progress
                xhr.upload.onprogress = function(e) {
                    var loaded = Math.floor((e.loaded/e.total)*100);
                    if (loaded == 100) {
                        $('.upload-progress-title').text($('#zip').prop('checked') ? 'Compressing...' : 'Finishing...');
                    } else {
                        $('.upload-progress-title').text(loaded+'% Uploaded');
                    }
                    $('.upload-bar').css('width', loaded+'%');
                };
                return xhr;
            },
            data: form,
            success: function (data) {
                if (data.success) {
                    $('#urlbox').val('http://cppt.su/'+data.url);
                    $('.cat-list').children(':nth-child(1)').after(qrgen('http://cppt.su/'+data.url))
                    if (data.pin) {
                        $('.cat-list').children(':nth-child(1)').after($('<div>').attr('id','pin-code').text(data.pin));
                    }

                    $('.drop-message').show();
                    $('.upload-progress').hide();

                    $('.drop-message').text('Link to your file ready!');
                    setTimeout(function(){
                        $('.drop-message').text('Drop file or click here for upload');
                    }, 1000)
                } else {
                    new PNotify({
                        title: 'Error',
                        text: data.message
                    });
                }
            },
            error: function (e) {
                $('.drop-message').show();
                $('.upload-progress').hide();
                $('.drop-message').text('Something wrong, Try again!');
                setTimeout(function(){
                    $('.drop-message').text('Drop file or click here for upload');
                }, 1000)
            }
        });
    }

    function short() {
        var url = $('#urlbox').val();
        if (url && url.indexOf('cppt.su') == -1) {
            $.ajax({
                url: document.location.origin + "/api/short",
                cache: false,
                method: "post",
                data: {
                    url : url,
                    secure: $('#secure').prop('checked')
                },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        $('#urlbox').val('http://cppt.su/'+data.url);
                        $('.cat-list').children(':nth-child(1)').after(qrgen('http://cppt.su/'+data.url))
                        if (data.pin) {
                            $('.cat-list').children(':nth-child(1)').after($('<div>').attr('id','pin-code').text(data.pin));
                        }
                    } else {
                        new PNotify({
                            title: 'Error',
                            text: data.message
                        });
                    }
                },
                error: function(data) {}
            })
        } else {
            new PNotify({
                title: 'Warning',
                text: 'Nothing to short!'
            });
        }
    }

    $('.last-click').each(function(i,v){
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
        // $(this).css({
        //     'border-color': '#740303',
        //     background: '#C35547',
        //     color: 'white'
        // });
    });

    $('#drop-area').on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        // $(this).css({
        //     'border-color': '#EA5A1D',
        //     background: 'none',
        //     color: '#EA5A1D'
        // });
    });

    $('#drop-area').on('drop', function (e) {
        if(e.originalEvent.dataTransfer){
            if(e.originalEvent.dataTransfer.files.length) {
                e.preventDefault();
                e.stopPropagation();
                uploadFiles(e.originalEvent.dataTransfer.files);
            }   
        }
    });
});