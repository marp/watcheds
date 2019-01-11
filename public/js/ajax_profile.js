$(document).ready(function() {
    var frm = $('#change_mail');
    frm.submit(function(e){
        e.preventDefault();

        $('#submit_btn').prop('disabled', 1);

        var formData = frm.serialize();
        formData += '&' + $('#submit_btn').attr('name') + '=' + $('#submit_btn').attr('value');
        $.ajax({
            type: frm.attr('method'),
            url: frm.attr('action'),
            data: formData,
            success: function(data){
                $('#message').html('<div class="alert alert-success">Email has been changed!</div>').delay(3000).fadeIn(1000);
                $('#oldmail').val($('#change_email_email').val());
                $('#oldmail').css("background-color", "#edffe6");

                //$('#oldmail').val('Email has been changed!');
                $('#submit_btn').prop('disabled',false);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#message').html(textStatus).delay(2000).fadeOut(2000);
            }

        })
        .always(function() {
            $('#submit_btn').prop('disabled',false);
        });
    });

    //Visibility
    var formVisibility = $('#change_visibility');
    formVisibility.submit(function(e){
        e.preventDefault();

        $('#submitVisibility').prop('disabled', 1);

        var formData = formVisibility.serialize();
        formData += '&' + $('#submitVisibility').attr('name') + '=' + $('#submitVisibility').attr('value');
        $.ajax({
            type: formVisibility.attr('method'),
            url: formVisibility.attr('action'),
            data: formData,
            success: function(data){
                $('#profile_visibility').html(' '+$('#change_visibility_visibility option:selected').text());
                // $('#message').html('<div class="alert alert-success">Email has been changed!</div>').delay(3000).fadeIn(1000);
                // $('#oldmail').val($('#change_email_email').val());
                // $('#oldmail').css("background-color", "#edffe6");

                //$('#oldmail').val('Email has been changed!');
                $('#submit_btn').prop('disabled',false);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#message').html(textStatus).delay(2000).fadeOut(2000);
            }

        })
            .always(function() {
                $('#submitVisibility').prop('disabled',false);
            });
    });
    //Avatar
    var formAvatar = $('#change_avatar');
    formAvatar.submit(function(e){
        e.preventDefault();
        $('#submitAvatar').prop('disabled', 1);
        $.ajax({
            type: formAvatar.attr('method'),
            url: formAvatar.attr('action'),
            processData: false,
            contentType: false,
            async: true,
            cache: false,
            data: new FormData($(this)[0]),
            // dataType: 'json',
            success: function(data){
                // $('#profile_Avatar').html(' '+$('#change_Avatar_visibility option:selected').text());
                // $('#message').html('<div class="alert alert-success">Email has been changed!</div>').delay(3000).fadeIn(1000);
                // $('#oldmail').val($('#change_email_email').val());
                // $('#oldmail').css("background-color", "#edffe6");

                //$('#oldmail').val('Email has been changed!');
                $('#submit_btn').prop('disabled',false);
                $('#avatarMessage').css('display','block');
            },
            error: function (response, desc, err) {
               alert('Upload error!');
            },

        })
            .always(function() {
                $('#submitAvatar').prop('disabled',false);
            });
    });



});