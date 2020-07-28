var svgLoop = function(target){
    this.target = target,
    this.radius = 150,
    this.stroke= 150,
    this.normalizedRadius = 150,
    this.circumference = 268 * Math.PI,
    this.progress = 0,
    this.progress2 = 0,
    this.progress3 = 0,
    this.stage = 0;
    this.strokeDashoffset = function () {
        return this.circumference - this.progress / 100 * this.circumference
    };
    this.strokeDashoffset2 = function () {
        return this.circumference - this.progress2 / 100 * this.circumference
    };
    this.strokeDashoffset3 = function () {
        return this.circumference - this.progress3 / 100 * this.circumference
    };
    this.stageColor = function () {
        return ["#dc2323", "#dec123", "#24c72c", "#24c72c"][this.stage]
    };
    this.animate = function () {
        var t = this;
        setTimeout(function () {
            t.progress = 33,t.render()
        }, 1e3), setTimeout(function () {
            t.progress = 66,t.render()
        }, 2e3), setTimeout(function () {
            t.progress = 100, t.stage = 1,t.render()
        }, 3e3), setTimeout(function () {
            t.progress2 = 33,t.render()
        }, 5e3), setTimeout(function () {
            t.progress2 = 66,t.render()
        }, 6e3), setTimeout(function () {
            t.progress2 = 100, t.stage = 2,t.render()
        }, 7e3), setTimeout(function () {
            t.progress3 = 33,t.render()
        }, 9e3), setTimeout(function () {
            t.progress3 = 66,t.render()
        }, 1e4), setTimeout(function () {
            t.progress3 = 100, t.stage = 3,t.render()
        }, 11e3), setTimeout(function () {
            t.stage = 0, t.progress = 0, t.progress2 = 0, t.progress3 = 0, t.render(), t.animate()
        }, 14e3)
    };
    this.render = function () {
        this.target.find('svg circle:eq(0)').css({strokeDashoffset : 0});
        this.target.find('svg circle:eq(1)').css({strokeDashoffset : this.strokeDashoffset()});
        this.target.find('svg circle:eq(2)').css({strokeDashoffset : this.strokeDashoffset2()});
        this.target.find('svg circle:eq(3)').css({strokeDashoffset : this.strokeDashoffset3()});
        if( this.stage > 1 ){
            this.target.find('.rows .c1').removeClass('active').addClass('gray');
        }
        if( this.stage > 2 ){
            this.target.find('.rows .c2').removeClass('active').addClass('gray');
        }
        if(this.stage === 1) {
            this.target.find('.rows .c1').addClass('active');
        }
        if(this.stage === 2) {
            this.target.find('.rows .c2').addClass('active');
        }
        if(this.stage === 3) {
            this.target.find('.rows .c3').addClass('active');
        }
        if(this.stage === 0){
            this.target.find('.rows .c1').removeClass('gray').removeClass('active');
            this.target.find('.rows .c2').removeClass('gray').removeClass('active');
            this.target.find('.rows .c3').removeClass('gray').removeClass('active');
        }
    }
};

new svgLoop($('.human-loop')).animate();

var customLoop = function (target) {
    this.target = target, this.stage=0,this.images = this.target.find('img'),this.labels = this.target.find('.row');
    this.animate = function () {
        var t = this;
        setInterval(function () {
            t.stage = (t.stage + 1) % t.images.length;
            t.render()
        }, 1500)
    };
    this.render = function () {
        var t = this;
        this.images.each(function (e,a) {
            if( t.stage === e ) {
                $(a).addClass('active')
            }else{
                $(a).removeClass('active')
            }
        });
        this.labels.each(function (e,a) {
            if( e <= t.stage ) {
                $(a).addClass('active')
            }else{
                $(a).removeClass('active')
            }
            if( t.stage === e ) {
                $(a).addClass('selected')
            }else{
                $(a).removeClass('selected')
            }
        })
    }
};

new customLoop($('.custom-loop')).animate();