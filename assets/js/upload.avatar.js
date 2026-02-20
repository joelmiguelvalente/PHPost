const avatarState = {
	mode: null,           // 'file' | 'url'
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
	dialog.loading('Cargando...');// lo ejecuta bien
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

		const rsp = await fetch(`${route.url}/upload-avatar.php`, {
			method: 'POST',
			body: data
		});
		const json = await rsp.json();
		if (json.error) {
			throw json.error;
			$('.avatar-loading').hide();
		}

		avatarState.key = json.key;
		avatarState.ext = json.ext;

		loadCropper(json.key, json.ext);

	} catch (err) {
		dialog.alert('Atención', err);
		$('.avatar-loading').hide();
	} finally {
		//
	}
});

function loadCropper(key, ext) {
	const imgUrl = `${route.url}/storage/uploads/avatar_${key}.${ext}?t=${Date.now()}`;
	avatarState.imageUrl = imgUrl;

	dialog.init({
      buttonClose: true,
      title: 'Cortar avatar',
      body: `<img id="avatar-crop" src="${imgUrl}" />`,
      buttons: {
         confirm: {
            text: 'Cortar',
            action: () => saveAvatarCrop()
         },
         cancel: {
            text: 'Cerrar',
            action: 'close'
         }
      }
   });

	$('#avatar-crop').on('load', () => {
		if (avatarState.croppr) {
			avatarState.croppr.destroy();
		}

		avatarState.croppr = new Croppr('#avatar-crop', {
			aspectRatio: 1,
			maxSize: { width: imageSize, height: imageSize },
			onCropEnd: data => {
				console.log(data)
				avatarState.crop = data;
			}
		});
	});
}

async function saveAvatarCrop() {
	if (!avatarState.crop) {
		dialog.alert('Atención', 'Debes seleccionar un área');
	}

	const data = new FormData();
	data.append('key', avatarState.key);
	data.append('ext', avatarState.ext);
	data.append('x', Math.round(avatarState.crop.x));
	data.append('y', Math.round(avatarState.crop.y));
	data.append('w', Math.round(avatarState.crop.width));
	data.append('h', Math.round(avatarState.crop.height));

	try {
		const rsp = await fetch(`${route.url}/upload-crop.php`, {
			method: 'POST',
			body: data
		});
		
		const json = await rsp.json();
		console.log(json)
		if (json.error !== 'success') throw json.error;

		dialog.init({
	      buttonClose: true,
	      title: 'Excelente',
	      body: 'Avatar actualizado correctamente',
	      buttons: {
	         confirm: {
	            text: 'Aceptar',
	            action: () => location.reload()
	         }
	      }
	   });

	} catch (err) {
		dialog.alert('Atención', err);
	}
}

function resetAvatarUI() {
	avatarState.key = null;
	avatarState.ext = null;
	avatarState.crop = null;
	avatarState.imageUrl = null;

	if (avatarState.croppr) {
		avatarState.croppr.destroy();
		avatarState.croppr = null;
	}
}
