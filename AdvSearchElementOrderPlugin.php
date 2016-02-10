<?php
/**
 * @package AdvSearchElementOrder
 * @copyright Copyright 2015, Gero Zahn
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPLv3 or any later version
 */

class AdvSearchElementOrderPlugin extends Omeka_Plugin_AbstractPlugin {

  protected $_filters = array('elements_select_options');

  public function filterElementsSelectOptions($options) {

    $allowedIds = array();

    foreach($options as $optionGroup) {
      foreach(array_keys($optionGroup) as $allowedId) {
        $allowedIds[] = $allowedId;
      }
    }
    $allowedIds = array_unique($allowedIds);
    $allowed = implode(",", $allowedIds);

    $sqlDb = get_db();
    $select = "
      SELECT es.name AS element_set_name, e.id AS element_id,
      e.name AS element_name, it.name AS item_type_name
      FROM {$sqlDb->ElementSet} es
      JOIN {$sqlDb->Element} e ON es.id = e.element_set_id
      LEFT JOIN {$sqlDb->ItemTypesElements} ite ON e.id = ite.element_id
      LEFT JOIN {$sqlDb->ItemType} it ON ite.item_type_id = it.id
      WHERE es.id in (1,3) AND e.id in ($allowed)
      ORDER BY es.id, it.name, e.name
    ";
    $records = $sqlDb->fetchAll($select);
    $elements = array();
    foreach ($records as $record) {
        $optGroup = $record['item_type_name']
                  ? __('Item Type') . ': ' . __($record['item_type_name'])
                  : __($record['element_set_name']);
        $value = __($record['element_name']);
        $elements[$optGroup][$record['element_id']] = $value;
    }

    return $elements;
  }

}
