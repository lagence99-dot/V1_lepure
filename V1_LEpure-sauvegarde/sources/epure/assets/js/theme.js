(function () {
	// Fermeture de la bannière collante mobile.
	var sticky = document.getElementById('m-sticky');
	if (sticky) {
		var x = sticky.querySelector('.m-sticky-x');
		if (x) {
			x.addEventListener('click', function () {
				sticky.remove();
				document.body.classList.remove('has-m-sticky');
			});
		}
	}

	// Bouton « Copier le lien » des articles.
	var copy = document.getElementById('copy-link');
	if (copy) {
		copy.addEventListener('click', function () {
			var done = function () { copy.textContent = 'Lien copié'; };
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(window.location.href).then(done, function () {});
			}
		});
	}
})();
