<?php
  /**
   * List of items to be packed, ordered by volume
   * @author Doug Wright
   * @package BoxPacker
   */
  class ArtatusItemList extends \SplMaxHeap {

    /**
     * Compare elements in order to place them correctly in the heap while sifting up.
     * @see \SplMaxHeap::compare()
     */
    public function compare($aItemA, $aItemB) {
      return $aItemA->getVolume() - $aItemB->getVolume();
    }

    /**
     * Get copy of this list as a standard PHP array
     * @return array
     */
    public function asArray() {
      $return = [];
      foreach (clone $this as $item) {
        $return[] = $item;
      }
      return $return;
    }

  }
