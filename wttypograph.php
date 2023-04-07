<?php

/**
 * @package         WT Typograf
 * @copyright   (C) 2023 Sergey Tolkachyov. <https://web-tolk.ru>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Object\CMSObject;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Editor Article button
 *
 * @since  1.5
 */
class PlgButtonWttypograph extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Display the button
	 *
	 * @param   string  $name  The name of the button to add
	 *
	 * @return  CMSObject|void  The button options as CMSObject, void if ACL check fails.
	 *
	 * @since   1.0.0
	 */
	public function onDisplay($name)
	{
		$user = Factory::getApplication()->getIdentity();

		// Can create in any category (component permission) or at least in one category
		$canCreateRecords = $user->authorise('core.create', 'com_content')
			|| count($user->getAuthorisedCategories('com_content', 'core.create')) > 0;

		// Instead of checking edit on all records, we can use **same** check as the form editing view
		$values           = (array) Factory::getApplication()->getUserState('com_content.edit.article.id');
		$isEditingRecords = count($values);

		// This ACL check is probably a double-check (form view already performed checks)
		$hasAccess = $canCreateRecords || $isEditingRecords;
		if (!$hasAccess)
		{
			return;
		}


		$button          = new CMSObject();

		if($this->params->get('express_mode',0) == 1){
			$app = Factory::getApplication();
			$doc = $app->getDocument();
			$doc->getWebAssetManager()
				->registerAndUseScript('admin-wttypograph', 'plg_editors-xtd_wttypograph/admin-wttypograph.js', [], ['defer' => true], ['core']);

			$button->modal   = false;
			$button->onclick = 'wtTypograph(\'' . $name . '\');return false;';
			$button->link    = '#';
		} else {
			$button->modal   = true;
			$link = 'index.php?option=com_ajax&plugin=wttypograph&group=editors-xtd&format=html&tmpl=component&action=showform&'
				. Session::getFormToken() . '=1&amp;editor=' . $name;
			$button->link    = $link;
		}

		$button->text    = Text::_('PLG_WTTYPOGRAPH_BUTTON_NAME');
		$button->name    = $this->_type . '_' . $this->_name;
		$button->icon    = 'pencil-alt';
		$button->iconSVG = '<svg viewBox="0 0 32 32" width="24" height="24"><path d="M28 24v-4h-4v4h-4v4h4v4h4v-4h4v-4zM2 2h18v6h6v10h2v-10l-8-'
			. '8h-20v32h18v-2h-16z"></path></svg>';
		$button->options = [
			'height'     => '300px',
			'width'      => '800px',
			'bodyHeight' => '70',
			'modalWidth' => '80',
		];

		return $button;
	}

	public function onAjaxWttypograph()
	{

		$app = Factory::getApplication();

		if ($app->isClient('site'))
		{
			Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));
		}


		$action = $app->input->getCmd('action', 'wttypograph');

		if ($action == 'doTypograph')
		{
			$text = Factory::getApplication()->getInput()->json->get('text', '', 'raw');
			if (isset($text) && !empty($text))
			{
				return $this->doTypograph($text);
			}

		}
		elseif ($action == 'showform')
		{
			$this->showTypographForm();
		}
	}

	/**
	 * Показывает форму для модального окна, вызываемого кнопкой редактора.
	 *
	 * @throws Exception
	 * @since 1.0.0
	 */
	public function showTypographForm()
	{
		$app    = Factory::getApplication();
		$editor = $app->input->getCmd('editor', '');
		if (!empty($editor))
		{
			$app->getDocument()->addScriptOptions('xtd-wttypograph', ['editor' => $editor]);
		}

		/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
		$wa = $app->getDocument()->getWebAssetManager();
		$wa->useStyle('bootstrap.css')->useScript('core');
		$wa->registerAndUseScript('admin-wttypograph-modal', 'plg_editors-xtd_wttypograph/admin-wttypograph-modal.js');
		$insert_btn_label = Text::_('PLG_WTTYPOGRAPH_INSERT_BUTTON_LABEL');
		$original_text_label = Text::_('PLG_WTTYPOGRAPH_ORIGINAL_TEXT_LABEL');
		$reslut_text_label = Text::_('PLG_WTTYPOGRAPH_RESULT_TEXT_LABEL');
		$preview_result_label = Text::_('PLG_WTTYPOGRAPH_PREVIEW_RESULT_LABEL');
		$html = <<<HTML

		<div class="row">
			<div class="col h-100">
				<label for="wttypograph-textarea-1" class="form-label">$original_text_label</label>
				<textarea class="form-control" rows="10" disabled="disabled" id="wttypograph-textarea-1"></textarea>
			
			</div>
			<div class="col h-100">
				<label for="wttypograph-textarea-2" class="form-label">$reslut_text_label</label>
				<textarea class="form-control" rows="10" id="wttypograph-textarea-2"></textarea>
			</div>
		</div>
		<div class="btn-group" role="group" aria-label="$insert_btn_label" id="pasteTypographedTextBtn">
			<button type="button" class="btn btn-lg btn-large btn-primary mt-3">$insert_btn_label</button>
		</div>
		<details class="shadow-sm border border-1 w-100">
			<summary class="btn btn-outline-ligh text-center w-100">$preview_result_label</summary>
				<div class="p-3" id="wttypograph-render"></div>
		</details>

		HTML;

		$html .= '<div class="fixed-bottom py-2 bg-white border-top d-flex justify-content-end">           
					<a href="https://web-tolk.ru" target="_blank" class="btn btn-sm d-inline-flex align-items-center">
						<svg width="85" height="18" xmlns="http://www.w3.org/2000/svg">
							 <g>
							  <title>Go to https://web-tolk.ru</title>
							  <text font-weight="bold" xml:space="preserve" text-anchor="start" font-family="Helvetica, Arial, sans-serif" font-size="18" id="svg_3" y="18" x="8.152073" stroke-opacity="null" stroke-width="0" stroke="#000" fill="#0fa2e6">Web</text>
							  <text font-weight="bold" xml:space="preserve" text-anchor="start" font-family="Helvetica, Arial, sans-serif" font-size="18" id="svg_4" y="18" x="45" stroke-opacity="null" stroke-width="0" stroke="#000" fill="#384148">Tolk</text>
							 </g>
						</svg>
					</a>
 				</div>
            ';
		
		echo $html;
	}

	/**
	 * Выполняет типографирование входящего текста с помощью локальной библиотеки или стороннего сервиса.
	 *
	 * @param   string  $text
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function doTypograph(string $text) :string
	{

		$http       = (new \Joomla\Http\HttpFactory())->getHttp();
		$xml_params = '<?xml version="1.0" encoding="windows-1251" ?>
				<preferences>
					<!-- Теги -->
					<tags delete="0">1</tags>
					<!-- Абзацы -->
					<paragraph insert="1">
						<start><![CDATA[<p>]]></start>
						<end><![CDATA[</p>]]></end>
					</paragraph>
					<!-- Переводы строк -->
					<newline insert="1"><![CDATA[<br />]]></newline>
					<!-- Переводы строк <p>&nbsp;</p> -->
					<cmsNewLine valid="0" />
					<!-- DOS текст -->
					<dos-text delete="0" />
					<!-- Неразрывные конструкции -->
					<nowraped insert="1" nonbsp="0" length="0">
						<start><![CDATA[<nobr>]]></start>
						<end><![CDATA[</nobr>]]></end>
					</nowraped>
					<!-- Висячая пунктуация -->
					<hanging-punct insert="0" />
					<!-- Удалять висячие слова -->
					<hanging-line delete="0" />
					<!-- Символ минус -->
					<minus-sign><![CDATA[&ndash;]]></minus-sign>
					<!-- Переносы -->
					<hyphen insert="0" length="0" />
					<!-- Акронимы -->
					<acronym insert="1"></acronym>
					<!-- Вывод символов 0 - буквами 1 - числами -->
					<symbols type="0" />
					<!-- Параметры ссылок -->
					<link target="" class="" />
				</preferences>';


		$options = [
			'text' => $text,
			'chr'  => 'UTF-8',
//			'xml'  => $xml_params
		];

		$url      = 'https://typograf.ru/webservice/';
		$response = $http->post($url, $options);
		return $response->body;
	}
}
