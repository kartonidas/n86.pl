App =
{
    initMagnificPopupImage: function() {
        $(".open-image").magnificPopup({
            type: "image",
            gallery: {
                enabled: true,
            }
        });
    },
    
    validForm : function() {
        $("FORM.validate").submit(function(e) {
            var $this = $(this);
            var hasError = App.valid($this);
            if(hasError) {
                e.preventDefault();

                if($this.data("validate-hide-modal") == undefined || !$this.data("validate-hide-modal"))
                    $("#validateFormErrorModal").modal("toggle");
            }
        });
    },

    valid : function($this) {
        var hasError = false;
        $this.find("*[data-validate]").each(function() {
            if((!$(this).is(":visible") && !$(this).attr("data-force-validate")) || $(this).is(":disabled"))
                return true;

            var val = "";
            if($(this).is("input[type='radio'],input[type='checkbox']")) {
                var inputName = $(this).attr("name");
                val = $("input[name='" + inputName + "']:checked").val();
                if(val == undefined) val = "";
            }
            else {
                val = $(this).val();
                if(val == undefined) val = "";
                
                if(!Array.isArray(val))
                    val = val.trim();
           }

            var type = $(this).data("validate");
            if(type == undefined || !type)
                return true;

            type = type.split("|");
            for(var i in type) {
                var _type = type[i];
                var _param = null;
                if(_type.indexOf(":") !== -1) {
                    var tmp = _type.split(":");
                    _type = tmp[0];
                    _param = tmp[1];
                }
                if(Validator[_type] != undefined) {
                    if(!Validator[_type]($(this), val, _param)) {
                        hasError = true;
                        break;
                    }
                }
            }
        });
        return hasError;
    },

    formErrors : function(form, errors) {
        if(form.length && errors) {
            for(e in errors) {
                var fieldName = e;
                if(e.indexOf(".") !== -1) {
                    fieldNameSplitted = e.split(".");
                    fieldName = fieldNameSplitted[0] + "[" + fieldNameSplitted[1] + "]";
                }

                if($("*[name='" + fieldName + "']").length)
                    Validator.setError($("*[name='" + fieldName + "']"), errors[e]);
            }

            if(errors["_message"] != undefined)
                form.find("DIV.alert-modal-error").text(errors["_message"]).removeClass("d-none");
        }
    },
    
    submitContactForm : function($obj, $event) {
        if(App.valid($($obj)))
            return false;
        
        $event.preventDefault();
        
        var formData = $($obj).serialize();
        $($obj).find("INPUT,TEXTAREA,BUTTON").prop("disabled", true);
        $($obj).find("BUTTON[type='submit']").find(".spinner-border").removeClass("d-none");
        $("#contact-form-errors").html("").addClass("d-none");
        
        $.ajax({
            url: $($obj).attr("action"),
            data: formData,
            dataType: "json",
            type: "post",
            success: function(ret){
                $($obj).parent().html(ret.html);
            },
            error: function(error) {
                $($obj).find("INPUT,TEXTAREA,BUTTON").prop("disabled", false);
                $($obj).find("BUTTON[type='submit']").find(".spinner-border").addClass("d-none");
                
                var errorList = $("<UL>").addClass("mb-0 list-unstyled");
                if (error.responseJSON.errors != undefined) {
                    for (const [key, values] of Object.entries(error.responseJSON.errors)) {
                        values.forEach((err) => {
                            let errorItem = $("<LI>").text(err);
                            errorList.append(errorItem);
                        });
                    };
                } else {
                    if (error.responseJSON.message != undefined) {
                        let errorItem = $("<LI>").text(error.responseJSON.message);
                        errorList.append(errorItem);
                    }
                }
                $("#contact-form-errors").append(errorList).removeClass("d-none");
            },
        });
    },
    
    acceptCookie : function() {
        $("DIV.cookie-info").remove();
        App.setCookie("cookie", "true", 30);
    },
    
    setCookie : function(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "")  + expires + "; path=/";
    },
    
    verifyCaptchaCallback : function() {
        $("INPUT[name='captcha_response']").prop("checked", true);
    },
    
    initFBTrack : function() {
        if ($("A.fb-track").length) {
            $("A.fb-track").click(function() {
                $.ajax({
                    headers: {
                        "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                    },
                    url: "/fb-track",
                    type: "post",
                    data: {
                        source : $(this).attr("fb-data-source")
                    },
                    success: function() {
                    }
                });
            });
        }
    }
}

$(document).ready(function(){
    App.validForm();
    App.initMagnificPopupImage();
    App.initFBTrack();
});
