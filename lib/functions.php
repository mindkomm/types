<?php

namespace Types;

/**
 * Recursively add ancestor class and properties to menu items.
 *
 * @param object $child       Child menu item.
 * @param array  $menu_items  All menu items.
 * @param bool   $with_parent Whether to add the direct parent property and class.
 *
 * @return array Updated menu items.
 */
function menu_items_ancestors( $child, $menu_items, $with_parent = true ) {
	// Bailout if menu item has no parent.
	if ( ! (int) $child->menu_item_parent ) {
		return $menu_items;
	}

	foreach ( $menu_items as $item ) {
		if ( (int) $item->ID === (int) $child->menu_item_parent ) {
			if ( $with_parent ) {
				$item->current_item_parent = true;
				$item->classes[]           = 'current-menu-parent';
			}

			$item->current_item_ancestor = true;
			$item->classes[]             = 'current-menu-ancestor';

			if ( (int) $item->menu_item_parent ) {
				$menu_items = menu_items_ancestors( $item, $menu_items, false );
			}

			break;
		}
	}

	return $menu_items;
}
