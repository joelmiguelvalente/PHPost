'use strict';

const storage = {
   get(key) {
      return localStorage.getItem(key);
   },
   set(key, value) {
      localStorage.setItem(key, value);
   },
   remove(key) {
      localStorage.removeItem(key);
   }
};

let audioUnlocked = false;

function unlockAudio() {
	if (audioUnlocked) return;
	new Audio().play().catch(() => {});
	audioUnlocked = true;
}

const configs = {
	time: { update_fast: 3000, update_slow: 60000, hide: 4000 },
	focus: true,
	status: { notifications: 'ON', messages: 'ON', sound: 'ON' },
	total: { notifications: 0, messages: 0 },
	_noActivityCount: 0,
	_noActivityLimit: 3,
};

const checkedStatus = (label, name) => {
	const val = storage.get(name);
	if (val === null || val === undefined) {
		storage.set(name, 'ON', { expires: 90 });
	} else {
		configs.status[label] = val;
	}
};

const live = {
	_hideTimer: null,
	_updateTimer: null,
	_askingAudio: false,
	_shown: new Set(),
	_mouseBound: false,

	askAudioPermission() {
		if (this._askingAudio || $('#audio-permission-toast').length) return;
		this._askingAudio = true;

		const toast = $(`
			<div id="audio-permission-toast" role="alertdialog" aria-labelledby="audio-msg" aria-describedby="audio-msg">
				<span id="audio-msg">🔔 ¿Activar sonido para notificaciones?</span>
				<button id="audio-yes" class="audio-button" aria-label="Activar sonido">Sí</button>
				<button id="audio-no"  class="audio-button" aria-label="No activar sonido">No</button>
			</div>
		`).appendTo('body');

		$('#audio-yes').one('click', () => {
			unlockAudio();
			storage.set('audio_unlocked', 'true', { expires: 90 });
			toast.fadeOut(300, () => toast.remove());
			this._askingAudio = false;
		});

		$('#audio-no').one('click', () => {
			configs.status.sound = 'OFF';
			storage.set('live_sound', 'OFF', { expires: 90 });
			toast.fadeOut(300, () => toast.remove());
			this._askingAudio = false;
		});
	},

	inicializar() {
		checkedStatus('notifications', 'live_nots');
		checkedStatus('messages',      'live_mps');
		checkedStatus('sound',         'live_sound');

		if (configs.status.sound === 'ON' && !storage.get('audio_unlocked')) {
			this.askAudioPermission();
		}

		if (configs.status.notifications === 'OFF' && configs.status.messages === 'OFF') return;

		this._scheduleUpdate();
	},

	_scheduleUpdate() {
		clearTimeout(this._updateTimer);
		const interval = configs._noActivityCount >= configs._noActivityLimit ? configs.time.update_slow : configs.time.update_fast;
		this._updateTimer = setTimeout(() => this.update(), interval);
	},

	print(ld) {
      const container = document.getElementById('js');
      container.innerHTML = ld;

      const n_total = parseInt($('#live-stream').attr('ntotal')) || 0;
      const m_total = parseInt($('#live-stream').attr('mtotal')) || 0;
      const total   = n_total + m_total;

      if (total <= 0) {
      	configs._noActivityCount++;
      	return;
    	}
    	configs._noActivityCount = 0;

      let nuevas = 0;
      const beeperBox = document.getElementById('BeeperBox');
      const fragment = document.createDocumentFragment();

      $('#live-stream .UIBeeper_Full').each((_, el) => {
         const id = $(el).attr('id');
         if (this._shown.has(id)) return;
         this._shown.add(id);
         nuevas++;
         el.style.display = 'none';
         fragment.appendChild(el);
      });

      if (nuevas === 0) return;

      beeperBox.appendChild(fragment);
      $(fragment.children).each((_, el) => {
         $(el).fadeIn(600);
      });

      if (this._shown.size > 100) {
         const arr = [...this._shown];
         this._shown = new Set(arr.slice(-50));
      }

      if (!this._mouseBound) {
         this.mouse_events();
         this._mouseBound = true;
      }

      if (configs.focus) {
          this.scheduleHide();
      } else {
         $(document).attr('title', `(${total}) ${global_data.app.title} - ${global_data.app.slogan}`);
         this.play(m_total);
         notifica.popup(n_total);
         mensaje.popup(m_total);
      }
   },

	play(total) {
		if (configs.status.sound !== 'ON') return;
		const type  = total > 0 ? 'Message' : 'Alert';
		const audio = new Audio(`${route.assets}/sounds/new${type}.mp3`);
		audio.play().catch(err => console.warn('Audio bloqueado:', err));
	},

	mouse_events() {
		$('#BeeperBox')
			.on('mouseover', '.UIBeep', function () {
				$(this).closest('.UIBeeper_Full').addClass('UIBeep_Paused');
				clearTimeout(live._hideTimer);
			})
			.on('mouseout', '.UIBeep', function () {
				$(this).closest('.UIBeeper_Full').removeClass('UIBeep_Paused');
				live.scheduleHide();
			});
	},

	scheduleHide() {
		clearTimeout(this._hideTimer);
		this._hideTimer = setTimeout(() => this.hide(), configs.time.hide);
	},

	hide() {
		const $items = $('#BeeperBox .UIBeeper_Full').not('.UIBeep_Paused');
		if (!$items.length) return;

		$items.each((i, el) => {
			setTimeout(() => {
				$(el).fadeOut(400, function () { $(this).remove(); });
			}, i * 300);
		});
	},

	update() {
		$('#loading').fadeIn(250);
		api('live-stream', {
			nots: configs.status.notifications,
			mps:  configs.status.messages
		}, response => {
			this.print(response);
		}, { error: () => {} }).always(() => {
			$('#loading').fadeOut(350);
			this._scheduleUpdate();
		});
	},

	ch_status(type) {
		const map = { nots: 'notifications', mps: 'messages', sound: 'sound' };
		const key = map[type];
		if (!key) return;
		configs.status[key] = configs.status[key] === 'ON' ? 'OFF' : 'ON';
		storage.set('live_' + type, configs.status[key], { expires: 90 });
	}
};

$(document).ready(function () {
	$(document).on('click', '.beeper_x', function () {
		const bid = $(this).attr('bid');
		$('#beep_' + bid).fadeOut(300, function () { $(this).remove(); });
		return false;
	});

	live.inicializar();
	$(window).on('focus', () => { 
		configs.focus = true; 
	}).on('blur',  () => { 
		configs.focus = false; 
	});
});
