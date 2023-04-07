<?php
 
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Version;

class plgEditorsXtdWttypographInstallerScript{
    /**
     * This method is called after a component is installed.
     *
     * @param  \stdClass $parent - Parent object calling this method.
     *
     * @return void
     */
    public function install($parent)
    {
		
    }

    /**
     * This method is called after a component is uninstalled.
     *
     * @param  \stdClass $parent - Parent object calling this method.
     *
     * @return void
     */
    public function uninstall($parent) 
    {

		
    }

    /**
     * This method is called after a component is updated.
     *
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    public function update($parent) 
    {

    }

    /**
     * Runs just before any installation action is performed on the component.
     * Verifications and pre-requisites should run in this function.
     *
     * @param  string    $type   - Type of PreFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    public function preflight($type, $parent) 
    {
	    $version = new Version();
	    if ($version->isCompatible('4.0') == false){

		    Factory::getApplication()->enqueueMessage('&#128546; <strong>Editors-Xtd - WT Typograph</strong> plugin doesn\'t support Joomla versions <span class="alert-link">lower 4</span>. Your Joomla version is <span class="badge badge-important">'.$version->getShortVersion().'</span>','error');
		    return false;

	    }
		
    }
	

    /**
     * Runs right after any installation action is performed on the component.
     *
     * @param  string    $type   - Type of PostFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param  \stdClass $installer - Parent object calling object.
     *
     * @return void
     */
    function postflight($type, $installer)
    {
		
		// Get a db connection.
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$query = "UPDATE `#__extensions` SET `enabled`= 1 WHERE `element` = 'wttypograph'";
		$db->setQuery($query);
		$db->execute();	

	$app   = Factory::getApplication();
		$smile = '';
		if ($type != 'uninstall')
		{
			$smiles    = ['&#9786;', '&#128512;', '&#128521;', '&#128525;', '&#128526;', '&#128522;', '&#128591;'];
			$smile_key = array_rand($smiles, 1);
			$smile     = $smiles[$smile_key];
		}

		$element            = strtoupper($installer->getElement());
		$class              = 'col-';
		$web_tolk_site_icon = '';
		
		

		echo "
		<div class='row bg-white' style='margin:25px auto; border:1px solid rgba(0,0,0,0.125); box-shadow:0px 0px 10px rgba(0,0,0,0.125); padding: 10px 20px;'>
		<div class='" . $class . "8'>
		<h2>".$smile." " . Text::_("PLG_" . strtoupper($element) . "_AFTER_" . strtoupper($type)) . " <br/>" . Text::_("PLG_" . strtoupper($element)) . "</h2>
		" . Text::_("PLG_" . strtoupper($element) . "_DESC");


		echo Text::_("PLG_" . strtoupper($element) . "_WHATS_NEW");

		echo "</div>
		<div class='" . $class . "4' style='display:flex; flex-direction:column; justify-content:center;'>
		<img width='200px' src='https://web-tolk.ru/web_tolk_logo_wide.png'>
		<p>Joomla Extensions</p>
		<p class='btn-group'>
			<a class='btn btn-sm btn-outline-primary' href='https://web-tolk.ru' target='_blank'>" . $web_tolk_site_icon . " https://web-tolk.ru</a>
			<a class='btn btn-sm btn-outline-primary' href='mailto:info@web-tolk.ru'><i class='icon-envelope'></i> info@web-tolk.ru</a>
		</p>
		<p><a class='btn btn-info' href='https://t.me/joomlaru' target='_blank'>Joomla Russian Community in Telegram</a></p>
		" . Text::_("PLG_" . strtoupper($element) . "_MAYBE_INTERESTING") . "
		</div>
		";
    }
}