(function(){
    
    $(document).on('click', '.configurator-step-btn', function(e) {
        e.preventDefault();
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
                //applyNewState(response);
                location.reload();
                console.log(response);
            },
            error: function (response) {
                alert('Fehler');
                console.log(response);
            }
        });
    }

    var applyNewState = function (response) {

        //var configurator = $('#configurator');
        var step_wrapper = $('.configurator-step-wrapper');
        for (step of response.steps) {
            var wrapper_selector = '#step-' + step.id;
            var step_wrapper = $(wrapper_selector);
            if(step.visible) {
                step_wrapper.removeClass('step-hidden');
            } else {
                step_wrapper.addClass('step-hidden');
            }
           
        }

    }


})();


