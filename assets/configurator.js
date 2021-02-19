(function(){
    
    $(document).on('click', '.configurator-step-btn', function(e) {
        e.preventDefault();
        alert('submit');
        submitHandler(e);
    });

    var submitHandler = function(e) {

        var btn = $(e.target);
        var form = $(e.target).closest('form');
        var data = form.serialize();
        if(btn.hasClass('back-btn')) {
            var action = 'back';
        } else if (btn.hasClass('confirm-btn')) {
            var action = 'confirm'; 
        }
        $.ajax({
            type: form.attr('method'),
            url: form.attr('action'),
            data: form.serialize() + '&action=' + action,
            beforeSend: function() {
                console.log(data);
            },
            success: function (response) {
                alert('OK');
                console.log(response);
            },
            error: function (response) {
                alert('Fehler');
                console.log(response);
            }
        });
    }
})();


