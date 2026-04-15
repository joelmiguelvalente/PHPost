const avatarState = {
	mode: null,
	key: null,
	ext: null,
	crop: null,
	croppr: null,
	imageUrl: null
};

const $changeAvatar = $('.change-avatar');
const $avatarImg    = $('#avatar-img');
const imageSize     = 160;

$changeAvatar.on('click', '.file', function () {
	const mode = this.dataset.file;
	$('.file').hide();
	resetAvatarUI();
	avatarState.mode = mode;

	const template = document.getElementById(`${mode}-local`);
	const clone = template.content.cloneNode(true);

	$changeAvatar.find('.upload-container').remove();
	$changeAvatar.css({ 'display': 'block' })

	const container = document.createElement('div');
	container.className = 'upload-container';
	container.appendChild(clone);

	$changeAvatar.append(container);
});

$changeAvatar.on('change', '.browse-file', function (e) {
	if (!this.files?.[0]) return;
	$('.drop-message').text(this.files[0].name);
});

$changeAvatar.on('keyup', '.browse-url', function () {
	if (this.value.length > 5) {
		avatarState.imageUrl = this.value;
	}
});

$changeAvatar.on('click', '.avatar-upload', async function () {
	dialog.loading('Cargando...');
	$('.avatar-loading').show();
	try {
		const data = new FormData();

		if (avatarState.mode === 'file') {
			const fileInput = document.querySelector('.browse-file');
			data.append('file', fileInput.files[0]);
		}

		if (avatarState.mode === 'url') {
			data.append('url', avatarState.imageUrl);
		}

		const rsp = await fetch(`${route.url}/upload-avatar`, {
			method: 'POST',
			body: data
		});
		const json = await rsp.json();
		if (json.error) {
			throw json.error;
		}

		avatarState.key = json.key;
		avatarState.ext = json.ext;

		loadCropper(json.key, json.ext);

	} catch (err) {
		dialog.alert('Atención', err);
		$('.avatar-loading').hide();
	}
});

function loadCropper(key, ext) {
	const imgUrl = `${route.url}/storage/uploads/avatar_${key}.${ext}?t=${Date.now()}`;
	avatarState.imageUrl = imgUrl;

	dialog.init({
		buttonClose: true,
		title: 'Cortar avatar',
		body: `
			<div style="display:flex; gap:1rem; align-items:flex-start;">
				<div style="flex:1; min-width:0;">
					<img id="avatar-crop" src="${imgUrl}" style="max-width:100%; display:block;" />
				</div>
				<div style="display:flex; flex-direction:column; align-items:center; gap:.5rem;">
					<span style="font-size:11px; color:#888;">Preview</span>
					<div style="width:${imageSize}px; height:${imageSize}px; overflow:hidden; border-radius:50%; border:2px solid #ccc;">
						<div id="avatar-preview" style="width:100%; height:100%;"></div>
					</div>
					<div style="width:64px; height:64px; overflow:hidden; border-radius:50%; border:2px solid #ccc;">
						<div id="avatar-preview-sm" style="width:100%; height:100%;"></div>
					</div>
				</div>
			</div>`,
		buttons: {
			confirm: { text: 'Cortar', action: () => saveAvatarCrop() },
			cancel:  { text: 'Cerrar', action: 'close' }
		}
	});

	const imgEl = document.getElementById('avatar-crop');
	if (!imgEl) return;

	// Esperar a que el dialog renderice la imagen
	$(imgEl).on('load', () => {
		if (avatarState.croppr) {
			avatarState.croppr.destroy();
			avatarState.croppr = null;
		}

		avatarState.croppr = new Cropper(imgEl, {
			aspectRatio: 1,
			viewMode: 1,
			preview: '#avatar-preview, #avatar-preview-sm',
			autoCropArea: 1,      // selecciona el área máxima por defecto
			movable: true,
			rotatable: false,     // no necesario para avatares, podés activarlo
			scalable: false,
			zoomable: true,
			zoomOnWheel: true,
			cropBoxMovable: true,
			cropBoxResizable: true,
			crop(event) {
				avatarState.crop = event.detail;
			}
		});
	});

	// Si la imagen ya estaba cacheada y no dispara 'load'
	if (imgEl.complete) {
		$(imgEl).trigger('load');
	}
}

async function saveAvatarCrop() {
	if (!avatarState.croppr) {
		dialog.alert('Atención', 'El recortador no está listo.');
		return;
	}

	// Leer datos actuales aunque el usuario no haya tocado el recuadro
	const cropData = avatarState.crop ?? avatarState.croppr.getData(true);

	if (!cropData || !cropData.width || !cropData.height) {
		dialog.alert('Atención', 'Debes seleccionar un área.');
		return;
	}

	const data = new FormData();
	data.append('key', avatarState.key);
	data.append('ext', avatarState.ext);
	data.append('x', Math.round(cropData.x));
	data.append('y', Math.round(cropData.y));
	data.append('w', Math.round(cropData.width));
	data.append('h', Math.round(cropData.height));

	try {
		dialog.loading('Guardando...');

		const rsp = await fetch(`${route.url}/upload-crop`, {
			method: 'POST',
			body: data
		});
		const json = await rsp.json();

		if (json.error !== 'success') throw json.error;

		// Actualizar el avatar visible en la página sin recargar
		const canvas = avatarState.croppr.getCroppedCanvas({ width: imageSize, height: imageSize });
		$avatarImg.attr('src', canvas.toDataURL());
		$('.avatar-loading').hide();

		dialog.init({
			buttonClose: true,
			title: 'Excelente',
			body: 'Avatar actualizado correctamente.',
			buttons: {
				confirm: { text: 'Aceptar', action: () => location.reload() }
			}
		});

	} catch (err) {
		dialog.alert('Atención', err);
	}
}

function resetAvatarUI() {
	avatarState.key      = null;
	avatarState.ext      = null;
	avatarState.crop     = null;
	avatarState.imageUrl = null;

	if (avatarState.croppr) {
		avatarState.croppr.destroy();
		avatarState.croppr = null;
	}
}

$changeAvatar.on('dragover dragleave drop', '#drop-region label', function(e) {
	e.preventDefault();
	$(this).toggleClass('dragover', e.type === 'dragover');
	if (e.type === 'drop') {
		const file = e.originalEvent.dataTransfer.files[0];
		if (file) {
			$('.browse-file')[0].files = e.originalEvent.dataTransfer.files;
			$('.drop-message').html(`${file.name}<span>${(file.size / 1024).toFixed(0)} KB</span>`);
		}
	}
});
