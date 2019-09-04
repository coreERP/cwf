<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf\utils;

/**
 * MenuHelper: Reads the table sys.menu and renders menus
 * 
 * @author girish
 */
class MenuHelper {
    public $menuTempl = '<div class="vertical-nav">
                            <ul class="nav nav-pills flex-column">
                                {groups}
                            </ul>
                        </div>';
    public $groupTempl = '<li class="nav-item">
                            <a class="nav-link" data-toggle="collapse" href="{url-to-child}">{label}</a>
                            {childItems}
                          </li>';
    public $childItemTempl = '<div id="{url-to-child}" class="collapse">
                                <ul class="nav nav-pills flex-column ml-3 mt-0">
                                    {items}
                                </ul>
                            </div>';
    public $itemTempl = '<li class="nav-item"><a class="nav-link" href="{url-get}">{label}</a></li>';
    
    /**
     * Contains data extracted from DB
     * @var \cwf\data\DataTable 
     */
    private $menuData;
    
    /**
     * Renders the menu using various styles defined in cwf.css
     */
    public function renderHtml(): string {
        $menuList = '';
        $this->menuData = $this->getMenuData();
        $linkedMenuList = $this->getLinkedMenuList(-1);
        foreach($linkedMenuList as $lmItem) {
            if ($lmItem['HTML_TYPE'] == 'MENU_GROUP') {
                $menuList .= $this->renderMenuGroup($lmItem);
            } elseif ($lmItem['HTML_TYPE'] == 'MENU_ITEM') {
                $menuList .= $this->renderMenuItem($lmItem);
            }
        }
        return strtr($this->menuTempl, [
                    '{groups}' => $menuList
                ]) ;
    }
    
    private function getLinkedMenuList(int $parent_id) {
        $linkedList = new \SplDoublyLinkedList();
        foreach($this->menuData->Rows() as $drmenu) {
            if ($drmenu['parent_menu_id'] == $parent_id) {
                if ($drmenu['link_path'] == '') {
                    $drmenu['HTML_TYPE'] = 'MENU_GROUP';
                    $drmenu['CHILD_ITEMS'] = $this->getLinkedMenuList($drmenu['menu_id']);
                } else {
                    $drmenu['HTML_TYPE'] = 'MENU_ITEM';
                    $drmenu['CHILD_ITEMS'] = [];
                }
                $linkedList->push($drmenu);
            }
        }
        return $linkedList;
    }
    
    private function renderMenuGroup(array $lmGroup): string {
        $html = '';
        foreach($lmGroup['CHILD_ITEMS'] as $lmItem) {
            if ($lmItem['HTML_TYPE'] == 'MENU_GROUP') {
                $html .= $this->renderMenuGroup($lmItem);
            } elseif ($lmItem['HTML_TYPE'] == 'MENU_ITEM') {
                $html .= strtr($this->itemTempl, [
                                '{url-get}' => $lmItem['link_path'], 
                                '{label}' => $lmItem['menu_text']
                            ]);
            }
        }
        $cmenu = strtr($this->groupTempl, [
                        '{url-to-child}' => '#mnu-'.$lmGroup['menu_id'], 
                        '{label}' => $lmGroup['menu_text'],
                        '{childItems}' => strtr($this->childItemTempl, [
                                                '{url-to-child}' => 'mnu-'.$lmGroup['menu_id'],
                                                '{items}' => $html
                                            ])
                    ]);
        return $cmenu;
    }
    
    private function renderMenuItem(array $lmItem) {
        $rmenu = strtr($this->itemTempl, [
                    '{url-get}' => $lmItem['link_path'], 
                    '{label}' => $lmItem['menu_text']
                ]);
        return $rmenu;
    }


    /**
     * This applies the user Role access levels and fetches the menus
     * from the table sys.menu
     * 
     * @return \cwf\data\DataTable
     */
    private function getMenuData(): \cwf\data\DataTable {
        $sql = "With Recursive menu_parent
                As
                (	Select parent_menu_id, menu_id, 1 as level, menu_name, menu_text::Text, 
                                array[menu_id] as path_info, array[menu_key::text] as key_path, false as cycle,
                                link_path
                        From sys.menu
                        Where parent_menu_id = -1
                        Union All
                        Select a.parent_menu_id, a.menu_id, b.level + 1, a.menu_name, a.menu_text, 
                                b.path_info||a.menu_id, b.key_path||a.menu_key::text, a.menu_id = Any(b.path_info),
                                a.link_path
                        From sys.menu a
                        Inner Join menu_parent b On a.parent_menu_id = b.menu_id
                        Where not is_hidden And not cycle
                )
                Select parent_menu_id, menu_id, level, menu_name, menu_text, path_info, array_to_string(key_path, '/') menu_path,
                        link_path
                From menu_parent
                Order by path_info";
        $cmm = new \cwf\data\SqlCommand($sql);
        $data = \cwf\data\DataConnect::getData($cmm);
        return $data;
    }
}
