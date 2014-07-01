<?php
  class ArtatusPacker {
    /**
     * List of items to be packed
     * @var ArtatusItemList
     */
    protected $items;

    /**
     * List of box sizes available to pack items into
     * @var ArtatusBoxList
     */
    protected $boxes;

    /**
     * Should I echo all over the show?
     */
	protected $debug = false;

    /**
     * Constructor
     */
    public function __construct() {
      $this->items = new ArtatusItemList();
      $this->boxes = new ArtatusBoxList();
    }

    /**
     * Add item to be packed
     * @param ArtatusItem $aItem
     * @param int  $aQty
     */
    public function addItem(ArtatusItem $aItem, $aQty = 1) {
      for ($i = 0; $i < $aQty; $i++) {
        $this->items->insert($aItem);
      }
	  if ($this->debug)
	      echo ("added {$aQty} x {$aItem->getDescription()}");
    }

    /**
     * Set a list of items all at once
     * @param \Traversable $aItems
     */
    public function setItems($aItems) {
      if ($aItems instanceof ArtatusItemList) {
        $this->items = clone $aItems;
      }
      else if (is_array($aItems)) {
        $this->items = new ArtatusItemList();
        foreach ($aItems as $item) {
          $this->items->insert($item);
        }
      }
      else {
        throw new \RuntimeException('Not a valid list of items');
      }
    }

	/**
     * Pooch with the debug setting
     */

	public function setDebug($debugValue) {
	  $this->debug  = $debugValue;
    }

    /**
     * Add box size
     * @param Box $aBox
     */
    public function addBox(ArtatusBox $aBox) {
      $this->boxes->insert($aBox);
	  if ($this->debug)
      	echo("added box {$aBox->getReference()}");
    }

    /**
     * Add a pre-prepared set of boxes all at once
     * @param ArtatusBoxList $aBoxList
     */
    public function setBoxes(ArtatusBoxList $aBoxList) {
      $this->boxes = clone $aBoxList;
    }

    /**
     * Pack items into boxes
     *
     * @throws \RuntimeException
     * @return ArtatusPackedBoxList
     */
    public function pack() {
      $packedBoxes = $this->doVolumePacking();

      //If we have multiple boxes, try and optimise/even-out weight distribution
      if ($packedBoxes->count() > 1) {
        $packedBoxes = $this->redistributeWeight($packedBoxes);
      }

	  if ($this->debug)
      	echo( "packing completed, {$packedBoxes->count()} boxes");
      return $packedBoxes;
    }

    /**
     * Pack items into boxes using the principle of largest volume item first
     *
     * @throws \RuntimeException
     * @return PackedBoxList
     */
    public function doVolumePacking() {

      $packedBoxes = new ArtatusPackedBoxList;

      //Keep going until everything packed
      while ($this->items->count()) {
        $boxesToEvaluate = clone $this->boxes;
        $packedBoxesIteration = new ArtatusPackedBoxList;

        //Loop through boxes starting with smallest, see what happens
        while (!$boxesToEvaluate->isEmpty()) {
          $box = $boxesToEvaluate->extract();
          $packedBox = $this->packIntoBox($box, clone $this->items);
          if ($packedBox->getItems()->count()) {
            $packedBoxesIteration->insert($packedBox);

            //Have we found a single box that contains everything?
            if ($packedBox->getItems()->count() === $this->items->count()) {
              break;
            }
          }
        }

        //Check iteration was productive
        if ($packedBoxesIteration->isEmpty()) {
          throw new \RuntimeException('Item ' . $this->items->top()->getDescription() . ' is too large to fit into any box');
        }

        //Find best box of iteration, and remove packed items from unpacked list
        $bestBox = $packedBoxesIteration->top();
        for ($i = 0; $i < $bestBox->getItems()->count(); $i++) {
          $this->items->extract();
        }
        $packedBoxes->insert($bestBox);

      }

      return $packedBoxes;
    }

    /**
     * Given a solution set of packed boxes, repack them to achieve optimum weight distribution
     *
     * @param ArtatusPackedBoxList $aPackedBoxes
     * @return ArtatusPackedBoxList
     */
    public function redistributeWeight(ArtatusPackedBoxList $aPackedBoxes) {
      $targetWeight = $aPackedBoxes->getMeanWeight();
	  if ($this->debug)
      	echo ("repacking for weight distribution, weight variance {$aPackedBoxes->getWeightVariance()}, target weight {$targetWeight}");

      $packedBoxes = new ArtatusPackedBoxList;

      $overWeightBoxes = array();
      $underWeightBoxes = array();
      foreach ($aPackedBoxes as $packedBox) {
        $boxWeight = $packedBox->getWeight();
        if ($boxWeight > $targetWeight) {
          $overWeightBoxes[] = $packedBox;
        }
        else if ($boxWeight < $targetWeight) {
          $underWeightBoxes[] = $packedBox;
        }
        else {
          $packedBoxes->insert($packedBox); //target weight, so we'll keep these
        }
      }

      do { //Keep moving items from most overweight box to most underweight box
        $tryRepack = false;
	    if ($this->debug)
          echo ('boxes under/over target: ' . count($underWeightBoxes) . '/' . count($overWeightBoxes));

        foreach ($underWeightBoxes as $u => $underWeightBox) {
          foreach ($overWeightBoxes as $o => $overWeightBox) {
            $overWeightBoxItems = $overWeightBox->getItems()->asArray();

            //For each item in the heavier box, try and move it to the lighter one
            foreach ($overWeightBoxItems as $oi => $overWeightBoxItem) {
              if ($underWeightBox->getWeight() + $overWeightBoxItem->getWeight() > $targetWeight) {
                continue; //skip if moving this item would hinder rather than help weight distribution
              }

              $newItemsForLighterBox = clone $underWeightBox->getItems();
              $newItemsForLighterBox->insert($overWeightBoxItem);

              $newLighterBoxPacker = new Packer(); //we may need a bigger box
              $newLighterBoxPacker->setBoxes($this->boxes);
              $newLighterBoxPacker->setItems($newItemsForLighterBox);
              $newLighterBox = $newLighterBoxPacker->doVolumePacking()->extract();

              if ($newLighterBox->getItems()->count() === $newItemsForLighterBox->count()) { //new item fits
                unset($overWeightBoxItems[$oi]); //now packed in different box

                $newHeavierBoxPacker = new Packer(); //we may be able to use a smaller box
                $newHeavierBoxPacker->setBoxes($this->boxes);
                $newHeavierBoxPacker->setItems($overWeightBoxItems);

                $overWeightBoxes[$o] = $newHeavierBoxPacker->doVolumePacking()->extract();
                $underWeightBoxes[$u] = $newLighterBox;

                $tryRepack = true; //we did some work, so see if we can do even better
                usort($overWeightBoxes, array($packedBoxes, 'reverseCompare'));
                usort($underWeightBoxes, array($packedBoxes, 'reverseCompare'));
                break 3;
              }
            }
          }
        }
      } while ($tryRepack);

      //Combine back into a single list
      $packedBoxes->insertFromArray($overWeightBoxes);
      $packedBoxes->insertFromArray($underWeightBoxes);

      return $packedBoxes;
    }


    /**
     * Pack as many items as possible into specific given box
     * @param ArtatusBox      $aBox
     * @param ArtatusItemList $aItems
     * @return ArtatusPackedBox packed box
     */
    public function packIntoBox(ArtatusBox $aBox, ArtatusItemList $aItems) {
	  if ($this->debug)
        echo("evaluating box {$aBox->getReference()}");

      $packedItems = new ArtatusItemList;
      $remainingDepth = $aBox->getInnerDepth();
      $remainingWeight = $aBox->getMaxWeight() - $aBox->getEmptyWeight();
      $remainingWidth = $aBox->getInnerWidth();
      $remainingLength = $aBox->getInnerLength();

      $layerWidth = $layerLength = $layerDepth = 0;
      while(!$aItems->isEmpty()) {

        $itemToPack = $aItems->top();

        if ($itemToPack->getDepth() > ($layerDepth ?: $remainingDepth) || $itemToPack->getWeight() > $remainingWeight) {
          break;
        }

	  	if ($this->debug) {
        	echo( "evaluating item {$itemToPack->getDescription()}");
	        echo( "remaining width: {$remainingWidth}, length: {$remainingLength}, depth: {$remainingDepth}");
    	    echo( "layerWidth: {$layerWidth}, layerLength: {$layerLength}, layerDepth: {$layerDepth}");
		}

        $itemWidth = $itemToPack->getWidth();
        $itemLength = $itemToPack->getLength();

        $fitsSameGap = min($remainingWidth - $itemWidth, $remainingLength - $itemLength);
        $fitsRotatedGap = min($remainingWidth - $itemLength, $remainingLength - $itemWidth);

        if ($fitsSameGap >= 0 || $fitsRotatedGap >= 0) {

          $packedItems->insert($aItems->extract());
          $remainingWeight -= $itemToPack->getWeight();

          if ($fitsRotatedGap < 0 ||
              $fitsSameGap <= $fitsRotatedGap ||
              (!$aItems->isEmpty() && $aItems->top() == $itemToPack && $remainingLength >= 2 * $itemLength)) {
	  	    if ($this->debug)
              echo ("fits (better) unrotated");
            $remainingLength -= $itemLength;
            $layerWidth += $itemWidth;
            $layerLength += $itemLength;
          }
          else {
	  		if ($this->debug)
            	echo ("fits (better) rotated");
            $remainingLength -= $itemWidth;
            $layerWidth += $itemLength;
            $layerLength += $itemWidth;
          }
          $layerDepth = max($layerDepth, $itemToPack->getDepth()); //greater than 0, items will always be less deep
        }
        else {
          if (!$layerWidth) {
	  		if ($this->debug)
            	echo(  "doesn't fit on layer even when empty");
            break;
          }

          $remainingWidth = min(floor($layerWidth * 1.1), $aBox->getInnerWidth());
          $remainingLength = min(floor($layerLength * 1.1), $aBox->getInnerLength());
          $remainingDepth -= $layerDepth;

          $layerWidth = $layerLength = $layerDepth = 0;
	      if ($this->debug)
            echo ("doesn't fit, so starting next vertical layer");
        }
      }
	  if ($this->debug)
        echo ("done with this box");
      return new ArtatusPackedBox($aBox, $packedItems, $remainingWidth, $remainingLength, $remainingDepth, $remainingWeight);
    }

    /**
     * Pack as many items as possible into specific given box
     * @deprecated
     * @param Box      $aBox
     * @param ItemList $aItems
     * @return ItemList items packed into box
     */
    public function packBox(ArtatusBox $aBox, ArtatusItemList $aItems) {
      $packedBox = $this->packIntoBox($aBox, $aItems);
      return $packedBox->getItems();
    }
  }
