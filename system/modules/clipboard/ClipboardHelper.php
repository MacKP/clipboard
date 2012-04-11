<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  MEN AT WORK 2012
 * @package    clipboard
 * @license    GNU/GPL 2
 * @filesource
 */

/**
 * Class ClipboardHelper
 */
class ClipboardHelper extends Backend
{

    /**
     * Current object instance (Singleton)
     * @var ClipboardHelper
     */
    protected static $objInstance = NULL;

    /**
     * Prevent constructing the object (Singleton)
     */
    protected function __construct()
    {
        parent::__construct();

        $this->import('BackendUser', 'User');
    }

    /**
     * Prevent cloning of the object (Singleton)
     */
    final private function __clone() {}

    /**
     * Get instanz of the object (Singelton) 
     *
     * @return ClipboardHelper 
     */
    public static function getInstance()
    {
        if (self::$objInstance == NULL)
        {
            self::$objInstance = new ClipboardHelper();
        }
        return self::$objInstance;
    }

    /**
     * Return the paste button
     * 
     * @param array $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     * @param string $table
     * @return string 
     */
    public function getPasteButton($row, $href, $label, $title, $icon, $attributes, $table)
    {
        $objFavorit = Clipboard::getInstance()->getFavorite($table);

        if ($objFavorit->numRows)
        {
            $return = '';
            if ($this->User->isAdmin || ($this->User->hasAccess($row['type'], 'alpty') && $this->User->isAllowed(2, $row)))
            {
                // Create link
                $return .= vsprintf('<a href="%s" title="%s" %s>%s</a>', array(
                    // Create URL
                    $this->addToUrl(
                            vsprintf('%s&amp;id=$s&amp;%spid=%s', array(
                                $href,
                                $objFavorit->elem_id,
                                (($objFavorit->childs == 1) ? 'childs=1&amp;' : ''),
                                $row['id']
                                    )
                            )
                    ),
                    specialchars($title),
                    $attributes,
                    // Create linkimage
                    $this->generateImage($icon, $label)
                        )
                );
            }
            else
            {
                // Create image
                $return .= $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
            }
            return $return;
        }
        else
        {
            return '';
        }
    }

    /**
     * Return clipboard button
     * 
     * HOOK: $GLOBALS['TL_HOOKS']['independentlyButtons']
     * 
     * @param object $dc
     * @param array $row
     * @param string $table
     * @param boolean $cr
     * @param array $arrClipboard
     * @param childs $childs
     * @return string
     */
    public function clipboardButtons(DataContainer $dc, $row, $table, $cr, $arrClipboard = false, $childs)
    {
        $objFavorit = Clipboard::getInstance()->getFavorite($table);

        if ($dc->table == 'tl_article' && $table == 'tl_page')
        {
            // Create button title and lable
            if ($this->pageType == 'content')
            {
                $label = $title = vsprintf($GLOBALS['TL_LANG'][$dc->table]['pasteafter'][1], array(
                    $objFavorit->elem_id
                        )
                );
            }
            else
            {
                $label = $title = vsprintf($GLOBALS['TL_LANG'][$dc->table]['pasteinto'][1], array(
                    $objFavorit->elem_id
                        )
                );
            }

            // Create Paste Button
            $return = $this->getPasteButton(
                    $row, $GLOBALS['CLIPBOARD']['pasteinto']['href'], $label, $title, $GLOBALS['CLIPBOARD']['pasteinto']['icon'], $GLOBALS['CLIPBOARD']['pasteinto']['attributes'], $dc->table
            );

            return $return;
        }
    }

    /**
     * Check if the current site is in backend, allowed for clipboard and the 
     * clipboard table exists 
     * 
     * @param string $dca
     * @return boolean 
     */
    public function isClipboardReadyToUse($dca = NULL)
    {
        if ($dca == NULL || !isset($GLOBALS['CLIPBOARD']['locations']))
        {
            return FALSE;
        }

        $arrAllowedLocations = $GLOBALS['CLIPBOARD']['locations'];

        if (in_array($dca, $arrAllowedLocations))
        {
            if (TL_MODE == 'BE' && in_array($this->Input->get('do'), $arrAllowedLocations) && $this->Database->tableExists('tl_clipboard'))
            {
                return TRUE;
            }
        }
        return FALSE;
    }

}

?>