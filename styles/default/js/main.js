;(function () {

	'use strict';



	// iPad and iPod detection
	var isiPad = function(){
		return (navigator.platform.indexOf("iPad") != -1);
	};

	var isiPhone = function(){
	    return (
			(navigator.platform.indexOf("iPhone") != -1) ||
			(navigator.platform.indexOf("iPod") != -1)
	    );
	};

	if( isiPhone() === true ){
		// 阻止双击放大
		var lastTouchEnd = 0;
		document.addEventListener('touchstart', function(event) {
			if (event.touches.length > 1) {
				event.preventDefault();
			}
		});
		document.addEventListener('touchend', function(event) {
			var now = (new Date()).getTime();
			if (now - lastTouchEnd <= 300) {
				event.preventDefault();
			}
			lastTouchEnd = now;
		}, false);
		// 阻止双指放大
		document.addEventListener('gesturestart', function(event) {
			event.preventDefault();
		});
	}

	// Main Menu Superfish
	var mainMenu = function() {
        document.addEventListener("scroll",function () {
            window.scrollY > 0 ? $("header").addClass('scrolled') : $("header").removeClass('scrolled')
        })
		$('#fh5co-primary-menu').superfish({
			delay: 0,
			animation: {
				opacity: 'show'
			},
			speed: 'fast',
			cssArrows: true,
			disableHI: true
		});

	};

	// Parallax
	var parallax = function() {
		$(window).stellar();
	};


	// Offcanvas and cloning of the main menu
	var offcanvas = function() {

		var $clone = $('#fh5co-menu-wrap').clone();
		$clone.attr({
			'id' : 'offcanvas-menu'
		});
		$clone.find('> ul').attr({
			'class' : '',
			'id' : ''
		});

		$('#fh5co-page').prepend($clone);

		// click the burger
		$('.js-fh5co-nav-toggle').on('click', function(){

			if ( $('body').hasClass('fh5co-offcanvas') ) {
				$('body').removeClass('fh5co-offcanvas');
			} else {
				$('body').addClass('fh5co-offcanvas');
			}
			// $('body').toggleClass('fh5co-offcanvas');

		});

		$('#offcanvas-menu').css('height', $(window).height());

		$(window).resize(function(){
			var w = $(window);


			$('#offcanvas-menu').css('height', w.height());

			if ( w.width() > 769 ) {
				if ( $('body').hasClass('fh5co-offcanvas') ) {
					$('body').removeClass('fh5co-offcanvas');
				}
			}

		});

	}



	// Click outside of the Mobile Menu
	var mobileMenuOutsideClick = function() {
		$(document).click(function (e) {
	    var container = $("#offcanvas-menu, .js-fh5co-nav-toggle");
	    if (!container.is(e.target) && container.has(e.target).length === 0) {
	      if ( $('body').hasClass('fh5co-offcanvas') ) {
				$('body').removeClass('fh5co-offcanvas');
			}
	    }
		});
	};


	// Animations

	var contentWayPoint = function() {
		var i = 0;
		$('.animate-box').waypoint( function( direction ) {

			if( direction === 'down' && !$(this.element).hasClass('animated') ) {

				i++;

				$(this.element).addClass('item-animate');
				setTimeout(function(){

					$('body .animate-box.item-animate').each(function(k){
						var el = $(this);
						setTimeout( function () {
							el.addClass('fadeInUp animated');
							el.removeClass('item-animate');
						},  k * 200, 'easeInOutExpo' );
					});

				}, 100);

			}

		} , { offset: '85%' } );
	};

	var sendEmail = function(){
        $('#contact-form').bootstrapValidator({
                message: '不能为空！',
                feedbackIcons: {
                    valid: 'glyphicon glyphicon-ok',
                    invalid: 'glyphicon glyphicon-remove',
                    validating: 'glyphicon glyphicon-refresh'
                },
                fields: {
                    email: {
                        validators: {
                            notEmpty: {
                                message: '请填写您的邮箱地址！'
                            },
                            emailAddress: {
                                message: '请填写正确的邮箱地址！'
                            }
                        }
                    },
                    name: {
                        message: 'The username is not valid',
                        validators: {
                            notEmpty: {
                                message: '请填写您的姓名！'
                            }
                        }
                    },
                    phone: {
                        validators: {
                            notEmpty: {
                                message: '请填写您的联系电话！'
                            }
                        }
                    },
                    message: {
                        validators: {
                            notEmpty: {
                                message: '请填写邮件内容'
                            }
                        }
                    }
                }
            }).on('success.form.bv', function(e) {
                // Prevent form submission
                e.preventDefault();

                // Get the form instance
                var $form = $(e.target);

                var alertModal = '<div class="modal" id="alert-modal">' +
					'<div class="modal-dialog">' +
                    '            <div class="modal-content">' +
                    '                <div class="modal-header">' +
                    '                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
                    '                    <h4 class="modal-title">系统提示</h4>' +
                    '                </div>' +
                    '                <div class="modal-body">' +
                    '                    <div class="text-center message"></div>' +
                    '                </div>' +
                    '            </div>' +
                    '        </div>' +
                    '    </div>';
            var $modal = $(alertModal);

            // Get the BootstrapValidator instance
                var bv = $form.data('bootstrapValidator');

                // Use Ajax to submit form data
                $.post($form.attr('action'), $form.serialize(), function(result) {
                    $modal.on('shown.bs.modal', function(){
                        var $this = $(this);
                        var $modal_dialog = $this.find('.modal-dialog');
                        var m_top = ( $(window).height() - $modal_dialog.height() )/2;
                        $modal_dialog.css({'margin': m_top + 'px auto'});
                        var $modal_message = $this.find('.message');
                        $modal_message.html(result.msg);
                    });
                    $modal.modal();
                    if( parseInt(result.code) === 200 ) {
                        $form.bootstrapValidator('resetForm', true);
					}
                }, 'json');
            });
	};

	$(".play-btn").on('click',function () {
		var video = $(this).data('video');
		var Modal = '<div class="modal" tabindex="-1" role="dialog"><div class="modal-dialog" role="document"><div  class="modal-content"><div class="modal-body"><iframe src="'+video+'" allow="autoplay; encrypted-media" allowfullscreen="allowfullscreen" style="display: block;" width="100%" height="315" frameborder="0"></div></iframe></div></div></div>';
        var $modal = $(Modal);
        $modal.on('shown.bs.modal', function(){
            var $this = $(this);
            var $modal_dialog = $this.find('.modal-dialog');
            var m_top = ( $(window).height() - $modal_dialog.height() )/2;
            $modal_dialog.css({'margin': m_top + 'px auto'});
        });
        $modal.modal();
    });
	// Document on load.
	$(function(){
		mainMenu();
		parallax();
		offcanvas();
		mobileMenuOutsideClick();
		contentWayPoint();
		sendEmail()
	});


}());
