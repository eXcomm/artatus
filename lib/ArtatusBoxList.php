<?php
  /**
   * List of boxes available to put items into, ordered by volume
   * @author Doug Wright
   * @package BoxPacker
   */
  class ArtatusBoxList extends \SplMinHeap {

    /**
     * Compare elements in order to place them correctly in the heap while sifting up.
     * @see \SplMinHeap::compare()
     */
    public function compare($aBoxA, $aBoxB) {
      return $aBoxB->getInnerVolume() - $aBoxA->getInnerVolume();
    }

  }
