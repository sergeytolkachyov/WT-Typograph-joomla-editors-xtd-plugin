/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(() => {

	window.wtTypograph = editor => {

		const content = window.Joomla.editors.instances[editor].getValue();

		if (!content) {
			Joomla.renderMessages({
				error: ['There is no text for Typograf']
			});
		} else if (content) {

			let selection = window.parent.Joomla.editors.instances[editor].getSelection();
			window.useFullTextForTypograph = false;

			if (selection.length == 0) {
				selection = window.parent.Joomla.editors.instances[editor].getValue();
				window.useFullTextForTypograph = true;
			}

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

						if (window.useFullTextForTypograph === true) {
							window.parent.Joomla.editors.instances[editor].setValue(typograph_result.data[0]);
						} else {
							window.parent.Joomla.editors.instances[editor].replaceSelection(typograph_result.data[0]);
						}
					}
				},
			});
		}

		return true;
	};
})();
