 <?php
  require('lib/artatus.php');
  /*
   * To figure out which boxes you need, and which items go into which box
   */
  $packer = new ArtatusPacker();
  $packer->addBox(new ArtatusBox('No. 1 box', 300, 300, 10, 10, 296, 296, 8, 1000));
  $packer->addBox(new ArtatusBox('No. 1 box', 300, 300, 10, 10, 296, 296, 8, 1000));
  $packer->addBox(new ArtatusBox('No. 1 box', 300, 300, 10, 10, 296, 296, 8, 1000));
  $packer->addBox(new ArtatusBox('No. 1 box', 300, 300, 10, 10, 296, 296, 8, 1000));
  $packer->addBox(new ArtatusBox('No. 1 box', 300, 300, 10, 10, 296, 296, 8, 1000));
  $packer->addBox(new ArtatusBox('No. 1 box', 300, 300, 10, 10, 296, 296, 8, 1000));
  $packer->addItem(new ArtatusItem('Item 1', 250, 250, 2, 200));
  $packer->addItem(new ArtatusItem('Item 2', 250, 250, 2, 200));
  $packer->addItem(new ArtatusItem('Item 3', 250, 250, 2, 200));
  $packedBoxes = $packer->pack();

  echo("These items fitted into " . count($packedBoxes) . " box(es)" . PHP_EOL);
  foreach ($packedBoxes as $packedBox) {
    $boxType = $packedBox->getBox(); // your own box object, in this case BasicBox
    echo("This box is a {$boxType->getReference()}, it is {$boxType->getOuterWidth()}mm wide, {$boxType->getOuterLength()}mm long and {$boxType->getOuterDepth()}mm high" . PHP_EOL);
    echo("The combined weight of this box and the items inside it is {$packedBox->getWeight()}g" . PHP_EOL);

    echo("The items in this box are:" . PHP_EOL);
    $itemsInTheBox = $packedBox->getItems();
    foreach ($itemsInTheBox as $item) { // your own item object, in this case BasicItem
      echo($item->getDescription() . PHP_EOL);
    }

    echo(PHP_EOL);
  }



  /*
   * To just see if a selection of items will fit into one specific box
   */
  $box = new ArtatusBox('Le box', 300, 300, 10, 10, 296, 296, 8, 1000);

  $items = new ArtatusItemList();
  $items->insert(new ArtatusItem('Item 1', 297, 296, 2, 200));
  $items->insert(new ArtatusItem('Item 2', 297, 296, 2, 500));
  $items->insert(new ArtatusItem('Item 3', 296, 296, 4, 290));

  $packer = new ArtatusPacker();
  $packedBox = $packer->packIntoBox($box, $items);
  /* $packedBox->getItems() contains the items that fit */
