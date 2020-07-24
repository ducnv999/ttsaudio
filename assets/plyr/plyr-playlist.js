(function ( $ ) {
    $.fn.PlyrPlaylist = function(options) {

		var r = {
			selector: this.selector,
			player: {},
			radio: {},
			songs: {},
			next: {},
			prev: {},
			active: false,
			options: {},
			init: function(options) {
				selector = this.selector;
				console.log(this.selector);
				console.log('options');
				console.log(options);
				var $this = this;
				this.filter();
				this.options = options || {};
				if (typeof(this.options.nextSongToShow) == 'undefined') {
					this.options.nextSongToShow = 50;
				}
				if (typeof(this.options.prevSongToShow) == 'undefined') {
					this.options.prevSongToShow = 50;
				}
				if (typeof(this.options.keyNavigation) == 'undefined') {
					this.options.keyNavigation = true;
				}
				this.container = document.querySelector(selector);
				this.container.playlist = this;
				this.player = document.querySelector(selector+' .plyr');
				this.next = document.querySelector(selector+' .next');
				this.prev = document.querySelector(selector+' .prev');
				//Plyr.setup(this.player);
        alert(this.player);
        const pl = new Plyr(this.player);
        //alert(pl);
				//this.radio = Plyr.get(this.player)[0];
        this.radio = pl.player[0];
				this.songs = document.querySelectorAll(selector+' .playlist li');
				var i;
				for(i = 0; i < this.songs.length; i++) {
					this.songs[i].onclick = function(e) {
						$this.changeChannel(e);
					}
				}
				this.setSource( this.songs[0], this.songs[0] );
				this.player.addEventListener('ended', function(e) {
					$this.nextSong(e);
				});
				this.next.onclick = function(e) {
					$this.nextSong(e);
				};
				this.prev.onclick = function(e) {
					$this.prevSong(e);
				};
				if (this.options.keyNavigation) {
					document.onkeydown = function (e) {
						e = e || window.event;
						switch (e.keyCode) {
							case 37 : // left
								$this.prevSong(e);
								break;
							case 39 : // right
								$this.nextSong(e);
								break;
							default :
								return;
						}
					}
				}
			},
			changeChannel : function(e) {
				this.setSource( e.currentTarget, e.currentTarget, true );
			},
			getId : function(el) {
				return Number(el.getAttribute('data-id'));
			},
			buildSource : function(el) {
				return [{
					src: el.getAttribute('data-audio'),
					type: 'audio/mp3'
				}];
			},
			setSource : function(selected, sourceAudio, play) {

				//alert(selected);
				selected = this.getId(selected);
				console.log(sourceAudio);
				sourceAudio = this.buildSource(sourceAudio);
				console.log(sourceAudio);
				console.log(selected);
				if(this.active !== selected) {
					this.active = selected;
					this.radio.source({
						type: 'audio',
						//title: 'test',
						sources: sourceAudio
					});
					var current = false;
					for(var i = 0; i < this.songs.length; i++) {
						if(Number(this.songs[i].getAttribute('data-id')) === selected) {
							this.songs[i].className = 'active';
							current = this.songs[i];

						} else {

							if (i > selected && i < selected + this.options.nextSongToShow) {
								this.songs[i].className = 'show up';
							}
							else if (i < selected && i >= selected - this.options.prevSongToShow) {
								this.songs[i].className = 'show down';
							}
							else {
								this.songs[i].className = 'hide';
							}
						}
					}
					if(play) {

						this.radio.play();
					}
				} else {
					this.radio.togglePlay();
				}
			},
			nextSong : function (e) {

				var next = this.active + 1;
				if(next < this.songs.length) {
					this.setSource( this.songs[next], this.songs[next], true );
				}
			},
			prevSong : function (e) {
				var prev = this.active - 1;
				if(prev >= 0) {
					this.setSource( this.songs[prev], this.songs[prev], true );
				}
			},
			filter : function() {
				$('.plyr-playlist-filter > ul > li').click(function() {

					if ($(this).hasClass('active')) {
						$(this).siblings().slideToggle();
						return false;
					}
					window.location = '//'+location.host + location.pathname +'?style='+$(this).data('id');
					return;
				});
			}
		};
		return r.init(options);


    }
}( jQuery ));
