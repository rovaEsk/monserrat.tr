$(function() {
    var couleurVar = '#000000',
        textColor = '#000000',
        tamponColor = 'transparent',
        setCfg = false,
        zIndex = 2,
        fixation = null,
        selected = null,
        dwnloading = false,
        fontName = $('#police').find(':selected').val(),
        theme = 'divers',
        dimensionsp,
        showDim = true,
        nbrElements = 0,
        tamponWidth,
        tamponHeight;
    $.data(document.body, 'preview', false);
    $.data(document.body, 'resizable', false);
    $.data(document.body, 'ratioCfg', 0);
    $.data(document.body, 'draggable', false);
    $.data(document.body, 'rotatable', false);
    $.data(document.body, 'currentID', false);
    $.data(document.body, 'startCfg', true);
    $.data(document.body, 'vWidth', 0);
    $.data(document.body, 'vHeight', 0);
    $.data(document.body, 'currentImage', false);
    $.data(document.body, 'typetampon', false);
    $.data(document.body, 'showColors', false);
    $.data(document.body, 'selfHeight', false);
    $.data(document.body, 'textEdit', false);
    $.data(document.body, 'typetamponVitro', false);
	$.data(document.body, 'firstElem', false);
	
	/** function tampon **/
	var rotation = 0;
	$.fn.rotate = function(degrees) {
	    $(this).css({'-webkit-transform' : 'rotate('+ degrees +'deg)',
	                 '-moz-transform' : 'rotate('+ degrees +'deg)',
	                 '-ms-transform' : 'rotate('+ degrees +'deg)',
	                 'transform' : 'rotate('+ degrees +'deg)'});
	    return $(this);
	};
	var getPercent = function(elem) {
	    var elemName = elem.attr("id"),
	        width = elem.width(),
	        parentWidth = elem.offsetParent().width(),
	        percent = Math.round(100 * width / parentWidth);
	    return percent;
	};
	$.widget("ui.resizable", $.ui.resizable, {
	    resizeTo: function(newSize) {
	        var start = new $.Event("mousedown", { pageX: 0, pageY: 0 });
	        this._mouseStart(start);
	        this.axis = 'se';
	        var end = new $.Event("mouseup", {
	            pageX: newSize.width - this.originalSize.width,
	            pageY: newSize.height - this.originalSize.height
	        });
	        this._mouseDrag(end);
	        this._mouseStop(end);
	    }
	});
	$.fn.resizeContent = function(newHeight){
	    var aspectRatio = $(this).data('aspectRatio'),
	        setRatio = $.data(document.body, 'tamponPreviewWidth') / $.data(document.body, 'tamponWidth'),
	        leftPos = $(this).position().left,
	        topPos = $(this).position().top;
	    $('.selectedElement').removeClass('selectedElement');
	    $('.ctrlElem, .imgL, .imgH').hide();
	    if (aspectRatio == undefined) {
	        aspectRatio = $(this).width() / $(this).height();
	        $(this).data('aspectRatio', aspectRatio);
	    }
	    $('#margin .elementContainer').each(function(){
	        if ( $.data(document.body, 'preview') ) {
	            //console.log('id: ' + $(this).attr('id') + ' - left: ' + $(this).position().left + ' - top: ' + $(this).position().top);
	            var newLeft = $(this).position().left * setRatio;
	            var newTop =  $(this).position().top * setRatio;
	            var newWidth = $(this).width() * setRatio;
	            $(this).css({left: newLeft, top: newTop});
	        } else {
	            $(this).css({left: leftPos, top: topPos});
	        }
	    });
	    $(this).height(newHeight); 
	    $(this).width(parseInt(newHeight * aspectRatio));
	}
	$.fn.verticalAlign = function() {
	    return this.css("margin-top", ($(this).parent().height() - $(this).height()) / 2 + "px")
	}
	$.fn.center = function(e) {
	    if (e) {
	        e = this.parent()
	    } else {
	        e = window
	    }
	    this.css({
	        position: "absolute",
	        top: ($(e).height() - this.outerHeight()) / 2 + $(e).scrollTop() + "px",
	        left: ($(e).width() - this.outerWidth()) / 2 + $(e).scrollLeft() + "px"
	    });
	    return this
	}
	$.fn.centerV = function(e) {
	    if (e) {
	        e = this.parent()
	    } else {
	        e = window
	    }
	    this.css({
	        position: "absolute",
	        top: ($(e).height() - this.outerHeight()) / 2 + $(e).scrollTop() + "px"
	    });
	    return this
	}
	$.fn.centerH = function(e) {
	    if (e) {
	        e = this.parent()
	    } else {
	        e = window
	    }
	    this.css({
	        position: "absolute",
	        left: ($(e).width() - this.outerWidth()) / 2 + $(e).scrollLeft() + "px"
	    });
	    return this
	}
	$.fn.pressEnter = function(e) {
	    return this.each(function() {
	        $(this).bind("enterPress", e);
	        $(this).keyup(function(e) {
	            if (e.keyCode == 13) {
	                $(this).trigger("enterPress")
	            }
	        })
	    })
	}

	function doDraggable() {
	    $(".draggable").draggable({
	        containment: "#tampon_content",
	        start: function() {
	            $(".reset_rotate").fadeOut(), $.data(document.body, "draggable", !0)
	        },
	        stop: function() {
	            setTimeout(function() {
	                $.data(document.body, "draggable", !1)
	            }, 100)
	        }
	    })
	}

	function doResizable() {
	    if ($('.selectedElement').hasClass('typeFond') || $('.selectedElement').hasClass('typeForme')) {
	        var aspect = false;
	    } else {
	        var aspect = !0;
	    }
	    $(".resizable").each(function() {
	        $(this).resizable({
	            containment: "#tampon_content",
	            alsoResize: $(this).find(".elemContent"),
	            aspectRatio: aspect,
	            minHeight: 2,
	            minWidth: 2,
	            handles: "se",
	            start: function() {
	                $(".reset_rotate").fadeOut(), $.data(document.body, "resizable", !0)
	            },
	            stop: function() {
	                var supportWidth = $('#longueur').val();
	                var supportHeight = $('#hauteur').val();
	                if ($('.selectedElement').hasClass('typeText')) {
	                    var elemType = 'text';
	                } else if ($('.selectedElement').hasClass('typeMotif')) {
	                    var elemType = 'motif';
	                } else if ($('.selectedElement').hasClass('typeImage')) {
	                    var elemType = 'image';
	                } else if ($('.selectedElement').hasClass('typeFond')) {
	                    var elemType = 'fond';
	                } else if ($('.selectedElement').hasClass('typeForme')) {
	                    var elemType = 'forme';
	                }
	                setTimeout(function() {
	                    $.data(document.body, "resizable", !1);
	                    /** Text Size **/
	                    $.ajax({
	                        type: "POST",
	                        url: "../html5/ajax/tampons_size.php",
	                        dataType: "json",
	                        data: {
	                            action: 'getsize',
	                            id: $.data(document.body, 'currentID'),
	                            elemWidth: $('.selectedElement').width()+2,
	                            elemHeight: $('.selectedElement').height()+2,
	                            ratio: $.data(document.body, 'ratioCfg'),
	                            vitWidthpx: $('#tampon_content').width(),
	                            vitHeightpx: $('#tampon_content').height(),
	                            vitWidthcm: $('#longueur').val(),
	                            vitHeightcm: $('#hauteur').val(),
	                            vitType: $.data(document.body, 'typetampon'),
	                            vitroType: $.data(document.body, 'typetamponVitro'),
	                            type: elemType
	                        },
	                        success: function(resultx) {
	                            $('.selectedElement > .imgH, .selectedElement > .imgL').remove();
	                            //$('.selectedElement').append( '<img class="imgL" style="position: absolute;bottom: -20px;left: 0;" src="data:image/png;base64,' + resultx['imgL'] + '" style="" /><img class="imgH" style="position: absolute;bottom: 0;left: -25px;" src="data:image/png;base64,' + resultx['imgH'] + '" style="" />' );
	                            $('.selectedElement').append('<img class="imgL" data-elemSize="' + resultx['longueur'] + '" style="position: absolute;bottom: -20px;left: 0;" src="data:image/png;base64,' + resultx['imgL'] + '" style="" /><img class="imgH" data-elemSize="' + resultx['hauteur'] + '" style="position: absolute;bottom: 0;left: -25px;" src="data:image/png;base64,' + resultx['imgH'] + '" style="" />');
	                            var thiscmHeight = $('.selectedElement').find('img.imgH').attr('data-elemsize'),
	                                thiscmWidth = $('.selectedElement').find('img.imgL').attr('data-elemsize');
	                            $('#elem_width').val(thiscmWidth);
	                            $('#elem_height').val(thiscmHeight);
	                            $.post('./html5/ajax/elemPrix.php', function(data) {
	                                var prixHT = parseFloat(data).toFixed(2);
	                                var tva = (Math.round(parseFloat(data)) / 100) * 20;
	                                var prixTTC = parseFloat(prixHT) + parseFloat(tva);
	                                $('#prix_ht span').html(Number(prixHT).toFixed(2));
	                                $('#prix_ttc span').html(Number(prixTTC).toFixed(2));
	                            });
	                        }
	                    });
	                }, 100);
	            }
	        })
	    })
	}

	function doRotatable() {
	    $(".rotatable").each(function() {
	        $(this).rotatable({
	            start: function() {
	                $.data(document.body, "rotatable", !0), $(".reset_rotate").fadeOut()
	            },
	            stop: function() {
	                setTimeout(function() {
	                    $.data(document.body, "rotatable", !1), $(".reset_rotate").fadeOut()
	                }, 100)
	            }
	        })
	    })
	}

	function isNumber(n) {
	    return !isNaN(parseFloat(n)) && isFinite(n);
	}! function(e) {
	    e.fn.numeric = function(t, n) {
	        "boolean" == typeof t && (t = {
	            decimal: t,
	            negative: !0,
	            decimalPlaces: -1
	        }), t = t || {}, "undefined" == typeof t.negative && (t.negative = !0);
	        var r = t.decimal === !1 ? "" : t.decimal || ".",
	            i = t.negative === !0 ? !0 : !1,
	            a = "undefined" == typeof t.decimalPlaces ? -1 : t.decimalPlaces;
	        return n = "function" == typeof n ? n : function() {}, this.data("numeric.decimal", r).data("numeric.negative", i).data("numeric.callback", n).data("numeric.decimalPlaces", a).keypress(e.fn.numeric.keypress).keyup(e.fn.numeric.keyup).blur(e.fn.numeric.blur)
	    }, e.fn.numeric.keypress = function(t) {
	        var n = e.data(this, "numeric.decimal"),
	            r = e.data(this, "numeric.negative"),
	            i = e.data(this, "numeric.decimalPlaces"),
	            a = t.charCode ? t.charCode : t.keyCode ? t.keyCode : 0;
	        if (13 == a && "input" == this.nodeName.toLowerCase()) return !0;
	        if (13 == a) return !1;
	        var c = !1;
	        if (t.ctrlKey && 97 == a || t.ctrlKey && 65 == a) return !0;
	        if (t.ctrlKey && 120 == a || t.ctrlKey && 88 == a) return !0;
	        if (t.ctrlKey && 99 == a || t.ctrlKey && 67 == a) return !0;
	        if (t.ctrlKey && 122 == a || t.ctrlKey && 90 == a) return !0;
	        if (t.ctrlKey && 118 == a || t.ctrlKey && 86 == a || t.shiftKey && 45 == a) return !0;
	        if (48 > a || a > 57) {
	            var u = e(this).val();
	            if (0 !== e.inArray("-", u.split("")) && r && 45 == a && (0 === u.length || 0 === parseInt(e.fn.getSelectionStart(this), 10))) return !0;
	            n && a == n.charCodeAt(0) && -1 != e.inArray(n, u.split("")) && (c = !1), 8 != a && 9 != a && 13 != a && 35 != a && 36 != a && 37 != a && 39 != a && 46 != a ? c = !1 : "undefined" != typeof t.charCode && (t.keyCode == t.which && 0 !== t.which ? (c = !0, 46 == t.which && (c = !1)) : 0 !== t.keyCode && 0 === t.charCode && 0 === t.which && (c = !0)), n && a == n.charCodeAt(0) && (c = -1 == e.inArray(n, u.split("")) ? !0 : !1)
	        } else if (c = !0, n && i > 0) {
	            var l = e.inArray(n, e(this).val().split(""));
	            l >= 0 && e(this).val().length > l + i && (c = !1)
	        }
	        return c
	    }, e.fn.numeric.keyup = function() {
	        var t = e(this).val();
	        if (t && t.length > 0) {
	            var n = e.fn.getSelectionStart(this),
	                r = e.fn.getSelectionEnd(this),
	                i = e.data(this, "numeric.decimal"),
	                a = e.data(this, "numeric.negative"),
	                c = e.data(this, "numeric.decimalPlaces");
	            if ("" !== i && null !== i) {
	                var u = e.inArray(i, t.split(""));
	                0 === u && (this.value = "0" + t, n++, r++), 1 == u && "-" == t.charAt(0) && (this.value = "-0" + t.substring(1), n++, r++), t = this.value
	            }
	            for (var l = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "-", i], s = t.length, d = s - 1; d >= 0; d--) {
	                var f = t.charAt(d);
	                0 !== d && "-" == f ? t = t.substring(0, d) + t.substring(d + 1) : 0 !== d || a || "-" != f || (t = t.substring(1));
	                for (var o = !1, h = 0; h < l.length; h++)
	                    if (f == l[h]) {
	                        o = !0;
	                        break
	                    }
	                o && " " != f || (t = t.substring(0, d) + t.substring(d + 1))
	            }
	            var m = e.inArray(i, t.split(""));
	            if (m > 0)
	                for (var v = s - 1; v > m; v--) {
	                    var y = t.charAt(v);
	                    y == i && (t = t.substring(0, v) + t.substring(v + 1))
	                }
	            if (i && c > 0) {
	                var u = e.inArray(i, t.split(""));
	                u >= 0 && (t = t.substring(0, u + c + 1), r = Math.min(t.length, r))
	            }
	            this.value = t, e.fn.setSelection(this, [n, r])
	        }
	    }, e.fn.numeric.blur = function() {
	        var t = e.data(this, "numeric.decimal"),
	            n = e.data(this, "numeric.callback"),
	            r = e.data(this, "numeric.negative"),
	            i = this.value;
	        if ("" !== i) {
	            var a = new RegExp(r ? "-?" : "^\\d+$|^\\d*" + t + "\\d+$");
	            a.exec(i) || n.apply(this)
	        }
	    }, e.fn.removeNumeric = function() {
	        return this.data("numeric.decimal", null).data("numeric.negative", null).data("numeric.callback", null).data("numeric.decimalPlaces", null).unbind("keypress", e.fn.numeric.keypress).unbind("keyup", e.fn.numeric.keyup).unbind("blur", e.fn.numeric.blur)
	    }, e.fn.getSelectionStart = function(e) {
	        if ("number" === e.type) return void 0;
	        if (e.createTextRange && document.selection) {
	            var t = document.selection.createRange().duplicate();
	            return t.moveEnd("character", e.value.length), "" == t.text ? e.value.length : Math.max(0, e.value.lastIndexOf(t.text))
	        }
	        try {
	            return e.selectionStart
	        } catch (n) {
	            return 0
	        }
	    }, e.fn.getSelectionEnd = function(e) {
	        if ("number" === e.type) return void 0;
	        if (e.createTextRange && document.selection) {
	            var t = document.selection.createRange().duplicate();
	            return t.moveStart("character", -e.value.length), t.text.length
	        }
	        return e.selectionEnd
	    }, e.fn.setSelection = function(e, t) {
	        if ("number" == typeof t && (t = [t, t]), t && t.constructor == Array && 2 == t.length)
	            if ("number" === e.type) e.focus();
	            else if (e.createTextRange) {
	            var n = e.createTextRange();
	            n.collapse(!0), n.moveStart("character", t[0]), n.moveEnd("character", t[1] - t[0]), n.select()
	        } else {
	            e.focus();
	            try {
	                e.setSelectionRange && e.setSelectionRange(t[0], t[1])
	            } catch (r) {}
	        }
	    }
	}(jQuery);

	function getRandomColor() {
	    var letters = '0123456789ABCDEF'.split('');
	    var color = '#';
	    for (var i = 0; i < 6; i++) {
	        color += letters[Math.floor(Math.random() * 16)];
	    }
	    return color;
	}
	jQuery.fn.repeatedclick = function(f, options) {
	    var defaults = {
	        duration: 350,
	        speed: 0.85,
	        min: 50
	    };
	    var opts = jQuery.extend(defaults, options);
	    if (typeof jQuery.repeatedEvents === 'undefined') {
	        jQuery.repeatedEvents = [];
	    }
	    jQuery.repeatedEvents.push(f);
	    var eventNum = jQuery.repeatedEvents.length - 1;
	    var repeatedEvent;
	    var repeatedEventTimer;
	    return this.each(function() {
	        repeatedEvent = function(eventNum, duration, event) {
	            var that = this;
	            jQuery.repeatedEvents[eventNum].call(that, event);
	            repeatedEventTimer = setTimeout(function() {
	                repeatedEvent.call(that, eventNum, duration > opts.min ? duration * opts.speed : duration, event)
	            }, duration);
	        };
	        jQuery(this).mousedown(function(e) {
	            repeatedEvent.call(this, eventNum, opts.duration, e);
	        });
	        var clearRepeatedEvent = function() {
	            if (typeof repeatedEventTimer !== 'undefined') {
	                clearInterval(repeatedEventTimer);
	            }
	        };
	        jQuery(this).mouseout(clearRepeatedEvent);
	        jQuery(this).mouseup(clearRepeatedEvent);
	    });
	};
	var mouseDrag = $.ui.resizable.prototype._mouseDrag;
	$.ui.resizable.prototype._mouseDrag = function (event) {
	    event.shiftKey = !event.shiftKey;
	    var aspectRatio = this._aspectRatio;
	    this._aspectRatio = aspectRatio && event.shiftKey;
	    mouseDrag.apply(this, arguments);
	    this._aspectRatio = aspectRatio;
	};
	/** end fonction tampon **/


    function showfirstElem() {
        if (!$.data(document.body, 'firstElem')) {
            Lobibox.notify('info', {
                msg: 'Utilisez les flèches de votre clavier pour déplacer l\'élément séléctionné et la touche Suppr. pour le supprimer.',
                img: 'html5/img/keyboard.png',
                title: 'Raccourcis clavier',
                showClass: 'fadeInDown',
                hideClass: 'fadeUpDown',
                delay: 10000,
                sound: false
            });
            $.data(document.body, 'firstElem', true);
        }
    }
	
    $("input[type='text']").click(function() {
        $(this).select();
    });
    $(".tampon-img img").elevateZoom({
        zoomWindowPosition: 10,
        zoomWindowHeight: 200,
        zoomWindowWidth:200,
        zoomWindowOffetx: -5
    });
    $('#textmoins').click(function(){
        var textWidth = $('.selectedElement').width(),
            textHeight = $('.selectedElement').height();
        console.log( textWidth + ' - ' + textHeight );
        $('.selectedElement').resizable("resizeTo", { height: textHeight-1, width: textWidth-1 });
    });
    $('#textplus').click(function(){
        var textWidth = $('.selectedElement').width(),
            textHeight = $('.selectedElement').height();
        console.log( textWidth + ' - ' + textHeight );
        $('.selectedElement').resizable("resizeTo", { height: textHeight+1, width: textWidth+1 });
    });
    $('.colorselectc').click(function(e){
        e.preventDefault();
        var elemData = $(this).attr('data-colorselect');
        $('.'+elemData).click();
    });
    $('.colorset').click(function(e){
        e.preventDefault();
        var inkcolor = $(this).attr('data-colorset');
        $('.check').hide();
        $(this).find('.check').show();
    });
    $('#change-modele').click(function(e){
        e.preventDefault();
        $('.tampon-select, .tamponcontainer').fadeIn();
        $('#change-modele').fadeOut();
    });
    $('.select-tampon').click(function(e){
        e.preventDefault();
        var tamponSize = $(this).attr('data-tampon-ws'),
            tamponDim = tamponSize.split('x'),
            previewSize = $(this).attr('data-tampon-prev'),
            previewDim = previewSize.split('x'),
            totalHeight = parseInt( tamponDim[1] ) + parseInt( previewDim[1] ) + 40,
            eximgSrc = $(this).find('div.tampon-ex').find('img').attr('src');
        setCfg = true;
        $('.tampon-select, .tamponcontainer').fadeOut();
        $('#change-modele').fadeIn();
        $('#tampon_content').css({'width': parseInt(tamponDim[0])+15, 'height': parseInt(tamponDim[1])+15}).show().centerH(true);
        $('#margin').css({'width': parseInt(tamponDim[0]), 'height': parseInt(tamponDim[1])}).show().center(true);
        $('#preview_container').css({'width': parseInt(previewDim[0])+35, 'height': parseInt(previewDim[1])+35}).show().centerH(true);
        $('#tampon_preview').css({'width': parseInt(previewDim[0]), 'height': parseInt(previewDim[1])}).show().centerH(true);
        $('#tampon_bg, #apercu_content').height(totalHeight);
        tamponWidth = tamponDim[0];
        tamponHeight = tamponDim[1];
        $.data(document.body, 'tamponWidth', tamponDim[0]);
        $.data(document.body, 'tamponHeight', tamponDim[1]);
        $.data(document.body, 'tamponPreviewWidth', parseInt( previewDim[0] ) + 10 );
        $.data(document.body, 'tamponPreviewHeight', parseInt( previewDim[1] )  + 10 );
        $('#tampon_preview').html('<img src="' + eximgSrc + '" alt="" />');
        $('#preview').show();
    });
    $('#tampon-metalline').click(function(e){
        e.preventDefault();
        $('#tampon-printy-content').animate({height:0},1000);
        $('#tampon-metallline-content').animate({height:180},1000);
        var texte = 'Le tampon printy est un tampon conçu pour une utilisation intensive...';
        $('#tampon-description').html(texte);
    });
    $('#tampon-printy').click(function(e){
        e.preventDefault();
        $('#tampon-metallline-content').animate({height:0},1000);
        $('#tampon-printy-content').animate({height:180},1000);
        var texte = 'Le tampon printy est un tampon conçu pour une utilisation simple et ponctuelle...';
        $('#tampon-description').html(texte);
    });
    $('#tampon-printy').hover(function(){
        $(this).find('#printy-pricetag').stop().rotate(20);
    }, function(){
        $(this).find('#printy-pricetag').stop().rotate(0);
    });
    $('#tampon-metalline').hover(function(){
        $(this).find('#metalline-pricetag').stop().rotate(20);
    }, function(){
        $(this).find('#metalline-pricetag').stop().rotate(0);
    });
    $('#ulmetalline, #ulprinty').mCustomScrollbar({
        theme:"3d-dark",
        alwaysShowScrollbar: 2,
        scrollButtons:{
            enable: true,
            scrollType: "stepped",
            scrollAmount: 170
        },
    });
    /*jQuery(window).on('beforeunload', function() {
        return 'Vous perdrez votre travail si vous quittez cette page !';
    }).unload(function() {
        $.ajax({
            type: "POST",
            async: false,
            url: "ajax/saveSession.php"
        });
        /*html2canvas( $( "#tampon_bg" ), {

            onrendered: function( canvas ) {

                var imgCanvas = canvas.toDataURL();

                $.ajax({

                    type: "POST",

                    async: false,

                    url: "ajax/postScrn.php",

                    data: {

                        imageCanvas: imgCanvas

                    }

                });

            }

        });*/
    //});


	function calculPrix() {
        /*var longueur = parseInt($('#longueur').val()),
        hauteur = parseInt($('#hauteur').val()),
        surface = longueur * hauteur;*/
		var longueur = 50,
        hauteur = 20,
        surface = longueur * hauteur;
        
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "./html5/ajax/tamponsPrix.php",
            data: {
                bLong: longueur,
                bHaut: hauteur,
            },
            success: function(result) {
            	console.log(result.prixAdhesif);
                var prixHT = parseFloat(result.prixHT).toFixed(2);
                var tva = (Math.round(parseFloat(result.prixHT)) / 100) * tvaNormal;
                var prixTTC = parseFloat(prixHT) + parseFloat(tva);
                $('#prix_ht span').html(Number(prixHT).toFixed(2));
                $('#prix_ttc span').html(Number(prixTTC).toFixed(2));
            }
        });
	}
	
    $('#drawRect').click(function(e) {
        e.preventDefault();
        var supportWidth = $('#longueur').val();
        var supportHeight = $('#hauteur').val();
        if (setCfg == false) {
            swal({
                title: "Attention",
                text: "Vous devez choisir votre type de tampon avant de pouvoir continuer",
                type: "warning",
                closeOnConfirm: true
            }, function() {
                $('#longueur').effect('highlight', {
                    color: '#c70077'
                }, 1500);
                $('input#longueur').focus();
            });
            return false;
        } else {
            $('#textecfg').val('');            
            $.ajax({
                type: "POST",
                url: strUrlModuleHTML5 + "/ajax/tampons_forme.php",
                dataType: "json",
                data: {
                    action: 'update',
                    couleur: couleurVar,
                    ratio: $.data(document.body, 'ratioCfg'),
                    width: tamponWidth,
                    height: tamponHeight,
                    type: 'fond'
                },
                success: function(result) {
                    $('.selectedElement').removeClass('selectedElement');
                    $('#tampon_content #margin').append('<div class="elementContainer typeForme resizable draggable rotatable" id="' + result['id'] + '" style="max-height:100%;position:absolute"><div class="reset_rotate ctrlElem" title="Réinitialiser la position de l\'élement"></div><div class="ctrlElem seresizer ui-resizable-handle ui-resizable-se"></div><div class="ctrlElem delete-element" title="Supprimer l\'élément"></div></div>');
                    $('#' + result['id']).addClass('selectedElement');
					
					showfirstElem();
					
                    $('input.elemSize').prop('disabled', false);
                    $('.ctrlElem, .imgL, .imgH').hide();
                    $('.selectedElement').css({
                        'width': '50%',
                        'height': '10%',
                        'position': 'absolute'
                    });
                    $('.selectedElement').css('background-color', couleurVar);
                    setTimeout(function() {
                        var maxWidth = $('#tampon_content').width() - 50;
                        var maxHeight = $('#tampon_content').height() - 50;
                        var ratio = 0;
                        var width = $('#' + result['id']).width();
                        var height = $('#' + result['id']).height();
                        if (width > maxWidth) {
                            ratio = maxWidth / width;
                            $('.selectedElement').css("width", maxWidth);
                            $('.selectedElement').css("height", height * ratio);
                            height = height * ratio;
                            width = width * ratio;
                        }
                        if (height > maxHeight) {
                            ratio = maxHeight / height;
                            $('.selectedElement').css("height", maxHeight);
                            $('.selectedElement').css("width", width * ratio);
                            width = width * ratio;
                            height = height * ratio;
                        }
                        $('.selectedElement').center(true);
                    }, 10);
                    $('#colorpicker-element').stop(true).animate({
                        height: 130
                    }, 200);
                    $('.selectedElement div.ctrlElem').show();
                    $('.selectedElement div.reset_rotate').hide();
                    var nbrLayers = $('ul#calques li').length;
                    var newLayer = parseInt(nbrLayers) + 1;
                    zIndex = zIndex + 1;
                    $('.selectedElement').css('z-index', zIndex);
                    $('ul#calques').prepend('<li data-zindex="' + zIndex + '" data-object-id="' + result['id'] + '" class="ui-state-default"><span class="text">Forme</span><span class="layer">calque ' + newLayer + '</span></li>');
                    $('ul#calques').sortable('refresh');
                    doDraggable();
                    doResizable();
                    doRotatable();
                    $.data(document.body, 'currentID', result['id']);
                }
            });
        } 
    });
    var tour = {
        id: "start-tour",
        showPrevButton: true,
        scrollTopMargin: 100,
        i18n: {
            nextBtn: "Suivant",
            prevBtn: "Précedent",
            doneBtn: "Terminé",
            skipBtn: "Passer",
            closeTooltip: "Fermer"
        },
        steps: [{
            title: "Modèle du tampon",
            content: "Choisissez votre le modèle de tampon désiré",
            target: document.querySelector("#tampons"),
            placement: "left"
        }, {
            title: "Texte et police d'écriture",
            content: "Ajoutez des textes sur votre tampon. Cliquez sur Ok ou appuyez sur la touche Entrée de votre clavier pour valider votre saisie.",
            target: document.querySelector("#block_right2"),
            placement: "left"
        }, {
            title: "Taille de l'élément",
            content: "Pour plus de précision, utilisez les boutons + et - pour modifier la taille de l'élément sélectionné (celui entouré de rouge).",
            target: document.querySelector("#textmoins"),
            placement: "bottom"
        }, {
            title: "Pictogrammes",
            content: "Vous pouvez choisir l'un des pictogrammes que nous vous proposons.",
            target: document.querySelector(".motifs_container"),
            placement: "left"
        }, {
            title: "Ajouter votre propre image",
            content: "Vous pouvez aussi télécharger et insérer votre propre image.",
            target: document.querySelector("#uploadFile"),
            placement: "left"
        }, {
            title: "Dessiner une forme",
            content: "Ce bouton sert à dessiner des formes carrés ou rectangulaires sur votre tampon. Vous pouvez utiliser la combinaison 'MAJ + redimensionnement souris' pour conserver les proportions.",
            target: document.querySelector("#drawRect"),
            placement: "left"
        }, {
            title: "Couleur de l'encre",
            content: "Sélectionnez la couleur de l'encre qui sera livrée avec votre tampon.",
            target: document.querySelector("#block_right4"),
            placement: "left"
        }, {
            title: "Aligner, déplacer, dupliquer, pivoter...",
            content: "Ces boutons de contrôles vous permettent d'interagir avec l'élement sélectioné (entouré en rouge sur l'aperçu), vous pouvez le déplacer, le centrer sur votre tampon, le dupliquer ou même le faire pivoter.",
            target: document.querySelector("#block_right4_2"),
            placement: "left"
        }]
    };
    hopscotch.startTour(tour);
    $('#longueur, #hauteur').numeric();
    $('#tampon_content, #tampon_bg').click(function(e) {
        e.preventDefault();
        if ($.data(document.body, 'rotatable') == false && $.data(document.body, 'draggable') == false && $.data(document.body, 'resizable') == false) {
            $('.selectedElement div.ctrlElem, .selectedElement .imgL, .selectedElement .imgH').hide();
            $('.selectedElement').removeClass('selectedElement');
            $('input.elemSize').prop('disabled', true).val('');
            $('#colorpicker-element').stop(true).animate({
                height: 0
            }, 200);
            $('#textecfg').val('');
            $('#validSize').hide();
        }
    });
    $('#duplicate').click(function(e) {
        e.preventDefault();
        var thisElem = $('.selectedElement');
        if ($('.selectedElement').hasClass('typeFond') || $('.selectedElement').hasClass('typeForme')) {
            var thisElemID = thisElem.attr('id');
        } else {
            var thisElemID = thisElem.find('.elemContent').attr('id');
        }
        var thisLayer = $('*[data-object-id="' + thisElemID + '"]');
        if ($('.selectedElement')[0]) {
            var newElem = thisElem.clone();
            var typeElem;
            $('.ctrlElem, .imgL, .imgH').hide();
            $('.selectedElement').removeClass('selectedElement');
            $('#tampon_content').append(newElem);
            newElem.addClass('selectedElement');
            $('input.elemSize').prop('disabled', false);
            $('.selectedElement').css({
                left: '+=5px',
                top: '+=5px'
            });
            $('.selectedElement').find('.imgL, .imgH').show();
            doDraggable();
            doResizable();
            doRotatable();
            if (thisElem.hasClass('typeMotif')) {
                typeElem = 'motif';
            } else if (thisElem.hasClass('typeText')) {
                typeElem = 'text';
            } else if (thisElem.hasClass('typeImage')) {
                typeElem = 'image';
            } else if (thisElem.hasClass('typeFond')) {
                typeElem = 'fond';
            } else if (thisElem.hasClass('typeForme')) {
                typeElem = 'forme';
            }
            $.ajax({
                type: "POST",
                url: strUrlModuleHTML5 + "/ajax/cloneElem.php",
                dataType: "json",
                data: {
                    action: 'clone',
                    id: thisElemID,
                    type: typeElem,
                    vitType: $.data(document.body, 'typetampon'),
                    vitroType: $.data(document.body, 'typetamponVitro')
                },
                success: function(resultx) {
                    if (resultx) {
                        var newid = resultx.id;
                        $.data(document.body, 'currentID', newid);
                        if (typeElem == 'fond') {
                            $('.selectedElement').attr('id', newid);
                        } else {
                            if (typeElem == 'forme') {
                                $('.selectedElement').attr('id', newid);
                            } else {
                                $('.selectedElement').find('.tampon_element').attr('id', newid);
                            }
                            zIndex = zIndex + 1;
                            $('.selectedElement').css('z-index', zIndex);
                            var nbrLayers = $('ul#calques li').length;
                            var newLayer = parseInt(nbrLayers) + 1;
                            if (thisElem.hasClass('typeMotif')) {
                                $('ul#calques').prepend('<li data-zindex="' + zIndex + '" data-object-id="' + newid + '" class="ui-state-default"><img style="width:25px" data-src-code="' + resultx.code + '" src="' + resultx.motif + '" alt="' + resultx.motif + '" /> <span class="layer">calque ' + newLayer + '</span></li>');
                            } else if (thisElem.hasClass('typeText')) {
                                $('ul#calques').prepend('<li data-zindex="' + zIndex + '" data-object-id="' + newid + '" class="ui-state-default"><span class="text">' + resultx.text + '</span><span class="layer">calque ' + newLayer + '</span></li>');
                            } else if (thisElem.hasClass('typeImage')) {
                                $('ul#calques').prepend('<li data-zindex="' + zIndex + '" data-object-id="' + newid + '" class="ui-state-default"><img style="width:25px;max-height:100%" src="' + resultx.imgPath + '" alt="' + resultx.imgPath + '" /> <span class="layer">calque ' + newLayer + '</span></li>');
                            } else if (thisElem.hasClass('typeForme')) {
                                $('ul#calques').prepend('<li data-zindex="' + zIndex + '" data-object-id="' + newid + '" class="ui-state-default"><span class="text">Forme</span><span class="layer">calque ' + newLayer + '</span></li>');
                            }
                        }
                        $('ul#calques').sortable('refresh');
                        $.post(strUrlModuleHTML5+ '/ajax/elemPrix.php', function(data) {
                            var prixHT = parseFloat(data).toFixed(2);
                            var tva = (Math.round(parseFloat(data)) / 100) * 20;
                            var prixTTC = parseFloat(prixHT) + parseFloat(tva);
                            $('#prix_ht span').html(Number(prixHT).toFixed(2));
                            $('#prix_ttc span').html(Number(prixTTC).toFixed(2));
                        });
                    }
                }
            });
        }
    });
    $('#uploadFile').click(function(e) {
        e.preventDefault();
        if (setCfg == false) {
            swal({
                title: "Attention",
                text: "Vous devez choisir votre type de tampon avant de pouvoir continuer",
                type: "warning",
                closeOnConfirm: true
            }, function() {
                $('#longueur').effect('highlight', {
                    color: '#c70077'
                }, 1500);
                $('input#longueur').focus();
            });
            return false;
        } else {
            $.fancybox({
                href: '#uploadFileContent'
            });
        }
    });
    $('.download').click(function(e) {
        e.preventDefault();
        if (!dwnloading) {
            $('#fileinput').val('');
            $('#fileinput').trigger('click');
        }
    });
    $("#progressbar").progressbar({
        value: 0,
        max: 100
    });
    $('#fileinput').change(function() {
        dwnloading = true;
        var fileData = $(this).prop('files')[0];
        var formData = new FormData();
        formData.append('imageupload', fileData);
        $('a.download').html('Téléchargement en cours <img src="./img/ajax-loader.gif" alt="" />');
        $("#progressbar").show();
        $.ajax({
            url: strUrlModuleHTML5 + '/ajax/tamponImgUpload.php',
            dataType: 'json',
            contentType: false,
            processData: false,
            data: formData,
            type: 'POST',
            xhr: function() {
                var xhr = $.ajaxSettings.xhr();
                xhr.upload.onprogress = function(evt) {
                    var uploadVal = evt.loaded / evt.total * 100;
                    $("#progressbar").progressbar("value", uploadVal).children('.ui-progressbar-value').html(uploadVal.toPrecision(3) + '%').css("display", "block");
                };
                xhr.upload.onload = function() {
                    console.log('DONE!');
                };
                return xhr;
            },
            success: function(result) {
                $.data(document.body, 'currentImage', result.id);
                if (result.success == true) {
                    if (result.dpi < 150 && result.dpi != 0) {
                        var msgContent = "<img style='max-width:100%;max-height:100px' src='" + result.imgPathOp + "' alt'' /><br><br>La qualité de votre image (<strong>" + result.dpi + " PPP</strong>) est insuffisante, nous recommandons d'utiliser des images avec une qualité de <strong>150 PPP</strong> (points par pouce) minimum pour obtenir un résultat d'impression optimal.";
                        var typeContent = "warning";
                        var confirmColor = "#DD6B55";
                        var confirmText = "Continuer";
                        var showCancel = true;
                    } else if (result.dpi > 150) {
                        var msgContent = "<img style='max-width:100%;max-height:100px' src='" + result.imgPathOp + "' alt'' /><br><br>La qualité de votre image (<strong>" + result.dpi + " PPP</strong>) est satisfaisante, le rendu d'impression sera optimal.";
                        var typeContent = "success";
                        var confirmColor = "#A5DC86";
                        var confirmText = "Ok";
                        var showCancel = false;
                    } else {
                        var msgContent = "<img style='max-width:100%;max-height:100px' src='" + result.imgPathOp + "' alt'' /><br><br>Nous n'avons pas pu déterminer la qualité de votre image, nous ne pouvons par conséquent pas garantir le rendu d'impression. Nous vous conseillons vivement de choisir une autre image.";
                        var typeContent = "warning";
                        var confirmColor = "#DD6B55";
                        var confirmText = "Continuer";
                        var showCancel = true;
                    }
                    swal({
                        title: "Qualité de l'image",
                        text: msgContent,
                        type: typeContent,
                        showCancelButton: showCancel,
                        confirmButtonColor: confirmColor,
                        confirmButtonText: confirmText,
                        cancelButtonText: "Changer d'image",
                        closeOnConfirm: true,
                        closeOnCancel: true,
                        html: true
                    }, function(isConfirm) {
                        if (isConfirm) {
                            $('#tampon_content').append('<div class="elementContainer typeImage resizable draggable rotatable" style="max-height:100%;position:absolute"><div class="reset_rotate ctrlElem" title="Réinitialiser la position de l\'élement"></div><img class="elemContent tampon_element" style="max-height:100%" src="' + result.imgPathOp + '" alt="' + result.imgPathOp + '" id="' + result.id + '" /><div class="ctrlElem seresizer ui-resizable-handle ui-resizable-se"></div><div class="ctrlElem delete-element" title="Supprimer l\'élément"></div></div>');
                            setTimeout(function() {
                                var maxWidth = $('#tampon_content').width() - 50;
                                var maxHeight = $('#tampon_content').height() - 50;
                                var ratio = 0;
                                var width = $('.selectedElement img.elemContent').width();
                                var height = $('.selectedElement img.elemContent').height();
                                if (width > maxWidth) {
                                    ratio = maxWidth / width;
                                    $('.selectedElement, .selectedElement img.elemContent').css("width", maxWidth);
                                    $('.selectedElement, .selectedElement img.elemContent').css("height", height * ratio);
                                    height = height * ratio;
                                    width = width * ratio;
                                }
                                if (height > maxHeight) {
                                    ratio = maxHeight / height;
                                    $('.selectedElement, .selectedElement img.elemContent').css("height", maxHeight);
                                    $('.selectedElement, .selectedElement img.elemContent').css("width", width * ratio);
                                    width = width * ratio;
                                    height = height * ratio;
                                }
                                $('.selectedElement').center(true);
                            }, 10);
                            $('.selectedElement').removeClass('selectedElement');
							
							showfirstElem();
							
                            $('#' + result['id']).parent('div').addClass('selectedElement');
                            $('input.elemSize').prop('disabled', false);
                            $('#colorpicker-element').stop(true).animate({
                                height: 0
                            }, 200);
                            var nbrLayers = $('ul#calques li').length;
                            var newLayer = parseInt(nbrLayers) + 1;
                            zIndex = zIndex + 1;
                            $('.selectedElement').css('z-index', zIndex);
                            $('ul#calques').prepend('<li data-zindex="' + zIndex + '" data-object-id="' + result.id + '" class="ui-state-default"><img style="width:25px;max-height: 100%;" src="' + result.imgPath + '" alt="' + result.imgPath + '" /> <span class="layer">calque ' + newLayer + '</span></li>');
                            $('ul#calques').sortable('refresh');
                            $('.ctrlElem').hide();
                            $('.selectedElement div.ctrlElem').show();
                            $('.selectedElement div.reset_rotate').hide();
                            doDraggable();
                            doResizable();
                            doRotatable();
                            $.data(document.body, 'currentID', result.id);
                            /*setTimeout(function() {
                                $.ajax({
                                    type: "POST",
                                    url: "./html5/ajax/tampons_size.php",
                                    dataType: "json",
                                    data: {
                                        action: 'getsize',
                                        id: result.id,
                                        elemWidth: parseInt($('.selectedElement').css('width'), 10) + 2,
                                        elemHeight: parseInt($('.selectedElement').css('height'), 10) + 2,
                                        ratio: $.data(document.body, 'ratioCfg'),
                                        vitWidthpx: $('#tampon_content').width(),
                                        vitHeightpx: $('#tampon_content').height(),
                                        vitWidthcm: $('#longueur').val(),
                                        vitHeightcm: $('#hauteur').val(),
                                        vitType: $.data(document.body, 'typetampon'),
                                        vitroType: $.data(document.body, 'typetamponVitro'),
                                        type: 'image'
                                    },
                                    success: function(resultx) {
                                        //$('#' + result.id).parent('div.elementContainer').append('<img class="imgL" style="' + ((!showDim) ? 'display:none;' : 'display:block;') + 'position: absolute;bottom: -20px;left: 0;" src="data:image/png;base64,' + resultx['imgL'] + '" style="" /><img class="imgH" style="' + ((!showDim) ? 'display:none;' : 'display:block;') + 'position: absolute;bottom: 0;left: -25px;" src="data:image/png;base64,' + resultx['imgH'] + '" style="" />');
                                        $('#' + result['id']).parent('div.elementContainer').append('<img class="imgL" data-elemSize="' + resultx['longueur'] + '" style="' + ((!showDim) ? 'display:none;' : 'display:block;') + 'position: absolute;bottom: -20px;left: 0;" src="data:image/png;base64,' + resultx['imgL'] + '" style="" /><img class="imgH" data-elemSize="' + resultx['hauteur'] + '" style="' + ((!showDim) ? 'display:none;' : 'display:block;') + 'position: absolute;bottom: 0;left: -25px;" src="data:image/png;base64,' + resultx['imgH'] + '" style="" />');
                                        var thiscmHeight = $('#' + result['id']).parent('div.elementContainer').find('img.imgH').attr('data-elemsize'),
                                            thiscmWidth = $('#' + result['id']).parent('div.elementContainer').find('img.imgL').attr('data-elemsize');
                                        $('#' + result['id']).parent('div.elementContainer').attr('elemratio', resultx['elemRatio']);
                                        $('#elem_width').val(thiscmWidth);
                                        $('#elem_height').val(thiscmHeight);
                                        $.post('./html5/ajax/elemPrix.php', function(data) {
                                            var prixHT = parseFloat(data).toFixed(2);
                                            var tva = (Math.round(parseFloat(data)) / 100) * 20;
                                            var prixTTC = parseFloat(prixHT) + parseFloat(tva);
                                            $('#prix_ht span').html(Number(prixHT).toFixed(2));
                                            $('#prix_ttc span').html(Number(prixTTC).toFixed(2));
                                        });
                                    }
                                });
                            }, 100);*/
                            $.fancybox.close();
                        } else {
                            $.ajax({
                                type: "POST",
                                url: strUrlModuleHTML5 + "/ajax/imageCancel.php",
                                dataType: "json",
                                data: {
                                    action: 'cancel',
                                    id: $.data(document.body, 'currentImage'),
                                    type: 'image'
                                }
                            });
                            return false;
                        }
                    });
                    $("#progressbar").hide();
                    $("#progressbar").progressbar("value", 0).children('.ui-progressbar-value').html(0 + '%').css("display", "block");
                } else {
                    swal({
                        title: "Attention",
                        text: result.msg,
                        type: "warning",
                        closeOnConfirm: true
                    });
                }
                dwnloading = false;
                $('a.download').html('Télécharger');
            }
        });
    });
    $('.dimensions_tampon').keyup(function() {
        var typeSel = $(this).val();
        if (isNumber(typeSel)) {
            $.data(document.body, 'vWidth', $('#longueur').val());
            $.data(document.body, 'vHeight', $('#hauteur').val());
            var longueur = parseInt($('#longueur').val()),
                hauteur = parseInt($('#hauteur').val());
            if (longueur > hauteur) {
                var ratio = hauteur / longueur;
                $.data(document.body, 'ratioCfg', ratio);
                var newHeight = parseInt(750) * ratio;
                if (newHeight <= 500) $('#tampon_content').width(750).height(newHeight);
                else $('#tampon_content').width(500 / ratio).height(500);
            } else if (hauteur > longueur) {
                var ratio = longueur / hauteur;
                $.data(document.body, 'ratioCfg', ratio);
                var newWidth = parseInt(500) * ratio;
                if (newWidth <= 750) $('#tampon_content').height(500).width(500 * ratio);
                else $('#tampon_content').height(750 / ratio).width(750);
            } else if (hauteur == longueur) {
                $('#tampon_content').height(500).width(500);
            }
            $('#tampon_content').center(true);
            if (isNumber($('#longueur').val()) && isNumber($('#hauteur').val())) {
                setCfg = true;
                var vitWidth = $('#tampon_content').width(),
                    vitHeight = $('#tampon_content').height();
                $('#tampon_height').html('<span style="color:#ff0000;font-size:10px;">' + hauteur + '</span>').css('height', vitHeight);
                $('#tampon_width').html('<span style="color:#ff0000;font-size:10px;">' + longueur + '</span>').css('width', vitWidth);
                var tamponxy = $('#tampon_content').offset();
                $('#tampon_width').css({
                    top: -15,
                    left: 0
                });
                $('#tampon_height').css({
                    top: 0,
                    left: -20
                });
            }
        } else {
            $(this).val('');
            setCfg = false;
            return false;
        }
    });
    $("#textecfgsize").change(function () {
        $('.selectedElement').attr("fontsize", $("#textecfgsize").val());
        $('#add_text').trigger("click");
    });
    $('#textecfg').pressEnter(function() {
        $('#add_text').click();
    });
    $('#conftabs').tabs();
    /*$('#themes').ddlist({
        width: 150,
        onSelected: function(index, value, text) {
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "./html5/ajax/motifs.php",
                data: {
                    motifs: value,
                    ratio: $.data(document.body, 'ratioCfg'),
                    vitType: $.data(document.body, 'typetampon'),
                    vitroType: $.data(document.body, 'typetamponVitro')
                },
                success: function(result) {
                    theme = value;
                    $('.motifs_container').html('');
                    $.each(result, function(i, item) {
                        $('.motifs_container').append('<div title="Ajouter un motif sur votre tampon" class="motif" id="' + item['code'] + '" style="background-size:100%;background-image: url(' + item['path'] + ');"></div>');
                    });
                    var width = 0;
                    $('.motifs_container div.motif').each(function() {
                        width += $(this).outerWidth(true);
                    });
                    $('.motifs_container').css('width', width + 'px');
                }
            });
        }
    });*/
    
    $('#themes').ddlist({
        width: 150,
        onSelected: function(index, value, text) {
        	
        	$('.motifs_container').hide();
            $('#'+value+"_container").show();
            
            theme=value;
        	          
            var width = 0;
            $('#'+value+'_container div.motif').each(function() {            	
                width += $(this).outerWidth(true);
            });            
            $('.motifs_container').css('width', width + 'px');
            
            
            /*$.ajax({
                type: "POST",
                dataType: "json",
                url: "./html5/ajax/motifs.php",
                data: {
                    motifs: value,
                    prodID: productID,
                    ratio: $.data(document.body, 'ratioCfg')
                },
                success: function(result) {
                    theme = value;
                    $('.motifs_container').html('');
                    $.each(result, function(i, item) {
                        $('.motifs_container').append('<div title="Ajouter un motif sur votre banderole" class="motif" id="' + item['code'] + '" style="background-size:100%;background-image: url(' + item['path'] + ');"></div>');
                    });
                    var width = 0;
                    $('.motifs_container div.motif').each(function() {
                        width += $(this).outerWidth(true);
                    });
                    $('.motifs_container').css('width', width + 'px');
                }
            });*/
        }
    });
    
    var width = 0;
    $('.motifs_container').each(function() {    	
    	var width = 0;
    	$(this).find('div.motif').each(function() {
           width += $(this).outerWidth(true);
    	});    	
    	$(this).css('width', width + 'px');
    });
    
    
    $('#police').ddlist({
        width: 200,
        onSelected: function(index, value, text) {
            fontName = value;
            if ($('.selectedElement').hasClass('typeText')) {
                var elementChange = $('.selectedElement').find('.elemContent');
                var idCode = elementChange.attr('id');
                var altCode = elementChange.attr('alt');
                var elemWidth = $('.selectedElement').width() + 2;
                if (typeof $('.selectedElement').attr("fontsize") != "undefined") {
	                textecfgsize = $('.selectedElement').attr("fontsize");
	                //document.getElementById("textecfgsize").value = textecfgsize;
	            } else {
	                textecfgsize = $("#textecfgsize").val();
	            }
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: strUrlModuleHTML5 + "ajax/tampons_text.php",
                    data: {
                        action: 'update',
                        text: encodeURIComponent(altCode),
                        couleur: couleurVar,
                        font: fontName,
                        fontsize: textecfgsize,
                        id: idCode,
                        vitType: $.data(document.body, 'typetampon'),
                        vitroType: $.data(document.body, 'typetamponVitro'),
                        ratio: $.data(document.body, 'ratioCfg')
                    },
                    success: function(result) {
                        
                        elementChange.attr('src', 'data:image/png;base64,' + result['image']);
                        
                        $('.selectedElement').css({
                            'height': 'auto',
                            'width': elemWidth
                        });
                        $('.selectedElement').find('.elemContent').css({
                            'height': 'auto',
                            'width': 'auto'
                        });
                        /*setTimeout(function() {
                            $.ajax({
                                type: "POST",
                                url: "./html5/ajax/tampons_size.php",
                                dataType: "json",
                                data: {
                                    action: 'getsize',
                                    id: idCode,
                                    elemWidth: parseInt($('.selectedElement').css('width'), 10) + 2,
                                    elemHeight: parseInt($('.selectedElement').css('height'), 10) + 2,
                                    ratio: $.data(document.body, 'ratioCfg'),
                                    vitWidthpx: $('#tampon_content').width(),
                                    vitHeightpx: $('#tampon_content').height(),
                                    vitWidthcm: $('#longueur').val(),
                                    vitHeightcm: $('#hauteur').val(),
                                    vitType: $.data(document.body, 'typetampon'),
                                    vitroType: $.data(document.body, 'typetamponVitro'),
                                    type: 'text'
                                },
                                success: function(resultx) {
                                    $('#' + idCode).parent('div.elementContainer').children('.imgL, .imgH').remove();
                                    //$('#' + idCode).parent('div.elementContainer').append('<img class="imgL" style="' + ((!showDim) ? 'display:none;' : 'display:block;') + 'position: absolute;bottom: -20px;left: 0;" src="data:image/png;base64,' + resultx['imgL'] + '" style="" /><img class="imgH" style="' + ((!showDim) ? 'display:none;' : 'display:block;') + 'position: absolute;bottom: 0;left: -25px;" src="data:image/png;base64,' + resultx['imgH'] + '" style="" />');
                                    $('#' + idCode).parent('div.elementContainer').append('<img class="imgL" data-elemSize="' + resultx['longueur'] + '" style="' + ((!showDim) ? 'display:none;' : 'display:block;') + 'position: absolute;bottom: -20px;left: 0;" src="data:image/png;base64,' + resultx['imgL'] + '" style="" /><img class="imgH" data-elemSize="' + resultx['hauteur'] + '" style="' + ((!showDim) ? 'display:none;' : 'display:block;') + 'position: absolute;bottom: 0;left: -25px;" src="data:image/png;base64,' + resultx['imgH'] + '" style="" />');
                                    var thiscmHeight = $('#' + idCode).parent('div.elementContainer').find('img.imgH').attr('data-elemsize'),
                                        thiscmWidth = $('#' + idCode).parent('div.elementContainer').find('img.imgL').attr('data-elemsize');
                                    $('#' + idCode).parent('div.elementContainer').attr('elemratio', resultx['elemRatio']);
                                    $('#elem_width').val(thiscmWidth);
                                    $('#elem_height').val(thiscmHeight);
                                    $.post('./html5/ajax/elemPrix.php', function(data) {
                                        var prixHT = parseFloat(data).toFixed(2);
                                        var tva = (Math.round(parseFloat(data)) / 100) * 20;
                                        var prixTTC = parseFloat(prixHT) + parseFloat(tva);
                                        $('#prix_ht span').html(Number(prixHT).toFixed(2));
                                        $('#prix_ttc span').html(Number(prixTTC).toFixed(2));
                                    });
                                    console.log('text tampon');
                                    if ($('.selectedElement').hasClass('typeText')) {
                                        $('.selectedElement').attr('fontattr', fontName);
                                    }
                                }
                            });
                        }, 100);*/
                        console.log('text tampon');
                        if ($('.selectedElement').hasClass('typeText')) {
                            $('.selectedElement').attr('fontattr', fontName);
                        }
                    }
                });
            }
        }
    });
 
    $('body').on('click', '.reset_rotate', function(e) {
        e.preventDefault();
        var parentDiv = $(this).parent('div');
        parentDiv.center(true).css({
            '-moz-transform': 'rotate(0deg)',
            '-webkit-transform': 'rotate(0deg)',
            '-o-transform': 'rotate(0deg)',
            '-ms-transform': 'rotate(0deg)',
            'transform': 'rotate(0deg)'
        });
    });
    var hideResetBtn = true;
    $('body').on({
        mouseenter: function(e) {
            var elem = $(this).siblings('.reset_rotate');
            elem.fadeIn();
            e.stopImmediatePropagation();
            e.preventDefault();
        },
        mouseleave: function(e) {
            var elem = $(this).siblings('.reset_rotate');
            setTimeout(function() {
                if (hideResetBtn) elem.fadeOut();
            }, 1000);
            e.stopImmediatePropagation();
            e.preventDefault();
        }
    }, '.ui-rotatable-handle');
    $('body').on({
        mouseenter: function(e) {
            hideResetBtn = false;
            e.stopImmediatePropagation();
            e.preventDefault();
        },
        mouseleave: function(e) {
            $('.reset_rotate').fadeOut();
            e.stopImmediatePropagation();
            e.preventDefault();
        }
    }, '.reset_rotate');
    $('body').on('click', '.draggable', function(e) {
        e.preventDefault();
        $('.ctrlElem').hide();
        $('.selectedElement').removeClass('selectedElement');
        $(this).addClass('selectedElement');
        var thiscmHeight = $(this).find('img.imgH').attr('data-elemsize'),
            thiscmWidth = $(this).find('img.imgL').attr('data-elemsize');
        $('#elem_width').val(thiscmWidth);
        $('#elem_height').val(thiscmHeight);
        $('input.elemSize').prop('disabled', false);
        $('.selectedElement div.ctrlElem, .selectedElement .imgL, .selectedElement .imgH').show();
        if ($(this).hasClass('typeText')) {
        	if (typeof $('.selectedElement').attr("fontsize") != "undefined") {
        		textecfgsize = $('.selectedElement').attr("fontsize");
        		$("#textecfgsize").val(textecfgsize);
        		$('#police').ddlist('select', {value: $('.selectedElement').attr("fontattr")});
        	} else {
        		textecfgsize = $("#textecfgsize").val();
        	}
            $.data(document.body, 'currentID', $(this).find('.tampon_element').attr('id'));
            var attrVal = $(this).find('.elemContent').attr('alt');
            $('#textecfg').val(attrVal).focus();
            $('#colorpicker-element').stop(true).animate({
                height: 130
            }, 200);
        } else if ($(this).hasClass('typeMotif')) {
            $.data(document.body, 'currentID', $(this).find('.tampon_element').attr('id'));
            $('#textecfg').val('');
            $('#colorpicker-element').stop(true).animate({
                height: 130
            }, 200);
            $('#elemColor').colpickSetColor($(this).find('.elemContent').attr('data-colorcode'));
        } else if ($(this).hasClass('typeImage')) {
            $.data(document.body, 'currentID', $(this).find('.tampon_element').attr('id'));
            $('#textecfg').val('');
            $('#colorpicker-element').stop(true).animate({
                height: 0
            }, 200);
        } else if ($(this).hasClass('typeFond') || $(this).hasClass('typeForme')) {
            $.data(document.body, 'currentID', $(this).attr('id'));
            $('#textecfg').val('');
            $('#colorpicker-element').stop(true).animate({
                height: 130
            }, 200);
        }
        $('.ui-tabs-nav a[href="#element"]').trigger('click');
    });
    $('body').on('click', '.delete-element', function(e) {
        e.preventDefault();
        var elemSel = $(this);
        var attrID = $(this).parent('div').find('.elemContent').attr('id');
        if ($(this).parent('div').hasClass('typeText')) {
            var type = 'text';
        } else if ($(this).parent('div').hasClass('typeMotif')) {
            var type = 'motif';
        } else if ($(this).parent('div').hasClass('typeImage')) {
            var type = 'image';
        } else if ($(this).parent('div').hasClass('typeFond')) {
            var attrID = $(this).parent('div').attr('id');
            var type = 'fond';
        } else if ($(this).parent('div').hasClass('typeForme')) {
            var attrID = $(this).parent('div').attr('id');
            var type = 'forme';
        }
        setTimeout(function() {
            $.ajax({
                type: "POST",
                url: strUrlModuleHTML5 + "ajax/delete.php",
                data: {
                    action: 'delete',
                    typedel: type,
                    id: attrID,
                    vitType: $.data(document.body, 'typetampon'),
                    vitroType: $.data(document.body, 'typetamponVitro')
                },
                success: function(result) {
                    elemSel.parent('div').remove();
                    $('input.elemSize').prop('disabled', true).val('');
                    $('*[data-object-id="' + attrID + '"]').remove();
                    if ($.data(document.body, 'showColors')) {
                        $('#colorpicker-element').stop(true).animate({
                            height: 130
                        }, 200);
                        $.data(document.body, 'showColors', false);
                    } else {
                        $('#colorpicker-element').stop(true).animate({
                            height: 0
                        }, 200);
                    }
                    //$('#textecfg').val('');
                    $('ul#calques').sortable('refreshPositions');
                    $.post(strUrlModuleHTML5 +'ajax/elemPrix.php', function(data) {
                        var prixHT = parseFloat(data).toFixed(2);
                        var tva = (Math.round(parseFloat(data)) / 100) * 20;
                        var prixTTC = parseFloat(prixHT) + parseFloat(tva);
                        $('#prix_ht span').html(Number(prixHT).toFixed(2));
                        $('#prix_ttc span').html(Number(prixTTC).toFixed(2));
                    });
                }
            });
        }, 100);
    });
    $('ul#calques').sortable({
        update: function(event, ui) {
            var result = $(this).sortable('toArray', {
                attribute: 'data-object-id'
            });
            result.reverse();
            var newZindex = 2;
            result.forEach(function(entry) {
                if ($('#' + entry).get(0).tagName == 'DIV') {
                    $('#' + entry).css('z-index', newZindex);
                } else {
                    $('#' + entry).parent('.elementContainer').css('z-index', newZindex);
                }
                newZindex++;
            });
            $(this).sortable('refreshPositions');
        }
    });
    $('#longueur, #hauteur').on('input', function() {
        var elemCount = $('#tampon_content > .elementContainer').length;
        if (elemCount >= 1 && setCfg) {
            swal({
                title: "Attention",
                text: "Vous perdrez votre travail si vous changez les dimensions de la tampon et vous devrez recommencer votre création",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Recommencer",
                cancelButtonText: "Annuler",
                closeOnConfirm: true,
                closeOnCancel: true
            }, function(isConfirm) {
                if (isConfirm) {
                    $('#calques > li').remove();
                    $('#tampon_content .elementContainer').remove();
                    $.post(strUrlModuleHTML5 + 'ajax/reset.php', function(data) {
                        $('#prix_ht span').html('0.00');
                        $('#prix_ttc span').html('0.00');
                    });
                    nbrElements = 0;
                    $.data(document.body, 'vWidth', 0);
                    $.data(document.body, 'vHeight', 0);
                    return false;
                } else {
                    $('#longueur').val($.data(document.body, 'vWidth'));
                    $('#hauteur').val($.data(document.body, 'vHeight'));
                    return false;
                }
            });
        }
    });
    $('#sizes').change(function() {
        if (this.checked) {
            $('.imgL, .imgH').css('display', 'block');
            showDim = true;
        } else {
            $('.imgL, .imgH').css('display', 'none');
            showDim = false;
        }
    });
    $('#add_text').click(function(e) {
        e.preventDefault();
        var textVal = $('#textecfg').val();
        $('.selectedElement').css("display", "none");
        var dimensionssel = dimensionsp;
        var parentSize = $('#tampon_content #margin').css('width');
        var supportWidth = $('#longueur').val();
        var supportHeight = $('#hauteur').val();
        if (textVal == '') {
            swal({
                title: "Attention",
                text: "Vous devez saisir au moins un caractère avant de pouvoir continuer !",
                type: "warning",
                closeOnConfirm: true
            });
            return false;
        } else if (setCfg == false) {
            swal({
                title: "Attention",
                text: "Vous devez choisir votre type de votre tampon avant de pouvoir continuer !",
                type: "warning",
                closeOnConfirm: true
            }, function() {
                $('#longueur').effect('highlight', {
                    color: '#c70077'
                }, 1500);
                $('input#longueur').focus();
            });
            return false;
        } else {
            var selElemWidth = $('.selectedElement').width() + 2;
            var selElemHeight = $('.selectedElement').height() + 2;
            /*if ($('.selectedElement').hasClass('typeText')) {
                $.data(document.body, 'textEdit', true);
                couleurVar = $('.selectedElement').find('img.elemContent').attr('data-ccode');
            } else {
                couleurVar = '#000000';
            }*/
            if (typeof $('.selectedElement').attr("fontsize") != "undefined") {
                textecfgsize = $('.selectedElement').attr("fontsize");
                //document.getElementById("textecfgsize").value = textecfgsize;
            } else {
                textecfgsize = $("#textecfgsize").val();
            }
            $.ajax({
                type: "POST",
                url: strUrlModuleHTML5 + "ajax/tampons_text.php",
                dataType: "json",
                data: {
                    action: 'update',
                    text: encodeURIComponent(textVal),
                    dimensions: dimensionssel,
                    couleur: couleurVar,
                    font: fontName,
                    fontsize: textecfgsize,
                    ratio: $.data(document.body, 'ratioCfg'),
//                    width: supportWidth,
//                    height: supportHeight
                },
                success: function(result) {
                    if ($('.selectedElement').hasClass('typeText')) {
                        var selHeight = $('.selectedElement').height();
                        $.data(document.body, 'selfHeight', selHeight);
                        var oldleftcontainer = $('.selectedElement').css("left");
                        var oldtopcontainer = $('.selectedElement').css("top");
                        var oldtransformcontainer = $('.selectedElement').css("transform");
                        isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
                        if (isChrome)
                            oldtransformcontainer = $('.selectedElement').css("-webkit-transform");
                        var oldwidthcolor = couleurVar;
                        $('.selectedElement').find('.delete-element').trigger('click');
                        $.data(document.body, 'showColors', true);
                    }
                    $('.selectedElement').removeClass('selectedElement');
                    $('#margin').append('<div fontsize=' + textecfgsize + ' fontattr=' + fontName + '  class="elementContainer typeText resizable draggable rotatable" style="position:absolute"><div class="reset_rotate ctrlElem" title="Réinitialiser la position de l\'élement"></div><img class="tampon_element elemContent" src="data:image/png;base64,' + result['image'] + '" data-ccode="' + oldwidthcolor + '" alt="' + result['text'] + '" id="' + result['id'] + '" /><div class="ctrlElem seresizer ui-resizable-handle ui-resizable-se"></div><div class="delete-element ctrlElem" title="Supprimer l\'élément"></div></div>');
                    $('#colorpicker-element').stop(true).animate({
                        height: 130
                    }, 200);
                    $('#' + result['id']).parent('div').addClass('selectedElement');
					
					showfirstElem();
					
                    $('.selectedElement').find('.elemContent').attr('data-colorcode', couleurVar);
                    $('input.elemSize').prop('disabled', false);
                    setTimeout(function() {
//                        var maxWidth = $('#tampon_content').width() - 50;
//                        var maxHeight = $('#tampon_content').height() - 50;
//                        var ratio = 0;
//                        var width = $('.selectedElement .elemContent').width();
//                        var height = $('.selectedElement .elemContent').height();
//                        if (width > maxWidth) {
//                            ratio = maxWidth / width;
//                            $('.selectedElement, .selectedElement .elemContent').css("width", maxWidth);
//                            $('.selectedElement, .selectedElement .elemContent').css("height", height * ratio);
//                            height = height * ratio;
//                            width = width * ratio;
//                        }
//                        if (height > maxHeight) {
//                            ratio = maxHeight / height;
//                            $('.selectedElement, .selectedElement .elemContent').css("height", maxHeight);
//                            $('.selectedElement, .selectedElement .elemContent').css("width", width * ratio);
//                            width = width * ratio;
//                            height = height * ratio;
//                        }
//                        $('.selectedElement').center(true);
if (typeof oldtopcontainer != undefined && typeof oldleftcontainer != undefined) {
                            $('.selectedElement').css("top", oldtopcontainer);
                            $('.selectedElement').css("left", oldleftcontainer);
                            $('.selectedElement').css("transform", oldtransformcontainer);
                            $('.selectedElement').css("position", "absolute");
                        }
                        else {
                            $('.selectedElement').center(true);
                        }
                    }, 10);
                    if (textVal.length <= 10 && textVal.length > 1) {
                        var newSize = parseInt(parentSize) / 4;
                    } else if (textVal.length > 10 && textVal.length <= 20) {
                        var newSize = parseInt(parentSize) / 2;
                    } else if (textVal.length > 21) {
                        var newSize = parseInt(parentSize) / 1.3;
                    } else if (textVal.length == 1) {
                        var newSize = 110;
                    }
                    //$('.selectedElement').height('auto').width(newSize);
                    var nbrLayers = $('ul#calques li').length;
                    var newLayer = parseInt(nbrLayers) + 1;
                    zIndex = zIndex + 1;
                    $('.selectedElement').css('z-index', zIndex);
                    $('ul#calques').prepend('<li data-zindex="' + zIndex + '" data-object-id="' + result['id'] + '" class="ui-state-default"><span class="text">' + textVal + '</span><span class="layer">calque ' + newLayer + '</span></li>');
                    $('#textecfg').val(textVal);
                    $('ul#calques').sortable('refresh');
                    $('.ctrlElem, .imgL, .imgH').hide();
                    $('.selectedElement div.ctrlElem').show();
                    $('.selectedElement div.reset_rotate').hide();
                    doDraggable();
                    doResizable();
                    doRotatable();
                    if (!$.data(document.body, 'textEdit')) $('#' + result['id']).parent('div').center(true);
                    $.data(document.body, 'currentID', result['id']);
                    setTimeout(function() {
                        $.ajax({
                            type: "POST",
                            url: strUrlModuleHTML5 + "ajax/tampons_size.php",
                            dataType: "json",
                            data: {
                                action: 'getsize',
                                id: result['id'],
                                elemWidth: $('.selectedElement').width() + 2,
                                elemHeight: $('.selectedElement').height() + 2,
                                ratio: $.data(document.body, 'ratioCfg'),
                                vitWidthpx: $('#tampon_content').width(),
                                vitHeightpx: $('#tampon_content').height(),
                                vitWidthcm: $('#longueur').val(),
                                vitHeightcm: $('#hauteur').val(),
                                vitType: $.data(document.body, 'typetampon'),
                                vitroType: $.data(document.body, 'typetamponVitro'),
                                type: 'text'
                            },
                            success: function(resultx) {
                                $.data(document.body, 'currentID', result['id']);
                                //$('#' + result['id']).parent('div.elementContainer').append('<img class="imgL" style="' + ((!showDim) ? 'display:none;' : 'display:block;') + 'position: absolute;bottom: -20px;left: 0;" src="data:image/png;base64,' + resultx['imgL'] + '" style="" /><img class="imgH" style="' + ((!showDim) ? 'display:none;' : 'display:block;') + 'position: absolute;bottom: 0;left: -25px;" src="data:image/png;base64,' + resultx['imgH'] + '" style="" />');
                                $('#' + result['id']).parent('div.elementContainer').attr('elemratio', resultx['elemRatio']);
                                $('#' + result['id']).parent('div.elementContainer').append('<img class="imgL" data-elemSize="' + resultx['longueur'] + '" style="' + ((!showDim) ? 'display:none;' : 'display:block;') + 'position: absolute;bottom: -20px;left: 0;" src="data:image/png;base64,' + resultx['imgL'] + '" style="" /><img class="imgH" data-elemSize="' + resultx['hauteur'] + '" style="' + ((!showDim) ? 'display:none;' : 'display:block;') + 'position: absolute;bottom: 0;left: -25px;" src="data:image/png;base64,' + resultx['imgH'] + '" style="" />');
                                var thiscmHeight = $('#' + result['id']).parent('div.elementContainer').find('img.imgH').attr('data-elemsize'),
                                    thiscmWidth = $('#' + result['id']).parent('div.elementContainer').find('img.imgL').attr('data-elemsize');
                                $('#elem_width').val(thiscmWidth);
                                $('#elem_height').val(thiscmHeight);
                                $.post(strUrlModuleHTML5 + 'ajax/elemPrix.php', function(data) {
                                    var prixHT = parseFloat(data).toFixed(2);
                                    var tva = (Math.round(parseFloat(data)) / 100) * 20;
                                    var prixTTC = parseFloat(prixHT) + parseFloat(tva);
                                    $('#prix_ht span').html(Number(prixHT).toFixed(2));
                                    $('#prix_ttc span').html(Number(prixTTC).toFixed(2));
                                });
                            }
                        });
                    }, 100);
                }
            });
            $.data(document.body, 'textEdit', false);
        }
    });
    $('body').on('click', '.motif', function() {
        var supportWidth = $('#longueur').val();
        var supportHeight = $('#hauteur').val();
        var motifUrl = $(this).attr('id');
        if (setCfg == false) {
            swal({
                title: "Attention",
                text: "Vous devez choisir votre type de tampon avant de pouvoir continuer",
                type: "warning",
                closeOnConfirm: true
            }, function() {
                $('#longueur').effect('highlight', {
                    color: '#c70077'
                }, 1500);
                $('input#longueur').focus();
            });
            return false;
        }
        $('#textecfg').val('');
        $.ajax({
            type: "POST",
            url: strUrlModuleHTML5 +"ajax/tampons_motif.php",
            dataType: "json",
            data: {
                action: 'update',
                motif: motifUrl,
                folder: theme,
                couleur: couleurVar,
                ratio: $.data(document.body, 'ratioCfg'),
                width: supportWidth,
                height: supportHeight,
                vitType: $.data(document.body, 'typetampon'),
                vitroType: $.data(document.body, 'typetamponVitro')
            },
            success: function(result) {
                 
                var srcvalue = "";
                if (result['svg'] == "1") {
                    srcvalue = result['imgMotif'];
                }
                else {
                    srcvalue = 'data:image/png;base64,' + result['imgMotif'];
                }
                $('.selectedElement').removeClass('selectedElement');
                $('#tampon_content').append('<div class="elementContainer typeMotif resizable draggable rotatable" style="position:absolute"><div class="reset_rotate ctrlElem" title="Réinitialiser la position de l\'élement"></div><img class="elemContent tampon_element" data-src-code="' + result['code'] + '" src="' + srcvalue + '" alt="' + result['motif'] + '" id="' + result['id'] + '" /><div class="ctrlElem seresizer ui-resizable-handle ui-resizable-se"></div><div class="ctrlElem delete-element" title="Supprimer l\'élément"></div></div>');
                $('#' + result['id']).parent('div').addClass('selectedElement');
				
				showfirstElem();
				
                $('input.elemSize').prop('disabled', false);
                setTimeout(function() {
                    var maxWidth = $('#tampon_content').width() - 50;
                    var maxHeight = $('#tampon_content').height() - 50;
                    var ratio = 0;
                    var width = $('.selectedElement img.elemContent').width();
                    var height = $('.selectedElement img.elemContent').height();
                    if (width > maxWidth) {
                        ratio = maxWidth / width;
                        $('.selectedElement, .selectedElement img.elemContent').css("width", maxWidth);
                        $('.selectedElement, .selectedElement img.elemContent').css("height", height * ratio);
                        height = height * ratio;
                        width = width * ratio;
                    }
                    if (height > maxHeight) {
                        ratio = maxHeight / height;
                        $('.selectedElement, .selectedElement img.elemContent').css("height", maxHeight);
                        $('.selectedElement, .selectedElement img.elemContent').css("width", width * ratio);
                        width = width * ratio;
                        height = height * ratio;
                    }
                    $('.selectedElement').center(true);
                }, 10);
                $('#colorpicker-element').stop(true).animate({
                    height: 130
                }, 200);
                var nbrLayers = $('ul#calques li').length;
                var newLayer = parseInt(nbrLayers) + 1;
                zIndex = zIndex + 1;
                $('.selectedElement').css('z-index', zIndex);
                $('ul#calques').prepend('<li data-zindex="' + zIndex + '" data-object-id="' + result['id'] + '" class="ui-state-default"><img style="width:25px" data-src-code="' + result['code'] + '" src="' + result['motif'] + '" alt="' + result['motif'] + '" /> <span class="layer">calque ' + newLayer + '</span></li>');
                $('ul#calques').sortable('refresh');
                $('.ctrlElem, .imgL, .imgH').hide();
                $('.selectedElement div.ctrlElem').show();
                $('.selectedElement div.reset_rotate').hide();
                $('.selectedElement').attr('data-colorcode', couleurVar);
                $('.selectedElement').parent().attr('data-colorcode', couleurVar);
                doDraggable();
                doResizable();
                doRotatable();
                $('#' + result['id']).parent('div').center(true);
                $.data(document.body, 'currentID', result['id']);
                /*setTimeout(function() {
                    $.ajax({
                        type: "POST",
                        url: "http://rapidpub.synapdev.fr/html5/ajax/tampons_size.php",
                        dataType: "json",
                        data: {
                            action: 'getsize',
                            id: result['id'],
                            elemWidth: parseInt($('.selectedElement').css('width'), 10) + 2,
                            elemHeight: parseInt($('.selectedElement').css('height'), 10) + 2,
                            ratio: $.data(document.body, 'ratioCfg'),
                            vitWidthpx: $('#tampon_content').width(),
                            vitHeightpx: $('#tampon_content').height(),
                            vitWidthcm: $('#longueur').val(),
                            vitHeightcm: $('#hauteur').val(),
                            vitType: $.data(document.body, 'typetampon'),
                            vitroType: $.data(document.body, 'typetamponVitro'),
                            type: 'motif'
                        },
                        success: function(resultx) {
                            //$('#' + result['id']).parent('div.elementContainer').append('<img class="imgL" style="' + ((!showDim) ? 'display:none;' : 'display:block;') + 'position: absolute;bottom: -20px;left: 0;" src="data:image/png;base64,' + resultx['imgL'] + '" style="" /><img class="imgH" style="' + ((!showDim) ? 'display:none;' : 'display:block;') + 'position: absolute;bottom: 0;left: -25px;" src="data:image/png;base64,' + resultx['imgH'] + '" style="" />');
                            $('#' + result['id']).parent('div.elementContainer').append('<img class="imgL" data-elemSize="' + resultx['longueur'] + '" style="' + ((!showDim) ? 'display:none;' : 'display:block;') + 'position: absolute;bottom: -20px;left: 0;" src="data:image/png;base64,' + resultx['imgL'] + '" style="" /><img class="imgH" data-elemSize="' + resultx['hauteur'] + '" style="' + ((!showDim) ? 'display:none;' : 'display:block;') + 'position: absolute;bottom: 0;left: -25px;" src="data:image/png;base64,' + resultx['imgH'] + '" style="" />');
                            var thiscmHeight = $('#' + result['id']).parent('div.elementContainer').find('img.imgH').attr('data-elemsize'),
                                thiscmWidth = $('#' + result['id']).parent('div.elementContainer').find('img.imgL').attr('data-elemsize');
                            $('#' + result['id']).parent('div.elementContainer').attr('elemratio', resultx['elemRatio']);
                            $('#elem_width').val(thiscmWidth);
                            $('#elem_height').val(thiscmHeight);
                            $.post('./html5/ajax/elemPrix.php', function(data) {
                                var prixHT = parseFloat(data).toFixed(2);
                                var tva = (Math.round(parseFloat(data)) / 100) * 20;
                                var prixTTC = parseFloat(prixHT) + parseFloat(tva);
                                $('#prix_ht span').html(Number(prixHT).toFixed(2));
                                $('#prix_ttc span').html(Number(prixTTC).toFixed(2));
                            });
                        }
                    });
                }, 100);*/
            }
        });
    });
    $(document).keydown(function(e) {
        if ($('.selectedElement')[0]) {
            var elemZ = $('.selectedElement'),
                parentZ = $('#tampon_content'),
                elemPosition = elemZ.position(),
                parentPosition = parentZ.position(),
                elemWidth = elemZ.width() + 2,
                elemHeight = elemZ.height() + 2,
                parentWidth = parentZ.width(),
                parentHeight = parentZ.height();
            switch (e.which) {
                case 37: // left
                    if (elemPosition.left > 2) $('.selectedElement').css('left', '-=2px');
                    break;
                case 38: // up
                    if (elemPosition.top > 2) $('.selectedElement').css('top', '-=2px')
                    break;
                case 39: // right
                    var positionRight = elemPosition.left + elemWidth;
                    if (positionRight < (parentWidth - 4)) $('.selectedElement').css('left', '+=2px')
                    break;
                case 40: // down
                    var positionBottom = elemPosition.top + elemHeight;
                    if (positionBottom < (parentHeight - 4)) $('.selectedElement').css('top', '+=2px')
                    break;
                case 46: // delete
                    var elemSel = $('.selectedElement');
                    var attrID = $('.selectedElement').find('.elemContent').attr('id');
                    if ($('.selectedElement').hasClass('typeText')) {
                        var type = 'text';
                    } else if ($('.selectedElement').hasClass('typeMotif')) {
                        var type = 'motif';
                    } else if ($('.selectedElement').hasClass('typeImage')) {
                        var type = 'image';
                    } else if ($('.selectedElement').hasClass('typeFond')) {
                        var attrID = $('.selectedElement').attr('id');
                        var type = 'fond';
                    } else if ($('.selectedElement').hasClass('typeForme')) {
                        var attrID = $('.selectedElement').attr('id');
                        var type = 'forme';
                    }
                    setTimeout(function() {
                        $.ajax({
                            type: "POST",
                            url: strUrlModuleHTML5 + "ajax/delete.php",
                            data: {
                                action: 'delete',
                                typedel: type,
                                id: attrID,
                                vitType: $.data(document.body, 'typetampon'),
                                vitroType: $.data(document.body, 'typetamponVitro')
                            },
                            success: function(result) {
                                $('.selectedElement').remove();
                                $('input.elemSize').prop('disabled', true).val('');
                                $('*[data-object-id="' + attrID + '"]').remove();
                                if ($.data(document.body, 'showColors')) {
                                    $('#colorpicker-element').stop(true).animate({
                                        height: 130
                                    }, 200);
                                    $.data(document.body, 'showColors', false);
                                } else {
                                    $('#colorpicker-element').stop(true).animate({
                                        height: 0
                                    }, 200);
                                }
                                //$('#textecfg').val('');
                                $('ul#calques').sortable('refreshPositions');
                                /*$.post(strUrlModuleHTML5 + 'ajax/elemPrix.php', function(data) {
                                    var prixHT = parseFloat(data).toFixed(2);
                                    var tva = (Math.round(parseFloat(data)) / 100) * 20;
                                    var prixTTC = parseFloat(prixHT) + parseFloat(tva);
                                    $('#prix_ht span').html(Number(prixHT).toFixed(2));
                                    $('#prix_ttc span').html(Number(prixTTC).toFixed(2));
                                });*/
                                calculPrix();
                            }
                        });
                    }, 100);
                    break;
                default:
                    return;
            }
            e.preventDefault();
        }
    });
    var editSize;
    /**

        Edition

    **/
    $('#validSize').click(function() {
        $('#validSize').hide();
        $('#elem_height, #elem_width').attr('disabled', false);
        var thisID = editSize,
            parentWidth = $('#tampon_content').height() - 10,
            parentHeight = $('#tampon_content').width() - 10,
            elemWidth = parseInt($('.selectedElement').css('width'), 10) + 2,
            elemHeight = parseInt($('.selectedElement').css('height'), 10) + 2,
            elemRatio = $('.selectedElement').attr('elemratio'),
            elemWidthCm = $('.selectedElement').find('.imgL').data('elemsize'),
            elemHeightCm = $('.selectedElement').find('.imgH').data('elemsize'),
            thisVal = $('#' + editSize).val();
        if (isNumber(thisVal)) {
            if (thisID == 'elem_width' && thisVal < parentWidth) {
                if (thisVal > elemWidthCm) {
                    var Ratio = elemWidthCm / thisVal;
                    var newHeightCm = elemHeightCm / Ratio;
                    var newHeight = elemHeight / Ratio;
                } else {
                    var Ratio = thisVal / elemWidthCm;
                    var newHeightCm = elemHeightCm * Ratio;
                    var newHeight = elemHeight * Ratio;
                }
                if (newHeight > parentHeight) return false;
                $('.selectedElement, .selectedElement > .elemContent').height(newHeight).width('auto');
            } else if (thisID == 'elem_height' && thisVal < parentHeight) {
                if (thisVal > elemHeightCm) {
                    var Ratio = elemHeightCm / thisVal;
                    var newWidthCm = elemWidthCm / Ratio;
                    var newWidth = elemWidth / Ratio;
                } else {
                    var Ratio = thisVal / elemHeightCm;
                    var newWidthCm = elemWidthCm * Ratio;
                    var newWidth = elemWidth * Ratio;
                }
                if (newWidth > parentWidth) return false;
                $('.selectedElement, .selectedElement > .elemContent').width(newWidth).height('auto');
            } else {
                return false;
            }
            if ($('.selectedElement').hasClass('typeText')) {
                var elemType = 'text';
            } else if ($('.selectedElement').hasClass('typeMotif')) {
                var elemType = 'motif';
            } else if ($('.selectedElement').hasClass('typeImage')) {
                var elemType = 'image';
            } else if ($('.selectedElement').hasClass('typeFond')) {
                var elemType = 'fond';
            } else if ($('.selectedElement').hasClass('typeForme')) {
                var elemType = 'forme';
            }
            /*setTimeout(function() {
                $.ajax({
                    type: "POST",
                    url: "./html5/ajax/tampons_size.php",
                    dataType: "json",
                    data: {
                        action: 'getsize',
                        id: $.data(document.body, 'currentID'),
                        elemWidth: $('.selectedElement').width() + 2,
                        elemHeight: $('.selectedElement').height() + 2,
                        ratio: $.data(document.body, 'ratioCfg'),
                        vitWidthpx: $('#tampon_content').width(),
                        vitHeightpx: $('#tampon_content').height(),
                        vitWidthcm: $('#longueur').val(),
                        vitHeightcm: $('#hauteur').val(),
                        vitType: $.data(document.body, 'typetampon'),
                        vitroType: $.data(document.body, 'typetamponVitro'),
                        type: elemType
                    },
                    success: function(resultx) {
                        $('.selectedElement > .imgH, .selectedElement > .imgL').remove();
                        //$('.selectedElement').append( '<img class="imgL" style="position: absolute;bottom: -20px;left: 0;" src="data:image/png;base64,' + resultx['imgL'] + '" style="" /><img class="imgH" style="position: absolute;bottom: 0;left: -25px;" src="data:image/png;base64,' + resultx['imgH'] + '" style="" />' );
                        $('.selectedElement').append('<img class="imgL" data-elemSize="' + resultx['longueur'] + '" style="position: absolute;bottom: -20px;left: 0;" src="data:image/png;base64,' + resultx['imgL'] + '" style="" /><img class="imgH" data-elemSize="' + resultx['hauteur'] + '" style="position: absolute;bottom: 0;left: -25px;" src="data:image/png;base64,' + resultx['imgH'] + '" style="" />');
                        $('.selectedElement').attr('elemratio', resultx['elemRatio']);
                        $('#elem_width').val(resultx['newWidth']);
                        $('#elem_height').val(resultx['newHeight']);
                        $.post('./html5/ajax/elemPrix.php', function(data) {
                            var prixHT = parseFloat(data).toFixed(2);
                            var tva = (Math.round(parseFloat(data)) / 100) * 20;
                            var prixTTC = parseFloat(prixHT) + parseFloat(tva);
                            $('#prix_ht span').html(Number(prixHT).toFixed(2));
                            $('#prix_ttc span').html(Number(prixTTC).toFixed(2));
                        });
                    }
                });
            }, 10);*/
        }
    });
    $('.elemSize').click(function() {
        $('#validSize').show();
        var thisID = $(this).attr('id');
        editSize = thisID;
        if (thisID == 'elem_width') {
            $('#elem_width').attr('disabled', false);
            $('#elem_height').attr('disabled', true);
        } else {
            $('#elem_height').attr('disabled', false);
            $('#elem_width').attr('disabled', true);
        }
    });
    $('.alignmove').repeatedclick(function(e) {
        e.preventDefault();
        var elemZ = $('.selectedElement'),
            parentZ = $('#tampon_content'),
            elemPosition = elemZ.position(),
            parentPosition = parentZ.position(),
            elemWidth = elemZ.width() + 2,
            elemHeight = elemZ.height() + 2,
            parentWidth = parentZ.width(),
            parentHeight = parentZ.height();
        switch ($(this).attr('id')) {
            /*case 'flip_vertical':
                if (!$('.selectedElement').hasClass('typeFond')) {
                    if ($('.selectedElement .elemContent').hasClass('flipVertical')) $('.selectedElement .elemContent').removeClass('flipVertical');
                    else $('.selectedElement .elemContent').addClass('flipVertical');
                }
                break;*/
            case 'center_h':
                $('.selectedElement').centerH(true);
                break;
            case 'center_v':
                $('.selectedElement').centerV(true);
                break;
            case 'center':
                $('.selectedElement').center(true);
                break;
            case 'move_left':
                if (elemPosition.left > 2) $('.selectedElement').css('left', '-=2px');
                break;
            case 'move_right':
                var positionRight = elemPosition.left + elemWidth;
                if (positionRight < (parentWidth - 4)) $('.selectedElement').css('left', '+=2px')
                break;
            case 'move_top':
                if (elemPosition.top > 2) $('.selectedElement').css('top', '-=2px')
                break;
            case 'move_bottom':
                var positionBottom = elemPosition.top + elemHeight;
                if (positionBottom < (parentHeight - 4)) $('.selectedElement').css('top', '+=2px')
                break;
           /* case 'flip_vertical':
                $('.selectedElement').animate({
                    rotate: 180
                });
                break;
            case 'flip_horizontal':
                if ($('.selectedElement .elemContent').hasClass('flipHorizontal')) $('.selectedElement .elemContent').removeClass('flipHorizontal');
                else $('.selectedElement .elemContent').addClass('flipHorizontal');
                break;*/
        }
    }, {
        duration: 100,
        speed: 0.8,
        min: 500
    });
    
    $("#flip_vertical").click(function() {    	
    	if ($('.selectedElement .elemContent').hasClass('flipVertical')){
    		$('.selectedElement .elemContent').removeClass('flipVertical');
    	}else{
    		$('.selectedElement .elemContent').addClass('flipVertical');
    	}
    });
    
    $("#flip_horizontal").click(function() {   
    	if ($('.selectedElement .elemContent').hasClass('flipHorizontal')){
    		$('.selectedElement .elemContent').removeClass('flipHorizontal');
    	}else{
    		$('.selectedElement .elemContent').addClass('flipHorizontal');
    	}    	
    });
    
    
    $('.colorset').click(function() {
        var color = $(this).attr('data-colorset');
        couleurVar = color;
        var supportWidth = $('#longueur').val();
        var supportHeight = $('#hauteur').val();
        $('.elementContainer').each(function() {
        	if ($(this).hasClass('typeMotif')) {
	            var elementChange = $(this).find('.elemContent');
	            var srcCode = elementChange.attr('data-src-code');
	            var idCode = elementChange.attr('id');
	            $.ajax({
	                type: "POST",
	                url: strUrlModuleHTML5 + "ajax/color2png.php",
	                data: {
	                    fileName: srcCode,
	                    folder: theme,
	                    fileColor: color,
	                    id: idCode,
	                    ratio: $.data(document.body, 'ratioCfg'),
	                    width: supportWidth,
	                    height: supportHeight,
	                    vitType: $.data(document.body, 'typetampon'),
	                    vitroType: $.data(document.body, 'typetamponVitro')
	                },
	                success: function(result) {
                            var newSRC = "";
                            var IS_JSON = true;
                            try
                            {
                                     parse = JSON.parse(result);
                            }
                            catch(err)
                            {
                                    IS_JSON = false;
                            }         
                    if(IS_JSON){
                        parse = JSON.parse(result);
                        if (parse.svg) {
                        newSRC = parse.imgMotif;
                        $("#svgblock").html(parse.contentsvg);}

                    }
                     else
                        newSRC = 'data:image/gif;base64,' + result;
	                    
	                    elementChange.attr('src', newSRC);
                            elementChange.attr('data-colorcode', color);
                            elementChange.parent().attr('data-colorcode', color);
	                }
	            });
	        } else if ($(this).hasClass('typeText')) {
	            var elementChange = $(this).find('.elemContent');
	            var idCode = elementChange.attr('id');
	            var altCode = elementChange.attr('alt');
	            var dimensionssel = $('#dimensionssel').val();
	            $(this).find('img.elemContent').attr('data-ccode', color);
                    if (typeof $('.selectedElement').attr("fontsize") != "undefined") {
                textecfgsize = $('.selectedElement').attr("fontsize");
                //document.getElementById("textecfgsize").value = textecfgsize;
            } else {
                textecfgsize = $("#textecfgsize").val();
            }
	            $.ajax({
	                type: "POST",
	                dataType: "json",
	                url: strUrlModuleHTML5 + "ajax/tampons_text.php",
	                data: {
	                    action: 'update',
	                    text: encodeURIComponent(altCode),
	                    dimensions: dimensionssel,
	                    couleur: color,
	                    font: fontName,
                            fontsize: textecfgsize,
	                    id: idCode,
	                    ratio: $.data(document.body, 'ratioCfg'),
//	                    width: supportWidth,
//	                    height: supportHeight,
	                    vitType: $.data(document.body, 'typetampon'),
	                    vitroType: $.data(document.body, 'typetamponVitro')
	                },
	                success: function(result) {
	                    elementChange.attr('src', 'data:image/png;base64,' + result['image']);
                           elementChange.attr('data-colorcode', color);elementChange.parent().attr('data-colorcode', color);
	                }
	            });
	        } else if ($(this).hasClass('typeFond')) {
	            var elementChange = $(this);
	            var idCode = elementChange.attr('id');
	            $.ajax({
	                type: "POST",
	                dataType: "json",
	                url: strUrlModuleHTML5 + "ajax/tampons_fond.php",
	                data: {
	                    action: 'update',
	                    couleur: color,
	                    type: 'fond',
	                    id: idCode,
	                    ratio: $.data(document.body, 'ratioCfg'),
	                    width: supportWidth,
	                    height: supportHeight,
	                    vitType: $.data(document.body, 'typetampon'),
	                    vitroType: $.data(document.body, 'typetamponVitro')
	                },
	                success: function(result) {
	                    $(this).css('background-color', color);
	                }
	            });
	        } else if ($(this).hasClass('typeForme')) {
	            var elementChange = $(this);
	            var idCode = elementChange.attr('id');
	            $.ajax({
	                type: "POST",
	                dataType: "json",
	                url: strUrlModuleHTML5 + "ajax/tampons_forme.php",
	                data: {
	                    action: 'update',
	                    couleur: color,
	                    type: 'forme',
	                    id: idCode,
	                    ratio: $.data(document.body, 'ratioCfg'),
	                    width: supportWidth,
	                    height: supportHeight,
	                    vitType: $.data(document.body, 'typetampon'),
	                    vitroType: $.data(document.body, 'typetamponVitro')
	                },
	                success: function(result) {
	                    $(this).css('background-color', color);
	                }
	            });
	        }
        });	        
    });
    $('#epaisseur').change(function() {
        var epaisseur = $(this).val();
        var parentWidth = $('#tampon_content').width();
        if ($('#tampon_content #bordure').length == 0) {
            $('#tampon_content').append('<div id="bordure" style="width:100%;height:100%;"><div style="width:100%;height:100%;" id="bordure_interne" style="box-shadow: inset 0 0 0 ' + epaisseur + 'px #000000;"></div></div>');
        } else {
            $('#bordure_interne').css('box-shadow', 'inset 0 0 0 ' + epaisseur + 'px #000000');
        }
    });
    $('#distance').change(function() {
        var distance = $(this).val();
        if ($('#tampon_content #bordure').length != 0) {
            $('#bordure').css('padding', distance + 'px');
        }
    });
    
    $("#addpanier_button").click(function() {   
    	$('.selectedElement div.ctrlElem, .selectedElement .imgL, .selectedElement .imgH').hide();
        $('.selectedElement').removeClass('selectedElement');
        $('input.elemSize').prop('disabled', true).val('');
        $('#colorpicker-element').stop(true).animate({
            height: 0
        }, 200);
        $('#textecfg').val('');
    	
    	setTimeout(function(){
    		html2canvas($("#tampon_content"),{
                onrendered: function( canvas ) {
        
                    var imgCanvas = canvas.toDataURL();
                    console.log (imgCanvas);
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "./html5/ajax/postScrn.php",
                        data: {
                            imageCanvas: imgCanvas
                        },
                        success: function(result){                        	
                                    	
                        	var prixHT = $("#prix_ht span").html();
                        	var prixTTC = $("#prix_ttc span").html();
                        	var artid = $("#artid_module").val();
                        	var longueur = $("#longueur").val();
                        	var hauteur = $("#hauteur").val();
                        	var urlImage = result;
                        	console.log( prixHT + " -> " + prixTTC );
                        	$("#add_prix_ht").val(prixHT);
                        	$("#add_prix_ttc").val(prixTTC);
                        	$("#add_artid").val(artid);
                        	$("#add_longueur").val(longueur);
                        	$("#add_hauteur").val(hauteur);
                        	$("#add_url_image").val(urlImage);
                            //getImage(8);
                        	/** 
                            * @author RV  
                            * @date 2017-02-28
                            * disable when add to cart option 
                            **/
                            /** generatePdf('cart'); **/
                        	$("#formu_add_panier_gravograph").submit();
                        	//console.log($("#add_url_pdf").val());
                
                        }
                    });                
                }
            });
    	}, 1000);

    });

    /** ---------- generate PDF -------------**/
    function generatePdf($pdfLaunch){
		//e.preventDefault();
		var imgData = $(".elemContent").attr("src");
		var elements=[];
		$(".elementContainer img.elemContent").each(function (index) {
			//console.log("test"+$(this).closest( ".elementContainer" ).position());
			src = $(this).attr("alt");
			var width = $(this).width();
			var height = $(this).height();

                    style = $(this).closest('.elementContainer').attr("style");
                    anglecurrent = getRotationDegrees($(this).closest( ".elementContainer" ));
                    var correctionxplaque="";
                    var correctionyplaque="";
                    if (typeof $(this).closest('.elementContainer').attr( "correctionx" ) !== typeof undefined && $(this).closest('.elementContainer').attr( "correctionx" ) !== false) {
                        correctionxplaque=$(this).closest('.elementContainer').attr( "correctionx" );
                    }
                    if (typeof $(this).closest('.elementContainer').attr( "correctiony" ) !== typeof undefined &&  $(this).closest('.elementContainer').attr( "correctiony" ) !== false) {
                        correctionyplaque= $(this).closest('.elementContainer').attr( "correctiony" );
                    }
                    
                    var position = $(this).closest( ".elementContainer" ).position();
                    if($(this).closest('.elementContainer').hasClass("typeText")){
                        //font= $(this).closest('.elementContainer').attr("fontattr");
                        font=$("#police").val();
                        fontsize= $(this).closest('.elementContainer').attr("fontsize");
                        
                    }
                    else{
                        font= "0";
                        fontsize= "0";
                        
                    }
                    datacolorcode=$(this).attr("data-colorcode");
                    elements[index] = {src: src, style: style,font:font,width:width,
                        height:height,datacolorcode:datacolorcode,fontsize:fontsize,
                        correctionxplaque:position.left,correctionyplaque:position.top,
                        anglecurrent:anglecurrent
                };
		});
		var heightplaque=$("#tampon_bg").css( "height" );
		var widthplaque=$("#tampon_bg").css( "width" );
		var toplaque=$("#tampon_bg").css( "top" );
		var leftplaque=$("#tampon_bg").css( "left" );
       
		var params = JSON.stringify(elements);
		var infoplaques = JSON.stringify({
			heightplaque:heightplaque,
			widthplaque:widthplaque,
			toplaque:toplaque,
			leftplaque:leftplaque 
		});

		$.ajax({
			url : strUrlModuleHTML5+ '/ajax/ajaxtcpdf.php',
			type : 'post',
	        dataType : 'json',
			data: {
				info:params,
				infoplaques:infoplaques
			},
			success : function(result){ 
	            if(result['file_stat'] === 1){
	                var urlPdf = './html5/ajax/'+result['pdf_path'];
	                switch($pdfLaunch) {
	                    case "pdf":
	                        window.open(urlPdf, '_blank');
	                    break;
	                    case "cart":
	                        $("#add_url_pdf").val('http://'+host+ '/html5/ajax/'+result['pdf_path']);

                        	console.log($("#add_url_pdf").val());
	                    break;
	                    default: 
	                       console.log("Undefined");  
	                }
	            }
			},
			error : function(data){ 
				console.log(data);
			},
			async:false,
			cache:false
		});
    }
    /** ---------- generate PDF -------------**/
    
	$("#pdf_button").click(function(e){
		e.preventDefault();
		generatePdf('pdf');
	})
    
    function getRotationDegrees(obj) {
        var matrix = obj.css("-webkit-transform") ||
        obj.css("-moz-transform")    ||
        obj.css("-ms-transform")     ||
        obj.css("-o-transform")      ||
        obj.css("transform");
        
        if(matrix !== 'none') {
            var values = matrix.split('(')[1].split(')')[0].split(',');
            var a = values[0];
            var b = values[1];
            var angle = Math.round(Math.atan2(b, a) * (180/Math.PI));
        } else { var angle = 0; }
        
        return (angle < 0) ? angle + 360 : angle;
    }
});