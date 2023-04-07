/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
	/**
	 * Javascript to insert the link
	 * View element calls wttypograph when an article is clicked
	 * wttypograph creates the link tag, sends it to the editor,
	 * and closes the select frame.
	 * */

	// window.wttypograph = (id, title, catid, object, link, lang) => {
	window.wttypograph = () => {

		if (!Joomla.getOptions('xtd-wttypograph')) {
			// Something went wrong!
			// @TODO Close the modal
			return false;
		}

		const {
			editor
		} = Joomla.getOptions('xtd-wttypograph');


		// const tag = `<a href="${link}">${title}</a>`;
		let selection = window.parent.Joomla.editors.instances[editor].getSelection();
		window.useFullTextForTypograph = false;

		if (selection.length == 0) {
			selection = window.parent.Joomla.editors.instances[editor].getValue();
			window.useFullTextForTypograph = true;
		}


		let textarea1 = document.getElementById('wttypograph-textarea-1');
		let textarea2 = document.getElementById('wttypograph-textarea-2');
		let wttypographRenderDiv = document.getElementById('wttypograph-render');
		textarea1.innerText = selection;


		Joomla.request({
			url: 'index.php?option=com_ajax&plugin=wttypograph&group=editors-xtd&format=json&action=doTypograph',
			method: 'POST',
			data: JSON.stringify({
				'text': selection,
			}),
			onSuccess: function (response, xhr) {
				// Тут делаем что-то с результатами

				//Проверяем пришли ли ответы
				if (response !== '') {
					let typograph_result = JSON.parse(response);
					textarea2.innerHTML = typograph_result.data[0];
					wttypographRenderDiv.innerHTML = typograph_result.data[0];
				}
			},
		});


		// if (window.parent.Joomla.Modal) {
		//   window.parent.Joomla.Modal.getCurrent().close();
		// }

		return true;
	};

	document.addEventListener('DOMContentLoaded', () => {
		window.wttypograph();

		// Get the elements
		let pasteBtn = document.getElementById('pasteTypographedTextBtn');
		pasteBtn.addEventListener('click', () => {
			if (!Joomla.getOptions('xtd-wttypograph')) {
				// Something went wrong!
				// @TODO Close the modal
				return false;
			}

			const {
				editor
			} = Joomla.getOptions('xtd-wttypograph');

			let wttypographRenderDiv = document.getElementById('wttypograph-render');

			if (window.useFullTextForTypograph === true) {
				window.parent.Joomla.editors.instances[editor].setValue(wttypographRenderDiv.innerHTML);
			} else {
				window.parent.Joomla.editors.instances[editor].replaceSelection(wttypographRenderDiv.innerHTML);
			}

			if (window.parent.Joomla.Modal) {
				window.parent.Joomla.Modal.getCurrent().close();
			}
		});

	});
})();
